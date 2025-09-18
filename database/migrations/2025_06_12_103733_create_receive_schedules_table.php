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
        Schema::create('receive_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('supplier_id')->unsigned()->nullable(false);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->enum('day', ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
            $table->time('time');
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
        Schema::dropIfExists('receive_schedules');
    }
};
