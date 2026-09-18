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
        Schema::create('quality_inspection_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_inspection_id')
                ->constrained('quality_inspections')
                ->cascadeOnDelete();
            $table->string('check_item', 150);
            $table->string('standard', 255)->nullable();
            $table->string('result', 10)->default('OK');
            $table->string('remark', 255)->nullable();
            $table->timestamps();

            $table->index(['result']);
            $table->index(['check_item']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspection_details');
    }
};