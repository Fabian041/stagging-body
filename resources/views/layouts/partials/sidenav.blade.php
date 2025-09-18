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
                    <li class="{{ request()->is('dashboard.prodPlan') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard.prodPlan') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Production Plan</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('loading-list') || request()->is('loading-list/*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('loadingList.index') }}">
                            <i class="fas fa-truck-loading"></i>
                            <span>Delivery</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('pulling.manual') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pulling.manual') }}">
                            <i class="fas fa-sync-alt"></i>
                            <span>Kanban Reset</span>
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
                    <li class="{{ request()->is('dashboard.kbnCheck') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard.kbnCheck') }}">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Check Kanban</span>
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

            <li class="{{ request()->is('error/log') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('error.log') }}">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Error Log</span>
                </a>
            </li>
        </ul>
    </aside>
</div>
