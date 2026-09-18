<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_item_checks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('part_id');

            $table->string('item_check');
            $table->integer('jumlah_check')->nullable();

            $table->timestamps();

            $table->foreign('part_id')
                ->references('id')
                ->on('internal_parts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_item_checks');
    }
};