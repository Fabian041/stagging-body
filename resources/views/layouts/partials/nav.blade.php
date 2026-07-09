@php
    $navPreferences = auth()->check() ? (auth()->user()->preferences ?? []) : [];
    $showBellaBrief = $navPreferences['bella_brief_notifications'] ?? true;
    $canAccessSettings = auth()->check();
    $canManageDevices = auth()->check() && auth()->user()->can('manage-device-sessions');
    $canManageUsers = auth()->check() && auth()->user()->isSuperAdmin();
@endphp

<div id="topbar">

    {{-- ===== LEFT: mobile toggle + page title ===== --}}
    <div class="tb-left">
        {{-- Mobile hamburger --}}
        <button id="mobile-menu-btn" class="tb-icon-btn d-lg-none" style="border:none;flex-shrink:0;" title="Open menu" aria-controls="sidebar-wrapper" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Breadcrumb (di-set otomatis dari menu aktif via JS) --}}
        <div class="d-none d-md-block">
            <div class="tb-crumb" id="tbCrumb">
                <i class="fas fa-home tb-crumb-home"></i>
                <i class="fas fa-chevron-right tb-crumb-sep"></i>
                <span class="tb-crumb-cur">Dashboard</span>
            </div>
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

        <button type="button" class="tb-icon-btn tb-search-mobile d-md-none" id="globalNavSearchMobile"
            data-search-url="{{ route('global-search') }}" title="Cari">
            <i class="fas fa-search"></i>
        </button>

        @if ($showBellaBrief)
            {{-- Notifications (BELLA brief) --}}
            <div class="tb-notif-wrap" id="tbNotifWrap">
                <button type="button" class="tb-icon-btn" id="tbNotifBtn" title="Notifikasi BELLA" aria-expanded="false" aria-controls="tbNotifPanel">
                    <i class="fas fa-bell"></i>
                    <span class="tb-notif-dot" id="tbNotifDot" hidden></span>
                </button>
                <div class="tb-notif-panel" id="tbNotifPanel" aria-hidden="true">
                    <div class="tb-notif-head">
                        <span>Notifikasi</span>
                        <button type="button" class="tb-notif-mark" id="tbNotifMarkRead" title="Tandai sudah dibaca">Tandai dibaca</button>
                    </div>
                    <div class="tb-notif-body" id="tbNotifBody">
                        <div class="tb-notif-loading">Memuat brief BELLA...</div>
                    </div>
                </div>
            </div>
        @endif

        @if ($canAccessSettings)
            {{-- Settings --}}
            <a href="{{ route('settings.index') }}" class="tb-icon-btn" title="Settings">
                <i class="fas fa-cog"></i>
            </a>
        @endif

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
                    <a href="{{ route('profile.show') }}" class="tb-dd-item">
                        <i class="far fa-user"></i> Profile
                    </a>
                    @if ($canAccessSettings)
                        <a href="{{ route('settings.index') }}" class="tb-dd-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    @endif
                    @if ($canManageDevices)
                        <a href="{{ route('manage-devices.index') }}" class="tb-dd-item">
                            <i class="fas fa-barcode"></i> Manage Device
                        </a>
                    @endif
                    @if ($canManageUsers)
                        <a href="{{ route('manage-users.index') }}" class="tb-dd-item">
                            <i class="fas fa-users-cog"></i> Manage User
                        </a>
                    @endif
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
    .tb-search,
    .tb-search-mobile {
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transform: translateZ(0);
        will-change: transform;
        transition: background .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .tb-search:hover,
    .tb-search:focus,
    .tb-search-mobile:hover,
    .tb-search-mobile:focus {
        background: rgba(255, 255, 255, .7);
        border-color: rgba(0, 112, 183, .18);
        box-shadow: 0 10px 24px rgba(41, 71, 149, .08), inset 0 1px 0 rgba(255, 255, 255, .7);
        outline: none;
    }

    .tb-search:active,
    .tb-search.is-launching,
    .tb-search-mobile:active,
    .tb-search-mobile.is-launching {
        transform: scale(.965);
    }

    .tb-search::after,
    .tb-search-mobile::after {
        content: '';
        position: absolute;
        inset: 2px;
        border-radius: inherit;
        pointer-events: none;
        opacity: 0;
        transform: scale(.7);
        background: radial-gradient(circle at var(--gsp-click-local-x, 50%) var(--gsp-click-local-y, 50%),
                rgba(255, 255, 255, .95) 0%,
                rgba(0, 151, 216, .22) 32%,
                rgba(255, 255, 255, 0) 62%);
    }

    .tb-search.is-launching::after,
    .tb-search-mobile.is-launching::after {
        animation: gspTriggerBloom .42s cubic-bezier(.16, 1, .3, 1);
    }

    @keyframes gspTriggerBloom {
        0% { opacity: .9; transform: scale(.65); }
        100% { opacity: 0; transform: scale(1.35); }
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

    .tb-search-mobile {
        flex-shrink: 0;
    }

    .tb-notif-wrap {
        position: relative;
    }

    .tb-notif-dot {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ef4444;
        border: 2px solid rgba(255, 255, 255, .9);
        box-shadow: 0 0 0 1px rgba(239, 68, 68, .35);
    }

    .tb-notif-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: min(360px, calc(100vw - 24px));
        max-height: 420px;
        display: none;
        flex-direction: column;
        overflow: hidden;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, .82);
        background: linear-gradient(145deg, rgba(255, 255, 255, .92), rgba(255, 255, 255, .78));
        box-shadow: 0 22px 60px rgba(15, 23, 42, .18), inset 0 1px 0 rgba(255, 255, 255, .62);
        backdrop-filter: blur(22px) saturate(180%);
        -webkit-backdrop-filter: blur(22px) saturate(180%);
        z-index: 5000;
    }

    .tb-notif-wrap.open .tb-notif-panel,
    .tb-notif-panel.is-open {
        display: flex;
    }

    .tb-notif-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(207, 232, 247, .75);
        font-size: 12px;
        font-weight: 900;
        color: var(--navy, #0f2557);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .tb-notif-mark {
        border: 0;
        background: transparent;
        color: var(--primary, #0070b7);
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        padding: 0;
    }

    .tb-notif-body {
        overflow: auto;
        padding: 10px;
        max-height: 340px;
    }

    .tb-notif-loading,
    .tb-notif-empty {
        padding: 18px 12px;
        text-align: center;
        color: var(--text-muted, #6b7a99);
        font-size: 12px;
        font-weight: 600;
    }

    .tb-notif-item {
        display: block;
        min-width: 0;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid rgba(207, 232, 247, .75);
        background: rgba(255, 255, 255, .62);
        color: inherit;
        text-decoration: none !important;
        margin-bottom: 8px;
        transition: .15s;
    }

    .tb-notif-item:hover {
        border-color: rgba(20, 152, 223, .35);
        background: rgba(224, 242, 254, .55);
        transform: translateY(-1px);
    }

    .tb-notif-item__title {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        font-size: 12.5px;
        font-weight: 900;
        color: var(--navy, #0f2557);
        margin-bottom: 6px;
    }

    .tb-notif-item__title i {
        color: #f59e0b;
    }

    .tb-notif-item__summary {
        font-size: 12px;
        line-height: 1.55;
        font-weight: 600;
        color: var(--text, #1a2340);
        overflow-wrap: anywhere;
    }

    .tb-notif-brief-list {
        display: grid;
        gap: 7px;
        margin: 8px 0 0;
        padding: 0;
        list-style: none;
    }

    .tb-notif-brief-row {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr);
        gap: 8px;
        align-items: flex-start;
        padding: 8px 9px;
        border: 1px solid rgba(207, 232, 247, .72);
        border-radius: 10px;
        background: rgba(248, 251, 255, .68);
    }

    .tb-notif-brief-icon {
        width: 24px;
        height: 24px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #0f5f94;
        background: #e0f2fe;
        font-size: 10px;
    }

    .tb-notif-brief-icon.is-preparation { color: #166534; background: #dcfce7; }
    .tb-notif-brief-icon.is-pulling { color: #1d4ed8; background: #dbeafe; }
    .tb-notif-brief-icon.is-production { color: #7c3aed; background: #ede9fe; }
    .tb-notif-brief-icon.is-stock { color: #b45309; background: #fef3c7; }
    .tb-notif-brief-icon.is-priority,
    .tb-notif-brief-icon.is-error { color: #b91c1c; background: #fee2e2; }

    .tb-notif-brief-copy {
        min-width: 0;
        color: var(--text, #1a2340);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .tb-notif-brief-label {
        display: block;
        margin-bottom: 2px;
        color: var(--text-muted, #6b7a99);
        font-size: 9.5px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .tb-notif-item__meta {
        margin-top: 8px;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--text-muted, #6b7a99);
        overflow-wrap: anywhere;
    }

    .tb-notif-item__cta {
        display: inline-flex;
        margin-top: 10px;
        font-size: 11px;
        font-weight: 800;
        color: var(--primary, #0070b7);
        overflow-wrap: anywhere;
    }

    .tb-right .tb-icon-btn,
    .tb-user-pill {
        background: rgba(255, 255, 255, .38);
        border-color: rgba(255, 255, 255, .68) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .52), 0 8px 20px rgba(41, 71, 149, .06);
        backdrop-filter: blur(16px) saturate(170%);
        -webkit-backdrop-filter: blur(16px) saturate(170%);
    }

    .tb-right .tb-icon-btn:hover,
    .tb-user-pill:hover,
    .tb-user-pill.open {
        background: rgba(255, 255, 255, .62);
        color: var(--primary, #0070b7);
        border-color: rgba(0, 112, 183, .16) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .68), 0 10px 24px rgba(41, 71, 149, .10);
        transform: translateY(-1px);
    }

    .tb-user-dropdown {
        background: linear-gradient(145deg, rgba(255, 255, 255, .84), rgba(255, 255, 255, .64)) !important;
        border: 1px solid rgba(255, 255, 255, .82) !important;
        box-shadow: 0 22px 60px rgba(15, 23, 42, .18), inset 0 1px 0 rgba(255, 255, 255, .62) !important;
        backdrop-filter: blur(22px) saturate(180%);
        -webkit-backdrop-filter: blur(22px) saturate(180%);
    }

    .tb-dd-header {
        background: rgba(255, 255, 255, .38);
        border: 1px solid rgba(255, 255, 255, .62);
    }

    .tb-dd-item:hover {
        background: rgba(255, 255, 255, .56);
        color: var(--primary, #0070b7);
    }

    /* Command palette overlay */
    .gsp-overlay {
        position: fixed;
        inset: 0;
        z-index: 6000;
        display: none;
        --gsp-launch-x: 50vw;
        --gsp-launch-y: 64px;
        perspective: 1000px;
    }

    .gsp-overlay.show {
        display: block;
    }

    .gsp-overlay::before {
        content: '';
        position: absolute;
        left: var(--gsp-launch-x);
        top: var(--gsp-launch-y);
        width: 48px;
        height: 48px;
        border-radius: 999px;
        pointer-events: none;
        opacity: 0;
        transform: translate(-50%, -50%) scale(.25);
        background: radial-gradient(circle, rgba(255, 255, 255, .62), rgba(0, 151, 216, .18) 48%, transparent 72%);
        filter: blur(1px);
        z-index: 1;
    }

    .gsp-overlay.show::before {
        animation: gspLaunchBloom .46s cubic-bezier(.16, 1, .3, 1);
    }

    .gsp-overlay.closing::before {
        display: none;
    }

    .gsp-backdrop {
        position: absolute;
        inset: 0;
        opacity: 0;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, .22), rgba(255, 255, 255, .06)),
            rgba(15, 23, 42, .34);
        backdrop-filter: blur(0) saturate(130%);
        -webkit-backdrop-filter: blur(0) saturate(130%);
        animation: gspBackdropIn .22s ease forwards;
        z-index: 0;
    }

    .gsp-overlay.closing .gsp-backdrop {
        animation: gspBackdropOut .16s ease forwards;
    }

    .gsp-panel {
        position: relative;
        max-width: 640px;
        width: calc(100% - 28px);
        margin: 8vh auto 0;
        background: linear-gradient(145deg, rgba(255, 255, 255, .78), rgba(255, 255, 255, .58));
        border: 1px solid rgba(255, 255, 255, .82);
        border-radius: 22px;
        box-shadow:
            0 30px 90px rgba(15, 23, 42, .30),
            0 10px 28px rgba(0, 112, 183, .12),
            inset 0 1px 0 rgba(255, 255, 255, .72);
        backdrop-filter: blur(28px) saturate(190%);
        -webkit-backdrop-filter: blur(28px) saturate(190%);
        overflow: hidden;
        opacity: 0;
        transform: translate3d(0, -18px, 0) scale(.88);
        transform-origin: top right;
        filter: blur(8px);
        animation: gspPanelIn .34s cubic-bezier(.16, 1, .3, 1) forwards;
        z-index: 2;
    }

    .gsp-overlay.closing .gsp-panel {
        animation: gspPanelOut .16s ease forwards;
    }

    @keyframes gspLaunchBloom {
        0% { opacity: .7; transform: translate(-50%, -50%) scale(.25); }
        60% { opacity: .2; }
        100% { opacity: 0; transform: translate(-50%, -50%) scale(34); }
    }

    @keyframes gspBackdropIn {
        to {
            opacity: 1;
            backdrop-filter: blur(16px) saturate(160%);
            -webkit-backdrop-filter: blur(16px) saturate(160%);
        }
    }

    @keyframes gspBackdropOut {
        from {
            opacity: 1;
            backdrop-filter: blur(16px) saturate(160%);
            -webkit-backdrop-filter: blur(16px) saturate(160%);
        }
        to {
            opacity: 0;
            backdrop-filter: blur(0) saturate(130%);
            -webkit-backdrop-filter: blur(0) saturate(130%);
        }
    }

    @keyframes gspPanelIn {
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            filter: blur(0);
        }
    }

    @keyframes gspPanelOut {
        from {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            filter: blur(0);
        }
        to {
            opacity: 0;
            transform: translate3d(0, -10px, 0) scale(.96);
            filter: blur(4px);
        }
    }

    .gsp-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 16px;
        background: rgba(255, 255, 255, .34);
        border-bottom: 1px solid rgba(255, 255, 255, .58);
    }

    .gsp-head > i {
        color: var(--primary, #0070b7);
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
        font-weight: 700;
        color: var(--text, #1a2340);
    }

    .gsp-head input::placeholder {
        color: rgba(107, 122, 153, .86);
    }

    .gsp-esc {
        flex-shrink: 0;
        border: 1px solid rgba(255, 255, 255, .72);
        background: rgba(255, 255, 255, .46);
        color: var(--text-muted, #6b7a99);
        font-size: 10px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 999px;
        cursor: pointer;
        transition: background .16s ease, color .16s ease, transform .16s ease;
    }

    .gsp-esc:hover {
        background: rgba(0, 112, 183, .10);
        color: var(--primary, #0070b7);
        transform: translateY(-1px);
    }

    .gsp-results {
        max-height: 62vh;
        overflow-y: auto;
        padding: 9px;
        scrollbar-width: thin;
        scrollbar-color: rgba(107, 122, 153, .34) transparent;
    }

    .gsp-results::-webkit-scrollbar {
        width: 8px;
    }

    .gsp-results::-webkit-scrollbar-thumb {
        background: rgba(107, 122, 153, .28);
        border-radius: 999px;
    }

    .gsp-results::-webkit-scrollbar-track {
        background: transparent;
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

    .tb-sr-loading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 4px;
        padding: 12px;
        border-radius: 14px;
        color: var(--text-muted, #6b7a99);
        font-size: 12.5px;
        font-weight: 700;
        background: rgba(255, 255, 255, .34);
        border: 1px solid rgba(255, 255, 255, .58);
    }

    .tb-sr-spinner {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        border: 2px solid rgba(0, 112, 183, .16);
        border-top-color: var(--primary, #0070b7);
        animation: gspSpin .7s linear infinite;
        flex-shrink: 0;
    }

    .tb-sr-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 11px;
        border-radius: 14px;
        color: var(--text, #1a2340);
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        background: transparent;
        border: 1px solid transparent;
        transform: translateZ(0);
        transition: background .16s ease, border-color .16s ease, box-shadow .16s ease, color .16s ease, transform .16s ease;
        animation: gspItemIn .2s ease both;
    }

    .tb-sr-item i {
        width: 26px;
        height: 26px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: var(--primary, #0070b7);
        background: rgba(0, 112, 183, .08);
        flex-shrink: 0;
    }

    .tb-sr-item.active,
    .tb-sr-item:hover {
        background: rgba(255, 255, 255, .54);
        border-color: rgba(255, 255, 255, .74);
        box-shadow: 0 10px 24px rgba(41, 71, 149, .10), inset 0 1px 0 rgba(255, 255, 255, .5);
        color: var(--primary, #0070b7);
        transform: translateY(-1px) scale(1.01);
    }

    .tb-sr-item.active i,
    .tb-sr-item:hover i {
        color: #fff;
        background: linear-gradient(135deg, #0070b7, #0097d8);
    }

    .tb-sr-item:active {
        transform: scale(.985);
    }

    .tb-sr-section {
        padding: 10px 12px 5px;
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
        background: rgba(255, 255, 255, .48);
        color: var(--text-muted, #6b7a99);
        border: 1px solid rgba(255, 255, 255, .68);
    }

    .tb-sr-empty {
        padding: 22px 16px;
        text-align: center;
        color: var(--text-muted, #6b7a99);
        font-size: 12px;
        font-weight: 600;
    }

    .tb-sr-empty-title {
        margin-bottom: 5px;
        color: var(--text, #1a2340);
        font-size: 13px;
        font-weight: 800;
    }

    .tb-sr-empty-sub {
        max-width: 360px;
        margin: 0 auto;
        line-height: 1.5;
    }

    @keyframes gspItemIn {
        from { opacity: 0; transform: translateY(4px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes gspSpin {
        to { transform: rotate(360deg); }
    }

    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .gsp-backdrop {
            background: rgba(15, 23, 42, .48);
        }

        .gsp-panel {
            background: rgba(255, 255, 255, .96);
        }
    }

    @media (max-width: 768px) {
        body > .tb-notif-panel {
            position: fixed;
            top: 86px;
            left: 12px;
            right: 12px;
            width: auto;
            max-height: min(72vh, 560px);
            border-radius: 14px;
            transform: none;
            z-index: 7200;
        }

        body > .tb-notif-panel .tb-notif-body {
            max-height: calc(min(72vh, 560px) - 48px);
        }

        .gsp-panel {
            width: calc(100% - 20px);
            margin-top: 70px;
            border-radius: 20px;
        }

        .gsp-results {
            max-height: 68vh;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .tb-search,
        .gsp-backdrop,
        .gsp-panel,
        .tb-sr-item,
        .gsp-esc,
        .tb-sr-spinner {
            animation: none !important;
            transition: none !important;
        }

        .gsp-backdrop,
        .gsp-panel {
            opacity: 1;
            transform: none;
            filter: none;
        }
    }
</style>

<script>
    // Global search command palette: menu (link sidebar, instan) + data lintas modul (endpoint backend)
    (function () {
        const trigger = document.getElementById('globalNavSearch');
        const mobileTrigger = document.getElementById('globalNavSearchMobile');
        const triggers = [trigger, mobileTrigger].filter(Boolean);
        const overlay = document.getElementById('gspOverlay');
        const input = document.getElementById('gspInput');
        const results = document.getElementById('gspResults');
        if (!overlay || !input || !results) return;

        const dataUrl = (triggers[0] && triggers[0].getAttribute('data-search-url')) || '';

        // Akses cepat ditampilkan saat palette dibuka (sebelum mengetik)
        const fastRoutes = [
            { label: 'Scan Detail', url: '{{ route('loadingList.index') }}', icon: 'fa-truck-loading' },
            { label: 'Production Result', url: '{{ route('dashboard.prodResult') }}', icon: 'fa-chart-line' },
            { label: 'Production Stock', url: '{{ route('dashboard.productionStock') }}', icon: 'fa-box' },
            { label: 'Alamat Chute', url: '{{ route('pulling.master-alamat-chute') }}', icon: 'fa-map-marker-alt' },
            { label: 'PIS Scanning', url: '{{ route('pis.index') }}', icon: 'fa-barcode' },
            { label: 'Kanban Reset', url: '{{ route('pulling.manual') }}', icon: 'fa-sync-alt' }
        ];

        let menuIndex = null;
        let dataResults = [];
        let lastQuery = '';
        let isLoading = false;
        let debounceTimer = null;
        let closeTimer = null;
        let launchTimer = null;
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

        function loadingHtml() {
            return '<div class="tb-sr-loading"><span class="tb-sr-spinner"></span><span>Mencari data...</span></div>';
        }

        function emptyHtml(kw) {
            return '<div class="tb-sr-empty">' +
                '<div class="tb-sr-empty-title">Belum ketemu untuk "' + esc(kw) + '"</div>' +
                '<div class="tb-sr-empty-sub">Coba part number, loading list, alamat chute, atau nama menu.</div>' +
                '</div>';
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
            if (isLoading) {
                html += loadingHtml();
            }
            if (!menu.length && !data.length) {
                html = isLoading ? loadingHtml() : emptyHtml(kw);
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
            clearTimeout(debounceTimer);
            if (!dataUrl || kw.length < 2) {
                if (ctrl) ctrl.abort();
                dataResults = [];
                isLoading = false;
                return;
            }
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
                        isLoading = false;
                        dataResults = (resp && resp.results) || [];
                        renderAll(menuMatches(kw), dataResults, kw);
                    })
                    .catch(function () {
                        if (lastQuery !== kw) return;
                        isLoading = false;
                        renderAll(menuMatches(kw), dataResults, kw);
                    });
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
            if (!kw) { isLoading = false; renderFastRoutes(); return; }
            isLoading = !!(dataUrl && kw.length >= 2);
            renderAll(menuMatches(kw), dataResults, kw); // menu instan, data menyusul
            fetchData(kw);
        }

        function setActive(items, idx) {
            items.forEach(function (el, i) { el.classList.toggle('active', i === idx); });
            if (items[idx]) items[idx].scrollIntoView({ block: 'nearest' });
        }

        function setLaunchPoint(e) {
            const source = (e && e.currentTarget && e.currentTarget.getBoundingClientRect) ? e.currentTarget : trigger;
            const rect = source ? source.getBoundingClientRect() : null;
            const hasPointer = e && typeof e.clientX === 'number' && e.clientX > 0;
            const x = hasPointer ? e.clientX : (rect ? rect.left + rect.width / 2 : window.innerWidth / 2);
            const y = hasPointer ? e.clientY : (rect ? rect.top + rect.height / 2 : 64);

            overlay.style.setProperty('--gsp-launch-x', x + 'px');
            overlay.style.setProperty('--gsp-launch-y', y + 'px');

            if (!source || !rect) return;
            source.style.setProperty('--gsp-click-local-x', (x - rect.left) + 'px');
            source.style.setProperty('--gsp-click-local-y', (y - rect.top) + 'px');
            source.classList.remove('is-launching');
            void source.offsetWidth;
            source.classList.add('is-launching');
            clearTimeout(launchTimer);
            launchTimer = setTimeout(function () { source.classList.remove('is-launching'); }, 420);
        }

        function open(e) {
            setLaunchPoint(e);
            clearTimeout(closeTimer);
            if (overlay.classList.contains('show') && !overlay.classList.contains('closing')) {
                input.focus();
                return;
            }
            overlay.classList.remove('closing');
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            input.value = '';
            dataResults = [];
            doSearch();
            setTimeout(function () { input.focus(); }, 90);
        }

        function close() {
            if (!overlay.classList.contains('show') || overlay.classList.contains('closing')) return;
            overlay.classList.add('closing');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            input.blur();
            clearTimeout(closeTimer);
            closeTimer = setTimeout(function () {
                overlay.classList.remove('show', 'closing');
            }, 170);
        }

        // Buka palette: klik trigger, Enter/Space di trigger, atau Ctrl/Cmd + K
        triggers.forEach(function (el) {
            el.addEventListener('click', open);
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(e); }
            });
        });
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

    // BELLA notifications — brief pagi di navbar
    (function() {
        var wrap = document.getElementById('tbNotifWrap');
        var btn = document.getElementById('tbNotifBtn');
        var panel = document.getElementById('tbNotifPanel');
        var body = document.getElementById('tbNotifBody');
        var dot = document.getElementById('tbNotifDot');
        var markRead = document.getElementById('tbNotifMarkRead');
        if (!wrap || !btn || !panel || !body) return;

        var panelHome = panel.parentNode;
        var url = @json(route('dashboard.bella.notifications'));
        var overviewUrl = @json(route('dashboard.overview'));
        var readKey = 'bellaNotifReadDate';
        var loaded = false;

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
            });
        }

        function briefLines(item) {
            if (Array.isArray(item.bullets) && item.bullets.length) return item.bullets;
            return String(item.summary || '')
                .split(/\r?\n/)
                .map(function(line) { return line.replace(/^\s*-\s*/, '').trim(); })
                .filter(Boolean);
        }

        function briefKind(label) {
            var key = String(label || '').toLowerCase();
            if (key.indexOf('preparation') !== -1) return ['preparation', 'fas fa-clipboard-check'];
            if (key.indexOf('pulling') !== -1) return ['pulling', 'fas fa-truck-loading'];
            if (key.indexOf('produksi') !== -1 || key.indexOf('production') !== -1) return ['production', 'fas fa-industry'];
            if (key.indexOf('stok') !== -1 || key.indexOf('stock') !== -1 || key.indexOf('part') !== -1) return ['stock', 'fas fa-boxes'];
            if (key.indexOf('prioritas') !== -1) return ['priority', 'fas fa-exclamation'];
            if (key.indexOf('error') !== -1) return ['error', 'fas fa-bug'];
            return ['default', 'fas fa-circle'];
        }

        function renderBriefLine(line) {
            var parts = String(line || '').split(':');
            var label = parts.length > 1 ? parts.shift().trim() : 'Info';
            var value = (parts.length ? parts.join(':') : line).trim();
            var kind = briefKind(label);
            return '<li class="tb-notif-brief-row">' +
                '<span class="tb-notif-brief-icon is-' + kind[0] + '"><i class="' + kind[1] + '"></i></span>' +
                '<span class="tb-notif-brief-copy">' +
                    '<span class="tb-notif-brief-label">' + esc(label) + '</span>' +
                    esc(value) +
                '</span>' +
                '</li>';
        }

        function todayKey() {
            return new Date().toISOString().slice(0, 10);
        }

        function isUnread() {
            try {
                return localStorage.getItem(readKey) !== todayKey();
            } catch (e) {
                return true;
            }
        }

        function setRead() {
            try {
                localStorage.setItem(readKey, todayKey());
            } catch (e) {}
            if (dot) dot.hidden = true;
        }

        function updateDot() {
            if (dot) dot.hidden = !isUnread();
        }

        function isMobileNotif() {
            return window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
        }

        function syncPanelHost() {
            if (isMobileNotif()) {
                if (panel.parentNode !== document.body) document.body.appendChild(panel);
                return;
            }

            if (panel.parentNode !== panelHome) panelHome.appendChild(panel);
        }

        function setOpen(open) {
            syncPanelHost();
            wrap.classList.toggle('open', open);
            panel.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (open && !loaded) loadNotifications();
        }

        function renderNotifications(items) {
            if (!items || !items.length) {
                body.innerHTML = '<div class="tb-notif-empty">Belum ada notifikasi hari ini.</div>';
                return;
            }
            body.innerHTML = items.map(function(item) {
                var href = item.ask_url || (overviewUrl + '?bella=brief');
                var lines = briefLines(item);
                var summary = lines.length
                    ? '<ul class="tb-notif-brief-list">' + lines.map(renderBriefLine).join('') + '</ul>'
                    : '<div class="tb-notif-item__summary">' + esc(item.summary || '') + '</div>';
                return '<a class="tb-notif-item" href="' + esc(href) + '">' +
                    '<div class="tb-notif-item__title"><i class="fas fa-sun"></i>' + esc(item.title || 'Brief BELLA') + '</div>' +
                    summary +
                    '<div class="tb-notif-item__meta">Diperbarui ' + esc(item.generated_at || '-') + '</div>' +
                    '<span class="tb-notif-item__cta">Buka dashboard · Tanya BELLA →</span>' +
                    '</a>';
            }).join('');
        }

        function loadNotifications() {
            body.innerHTML = '<div class="tb-notif-loading">Memuat brief BELLA...</div>';
            fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    loaded = true;
                    renderNotifications((data && data.notifications) || []);
                    updateDot();
                })
                .catch(function() {
                    body.innerHTML = '<div class="tb-notif-empty">Gagal memuat notifikasi.</div>';
                });
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            setOpen(!panel.classList.contains('is-open'));
        });

        if (markRead) {
            markRead.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setRead();
            });
        }

        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target) && !panel.contains(e.target)) setOpen(false);
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') setOpen(false);
        });

        window.addEventListener('resize', function() {
            syncPanelHost();
        });

        body.addEventListener('click', function(e) {
            if (e.target.closest('.tb-notif-item')) setRead();
        });

        updateDot();
        loadNotifications();
    })();
</script>
