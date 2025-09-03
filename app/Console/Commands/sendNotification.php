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
            echo "Notifikasi untuk supplier: $existingLog->supplier_code\n";
            $name = $supplier ? $supplier->name : $existingLog->supplier_code;
            // $groupWa = env('GROUP_WHATSAPP_RECEIVING');
            // $groupWa = '6282111707754,6281220936456,6281234583065,628124885590,628111932178,6282260050066';
            $groupWa = '120363422623636452@g.us';
            if ($supplier && $supplier->area == 'unit') {
                // $groupWa = '081280613890';
                $area = 'UNIT';
            } else if ($supplier && $supplier->area == 'body') {
                // $groupWa = '081280613890';
                $area = 'BODY';
            } else {
                // $groupWa = '081280613890';
                $area = 'UNIT & BODY';
            }

            // Atau implementasikan logic email/telegram di sini

            $token = env('FASTWA_KEY');

            $message = "```---- ``` *Supplier Receiving Alert* ``` ----\n"
                . "Supplier Code  : $existingLog->supplier_code\n"
                . "Supplier Name  : $name\n"
                . "Kedatangan     : $existingLog->expected_time\n"
                . "Area           : $area\n"
                . "Status         : ``` *Delay Kedatangan* ```\n"
                . "------------------------------```";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://app.fastwa.com/api/v1/4D9AF7CE224B91C9CE14FFDDB55D248D/send_text',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'api_key=793D30579A77D4A0E12648872BFBB085&phone=' . $groupWa . '&message=' . $message,
            ));
            // dd($curl);
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
