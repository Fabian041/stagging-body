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
        Schema::create('customer_parts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('internal_part_id')->unsigned()->nullable();
            $table->foreign('internal_part_id')->references('id')->on('internal_parts')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('cascade');
            $table->string('part_number')->nullable();
            $table->string('back_number')->nullable();
            $table->integer('qty_per_kanban')->nullable();
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
        Schema::dropIfExists('customer_parts');
    }
};
