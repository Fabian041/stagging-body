<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ProductionPlan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectPullingSSEController extends Controller
{
    private int $errorCount = 0;
    private ?string $clientId = null;

    public function streamDirectPullingUpdates(Request $request): StreamedResponse
    {
        $this->clientId = $request->ip() . '-' . substr(md5(microtime()), 0, 6);

        return new StreamedResponse(function () {
            // Setup SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $today = now()->startOfDay();
            $lastCheck = now()->subSeconds(3); // Start with 3-second buffer
            $connectionStart = now();
            $lastHeartbeat = now();

            $this->sendEvent('connected', [
                'message' => 'Connected to production plan updates',
                'clientId' => $this->clientId,
                'timestamp' => now()->toISOString()
            ]);

            $emptyPolls = 0;
            $loopCount = 0;
            
            while (true) {
                try {
                    // Connection check every 10 iterations
                    if ($loopCount++ % 10 === 0) {
                        if (now()->diffInMinutes($connectionStart) > 30 || connection_aborted()) {
                            if (connection_aborted()) {
                                Log::channel('sse')->debug("Client {$this->clientId} disconnected");
                            }
                            $this->sendEvent('close', ['message' => 'Connection ended']);
                            break;
                        }
                    }
            
                    // Get updates with buffer
                    $updates = $this->getUpdatedRecords($today, $lastCheck->copy()->subSeconds(2));
            
                    // Handle updates
                    if ($updates->isNotEmpty()) {
                        $payload = $updates->count() > 15 
                            ? ['batches' => $updates->chunk(5)]
                            : ['updates' => $updates];
            
                        $this->sendEvent('directPullingUpdate', $payload + [
                            'timestamp' => now()->toISOString(),
                            'clientId' => $this->clientId
                        ]);
            
                        $lastCheck = now();
                        $emptyPolls = 0;
                        Log::channel('sse')->debug("Sent updates to {$this->clientId}", [
                            'count' => $updates->count(),
                            'batched' => $updates->count() > 15
                        ]);
                    } else {
                        $emptyPolls++;
                    }
            
                    // Heartbeat
                    if (now()->diffInSeconds($lastHeartbeat) >= 15) {
                        $this->sendHeartbeat();
                        $lastHeartbeat = now();
                    }
            
                    // Dynamic sleep with exponential backoff
                    $sleepTime = $updates->isEmpty() 
                        ? min(5000000, 100000 * pow(2, min($emptyPolls, 5))) // 0.1s to 5s
                        : 500000; // 0.5s when updates found
                        
                    usleep($sleepTime);
            
                    // Memory management
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

    protected function getUpdatedRecords($today, $lastCheck)
    {
        return ProductionPlan::where('plan_date', $today->format('Y-m-d'))
            ->where(function($query) use ($lastCheck) {
                $query->where('updated_at', '>', $lastCheck)
                    ->orWhere('created_at', '>', $lastCheck)
                    ->orWhere(function($q) {
                        $q->where('direct_pulling_qty', '>', 0)
                        ->orWhere('stock_chute_qty', '>', 0);
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'direct_pulling_qty' => $item->direct_pulling_qty,
                    'stock_chute_qty' => $item->stock_chute_qty,
                    'back_no' => $item->back_no,
                    'cycle' => $item->cycle,
                    'line' => $item->line,
                    'updated_at' => $item->updated_at->toISOString()
                ];
            });
    }

    protected function handleStreamError(\Exception $e): void
    {
        Log::channel('sse')->error("SSE Error [{$this->clientId}]: " . $e->getMessage(), [
            'errorCount' => $this->errorCount
        ]);

        $this->sendEvent('error', [
            'message' => 'Temporary connection issue',
            'clientId' => $this->clientId,
            'timestamp' => now()->toISOString()
        ]);
    }

    protected function sendEvent($event, $data)
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    protected function sendHeartbeat()
    {
        echo ":heartbeat\n\n";
        ob_flush();
        flush();
    }
}