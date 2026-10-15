<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Job gabung (ujung rantai) untuk tab MANUAL file raksasa.
 *
 * Menggabungkan PDF parsial berurutan menjadi PDF hasil per VOLUME
 * (±100 halaman per file) agar tiap perakitan muat di RAM kecil (128MB).
 * 1 volume  = nama file klasik tunggal (kompatibel lama).
 * N volume = N tombol download (`.done` berisi JSON volumes).
 * TERISOLASI: hanya dipakai chain tab MANUAL.
 */
class MergeAllManualParts implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /** Maksimal halaman output per file volume (tunable 1 baris). */
    public const VOLUME_PAGES = 100;

    public string $jobId;

    public int $partTotal;

    public function __construct(string $jobId, int $partTotal)
    {
        $this->jobId = $jobId;
        $this->partTotal = $partTotal;
    }

    protected function freshOutput(): Fpdi
    {
        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban ALL Manual Crop Result');

        return $output;
    }

    public function handle()
    {
        $dir = storage_path('app/temp-kanban');
        $cancelPath = $dir.'/'.$this->jobId.'.cancel';
        $cancelledPath = $dir.'/'.$this->jobId.'.cancelled';
        $createdVolumes = [];

        // Bersihkan penanda error percobaan sebelumnya (retry job yang sama).
        @unlink($dir.'/'.$this->jobId.'.merge-error.json');

        // Dibatalkan sebelum gabung: bersihkan semua parsial.
        if (file_exists($cancelPath)) {
            $this->cleanupPartials($dir);
            @unlink($dir.'/'.$this->jobId.'.meta.json');
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        logger('QUEUE START ALL-MANUAL-MERGE '.$this->jobId);

        try {
            $this->runMerge($dir, $cancelPath, $cancelledPath, $createdVolumes);
        } catch (\Throwable $e) {
            // Catat error ke file agar tampil di UI (status FAILED) walau log tidak terbaca.
            @file_put_contents(
                $dir.'/'.$this->jobId.'.merge-error.json',
                json_encode([
                    'error' => get_class($e).': '.substr($e->getMessage(), 0, 300),
                    'at' => date('Y-m-d H:i:s'),
                ])
            );
            throw $e;
        }
    }

    protected function runMerge(string $dir, string $cancelPath, string $cancelledPath, array &$createdVolumes): void
    {

        $totalPages = 0;
        $skippedBlank = 0;
        $output = $this->freshOutput();
        $volPages = 0;
        $volIndex = 0;
        $cancelled = false;

        // Tutup volume aktif ke file & bebaskan RAM sebelum volume berikut.
        // PENTING: timpa langsung, JANGAN unset() — unset memutus reference closure.
        $flushVolume = function () use ($dir, &$output, &$volIndex, &$volPages, &$createdVolumes) {
            if ($volPages <= 0) {
                return;
            }
            $name = 'kanban_all_manual_'.$this->jobId.'_vol'.$volIndex.'.pdf';
            $output->Output($dir.DIRECTORY_SEPARATOR.$name, 'F');
            $createdVolumes[] = $name;
            $volIndex++;
            $volPages = 0;
            $output = $this->freshOutput();
            if (function_exists('gc_collect_cycles')) {
                @gc_collect_cycles();
            }
        };

        for ($i = 0; $i < $this->partTotal; $i++) {
            if (file_exists($cancelPath)) {
                $cancelled = true;
                break;
            }

            $subId = $this->jobId.'_part'.$i;
            $partPdf = $dir.'/kanban_all_manual_'.$subId.'.pdf';

            if (! is_file($partPdf)) {
                throw new \Exception('Part hilang: bagian '.($i + 1).'/'.$this->partTotal.'. Ulangi dari awal (Reset).');
            }

            $infoPath = $dir.'/'.$subId.'.info.json';
            if (is_file($infoPath)) {
                $info = json_decode((string) @file_get_contents($infoPath), true);
                if (is_array($info)) {
                    $skippedBlank += (int) ($info['skipped_blank'] ?? 0);
                }
            }

            $n = $output->setSourceFile($partPdf);
            for ($p = 1; $p <= $n; $p++) {
                if ($volPages >= static::VOLUME_PAGES) {
                    $flushVolume();
                    // Objek baru = reader hilang: buka ulang file part yang sama.
                    $output->setSourceFile($partPdf);
                }
                $tpl = $output->importPage($p);
                $size = $output->getTemplateSize($tpl);
                $w = (float) ($size['width'] ?? 0);
                $h = (float) ($size['height'] ?? 0);
                if ($w <= 0 || $h <= 0) {
                    continue;
                }
                $output->AddPage($w > $h ? 'L' : 'P', [$w, $h]);
                $output->useTemplate($tpl, 0, 0, $w, $h);
                $volPages++;
                $totalPages++;
            }
        }

        // Dibatalkan saat gabung: buang volume + parsial.
        if ($cancelled || file_exists($cancelPath)) {
            foreach ($createdVolumes as $v) {
                @unlink($dir.'/'.$v);
            }
            $this->cleanupPartials($dir);
            @unlink($dir.'/'.$this->jobId.'.meta.json');
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }

        $flushVolume();

        // 1 volume = nama klasik tunggal (kompatibel endpoint & UI lama).
        if (count($createdVolumes) === 1) {
            $plain = 'kanban_all_manual_'.$this->jobId.'.pdf';
            @rename($dir.'/'.$createdVolumes[0], $dir.'/'.$plain);
            $finalFiles = [$plain];
        } else {
            $finalFiles = array_values($createdVolumes);
        }

        @file_put_contents(
            $dir.DIRECTORY_SEPARATOR.$this->jobId.'.info.json',
            json_encode([
                'pages' => $totalPages,
                'skipped_blank' => $skippedBlank,
                'volumes' => $finalFiles,
            ])
        );

        // Arsip dulu, marker DONE belakangan: DONE terlihat = info arsip pasti sudah ada.
        // Arsip HASIL merge ke kanban-source dengan nama asli (fetch per PDS/manifest).
        \App\Support\KanbanSourceArchive::archiveJobOutputs($this->jobId, $finalFiles);

        if (count($finalFiles) === 1) {
            file_put_contents($dir.'/'.$this->jobId.'.done', $finalFiles[0]);
        } else {
            file_put_contents($dir.'/'.$this->jobId.'.done', json_encode(['volumes' => $finalFiles]));
        }

        $this->cleanupPartials($dir);
        @unlink($dir.'/'.$this->jobId.'.meta.json');
        @unlink($dir.'/'.$this->jobId.'.progress.json');

        logger('QUEUE FINISH ALL-MANUAL-MERGE '.$this->jobId.' pages='.$totalPages.' volumes='.count($finalFiles));
    }

    protected function cleanupPartials(string $dir): void
    {
        for ($i = 0; $i < $this->partTotal; $i++) {
            $subId = $this->jobId.'_part'.$i;
            @unlink($dir.'/kanban_all_manual_'.$subId.'.pdf');
            @unlink($dir.'/'.$subId.'.done');
            @unlink($dir.'/'.$subId.'.info.json');
        }
        @unlink($dir.'/'.$this->jobId.'.progress.json');
    }
}
