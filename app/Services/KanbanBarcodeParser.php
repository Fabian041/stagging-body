<?php

namespace App\Services;

/**
 * Membaca barcode kanban internal cetakan plant.
 *
 * Tabel offset-nya mengikuti parser yang sudah lama dipakai halaman
 * production/index dan as523: panjang barcode yang menentukan layoutnya.
 * Dipindahkan ke sini supaya alur direct kanban tidak menyalin aturan yang sama
 * untuk kesekian kalinya.
 */
class KanbanBarcodeParser
{
    /** panjang barcode => [offset, panjang] tiap field */
    private const LAYOUTS = [
        218 => ['internal' => [41, 16], 'serial' => [123, 4], 'back' => [100, 4], 'pcs' => [196, 1]], // kanban suzuki
        219 => ['internal' => [42, 17], 'serial' => [124, 4], 'back' => [100, 4], 'pcs' => [196, 1]],
        220 => ['internal' => [35, 12], 'serial' => [130, 4], 'back' => [100, 4], 'pcs' => [196, 1]], // kanban buffer
        230 => ['internal' => [41, 19], 'serial' => [123, 4], 'back' => [100, 4], 'pcs' => [196, 1]], // kanban normal
        241 => ['internal' => [35, 12], 'serial' => [127, 4], 'back' => [100, 4], 'pcs' => [196, 1]], // kanban passtrough
    ];

    /**
     * Apakah panjang barcode ini termasuk layout kanban internal yang dikenal.
     */
    public function looksLikeKanban(string $raw): bool
    {
        return isset(self::LAYOUTS[strlen(trim($raw))]);
    }

    /**
     * @return array{part_number: string, serial: string|null, back_number: string|null, pcs: string|null, length: int}|null
     */
    public function parse(string $raw): ?array
    {
        $code   = trim($raw);
        $layout = self::LAYOUTS[strlen($code)] ?? null;

        if (! $layout) {
            return null;
        }

        $partNumber = $this->cut($code, $layout['internal']);

        if ($partNumber === '') {
            return null;
        }

        return [
            'part_number' => $partNumber,
            'serial'      => $this->cut($code, $layout['serial']) ?: null,
            'back_number' => $this->cut($code, $layout['back']) ?: null,
            'pcs'         => $this->cut($code, $layout['pcs']) ?: null,
            'length'      => strlen($code),
        ];
    }

    private function cut(string $code, array $spec): string
    {
        return strtoupper(trim(substr($code, $spec[0], $spec[1])));
    }
}
