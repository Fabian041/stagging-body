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
        Schema::create('body_kanban_pairings', function (Blueprint $table) {
            $table->id();
            $table->string('project');
            $table->string('product');
            $table->string('part_name');
            $table->string('part_number_customer');
            $table->string('painting_part');
            $table->string('painting_back_number');
            $table->string('assembly_part');
            $table->string('assembly_back_number');
            $table->unsignedInteger('qty_painting')->default(1);
            $table->unsignedInteger('qty_assy')->default(1);
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
        Schema::dropIfExists('body_kanban_pairings');
    }
};
