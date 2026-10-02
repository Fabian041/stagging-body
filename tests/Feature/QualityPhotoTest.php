<?php

namespace Tests\Feature;

use App\Models\InternalPart;
use App\Models\Line;
use App\Models\MasterItemCheck;
use App\Models\MasterStandard;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QualityPhotoTest extends TestCase
{
    private ?string $uploadedPhoto = null;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        DB::connection()->getPdo()->sqliteCreateFunction('CHAR_LENGTH', 'strlen', 1);

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        if ($this->uploadedPhoto) {
            File::delete($this->uploadedPhoto);
        }

        parent::tearDown();
    }

    public function test_quality_scan_uses_separate_quality_photo_uploaded_from_internal_part_master(): void
    {
        $line = Line::create(['name' => 'AS526', 'is_active' => true]);
        $part = InternalPart::create([
            'line_id' => $line->id,
            'part_number' => 'PART-QUALITY-001',
            'back_number' => 'KP3Z',
            'part_name' => 'QUALITY TEST PART',
            'photo' => 'existing-pis-photo.jpg',
        ]);
        $item = MasterItemCheck::create([
            'part_id' => $part->id,
            'item_check' => 'Mass check',
            'jumlah_check' => 1,
        ]);
        MasterStandard::create([
            'item_check_id' => $item->id,
            'standard' => 'Mass standard',
            'min_value' => 140,
            'max_value' => 158,
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/quality/lookup-kanban', ['kanban_code' => 'SCAN PART-QUALITY-001'])
            ->assertOk()
            ->assertJsonPath('data.photo', null);

        $image = UploadedFile::fake()->createWithContent(
            'quality.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Wl9sAAAAASUVORK5CYII=')
        );

        $this->actingAs($admin)
            ->put("/dashboard/master/parts/{$part->id}", [
                'line_id' => $line->id,
                'part_number' => $part->part_number,
                'back_number' => $part->back_number,
                'part_name' => $part->part_name,
                'quality_photo' => $image,
            ])
            ->assertRedirect('/dashboard/master/parts');

        $filename = 'PART-QUALITY-001-quality.png';
        $this->uploadedPhoto = public_path('assets/img/quality/'.$filename);

        $this->assertFileExists($this->uploadedPhoto);
        $this->assertDatabaseHas('internal_parts', [
            'id' => $part->id,
            'photo' => 'existing-pis-photo.jpg',
            'quality_photo' => $filename,
        ]);

        $this->actingAs($admin)
            ->postJson('/quality/lookup-kanban', ['kanban_code' => 'SCAN PART-QUALITY-001'])
            ->assertOk()
            ->assertJsonPath('data.photo', url('/assets/img/quality/'.$filename));
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Administrator',
            'email' => 'quality-photo@example.test',
            'npk' => 'QUALITY-PHOTO',
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
            $table->string('part_number')->unique();
            $table->string('back_number')->nullable();
            $table->string('part_name')->nullable();
            $table->string('photo')->nullable();
            $table->string('quality_photo')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('part_number')->nullable();
            $table->string('back_number')->nullable();
            $table->integer('qty_per_kanban')->nullable();
            $table->timestamps();
        });

        Schema::create('master_item_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_id');
            $table->string('item_check');
            $table->integer('jumlah_check')->nullable();
            $table->timestamps();
        });

        Schema::create('master_standards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_check_id');
            $table->string('standard')->nullable();
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->timestamps();
        });
    }
}
