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
}
