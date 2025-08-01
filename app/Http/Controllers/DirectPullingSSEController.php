<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // Get the date parameter from the request or default to today
        $selectedDate = $request->has('date') 
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        return new StreamedResponse(function () use ($selectedDate) {
            // Setup SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $lastCheck = now()->subSeconds(3);
            $connectionStart = now();
            $lastHeartbeat = now();

            $this->sendEvent('connected', [
                'message' => 'Connected to production plan updates',
                'clientId' => $this->clientId,
                'date' => $selectedDate->format('Y-m-d'),
                'timestamp' => now()->toISOString()
            ]);

            $emptyPolls = 0;
            $loopCount = 0;
            
            while (true) {
                try {
                    // Connection check
                    if ($loopCount++ % 10 === 0) {
                        if (now()->diffInMinutes($connectionStart) > 30 || connection_aborted()) {
                            if (connection_aborted()) {
                                Log::channel('sse')->debug("Client {$this->clientId} disconnected");
                            }
                            $this->sendEvent('close', ['message' => 'Connection ended']);
                            break;
                        }
                    }
            
                    // Get updates for the selected date
                    $updates = ProductionPlan::where('plan_date', $selectedDate->format('Y-m-d'))
                        ->where(function($query) use ($lastCheck) {
                            $query->where('updated_at', '>', $lastCheck)
                                ->orWhere('created_at', '>', $lastCheck);
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
            
                    if ($updates->isNotEmpty()) {
                        $payload = $updates->count() > 15 
                            ? ['batches' => $updates->chunk(5)]
                            : ['updates' => $updates];
            
                        $this->sendEvent('directPullingUpdate', $payload + [
                            'timestamp' => now()->toISOString(),
                            'clientId' => $this->clientId,
                            'date' => $selectedDate->format('Y-m-d')
                        ]);
            
                        $lastCheck = now();
                        $emptyPolls = 0;
                    } else {
                        $emptyPolls++;
                    }
            
                    // Heartbeat
                    if (now()->diffInSeconds($lastHeartbeat) >= 15) {
                        $this->sendHeartbeat();
                        $lastHeartbeat = now();
                    }
            
                    // Dynamic sleep
                    $sleepTime = $updates->isEmpty() 
                        ? min(5000000, 100000 * pow(2, min($emptyPolls, 5)))
                        : 500000;
                        
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
        ob_flush();
        flush();
    }

    protected function sendHeartbeat(): void
    {
        echo ":heartbeat\n\n";
        ob_flush();
        flush();
    }
}