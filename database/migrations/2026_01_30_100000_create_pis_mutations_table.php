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
        Schema::create('pis_mutations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('mutation_date');
            $table->string('mutation_code', 3)->nullable();
            $table->string('part_number', 50);
            $table->string('part_number_customer', 50)->nullable();
            $table->string('part_kind', 10)->nullable();
            $table->string('part_dock', 10)->nullable();
            $table->string('back_number', 255)->nullable();
            $table->string('store_location', 4)->nullable();
            $table->double('quantity', 15, 2);
            $table->string('uom_code', 10)->nullable();
            $table->integer('serial_no')->nullable();
            $table->string('loading_list', 50)->nullable();
            $table->string('delivery', 50)->nullable();
            $table->string('customer', 50)->nullable();
            $table->string('part_name', 150)->nullable();
            $table->string('npk', 10);
            $table->tinyInteger('flag_confirm')->default(0);
            $table->string('npk_edited', 10)->nullable();
            $table->string('info_edited', 255)->nullable();
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
        Schema::dropIfExists('pis_mutations');
    }
};
