<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KanbanQzSetupTest extends TestCase
{
    private string $originalStoragePath;

    private string $testStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('npk')->nullable();
            $table->string('password');
            $table->string('role');
            $table->rememberToken();
            $table->timestamps();
        });

        $this->originalStoragePath = storage_path();
        $this->testStoragePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bella-qz-test-'.uniqid();
        app()->useStoragePath($this->testStoragePath);
    }

    protected function tearDown(): void
    {
        app()->useStoragePath($this->originalStoragePath);
        $this->deleteDirectory($this->testStoragePath);

        parent::tearDown();
    }

    public function test_only_admin_can_generate_qz_keypair_directly_on_active_server(): void
    {
        $ppic = $this->user('ppic');
        $admin = $this->user('admin');

        $this->actingAs($ppic)
            ->withServerVariables(['REMOTE_ADDR' => '10.10.10.20'])
            ->get('/dashboard/kanban/qz-setup')
            ->assertForbidden();

        $response = $this->actingAs($admin)
            ->withServerVariables(['REMOTE_ADDR' => '10.10.10.20'])
            ->get('/dashboard/kanban/qz-setup');

        $response
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('created', true)
            ->assertJsonPath('rotated', false);

        $this->assertFileExists($this->testStoragePath.'/app/qz-signing/private-key.pem');
        $certificatePath = $this->testStoragePath.'/app/qz-signing/digital-certificate.txt';
        $this->assertFileExists($certificatePath);
        $certificate = openssl_x509_parse(file_get_contents($certificatePath));
        $this->assertStringContainsString(
            'CA:TRUE',
            strtoupper(str_replace(' ', '', $certificate['extensions']['basicConstraints'] ?? ''))
        );

        $this->actingAs($admin)
            ->get('/dashboard/kanban/qz-cert')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        $this->actingAs($admin)
            ->withServerVariables(['REMOTE_ADDR' => '10.10.10.20'])
            ->get('/dashboard/kanban/qz-setup')
            ->assertOk()
            ->assertJsonPath('created', false)
            ->assertJsonPath('rotated', false);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => uniqid($role.'-', true).'@example.test',
            'npk' => uniqid('NPK-', true),
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = array_diff(scandir($path) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $path.DIRECTORY_SEPARATOR.$item;
            is_dir($itemPath) ? $this->deleteDirectory($itemPath) : unlink($itemPath);
        }

        rmdir($path);
    }
}
