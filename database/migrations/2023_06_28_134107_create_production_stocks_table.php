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
        Schema::create('production_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('internal_part_id')->unsigned()->nullable();
            $table->foreign('internal_part_id')->references('id')->on('internal_parts')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('date');
            $table->integer('current_stock')->default(0);
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
        Schema::dropIfExists('production_stocks');
    }
};
