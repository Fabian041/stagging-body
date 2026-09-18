<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateProductionStock extends Command
{
    protected $signature = 'stock:recalc';

    protected $description = 'Hitung ulang production_stocks dari mutations (supply - checkout). Pengganti trigger update_production_stock.';

    public function handle(): int
    {
        // Net stok per part = jumlah supply dikurangi jumlah checkout.
        $net = "SUM(CASE WHEN type = 'supply' THEN COALESCE(qty, 0) "
            . "WHEN type = 'checkout' THEN -COALESCE(qty, 0) ELSE 0 END)";

        $aggregate = "SELECT internal_part_id, {$net} AS net "
            . "FROM mutations WHERE internal_part_id IS NOT NULL GROUP BY internal_part_id";

        DB::transaction(function () use ($aggregate) {
            // 1) Update part yang sudah punya baris stok.
            DB::statement("
                UPDATE production_stocks ps
                JOIN ({$aggregate}) m ON m.internal_part_id = ps.internal_part_id
                SET ps.current_stock = m.net, ps.updated_at = NOW()
            ");

            // 2) Insert part yang punya mutasi tapi belum ada baris stok.
            DB::statement("
                INSERT INTO production_stocks (internal_part_id, current_stock, date, created_at, updated_at)
                SELECT m.internal_part_id, m.net, NOW(), NOW(), NOW()
                FROM ({$aggregate}) m
                LEFT JOIN production_stocks ps ON ps.internal_part_id = m.internal_part_id
                WHERE ps.id IS NULL
            ");
        });

        $this->info('production_stocks berhasil dihitung ulang dari mutations.');

        return self::SUCCESS;
    }
}
