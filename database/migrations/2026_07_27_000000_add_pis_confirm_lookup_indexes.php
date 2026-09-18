<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pis_scans', function (Blueprint $table) {
            $table->index('loading_list_number', 'pis_scans_loading_list_number_idx');
            $table->index('pds_number', 'pis_scans_pds_number_idx');
        });

        Schema::table('pis_scan_details', function (Blueprint $table) {
            $table->index(
                ['pis_scan_id', 'part_number_int', 'part_number_cust'],
                'pis_scan_details_part_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('pis_scan_details', function (Blueprint $table) {
            $table->dropIndex('pis_scan_details_part_lookup_idx');
        });

        Schema::table('pis_scans', function (Blueprint $table) {
            $table->dropIndex('pis_scans_loading_list_number_idx');
            $table->dropIndex('pis_scans_pds_number_idx');
        });
    }
};
