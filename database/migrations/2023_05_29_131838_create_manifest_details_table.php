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
        Schema::create('manifest_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('manifest_id')->unsigned()->nullable();
            $table->bigInteger('customer_part_id')->unsigned()->nullable();
            $table->foreign('manifest_id')->references('id')->on('manifests')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('customer_part_id')->references('id')->on('customer_parts')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('kanban_qty')->nullable();
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
        Schema::dropIfExists('manifest_details');
    }
};
