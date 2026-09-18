<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quality_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_inspections', 'inspection_mode')) {
                $table->string('inspection_mode', 20)->default('SAMPLING')->after('check_type')->index();
            }

            if (!Schema::hasColumn('quality_inspections', 'total_box')) {
                $table->unsignedInteger('total_box')->nullable()->after('qty_per_box');
            }

            if (!Schema::hasColumn('quality_inspections', 'total_part')) {
                $table->unsignedInteger('total_part')->nullable()->after('total_box');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('quality_inspections', 'total_part')) {
                $table->dropColumn('total_part');
            }

            if (Schema::hasColumn('quality_inspections', 'total_box')) {
                $table->dropColumn('total_box');
            }

            if (Schema::hasColumn('quality_inspections', 'inspection_mode')) {
                $table->dropIndex(['inspection_mode']);
                $table->dropColumn('inspection_mode');
            }
        });
    }
};