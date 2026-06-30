<?php

namespace Tests\Feature;

use Tests\TestCase;

class QualityDetailChecksheetNavigationTest extends TestCase
{
    public function test_quality_detail_checksheet_button_on_dashboard_home_uses_quality_history_route(): void
    {
        $viewPath = resource_path('views/pages/dashboard_home.blade.php');
        $viewContent = file_get_contents($viewPath);

        $this->assertMatchesRegularExpression(
            "/'title' => 'Quality'.*?'route' => 'quality\\.history'.*?'label' => 'Detail Scan'/s",
            $viewContent
        );
        $this->assertDoesNotMatchRegularExpression(
            "/'title' => 'Quality'.*?'route' => 'loadingList\\.index'.*?'label' => 'Detail Scan'/s",
            $viewContent
        );
    }

    public function test_quality_history_route_points_to_quality_detail_checksheet_page(): void
    {
        $this->assertSame(url('/quality/history'), route('quality.history'));
    }
}