<div id="sidebar-wrapper">

    {{-- ===== LOGO ===== --}}
    <div class="sb-logo">
        <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:9px;text-decoration:none;">
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

    {{-- ===== NAV ===== --}}
    <nav class="sb-nav">

        <div class="sb-section-label" style="margin-top: 16px;">Main Menu</div>

        {{-- ── Dashboard Overview ── --}}
        <a class="sb-item {{ request()->is('dashboard/overview') ? 'active' : '' }}"
            href="{{ route('dashboard.overview') }}">
            <i class="fas fa-chart-pie sb-icon"></i>
            <span class="sb-item-label">Dashboard</span>
            <span class="sb-item-tooltip">Dashboard</span>
        </a>

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
            @php
                $loadingListDetailActive =
                    (request()->is('loading-list') || request()->is('loading-list/*')) &&
                    !request()->is('loading-list/absen-driver*');
            @endphp
            <a class="sb-sub-item {{ request()->is('loading-list/absen-driver*') ? 'active' : '' }}"
                href="{{ route('loadingList.driverAbsen') }}">
                <i class="fas fa-truck"></i>
                Absen Driver
            </a>
            <a class="sb-sub-item {{ $loadingListDetailActive ? 'active' : '' }}"
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
                Kanban Check
            </a>
            <a class="sb-sub-item {{ request()->routeIs('dashboard.partCheck') ? 'active' : '' }}"
                href="{{ route('dashboard.partCheck') }}">
                <i class="fas fa-cogs"></i>
                Part Check
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
        </div>

        {{-- ── Quality ── --}}
        @php
            $qualityActive = request()->routeIs('quality.*');
        @endphp

        <div class="sb-item {{ $qualityActive ? 'open' : '' }}" data-has-sub>
            <i class="fas fa-clipboard-check sb-icon"></i>
            <span class="sb-item-label">Quality</span>
            <i class="fas fa-chevron-right sb-chevron"></i>
            <span class="sb-item-tooltip">Quality</span>
        </div>
        <div class="sb-submenu">
            <a class="sb-sub-item {{ request()->routeIs('quality.scan') ? 'active' : '' }}"
                href="{{ route('quality.scan') }}">
                <i class="fas fa-barcode"></i>
                Scan Quality
            </a>
            <a class="sb-sub-item {{ request()->routeIs('quality.pareto') ? 'active' : '' }}"
                href="{{ route('quality.pareto') }}">
                <i class="fas fa-chart-bar"></i>
                Reject & Pareto
            </a>
            <a class="sb-sub-item {{ request()->routeIs('quality.history') || request()->routeIs('quality.show') ? 'active' : '' }}"
                href="{{ route('quality.history') }}">
                <i class="fas fa-list-ul"></i>
                Detail Scan
            </a>
        </div>

        {{-- ── Master Data ── --}}
        @php
            $masterActive =
                request()->routeIs('pis.master') ||
                request()->routeIs('pis.edit') ||
                request()->routeIs('pis.preview') ||
                request()->routeIs('dashboard.master.*') ||
                request()->routeIs('pulling.master-alamat-chute*');
        @endphp

        <div class="sb-item {{ $masterActive ? 'open' : '' }}" data-has-sub>
            <i class="fas fa-database sb-icon"></i>
            <span class="sb-item-label">Master Data</span>
            <i class="fas fa-chevron-right sb-chevron"></i>
            <span class="sb-item-tooltip">Master Data</span>
        </div>
        <div class="sb-submenu">
            <a class="sb-sub-item {{ request()->routeIs('dashboard.master.molds.*') ? 'active' : '' }}"
                href="{{ route('dashboard.master.molds.index') }}">
                <i class="fas fa-cubes"></i>
                Mold Injection
            </a>
            <a class="sb-sub-item {{ request()->routeIs('dashboard.master.parts.*') ? 'active' : '' }}"
                href="{{ route('dashboard.master.parts.index') }}">
                <i class="fas fa-cog"></i>
                Part Internal
            </a>
            <a class="sb-sub-item {{ request()->routeIs('dashboard.master.customer-parts.*') ? 'active' : '' }}"
                href="{{ route('dashboard.master.customer-parts.index') }}">
                <i class="fas fa-box-open"></i>
                Part Customer
            </a>
            <a class="sb-sub-item {{ request()->routeIs('pis.master') || request()->routeIs('pis.edit') || request()->routeIs('pis.preview') ? 'active' : '' }}"
                href="{{ route('pis.master') }}">
                <i class="fas fa-database"></i>
                Master Data PIS
            </a>
            <a class="sb-sub-item {{ request()->routeIs('pulling.master-alamat-chute*') ? 'active' : '' }}"
                href="{{ route('pulling.master-alamat-chute') }}">
                <i class="fas fa-map-marker-alt"></i>
                Alamat Chute
            </a>
        </div>

        <div class="sb-section-label" style="margin-top:6px;">System</div>

        <a class="sb-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
            <i class="far fa-user sb-icon"></i>
            <span class="sb-item-label">Profile</span>
            <span class="sb-item-tooltip">Profile</span>
        </a>

        <a class="sb-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"
            href="{{ route('settings.index') }}">
            <i class="fas fa-cog sb-icon"></i>
            <span class="sb-item-label">Settings</span>
            <span class="sb-item-tooltip">Settings</span>
        </a>

        @can('manage-device-sessions')
            <a class="sb-item {{ request()->routeIs('manage-devices.*') ? 'active' : '' }}"
                href="{{ route('manage-devices.index') }}">
                <i class="fas fa-barcode sb-icon"></i>
                <span class="sb-item-label">Manage Device</span>
                <span class="sb-item-tooltip">Manage Device</span>
            </a>
        @endcan

        @if (auth()->check() && auth()->user()->isSuperAdmin())
            <a class="sb-item {{ request()->routeIs('manage-users.*') ? 'active' : '' }}"
                href="{{ route('manage-users.index') }}">
                <i class="fas fa-users-cog sb-icon"></i>
                <span class="sb-item-label">Manage User</span>
                <span class="sb-item-tooltip">Manage User</span>
            </a>
        @endif

        {{-- ── Error Log ── --}}
        <a class="sb-item {{ request()->is('error/log') ? 'active' : '' }}" href="{{ route('error.log') }}">
            <i class="fas fa-exclamation-circle sb-icon"></i>
            <span class="sb-item-label">Error Log</span>
            <span class="sb-item-tooltip">Error Log</span>
        </a>

    </nav>

    {{-- ===== USER CARD (dipindah ke bawah) ===== --}}
    @php
        $sidebarUser = auth()->user();
        $sidebarName = $sidebarUser ? $sidebarUser->name : 'Guest';
        $sidebarRole = $sidebarUser ? $sidebarUser->role ?? 'User' : 'Not logged in';
        $sidebarRoleParts = array_values(array_filter(preg_split('/[\s_-]+/', (string) $sidebarRole) ?: []));
        $sidebarBadge =
            count($sidebarRoleParts) > 1
                ? implode('', array_map(fn($part) => strtoupper(substr($part, 0, 1)), $sidebarRoleParts))
                : strtoupper(substr((string) $sidebarRole, 0, 5));
    @endphp
    <div class="sb-user-wrap">
        <div class="sb-user">
            <div class="sb-avatar" title="{{ $sidebarName }}">
                {{ $sidebarUser ? strtoupper(substr($sidebarName, 0, 2)) : 'GS' }}
            </div>
            <div class="sb-user-meta">
                <div class="sb-user-name" title="{{ $sidebarName }}">{{ $sidebarName }}</div>
                <div class="sb-user-role" title="{{ $sidebarRole }}">{{ $sidebarRole }}</div>
            </div>
            @if ($sidebarUser)
                <div class="sb-user-badge" title="{{ $sidebarRole }}">{{ $sidebarBadge }}</div>
            @endif
        </div>
    </div>

    {{-- ===== BOTTOM: version ===== --}}
    <div class="sb-bottom">
        <div style="padding:6px 10px;display:flex;align-items:center;gap:8px;">
        </div>
    </div>

</div>
