<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            ['internal_part_id' => 10, 'type' => 'supply', 'qty' => 120],
            ['internal_part_id' => 10, 'type' => 'checkout', 'qty' => 20],
            ['internal_part_id' => 10, 'type' => 'supply', 'qty' => 4],
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
}
