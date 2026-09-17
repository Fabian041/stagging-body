<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Mendeteksi area tiap kanban dalam satu halaman PDF.
 *
 * Format base: 1 halaman portrait berisi N kanban yang tersusun vertikal
 * (contoh: 3 kanban per halaman, dipisah garis putus-putus).
 *
 * Cara kerja:
 *  1. Ambil ukuran halaman asli (satuan = satuan dokumen FPDI, default mm).
 *  2. Hitung jumlah kanban di halaman tsb dari content stream PDF
 *     (menghitung marker teks seperti "UNIQUE NO", "MANIFEST NO", dst).
 *  3. Cari posisi potong: prioritas (a) tengah celah antara baris
 *     "Printed at Supplier" dan header "SUPPLIER" kanban berikutnya,
 *     (b) garis pemisah horizontal, (c) fallback potongan sama rata.
 *
 * @return array<int, array{x:float, y:float, width:float, height:float}>
 *         Koordinat dalam satuan dokumen FPDI (default mm), origin kiri-atas.
 */
class KanbanRegionDetector
{
    protected const MIN_KANBAN = 1;
    protected const MAX_KANBAN = 6;
    protected const DEFAULT_KANBAN = 3;

    protected const TRIM_MM = 2.0;

    protected const MIN_REGION_MM = 20.0;

    protected const MARKERS = [
        'UNIQUE NO',
        'SUPPLIER DATA',
        'MANIFEST NO',
        'CONVEYANCE NO',
        'PCS/KANBAN',
        'DOCK CODE',
        'Printed at Supplier',
    ];

    protected const SHORT_MARKERS = [
        'UNIQUE',
        'MANIFEST',
        'SUPPLIER',
        'CONVEYANCE',
        'KANBAN',
    ];


