<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            $table->unsignedBigInteger('device_session_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('error_logs', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'device_session_id']);
        });
    }
};
