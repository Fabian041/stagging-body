<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mutations', 'is_adjustment')) {
            Schema::table('mutations', function (Blueprint $table) {
                $table->boolean('is_adjustment')->default(false)->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mutations', 'is_adjustment')) {
            Schema::table('mutations', function (Blueprint $table) {
                $table->dropColumn('is_adjustment');
            });
        }
    }
};
