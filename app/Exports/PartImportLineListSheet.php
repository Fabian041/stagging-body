<?php

namespace App\Exports;

use App\Exports\Concerns\MergesTwoRowHeader;
use App\Models\Line;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PartImportLineListSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    use MergesTwoRowHeader;

    public function title(): string
    {
        return 'Daftar Line';
    }

    public function headings(): array
    {
        return [
            'No',
            'ID',
            'Name',
        ];
    }

    public function array(): array
    {
        $rows = Line::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values()
            ->map(fn ($line, $index) => [
                $index + 1,
                $line->id,
                $line->name,
            ])
            ->all();

        return array_merge([['', '', '']], $rows);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn (AfterSheet $event) => $this->mergeHeaderRows($event, 'C'),
        ];
    }
}
