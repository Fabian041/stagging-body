<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pis_scans', function (Blueprint $table) {
            $table->string('delivery_type')->nullable()->after('shipping_date');
            $table->string('dock_type')->nullable()->after('delivery_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pis_scans', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'dock_type']);
        });
    }
};

