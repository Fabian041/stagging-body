<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('part_scans', function (Blueprint $t) {
            $t->id();
            $t->string('line', 32);
            $t->string('model', 128);
            $t->string('dandori_board', 128)->nullable();
            $t->string('barcode', 26);      // alfanumerik
            $t->string('last4', 4);         // untuk laporan/filter cepat
            $t->date('scan_date');          // untuk unique per-hari

            // Letakkan kanban_id DI SINI agar “posisinya” setelah scanned_at nanti bisa diatur (lihat urutan)
            $t->timestamp('scanned_at')->useCurrent();
            $t->unsignedBigInteger('kanban_id')->nullable();

            $t->timestamps();

            $t->index(['line', 'model', 'scan_date', 'kanban_id']);
            $t->index('kanban_id'); // opsional; bisa dihapus kalau index gabungan sudah cukup
        });
    }

    public function down()
    {
        Schema::dropIfExists('part_scans');
    }
};
