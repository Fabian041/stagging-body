<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManifestUploadWithOverlayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('manifest-source');
    }

    public function test_manifest_upload_process_inserts_image_and_stores_in_manifest_source(): void
    {
        $samplePdf = storage_path('app/manifest-source/4260569087.pdf');
        $imagePath = public_path('assets/img/stamp.jpeg');

        if (! is_file($samplePdf) || ! is_file($imagePath)) {
            $this->markTestSkipped('Sample PDF or image is not present');
        }

        config([
            'manifest.overlay.enabled' => true,
            'manifest.overlay.image_path' => $imagePath,
            'manifest.overlay.x' => 73.0,
            'manifest.overlay.y' => 216.0,
            'manifest.overlay.width' => 22.0,
            'manifest.overlay.height' => 12.5,
            'manifest.overlay.preserve_aspect_ratio' => true,
            'manifest.overlay.pages' => 'all',
        ]);

        $user = new User([
            'id' => 1,
            'name' => 'Tester',
            'role' => 'admin',
        ]);

        $uploadedFile = new UploadedFile(
            $samplePdf,
            '4260569087.pdf',
            'application/pdf',
            null,
            true
        );

        $response = $this->actingAs($user)
            ->post(route('dashboard.manifestUploadProcess'), [
                'files' => [$uploadedFile],
            ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('count', 1);

        $this->assertTrue(Storage::disk('local')->exists('manifest-source/4260569087.pdf'));
        $content = Storage::disk('local')->get('manifest-source/4260569087.pdf');
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_manifest_upload_process_fails_gracefully_when_overlay_image_is_invalid(): void
    {
        $samplePdf = storage_path('app/manifest-source/4260569087.pdf');

        if (! is_file($samplePdf)) {
            $this->markTestSkipped('Sample PDF is not present');
        }

        config([
            'manifest.overlay.enabled' => true,
            'manifest.overlay.image_path' => '/invalid/path/missing-stamp.png',
        ]);

        $user = new User([
            'id' => 1,
            'name' => 'Tester',
            'role' => 'admin',
        ]);

        $uploadedFile = new UploadedFile(
            $samplePdf,
            '4260569087.pdf',
            'application/pdf',
            null,
            true
        );

        $response = $this->actingAs($user)
            ->post(route('dashboard.manifestUploadProcess'), [
                'files' => [$uploadedFile],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);

        // Ensure nothing was saved into manifest-source
        $this->assertFalse(Storage::disk('local')->exists('manifest-source/4260569087.pdf'));
    }
}
