<?php

namespace App\Services\All;

use App\Services\KanbanRegionDetector;
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
 * - segmen blank/putih saja => dibuang otomatis (tidak jadi page)
 *
 * TERISOLASI: tidak dipakai TMMIN/MMKI, tidak mengubah splitter lama.
 */
class AllManualCropProcessor
{
    public function __construct(
        protected KanbanRegionDetector $detector
    ) {
    }

    /**
     * @param  array  $files      absolute path file PDF (urutan = fileIndex)
     * @param  string $jobId
     * @param  array  $cutsByFile [fileIndex => [pageNum => [frac,...]]]
     * @param  bool   $filterBlank false = jalur cepat tanpa deteksi blank
     */
    public function process(array $files, string $jobId, array $cutsByFile = [], bool $filterBlank = true): string
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
        $skippedBlank = 0;
        $applyFilter = $filterBlank;

        // 2 pass: pass 1 buang blank, bila hasilnya NOL halaman ulangi tanpa filter
        // (pengaman agar tidak pernah menghasilkan PDF kosong).
        for ($pass = 0; $pass < 2; $pass++) {
            if ($pass === 1) {
                $totalPages = 0;
                $skippedBlank = 0;
                $applyFilter = false;
                logger()->warning('AllManual all blank, retry without filter', ['job' => $jobId]);
            }

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
                    // Tanpa garis: salin apa adanya, tapi buang bila blank.
                    if ($applyFilter && $this->isBlankSegment($file, $page, 0.0, 1.0)) {
                        $skippedBlank++;
                        continue;
                    }
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

                    // Buang segmen blank/putih saja.
                    if ($applyFilter && $this->isBlankSegment($file, $page, (float) $top, (float) $bottom)) {
                        $skippedBlank++;
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

            if ($totalPages > 0 || $skippedBlank === 0) {
                break;
            }
        }

        $filename = 'kanban_all_manual_'.$jobId.'.pdf';
        $dir = storage_path('app/temp-kanban');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $output->Output($dir.DIRECTORY_SEPARATOR.$filename, 'F');

        // Sidecar info agar UI bisa menampilkan "X page (Y blank dibuang)".
        // Format terpisah dari .done supaya endpoint status lama tetap kompatibel.
        @file_put_contents(
            $dir.DIRECTORY_SEPARATOR.$jobId.'.info.json',
            json_encode([
                'pages' => $totalPages,
                'skipped_blank' => $skippedBlank,
            ])
        );

        logger()->info('AllManualCropProcessor done', [
            'job' => $jobId,
            'file' => $filename,
            'pages' => $totalPages,
            'skipped_blank' => $skippedBlank,
        ]);

        return $filename;
    }

