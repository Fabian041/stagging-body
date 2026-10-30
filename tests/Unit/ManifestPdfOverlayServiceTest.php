<?php

namespace Tests\Unit;

use App\Services\ManifestPdfOverlayService;
use Tests\TestCase;

class ManifestPdfOverlayServiceTest extends TestCase
{
    protected ManifestPdfOverlayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ManifestPdfOverlayService();
    }

    public function test_it_correctly_calculates_draw_coordinates_preserving_aspect_ratio_for_square_stamp(): void
    {
        $imagePath = public_path('assets/img/stamp.jpeg');
        if (! is_file($imagePath)) {
            $this->markTestSkipped('Sample image stamp.jpeg is not present');
        }

        $boxX = 73.0;
        $boxY = 216.0;
        $boxW = 22.0;
        $boxH = 12.5;

        [$drawX, $drawY, $drawW, $drawH] = $this->service->calculateDrawCoordinates(
            $imagePath,
            $boxX,
            $boxY,
            $boxW,
            $boxH,
            true
        );

        // Circular/square stamp must fit within boxH (12.5) and center horizontally in box
        $this->assertLessThanOrEqual(12.5, $drawH);
        $this->assertLessThanOrEqual(22.0, $drawW);
        $this->assertGreaterThan(73.0, $drawX);
        $this->assertGreaterThanOrEqual(216.0, $drawY);
    }

    public function test_it_uses_exact_bounding_box_when_preserve_aspect_ratio_is_false(): void
    {
        $imagePath = public_path('assets/img/stamp.jpeg');
        if (! is_file($imagePath)) {
            $this->markTestSkipped('Sample image stamp.jpeg is not present');
        }

        $coords = $this->service->calculateDrawCoordinates(
            $imagePath,
            73.0,
            216.0,
            22.0,
            12.5,
            false
        );

        $this->assertEquals([73.0, 216.0, 22.0, 12.5], $coords);
    }

    public function test_it_evaluates_target_pages_correctly(): void
    {
        // 'all'
        $this->assertTrue($this->service->shouldOverlayPage(1, 5, 'all'));
        $this->assertTrue($this->service->shouldOverlayPage(3, 5, 'all'));
        $this->assertTrue($this->service->shouldOverlayPage(5, 5, 'all'));

        // 'first'
        $this->assertTrue($this->service->shouldOverlayPage(1, 5, 'first'));
        $this->assertFalse($this->service->shouldOverlayPage(2, 5, 'first'));

        // 'last'
        $this->assertFalse($this->service->shouldOverlayPage(1, 5, 'last'));
        $this->assertFalse($this->service->shouldOverlayPage(4, 5, 'last'));
        $this->assertTrue($this->service->shouldOverlayPage(5, 5, 'last'));

        // Comma-separated list '1,3'
        $this->assertTrue($this->service->shouldOverlayPage(1, 5, '1,3'));
        $this->assertFalse($this->service->shouldOverlayPage(2, 5, '1,3'));
        $this->assertTrue($this->service->shouldOverlayPage(3, 5, '1,3'));
        $this->assertFalse($this->service->shouldOverlayPage(4, 5, '1,3'));
    }

    public function test_it_throws_exception_for_missing_overlay_image(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Asset gambar overlay tidak ditemukan');

        $this->service->validateImage('/nonexistent/path/stamp.png');
    }

    public function test_it_successfully_applies_image_overlay_to_manifest_pdf(): void
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

        $outputPath = $this->service->applyOverlay($samplePdf, '4260569087.pdf');

        $this->assertFileExists($outputPath);
        $this->assertGreaterThan(1000, filesize($outputPath));

        // Check PDF magic header
        $handle = fopen($outputPath, 'rb');
        $header = fread($handle, 5);
        fclose($handle);
        $this->assertSame('%PDF-', $header);

        @unlink($outputPath);
    }
}
