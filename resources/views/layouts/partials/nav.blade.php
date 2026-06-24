<div id="topbar">

    {{-- ===== LEFT: mobile toggle + page title ===== --}}
    <div class="tb-left">
        {{-- Mobile hamburger --}}
        <button id="mobile-menu-btn" class="tb-icon-btn d-lg-none" style="border:none;flex-shrink:0;" title="Open menu">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Page title (rendered by each page via @section or just static) --}}
        <div class="d-none d-md-block">
            {{-- <div class="tb-page-title">{{ $pageTitle ?? 'Dashboard' }}</div> --}}
            @if (!empty($pageSubtitle))
                <div class="tb-page-sub">{{ $pageSubtitle }}</div>
            @endif
        </div>
    </div>

    {{-- ===== RIGHT: search + actions ===== --}}
    <div class="tb-right">

        {{-- Global Search trigger (buka command palette) --}}
        <div class="tb-search d-none d-md-flex" id="globalNavSearch" data-search-url="{{ route('global-search') }}"
            role="button" tabindex="0" title="Cari (Ctrl/Cmd + K)">
            <i class="fas fa-search"></i>
            <span class="tb-search-ph">Cari menu / data...</span>
            <kbd class="tb-search-kbd">Ctrl K</kbd>
        </div>

        {{-- Notifications --}}
        <div class="tb-icon-btn" title="Notifications" style="position:relative;">
            <i class="fas fa-bell"></i>
            <span class="tb-notif-dot"></span>
        </div>

        {{-- Settings --}}
        <a href="features-settings.html" class="tb-icon-btn" title="Settings">
            <i class="fas fa-cog"></i>
        </a>

        <div class="tb-divider"></div>

        {{-- User pill + dropdown --}}
        <div class="tb-user-pill">
            <div class="tb-user-pill-av">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'GS' }}
            </div>
            <span class="tb-user-pill-name d-none d-sm-inline">
                {{ auth()->check() ? auth()->user()->name : 'Guest' }}
            </span>
            <i class="fas fa-chevron-down tb-pill-chevron d-none d-sm-inline"></i>

            {{-- Dropdown --}}
            <div class="tb-user-dropdown">
                <div class="tb-dd-header">
                    <div class="tb-dd-name">{{ auth()->check() ? auth()->user()->name : 'Guest' }}</div>
                    <div class="tb-dd-role">{{ auth()->check() ? auth()->user()->role ?? 'User' : 'Not logged in' }}
                    </div>
                </div>

                @if (auth()->check())
                    <a href="features-profile.html" class="tb-dd-item">
                        <i class="far fa-user"></i> Profile
                    </a>
                    <a href="features-settings.html" class="tb-dd-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <div class="tb-dd-divider"></div>
                    <form action="{{ route('logout.auth') }}" method="POST">
                        @csrf
                        <button type="submit" class="tb-dd-item danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login.index') }}" class="tb-dd-item">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                @endif
            </div>
        </div>

    </div>
</div>

<div class="gsp-overlay" id="gspOverlay" aria-hidden="true">
    <div class="gsp-backdrop" data-gsp-close></div>
    <div class="gsp-panel" role="dialog" aria-modal="true" aria-label="Global search">
        <div class="gsp-head">
            <i class="fas fa-search"></i>
            <input type="text" id="gspInput"
                placeholder="Cari menu atau data — loading list, part, alamat chute..."
                autocomplete="off" spellcheck="false">
            <button type="button" class="gsp-esc" data-gsp-close>ESC</button>
        </div>
        <div class="gsp-results" id="gspResults"></div>
    </div>
</div>

