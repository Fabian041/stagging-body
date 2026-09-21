<?php

namespace Tests\Feature;

use App\Http\Controllers\ValidationController;
use App\Models\KanbanPairingLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ValidationPairingRepeatedSerialsTest extends TestCase
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

        Schema::create('internal_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
        });
        Schema::create('kanbans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id');
            $table->string('serial_number');
        });
        Schema::create('mutations', fn(Blueprint $table) => $table->id());

        (require database_path('migrations/2025_08_12_172420_create_body_kanban_pairings_table.php'))->up();
        (require database_path('migrations/2026_09_09_000001_create_kanban_pairing_logs_table.php'))->up();
        (require database_path('migrations/2026_09_17_000000_add_is_uncomplete_to_kanban_pairing_logs_table.php'))->up();

        DB::table('internal_parts')->insert(['id' => 1, 'part_number' => 'ASSEMBLY']);
        DB::table('kanbans')->insert([
            ['internal_part_id' => 1, 'serial_number' => '0001'],
            ['internal_part_id' => 1, 'serial_number' => '0002'],
        ]);
        DB::table('body_kanban_pairings')->insert([
            'id' => 1,
            'project' => '4L45W',
            'product' => 'TEST',
            'part_name' => 'TEST',
            'part_number_customer' => 'TEST',
            'assembly_part' => 'ASSEMBLY',
            'assembly_back_number' => 'ASSY',
            'painting_part' => 'PAINTING',
            'painting_back_number' => 'PAINT',
            'qty_assy' => 8,
            'qty_painting' => 8,
        ]);
    }

    /** @dataProvider pairingScenarios */
    public function test_repeated_scans_obey_project_policy_and_ratio(
        string $project,
        int $assemblyQty,
        int $paintingQty,
        array $assemblySerials,
        array $paintingSerials,
        bool $uncomplete,
        int $expectedStatus
    ): void {
        DB::table('body_kanban_pairings')->where('id', 1)->update([
            'project' => $project,
            'qty_assy' => $assemblyQty,
            'qty_painting' => $paintingQty,
        ]);

        $controller = new ValidationController();
        $master = $controller->pair(Request::create('/validation/kanban/pairing', 'GET', [
            'part' => 'ASSEMBLY',
        ]))->getData(true);
        $this->assertTrue($master['interlock_enabled']);
        $this->assertSame($project === '4L45W', $master['allow_repeated_serials']);

        $response = $controller->completePairing(Request::create('/validation/kanban/pairing/complete', 'POST', [
            'pairing_id' => 1,
            'assembly_part' => 'ASSEMBLY',
            'painting_part' => 'PAINTING',
            'assembly_serials' => $assemblySerials,
            'painting_serials' => $paintingSerials,
            'uncomplete' => $uncomplete,
        ]));

        $this->assertSame($expectedStatus, $response->getStatusCode(), $response->getContent());
        $logs = KanbanPairingLog::all();
        $this->assertCount($expectedStatus === 200 ? count(array_unique($assemblySerials)) : 0, $logs);
        foreach ($logs as $log) {
            $this->assertSame(KanbanPairingLog::STATUS_READY, (int) $log->status);
            $this->assertSame($paintingSerials, $log->painting_serial_numbers);
        }
    }

    public static function pairingScenarios(): array
    {
        return [
            '4L45W repeats assembly for 2:1' => ['4L45W', 8, 16, ['0001', '0001'], ['0101'], false, 200],
            '4L45W repeats painting for 1:2' => ['4L45W', 16, 8, ['0001'], ['0101', '0101'], false, 200],
            '4L45W repeats both sides' => ['4L45W', 8, 8, ['0001', '0001'], ['0101', '0101'], false, 200],
            '4L45W distinct assembly keeps separate logs' => ['4L45W', 8, 16, ['0001', '0002'], ['0101'], false, 200],
            '4L45W still rejects incorrect ratio' => ['4L45W', 8, 8, ['0001', '0001'], ['0101'], false, 422],
            '4L45W uncomplete still requires one scan each' => ['4L45W', 8, 16, ['0001', '0001'], ['0101'], true, 422],
            '660A rejects repeated assembly' => ['660A', 8, 16, ['0001', '0001'], ['0101'], false, 422],
            '660A rejects repeated painting' => ['660A', 16, 8, ['0001'], ['0101', '0101'], false, 422],
            '560B rejects repeated assembly' => ['560B', 8, 16, ['0001', '0001'], ['0101'], false, 422],
            '560B rejects repeated painting' => ['560B', 16, 8, ['0001'], ['0101', '0101'], false, 422],
        ];
    }

    public function test_4l45w_still_prevents_another_ready_pairing_for_same_assembly(): void
    {
        $controller = new ValidationController();
        $payload = [
            'pairing_id' => 1,
            'assembly_part' => 'ASSEMBLY',
            'painting_part' => 'PAINTING',
            'assembly_serials' => ['0001'],
            'painting_serials' => ['0101'],
        ];

        $first = $controller->completePairing(Request::create('/', 'POST', $payload));
        $second = $controller->completePairing(Request::create('/', 'POST', $payload));

        $this->assertSame(200, $first->getStatusCode(), $first->getContent());
        $this->assertSame(422, $second->getStatusCode(), $second->getContent());
        $this->assertStringContainsString('pairing aktif', $second->getData(true)['message']);
        $this->assertCount(1, KanbanPairingLog::all());
    }

    public function test_assembly_status_rejects_ready_pairing_before_painting_scan(): void
    {
        $log = KanbanPairingLog::create([
            'body_kanban_pairing_id' => 1,
            'assembly_internal_part_id' => 1,
            'assembly_kanban_id' => 1,
            'project' => '4L45W',
            'assembly_part' => 'ASSEMBLY',
            'assembly_serial_number' => '0001',
            'painting_part' => 'PAINTING',
            'painting_serial_numbers' => ['0101'],
            'status' => KanbanPairingLog::STATUS_READY,
        ]);
        $controller = new ValidationController();
        $request = Request::create('/validation/kanban/pairing/assembly-status', 'GET', [
            'pairing_id' => 1,
            'serial' => '0001',
        ]);

        $readyResponse = $controller->assemblyStatus($request);
        $this->assertSame(422, $readyResponse->getStatusCode());
        $this->assertStringContainsString('Production PRD Report', $readyResponse->getData(true)['message']);

        $log->update(['status' => KanbanPairingLog::STATUS_CONSUMED]);
        $consumedResponse = $controller->assemblyStatus($request);
        $this->assertSame(200, $consumedResponse->getStatusCode());
        $this->assertTrue($consumedResponse->getData(true)['success']);
    }
}
