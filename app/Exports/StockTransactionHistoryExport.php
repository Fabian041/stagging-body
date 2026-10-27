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

    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Line',
            'Back Number',
            'Part Number',
            'Tanggal',
            'Waktu',
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
        $dateTime = ! empty($row['date'])
            ? Date::dateTimeToExcel(new \DateTimeImmutable((string) $row['date']))
            : null;

        return [
            ++$this->number,
            $row['line_name'],
            $row['back_number'],
            $row['part_number'],
            $dateTime !== null ? floor($dateTime) : null,
            $dateTime !== null ? $dateTime - floor($dateTime) : null,
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
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => 'dd-mmm-yyyy',
            'F' => 'hh:mm:ss',
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'H' => '#,##0',
            'L' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:L1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
