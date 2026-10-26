<?php

namespace Tests\Feature;

use App\Exports\StockTransactionHistoryExport;
use App\Http\Controllers\DashboardController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProductionStockBreakdownTest extends TestCase
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
            $table->string('part_number');
            $table->string('back_number');
            $table->integer('standard_stock')->default(0);
        });
        Schema::create('production_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id');
            $table->integer('current_stock');
        });
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id');
            $table->string('type');
            $table->integer('qty');
            $table->string('serial_number')->nullable();
            $table->string('npk')->nullable();
            $table->boolean('is_adjustment')->default(false);
            $table->dateTime('date')->nullable();
        });

        DB::table('lines')->insert(['id' => 1, 'name' => 'AS546']);
        DB::table('internal_parts')->insert([
            'id' => 10,
            'line_id' => 1,
            'part_number' => '423105-11660',
            'back_number' => 'MP21',
            'standard_stock' => 40,
        ]);
        DB::table('production_stocks')->insert([
            'internal_part_id' => 10,
            'current_stock' => 104,
        ]);
        DB::table('mutations')->insert([
            ['internal_part_id' => 10, 'type' => 'supply', 'qty' => 120, 'serial_number' => '0001', 'npk' => '1001', 'is_adjustment' => false, 'date' => '2026-10-21 08:00:00'],
            ['internal_part_id' => 10, 'type' => 'checkout', 'qty' => 20, 'serial_number' => '0002', 'npk' => '1002', 'is_adjustment' => false, 'date' => '2026-10-22 09:00:00'],
            ['internal_part_id' => 10, 'type' => 'supply', 'qty' => 4, 'serial_number' => 'ADJUSTMENT', 'npk' => '1003', 'is_adjustment' => true, 'date' => '2026-10-22 10:00:00'],
        ]);
    }

    public function test_stock_data_contains_cumulative_in_and_out_totals(): void
    {
        $response = (new DashboardController())->productionStockData(
            Request::create('/dashboard/production/stock/data', 'GET', ['line' => 'AS546'])
        );

        $row = $response->getData(true)['data'][0];

        $this->assertSame(124, $row['stock_in']);
        $this->assertSame(20, $row['stock_out']);
        $this->assertSame(104, $row['qty']);
    }

    public function test_transaction_history_contains_latest_rows_and_running_balance(): void
    {
        $response = (new DashboardController())->productionStockTransactions(
            Request::create('/dashboard/production/stock/transactions', 'GET', ['internal_part_id' => 10])
        );

        $payload = $response->getData(true);

        $this->assertSame(3, $payload['pagination']['total']);
        $this->assertSame([104, 100, 120], array_column($payload['data'], 'balance_after'));
        $this->assertSame(['in', 'out', 'in'], array_column($payload['data'], 'direction'));
        $this->assertTrue($payload['data'][0]['is_adjustment']);
    }

    public function test_transaction_history_can_be_filtered_by_date(): void
    {
        $response = (new DashboardController())->productionStockTransactions(
            Request::create('/dashboard/production/stock/transactions', 'GET', [
                'internal_part_id' => 10,
                'date_from' => '2026-10-22',
                'date_to' => '2026-10-22',
            ])
        );

        $payload = $response->getData(true);

        $this->assertSame(2, $payload['pagination']['total']);
        $this->assertSame([104, 100], array_column($payload['data'], 'balance_after'));

        $untilResponse = (new DashboardController())->productionStockTransactions(
            Request::create('/dashboard/production/stock/transactions', 'GET', [
                'internal_part_id' => 10,
                'date_to' => '2026-10-21',
            ])
        );

        $this->assertSame(1, $untilResponse->getData(true)['pagination']['total']);
    }

    public function test_filtered_transaction_history_can_be_downloaded(): void
    {
        Excel::fake();

        (new DashboardController())->productionStockTransactionsExport(
            Request::create('/dashboard/production/stock/transactions/export', 'GET', [
                'internal_part_id' => 10,
                'date_from' => '2026-10-22',
                'date_to' => '2026-10-22',
            ])
        );

        Excel::assertDownloaded('Riwayat_Stock_MP21_20261022_20261022.xlsx', function (StockTransactionHistoryExport $export) {
            $mappedRow = $export->map($export->collection()->first());

            $this->assertCount(2, $export->collection());
            $this->assertCount(11, $export->headings());
            $this->assertIsFloat($mappedRow[3]);
            $this->assertIsFloat($mappedRow[4]);
            $this->assertSame('dd-mmm-yyyy', $export->columnFormats()['D']);
            $this->assertSame('hh:mm:ss', $export->columnFormats()['E']);

            return true;
        });
    }
}
