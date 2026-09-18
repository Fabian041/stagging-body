<?php

namespace App\Services\Mmki;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Splitter khusus form MMKI (PT Mitsubishi Motors Krama Yudha Indonesia).
 *
 * Alur sama dengan KanbanPdfSplitter (collect -> skip kosong -> press ke isi
 * -> render seragam per orientasi), tapi detector yang dipakai adalah
 * MmkiKanbanRegionDetector yang marker & titik potongnya disetel untuk
 * layout MMKI (Parts Number / PO Number / Label Number, 3 kanban per halaman
 * portrait). Service ini sepenuhnya terpisah dari alur TMMIN/YMMIN agar
 * penyetelan satu form tidak merusak form lainnya.
 */
class MmkiKanbanPdfSplitter
{


    public function __construct(
        protected MmkiKanbanRegionDetector $detector
    )
    {

    }



    /**
     * Kumpulkan region kanban per file TANPA render (detect + buang
     * kosong + press ke isi). Hasil dalam mm (satuan FPDI).
     *
     * @return array<int, array{page:int,x:float,y:float,w:float,h:float,pageW:float,pageH:float}>
     */
    public function collectRegions(string $file): array
    {
        $probe = new Fpdi();
        try {
            $pageCount = $probe->setSourceFile($file);
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];

        for ($page = 1; $page <= $pageCount; $page++) {
            try {
                $template = $probe->importPage($page);
                $size = $probe->getTemplateSize($template);
            } catch (\Throwable $e) {
                continue;
            }

            $pageW = (float) ($size['width'] ?? 0);
            $pageH = (float) ($size['height'] ?? 0);

            if ($pageW <= 0 || $pageH <= 0) {
                continue;
            }

            try {
                $regions = $this->detector->detect($file, $page, ['width' => $pageW, 'height' => $pageH]);
            } catch (\Throwable $e) {
                $regions = [];
            }

            if (empty($regions)) {
                continue;
            }

            foreach ($regions as $region) {
                $w = (float) ($region['width'] ?? 0);
                $h = (float) ($region['height'] ?? 0);
                $x = (float) ($region['x'] ?? 0);
                $y = (float) ($region['y'] ?? 0);

                if ($w <= 0 || $h <= 0) {
                    continue;
                }

                $x = max(0, min($x, $pageW - 1));
                $y = max(0, min($y, $pageH - 1));
                $w = min($w, $pageW - $x);
                $h = min($h, $pageH - $y);

                if ($w <= 0 || $h <= 0) {
                    continue;
                }

                $fracTop = $y / $pageH;
                $fracBottom = ($y + $h) / $pageH;

                // Buang slice kosong / whitespace saja.
                try {
                    if ($this->detector->isRegionEmpty($file, $page, $fracTop, $fracBottom)) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // lanjut seperti biasa
                }

                // Press ke isi: buang margin kertas kosong atas/bawah.
                try {
                    [$tFrac, $bFrac] = $this->detector->trimRegionToContent($file, $page, $fracTop, $fracBottom);
                    $y = $tFrac * $pageH;
                    $h = max(1.0, ($bFrac - $tFrac) * $pageH);
                } catch (\Throwable $e) {
                    // pakai region asal
                }

                // Potongan tambahan atas 10px (~2.65mm). Ditaruh di sini
                // (bukan di detector) agar SELALU kena untuk semua region
                // MMKI, apapun cabang deteksi yang dipakai (box-gap,
                // box-rect, maupun fallback baris teks).
                $y += 2.65;
                $h = max(1.0, $h - 2.65);

                if ($w <= 0 || $h <= 0) {
                    continue;
                }

                $out[] = [
                    'page' => $page,
                    'x' => $x,
                    'y' => $y,
                    'w' => $w,
                    'h' => $h,
                    'pageW' => $pageW,
                    'pageH' => $pageH,
                ];
            }
        }

        return $out;
    }

