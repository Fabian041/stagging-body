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
        Schema::create('loading_list_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('loading_list_id')->unsigned()->nullable();
            $table->bigInteger('customer_part_id')->unsigned()->nullable();
            $table->foreign('loading_list_id')->references('id')->on('loading_lists')->onDelete('CASCADE')->onUpdate('CASCADE')->nullable();
            $table->foreign('customer_part_id')->references('id')->on('customer_parts')->onDelete('CASCADE')->onUpdate('CASCADE')->nullable();
            $table->integer('kanban_qty')->nullable();
            $table->integer('qty_per_kanban')->nullable();
            $table->integer('total_qty')->nullable();
            $table->integer('actual_kanban_qty')->nullable();
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
        Schema::dropIfExists('loading_list_details');
    }
};
