<?php

namespace App\Exports;

use App\Exports\Concerns\MergesTwoRowHeader;
use App\Models\Customer;
use App\Models\Line;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class PartImportDataSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    use MergesTwoRowHeader;
    public function title(): string
    {
        return 'YTB';
    }

    public function headings(): array
    {
        return [
            'No',
            'PART NAME',
            'PART NO CUSTOMER',
            'PART NO AIIA',
            'CUST BACK NO',
            'AIIA BACK NO',
            'Qty per kbn',
            'line name',
            'customer name',
        ];
    }

    public function array(): array
    {
        $sampleLine = Line::query()->orderBy('name')->value('name') ?? '';
        $sampleCustomer = Customer::query()->orderBy('name')->value('name') ?? '';

        return [
            ['', '', '', '', '', '', '', '', ''],
            [
                1,
                'HANDLE ASSY, ELECTRICAL KEY, ANTENNA, RH (HOLE)',
                '82081-74U00',
                '484170-13960',
                '',
                'SPD1',
                5,
                $sampleLine,
                $sampleCustomer,
            ],
        ];
    }

    private function applyListValidation($sheet, string $column, int $startRow, int $endRow, array $options): void
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle($options['errorTitle']);
        $validation->setError($options['error']);
        $validation->setPromptTitle($options['promptTitle']);
        $validation->setPrompt($options['prompt']);
        $validation->setFormula1($options['formula']);

        for ($row = $startRow; $row <= $endRow; $row++) {
            $sheet->getCell("{$column}{$row}")->setDataValidation(clone $validation);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->mergeHeaderRows($event, 'I');

                $lineCount = Line::count();
                $lastLineRow = max(3, $lineCount + 2);
                $customerCount = Customer::count();
                $lastCustomerRow = max(3, $customerCount + 2);

                $this->applyListValidation($sheet, 'H', 3, 1000, [
                    'errorTitle' => 'Line tidak valid',
                    'error' => 'Pilih line dari daftar pada sheet "Daftar Line".',
                    'promptTitle' => 'Line',
                    'prompt' => 'Pilih nama line dari dropdown.',
                    'formula' => "'Daftar Line'!\$C\$3:\$C\${$lastLineRow}",
                ]);

                $this->applyListValidation($sheet, 'I', 3, 1000, [
                    'errorTitle' => 'Customer tidak valid',
                    'error' => 'Pilih customer dari daftar pada sheet "Daftar Customer".',
                    'promptTitle' => 'Customer',
                    'prompt' => 'Pilih nama customer dari dropdown.',
                    'formula' => "'Daftar Customer'!\$C\$3:\$C\${$lastCustomerRow}",
                ]);
            },
        ];
    }
}