    /**
     * Render region-region ke PDF output dengan ukuran kertas SERAGAM
     * (max w/h per orientasi), konten di tengah. Tidak perlu crop manual.
     */
    public function renderRegions(
        Fpdi $pdf,
        string $file,
        array $regions,
        float $uniW,
        float $uniH
    ): int {
        if (empty($regions) || $uniW <= 0 || $uniH <= 0) {
            return 0;
        }

        try {
            $pdf->setSourceFile($file);
        } catch (\Throwable $e) {
            return 0;
        }

        $tplCache = [];
        $added = 0;

        foreach ($regions as $r) {
            $page = (int) ($r['page'] ?? 0);
            $x = (float) ($r['x'] ?? 0);
            $y = (float) ($r['y'] ?? 0);
            $w = (float) ($r['w'] ?? 0);
            $h = (float) ($r['h'] ?? 0);
            $pageW = (float) ($r['pageW'] ?? 0);
            $pageH = (float) ($r['pageH'] ?? 0);

            if ($page < 1 || $w <= 0 || $h <= 0 || $pageW <= 0 || $pageH <= 0) {
                continue;
            }

            try {
                if (! isset($tplCache[$page])) {
                    $tplCache[$page] = $pdf->importPage($page);
                }
                $template = $tplCache[$page];
            } catch (\Throwable $e) {
                continue;
            }

            // Pusatkan konten di kertas seragam.
            $offX = -$x + max(0, ($uniW - $w) / 2);
            $offY = -$y + max(0, ($uniH - $h) / 2);

            $pdf->AddPage($uniW > $uniH ? 'L' : 'P', [$uniW, $uniH]);
            $pdf->useTemplate($template, $offX, $offY, $pageW, $pageH);
            $added++;
        }

        return $added;
    }

    public function split(
        Fpdi $pdf,
        string $file
    ): int
    {
        // Jalur MMKI: collect (detect + skip kosong + press ke isi),
        // lalu render seragam per file.
        $regions = $this->collectRegions($file);

        if (empty($regions)) {
            // Fallback: salin halaman apa adanya.
            try {
                $pageCount = $pdf->setSourceFile($file);
            } catch (\Throwable $e) {
                return 0;
            }

            $added = 0;
            for ($page = 1; $page <= $pageCount; $page++) {
                try {
                    $template = $pdf->importPage($page);
                    $size = $pdf->getTemplateSize($template);
                } catch (\Throwable $e) {
                    continue;
                }

                $pageW = (float) ($size['width'] ?? 0);
                $pageH = (float) ($size['height'] ?? 0);

                if ($pageW <= 0 || $pageH <= 0) {
                    continue;
                }

                $pdf->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
                $pdf->useTemplate($template, 0, 0, $pageW, $pageH);
                $added++;
            }

            logger()->warning('Mmki split: no regions, page copied as-is', [
                'file' => basename($file),
            ]);

            return $added;
        }

        // Ukuran seragam per file (max w/h per orientasi).
        [$uniL, $uniP] = $this->uniformSizes($regions);

        $byOrient = ['L' => [], 'P' => []];
        foreach ($regions as $r) {
            $byOrient[$r['w'] > $r['h'] ? 'L' : 'P'][] = $r;
        }

        $added = 0;
        if (! empty($byOrient['L'])) {
            $added += $this->renderRegions($pdf, $file, $byOrient['L'], $uniL[0], $uniL[1]);
        }
        if (! empty($byOrient['P'])) {
            $added += $this->renderRegions($pdf, $file, $byOrient['P'], $uniP[0], $uniP[1]);
        }

        logger()->info('Mmki split file', [
            'file' => basename($file),
            'regions' => count($regions),
            'uniL' => array_map(static fn ($v) => round($v, 1), $uniL),
            'uniP' => array_map(static fn ($v) => round($v, 1), $uniP),
        ]);

        return $added;
    }

    /**
     * Ukuran seragam = max w/h per orientasi.
     *
     * @param array<int, array{w:float,h:float}> $regions
     * @return array{0:array{0:float,1:float}, 1:array{0:float,1:float}} [uniL, uniP]
     */
    public function uniformSizes(array $regions): array
    {
        $maxLw = $maxLh = $maxPw = $maxPh = 0.0;

        foreach ($regions as $r) {
            $w = (float) ($r['w'] ?? 0);
            $h = (float) ($r['h'] ?? 0);

            if ($w <= 0 || $h <= 0) {
                continue;
            }

            if ($w > $h) {
                $maxLw = max($maxLw, $w);
                $maxLh = max($maxLh, $h);
            } else {
                $maxPw = max($maxPw, $w);
                $maxPh = max($maxPh, $h);
            }
        }

        return [[$maxLw, $maxLh], [$maxPw, $maxPh]];
    }


}
