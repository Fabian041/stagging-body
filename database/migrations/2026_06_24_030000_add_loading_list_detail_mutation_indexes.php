<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `mutations`
                ADD INDEX `mutations_part_type_created_idx`
                    (`internal_part_id`, `type`, `created_at`),
                ADD INDEX `mutations_part_type_serial_created_idx`
                    (`internal_part_id`, `type`, `serial_number`, `created_at`),
                ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('mutations', function (Blueprint $table) {
            $table->index(
                ['internal_part_id', 'type', 'created_at'],
                'mutations_part_type_created_idx'
            );
            $table->index(
                ['internal_part_id', 'type', 'serial_number', 'created_at'],
                'mutations_part_type_serial_created_idx'
            );
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `mutations`
                DROP INDEX `mutations_part_type_created_idx`,
                DROP INDEX `mutations_part_type_serial_created_idx`,
                ALGORITHM=INPLACE, LOCK=NONE');

            return;
        }

        Schema::table('mutations', function (Blueprint $table) {
            $table->dropIndex('mutations_part_type_created_idx');
            $table->dropIndex('mutations_part_type_serial_created_idx');
        });
    }
};
