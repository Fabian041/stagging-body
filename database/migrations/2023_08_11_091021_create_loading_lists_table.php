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
        Schema::create('loading_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number')->unique()->nullable();
            $table->string('pds_number')->nullable();
            $table->string('cycle')->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->bigInteger('customer_part_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('customer_part_id')->references('id')->on('customer_parts')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->integer('kanbann_qty')->nullable();
            $table->integer('qty_per_kanban')->nullable();
            $table->integer('total_qty')->nullable();
            $table->integer('actual_qty')->nullable();
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
        Schema::dropIfExists('loading_lists');
    }
};