    /**
     * Varian chunked untuk file raksasa (upload per bagian karena post_max_size).
     *
     * @param  array  $chunkMap   [fileIndex => [chunkAbsPath, ...]] urut per file
     * @param  string $jobId
     * @param  array  $cutsByFile [fileIndex => [globalPageNum => [frac,...]]]
     * @param  bool   $filterBlank false = jalur cepat tanpa deteksi blank
     * @param  array  $pageOffsets [fileIndex => offset] nomor halaman global awal - 1 (untuk part job)
     */
    public function processChunked(array $chunkMap, string $jobId, array $cutsByFile = [], bool $filterBlank = true, array $pageOffsets = []): string
    {
        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban ALL Manual Crop Result');

        logger()->info('AllManualCropProcessor chunked start', [
            'job' => $jobId,
            'files' => count($chunkMap),
        ]);

        $totalPages = 0;
        $skippedBlank = 0;
        $applyFilter = $filterBlank;

        // 2 pass: pass 1 buang blank, bila hasilnya NOL halaman ulangi tanpa filter
        // (pengaman agar tidak pernah menghasilkan PDF kosong).
        for ($pass = 0; $pass < 2; $pass++) {
            if ($pass === 1) {
                $totalPages = 0;
                $skippedBlank = 0;
                $applyFilter = false;
                logger()->warning('AllManualChunked all blank, retry without filter', ['job' => $jobId]);
            }

        foreach ($chunkMap as $fileIndex => $chunks) {
            $globalPage = 0;

            foreach (array_values((array) $chunks) as $chunk) {
                if (! is_string($chunk) || ! is_file($chunk)) {
                    continue;
                }

                try {
                    $chunkPages = $output->setSourceFile($chunk);
                } catch (\Throwable $e) {
                    logger()->warning('AllManual chunk open failed', [
                        'file' => basename((string) $chunk),
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }

                for ($local = 1; $local <= $chunkPages; $local++) {
                    $globalPage++;

                    try {
                        $template = $output->importPage($local);
                        $size = $output->getTemplateSize($template);
                    } catch (\Throwable $e) {
                        continue;
                    }

                    $pageW = (float) ($size['width'] ?? 0);
                    $pageH = (float) ($size['height'] ?? 0);

                    if ($pageW <= 0 || $pageH <= 0) {
                        continue;
                    }

                    $off = $pageOffsets[$fileIndex] ?? ($pageOffsets[(string) $fileIndex] ?? 0);
                    $lookup = $globalPage + (int) $off;
                    $cuts = $this->normalizeCuts(
                        $cutsByFile[$fileIndex][$lookup]
                        ?? ($cutsByFile[(string) $fileIndex][(string) $lookup] ?? [])
                    );

                    if (empty($cuts)) {
                        if ($applyFilter && $this->isBlankSegment($chunk, $local, 0.0, 1.0)) {
                            $skippedBlank++;
                            continue;
                        }
                        $output->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
                        $output->useTemplate($template, 0, 0, $pageW, $pageH);
                        $totalPages++;
                        continue;
                    }

                    $bounds = array_merge([0.0], $cuts, [1.0]);

                    foreach ($bounds as $i => $top) {
                        if (! isset($bounds[$i + 1])) {
                            break;
                        }
                        $bottom = $bounds[$i + 1];
                        $segH = ($bottom - $top) * $pageH;

                        if ($segH < 3.0) {
                            continue;
                        }

                        if ($applyFilter && $this->isBlankSegment($chunk, $local, (float) $top, (float) $bottom)) {
                            $skippedBlank++;
                            continue;
                        }

                        $y = $top * $pageH;

                        $output->AddPage($pageW > $segH ? 'L' : 'P', [$pageW, $segH]);
                        $output->useTemplate($template, 0.0, -$y, $pageW, $pageH);
                        $totalPages++;
                    }
                }
            }
        }

            if ($totalPages > 0 || $skippedBlank === 0) {
                break;
            }
        }

        $filename = 'kanban_all_manual_'.$jobId.'.pdf';
        $dir = storage_path('app/temp-kanban');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $output->Output($dir.DIRECTORY_SEPARATOR.$filename, 'F');

        @file_put_contents(
            $dir.DIRECTORY_SEPARATOR.$jobId.'.info.json',
            json_encode([
                'pages' => $totalPages,
                'skipped_blank' => $skippedBlank,
            ])
        );

        logger()->info('AllManualCropProcessor chunked done', [
            'job' => $jobId,
            'file' => $filename,
            'pages' => $totalPages,
            'skipped_blank' => $skippedBlank,
        ]);

        return $filename;
    }

    /**
     * Segmen blank = tidak ada teks DAN nyaris tidak ada tinta/gambar.
     * Fail-safe: ragu-ragu => anggap BERISI (jangan buang).
     */
    protected function isBlankSegment(string $file, int $page, float $fracTop, float $fracBottom): bool
    {
        try {
            if (! $this->detector->isRegionEmpty($file, $page, $fracTop, $fracBottom)) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        try {
            return $this->detector->countInkInBand($file, $page, $fracTop, $fracBottom) <= 5;
        } catch (\Throwable $e) {
            return true;
        }
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
