<?php

namespace App\Services\All;

use App\Services\KanbanRegionDetector;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Processor MANUAL khusus tab ALL.
 *
 * Menerima garis potong horizontal (tidur, fraksi 0-1 dari atas) dan
 * vertikal (berdiri, fraksi 0-1 dari kiri) per halaman
 * yang dibuat user via preview draggable di browser.
 *
 * Aturan hasil (sesuai request):
 * - 1 page + 1 garis tidur  => 2 page (tumpuk vertikal)
 * - 1 page + 2 garis tidur  => 3 page
 * - dan seterusnya (N garis => N+1 segmen)
 * - garis berdiri membagi tiap baris jadi kolom (grid tidur × berdiri)
 * - page tanpa garis  => disalin apa adanya (1 page)
 * - segmen/sel blank/putih saja => dibuang otomatis (tidak jadi page)
 *
 */
class AllManualCropProcessor
{
    /**
     * Tinggi minimum segmen agar disimpan (mm, satuan dokumen FPDI).
     * Segmen lebih pendek dari ini = strip sisa/margin putih → buang.
     * Kanban asli umumnya >= 40mm (3–6 kanban per halaman A4),
     * jadi 30mm aman dari kanban valid.
     */
    private const MIN_SEGMENT_HEIGHT_MM = 30.0;

    /**
     * Lebar minimum sel agar disimpan (mm). Sel lebih sempit dari ini
     * = strip sisa/margin putih → buang (sama seperti aturan tinggi).
     */
    private const MIN_SEGMENT_WIDTH_MM = 30.0;

    public function __construct(
        protected KanbanRegionDetector $detector
    ) {
    }

