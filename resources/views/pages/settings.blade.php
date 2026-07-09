@extends('layouts.root.main')

@section('main')
@include('pages.partials.settings-styles')

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

</div>

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
