<div id="topbar">

    {{-- ===== LEFT: mobile toggle + page title ===== --}}
    <div class="tb-left">
        {{-- Mobile hamburger --}}
        <button id="mobile-menu-btn" class="tb-icon-btn d-lg-none" style="border:none;flex-shrink:0;" title="Open menu">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Page title (rendered by each page via @section or just static) --}}
        <div class="d-none d-md-block">
            <div class="tb-page-title">{{ $pageTitle ?? 'Dashboard' }}</div>
            @if (!empty($pageSubtitle))
                <div class="tb-page-sub">{{ $pageSubtitle }}</div>
            @endif
        </div>
    </div>

    {{-- ===== RIGHT: search + actions ===== --}}
    <div class="tb-right">

        {{-- Search --}}
        <div class="tb-search d-none d-md-flex">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Quick search...">
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
