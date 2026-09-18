<?php

namespace App\Services\All;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Processor MANUAL khusus tab ALL.
 *
 * Menerima garis potong horizontal per halaman (fraksi 0-1 dari atas)
 * yang dibuat user via preview draggable di browser.
 *
 * Aturan hasil (sesuai request):
 * - 1 page + 1 garis  => 2 page
 * - 1 page + 2 garis  => 3 page
 * - dan seterusnya (N garis => N+1 segmen)
 * - page tanpa garis  => disalin apa adanya (1 page)
 *
 * TERISOLASI: tidak dipakai TMMIN/MMKI, tidak mengubah splitter lama.
 */
class AllManualCropProcessor
{
    /**
     * @param  array  $files      absolute path file PDF (urutan = fileIndex)
     * @param  string $jobId
     * @param  array  $cutsByFile [fileIndex => [pageNum => [frac,...]]]
     */
    public function process(array $files, string $jobId, array $cutsByFile = []): string
    {
        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban ALL Manual Crop Result');

        logger()->info('AllManualCropProcessor start', [
            'job' => $jobId,
            'files' => count($files),
        ]);

        $totalPages = 0;

        foreach (array_values($files) as $fileIndex => $file) {
            if (! is_string($file) || ! is_file($file)) {
                continue;
            }

            // Probe untuk ukuran tiap halaman.
            $probe = new Fpdi();
            try {
                $pageCount = $probe->setSourceFile($file);
            } catch (\Throwable $e) {
                logger()->warning('AllManual probe failed', [
                    'file' => basename((string) $file),
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            // FPDI output juga perlu setSourceFile per file untuk import.
            try {
                $output->setSourceFile($file);
            } catch (\Throwable $e) {
                continue;
            }

            for ($page = 1; $page <= $pageCount; $page++) {
                try {
                    $template = $output->importPage($page);
                    $size = $output->getTemplateSize($template);
                } catch (\Throwable $e) {
                    continue;
                }

                $pageW = (float) ($size['width'] ?? 0);
                $pageH = (float) ($size['height'] ?? 0);

                if ($pageW <= 0 || $pageH <= 0) {
                    continue;
                }

                $cuts = $this->normalizeCuts($cutsByFile[$fileIndex][$page] ?? ($cutsByFile[(string) $fileIndex][(string) $page] ?? ($cutsByFile[$fileIndex][(string) $page] ?? [])));

                if (empty($cuts)) {
                    // Tanpa garis: salin apa adanya.
                    $output->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
                    $output->useTemplate($template, 0, 0, $pageW, $pageH);
                    $totalPages++;
                    continue;
                }

                // Bangun segmen dari garis: [0, c1, c2, ..., 1].
                $bounds = array_merge([0.0], $cuts, [1.0]);

                foreach ($bounds as $i => $top) {
                    if (! isset($bounds[$i + 1])) {
                        break;
                    }
                    $bottom = $bounds[$i + 1];
                    $segH = ($bottom - $top) * $pageH;

                    // Abaikan segmen terlalu tipis (< 3mm) agar tidak jadi page sampah.
                    if ($segH < 3.0) {
                        continue;
                    }

                    $y = $top * $pageH;
                    $offX = 0.0;
                    $offY = -$y;

                    $output->AddPage($pageW > $segH ? 'L' : 'P', [$pageW, $segH]);
                    $output->useTemplate($template, $offX, $offY, $pageW, $pageH);
                    $totalPages++;
                }
            }
        }

        $filename = 'kanban_all_manual_'.$jobId.'.pdf';
        $dir = storage_path('app/temp-kanban');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $output->Output($dir.DIRECTORY_SEPARATOR.$filename, 'F');

        logger()->info('AllManualCropProcessor done', [
            'job' => $jobId,
            'file' => $filename,
            'pages' => $totalPages,
        ]);

        return $filename;
    }

    /**
     * Normalisasi fraksi garis: float 0-1, urut, dedup, buang tepi & terlalu rapat.
     *
     * @param  mixed $raw
     * @return float[]
     */
    private function normalizeCuts($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $cuts = [];
        foreach ($raw as $v) {
            $f = (float) $v;
            if ($f <= 0.02 || $f >= 0.98) {
                continue;
            }
            $cuts[] = $f;
        }

        sort($cuts);

        // Dedup garis yang terlalu rapat (< 3% tinggi page).
        $filtered = [];
        foreach ($cuts as $c) {
            $last = end($filtered);
            if ($last === false || abs($c - $last) >= 0.03) {
                $filtered[] = $c;
            }
        }

        return array_values($filtered);
    }
}
