<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StockImportSheet implements ToCollection
{
    public function __construct(
        private StockImport $import,
        private int $sheetNumber
    ) {}

    public function collection(Collection $rows): void
    {
        $this->import->importRows($rows, 'Sheet '.$this->sheetNumber);
    }
}
