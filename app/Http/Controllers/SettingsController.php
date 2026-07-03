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
        'sidenav_theme' => 'light',
        'fab_color' => 'blue',
    ];

    /** Pilihan warna tombol chat BELLA (floating). Key disimpan di preferences, hex dipakai layout. */
    public const FAB_COLORS = [
        'blue' => '#1498df',
        'navy' => '#294795',
        'teal' => '#0f9d8f',
        'green' => '#16a34a',
        'purple' => '#7c3aed',
        'rose' => '#e11d48',
        'slate' => '#334155',
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
            'fabColors' => self::FAB_COLORS,
            'canManageSystemSettings' => $request->user()->isSuperAdmin(),
        ]);
    }

    public function update(Request $request)
    {
        $preferences = $this->preferences($request);
        $canManageSystemSettings = $request->user()->isSuperAdmin();

        if ($request->has('reset_preferences')) {
            $reset = self::DEFAULTS;

            if (! $canManageSystemSettings) {
                $reset = array_merge($preferences, [
                    'table_density' => self::DEFAULTS['table_density'],
                    'bella_brief_notifications' => self::DEFAULTS['bella_brief_notifications'],
                    'confirm_logout' => self::DEFAULTS['confirm_logout'],
                    'sidebar_mode' => self::DEFAULTS['sidebar_mode'],
                    'sidenav_theme' => self::DEFAULTS['sidenav_theme'],
                    'fab_color' => self::DEFAULTS['fab_color'],
                ]);
            }

            $request->user()->update(['preferences' => $reset]);

            return back()->with('settings_success', 'Preferences berhasil direset ke default.');
        }

        $rules = [
            'table_density' => ['required', Rule::in(['comfortable', 'compact'])],
            'bella_brief_notifications' => ['required', 'boolean'],
            'confirm_logout' => ['required', 'boolean'],
            'sidebar_mode' => ['required', Rule::in(['remember', 'expanded', 'collapsed'])],
            'sidenav_theme' => ['required', Rule::in(['light', 'dark'])],
            'fab_color' => ['required', Rule::in(array_keys(self::FAB_COLORS))],
        ];

        if ($canManageSystemSettings) {
            $rules += [
                'landing_route' => ['required', Rule::in(array_keys(self::LANDING_PAGES))],
                'bella_assistant_enabled' => ['required', 'boolean'],
                'bella_answer_style' => ['required', Rule::in(['concise', 'detailed'])],
            ];
        }

        $validated = $request->validate($rules);

        $nextPreferences = [
            'table_density' => $validated['table_density'],
            'bella_brief_notifications' => $request->boolean('bella_brief_notifications'),
            'confirm_logout' => $request->boolean('confirm_logout'),
            'sidebar_mode' => $validated['sidebar_mode'],
            'sidenav_theme' => $validated['sidenav_theme'],
            'fab_color' => $validated['fab_color'],
        ];

        if ($canManageSystemSettings) {
            $nextPreferences += [
                'landing_route' => $validated['landing_route'],
                'bella_assistant_enabled' => $request->boolean('bella_assistant_enabled'),
                'bella_answer_style' => $validated['bella_answer_style'],
            ];
        }

        $request->user()->update([
            'preferences' => array_merge($preferences, $nextPreferences),
        ]);

        return back()->with('settings_success', 'Preferences berhasil disimpan.');
    }

    private function preferences(Request $request): array
    {
        return array_merge(self::DEFAULTS, $request->user()->preferences ?? []);
    }
}
