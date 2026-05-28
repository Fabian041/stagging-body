<?php

namespace Database\Seeders;

use App\Models\MasterAlamatChute;
use Illuminate\Database\Seeder;

class MasterAlamatChuteSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'part_no_customer' => '692010K04000',
                'back_no' => 'MP21',
                'part_name' => 'FRAME SUB-ASSY, FR DOOR OUTSIDE HANDLE, LH',
                'alamat_chute' => 'TA-09',
            ],
        ];

        foreach ($rows as $row) {
            MasterAlamatChute::updateOrCreate(
                [
                    'part_no_customer' => $row['part_no_customer'],
                    'back_no' => $row['back_no'],
                    'part_name' => $row['part_name'],
                ],
                ['alamat_chute' => $row['alamat_chute']]
            );
        }
    }
}
