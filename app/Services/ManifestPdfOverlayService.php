<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Tcpdf\Fpdi;

class ManifestPdfOverlayService
{
    /**
     * Overlay the configured image onto the given PDF file.
     *
     * @param string $sourcePdfPath Path to the uploaded PDF file
     * @param string|null $originalFileName Original filename for diagnostic logging
     * @return string Path to the newly created temporary processed PDF file
     *
     * @throws \RuntimeException if image or PDF processing fails
     */
    public function applyOverlay(string $sourcePdfPath, ?string $originalFileName = null): string
    {
        $config = config('manifest.overlay', []);
        $enabled = (bool) ($config['enabled'] ?? true);

        // If overlay is disabled, create a safe temporary copy of the original PDF
        if (! $enabled) {
            $tempCopy = $this->createTempPdfPath();
            if (! @copy($sourcePdfPath, $tempCopy)) {
                throw new \RuntimeException('Gagal menyalin file manifest sementara.');
            }
            return $tempCopy;
        }

        $imagePath = $this->resolveImagePath($config['image_path'] ?? null);
        $this->validateImage($imagePath);

        // Remove white background if enabled (creates clean transparent stamp like PNG)
        if (! empty($config['remove_white_bg'])) {
            try {
                $imagePath = $this->makeWhiteBackgroundTransparent($imagePath);
            } catch (\Throwable $e) {
                Log::warning('Manifest image background transparency removal skipped: ' . $e->getMessage());
            }
        }

        $boxX = (float) ($config['x'] ?? 73.0);
        $boxY = (float) ($config['y'] ?? 216.0);
        $boxW = (float) ($config['width'] ?? 22.0);
        $boxH = (float) ($config['height'] ?? 12.5);
        $preserveAspect = (bool) ($config['preserve_aspect_ratio'] ?? true);
        $rotation = (float) ($config['rotation'] ?? 0.0);
        $pagesConfig = $config['pages'] ?? 'all';

        [$drawX, $drawY, $drawW, $drawH] = $this->calculateDrawCoordinates(
            $imagePath,
            $boxX,
            $boxY,
            $boxW,
            $boxH,
            $preserveAspect
        );

        $tempOutputPath = $this->createTempPdfPath();

        try {
            $pdf = new Fpdi();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->SetCreator('BELLA');
            $pdf->SetAuthor('BELLA');
            $pdf->SetTitle('Manifest');

            $pageCount = $pdf->setSourceFile($sourcePdfPath);
            if ($pageCount < 1) {
                throw new \RuntimeException('File PDF manifest tidak memiliki halaman yang valid.');
            }

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $width = (float) ($size['width'] ?? 0);
                $height = (float) ($size['height'] ?? 0);
                if ($width <= 0 || $height <= 0) {
                    $width = 210.0;
                    $height = 297.0;
                }

                $orientation = $width > $height ? 'L' : 'P';
                $pdf->AddPage($orientation, [$width, $height]);
                $pdf->useTemplate($templateId, 0, 0, $width, $height);

                if ($this->shouldOverlayPage($pageNo, $pageCount, $pagesConfig)) {
                    if ($rotation != 0.0) {
                        $centerX = $drawX + ($drawW / 2);
                        $centerY = $drawY + ($drawH / 2);
                        $pdf->StartTransform();
                        $pdf->Rotate($rotation, $centerX, $centerY);
                    }

                    $pdf->Image($imagePath, $drawX, $drawY, $drawW, $drawH);

                    if ($rotation != 0.0) {
                        $pdf->StopTransform();
                    }
                }
            }

            $pdf->Output($tempOutputPath, 'F');

            if (! is_file($tempOutputPath) || filesize($tempOutputPath) === 0) {
                throw new \RuntimeException('Gagal menghasilkan file PDF manifest dengan overlay gambar.');
            }

            Log::info('Manifest PDF image overlay applied successfully', [
                'file' => $originalFileName ? basename($originalFileName) : basename($sourcePdfPath),
                'pages' => $pageCount,
                'draw_box' => ['x' => $drawX, 'y' => $drawY, 'w' => $drawW, 'h' => $drawH],
            ]);

            return $tempOutputPath;
        } catch (\Throwable $e) {
            if (is_file($tempOutputPath)) {
                @unlink($tempOutputPath);
            }

            Log::error('Manifest PDF image overlay processing failed', [
                'file' => $originalFileName ? basename($originalFileName) : basename($sourcePdfPath),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Gagal memproses overlay gambar manifest: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Resolve the image path, with project asset fallbacks.
     */
    public function resolveImagePath(?string $configuredPath): string
    {
        $candidates = [];

        if ($configuredPath) {
            $candidates[] = $configuredPath;
            $candidates[] = public_path($configuredPath);
            $candidates[] = public_path('assets/img/' . ltrim($configuredPath, '/\\'));
            $candidates[] = public_path('assets/img/' . basename($configuredPath));
            $candidates[] = base_path($configuredPath);
            $candidates[] = storage_path('app/' . ltrim($configuredPath, '/\\'));
        }

        // Check configured candidates first
        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate)) {
                return str_replace('\\', '/', realpath($candidate) ?: $candidate);
            }
        }

        // Project defaults
        foreach ([
            public_path('assets/img/stamp.jpeg'),
            public_path('assets/img/stamp.jpg'),
            public_path('assets/img/stamp.png'),
            public_path('assets/img/bella.png'),
        ] as $fallback) {
            if (is_file($fallback)) {
                return str_replace('\\', '/', realpath($fallback) ?: $fallback);
            }
        }

        return (string) $configuredPath;
    }

