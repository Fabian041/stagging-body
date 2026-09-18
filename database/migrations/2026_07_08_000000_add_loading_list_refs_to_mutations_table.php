<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            if (! Schema::hasColumn('mutations', 'loading_list_id')) {
                $table->unsignedBigInteger('loading_list_id')->nullable()->after('internal_part_id');
                $table->index('loading_list_id', 'mutations_loading_list_id_idx');
            }

            if (! Schema::hasColumn('mutations', 'loading_list_detail_id')) {
                $table->unsignedBigInteger('loading_list_detail_id')->nullable()->after('loading_list_id');
                $table->index('loading_list_detail_id', 'mutations_loading_list_detail_id_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            if (Schema::hasColumn('mutations', 'loading_list_detail_id')) {
                $table->dropIndex('mutations_loading_list_detail_id_idx');
                $table->dropColumn('loading_list_detail_id');
            }

            if (Schema::hasColumn('mutations', 'loading_list_id')) {
                $table->dropIndex('mutations_loading_list_id_idx');
                $table->dropColumn('loading_list_id');
            }
        });
    }
};