    public function detect(
        string $file,
        int $page,
        ?array $pageSize = null
    ): array {
        try {
            $size = $pageSize ?? $this->readPageSize($file, $page);

            if ($size === null) {
                return [];
            }

            $pageW = (float) ($size['width'] ?? 0);
            $pageH = (float) ($size['height'] ?? 0);

            if ($pageW <= 0 || $pageH <= 0) {
                return [];
            }

            $raw = @file_get_contents($file);

            $kanbanCount = self::DEFAULT_KANBAN;
            $cuts = [];
            $method = 'equal';

            if (is_string($raw) && $raw !== '') {
                $kanbanCount = $this->countKanbansOnPage($raw, $page);

                if ($pageW <= $pageH) {
                    $cuts = $this->findTextGapFractions($raw, $page, $kanbanCount);
                    if (! empty($cuts)) {
                        $method = 'text-gap';
                    } else {
                        // PDF kanban Toyota memakai custom font encoding sehingga
                        // teks tidak bisa di-decode ("SUPPLIER" terbaca garbled).
                        // Deteksi whitespace tahan encoding: pakai posisi Y semua
                        // teks + garis horizontal panjang, potong di tengah gap.
                        $ws = $this->findWhitespaceCutFractions($raw, $page, $kanbanCount);
                        if (! empty($ws)) {
                            $cuts = $ws;
                            $method = 'whitespace';
                        } else {
                        $dotted = $this->findDottedCutFractions($raw, $page, $kanbanCount);
                        if (! empty($dotted)) {
                            $cuts = $dotted;
                            $method = 'dotted';
                        } else {
                            $dash = $this->findDashedCutFractions($raw, $page, $kanbanCount);
                            if (! empty($dash)) {
                                $cuts = $dash;
                                $method = 'dash';
                            } else {
                                $cuts = $this->findCutFractions($raw, $page, $kanbanCount);
                                if (! empty($cuts)) {
                                    $method = 'line';
                                }
                            }
                        }
                        }
                    }
                }
            }

            $kanbanCount = max(static::MIN_KANBAN, min(static::MAX_KANBAN, $kanbanCount));

            if ($kanbanCount === 1) {
                return [
                    ['x' => 0.0, 'y' => 0.0, 'width' => $pageW, 'height' => $pageH],
                ];
            }

            if (empty($cuts)) {
                for ($i = 1; $i < $kanbanCount; $i++) {
                    $cuts[] = $i / $kanbanCount;
                }
            }

            logger()->info('Kanban detect', [
                'file' => basename($file),
                'page' => $page,
                'kanban' => $kanbanCount,
                'method' => $method,
                'cuts' => array_map(
                    static fn ($f) => round((float) $f, 4),
                    array_values($cuts)
                ),
            ]);

            if ($pageW > $pageH) {
                return $this->buildColumns($pageW, $pageH, $kanbanCount);
            }

            $cutsMm = array_map(
                static fn ($f) => (float) $f * $pageH,
                $cuts
            );

            $regions = $this->buildRows($pageW, $pageH, $cutsMm);

            foreach ($regions as $r) {
                if ($r['height'] < static::MIN_REGION_MM) {
                    $equal = [];
                    for ($i = 1; $i < $kanbanCount; $i++) {
                        $equal[] = ($i / $kanbanCount) * $pageH;
                    }

                    return $this->buildRows($pageW, $pageH, $equal);
                }
            }

            return $regions;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Cek apakah potongan vertikal benar-benar kosong (tidak ada teks).
     *
     * Dipakai splitter untuk membuang slice whitespace (mis. margin /
     * sisa dotted-line) agar tidak jadi halaman baru.
     *
     * @param float $fracTop    fraksi dari atas (0..1) tepi atas region.
     * @param float $fracBottom fraksi dari atas (0..1) tepi bawah region.
     */
    public function isRegionEmpty(string $file, int $page, float $fracTop, float $fracBottom): bool
    {
        try {
            if ($fracBottom <= $fracTop) {
                return true;
            }

            $raw = @file_get_contents($file);
            if (! is_string($raw) || $raw === '') {
                return false;
            }

            $objects = $this->parseObjects($raw);
            $pages = $this->orderedPageNumbers($objects);

            if (! isset($pages[$page - 1])) {
                return false;
            }

            $pageObj = (int) $pages[$page - 1];
            $content = $this->decodedPageContent($objects, $pageObj);

            if ($content === '') {
                return false;
            }

            $media = $this->readMediaBoxPt($objects, $pageObj);
            $pageH = (float) $media['height'];

            if ($pageH <= 0) {
                return false;
            }

            // Konversi fraksi-dari-atas ke Y PDF (origin bawah).
            $yTop = (1 - $fracTop) * $pageH;
            $yBottom = (1 - $fracBottom) * $pageH;

            // Inset 1pt supaya teks tepat di garis potong tidak dihitung
            // milik dua region sekaligus.
            $yTop -= 1.0;
            $yBottom += 1.0;

            if ($yTop <= $yBottom) {
                return true;
            }

            $items = $this->extractTextWithPositions($content);

            $n = 0;
            foreach ($items as $it) {
                $y = (float) $it['y'];
                if ($y >= $yBottom && $y <= $yTop) {
                    $n++;
                    if ($n >= 5) {
                        return false; // ada isi, bukan kosong
                    }
                }
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Press region ke bounding-box isi (auto-crop margin kertas kosong).
     *
     * Untuk tiap region (fraksi dari atas), cari baris teks pertama &
     * terakhir di dalamnya, lalu rapatkan tepi ke sana + padding.
     * Hasil: gap kertas kosong tinggi di atas/bawah (contoh halaman 1)
     * ikut terbuang, semua halaman output pas dengan konten.
     *
     * @return array{0:float, 1:float} [fracTop, fracBottom] baru.
     */
    public function trimRegionToContent(
        string $file,
        int $page,
        float $fracTop,
        float $fracBottom,
        float $padPt = 6.0
    ): array {
        try {
            if ($fracBottom <= $fracTop) {
                return [$fracTop, $fracBottom];
            }

            $raw = @file_get_contents($file);
            if (! is_string($raw) || $raw === '') {
                return [$fracTop, $fracBottom];
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
            $pageW = (float) $media['width'];
            $pageH = (float) $media['height'];

            if ($pageW <= 0 || $pageH <= 0) {
                return [$fracTop, $fracBottom];
            }

            $yTop = (1 - $fracTop) * $pageH;
            $yBottom = (1 - $fracBottom) * $pageH;

            $items = $this->extractTextWithPositions($content);

            $minY = null;
            $maxY = null;
            $minX = null;
            $maxX = null;
            $maxSize = 10.0;

            foreach ($items as $it) {
                $y = (float) ($it['y'] ?? 0);
                if ($y < $yBottom || $y > $yTop) {
                    continue;
                }
                $x = (float) ($it['x'] ?? 0);
                $s = (float) ($it['size'] ?? 10);

                $minY = $minY === null ? $y : min($minY, $y);
                $maxY = $maxY === null ? $y : max($maxY, $y);
                $minX = $minX === null ? $x : min($minX, $x);
                $maxX = $maxX === null ? $x : max($maxX, $x);
                $maxSize = max($maxSize, $s);
            }

            if ($minY === null || $maxY === null) {
                return [$fracTop, $fracBottom];
            }

            // Ekspansi untuk ascender/descender + border tabel + padding.
            $expandY = max(8.0, $maxSize * 0.5 + $padPt);
            $newTopY = min($yTop, $maxY + $expandY);
            $newBottomY = max($yBottom, $minY - $expandY);

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


    private function readPageSize(string $file, int $page): ?array
    {
        try {
            $pdf = new Fpdi();
            $count = $pdf->setSourceFile($file);

            if ($page < 1 || $page > $count) {
                return null;
            }

            $tpl = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($tpl);

            if (empty($size['width']) || empty($size['height'])) {
                return null;
            }

            return [
                'width' => (float) $size['width'],
                'height' => (float) $size['height'],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }


    protected function countKanbansOnPage(string $raw, int $page): int
    {
        $objects = $this->parseObjects($raw);
        $pages = $this->orderedPageNumbers($objects);
        $pageCount = count($pages) > 0 ? count($pages) : 1;

        $text = '';
        if (isset($pages[$page - 1])) {
            $text = $this->extractPageText($objects, (int) $pages[$page - 1]);
        }

        if ($text === '') {
            $text = $this->extractAllText($objects);

            if ($text === '') {
                return static::DEFAULT_KANBAN;
            }

            $perMarker = [];
            foreach (static::MARKERS as $marker) {
                $c = $this->countMarker($text, $marker);
                if ($c > 0) {
                    $perMarker[] = $c;
                }
            }

            if (empty($perMarker)) {
                return static::DEFAULT_KANBAN;
            }

            $total = $this->modeValue($perMarker);

            return max(
                static::MIN_KANBAN,
                min(static::MAX_KANBAN, (int) round($total / $pageCount))
            );
        }

        $counts = [];
        foreach (static::MARKERS as $marker) {
            $c = $this->countMarker($text, $marker);
            if ($c > 0 && $c <= static::MAX_KANBAN) {
                $counts[] = $c;
            }
        }

        if (empty($counts)) {
            foreach (static::SHORT_MARKERS as $marker) {
                $c = $this->countMarker($text, $marker);
                if ($c > 0 && $c <= static::MAX_KANBAN) {
                    $counts[] = $c;
                }
            }
        }

        if (empty($counts)) {
            return static::DEFAULT_KANBAN;
        }

        return max(
            static::MIN_KANBAN,
            min(static::MAX_KANBAN, $this->modeValue($counts))
        );
    }

    protected function modeValue(array $values): int
    {
        $freq = array_count_values(array_map('intval', $values));
        arsort($freq);

        return (int) array_key_first($freq);
    }

    protected function countMarker(string $haystack, string $marker): int
    {
        $h = strtoupper($haystack);
        $m = strtoupper($marker);

        $plain = substr_count($h, $m);

        $compactH = (string) preg_replace('/\s+/', '', $h);
        $compactM = (string) preg_replace('/\s+/', '', $m);
        $compact = $compactM !== '' ? substr_count($compactH, $compactM) : 0;

        return max($plain, $compact);
    }


    /**
     * Histogram tinta per bin Y 1pt (origin bawah). Bin >0 = ada teks,
     * segmen garis, rect, kurva, atau image yang melewatinya.
     *
     * PENTING: koordinat grafik ditransformasi CTM (q/Q/cm) seperti text
     * parser, karena barcode TCPDF digambar dalam sistem koordinat lokal
     * (cm scale/translate). Tanpa ini posisi Y barcode salah total dan
     * potongan jatuh di tengah barcode.
     *
     * @param array<int, array{x?:float, y?:float, size?:float, text?:string}> $items
     * @return array<int, int>
     */
    protected function buildInkHistogram(string $content, array $items, float $pageH): array
    {
        $n = max(1, (int) ceil($pageH));
        $ink = array_fill(0, $n + 1, 0);

        $mark = static function (float $a, float $b) use (&$ink, $n): void {
            $lo = max(0, (int) floor(min($a, $b)));
            $hi = min($n, (int) ceil(max($a, $b)));
            for ($k = $lo; $k <= $hi; $k++) {
                $ink[$k]++;
            }
        };

        foreach ($items as $it) {
            $y = (float) ($it['y'] ?? 0);
            $s = (float) ($it['size'] ?? 10);
            $mark($y - 2, $y + max(6.0, $s * 0.7));
        }

        // String literal/hex dibuang dulu supaya angka di dalam teks
        // (mis. "(2026/08/03)") tidak terbaca sebagai koordinat.
        $stripped = $this->stripStreamStrings($content);

        $rx = '/(-?\d+(?:\.\d+)?)|(\/[^\s<>\[\]()\/]+)|([A-Za-z]+\*?|\'|")/';

        if (! preg_match_all($rx, $stripped, $m, PREG_SET_ORDER)) {
            return $ink;
        }

        $ident = [1, 0, 0, 1, 0, 0];
        $ctm = $ident;
        $ctmStack = [];
        $stack = [];
        $cur = null; // [x, y] mentah (sebelum CTM)
        $inInlineImg = false;

        // Transformasi titik mentah -> user space, ambil Y-nya.
        $ty = static function (float $x, float $y) use (&$ctm): float {
            return $x * $ctm[1] + $y * $ctm[3] + $ctm[5];
        };

        foreach ($m as $tok) {
            $t = $tok[0];

            if ($inInlineImg) {
                if ($t === 'EI') {
                    $inInlineImg = false;
                    // Inline image menempati unit square [0,0]-[1,1].
                    $mark($ty(0, 0), $ty(1, 1));
                }

                continue;
            }

            if (is_numeric($t)) {
                $stack[] = (float) $t;

                continue;
            }

            if (str_starts_with($t, '/')) {
                continue;
            }

            switch ($t) {
                case 'q':
                    $ctmStack[] = $ctm;
                    $stack = [];
                    break;
                case 'Q':
                    $ctm = ! empty($ctmStack) ? array_pop($ctmStack) : $ident;
                    $stack = [];
                    break;
                case 'cm':
                    $o = $this->popNumbers($stack, 6);
                    if ($o !== null) {
                        $ctm = $this->mtxMul($o, $ctm);
                    }
                    break;
                case 'm':
                    $o = $this->popNumbers($stack, 2);
                    if ($o !== null) {
                        $cur = $o;
                    }
                    break;
                case 'l':
                    $o = $this->popNumbers($stack, 2);
                    if ($o !== null) {
                        if ($cur !== null) {
                            $mark($ty($cur[0], $cur[1]), $ty($o[0], $o[1]));
                        } else {
                            $mark($ty($o[0], $o[1]) - 1, $ty($o[0], $o[1]) + 1);
                        }
                        $cur = $o;
                    }
                    break;
                case 're':
                    $o = $this->popNumbers($stack, 4);
                    if ($o !== null) {
                        // 4 sudut -> rentang Y hasil transformasi.
                        $ys = [
                            $ty($o[0], $o[1]),
                            $ty($o[0] + $o[2], $o[1]),
                            $ty($o[0], $o[1] + $o[3]),
                            $ty($o[0] + $o[2], $o[1] + $o[3]),
                        ];
                        $mark(min($ys) - 1, max($ys) + 1);
                    }
                    break;
                case 'c':
                    $o = $this->popNumbers($stack, 6);
                    if ($o !== null) {
                        $ys = [$ty($o[0], $o[1]), $ty($o[2], $o[3]), $ty($o[4], $o[5])];
                        if ($cur !== null) {
                            $ys[] = $ty($cur[0], $cur[1]);
                        }
                        $mark(min($ys), max($ys));
                        $cur = [$o[4], $o[5]];
                    }
                    break;
                case 'v':
                case 'y':
                    $o = $this->popNumbers($stack, 4);
                    if ($o !== null) {
                        $ys = [$ty($o[0], $o[1]), $ty($o[2], $o[3])];
                        if ($cur !== null) {
                            $ys[] = $ty($cur[0], $cur[1]);
                        }
                        $mark(min($ys), max($ys));
                        $cur = [$o[2], $o[3]];
                    }
                    break;
                case 'h':
                    break;
                case 'BT':
                case 'ET':
                case 'Tf':
                case 'Tj':
                case 'TJ':
                case 'Tm':
                case 'Td':
                case 'TD':
                case 'T*':
                case 'TL':
                case 'Tc':
                case 'Tw':
                case 'Tz':
                case 'Tr':
                case 'Ts':
                case "'":
                case '"':
                    // Operator teks: buang operand sisa (mis. ukuran font)
                    // supaya tidak mencemari operand grafik berikutnya.
                    $stack = [];
                    break;
                case 'BI':
                    $inInlineImg = true;
                    $stack = [];
                    break;
                case 'ID':
                    $stack = [];
                    break;
                case 'Do':
                    // Image XObject digambar pada unit square yang
                    // diskalakan oleh cm sebelumnya (pola TCPDF standar).
                    $mark($ty(0, 0), $ty(1, 1));
                    $stack = [];
                    break;
                default:
                    break;
            }
        }

        return $ink;
    }

    /**
     * Potong di tengah whitespace horizontal (tahan custom font encoding).
     *
     * Masalah: PDF kanban Toyota memakai subset font dengan encoding custom,
     * sehingga string "(SUPPLIER)" terbaca sebagai glyph-id garbled dan
     * findTextGapFractions selalu gagal (tops=0/bottoms=0). Posisi Y teks
     * tetap valid meski decode gagal, jadi:
     *  1. Kumpulkan semua Y teks (garbled pun tidak masalah).
     *  2. Kumpulkan semua garis horizontal panjang (>50% lebar halaman,
     *     dari operator m..l maupun x y w h re).
     *  3. Untuk tiap posisi ideal i/N, cari garis panjang dalam window
     *     ±8% tinggi halaman yang paling terisolasi dari teks, lalu
     *     geser ke tengah gap teks terdekat (tempat garis putus-putus berada).
     *
     * @return array<int, float> fraksi 0..1 dari atas, terurut naik.
     */
    private function findWhitespaceCutFractions(string $raw, int $page, int $kanbanCount): array
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
        $pageW = (float) $media['width'];
        $pageH = (float) $media['height'];

        if ($pageW <= 0 || $pageH <= 0) {
            return [];
        }

        $items = $this->extractTextWithPositions($content);

        if (count($items) < 10) {
            return [];
        }

        $textYs = [];
        foreach ($items as $it) {
            $textYs[] = (float) $it['y'];
        }

        // Kelompokkan teks sebaris (toleransi 2pt) -> satu baris.
        // uniqueYs() me-return descending (atas dulu).
        $rows = $this->uniqueYs($textYs, 2.0);

        if (count($rows) < $kanbanCount + 1) {
            return [];
        }

        // Bangun daftar gap antar baris: gap = selisih Y dua baris
        // berurutan. Gap besar = whitespace kosong kiri-kanan (tidak ada
        // teks apa pun di rentang Y tersebut, sesuai permintaan).
        $gaps = []; // ['top'=>, 'bottom'=>, 'mid'=>, 'h'=>]
        for ($k = 0, $n = count($rows); $k < $n - 1; $k++) {
            $top = (float) $rows[$k];
            $bottom = (float) $rows[$k + 1];
            $h = $top - $bottom;
            if ($h < 5) {
                continue; // teks rapat dalam kanban, bukan pemisah
            }
            $mid = ($top + $bottom) / 2;
            if ($mid < 15 || $mid > $pageH - 15) {
                continue; // margin tepi halaman
            }
            $gaps[] = ['top' => $top, 'bottom' => $bottom, 'mid' => $mid, 'h' => $h];
        }

        if (empty($gaps)) {
            return [];
        }

        $fracs = [];
        $window = 0.10 * $pageH;

        for ($i = 1; $i < $kanbanCount; $i++) {
            $idealF = $i / $kanbanCount;
            $idealY = (1 - $idealF) * $pageH; // Y PDF (origin bawah)

            // Kandidat: gap yang mid-nya dalam window ideal.
            // Pilih gap TERBESAR (whitespace penuh kiri-kanan).
            // Seri (selisih <2pt): pilih yang paling dekat ideal.
            $best = null;
            foreach ($gaps as $g) {
                if (abs($g['mid'] - $idealY) > $window) {
                    continue;
                }
                if ($best === null
                    || $g['h'] > $best['h'] + 2.0
                    || (abs($g['h'] - $best['h']) <= 2.0
                        && abs($g['mid'] - $idealY) < abs($best['mid'] - $idealY))
                ) {
                    $best = $g;
                }
            }

            if ($best === null) {
                return [];
            }

            $fracs[] = 1 - ($best['mid'] / $pageH);
        }

        sort($fracs);

        // Validasi jarak antar potongan (minimal 15% tinggi halaman).
        $prev = 0.0;
        foreach (array_merge($fracs, [1.0]) as $f) {
            if (($f - $prev) < 0.15) {
                return [];
            }
            $prev = $f;
        }

        return array_values($fracs);
    }

    /** Kumpulkan Y garis horizontal panjang (>50% lebar halaman). */
    private function collectLongLineYs(string $content, float $pageW, float $pageH): array
    {
        $stripped = $this->stripStreamStrings($content);
        $ys = [];

        if (preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+m\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+l/',
            $stripped,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $s) {
                $y1 = (float) $s[2];
                $y2 = (float) $s[4];
                if (abs($y2 - $y1) > 1.5) {
                    continue;
                }
                $len = abs((float) $s[3] - (float) $s[1]);
                if ($len < 0.5 * $pageW) {
                    continue;
                }
                $ys[] = ($y1 + $y2) / 2;
            }
        }

        if (preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+re\b/',
            $stripped,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $s) {
                $w = (float) $s[3];
                $h = (float) $s[4];
                if ($w < 0.5 * $pageW) {
                    continue;
                }
                if ($h > 3) {
                    continue; // hanya sisi horizontal tipis
                }
                $y = (float) $s[2];
                $ys[] = $y;
                $ys[] = $y + $h;
            }
        }

        if (empty($ys)) {
            return [];
        }

        sort($ys);

        // Cluster Y berdekatan (<3pt) -> rata-rata.
        $clusters = [];
        $cur = [$ys[0]];
        for ($k = 1, $n = count($ys); $k < $n; $k++) {
            if ($ys[$k] - end($cur) < 3) {
                $cur[] = $ys[$k];
            } else {
                $clusters[] = array_sum($cur) / count($cur);
                $cur = [$ys[$k]];
            }
        }
        $clusters[] = array_sum($cur) / count($cur);

        // Buang tepi halaman (border luar), bukan pemisah.
        $out = [];
        foreach ($clusters as $y) {
            if ($y < 15 || $y > $pageH - 15) {
                continue;
            }
            $out[] = $y;
        }

        return array_values($out);
    }

    /** Jarak absolut ke baris teks terdekat. */
    private function nearestTextDistance(array $sortedYs, float $y): float
    {
        $best = PHP_FLOAT_MAX;
        foreach ($sortedYs as $ty) {
            $d = abs($ty - $y);
            if ($d < $best) {
                $best = $d;
            }
            if ($ty > $y && $d > $best) {
                break; // sorted: jarak hanya membesar setelah lewat
            }
        }

        return $best === PHP_FLOAT_MAX ? 0.0 : (float) $best;
    }

    /**
     * Tengah whitespace di sekitar garis: rata-rata teks tepat di bawah
     * dan tepat di atas garis. Jika satu sisi kosong, pakai garis itu sendiri.
     */
    private function midWhitespaceNear(array $sortedYs, float $lineY, float $pageH): float
    {
        $below = null; // Y teks terbesar yang < lineY
        $above = null; // Y teks terkecil yang > lineY

        foreach ($sortedYs as $ty) {
            if ($ty < $lineY - 0.5) {
                $below = $ty;
            } elseif ($ty > $lineY + 0.5) {
                $above = $ty;
                break;
            }
        }

        if ($below !== null && $above !== null) {
            $gap = $above - $below;
            // Gap wajar antar kanban 6..60pt; di luar itu pakai garis aslinya.
            if ($gap >= 4 && $gap <= 80) {
                return ($below + $above) / 2;
            }
        }

        return $lineY;
    }

    /**
     * Cari garis putus-putus horizontal (pemisah kanban).
     *
     * Dotted line = BANYAK segmen pendek di Y yang sama, bukan satu garis
     * panjang — jadi dinilai dari jumlah segmen + rentang X selebar halaman.
     * Hanya dipakai bila hasilnya pas N-1 (tanpa menebak jumlah kanban).
     *
     * @return array<int, float> fraksi 0..1 dari atas, terurut naik.
     */
    private function findDottedCutFractions(string $raw, int $page, int $kanbanCount): array
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
        $pageW = $media['width'];
        $pageH = $media['height'];

        $content = $this->stripStreamStrings($content);

        if (! preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+m\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+l/',
            $content,
            $m,
            PREG_SET_ORDER
        )) {
            $this->logOpsDiag($content, $page, $pageW, $pageH);

            return [];
        }

        // Bin 1pt: hitung segmen + rentang X per bin.
        $bins = [];
        foreach ($m as $s) {
            $y1 = (float) $s[2];
            $y2 = (float) $s[4];
            if (abs($y2 - $y1) > 1.0) {
                continue;
            }
            $x1 = (float) $s[1];
            $x2 = (float) $s[3];
            $len = abs($x2 - $x1);
            if ($len < 0.3 || $len > 15) {
                continue; // hanya titik/strip pendek
            }
            $bin = (int) round(($y1 + $y2) / 2);
            if (! isset($bins[$bin])) {
                $bins[$bin] = ['n' => 0, 'min' => $x1, 'max' => $x1];
            }
            $bins[$bin]['n']++;
            $bins[$bin]['min'] = min($bins[$bin]['min'], $x1, $x2);
            $bins[$bin]['max'] = max($bins[$bin]['max'], $x1, $x2);
        }

        $cands = [];
        foreach ($bins as $bin => $b) {
            $y = (float) $bin;
            if ($y < 10 || $y > $pageH - 10) {
                continue; // tepi halaman, bukan pemisah
            }
            if ($b['n'] < 8) {
                continue;
            }
            if (($b['max'] - $b['min']) < 0.6 * $pageW) {
                continue; // harus selebar halaman
            }
            $cands[$bin] = $b['n'];
        }

        if (count($cands) < $kanbanCount - 1) {
            $this->logOpsDiag($content, $page, $pageW, $pageH);

            return [];
        }

        // Gabungkan bin berdekatan (<3pt), simpan count terbesar.
        ksort($cands);
        $merged = [];
        $lastBin = null;
        foreach ($cands as $bin => $n) {
            if ($lastBin !== null && $bin - $lastBin < 3) {
                $merged[$lastBin] = max($merged[$lastBin], $n);

                continue;
            }
            $merged[$bin] = $n;
            $lastBin = $bin;
        }

        if (count($merged) < $kanbanCount - 1) {
            $this->logOpsDiag($content, $page, $pageW, $pageH);

            return [];
        }

        if (count($merged) > $kanbanCount - 1) {
            // Lebih dari cukup: ambil yang jumlah titiknya terbanyak.
            arsort($merged);
            $merged = array_slice($merged, 0, $kanbanCount - 1, true);
        }

        $fracs = array_map(
            static fn ($bin) => 1 - ($bin / $pageH),
            array_keys($merged)
        );
        sort($fracs);

        return array_values($fracs);
    }

    /** Buang string literal/hex agar tidak mengacaukan pencocokan operator. */
    private function stripStreamStrings(string $content): string
    {
        $out = preg_replace('/\((?:\\\\.|[^\\\\()])*\)/s', ' ', $content);
        $out = is_string($out) ? $out : $content;
        $out2 = preg_replace('/<[0-9A-Fa-f\s]*>/', ' ', $out);

        return is_string($out2) ? $out2 : $out;
    }

    /** Diagnostik sekali jalan: ringkasan operator vektor per halaman ke log. */
    private function logOpsDiag(string $content, int $page, float $pageW, float $pageH): void
    {
        try {
            $stripped = $this->stripStreamStrings($content);

            $segs = 0;
            $tiny = 0;
            $short = 0;
            $long = 0;
            $bins = [];
            if (preg_match_all(
                '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+m\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+l/',
                $stripped,
                $m,
                PREG_SET_ORDER
            )) {
                foreach ($m as $s) {
                    if (abs((float) $s[4] - (float) $s[2]) > 1.0) {
                        continue;
                    }
                    $segs++;
                    $len = abs((float) $s[3] - (float) $s[1]);
                    if ($len < 0.3) {
                        $tiny++;

                        continue;
                    }
                    if ($len > 15) {
                        $long++;

                        continue;
                    }
                    $short++;
                    $bin = (int) round((((float) $s[2] + (float) $s[4])) / 2);
                    $x1 = (float) $s[1];
                    $x2 = (float) $s[3];
                    if (! isset($bins[$bin])) {
                        $bins[$bin] = ['n' => 0, 'min' => $x1, 'max' => $x1];
                    }
                    $bins[$bin]['n']++;
                    $bins[$bin]['min'] = min($bins[$bin]['min'], $x1, $x2);
                    $bins[$bin]['max'] = max($bins[$bin]['max'], $x1, $x2);
                }
            }

            uasort($bins, static fn ($a, $b) => $b['n'] <=> $a['n']);
            $top = [];
            foreach (array_slice($bins, 0, 8, true) as $bin => $b) {
                $top[] = $bin.':'.$b['n'].':'.(int) round($b['max'] - $b['min']);
            }

            $re = preg_match_all('/\bre\b/', $stripped, $mm);
            $curves = preg_match_all('/\b[cyv]\b/', $stripped, $mm2);
            $dash = preg_match('/\[[^\]]*\]\s*[\d.\-]+\s+d\b/', $stripped);
            $do = preg_match_all('/\bDo\b/', $stripped, $mm3);
            $bi = preg_match_all('/\bBI\b/', $stripped, $mm4);

            logger()->info('Kanban ops diag', [
                'page' => $page,
                'bytes' => strlen($content),
                'segH' => $segs,
                'tiny' => $tiny,
                'short' => $short,
                'long' => $long,
                'topBins' => $top,
                're' => $re === false ? -1 : $re,
                'curves' => $curves === false ? -1 : $curves,
                'dash' => $dash,
                'do' => $do === false ? -1 : $do,
                'bi' => $bi === false ? -1 : $bi,
            ]);
        } catch (\Throwable $e) {
            // diagnostik tidak boleh menggagalkan proses
        }
    }

    /**
     * Cari separator berupa segmen PANJANG dengan pola dash aktif.
     * Status dash ([..] .. d) dilacak sesuai urutan kemunculan di stream;
     * segmen panjang + dashed + selebar halaman dianggap separator.
     *
     * @return array<int, float> fraksi 0..1 dari atas, terurut naik.
     */
    private function findDashedCutFractions(string $raw, int $page, int $kanbanCount): array
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

        $content = $this->stripStreamStrings($content);

        $media = $this->readMediaBoxPt($objects, $pageObj);
        $pageW = $media['width'];
        $pageH = $media['height'];

        // Status dash sesuai urutan kemunculan di stream.
        $dashAt = [];
        if (preg_match_all(
            '/\[([^\]]*)\]\s*-?\d+(?:\.\d+)?\s+d\b/',
            $content,
            $dm,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        )) {
            foreach ($dm as $d) {
                $dashAt[(int) $d[0][1]] = trim($d[1][0]) !== '';
                $dashArrays[(int) $d[0][1]] = trim($d[1][0]);
            }
        }

        if (empty($dashAt)) {
            return [];
        }
        ksort($dashAt);

        $isDashedAt = static function (int $off) use ($dashAt): bool {
            $state = false;
            foreach ($dashAt as $doff => $st) {
                if ($doff > $off) {
                    break;
                }
                $state = $st;
            }

            return $state;
        };

        if (! preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+m\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+l/',
            $content,
            $m,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        )) {
            return [];
        }

        $longYs = [];
        $bins = [];
        foreach ($m as $s) {
            $y1 = (float) $s[2][0];
            $y2 = (float) $s[4][0];
            if (abs($y2 - $y1) > 1.0) {
                continue;
            }
            if (! $isDashedAt((int) $s[0][1])) {
                continue;
            }
            $x1 = (float) $s[1][0];
            $x2 = (float) $s[3][0];
            $len = abs($x2 - $x1);
            $y = ($y1 + $y2) / 2;
            if ($len > 0.5 * $pageW) {
                $longYs[] = $y;

                continue;
            }
            if ($len < 0.3) {
                continue;
            }
            $bin = (int) round($y);
            if (! isset($bins[$bin])) {
                $bins[$bin] = ['n' => 0, 'min' => $x1, 'max' => $x1];
            }
            $bins[$bin]['n']++;
            $bins[$bin]['min'] = min($bins[$bin]['min'], $x1, $x2);
            $bins[$bin]['max'] = max($bins[$bin]['max'], $x1, $x2);
        }

        // Kandidat 1: segmen dashed panjang selebar halaman.
        sort($longYs);
        $mergedLong = [];
        foreach ($longYs as $y) {
            if (! empty($mergedLong) && abs($y - end($mergedLong)) < 3) {
                continue;
            }
            $mergedLong[] = $y;
        }

        $need = $kanbanCount - 1;

        logger()->info('Kanban dash diag', [
            'page' => $page,
            'dashArrays' => array_values(array_unique(array_values($dashArrays ?? []))),
            'longLines' => array_map(
                static fn ($y) => round(1 - ($y / $pageH), 4),
                $mergedLong
            ),
        ]);

        if (count($mergedLong) === $need) {
            $fracs = array_map(static fn ($y) => 1 - ($y / $pageH), $mergedLong);
            sort($fracs);

            return array_values($fracs);
        }

        if (count($mergedLong) > $need) {
            // Lebih dari cukup: ambil yang terdekat dengan posisi ideal.
            $picked = [];
            for ($i = 1; $i < $kanbanCount; $i++) {
                $ideal = $i / $kanbanCount;
                $best = null;
                $bestD = PHP_FLOAT_MAX;
                foreach ($mergedLong as $y) {
                    $f = 1 - ($y / $pageH);
                    if (in_array($f, $picked, true)) {
                        continue;
                    }
                    $d = abs($f - $ideal);
                    if ($d < $bestD) {
                        $bestD = $d;
                        $best = $f;
                    }
                }
                if ($best !== null) {
                    $picked[] = $best;
                }
            }
            sort($picked);

            return array_values($picked);
        }

        // Kandidat 2: strip dashed pendek yang merentang selebar halaman.
        $cands = [];
        foreach ($bins as $bin => $b) {
            $y = (float) $bin;
            if ($y < 10 || $y > $pageH - 10) {
                continue;
            }
            if ($b['n'] < 3) {
                continue;
            }
            if (($b['max'] - $b['min']) < 0.6 * $pageW) {
                continue;
            }
            $cands[$bin] = $b['n'];
        }

        if (count($cands) < $need) {
            return [];
        }

        ksort($cands);
        $merged = [];
        $lastBin = null;
        foreach ($cands as $bin => $n) {
            if ($lastBin !== null && $bin - $lastBin < 3) {
                $merged[$lastBin] = max($merged[$lastBin], $n);

                continue;
            }
            $merged[$bin] = $n;
            $lastBin = $bin;
        }

        if (count($merged) < $need) {
            return [];
        }

        if (count($merged) > $need) {
            arsort($merged);
            $merged = array_slice($merged, 0, $need, true);
        }

        $fracs = array_map(
            static fn ($bin) => 1 - ($bin / $pageH),
            array_keys($merged)
        );
        sort($fracs);

        return array_values($fracs);
    }

    /**
     * @return array<int, float> fraksi 0..1 dari atas, terurut naik.
     */
    private function findCutFractions(string $raw, int $page, int $kanbanCount): array
    {
        if ($kanbanCount <= 1) {
            return [];
        }

        $objects = $this->parseObjects($raw);
        $pages = $this->orderedPageNumbers($objects);

        if (! isset($pages[$page - 1])) {
            return [];
        }

        $content = $this->decodedPageContent($objects, (int) $pages[$page - 1]);

        if ($content === '') {
            return [];
        }

        $media = $this->readMediaBoxPt($objects, (int) $pages[$page - 1]);
        $pageW = $media['width'];
        $pageH = $media['height'];

        $segments = [];

        $content = $this->stripStreamStrings($content);

        if (preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+m\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+l/',
            $content,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $s) {
                $y1 = (float) $s[2];
                $y2 = (float) $s[4];
                if (abs($y2 - $y1) > 1.0) {
                    continue;
                }
                $len = abs((float) $s[3] - (float) $s[1]);
                if ($len > 1.5) {
                    $segments[] = ['y' => ($y1 + $y2) / 2, 'len' => $len];
                }
            }
        }

        // Sisi atas/bawah rect: "x y w h re".
        if (preg_match_all(
            '/(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+re\b/',
            $content,
            $m,
            PREG_SET_ORDER
        )) {
            foreach ($m as $s) {
                $w = (float) $s[3];
                if ($w < 1.5) {
                    continue;
                }
                $y = (float) $s[2];
                $h = (float) $s[4];
                $segments[] = ['y' => $y, 'len' => $w];
                $segments[] = ['y' => $y + $h, 'len' => $w];
            }
        }

        if (empty($segments)) {
            return [];
        }

        // Kelompokkan per bin 2pt; garis putus-putus akan menumpuk di bin yang sama.
        $bins = [];
        foreach ($segments as $sg) {
            $bin = (int) round($sg['y'] / 2);
            $bins[$bin] = ($bins[$bin] ?? 0) + $sg['len'];
        }

        $candidates = [];
        foreach ($bins as $bin => $total) {
            $y = $bin * 2;
            if ($y < 10 || $y > $pageH - 10) {
                continue; // tepi halaman, bukan pemisah
            }
            if ($total > 0.5 * $pageW) {
                $candidates[] = $y;
            }
        }

        if (empty($candidates)) {
            return [];
        }

        sort($candidates);

        // Gabungkan kandidat yang berdekatan (<4pt).
        $merged = [$candidates[0]];
        foreach (array_slice($candidates, 1) as $y) {
            if ($y - end($merged) < 4) {
                continue;
            }
            $merged[] = $y;
        }

        // PDF origin kiri-bawah -> fraksi dari atas.
        $fracs = array_map(
            static fn ($y) => 1 - ($y / $pageH),
            $merged
        );
        sort($fracs);

        if (count($fracs) === $kanbanCount - 1) {
            return array_values($fracs);
        }

        if (count($fracs) > $kanbanCount - 1) {
            // Ada garis ekstra (border tabel dsb): ambil yang terdekat
            // dengan posisi ideal sama-rata.
            $picked = [];
            for ($i = 1; $i < $kanbanCount; $i++) {
                $ideal = $i / $kanbanCount;
                $best = null;
                $bestD = PHP_FLOAT_MAX;
                foreach ($fracs as $f) {
                    if (in_array($f, $picked, true)) {
                        continue;
                    }
                    $d = abs($f - $ideal);
                    if ($d < $bestD) {
                        $bestD = $d;
                        $best = $f;
                    }
                }
                if ($best !== null) {
                    $picked[] = $best;
                }
            }
            sort($picked);

            return $picked;
        }

        return [];
    }


    /**
     * Cari posisi potong dari celah teks: titik tengah antara baris
     * "Printed at Supplier..." (kaki kanban) dan header "SUPPLIER"
     * (kepala kanban berikutnya). Hasilnya tepat di whitespace pemisah.
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
        // menjadi beberapa string, mis. (SUP)(PLIER).
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
        foreach (array_merge($items, $lines) as $c) {
            $t = strtoupper(trim((string) $c['text']));
            if ($t === '') {
                continue;
            }
            $tc = (string) preg_replace('/\s+/', '', $t);
            if (str_starts_with($t, 'PRINTED AT SUPPLIER') || str_starts_with($tc, 'PRINTEDATSUPPLIER')) {
                $bottoms[] = (float) $c['y'];
            } elseif ($t === 'SUPPLIER' || $tc === 'SUPPLIER') {
                $tops[] = (float) $c['y'];
            }
        }

        $bottoms = $this->uniqueYs($bottoms);
        $tops = $this->uniqueYs($tops);

        if (count($bottoms) !== $kanbanCount || count($tops) !== $kanbanCount) {
            logger()->info('Kanban text-gap skip', [
                'page' => $page,
                'kanban' => $kanbanCount,
                'items' => count($items),
                'tops' => count($tops),
                'bottoms' => count($bottoms),
                'sample' => array_slice(array_map(
                    static fn ($ln) => substr((string) $ln['text'], 0, 50),
                    $lines
                ), 0, 8),
            ]);

            return [];
        }

        for ($i = 0; $i < $kanbanCount; $i++) {
            // Header harus di atas kaki kanbannya sendiri.
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

    /** Kelompokkan posisi Y yang berdekatan (satu baris teks), urut dari atas. */
    protected function uniqueYs(array $ys, float $tol = 2.0): array
    {
        rsort($ys);
        $out = [];
        foreach ($ys as $y) {
            $y = (float) $y;
            if (empty($out) || abs($y - end($out)) > $tol) {
                $out[] = $y;
            }
        }

        return $out;
    }

    /**
     * Ekstrak string teks beserta posisi Y (user space PDF, origin kiri-bawah).
     *
     * Parser ringan: track CTM (q, Q, cm) dan text line matrix
     * (BT, ET, Tm, Td, TD, T-star, TL, Tf, Tj, TJ, quote, dquote).
     *
     * @return array<int, array{text:string, y:float, size:float}>
     */
    protected function extractTextWithPositions(string $content): array
    {
        $items = [];

        $rx = '/(<[0-9A-Fa-f\s]*>)|(\((?:\\\\.|[^\\\\()])*\))|(-?\d+(?:\.\d+)?)|(\/[^\s<>\[\]()\/]+)|(\[|\])|([A-Za-z]+\*?|\'|")/';

        if (! preg_match_all($rx, $content, $m, PREG_SET_ORDER)) {
            return [];
        }

        $ident = [1, 0, 0, 1, 0, 0];
        $ctm = $ident;
        $ctmStack = [];
        $stack = [];
        $inText = false;
        $tlm = $ident;
        $size = 10.0;
        $leading = 0.0;

        $record = static function (string $text) use (&$items, &$tlm, &$ctm, &$size): void {
            if ($text === '') {
                return;
            }
            $x = $tlm[4] * $ctm[0] + $tlm[5] * $ctm[2] + $ctm[4];
            $y = $tlm[4] * $ctm[1] + $tlm[5] * $ctm[3] + $ctm[5];
            $items[] = ['text' => $text, 'x' => $x, 'y' => $y, 'size' => $size];
        };

        foreach ($m as $tok) {
            $t = $tok[0];

            if (str_starts_with($t, '<')) {
                $stack[] = ['s' => $this->decodeHexBytes(substr($t, 1, -1))];

                continue;
            }

            if (str_starts_with($t, '(')) {
                $stack[] = ['s' => $this->decodeLiteral(substr($t, 1, -1))];

                continue;
            }

            if ($t === '[') {
                $stack[] = ['mark' => true];

                continue;
            }

            if ($t === ']') {
                $arr = [];
                while (! empty($stack)) {
                    $v = array_pop($stack);
                    if (is_array($v) && isset($v['mark'])) {
                        break;
                    }
                    $arr[] = $v;
                }
                $stack[] = ['a' => array_reverse($arr)];

                continue;
            }

            if (str_starts_with($t, '/')) {
                $stack[] = ['n' => $t];

                continue;
            }

            if (is_numeric($t)) {
                $stack[] = (float) $t;

                continue;
            }

            switch ($t) {
                case 'q':
                    $ctmStack[] = $ctm;
                    $stack = [];
                    break;
                case 'Q':
                    $ctm = ! empty($ctmStack) ? array_pop($ctmStack) : $ident;
                    $stack = [];
                    break;
                case 'cm':
                    $o = $this->popNumbers($stack, 6);
                    if ($o !== null) {
                        $ctm = $this->mtxMul($o, $ctm);
                    }
                    break;
                case 'BT':
                    $inText = true;
                    $tlm = $ident;
                    $stack = [];
                    break;
                case 'ET':
                    $inText = false;
                    $stack = [];
                    break;
                case 'Tm':
                    $o = $this->popNumbers($stack, 6);
                    if ($o !== null) {
                        $tlm = $o;
                    }
                    break;
                case 'Td':
                case 'TD':
                    $o = $this->popNumbers($stack, 2);
                    if ($o !== null) {
                        $tlm = $this->mtxMul([1, 0, 0, 1, $o[0], $o[1]], $tlm);
                        if ($t === 'TD') {
                            $leading = -$o[1];
                        }
                    }
                    break;
                case 'T*':
                    $tlm = $this->mtxMul([1, 0, 0, 1, 0, -$leading], $tlm);
                    break;
                case 'TL':
                    $v = array_pop($stack);
                    if (is_numeric($v)) {
                        $leading = (float) $v;
                    }
                    break;
                case 'Tf':
                    $v = array_pop($stack);
                    array_pop($stack);
                    if (is_numeric($v)) {
                        $size = (float) $v;
                    }
                    break;
                case 'Tj':
                    $v = array_pop($stack);
                    if (is_array($v) && isset($v['s'])) {
                        $record($v['s']);
                    }
                    break;
                case 'TJ':
                    $v = array_pop($stack);
                    if (is_array($v) && isset($v['a'])) {
                        foreach ($v['a'] as $el) {
                            if (is_array($el) && isset($el['s'])) {
                                $record($el['s']);
                            }
                        }
                    }
                    break;
                case "'":
                    $v = array_pop($stack);
                    $tlm = $this->mtxMul([1, 0, 0, 1, 0, -$leading], $tlm);
                    if (is_array($v) && isset($v['s'])) {
                        $record($v['s']);
                    }
                    break;
                case '"':
                    $str = array_pop($stack);
                    array_pop($stack);
                    array_pop($stack);
                    if (is_array($str) && isset($str['s'])) {
                        $record($str['s']);
                    }
                    break;
                case 'Tc':
                case 'Tw':
                case 'Tz':
                case 'Tr':
                case 'Ts':
                    array_pop($stack);
                    break;
                default:
                    break;
            }
        }

        return $items;
    }

    /** Ambil N angka terakhir dari stack (urutan semula). */
    private function popNumbers(array &$stack, int $n): ?array
    {
        if (count($stack) < $n) {
            return null;
        }
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $v = array_pop($stack);
            if (! is_numeric($v)) {
                return null;
            }
            $out[] = (float) $v;
        }

        return array_reverse($out);
    }

    /** Kalikan matriks affine 2D [a,b,c,d,e,f]: terapkan $m1 lalu $m2. */
    private function mtxMul(array $m1, array $m2): array
    {
        return [
            $m1[0] * $m2[0] + $m1[1] * $m2[2],
            $m1[0] * $m2[1] + $m1[1] * $m2[3],
            $m1[2] * $m2[0] + $m1[3] * $m2[2],
            $m1[2] * $m2[1] + $m1[3] * $m2[3],
            $m1[4] * $m2[0] + $m1[5] * $m2[2] + $m2[4],
            $m1[4] * $m2[1] + $m1[5] * $m2[3] + $m2[5],
        ];
    }

    private function decodeLiteral(string $inner): string
    {
        $out = preg_replace_callback(
            '/\\\\(?:([0-7]{1,3})|(.))/s',
            static function ($mm) {
                if ($mm[1] !== '') {
                    return chr((int) octdec($mm[1]));
                }

                return match ($mm[2]) {
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    'b' => "\x08",
                    'f' => "\x0C",
                    default => $mm[2],
                };
            },
            $inner
        );

        return is_string($out) ? $out : $inner;
    }

    private function decodeHexBytes(string $hex): string
    {
        $hex = (string) preg_replace('/\s+/', '', $hex);
        if ($hex === '') {
            return '';
        }
        if (strlen($hex) % 2 === 1) {
            $hex .= '0';
        }
        $bin = @pack('H*', $hex);
        if (! is_string($bin) || $bin === '') {
            return '';
        }
        if (strpos($bin, "\x00") !== false && function_exists('mb_convert_encoding')) {
            $conv = @mb_convert_encoding($bin, 'UTF-8', 'UTF-16BE');
            if (is_string($conv) && $conv !== '') {
                $bin = $conv;
            }
        }

        return $bin;
    }


    // -----------------------------------------------------------------
    // Bangun region
    // -----------------------------------------------------------------

    /**
     * @param array<int, float> $cutsMm posisi potong dari atas (mm).
     */
    private function buildRows(float $pageW, float $pageH, array $cutsMm): array
    {
        $bounds = array_merge([0.0], $cutsMm, [$pageH]);
        sort($bounds);

        $regions = [];
        $n = count($bounds) - 1;

        for ($i = 0; $i < $n; $i++) {
            $top = $bounds[$i] + ($i > 0 ? self::TRIM_MM / 2 : 0);
            $bottom = $bounds[$i + 1] - ($i < $n - 1 ? self::TRIM_MM / 2 : 0);

            if ($bottom <= $top) {
                $top = $bounds[$i];
                $bottom = $bounds[$i + 1];
            }

            $regions[] = [
                'x' => 0.0,
                'y' => $top,
                'width' => $pageW,
                'height' => $bottom - $top,
            ];
        }

        return $regions;
    }

    private function buildColumns(float $pageW, float $pageH, int $n): array
    {
        $regions = [];
        for ($i = 0; $i < $n; $i++) {
            $left = ($i / $n) * $pageW + ($i > 0 ? self::TRIM_MM / 2 : 0);
            $right = (($i + 1) / $n) * $pageW - ($i < $n - 1 ? self::TRIM_MM / 2 : 0);

            $regions[] = [
                'x' => $left,
                'y' => 0.0,
                'width' => $right - $left,
                'height' => $pageH,
            ];
        }

        return $regions;
    }


    // -----------------------------------------------------------------
    // Parsing PDF minimal (tanpa dependency tambahan)
    // -----------------------------------------------------------------

    /** @return array<int, string> objNum => isi object */
    protected function parseObjects(string $raw): array
    {
        $objects = [];

        if (preg_match_all('/(\d+)\s+\d+\s+obj(.*?)endobj/s', $raw, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $num = (int) $match[1];
                if (! isset($objects[$num])) {
                    $objects[$num] = $match[2];
                }
            }
        }

        return $objects;
    }

    /** Nomor object halaman sesuai urutan /Kids (index 0 = halaman 1). */
    protected function orderedPageNumbers(array $objects): array
    {
        foreach ($objects as $body) {
            if (preg_match('/\/Type\s*\/Pages\b/', $body)
                && preg_match('/\/Kids\s*\[(.*?)\]/s', $body, $k)
                && preg_match_all('/(\d+)\s+\d+\s+R/', $k[1], $refs)
            ) {
                return array_map('intval', $refs[1]);
            }
        }

        $pages = [];
        foreach ($objects as $num => $body) {
            if (preg_match('/\/Type\s*\/Page\b/', $body)) {
                $pages[] = $num;
            }
        }

        return $pages;
    }

    /** @return array<int, string> isi object stream /Contents halaman */
    private function pageContentStreams(array $objects, int $pageObjNum): array
    {
        if (! isset($objects[$pageObjNum])) {
            return [];
        }

        $body = $objects[$pageObjNum];
        $refs = [];

        if (preg_match('/\/Contents\s*\[(.*?)\]/s', $body, $m)) {
            preg_match_all('/(\d+)\s+\d+\s+R/', $m[1], $rm);
            $refs = array_map('intval', $rm[1]);
        } elseif (preg_match('/\/Contents\s+(\d+)\s+\d+\s+R/', $body, $m)) {
            $refs = [(int) $m[1]];
        }

        $streams = [];
        foreach ($refs as $r) {
            if (isset($objects[$r])) {
                $streams[] = $objects[$r];
            }
        }

        return $streams;
    }

    protected function decodedPageContent(array $objects, int $pageObjNum): string
    {
        $out = '';
        foreach ($this->pageContentStreams($objects, $pageObjNum) as $stream) {
            $out .= "\n" . $this->decodeStreamBody($stream);
        }

        return $out;
    }

    private function decodeStreamBody(string $objBody): string
    {
        if (! preg_match('/stream\r?\n?(.*?)endstream/s', $objBody, $m)) {
            return '';
        }

        $data = (string) preg_replace('/\r?\n$/', '', $m[1]);

        if (preg_match('/\/ASCII85Decode\b/', $objBody)) {
            $data = $this->ascii85Decode($data);
        }

        if (preg_match('/\/FlateDecode\b/', $objBody)) {
            $dec = @gzuncompress($data);
            if ($dec === false) {
                $dec = @gzinflate($data);
            }
            if ($dec !== false) {
                return $dec;
            }
        }

        return $data;
    }

    private function ascii85Decode(string $data): string
    {
        $data = trim($data);
        $data = (string) preg_replace('/^<~|~>$/', '', $data);
        $data = (string) preg_replace('/\s+/', '', $data);

        $out = '';
        $group = '';

        $flush = static function (string $chunk, bool $final) use (&$out): void {
            $len = strlen($chunk);
            if ($len === 0) {
                return;
            }
            $pad = 5 - $len;
            if ($final && $pad > 0) {
                $chunk .= str_repeat('u', $pad);
            } elseif ($len < 5) {
                return;
            }
            $value = 0;
            for ($i = 0; $i < 5; $i++) {
                $value = $value * 85 + (ord($chunk[$i]) - 33);
            }
            $bytes = pack('N', $value);
            $out .= $final ? substr($bytes, 0, 5 - $pad - 1) : $bytes;
        };

        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $c = $data[$i];
            if ($c === 'z') {
                if ($group !== '') {
                    return $data;
                }
                $out .= "\x00\x00\x00\x00";
                continue;
            }
            $group .= $c;
            if (strlen($group) === 5) {
                $flush($group, false);
                $group = '';
            }
        }
        $flush($group, true);

        return $out !== '' ? $out : $data;
    }

