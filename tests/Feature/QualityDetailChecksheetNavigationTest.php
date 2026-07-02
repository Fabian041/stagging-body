<?php

namespace Tests\Feature;

use Tests\TestCase;

class QualityDetailChecksheetNavigationTest extends TestCase
{
    public function test_quality_history_route_points_to_quality_detail_checksheet_page(): void
    {
        $this->assertSame(url('/quality/history'), route('quality.history'));
    }
}