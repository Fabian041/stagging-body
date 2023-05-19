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
        Schema::create('parts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('line_id')->unsigned()->nullable();
            $table->foreign('line_id')->references('id')->on('parts');
            $table->string('part_number')->nullable();
            $table->string('part_name')->nullable();
            $table->string('line_id')->nullable();
            $table->integer('qty_per_box')->nullable();
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
        Schema::dropIfExists('parts');
    }
};
