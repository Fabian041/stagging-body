<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_alamat_chutes', function (Blueprint $table) {
            $table->id();
            $table->string('part_no_customer', 100);
            $table->string('back_no', 50)->default('');
            $table->string('part_name', 150)->default('');
            $table->string('alamat_chute', 100);
            $table->timestamps();

            $table->unique(
                ['part_no_customer', 'back_no', 'part_name'],
                'master_alamat_chutes_unique_part'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_alamat_chutes');
    }
};
