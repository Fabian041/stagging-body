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
        $internalParts = InternalPart::all();
        $countCreated = 0;

        foreach ($internalParts as $part) {
            $exists = Kanban::where('internal_part_id', $part->id)->exists();

            if (!$exists) {
                for ($i = 1; $i <= 1000; $i++) {
                    $formattedSerial = sprintf('%04d', $i);
                    Kanban::create([
                        'serial_number' => $formattedSerial,
                        'internal_part_id' => $part->id,
                    ]);
                }

                $this->info("✅ Created kanban for internal_part_id: {$part->id}");
                $countCreated++;
            }
        }

        if ($countCreated === 0) {
            $this->info("👍 No missing kanban data. All internal parts already have kanban.");
        }
    }
}
