<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachinePermission;
use App\Models\RfidCard;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class MachineAuthorizationApiTest extends TestCase
{
    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'sanctum.guard' => [],
            'machine_auth.allowed_cache_ttl_hours' => 24,
            'machine_auth.denied_cache_ttl_minutes' => 60,
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_active_card_user_machine_and_permission_are_authorized(): void
    {
        $fixture = $this->authorizedFixture();

        $response = $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        );

        $response->assertOk()
            ->assertJsonPath('authorized', true)
            ->assertJsonPath('reason', 'AUTHORIZED')
            ->assertJsonPath('employee.npk', $fixture['user']->npk)
            ->assertJsonPath('machine.code', $fixture['machine']->machine_code)
            ->assertJsonPath('cache.allowed', true);

        $this->assertDatabaseHas('machine_access_logs', [
            'card_uid' => $fixture['card']->card_uid,
            'result' => 'GRANTED',
            'reason' => 'AUTHORIZED',
        ]);
    }

    public function test_authorization_and_sync_include_machine_line_context(): void
    {
        $lineId = DB::table('lines')->insertGetId([
            'name' => 'PC6A',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $fixture = $this->authorizedFixture(['line_id' => $lineId]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()
            ->assertJsonPath('machine.line.id', $lineId)
            ->assertJsonPath('machine.line.name', 'PC6A');

        $this->withToken($fixture['token'])
            ->getJson('/api/v1/machine-auth/sync')
            ->assertOk()
            ->assertJsonPath('machine.line.id', $lineId)
            ->assertJsonPath('machine.line.name', 'PC6A');
    }

    public function test_unknown_card_is_denied_and_audited(): void
    {
        $fixture = $this->authorizedFixture();

        $response = $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, ['card_uid' => 'UNKNOWN-001'])
        );

        $response->assertOk()
            ->assertJsonPath('authorized', false)
            ->assertJsonPath('reason', 'CARD_NOT_FOUND');
        $this->assertDatabaseHas('machine_access_logs', [
            'card_uid' => 'UNKNOWN-001',
            'reason' => 'CARD_NOT_FOUND',
        ]);
    }

    public function test_inactive_card_is_denied(): void
    {
        $fixture = $this->authorizedFixture();
        $fixture['card']->update(['is_active' => false]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('reason', 'CARD_INACTIVE');
    }

    public function test_inactive_machine_is_denied(): void
    {
        $fixture = $this->authorizedFixture();
        $fixture['machine']->update(['is_active' => false]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('reason', 'MACHINE_INACTIVE');
    }

    public function test_missing_permission_is_denied(): void
    {
        $fixture = $this->authorizedFixture();
        $fixture['permission']->delete();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('reason', 'PERMISSION_NOT_FOUND');
    }

    public function test_explicit_denied_permission_is_denied(): void
    {
        $fixture = $this->authorizedFixture();
        $fixture['permission']->update(['is_allowed' => false]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('reason', 'PERMISSION_DENIED');

        $this->assertDatabaseHas('machine_access_logs', [
            'card_uid' => $fixture['card']->card_uid,
            'result' => 'DENIED',
            'reason' => 'PERMISSION_DENIED',
        ]);
    }

    public function test_permission_before_valid_from_is_denied(): void
    {
        $fixture = $this->authorizedFixture();
        $fixture['permission']->update([
            'valid_from' => now()->addHour(),
            'valid_until' => now()->addDay(),
        ]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('reason', 'PERMISSION_NOT_YET_VALID');
    }

    public function test_expired_permission_is_denied(): void
    {
        $fixture = $this->authorizedFixture();
        $fixture['permission']->update([
            'valid_from' => now()->subDay(),
            'valid_until' => now()->subMinute(),
        ]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('reason', 'PERMISSION_EXPIRED');
    }

    public function test_missing_machine_token_returns_authentication_failure(): void
    {
        $this->postJson('/api/v1/machine-auth/validate', [
            'request_id' => (string) Str::uuid(),
            'card_uid' => '1667815478',
            'machine_code' => 'MC-001',
        ])->assertUnauthorized();
    }

    public function test_invalid_machine_token_returns_authentication_failure(): void
    {
        $fixture = $this->authorizedFixture();

        $this->withToken('invalid-token-value')->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertUnauthorized();
    }

    public function test_machine_token_with_wrong_ability_is_forbidden(): void
    {
        $fixture = $this->authorizedFixture();
        $wrongAbilityToken = $fixture['machine']->createToken('wrong-ability', ['read-only'])->plainTextToken;

        $this->withToken($wrongAbilityToken)->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertForbidden()->assertJsonPath('reason', 'INVALID_MACHINE_TOKEN_ABILITY');
    }

    public function test_machine_a_token_cannot_represent_machine_b(): void
    {
        $fixture = $this->authorizedFixture(['machine_code' => 'MC-A']);

        $response = $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, ['machine_code' => 'MC-B'])
        );

        $response->assertForbidden()
            ->assertJsonPath('authorized', false)
            ->assertJsonPath('reason', 'INVALID_REQUEST');
        $this->assertDatabaseHas('machine_access_logs', [
            'machine_id' => $fixture['machine']->id,
            'machine_code' => 'MC-B',
            'reason' => 'INVALID_REQUEST',
        ]);
    }

    public function test_validate_requires_request_id(): void
    {
        $fixture = $this->authorizedFixture();

        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/validate', [
            'card_uid' => $fixture['card']->card_uid,
            'machine_code' => $fixture['machine']->machine_code,
        ])->assertUnprocessable()->assertJsonValidationErrors('request_id');
    }

    public function test_allowed_cache_uses_configured_ttl(): void
    {
        $now = CarbonImmutable::parse('2026-08-28 08:00:00', 'Asia/Jakarta');
        CarbonImmutable::setTestNow($now);
        config(['machine_auth.allowed_cache_ttl_hours' => 6]);
        $fixture = $this->authorizedFixture();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()->assertJsonPath('cache.expires_at', $now->addHours(6)->toISOString());
    }

    public function test_denied_cache_uses_configured_ttl(): void
    {
        $now = CarbonImmutable::parse('2026-08-28 01:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);
        config(['machine_auth.denied_cache_ttl_minutes' => 15]);
        $fixture = $this->authorizedFixture();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, ['card_uid' => 'UNKNOWN-CACHE'])
        )->assertOk()->assertJsonPath('cache.expires_at', $now->addMinutes(15)->toISOString());
    }

    public function test_allowed_cache_never_exceeds_permission_valid_until(): void
    {
        $now = CarbonImmutable::parse('2026-08-28 08:00:00', 'Asia/Jakarta');
        CarbonImmutable::setTestNow($now);
        config(['machine_auth.allowed_cache_ttl_hours' => 24]);
        $fixture = $this->authorizedFixture();
        $validUntil = $now->addMinutes(90);
        $fixture['permission']->update(['valid_until' => $validUntil]);

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk()
            ->assertJsonPath('reason', 'AUTHORIZED')
            ->assertJsonPath('cache.expires_at', $validUntil->toISOString());
    }

    public function test_sync_uses_configured_ttl(): void
    {
        $now = CarbonImmutable::parse('2026-08-28 01:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);
        config(['machine_auth.sync_cache_ttl_hours' => 8]);
        $fixture = $this->authorizedFixture();

        $this->withToken($fixture['token'])->getJson('/api/v1/machine-auth/sync')
            ->assertOk()
            ->assertJsonPath('generated_at', $now->toISOString())
            ->assertJsonPath('expires_at', $now->addHours(8)->toISOString());
    }

    public function test_repeated_validate_request_id_returns_same_transaction_without_duplicate_log(): void
    {
        $fixture = $this->authorizedFixture();
        $requestId = (string) Str::uuid();
        $payload = [
            'request_id' => $requestId,
            'card_uid' => $fixture['card']->card_uid,
            'machine_code' => $fixture['machine']->machine_code,
        ];

        $first = $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/validate', $payload);
        $second = $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/validate', $payload);

        $first->assertOk()->assertJsonPath('transaction_id', $requestId);
        $second->assertOk()->assertJsonPath('transaction_id', $requestId);
        $this->assertSame(1, DB::table('machine_access_logs')->where('transaction_id', $requestId)->count());
    }

    public function test_same_request_id_with_different_card_uid_returns_conflict(): void
    {
        $fixture = $this->authorizedFixture();
        $requestId = (string) Str::uuid();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, ['request_id' => $requestId])
        )->assertOk();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, [
                'request_id' => $requestId,
                'card_uid' => 'DIFFERENT-CARD',
            ])
        )->assertStatus(409)->assertJsonPath('reason', 'IDEMPOTENCY_CONFLICT');

        $this->assertSame(1, DB::table('machine_access_logs')->where('transaction_id', $requestId)->count());
    }

    public function test_same_request_id_with_different_machine_returns_conflict(): void
    {
        $fixture = $this->authorizedFixture(['machine_code' => 'MC-A']);
        $requestId = (string) Str::uuid();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, ['request_id' => $requestId])
        )->assertOk();

        $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture, [
                'request_id' => $requestId,
                'machine_code' => 'MC-B',
            ])
        )->assertStatus(409)->assertJsonPath('reason', 'IDEMPOTENCY_CONFLICT');
    }

    public function test_duplicate_event_id_is_idempotent(): void
    {
        $fixture = $this->authorizedFixture();
        $authorization = $this->withToken($fixture['token'])->postJson(
            '/api/v1/machine-auth/validate',
            $this->validationPayload($fixture)
        )->assertOk();

        $payload = [
            'event_id' => (string) Str::uuid(),
            'transaction_id' => $authorization->json('transaction_id'),
            'event_type' => 'ACCESS_GRANTED',
            'card_uid' => $fixture['card']->card_uid,
            'machine_code' => $fixture['machine']->machine_code,
            'occurred_at' => now()->utc()->toISOString(),
            'metadata' => ['source' => 'raspberry-pi'],
        ];

        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $payload)
            ->assertCreated()
            ->assertJsonPath('duplicate', false);
        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $payload)
            ->assertOk()
            ->assertJsonPath('duplicate', true);

        $this->assertSame(1, DB::table('machine_events')->where('event_id', $payload['event_id'])->count());
    }

    public function test_equivalent_event_timestamp_and_metadata_serialization_is_idempotent(): void
    {
        $fixture = $this->authorizedFixture();
        $transactionId = $this->authorizeTransaction($fixture);
        $eventId = (string) Str::uuid();
        $firstPayload = [
            'event_id' => $eventId,
            'transaction_id' => $transactionId,
            'event_type' => 'PLC_AUTHORIZED',
            'card_uid' => $fixture['card']->card_uid,
            'machine_code' => $fixture['machine']->machine_code,
            'occurred_at' => '2026-08-28T13:30:00.000000Z',
            'metadata' => ['source' => 'raspberry-pi', 'retry' => 1],
        ];
        $retryPayload = $firstPayload;
        $retryPayload['occurred_at'] = '2026-08-28 13:30:00+00:00';
        $retryPayload['metadata'] = ['retry' => 1, 'source' => 'raspberry-pi'];

        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $firstPayload)
            ->assertCreated();
        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $retryPayload)
            ->assertOk()
            ->assertJsonPath('duplicate', true);

        $this->assertSame(1, DB::table('machine_events')->where('event_id', $eventId)->count());
    }

    public function test_same_event_id_with_different_event_type_returns_conflict(): void
    {
        $fixture = $this->authorizedFixture();
        $transactionId = $this->authorizeTransaction($fixture);
        $payload = $this->eventPayload($fixture, $transactionId);

        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $payload)
            ->assertCreated();
        $payload['event_type'] = 'ACCESS_DENIED';
        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $payload)
            ->assertStatus(409)
            ->assertJsonPath('reason', 'IDEMPOTENCY_CONFLICT');
    }

    public function test_same_event_id_with_different_transaction_returns_conflict(): void
    {
        $fixture = $this->authorizedFixture();
        $firstTransaction = $this->authorizeTransaction($fixture);
        $secondTransaction = $this->authorizeTransaction($fixture);
        $payload = $this->eventPayload($fixture, $firstTransaction);

        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $payload)
            ->assertCreated();
        $payload['transaction_id'] = $secondTransaction;
        $this->withToken($fixture['token'])->postJson('/api/v1/machine-auth/events', $payload)
            ->assertStatus(409)
            ->assertJsonPath('reason', 'IDEMPOTENCY_CONFLICT');

        $this->assertSame(1, DB::table('machine_events')->where('event_id', $payload['event_id'])->count());
    }

    public function test_authorization_log_keeps_uid_and_machine_code_snapshots(): void
    {
        $fixture = $this->authorizedFixture(['machine_code' => 'MC-SNAPSHOT', 'card_uid' => 'CARD-SNAPSHOT']);
        $transactionId = $this->authorizeTransaction($fixture);

        $fixture['card']->update(['card_uid' => 'CARD-CHANGED']);
        $fixture['machine']->update(['machine_code' => 'MC-CHANGED']);

        $this->assertDatabaseHas('machine_access_logs', [
            'transaction_id' => $transactionId,
            'card_uid' => 'CARD-SNAPSHOT',
            'machine_code' => 'MC-SNAPSHOT',
        ]);
    }

    public function test_sync_only_exposes_permissions_for_authenticated_machine(): void
    {
        $fixture = $this->authorizedFixture(['machine_code' => 'MC-A', 'card_uid' => 'CARD-A']);
        $other = $this->authorizedFixture(['machine_code' => 'MC-B', 'card_uid' => 'CARD-B']);

        $response = $this->withToken($fixture['token'])->getJson('/api/v1/machine-auth/sync');

        $response->assertOk()
            ->assertJsonPath('machine.code', 'MC-A')
            ->assertJsonCount(1, 'permissions')
            ->assertJsonPath('permissions.0.card_uid', 'CARD-A');
        $this->assertNotSame($other['machine']->id, $response->json('machine.id'));
    }

    public function test_admin_can_open_machine_authorization_management_page(): void
    {
        $admin = User::create([
            'name' => 'Machine Admin',
            'email' => 'machine-admin@example.test',
            'npk' => '900001',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/machine-authorization')
            ->assertOk()
            ->assertSee('Machine Authorization')
            ->assertSee('RFID Card')
            ->assertSee('Mesin & Token', false);
    }

    public function test_non_admin_cannot_open_machine_authorization_management_page(): void
    {
        $fixture = $this->authorizedFixture();

        $this->actingAs($fixture['user'])
            ->get('/machine-authorization')
            ->assertForbidden();
    }

    public function test_admin_can_provision_machine_token_and_plaintext_is_flashed_once(): void
    {
        $admin = User::create([
            'name' => 'Token Admin',
            'email' => 'token-admin@example.test',
            'npk' => '900002',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);
        $machine = Machine::create([
            'machine_code' => 'MC-TOKEN',
            'machine_name' => 'Token Test Machine',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(
            route('machine-auth.machines.tokens.store', $machine),
            [
                'name' => 'raspberry-pi-test',
                'rotate_existing' => '0',
            ]
        );

        $response->assertRedirect()
            ->assertSessionHas('machine_plain_text_token')
            ->assertSessionHas('token_machine_code', 'MC-TOKEN');
        $this->assertSame(1, DB::table('personal_access_tokens')
            ->where('tokenable_type', Machine::class)
            ->where('tokenable_id', $machine->id)
            ->count());
        $this->assertStringContainsString('|', (string) session('machine_plain_text_token'));
    }

    private function authorizedFixture(array $overrides = []): array
    {
        $this->sequence++;
        $suffix = str_pad((string) $this->sequence, 3, '0', STR_PAD_LEFT);
        $user = User::create([
            'name' => 'Operator '.$suffix,
            'email' => 'operator'.$suffix.'@example.test',
            'npk' => str_pad((string) $this->sequence, 6, '0', STR_PAD_LEFT),
            'password' => Hash::make('password'),
            'role' => 'prod',
        ]);
        $machine = Machine::create([
            'line_id' => $overrides['line_id'] ?? null,
            'machine_code' => $overrides['machine_code'] ?? 'MC-'.$suffix,
            'machine_name' => 'Machine '.$suffix,
            'is_active' => true,
        ]);
        $card = RfidCard::create([
            'user_id' => $user->id,
            'card_uid' => $overrides['card_uid'] ?? '1667815'.$suffix,
            'is_active' => true,
        ]);
        $permission = MachinePermission::create([
            'user_id' => $user->id,
            'machine_id' => $machine->id,
            'is_allowed' => true,
        ]);
        $token = $machine->createToken('test-pi', ['machine-auth'])->plainTextToken;

        return compact('user', 'machine', 'card', 'permission', 'token');
    }

    private function validationPayload(array $fixture, array $overrides = []): array
    {
        return array_merge([
            'request_id' => (string) Str::uuid(),
            'card_uid' => $fixture['card']->card_uid,
            'machine_code' => $fixture['machine']->machine_code,
        ], $overrides);
    }

    private function authorizeTransaction(array $fixture): string
    {
        return (string) $this->withToken($fixture['token'])
            ->postJson('/api/v1/machine-auth/validate', $this->validationPayload($fixture))
            ->assertOk()
            ->json('transaction_id');
    }

    private function eventPayload(array $fixture, string $transactionId): array
    {
        return [
            'event_id' => (string) Str::uuid(),
            'transaction_id' => $transactionId,
            'event_type' => 'ACCESS_GRANTED',
            'card_uid' => $fixture['card']->card_uid,
            'machine_code' => $fixture['machine']->machine_code,
            'occurred_at' => now()->utc()->toISOString(),
            'metadata' => ['source' => 'raspberry-pi'],
        ];
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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('line_id')->nullable();
            $table->string('machine_code', 64)->unique();
            $table->string('machine_name');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('card_uid', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('machine_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('machine_id');
            $table->boolean('is_allowed')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'machine_id']);
        });
        Schema::create('machine_access_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('transaction_id')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->string('card_uid', 64);
            $table->string('machine_code', 64)->nullable();
            $table->string('result', 16);
            $table->string('reason', 64);
            $table->timestamp('occurred_at');
            $table->timestamp('cache_expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('machine_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->uuid('transaction_id');
            $table->unsignedBigInteger('machine_access_log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->string('event_type', 40);
            $table->string('card_uid', 64);
            $table->string('machine_code', 64);
            $table->timestamp('occurred_at');
            $table->json('metadata')->nullable();
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