    protected function extractPageText(array $objects, int $pageObjNum): string
    {
        $text = '';
        foreach ($this->pageContentStreams($objects, $pageObjNum) as $stream) {
            $text .= ' ' . $this->extractTextFromContent($this->decodeStreamBody($stream));
        }

        return trim($text);
    }

    protected function extractAllText(array $objects): string
    {
        $text = '';
        foreach ($objects as $body) {
            if (strpos($body, 'stream') === false) {
                continue;
            }
            $decoded = $this->decodeStreamBody($body);
            if ($decoded !== '') {
                $text .= ' ' . $this->extractTextFromContent($decoded);
            }
        }

        return trim($text);
    }

    /** Ambil string literal (...) dan hex <...> dari content stream. */
    private function extractTextFromContent(string $content): string
    {
        $parts = [];

        if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $content, $m)) {
            foreach ($m[0] as $s) {
                $inner = substr($s, 1, -1);
                $inner = str_replace(
                    ['\\\\', '\\(', '\\)', '\\n', '\\r', '\\t'],
                    ['\\', '(', ')', "\n", "\r", "\t"],
                    $inner
                );
                $parts[] = $inner;
            }
        }

        if (preg_match_all('/<([0-9A-Fa-f\s]+)>/', $content, $m)) {
            foreach ($m[1] as $hex) {
                $hex = (string) preg_replace('/\s+/', '', $hex);
                if ($hex === '' || strlen($hex) < 4) {
                    continue;
                }
                if (strlen($hex) % 2 === 1) {
                    $hex .= '0';
                }
                $bin = @pack('H*', $hex);
                if (! is_string($bin) || $bin === '') {
                    continue;
                }
                if (strpos($bin, "\x00") !== false && function_exists('mb_convert_encoding')) {
                    $conv = @mb_convert_encoding($bin, 'UTF-8', 'UTF-16BE');
                    if (is_string($conv) && $conv !== '') {
                        $bin = $conv;
                    }
                }
                // Buang decode yang jelas bukan teks (banyak byte non-printable).
                $printable = (string) preg_replace('/[\x20-\x7E\x0A\x0D\x09]/', '', $bin);
                if (strlen($printable) > strlen($bin) / 2) {
                    continue;
                }
                $parts[] = $bin;
            }
        }

        return implode(' ', $parts);
    }

    /** MediaBox/CropBox halaman dalam point (origin kiri-bawah). */
    protected function readMediaBoxPt(array $objects, int $pageObjNum): array
    {
        $default = ['width' => 595.28, 'height' => 841.89];

        if (! isset($objects[$pageObjNum])) {
            return $default;
        }

        $body = $objects[$pageObjNum];

        foreach (['/CropBox', '/MediaBox'] as $key) {
            if (preg_match('/' . preg_quote($key, '/') . '\s*\[\s*([^\]]+)\]/', $body, $m)) {
                $nums = preg_split('/\s+/', trim($m[1]));
                if (count($nums) === 4) {
                    $w = (float) $nums[2] - (float) $nums[0];
                    $h = (float) $nums[3] - (float) $nums[1];
                    if ($w > 0 && $h > 0) {
                        return ['width' => $w, 'height' => $h];
                    }
                }
            }
        }

        return $default;
    }
}
