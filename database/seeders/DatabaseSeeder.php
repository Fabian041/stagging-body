<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Kanban;
use App\Models\InternalPart;
use Illuminate\Database\Seeder;

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

    }
}
