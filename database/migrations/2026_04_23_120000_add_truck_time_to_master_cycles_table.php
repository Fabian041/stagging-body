<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_cycles', function (Blueprint $table) {
            $table->time('truck_time')->nullable()->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('master_cycles', function (Blueprint $table) {
            $table->dropColumn('truck_time');
        });
    }
};
