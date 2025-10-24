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
        Schema::table('production_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('production_plans', 'plan_source')) {
                $table->enum('plan_source', ['external','static'])->nullable()->after('line');
                // kalau tak suka enum, boleh string(16) nullable
            }
            if (!Schema::hasColumn('production_plans', 'seq_no')) {
                $table->integer('seq_no')->nullable()->after('plan_source');
            }

            // indeks yang membantu query board:
            $table->index(['plan_date','line','seq_no'], 'pp_plan_line_seq_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_plans', function (Blueprint $table) {
            if (Schema::hasColumn('production_plans', 'seq_no')) {
                $table->dropIndex('pp_plan_line_seq_idx');
                $table->dropColumn('seq_no');
            }
            if (Schema::hasColumn('production_plans', 'plan_source')) {
                $table->dropColumn('plan_source');
            }
        });
    }
};
