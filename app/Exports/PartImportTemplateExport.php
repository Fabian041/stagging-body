<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PartImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new PartImportDataSheet(),
            new PartImportLineListSheet(),
            new PartImportCustomerListSheet(),
        ];
    }
}
