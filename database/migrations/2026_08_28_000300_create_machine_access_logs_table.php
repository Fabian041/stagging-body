<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_access_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('transaction_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('set null');
            $table->string('card_uid', 64);
            $table->string('machine_code', 64)->nullable();
            $table->string('result', 16)->index();
            $table->string('reason', 64)->index();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('cache_expires_at')->nullable();
            $table->timestamps();

            $table->index(['machine_id', 'occurred_at']);
            $table->index(['card_uid', 'occurred_at']);
            $table->index(['result', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_access_logs');
    }
};
