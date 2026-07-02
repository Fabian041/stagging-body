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

        /* ===== BODY ===== */
        .portal-body {
            width: 100%;
            max-width: 1060px;
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
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* ===== CARD ===== */
        .lesson-card {
            display: flex;
            flex-direction: column;
            min-height: 196px;
            padding: 18px 20px 20px;
            border-radius: 16px;
            border: 1px solid #ECEEF2;
            background: #fff;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .lesson-card:hover {
            transform: translateY(-3px);
            border-color: #D9DDE4;
            box-shadow: 0 12px 28px rgba(16, 24, 40, .09);
            text-decoration: none;
        }

        .lc-top {
            display: flex;
            justify-content: flex-end;
        }

        .lc-badge {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .02em;
            color: #5A6472;
            background: #F1F2F4;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .lc-title {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 10px 4px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -.02em;
            color: #1F2430;
        }

        .lc-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .lc-icon {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #F5F6F8;
            color: #6B7280;
            font-size: 12.5px;
        }

        .lc-go {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #9AA1AC;
            transition: color .16s ease;
        }

        .lesson-card:hover .lc-go {
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
        $menus = [
            ['title' => 'PPIC · Delivery', 'icon' => 'fa-truck-loading', 'tag' => 'PPIC'],
            ['title' => 'PPIC · Receiving', 'icon' => 'fa-dolly', 'tag' => 'PPIC'],
            ['title' => 'Production', 'icon' => 'fa-industry', 'tag' => 'Produksi'],
            ['title' => 'Quality', 'icon' => 'fa-clipboard-check', 'tag' => 'Quality'],
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
                    <p class="portal-subtitle">Pilih salah satu modul untuk melanjutkan ke login.</p>
                </div>
                <a href="{{ route('login.index') }}" class="portal-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>

            <div class="portal-grid">
                @foreach ($menus as $m)
                    <a href="{{ route('login.index') }}" class="lesson-card">
                        <div class="lc-top">
                            <span class="lc-badge">{{ $m['tag'] }}</span>
                        </div>
                        <div class="lc-title">{{ $m['title'] }}</div>
                        <div class="lc-foot">
                            <span class="lc-icon"><i class="fas {{ $m['icon'] }}"></i></span>
                            <span class="lc-go">Masuk <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>
        </main>
    </div>

    <a href="{{ route('login.index') }}" class="portal-info" title="Internal use only — login untuk mengakses modul">
        <i class="fas fa-info"></i>
    </a>
@endsection