    /**
     * @param  array  $files      absolute path file PDF (urutan = fileIndex)
     * @param  string $jobId
     * @param  array  $cutsByFile [fileIndex => [pageNum => [frac,...]]] garis tidur
     * @param  bool   $filterBlank false = jalur cepat tanpa deteksi blank
     * @param  array  $vCutsByFile [fileIndex => [pageNum => [frac,...]]] garis berdiri
     */
    public function process(array $files, string $jobId, array $cutsByFile = [], bool $filterBlank = true, array $vCutsByFile = []): string
    {
        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban ALL Manual Crop Result');

        try {
            $renderer = $this->detector->availableRenderer();
        } catch (\Throwable $e) {
            $renderer = null;
        }
        logger()->info('AllManualCropProcessor start', [
            'job' => $jobId,
            'files' => count($files),
            'blank_renderer' => $renderer ?? 'none-vector-only',
            'filter_blank' => $filterBlank,
            'min_seg_mm' => self::MIN_SEGMENT_HEIGHT_MM,
            'min_seg_w_mm' => self::MIN_SEGMENT_WIDTH_MM,
            'vcuts_pages' => $this->countCutPages($vCutsByFile),
        ]);

        $totalPages = 0;
        $skippedBlank = 0;
        $applyFilter = $filterBlank;
        $debugSamples = [];

        // Peta segmen untuk diagnostik (logging saja, tidak mengubah hasil):
        // kept = segmen jadi halaman hasil, skipped = segmen dibuang + alasan.
        $keptSegs = [];
        $skipSegs = [];

        // 2 pass: pass 1 buang blank, bila hasilnya NOL halaman ulangi tanpa filter
        // (pengaman agar tidak pernah menghasilkan PDF kosong).
        for ($pass = 0; $pass < 2; $pass++) {
            if ($pass === 1) {
                $totalPages = 0;
                $skippedBlank = 0;
                $applyFilter = false;
                $keptSegs = [];
                $skipSegs = [];
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
                $vCuts = $this->normalizeCuts($vCutsByFile[$fileIndex][$page] ?? ($vCutsByFile[(string) $fileIndex][(string) $page] ?? ($vCutsByFile[$fileIndex][(string) $page] ?? [])));

                if (empty($cuts) && empty($vCuts)) {
                    // Tanpa garis: salin apa adanya, tapi buang bila blank.
                    if ($applyFilter && $this->isBlankSegment($file, $page, 0.0, 1.0)) {
                        $skippedBlank++;
                        try {
                            $skipSegs[] = array_merge(
                                ['fi' => $fileIndex, 'pg' => $page, 'top' => 0.0, 'bot' => 1.0, 'reason' => 'blank_fullpage'],
                                $this->detector->debugSegmentMetrics($file, $page, 0.0, 1.0)
                            );
                        } catch (\Throwable $e) {
                            $skipSegs[] = ['fi' => $fileIndex, 'pg' => $page, 'top' => 0.0, 'bot' => 1.0, 'reason' => 'blank_fullpage'];
                        }
                        continue;
                    }
                    $output->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
                    $output->useTemplate($template, 0, 0, $pageW, $pageH);
                    $totalPages++;
                    $keptSegs[] = ['fi' => $fileIndex, 'pg' => $page, 'top' => 0.0, 'bot' => 1.0];
                    continue;
                }

                // Ada garis berdiri: potong grid (tidur × berdiri) → sel.
                if (! empty($vCuts)) {
                    $this->emitGridCells($output, $template, $file, $page, $pageW, $pageH, $cuts, $vCuts, $applyFilter, $pass, $totalPages, $skippedBlank, $fileIndex, $keptSegs, $skipSegs);
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

                    // Rule tinggi: segmen pendek (< 30mm) = strip sisa/margin
                    // putih → SELALU buang (tidak ikut checkbox blank).
                    // Pada pass 2 (retry tanpa filter) kembali ke batas
                    // struktural 3mm agar tidak pernah menghasilkan PDF kosong.
                    $minSegH = ($pass === 0) ? self::MIN_SEGMENT_HEIGHT_MM : 3.0;
                    if ($segH < $minSegH) {
                        if ($pass === 0) {
                            $skippedBlank++;
                        }
                        $skipSegs[] = ['fi' => $fileIndex, 'pg' => $page, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3), 'reason' => 'min_seg', 'seg_mm' => round($segH, 1)];
                        continue;
                    }

                    // Buang segmen blank/putih saja.
                    if ($applyFilter && $this->isBlankSegment($file, $page, (float) $top, (float) $bottom)) {
                        $skippedBlank++;
                        try {
                            $skipSegs[] = array_merge(
                                ['fi' => $fileIndex, 'pg' => $page, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3), 'reason' => 'blank', 'seg_mm' => round($segH, 1)],
                                $this->detector->debugSegmentMetrics($file, $page, (float) $top, (float) $bottom)
                            );
                        } catch (\Throwable $e) {
                            $skipSegs[] = ['fi' => $fileIndex, 'pg' => $page, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3), 'reason' => 'blank', 'seg_mm' => round($segH, 1)];
                        }
                        continue;
                    }

                    // Sampling diagnostik: rekam metrik segmen yang LOLOS (maks 8/job)
                    // agar terlihat dari log kenapa strip putih tidak terbuang.
                    if ($pass === 0 && $applyFilter && count($debugSamples) < 8) {
                        try {
                            $debugSamples[] = array_merge(
                                ['fi' => $fileIndex, 'pg' => $page, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3)],
                                $this->detector->debugSegmentMetrics($file, $page, (float) $top, (float) $bottom)
                            );
                        } catch (\Throwable $e) {
                        }
                    }

                    $y = $top * $pageH;
                    $offX = 0.0;
                    $offY = -$y;

