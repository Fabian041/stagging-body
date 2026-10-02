<?php

namespace Tests\Feature;

use App\Exports\MutationMultiSheetExport;
use App\Http\Controllers\DashboardController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProductionResultExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('internal_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id');
            $table->string('back_number');
        });
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id');
            $table->string('serial_number');
            $table->integer('qty');
            $table->string('type');
            $table->boolean('is_adjustment')->default(false);
            $table->dateTime('date');
        });

        DB::table('lines')->insert([
            ['id' => 1, 'name' => 'AS523'],
            ['id' => 2, 'name' => 'AS547'],
        ]);
        DB::table('internal_parts')->insert([
            ['id' => 1, 'line_id' => 1, 'back_number' => 'NORMAL'],
            ['id' => 2, 'line_id' => 2, 'back_number' => 'RIGHT'],
            ['id' => 3, 'line_id' => 2, 'back_number' => 'LEFT'],
        ]);
        DB::table('mutations')->insert([
            ['internal_part_id' => 1, 'serial_number' => '0001', 'qty' => 5, 'type' => 'supply', 'date' => '2026-10-02 08:00:00'],
            ['internal_part_id' => 2, 'serial_number' => 'R001', 'qty' => 5, 'type' => 'as547_kanan', 'date' => '2026-10-02 08:01:00'],
            ['internal_part_id' => 3, 'serial_number' => 'L001', 'qty' => 5, 'type' => 'as547_kiri', 'date' => '2026-10-02 08:02:00'],
            ['internal_part_id' => 2, 'serial_number' => 'OUT1', 'qty' => 5, 'type' => 'checkout', 'date' => '2026-10-02 08:03:00'],
        ]);

        Excel::fake();
    }

    public function test_production_result_export_has_a_separate_as547_sheet(): void
    {
        $this->export(['production_result' => 1]);

        Excel::assertDownloaded('Production_Result_20261002_20261002.xlsx', function (MutationMultiSheetExport $export) {
            $sheets = collect($export->sheets())->keyBy(fn ($sheet) => $sheet->title());

            $this->assertEqualsCanonicalizing(['AS523', 'AS547'], $sheets->keys()->all());
            $this->assertCount(2, $sheets['AS547']->collection());
            $mappedRow = $sheets['AS547']->map($sheets['AS547']->collection()->first());
            $this->assertIsFloat($mappedRow[7]);
            $this->assertSame('yyyy-mm-dd hh:mm:ss', $sheets['AS547']->columnFormats()['H']);

            return true;
        });
    }

    public function test_regular_mutation_export_remains_supply_only(): void
    {
        $this->export();

        Excel::assertDownloaded('Mutation_20261002_20261002.xlsx', function (MutationMultiSheetExport $export) {
            $sheets = $export->sheets();

            $this->assertCount(1, $sheets);
            $this->assertSame('AS523', $sheets[0]->title());

            return true;
        });
    }

    private function export(array $extra = []): void
    {
        $request = Request::create('/dashboard/mutation/export', 'GET', $extra + [
            'from' => '2026-10-02',
            'to' => '2026-10-02',
        ]);
        $this->app->instance('request', $request);

        (new DashboardController())->exportMutation();
    }
}
