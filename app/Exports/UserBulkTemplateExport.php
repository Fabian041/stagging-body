<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class UserBulkTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(private array $roles)
    {
    }

    public function title(): string
    {
        return 'bulk_users';
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Username',
            'Password',
            'Role',
        ];
    }

    public function array(): array
    {
        return [
            ['Operator PPIC', 'operator.ppic@bella.local', '123456', '123456', 'ppic'],
            ['Operator Produksi', 'operator.prod@bella.local', '123457', '123456', 'prod'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Role tidak valid');
                $validation->setError('Role hanya boleh: '.implode(', ', $this->roles).'.');
                $validation->setPromptTitle('Role');
                $validation->setPrompt('Pilih salah satu role yang tersedia.');
                $validation->setFormula1('"'.implode(',', $this->roles).'"');

                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell("E{$row}")->setDataValidation(clone $validation);
                }
            },
        ];
    }
}