<style>
    /* Navbar search trigger */
    .tb-search {
        cursor: pointer;
    }

    .tb-search-ph {
        flex: 1;
        min-width: 0;
        color: var(--text-muted, #6b7a99);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tb-search-kbd {
        flex-shrink: 0;
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: .03em;
        color: var(--text-muted, #6b7a99);
        background: var(--bg, #f1f5f9);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 5px;
        padding: 2px 6px;
    }

    /* Command palette overlay */
    .gsp-overlay {
        position: fixed;
        inset: 0;
        z-index: 6000;
        display: none;
    }

    .gsp-overlay.show {
        display: block;
    }

    .gsp-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(2px);
    }

    .gsp-panel {
        position: relative;
        max-width: 640px;
        width: calc(100% - 28px);
        margin: 8vh auto 0;
        background: var(--card, #fff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 14px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
        overflow: hidden;
        animation: gspIn .14s ease;
    }

    @keyframes gspIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: none; }
    }

    .gsp-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border, #e2e8f0);
    }

    .gsp-head > i {
        color: var(--text-muted, #6b7a99);
        font-size: 15px;
        flex-shrink: 0;
    }

    .gsp-head input {
        flex: 1;
        min-width: 0;
        border: none;
        outline: none;
        background: transparent;
        font-size: 15.5px;
        font-weight: 600;
        color: var(--text, #1a2340);
    }

    .gsp-esc {
        flex-shrink: 0;
        border: 1px solid var(--border, #e2e8f0);
        background: var(--bg, #f1f5f9);
        color: var(--text-muted, #6b7a99);
        font-size: 10px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 6px;
        cursor: pointer;
    }

    .gsp-results {
        max-height: 62vh;
        overflow-y: auto;
        padding: 8px;
    }

    .gsp-results .tb-sr-item {
        padding: 11px 12px;
    }

    .gsp-hint {
        padding: 22px 16px;
        text-align: center;
        color: var(--text-muted, #6b7a99);
        font-size: 12.5px;
        font-weight: 600;
    }

    .tb-sr-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 11px;
        border-radius: 7px;
        color: var(--text, #1a2340);
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .tb-sr-item i {
        font-size: 10px;
        color: var(--text-muted, #6b7a99);
        flex-shrink: 0;
    }

    .tb-sr-item.active,
    .tb-sr-item:hover {
        background: var(--bg, #f1f5f9);
        color: var(--primary, #0070b7);
    }

    .tb-sr-item.active i,
    .tb-sr-item:hover i {
        color: var(--primary, #0070b7);
    }

    .tb-sr-section {
        padding: 8px 10px 4px;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--text-muted, #6b7a99);
    }

    .tb-sr-text {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }

    .tb-sr-title {
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tb-sr-sub {
        font-size: 11px;
        font-weight: 500;
        color: var(--text-muted, #6b7a99);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tb-sr-item.active .tb-sr-sub,
    .tb-sr-item:hover .tb-sr-sub {
        color: inherit;
    }

    .tb-sr-tag {
        flex-shrink: 0;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 2px 7px;
        border-radius: 999px;
        background: var(--bg, #f1f5f9);
        color: var(--text-muted, #6b7a99);
        border: 1px solid var(--border, #e2e8f0);
    }

    .tb-sr-empty {
        padding: 14px;
        text-align: center;
        color: var(--text-muted, #6b7a99);
        font-size: 12px;
        font-weight: 600;
    }
</style>

<script>
    // Global search command palette: menu (link sidebar, instan) + data lintas modul (endpoint backend)
    (function () {
        const trigger = document.getElementById('globalNavSearch');
        const overlay = document.getElementById('gspOverlay');
        const input = document.getElementById('gspInput');
        const results = document.getElementById('gspResults');
        if (!overlay || !input || !results) return;

        const dataUrl = (trigger && trigger.getAttribute('data-search-url')) || '';

        // Akses cepat ditampilkan saat palette dibuka (sebelum mengetik)
        const fastRoutes = [
            { label: 'Preparation Detail', url: '{{ route('loadingList.index') }}', icon: 'fa-truck-loading' },
            { label: 'Production Result', url: '{{ route('dashboard.prodResult') }}', icon: 'fa-chart-line' },
            { label: 'Production Stock', url: '{{ route('dashboard.productionStock') }}', icon: 'fa-box' },
            { label: 'Alamat Chute', url: '{{ route('pulling.master-alamat-chute') }}', icon: 'fa-map-marker-alt' },
            { label: 'PIS Scanning', url: '{{ route('pis.index') }}', icon: 'fa-barcode' },
            { label: 'Kanban Reset', url: '{{ route('pulling.manual') }}', icon: 'fa-sync-alt' }
        ];

        let menuIndex = null;
        let dataResults = [];
        let lastQuery = '';
        let debounceTimer = null;
        let ctrl = null;

        function buildMenuIndex() {
            const seen = {};
            const items = [];
            document.querySelectorAll('#sidebar-wrapper a.sb-sub-item, #sidebar-wrapper a.sb-item').forEach(function (a) {
                const href = a.getAttribute('href');
                if (!href || href.charAt(0) === '#') return;
                const label = (a.textContent || '').replace(/\s+/g, ' ').trim();
                if (!label) return;
                const key = label + '|' + href;
                if (seen[key]) return;
                seen[key] = true;
                items.push({ label: label, href: href });
            });
            return items;
        }

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
            });
        }

        function itemHtml(href, title, subtitle, icon, tag) {
            return '<a href="' + esc(href) + '" class="tb-sr-item">' +
                '<i class="fas ' + (icon || 'fa-arrow-right') + '"></i>' +
                '<span class="tb-sr-text">' +
                '<span class="tb-sr-title">' + esc(title) + '</span>' +
                (subtitle ? '<span class="tb-sr-sub">' + esc(subtitle) + '</span>' : '') +
                '</span>' +
                (tag ? '<span class="tb-sr-tag">' + esc(tag) + '</span>' : '') +
                '</a>';
        }

        function renderAll(menu, data, kw) {
            let html = '';
            if (menu.length) {
                html += '<div class="tb-sr-section">Menu</div>';
                html += menu.map(function (it) { return itemHtml(it.href, it.label, '', 'fa-arrow-right'); }).join('');
            }
            if (data.length) {
                html += '<div class="tb-sr-section">Data</div>';
                html += data.map(function (it) { return itemHtml(it.url, it.title, it.subtitle, it.icon, it.type); }).join('');
            }
            if (!menu.length && !data.length) {
                html = '<div class="tb-sr-empty">Tidak ada hasil untuk "' + esc(kw) + '"</div>';
            }
            results.innerHTML = html;
            results.classList.add('show');
            const first = results.querySelector('.tb-sr-item');
            if (first) first.classList.add('active');
        }

        function menuMatches(kw) {
            if (!menuIndex) menuIndex = buildMenuIndex();
            const k = kw.toLowerCase();
            return menuIndex.filter(function (it) { return it.label.toLowerCase().indexOf(k) !== -1; }).slice(0, 5);
        }

        function fetchData(kw) {
            if (!dataUrl || kw.length < 2) { dataResults = []; return; }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (ctrl) ctrl.abort();
                ctrl = (typeof AbortController !== 'undefined') ? new AbortController() : null;
                fetch(dataUrl + '?q=' + encodeURIComponent(kw), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: ctrl ? ctrl.signal : undefined
                })
                    .then(function (r) { return r.json(); })
                    .then(function (resp) {
                        if (lastQuery !== kw) return; // hasil basi, abaikan
                        dataResults = (resp && resp.results) || [];
                        renderAll(menuMatches(kw), dataResults, kw);
                    })
                    .catch(function () {});
            }, 250);
        }

        function renderFastRoutes() {
            let html = '<div class="tb-sr-section">Akses Cepat</div>';
            html += fastRoutes.map(function (r) { return itemHtml(r.url, r.label, '', r.icon); }).join('');
            results.innerHTML = html;
            const first = results.querySelector('.tb-sr-item');
            if (first) first.classList.add('active');
        }

        function doSearch() {
            const kw = input.value.trim();
            lastQuery = kw;
            if (!kw) { renderFastRoutes(); return; }
            renderAll(menuMatches(kw), dataResults, kw); // menu instan, data menyusul
            fetchData(kw);
        }

        function setActive(items, idx) {
            items.forEach(function (el, i) { el.classList.toggle('active', i === idx); });
            if (items[idx]) items[idx].scrollIntoView({ block: 'nearest' });
        }

        function open() {
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            input.value = '';
            dataResults = [];
            doSearch();
            setTimeout(function () { input.focus(); }, 20);
        }

        function close() {
            overlay.classList.remove('show');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            input.blur();
        }

        // Buka palette: klik trigger, Enter/Space di trigger, atau Ctrl/Cmd + K
        if (trigger) {
            trigger.addEventListener('click', open);
            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
            });
        }
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) { e.preventDefault(); open(); }
        });

        // Tutup palette: klik backdrop / tombol ESC
        overlay.addEventListener('click', function (e) {
            if (e.target.hasAttribute('data-gsp-close')) close();
        });

        input.addEventListener('input', function () { dataResults = []; doSearch(); });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { close(); return; }
            const items = Array.prototype.slice.call(results.querySelectorAll('.tb-sr-item'));
            if (!items.length) return;
            let idx = items.findIndex(function (el) { return el.classList.contains('active'); });
            if (e.key === 'ArrowDown') { e.preventDefault(); idx = (idx + 1) % items.length; setActive(items, idx); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); idx = (idx - 1 + items.length) % items.length; setActive(items, idx); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                const a = items[idx] || items[0];
                if (a) window.location.href = a.getAttribute('href');
            }
        });
    })();
</script>
