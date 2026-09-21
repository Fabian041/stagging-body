<?php

namespace App\Jobs;

use App\Services\All\AllManualCropProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job TERISOLASI untuk tab MANUAL file raksasa (upload per bagian/chunk).
 *
 * Chunk diolah berurutan jadi satu PDF hasil (nomor halaman global),
 * marker .done / .cancel / .info.json sama agar endpoint lama bisa dipakai.
 */
class ProcessAllManualChunked implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /** @var array<int, array<int, string>> [fileIndex => [chunkAbsPath, ...]] */
    public array $chunkMap;

    public string $jobId;

    public array $cuts;

    public bool $skipBlank;

    public array $vCuts;

    public function __construct(array $chunkMap, string $jobId, array $cuts = [], bool $skipBlank = true, array $vCuts = [])
    {
        $this->chunkMap = $chunkMap;
        $this->jobId = $jobId;
        $this->cuts = $cuts;
        $this->skipBlank = $skipBlank;
        $this->vCuts = $vCuts;
    }

    public function handle(AllManualCropProcessor $processor)
    {
        $dir = storage_path('app/temp-kanban');
        $cancelPath = $dir.'/'.$this->jobId.'.cancel';
        $cancelledPath = $dir.'/'.$this->jobId.'.cancelled';

        $flat = [];
        foreach ((array) $this->chunkMap as $chunks) {
            foreach ((array) $chunks as $c) {
                $flat[] = $c;
            }
        }

        $cleanupInputs = function () use ($dir, $flat) {
            foreach ($flat as $file) {
                if (is_string($file) && $file !== '' && file_exists($file)) {
                    @unlink($file);
                }
            }
            @unlink($dir.'/'.$this->jobId.'.meta.json');
        };

        if (file_exists($cancelPath)) {
            $cleanupInputs();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        logger('QUEUE START ALL-MANUAL-CHUNKED '.$this->jobId);

        $filename = $processor->processChunked($this->chunkMap, $this->jobId, $this->cuts, $this->skipBlank, [], $this->vCuts);

        if (file_exists($cancelPath)) {
            $out = $dir.'/'.basename((string) $filename);
            if (is_file($out)) {
                @unlink($out);
            }
            @unlink($dir.'/'.$this->jobId.'.info.json');
            $cleanupInputs();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        file_put_contents($dir.'/'.$this->jobId.'.done', $filename);

        $cleanupInputs();

        logger('QUEUE FINISH ALL-MANUAL-CHUNKED '.$filename);
    }
}
