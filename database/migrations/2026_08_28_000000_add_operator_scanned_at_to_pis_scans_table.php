<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pis_scans', 'operator_scanned_at')) {
            Schema::table('pis_scans', function (Blueprint $table) {
                $table->timestamp('operator_scanned_at')->nullable()->after('shipping_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pis_scans', 'operator_scanned_at')) {
            Schema::table('pis_scans', function (Blueprint $table) {
                $table->dropColumn('operator_scanned_at');
            });
        }
    }
};
