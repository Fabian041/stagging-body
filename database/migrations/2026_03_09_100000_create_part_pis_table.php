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
        Schema::create('part_pis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('part_number');
            $table->string('part_number_ag')->nullable();
            $table->string('part_number_kanban')->nullable();
            $table->string('part_number_customer')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('customer_code_ag')->nullable();
            $table->string('part_kind', 10);
            $table->string('part_dock', 10);
            $table->string('back_number')->nullable();
            $table->double('qty_kanban', 8, 2);
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
        Schema::dropIfExists('part_pis');
    }
};
