<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Config posisi serial pada barcode kanban customer (per customer, absolut).
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'serial_first')) {
                $table->integer('serial_first')->nullable()->after('char_total');
            }
            if (! Schema::hasColumn('customers', 'serial_length')) {
                $table->integer('serial_length')->nullable()->after('serial_first');
            }
        });

        // Pairing: serial kanban customer disandingkan dgn serial internal di record checkout.
        Schema::table('mutations', function (Blueprint $table) {
            if (! Schema::hasColumn('mutations', 'customer_serial')) {
                $table->string('customer_serial', 32)->nullable()->after('serial_number');
            }
        });

        // Pilot: TMMIN KRW 1 (code 7A00001) → serial = substr(35, 4). Customer lain tetap null.
        DB::table('customers')->where('code', '7A00001')->update([
            'serial_first' => 35,
            'serial_length' => 4,
        ]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['serial_first', 'serial_length']);
        });

        Schema::table('mutations', function (Blueprint $table) {
            $table->dropColumn('customer_serial');
        });
    }
};
