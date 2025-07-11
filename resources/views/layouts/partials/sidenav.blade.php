<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid mt-3" style="max-width: 100px;">
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid mt-3" style="max-width: 50px;">
        </div>
        <ul class="sidebar-menu mt-2">
            <li class="menu-header">Main Menu</li>

            {{-- PPIC Submenu --}}
            <li
                class="nav-item dropdown {{ request()->is('dashboard.receiving') || request()->is('loading-list') || request()->is('loading-list/*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-warehouse"></i> <span>PPIC</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('dashboard.receiving') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard.receiving') }}">
                            <i class="fas fa-inbox"></i>
                            <span>Receiving</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('loading-list') || request()->is('loading-list/*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('loadingList.index') }}">
                            <i class="fas fa-truck-loading"></i>
                            <span>Delivery</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Production Submenu --}}
            <li
                class="nav-item dropdown {{ request()->is('dashboard') || request()->is('dashboard/production/result') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-industry"></i>
                    <span>Production</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard.index') }}">
                            <i class="fas fa-box"></i>
                            <span>Production Stock</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/production/result') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard.prodResult') }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Production Result</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </aside>
</div>
