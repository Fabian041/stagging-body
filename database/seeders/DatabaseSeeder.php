<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Kanban;
use App\Models\InternalPart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // get all part number internal id
        $internalCount = InternalPart::count();
        $internalPart = InternalPart::select('id')->get();

        for($i= 0; $i < $internalCount; $i++){
            for($j= 1; $j<=1000; $j++){
                $formattedSerial = sprintf('%04d', $j);
                Kanban::create([
                    'serial_number' => $formattedSerial,
                    'internal_part_id' => $internalPart[$i]->id,
                ]);
            }
        }

        // $lines = [
        //     'AS524', 'AS501', 'AS523', 'AS526', 'AS546',
        //     'AS561', 'AS522', 'AS600', 'AS525', 'AS547',
        //     'AS548', 'AS711', 'AS528', 'AS549', 'AS731',
        //     'TORIMETRON', 'PASSTROUGH'
        // ];

        // foreach ($lines as $line) {
        //     DB::table('line_qty_temp')->insert([
        //         'line' => $line,
        //         'qty' => 0,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }

    }
}
