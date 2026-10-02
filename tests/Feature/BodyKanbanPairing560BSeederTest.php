<?php

namespace Tests\Feature;

use Database\Seeders\BodyKanbanPairing560BSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BodyKanbanPairing560BSeederTest extends TestCase
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

        (require database_path('migrations/2025_08_12_172420_create_body_kanban_pairings_table.php'))->up();

        DB::table('body_kanban_pairings')->insert([
            'project' => '560B',
            'product' => 'OLD',
            'part_name' => 'OLD',
            'part_number_customer' => '484170-12580-B40',
            'assembly_part' => '484170-12580-B40',
            'assembly_back_number' => 'SP2C',
            'painting_part' => '423110-14450-B40',
            'painting_back_number' => 'KP7E',
            'qty_assy' => 6,
            'qty_painting' => 6,
        ]);
    }

    public function test_seeder_creates_only_direct_assembly_to_painting_pairs(): void
    {
        (new BodyKanbanPairing560BSeeder())->run();

        $rows = DB::table('body_kanban_pairings')->where('project', '560B')->get();

        $this->assertCount(5, $rows);
        $this->assertEqualsCanonicalizing(
            [
                '484170-12580-B40|423115-11060-B40',
                '484170-12590-B40|423115-11070-B40',
                '423110-14450-B40|423115-11190-B40',
                '423110-14360-B40|423115-11100-B40',
                '423110-14370-B40|423115-11120-B40',
            ],
            $rows->map(fn ($row) => $row->assembly_part.'|'.$row->painting_part)->all()
        );
        $this->assertTrue($rows->every(fn ($row) => (int) $row->qty_assy === 6 && (int) $row->qty_painting === 6));
        $this->assertFalse($rows->contains(fn ($row) => str_starts_with($row->painting_part, '423110-')));
    }
}
