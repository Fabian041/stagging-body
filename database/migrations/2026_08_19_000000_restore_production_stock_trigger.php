<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('SET GLOBAL log_bin_trust_function_creators = 1');
        } catch (\Throwable $e) {
        }

        DB::unprepared('DROP TRIGGER IF EXISTS update_production_stock');
        DB::unprepared('CREATE TRIGGER update_production_stock AFTER INSERT ON mutations FOR EACH ROW
            BEGIN
                IF NEW.type = "supply" THEN
                    IF EXISTS (SELECT 1 FROM production_stocks WHERE internal_part_id = NEW.internal_part_id) THEN
                        UPDATE production_stocks SET current_stock = current_stock + NEW.qty
                        WHERE internal_part_id = NEW.internal_part_id;
                    ELSE
                        INSERT INTO production_stocks (internal_part_id, date, current_stock)
                        VALUES (NEW.internal_part_id, NEW.date, NEW.qty);
                    END IF;
                ELSEIF NEW.type = "checkout" THEN
                    IF EXISTS (SELECT 1 FROM production_stocks WHERE internal_part_id = NEW.internal_part_id) THEN
                        UPDATE production_stocks SET current_stock = current_stock - NEW.qty
                        WHERE internal_part_id = NEW.internal_part_id;
                    ELSE
                        INSERT INTO production_stocks (internal_part_id, date, current_stock)
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
