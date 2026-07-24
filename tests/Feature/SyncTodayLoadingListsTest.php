<?php

namespace Tests\Feature;

use App\Http\Controllers\LoadingListController;
use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\LoadingList;
use App\Models\LoadingListDetail;
use App\Models\MasterCycle;
use App\Models\PisScan;
use App\Models\PisScanDetail;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncTodayLoadingListsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
            'database.connections.mssql_external' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('sqlite');
        DB::purge('mssql_external');
        DB::reconnect('sqlite');
        DB::reconnect('mssql_external');

        $this->createLocalTables();
        $this->createSourceTable();
        Carbon::setTestNow('2026-07-24 00:05:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_imports_selected_date_and_preserves_actual_progress_on_rerun(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Customer Test',
        ]);
        $internalPart = InternalPart::create(['part_number' => 'INT-001']);
        $customerPart = CustomerPart::create([
            'internal_part_id' => $internalPart->id,
            'customer_id' => $customer->id,
            'part_number' => '12345-67890',
        ]);

        $this->insertSourceRow();
        $this->insertSourceRow(['CHR_COD_SYKMNO' => 'A-IGNORED']);
        $this->insertSourceRow([
            'CHR_COD_SYKMNO' => 'C-LL-TOMORROW',
            'CHR_NGP_NOUNYU' => '20260723',
            'CHR_COD_TOKISAKI' => 'UNKNOWN',
        ]);
        $this->insertSourceRow([
            'CHR_COD_SYKMNO' => 'C-LL-UNSUPPORTED',
            'CHR_COD_TOKISAKI' => 'UNSUPPORTED',
        ]);

        $this->artisan('sync:loading-lists', ['date' => '2026-07-22'])
            ->expectsOutputToContain('Catatan sync: 1 data dilewati')
            ->assertExitCode(Command::SUCCESS);

        $storedLoadingList = LoadingList::where('number', 'C-LL-001')->firstOrFail();
        $storedDetail = LoadingListDetail::where('loading_list_id', $storedLoadingList->id)->firstOrFail();

        $this->assertFalse(LoadingList::where('number', 'A-IGNORED')->exists());
        $this->assertFalse(LoadingList::where('number', 'C-LL-TOMORROW')->exists());
        $this->assertFalse(LoadingList::where('number', 'C-LL-UNSUPPORTED')->exists());
        $this->assertSame($customerPart->id, $storedDetail->customer_part_id);
        $this->assertSame(5, $storedDetail->kanban_qty);

        $storedDetail->update(['actual_kanban_qty' => 2]);
        DB::connection('mssql_external')->table('TT_GIG_SYKMEISAI')
            ->where('CHR_COD_SYKMNO', 'C-LL-001')
            ->update([
                'INT_NUB_NOUBIN' => '2',
                'INT_SUR_NOUSIJI' => 6,
                'INT_SUR_JYUCYUU' => 60,
            ]);

        $this->artisan('sync:loading-lists', ['date' => '2026-07-22'])
            ->assertExitCode(Command::SUCCESS);

        $this->assertSame(1, LoadingList::count());
        $this->assertSame(1, LoadingListDetail::count());
        $this->assertSame('2', $storedLoadingList->fresh()->cycle);
        $this->assertSame(6, $storedDetail->fresh()->kanban_qty);
        $this->assertSame(60, $storedDetail->fresh()->total_qty);
        $this->assertSame(2, $storedDetail->fresh()->actual_kanban_qty);
    }

    public function test_sync_range_imports_selected_date_and_next_two_days(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Customer Test',
        ]);
        $internalPart = InternalPart::create(['part_number' => 'INT-001']);
        CustomerPart::create([
            'internal_part_id' => $internalPart->id,
            'customer_id' => $customer->id,
            'part_number' => '12345-67890',
        ]);

        foreach ([0, 1, 2, 3] as $dayOffset) {
            $this->insertSourceRow([
                'CHR_COD_SYKMNO' => 'C-LL-D'.$dayOffset,
                'CHR_NGP_NOUNYU' => Carbon::parse('2026-07-22')->addDays($dayOffset)->format('Ymd'),
            ]);
        }

        $this->artisan('sync:loading-lists', [
            'date' => '2026-07-22',
            '--days' => 3,
        ])->assertExitCode(Command::SUCCESS);

        $this->assertSame(
            ['C-LL-D0', 'C-LL-D1', 'C-LL-D2'],
            LoadingList::query()->orderBy('number')->pluck('number')->all()
        );
        $this->assertFalse(LoadingList::where('number', 'C-LL-D3')->exists());
    }

    public function test_it_routes_pis_customers_to_pis_tables_and_preserves_scan_progress(): void
    {
        Customer::create([
            'code' => '7A00020',
            'name' => 'ADM SPD',
        ]);

        $this->insertSourceRow([
            'CHR_COD_TOKISAKI' => '7A00020',
            'CHR_MEI_NOUNYU' => 'ADM SPD',
        ]);

        $this->artisan('sync:loading-lists', ['date' => '2026-07-22'])
            ->assertExitCode(Command::SUCCESS);

        $pisScan = PisScan::where('loading_list_number', 'C-LL-001')->firstOrFail();
        $detail = PisScanDetail::where('pis_scan_id', $pisScan->id)->firstOrFail();

        $this->assertFalse(LoadingList::where('number', 'C-LL-001')->exists());
        $this->assertSame(5, $detail->target_qty);
        $this->assertSame(0, $detail->scanned_qty);
        $this->assertSame(5, $detail->remaining_qty);

        $detail->update([
            'scanned_qty' => 2,
            'remaining_qty' => 3,
        ]);
        DB::connection('mssql_external')->table('TT_GIG_SYKMEISAI')
            ->where('CHR_COD_SYKMNO', 'C-LL-001')
            ->update(['INT_SUR_NOUSIJI' => 6]);

        $this->artisan('sync:loading-lists', ['date' => '2026-07-22'])
            ->assertExitCode(Command::SUCCESS);

        $this->assertSame(1, PisScan::count());
        $this->assertSame(1, PisScanDetail::count());
        $this->assertSame(6, $detail->fresh()->target_qty);
        $this->assertSame(2, $detail->fresh()->scanned_qty);
        $this->assertSame(4, $detail->fresh()->remaining_qty);
    }

    public function test_dashboard_uses_pis_as_the_exclusive_source_for_pis_customers(): void
    {
        $pisCustomer = Customer::create([
            'code' => '7A00020',
            'name' => 'ADM SPD',
        ]);
        $regularCustomer = Customer::create([
            'code' => 'REGULAR',
            'name' => 'Regular Customer',
        ]);

        $legacyLoadingList = LoadingList::create([
            'customer_id' => $pisCustomer->id,
            'number' => 'LEGACY-ADM',
            'pds_number' => 'PDS-LEGACY',
            'cycle' => '1',
            'delivery_date' => '2026-07-22 08:00:00',
        ]);
        LoadingListDetail::create([
            'loading_list_id' => $legacyLoadingList->id,
            'customer_part_id' => 1,
            'kanban_qty' => 99,
            'actual_kanban_qty' => 99,
        ]);

        $pisScan = PisScan::create([
            'customer_id' => $pisCustomer->id,
            'loading_list_number' => 'PIS-ADM',
            'pds_number' => 'PDS-PIS',
            'cycle' => '1',
            'delivery_date' => '2026-07-22 08:00:00',
        ]);
        PisScanDetail::create([
            'pis_scan_id' => $pisScan->id,
            'part_number_int' => 'INT-PIS',
            'part_number_cust' => 'CUST-PIS',
            'target_qty' => 10,
            'scanned_qty' => 4,
            'remaining_qty' => 6,
        ]);
        $secondPisScan = PisScan::create([
            'customer_id' => $pisCustomer->id,
            'loading_list_number' => 'PIS-ADM-2',
            'pds_number' => 'PDS-PIS',
            'cycle' => '1',
            'delivery_date' => '2026-07-22 08:00:00',
        ]);
        PisScanDetail::create([
            'pis_scan_id' => $secondPisScan->id,
            'part_number_int' => 'INT-PIS-2',
            'part_number_cust' => 'CUST-PIS-2',
            'target_qty' => 3,
            'scanned_qty' => 1,
            'remaining_qty' => 2,
        ]);

        $regularLoadingList = LoadingList::create([
            'customer_id' => $regularCustomer->id,
            'number' => 'LL-REGULAR',
            'pds_number' => 'PDS-REGULAR',
            'cycle' => '1',
            'delivery_date' => '2026-07-22 08:00:00',
        ]);
        LoadingListDetail::create([
            'loading_list_id' => $regularLoadingList->id,
            'customer_part_id' => 2,
            'kanban_qty' => 5,
            'actual_kanban_qty' => 2,
        ]);

        $response = (new LoadingListController)->getDeliveryStackedChartData(
            Request::create('/dashboard/delivery/stacked-chart', 'GET', [
                'date_from' => '2026-07-22',
                'date_to' => '2026-07-22',
            ])
        );
        $payload = $response->getData(true);
        $rowsByCustomer = collect($payload['rows'])->keyBy('customer_name');

        $this->assertCount(2, $payload['rows']);
        $this->assertSame(13, $rowsByCustomer['ADM SPD']['total_target']);
        $this->assertSame(5, $rowsByCustomer['ADM SPD']['total_done']);
        $this->assertSame(2, $rowsByCustomer['ADM SPD']['ll_count']);
        $this->assertSame(['PIS-ADM', 'PIS-ADM-2'], $rowsByCustomer['ADM SPD']['loading_lists']);
        $this->assertSame(2, collect($payload['weekly_points'])->firstWhere('customer_name', 'ADM SPD')['ll_count']);
        $this->assertSame(5, $rowsByCustomer['Regular Customer']['total_target']);
        $this->assertSame(2, $rowsByCustomer['Regular Customer']['total_done']);
        $this->assertSame(3, $payload['meta']['raw_rows_loaded']);
    }

    public function test_dashboard_reports_skipped_data_as_a_note(): void
    {
        $skippedItems = [
            [
                'loading_list' => 'LL-001',
                'part_number_int' => 'INT-001',
                'part_number_cust' => 'CUST-001',
                'reason' => 'Customer part tidak ditemukan',
            ],
            ['loading_list' => 'LL-002', 'reason' => 'Customer tidak ditemukan'],
            ['loading_list' => 'LL-003', 'reason' => 'Internal part tidak ditemukan'],
        ];

        Artisan::shouldReceive('call')
            ->once()
            ->with('sync:loading-lists', [
                'date' => '2026-07-22',
                '--days' => 3,
                '--web' => true,
            ])
            ->andReturn(Command::SUCCESS);
        Artisan::shouldReceive('output')
            ->once()
            ->andReturn(json_encode([
                'loading_list_count' => 3,
                'skipped_items' => $skippedItems,
            ]));

        $response = (new LoadingListController)->syncDeliveryLoadingLists(
            Request::create('/dashboard/delivery/sync-loading-lists', 'POST', ['date' => '2026-07-22'])
        );
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('warning', $payload['status']);
        $this->assertSame(3, $payload['skipped']);
        $this->assertSame($skippedItems, $payload['skipped_items']);
        $this->assertSame('Sync berhasil dengan catatan: 3 data dilewati.', $payload['message']);
        $this->assertSame([
            'date' => '2026-07-22',
            'date_to' => '2026-07-24',
            'date_label' => '22/07/2026–24/07/2026',
            'synced_at' => '24/07/2026 00:05',
            'loading_list_count' => 3,
            'skipped' => 3,
        ], Cache::get('dashboard_delivery_last_loading_list_sync'));
    }

    public function test_dashboard_reports_when_no_loading_list_was_found(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('sync:loading-lists', [
                'date' => '2026-07-24',
                '--days' => 3,
                '--web' => true,
            ])
            ->andReturn(Command::SUCCESS);
        Artisan::shouldReceive('output')
            ->once()
            ->andReturn(json_encode([
                'loading_list_count' => 0,
                'skipped_items' => [],
            ]));

        $response = (new LoadingListController)->syncDeliveryLoadingLists(
            Request::create('/dashboard/delivery/sync-loading-lists', 'POST', ['date' => '2026-07-24'])
        );
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('empty', $payload['status']);
        $this->assertSame(0, $payload['loading_list_count']);
        $this->assertSame('2026-07-24', $payload['date_from']);
        $this->assertSame('2026-07-26', $payload['date_to']);
        $this->assertSame(
            'Tidak ada loading list untuk rentang tanggal 2026-07-24 s.d. 2026-07-26.',
            $payload['message']
        );
    }

    public function test_master_cycle_update_respects_explicit_zero_prep_offset(): void
    {
        $customer = Customer::create([
            'code' => 'HMMI',
            'name' => 'HINO (HMMI)',
        ]);
        $masterCycle = MasterCycle::create([
            'customer_id' => $customer->id,
            'cycle_name' => '1',
            'time' => '10:15',
            'prep_end_time' => '13:45',
            'prep_day_offset' => -1,
            'prep_end_day_offset' => -1,
            'truck_time' => '05:30',
        ]);

        $response = (new LoadingListController)->updateMasterCycle(
            Request::create('/dashboard/delivery/master-cycles/'.$masterCycle->id, 'PUT', [
                'customer_id' => $customer->id,
                'cycle_name' => '1',
                'time' => '10:15',
                'prep_end_time' => '13:45',
                'prep_day_offset' => 0,
                'truck_time' => '05:30',
            ]),
            $masterCycle
        );
        $indexPayload = (new LoadingListController)->getMasterCycles()->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, $masterCycle->fresh()->prep_day_offset);
        $this->assertSame(0, $indexPayload['data'][0]['prep_day_offset']);
        $this->assertSame('H+0', $indexPayload['data'][0]['prep_day_offset_label']);
    }

    private function insertSourceRow(array $overrides = []): void
    {
        DB::connection('mssql_external')->table('TT_GIG_SYKMEISAI')->insert(array_merge([
            'CHR_COD_SYKMNO' => 'C-LL-001',
            'CHR_COD_TKS_NOUBAN' => 'PDS-001',
            'CHR_NGP_NOUNYU' => '20260722',
            'CHR_NGP_SYUKKA' => '20260721',
            'INT_NUB_NOUBIN' => '1',
            'CHR_COD_TOKISAKI' => 'CUST-001',
            'CHR_MEI_NOUNYU' => 'Customer Test',
            'CHR_NUB_HINMOKU_TOK' => '1234567890',
            'CHR_NUB_HMK_HYP' => 'INT-001',
            'INT_SUR_NOUSIJI' => 5,
            'INT_SUR_SYUUYOU' => 10,
            'INT_SUR_JYUCYUU' => 50,
            'INT_NUB_MSEQ' => 1,
        ], $overrides));
    }

    private function createLocalTables(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('internal_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->timestamps();
        });

        Schema::create('customer_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('part_number');
            $table->timestamps();
        });

        Schema::create('loading_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('number')->unique();
            $table->string('pds_number')->nullable();
            $table->string('cycle')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('shipping_date')->nullable();
            $table->string('truck_status')->default('pending');
            $table->timestamp('truck_picked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loading_list_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loading_list_id');
            $table->unsignedBigInteger('customer_part_id');
            $table->integer('kanban_qty')->nullable();
            $table->integer('qty_per_kanban')->nullable();
            $table->integer('total_qty')->nullable();
            $table->integer('actual_kanban_qty')->nullable();
            $table->timestamps();
        });

        Schema::create('pis_scans', function (Blueprint $table) {
            $table->id();
            $table->string('loading_list_number')->nullable();
            $table->string('pds_number')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('cycle')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('shipping_date')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('dock_type')->nullable();
            $table->timestamps();
        });

        Schema::create('pis_scan_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pis_scan_id');
            $table->string('part_number_int')->nullable();
            $table->string('part_number_cust')->nullable();
            $table->integer('target_qty')->default(0);
            $table->integer('scanned_qty')->default(0);
            $table->integer('remaining_qty')->default(0);
            $table->timestamps();
        });

        Schema::create('pullings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('loading_list')->nullable();
            $table->string('pds_number')->nullable();
            $table->timestamp('pulling_date')->nullable();
            $table->integer('cycle')->nullable();
            $table->timestamps();
        });

        Schema::create('master_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('cycle_name');
            $table->time('time');
            $table->time('prep_end_time')->nullable();
            $table->smallInteger('prep_day_offset')->default(0);
            $table->smallInteger('prep_end_day_offset')->default(0);
            $table->time('truck_time')->nullable();
            $table->timestamps();
        });
    }

    private function createSourceTable(): void
    {
        Schema::connection('mssql_external')->create('TT_GIG_SYKMEISAI', function (Blueprint $table) {
            $table->string('CHR_COD_SYKMNO');
            $table->string('CHR_COD_TKS_NOUBAN')->nullable();
            $table->string('CHR_NGP_NOUNYU');
            $table->string('CHR_NGP_SYUKKA')->nullable();
            $table->string('INT_NUB_NOUBIN')->nullable();
            $table->string('CHR_COD_TOKISAKI');
            $table->string('CHR_MEI_NOUNYU')->nullable();
            $table->string('CHR_NUB_HINMOKU_TOK');
            $table->string('CHR_NUB_HMK_HYP');
            $table->integer('INT_SUR_NOUSIJI');
            $table->integer('INT_SUR_SYUUYOU');
            $table->integer('INT_SUR_JYUCYUU');
            $table->integer('INT_NUB_MSEQ');
        });
    }
}
