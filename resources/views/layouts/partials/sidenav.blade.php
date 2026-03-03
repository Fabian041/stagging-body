<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard.index') }}" class="d-inline-block">
                <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid mt-3" style="max-width: 100px;">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('dashboard.index') }}" class="d-inline-block">
                <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid mt-3" style="max-width: 50px;">
            </a>
        </div>
        <hr class="sidebar-divider">



        <ul class="sidebar-menu mt-2">
            <li class="menu-header">Main Menu</li>

            {{-- PPIC Submenu --}}
            <li
                class="nav-item dropdown {{ request()->is('dashboard/receiving*') || request()->is('dashboard/production/landing') || request()->is('loading-list') || request()->is('loading-list/*') || request()->is('pulling/manual') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-warehouse"></i> <span>PPIC</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('dashboard/production/landing') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('board.landing') }}">
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
                    <li class="{{ request()->is('pulling/manual') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pulling.manual') }}">
                            <i class="fas fa-sync-alt"></i>
                            <span>Kanban Reset</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Production Submenu --}}
            <li
                class="nav-item dropdown {{ request()->is('dashboard/production*')|| request()->is('dashboard/production/stock') || request()->is('dashboard/production/result') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-industry"></i>
                    <span>Production</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('dashboard/production/stock') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dashboard.productionStock') }}">
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

            {{-- PIS Submenu --}}
            <li
                class="nav-item dropdown {{ request()->is('pis*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-qrcode"></i>
                    <span>PIS</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->routeIs('pis.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pis.index') }}">
                            <i class="fas fa-barcode"></i>
                            <span>Scanning</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('pis.scanList') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pis.scanList') }}">
                            <i class="fas fa-list-alt"></i>
                            <span>Scan List</span>
                        </a>
                    </li>
                    <!-- <li class="{{ request()->routeIs('pis.packing') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pis.packing') }}">
                            <i class="fas fa-box"></i>
                            <span>Packing</span>
                        </a>
                    </li> -->
                    <li class="{{ request()->routeIs('pis.master') || request()->routeIs('pis.edit') || request()->routeIs('pis.preview') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pis.master') }}">
                            <i class="fas fa-database"></i>
                            <span>Master Data</span>
                        </a>
                    </li>
                    <!-- <li class="{{ request()->routeIs('pis.validasi') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('pis.validasi') }}">
                            <i class="fas fa-check-circle"></i>
                            <span>Validation</span>
                        </a>
                    </li> -->
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
