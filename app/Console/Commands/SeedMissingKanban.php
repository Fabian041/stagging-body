<?php

namespace App\Console\Commands;

use App\Models\Kanban;
use App\Models\InternalPart;
use Illuminate\Console\Command;

class SeedMissingKanban extends Command
{
    protected $signature = 'kanban:seed-missing';
    protected $description = 'Seed kanban entries for internal parts that do not have any kanban yet';

public function handle()
{
    $target = 3000;
    $internalParts = InternalPart::all();

    foreach ($internalParts as $part) {
        $currentCount = Kanban::where('internal_part_id', $part->id)->count();

        if ($currentCount >= $target) {
            $this->info("👍 internal_part_id {$part->id} sudah {$currentCount}/{$target}");
            continue;
        }

        $maxSerial = Kanban::where('internal_part_id', $part->id)
            ->max('serial_number'); // contoh: '1000'

        $start = $maxSerial ? (int)$maxSerial + 1 : 1;
        $need  = $target - $currentCount;

        for ($i = $start; $i <= $target; $i++) {
            $formattedSerial = sprintf('%04d', $i);
            Kanban::create([
                'serial_number' => $formattedSerial,
                'internal_part_id' => $part->id,
            ]);
        }

        $this->info("✅ internal_part_id {$part->id}: tambah {$need} jadi {$target}");
    }
}
}
