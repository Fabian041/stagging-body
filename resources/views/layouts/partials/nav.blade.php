<nav class="navbar navbar-expand-lg main-navbar">
    <ul class="navbar-nav mr-auto mt-2">
        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg mt-3"><i class="fas fa-bars"></i></a>
        </li>
        <div class="search-element mt-2">
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="250"
                style="border-radius:6px">
            <div class="search-backdrop"></div>
        </div>
    </ul>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown"><a href="#" data-toggle="dropdown"
                class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">{{ auth()->user()->name }}, Logged in</div>
                <a href="features-profile.html" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> Profile
                </a>
                <a href="features-settings.html" class="dropdown-item has-icon">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout.auth') }}" method="post">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger" id="logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
