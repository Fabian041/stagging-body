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

    @media (max-width: 768px) {
        .settings-head {
            align-items: flex-start;
        }

        .settings-grid,
        .settings-radio-row,
        .settings-radio-row.is-three {
            grid-template-columns: 1fr;
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
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="settings-grid">
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
                    @error('sidebar_mode')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="settings-actions">
                <button type="submit" class="settings-btn">
                    <i class="fas fa-save"></i>
                    Simpan Preferences
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
