<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_parts', function (Blueprint $table) {
            $table->string('quality_photo')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('internal_parts', function (Blueprint $table) {
            $table->dropColumn('quality_photo');
        });
    }
};
