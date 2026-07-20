<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Template impor master alamat chute.
 * Format: Part no customer | Back No | Part name | Alamat Chute
 */
class MasterAlamatChuteTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'master_alamat_chutes';
    }

    public function headings(): array
    {
        return [
            'Part no customer',
            'Back No',
            'Part name',
            'Alamat Chute',
        ];
    }

    public function array(): array
    {
        return [];
    }
}
