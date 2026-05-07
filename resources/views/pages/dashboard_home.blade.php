@extends('layouts.root.dashboard')

@section('main')
    <style>
        .bella-dashboard-home {
            --home-navy: #294795;
            --home-blue: #0070B7;
            --home-sky: #0097D8;
            --home-text: #1A2340;
            --home-muted: #6B7A99;
            --home-border: #DDE3EF;
            --home-soft: #E8F4FD;
            --home-radius: 22px;
        }

        .bella-hero {
            position: relative;
            margin-bottom: 22px;
            padding: 28px;
            border-radius: 30px;
            background:
                linear-gradient(135deg, rgba(41, 71, 149, .98), rgba(0, 112, 183, .94) 58%, rgba(0, 151, 216, .88)),
                radial-gradient(circle at 18% 20%, rgba(255, 255, 255, .22), transparent 24%);
            color: #FFFFFF;
            box-shadow: 0 22px 55px rgba(41, 71, 149, .23);
            overflow: hidden;
        }

        .bella-hero::before,
        .bella-hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .bella-hero::before {
            width: 240px;
            height: 240px;
            right: -76px;
            top: -92px;
            background: rgba(255, 255, 255, .13);
        }

        .bella-hero::after {
            width: 160px;
            height: 160px;
            right: 110px;
            bottom: -92px;
            background: rgba(255, 255, 255, .09);
        }

        .bella-hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
        }

        .bella-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding: 6px 12px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .86);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .bella-hero-title {
            max-width: 680px;
            margin: 0;
            color: #FFFFFF;
            font-size: 34px;
            line-height: 1.12;
            font-weight: 850;
            letter-spacing: -.055em;
        }

        .bella-hero-title span {
            color: #9BE2FF;
        }

        .bella-hero-sub {
            max-width: 660px;
            margin-top: 12px;
            color: rgba(255, 255, 255, .72);
            font-size: 14px;
            line-height: 1.75;
            font-weight: 500;
        }

        .bella-hero-logo {
            width: 104px;
            height: 104px;
            flex: 0 0 104px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 28px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .18);
        }

        .bella-hero-logo img {
            width: 68px;
            height: 68px;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, .18));
        }

        .bella-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .bella-stat-card {
            position: relative;
            padding: 18px;
            border: 1px solid rgba(221, 227, 239, .9);
            border-radius: 22px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 10px 28px rgba(41, 71, 149, .08);
            overflow: hidden;
        }

        .bella-stat-card::after {
            content: '';
            position: absolute;
            top: -42px;
            right: -42px;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: rgba(0, 151, 216, .10);
        }

        .bella-stat-label {
            color: var(--home-muted);
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-stat-value {
            margin-top: 8px;
            color: var(--home-text);
            font-size: 26px;
            font-weight: 850;
            letter-spacing: -.045em;
            line-height: 1;
        }

        .bella-stat-sub {
            margin-top: 7px;
            color: var(--home-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .bella-menu-card {
            height: 100%;
            position: relative;
            border: 1px solid rgba(221, 227, 239, .9);
            border-radius: 24px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 12px 32px rgba(41, 71, 149, .09);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            overflow: hidden;
        }

        .bella-menu-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 151, 216, .28);
            box-shadow: 0 18px 44px rgba(41, 71, 149, .14);
        }

        .bella-menu-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 5px;
            background: linear-gradient(90deg, var(--card-accent, var(--home-blue)), var(--home-sky));
        }

        .bella-menu-body {
            padding: 24px;
        }

        .bella-menu-head {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 14px;
        }

        .bella-icon-box {
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            background: var(--icon-bg, var(--home-soft));
            color: var(--icon-color, var(--home-blue));
            font-size: 20px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .76);
        }

        .bella-menu-title {
            margin: 0;
            color: var(--home-text);
            font-size: 18px;
            line-height: 1.25;
            font-weight: 850;
            letter-spacing: -.035em;
        }

        .bella-menu-desc {
            min-height: 46px;
            margin: 7px 0 0 0;
            color: var(--home-muted);
            font-size: 13px;
            line-height: 1.7;
            font-weight: 500;
        }

        .bella-action-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
            margin-top: 20px;
        }

        .bella-action-list.single {
            grid-template-columns: 1fr;
        }

        .bella-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            padding: 9px 12px;
            border-radius: 13px;
            font-size: 12px;
            line-height: 1.2;
            font-weight: 850;
            text-align: center;
            transition: transform .16s ease, filter .16s ease, background .16s ease;
        }

        .bella-action:hover {
            text-decoration: none;
            transform: translateY(-1px);
        }

        .bella-action-primary {
            border: 0;
            background: linear-gradient(135deg, var(--home-navy), var(--home-blue));
            color: #FFFFFF !important;
            box-shadow: 0 12px 22px rgba(0, 112, 183, .16);
        }

        .bella-action-outline {
            border: 1px solid rgba(0, 112, 183, .16);
            background: rgba(232, 244, 253, .72);
            color: var(--home-blue) !important;
        }

        .bella-section-note {
            margin: 4px 0 16px;
            color: var(--home-muted);
            font-size: 13px;
            font-weight: 600;
        }

        .accent-primary {
            --card-accent: #294795;
            --icon-bg: #E8F4FD;
            --icon-color: #0070B7;
        }

        .accent-warning {
            --card-accent: #D97706;
            --icon-bg: #FEF3C7;
            --icon-color: #B45309;
        }

        .accent-info {
            --card-accent: #0097D8;
            --icon-bg: #E0F2FE;
            --icon-color: #0284C7;
        }

        .accent-danger {
            --card-accent: #DC2626;
            --icon-bg: #FEE2E2;
            --icon-color: #B91C1C;
        }

        @media (max-width: 991.98px) {
            .bella-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .bella-hero-content {
                align-items: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .bella-hero {
                padding: 24px;
            }

            .bella-hero-content {
                flex-direction: column;
            }

            .bella-hero-title {
                font-size: 28px;
            }

            .bella-hero-logo {
                width: 82px;
                height: 82px;
                flex-basis: 82px;
            }

            .bella-hero-logo img {
                width: 54px;
                height: 54px;
            }

            .bella-action-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .bella-stats {
                grid-template-columns: 1fr;
            }
        }

        /* ===== FORCE DASHBOARD HOME FULL WIDTH & TIGHT TOP GAP ===== */

        /* Lawan padding/margin dari layout root dashboard */
        body .main-content,
        body .section,
        body .section-body,
        body .content,
        body .page-content {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        body .main-content {
            padding-top: 10px !important;
        }

        body .section {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        body .section .section-header,
        body .section-header {
            margin-bottom: 8px !important;
        }

        /* Kalau layout pakai container bootstrap */
        body .container,
        body .container-fluid {
            width: 100% !important;
            max-width: none !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        /* Wrapper halaman home */
        .bella-dashboard-home {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Jarak navbar ke hero diperkecil */
        .bella-hero {
            margin-top: 0 !important;
            margin-bottom: 14px !important;
            padding: 24px 26px !important;
        }

        /* Note di bawah hero jangan terlalu jauh */
        .bella-section-note {
            margin: 0 0 12px !important;
        }

        /* Row bootstrap default terlalu lebar/longgar */
        .bella-dashboard-home .row {
            margin-left: -8px !important;
            margin-right: -8px !important;
        }

        .bella-dashboard-home [class*="col-"] {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        /* Card spacing */
        .bella-dashboard-home .mb-4 {
            margin-bottom: 16px !important;
        }

        /* Menu card sedikit lebih compact */
        .bella-menu-body {
            padding: 20px !important;
        }

        @media (max-width: 767.98px) {

            body .main-content,
            body .section,
            body .section-body,
            body .content,
            body .page-content,
            body .container,
            body .container-fluid {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }

            .bella-hero {
                padding: 20px !important;
                margin-bottom: 12px !important;
                border-radius: 24px !important;
            }
        }
    </style>

    <div class="bella-dashboard-home">
        <div class="bella-hero">
            <div class="bella-hero-content">
                <div>
                    <div class="bella-hero-kicker">
                        <i class="fas fa-shield-alt"></i>
                        BELLA SYSTEM
                    </div>
                    <h2 class="bella-hero-title">
                        Body Electronic Logistic <span>Application</span>
                    </h2>
                    <div class="bella-hero-sub">
                        Satu workspace untuk mengakses aktivitas PPIC, Production, dan Evaluation
                        dengan tampilan yang lebih rapi, cepat, dan konsisten.
                    </div>
                </div>

                <div class="bella-hero-logo">
                    <img src="{{ asset('assets/img/bella.png') }}" alt="Bella">
                </div>
            </div>
        </div>

        <div class="bella-section-note">
            Pilih modul sesuai kebutuhan pekerjaan anda.
        </div>

        <div class="row">
            @php
                $menus = [
                    [
                        'title' => 'PPIC - Delivery',
                        'icon' => 'fa-warehouse',
                        'color' => 'primary',
                        'desc' => 'Preparation Monitoring, Preparation Detail, dan Kanban Reset.',
                        'buttons' => [
                            [
                                'route' => 'dashboard.delivery',
                                'label' => 'Preparation Monitoring',
                                'icon' => 'fa-truck-loading',
                                'type' => 'solid',
                            ],
                            [
                                'route' => 'loadingList.index',
                                'label' => 'Preparation Detail',
                                'icon' => 'fa-list',
                                'type' => 'outline',
                            ],
                            [
                                'route' => 'pulling.manual',
                                'label' => 'Kanban Reset',
                                'icon' => 'fa-sync-alt',
                                'type' => 'outline',
                            ],
                        ],
                    ],
                    [
                        'title' => 'PPIC - Packing',
                        'icon' => 'fa-qrcode',
                        'color' => 'warning',
                        'desc' => 'Scanning, Scan List, dan Master Data PIS.',
                        'buttons' => [
                            ['route' => 'pis.index', 'label' => 'Scanning', 'icon' => 'fa-barcode', 'type' => 'solid'],
                            [
                                'route' => 'pis.scanList',
                                'label' => 'Scan List',
                                'icon' => 'fa-list-alt',
                                'type' => 'outline',
                            ],
                            [
                                'route' => 'pis.master',
                                'label' => 'Master Data',
                                'icon' => 'fa-database',
                                'type' => 'outline',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Production',
                        'icon' => 'fa-industry',
                        'color' => 'info',
                        'desc' => 'Production Stock, Check Kanban, dan Production Result.',
                        'buttons' => [
                            [
                                'route' => 'dashboard.productionStock',
                                'label' => 'Production Stock',
                                'icon' => 'fa-box',
                                'type' => 'solid',
                            ],
                            [
                                'route' => 'dashboard.kbnCheck',
                                'label' => 'Check Kanban',
                                'icon' => 'fa-clipboard-list',
                                'type' => 'outline',
                            ],
                            [
                                'route' => 'dashboard.prodResult',
                                'label' => 'Production Result',
                                'icon' => 'fa-chart-line',
                                'type' => 'outline',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Evaluation',
                        'icon' => 'fa-exclamation-triangle',
                        'color' => 'danger',
                        'desc' => 'Melihat log error dari aplikasi.',
                        'buttons' => [
                            [
                                'route' => 'error.log',
                                'label' => 'Error Log',
                                'icon' => 'fa-exclamation-circle',
                                'type' => 'solid',
                            ],
                        ],
                    ],
                ];
            @endphp

            @foreach ($menus as $menu)
                <div class="col-12 col-lg-6 mb-4">
                    <div class="bella-menu-card accent-{{ $menu['color'] }}">
                        <div class="bella-menu-body">
                            <div class="bella-menu-head">
                                <div class="bella-icon-box">
                                    <i class="fas {{ $menu['icon'] }}"></i>
                                </div>
                                <div>
                                    <h5 class="bella-menu-title">{{ $menu['title'] }}</h5>
                                    <p class="bella-menu-desc">{{ $menu['desc'] }}</p>
                                </div>
                            </div>

                            <div class="bella-action-list {{ count($menu['buttons']) === 1 ? 'single' : '' }}">
                                @foreach ($menu['buttons'] as $btn)
                                    <a href="{{ route($btn['route']) }}"
                                        class="bella-action {{ $btn['type'] === 'solid' ? 'bella-action-primary' : 'bella-action-outline' }}">
                                        <i class="fas {{ $btn['icon'] }}"></i>
                                        <span>{{ $btn['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
