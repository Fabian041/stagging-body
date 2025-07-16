<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate production plan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
