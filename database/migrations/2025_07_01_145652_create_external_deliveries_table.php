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
        Schema::create('external_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code'); // CHR_COD_OMKCD
            $table->string('flight_id')->unique(); // DEC_COD_BINID (unik agar bisa digunakan untuk updateOrInsert)
            $table->string('pick_list')->nullable(); // CHR_NUB_NYSJNO
            $table->date('delivery_date')->nullable(); // CHR_DAY_NYUD
            $table->time('delivery_time')->nullable(); // CHR_TIM_BNTK
            $table->unsignedTinyInteger('status')->default(0); // CHR_KUB_JSKK (0-5)
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
        Schema::dropIfExists('external_deliveries');
    }
};
