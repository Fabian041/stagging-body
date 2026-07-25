<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumns('pis_scan_details', ['order_qty', 'target_qty', 'scanned_qty', 'remaining_qty'])) {
            return;
        }

        DB::table('pis_scan_details')
            ->where('order_qty', '>', 0)
            ->update([
                'target_qty' => DB::raw('order_qty'),
                'remaining_qty' => DB::raw('CASE WHEN order_qty > scanned_qty THEN order_qty - scanned_qty ELSE 0 END'),
            ]);
    }

    public function down(): void
    {
        // Nilai target kanban lama tidak dapat direkonstruksi secara aman.
    }
};
