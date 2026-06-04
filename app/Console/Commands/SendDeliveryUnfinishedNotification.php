<?php

namespace App\Console\Commands;

use App\Http\Controllers\LoadingListController;
use Illuminate\Console\Command;

class SendDeliveryUnfinishedNotification extends Command
{
    protected $signature = 'send:delivery-unfinished-notification';

    protected $description = 'Kirim peringatan WhatsApp untuk delivery yang belum selesai melewati Finish Preparation';

    public function handle(): int
    {
        $result = app(LoadingListController::class)->runScheduledDeliveryUnfinishedWaNotification();

        $this->info($result['message'] ?? 'Selesai.');

        if (! empty($result['detail'])) {
            $this->line($result['detail']);
        }

        return (int) ($result['exit_code'] ?? 0);
    }
}
