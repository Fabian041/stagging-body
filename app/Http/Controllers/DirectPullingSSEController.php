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

    private int $errorCount = 0;
    private ?string $clientId = null;

    // mapping shared (samakan dengan BE)
    private array $backNosByLine = [
        'AS003' => ['CI11', 'CI12', 'CI13', 'CI14', 'CI17', 'CI18', 'D403', 'D111'],
        'AS004' => ['CI15', 'CI16', 'CI19', 'D500'],
    ];

    private array $prodTimeByBackNo = [
        'CI11' => '00:34','CI12' => '00:34','CI13' => '00:40','CI14' => '00:34',
        'CI15' => '00:39','CI16' => '00:40','CI17' => '00:40','CI18' => '00:40','CI19' => '00:37',
        'D403' => '00:40','D111' => '00:34','D500' => '00:37'
    ];

    private array $excludedCustomers = [
        'TMMIN ASSY PLANT',
        'ADM SERVICE PART DIVISION',
        'TMMIN SERVICE PARTS DIVISION',
        'TAM SPARE PART DIVISION (DAIHATSU)',
        'PT MITSUBISHI MOTORS KRAMAYUDHA SALES ID'
    ];

    public function streamDirectPullingUpdates(Request $request): StreamedResponse
    {
        $this->clientId = $request->ip() . '-' . substr(md5(microtime()), 0, 6);

        $selectedDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        return new StreamedResponse(function () use ($selectedDate) {
            // SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $lastCheck = now()->subSeconds(3);
            $connectionStart = now();
            $lastHeartbeat = now();

            // signature init
            $sigKey = 'pulling:sig:' . $selectedDate->format('Ymd');
            $lastSig = Cache::get($sigKey, null);
            $lastSigCheck = now()->subSeconds(5);

            $this->sendEvent('connected', [
                'message'   => 'Connected to production plan updates',
                'clientId'  => $this->clientId,
                'date'      => $selectedDate->format('Y-m-d'),
                'timestamp' => now()->toISOString()
            ]);

            $emptyPolls = 0;
            $loopCount = 0;

            while (true) {
                try {
                    // putuskan koneksi tiap 30 menit / client abort
                    if ($loopCount++ % 10 === 0) {
                        if (now()->diffInMinutes($connectionStart) > 30 || connection_aborted()) {
                            if (connection_aborted()) {
                                Log::channel('sse')->debug("Client {$this->clientId} disconnected");
                            }
                            $this->sendEvent('close', ['message' => 'Connection ended']);
                            break;
                        }
                    }

                    // ==== 1) CEK SIGNATURE EXTERNAL (tiap ~5s) ====
                    if (now()->diffInSeconds($lastSigCheck) >= 5) {
                        $currentSig = $this->externalSignature($selectedDate);
                        if ($currentSig && $currentSig !== $lastSig) {
                            $this->sendEvent('refetching', [
                                'reason' => 'external_changed',
                                'at' => now()->toISOString(),
                                'date' => $selectedDate->format('Y-m-d')
                            ]);

                            // pakai cache lock supaya tidak balapan multi-client
                            $lockKey = 'pulling:refresh:' . $selectedDate->format('Ymd');
                            $lock = Cache::lock($lockKey, 60); // 60s

                            if ($lock->get()) {
                                try {
                                    $ok = $this->refetchFromExternal($selectedDate);
                                    if ($ok) {
                                        // update signature yang tersimpan
                                        Cache::put($sigKey, $currentSig, now()->addHours(6));
                                        $lastSig = $currentSig;
                                        $this->sendEvent('refetched', [
                                            'status' => 'success',
                                            'at' => now()->toISOString(),
                                            'date' => $selectedDate->format('Y-m-d')
                                        ]);
                                        // catatan: update data akan terdeteksi blok "updates" di bawah
                                    } else {
                                        $this->sendEvent('refetched', [
                                            'status' => 'nochange_or_failed',
                                            'at' => now()->toISOString()
                                        ]);
                                    }
                                } finally {
                                    optional($lock)->release();
                                }
                            }
                        }
                        $lastSigCheck = now();
                    }

                    // ==== 2) PANTAU UPDATE DI PRODUCTIONPLAN ====
                    $updates = \App\Models\ProductionPlan::where('plan_date', $selectedDate->format('Y-m-d'))
                        ->where(function($query) use ($lastCheck) {
                            $query->where('updated_at', '>', $lastCheck)
                                    ->orWhere('created_at', '>', $lastCheck);
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id'                  => $item->id,
                                'order_qty'           => $item->order_qty,
                                'direct_pulling_qty'  => $item->direct_pulling_qty,
                                'stock_chute_qty'     => $item->stock_chute_qty,
                                'back_no'             => $item->back_no,
                                'cycle'               => $item->cycle,
                                'line'                => $item->line,
                                'balance'             => $item->balance_time,
                                'start'               => $item->working_start,
                                'actual_start'        => $item->actual_working_start,
                                'end'                 => $item->working_end,
                                'updated_at'          => $item->updated_at->toISOString()
                            ];
                        });

                    if ($updates->isNotEmpty()) {
                        $payload = $updates->count() > 15
                            ? ['batches' => $updates->chunk(5)]
                            : ['updates' => $updates];

                        $this->sendEvent('directPullingUpdate', $payload + [
                            'timestamp' => now()->toISOString(),
                            'clientId'  => $this->clientId,
                            'date'      => $selectedDate->format('Y-m-d')
                        ]);

                        $lastCheck = now();
                        $emptyPolls = 0;
                    } else {
                        $emptyPolls++;
                    }

                    // heartbeat
                    if (now()->diffInSeconds($lastHeartbeat) >= 15) {
                        $this->sendHeartbeat();
                        $lastHeartbeat = now();
                    }

                    // dynamic sleep
                    $sleepTime = $updates->isEmpty()
                        ? min(5000000, 100000 * pow(2, min($emptyPolls, 5))) // 0.1s → max 5s
                        : 500000; // 0.5s
                    usleep($sleepTime);

                    if ($loopCount % 100 === 0) {
                        gc_collect_cycles();
                    }
                } catch (\Throwable $e) {
                    $this->handleStreamError($e);
                    $sleepTime = min(30, pow(2, $this->errorCount++));
                    sleep($sleepTime);
                    if ($this->errorCount > 5) break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache'
        ]);
    }

    /**
     * Hitung signature (hash) data eksternal dalam window 09:40–09:39.
     * Signature = sha256 dari string gabungan "dn|back_no|sum_order".
     */
    protected function externalSignature(Carbon $selectedDate): ?string
    {
        try {
            $deliveryDate = $selectedDate->format('Ymd');
            $nextDay = $selectedDate->copy()->addDay()->format('Ymd');
            $threshold = '104000'; // 09:40:00

            $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();

            // query ringan: group by dn_number + back_no efektif
            $rows = DB::connection('mssql_external')
                ->table('TT_GIG_SYKMEISAI')
                ->selectRaw("
                    CHR_COD_TKS_NOUBAN as dn_number,
                    CASE WHEN CHR_COD_UKEIRE = '6I' 
                        THEN RTRIM(CHR_COD_SEBANGOU_TOK) 
                        ELSE RTRIM(CHR_COD_SEBANGOU) END as back_no,
                    SUM(INT_SUR_JYUCYUU) as sum_order
                ")
                ->whereNotNull('CHR_TIM_SYUKKA')
                ->where(function ($query) use ($deliveryDate, $nextDay, $threshold) {
                    $query->where(function ($q) use ($deliveryDate, $threshold) {
                            $q->where('CHR_NGP_NOUNYU', $deliveryDate)
                                ->where('CHR_TIM_SYUKKA', '>=', $threshold);
                        })
                        ->orWhere(function ($q) use ($nextDay, $threshold) {
                            $q->where('CHR_NGP_NOUNYU', $nextDay)
                                ->where('CHR_TIM_SYUKKA', '<', $threshold);
                        });
                })
                ->whereNotIn('CHR_MEI_NOUNYU', $this->excludedCustomers)
                ->where(function ($q) use ($allBackNos) {
                    $q->where(function ($qq) use ($allBackNos) {
                            $qq->where('CHR_COD_UKEIRE', '<>', '6I')
                                ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU)"), $allBackNos);
                        })
                        ->orWhere(function ($qq) use ($allBackNos) {
                            $qq->where('CHR_COD_UKEIRE', '6I')
                                ->whereIn(DB::raw("RTRIM(CHR_COD_SEBANGOU_TOK)"), $allBackNos);
                        });
                })
                ->groupBy('dn_number', 'back_no')
                ->orderBy('dn_number')
                ->orderBy('back_no')
                ->get();

            if ($rows->isEmpty()) return 'empty:' . $deliveryDate;

            $payload = $rows->map(fn($r) => "{$r->dn_number}|{$r->back_no}|{$r->sum_order}")
                            ->implode(';');

            return hash('sha256', $payload);
        } catch (\Throwable $e) {
            Log::warning('externalSignature error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Jalankan full refresh dari sumber eksternal → simpan ke ProductionPlan.
     * Return true kalau sukses ada data yang diproses.
     */
    protected function refetchFromExternal(Carbon $selectedDate): bool
    {
        $today = $selectedDate->copy();
        $start = $today->copy()->addHours(10); // konsisten dengan BE-mu
        $end   = $start->copy()->addDay();

        DB::beginTransaction();
        try {
            $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();

            $raw = $this->fetchWithLaravelDB($today, $start, $allBackNos, $this->prodTimeByBackNo, $selectedDate);
            if ($raw->isEmpty()) {
                DB::rollBack(); // tidak ada yang perlu disimpan
                return false;
            }

            $processed = $this->processRawData($raw, $start, $end);
            $this->updateProductionData($processed, $this->backNosByLine, $today);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('refetchFromExternal failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function handleStreamError(\Throwable $e): void
    {
        Log::channel('sse')->error("SSE Error [{$this->clientId}]: " . $e->getMessage());
        $this->sendEvent('error', [
            'message' => 'Temporary connection issue',
            'clientId' => $this->clientId,
            'timestamp' => now()->toISOString()
        ]);
    }

    protected function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        @ob_flush(); @flush();
    }

    protected function sendHeartbeat(): void
    {
        echo ":heartbeat\n\n";
        @ob_flush(); @flush();
    }
}