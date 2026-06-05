<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_cycles', function (Blueprint $table) {
            $table->smallInteger('prep_day_offset')->default(0)->after('prep_end_time');
            $table->smallInteger('prep_end_day_offset')->default(0)->after('prep_day_offset');
        });
    }

    public function down(): void
    {
        Schema::table('master_cycles', function (Blueprint $table) {
            $table->dropColumn(['prep_day_offset', 'prep_end_day_offset']);
        });
    }
};
