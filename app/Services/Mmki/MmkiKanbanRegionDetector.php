<?php

namespace App\Services\Mmki;

use App\Services\KanbanRegionDetector;

/**
 * Detector khusus form MMKI (PT Mitsubishi Motors Krama Yudha Indonesia).
 *
 * Layout MMKI berbeda dari TMMIN/YMMIN:
 *  - 1 halaman portrait berisi N kanban tersusun vertikal (umumnya 3).
 *  - Tiap kanban diawali blok "Parts Number" tepat di bawah header
 *    "PT Mitsubishi Motors Krama Yudha Indonesia" dan diakhiri blok
 *    "Label Number" + barcode di kaki kanban.
 *  - Marker Toyota ("UNIQUE NO", "MANIFEST NO", "Printed at Supplier", ...)
 *    TIDAK ada di PDF MMKI, sehingga penghitung & titik potong memakai
 *    marker MMKI sendiri.
 *
 * Prioritas titik potong (diwarisi dari base untuk butir 2-5):
 *  1. tengah celah antara baris "Label Number" (kaki) dan "Parts Number"
 *     (kepala kanban berikutnya),
 *  2. whitespace horizontal tahan encoding,
 *  3. garis putus-putus / dash,
 *  4. garis horizontal / border tabel,
 *  5. fallback potongan sama rata.
 */
class MmkiKanbanRegionDetector extends KanbanRegionDetector
{
    protected const MARKERS = [
        'PARTS NUMBER',
        'PO NUMBER',
        'LABEL NUMBER',
        'DELIVERY DATE',
        'VENDOR CODE',
        'VENDOR NAME',
        'PART NAMES',
        'MITSUBISHI MOTORS',
    ];

    protected const SHORT_MARKERS = [
        'PARTS NUMBER',
        'LABEL NUMBER',
        'PO NUMBER',
        'VENDOR',
        'DELIVERY',
    ];

    /** Potongan tambahan dari atas per kanban: 0 = pas border box.
     *  Potongan ekstra diatur tunggal di MmkiKanbanPdfSplitter agar
     *  tidak dobel dengan konverter box di bawah. */
    private const TOP_CUT_EXTRA_MM = 3.2;

    /**
     * Deteksi MMKI prioritas border box luar (solid `re` selebar halaman).
     *
     * Setiap kanban MMKI punya outer border solid yang sama persis
     * ukurannya — tepi atasnya 2-3mm di atas header "PT Mitsubishi...".
     * Potong berbasis teks selalu meleset (label vs parts beda 30-40pt
     * dari tepi + field kosong bikin tinggi bervariasi -> halaman tengah
     * bocor tetangga seperti di result). Jadi di sini region = box-nya
     * langsung, dikonversi pt -> mm. Gagal -> fallback parent.
     *
     * @return array<int, array{x:float, y:float, width:float, height:float}>
     */
    public function detect(
        string $file,
        int $page,
        ?array $pageSize = null
    ): array {
        try {
            $raw = @file_get_contents($file);
            if (is_string($raw) && $raw !== '') {
                $boxes = $this->findMmkiBoxes($raw, $page, $pageSize);
                if (! empty($boxes)) {
                    logger()->info('Mmki detect boxes', [
                        'page' => $page,
                        'boxes' => count($boxes),
                    ]);

                    return $boxes;
                }
            }
        } catch (\Throwable $e) {
            // fallback ke parent
        }

        return parent::detect($file, $page, $pageSize);
    }

    /**
     * Cari box MMKI dengan approach baru:
     *  1. crop blank putih atas -> tinta pertama = top kanban-1,
     *  2. ukur kanban-1 sampai gap blank pertama = H1,
     *  3. kanban tengah & bawah mengikuti H1 (top jangkar ke tinta,
     *     tinggi dipaksa = H1).
     * Gagal -> fallback ke deteksi `re` border box.
     *
     * @return array<int, array{x:float, y:float, width:float, height:float}>
     *         Satuan mm (FPDI), origin kiri-atas. Kosong bila tak cocok.
     */
    private function findMmkiBoxes(string $raw, int $page, ?array $pageSize): array
    {
        $byGap = $this->findMmkiBoxesByGap($raw, $page, $pageSize);
        if (! empty($byGap)) {
            return $byGap;
        }

        return $this->findMmkiBoxesByRect($raw, $page, $pageSize);
    }

