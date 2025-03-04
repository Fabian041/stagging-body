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
        Schema::create('skid_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('loading_list_detail_id')->unsigned()->nullable();
            $table->string('skid_no')->nullable();
            $table->string('item_no')->nullable();
            $table->string('serial')->nullable();
            $table->string('kanban_id')->nullable()->unique();
            $table->text('message')->nullable();
            $table->foreign('loading_list_detail_id')->references('id')->on('loading_list_details')->onDelete('CASCADE')->onUpdate('CASCADE');
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
        Schema::dropIfExists('skid_details');
    }
};
