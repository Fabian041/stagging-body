<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loading_lists', function (Blueprint $table) {
            $table->string('truck_status', 20)->default('pending')->after('shipping_date');
            $table->timestamp('truck_picked_at')->nullable()->after('truck_status');
            $table->unsignedBigInteger('truck_picked_by')->nullable()->after('truck_picked_at');

            $table->foreign('truck_picked_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loading_lists', function (Blueprint $table) {
            $table->dropForeign(['truck_picked_by']);
            $table->dropColumn(['truck_status', 'truck_picked_at', 'truck_picked_by']);
        });
    }
};
