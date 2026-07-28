<?php

namespace Tests\Unit;

use App\Http\Controllers\PisController;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class PisPreviewImageLookupTest extends TestCase
{
    public function test_it_resolves_legacy_part_and_dock_filename_variants_on_the_server(): void
    {
        Storage::fake('pis');
        Storage::disk('pis')->put('8281074820WBY-OEM-TMMIN_SPD.webp', 'image');

        $method = new ReflectionMethod(PisController::class, 'resolvePisPreviewFileName');
        $method->setAccessible(true);

        $this->assertSame(
            '8281074820WBY-OEM-TMMIN_SPD.webp',
            $method->invoke(new PisController, '82810-74820-WBY', 'oem', 'tmmin spd')
        );
    }
}
