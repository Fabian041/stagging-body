<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('device_sessions', 'device_label')) {
                $table->string('device_label', 80)->nullable();
            }
            if (!Schema::hasColumn('device_sessions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (!Schema::hasColumn('device_sessions', 'screen_size')) {
                $table->string('screen_size', 40)->nullable();
            }
            if (!Schema::hasColumn('device_sessions', 'language')) {
                $table->string('language', 40)->nullable();
            }
            if (!Schema::hasColumn('device_sessions', 'is_online_reported')) {
                $table->boolean('is_online_reported')->default(true);
            }
            if (!Schema::hasColumn('device_sessions', 'heartbeat_count')) {
                $table->unsignedInteger('heartbeat_count')->default(0);
            }
            if (!Schema::hasColumn('device_sessions', 'reset_count')) {
                $table->unsignedInteger('reset_count')->default(0);
            }
            if (!Schema::hasColumn('device_sessions', 'last_error_message')) {
                $table->text('last_error_message')->nullable();
            }
            if (!Schema::hasColumn('device_sessions', 'last_error_at')) {
                $table->timestamp('last_error_at')->nullable();
            }
        });

        Schema::create('device_session_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_session_id')->constrained('device_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type', 40);
            $table->string('message', 255);
            $table->text('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['device_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_session_events');

        Schema::table('device_sessions', function (Blueprint $table) {
            foreach ([
                'device_label',
                'ip_address',
                'screen_size',
                'language',
                'is_online_reported',
                'heartbeat_count',
                'reset_count',
                'last_error_message',
                'last_error_at',
            ] as $column) {
                if (Schema::hasColumn('device_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
