<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid mt-3" style="max-width: 100px;">
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <img src="{{ asset('assets/img/bella.png') }}" alt="Bella" class="img-fluid mt-3" style="max-width: 50px;">
        </div>
        <ul class="sidebar-menu mt-2">
            <li class="menu-header">Dashboard</li>
            <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard.index') }}">
                    <i class="fas fa-fire"></i>
                    <span class="beep">Dashboard</span>
                </a>
            </li>
            <li class="{{ request()->is('loading-list') || request()->is('loading-list/*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('loadingList.index') }}">
                    <i class="fas fa-solid fa-list-ul"></i>
                    <span class="">Loading List Details</span>
                </a>
            </li>
        </ul>
    </aside>
</div>
