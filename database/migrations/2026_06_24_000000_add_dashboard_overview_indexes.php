<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->index(['type', 'date', 'internal_part_id'], 'mutations_type_date_part_idx');
        });

        Schema::table('pullings', function (Blueprint $table) {
            $table->index('pulling_date', 'pullings_pulling_date_idx');
        });

        Schema::table('pis_scan_logs', function (Blueprint $table) {
            $table->index('scan_time', 'pis_scan_logs_scan_time_idx');
        });

        Schema::table('error_logs', function (Blueprint $table) {
            $table->index('date', 'error_logs_date_idx');
        });

        Schema::table('loading_lists', function (Blueprint $table) {
            $table->index('delivery_date', 'loading_lists_delivery_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropIndex('mutations_type_date_part_idx');
        });

        Schema::table('pullings', function (Blueprint $table) {
            $table->dropIndex('pullings_pulling_date_idx');
        });

        Schema::table('pis_scan_logs', function (Blueprint $table) {
            $table->dropIndex('pis_scan_logs_scan_time_idx');
        });

        Schema::table('error_logs', function (Blueprint $table) {
            $table->dropIndex('error_logs_date_idx');
        });

        Schema::table('loading_lists', function (Blueprint $table) {
            $table->dropIndex('loading_lists_delivery_date_idx');
        });
    }
};
