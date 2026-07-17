<?php

namespace App\Http\Controllers;

use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\Kanban;
use App\Models\Line;
use App\Models\Mold;
use App\Models\Mutation;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class InjectionController extends Controller
{
    private const PRD_BODY_URL = 'https://prdreport.aiia.co.id/prd_body/';

    /**
     * Halaman utama scan injection.
     */
    public function index()
    {
        return view('pages.injection.index');
    }

    /**
     * Halaman PRD report injection.
     */
    public function indexprdreport()
    {
        return view('pages.injection.prdreport');
    }

    /**
     * Halaman direct injection, jika digunakan.
     */
    public function direct()
    {
        return view('pages.injection.direct');
    }

    /**
     * Cek tag mold dan ambil semua part anggota mold.
     *
     * Dipakai oleh blade:
     * GET /injection/mold-check/{moldCode}?line=INJ001
     *
     * Response parts harus dapat dipakai oleh blade untuk validasi membership part.
     */
    public function moldCheck(Request $request, string $moldCode): JsonResponse
    {
        $lineName = $request->query('line');

        $mold = Mold::query()
            ->with([
                // Sesuaikan nama relasi ini jika di model Mold paduka berbeda.
                // Ideal: Mold hasMany MoldPart, dan MoldPart belongsTo InternalPart.
                'parts.internalPart.customerPart',
            ])
            ->where('tag', $moldCode)
            ->first();

        if (!$mold) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tag mold tidak ditemukan.',
            ], 404);
        }

        if ($lineName && $this->moldHasLineColumn($mold)) {
            $line = Line::where('name', $lineName)->first();

            if (!$line) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Line tidak ditemukan.',
                ], 404);
            }

            if ((int) $mold->line_id !== (int) $line->id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Mold tidak sesuai dengan line.',
                ], 422);
            }
        }

        $parts = $this->formatMoldParts($mold);

        if (empty($parts)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mold belum memiliki part anggota.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'        => $mold->id,
                'mold_code' => $mold->tag ?? $moldCode,
                'mold_name' => $mold->name ?? $moldCode,
                'parts'     => $parts,
            ],
        ]);
    }

    /**
     * Ambil internal part dari customer part.
     *
     * Dipakai oleh blade:
     * GET /injection/api-get-internal/{custPart}
     */
    public function getInternalPart(string $custPart): JsonResponse
    {
        $customerPart = CustomerPart::with('internalPart')
            ->where('part_number', $custPart)
            ->first();

        if (!$customerPart || !$customerPart->internalPart) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Internal part tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $customerPart,
        ]);
    }

    /**
     * Simpan hasil scan part anggota mold.
     *
     * Dipakai oleh blade:
     * GET /injection/store
     *
     * Request:
     * - line
     * - mold_id
     * - mold_code
     * - partNumber
     * - seri
     * - start_time
     * - end_time
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'line'       => ['required', 'string', 'max:64'],
            'mold_id'    => ['nullable'],
            'mold_code'  => ['required', 'string', 'max:128'],
            'partNumber' => ['required', 'string', 'max:128'],
            'seri'       => ['nullable', 'string', 'max:64'],
            'start_time' => ['required', 'date'],
            'end_time'   => ['required', 'date'],
        ]);

        $seri = $validated['seri'] ?: 'noseri';

        $line = Line::where('name', $validated['line'])->first();
        if (!$line) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Line tidak ditemukan.',
            ], 404);
        }

        $mold = Mold::with(['parts.internalPart.customerPart'])
            ->when($validated['mold_id'] ?? null, fn($query, $moldId) => $query->where('id', $moldId))
            ->where(function ($query) use ($validated) {
                $query->where('tag', $validated['mold_code']);
            })
            ->first();

        if (!$mold) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mold tidak ditemukan.',
            ], 404);
        }

        $internalPart = InternalPart::where('part_number', $validated['partNumber'])->first();
        if (!$internalPart) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Part tidak ditemukan di internal_parts.',
            ], 404);
        }

        if (!$this->isPartMemberOfMold($mold, $internalPart->id)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Part bukan anggota mold.',
            ], 422);
        }

        $customerPart = CustomerPart::where('internal_part_id', $internalPart->id)->first();
        $qtyPerKanban = (int) ($customerPart->qty_per_kanban ?? 1);

        $kanban = null;
        if ($seri !== 'noseri') {
            $kanban = Kanban::where('internal_part_id', $internalPart->id)
                ->where('serial_number', $seri)
                ->first();

            if (!$kanban) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Kanban tidak terdaftar.',
                ], 404);
            }

            if (!$this->canSkipKanbanStatusCheck($internalPart) && (int) $kanban->status === 1) {
                return response()->json([
                    'status'  => 'kanbanExist',
                    'message' => 'Kanban sudah di-scan.',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $this->sendInboundToPrdReport([
                'line_id' => $validated['line'],
                'prd_dt'  => (new DateTime($validated['start_time']))->format('Y-m-d'),
                'str_dt'  => $validated['start_time'],
                'end_dt'  => '',
                'matnr'   => $validated['partNumber'],
                'menge'   => $qtyPerKanban,
                'crtby'   => auth()->user()->npk ?? 'SYS',
            ]);

            Mutation::create([
                'internal_part_id' => $internalPart->id,
                'serial_number'   => $seri,
                'type'            => 'supply',
                'qty'             => $qtyPerKanban,
                'npk'             => auth()->user()->npk ?? 'SYS',
                'date'            => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            if ($kanban) {
                // $kanban->update(['status' => 1]);
            }

            $this->publishProductionQuantity();

            DB::commit();

            return response()->json([
                'status'      => 'success',
                'message'     => 'Part anggota mold berhasil di-scan.',
                'qty'         => $qtyPerKanban,
                'part_number' => $internalPart->part_number,
                'back_number' => $internalPart->back_number,
                'photo'       => $internalPart->photo ?? null,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop / release injection.
     *
     * Dipakai oleh blade:
     * POST /injection/api-stop
     */
    public function inboundStop(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'line'      => ['required', 'string', 'max:64'],
            'mold_id'   => ['nullable'],
            'mold_code' => ['nullable', 'string', 'max:128'],
            'end_time'  => ['nullable', 'date'],
        ]);

        $response = Http::withOptions(['verify' => false])
            ->post(self::PRD_BODY_URL . '?action=api_stop_inbound', [
                'line_id' => $validated['line'],
            ]);

        if (!$response->successful()) {
            return response()->json([
                'status'  => 'error',
                'message' => $response->body(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $response->json(),
        ]);
    }

    /**
     * Ambil list stop reason berdasarkan line dan category.
     *
     * Dipakai oleh blade:
     * GET /injection/api-list-stop/{line}/{category}
     */
    public function getListStop(string $line, string $category): JsonResponse
    {
        $response = Http::withOptions(['verify' => false])
            ->get(self::PRD_BODY_URL, [
                'action'   => 'api_list_stop',
                'line'     => $line,
                'category' => $category,
            ]);

        if (!$response->successful()) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch stop list.',
            ], 500);
        }

        return response()->json($response->json());
    }

    /**
     * Insert stop reason / setup / QC cek.
     *
     * Dipakai oleh blade:
     * POST /injection/api-insert-stop
     */
    public function insertStop(Request $request): JsonResponse
    {
        $response = Http::withOptions(['verify' => false])
            ->post(self::PRD_BODY_URL . '?action=api_insert_inb_stop', $request->all());

        if (!$response->successful()) {
            return response()->json([
                'status'  => 'error',
                'message' => $response->body(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $response->json(),
        ]);
    }

    /**
     * Optional: cek line injection.
     */
    public function lineCheck(string $line): JsonResponse
    {
        $lineData = Line::where('name', $line)->first();

        if (!$lineData) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Line tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'line'   => $lineData->name,
        ]);
    }

    /**
     * Optional: scan counter per line.
     */
    public function getCurrentScanCount(string $line): JsonResponse
    {
        $record = DB::table('line_qty_temp')->where('line', $line)->first();

        return response()->json([
            'status'     => 'success',
            'total_scan' => $record->qty ?? 0,
        ]);
    }

    /**
     * Optional: reset scan counter per line.
     */
    public function resetScanCount(string $line): JsonResponse
    {
        DB::table('line_qty_temp')
            ->where('line', $line)
            ->update([
                'qty'        => 0,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Counter for line {$line} has been reset.",
        ]);
    }

    /**
     * Optional: update scan target per line.
     */
    public function updateScanTarget(string $line, int $target): JsonResponse
    {
        DB::table('line_qty_temp')
            ->where('line', $line)
            ->update([
                'target'     => $target,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Target for line {$line} updated.",
        ]);
    }

    /**
     * Optional: manual adjustment stock.
     */
    public function adjust(Request $request): RedirectResponse
    {
        $request->validate([
            'internal_part_id' => ['required', 'integer'],
            'current_stock'    => ['nullable', 'numeric'],
            'standard_stock'   => ['nullable', 'numeric'],
        ]);

        $productionStock = \App\Models\ProductionStock::where('internal_part_id', $request->internal_part_id)->firstOrFail();
        $actualStock = $request->current_stock;
        $currentStock = $productionStock->current_stock;

        $type = $actualStock < $currentStock ? 'checkout' : 'supply';
        $qty = abs($currentStock - $actualStock);

        try {
            DB::beginTransaction();

            if ($request->standard_stock !== null) {
                InternalPart::where('id', $request->internal_part_id)
                    ->update(['standard_stock' => $request->standard_stock]);
            }

            if ($actualStock !== null) {
                Mutation::create([
                    'internal_part_id' => $request->internal_part_id,
                    'serial_number'   => 'xxxx',
                    'qty'             => $qty,
                    'type'            => $type,
                    'npk'             => auth()->user()->npk ?? 'SYS',
                    'date'            => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Updated Successfully!');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error: ' . $th->getMessage());
        }
    }

    /**
     * Format part anggota mold untuk dikirim ke blade.
     *
     * Catatan:
     * - Jika relasi Mold -> parts -> internalPart berbeda, cukup sesuaikan method ini.
     */
    private function formatMoldParts(Mold $mold): array
    {
        return $mold->parts
            ->map(function ($moldPart) {
                $internalPart = $moldPart->internalPart ?? $moldPart;
                $customerPart = $internalPart->customerPart;

                return [
                    'id'             => $internalPart->id,
                    'part_number'    => $internalPart->part_number,
                    'back_number'    => $internalPart->back_number,
                    'part_name'      => $internalPart->part_name,
                    'qty_per_kanban' => (int) ($customerPart->qty_per_kanban ?? 1),
                    'photo'          => $internalPart->photo ?? null,
                ];
            })
            ->filter(fn($part) => !empty($part['part_number']))
            ->values()
            ->all();
    }

    /**
     * Cek apakah part merupakan anggota mold.
     */
    private function isPartMemberOfMold(Mold $mold, int $internalPartId): bool
    {
        return $mold->parts->contains(function ($moldPart) use ($internalPartId) {
            if (isset($moldPart->internal_part_id)) {
                return (int) $moldPart->internal_part_id === $internalPartId;
            }

            return (int) $moldPart->id === $internalPartId;
        });
    }

    /**
     * Beberapa kanban boleh skip validasi status jika mengikuti rule lama.
     */
    private function canSkipKanbanStatusCheck(InternalPart $internalPart): bool
    {
        return isset($internalPart->back_number)
            && strlen($internalPart->back_number) >= 2
            && $internalPart->back_number[1] === 'O';
    }

    /**
     * Cek apakah Mold punya field line_id.
     */
    private function moldHasLineColumn(Mold $mold): bool
    {
        return array_key_exists('line_id', $mold->getAttributes());
    }

    /**
     * Kirim data inbound ke PRD Report.
     */
    private function sendInboundToPrdReport(array $row): void
    {
        $response = Http::withOptions(['verify' => false])
            ->timeout(15)
            ->connectTimeout(5)
            ->post(self::PRD_BODY_URL . '?action=api_insert_inbound', [
                'data' => [$row],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Gagal sinkronisasi inbound ke PRD Report: ' . $response->body()
            );
        }
    }

    /**
     * Publish quantity ke MQTT, mengikuti pola controller lama.
     */
    private function publishProductionQuantity(): void
    {
        $payload = DB::table('internal_parts')
            ->join('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            ->join('lines', 'internal_parts.line_id', '=', 'lines.id')
            ->select(
                'lines.name',
                'production_stocks.internal_part_id as id',
                'internal_parts.part_number',
                'internal_parts.back_number',
                'production_stocks.current_stock'
            )
            ->groupBy(
                'internal_parts.part_number',
                'internal_parts.back_number',
                'production_stocks.internal_part_id',
                'lines.name',
                'production_stocks.current_stock'
            )
            ->get();

        $this->mqttConnect('prod/quantity', $payload);
    }

    /**
     * MQTT publisher.
     */
    private function mqttConnect(string $topic, $message): void
    {
        $server = '172.18.3.70';
        $port = 1883;
        $clientId = 'injection-' . uniqid();
        $mqttVersion = MqttClient::MQTT_3_1_1;

        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(600)
            ->setConnectTimeout(10)
            ->setLastWillTopic('test')
            ->setLastWillMessage('client disconnect')
            ->setLastWillQualityOfService(1);

        $mqtt = new MqttClient($server, $port, $clientId, $mqttVersion);

        try {
            $mqtt->connect($connectionSettings, false);
            $mqtt->publish($topic, json_encode($message), 0, false);
            sleep(1);
        } catch (\Throwable $e) {
            report($e);
        } finally {
            try {
                $mqtt->disconnect();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
