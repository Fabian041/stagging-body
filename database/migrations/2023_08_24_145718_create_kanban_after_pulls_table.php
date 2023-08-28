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
        Schema::create('kanban_after_pulls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('internal_part_id')->unsigned()->nullable(false);
            $table->foreign('internal_part_id')->references('id')->on('internal_parts')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string('code')->unique()->nullable(false);
            $table->string('npk')->nullable();
            $table->date('date')->nullable();
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
        Schema::dropIfExists('kanban_after_pulls');
    }
};
