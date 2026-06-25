<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `loading_lists`
                ADD INDEX `loading_lists_pds_number_idx` (`pds_number`),
                ADD INDEX `loading_lists_created_at_idx` (`created_at`),
                ADD INDEX `loading_lists_updated_at_idx` (`updated_at`),
                ADD INDEX `loading_lists_cycle_idx` (`cycle`),
                ADD INDEX `loading_lists_customer_delivery_idx` (`customer_id`, `delivery_date`),
                ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('loading_lists', function (Blueprint $table) {
            $table->index('pds_number', 'loading_lists_pds_number_idx');
            $table->index('created_at', 'loading_lists_created_at_idx');
            $table->index('updated_at', 'loading_lists_updated_at_idx');
            $table->index('cycle', 'loading_lists_cycle_idx');
            $table->index(['customer_id', 'delivery_date'], 'loading_lists_customer_delivery_idx');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `loading_lists`
                DROP INDEX `loading_lists_pds_number_idx`,
                DROP INDEX `loading_lists_created_at_idx`,
                DROP INDEX `loading_lists_updated_at_idx`,
                DROP INDEX `loading_lists_cycle_idx`,
                DROP INDEX `loading_lists_customer_delivery_idx`,
                ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('loading_lists', function (Blueprint $table) {
            $table->dropIndex('loading_lists_pds_number_idx');
            $table->dropIndex('loading_lists_created_at_idx');
            $table->dropIndex('loading_lists_updated_at_idx');
            $table->dropIndex('loading_lists_cycle_idx');
            $table->dropIndex('loading_lists_customer_delivery_idx');
        });
    }
};
