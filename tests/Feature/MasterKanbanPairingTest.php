<?php

namespace Tests\Feature;

use App\Models\KanbanPairing;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterKanbanPairingTest extends TestCase
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

        $this->createSchema();
    }

    public function test_admin_can_open_and_create_normalized_pairing_master(): void
    {
        $this->actingAs($this->admin())
            ->get('/dashboard/master/kanban-pairings')
            ->assertOk()
            ->assertSee('Master Kanban Pairing')
            ->assertSee('Tambah Multiple Pairing')
            ->assertSee('Tambah Baris');

        $response = $this->actingAs($this->admin())->post('/dashboard/master/kanban-pairings', [
            'project' => ' 560b ',
            'product' => ' handle painting ',
            'part_name' => ' handle assembly ',
            'part_number_customer' => ' 69210-vt010-b3 ',
            'assembly_part' => ' 484170-12580-b40 ',
            'assembly_back_number' => ' sp2c ',
            'qty_assy' => 6,
            'painting_part' => ' 423115-11060-b40 ',
            'painting_back_number' => ' sn31 ',
            'qty_painting' => 6,
        ]);

        $response->assertRedirect('/dashboard/master/kanban-pairings');
        $this->assertDatabaseHas('body_kanban_pairings', [
            'project' => '560B',
            'product' => 'HANDLE PAINTING',
            'part_name' => 'HANDLE ASSEMBLY',
            'part_number_customer' => '69210-VT010-B3',
            'assembly_part' => '484170-12580-B40',
            'assembly_back_number' => 'SP2C',
            'painting_part' => '423115-11060-B40',
            'painting_back_number' => 'SN31',
            'qty_assy' => 6,
            'qty_painting' => 6,
        ]);
    }

    public function test_multiple_pairings_can_be_created_in_one_request(): void
    {
        $response = $this->actingAs($this->admin())->post('/dashboard/master/kanban-pairings', [
            'pairings' => [
                array_merge($this->pairingValues(), [
                    'project' => ' 560b ',
                    'assembly_part' => ' assy-001 ',
                    'painting_part' => ' paint-001 ',
                ]),
                array_merge($this->pairingValues(), [
                    'project' => ' 660a ',
                    'assembly_part' => ' assy-002 ',
                    'painting_part' => ' paint-002 ',
                ]),
            ],
        ]);

        $response->assertRedirect('/dashboard/master/kanban-pairings')
            ->assertSessionHas('success', '2 Master Kanban Pairing berhasil ditambah.');

        $this->assertSame(2, KanbanPairing::count());
        $this->assertDatabaseHas('body_kanban_pairings', [
            'project' => '560B',
            'assembly_part' => 'ASSY-001',
            'painting_part' => 'PAINT-001',
        ]);
        $this->assertDatabaseHas('body_kanban_pairings', [
            'project' => '660A',
            'assembly_part' => 'ASSY-002',
            'painting_part' => 'PAINT-002',
        ]);
    }

    public function test_duplicate_assembly_inside_batch_rejects_the_whole_request(): void
    {
        $response = $this->actingAs($this->admin())->post('/dashboard/master/kanban-pairings', [
            'pairings' => [
                array_merge($this->pairingValues(), ['assembly_part' => ' assy-duplicate ']),
                array_merge($this->pairingValues(), [
                    'assembly_part' => 'ASSY-DUPLICATE',
                    'painting_part' => 'PAINT-002',
                ]),
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertSame(0, KanbanPairing::count());
    }

    public function test_pairing_can_be_updated_and_duplicate_assembly_is_rejected(): void
    {
        $pairing = KanbanPairing::create($this->pairingValues());

        $this->actingAs($this->admin())
            ->put("/dashboard/master/kanban-pairings/{$pairing->id}", array_merge(
                $this->pairingValues(),
                ['qty_assy' => 2, 'qty_painting' => 1]
            ))
            ->assertRedirect('/dashboard/master/kanban-pairings');

        $this->assertDatabaseHas('body_kanban_pairings', [
            'id' => $pairing->id,
            'qty_assy' => 2,
            'qty_painting' => 1,
        ]);

        $this->actingAs($this->admin())
            ->post('/dashboard/master/kanban-pairings', array_merge(
                $this->pairingValues(),
                ['painting_part' => 'DIFFERENT-PAINTING']
            ))
            ->assertSessionHasErrors('assembly_part');

        $this->assertSame(1, KanbanPairing::count());
    }

    public function test_pairing_datatable_can_be_filtered_and_pairing_can_be_deleted(): void
    {
        $first = KanbanPairing::create($this->pairingValues());
        KanbanPairing::create(array_merge($this->pairingValues(), [
            'project' => '660A',
            'assembly_part' => 'ASSEMBLY-660A',
            'painting_part' => 'PAINTING-660A',
        ]));

        $this->actingAs($this->admin())
            ->getJson('/dashboard/master/kanban-pairings/data?project=560b', [
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.project', '560B');

        $this->actingAs($this->admin())
            ->delete("/dashboard/master/kanban-pairings/{$first->id}")
            ->assertRedirect('/dashboard/master/kanban-pairings');

        $this->assertDatabaseMissing('body_kanban_pairings', ['id' => $first->id]);
    }

    private function pairingValues(): array
    {
        return [
            'project' => '560B',
            'product' => 'HANDLE PAINTING',
            'part_name' => 'HANDLE ASSEMBLY',
            'part_number_customer' => '69210-VT010-B3',
            'assembly_part' => '484170-12580-B40',
            'assembly_back_number' => 'SP2C',
            'qty_assy' => 6,
            'painting_part' => '423115-11060-B40',
            'painting_back_number' => 'SN31',
            'qty_painting' => 6,
        ];
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Administrator',
            'email' => uniqid('admin-', true).'@example.test',
            'npk' => uniqid('NPK-', true),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('npk')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        (require database_path('migrations/2025_08_12_172420_create_body_kanban_pairings_table.php'))->up();
    }
}
