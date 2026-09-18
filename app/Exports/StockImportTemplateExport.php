<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StockImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $rows = DB::table('internal_parts')
            ->leftJoin('lines', 'lines.id', '=', 'internal_parts.line_id')
            ->leftJoin('production_stocks', 'production_stocks.internal_part_id', '=', 'internal_parts.id')
            ->select(
                DB::raw('COALESCE(lines.name, "NO LINE") as line_name'),
                'internal_parts.back_number',
                'internal_parts.part_number',
                'internal_parts.part_name',
                DB::raw('COALESCE(internal_parts.standard_stock, 0) as standard_stock'),
                DB::raw('COALESCE(production_stocks.current_stock, 0) as current_stock')
            )
            ->whereNotNull('internal_parts.back_number')
            ->orderBy('lines.name')
            ->orderBy('internal_parts.back_number')
            ->get()
            ->groupBy('line_name');

        $sheets = [];

        foreach ($rows as $lineName => $items) {
            $sheets[] = new StockImportTemplateLineSheet((string) $lineName, $items->values());
        }

        return $sheets ?: [new StockImportTemplateLineSheet('NO DATA', collect())];
    }
}
