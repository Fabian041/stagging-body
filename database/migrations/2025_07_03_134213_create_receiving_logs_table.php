<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiving_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pick_list');
            $table->string('supplier_code');
            $table->dateTime('expected_time');
            $table->integer('status')->default(0); // status saat melewati
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receiving_logs');
    }
};
