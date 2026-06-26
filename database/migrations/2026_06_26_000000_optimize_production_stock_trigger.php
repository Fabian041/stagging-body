<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Trigger versi optimized: pakai NEW.type langsung (tanpa SELECT redundan ke `mutations`).
     * Sebelumnya di-recreate per-request lewat PullingController::ensureOptimizedTrigger(),
     * artinya DROP/CREATE TRIGGER (DDL) jalan di jalur scan. DDL butuh metadata lock di tabel
     * `mutations` yang sangat hot → saat flag cache dingin, scan bisa stall sampai ~30 detik.
     * Dipindah ke migration biar dijalankan sekali saat deploy, bukan di request user.
     */
    public function up(): void
    {
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
