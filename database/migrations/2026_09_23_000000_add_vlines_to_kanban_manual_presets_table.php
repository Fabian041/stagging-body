<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kanban_manual_presets', function (Blueprint $table) {
            // Posisi garis BERDIRI master (fraksi 0-1 dari kiri), mis. [0.5]
            $table->json('vlines')->nullable()->after('lines');
        });
    }

    public function down(): void
    {
        Schema::table('kanban_manual_presets', function (Blueprint $table) {
            $table->dropColumn('vlines');
        });
    }
};