    /**
     * Gap-based: tinta pertama dari atas = top box-1, blank run pertama
     * = akhir box-1. Tinggi H1 dipakai untuk semua box berikutnya.
     *
     * @return array<int, array{x:float, y:float, width:float, height:float}>
     */
    private function findMmkiBoxesByGap(string $raw, int $page, ?array $pageSize): array
    {
        $objects = $this->parseObjects($raw);
        $pages = $this->orderedPageNumbers($objects);

        if (! isset($pages[$page - 1])) {
            return [];
        }

        $pageObj = (int) $pages[$page - 1];
        $content = $this->decodedPageContent($objects, $pageObj);

        if ($content === '') {
            return [];
        }

        $media = $this->readMediaBoxPt($objects, $pageObj);
        $pageWPt = (float) $media['width'];
        $pageHPt = (float) $media['height'];

        if ($pageWPt <= 0 || $pageHPt <= 0) {
            return [];
        }

        $pageWMm = (float) ($pageSize['width'] ?? 0);
        $pageHMm = (float) ($pageSize['height'] ?? 0);
        if ($pageWMm <= 0 || $pageHMm <= 0) {
            $pageWMm = $pageWPt * 25.4 / 72;
            $pageHMm = $pageHPt * 25.4 / 72;
        }
        $k = $pageWMm / $pageWPt;

        try {
            $expected = $this->countKanbansOnPage($raw, $page);
        } catch (\Throwable $e) {
            $expected = 3;
        }
        $expected = max(1, min(6, (int) $expected));

        if ($expected === 1) {
            return [];
        }

        $items = $this->extractTextWithPositions($content);
        if (count($items) < 10) {
            return [];
        }

        $ink = $this->buildInkHistogram($content, $items, $pageHPt);
        if (empty($ink)) {
            return [];
        }

        $n = count($ink) - 1;
        $minGap = 4; // pt blank berurutan = pemisah antar box

        // 1. Crop blank putih atas: tinta pertama dari atas.
        $top1 = null;
        for ($y = $n; $y >= 0; $y--) {
            if (($ink[$y] ?? 0) > 0) {
                $top1 = (float) $y;
                break;
            }
        }
        if ($top1 === null) {
            return [];
        }

        // 2. Ukur kanban-1: scan turun sampai blank run >= minGap.
        $measureBox = static function (float $fromY, int $lo) use ($ink, $n, $minGap): ?array {
            $zeroRun = 0;
            $gapTop = null;
            for ($y = (int) ceil($fromY); $y >= $lo; $y--) {
                if (($ink[$y] ?? 0) > 0) {
                    if ($zeroRun >= $minGap) {
                        $gapTop = (float) ($y + 1); // tinta terakhir sblm gap
                        break;
                    }
                    $zeroRun = 0;
                } else {
                    $zeroRun++;
                }
            }
            if ($gapTop === null) {
                return null;
            }

            return ['bottom' => $gapTop, 'height' => $fromY - $gapTop];
        };

        $first = $measureBox($top1, 0);
        if ($first === null) {
            return [];
        }

        $h1 = (float) $first['height'];
        // Validasi: satu kanban ~1/expected halaman (toleransi longgar).
        $approx = $pageHPt / $expected;
        if ($h1 < $approx * 0.55 || $h1 > $approx * 1.45) {
            return [];
        }
        $bottom1 = (float) $first['bottom'];

        // 3. Kanban berikut: top jangkar ke tinta pertama di bawah gap,
        // tinggi dipaksa = H1 (mengikuti kanban pertama).
        $tops = [$top1];
        $bottoms = [$bottom1];
        $cursor = $bottom1;

        for ($i = 1; $i < $expected; $i++) {
            // Lewati gap blank dari cursor ke bawah.
            $gapBottom = null;
            $inGap = false;
            for ($y = (int) floor($cursor); $y >= 0; $y--) {
                if (($ink[$y] ?? 0) > 0) {
                    if ($inGap) {
                        $gapBottom = (float) $y;
                        break;
                    }
                } else {
                    $inGap = true;
                }
            }
            if ($gapBottom === null) {
                return [];
            }
            // Tinta pertama di bawah gap = top box berikutnya.
            // (gapBottom sudah tinta, karena loop berhenti di tinta.)
            $topI = $gapBottom;
            // Koreksi naik 1-2pt: loop berhenti tepat di tinta pertama,
            // tapi border atas tebal 1pt -> sudah pas.
            $bottomI = $topI - $h1;

            if ($bottomI < -5) {
                return [];
            }

            $tops[] = $topI;
            $bottoms[] = $bottomI;
            $cursor = $bottomI;
        }

        // Konversi ke mm origin kiri-atas, full width.
        $out = [];
        for ($i = 0; $i < $expected; $i++) {
            $topPt = $tops[$i];
            $botPt = $bottoms[$i];
            // Rapat ke border + potong ekstra 10px dari atas.
            $yMm = max(0.0, ($pageHPt - $topPt) * $k + self::TOP_CUT_EXTRA_MM);
            $hMm = max(1.0, ($topPt - $botPt) * $k + 0.3 - self::TOP_CUT_EXTRA_MM);
            if ($yMm + $hMm > $pageHMm + 1) {
                $hMm = $pageHMm - $yMm;
            }
            if ($hMm <= 0) {
                return [];
            }
            $out[] = ['x' => 0.0, 'y' => $yMm, 'width' => $pageWMm, 'height' => $hMm];
        }

        return $out;
    }

