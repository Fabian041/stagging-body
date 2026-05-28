<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_alamat_chutes')) {
            return;
        }

        if (! Schema::hasColumn('master_alamat_chutes', 'customer_part_id')) {
            return;
        }

        Schema::table('master_alamat_chutes', function (Blueprint $table) {
            $table->dropForeign(['customer_part_id']);
            $table->dropUnique('master_alamat_chutes_unique_customer_part');
        });

        Schema::table('master_alamat_chutes', function (Blueprint $table) {
            $table->string('part_no_customer', 100)->nullable()->after('id');
            $table->string('back_no', 50)->default('')->after('part_no_customer');
            $table->string('part_name', 150)->default('')->after('back_no');
        });

        DB::statement('
            UPDATE master_alamat_chutes mac
            INNER JOIN customer_parts cp ON cp.id = mac.customer_part_id
            LEFT JOIN internal_parts ip ON ip.id = cp.internal_part_id
            SET
                mac.part_no_customer = COALESCE(cp.part_number, ""),
                mac.back_no = COALESCE(NULLIF(TRIM(cp.back_number), ""), NULLIF(TRIM(ip.back_number), ""), ""),
                mac.part_name = COALESCE(NULLIF(TRIM(ip.part_name), ""), "")
        ');

        Schema::table('master_alamat_chutes', function (Blueprint $table) {
            $table->dropColumn(['customer_part_id', 'is_active']);
            $table->unique(
                ['part_no_customer', 'back_no', 'part_name'],
                'master_alamat_chutes_unique_part'
            );
        });

        DB::statement('ALTER TABLE master_alamat_chutes MODIFY part_no_customer VARCHAR(100) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('master_alamat_chutes')) {
            return;
        }

        if (Schema::hasColumn('master_alamat_chutes', 'customer_part_id')) {
            return;
        }

        Schema::table('master_alamat_chutes', function (Blueprint $table) {
            $table->dropUnique('master_alamat_chutes_unique_part');
        });

        Schema::table('master_alamat_chutes', function (Blueprint $table) {
            $table->foreignId('customer_part_id')->nullable()->after('id')->constrained('customer_parts')->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->after('alamat_chute');
        });

        Schema::table('master_alamat_chutes', function (Blueprint $table) {
            $table->dropColumn(['part_no_customer', 'back_no', 'part_name']);
            $table->unique('customer_part_id', 'master_alamat_chutes_unique_customer_part');
        });
    }
};
