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

    public function streamDirectPullingUpdates(Request $request): StreamedResponse
    {
        $clientId = $request->ip() . '-' . substr(md5(microtime()), 0, 6);

        $selectedDate = $request->has('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        // Lepaskan session lock (kalau ada) biar stream nggak keganjal
        try { if (app()->bound('session')) { session()->save(); } } catch (\Throwable $e) {}

        ignore_user_abort(true);
        @set_time_limit(0);
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', 'off');

        return response()->stream(function () use ($selectedDate, $clientId) {

            // ===== headers awal SSE + retry =====
            echo "retry: 5000\n\n";
            @ob_flush(); @flush();

            // kirim event "connected" (Blade mendengarkan ini)
            $connectedPayload = [
                'message'   => 'Connected to production plan updates',
                'clientId'  => $clientId,
                'date'      => $selectedDate->format('Y-m-d'),
                'timestamp' => now()->toIso8601String(),
            ];
            echo "event: connected\n";
            echo "data: " . json_encode($connectedPayload) . "\n\n";
            @ob_flush(); @flush();

            $startAt         = now();
            $lastCheck       = now()->subSeconds(2);
            $lastPing        = now()->subSeconds(10); // paksa ping segera
            $lastSig         = null;
            $lastSigCheck    = now()->subSeconds(5);

            while (true) {
                // putuskan kalau client udah nutup atau 30 menit lewat
                if (connection_aborted() || now()->diffInMinutes($startAt) > 30) {
                    echo "event: close\n";
                    echo "data: " . json_encode(['message' => 'Connection ended']) . "\n\n";
                    @ob_flush(); @flush();
                    break;
                }

                try {
                    // 1) cek signature eksternal tiap 5s → kalau beda, refetch + event "refetched"
                    if (now()->diffInSeconds($lastSigCheck) >= 5) {
                        $allBackNos  = collect($this->backNosByLine)->flatten()->unique()->values();
                        $currentSig  = $this->externalSignature($selectedDate, $allBackNos, $this->excludedCustomersDefault);

                        if ($currentSig && $currentSig !== $lastSig) {
                            echo "event: refetching\n";
                            echo "data: " . json_encode([
                                'reason' => 'external_changed',
                                'at'     => now()->toIso8601String(),
                                'date'   => $selectedDate->format('Y-m-d'),
                            ]) . "\n\n";
                            @ob_flush(); @flush();

                            // lakukan refresh data
                            $ok = $this->refetchFromExternal($selectedDate);

                            $lastSig = $currentSig;
                            Cache::put('pulling:sig:'.$selectedDate->format('Ymd'), $currentSig, now()->addMinutes(30));

                            echo "event: refetched\n";
                            echo "data: " . json_encode([
                                'status' => $ok ? 'success' : 'nochange',
                                'at'     => now()->toIso8601String(),
                                'date'   => $selectedDate->format('Y-m-d'),
                            ]) . "\n\n";
                            @ob_flush(); @flush();
                        }
                        $lastSigCheck = now();
                    }

                    // 2) pantau perubahan di ProductionPlan sejak lastCheck → event "directPullingUpdate"
                    $updates = \App\Models\ProductionPlan::where('plan_date', $selectedDate->format('Y-m-d'))
                        ->where(function($q) use ($lastCheck) {
                            $q->where('updated_at', '>', $lastCheck)
                            ->orWhere('created_at', '>', $lastCheck);
                        })
                        ->orderBy('updated_at', 'desc')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id'                 => $item->id,
                                'order_qty'          => $item->order_qty,
                                'direct_pulling_qty' => $item->direct_pulling_qty,
                                'stock_chute_qty'    => $item->stock_chute_qty,
                                'back_no'            => $item->back_no,
                                'cycle'              => $item->cycle,
                                'line'               => $item->line,
                                'balance'            => $item->balance_time,
                                'start'              => $item->working_start,
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

                        $lastCheck = now();
                    }

                    // 3) heartbeat yang *match Blade*: event name = "ping"
                    if (now()->diffInSeconds($lastPing) >= 10) {
                        echo "event: ping\n";
                        echo "data: " . json_encode(['at' => now()->toIso8601String()]) . "\n\n";
                        @ob_flush(); @flush();
                        $lastPing = now();
                    }

                    // jeda pendek
                    usleep(400000); // 0.4s
                } catch (\Throwable $e) {
                    Log::error('SSE loop error: '.$e->getMessage());
                    // beri tahu FE (opsional)
                    echo "event: error\n";
                    echo "data: " . json_encode([
                        'message' => 'Temporary connection issue',
                        'at'      => now()->toIso8601String(),
                    ]) . "\n\n";
                    @ob_flush(); @flush();
                    sleep(2);
                }
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=utf-8',
            'Cache-Control'     => 'no-cache, no-transform',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no', // cegah buffering di Nginx
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
        echo "event: ping\n";
        echo "data: " . json_encode(['at' => now()->toIso8601String()]) . "\n\n";
        @ob_flush(); @flush();
    }
}