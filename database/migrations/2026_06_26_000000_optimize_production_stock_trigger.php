<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Trigger versi optimized: pakai NEW.type langsung (tanpa SELECT redundan ke `mutations`).
     * Sebelumnya di-recreate per-request lewat PullingController::ensureOptimizedTrigger(),
     * artinya DROP/CREATE TRIGGER (DDL) jalan di jalur scan → metadata lock di tabel `mutations`
     * yang hot → scan bisa stall sampai ~30 detik. Dipindah ke migration (sekali saat deploy).
     *
     * CREATE TRIGGER butuh SUPER atau log_bin_trust_function_creators=1 saat binary logging ON
     * (kalau tidak → error 1419). Migration ini defensif: kalau pembuatan tidak mungkin, ia
     * MELEWATI tanpa men-DROP trigger lama (biar tidak meninggalkan tabel tanpa trigger) dan
     * tanpa menggagalkan `migrate`.
     */
    public function up(): void
    {
        // best-effort: aktifkan kalau user punya hak (kalau tidak, abaikan)
        try {
            DB::statement('SET GLOBAL log_bin_trust_function_creators = 1');
        } catch (\Throwable $e) {
        }

        // pastikan CREATE TRIGGER memang bisa jalan sebelum men-DROP yang lama
        $binlogOn = false;
        $trustOn = false;
        try {
            $row = DB::selectOne('SELECT @@log_bin AS log_bin, @@log_bin_trust_function_creators AS trust');
            $binlogOn = (int) $row->log_bin === 1;
            $trustOn = (int) $row->trust === 1;
        } catch (\Throwable $e) {
        }

        if ($binlogOn && ! $trustOn) {
            logger()->warning(
                'Lewati trigger update_production_stock: butuh log_bin_trust_function_creators=1 atau SUPER. '
                .'Set di my.cnf ([mysqld] log_bin_trust_function_creators=1) lalu jalankan ulang migrate.'
            );

            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS update_production_stock');
        DB::unprepared('CREATE TRIGGER update_production_stock AFTER INSERT ON mutations FOR EACH ROW
            BEGIN
                IF NEW.type = "supply" THEN
                    IF EXISTS (SELECT 1 FROM production_stocks WHERE internal_part_id = NEW.internal_part_id) THEN
                        UPDATE production_stocks SET current_stock = current_stock + NEW.qty
                        WHERE internal_part_id = NEW.internal_part_id;
                    ELSE
                        INSERT INTO production_stocks (internal_part_id, DATE, current_stock)
                        VALUES (NEW.internal_part_id, NEW.date, NEW.qty);
                    END IF;
                ELSE
                    IF EXISTS (SELECT 1 FROM production_stocks WHERE internal_part_id = NEW.internal_part_id) THEN
                        UPDATE production_stocks SET current_stock = current_stock - NEW.qty
                        WHERE internal_part_id = NEW.internal_part_id;
                    ELSE
                        INSERT INTO production_stocks (internal_part_id, DATE, current_stock)
                        VALUES (NEW.internal_part_id, NEW.date, -NEW.qty);
                    END IF;
                END IF;
            END');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_production_stock');
    }
};
