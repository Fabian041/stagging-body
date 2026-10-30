<?php

namespace App\Jobs;

use App\Services\All\AllManualCropProcessor;
use App\Services\KanbanPdfOptimizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job TERISOLASI untuk tab ALL mode manual crop.
 *
 * Tidak mengubah ProcessKanbanUpload (TMMIN/MMKI/ALL-auto tetap pakai itu).
 * Menulis marker .done yang sama agar endpoint status lama bisa dipakai polling.
 */
class ProcessAllManualCrop implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public array $files;

    public string $jobId;

    public array $cuts;

    public bool $skipBlank;

    public array $vCuts;

    public function __construct(array $files, string $jobId, array $cuts = [], bool $skipBlank = true, array $vCuts = [])
    {
        $this->files = $files;
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

        $cleanupInputs = function () use ($dir) {
            foreach ($this->files as $file) {
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

        logger('QUEUE START ALL-MANUAL '.$this->jobId);

        $filename = $processor->process($this->files, $this->jobId, $this->cuts, $this->skipBlank, $this->vCuts);

        // Optimasi ukuran file hasil (opsional; fallback ke asli).
        if (is_string($filename) && $filename !== '') {
            $beforePath = $dir . DIRECTORY_SEPARATOR . basename((string) $filename);
            $beforeSize = is_file($beforePath) ? (int) @filesize($beforePath) : null;
            $optimized = app(KanbanPdfOptimizer::class)->optimizeFiles([(string) $filename], $dir);
            if (! empty($optimized[0]) && is_string($optimized[0])) {
                $filename = $optimized[0];
            }
            $afterPath = $dir . DIRECTORY_SEPARATOR . basename((string) $filename);
            $afterSize = is_file($afterPath) ? (int) @filesize($afterPath) : null;
            try {
                logger()->info('KanbanPdfOptimizer size', [
                    'job' => $this->jobId,
                    'file' => (string) $filename,
                    'before_bytes' => $beforeSize,
                    'after_bytes' => $afterSize,
                ]);
            } catch (\Throwable $ignored) {
            }
        }

        if (file_exists($cancelPath)) {
            $out = $dir.'/'.basename((string) $filename);
            if (is_file($out)) {
                @unlink($out);
            }
            $cleanupInputs();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        // Arsip dulu, marker DONE belakangan: DONE terlihat = info arsip pasti sudah ada.
        // Arsip HASIL crop ke kanban-source dengan nama asli (fetch per PDS/manifest).
        $archived = \App\Support\KanbanSourceArchive::archiveJobOutputs(
            $this->jobId,
            \App\Support\KanbanSourceArchive::parseDoneMarker((string) $filename)
        );

        // Hemat space: hapus hasil temp setelah arsip berhasil.
        if (! empty($archived) && is_string($filename) && $filename !== '') {
            @unlink($dir . DIRECTORY_SEPARATOR . basename((string) $filename));
        }

        file_put_contents($dir.'/'.$this->jobId.'.done', $filename);

        foreach ($this->files as $file) {
            if (is_string($file) && file_exists($file)) {
                @unlink($file);
            }
        }
        @unlink($dir.'/'.$this->jobId.'.meta.json');

        logger('QUEUE FINISH ALL-MANUAL '.$filename);
    }
}
