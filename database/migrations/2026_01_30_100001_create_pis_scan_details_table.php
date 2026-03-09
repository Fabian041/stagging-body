<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pis_scan_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('pis_scan_id')->unsigned()->nullable();
            $table->foreign('pis_scan_id')->references('id')->on('pis_scans')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string('part_number_int')->nullable();
            $table->string('part_number_cust')->nullable();
            $table->integer('target_qty')->nullable()->default(0);
            $table->integer('scanned_qty')->nullable()->default(0);
            $table->integer('remaining_qty')->nullable()->default(0);
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
        Schema::dropIfExists('pis_scan_details');
    }
};
