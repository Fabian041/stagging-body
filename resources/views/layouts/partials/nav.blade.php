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

        {{-- Global Search (navigasi menu) --}}
        <div class="tb-search d-none d-md-flex" id="globalNavSearch">
            <i class="fas fa-search"></i>
            <input type="text" id="globalNavSearchInput" placeholder="Quick search... (cari menu)"
                autocomplete="off">
            <div class="tb-search-results" id="globalNavSearchResults"></div>
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

<style>
    .tb-search {
        position: relative;
    }

    .tb-search-results {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 100%;
        min-width: 300px;
        background: var(--card, #fff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 10px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
        padding: 6px;
        z-index: 5000;
        max-height: 70vh;
        overflow-y: auto;
        display: none;
    }

    .tb-search-results.show {
        display: block;
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

    .tb-sr-empty {
        padding: 14px;
        text-align: center;
        color: var(--text-muted, #6b7a99);
        font-size: 12px;
        font-weight: 600;
    }
</style>

<script>
    // Global search navbar: command-palette navigasi menu (index diambil dari link sidebar)
    (function () {
        const box = document.getElementById('globalNavSearch');
        if (!box) return;
        const input = document.getElementById('globalNavSearchInput');
        const results = document.getElementById('globalNavSearchResults');
        let index = null;

        function buildIndex() {
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
            return (s || '').replace(/[&<>"]/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
            });
        }

        function hide() { results.classList.remove('show'); results.innerHTML = ''; }

        function render(list) {
            if (!list.length) {
                results.innerHTML = '<div class="tb-sr-empty">Tidak ada menu cocok</div>';
                results.classList.add('show');
                return;
            }
            results.innerHTML = list.map(function (it, i) {
                return '<a href="' + it.href + '" class="tb-sr-item' + (i === 0 ? ' active' : '') + '">' +
                    '<i class="fas fa-arrow-right"></i><span>' + esc(it.label) + '</span></a>';
            }).join('');
            results.classList.add('show');
        }

        function search() {
            const kw = input.value.toLowerCase().trim();
            if (!kw) { hide(); return; }
            if (!index) index = buildIndex();
            const matches = index.filter(function (it) {
                return it.label.toLowerCase().indexOf(kw) !== -1;
            }).slice(0, 8);
            render(matches);
        }

        function setActive(items, idx) {
            items.forEach(function (el, i) { el.classList.toggle('active', i === idx); });
            if (items[idx]) items[idx].scrollIntoView({ block: 'nearest' });
        }

        input.addEventListener('input', search);
        input.addEventListener('focus', search);

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { hide(); input.blur(); return; }
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

        document.addEventListener('click', function (e) {
            if (!box.contains(e.target)) hide();
        });

        // Ctrl/Cmd + K untuk fokus ke search
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                input.focus();
                input.select();
            }
        });
    })();
</script>
