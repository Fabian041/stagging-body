<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\ReceiveSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReceiveScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $schedules = [
            ['code' => 'SUGIURA', 'day' => 'wed', 'time' => '12:00'],
            ['code' => 'TTI', 'day' => 'mon', 'time' => '11:30'],
            ['code' => 'NSK', 'day' => 'mon', 'time' => '09:30'],
            ['code' => 'EKK', 'day' => 'wed', 'time' => '11:30'],
            ['code' => 'ARAI', 'day' => 'tue', 'time' => '11:30'],
            ['code' => 'ARAI', 'day' => 'thu', 'time' => '11:30'],
            ['code' => 'AOYAMA', 'day' => 'wed', 'time' => '00:30'],
            ['code' => 'AOYAMA', 'day' => 'fri', 'time' => '00:30'],
            ['code' => 'SUMIDEN', 'day' => 'tue', 'time' => '09:00'],
            ['code' => 'SUMIDEN', 'day' => 'fri', 'time' => '09:00'],
            ['code' => 'YAMANI', 'day' => 'fri', 'time' => '09:30'],
            ['code' => 'ITOKIN', 'day' => 'tue', 'time' => '00:30'],
            ['code' => 'NOK', 'day' => 'mon', 'time' => '12:00'],
            ['code' => 'RYOKO', 'day' => 'mon', 'time' => '21:00'],
            ['code' => 'NUSA_METAL', 'day' => 'mon', 'time' => '12:00'],
            ['code' => 'NUSA_METAL', 'day' => 'wed', 'time' => '12:00'],
            ['code' => 'NUSA_METAL', 'day' => 'fri', 'time' => '12:00'],
            ['code' => 'JTEKT', 'day' => 'mon', 'time' => '22:50'],
            ['code' => 'KANEMITSU', 'day' => 'mon', 'time' => '11:40'],
            ['code' => 'KANEMITSU', 'day' => 'mon', 'time' => '20:40'],
            ['code' => 'SINALUM', 'day' => 'mon', 'time' => '02:00'],
            ['code' => 'NAKAKIN', 'day' => 'mon', 'time' => '08:20'],
            ['code' => 'NAKAKIN', 'day' => 'mon', 'time' => '20:20'],
            ['code' => 'SHEITAI', 'day' => 'mon', 'time' => '06:40'],
            ['code' => 'SHEITAI', 'day' => 'mon', 'time' => '19:00'],
            ['code' => 'INGOT', 'day' => 'mon', 'time' => '07:00'],
        ];

        foreach ($schedules as $s) {
            $supplier = Supplier::where('code', $s['code'])->first();
            if ($supplier) {
                ReceiveSchedule::create([
                    'supplier_id' => $supplier->id,
                    'day' => strtolower($s['day']),
                    'time' => $s['time'],
                ]);
            }
        }
    }
}
