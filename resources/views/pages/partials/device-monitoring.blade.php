<section class="settings-panel" data-device-monitor-section data-device-monitor-url="{{ route('settings.devices.monitor') }}">
    <div class="settings-section-head">
        <div>
            <h2 class="settings-section-title">Device Monitoring</h2>
            <div class="settings-section-copy">Pantau device aktif dan reset local data untuk device tertentu.</div>
        </div>
        <div class="settings-section-head-right">
            <span class="settings-live"><span class="dot"></span> LIVE</span>
            <span class="settings-device-status">{{ $deviceSessions->count() }} device</span>
        </div>
    </div>

    <div class="settings-device-list">
        @forelse ($deviceSessions as $device)
            <div class="settings-device-row" data-device-card="{{ $device->device_id }}"
                data-device-reset-state="{{ $device->reset_pending ? '1' : '0' }}">
                <div class="settings-device-user">
                    <div class="settings-device-card-top">
                        <div class="settings-device-user">
                            <div class="settings-device-name">{{ $device->display_label }}</div>
                            <div class="settings-device-meta">
                                {{ $device->user_name }} · {{ $device->user_role }} · #{{ $device->device_short }}
                            </div>
                        </div>
                        <span class="settings-device-status {{ $device->is_online ? 'is-online' : '' }}"
                            data-device-online>
                            <i class="fas fa-circle"></i>
                            {{ $device->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    @if ($device->reset_pending)
                        <span class="settings-device-status is-pending" data-device-reset-pending>
                            <i class="fas fa-sync-alt"></i>
                            Reset pending
                        </span>
                    @endif
                </div>
                <div class="settings-device-main">
                    <div class="settings-device-stat">
                        <div class="settings-label mb-1">Halaman terakhir</div>
                        <div class="settings-device-page">{{ $device->current_page ?: '-' }}</div>
                    </div>
                    <div class="settings-device-stat">
                        <div class="settings-label mb-1">Terakhir aktif</div>
                        <div class="settings-device-time" data-device-last-seen title="{{ $device->last_seen_at_label }}">
                            {{ $device->last_seen_label }}
                        </div>
                    </div>
                        <div class="settings-device-stat">
                            <div class="settings-label mb-1">Heartbeat</div>
                            <div class="settings-heartbeat {{ $device->is_online ? 'is-online' : '' }}"
                                data-device-heartbeat="{{ $device->device_id }}"
                                data-heartbeat-count="{{ (int) $device->heartbeat_count }}"
                                aria-label="Heartbeat {{ number_format($device->heartbeat_count) }}">
                                <svg viewBox="0 0 200 34" preserveAspectRatio="none" aria-hidden="true">
                                    <line class="settings-heartbeat-base" x1="0" y1="17" x2="200" y2="17" />
                                    <polyline class="settings-heartbeat-line"
                                        points="0,17 28,17 35,13 42,22 51,4 62,29 72,17 108,17 116,14 123,20 132,8 142,25 152,17 200,17" />
                                </svg>
                                <span class="settings-heartbeat-count">HB {{ number_format($device->heartbeat_count) }}</span>
                            </div>
                            <div class="settings-device-meta settings-heartbeat-meta">
                                {{ $device->browser_label }} · Reset {{ number_format($device->reset_count) }}
                            </div>
                        </div>
                    @if ($device->last_error_message)
                        <div class="settings-device-stat">
                            <div class="settings-label mb-1">Last error</div>
                            <div class="settings-device-meta">{{ \Illuminate\Support\Str::limit($device->last_error_message, 88) }}</div>
                        </div>
                    @endif
                </div>
                <div class="settings-device-actions">
                    <button type="button" class="settings-btn is-muted" data-device-modal-open="deviceModal{{ $device->id }}">
                        <i class="fas fa-info-circle"></i>
                        Detail
                    </button>
                    <form method="POST" action="{{ route('settings.devices.reset', $device->id) }}" data-device-reset-form>
                        @csrf
                        <button type="submit" class="settings-btn is-danger-outline" {{ $device->reset_pending ? 'disabled' : '' }}>
                            <i class="fas fa-broom"></i>
                            Reset Device
                        </button>
                    </form>
                    <form method="POST" action="{{ route('settings.devices.delete', $device->id) }}" data-device-delete-form>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="settings-btn is-danger">
                            <i class="fas fa-trash"></i>
                            Delete Device
                        </button>
                    </form>
                </div>
            </div>

            <div class="settings-modal" id="deviceModal{{ $device->id }}" aria-hidden="true">
                <div class="settings-modal-backdrop" data-device-modal-close></div>
                <div class="settings-modal-card is-device" role="dialog" aria-modal="true" aria-labelledby="deviceModalTitle{{ $device->id }}">
                    <div class="settings-device-modal-head">
                        <div>
                            <h2 class="settings-modal-title" id="deviceModalTitle{{ $device->id }}">{{ $device->display_label }}</h2>
                            <div class="settings-modal-copy">
                                {{ $device->user_name }} · {{ $device->user_role }} · {{ $device->user_email }} · #{{ $device->device_short }}
                            </div>
                        </div>
                        <div class="settings-device-modal-actions">
                            <span class="settings-device-status {{ $device->is_online ? 'is-online' : '' }}">
                                <i class="fas fa-circle"></i>
                                {{ $device->is_online ? 'Online' : 'Offline' }}
                            </span>
                            <button type="button" class="settings-btn is-muted" data-device-modal-close>Tutup</button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.devices.label', $device->id) }}" class="settings-device-label-form">
                        @csrf
                        <input type="text" name="device_label" value="{{ $device->device_label }}"
                            class="settings-device-label-input" maxlength="80" placeholder="Nama device, contoh: PC Pulling Line 1">
                        <button type="submit" class="settings-icon-btn" title="Simpan label">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>

                    <div class="settings-health-grid">
                        <div class="settings-health-card">
                            <div class="settings-health-label">Browser</div>
                            <div class="settings-health-value">{{ $device->browser_label }}</div>
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">IP / Timezone</div>
                            <div class="settings-health-value">{{ $device->ip_address ?: '-' }} · {{ $device->timezone ?: '-' }}</div>
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">Screen / Language</div>
                            <div class="settings-health-value">{{ $device->screen_size ?: '-' }} · {{ $device->language ?: '-' }}</div>
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">Heartbeat / Reset</div>
                            <div class="settings-health-value">{{ number_format($device->heartbeat_count) }} · {{ number_format($device->reset_count) }}</div>
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">Browser Online</div>
                            <div class="settings-health-value">{{ $device->is_online_reported ? 'Online reported' : 'Offline reported' }}</div>
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">Reset Applied</div>
                            <div class="settings-health-value">{{ $device->reset_applied_at ? \Illuminate\Support\Carbon::parse($device->reset_applied_at)->diffForHumans() : '-' }}</div>
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">Last Error</div>
                            <div class="settings-health-value">{{ $device->last_error_message ?: '-' }}</div>
                            @if ($device->last_error_label)
                                <div class="settings-device-meta">{{ $device->last_error_label }}</div>
                            @endif
                        </div>
                        <div class="settings-health-card">
                            <div class="settings-health-label">User Agent</div>
                            <div class="settings-health-value">{{ $device->user_agent ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="settings-timeline">
                        <div class="settings-timeline-title">Activity Timeline</div>
                        @forelse ($device->events as $event)
                            <div class="settings-timeline-item">
                                <span class="settings-timeline-icon">
                                    <i class="fas fa-history"></i>
                                </span>
                                <span>
                                    <span class="settings-timeline-message">{{ $event->message }}</span>
                                    <span class="settings-timeline-time">{{ $event->time_label }}</span>
                                </span>
                            </div>
                        @empty
                            <div class="settings-device-meta">Belum ada aktivitas tercatat.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="settings-empty">Belum ada device aktif yang terdaftar.</div>
        @endforelse
    </div>
</section>
