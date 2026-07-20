<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bikin trigger update_production_stock (versi optimized: pakai NEW.type langsung).
     *
     * Catatan: di server dengan binary logging ON, CREATE TRIGGER butuh
     * log_bin_trust_function_creators=1 (atau user SUPER). Karena user DB app
     * biasanya tanpa SUPER, AKTIFKAN dulu sekali sebagai root sebelum migrate:
     *     SET GLOBAL log_bin_trust_function_creators = 1;
     * lalu tulis permanen di my.cnf ([mysqld] log_bin_trust_function_creators=1).
     *
     * Migration ini defensif: kalau CREATE TRIGGER belum mungkin (binlog ON & trust OFF),
     * ia MELEWATI tanpa men-DROP trigger lama dan tanpa menggagalkan migrate.
     */
    public function up(): void
    {
        try {
            DB::statement('SET GLOBAL log_bin_trust_function_creators = 1');
        } catch (\Throwable $e) {
        }

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
                'Lewati trigger update_production_stock: aktifkan dulu `SET GLOBAL log_bin_trust_function_creators = 1` '
                .'(sebagai root) + tulis di my.cnf, lalu jalankan ulang migrate.'
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
