<?php

namespace Tests\Feature;

use App\Models\Line;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterLineTest extends TestCase
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

    public function test_master_line_can_be_created_with_normalized_name(): void
    {
        $response = $this->actingAs($this->admin())->post('/dashboard/master/lines', [
            'name' => '  pc6a  ',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/dashboard/master/lines');
        $this->assertDatabaseHas('lines', [
            'name' => 'PC6A',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->post('/dashboard/master/lines', [
            'name' => 'pc6a',
            'is_active' => '1',
        ])->assertSessionHasErrors('name');

        $this->assertSame(1, Line::count());
    }

    public function test_admin_can_open_master_line_page(): void
    {
        $this->actingAs($this->admin())
            ->get('/dashboard/master/lines')
            ->assertOk()
            ->assertSee('Master Line')
            ->assertSee('Daftar Line');
    }

    public function test_line_status_is_toggled_without_deleting_relations(): void
    {
        $line = Line::create(['name' => 'PC6A', 'is_active' => true]);
        DB::table('internal_parts')->insert([
            'line_id' => $line->id,
            'part_number' => 'PART-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->patch("/dashboard/master/lines/{$line->id}/toggle")
            ->assertRedirect('/dashboard/master/lines');

        $this->assertFalse($line->fresh()->is_active);
        $this->assertDatabaseHas('internal_parts', ['line_id' => $line->id]);
    }

    public function test_machine_must_use_an_active_line_and_line_has_many_machines(): void
    {
        $activeLine = Line::create(['name' => 'PC6A', 'is_active' => true]);
        $inactiveLine = Line::create(['name' => 'PC6B', 'is_active' => false]);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/machine-authorization/machines', [
            'line_id' => $inactiveLine->id,
            'machine_code' => 'MC-001',
            'machine_name' => 'Injection 001',
            'is_active' => '1',
        ])->assertSessionHasErrors('line_id');

        $this->actingAs($admin)->post('/machine-authorization/machines', [
            'line_id' => $activeLine->id,
            'machine_code' => 'mc-001',
            'machine_name' => 'Injection 001',
            'location' => 'Injection Area',
            'is_active' => '1',
        ])->assertRedirect();

        $machine = Machine::firstOrFail();

        $this->assertSame('MC-001', $machine->machine_code);
        $this->assertTrue($machine->line->is($activeLine));
        $this->assertTrue($activeLine->machines->contains($machine));
        $this->assertSame(1, $activeLine->machines()->count());
    }

    public function test_lines_datatable_reports_part_and_machine_counts(): void
    {
        $line = Line::create(['name' => 'PC6A', 'is_active' => true]);
        DB::table('internal_parts')->insert([
            'line_id' => $line->id,
            'part_number' => 'PART-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Machine::create([
            'line_id' => $line->id,
            'machine_code' => 'MC-001',
            'machine_name' => 'Injection 001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->getJson('/dashboard/master/lines/data', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.name', '<strong>PC6A</strong>')
            ->assertJsonPath('data.0.internal_parts_count', 1)
            ->assertJsonPath('data.0.machines_count', 1);
    }

    public function test_machine_datatable_can_be_filtered_by_line(): void
    {
        $firstLine = Line::create(['name' => 'PC6A', 'is_active' => true]);
        $secondLine = Line::create(['name' => 'PC6B', 'is_active' => true]);
        Machine::create([
            'line_id' => $firstLine->id,
            'machine_code' => 'MC-001',
            'machine_name' => 'Injection 001',
            'is_active' => true,
        ]);
        Machine::create([
            'line_id' => $secondLine->id,
            'machine_code' => 'MC-002',
            'machine_name' => 'Injection 002',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->getJson(
            "/machine-authorization/machines/data?line_id={$firstLine->id}",
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.machine_code', '<strong>MC-001</strong>')
            ->assertJsonPath('data.0.line_name', 'PC6A');
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

        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('internal_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id')->nullable();
            $table->string('part_number')->nullable();
            $table->timestamps();
        });

        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id')->nullable();
            $table->string('machine_code', 64)->unique();
            $table->string('machine_name', 150);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('location', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('machine_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}
