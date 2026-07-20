<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('device_sessions', 'scanner_no')) {
            Schema::table('device_sessions', function (Blueprint $table) {
                $table->unsignedInteger('scanner_no')->nullable();
            });
        }

        // Backfill: nomori device scanner pulling (route /pulling) urut registrasi.
        $ids = DB::table('device_sessions')
            ->where('current_page', '/pulling')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id');

        $n = 0;
        foreach ($ids as $id) {
            DB::table('device_sessions')->where('id', $id)->update(['scanner_no' => ++$n]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('device_sessions', 'scanner_no')) {
            Schema::table('device_sessions', function (Blueprint $table) {
                $table->dropColumn('scanner_no');
            });
        }
    }
};
