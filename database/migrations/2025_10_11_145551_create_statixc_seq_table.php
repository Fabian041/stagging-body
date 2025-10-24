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
        Schema::create('line_static_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('line', 16);                    // <— kolomnya 'line'
            $table->string('back_no', 32);
            $table->unsignedInteger('seq_no');
            $table->enum('dock_hint', ['DOM','EXP','STR','6I'])->nullable();
            $table->unsignedInteger('default_order_qty')->nullable();
            $table->time('default_prod_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['line','seq_no']);
            $table->index(['line']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_static_sequences');
    }
};
