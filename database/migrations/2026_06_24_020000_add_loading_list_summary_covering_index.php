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
                ADD INDEX `loading_lists_pds_summary_idx`
                    (`pds_number`, `customer_id`, `cycle`, `delivery_date`, `updated_at`),
                ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('loading_lists', function (Blueprint $table) {
            $table->index(
                ['pds_number', 'customer_id', 'cycle', 'delivery_date', 'updated_at'],
                'loading_lists_pds_summary_idx'
            );
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `loading_lists`
                DROP INDEX `loading_lists_pds_summary_idx`,
                ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('loading_lists', function (Blueprint $table) {
            $table->dropIndex('loading_lists_pds_summary_idx');
        });
    }
};
