@extends('layouts.root.auth')

@section('title', 'Bella - Portal')

@section('main')
    <style>
        .portal-wrap {
            min-height: 100vh;
            /* Versi terang dari gradient halaman login: senada brand, tetap minimalis.
               Dua blob radial di-drift via background-position; base terang tetap diam. */
            background:
                radial-gradient(circle, rgba(0, 151, 216, .14) 0%, transparent 36%),
                radial-gradient(circle, rgba(41, 71, 149, .10) 0%, transparent 38%),
                linear-gradient(160deg, #F8FBFF 0%, #EEF3FA 100%);
            background-size: 160% 160%, 170% 170%, 100% 100%;
            background-position: 0% 20%, 100% 80%, 0 0;
            color: #1F2430;
            display: flex;
            flex-direction: column;
            justify-content: center;
            animation: portalDrift 22s ease-in-out infinite;
        }

        @keyframes portalDrift {
            0%   { background-position: 0% 20%, 100% 80%, 0 0; }
            33%  { background-position: 60% 50%, 30% 40%, 0 0; }
            66%  { background-position: 30% 80%, 70% 20%, 0 0; }
            100% { background-position: 0% 20%, 100% 80%, 0 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .portal-wrap {
                animation: none;
            }
        }

        .portal-topline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 30px;
        }

        .portal-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 700;
            color: #fff !important;
            text-decoration: none;
            background: linear-gradient(135deg, #294795, #0070B7);
            box-shadow: 0 8px 20px rgba(0, 112, 183, .22);
            transition: background .16s ease, transform .16s ease, box-shadow .16s ease;
        }

        .portal-login:hover {
            background: linear-gradient(135deg, #1d3a80, #0063a3);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(0, 112, 183, .28);
            text-decoration: none;
        }

        /* ===== USER PILL (authed) ===== */
        .portal-user {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            height: 44px;
            padding: 5px 12px 5px 5px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid #E3E9F3;
            cursor: pointer;
            outline: none;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .portal-user:hover,
        .portal-user:focus-within {
            border-color: #CFE0F1;
        }

        .pu-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: linear-gradient(135deg, #294795, #0070B7);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .pu-name {
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 700;
            color: #1F2430;
        }

        .pu-chev {
            font-size: 10px;
            color: #9AA1AC;
            transition: transform .16s ease;
        }

        .portal-user:hover .pu-chev,
        .portal-user:focus-within .pu-chev {
            transform: rotate(180deg);
        }

        .portal-user-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 210px;
            padding: 6px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #E3E9F3;
            box-shadow: 0 18px 44px rgba(16, 24, 40, .16);
            opacity: 0;
            visibility: hidden;
            transform: translateY(6px);
            transition: opacity .16s ease, transform .16s ease, visibility .16s;
            z-index: 20;
        }

        .portal-user:hover .portal-user-menu,
        .portal-user:focus-within .portal-user-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .pu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 11px;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #334155;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .pu-item i {
            width: 16px;
            text-align: center;
            color: #7B8AA6;
        }

        .pu-item:hover {
            background: #F1F5FB;
            color: #0070B7;
            text-decoration: none;
        }

        .pu-item:hover i {
            color: #0070B7;
        }

        .pu-item.pu-danger {
            color: #DC2626;
        }

        .pu-item.pu-danger i {
            color: #DC2626;
        }

        .pu-item.pu-danger:hover {
            background: #FEE2E2;
            color: #B91C1C;
        }

        .portal-user-menu form {
            margin: 0;
        }

        /* ===== SEARCH ===== */
        .portal-search {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 46px;
            margin: 0 0 22px;
            padding: 0 14px;
            border-radius: 12px;
            border: 1px solid #E3E9F3;
            background: #fff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .portal-search:focus-within {
            border-color: #0070B7;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .10);
        }

        .portal-search > i {
            color: #9AA1AC;
            font-size: 14px;
        }

        .portal-search input {
            flex: 1;
            min-width: 0;
            border: 0;
            outline: 0;
            background: transparent;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            color: #1F2430;
        }

        .portal-search input::placeholder {
            color: #9AA1AC;
            font-weight: 500;
        }

        .ps-clear {
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            border: 0;
            border-radius: 8px;
            background: #F1F2F4;
            color: #6B7280;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ps-clear:hover {
            background: #E5E7EB;
            color: #1F2430;
        }

        .portal-noresult {
            padding: 30px 0;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #8A929E;
        }

        .portal-noresult i {
            margin-right: 6px;
        }

        .is-hidden {
            display: none !important;
        }

        /* ===== BODY ===== */
        .portal-body {
            width: 100%;
            max-width: 1160px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .portal-brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .portal-brand span {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -.03em;
            color: #1F2430;
        }

        .portal-subtitle {
            margin: 8px 0 0;
            font-size: 13.5px;
            color: #8A929E;
        }

        .portal-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            align-items: start;
            gap: 18px;
        }

        @media (max-width: 760px) {
            .portal-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== MODULE CARD ===== */
        .module-card {
            padding: 16px;
            border-radius: 16px;
            border: 1px solid #ECEEF2;
            background: #fff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .mc-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 13px;
            border-bottom: 1px solid #EEF1F5;
        }

        .mc-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #294795, #0070B7);
            color: #fff;
            font-size: 17px;
            box-shadow: 0 8px 18px rgba(0, 112, 183, .22);
        }

        .mc-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -.025em;
            color: #1F2430;
            line-height: 1.15;
        }

        .mc-sub-count {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 600;
            color: #9AA1AC;
        }

        .mc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .mc-tile {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 11px;
            padding: 13px 14px;
            border-radius: 11px;
            border: 1px solid transparent;
            background: #F4F7FB;
            text-decoration: none;
            transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .mc-tile:hover {
            transform: translateY(-2px);
            border-color: #CFE0F1;
            background: #fff;
            box-shadow: 0 10px 22px rgba(41, 71, 149, .10);
            text-decoration: none;
        }

        .mc-tile i {
            font-size: 15px;
            color: #0070B7;
            width: 22px;
            flex: 0 0 22px;
            text-align: center;
        }

        .mc-tile span {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.25;
            color: #1F2430;
        }

        /* ===== INFO BUTTON ===== */
        .portal-info {
            position: fixed;
            right: 22px;
            bottom: 22px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #fff;
            border: 1px solid #ECEEF2;
            color: #9AA1AC;
            font-size: 13px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(16, 24, 40, .06);
            transition: color .16s ease, border-color .16s ease;
        }

        .portal-info:hover {
            color: #1F2430;
            border-color: #D9DDE4;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .portal-head {
                padding: 14px 18px;
            }

            .portal-body {
                padding: 30px 18px 50px;
            }

            .portal-brand span {
                font-size: 24px;
            }

            .portal-brand img {
                width: 30px;
                height: 30px;
            }
        }
    </style>

    @php
        $user = auth()->user();
        $initials = 'U';
        if ($user) {
            $parts = preg_split('/\s+/', trim((string) $user->name));
            $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
            $initials = $initials !== '' ? $initials : 'U';
        }

        $menus = [
            [
                'title' => 'PPIC · Delivery',
                'icon' => 'fa-warehouse',
                'sub' => [
                    ['label' => 'Preparation Monitoring', 'icon' => 'fa-chart-bar', 'route' => 'dashboard.delivery'],
                    ['label' => 'Absen Driver', 'icon' => 'fa-truck', 'route' => 'loadingList.driverAbsen'],
                    ['label' => 'Scan Detail', 'icon' => 'fa-truck-loading', 'route' => 'loadingList.index'],
                    ['label' => 'Kanban Reset', 'icon' => 'fa-sync-alt', 'route' => 'pulling.manual'],
                ],
            ],
            [
                'title' => 'PPIC · Receiving',
                'icon' => 'fa-dolly',
                'sub' => [
                    ['label' => 'Scanning', 'icon' => 'fa-barcode', 'route' => 'pis.index'],
                    ['label' => 'Scan List', 'icon' => 'fa-list-alt', 'route' => 'pis.scanList'],
                ],
            ],
            [
                'title' => 'Production',
                'icon' => 'fa-industry',
                'sub' => [
                    ['label' => 'Production Stock', 'icon' => 'fa-box', 'route' => 'dashboard.productionStock'],
                    ['label' => 'Kanban Check', 'icon' => 'fa-clipboard-list', 'route' => 'dashboard.kbnCheck'],
                    ['label' => 'Part Check', 'icon' => 'fa-cogs', 'route' => 'dashboard.partCheck'],
                    ['label' => 'Production Result', 'icon' => 'fa-chart-line', 'route' => 'dashboard.prodResult'],
                ],
            ],
            [
                'title' => 'Quality',
                'icon' => 'fa-clipboard-check',
                'sub' => [
                    ['label' => 'Scan Quality', 'icon' => 'fa-barcode', 'route' => 'quality.scan'],
                    ['label' => 'Reject & Pareto', 'icon' => 'fa-chart-bar', 'route' => 'quality.pareto'],
                    ['label' => 'Detail Scan', 'icon' => 'fa-list-ul', 'route' => 'quality.history'],
                ],
            ],
        ];
    @endphp

    <div class="portal-wrap">
        <main class="portal-body">
            <div class="portal-topline">
                <div>
                    <div class="portal-brand">
                        <img src="{{ asset('assets/img/bella.png') }}" alt="Bella">
                        <span>BELLA</span>
                    </div>
                    <p class="portal-subtitle">Pilih salah satu modul untuk melanjutkan.</p>
                </div>
                @guest
                    <a href="{{ route('login.index') }}" class="portal-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                @endguest
                @auth
                    <div class="portal-user" tabindex="0">
                        <span class="pu-avatar">{{ $initials }}</span>
                        <span class="pu-name">{{ $user->name }}</span>
                        <i class="fas fa-chevron-down pu-chev"></i>
                        <div class="portal-user-menu">
                            <a href="{{ route('dashboard.overview') }}" class="pu-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <form action="{{ route('logout.auth') }}" method="POST">
                                @csrf
                                <button type="submit" class="pu-item pu-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <div class="portal-search">
                <i class="fas fa-search"></i>
                <input type="text" id="portalSearch" placeholder="Cari menu… (mis. stock, scan, kanban)" autocomplete="off" aria-label="Cari menu">
                <button type="button" class="ps-clear" id="portalSearchClear" aria-label="Bersihkan pencarian" hidden><i class="fas fa-times"></i></button>
            </div>

            <div class="portal-grid">
                @foreach ($menus as $m)
                    <div class="module-card" data-title="{{ $m['title'] }}">
                        <div class="mc-head">
                            <span class="mc-icon"><i class="fas {{ $m['icon'] }}"></i></span>
                            <div>
                                <div class="mc-title">{{ $m['title'] }}</div>
                                <div class="mc-sub-count">{{ count($m['sub']) }} menu</div>
                            </div>
                        </div>
                        <div class="mc-grid">
                            @foreach ($m['sub'] as $s)
                                <a href="{{ auth()->check() ? route($s['route']) : route('login.index') }}" class="mc-tile">
                                    <i class="fas {{ $s['icon'] }}"></i>
                                    <span>{{ $s['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="portal-noresult" id="portalNoResult" hidden>
                <i class="fas fa-search"></i> Tidak ada menu yang cocok.
            </div>
        </main>
    </div>

    @guest
        <a href="{{ route('login.index') }}" class="portal-info" title="Internal use only — login untuk mengakses modul">
            <i class="fas fa-info"></i>
        </a>
    @endguest
@endsection

@section('custom-script')
    <script>
        (function () {
            var input = document.getElementById('portalSearch');
            if (!input) return;

            var clearBtn = document.getElementById('portalSearchClear');
            var noResult = document.getElementById('portalNoResult');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.module-card'));

            function apply() {
                var q = input.value.trim().toLowerCase();
                if (clearBtn) clearBtn.hidden = q === '';
                var anyVisible = false;

                cards.forEach(function (card) {
                    var title = (card.getAttribute('data-title') || '').toLowerCase();
                    var tiles = Array.prototype.slice.call(card.querySelectorAll('.mc-tile'));
                    var shown = 0;

                    tiles.forEach(function (tile) {
                        var label = (tile.textContent || '').trim().toLowerCase();
                        var match = q === '' || label.indexOf(q) !== -1 || title.indexOf(q) !== -1;
                        tile.classList.toggle('is-hidden', !match);
                        if (match) shown++;
                    });

                    card.classList.toggle('is-hidden', shown === 0);
                    if (shown > 0) anyVisible = true;
                });

                if (noResult) noResult.hidden = anyVisible || q === '';
            }

            input.addEventListener('input', apply);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { input.value = ''; apply(); }
            });
            if (clearBtn) clearBtn.addEventListener('click', function () {
                input.value = '';
                input.focus();
                apply();
            });
        })();
    </script>
@endsection
