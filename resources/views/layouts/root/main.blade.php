<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>BELLA</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/bella-favicon.svg') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="manifest" href={{ asset('manifest.json') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}>
    <link rel="stylesheet" href={{ asset('assets/modules/fontawesome/css/all.min.css') }}>
    @unless (request()->routeIs('dashboard.overview'))
    <link rel="stylesheet"
        href={{ asset('assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}>
    @unless (request()->routeIs('dashboard.productionStock'))
    <link rel="stylesheet" href={{ asset('assets/modules/select2/dist/css/select2.min.css') }}>
    @endunless
    @endunless
    <link rel="stylesheet" href={{ asset('assets/modules/izitoast/css/iziToast.min.css') }}>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --navy: #294795;
            --blue: #0070B7;
            --sky: #0097D8;
            --bg: #F0F4F9;
            --sidebar: #FFFFFF;
            --sidebar-w: 232px;
            --card: #FFFFFF;
            --text: #1A2340;
            --text-muted: #6B7A99;
            --border: #DDE3EF;
            --primary: #0070B7;
            --primary-light: #E8F4FD;
            --accent: #0097D8;
            --success: #16A34A;
            --success-light: #DCFCE7;
            --warning: #D97706;
            --warning-light: #FEF3C7;
            --danger: #DC2626;
            --danger-light: #FEE2E2;
            --shadow: 0 1px 8px rgba(41, 71, 149, .08);
            --shadow-md: 0 4px 20px rgba(41, 71, 149, .12);
            --r: 8px;
            --r-sm: 5px;
            --topbar-h: 58px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 13.5px;
            line-height: 1.55;
            overflow-x: hidden;
        }

        /* ===== LAYOUT ===== */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        #sidebar-wrapper {
            width: var(--sidebar-w);
            background: var(--sidebar);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            transition: width .25s cubic-bezier(.4, 0, .2, 1), transform .25s cubic-bezier(.4, 0, .2, 1);
            border-right: 1px solid #eef1f6;
            box-shadow: 2px 0 16px rgba(41, 71, 149, .05);
            overflow: visible;
        }

        #sidebar-wrapper.collapsed {
            width: 68px;
        }

        /* Hide labels when collapsed */
        #sidebar-wrapper.collapsed .sb-logo-text,
        #sidebar-wrapper.collapsed .sb-logo-sub,
        #sidebar-wrapper.collapsed .sb-user-name,
        #sidebar-wrapper.collapsed .sb-user-role,
        #sidebar-wrapper.collapsed .sb-user-badge,
        #sidebar-wrapper.collapsed .sb-item-label,
        #sidebar-wrapper.collapsed .sb-count,
        #sidebar-wrapper.collapsed .sb-section-label,
        #sidebar-wrapper.collapsed .sb-submenu,
        #sidebar-wrapper.collapsed .sb-chevron {
            display: none !important;
        }

        #sidebar-wrapper.collapsed .sb-item {
            justify-content: center;
            width: 44px;
            height: 40px;
            margin: 4px auto;
            padding: 0;
            border-radius: 12px;
        }

        #sidebar-wrapper.collapsed .sb-item i.sb-icon {
            font-size: 16px;
            width: auto;
            opacity: .7;
        }

        #sidebar-wrapper.collapsed .sb-item:hover i.sb-icon,
        #sidebar-wrapper.collapsed .sb-item.active i.sb-icon,
        #sidebar-wrapper.collapsed .sb-item.open i.sb-icon {
            opacity: 1;
        }

        #sidebar-wrapper.collapsed .sb-item.active::before {
            display: none;
        }

        #sidebar-wrapper.collapsed .sb-item[data-has-sub] .sb-item-tooltip {
            display: none !important;
        }

        /* Flyout submenu saat collapsed — dipasang via JS sebagai position:fixed (lolos dari overflow .sb-nav) */
        #sidebar-wrapper.collapsed .sb-submenu.sb-flyout {
            display: block !important;
            position: fixed !important;
            width: auto;
            min-width: 196px;
            max-width: 240px;
            padding: 6px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 14px 34px rgba(41, 71, 149, .18);
            z-index: 3000;
        }

        #sidebar-wrapper.collapsed .sb-submenu.sb-flyout .sb-sub-item {
            padding: 8px 11px;
            border-radius: 8px;
            font-size: 12.5px;
        }

        #sidebar-wrapper.collapsed .sb-logo {
            justify-content: center;
            padding: 18px 0 14px;
        }

        #sidebar-wrapper.collapsed .sb-user-wrap {
            padding: 8px;
        }

        #sidebar-wrapper.collapsed .sb-user {
            padding: 7px;
            justify-content: center;
        }

        /* Logo */
        .sb-logo {
            padding: 18px 16px 14px;
            display: flex;
            align-items: center;
            gap: 9px;
            border-bottom: 1px solid #E8ECF4;
            flex-shrink: 0;
            position: relative;
        }

        .sb-logo-icon {
            width: 32px;
            height: 32px;
            background: var(--sky);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sb-logo-icon i {
            color: #fff;
            font-size: 15px;
        }

        .sb-logo-text {
            font-weight: 800;
            font-size: 15px;
            color: var(--navy);
            letter-spacing: -.01em;
            white-space: nowrap;
        }

        .sb-logo-sub {
            font-size: 10px;
            color: var(--text-muted);
            display: block;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        /* Collapse toggle */
        .sb-toggle {
            position: absolute;
            top: 16px;
            right: -14px;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #fff;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 101;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .10);
            transition: .2s;
        }

        .sb-toggle:hover {
            background: var(--bg);
            border-color: var(--primary);
        }

        .sb-toggle i {
            font-size: 10px;
            color: var(--text-muted);
            transition: transform .25s;
        }

        #sidebar-wrapper.collapsed .sb-toggle i {
            transform: rotate(180deg);
        }

        /* User card */
        .sb-user-wrap {
            padding: 10px 10px 6px;
        }

        .sb-user {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 10px;
            background: var(--bg);
            border-radius: var(--r);
            border: 1px solid var(--border);
            cursor: default;
            transition: .2s;
        }

        .sb-user:hover {
            background: #E8F0FB;
            border-color: #c7d2fe;
        }

        .sb-avatar {
            width: 34px;
            height: 34px;
            border-radius: 7px;
            background: linear-gradient(135deg, var(--navy), var(--sky));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(41, 71, 149, .25);
        }

        .sb-user-name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
            white-space: nowrap;
        }

        .sb-user-role {
            font-size: 10px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .sb-user-badge {
            margin-left: auto;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 800;
            background: var(--sky);
            color: #fff;
            letter-spacing: .03em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* Nav */
        .sb-nav {
            padding: 0 10px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sb-nav::-webkit-scrollbar {
            width: 0;
        }

        .sb-section-label {
            padding: 14px 12px 6px;
            font-size: 9.5px;
            font-weight: 600;
            color: #9aa5b8;
            text-transform: uppercase;
            letter-spacing: .12em;
            white-space: nowrap;
        }

        .sb-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 12px;
            border-radius: 11px;
            cursor: pointer;
            font-size: 12.5px;
            font-weight: 500;
            color: #5b6b85;
            transition: background .15s, color .15s;
            margin-bottom: 3px;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
            text-decoration: none;
        }

        .sb-item:hover {
            background: #f4f7fb;
            color: #1A2340;
            text-decoration: none;
        }

        .sb-item.active {
            background: #EAF4FD;
            color: #0b6aa3;
            font-weight: 600;
        }

        .sb-item.active::before {
            display: none;
        }

        .sb-item i.sb-icon {
            width: 16px;
            font-size: 14px;
            flex-shrink: 0;
            opacity: .55;
            text-align: center;
        }

        .sb-item.active i.sb-icon {
            opacity: 1;
            color: #0097D8;
        }

        .sb-item.open i.sb-icon {
            opacity: 1;
        }

        .sb-count {
            margin-left: auto;
            background: var(--sky);
            color: #fff;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 10px;
            font-weight: 700;
        }

        .sb-chevron {
            margin-left: auto;
            font-size: 9px;
            color: var(--text-muted);
            transition: transform .2s;
        }

        .sb-item.open .sb-chevron {
            transform: rotate(90deg);
        }

        /* Submenu */
        .sb-submenu {
            display: none;
            padding: 2px 0 6px 14px;
        }

        .sb-item.open+.sb-submenu {
            display: block;
        }

        .sb-sub-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 11px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 400;
            color: #6b7894;
            text-decoration: none;
            transition: background .12s, color .12s;
            margin-bottom: 2px;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }

        .sb-logo {
            position: relative;
            overflow: visible;
            /* wajib ini */
        }

        .sb-toggle {
            position: absolute;
            right: -14px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 999;
        }

        .sb-sub-item:hover {
            background: #f4f7fb;
            color: #1A2340;
            text-decoration: none;
        }

        .sb-sub-item.active {
            color: #0b6aa3;
            font-weight: 600;
            background: #EAF4FD;
        }

        .sb-sub-item.active::before {
            display: none;
        }

        .sb-sub-item i {
            font-size: 11px;
            width: 13px;
            text-align: center;
            flex-shrink: 0;
        }

        /* Tooltip (collapsed mode) */
        .sb-item-tooltip {
            display: none;
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: #1A2340;
            color: #fff;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            z-index: 200;
            pointer-events: none;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .25);
        }

        .sb-item-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #1A2340;
        }

        #sidebar-wrapper.collapsed .sb-item:hover .sb-item-tooltip {
            display: block;
        }

        /* Bottom */
        .sb-bottom {
            padding: 10px 8px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        /* ===== TOPBAR (glassmorphism) ===== */
        #topbar {
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--shadow);
        }

        .tb-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .tb-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tb-search {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            padding: 7px 12px;
            min-width: 210px;
        }

        .tb-search i {
            font-size: 12px;
            color: var(--text-muted);
            flex-shrink: 0;
        }

        .tb-search input {
            border: 0;
            background: transparent;
            font: inherit;
            font-size: 12.5px;
            color: var(--text);
            outline: none;
            width: 100%;
        }

        .tb-search input::placeholder {
            color: var(--text-muted);
        }

        .tb-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: var(--r-sm);
            border: 1px solid var(--border);
            background: var(--card);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: .15s;
            color: var(--text-muted);
            text-decoration: none;
        }

        .tb-icon-btn:hover {
            background: var(--bg);
            color: var(--text);
        }

        .tb-icon-btn i {
            font-size: 14px;
        }

        .tb-notif-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #ef4444;
            position: absolute;
            top: 5px;
            right: 5px;
            border: 1.5px solid var(--card);
        }

        .tb-divider {
            width: 1px;
            height: 22px;
            background: var(--border);
        }

        .tb-user-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            padding: 5px 10px 5px 5px;
            cursor: pointer;
            position: relative;
            transition: .15s;
        }

        .tb-user-pill:hover {
            background: #E8F0FB;
            border-color: #c7d2fe;
        }

        .tb-user-pill-av {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #fff;
        }

        .tb-user-pill-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
        }

        .tb-pill-chevron {
            font-size: 9px;
            color: var(--text-muted);
        }

        /* User dropdown */
        .tb-user-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 200px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--shadow-md);
            z-index: 200;
            padding: 6px;
        }

        .tb-user-pill.open .tb-user-dropdown {
            display: block;
        }

        .tb-dd-header {
            padding: 8px 10px 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 4px;
        }

        .tb-dd-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .tb-dd-role {
            font-size: 11px;
            color: var(--text-muted);
        }

        .tb-dd-divider {
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        .tb-dd-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: var(--r-sm);
            font-size: 12.5px;
            font-weight: 500;
            color: var(--text);
            text-decoration: none;
            transition: .12s;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
        }

        .tb-dd-item:hover {
            background: var(--bg);
            color: var(--text);
            text-decoration: none;
        }

        .tb-dd-item.danger {
            color: var(--danger);
        }

        .tb-dd-item.danger:hover {
            background: var(--danger-light);
        }

        .tb-dd-item i {
            font-size: 13px;
            width: 15px;
            color: var(--text-muted);
        }

        .tb-dd-item.danger i {
            color: var(--danger);
        }

        /* ===== MAIN CONTENT ===== */
        #main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left .25s cubic-bezier(.4, 0, .2, 1);
            overflow-x: hidden;
            min-width: 0;
            background:
                radial-gradient(420px 150px at 16px 42px, rgba(0, 151, 216, .055), transparent 72%),
                radial-gradient(520px 160px at 84% 36px, rgba(124, 58, 237, .04), transparent 74%),
                linear-gradient(180deg, rgba(255, 255, 255, .18) 0%, rgba(240, 244, 249, 0) 150px),
                var(--bg);
        }

        #main-wrapper.sidebar-collapsed {
            margin-left: 68px;
        }

        #main-content {
            padding: 20px 24px;
            flex: 1;
            overflow-x: hidden;
            min-width: 0;
        }

        /* ===== FOOTER ===== */
        #main-footer {
            padding: 14px 24px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--card);
        }

        /* ===== MOBILE ===== */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 35, 64, .35);
            z-index: 99;
        }

        @media (max-width: 768px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
                width: var(--sidebar-w) !important;
            }

            body.mobile-open #sidebar-wrapper {
                transform: translateX(0);
            }

            body.mobile-open #sidebar-overlay {
                display: block;
            }

            #main-wrapper {
                margin-left: 0 !important;
            }
        }

        /* ===== REUSABLE COMPONENTS ===== */
        .bella-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .bella-btn {
            border: 0;
            border-radius: var(--r-sm);
            padding: 8px 14px;
            cursor: pointer;
            font: inherit;
            font-weight: 600;
            font-size: 12.5px;
            transition: .15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .bella-btn:hover {
            filter: brightness(.93);
        }

        .bella-btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 112, 183, .25);
        }

        .bella-btn-secondary {
            background: var(--card);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .bella-btn-danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .bella-btn-success {
            background: var(--success-light);
            color: var(--success);
        }

        .bella-btn-sm {
            padding: 5px 10px;
            font-size: 11.5px;
            border-radius: 4px;
        }

        .bella-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 600;
            border: 1px solid;
        }

        .bella-badge-blue {
            background: #E8F4FD;
            color: #0055a5;
            border-color: #bfdbfe;
        }

        .bella-badge-green {
            background: var(--success-light);
            color: #15803d;
            border-color: #bbf7d0;
        }

        .bella-badge-amber {
            background: var(--warning-light);
            color: #92400e;
            border-color: #fde68a;
        }

        .bella-badge-red {
            background: var(--danger-light);
            color: #991b1b;
            border-color: #fecaca;
        }

        .bella-badge-gray {
            background: #F1F5F9;
            color: #475569;
            border-color: #cbd5e1;
        }

        .bella-badge-navy {
            background: #EEF2FF;
            color: #294795;
            border-color: #c7d2fe;
        }

        table.bella-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        table.bella-table th,
        table.bella-table td {
            text-align: left;
            padding: 9px 11px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        table.bella-table th {
            color: var(--text-muted);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            background: var(--bg);
            white-space: nowrap;
        }

        table.bella-table tr:last-child td {
            border-bottom: 0;
        }

        table.bella-table tr:hover td {
            background: var(--bg);
            transition: .1s;
        }

        .act-btn {
            border: 0;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 4px;
            padding: 4px 9px;
            cursor: pointer;
            font: inherit;
            font-size: 11px;
            font-weight: 700;
            transition: .15s;
        }

        .act-btn:hover {
            background: var(--primary);
            color: #fff;
        }

        .act-btn.danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .act-btn.danger:hover {
            background: var(--danger);
            color: #fff;
        }

        .act-btn.success {
            background: var(--success-light);
            color: var(--success);
        }

        .act-btn.success:hover {
            background: var(--success);
            color: #fff;
        }

        /* ================================
   BELLA TOPBAR STYLE
================================ */
        #topbar {
            position: sticky;
            top: 0;
            z-index: 80;
            height: auto;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: transparent;
            border-bottom: none;
            box-shadow: none;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            pointer-events: none;
            overflow: visible;
        }

        #topbar > * {
            pointer-events: auto;
        }

        /* aurora tipis di belakang pod biar efek kaca (frost) kelihatan walau konten terang */
        #topbar::before {
            content: '';
            position: absolute;
            inset: -18px 0 -72px;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(340px 110px at 18% 34%, rgba(0, 151, 216, .095), transparent 74%),
                radial-gradient(420px 120px at 82% 30%, rgba(124, 58, 237, .06), transparent 76%);
            -webkit-mask-image: linear-gradient(180deg, transparent 0%, #000 18%, #000 58%, transparent 100%);
            mask-image: linear-gradient(180deg, transparent 0%, #000 18%, #000 58%, transparent 100%);
        }

        .tb-left,
        .tb-right {
            display: flex;
            align-items: center;
            min-width: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, .45);
            -webkit-backdrop-filter: blur(20px) saturate(185%);
            backdrop-filter: blur(20px) saturate(185%);
            border: 1px solid rgba(255, 255, 255, .85);
            box-shadow: 0 8px 28px rgba(41, 71, 149, .12), inset 0 1px 0 rgba(255, 255, 255, .55);
        }

        .tb-left {
            flex: 0 1 auto;
            gap: 10px;
            padding: 7px 16px 7px 9px;
        }

        .tb-right {
            flex-shrink: 0;
            gap: 5px;
            padding: 6px 7px;
        }

        .tb-crumb {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            white-space: nowrap;
            min-width: 0;
        }

        .tb-crumb-home {
            color: #8a97ad;
            font-size: 12px;
        }

        .tb-crumb-sep {
            color: #c2cad8;
            font-size: 8px;
        }

        .tb-crumb-parent {
            color: #7a86a0;
            font-weight: 500;
        }

        .tb-crumb-cur {
            color: #1A2340;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tb-page-sub {
            margin-top: 3px;
            font-size: 11.5px;
            font-weight: 600;
            color: #6B7A99;
        }

        /* Search */
        .tb-search {
            width: 230px;
            height: 36px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .5);
            border: 1px solid rgba(15, 23, 42, .07);
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

        /* di dalam pod kanan: ikon dibuat flat (pod-nya yang jadi permukaan kaca) */
        .tb-right .tb-icon-btn {
            width: 36px;
            height: 36px;
            background: transparent;
            border: 1px solid transparent !important;
            box-shadow: none;
        }

        .tb-right .tb-icon-btn:hover {
            background: rgba(0, 112, 183, .10);
            border-color: transparent !important;
            color: #0070B7;
            transform: none;
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
            height: 22px;
            background: rgba(15, 23, 42, .1);
            margin: 0 3px;
        }

        /* User pill */
        .tb-user-pill {
            position: relative;
            height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 10px 4px 4px;
            border-radius: 999px;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all .18s ease;
        }

        .tb-user-pill:hover {
            background: rgba(0, 112, 183, .08);
            border-color: transparent;
        }

        .tb-user-pill-av {
            width: 28px;
            height: 28px;
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
                padding: 10px 12px;
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
</head>

<body>
    <div class="app-wrapper">

        {{-- SIDEBAR --}}
        @include('layouts.partials.sidenav')

        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" onclick="document.body.classList.remove('mobile-open')"></div>

        {{-- MAIN WRAPPER --}}
        <div id="main-wrapper">

            {{-- TOPBAR --}}
            @include('layouts.partials.nav')

            {{-- CONTENT --}}
            <div id="main-content">
                @yield('main')
            </div>

            {{-- FOOTER --}}
            <footer id="main-footer">
                <span>Copyright &copy; ITD 2023</span>
                <span></span>
            </footer>

        </div>
    </div>

    <!-- General JS Scripts -->
    <script src={{ asset('assets/modules/jquery.min.js') }}></script>
    <script src={{ asset('assets/modules/popper.js') }}></script>
    <script src={{ asset('assets/modules/tooltip.js') }}></script>
    <script src={{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}></script>
    <script src={{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}></script>
    <script src={{ asset('assets/modules/moment.min.js') }}></script>
    <script src={{ asset('assets/js/stisla.js') }}></script>
    @unless (request()->routeIs('dashboard.overview'))
    @if (request()->routeIs('loadingList.index', 'loadingList.detail', 'dashboard.productionStock'))
    <script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/jquery.dataTables.min.js') }}></script>
    <script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}></script>
    @else
    <script src={{ asset('assets/modules/datatables/datatables.min.js') }}></script>
    @endif
    @unless (request()->routeIs('loadingList.index', 'loadingList.detail', 'dashboard.productionStock'))
    <script src={{ asset('assets/modules/jquery.sparkline.min.js') }}></script>
    <script src={{ asset('assets/modules/chart.min.js') }}></script>
    <script src={{ asset('assets/modules/owlcarousel2/dist/owl.carousel.min.js') }}></script>
    <script src={{ asset('assets/modules/summernote/summernote-bs4.js') }}></script>
    <script src={{ asset('assets/modules/chocolat/dist/js/jquery.chocolat.min.js') }}></script>
    @endunless
    @unless (request()->routeIs('loadingList.detail', 'dashboard.productionStock'))
    <script src={{ asset('dist/assets/select2/dist/js/select2.full.min.js') }}></script>
    @endunless
    @unless (request()->routeIs('loadingList.index', 'loadingList.detail', 'dashboard.productionStock'))
    <script src={{ asset('assets/js/page/bootstrap-modal.js') }}></script>
    <script src={{ asset('assets/js/page/index.js') }}></script>
    @endunless
    @endunless
    <script src={{ asset('assets/modules/izitoast/js/iziToast.min.js') }}></script>
    <script src={{ asset('assets/js/scripts.js') }}></script>
    <script src={{ asset('assets/js/custom.js') }}></script>

    <script>
        (function() {
            var sidebar = document.getElementById('sidebar-wrapper');
            var mainWrap = document.getElementById('main-wrapper');
            var toggleBtn = document.getElementById('sb-toggle-btn');

            /* ---- Collapse toggle ---- */
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    var c = sidebar.classList.toggle('collapsed');
                    mainWrap.classList.toggle('sidebar-collapsed', c);
                    try {
                        localStorage.setItem('bella_sb_collapsed', c ? '1' : '0');
                    } catch (e) {}
                });
            }
            try {
                if (localStorage.getItem('bella_sb_collapsed') === '1') {
                    sidebar.classList.add('collapsed');
                    mainWrap.classList.add('sidebar-collapsed');
                }
            } catch (e) {}

            /* ---- Submenu toggle ---- */
            document.querySelectorAll('.sb-item[data-has-sub]').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    var wasOpen = item.classList.contains('open');
                    document.querySelectorAll('.sb-item.open').forEach(function(o) {
                        o.classList.remove('open');
                    });
                    if (!wasOpen) item.classList.add('open');
                });
            });

            /* ---- Flyout submenu saat sidebar collapsed (hover ikon → submenu mengambang & bisa diklik) ---- */
            (function() {
                var hideTimer = null;
                document.querySelectorAll('.sb-item[data-has-sub]').forEach(function(item) {
                    var sub = item.nextElementSibling;
                    if (!sub || !sub.classList.contains('sb-submenu')) return;

                    function show() {
                        if (!sidebar.classList.contains('collapsed')) return;
                        clearTimeout(hideTimer);
                        // tutup flyout lain
                        document.querySelectorAll('.sb-submenu.sb-flyout').forEach(function(f) {
                            if (f !== sub) {
                                f.classList.remove('sb-flyout');
                                f.removeAttribute('style');
                            }
                        });
                        var r = item.getBoundingClientRect();
                        sub.classList.add('sb-flyout');
                        sub.style.left = (r.right + 8) + 'px';
                        var sh = sub.offsetHeight;
                        var top = r.top - 6;
                        if (top + sh > window.innerHeight - 8) top = Math.max(8, window.innerHeight - sh - 8);
                        sub.style.top = top + 'px';
                    }

                    function hide() {
                        hideTimer = setTimeout(function() {
                            sub.classList.remove('sb-flyout');
                            sub.removeAttribute('style');
                        }, 140);
                    }
                    item.addEventListener('mouseenter', show);
                    item.addEventListener('mouseleave', hide);
                    sub.addEventListener('mouseenter', function() { clearTimeout(hideTimer); });
                    sub.addEventListener('mouseleave', hide);
                });
            })();

            /* Auto-open active parent */
            document.querySelectorAll('.sb-sub-item.active').forEach(function(sub) {
                var subMenu = sub.closest('.sb-submenu');
                if (subMenu) {
                    var trigger = subMenu.previousElementSibling;
                    if (trigger) trigger.classList.add('open');
                }
            });

            /* Breadcrumb pod topbar dari menu aktif */
            (function() {
                var box = document.getElementById('tbCrumb');
                if (!box) return;
                var clean = function(s) { return (s || '').replace(/\s+/g, ' ').trim(); };
                var crumbs = [];
                var sub = document.querySelector('#sidebar-wrapper .sb-sub-item.active');
                if (sub) {
                    var menu = sub.closest('.sb-submenu');
                    var parent = menu ? menu.previousElementSibling : null;
                    var plabel = parent ? parent.querySelector('.sb-item-label') : null;
                    if (plabel) crumbs.push(clean(plabel.textContent));
                    crumbs.push(clean(sub.textContent));
                } else {
                    var item = document.querySelector('#sidebar-wrapper .sb-item.active');
                    var ilabel = item ? item.querySelector('.sb-item-label') : null;
                    crumbs.push(ilabel ? clean(ilabel.textContent) : 'Dashboard');
                }
                var html = '<i class="fas fa-home tb-crumb-home"></i>';
                crumbs.forEach(function(c, i) {
                    var last = i === crumbs.length - 1;
                    html += '<i class="fas fa-chevron-right tb-crumb-sep"></i>';
                    html += '<span class="' + (last ? 'tb-crumb-cur' : 'tb-crumb-parent') + '">' +
                        c.replace(/[<>&]/g, '') + '</span>';
                });
                box.innerHTML = html;
            })();

            /* ---- Topbar user dropdown ---- */
            var pill = document.querySelector('.tb-user-pill');
            if (pill) {
                pill.addEventListener('click', function(e) {
                    e.stopPropagation();
                    pill.classList.toggle('open');
                });
                document.addEventListener('click', function() {
                    pill.classList.remove('open');
                });
            }

            /* ---- Mobile sidebar ---- */
            var mobileBtn = document.getElementById('mobile-menu-btn');
            if (mobileBtn) {
                mobileBtn.addEventListener('click', function() {
                    document.body.classList.toggle('mobile-open');
                });
            }
        })();
    </script>

    @yield('custom-script')
    @stack('scripts')
</body>

</html>
