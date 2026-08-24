<?php

namespace Tests\Feature;

use App\Http\Controllers\PullingController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PullingManualResetTest extends TestCase
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
            $table->string('part_number')->nullable();
            $table->string('back_number')->nullable();
            $table->string('part_name')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id')->nullable();
            $table->integer('qty_per_kanban')->nullable();
            $table->timestamps();
        });

        Schema::create('kanbans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id')->nullable();
            $table->string('serial_number', 10)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });

        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('qty')->nullable();
            $table->string('type')->nullable();
            $table->string('npk');
            $table->timestamp('date');
            $table->timestamps();
        });
    }

    public function test_manual_reset_accepts_back_number_and_serial_number(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1, 'npk' => '000999']));

        DB::table('internal_parts')->insert([
            'id' => 680,
            'part_number' => '423110-13430',
            'back_number' => 'KPE1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('customer_parts')->insert([
            'internal_part_id' => 680,
            'qty_per_kanban' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('kanbans')->insert([
            'internal_part_id' => 680,
            'serial_number' => '0019',
            'status' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = (new PullingController)->manualReset(Request::create('/pulling/manual', 'POST', [
            'back_number' => 'kpe1',
            'serial' => '0019',
        ]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success', $response->getData(true)['status']);
        $this->assertDatabaseHas('kanbans', [
            'internal_part_id' => 680,
            'serial_number' => '0019',
            'status' => 0,
        ]);
        $this->assertDatabaseHas('mutations', [
            'internal_part_id' => 680,
            'serial_number' => '0019',
            'qty' => 5,
            'type' => 'manual',
            'npk' => '000999',
        ]);
    }
}
