<?php

namespace App\Exports;

use App\Exports\Concerns\MergesTwoRowHeader;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class PartImportCustomerListSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    use MergesTwoRowHeader;

    public function title(): string
    {
        return 'Daftar Customer';
    }

    public function headings(): array
    {
        return [
            'No',
            'Code',
            'Name',
        ];
    }

    public function array(): array
    {
        $rows = Customer::query()
            ->orderBy('name')
            ->get(['code', 'name'])
            ->values()
            ->map(fn ($customer, $index) => [
                $index + 1,
                $customer->code,
                $customer->name,
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
