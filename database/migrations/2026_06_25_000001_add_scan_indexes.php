<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index untuk mempercepat jalur scan (lookup by part number / serial number).
 *
 * Semua index BIASA (non-unique) → tidak mengubah hasil query, hanya mempercepat.
 * Idempotent: hanya membuat index yang belum ada, dan reversible lewat down().
 *
 * Catatan deploy: untuk tabel besar (mis. kanbans) jalankan saat jam sepi.
 */
return new class extends Migration
{
    private array $indexes = [
        'internal_parts' => [
            // InternalPart::where('part_number', ...) di mutation, kanbanCheck, storeDetail, dll.
            'internal_parts_part_number_index' => ['part_number'],
        ],
        'customer_parts' => [
            // getAlamatChute / findCustomerPart: where part_number + customer_id
            'customer_parts_part_number_customer_id_index' => ['part_number', 'customer_id'],
            // mutation / storeDetail: where internal_part_id + part_number
            'customer_parts_internal_part_id_part_number_index' => ['internal_part_id', 'part_number'],
        ],
        'kanbans' => [
            // Kanban::where('internal_part_id', ...)->where('serial_number', ...) di mutation, kanbanCheck
            'kanbans_internal_part_id_serial_number_index' => ['internal_part_id', 'serial_number'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $defs) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $defs) {
                foreach ($defs as $name => $columns) {
                    if (! $this->indexExists($table, $name)) {
                        $t->index($columns, $name);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $defs) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $defs) {
                foreach ($defs as $name => $columns) {
                    if ($this->indexExists($table, $name)) {
                        $t->dropIndex($name);
                    }
                }
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
