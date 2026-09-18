<?php

namespace App\Http\Controllers;

use App\Models\InternalPart;
use App\Models\Kanban;
use App\Models\KanbanPairing;
use App\Models\KanbanPairingLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.validation.index');
    }

    public function pair(Request $request): JsonResponse
    {
        $part = $request->query('part');

        if (!$part) {
            return response()->json([
                'status' => 'error',
                'message' => 'Part number is required',
            ], 400);
        }

        $normalizedPart = $this->normalize($part);
        $pair = KanbanPairing::forAssemblyPart($normalizedPart);

        if (!$pair) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Part not found in assembly kanbans'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pairing_id' => $pair->id,
            'project' => $pair->project,
            'interlock_enabled' => $pair->usesProductionInterlock(),
            'type' => $normalizedPart === $this->normalize($pair->assembly_part) ? 'assembly' : 'painting',
            'assembly' => $pair->assembly_part,
            'painting' => $pair->painting_part,
            'qty_painting' => $pair->qty_painting,
            'qty_assy' => $pair->qty_assy,
            'model_painting' => $pair->painting_back_number,
            'model_assy' => $pair->assembly_back_number
        ]);
    }

    public function completePairing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pairing_id' => ['required', 'integer', 'exists:body_kanban_pairings,id'],
            'assembly_part' => ['required', 'string', 'max:128'],
            'painting_part' => ['required', 'string', 'max:128'],
            'assembly_serials' => ['required', 'array', 'min:1'],
            'assembly_serials.*' => ['required', 'string', 'max:64'],
            'painting_serials' => ['required', 'array', 'min:1'],
            'painting_serials.*' => ['required', 'string', 'max:64'],
            'uncomplete' => ['sometimes', 'boolean'],
        ]);

        $pair = KanbanPairing::findOrFail($validated['pairing_id']);
        $assemblyPart = $this->normalize($validated['assembly_part']);
        $paintingPart = $this->normalize($validated['painting_part']);

        if ($assemblyPart !== $this->normalize($pair->assembly_part)
            || $paintingPart !== $this->normalize($pair->painting_part)) {
            return response()->json([
                'success' => false,
                'message' => 'Part pairing tidak sesuai dengan master.',
            ], 422);
        }

        if (!$pair->usesProductionInterlock()) {
            return response()->json([
                'success' => true,
                'interlock_enabled' => false,
                'message' => 'Pairing selesai.',
            ]);
        }

        $assemblySerials = $this->uniqueSerials($validated['assembly_serials']);
        $paintingSerials = $this->uniqueSerials($validated['painting_serials']);

        if (count($assemblySerials) !== count($validated['assembly_serials'])
            || count($paintingSerials) !== count($validated['painting_serials'])) {
            return response()->json([
                'success' => false,
                'message' => 'Serial kanban duplikat pada sesi pairing.',
            ], 422);
        }

        $isUncomplete = (bool) ($validated['uncomplete'] ?? false);

        if ($isUncomplete) {
            if (count($assemblySerials) !== 1 || count($paintingSerials) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mode uncomplete hanya menerima 1 kanban assembly dan 1 kanban painting.',
                ], 422);
            }
        } elseif (!$this->isValidRatio($pair, count($assemblySerials), count($paintingSerials))) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah kanban assembly dan painting belum sesuai rasio master.',
            ], 422);
        }

        $internalPart = InternalPart::whereRaw('UPPER(TRIM(part_number)) = ?', [$assemblyPart])->first();
        if (!$internalPart) {
            return response()->json([
                'success' => false,
                'message' => 'Part assembly tidak ditemukan di internal_parts.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $kanbans = Kanban::where('internal_part_id', $internalPart->id)
                ->whereIn('serial_number', $assemblySerials)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn($kanban) => $this->normalize($kanban->serial_number));

            $missingSerials = array_values(array_filter(
                $assemblySerials,
                fn($serial) => !$kanbans->has($serial)
            ));

            if (!empty($missingSerials)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Serial assembly tidak terdaftar: ' . implode(', ', $missingSerials),
                ], 404);
            }

            $activeSerials = KanbanPairingLog::whereIn('assembly_kanban_id', $kanbans->pluck('id'))
                ->where('status', KanbanPairingLog::STATUS_READY)
                ->pluck('assembly_serial_number')
                ->map(fn($serial) => $this->normalize($serial))
                ->all();

            if (!empty($activeSerials)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Serial assembly masih punya pairing aktif: ' . implode(', ', $activeSerials),
                ], 422);
            }

            foreach ($assemblySerials as $serial) {
                $kanban = $kanbans->get($serial);

                KanbanPairingLog::create([
                    'body_kanban_pairing_id' => $pair->id,
                    'assembly_internal_part_id' => $internalPart->id,
                    'assembly_kanban_id' => $kanban->id,
                    'project' => $pair->project,
                    'assembly_part' => $pair->assembly_part,
                    'assembly_serial_number' => $serial,
                    'painting_part' => $pair->painting_part,
                    'painting_serial_numbers' => $paintingSerials,
                    'status' => KanbanPairingLog::STATUS_READY,
                    'is_uncomplete' => $isUncomplete,
                    'paired_by' => auth()->user()->npk ?? 'SYS',
                    'paired_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'interlock_enabled' => true,
                'message' => 'Pairing tersimpan. Kanban assembly siap scan produksi.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    private function normalize(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function uniqueSerials(array $serials): array
    {
        return array_values(array_unique(array_map(fn($serial) => $this->normalize($serial), $serials)));
    }

    private function isValidRatio(KanbanPairing $pair, int $assemblyCount, int $paintingCount): bool
    {
        $assemblyQty = (int) $pair->qty_assy;
        $paintingQty = (int) $pair->qty_painting;

        return $assemblyQty > 0
            && $paintingQty > 0
            && $assemblyCount > 0
            && $paintingCount > 0
            && ($assemblyCount * $assemblyQty) === ($paintingCount * $paintingQty);
    }
}
