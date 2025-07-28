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
        Schema::create('production_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('line');
            $table->string('customer');
            $table->string('dock');
            $table->integer('cycle');
            $table->string('back_no');
            $table->integer('order_qty');
            $table->integer('direct_pulling_qty')->default(0);
            $table->integer('stock_chute_qty')->default(0);
            $table->string('prod_time');
            $table->string('working_start');
            $table->string('working_end');
            $table->string('working_duration');
            $table->string('delivery_time');
            $table->string('balance_time');
            $table->string('dn_number')->nullable();
            $table->date('plan_date');
            $table->timestamps();
            
            $table->index(['plan_date', 'line']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_plans');
    }
};
