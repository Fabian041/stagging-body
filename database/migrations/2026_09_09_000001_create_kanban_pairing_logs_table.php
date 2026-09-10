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
        Schema::create('kanban_pairing_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('body_kanban_pairing_id')->nullable();
            $table->unsignedBigInteger('assembly_internal_part_id')->nullable();
            $table->unsignedBigInteger('assembly_kanban_id')->nullable();
            $table->unsignedBigInteger('consumed_mutation_id')->nullable();
            $table->string('project')->nullable();
            $table->string('assembly_part');
            $table->string('assembly_serial_number', 64);
            $table->string('painting_part')->nullable();
            $table->text('painting_serial_numbers')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('paired_by', 64)->nullable();
            $table->timestamp('paired_at')->nullable();
            $table->string('consumed_by', 64)->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['assembly_kanban_id', 'status']);
            $table->index(['project', 'status']);
            $table->index('assembly_serial_number');
            $table->index('paired_at');
            $table->index('consumed_at');

            $table->foreign('body_kanban_pairing_id')
                ->references('id')
                ->on('body_kanban_pairings')
                ->onDelete('set null');
            $table->foreign('assembly_internal_part_id')
                ->references('id')
                ->on('internal_parts')
                ->onDelete('set null');
            $table->foreign('assembly_kanban_id')
                ->references('id')
                ->on('kanbans')
                ->onDelete('set null');
            $table->foreign('consumed_mutation_id')
                ->references('id')
                ->on('mutations')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kanban_pairing_logs');
    }
};