    /**
     * Fallback lama: outer border `x y w h re` selebar halaman.
     *
     * @return array<int, array{x:float, y:float, width:float, height:float}>
     */
    private function findMmkiBoxesByRect(string $raw, int $page, ?array $pageSize): array
    {
        $objects = $this->parseObjects($raw);
        $pages = $this->orderedPageNumbers($objects);

        if (! isset($pages[$page - 1])) {
            return [];
        }

        $pageObj = (int) $pages[$page - 1];
        $content = $this->decodedPageContent($objects, $pageObj);

        if ($content === '') {
            return [];
        }

        $media = $this->readMediaBoxPt($objects, $pageObj);
        $pageWPt = (float) $media['width'];
        $pageHPt = (float) $media['height'];

        if ($pageWPt <= 0 || $pageHPt <= 0) {
            return [];
        }

        // Ukuran mm dari pemanggil (FPDI). Fallback ke konversi pt.
        $pageWMm = (float) ($pageSize['width'] ?? 0);
        $pageHMm = (float) ($pageSize['height'] ?? 0);
        if ($pageWMm <= 0 || $pageHMm <= 0) {
            $pageWMm = $pageWPt * 25.4 / 72;
            $pageHMm = $pageHPt * 25.4 / 72;
        }
        $k = $pageWMm / $pageWPt; // skala pt -> mm

        // Buang string literal/hex lokal (base strip private).
        $stripped = (string) preg_replace('/\((?:\\\\.|[^\\\\()])*\)/s', ' ', $content);
        $stripped = (string) preg_replace('/<[0-9A-Fa-f\s]*>/', ' ', $stripped);

        if (! preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+re\b/',
            $stripped,
            $m,
            PREG_SET_ORDER
        )) {
            return [];
        }

        $cands = [];
        foreach ($m as $s) {
            $x = (float) $s[1];
            $y = (float) $s[2];
            $w = (float) $s[3];
            $h = (float) $s[4];

            // Outer box: hampir selebar halaman, tinggi ~1/6..1/2 halaman.
            // Bukan garis tabel dalam (w kecil) atau tepi halaman penuh.
            if ($w < 0.80 * $pageWPt) {
                continue;
            }
            if ($h < 40 || $h > 0.55 * $pageHPt) {
                continue;
            }
            if ($x < -5 || $x > 0.15 * $pageWPt) {
                continue;
            }
            if ($y < -5 || ($y + $h) > $pageHPt + 5) {
                continue;
            }

            $cands[] = ['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h];
        }

        if (empty($cands)) {
            return [];
        }

        // Gabungkan duplikat (border digambar 2x: fill + stroke).
        usort($cands, static fn ($a, $b) => $b['y'] <=> $a['y']);
        $merged = [];
        foreach ($cands as $c) {
            $dup = false;
            foreach ($merged as $e) {
                if (abs($c['y'] - $e['y']) < 3 && abs($c['h'] - $e['h']) < 3) {
                    $dup = true;
                    break;
                }
            }
            if (! $dup) {
                $merged[] = $c;
            }
        }

        // Harus 2..6 box dan selaras dengan jumlah marker teks.
        if (count($merged) < 1 || count($merged) > 6) {
            return [];
        }

        try {
            $expected = $this->countKanbansOnPage($raw, $page);
        } catch (\Throwable $e) {
            $expected = count($merged);
        }

        if ($expected >= 1 && $expected <= 6 && count($merged) !== $expected) {
            // Toleransi: ambil yang tingginya paling seragam bila lebih.
            if (count($merged) < $expected) {
                return [];
            }
        }

        // Urut atas -> bawah, konversi ke mm origin kiri-atas.
        usort($merged, static fn ($a, $b) => $b['y'] <=> $a['y']);

        $out = [];
        foreach ($merged as $c) {
            $xMm = max(0.0, $c['x'] * $k);
            $topPt = $pageHPt - ($c['y'] + $c['h']);
            $yMm = max(0.0, $topPt * $k + self::TOP_CUT_EXTRA_MM);
            $wMm = min($pageWMm - $xMm, $c['w'] * $k + 0.3);
            $hMm = $c['h'] * $k + 0.3 - self::TOP_CUT_EXTRA_MM;

            if ($wMm <= 0 || $hMm <= 0) {
                continue;
            }

            $out[] = ['x' => $xMm, 'y' => $yMm, 'width' => $wMm, 'height' => $hMm];
        }

        // Validasi jarak: box tidak boleh tumpang tindih berat.
        for ($i = 0; $i + 1 < count($out); $i++) {
            $b0 = $out[$i]['y'] + $out[$i]['height'];
            $b1 = $out[$i + 1]['y'];
            if ($b0 - $b1 > 5) {
                return [];
            }
        }

        return $out;
    }