    /**
     * Validate that the image file exists and can be read.
     */
    public function validateImage(string $imagePath): void
    {
        if ($imagePath === '' || ! is_file($imagePath)) {
            throw new \RuntimeException("Asset gambar overlay tidak ditemukan: {$imagePath}");
        }

        if (! is_readable($imagePath)) {
            throw new \RuntimeException("Asset gambar overlay tidak dapat dibaca: {$imagePath}");
        }

        $info = @getimagesize($imagePath);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            throw new \RuntimeException("Asset gambar overlay bukan format gambar yang valid.");
        }
    }

    /**
     * Calculate draw coordinates and dimensions, preserving aspect ratio if configured.
     *
     * @return array{0: float, 1: float, 2: float, 3: float} [x, y, width, height]
     */
    public function calculateDrawCoordinates(
        string $imagePath,
        float $boxX,
        float $boxY,
        float $boxW,
        float $boxH,
        bool $preserveAspect
    ): array {
        if (! $preserveAspect || $boxW <= 0 || $boxH <= 0) {
            return [$boxX, $boxY, $boxW, $boxH];
        }

        $info = @getimagesize($imagePath);
        $origW = (float) ($info[0] ?? 0);
        $origH = (float) ($info[1] ?? 0);

        if ($origW <= 0 || $origH <= 0) {
            return [$boxX, $boxY, $boxW, $boxH];
        }

        $imgAspect = $origW / $origH;
        $boxAspect = $boxW / $boxH;

        if ($imgAspect > $boxAspect) {
            // Image is wider than bounding box -> constrained by width
            $drawW = $boxW;
            $drawH = $boxW / $imgAspect;
        } else {
            // Image is taller than bounding box -> constrained by height
            $drawH = $boxH;
            $drawW = $boxH * $imgAspect;
        }

        // Center inside the bounding box
        $drawX = $boxX + (($boxW - $drawW) / 2);
        $drawY = $boxY + (($boxH - $drawH) / 2);

        return [
            round($drawX, 3),
            round($drawY, 3),
            round($drawW, 3),
            round($drawH, 3),
        ];
    }

    /**
     * Determine whether the given page index should receive the image overlay.
     */
    public function shouldOverlayPage(int $pageNumber, int $totalPages, mixed $configPages): bool
    {
        if (is_array($configPages)) {
            return in_array($pageNumber, $configPages, true)
                || in_array((string) $pageNumber, $configPages, true);
        }

        $val = strtolower(trim((string) $configPages));
        if ($val === '' || $val === 'all' || $val === '*') {
            return true;
        }

        if ($val === 'first') {
            return $pageNumber === 1;
        }

        if ($val === 'last') {
            return $pageNumber === $totalPages;
        }

        $targetPages = array_filter(array_map('trim', explode(',', $val)));
        return in_array((string) $pageNumber, $targetPages, true);
    }

    /**
     * Remove white/light background from an image and save as a transparent PNG.
     * Preserves anti-aliased edges for stamps/signatures.
     */
    public function makeWhiteBackgroundTransparent(string $imagePath, int $threshold = 230): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            return $imagePath;
        }

        // Determine destination transparent PNG path
        $dir = dirname($imagePath);
        $filename = pathinfo($imagePath, PATHINFO_FILENAME);
        $cachePath = $dir . DIRECTORY_SEPARATOR . $filename . '.png';

        // If a transparent PNG already exists and is newer than source, use it directly
        if (is_file($cachePath) && filemtime($cachePath) >= filemtime($imagePath)) {
            return $cachePath;
        }

        // Fallback to system temp directory if target folder is not writable
        if (! is_writable($dir)) {
            $cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'transparent_' . md5($imagePath) . '.png';
            if (is_file($cachePath) && filemtime($cachePath) >= filemtime($imagePath)) {
                return $cachePath;
            }
        }

        $raw = @file_get_contents($imagePath);
        if (! $raw) {
            return $imagePath;
        }

        $src = @imagecreatefromstring($raw);
        if (! $src) {
            return $imagePath;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        $dst = imagecreatetruecolor($w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgb = imagecolorat($src, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = ($rgb >> 0) & 0xFF;

                // For a blue/dark stamp on white paper, paper has high R, G, B
                $whiteness = min($r, $g, $b);

                if ($whiteness >= $threshold) {
                    // Fully transparent paper background
                    imagesetpixel($dst, $x, $y, $transparent);
                } elseif ($whiteness > ($threshold - 40)) {
                    // Smooth anti-aliased edge transition
                    $alpha = (int) round((($whiteness - ($threshold - 40)) / 40) * 127);
                    $color = imagecolorallocatealpha($dst, $r, $g, $b, $alpha);
                    imagesetpixel($dst, $x, $y, $color);
                } else {
                    // Fully opaque ink
                    $color = imagecolorallocatealpha($dst, $r, $g, $b, 0);
                    imagesetpixel($dst, $x, $y, $color);
                }
            }
        }

        @imagepng($dst, $cachePath, 9);
        @imagedestroy($src);
        @imagedestroy($dst);

        return is_file($cachePath) ? $cachePath : $imagePath;
    }

    /**
     * Generate a unique temporary PDF file path in the system temp directory.
     */
    protected function createTempPdfPath(): string
    {
        $dir = sys_get_temp_dir();
        $unique = 'bella_manifest_' . uniqid('', true) . '.pdf';
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $unique;
    }
}
