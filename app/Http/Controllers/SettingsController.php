<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private const DEFAULTS = [
        'landing_route' => 'role_default',
        'table_density' => 'comfortable',
        'bella_brief_notifications' => true,
        'bella_assistant_enabled' => true,
        'bella_answer_style' => 'concise',
        'confirm_logout' => false,
        'sidebar_mode' => 'remember',
    ];

    private const LANDING_PAGES = [
        'role_default' => 'Ikuti role default',
        'dashboard.overview' => 'Dashboard Overview',
        'dashboard.delivery' => 'Dashboard Delivery',
        'loadingList.index' => 'Loading List',
        'validation.index' => 'Validation',
        'quality.scan' => 'Quality Scan',
        'pis.index' => 'PIS Scan',
    ];

    public function index(Request $request)
    {
        return view('pages.settings', [
            'preferences' => $this->preferences($request),
            'landingPages' => self::LANDING_PAGES,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'landing_route' => ['required', Rule::in(array_keys(self::LANDING_PAGES))],
            'table_density' => ['required', Rule::in(['comfortable', 'compact'])],
            'bella_brief_notifications' => ['required', 'boolean'],
            'bella_assistant_enabled' => ['required', 'boolean'],
            'bella_answer_style' => ['required', Rule::in(['concise', 'detailed'])],
            'confirm_logout' => ['required', 'boolean'],
            'sidebar_mode' => ['required', Rule::in(['remember', 'expanded', 'collapsed'])],
        ]);

        $request->user()->update([
            'preferences' => array_merge($this->preferences($request), [
                'landing_route' => $validated['landing_route'],
                'table_density' => $validated['table_density'],
                'bella_brief_notifications' => $request->boolean('bella_brief_notifications'),
                'bella_assistant_enabled' => $request->boolean('bella_assistant_enabled'),
                'bella_answer_style' => $validated['bella_answer_style'],
                'confirm_logout' => $request->boolean('confirm_logout'),
                'sidebar_mode' => $validated['sidebar_mode'],
            ]),
        ]);

        return back()->with('settings_success', 'Preferences berhasil disimpan.');
    }

    private function preferences(Request $request): array
    {
        return array_merge(self::DEFAULTS, $request->user()->preferences ?? []);
    }
}
