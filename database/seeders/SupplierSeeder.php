<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            ['code' => 'SUGIURA', 'name' => 'SUGIURA', 'nname' => null, 'plant' => 'SURYA CIPTA', 'schedule_type' => 'custom'],
            ['code' => 'TTI', 'name' => 'TTI', 'nname' => null, 'plant' => 'MM2100', 'schedule_type' => 'custom'],
            ['code' => 'NSK', 'name' => 'NSK', 'nname' => null, 'plant' => 'DELTA SILICON', 'schedule_type' => 'custom'],
            ['code' => 'EKK', 'name' => 'EKK', 'nname' => null, 'plant' => 'MM2100', 'schedule_type' => 'custom'],
            ['code' => 'ARAI', 'name' => 'ARAI', 'nname' => null, 'plant' => 'ESTATE MANIS', 'schedule_type' => 'custom'],
            ['code' => 'AOYAMA', 'name' => 'AOYAMA', 'nname' => null, 'plant' => 'KIM', 'schedule_type' => 'custom'],
            ['code' => 'SUMIDEN', 'name' => 'SUMIDEN', 'nname' => null, 'plant' => 'DELTA SILICON', 'schedule_type' => 'custom'],
            ['code' => 'YAMANI', 'name' => 'YAMANI', 'nname' => null, 'plant' => 'EJIP', 'schedule_type' => 'custom'],
            ['code' => 'ITOKIN', 'name' => 'ITOKIN', 'nname' => null, 'plant' => 'KIM', 'schedule_type' => 'custom'],
            ['code' => 'NOK', 'name' => 'NOK', 'nname' => null, 'plant' => 'MM2100', 'schedule_type' => 'custom'],
            ['code' => 'RYOKO', 'name' => 'RYOKO', 'nname' => null, 'plant' => 'SEDANA GOLF', 'schedule_type' => 'custom'],
            ['code' => 'NUSA_METAL', 'name' => 'NUSA METAL', 'nname' => null, 'plant' => 'EJIP', 'schedule_type' => 'custom'],
            ['code' => 'JTEKT', 'name' => 'JTEKT', 'nname' => null, 'plant' => 'SURYA CIPTA', 'schedule_type' => 'daily'],
            ['code' => 'KANEMITSU', 'name' => 'KANEMITSU', 'nname' => null, 'plant' => 'JABABEKA', 'schedule_type' => 'daily_2x'],
            ['code' => 'SINALUM', 'name' => 'SINALUM', 'nname' => null, 'plant' => 'KIIC', 'schedule_type' => 'daily'],
            ['code' => 'NAKAKIN', 'name' => 'NAKAKIN', 'nname' => null, 'plant' => 'EJIP', 'schedule_type' => 'daily_2x'],
            ['code' => 'SHEITAI', 'name' => 'SHEITAI', 'nname' => null, 'plant' => 'MM2100', 'schedule_type' => 'daily_2x'],
            ['code' => 'INGOT', 'name' => 'INGOT (SAC,TTMI,AAA)', 'nname' => null, 'plant' => null, 'schedule_type' => 'custom'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
