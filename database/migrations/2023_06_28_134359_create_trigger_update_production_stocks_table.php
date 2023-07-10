<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE TRIGGER update_production_stock AFTER INSERT ON mutations FOR EACH ROW
            BEGIN
                DECLARE type VARCHAR(255);
                SELECT type INTO type FROM mutations WHERE id = NEW.id;
            
                IF type = "supply" THEN
                    IF EXISTS (SELECT 1 FROM production_stocks WHERE internal_part_id = NEW.internal_part_id) THEN
                        UPDATE production_stocks SET current_stock = current_stock + NEW.qty 
                        WHERE internal_part_id = NEW.internal_part_id;
                    ELSE
                        INSERT INTO production_stocks (internal_part_id, DATE, current_stock) 
                        VALUES (NEW.internal_part_id, NEW.date ,NEW.qty);
                    END IF;
                ELSE
                    IF EXISTS (SELECT 1 FROM production_stocks WHERE internal_part_id = NEW.internal_part_id) THEN
                        UPDATE production_stocks SET current_stock = current_stock - NEW.qty 
                        WHERE internal_part_id = NEW.internal_part_id;
                    ELSE
                        INSERT INTO production_stocks (internal_part_id, DATE, current_stock) 
                        VALUES (NEW.internal_part_id, NEW.date ,-NEW.qty);
                    END IF;
                END IF;
            END'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_current_stock');
    }
};
