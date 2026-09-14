<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Support\ConvertsCustomerPartNumber;

/**
 * Menerjemahkan barcode kanban customer menjadi part internal.
 *
 * Dipakai alur direct kanban: produksi men-scan kanban customer, bukan kanban
 * internal cetakan plant. Aturan potong dan konversi mengambil master `customers`
 * (char_first / char_length / char_total / serial_first / serial_length) — sumber
 * yang sama dengan yang dipakai halaman pulling, supaya tidak ada dua versi aturan.
 */
class CustomerKanbanResolver
{
    use ConvertsCustomerPartNumber;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Customer>|null */
    private $customers = null;

    /**
     * @param  string       $rawBarcode  isi barcode kanban customer apa adanya
     * @param  string|null  $lineName    nama line yang sedang jalan, dipakai sebagai
     *                                   pemutus kalau satu part number customer
     *                                   menunjuk ke lebih dari satu part internal
     * @return array{status: string, message?: string, data?: array, candidates?: array, internal_parts?: array}
     */
    public function resolve(string $rawBarcode, ?string $lineName = null): array
    {
        $raw = strtoupper(trim($rawBarcode));

        if ($raw === '') {
            return ['status' => 'invalid', 'message' => 'Barcode kosong.'];
        }

        [$candidates, $serial, $customerIds] = $this->buildCandidates($raw);

        $rows = CustomerPart::with(['internalPart.line', 'customer'])
            ->whereIn('part_number', $candidates)
            ->get()
            ->filter(function ($row) {
                return $row->internalPart !== null;
            });

        if ($rows->isEmpty()) {
            return [
                'status'     => 'not_found',
                'message'    => 'Kanban customer ' . $raw . ' tidak terdaftar di master part.',
                'candidates' => $candidates,
            ];
        }

        $byInternal = $rows->groupBy('internal_part_id');

        // Pemutus 1: customer yang aturan panjangnya cocok dengan barcode.
        if ($byInternal->count() > 1 && !empty($customerIds)) {
            $narrowed = $byInternal->filter(function ($group) use ($customerIds) {
                return $group->whereIn('customer_id', $customerIds)->isNotEmpty();
            });

            if ($narrowed->count() === 1) {
                $byInternal = $narrowed;
            }
        }

        // Pemutus 2: line yang sedang jalan.
        if ($byInternal->count() > 1 && $lineName) {
            $narrowed = $byInternal->filter(function ($group) use ($lineName) {
                $line = optional($group->first()->internalPart->line)->name;

                return $line !== null && strcasecmp($line, $lineName) === 0;
            });

            if ($narrowed->count() === 1) {
                $byInternal = $narrowed;
            }
        }

        if ($byInternal->count() > 1) {
            return [
                'status'         => 'ambiguous',
                'message'        => 'Kanban customer ' . $raw . ' menunjuk ke lebih dari satu part internal.',
                'internal_parts' => $byInternal->map(function ($group) {
                    return $group->first()->internalPart->part_number;
                })->values()->all(),
            ];
        }

        $group        = $byInternal->first();
        $customerPart = $group->whereIn('customer_id', $customerIds)->first() ?: $group->first();
        $internalPart = $customerPart->internalPart;

        return [
            'status' => 'ok',
            'data'   => [
                'raw'                  => $raw,
                'customer_part_number' => $customerPart->part_number,
                'customer_id'          => $customerPart->customer_id,
                'customer_name'        => optional($customerPart->customer)->name,
                'internal_part'        => $internalPart,
                'internal_part_id'     => $internalPart->id,
                'line'                 => optional($internalPart->line)->name,
                'qty_per_kanban'       => $customerPart->qty_per_kanban !== null
                    ? (int) $customerPart->qty_per_kanban
                    : null,
                'customer_serial'      => $serial,
            ],
        ];
    }

    /**
     * Susun semua kemungkinan bentuk part number customer dari satu barcode,
     * sekaligus ambil serial kalau master customer-nya memang punya aturan serial.
     *
     * @return array{0: array<int, string>, 1: string|null, 2: array<int, int>}
     */
    private function buildCandidates(string $raw): array
    {
        $length = strlen($raw);
        $plain  = str_replace(['-', ' '], '', $raw);

        $candidates  = [$raw, $plain];
        $serial      = null;
        $customerIds = [];

        foreach ($this->customers() as $customer) {
            $total = (int) $customer->char_total;

            // char_total = 0 berarti customer itu tidak memakai penjagaan panjang.
            if ($total > 0 && $total !== $length) {
                continue;
            }

            $charFirst  = (int) $customer->char_first;
            $charLength = (int) $customer->char_length;

            $cut = $charLength > 0 && $length >= $charFirst + $charLength
                ? substr($raw, $charFirst, $charLength)
                : $raw;

            $cut = strtoupper(trim(str_replace(['-', ' '], '', $cut)));

            if ($cut === '') {
                continue;
            }

            $customerIds[] = (int) $customer->id;
            $candidates[]  = $cut;
            $candidates[]  = $this->convertCustomerPartNumber($cut, $customer->id);

            if ($serial === null) {
                $serial = $this->extractSerial($raw, $customer);
            }
        }

        $candidates = array_values(array_unique(array_filter($candidates, function ($value) {
            return $value !== null && $value !== '';
        })));

        return [$candidates, $serial, array_values(array_unique($customerIds))];
    }

    private function extractSerial(string $raw, Customer $customer): ?string
    {
        $first  = $customer->serial_first;
        $length = $customer->serial_length;

        if ($first === null || $length === null || (int) $length <= 0) {
            return null;
        }

        if (strlen($raw) < (int) $first + (int) $length) {
            return null;
        }

        $serial = trim(substr($raw, (int) $first, (int) $length));

        return $serial !== '' ? $serial : null;
    }

    private function customers()
    {
        if ($this->customers === null) {
            $this->customers = Customer::all([
                'id', 'name', 'char_first', 'char_length', 'char_total', 'serial_first', 'serial_length',
            ]);
        }

        return $this->customers;
    }
}
