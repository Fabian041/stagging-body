<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_manual_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            // Posisi garis merah master (fraksi 0-1 dari tinggi halaman), mis. [0.3333, 0.6667]
            $table->json('lines');
            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_manual_presets');
    }
};
