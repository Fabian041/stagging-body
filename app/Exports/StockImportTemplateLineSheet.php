<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockImportTemplateLineSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private string $lineName,
        private Collection $rows
    ) {}

    public function title(): string
    {
        $title = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $this->lineName) ?: 'LINE';
        $title = trim(preg_replace('/\s+/', ' ', $title));

        return mb_substr($title !== '' ? $title : 'LINE', 0, 31);
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Line',
            'Back Number',
            'Part Number',
            'Part Name',
            'Standard Stock',
            'Current Stock',
        ];
    }

    public function map($row): array
    {
        return [
            $row->line_name,
            $row->back_number,
            $row->part_number,
            $row->part_name,
            (int) $row->standard_stock,
            (int) $row->current_stock,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        return [];
    }
}
