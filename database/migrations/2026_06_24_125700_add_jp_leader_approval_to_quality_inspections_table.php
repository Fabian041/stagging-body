<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('quality_inspections', 'jp_approved_by')) {
                $table->string('jp_approved_by', 100)->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('quality_inspections', 'jp_approved_at')) {
                $table->timestamp('jp_approved_at')->nullable()->after('jp_approved_by');
            }

            if (!Schema::hasColumn('quality_inspections', 'leader_approved_by')) {
                $table->string('leader_approved_by', 100)->nullable()->after('jp_approved_at');
            }

            if (!Schema::hasColumn('quality_inspections', 'leader_approved_at')) {
                $table->timestamp('leader_approved_at')->nullable()->after('leader_approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quality_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('quality_inspections', 'leader_approved_at')) {
                $table->dropColumn('leader_approved_at');
            }

            if (Schema::hasColumn('quality_inspections', 'leader_approved_by')) {
                $table->dropColumn('leader_approved_by');
            }

            if (Schema::hasColumn('quality_inspections', 'jp_approved_at')) {
                $table->dropColumn('jp_approved_at');
            }

            if (Schema::hasColumn('quality_inspections', 'jp_approved_by')) {
                $table->dropColumn('jp_approved_by');
            }
        });
    }
};