<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MutationPerLineSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
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
            'Adjustment',
            'Tanggal',
        ];
    }

    public function map($row): array
    {
        $this->no++;

        $rawType = strtoupper(trim((string) $row->type));
        $mutationDate = $row->mutation_date
            ? Date::dateTimeToExcel(new \DateTimeImmutable((string) $row->mutation_date))
            : null;

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
            ! empty($row->is_adjustment) ? 'YES' : 'NO',
            $mutationDate,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => 'yyyy-mm-dd hh:mm:ss',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(20);

        return [];
    }
}
