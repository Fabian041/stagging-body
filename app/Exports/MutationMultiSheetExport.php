<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MutationMultiSheetExport implements WithMultipleSheets
{
    public function __construct(protected Collection $rows) {}

    public function sheets(): array
    {
        $grouped = $this->rows->groupBy('line_name');

        $sheets = [];
        foreach ($grouped as $lineName => $items) {
            $sheets[] = new MutationPerLineSheet($lineName, $items->values());
        }

        // kalau tidak ada data sama sekali, tetap bikin 1 sheet biar file tidak error
        if (empty($sheets)) {
            $sheets[] = new MutationPerLineSheet('NO_DATA', collect());
        }

        return $sheets;
    }
}
