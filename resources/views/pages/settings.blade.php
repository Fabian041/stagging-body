@extends('layouts.root.main')

@section('main')
<style>
    .settings-page {
        display: grid;
        gap: 18px;
    }

    .settings-head,
    .settings-panel {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--r);
        box-shadow: var(--shadow);
    }

    .settings-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 22px;
    }

    .settings-title {
        margin: 0;
        color: var(--text);
        font-size: 24px;
        font-weight: 850;
    }

    .settings-subtitle {
        margin-top: 5px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
    }

    .settings-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-light);
        color: var(--primary);
        font-size: 18px;
        flex-shrink: 0;
    }

    .settings-panel {
        padding: 20px;
    }

    .settings-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 8px;
        color: #166534;
        background: var(--success-light);
        border: 1px solid rgba(22, 163, 74, .2);
        font-size: 13px;
        font-weight: 750;
    }

    .settings-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .settings-section-title {
        margin: 0;
        color: var(--text);
        font-size: 17px;
        font-weight: 850;
    }

    .settings-section-copy {
        margin-top: 4px;
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 650;
    }

    .settings-section-head-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .settings-live {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--success-light);
        color: #166534;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .04em;
    }

    .settings-live .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #16a34a;
        animation: settings-live-pulse 1.5s ease-in-out infinite;
    }

    @keyframes settings-live-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .35; }
    }

    .settings-device-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 12px;
    }

    .settings-device-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 238px;
        padding: 14px;
        border: 1px solid #edf1f7;
        border-radius: 8px;
        background: #fbfdff;
    }

    .settings-device-user {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .settings-device-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .settings-device-name {
        color: var(--text);
        font-size: 14px;
        font-weight: 850;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .settings-device-meta,
    .settings-device-page,
    .settings-device-time {
        color: var(--text-muted);
        font-size: 11.5px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .settings-device-page {
        color: #51617a;
    }

    .settings-device-main {
        display: grid;
        gap: 10px;
    }

    .settings-device-stat {
        min-width: 0;
    }

    .settings-device-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: auto;
    }

    .settings-device-actions > .settings-btn {
        grid-column: 1 / -1;
    }

    .settings-device-actions form {
        min-width: 0;
    }

    .settings-device-actions .settings-btn {
        justify-content: center;
        width: 100%;
    }

    .settings-device-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 850;
    }

    .settings-device-status i {
        font-size: 7px;
    }

    .settings-device-status.is-online {
        background: var(--success-light);
        color: #166534;
    }

    .settings-device-status.is-pending {
        margin-top: 6px;
        background: var(--warning-light);
        color: #92400e;
    }

    .settings-device-label-form {
        display: flex;
        gap: 7px;
        margin-top: 9px;
    }

    .settings-device-label-input {
        min-width: 0;
        flex: 1;
        height: 34px;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 0 10px;
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
        outline: none;
    }

    .settings-icon-btn {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary);
        color: #fff;
        cursor: pointer;
    }

    .settings-modal-card.is-device {
        width: min(980px, 100%);
        max-height: calc(100vh - 34px);
        overflow: auto;
    }

    .settings-device-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .settings-device-modal-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .settings-health-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
        margin-top: 11px;
    }

    .settings-health-card {
        padding: 10px;
        border: 1px solid #edf1f7;
        border-radius: 8px;
        background: #fff;
    }

    .settings-health-label {
        color: var(--text-muted);
        font-size: 10.5px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .settings-health-value {
        margin-top: 4px;
        color: var(--text);
        font-size: 12px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .settings-timeline {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }

    .settings-timeline-title {
        color: var(--text);
        font-size: 12px;
        font-weight: 850;
    }

    .settings-timeline-item {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr);
        gap: 8px;
        align-items: start;
    }

    .settings-timeline-icon {
        width: 24px;
        height: 24px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-light);
        color: var(--primary);
        font-size: 10px;
    }

    .settings-timeline-message {
        color: var(--text);
        font-size: 12px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .settings-timeline-time {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .settings-empty {
        padding: 18px;
        border: 1px dashed var(--border);
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .settings-field {
        padding: 16px;
        border: 1px solid #edf1f7;
        border-radius: 8px;
        background: #fbfdff;
    }

    .settings-field.is-full {
        grid-column: 1 / -1;
    }

    .settings-label {
        display: block;
        margin-bottom: 7px;
        color: var(--text);
        font-size: 13px;
        font-weight: 850;
    }

    .settings-help {
        margin-top: 6px;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .settings-select {
        width: 100%;
        height: 42px;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 0 12px;
        background: #fff;
        color: var(--text);
        font-size: 13px;
        font-weight: 700;
        outline: none;
    }

    .settings-radio-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .settings-radio-row.is-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .settings-radio {
        position: relative;
    }

    .settings-radio input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-radio span,
    .settings-toggle {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: #fff;
        color: #51617a;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .settings-radio input:checked + span {
        border-color: rgba(0, 112, 183, .35);
        background: var(--primary-light);
        color: var(--primary);
    }

    .settings-toggle {
        justify-content: space-between;
    }

    .settings-toggle input {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
    }

    .settings-error {
        margin-top: 7px;
        color: var(--danger);
        font-size: 11.5px;
        font-weight: 700;
    }

    .settings-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .settings-btn {
        min-height: 40px;
        border: 0;
        border-radius: 7px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: #fff;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
    }

    .settings-btn.is-muted {
        background: #EEF2F7;
        color: #51617a;
    }

    .settings-actions {
        gap: 10px;
    }

    .settings-dirty {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-right: auto;
        color: #b45309;
        font-size: 12.5px;
        font-weight: 800;
    }

    .settings-dirty i {
        font-size: 7px;
    }

    .settings-btn.is-pending {
        box-shadow: 0 0 0 3px rgba(0, 112, 183, .18);
    }

    .settings-btn.is-danger {
        background: var(--danger);
        color: #fff;
    }

    .settings-btn.is-danger-outline {
        border: 1px solid rgba(220, 38, 38, .35);
        background: #fff;
        color: var(--danger);
    }

    .settings-btn.is-danger-outline:hover {
        background: rgba(220, 38, 38, .08);
    }

    .settings-btn:disabled {
        cursor: not-allowed;
        opacity: .58;
    }

    .settings-modal {
        position: fixed;
        inset: 0;
        z-index: 7200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .settings-modal.open {
        display: flex;
    }

    .settings-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .34);
        backdrop-filter: blur(8px);
    }

    .settings-modal-card {
        position: relative;
        width: min(420px, 100%);
        padding: 20px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--border);
        box-shadow: 0 28px 80px rgba(15, 23, 42, .26);
    }

    .settings-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        color: #b45309;
        font-size: 18px;
        margin-bottom: 12px;
    }

    .settings-modal-title {
        margin: 0 0 6px;
        color: var(--text);
        font-size: 17px;
        font-weight: 850;
    }

    .settings-modal-copy {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
        line-height: 1.55;
    }

    .settings-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 18px;
    }

    .settings-preview {
        display: grid;
        grid-template-columns: 210px minmax(0, 1fr);
        gap: 14px;
        min-height: 170px;
        overflow: hidden;
    }

    .settings-preview-side,
    .settings-preview-main {
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
    }

    .settings-preview-side {
        padding: 12px;
        transition: width .18s ease, background .18s ease, color .18s ease;
    }

    .settings-preview-brand,
    .settings-preview-menu {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .settings-preview-brand {
        margin-bottom: 14px;
        font-weight: 850;
        color: var(--text);
    }

    .settings-preview-dot {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary);
        color: #fff;
        flex-shrink: 0;
    }

    .settings-preview-menu {
        min-height: 34px;
        border-radius: 8px;
        padding: 0 9px;
        color: #51617a;
        font-size: 12px;
        font-weight: 750;
    }

    .settings-preview-menu.is-active {
        background: var(--primary-light);
        color: var(--primary);
    }

    .settings-preview-main {
        position: relative;
        padding: 13px;
        background: #F7FAFE;
    }

    .settings-preview-topbar {
        height: 28px;
        width: 72%;
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--border);
        margin-bottom: 13px;
    }

    .settings-preview-row {
        height: 30px;
        border-radius: 7px;
        background: #fff;
        border: 1px solid #edf1f7;
        margin-top: 8px;
    }

    .settings-preview-bella {
        position: absolute;
        right: 13px;
        bottom: 13px;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary);
        color: #fff;
        box-shadow: 0 10px 26px rgba(0, 112, 183, .24);
    }

    .settings-preview.is-dark .settings-preview-side {
        background: #111827;
        border-color: rgba(148, 163, 184, .2);
    }

    .settings-preview.is-dark .settings-preview-brand {
        color: #F8FAFC;
    }

    .settings-preview.is-dark .settings-preview-menu {
        color: #CBD5E1;
    }

    .settings-preview.is-dark .settings-preview-menu.is-active {
        background: rgba(14, 165, 233, .16);
        color: #7DD3FC;
    }

    .settings-preview.is-collapsed {
        grid-template-columns: 74px minmax(0, 1fr);
    }

    .settings-preview.is-collapsed .settings-preview-text {
        display: none;
    }

    .settings-preview.is-collapsed .settings-preview-side {
        padding-inline: 10px;
    }

    .settings-preview.is-compact .settings-preview-row {
        height: 23px;
        margin-top: 6px;
    }

    .settings-preview.is-bella-hidden .settings-preview-bella {
        display: none;
    }

    .settings-swatches {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 2px;
    }

    .settings-swatch {
        position: relative;
    }

    .settings-swatch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-swatch span {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--sw);
        color: #fff;
        font-size: 15px;
        cursor: pointer;
        border: 2px solid transparent;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .12);
        transition: transform .14s ease, box-shadow .14s ease;
    }

    .settings-swatch span:hover {
        transform: translateY(-2px);
    }

    .settings-swatch input:checked + span {
        border-color: #0f172a;
        box-shadow: 0 0 0 3px rgba(15, 23, 42, .12), 0 6px 14px rgba(15, 23, 42, .18);
    }

    @media (min-width: 1280px) {
        .settings-device-list {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1680px) {
        .settings-device-list {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .settings-head {
            align-items: flex-start;
        }

        .settings-grid,
        .settings-radio-row,
        .settings-radio-row.is-three {
            grid-template-columns: 1fr;
        }

        .settings-preview {
            grid-template-columns: 1fr;
        }

        .settings-section-head {
            flex-direction: column;
        }

        .settings-health-grid {
            grid-template-columns: 1fr;
        }

        .settings-device-row {
            align-items: stretch;
        }
    }
</style>

<div class="settings-page">
    @if (session('settings_success'))
        <div class="settings-alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('settings_success') }}</span>
        </div>
    @endif

    <section class="settings-head">
        <div>
            <h1 class="settings-title">Settings</h1>
            <div class="settings-subtitle">Preference pribadi untuk cara BELLA tampil dan membuka halaman.</div>
        </div>
        <div class="settings-icon">
            <i class="fas fa-cog"></i>
        </div>
    </section>

    <section class="settings-panel">
        <form action="{{ route('settings.update') }}" method="POST" id="settingsForm">
            @csrf
            @method('PUT')

            <div class="settings-grid">
                @if ($canManageSystemSettings)
                    <div class="settings-field is-full">
                        <label class="settings-label" for="landing_route">Halaman awal setelah login</label>
                        <select id="landing_route" name="landing_route" class="settings-select">
                            @foreach ($landingPages as $route => $label)
                                <option value="{{ $route }}" {{ old('landing_route', $preferences['landing_route']) === $route ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="settings-help">Jika memilih role default, BELLA tetap memakai redirect login lama sesuai role.</div>
                        @error('landing_route')
                            <div class="settings-error">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="settings-field">
                    <span class="settings-label">Density tabel</span>
                    <div class="settings-radio-row">
                        <label class="settings-radio">
                            <input type="radio" name="table_density" value="comfortable"
                                {{ old('table_density', $preferences['table_density']) === 'comfortable' ? 'checked' : '' }}>
                            <span><i class="fas fa-grip-lines"></i> Comfortable</span>
                        </label>
                        <label class="settings-radio">
                            <input type="radio" name="table_density" value="compact"
                                {{ old('table_density', $preferences['table_density']) === 'compact' ? 'checked' : '' }}>
                            <span><i class="fas fa-bars"></i> Compact</span>
                        </label>
                    </div>
                    @error('table_density')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-field">
                    <span class="settings-label">Tampilan & notifikasi</span>
                    <input type="hidden" name="bella_brief_notifications" value="0">
                    <label class="settings-toggle">
                        <span><i class="fas fa-bell"></i> Tampilkan brief BELLA</span>
                        <input type="checkbox" name="bella_brief_notifications" value="1"
                            {{ old('bella_brief_notifications', $preferences['bella_brief_notifications']) ? 'checked' : '' }}>
                    </label>

                    <input type="hidden" name="confirm_logout" value="0">
                    <label class="settings-toggle mt-2">
                        <span><i class="fas fa-sign-out-alt"></i> Konfirmasi saat logout</span>
                        <input type="checkbox" name="confirm_logout" value="1"
                            {{ old('confirm_logout', $preferences['confirm_logout']) ? 'checked' : '' }}>
                    </label>
                    @error('bella_brief_notifications')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                    @error('confirm_logout')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                </div>

                @if ($canManageSystemSettings)
                    <div class="settings-field">
                        <span class="settings-label">BELLA Assistant</span>
                        <input type="hidden" name="bella_assistant_enabled" value="0">
                        <label class="settings-toggle">
                            <span><i class="fas fa-comment-dots"></i> Tampilkan tombol chat</span>
                            <input type="checkbox" name="bella_assistant_enabled" value="1"
                                {{ old('bella_assistant_enabled', $preferences['bella_assistant_enabled']) ? 'checked' : '' }}>
                        </label>

                        <div class="settings-help">Gaya jawaban BELLA di Dashboard Overview.</div>
                        <div class="settings-radio-row mt-2">
                            <label class="settings-radio">
                                <input type="radio" name="bella_answer_style" value="concise"
                                    {{ old('bella_answer_style', $preferences['bella_answer_style']) === 'concise' ? 'checked' : '' }}>
                                <span><i class="fas fa-bolt"></i> Ringkas</span>
                            </label>
                            <label class="settings-radio">
                                <input type="radio" name="bella_answer_style" value="detailed"
                                    {{ old('bella_answer_style', $preferences['bella_answer_style']) === 'detailed' ? 'checked' : '' }}>
                                <span><i class="fas fa-list-ul"></i> Detail</span>
                            </label>
                        </div>
                        @error('bella_assistant_enabled')
                            <div class="settings-error">{{ $message }}</div>
                        @enderror
                        @error('bella_answer_style')
                            <div class="settings-error">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="settings-field">
                    <span class="settings-label">Sidebar</span>
                    <div class="settings-radio-row is-three">
                        <label class="settings-radio">
                            <input type="radio" name="sidebar_mode" value="remember"
                                {{ old('sidebar_mode', $preferences['sidebar_mode']) === 'remember' ? 'checked' : '' }}>
                            <span><i class="fas fa-history"></i> Remember</span>
                        </label>
                        <label class="settings-radio">
                            <input type="radio" name="sidebar_mode" value="expanded"
                                {{ old('sidebar_mode', $preferences['sidebar_mode']) === 'expanded' ? 'checked' : '' }}>
                            <span><i class="fas fa-columns"></i> Expanded</span>
                        </label>
                        <label class="settings-radio">
                            <input type="radio" name="sidebar_mode" value="collapsed"
                                {{ old('sidebar_mode', $preferences['sidebar_mode']) === 'collapsed' ? 'checked' : '' }}>
                            <span><i class="fas fa-compress-alt"></i> Collapsed</span>
                        </label>
                    </div>
                    <div class="settings-help">Remember mengikuti toggle terakhir, sementara expanded/collapsed jadi default tetap saat halaman dibuka.</div>
                    <div class="settings-help">Theme sidenav</div>
                    <div class="settings-radio-row mt-2">
                        <label class="settings-radio">
                            <input type="radio" name="sidenav_theme" value="light"
                                {{ old('sidenav_theme', $preferences['sidenav_theme']) === 'light' ? 'checked' : '' }}>
                            <span><i class="fas fa-sun"></i> Light</span>
                        </label>
                        <label class="settings-radio">
                            <input type="radio" name="sidenav_theme" value="dark"
                                {{ old('sidenav_theme', $preferences['sidenav_theme']) === 'dark' ? 'checked' : '' }}>
                            <span><i class="fas fa-moon"></i> Dark</span>
                        </label>
                    </div>
                    @error('sidebar_mode')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                    @error('sidenav_theme')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-field">
                    <span class="settings-label">Warna tombol BELLA</span>
                    <div class="settings-swatches">
                        @foreach ($fabColors as $key => $hex)
                            <label class="settings-swatch" title="{{ ucfirst($key) }}">
                                <input type="radio" name="fab_color" value="{{ $key }}" data-hex="{{ $hex }}"
                                    {{ old('fab_color', $preferences['fab_color']) === $key ? 'checked' : '' }}>
                                <span style="--sw: {{ $hex }}"><i class="fas fa-comment-dots"></i></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="settings-help">Warna tombol chat BELLA yang mengambang di Dashboard Overview.</div>
                    @error('fab_color')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-field is-full">
                    <span class="settings-label">Live Preview</span>
                    <div class="settings-preview" id="settingsPreview">
                        <div class="settings-preview-side">
                            <div class="settings-preview-brand">
                                <span class="settings-preview-dot">B</span>
                                <span class="settings-preview-text">BELLA</span>
                            </div>
                            <div class="settings-preview-menu is-active">
                                <i class="fas fa-chart-pie"></i>
                                <span class="settings-preview-text">Dashboard</span>
                            </div>
                            <div class="settings-preview-menu">
                                <i class="fas fa-cog"></i>
                                <span class="settings-preview-text">Settings</span>
                            </div>
                        </div>
                        <div class="settings-preview-main">
                            <div class="settings-preview-topbar"></div>
                            <div class="settings-preview-row"></div>
                            <div class="settings-preview-row"></div>
                            <div class="settings-preview-row"></div>
                            <div class="settings-preview-bella"><i class="fas fa-comment-dots"></i></div>
                        </div>
                    </div>
                    <div class="settings-help">Preview mengikuti pilihan sidebar, density tabel, dan tombol BELLA sebelum disimpan.</div>
                </div>
            </div>

            <div class="settings-actions">
                <span class="settings-dirty" id="settingsDirty" hidden>
                    <i class="fas fa-circle"></i> Perubahan belum disimpan
                </span>
                <button type="button" id="settingsResetOpen" class="settings-btn is-muted">
                    <i class="fas fa-undo"></i>
                    Reset Default
                </button>
                <button type="submit" class="settings-btn">
                    <i class="fas fa-save"></i>
                    Simpan Preferences
                </button>
            </div>
        </form>
    </section>

    @if ($canManageDeviceSessions)
        @include('pages.partials.device-monitoring', ['deviceSessions' => $deviceSessions])
    @endif
</div>

@if ($canManageDeviceSessions)
    <div class="settings-modal" id="settingsDeviceResetModal" aria-hidden="true">
        <div class="settings-modal-backdrop" data-device-reset-close></div>
        <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsDeviceResetTitle">
            <div class="settings-modal-icon"><i class="fas fa-broom"></i></div>
            <h2 class="settings-modal-title" id="settingsDeviceResetTitle">Reset device?</h2>
            <div class="settings-modal-copy">Device akan clear data lokal saat heartbeat berikutnya. Gunakan untuk device yang cache atau data lokalnya bermasalah.</div>
            <div class="settings-modal-actions">
                <button type="button" class="settings-btn is-muted" data-device-reset-close>Batal</button>
                <button type="button" class="settings-btn is-danger" id="settingsDeviceResetConfirm">
                    <i class="fas fa-broom"></i> Reset Device
                </button>
            </div>
        </div>
    </div>

    <div class="settings-modal" id="settingsDeviceDeleteModal" aria-hidden="true">
        <div class="settings-modal-backdrop" data-device-delete-close></div>
        <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsDeviceDeleteTitle">
            <div class="settings-modal-icon"><i class="fas fa-trash"></i></div>
            <h2 class="settings-modal-title" id="settingsDeviceDeleteTitle">Hapus device?</h2>
            <div class="settings-modal-copy">Device akan dihapus dari monitoring. Jika browser device masih aktif, heartbeat berikutnya bisa mendaftarkannya lagi.</div>
            <div class="settings-modal-actions">
                <button type="button" class="settings-btn is-muted" data-device-delete-close>Batal</button>
                <button type="button" class="settings-btn is-danger" id="settingsDeviceDeleteConfirm">
                    <i class="fas fa-trash"></i> Hapus Device
                </button>
            </div>
        </div>
    </div>
@endif

<div class="settings-modal" id="settingsResetModal" aria-hidden="true">
    <div class="settings-modal-backdrop" data-reset-close></div>
    <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsResetTitle">
        <div class="settings-modal-icon"><i class="fas fa-undo"></i></div>
        <h2 class="settings-modal-title" id="settingsResetTitle">Reset ke default?</h2>
        <div class="settings-modal-copy">Semua preference kamu dikembalikan ke pengaturan default. Perubahan yang belum disimpan juga ikut hilang.</div>
        <div class="settings-modal-actions">
            <button type="button" class="settings-btn is-muted" data-reset-close>Batal</button>
            <button type="submit" name="reset_preferences" value="1" form="settingsForm" formnovalidate class="settings-btn is-danger">
                <i class="fas fa-undo"></i> Ya, Reset
            </button>
        </div>
    </div>
</div>

<div class="settings-modal" id="settingsLeaveModal" aria-hidden="true">
    <div class="settings-modal-backdrop" data-leave-close></div>
    <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsLeaveTitle">
        <div class="settings-modal-icon"><i class="fas fa-sign-out-alt"></i></div>
        <h2 class="settings-modal-title" id="settingsLeaveTitle">Keluar tanpa simpan?</h2>
        <div class="settings-modal-copy">Ada perubahan preference yang belum disimpan. Kalau keluar sekarang, perubahannya hilang.</div>
        <div class="settings-modal-actions">
            <button type="button" class="settings-btn is-muted" data-leave-close>Tetap di sini</button>
            <button type="button" class="settings-btn is-danger" id="settingsLeaveConfirm">
                <i class="fas fa-sign-out-alt"></i> Keluar tanpa simpan
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        var preview = document.getElementById('settingsPreview');
        if (!preview) return;

        function checkedValue(name) {
            var checked = document.querySelector('input[name="' + name + '"]:checked');
            return checked ? checked.value : '';
        }

        function syncPreview() {
            var bella = document.querySelector('input[name="bella_assistant_enabled"][type="checkbox"]');
            preview.classList.toggle('is-dark', checkedValue('sidenav_theme') === 'dark');
            preview.classList.toggle('is-collapsed', checkedValue('sidebar_mode') === 'collapsed');
            preview.classList.toggle('is-compact', checkedValue('table_density') === 'compact');
            preview.classList.toggle('is-bella-hidden', bella && !bella.checked);

            var fab = document.querySelector('input[name="fab_color"]:checked');
            var bellaBtn = preview.querySelector('.settings-preview-bella');
            if (fab && bellaBtn) {
                var hex = fab.getAttribute('data-hex');
                if (hex) bellaBtn.style.background = hex;
            }
        }

        document.querySelectorAll('input[name="sidenav_theme"], input[name="sidebar_mode"], input[name="table_density"], input[name="bella_assistant_enabled"], input[name="fab_color"]').forEach(function(input) {
            input.addEventListener('change', syncPreview);
        });
        syncPreview();
    })();

    (function() {
        function toggleModal(el, open) {
            if (!el) return;
            el.classList.toggle('open', open);
            el.setAttribute('aria-hidden', open ? 'false' : 'true');
        }

        document.addEventListener('click', function(e) {
            var openBtn = e.target.closest('[data-device-modal-open]');
            if (openBtn) {
                toggleModal(document.getElementById(openBtn.getAttribute('data-device-modal-open')), true);
                return;
            }

            if (e.target.closest('[data-device-modal-close]')) {
                toggleModal(e.target.closest('.settings-modal'), false);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.settings-modal.open').forEach(function(modal) {
                if (modal.id && modal.id.indexOf('deviceModal') === 0) toggleModal(modal, false);
            });
        });
    })();

    (function() {
        function bindDeviceConfirm(formSelector, modalId, closeSelector, confirmId) {
            var modal = document.getElementById(modalId);
            var confirm = document.getElementById(confirmId);
            var pendingForm = null;
            var confirmedForm = null;

            function toggleModal(open) {
                if (!modal) return;
                modal.classList.toggle('open', open);
                modal.setAttribute('aria-hidden', open ? 'false' : 'true');
            }

            function closeModal() {
                pendingForm = null;
                toggleModal(false);
            }

            document.addEventListener('submit', function(e) {
                if (!e.target.matches(formSelector)) return;
                if (e.target === confirmedForm) {
                    confirmedForm = null;
                    return;
                }

                e.preventDefault();
                e.stopImmediatePropagation();
                pendingForm = e.target;
                if (confirm) confirm.disabled = false;
                toggleModal(true);
            }, true);

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target.closest(closeSelector)) closeModal();
                });
            }

            if (confirm) {
                confirm.addEventListener('click', function() {
                    if (!pendingForm) return;
                    confirmedForm = pendingForm;
                    confirm.disabled = true;
                    if (pendingForm.requestSubmit) {
                        pendingForm.requestSubmit();
                    } else {
                        pendingForm.submit();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal();
            });
        }

        bindDeviceConfirm('[data-device-reset-form]', 'settingsDeviceResetModal', '[data-device-reset-close]', 'settingsDeviceResetConfirm');
        bindDeviceConfirm('[data-device-delete-form]', 'settingsDeviceDeleteModal', '[data-device-delete-close]', 'settingsDeviceDeleteConfirm');
    })();

    (function() {
        var inFlight = false;
        var lastSuccessAt = Date.now();
        var reloadKey = 'settings_device_monitor_scroll';

        try {
            var restoreScroll = sessionStorage.getItem(reloadKey);
            if (restoreScroll) {
                sessionStorage.removeItem(reloadKey);
                window.scrollTo(0, parseInt(restoreScroll, 10) || 0);
            }
        } catch (e) {}

        function shouldSkip() {
            return document.hidden
                || inFlight
                || document.querySelector('#settingsDeviceResetModal.open, #settingsDeviceDeleteModal.open, #settingsResetModal.open, #settingsLeaveModal.open')
                || (document.activeElement && document.activeElement.closest('[data-device-monitor-section] input, [data-device-monitor-section] textarea, [data-device-monitor-section] select'));
        }

        function hasPendingReset() {
            return !!document.querySelector('[data-device-reset-pending]');
        }

        function reloadForSync() {
            if (!hasPendingReset()) return;
            try { sessionStorage.setItem(reloadKey, String(window.scrollY || 0)); } catch (e) {}
            window.location.reload();
        }

        function refreshDeviceMonitoring(force) {
            var current = document.querySelector('[data-device-monitor-section]');
            if (!current || (!force && shouldSkip())) return;

            inFlight = true;
            var openDeviceModal = document.querySelector('.settings-modal.open[id^="deviceModal"]');
            var openDeviceModalId = openDeviceModal ? openDeviceModal.id : '';
            var url = new URL(current.getAttribute('data-device-monitor-url'), window.location.origin);
            url.searchParams.set('_device_monitor', Date.now());

            fetch(url.toString(), {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function(response) { return response.ok ? response.json() : null; })
                .then(function(data) {
                    if (!data || !data.html || (!force && shouldSkip())) return;
                    current = document.querySelector('[data-device-monitor-section]');
                    if (current) {
                        current.outerHTML = data.html;
                        lastSuccessAt = Date.now();
                        if (openDeviceModalId) {
                            var freshModal = document.getElementById(openDeviceModalId);
                            if (freshModal) {
                                freshModal.classList.add('open');
                                freshModal.setAttribute('aria-hidden', 'false');
                            }
                        }
                    }
                })
                .catch(function() { reloadForSync(); })
                .finally(function() { inFlight = false; });
        }

        refreshDeviceMonitoring(true);
        setInterval(function() {
            refreshDeviceMonitoring(false);
            if (hasPendingReset() && Date.now() - lastSuccessAt > 15000) reloadForSync();
        }, 5000);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) refreshDeviceMonitoring(true);
        });
    })();

    // Guard perubahan belum disimpan: modal in-app untuk reset & navigasi internal,
    // native beforeunload hanya untuk hard-exit (tutup tab / refresh / back).
    (function() {
        var form = document.getElementById('settingsForm');
        if (!form) return;

        var hint = document.getElementById('settingsDirty');
        var saveBtn = form.querySelector('.settings-actions .settings-btn:not(.is-muted)');
        var saving = false;
        var pendingUrl = null;
        var pendingForm = null;

        function serialize() {
            var out = [];
            form.querySelectorAll('input, select, textarea').forEach(function(el) {
                if (!el.name) return;
                if (el.type === 'checkbox' || el.type === 'radio') {
                    out.push(el.name + '#' + el.value + '=' + (el.checked ? 1 : 0));
                } else {
                    out.push(el.name + '=' + el.value);
                }
            });
            return out.join('&');
        }

        var initial = serialize();
        function isDirty() { return !saving && serialize() !== initial; }

        function refresh() {
            var dirty = serialize() !== initial;
            if (hint) hint.hidden = !dirty;
            if (saveBtn) saveBtn.classList.toggle('is-pending', dirty);
        }

        form.addEventListener('input', refresh);
        form.addEventListener('change', refresh);

        function toggleModal(el, open) {
            if (!el) return;
            el.classList.toggle('open', open);
            el.setAttribute('aria-hidden', open ? 'false' : 'true');
        }

        // Hard-exit (tutup tab / refresh / back) → prompt native (satu-satunya cara yang diizinkan browser)
        window.addEventListener('beforeunload', function(e) {
            if (isDirty()) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // ===== Reset =====
        var resetOpen = document.getElementById('settingsResetOpen');
        var resetModal = document.getElementById('settingsResetModal');
        if (resetOpen && resetModal) {
            resetOpen.addEventListener('click', function() { toggleModal(resetModal, true); });
            resetModal.addEventListener('click', function(e) {
                if (e.target.closest('[data-reset-close]')) toggleModal(resetModal, false);
            });
        }

        // ===== Keluar tanpa simpan (navigasi internal) =====
        var leaveModal = document.getElementById('settingsLeaveModal');
        var leaveConfirm = document.getElementById('settingsLeaveConfirm');

        function closeLeave() {
            toggleModal(leaveModal, false);
            pendingUrl = null;
            pendingForm = null;
        }

        if (leaveModal) {
            leaveModal.addEventListener('click', function(e) {
                if (e.target.closest('[data-leave-close]')) closeLeave();
            });
        }
        if (leaveConfirm) {
            leaveConfirm.addEventListener('click', function() {
                saving = true; // lolos beforeunload
                toggleModal(leaveModal, false);
                if (pendingUrl) {
                    window.location.href = pendingUrl;
                } else if (pendingForm) {
                    pendingForm.submit();
                }
            });
        }

        // Intercept klik link internal saat masih ada perubahan
        document.addEventListener('click', function(e) {
            if (!isDirty()) return;
            var a = e.target.closest('a[href]');
            if (!a) return;
            var href = a.getAttribute('href') || '';
            if (!href || href.charAt(0) === '#' || /^(javascript:|mailto:|tel:)/i.test(href)) return;
            if (a.target === '_blank' || a.hasAttribute('download')) return;
            e.preventDefault();
            pendingUrl = a.href;
            pendingForm = null;
            toggleModal(leaveModal, true);
        }, true);

        // Intercept submit form LAIN (mis. logout); form settings sendiri dianggap "saving"
        document.addEventListener('submit', function(e) {
            if (e.target === form) { saving = true; return; }
            if (e.target.matches('[data-device-reset-form], [data-device-delete-form]')) { saving = true; return; }
            if (!isDirty()) return;
            e.preventDefault();
            pendingForm = e.target;
            pendingUrl = null;
            toggleModal(leaveModal, true);
        }, true);

        // Esc menutup modal yang terbuka
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            if (resetModal && resetModal.classList.contains('open')) toggleModal(resetModal, false);
            if (leaveModal && leaveModal.classList.contains('open')) closeLeave();
        });
    })();
</script>
@endsection
