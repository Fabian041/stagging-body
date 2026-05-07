<div id="sidebar-wrapper">

    {{-- ===== LOGO ===== --}}
    <div class="sb-logo">
        <a href="{{ route('dashboard.index') }}" style="display:flex;align-items:center;gap:9px;text-decoration:none;">
            <div class="sb-logo-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div>
                <span class="sb-logo-text">BELLA</span>
                <span class="sb-logo-sub">Management System</span>
            </div>
        </a>

        {{-- Collapse toggle --}}
        <div class="sb-toggle" id="sb-toggle-btn" title="Toggle sidebar">
            <i class="fas fa-chevron-left"></i>
        </div>
    </div>

    {{-- ===== USER CARD ===== --}}
    <div class="sb-user-wrap">
        <div class="sb-user">
            <div class="sb-avatar">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'GS' }}
            </div>
            <div style="min-width:0;flex:1;">
                <div class="sb-user-name">{{ auth()->check() ? auth()->user()->name : 'Guest' }}</div>
                <div class="sb-user-role">{{ auth()->check() ? auth()->user()->role ?? 'User' : 'Not logged in' }}
                </div>
            </div>
            @if (auth()->check())
                <div class="sb-user-badge">{{ strtoupper(auth()->user()->role ?? 'USR') }}</div>
            @endif
        </div>
    </div>

    {{-- ===== NAV ===== --}}
    <nav class="sb-nav">

        <div class="sb-section-label">Main Menu</div>

        {{-- ── PPIC ── --}}
        @php
            $ppicActive =
                request()->is('dashboard/receiving*') ||
                request()->is('dashboard/delivery') ||
                request()->is('loading-list*') ||
                request()->is('pulling/manual');
        @endphp

        <div class="sb-item {{ $ppicActive ? 'open' : '' }}" data-has-sub>
            <i class="fas fa-warehouse sb-icon"></i>
            <span class="sb-item-label">PPIC</span>
            <i class="fas fa-chevron-right sb-chevron"></i>
            <span class="sb-item-tooltip">PPIC</span>
        </div>
        <div class="sb-submenu">
            <a class="sb-sub-item {{ request()->is('dashboard/delivery') ? 'active' : '' }}"
                href="{{ route('dashboard.delivery') }}">
                <i class="fas fa-chart-bar"></i>
                Preparation Monitoring
            </a>
            <a class="sb-sub-item {{ request()->is('loading-list') || request()->is('loading-list/*') ? 'active' : '' }}"
                href="{{ route('loadingList.index') }}">
                <i class="fas fa-truck-loading"></i>
                Preparation Detail
            </a>
            <a class="sb-sub-item {{ request()->is('pulling/manual') ? 'active' : '' }}"
                href="{{ route('pulling.manual') }}">
                <i class="fas fa-sync-alt"></i>
                Kanban Reset
            </a>
        </div>

        {{-- ── Production ── --}}
        @php
            $prodActive = request()->is('dashboard/production*');
        @endphp

        <div class="sb-item {{ $prodActive ? 'open' : '' }}" data-has-sub>
            <i class="fas fa-industry sb-icon"></i>
            <span class="sb-item-label">Production</span>
            <i class="fas fa-chevron-right sb-chevron"></i>
            <span class="sb-item-tooltip">Production</span>
        </div>
        <div class="sb-submenu">
            <a class="sb-sub-item {{ request()->is('dashboard/production/stock') ? 'active' : '' }}"
                href="{{ route('dashboard.productionStock') }}">
                <i class="fas fa-box"></i>
                Production Stock
            </a>
            <a class="sb-sub-item {{ request()->routeIs('dashboard.kbnCheck') ? 'active' : '' }}"
                href="{{ route('dashboard.kbnCheck') }}">
                <i class="fas fa-clipboard-list"></i>
                Check Kanban
            </a>
            <a class="sb-sub-item {{ request()->is('dashboard/production/result') ? 'active' : '' }}"
                href="{{ route('dashboard.prodResult') }}">
                <i class="fas fa-chart-line"></i>
                Production Result
            </a>
        </div>

        {{-- ── PIS ── --}}
        @php
            $pisActive = request()->is('pis*');
        @endphp

        <div class="sb-item {{ $pisActive ? 'open' : '' }}" data-has-sub>
            <i class="fas fa-qrcode sb-icon"></i>
            <span class="sb-item-label">PIS</span>
            <i class="fas fa-chevron-right sb-chevron"></i>
            <span class="sb-item-tooltip">PIS</span>
        </div>
        <div class="sb-submenu">
            <a class="sb-sub-item {{ request()->routeIs('pis.index') ? 'active' : '' }}"
                href="{{ route('pis.index') }}">
                <i class="fas fa-barcode"></i>
                Scanning
            </a>
            <a class="sb-sub-item {{ request()->routeIs('pis.scanList') ? 'active' : '' }}"
                href="{{ route('pis.scanList') }}">
                <i class="fas fa-list-alt"></i>
                Scan List
            </a>
            <a class="sb-sub-item {{ request()->routeIs('pis.master') || request()->routeIs('pis.edit') || request()->routeIs('pis.preview') ? 'active' : '' }}"
                href="{{ route('pis.master') }}">
                <i class="fas fa-database"></i>
                Master Data
            </a>
        </div>

        <div class="sb-section-label" style="margin-top:6px;">System</div>

        {{-- ── Error Log ── --}}
        <a class="sb-item {{ request()->is('error/log') ? 'active' : '' }}" href="{{ route('error.log') }}">
            <i class="fas fa-exclamation-circle sb-icon"></i>
            <span class="sb-item-label">Error Log</span>
            <span class="sb-item-tooltip">Error Log</span>
        </a>

    </nav>

    {{-- ===== BOTTOM: version ===== --}}
    <div class="sb-bottom">
        <div style="padding:6px 10px;display:flex;align-items:center;gap:8px;">
            <div
                style="width:6px;height:6px;border-radius:50%;background:#22c55e;flex-shrink:0;box-shadow:0 0 0 3px rgba(34,197,94,.18);">
            </div>
            <span class="sb-item-label" style="font-size:11px;color:var(--text-muted);">System online · v1.0</span>
        </div>
    </div>

</div>
