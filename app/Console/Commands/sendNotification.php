<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to suppliers about late deliveries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $existingLogs = DB::table('receiving_logs')
            ->where('status', '<', 3)
            ->whereNull('notified_at')
            ->get();
        foreach ($existingLogs as $existingLog) {

            $supplier = DB::table('suppliers')->where('code', $existingLog->supplier_code)->first();
            if (!$supplier) {
                return;
            }
            $groupWa = env('GROUP_WHATSAPP_RECEIVING');

            // Atau implementasikan logic email/telegram di sini

            $token = "v2n49drKeWNoRDN4jgqcdsR8a6bcochcmk6YphL6vLcCpRZdV1";
            $message = sprintf("```---- ``` *Supplier Receiving Alert* ``` ----%cSupplier Code  : $existingLog->supplier_code %cSupplier Name  : $supplier->name %cKedatangan     : $existingLog->expected_time %cStatus         : ``` *Delay Kedatangan* ``` %c------------------------------``` ", 10, 10, 10, 10, 10, 10);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://app.ruangwa.id/api/send_message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'token=' . $token . '&number=' . $groupWa . '&message=' . $message,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            sleep(10);


            // Sudah ada di log tapi belum dikirim notifikasi
            DB::table('receiving_logs')
                ->where('id', $existingLog->id)
                ->update([
                    'notified_at' => now(),
                    'updated_at' => now(),
                ]);
            echo $response;
        }
    }
}
