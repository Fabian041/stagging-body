<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExternalDeliveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:external-deliveries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data pengiriman dari SQL Server ke MySQL lokal';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('⏳ Memulai sinkronisasi data dari SQL Server...');

        try {
            $externalData = DB::connection('mssql_external')
                ->table('IA31NT as a')
                ->join('IA01NT as b', 'a.DEC_COD_BINID', '=', 'b.DEC_COD_BINID')
                ->select(
                    'a.CHR_COD_OMKCD as supplier_code',
                    'a.DEC_COD_BINID as flight_id',
                    'a.CHR_NUB_NYSJNO as pick_list',
                    'b.CHR_DAY_NYUD as delivery_date',
                    'b.CHR_TIM_BNTK as delivery_time',
                    'b.CHR_KUB_JSKK as status'
                )
                ->get();

            $jumlahInsert = 0;
            $jumlahUpdate = 0;

            foreach ($externalData as $row) {
                $existing = DB::table('external_deliveries')->where('flight_id', $row->flight_id)->first();

                if (!$existing) {
                    // Insert
                    DB::table('external_deliveries')->insert([
                        'supplier_code' => $row->supplier_code,
                        'flight_id' => $row->flight_id,
                        'pick_list' => $row->pick_list,
                        'delivery_date' => $row->delivery_date,
                        'delivery_time' => $row->delivery_time,
                        'status' => $row->status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $jumlahInsert++;
                } else {
                    // Update jika ada perubahan
                    if (
                        $existing->pick_list !== $row->pick_list ||
                        $existing->delivery_date !== $row->delivery_date ||
                        $existing->delivery_time !== $row->delivery_time ||
                        $existing->status !== $row->status
                    ) {
                        DB::table('external_deliveries')->where('flight_id', $row->flight_id)->update([
                            'supplier_code' => $row->supplier_code,
                            'pick_list' => $row->pick_list,
                            'delivery_date' => $row->delivery_date,
                            'delivery_time' => $row->delivery_time,
                            'status' => $row->status,
                            'updated_at' => now(),
                        ]);
                        $jumlahUpdate++;
                    }
                }
            }

            $this->info("✅ Sinkronisasi selesai.");
            $this->info("➕ Inserted: $jumlahInsert");
            $this->info("♻️ Updated : $jumlahUpdate");
        } catch (\Throwable $e) {
            $this->error("❌ Gagal sinkronisasi: " . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
