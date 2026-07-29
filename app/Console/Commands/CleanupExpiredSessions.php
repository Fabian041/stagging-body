<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupExpiredSessions extends Command
{
    protected $signature = 'session:cleanup';

    protected $description = 'Delete expired sessions outside web requests';

    public function handle(): int
    {
        $lifetime = (int) config('session.lifetime') * 60;
        $deleted = app('session')->driver()->getHandler()->gc($lifetime);

        if ($deleted === false) {
            $this->error('Session cleanup failed.');

            return self::FAILURE;
        }

        $this->info("Deleted {$deleted} expired session(s).");

        return self::SUCCESS;
    }
}
