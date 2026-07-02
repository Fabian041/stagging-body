<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'loading_list_details';

    private string $index = 'lld_loading_customer_idx';

    public function up(): void
    {
        if (! Schema::hasTable($this->table) || $this->indexExists()) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->index(['loading_list_id', 'customer_part_id'], $this->index);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable($this->table) || ! $this->indexExists()) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->dropIndex($this->index);
        });
    }

    private function indexExists(): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $this->table)
            ->where('index_name', $this->index)
            ->exists();
    }
};
