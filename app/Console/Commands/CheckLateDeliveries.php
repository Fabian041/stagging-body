<?php

namespace App\Console\Commands;

use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckLateDeliveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:late-deliveries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();

        $deliveries = DB::table('external_deliveries')
            ->where('status', '<', 2)
            ->get();
        foreach ($deliveries as $delivery) {

            $expectedTime = \Carbon\Carbon::parse($delivery->delivery_date . ' ' . substr($delivery->delivery_time, 3)); // hh:mm
            if ($expectedTime < $now) {
                $existingLog = DB::table('receiving_logs')
                    ->where('pick_list', $delivery->pick_list)
                    ->first();

                if (!$existingLog) {
                    // Pertama kali masuk log + kirim notifikasi
                    DB::table('receiving_logs')->insert([
                        'pick_list' => $delivery->pick_list,
                        'supplier_code' => $delivery->supplier_code,
                        'expected_time' => $expectedTime,
                        'status' => $delivery->status,
                        'notified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->sendNotification($delivery, $expectedTime);
                } elseif (is_null($existingLog->notified_at)) {
                    // dd('masih null');
                    // Sudah ada di log tapi belum dikirim notifikasi
                    DB::table('receiving_logs')
                        ->where('id', $existingLog->id)
                        ->update([
                            'notified_at' => now(),
                            'updated_at' => now(),
                        ]);

                    $this->sendNotification($delivery, $expectedTime);
                }
            }
        }

        $this->info('Cek keterlambatan selesai.');
    }

    protected function sendNotification($delivery, $expectedTime)
    {
        $supplier = DB::table('suppliers')->where('code', $delivery->supplier_code)->first();
        if (!$supplier) {
            return;
        }
        $groupWa = env('GROUP_WHATSAPP_RECEIVING');

        // Atau implementasikan logic email/telegram di sini

        $token = "v2n49drKeWNoRDN4jgqcdsR8a6bcochcmk6YphL6vLcCpRZdV1";
        $message = sprintf("```---- ``` *Supplier Receiving Alert* ``` ----%cSupplier Code  : $delivery->supplier_code %cSupplier Name  : $supplier->name %cKedatangan     : $expectedTime %cStatus         : ``` *Delay Kedatangan* ``` %c------------------------------``` ", 10, 10, 10, 10, 10, 10);
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
        echo $response;
    }
}
