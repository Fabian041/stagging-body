<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->uuid('transaction_id')->index();
            $table->foreignId('machine_access_log_id')->constrained('machine_access_logs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('set null');
            $table->string('event_type', 40)->index();
            $table->string('card_uid', 64);
            $table->string('machine_code', 64);
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'occurred_at']);
            $table->index(['transaction_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_events');
    }
};
