<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pis_scan_details', function (Blueprint $table) {
            if (! Schema::hasColumn('pis_scan_details', 'order_qty')) {
                $table->integer('order_qty')->default(0)->after('target_qty');
            }

            if (! Schema::hasColumn('pis_scan_details', 'qty_per_kanban')) {
                $table->integer('qty_per_kanban')->default(1)->after('order_qty');
            }
        });

        // Fallback aman untuk data lama. Nilai sebenarnya akan diperbarui oleh
        // sync:loading-lists tanpa mengubah scanned_qty yang sudah berjalan.
        DB::table('pis_scan_details')
            ->where(function ($query) {
                $query->whereNull('order_qty')->orWhere('order_qty', '<=', 0);
            })
            ->update([
                'order_qty' => DB::raw('target_qty'),
                'qty_per_kanban' => 1,
            ]);
    }

    public function down(): void
    {
        Schema::table('pis_scan_details', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('pis_scan_details', 'qty_per_kanban')) {
                $columns[] = 'qty_per_kanban';
            }

            if (Schema::hasColumn('pis_scan_details', 'order_qty')) {
                $columns[] = 'order_qty';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
