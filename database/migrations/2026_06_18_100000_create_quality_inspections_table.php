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
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('kanban_code', 500);
            $table->string('check_type', 30);
            $table->string('line', 50)->nullable();
            $table->string('shift', 20)->nullable();
            $table->string('pic', 100)->nullable();
            $table->string('part_name', 150)->nullable();
            $table->string('part_number', 100)->nullable();
            $table->string('back_number', 100)->nullable();
            $table->string('qty_per_box', 50)->nullable();
            $table->string('judgement', 10)->default('OK');
            $table->string('approval_status', 30)->default('AUTO_RELEASE');
            $table->string('approved_by', 100)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('supervisor_note', 255)->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['checked_at']);
            $table->index(['judgement']);
            $table->index(['approval_status']);
            $table->index(['part_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
};