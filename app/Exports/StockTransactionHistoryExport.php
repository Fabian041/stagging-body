<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockTransactionHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    private int $number = 0;

    public function __construct(
        private object $part,
        private Collection $rows
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Back Number',
            'Part Number',
            'Tanggal',
            'Tipe',
            'Qty',
            'Serial Number',
            'Operator',
            'Adjustment',
            'Saldo Akhir',
        ];
    }

    public function map($row): array
    {
        return [
            ++$this->number,
            $this->part->back_number,
            $this->part->part_number,
            ! empty($row['date'])
                ? Date::dateTimeToExcel(new \DateTimeImmutable((string) $row['date']))
                : null,
            strtoupper((string) $row['direction']),
            (int) $row['qty'],
            $row['serial_number'] ?: '-',
            $row['npk'] ?: '-',
            $row['is_adjustment'] ? 'YES' : 'NO',
            (int) $row['balance_after'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => 'dd-mmm-yyyy hh:mm:ss',
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'F' => '#,##0',
            'J' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:J1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
