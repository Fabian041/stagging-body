<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Stok kini dikelola command terjadwal `stock:recalc` (bukan trigger),
        // supaya transaksi scan cukup INSERT mutation (tanpa lock baris stok → tanpa deadlock).
        DB::unprepared('DROP TRIGGER IF EXISTS update_production_stock');
    }

    public function down(): void
    {
        // Sengaja no-op. Mengembalikan trigger akan memunculkan lagi masalah
        // deadlock/privilege (1419). Bila benar-benar perlu, buat manual.
    }
};
