<?php

namespace App\Console\Commands;

use App\Models\InternalPart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update photos based on part names';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        InternalPart::whereNull('photo')
            ->update([
                'photo' => DB::raw("CONCAT(back_number, '.jpeg')")
            ]);

        $this->info('Photos updated successfully!');
    }
}