                    $output->AddPage($pageW > $segH ? 'L' : 'P', [$pageW, $segH]);
                    $output->useTemplate($template, $offX, $offY, $pageW, $pageH);
                    $totalPages++;
                    $keptSegs[] = ['fi' => $fileIndex, 'pg' => $page, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3)];
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

        // Peta segmen final (pass terakhir): mana jadi halaman, mana dibuang + alasan.
        // fi=fileIndex, pg=halaman sumber, top/bot=fraksi garis. Cap agar log tidak bengkak.
        try {
            $keptLog = array_slice($keptSegs, 0, 500);
            $skipLog = array_slice($skipSegs, 0, 200);
            logger()->info('AllManual segment-map', [
                'job' => $jobId,
                'kept' => count($keptSegs),
                'skipped' => count($skipSegs),
                'kept_segs' => $keptLog,
                'skipped_segs' => $skipLog,
                'kept_truncated' => count($keptSegs) > count($keptLog),
                'skipped_truncated' => count($skipSegs) > count($skipLog),
            ]);
        } catch (\Throwable $e) {
        }

        if (! empty($debugSamples)) {
            logger()->info('AllManual blank-debug', [
                'job' => $jobId,
                'kept_samples' => $debugSamples,
            ]);
        }

        try {
            $this->detector->clearRenderCache();
        } catch (\Throwable $e) {
        }

        return $filename;
    }

    /**
     * Varian chunked untuk file raksasa (upload per bagian karena post_max_size).
     *
     * @param  array  $chunkMap   [fileIndex => [chunkAbsPath, ...]] urut per file
     * @param  string $jobId
     * @param  array  $cutsByFile [fileIndex => [globalPageNum => [frac,...]]] garis tidur
     * @param  bool   $filterBlank false = jalur cepat tanpa deteksi blank
     * @param  array  $pageOffsets [fileIndex => offset] nomor halaman global awal - 1 (untuk part job)
     * @param  array  $vCutsByFile [fileIndex => [globalPageNum => [frac,...]]] garis berdiri
     */
    public function processChunked(array $chunkMap, string $jobId, array $cutsByFile = [], bool $filterBlank = true, array $pageOffsets = [], array $vCutsByFile = []): string
    {
        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban ALL Manual Crop Result');

        try {
            $chunkRenderer = $this->detector->availableRenderer();
        } catch (\Throwable $e) {
            $chunkRenderer = null;
        }
        logger()->info('AllManualCropProcessor chunked start', [
            'job' => $jobId,
            'files' => count($chunkMap),
            'blank_renderer' => $chunkRenderer ?? 'none-vector-only',
            'filter_blank' => $filterBlank,
            'min_seg_mm' => self::MIN_SEGMENT_HEIGHT_MM,
            'min_seg_w_mm' => self::MIN_SEGMENT_WIDTH_MM,
            'vcuts_pages' => $this->countCutPages($vCutsByFile),
        ]);

        $totalPages = 0;
        $skippedBlank = 0;
        $applyFilter = $filterBlank;
        $chunkDebugSamples = [];
        $keptSegs = [];
        $skipSegs = [];

        // 2 pass: pass 1 buang blank, bila hasilnya NOL halaman ulangi tanpa filter
        // (pengaman agar tidak pernah menghasilkan PDF kosong).
        for ($pass = 0; $pass < 2; $pass++) {
            if ($pass === 1) {
                $totalPages = 0;
                $skippedBlank = 0;
                $applyFilter = false;
                $keptSegs = [];
                $skipSegs = [];
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
                    $vCuts = $this->normalizeCuts(
                        $vCutsByFile[$fileIndex][$lookup]
                        ?? ($vCutsByFile[(string) $fileIndex][(string) $lookup] ?? [])
                    );

                    if (empty($cuts) && empty($vCuts)) {
                        if ($applyFilter && $this->isBlankSegment($chunk, $local, 0.0, 1.0)) {
                            $skippedBlank++;
                            try {
                                $skipSegs[] = array_merge(
                                    ['fi' => $fileIndex, 'pg' => $lookup, 'top' => 0.0, 'bot' => 1.0, 'reason' => 'blank_fullpage'],
                                    $this->detector->debugSegmentMetrics($chunk, $local, 0.0, 1.0)
                                );
                            } catch (\Throwable $e) {
                                $skipSegs[] = ['fi' => $fileIndex, 'pg' => $lookup, 'top' => 0.0, 'bot' => 1.0, 'reason' => 'blank_fullpage'];
                            }
                            continue;
                        }
                        $output->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
                        $output->useTemplate($template, 0, 0, $pageW, $pageH);
                        $totalPages++;
                        $keptSegs[] = ['fi' => $fileIndex, 'pg' => $lookup, 'top' => 0.0, 'bot' => 1.0];
                        continue;
                    }

                    // Ada garis berdiri: potong grid (tidur × berdiri) → sel.
                    if (! empty($vCuts)) {
                        $this->emitGridCells($output, $template, $chunk, $local, $pageW, $pageH, $cuts, $vCuts, $applyFilter, $pass, $totalPages, $skippedBlank, (int) $fileIndex, $keptSegs, $skipSegs);
                        continue;
                    }

                    $bounds = array_merge([0.0], $cuts, [1.0]);

                    foreach ($bounds as $i => $top) {
                        if (! isset($bounds[$i + 1])) {
                            break;
                        }
                        $bottom = $bounds[$i + 1];
                        $segH = ($bottom - $top) * $pageH;

                        // Rule tinggi (sama seperti jalur normal): segmen pendek
                        // (< 30mm) SELALU buang sebagai strip putih.
                        $minSegH = ($pass === 0) ? self::MIN_SEGMENT_HEIGHT_MM : 3.0;
                        if ($segH < $minSegH) {
                            if ($pass === 0) {
                                $skippedBlank++;
                            }
                            $skipSegs[] = ['fi' => $fileIndex, 'pg' => $lookup, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3), 'reason' => 'min_seg', 'seg_mm' => round($segH, 1)];
                            continue;
                        }

                        if ($applyFilter && $this->isBlankSegment($chunk, $local, (float) $top, (float) $bottom)) {
                            $skippedBlank++;
                            try {
                                $skipSegs[] = array_merge(
                                    ['fi' => $fileIndex, 'pg' => $lookup, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3), 'reason' => 'blank', 'seg_mm' => round($segH, 1)],
                                    $this->detector->debugSegmentMetrics($chunk, $local, (float) $top, (float) $bottom)
                                );
                            } catch (\Throwable $e) {
                                $skipSegs[] = ['fi' => $fileIndex, 'pg' => $lookup, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3), 'reason' => 'blank', 'seg_mm' => round($segH, 1)];
                            }
                            continue;
                        }

                        if ($pass === 0 && $applyFilter && count($chunkDebugSamples) < 8) {
                            try {
                                $chunkDebugSamples[] = array_merge(
                                    ['fi' => $fileIndex, 'pg' => $lookup, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3)],
                                    $this->detector->debugSegmentMetrics($chunk, $local, (float) $top, (float) $bottom)
                                );
                            } catch (\Throwable $e) {
                            }
                        }

                        $y = $top * $pageH;

                        $output->AddPage($pageW > $segH ? 'L' : 'P', [$pageW, $segH]);
                        $output->useTemplate($template, 0.0, -$y, $pageW, $pageH);
                        $totalPages++;
                        $keptSegs[] = ['fi' => $fileIndex, 'pg' => $lookup, 'top' => round((float) $top, 3), 'bot' => round((float) $bottom, 3)];
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

        try {
            logger()->info('AllManual segment-map', [
                'job' => $jobId,
                'kept' => count($keptSegs),
                'skipped' => count($skipSegs),
                'kept_segs' => array_slice($keptSegs, 0, 500),
                'skipped_segs' => array_slice($skipSegs, 0, 200),
                'kept_truncated' => count($keptSegs) > 500,
                'skipped_truncated' => count($skipSegs) > 200,
            ]);
        } catch (\Throwable $e) {
        }

        if (! empty($chunkDebugSamples)) {
            logger()->info('AllManual blank-debug', [
                'job' => $jobId,
                'kept_samples' => $chunkDebugSamples,
            ]);
        }

        try {
            $this->detector->clearRenderCache();
        } catch (\Throwable $e) {
        }

        return $filename;
    }

    /**
     * Segmen blank = halaman/potongan putih polos tanpa isi.
     * Urutan cek: (0) halaman tanpa operator, (1) piksel render
     * (scan gambar & sisa border), (2) fallback vektor teks+tinta,
     * (3) veto parse-degenere (stream besar tapi tak terekstrak).
     * Fail-safe: ragu-ragu => BERISI (jangan buang).
     */
    protected function isBlankSegment(string $file, int $page, float $fracTop, float $fracBottom): bool
    {
        // 0) Halaman-penuh tanpa teks & tanpa gambar (atau tanpa operator
        //    sama sekali) = blank. Berlaku untuk segmen mana pun di halaman
        //    tersebut — halaman kosong sumber tidak lolos jadi strip putih.
        try {
            if ($this->detector->isFullPageBlank($file, $page) === true) {
                return true;
            }
        } catch (\Throwable $e) {
            // abaikan, lanjut cek berikut
        }

        // 1) Cek piksel: akurat untuk PDF scan (gambar) maupun vektor.
        try {
            $pixel = $this->detector->isPixelBlank($file, $page, $fracTop, $fracBottom);
            if ($pixel === true) {
                return true;
            }
            if ($pixel === false) {
                return false;
            }
            // null = tidak bisa render, lanjut ke fallback vektor.
        } catch (\Throwable $e) {
            // abaikan, lanjut fallback
        }

        // 2) Fallback vektor: tidak ada teks DAN nyaris tidak ada tinta.
        try {
            if (! $this->detector->isRegionEmpty($file, $page, $fracTop, $fracBottom)) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        try {
            if ($this->detector->countInkInBand($file, $page, $fracTop, $fracBottom) > 5) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        // 3) Veto parse-degenere (kasus 4260669054 hal-1): segmen mau dibuang
        //    TAPI parser nyaris tidak mengekstrak apa pun dari SELURUH halaman
        //    padahal stream mentah besar (filter tak dikenal / object stream /
        //    encoding aneh) → parse tidak bisa dipercaya, anggap BERISI.
        //    Bukti: raw 12802 byte, text_page 5 (normal 135-138), ink total ~0
        //    (normal puluhan ribu). Halaman blank beneran punya raw kecil
        //    sehingga tetap terbuang. Fail-safe: ragu-ragu => BERISI.
        //    Ambang longgar (500, bukan 5) karena 5 item teks stray saja sudah
        //    menandai ~45 bin; halaman kanban asli ber-ink puluhan ribu.
        try {
            $metrics = $this->detector->debugSegmentMetrics($file, $page, $fracTop, $fracBottom);
            $rawTotal = $metrics['raw'] ?? null;
            $textTotal = $metrics['text_page'] ?? null;
            if (is_int($rawTotal) && is_int($textTotal) && $rawTotal > 5000 && $textTotal < 30) {
                $inkTotal = $this->detector->countInkInBand($file, $page, 0.0, 1.0);
                if ($inkTotal < 500) {
                    logger()->info('AllManual blank-veto', [
                        'file' => basename($file),
                        'pg' => $page,
                        'top' => round($fracTop, 3),
                        'bot' => round($fracBottom, 3),
                        'metrics' => $metrics,
                        'ink_total' => $inkTotal,
                    ]);

                    return false;
                }
                // Halaman mencurigakan tapi ink total tak terduga tinggi:
                // catat agar terlihat bila veto lolos palsu.
                logger()->info('AllManual blank-veto-skip', [
                    'file' => basename($file),
                    'pg' => $page,
                    'top' => round($fracTop, 3),
                    'bot' => round($fracBottom, 3),
                    'metrics' => $metrics,
                    'ink_total' => $inkTotal,
                ]);
            }
        } catch (\Throwable $e) {
            // abaikan, lanjut putusan blank di bawah
        }

        return true;
    }

    /**
     * Potong satu halaman jadi grid sel (garis tidur × garis berdiri).
     * Tiap sel jadi 1 halaman hasil (orientasi mengikuti bentuk sel).
     * Sel sempit (< 30mm) atau blank otomatis dibuang — sama seperti
     * aturan segmen horizontal.
     */
    protected function emitGridCells(
        Fpdi $output,
        int|string $template,
        string $file,
        int $page,
        float $pageW,
        float $pageH,
        array $hCuts,
        array $vCuts,
        bool $applyFilter,
        int $pass,
        int &$totalPages,
        int &$skippedBlank,
        int $fileIndex = 0,
        ?array &$keptSegs = null,
        ?array &$skipSegs = null
    ): void {
        $hBounds = array_merge([0.0], array_values($hCuts), [1.0]);
        $vBounds = array_merge([0.0], array_values($vCuts), [1.0]);

        $cell = function (float $top, float $bottom, float $left, float $right, string $reason, array $extra = []) use ($fileIndex, $page, &$keptSegs, &$skipSegs): void {
            try {
                if ($skipSegs !== null) {
                    $skipSegs[] = array_merge(['fi' => $fileIndex, 'pg' => $page, 'top' => round($top, 3), 'bot' => round($bottom, 3), 'left' => round($left, 3), 'right' => round($right, 3), 'reason' => $reason], $extra);
                }
            } catch (\Throwable $e) {
            }
        };

        for ($hi = 0; $hi < count($hBounds) - 1; $hi++) {
            $top = (float) $hBounds[$hi];
            $bottom = (float) $hBounds[$hi + 1];
            $segH = ($bottom - $top) * $pageH;

            $minSegH = ($pass === 0) ? self::MIN_SEGMENT_HEIGHT_MM : 3.0;
            if ($segH < $minSegH) {
                if ($pass === 0) {
                    $skippedBlank++;
                }
                $cell($top, $bottom, 0.0, 1.0, 'min_seg_h', ['seg_mm' => round($segH, 1)]);
                continue;
            }

            for ($vi = 0; $vi < count($vBounds) - 1; $vi++) {
                $left = (float) $vBounds[$vi];
                $right = (float) $vBounds[$vi + 1];
                $segW = ($right - $left) * $pageW;

                $minSegW = ($pass === 0) ? self::MIN_SEGMENT_WIDTH_MM : 3.0;
                if ($segW < $minSegW) {
                    if ($pass === 0) {
                        $skippedBlank++;
                    }
                    $cell($top, $bottom, $left, $right, 'min_seg_w', ['seg_mm' => round($segW, 1)]);
                    continue;
                }

                if ($applyFilter && $this->isBlankCell($file, $page, $top, $bottom, $left, $right)) {
                    $skippedBlank++;
                    $extra = [];
                    try {
                        $extra = $this->detector->debugSegmentMetrics($file, $page, $top, $bottom);
                    } catch (\Throwable $e) {
                    }
                    $cell($top, $bottom, $left, $right, 'blank_cell', $extra);
                    continue;
                }

                $x = $left * $pageW;
                $y = $top * $pageH;

                $output->AddPage($segW > $segH ? 'L' : 'P', [$segW, $segH]);
                $output->useTemplate($template, -$x, -$y, $pageW, $pageH);
                $totalPages++;
                try {
                    if ($keptSegs !== null) {
                        $keptSegs[] = ['fi' => $fileIndex, 'pg' => $page, 'top' => round($top, 3), 'bot' => round($bottom, 3), 'left' => round($left, 3), 'right' => round($right, 3)];
                    }
                } catch (\Throwable $e) {
                }
            }
        }
    }

    /**
     * Sel blank = tidak ada isi pada persegi (top,bottom,left,right).
     * Fail-safe: ragu-ragu => BERISI (jangan buang).
     */
    protected function isBlankCell(
        string $file,
        int $page,
        float $fracTop,
        float $fracBottom,
        float $fracLeft,
        float $fracRight
    ): bool {
        // Halaman-penuh kosong = semua sel blank.
        try {
            if ($this->detector->isFullPageBlank($file, $page) === true) {
                return true;
            }
        } catch (\Throwable $e) {
        }

        // Strip horizontal blank (cek lama) = sel ikut blank.
        if ($this->isBlankSegment($file, $page, $fracTop, $fracBottom)) {
            return true;
        }

        // Cek piksel persegi sel; null = tidak bisa render → simpan (jangan buang).
        try {
            if ($this->detector->isPixelBlankCell($file, $page, $fracTop, $fracBottom, $fracLeft, $fracRight) === true) {
                return true;
            }
        } catch (\Throwable $e) {
        }

        return false;
    }

    /**
     * Hitung berapa halaman yang punya garis (untuk log diagnostik).
     */
    protected function countCutPages(array $cutsByFile): int
    {
        $n = 0;
        foreach ($cutsByFile as $pages) {
            if (! is_array($pages)) {
                continue;
            }
            foreach ($pages as $lines) {
                if (is_array($lines) && ! empty($lines)) {
                    $n++;
                }
            }
        }

        return $n;
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
