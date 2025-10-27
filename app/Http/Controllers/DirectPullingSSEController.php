<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Traits\prodPlanOps;
use Illuminate\Http\Request;
use App\Models\ProductionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectPullingSSEController extends Controller
{
    use prodPlanOps;

    public function streamDirectPullingUpdates(Request $request): StreamedResponse
    {
        $clientId = $request->ip() . '-' . substr(md5(microtime()), 0, 6);

        $selectedDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        // Lepaskan session lock agar stream tidak nge-hang
        try { if (app()->bound('session')) { session()->save(); } } catch (\Throwable $e) {}

        ignore_user_abort(true);
        @set_time_limit(0);
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');

        $headers = [
            'Content-Type'      => 'text/event-stream; charset=utf-8',
            'Cache-Control'     => 'no-cache, no-transform',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ];

        return response()->stream(function () use ($selectedDate, $clientId) {
            // ===== SSE preface =====
            echo "retry: 5000\n\n";
            @ob_flush(); @flush();

            // Connected event
            $connectedPayload = [
                'message'   => 'Connected to production plan updates',
                'clientId'  => $clientId,
                'date'      => $selectedDate->format('Y-m-d'),
                'timestamp' => now()->toIso8601String(),
            ];
            echo "event: connected\n";
            echo "data: " . json_encode($connectedPayload) . "\n\n";
            @ob_flush(); @flush();

            $startAt      = now();
            $lastCheck    = now()->subSeconds(2);   // mulai mundur 2 detik (buffer)
            $lastPing     = now()->subSeconds(10);  // paksa ping awal
            $lastSig      = null;
            $lastSigCheck = now()->subSeconds(5);

            // (Opsional, jika pakai tick trigger dari updateQty)
            $tickKey      = 'dp_update_tick:' . $selectedDate->format('Y-m-d');
            $lastTick     = (int) Cache::get($tickKey, 0);

            while (true) {
                if (connection_aborted() || now()->diffInMinutes($startAt) > 30) {
                    echo "event: close\n";
                    echo "data: " . json_encode(['message' => 'Connection ended']) . "\n\n";
                    @ob_flush(); @flush();
                    break;
                }

                try {
                    // 1) Cek signature eksternal setiap 5 detik
                    if (now()->diffInSeconds($lastSigCheck) >= 5) {
                        $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();
                        $currentSig = $this->externalSignature($selectedDate, $allBackNos, $this->excludedCustomersDefault);

                        if ($currentSig && $currentSig !== $lastSig) {
                            echo "event: refetching\n";
                            echo "data: " . json_encode([
                                'reason' => 'external_changed',
                                'at'     => now()->toIso8601String(),
                                'date'   => $selectedDate->format('Y-m-d'),
                            ]) . "\n\n";
                            @ob_flush(); @flush();

                            $ok = $this->refetchFromExternal($selectedDate);

                            $lastSig = $currentSig;
                            Cache::put('pulling:sig:' . $selectedDate->format('Ymd'), $currentSig, now()->addMinutes(30));

                            echo "event: refetched\n";
                            echo "data: " . json_encode([
                                'status' => $ok ? 'success' : 'nochange',
                                'at'     => now()->toIso8601String(),
                                'date'   => $selectedDate->format('Y-m-d'),
                            ]) . "\n\n";
                            @ob_flush(); @flush();

                            // Mundurkan lastCheck supaya perubahan fresh ikut terambil
                            $lastCheck = now()->subSecond();
                        }
                        $lastSigCheck = now();
                    }

                    // 2) (Opsional) Watch dog tick dari updateQty (jika kamu aktifkan increment di controller updateQty)
                    $currTick = (int) Cache::get($tickKey, 0);
                    if ($currTick !== $lastTick) {
                        $lastTick = $currTick;
                        echo "event: directPullingUpdate\n";
                        echo "data: " . json_encode([
                            'reason'    => 'tick',
                            'timestamp' => now()->toIso8601String(),
                            'date'      => $selectedDate->format('Y-m-d'),
                        ]) . "\n\n";
                        @ob_flush(); @flush();

                        // Mundurkan lastCheck untuk menangkap update di detik yang sama
                        $lastCheck = now()->subSecond();
                    }

                    // 3) Pantau perubahan langsung di DB sejak lastCheck
                    $updates = ProductionPlan::whereDate('plan_date', $selectedDate->format('Y-m-d'))
                        ->where(function($q) use ($lastCheck) {
                            // gunakan >= + buffer agar tidak miss pada presisi detik
                            $q->where('updated_at', '>=', $lastCheck)
                              ->orWhere('created_at', '>=', $lastCheck);
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id'                 => $item->id,
                                'order_qty'          => (int) $item->order_qty,
                                'direct_pulling_qty' => (int) $item->direct_pulling_qty,
                                'stock_chute_qty'    => (int) $item->stock_chute_qty,
                                'back_no'            => $item->back_no,
                                'cycle'              => $item->cycle,
                                'line'               => $item->line,
                                'balance'            => $item->balance_time,
                                // penting: sesuaikan dengan field yang diupdate di updateQty()
                                'start'              => $item->actual_working_start,
                                'end'                => $item->working_end,
                                'updated_at'         => optional($item->updated_at)->toIso8601String(),
                            ];
                        });

                    if ($updates->isNotEmpty()) {
                        echo "event: directPullingUpdate\n";
                        echo "data: " . json_encode([
                            'updates'   => $updates,
                            'timestamp' => now()->toIso8601String(),
                            'clientId'  => $clientId,
                            'date'      => $selectedDate->format('Y-m-d'),
                        ]) . "\n\n";
                        @ob_flush(); @flush();

                        // Buffer 1 detik supaya perubahan pada detik yang sama tidak terlewat
                        $lastCheck = now()->subSecond();
                    }

                    // 4) Heartbeat
                    if (now()->diffInSeconds($lastPing) >= 10) {
                        echo "event: ping\n";
                        echo "data: " . json_encode(['at' => now()->toIso8601String()]) . "\n\n";
                        @ob_flush(); @flush();
                        $lastPing = now();
                    }

                    // jeda kecil
                    usleep(400000); // 0.4s
                } catch (\Throwable $e) {
                    Log::error('SSE loop error: '.$e->getMessage());

                    echo "event: error\n";
                    echo "data: " . json_encode([
                        'message' => 'Temporary connection issue',
                        'at'      => now()->toIso8601String(),
                    ]) . "\n\n";
                    @ob_flush(); @flush();

                    sleep(2);
                }
            }
        }, 200, $headers);
    }

    /**
     * Full refresh dari sumber eksternal → simpan ke ProductionPlan.
     * Return true kalau ada data yang diproses.
     */
    protected function refetchFromExternal(Carbon $selectedDate): bool
    {
        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10);
        $end   = $start->copy()->addDay();

        DB::beginTransaction();
        try {
            $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();

            $raw = $this->fetchWithLaravelDB($today, $start, $allBackNos, $this->prodTimeByBackNo, $selectedDate);
            if ($raw->isEmpty()) {
                DB::rollBack();
                return false;
            }

            $processed = $this->processRawData($raw, $start, $end);
            $this->updateProductionData($processed, $this->backNosByLine, $today);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('refetchFromExternal failed: ' . $e->getMessage());
            return false;
        }
    }
}
