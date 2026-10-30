<?php

namespace App\Jobs;

use App\Services\All\AllManualCropProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Satu bagian (batch ≤100 halaman sumber) dari job chunked MANUAL.
 *
 * TERISOLASI: hanya dipakai chain tab MANUAL. Menulis PDF parsial
 * `kanban_all_manual_{jobId}_part{i}.pdf` + info + progres untuk job utama.
 * RAM terbebas setiap part selesai (proses baru per job).
 */
class ProcessAllManualPart implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /** @var array{fileIndex:int,chunks:array<int,string>,cutStart:int} */
    public array $group;

    public string $jobId;

    public int $partIndex;

    public int $partTotal;

    public array $cuts;

    public bool $skipBlank;

    public array $vCuts;

    public function __construct(
        array $group,
        string $jobId,
        int $partIndex,
        int $partTotal,
        array $cuts = [],
        bool $skipBlank = true,
        array $vCuts = []
    ) {
        $this->group = $group;
        $this->jobId = $jobId;
        $this->partIndex = $partIndex;
        $this->partTotal = $partTotal;
        $this->cuts = $cuts;
        $this->skipBlank = $skipBlank;
        $this->vCuts = $vCuts;
    }

    public function handle(AllManualCropProcessor $processor)
    {
        $dir = storage_path('app/temp-kanban');
        $cancelPath = $dir.'/'.$this->jobId.'.cancel';
        $cancelledPath = $dir.'/'.$this->jobId.'.cancelled';
        $fi = (int) ($this->group['fileIndex'] ?? 0);
        $chunks = array_values((array) ($this->group['chunks'] ?? []));
        $cutStart = max(1, (int) ($this->group['cutStart'] ?? 1));
        $subId = $this->jobId.'_part'.$this->partIndex;

        $cleanupChunks = function () use ($chunks) {
            foreach ($chunks as $c) {
                if (is_string($c) && $c !== '' && file_exists($c)) {
                    @unlink($c);
                }
            }
        };

        // Dibatalkan sebelum mulai: bersihkan chunk sendiri, tandai, rantai lanjut no-op.
        if (file_exists($cancelPath)) {
            $cleanupChunks();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        logger('QUEUE START ALL-MANUAL-PART '.$subId);

        $processor->processChunked(
            [$fi => $chunks],
            $subId,
            $this->cuts,
            $this->skipBlank,
            [$fi => $cutStart - 1],
            $this->vCuts
        );

        // Dibatalkan di tengah jalan: buang parsial, tandai, rantai lanjut no-op.
        if (file_exists($cancelPath)) {
            @unlink($dir.'/kanban_all_manual_'.$subId.'.pdf');
            @unlink($dir.'/'.$subId.'.done');
            @unlink($dir.'/'.$subId.'.info.json');
            $cleanupChunks();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        // Progres untuk polling UI (chain jalan sekuensial, tulis-baca aman).
        try {
            $progPath = $dir.'/'.$this->jobId.'.progress.json';
            $prog = ['done' => 0, 'total' => $this->partTotal];
            if (is_file($progPath)) {
                $old = json_decode((string) @file_get_contents($progPath), true);
                if (is_array($old) && isset($old['done'])) {
                    $prog['done'] = (int) $old['done'];
                }
            }
            $prog['done'] = min($this->partTotal, $prog['done'] + 1);
            @file_put_contents($progPath, json_encode($prog));
        } catch (\Throwable $e) {
            // progres informatif saja, jangan gagalkan job
        }

        // Optimasi ukuran file parsial (opsional; fallback ke asli).
        $partName = 'kanban_all_manual_'.$subId.'.pdf';
        $beforePath = $dir . DIRECTORY_SEPARATOR . $partName;
        $beforeSize = is_file($beforePath) ? (int) @filesize($beforePath) : null;
        app(\App\Services\KanbanPdfOptimizer::class)->optimizeFiles([$partName], $dir);
        $afterSize = is_file($beforePath) ? (int) @filesize($beforePath) : null;
        try {
            logger()->info('KanbanPdfOptimizer size', [
                'job' => $this->jobId,
                'file' => $partName,
                'before_bytes' => $beforeSize,
                'after_bytes' => $afterSize,
            ]);
        } catch (\Throwable $ignored) {
        }

        $cleanupChunks();

        logger('QUEUE FINISH ALL-MANUAL-PART '.$subId);
    }
}
