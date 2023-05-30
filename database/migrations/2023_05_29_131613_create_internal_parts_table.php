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
        Schema::create('internal_parts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('line_id')->unsigned()->nullable();
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade')->onUpdate('cascade');
            $table->string('part_number')->nullable();
            $table->string('back_number')->nullable();
            $table->string('part_name')->nullable();
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
        Schema::dropIfExists('internal_parts');
    }
};