    /**
     * Cari posisi potong dari celah teks MMKI: titik tengah antara baris
     * "Label Number..." (kaki kanban) dan "Parts Number" (kepala kanban
     * berikutnya). Hasilnya tepat di whitespace pemisah.
     *
     * @return array<int, float> fraksi 0..1 dari atas, terurut naik.
     */
    protected function findTextGapFractions(string $raw, int $page, int $kanbanCount): array
    {
        if ($kanbanCount <= 1) {
            return [];
        }

        $objects = $this->parseObjects($raw);
        $pages = $this->orderedPageNumbers($objects);

        if (! isset($pages[$page - 1])) {
            return [];
        }

        $pageObj = (int) $pages[$page - 1];
        $content = $this->decodedPageContent($objects, $pageObj);

        if ($content === '') {
            return [];
        }

        $media = $this->readMediaBoxPt($objects, $pageObj);
        $pageH = $media['height'];

        $items = $this->extractTextWithPositions($content);

        if (empty($items)) {
            return [];
        }

        $bottoms = [];
        $tops = [];

        // Gabung string yang sebaris: TJ array sering memecah kata
        // menjadi beberapa string, mis. (LABEL )(NUMBER).
        $byLine = [];
        foreach ($items as $it) {
            $key = (string) round(((float) $it['y']) / 2);
            $byLine[$key][] = $it;
        }

        $lines = [];
        foreach ($byLine as $parts) {
            $joined = '';
            $ys = [];
            foreach ($parts as $p) {
                $joined .= (string) $p['text'];
                $ys[] = (float) $p['y'];
            }
            $lines[] = [
                'text' => $joined,
                'y' => array_sum($ys) / max(1, count($ys)),
            ];
        }

        // Cocokkan per string maupun per baris gabungan.
        // Kaki kanban MMKI  : "Label Number"
        // Kepala kanban MMKI: "Parts Number" (tepat di bawah header
        // "PT Mitsubishi Motors Krama Yudha Indonesia").
        foreach (array_merge($items, $lines) as $c) {
            $t = strtoupper(trim((string) $c['text']));
            if ($t === '') {
                continue;
            }
            $tc = (string) preg_replace('/\s+/', '', $t);
            if (str_starts_with($t, 'LABEL NUMBER') || str_starts_with($tc, 'LABELNUMBER')) {
                $bottoms[] = (float) $c['y'];
            } elseif (str_starts_with($t, 'PARTS NUMBER') || str_starts_with($tc, 'PARTSNUMBER')) {
                $tops[] = (float) $c['y'];
            }
        }

        $bottoms = $this->uniqueYs($bottoms);
        $tops = $this->uniqueYs($tops);

        if (count($bottoms) !== $kanbanCount || count($tops) !== $kanbanCount) {
            logger()->info('Mmki text-gap skip', [
                'page' => $page,
                'kanban' => $kanbanCount,
                'items' => count($items),
                'tops' => count($tops),
                'bottoms' => count($bottoms),
            ]);

            return [];
        }

        for ($i = 0; $i < $kanbanCount; $i++) {
            // Kepala ("Parts Number") harus di atas kaki ("Label Number")
            // kanbannya sendiri.
            if ($tops[$i] <= $bottoms[$i]) {
                return [];
            }
        }

        $fracs = [];
        for ($i = 0; $i < $kanbanCount - 1; $i++) {
            if ($bottoms[$i] <= $tops[$i + 1]) {
                return [];
            }
            $gap = $bottoms[$i] - $tops[$i + 1];
            if ($gap < 4 || $gap > 0.6 * $pageH) {
                return [];
            }
            $fracs[] = 1 - (($bottoms[$i] + $tops[$i + 1]) / 2 / $pageH);
        }

        sort($fracs);

        return array_values($fracs);
    }

