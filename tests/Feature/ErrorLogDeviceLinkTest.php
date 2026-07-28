<?php

namespace Tests\Feature;

use App\Http\Controllers\ErrorLogController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ErrorLogDeviceLinkTest extends TestCase
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

        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('device_session_id')->nullable();
            $table->string('area')->nullable();
            $table->text('message')->nullable();
            $table->string('expected')->nullable();
            $table->string('scanned')->nullable();
            $table->timestamp('date');
            $table->timestamps();
        });
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_id', 100);
            $table->text('last_error_message')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->timestamps();
        });
        Schema::create('device_session_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_session_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type', 40);
            $table->string('message', 255);
            $table->text('meta')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_operational_error_updates_the_matching_device(): void
    {
        $user = (new User)->forceFill(['id' => 7, 'role' => 'ppic']);
        $user->exists = true;
        $this->actingAs($user);

        $deviceId = DB::table('device_sessions')->insertGetId([
            'user_id' => 7,
            'device_id' => 'scanner-ppic-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new ErrorLogController)->store(Request::create('/error/store', 'POST', [
            'device_id' => 'scanner-ppic-1',
            'message' => 'Kanban tidak sesuai!',
            'expected' => 'PART-A',
            'scanned' => 'PART-B',
        ]));

        $this->assertDatabaseHas('error_logs', [
            'area' => 'ppic',
            'user_id' => 7,
            'device_session_id' => $deviceId,
        ]);
        $this->assertDatabaseHas('device_sessions', [
            'id' => $deviceId,
            'last_error_message' => 'Kanban tidak sesuai!',
        ]);
        $this->assertDatabaseHas('device_session_events', [
            'device_session_id' => $deviceId,
            'type' => 'operational_error',
        ]);
    }
}
