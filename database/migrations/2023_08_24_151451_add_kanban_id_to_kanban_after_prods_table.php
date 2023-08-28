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
        Schema::table('kanban_after_prods', function (Blueprint $table) {
            $table->bigInteger('kanban_id')->unsigned()->nullable()->after('id');
            $table->foreign('kanban_id')->references('id')->on('kanbans')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kanban_after_prods', function (Blueprint $table) {
            //
        });
    }
};
