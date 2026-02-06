<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MutationPerLineSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles
{
    protected int $no = 0;

    public function __construct(
        protected string $lineName,
        protected Collection $items
    ) {}

    public function title(): string
    {
        return mb_substr($this->lineName, 0, 31);
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'No',
            'Line',
            'Back Number',
            'Serial Number',
            'Qty',
            'Type',
            'Tanggal',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        $rawType = strtoupper(trim((string) $row->type));

        // mapping output Type (hasil export)
        if (in_array($rawType, ['IN', 'SUPPLY', 'S', 'ADD', 'PLUS', 'MASUK'])) {
            $exportType = 'SUPPLY';
        } elseif (in_array($rawType, ['OUT', 'CHECKOUT', 'C', 'SUB', 'MINUS', 'KELUAR'])) {
            $exportType = 'CHECKOUT';
        } else {
            // fallback kalau ada type lain (biar ketahuan di excel)
            $exportType = $rawType ?: '-';
        }

        return [
            $this->no,
            $row->line_name,
            $row->back_number,
            $row->serial_number,
            (int) $row->qty,
            $exportType,          // ✅ SUPPLY / CHECKOUT
            $row->mutation_date,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(20);

        return [];
    }
}
