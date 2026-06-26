<?php

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait MergesTwoRowHeader
{
    protected function mergeHeaderRows(AfterSheet $event, string $lastColumn): void
    {
        $sheet = $event->sheet->getDelegate();

        foreach (range('A', $lastColumn) as $column) {
            $sheet->mergeCells("{$column}1:{$column}2");
        }

        $range = "A1:{$lastColumn}2";
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');
        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
