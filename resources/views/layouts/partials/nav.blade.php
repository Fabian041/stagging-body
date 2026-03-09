<nav class="navbar navbar-expand-lg main-navbar">
    <ul class="navbar-nav mr-auto mt-2">
        <li>
            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg ">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <div class="d-sm-none d-lg-inline-block">
                    Hi, {{ auth()->check() ? auth()->user()->name : 'Guest' }}
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">
                    {{ auth()->check() ? auth()->user()->name : 'Guest' }}, {{ auth()->check() ? 'Logged in' : 'Not logged in' }}
                    @if(auth()->check())
                    <div class="small text-muted mt-1">
                        Role: {{ auth()->user()->role ?? '-' }}
                    </div>
                    @endif
                    
                </div>
                @if(auth()->check())
                <a href="features-profile.html" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> Profile
                </a>
                <a href="features-settings.html" class="dropdown-item has-icon">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>

                <form action="{{ route('logout.auth') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger" id="logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </form>
                @else
                <a href="{{ route('login.index') }}" class="dropdown-item has-icon">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                @endif
            </div>
        </li>
    </ul>
</nav>
