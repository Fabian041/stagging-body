<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>BELLA</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href={{ asset('manifest.json') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/fontawesome/css/all.min.css') }}>
    <link rel="stylesheet"
        href={{ asset('assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/select2/dist/css/select2.min.css') }}>

    <link rel="stylesheet" href={{ asset('assets/modules/jqvmap/dist/jqvmap.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/summernote/summernote-bs4.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/izitoast/css/iziToast.min.css') }}>

    <link rel="stylesheet" href={{ asset('assets/css/style.css') }}>
    <link rel="stylesheet" href={{ asset('assets/css/components.css') }}>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;850&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --bella-navy: #294795;
            --bella-blue: #0070B7;
            --bella-sky: #0097D8;
            --bella-bg: #F0F4F9;
            --bella-card: #FFFFFF;
            --bella-text: #1A2340;
            --bella-muted: #6B7A99;
            --bella-border: #DDE3EF;
            --bella-soft: #E8F4FD;
            --bella-shadow: 0 18px 45px rgba(41, 71, 149, .12);
            --bella-shadow-sm: 0 8px 24px rgba(41, 71, 149, .08);
            --bella-radius: 22px;
        }

        html,
        body {
            min-height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: var(--bella-text);
            background:
                radial-gradient(circle at top left, rgba(0, 151, 216, .18), transparent 34%),
                radial-gradient(circle at 82% 12%, rgba(41, 71, 149, .14), transparent 30%),
                linear-gradient(180deg, #F7FAFF 0%, var(--bella-bg) 54%, #EAF1FA 100%) !important;
        }

        body.sidebar-mini {
            overflow-x: hidden;
        }

        #app,
        .main-wrapper {
            min-height: 100vh;
            background: transparent !important;
        }

        .navbar-bg {
            height: 86px !important;
            background:
                linear-gradient(135deg, rgba(41, 71, 149, .98), rgba(0, 112, 183, .96) 58%, rgba(0, 151, 216, .92)) !important;
            box-shadow: 0 18px 45px rgba(41, 71, 149, .22);
        }

        .navbar-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 16% 10%, rgba(255, 255, 255, .22), transparent 20%),
                radial-gradient(circle at 86% 12%, rgba(255, 255, 255, .18), transparent 22%);
            pointer-events: none;
        }

        .navbar,
        .main-navbar {
            min-height: 72px;
            background: transparent !important;
            box-shadow: none !important;
        }

        .navbar .nav-link,
        .main-navbar .nav-link {
            color: rgba(255, 255, 255, .86) !important;
            font-weight: 700;
        }

        .navbar .nav-link:hover,
        .main-navbar .nav-link:hover {
            color: #FFFFFF !important;
        }

        .navbar .dropdown-menu,
        .main-navbar .dropdown-menu {
            border: 1px solid rgba(221, 227, 239, .9);
            border-radius: 18px;
            box-shadow: var(--bella-shadow-sm);
            overflow: hidden;
        }

        .main-content {
            padding-left: 28px !important;
            padding-right: 28px !important;
            padding-top: 104px !important;
            min-height: calc(100vh - 74px);
        }

        .section {
            max-width: 1180px;
            margin: 0 auto;
        }

        .section-header {
            position: relative;
            align-items: flex-start !important;
            justify-content: space-between !important;
            gap: 18px;
            margin: 0 0 22px 0 !important;
            padding: 26px 28px !important;
            border: 1px solid rgba(255, 255, 255, .74) !important;
            border-radius: 28px !important;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, .94), rgba(255, 255, 255, .78)),
                radial-gradient(circle at 85% 10%, rgba(0, 151, 216, .16), transparent 32%) !important;
            box-shadow: var(--bella-shadow-sm) !important;
            overflow: hidden;
        }

        .section-header::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(0, 151, 216, .12);
        }

        .section-header h1 {
            position: relative;
            z-index: 1;
            margin: 0;
            color: var(--bella-text) !important;
            font-size: 28px !important;
            font-weight: 850 !important;
            line-height: 1.1;
            letter-spacing: -.045em;
        }

        .section-header h1::after {
            content: 'Body Electronic Logistic Application';
            display: block;
            margin-top: 9px;
            color: var(--bella-muted);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0;
        }

        .section-header-breadcrumb {
            position: relative;
            z-index: 1;
            margin-left: auto;
            max-width: 520px;
            padding-top: 3px;
            text-align: right;
            color: var(--bella-muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .section-header-breadcrumb .text-muted {
            color: var(--bella-muted) !important;
        }

        .card {
            border: 1px solid rgba(221, 227, 239, .9) !important;
            border-radius: var(--bella-radius) !important;
            box-shadow: var(--bella-shadow-sm) !important;
            background: rgba(255, 255, 255, .92) !important;
            overflow: hidden;
        }

        .card .card-header {
            border-bottom-color: var(--bella-border) !important;
            background: rgba(248, 250, 255, .82) !important;
        }

        .card .card-body {
            color: var(--bella-text);
        }

        .btn {
            border-radius: 11px !important;
            font-weight: 800 !important;
            letter-spacing: -.01em;
            box-shadow: none !important;
        }

        .btn-primary,
        .btn-info {
            border-color: transparent !important;
            background: linear-gradient(135deg, var(--bella-navy), var(--bella-blue)) !important;
            color: #FFFFFF !important;
        }

        .btn-primary:hover,
        .btn-info:hover {
            filter: brightness(.95);
            transform: translateY(-1px);
        }

        .btn-outline-primary,
        .btn-outline-info {
            border-color: rgba(0, 112, 183, .25) !important;
            color: var(--bella-blue) !important;
            background: rgba(232, 244, 253, .64) !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-info:hover {
            background: var(--bella-blue) !important;
            color: #FFFFFF !important;
        }

        .btn-warning {
            border-color: transparent !important;
            background: linear-gradient(135deg, #D97706, #F59E0B) !important;
            color: #FFFFFF !important;
        }

        .btn-outline-warning {
            border-color: rgba(217, 119, 6, .25) !important;
            color: #B45309 !important;
            background: #FEF3C7 !important;
        }

        .btn-outline-warning:hover {
            background: #D97706 !important;
            color: #FFFFFF !important;
        }

        .btn-danger {
            border-color: transparent !important;
            background: linear-gradient(135deg, #B91C1C, #DC2626) !important;
            color: #FFFFFF !important;
        }

        .btn-outline-danger {
            border-color: rgba(220, 38, 38, .24) !important;
            color: #B91C1C !important;
            background: #FEE2E2 !important;
        }

        .btn-outline-danger:hover {
            background: #DC2626 !important;
            color: #FFFFFF !important;
        }

        .form-control,
        .custom-select,
        .select2-container .select2-selection--single {
            border-color: var(--bella-border) !important;
            border-radius: 12px !important;
            color: var(--bella-text) !important;
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: var(--bella-sky) !important;
            box-shadow: 0 0 0 4px rgba(0, 151, 216, .12) !important;
        }

        .main-footer {
            padding-left: 28px !important;
            padding-right: 28px !important;
            border-top: 0 !important;
            background: transparent !important;
            color: var(--bella-muted);
        }

        .main-footer .footer-left,
        .main-footer .footer-right {
            max-width: 1180px;
            margin: 0 auto;
            font-weight: 700;
            font-size: 12px;
        }

        @media (max-width: 767.98px) {
            .main-content {
                padding-left: 14px !important;
                padding-right: 14px !important;
                padding-top: 94px !important;
            }

            .section-header {
                padding: 22px !important;
                flex-direction: column;
            }

            .section-header h1 {
                font-size: 24px !important;
            }

            .section-header-breadcrumb {
                text-align: left;
                margin-left: 0;
            }

            .main-footer {
                padding-left: 14px !important;
                padding-right: 14px !important;
            }
        }

        /* ================================
   BELLA TOPBAR STYLE
================================ */
        #topbar {
            position: sticky;
            top: 0;
            z-index: 80;
            min-height: 68px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            background: rgba(255, 255, 255, .88);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border-bottom: 1px solid rgba(221, 227, 239, .92);
            box-shadow: 0 10px 32px rgba(41, 71, 149, .08);
        }

        .tb-left,
        .tb-right {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .tb-left {
            flex: 1;
        }

        .tb-right {
            flex-shrink: 0;
        }

        .tb-page-title {
            font-size: 18px;
            font-weight: 850;
            line-height: 1.15;
            letter-spacing: -.04em;
            color: #1A2340;
        }

        .tb-page-sub {
            margin-top: 3px;
            font-size: 11.5px;
            font-weight: 600;
            color: #6B7A99;
        }

        /* Search */
        .tb-search {
            width: 260px;
            height: 40px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 14px;
            border-radius: 14px;
            background: #F0F4F9;
            border: 1px solid #DDE3EF;
            transition: all .18s ease;
        }

        .tb-search:focus-within {
            background: #FFFFFF;
            border-color: rgba(0, 151, 216, .45);
            box-shadow: 0 0 0 4px rgba(0, 151, 216, .10);
        }

        .tb-search i {
            font-size: 13px;
            color: #8A97AD;
        }

        .tb-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #1A2340;
            font-size: 13px;
            font-weight: 600;
        }

        .tb-search input::placeholder {
            color: #9AA7BC;
        }

        /* Icon button */
        .tb-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            border: 1px solid #DDE3EF !important;
            color: #6B7A99;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s ease;
            box-shadow: 0 8px 20px rgba(41, 71, 149, .05);
        }

        .tb-icon-btn i {
            font-size: 14px;
        }

        .tb-icon-btn:hover {
            background: #E8F4FD;
            border-color: rgba(0, 112, 183, .25) !important;
            color: #0070B7;
            transform: translateY(-1px);
        }

        .tb-notif-dot {
            position: absolute;
            top: 9px;
            right: 9px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #DC2626;
            border: 2px solid #FFFFFF;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .12);
        }

        /* Divider */
        .tb-divider {
            width: 1px;
            height: 28px;
            background: #DDE3EF;
            margin: 0 2px;
        }

        /* User pill */
        .tb-user-pill {
            position: relative;
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 5px 12px 5px 5px;
            border-radius: 999px;
            background: #F8FAFF;
            border: 1px solid #DDE3EF;
            cursor: pointer;
            transition: all .18s ease;
            box-shadow: 0 8px 20px rgba(41, 71, 149, .05);
        }

        .tb-user-pill:hover {
            background: #FFFFFF;
            border-color: rgba(0, 112, 183, .30);
            box-shadow: 0 12px 28px rgba(41, 71, 149, .10);
        }

        .tb-user-pill-av {
            width: 32px;
            height: 32px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #294795, #0070B7 65%, #0097D8);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: -.02em;
            box-shadow: 0 8px 18px rgba(0, 112, 183, .24);
        }

        .tb-user-pill-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #1A2340;
            font-size: 13px;
            font-weight: 800;
        }

        .tb-pill-chevron {
            font-size: 10px;
            color: #8A97AD;
            transition: transform .18s ease;
        }

        .tb-user-pill:hover .tb-pill-chevron {
            transform: rotate(180deg);
        }

        /* User dropdown */
        .tb-user-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 230px;
            padding: 8px;
            border-radius: 18px;
            background: #FFFFFF;
            border: 1px solid #DDE3EF;
            box-shadow: 0 22px 55px rgba(26, 35, 64, .16);
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px) scale(.98);
            transform-origin: top right;
            transition: all .18s ease;
            z-index: 120;
        }

        .tb-user-pill:hover .tb-user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .tb-dd-header {
            padding: 12px 12px 10px;
            margin-bottom: 6px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(41, 71, 149, .08), rgba(0, 151, 216, .08));
            border: 1px solid rgba(0, 112, 183, .10);
        }

        .tb-dd-name {
            font-size: 13px;
            font-weight: 850;
            color: #1A2340;
            line-height: 1.25;
        }

        .tb-dd-role {
            margin-top: 3px;
            font-size: 11px;
            font-weight: 650;
            color: #6B7A99;
        }

        .tb-dd-item {
            width: 100%;
            min-height: 38px;
            padding: 9px 11px;
            border: 0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            color: #334155;
            font-size: 13px;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
            transition: all .16s ease;
        }

        .tb-dd-item i {
            width: 16px;
            text-align: center;
            color: #7B8AA6;
        }

        .tb-dd-item:hover {
            background: #E8F4FD;
            color: #0070B7;
        }

        .tb-dd-item:hover i {
            color: #0070B7;
        }

        .tb-dd-divider {
            height: 1px;
            background: #EEF2F7;
            margin: 6px 4px;
        }

        .tb-dd-item.danger {
            color: #DC2626;
        }

        .tb-dd-item.danger i {
            color: #DC2626;
        }

        .tb-dd-item.danger:hover {
            background: #FEE2E2;
            color: #B91C1C;
        }

        /* Form inside dropdown */
        .tb-user-dropdown form {
            margin: 0;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            #topbar {
                min-height: 62px;
                padding: 0 14px;
            }

            .tb-right {
                gap: 8px;
            }

            .tb-icon-btn {
                width: 38px;
                height: 38px;
                border-radius: 13px;
            }

            .tb-user-pill {
                height: 38px;
                padding: 3px;
                border-radius: 999px;
            }

            .tb-user-pill-av {
                width: 30px;
                height: 30px;
                font-size: 10px;
            }

            .tb-user-dropdown {
                right: -4px;
                width: 220px;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="sidebar-mini">
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>

            @include('layouts.partials.nav', ['hideSidebarToggle' => true])

            <div class="main-content">
                <section class="section">
                    @yield('main')
                </section>
            </div>

            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; ITD {{ date('Y') }} · Bella System
                </div>
                <div class="footer-right">
                    Body Electronic Logistic Application
                </div>
            </footer>
        </div>
    </div>

    <script src={{ asset('assets/modules/jquery.min.js') }}></script>
    <script src={{ asset('assets/modules/popper.js') }}></script>
    <script src={{ asset('assets/modules/tooltip.js') }}></script>
    <script src={{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}></script>
    <script src={{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}></script>
    <script src={{ asset('assets/modules/moment.min.js') }}></script>
    <script src={{ asset('assets/js/stisla.js') }}></script>
    <script src={{ asset('assets/modules/datatables/datatables.min.js') }}></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>

    <script src={{ asset('assets/modules/jquery.sparkline.min.js') }}></script>
    <script src={{ asset('assets/modules/chart.min.js') }}></script>
    <script src={{ asset('assets/modules/owlcarousel2/dist/owl.carousel.min.js') }}></script>
    <script src={{ asset('assets/modules/summernote/summernote-bs4.js') }}></script>
    <script src={{ asset('assets/modules/chocolat/dist/js/jquery.chocolat.min.js') }}></script>
    <script src={{ asset('dist/assets/select2/dist/js/select2.full.min.js') }}></script>
    <script src={{ asset('assets/modules/izitoast/js/iziToast.min.js') }}></script>

    <script src={{ asset('assets/js/page/bootstrap-modal.js') }}></script>
    <script src={{ asset('assets/js/page/index.js') }}></script>

    <script src={{ asset('assets/js/scripts.js') }}></script>
    <script src={{ asset('assets/js/custom.js') }}></script>

    @yield('custom-script')
    @stack('scripts')
</body>

</html>
