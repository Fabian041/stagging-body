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
            ['name' => 'SUGIURA', 'day' => 'wed', 'time' => '12:00'],
            ['name' => 'TTI', 'day' => 'mon', 'time' => '11:30'],
            ['name' => 'NSK', 'day' => 'mon', 'time' => '09:30'],
            ['name' => 'EKK', 'day' => 'wed', 'time' => '11:30'],
            ['name' => 'ARAI', 'day' => 'tue', 'time' => '11:30'],
            ['name' => 'ARAI', 'day' => 'thu', 'time' => '11:30'],
            ['name' => 'AOYAMA', 'day' => 'wed', 'time' => '00:30'],
            ['name' => 'AOYAMA', 'day' => 'fri', 'time' => '00:30'],
            ['name' => 'SUMIDEN', 'day' => 'tue', 'time' => '09:00'],
            ['name' => 'SUMIDEN', 'day' => 'fri', 'time' => '09:00'],
            ['name' => 'YAMANI', 'day' => 'fri', 'time' => '09:30'],
            ['name' => 'ITOKIN', 'day' => 'tue', 'time' => '00:30'],
            ['name' => 'NOK', 'day' => 'mon', 'time' => '12:00'],
            ['name' => 'RYOKO', 'day' => 'mon', 'time' => '21:00'],
            ['name' => 'NUSA_METAL', 'day' => 'mon', 'time' => '12:00'],
            ['name' => 'NUSA_METAL', 'day' => 'wed', 'time' => '12:00'],
            ['name' => 'NUSA_METAL', 'day' => 'fri', 'time' => '12:00'],
            ['name' => 'JTEKT', 'day' => 'mon', 'time' => '22:50'],
            ['name' => 'KANEMITSU', 'day' => 'mon', 'time' => '11:40'],
            ['name' => 'KANEMITSU', 'day' => 'mon', 'time' => '20:40'],
            ['name' => 'SINALUM', 'day' => 'mon', 'time' => '02:00'],
            ['name' => 'NAKAKIN', 'day' => 'mon', 'time' => '08:20'],
            ['name' => 'NAKAKIN', 'day' => 'mon', 'time' => '20:20'],
            ['name' => 'SHEITAI', 'day' => 'mon', 'time' => '06:40'],
            ['name' => 'SHEITAI', 'day' => 'mon', 'time' => '19:00'],
            ['name' => 'INGOT', 'day' => 'mon', 'time' => '07:00'],
        ];

        foreach ($schedules as $s) {
            $supplier = Supplier::where('name', $s['name'])->first();
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
