<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('cycle_name');
            $table->time('time');
            $table->timestamps();

            $table->unique(['customer_id', 'cycle_name'], 'master_cycles_unique_customer_cycle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_cycles');
    }
};
