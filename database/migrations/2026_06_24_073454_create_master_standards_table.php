<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_standards', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_check_id');

            $table->string('standard')->nullable();

            $table->decimal('min_value',10,2)->nullable();
            $table->decimal('max_value',10,2)->nullable();

            $table->timestamps();

            $table->foreign('item_check_id')
                ->references('id')
                ->on('master_item_checks')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_standards');
    }
};