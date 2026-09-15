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
    private const MIN_KANBAN = 1;
    private const MAX_KANBAN = 6;
    private const DEFAULT_KANBAN = 3;

    private const TRIM_MM = 2.0;

    private const MIN_REGION_MM = 20.0;

    private const MARKERS = [
        'UNIQUE NO',
        'SUPPLIER DATA',
        'MANIFEST NO',
        'CONVEYANCE NO',
        'PCS/KANBAN',
        'DOCK CODE',
        'Printed at Supplier',
    ];

    private const SHORT_MARKERS = [
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
                        $cuts = $this->findCutFractions($raw, $page, $kanbanCount);
                        if (! empty($cuts)) {
                            $method = 'line';
                        }
                    }
                }
            }

            $kanbanCount = max(self::MIN_KANBAN, min(self::MAX_KANBAN, $kanbanCount));

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
                if ($r['height'] < self::MIN_REGION_MM) {
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


    private function countKanbansOnPage(string $raw, int $page): int
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
                return self::DEFAULT_KANBAN;
            }

            $perMarker = [];
            foreach (self::MARKERS as $marker) {
                $c = $this->countMarker($text, $marker);
                if ($c > 0) {
                    $perMarker[] = $c;
                }
            }

            if (empty($perMarker)) {
                return self::DEFAULT_KANBAN;
            }

            $total = $this->modeValue($perMarker);

            return max(
                self::MIN_KANBAN,
                min(self::MAX_KANBAN, (int) round($total / $pageCount))
            );
        }

        $counts = [];
        foreach (self::MARKERS as $marker) {
            $c = $this->countMarker($text, $marker);
            if ($c > 0 && $c <= self::MAX_KANBAN) {
                $counts[] = $c;
            }
        }

        if (empty($counts)) {
            foreach (self::SHORT_MARKERS as $marker) {
                $c = $this->countMarker($text, $marker);
                if ($c > 0 && $c <= self::MAX_KANBAN) {
                    $counts[] = $c;
                }
            }
        }

        if (empty($counts)) {
            return self::DEFAULT_KANBAN;
        }

        return max(
            self::MIN_KANBAN,
            min(self::MAX_KANBAN, $this->modeValue($counts))
        );
    }

    private function modeValue(array $values): int
    {
        $freq = array_count_values(array_map('intval', $values));
        arsort($freq);

        return (int) array_key_first($freq);
    }

]    private function countMarker(string $haystack, string $marker): int
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
    private function findTextGapFractions(string $raw, int $page, int $kanbanCount): array
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
    private function uniqueYs(array $ys, float $tol = 2.0): array
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
    private function extractTextWithPositions(string $content): array
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
            $y = $tlm[4] * $ctm[1] + $tlm[5] * $ctm[3] + $ctm[5];
            $items[] = ['text' => $text, 'y' => $y, 'size' => $size];
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
    private function parseObjects(string $raw): array
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
    private function orderedPageNumbers(array $objects): array
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

    private function decodedPageContent(array $objects, int $pageObjNum): string
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

    private function extractPageText(array $objects, int $pageObjNum): string
    {
        $text = '';
        foreach ($this->pageContentStreams($objects, $pageObjNum) as $stream) {
            $text .= ' ' . $this->extractTextFromContent($this->decodeStreamBody($stream));
        }

        return trim($text);
    }

    private function extractAllText(array $objects): string
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
    private function readMediaBoxPt(array $objects, int $pageObjNum): array
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
