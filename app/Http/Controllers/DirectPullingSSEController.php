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

            $lastCheck = now()->subSeconds(2);
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
                        $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();
                        $currentSig = $this->externalSignature($selectedDate, $allBackNos, $this->excludedCustomers);
                        if ($currentSig && $currentSig !== $lastSig) {
                            $this->sendEvent('refetching', [
                                'reason' => 'external_changed',
                                'at'     => now()->toISOString(),
                                'date'   => $selectedDate->format('Y-m-d'),
                            ]);

                            $lockKey = 'pulling:refresh:' . $selectedDate->format('Ymd');
                            $lock = null;
                            try {
                                // Fallback kalau store cache tidak support lock (lihat poin c)
                                $lock = Cache::lock($lockKey, 60);
                                $got = method_exists($lock, 'get') ? $lock->get() : true;

                                if ($got) {
                                    try {
                                        $ok = $this->refetchFromExternal($selectedDate);
                                    } finally {
                                        try { $lock?->release(); } catch (\Throwable $e) {}
                                    }

                                    // **Selalu** tulis signature, walau ok=false (terutama kalau prefix 'empty:')
                                    Cache::put($sigKey, $currentSig, now()->addMinutes(30));
                                    $lastSig = $currentSig;

                                    $this->sendEvent('refetched', [
                                        'status' => $ok ? 'success' : 'nochange',
                                        'at'     => now()->toISOString(),
                                        'date'   => $selectedDate->format('Y-m-d')
                                    ]);
                                }
                            } catch (\Throwable $e) {
                                // jalan tanpa lock kalau store cache tidak support lock
                                $ok = $this->refetchFromExternal($selectedDate);
                                Cache::put($sigKey, $currentSig, now()->addMinutes(30));
                                $lastSig = $currentSig;

                                $this->sendEvent('refetched', [
                                    'status' => $ok ? 'success' : 'nochange',
                                    'at'     => now()->toISOString(),
                                    'date'   => $selectedDate->format('Y-m-d')
                                ]);
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