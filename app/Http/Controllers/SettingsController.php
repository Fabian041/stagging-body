<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function devices()
    {
        return view('pages.manage_devices', [
            'deviceSessions' => $this->deviceSessions(),
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

    public function deviceHeartbeat(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
            'current_page' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'screen_size' => ['nullable', 'string', 'max:40'],
            'language' => ['nullable', 'string', 'max:40'],
            'online' => ['nullable', 'boolean'],
        ]);

        // Device monitoring khusus scanner pulling (route /pulling) — abaikan heartbeat dari halaman lain.
        if (($validated['current_page'] ?? '') !== '/pulling') {
            return response()->json(['reset_required' => false]);
        }

        $now = now();
        $existing = DB::table('device_sessions')
            ->where('device_id', $validated['device_id'])
            ->first();
        $pageChanged = $existing && ($existing->current_page !== ($validated['current_page'] ?? null));

        $payload = [
            'user_id' => $request->user()->id,
            'current_page' => $validated['current_page'] ?? null,
            'timezone' => $validated['timezone'] ?? null,
            'ip_address' => $request->ip(),
            'screen_size' => $validated['screen_size'] ?? null,
            'language' => $validated['language'] ?? null,
            'is_online_reported' => $request->boolean('online', true),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'last_seen_at' => $now,
            'updated_at' => $now,
        ];

        if ($existing) {
            if ((int) $existing->user_id !== (int) $request->user()->id) {
                $payload['reset_requested_at'] = null;
                $payload['reset_requested_by'] = null;
                $payload['reset_applied_at'] = null;
                $this->recordDeviceEvent($existing->id, $request->user()->id, 'user_changed', 'Device dipakai oleh user berbeda');
            }

            $updatePayload = $payload + [
                'heartbeat_count' => DB::raw('heartbeat_count + 1'),
            ];
            if ($existing->scanner_no === null) {
                $updatePayload['scanner_no'] = (int) (DB::table('device_sessions')->max('scanner_no') ?? 0) + 1;
            }

            DB::table('device_sessions')
                ->where('device_id', $validated['device_id'])
                ->update($updatePayload);

            if ($pageChanged) {
                $this->recordDeviceEvent($existing->id, $request->user()->id, 'page_changed', 'Pindah ke '.$payload['current_page']);
            }
        } else {
            $deviceId = DB::table('device_sessions')->insertGetId($payload + [
                'device_id' => $validated['device_id'],
                'heartbeat_count' => 1,
                'scanner_no' => (int) (DB::table('device_sessions')->max('scanner_no') ?? 0) + 1,
                'created_at' => $now,
            ]);
            $this->recordDeviceEvent($deviceId, $request->user()->id, 'registered', 'Device terdaftar');
        }

        $device = DB::table('device_sessions')
            ->where('device_id', $validated['device_id'])
            ->first(['reset_requested_at', 'reset_applied_at']);

        return response()->json([
            'reset_required' => $this->resetPending($device),
        ]);
    }

    public function deviceResetApplied(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
        ]);

        $session = DB::table('device_sessions')
            ->where('device_id', $validated['device_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        DB::table('device_sessions')
            ->where('device_id', $validated['device_id'])
            ->where('user_id', $request->user()->id)
            ->update([
                'reset_applied_at' => now(),
                'updated_at' => now(),
            ]);

        if ($session) {
            $this->recordDeviceEvent($session->id, $request->user()->id, 'reset_applied', 'Reset local data diterapkan');
        }

        return response()->json(['ok' => true]);
    }

    public function reportDeviceError(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:255'],
            'line' => ['nullable', 'integer'],
            'column' => ['nullable', 'integer'],
        ]);

        $session = DB::table('device_sessions')
            ->where('device_id', $validated['device_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if ($session) {
            DB::table('device_sessions')
                ->where('id', $session->id)
                ->update([
                    'last_error_message' => $validated['message'],
                    'last_error_at' => now(),
                    'updated_at' => now(),
                ]);

            $this->recordDeviceEvent($session->id, $request->user()->id, 'client_error', $validated['message'], [
                'source' => $validated['source'] ?? null,
                'line' => $validated['line'] ?? null,
                'column' => $validated['column'] ?? null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function updateDeviceLabel(Request $request, int $device)
    {
        $validated = $request->validate([
            'device_label' => ['nullable', 'string', 'max:80'],
        ]);

        $label = trim($validated['device_label'] ?? '');
        $session = DB::table('device_sessions')->where('id', $device)->first();
        abort_if(! $session, 404);

        DB::table('device_sessions')
            ->where('id', $device)
            ->update([
                'device_label' => $label !== '' ? $label : null,
                'updated_at' => now(),
            ]);

        $this->recordDeviceEvent($device, $request->user()->id, 'label_updated', $label !== '' ? 'Label device diubah menjadi '.$label : 'Label device dikosongkan');

        return back()->with('settings_success', 'Label device berhasil disimpan.');
    }

    public function resetDevice(Request $request, int $device)
    {
        $session = DB::table('device_sessions')->where('id', $device)->first();
        abort_if(! $session, 404);

        DB::table('device_sessions')
            ->where('id', $device)
            ->update([
                'reset_requested_at' => now(),
                'reset_requested_by' => $request->user()->id,
                'reset_applied_at' => null,
                'reset_count' => DB::raw('reset_count + 1'),
                'updated_at' => now(),
            ]);

        $this->recordDeviceEvent($device, $request->user()->id, 'reset_requested', 'Reset local data diminta');

        return back()->with('settings_success', 'Reset device dikirim. Device akan clear data lokal saat heartbeat berikutnya.');
    }

    public function deleteDevice(int $device)
    {
        $session = DB::table('device_sessions')->where('id', $device)->first();
        abort_if(! $session, 404);

        DB::table('device_sessions')->where('id', $device)->delete();

        return back()->with('settings_success', 'Device berhasil dihapus dari monitoring.');
    }

    public function deviceMonitor(Request $request)
    {
        $summaryOnly = $request->boolean('summary');
        $deviceSessions = $this->deviceSessions(! $summaryOnly);

        if ($request->expectsJson()) {
            $payload = [
                'devices' => $deviceSessions->map(function ($device) {
                    return [
                        'device_id' => $device->device_id,
                        'heartbeat_count' => (int) $device->heartbeat_count,
                        'is_online' => (bool) $device->is_online,
                        'last_seen_label' => $device->last_seen_label,
                        'last_seen_at_label' => $device->last_seen_at_label,
                        'reset_pending' => (bool) $device->reset_pending,
                    ];
                })->values(),
                'pending_count' => $deviceSessions->where('reset_pending', true)->count(),
                'updated_at' => now()->format('H:i:s'),
            ];

            if (! $summaryOnly) {
                $payload['html'] = view('pages.partials.device-monitoring', [
                    'deviceSessions' => $deviceSessions,
                ])->render();
            }

            return response()->json($payload)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        return view('pages.partials.device-monitoring', [
            'deviceSessions' => $deviceSessions,
        ])->render();
    }

    private function preferences(Request $request): array
    {
        return array_merge(self::DEFAULTS, $request->user()->preferences ?? []);
    }

    private function deviceSessions(bool $withEvents = true)
    {
        $devices = DB::table('device_sessions')
            ->join('users', 'users.id', '=', 'device_sessions.user_id')
            ->leftJoin('users as reset_users', 'reset_users.id', '=', 'device_sessions.reset_requested_by')
            ->where('device_sessions.current_page', '/pulling')
            ->where('device_sessions.last_seen_at', '>=', now()->subHours(3))
            ->select([
                'device_sessions.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.role as user_role',
                'reset_users.name as reset_by_name',
            ])
            ->orderByDesc('device_sessions.last_seen_at')
            ->limit(80)
            ->get();

        $events = collect();
        if ($withEvents && $devices->isNotEmpty()) {
            $events = DB::table('device_session_events')
                ->whereIn('device_session_id', $devices->pluck('id'))
                ->orderByDesc('created_at')
                ->limit(400)
                ->get()
                ->groupBy('device_session_id');
        }

        return $devices
            ->map(function ($device) {
                $lastSeen = $device->last_seen_at ? Carbon::parse($device->last_seen_at) : null;
                $resetRequested = $device->reset_requested_at ? Carbon::parse($device->reset_requested_at) : null;
                $resetApplied = $device->reset_applied_at ? Carbon::parse($device->reset_applied_at) : null;
                $lastError = $device->last_error_at ? Carbon::parse($device->last_error_at) : null;

                $device->is_online = $lastSeen && $lastSeen->gt(now()->subMinutes(2));
                $device->last_seen_label = $lastSeen ? $lastSeen->diffForHumans() : '-';
                $device->last_seen_at_label = $lastSeen ? $lastSeen->format('d M Y H:i:s') : '-';
                $device->last_error_label = $lastError ? $lastError->diffForHumans() : null;
                $device->reset_pending = $resetRequested && (! $resetApplied || $resetRequested->gt($resetApplied));
                $device->device_short = substr($device->device_id, 0, 8);
                $device->browser_label = $this->browserLabel($device->user_agent);
                $device->display_label = $device->device_label
                    ?: ($device->scanner_no ? 'Scanner #'.$device->scanner_no : 'Device #'.$device->device_short);

                return $device;
            })
            ->map(function ($device) use ($events) {
                $device->events = ($events->get($device->id) ?? collect())
                    ->take(6)
                    ->map(function ($event) {
                        $event->time_label = Carbon::parse($event->created_at)->diffForHumans();
                        return $event;
                    });

                return $device;
            });
    }

    private function resetPending($device): bool
    {
        if (! $device || ! $device->reset_requested_at) {
            return false;
        }

        if (! $device->reset_applied_at) {
            return true;
        }

        return Carbon::parse($device->reset_requested_at)->gt(Carbon::parse($device->reset_applied_at));
    }

    private function recordDeviceEvent(int $deviceId, ?int $userId, string $type, string $message, array $meta = []): void
    {
        DB::table('device_session_events')->insert([
            'device_session_id' => $deviceId,
            'user_id' => $userId,
            'type' => $type,
            'message' => substr($message, 0, 255),
            'meta' => $meta ? json_encode($meta) : null,
            'created_at' => now(),
        ]);
    }

    private function browserLabel(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        if (stripos($ua, 'Edg/') !== false) {
            return 'Microsoft Edge';
        }
        if (stripos($ua, 'Chrome/') !== false) {
            return 'Chrome';
        }
        if (stripos($ua, 'Firefox/') !== false) {
            return 'Firefox';
        }
        if (stripos($ua, 'Safari/') !== false) {
            return 'Safari';
        }

        return 'Unknown browser';
    }
}
