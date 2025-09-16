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
        Schema::create('part_scans', function (Blueprint $t) {
            $t->id();
            $t->string('line', 32);
            $t->string('model', 128);
            $t->string('dandori_board', 128)->nullable();
            $t->string('barcode', 26);      // alfanumerik
            $t->string('last4', 4);         // untuk laporan/filter cepat
            $t->date('scan_date');          // <- untuk unique per-hari
            $t->timestamp('scanned_at')->useCurrent();
            $t->timestamps();

            $t->unsignedBigInteger('kanban_id')->nullable()->after('scanned_at');
            $t->index(['line','model','scan_date','kanban_id']);
            $t->index('kanban_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scanned_parts');
    }
};