    /**
     * Press region ke bounding-box TINTA (bukan teks saja).
     *
     * Masalah MMKI: header "PT Mitsubishi..." + logo + border atas dan
     * barcode + border bawah adalah grafik/image, bukan teks. Trim base
     * yang hanya pakai maxY teks ("Parts Number") selalu jatuh di atas
     * header sehingga header kepotong.
     *
     * Di sini tepi atas/bawah dicari dari histogram tinta (teks + m/l/re/
     * kurva + image Do/BI via CTM) di dalam region asal, lalu diberi
     * padding kecil. Gap antar box kosong tinta sehingga snap tepat ke
     * outer border box.
     *
     * @return array{0:float, 1:float} [fracTop, fracBottom] baru.
     */
    public function trimRegionToContent(
        string $file,
        int $page,
        float $fracTop,
        float $fracBottom,
        float $padPt = 4.0
    ): array {
        try {
            if ($fracBottom <= $fracTop) {
                return [$fracTop, $fracBottom];
            }

            $raw = @file_get_contents($file);
            if (! is_string($raw) || $raw === '') {
                return parent::trimRegionToContent($file, $page, $fracTop, $fracBottom, $padPt);
            }

            $objects = $this->parseObjects($raw);
            $pages = $this->orderedPageNumbers($objects);

            if (! isset($pages[$page - 1])) {
                return [$fracTop, $fracBottom];
            }

            $pageObj = (int) $pages[$page - 1];
            $content = $this->decodedPageContent($objects, $pageObj);

            if ($content === '') {
                return [$fracTop, $fracBottom];
            }

            $media = $this->readMediaBoxPt($objects, $pageObj);
            $pageH = (float) $media['height'];

            if ($pageH <= 0) {
                return [$fracTop, $fracBottom];
            }

            $yTop = (1 - $fracTop) * $pageH;
            $yBottom = (1 - $fracBottom) * $pageH;

            if ($yTop <= $yBottom) {
                return [$fracTop, $fracBottom];
            }

            $items = $this->extractTextWithPositions($content);
            $ink = $this->buildInkHistogram($content, $items, $pageH);

            if (empty($ink)) {
                return parent::trimRegionToContent($file, $page, $fracTop, $fracBottom, $padPt);
            }

            $n = count($ink) - 1;
            $hi = max(0, min($n, (int) ceil($yTop)));
            $lo = max(0, min($n, (int) floor($yBottom)));

            // Scan dari tepi region ke dalam: lewati whitespace gap,
            // berhenti di tinta pertama (border box / header / barcode).
            $topInk = null;
            for ($y = $hi; $y >= $lo; $y--) {
                if (($ink[$y] ?? 0) > 0) {
                    $topInk = (float) $y;
                    break;
                }
            }

            $botInk = null;
            for ($y = $lo; $y <= $hi; $y++) {
                if (($ink[$y] ?? 0) > 0) {
                    $botInk = (float) $y;
                    break;
                }
            }

            if ($topInk === null || $botInk === null || $topInk <= $botInk) {
                return parent::trimRegionToContent($file, $page, $fracTop, $fracBottom, $padPt);
            }

            // Atas dibuat rapat ke border (putih minimal), bawah tetap
            // diberi napas untuk barcode.
            $padTop = 1.0;
            $padBottom = max(3.0, (float) $padPt);
            $newTopY = min($yTop, $topInk + 0.5 + $padTop);
            $newBottomY = max($yBottom, $botInk - 1.0 - $padBottom);

            if ($newTopY - $newBottomY < 10) {
                return [$fracTop, $fracBottom];
            }

            $newFracTop = 1 - ($newTopY / $pageH);
            $newFracBottom = 1 - ($newBottomY / $pageH);

            // Clamp ke region asal (tidak boleh melebar keluar).
            $newFracTop = max($fracTop, min($newFracTop, $fracBottom));
            $newFracBottom = max($newFracTop, min($newFracBottom, $fracBottom));

            return [(float) $newFracTop, (float) $newFracBottom];
        } catch (\Throwable $e) {
            return [$fracTop, $fracBottom];
        }
    }
}
