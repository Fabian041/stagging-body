<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <title>Pulling Day Shift - 05-Jul-25</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">

    <style>
        /* ======================
           THEME TOKENS (LIGHT)
           ====================== */
        :root {
            --brand-primary: #0d6efd;
            --brand-accent: #20c997;
            --brand-warning: #ffc107;

            --ink: #2b2f33;
            /* teks utama */
            --muted: #6c757d;
            /* teks sekunder */
            --surface: #ffffff;
            /* kartu */
            --surface-subtle: #f8f9fa;
            /* page bg */
            --border: #e9ecef;
            /* garis */

            --success-25: rgba(25, 135, 84, .15);
            --success-75: rgba(25, 135, 84, .75);
            --warning-25: rgba(255, 193, 7, .2);
            --warning-75: rgba(255, 193, 7, .75);
            --danger-10: rgba(220, 53, 69, .08);

            --shadow: 0 2px 8px rgba(16, 24, 40, .08);
            --radius: 12px;

            --highlight-direct: #e7f7ef;
            /* blinking highlight */
            --highlight-stock: #fff6e0;
            --highlight-base: #ffffff;

            --table-hover: #fafcff;
            --bar-bg: #eef2f7;
            --bar-grad-from: #3ddc97;
            --bar-grad-to: #0d6efd;

            --chip-bg: #eef2ff;
            --chip-border: #e1e6ff;
            --chip-ink: #344767;
        }

        /* ======================
           THEME TOKENS (DARK)
           ====================== */
        html[data-theme="dark"] {
            --ink: #edf1f5;
            --muted: #b7c0cc;
            --surface: #1b1f26;
            --surface-subtle: #12161b;
            --border: #2f3742;

            --success-25: rgba(52, 199, 89, .25);
            --success-75: rgba(52, 199, 89, .70);
            --warning-25: rgba(255, 189, 46, .25);
            --warning-75: rgba(255, 189, 46, .80);
            --danger-10: rgba(240, 68, 56, .22);

            --shadow: 0 2px 10px rgba(0, 0, 0, .45);

            --highlight-direct: #163c2a;
            --highlight-stock: #3a2f10;
            --highlight-base: #1b1f26;

            --table-hover: #202733;
            --bar-bg: #2a3340;
            --bar-grad-from: #2bb383;
            --bar-grad-to: #5691ff;

            --chip-bg: #243041;
            --chip-border: #324055;
            --chip-ink: #e8edf4;
        }

        /* ======================
           BASE
           ====================== */
        html,
        body {
            background: var(--surface-subtle);
            color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans";
        }

        h2 {
            color: var(--ink);
            letter-spacing: .2px;
            line-height: 1.35;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .card-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .page-head {
            background: linear-gradient(180deg, var(--surface), var(--surface-subtle));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        /* Tabs */
        .nav-tabs .nav-link {
            color: var(--muted);
            border: 1px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: var(--ink);
            background: var(--surface);
            border-color: var(--border) var(--border) var(--surface);
            box-shadow: var(--shadow);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        html[data-theme="dark"] .nav-tabs .nav-link.active {
            background: #1f2530;
            border-color: #2d3746 #2d3746 #1f2530;
            color: #eaf2ff;
        }

        /* ======================
           TABLE (base)
           ====================== */
        .table {
            color: var(--ink);
            border-color: var(--border);
        }

        .table td,
        .table th {
            vertical-align: middle;
            border-color: var(--border);
        }

        .table-hover tbody tr:hover {
            background: var(--table-hover);
        }

        html[data-theme="dark"] .table thead th {
            color: #f5f9ff;
            background: #1f2530;
        }

        /* Subtle risk row di dark */
        html[data-theme="dark"] .table-danger-subtle {
            background-color: var(--danger-10) !important;
            color: var(--ink) !important;
        }

        /* ======================
           STATUS COLORS (DP/SC cells)
           ====================== */
        .bg-success.bg-opacity-75 {
            background: var(--success-75) !important;
            color: #fff !important;
        }

        .bg-success.bg-opacity-25 {
            background: var(--success-25) !important;
        }

        .bg-warning.bg-opacity-75 {
            background: var(--warning-75) !important;
            color: #1a1d21 !important;
        }

        .bg-warning.bg-opacity-25 {
            background: var(--warning-25) !important;
        }

        html[data-theme="dark"] .bg-success.bg-opacity-75 {
            color: #0f141a !important;
            background: var(--success-75) !important;
        }

        html[data-theme="dark"] .bg-warning.bg-opacity-75 {
            color: #111 !important;
            background: var(--warning-75) !important;
        }

        html[data-theme="dark"] .bg-success.bg-opacity-25 {
            color: var(--ink) !important;
            background: var(--success-25) !important;
        }

        html[data-theme="dark"] .bg-warning.bg-opacity-25 {
            color: var(--ink) !important;
            background: var(--warning-25) !important;
        }

        /* ======================
           MINI PROGRESS
           ====================== */
        .qty-progress {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-progress .bar {
            position: relative;
            flex: 1 1 auto;
            height: 8px;
            border-radius: 999px;
            background: var(--bar-bg);
            overflow: hidden;
        }

        .qty-progress .bar>i {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0%;
            background: linear-gradient(90deg, var(--bar-grad-from), var(--bar-grad-to));
            transition: width .3s ease;
        }

        .qty-progress .val {
            font-weight: 700;
            font-size: .9rem;
            min-width: 56px;
            text-align: right;
        }

        html[data-theme="dark"] .qty-progress .bar {
            background: var(--bar-bg);
        }

        html[data-theme="dark"] .qty-progress .bar>i {
            filter: saturate(115%);
        }

        html[data-theme="dark"] .total-progress .val {
            color: #eaf2ff;
        }

        /* ======================
           HIGHLIGHT + ANIMATIONS
           ====================== */
        .flip {
            display: inline-block;
            transition: all .3s ease;
            transform-style: preserve-3d;
            transform-origin: bottom center;
        }

        .animate-flip {
            animation: flipAnimation .6s ease;
        }

        @keyframes flipAnimation {
            0% {
                transform: rotateX(0deg);
                opacity: 1;
            }

            50% {
                transform: rotateX(90deg);
                opacity: 0;
            }

            51% {
                transform: rotateX(-90deg);
            }

            100% {
                transform: rotateX(0deg);
                opacity: 1;
            }
        }

        @keyframes continuousBlink {

            0%,
            100% {
                background-color: var(--highlight-color);
            }

            50% {
                background-color: var(--base-bg);
            }
        }

        .highlight-beep-direct {
            --highlight-color: var(--highlight-direct);
            --base-bg: var(--highlight-base);
            animation: continuousBlink 1s ease-in-out infinite;
        }

        .highlight-beep-stock {
            --highlight-color: var(--highlight-stock);
            --base-bg: var(--highlight-base);
            animation: continuousBlink 1s ease-in-out infinite;
        }

        .highlight-beep-direct td,
        .highlight-beep-stock td {
            background-color: inherit !important;
        }

        /* ======================
           MISC
           ====================== */
        #sse-connection-status {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--ink);
            z-index: 9999;
        }

        html[data-theme="dark"] #sse-connection-status {
            background: #1f2530;
            border-color: #2e3a49;
            color: #dfe8f5;
        }

        .badge-soft {
            background: var(--chip-bg);
            color: var(--chip-ink);
            border: 1px solid var(--chip-border);
            font-weight: 600;
        }

        .strip-stat .title {
            font-size: .75rem;
            letter-spacing: .5px;
            color: var(--muted);
            text-transform: uppercase;
        }

        .strip-stat .value {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .strip-stat .chip {
            font-size: .7rem;
            border-radius: 2px;
            padding: .2rem .5rem;
        }

        html[data-theme="dark"] .btn-outline-secondary {
            color: #dbe6f2;
            border-color: #3b4656;
        }

        html[data-theme="dark"] .btn-outline-secondary:hover {
            background: #2a3340;
            border-color: #4b586c;
        }

        html[data-theme="dark"] .btn-outline-primary {
            color: #a3c4ff;
            border-color: #3a68d8;
        }

        html[data-theme="dark"] .btn-outline-primary:hover {
            background: #20335a;
            border-color: #4c7bf0;
        }

        html[data-theme="dark"] .input-group-text {
            background: #242b36;
            color: var(--muted);
            border-color: var(--border);
        }

        html[data-theme="dark"] .form-control {
            color: var(--ink);
            background: #1b2230;
            border-color: var(--border);
        }

        html[data-theme="dark"] .form-control::placeholder {
            color: #8b96a6;
        }

        html[data-theme="dark"] .dropdown-menu {
            background: #1b2330;
            border-color: #2d3847;
            color: var(--ink);
        }

        html[data-theme="dark"] .dropdown-item {
            color: var(--ink);
        }

        html[data-theme="dark"] .dropdown-item:hover {
            background: #242e3f;
        }

        html[data-theme="dark"] .tooltip .tooltip-inner {
            background: #0f1722;
            color: #e9f0f8;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .5);
        }

        html[data-theme="dark"] .tooltip .tooltip-arrow::before {
            border-top-color: #0f1722 !important;
        }

        .theme-toggle {
            border: 1px solid var(--border) !important;
        }

        /* ======================
           RESPONSIVE (stacked table)
           ====================== */
        @media (max-width: 992px) {
            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 12px;
                border: 1px solid var(--border);
                border-radius: 10px;
                overflow: hidden;
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border-bottom: 1px dashed var(--border);
            }

            .table tbody td:last-child {
                border-bottom: none;
            }

            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--muted);
            }

            html[data-theme="dark"] .table tbody td::before {
                color: #aeb9c8;
            }
        }

        /* ======================
           STICKY THEAD 2-BARIS – SEAMLESS (letakkan terakhir)
           ====================== */
        /* hilangkan bayangan/garis ganda */
        .table thead th {
            box-shadow: none !important;
        }

        /* semua th sticky */
        .table>thead>tr>th {
            position: sticky;
            background: var(--surface);
            z-index: 2;
        }

        html[data-theme="dark"] .table>thead>tr>th {
            background: #1f2530;
        }

        /* Baris 1: tanpa border-bottom, garis dibuat via ::after untuk nutup subpixel gap */
        .table>thead>tr:first-child>th {
            top: 0;
            z-index: 5;
            border-bottom: 0 !important;
        }

        .table>thead>tr:first-child>th::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 1px;
            background: var(--border);
            pointer-events: none;
        }

        /* Baris 2: menempel tepat di bawah baris 1 (offset dari JS: --thead-row1) */
        .table>thead>tr:nth-child(2)>th {
            top: var(--thead-row1, 40px) !important;
            z-index: 4;
            border-top: 0 !important;
            box-shadow: none !important;
        }

        /* rapatkan sel */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        /* ==== Fix: Total value visibility ==== */
        .card .strip-stat .value {
            color: var(--ink) !important;
            /* terang di light mode */
            opacity: 1;
            /* pastikan tidak ikut opacity parent */
        }

        /* di dark mode, paksa warna terang */
        html[data-theme="dark"] .card .strip-stat .value {
            color: #eaf2ff !important;
            /* atau pakai var(--ink), tapi ini ekstra terang */
        }

        /* kalau mau angka Total sedikit lebih menonjol */
        html[data-theme="dark"] .card .strip-stat .value,
        .card .strip-stat .value {
            text-shadow: 0 0 0 rgba(0, 0, 0, 0);
            /* matikan efek blend/anti-alias hairline */
        }

        .card {
            border-radius: var(--radius);
        }

        /* already used by your theme */
        .radius-4 {
            --radius: 4px;
        }

        .radius-6 {
            --radius: 6px;
        }

        .radius-8 {
            --radius: 8px;
        }

        html[data-theme="dark"] .bg-light {
            background-color: #262d38 !important;
            /* gelap lembut */
            color: #d7dee9 !important;
        }

        html[data-theme="dark"] .text-secondary {
            color: #b7c0cc !important;
        }

        /* 2) Progress number selalu terlihat */
        .total-progress .val {
            color: inherit;
            opacity: 1;
        }

        html[data-theme="dark"] .total-progress .val {
            color: #eaf2ff !important;
            /* kontras di latar gelap */
            font-weight: 800;
        }

        /* 3) (opsional, tapi bikin konsisten) latar sel tabel di dark mode */
        html[data-theme="dark"] .table> :not(caption)>*>* {
            background-color: #1f2530 !important;
            color: #e9f0f8 !important;
            border-color: var(--border) !important;
        }

        html[data-theme="dark"] .table-hover tbody tr:hover>* {
            background-color: #202a3a !important;
        }

        /* ==== Restore borders for .table-bordered (keep sticky header seam) ==== */

        /* Warna garis default (light & dark) */
        .table.table-bordered {
            border-color: var(--border) !important;
        }

        /* Semua sel: ada right & bottom border (left/top diatur terpisah) */
        .table.table-bordered> :not(caption)>*>* {
            border-style: solid !important;
            border-color: var(--border) !important;
            border-width: 0 1px 1px 0 !important;
            /* top 0, right 1, bottom 1, left 0 */
        }

        /* Sel pertama di setiap baris: tampilkan left border */
        .table.table-bordered>thead>tr>th:first-child,
        .table.table-bordered>tbody>tr>td:first-child,
        .table.table-bordered>tfoot>tr>*:first-child {
            border-left-width: 1px !important;
        }

        /* Header: pastikan ada border-bottom (sekaligus anti-gap dari sticky) */
        .table>thead>tr>th {
            border-bottom: 1px solid var(--border) !important;
        }

        .table>thead>tr:first-child>th {
            top: 0;
            z-index: 5;
        }

        .table>thead>tr:nth-child(2)>th {
            top: calc(var(--thead-row1, 40px) - 1px) !important;
            /* overlap 1px */
            z-index: 4;
            border-top: 0 !important;
            /* hindari double line dengan baris-1 */
        }

        /* (opsional) garis sedikit lebih tegas di dark */
        html[data-theme="dark"] .table.table-bordered> :not(caption)>*>* {
            border-color: #3a4452 !important;
        }

        html[data-theme="dark"] .table>thead>tr>th {
            border-bottom-color: #3a4452 !important;
        }

        /* =========================
   Responsive scale: Laptop → TV
   ========================= */

        /* lebar container & padding sisi mengikuti layar */
        :root {
            --page-side-pad: clamp(16px, 2vw, 32px);
            --table-top-offset: 260px;
            /* sesuaikan jika header kamu lebih tinggi/rendah */
        }

        /* paksa container lebih lebar tapi tetap ada margin aman */
        .container {
            max-width: min(96vw, 1920px);
            /* 96% layar, cap di ~1920px */
            padding-left: var(--page-side-pad);
            padding-right: var(--page-side-pad);
        }

        /* skala font global: 16px di laptop → ±28px di TV lebar */
        html {
            font-size: clamp(16px, 1.2vw, 28px);
        }

        /* judul halaman mengikuti lebar layar */
        h2 {
            font-size: clamp(1.75rem, 2.4vw, 3rem);
        }

        /* tabs & tombol lebih besar tapi proporsional */
        .nav-tabs .nav-link {
            font-size: clamp(0.95rem, 1.2vw, 1.3rem);
            padding: clamp(.6rem, .9vw, 1rem) clamp(.8rem, 1.2vw, 1.25rem);
        }

        .btn {
            font-size: clamp(.55rem, 1vw, 1.2rem);
            padding: clamp(.55rem, .8vw, .8rem) clamp(.9rem, 1.3vw, 1.2rem);
        }

        /* tabel: header & sel membesar responsif */
        .table thead th {
            font-size: clamp(1rem, 1.2vw, 1.35rem);
            padding: clamp(.7rem, .9vw, 1.05rem) clamp(.6rem, .9vw, .9rem);
        }

        .table tbody td {
            font-size: clamp(.95rem, 1.1vw, 1.25rem);
            padding: clamp(.65rem, .9vw, 1rem) clamp(.6rem, .9vw, .9rem);
        }

        /* progress bar & angka */
        .qty-progress .val {
            font-size: clamp(.95rem, 1.1vw, 1.25rem);
        }

        .qty-progress .bar {
            height: clamp(8px, .8vw, 16px);
        }

        /* angka ringkasan di kartu */
        .strip-stat .title {
            font-size: clamp(.75rem, .9vw, 1.1rem);
        }

        .strip-stat .value {
            font-size: clamp(1.1rem, 2.1vw, 3.2rem);
        }

        /* area scroll tabel menyesuaikan tinggi layar (biar pas di TV) */
        .table-responsive {
            max-height: calc(100vh - var(--table-top-offset)) !important;
        }

        /* kalau layarnya lebih tinggi, kurangi offset supaya tabel makin tinggi */
        @media (min-height: 900px) {
            :root {
                --table-top-offset: 240px;
            }
        }

        @media (min-height: 1080px) {
            :root {
                --table-top-offset: 220px;
            }
        }

        /* ultra-wide: sedikit lebih melebar lagi */
        @media (min-width: 2200px) {
            .container {
                max-width: 95vw;
            }
        }

        /* =======================================
   Light Mode Table Header -- Soft Slate
   ======================================= */
        html[data-theme="light"] {
            --thead-bg: #EEF2F7;
            /* latar header */
            --thead-ink: #273446;
            /* teks header (slate gelap) */
            --thead-border: #D6DEE9;
            /* garis header */
        }

        html[data-theme="light"] .table>thead>tr>th {
            background: var(--thead-bg) !important;
            color: var(--thead-ink) !important;
            border-bottom: 1px solid var(--thead-border) !important;
            box-shadow: none !important;
        }

        /* Sticky 2-baris tetap rapat (overlap 1px) */
        html[data-theme="light"] .table>thead>tr:first-child>th {
            top: 0;
            z-index: 5;
        }

        html[data-theme="light"] .table>thead>tr:nth-child(2)>th {
            top: calc(var(--thead-row1, 40px) - 1px) !important;
            z-index: 4;
            border-top: 0 !important;
        }

        /* Biar .table-bordered pakai warna garis header yang sama */
        html[data-theme="light"] .table.table-bordered>thead>tr>th {
            border-color: var(--thead-border) !important;
        }
    </style>

</head>

<body>
    <div class="container py-4">

        <!-- Header / Filters -->
        {{-- <div class="page-head p-3 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-industry text-primary"></i>
                    <strong class="h5 m-0">Production Date</strong>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light border">
                        {{ Carbon\Carbon::parse($selectedDate ?? now())->format('D, M j Y') }}
                    </span>

                    <!-- Theme toggle -->
                   
                </div>
            </div>

            <form method="GET" action="{{ route('dashboard.prodPlan') }}" class="row g-2 align-items-center mt-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border">
                            <i class="far fa-calendar"></i>
                        </span>
                        <input type="date" class="form-control border" name="date"
                            value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                            style="font-weight:600">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel-fill me-1"></i> Filter
                        </button>
                        @if (request()->has('date'))
                            <a href="{{ route('dashboard.prodPlan') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        @endif
                        <button type="button" class="btn btn-outline-secondary" onclick="navigateDate(-1)">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        @php
                            $selected = $selectedDate ?? now()->format('Y-m-d');
                            $isToday = $selected === now()->format('Y-m-d');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary {{ $isToday ? 'disabled' : '' }}"
                            onclick="navigateDate(1)" {{ $isToday ? 'disabled' : '' }}>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="gotoToday()">Today</button>
                        <button type="submit" name="force_refresh" value="1" class="btn btn-warning">
                            <i class="fas fa-sync-alt me-1"></i> Re-fetch
                        </button>
                    </div>
                </div>
            </form>
        </div> --}}

        @if (isset($message))
            <div class="alert alert-{{ $messageType ?? 'info' }} alert-dismissible fade show mb-3" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold m-0">Production Pulling Plan –
                {{ Carbon\Carbon::parse($selectedDate ?? now())->format('l, j F Y') }}
            </h2>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft">
                    <i class="far fa-clock me-1"></i> Last Update:
                    {{ \Carbon\Carbon::parse($lastUpdate ?? now())->format('H:i:s') }}
                </span>
                <a class="btn btn-outline-primary" href="/pulling/settings">
                    <i class="fas fa-cog me-1"></i> Settings
                </a>
                <a type="button" id="themeToggle" class="btn btn-outline-secondary theme-toggle">
                    <i class="far fa-moon me-1"></i><span>Dark</span>
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="lineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="line3-tab" data-bs-toggle="tab" data-bs-target="#line3"
                    type="button" role="tab">
                    AS003
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line4-tab" data-bs-toggle="tab" data-bs-target="#line4" type="button"
                    role="tab">
                    AS004
                </button>
            </li>
        </ul>

        <div class="tab-content" id="lineTabsContent">
            <!-- ================== AS003 ================== -->
            <div class="tab-pane fade show active" id="line3" role="tabpanel" aria-labelledby="line3-tab">
                <div data-toggle-table="AS003">
                    @php
                        $as003MorningQty = $grouped['AS003']['morning_shift_qty'] ?? 0;
                        $as003NightQty = $grouped['AS003']['night_shift_qty'] ?? 0;
                        $as003TotalQty = $grouped['AS003']['total_qty'] ?? 0;

                        $as003MorningStatus = 'Normal Shift';
                        if ($as003MorningQty > 900) {
                            $as003MorningStatus = 'Advance to LS1';
                        } elseif ($as003MorningQty > 750) {
                            $as003MorningStatus = 'Advance to NS';
                        }

                        $as003NightStatus = 'Normal Shift';
                        if ($as003NightQty > 630) {
                            $as003NightStatus = 'Advance to LS3';
                        }
                    @endphp

                    <div class="card mb-3 radius-4">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3">
                            <div class="strip-stat">
                                <div class="title">Morning Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-primary">{{ $as003MorningQty }}</div>
                                    @if ($as003MorningStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-warning-subtle border text-dark fw-bolder">{{ $as003MorningStatus }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="strip-stat">
                                <div class="title">Night Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-success">{{ $as003NightQty }}</div>
                                    @if ($as003NightStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-danger-subtle border text-dark fw-bolder">{{ $as003NightStatus }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ms-auto strip-stat">
                                <div class="title">Total</div>
                                <div class="value">{{ $as003TotalQty }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar: Presets & Columns -->
                    <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                        <!-- Presets -->
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown">
                                Presets
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"
                                        onclick="applyPreset('AS003','default');return false;">Default</a></li>
                                <li><a class="dropdown-item" href="#"
                                        onclick="applyPreset('AS003','risk');return false;">Risk first</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#"
                                        onclick="saveCurrentView('AS003');return false;">Save current view</a></li>
                            </ul>
                        </div>
                        <!-- Columns -->
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Columns
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 160px"
                                data-colpicker="AS003">
                                @foreach (['Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute', 'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time', 'Delivery Date', 'Balance Time'] as $i => $label)
                                    <li class="form-check form-check-sm d-flex align-items-center gap-2 mb-1">
                                        <input class="form-check-input column-check" type="checkbox"
                                            data-col="{{ $i }}" id="col_AS003_{{ $i }}">
                                        <label class="form-check-label small"
                                            for="col_AS003_{{ $i }}">{{ $label }}</label>
                                    </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item small" href="#"
                                        onclick="resetColumns('AS003');return false;">Reset columns</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="table-responsive" style="max-height:800px;">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Customer</th>
                                        <th rowspan="2">Dock</th>
                                        <th rowspan="2">Cycle</th>
                                        <th rowspan="2">Back No</th>
                                        <th rowspan="2">Order</th>
                                        <th colspan="2" class="text-center">Running Qty</th>
                                        <th rowspan="2">Cycle Time</th>
                                        <th colspan="4" class="text-center">Working Time</th>
                                        <th rowspan="2">Delivery Time</th>
                                        <th rowspan="2">Delivery Date</th>
                                        <th rowspan="2">Balance Time</th>
                                    </tr>
                                    <tr>
                                        <th>Direct Pulling</th>
                                        <th>Stock Chute</th>
                                        <th>Planning Start</th>
                                        <th>Actual Start</th>
                                        <th>Duration</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($grouped['AS003']['data'] ?? [] as $key => $rows)
                                        @php
                                            [$customer, $delivery] = explode('|', $key);
                                            $rowspan = $rows->count();
                                            $dock = $rows->first()->dock ?? '--';
                                        @endphp
                                        @foreach ($rows as $index => $item)
                                            @php
                                                $timeParts = explode(':', $item->balance_time ?? '00:00');
                                                $hours = (int) $timeParts[0];

                                                if (!function_exists('getQtyClass')) {
                                                    function getQtyClass($qty, $orderQty)
                                                    {
                                                        if ($qty >= $orderQty) {
                                                            return 'bg-success bg-opacity-75 fw-bold text-white';
                                                        } elseif ($qty > 0) {
                                                            return 'bg-warning bg-opacity-75 fw-bold';
                                                        }
                                                        return 'bg-light fw-semibold text-secondary';
                                                    }
                                                }

                                                $dp = (int) ($item->direct_pulling_qty ?: 0);
                                                $sc = (int) ($item->stock_chute_qty ?: 0);
                                                $ord = max(1, (int) $item->order_qty);
                                                $pct = min(100, round((($dp + $sc) / $ord) * 100));
                                            @endphp
                                            <tr>
                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Customer"><span
                                                            class="flip">{{ $customer }}</span></td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Dock"><span
                                                            class="flip">{{ $dock }}</span></td>
                                                @endif
                                                <td data-label="Cycle"><span
                                                        class="flip">{{ $item->cycle }}</span></td>
                                                <td data-label="Back No"><span
                                                        class="flip">{{ $item->back_no }}</span></td>
                                                <td data-label="Order"><span
                                                        class="flip">{{ $item->order_qty }}</span></td>

                                                <td class="{{ getQtyClass($item->direct_pulling_qty, $item->order_qty) }}"
                                                    data-label="Direct Pulling">
                                                    <div class="qty-progress"
                                                        title="DP {{ $dp }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($dp / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="direct-pulling"
                                                                data-item-id="{{ $item->id }}">{{ $dp }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="{{ getQtyClass($item->stock_chute_qty, $item->order_qty) }}"
                                                    data-label="Stock Chute">
                                                    <div class="qty-progress"
                                                        title="SC {{ $sc }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($sc / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="stock-chute"
                                                                data-item-id="{{ $item->id }}">{{ $sc }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td data-label="Cycle Time"><span
                                                        class="flip">{{ $item->prod_time }}</span></td>
                                                <td data-label="Planning Start"><span data-type="start"
                                                        data-item-id="{{ $item->id }}">{{ $item->working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Actual Start"><span data-type="actual_start"
                                                        data-item-id="{{ $item->id }}">{{ $item->actual_working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Duration"><span
                                                        class="flip text-warning">{{ $item->working_duration ?? '--' }}</span>
                                                </td>

                                                <!-- Progress total -->
                                                <td data-label="Progress" class="total-progress">
                                                    <div class="qty-progress"
                                                        title="DP+SC {{ $dp + $sc }} / {{ $ord }} ({{ $pct }}%)">
                                                        <div class="bar"><i
                                                                style="width: {{ $pct }}%"></i></div>
                                                        <span class="val">{{ $pct }}%</span>
                                                    </div>
                                                </td>

                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Time">
                                                        <span class="flip">{{ $delivery }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Date">
                                                        <span
                                                            class="flip">{{ $item->delivery_date ? Carbon\Carbon::parse($item->delivery_date)->format('m/d') : '--' }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Balance Time"
                                                        class="{{ $item->balance_time && $hours < 3 ? 'table-danger-subtle' : '' }}">
                                                        <span data-type="balance"
                                                            data-item-id="{{ $item->id }}">{{ $item->balance_time ?? '--' }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center py-4 text-muted">Belum ada plan
                                                untuk tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================== AS004 ================== -->
            <div class="tab-pane fade" id="line4" role="tabpanel" aria-labelledby="line4-tab">
                <div data-toggle-table="AS004">
                    @php
                        $as004MorningQty = $grouped['AS004']['morning_shift_qty'] ?? 0;
                        $as004NightQty = $grouped['AS004']['night_shift_qty'] ?? 0;
                        $as004TotalQty = $grouped['AS004']['total_qty'] ?? 0;

                        $as004MorningStatus = 'Normal Shift';
                        if ($as004MorningQty > 900) {
                            $as004MorningStatus = 'Advance to LS1';
                        } elseif ($as004MorningQty > 750) {
                            $as004MorningStatus = 'Advance to NS';
                        }

                        $as004NightStatus = 'Normal Shift';
                        if ($as004NightQty > 630) {
                            $as004NightStatus = 'Advance to LS3';
                        }
                    @endphp

                    <div class="card mb-3 radius-4">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3">
                            <div class="strip-stat">
                                <div class="title">Morning Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-primary">{{ $as004MorningQty }}</div>
                                    @if ($as004MorningStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-warning-subtle border text-dark fw-bolder">{{ $as004MorningStatus }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="strip-stat">
                                <div class="title">Night Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-success">{{ $as004NightQty }}</div>
                                    @if ($as004NightStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-danger-subtle border text-dark fw-bolder">{{ $as004NightStatus }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ms-auto strip-stat">
                                <div class="title">Total</div>
                                <div class="value">{{ $as004TotalQty }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar: Presets & Columns -->
                    <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown">
                                Presets
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"
                                        onclick="applyPreset('AS004','default');return false;">Default</a></li>
                                <li><a class="dropdown-item" href="#"
                                        onclick="applyPreset('AS004','risk');return false;">Risk first</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#"
                                        onclick="saveCurrentView('AS004');return false;">Save current view</a></li>
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Columns
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 160px"
                                data-colpicker="AS004">
                                @foreach (['Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute', 'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time', 'Delivery Date', 'Balance Time'] as $i => $label)
                                    <li class="form-check form-check-sm d-flex align-items-center gap-2 mb-1">
                                        <input class="form-check-input column-check" type="checkbox"
                                            data-col="{{ $i }}" id="col_AS004_{{ $i }}">
                                        <label class="form-check-label small"
                                            for="col_AS004_{{ $i }}">{{ $label }}</label>
                                    </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item small" href="#"
                                        onclick="resetColumns('AS004');return false;">Reset columns</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="table-responsive" style="max-height:800px;">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Customer</th>
                                        <th rowspan="2">Dock</th>
                                        <th rowspan="2">Cycle</th>
                                        <th rowspan="2">Back No</th>
                                        <th rowspan="2">Order</th>
                                        <th colspan="2" class="text-center">Running Qty</th>
                                        <th rowspan="2">Cycle Time</th>
                                        <th colspan="4" class="text-center">Working Time</th>
                                        <th rowspan="2">Delivery Time</th>
                                        <th rowspan="2">Delivery Date</th>
                                        <th rowspan="2">Balance Time</th>
                                    </tr>
                                    <tr>
                                        <th>Direct Pulling</th>
                                        <th>Stock Chute</th>
                                        <th>Planning Start</th>
                                        <th>Actual Start</th>
                                        <th>Duration</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($grouped['AS004']['data'] ?? [] as $key => $rows)
                                        @php
                                            [$customer, $delivery] = explode('|', $key);
                                            $rowspan = $rows->count();
                                            $dock = $rows->first()->dock ?? '--';
                                        @endphp
                                        @foreach ($rows as $index => $item)
                                            @php
                                                $timeParts = explode(':', $item->balance_time ?? '00:00');
                                                $hours = (int) $timeParts[0];

                                                if (!function_exists('getQtyClass')) {
                                                    function getQtyClass($qty, $orderQty)
                                                    {
                                                        if ($qty >= $orderQty) {
                                                            return 'bg-success bg-opacity-75 fw-bold text-white';
                                                        } elseif ($qty > 0) {
                                                            return 'bg-warning bg-opacity-75 fw-bold';
                                                        }
                                                        return 'bg-light fw-semibold text-secondary';
                                                    }
                                                }

                                                $dp = (int) ($item->direct_pulling_qty ?: 0);
                                                $sc = (int) ($item->stock_chute_qty ?: 0);
                                                $ord = max(1, (int) $item->order_qty);
                                                $pct = min(100, round((($dp + $sc) / $ord) * 100));
                                            @endphp
                                            <tr>
                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Customer"><span
                                                            class="flip">{{ $customer }}</span></td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Dock"><span
                                                            class="flip">{{ $dock }}</span></td>
                                                @endif
                                                <td data-label="Cycle"><span
                                                        class="flip">{{ $item->cycle }}</span></td>
                                                <td data-label="Back No"><span
                                                        class="flip">{{ $item->back_no }}</span></td>
                                                <td data-label="Order"><span
                                                        class="flip">{{ $item->order_qty }}</span></td>

                                                <td class="{{ getQtyClass($item->direct_pulling_qty, $item->order_qty) }}"
                                                    data-label="Direct Pulling">
                                                    <div class="qty-progress"
                                                        title="DP {{ $dp }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($dp / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="direct-pulling"
                                                                data-item-id="{{ $item->id }}">{{ $dp }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="{{ getQtyClass($item->stock_chute_qty, $item->order_qty) }}"
                                                    data-label="Stock Chute">
                                                    <div class="qty-progress"
                                                        title="SC {{ $sc }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($sc / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="stock-chute"
                                                                data-item-id="{{ $item->id }}">{{ $sc }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td data-label="Cycle Time"><span
                                                        class="flip">{{ $item->prod_time }}</span></td>
                                                <td data-label="Planning Start"><span data-type="start"
                                                        data-item-id="{{ $item->id }}">{{ $item->working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Actual Start"><span data-type="actual_start"
                                                        data-item-id="{{ $item->id }}">{{ $item->actual_working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Duration"><span
                                                        class="flip text-warning">{{ $item->working_duration ?? '--' }}</span>
                                                </td>

                                                <td data-label="Progress" class="total-progress">
                                                    <div class="qty-progress"
                                                        title="DP+SC {{ $dp + $sc }} / {{ $ord }} ({{ $pct }}%)">
                                                        <div class="bar"><i
                                                                style="width: {{ $pct }}%"></i></div>
                                                        <span class="val">{{ $pct }}%</span>
                                                    </div>
                                                </td>

                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Time">
                                                        <span class="flip">{{ $delivery }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Date">
                                                        <span
                                                            class="flip">{{ $item->delivery_date ? Carbon\Carbon::parse($item->delivery_date)->format('m/d') : '--' }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Balance Time"
                                                        class="{{ $item->balance_time && $hours < 3 ? 'table-danger-subtle' : '' }}">
                                                        <span data-type="balance"
                                                            data-item-id="{{ $item->id }}">{{ $item->balance_time ?? '--' }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center py-4 text-muted">Belum ada plan
                                                untuk tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /tab-content -->
    </div><!-- /container -->

    <script>
        /* ======================
                                                               THEME TOGGLE (tetap)
                                                               ====================== */
        (function themeInit() {
            const key = 'pulling_theme';
            const saved = localStorage.getItem(key);
            const el = document.documentElement;

            function apply(mode) {
                el.setAttribute('data-theme', mode);
                const btn = document.getElementById('themeToggle');
                if (!btn) return;
                const icon = btn.querySelector('i');
                const label = btn.querySelector('span');
                if (mode === 'dark') {
                    icon.className = 'far fa-sun me-1';
                    label.textContent = 'Light';
                } else {
                    icon.className = 'far fa-moon me-1';
                    label.textContent = 'Dark';
                }
            }

            if (saved) apply(saved);
            else apply(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                'light');

            document.getElementById('themeToggle')?.addEventListener('click', () => {
                const current = el.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem(key, next);
                apply(next);
            });
        })();

        /* ======================
           KONST & UTIL KOLOM
           ====================== */
        const COL_ORDER = [
            'Customer', 'Dock', 'Cycle', 'Back No', 'Order',
            'Direct Pulling', 'Stock Chute', 'Cycle Time',
            'Planning Start', 'Actual Start', 'Duration', 'Progress',
            'Delivery Time', 'Delivery Date', 'Balance Time'
        ];

        function normLabel(s) {
            return (s || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        function colIndexByLabel(td) {
            const lbl = normLabel(td?.getAttribute('data-label'));
            const idx = COL_ORDER.map(normLabel).indexOf(lbl);
            return idx >= 0 ? idx : (td?.cellIndex ?? 9999);
        }

        function getCellByLabel(row, wanted) {
            const wl = normLabel(wanted);
            let td = row.querySelector(`[data-label]`);
            // cepat: kalau ada yang tepat
            const tds = Array.from(row.children);
            for (const c of tds) {
                if (normLabel(c.getAttribute('data-label')) === wl) return c;
            }
            // fallback: hampir sama (mis. spasi/kapitalisasi beda)
            for (const c of tds) {
                const l = normLabel(c.getAttribute('data-label'));
                if (l && (l.includes(wl) || wl.includes(l))) return c;
            }
            return null;
        }

        /* ======================
           SSE CLIENT + SUMMARY PINNED (ORIGINAL LOGIC)
           ====================== */
        class ProductionPlanSSEClient {
            constructor() {
                this.eventSource = null;
                this.statusElement = null;
                this.currentDate = this.getCurrentDate();

                this.highlightTimeouts = new Set();
                this.originalOrder = new Map();
                this.orderRestoreTimeouts = new Map();

                this.summaries = {}; // {AS003:{row,totals,ids}, AS004:{...}}

                this.init();
            }

            init() {
                this.createStatusIndicator();
                this.addFlipStyles();
                this.connect();
                this.setupDateChangeListener();
                this.setupErrorHandling();
                this.storeOriginalOrder();

                this.AS003 = document.querySelector('[data-toggle-table="AS003"]');
                this.AS004 = document.querySelector('[data-toggle-table="AS004"]');

                this.prefillRawAttrs(this.AS003);
                this.prefillRawAttrs(this.AS004);

                this.buildSummaries();

                if (window.bootstrap?.Tooltip) {
                    const triggers = [].slice.call(document.querySelectorAll('[title]'));
                    triggers.forEach(el => {
                        el.setAttribute('data-bs-toggle', 'tooltip');
                        try {
                            new bootstrap.Tooltip(el);
                        } catch {}
                    });
                }
            }

            /* ===== Prefill ===== */
            prefillRawAttrs(container) {
                if (!container) return;
                container.querySelectorAll('tbody tr').forEach(row => {
                    // Back No: simpan mentah
                    const bnTd = getCellByLabel(row, 'Back No');
                    const bnEl = bnTd?.querySelector('.flip') || bnTd;
                    if (bnEl && !bnEl.dataset.backnoRaw) {
                        bnEl.dataset.backnoRaw = (bnEl.textContent || '').trim();
                    }
                    // Order: simpan mentah
                    const odTd = getCellByLabel(row, 'Order');
                    const odEl = odTd?.querySelector('.flip') || odTd;
                    if (odEl && !odEl.dataset.orderRaw) {
                        const num = parseInt(String(odEl.textContent || '0').replace(/[^\d-]/g, ''), 10) || 0;
                        odEl.dataset.orderRaw = String(num);
                    }
                });
            }

            /* ===== Group helpers ===== */
            isGroupStart(row) {
                return row && row.style.display !== 'none' && !!row.querySelector('[rowspan]');
            }
            getGroupRowsFrom(startRow) {
                const rows = [startRow];
                let p = startRow;
                while (p?.nextElementSibling && !this.isGroupStart(p.nextElementSibling)) {
                    p = p.nextElementSibling;
                    rows.push(p);
                }
                return rows;
            }
            recalcRowspans(container) {
                const tbody = container?.querySelector('tbody');
                if (!tbody) return;
                Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                    if (!this.isGroupStart(row)) return;
                    const groupRows = this.getGroupRowsFrom(row);
                    const visible = Math.max(1, groupRows.filter(r => r.style.display !== 'none').length);
                    row.querySelectorAll('[rowspan]').forEach(td => td.rowSpan = visible);
                });
            }

            /* ===== Pindahkan sel header ke hostRow (posisi kolom akurat) ===== */
            _moveRowspanCellsTo(startRow, hostRow) {
                if (!startRow || !hostRow || hostRow === startRow) return;

                hostRow.querySelectorAll('[data-cloned-header]').forEach(n => n.remove());

                const startCells = Array.from(startRow.children)
                    .filter(td => td.hasAttribute('rowspan'))
                    .sort((a, b) => colIndexByLabel(a) - colIndexByLabel(b));

                startCells.forEach(td => {
                    const clone = td.cloneNode(true);
                    clone.setAttribute('rowspan', 1);
                    clone.setAttribute('data-cloned-header', '1');

                    const wantIdx = colIndexByLabel(td);
                    let ref = null;
                    for (const ex of Array.from(hostRow.children)) {
                        if (colIndexByLabel(ex) > wantIdx) {
                            ref = ex;
                            break;
                        }
                    }
                    hostRow.insertBefore(clone, ref);
                });

                const tbody = startRow.parentElement;
                if (tbody && hostRow.previousElementSibling !== startRow) tbody.insertBefore(hostRow, startRow);

                Array.from(startRow.querySelectorAll('[rowspan]')).forEach(td => td.removeAttribute('rowspan'));
                startRow.style.display = 'none';
            }

            /* ===== Row utils (lebih robust) ===== */
            _getBackNo(row) {
                const td = getCellByLabel(row, 'Back No');
                const el = td?.querySelector('.flip') || td;
                let val = (el?.dataset?.backnoAlias || el?.dataset?.backnoRaw || el?.textContent || '').trim();
                if (val) return val.toUpperCase();
                const text = (row.textContent || '').toUpperCase();
                const m = text.match(/\b(?:D\d{2,4}|CI\d{2,4})\b/);
                return m ? m[0] : '';
            }
            _getOrder(row) {
                const td = getCellByLabel(row, 'Order');
                const el = td?.querySelector('.flip') || td;
                if (!el) return 0;
                const raw = parseInt(el.dataset?.orderRaw || '', 10);
                if (!isNaN(raw)) return raw;
                return parseInt(String(el.textContent || '0').replace(/[^\d-]/g, ''), 10) || 0;
            }
            _getDP(row) {
                const el = row?.querySelector('[data-type="direct-pulling"]');
                return el ? (parseInt((el.textContent || '').replace(/[^\d-]/g, ''), 10) || 0) : 0;
            }
            _getSC(row) {
                const el = row?.querySelector('[data-type="stock-chute"]');
                return el ? (parseInt((el.textContent || '').replace(/[^\d-]/g, ''), 10) || 0) : 0;
            }
            _getId(row) {
                const el = row?.querySelector('[data-type="direct-pulling"]') || row?.querySelector(
                    '[data-type="stock-chute"]');
                return el ? el.getAttribute('data-item-id') : null;
            }

            /* ===== Build summaries (pinned top) ===== */
            buildSummaries() {
                Object.values(this.summaries).forEach(s => s.row?.remove());
                this.summaries = {};

                if (this.AS003) {
                    this.summaries.AS003 = this._extractAndPinSummary({
                        key: 'AS003',
                        container: this.AS003,
                        targets: ['D111', 'CI12'],
                        label: 'CI12'
                    });
                }
                if (this.AS004) {
                    this.summaries.AS004 = this._extractAndPinSummary({
                        key: 'AS004',
                        container: this.AS004,
                        targets: ['D500', 'CI19'],
                        label: 'CI19'
                    });
                }
            }

            _extractAndPinSummary({
                key,
                container,
                targets,
                label
            }) {
                const tgtSet = new Set(targets.map(t => String(t).toUpperCase()));
                const tbody = container?.querySelector('tbody');
                const summary = {
                    row: null,
                    totals: {
                        order: 0,
                        dp: 0,
                        sc: 0
                    },
                    ids: new Map()
                };
                if (!tbody) return summary;

                const allRows = Array.from(tbody.querySelectorAll('tr'));
                let i = 0;
                const groups = [];
                while (i < allRows.length) {
                    const start = allRows[i];
                    const rs = this.isGroupStart(start) ? parseInt(start.querySelector('[rowspan]')?.getAttribute(
                        'rowspan') || '1', 10) : 1;
                    const g = [start];
                    let w = start;
                    for (let k = 1; k < rs && (i + k) < allRows.length; k++) {
                        w = allRows[i + k];
                        g.push(w);
                    }
                    groups.push(g);
                    i += Math.max(1, rs);
                }

                const customerBag = [];

                groups.forEach(groupRows => {
                    const startRow = groupRows[0];
                    const matches = groupRows.filter(r => tgtSet.has(this._getBackNo(r)));
                    if (matches.length === 0) return;

                    // catat nama customer dari baris start grup
                    const custTd = getCellByLabel(startRow, 'Customer');
                    const custText = (custTd?.querySelector('.flip')?.textContent || custTd?.textContent || '')
                        .trim();
                    if (custText) customerBag.push(custText);

                    // akumulasi total + id
                    matches.forEach(r => {
                        const id = this._getId(r);
                        const dp = this._getDP(r);
                        const sc = this._getSC(r);
                        const od = this._getOrder(r);
                        summary.totals.dp += dp;
                        summary.totals.sc += sc;
                        summary.totals.order += od;
                        if (id) summary.ids.set(id, {
                            dp,
                            sc,
                            order: od
                        });
                    });

                    const keepRows = groupRows.filter(r => !matches.includes(r));
                    if (keepRows.length === 0) {
                        groupRows.forEach(r => r.remove());
                        return;
                    }

                    if (matches.includes(startRow)) this._moveRowspanCellsTo(startRow, keepRows[0]);
                    matches.forEach(r => {
                        if (r !== startRow) r.remove();
                    });
                });

                // pilih customer yang paling sering (mode)
                let customerText = '--';
                if (customerBag.length) {
                    const freq = customerBag.reduce((m, s) => (m[s] = (m[s] || 0) + 1, m), {});
                    customerText = Object.entries(freq).sort((a, b) => b[1] - a[1])[0][0];
                }

                if (summary.totals.order + summary.totals.dp + summary.totals.sc > 0) {
                    summary.row = this._createSummaryRow({
                        label,
                        totals: summary.totals,
                        customerText
                    });
                    tbody.insertBefore(summary.row, tbody.firstChild || null);
                }

                this.recalcRowspans(container);
                return summary;
            }

            _createSummaryRow({
                label,
                totals,
                customerText = '--'
            }) {
                const tr = document.createElement('tr');
                tr.className = 'fw-bold';

                const pct = Math.min(100, Math.round(((totals.dp + totals.sc) / Math.max(1, totals.order)) * 100));
                const td = (text, attrs = {}) => {
                    const el = document.createElement('td');
                    if (text != null) el.innerHTML = `<span class="flip">${text}</span>`;
                    for (const k in attrs) el.setAttribute(k, attrs[k]);
                    return el;
                };

                // kolom-kolom
                tr.appendChild(td(customerText, {
                    'data-label': 'Customer'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Dock'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Cycle'
                }));
                tr.appendChild(td(label, {
                    'data-label': 'Back No'
                }));
                tr.appendChild(td(totals.order.toLocaleString('id-ID'), {
                    'data-label': 'Order'
                }));

                const tdDP = document.createElement('td');
                tdDP.setAttribute('data-label', 'Direct Pulling');
                tdDP.innerHTML =
                    `<div class="qty-progress" title="DP ${totals.dp} / ${totals.order}">
       <div class="bar"><i style="width:${Math.min(100, Math.round((totals.dp/Math.max(1,totals.order))*100))}%;"></i></div>
       <span class="val"><span class="flip" data-summary-dp>${totals.dp}</span></span>
     </div>`;
                tr.appendChild(tdDP);

                const tdSC = document.createElement('td');
                tdSC.setAttribute('data-label', 'Stock Chute');
                tdSC.innerHTML =
                    `<div class="qty-progress" title="SC ${totals.sc} / ${totals.order}">
       <div class="bar"><i style="width:${Math.min(100, Math.round((totals.sc/Math.max(1,totals.order))*100))}%;"></i></div>
       <span class="val"><span class="flip" data-summary-sc>${totals.sc}</span></span>
     </div>`;
                tr.appendChild(tdSC);

                tr.appendChild(td('--', {
                    'data-label': 'Cycle Time'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Planning Start'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Actual Start'
                }));
                tr.appendChild(td('<span class="text-warning">--</span>', {
                    'data-label': 'Duration'
                }));

                const tdProg = document.createElement('td');
                tdProg.className = 'total-progress';
                tdProg.setAttribute('data-label', 'Progress');
                tdProg.innerHTML =
                    `<div class="qty-progress" title="DP+SC ${totals.dp + totals.sc} / ${totals.order} (${pct}%)">
       <div class="bar"><i data-summary-totalbar style="width:${pct}%;"></i></div>
       <span class="val" data-summary-totalpct>${pct}%</span>
     </div>`;
                tr.appendChild(tdProg);

                tr.appendChild(td('--', {
                    'data-label': 'Delivery Time'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Delivery Date'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Balance Time'
                }));

                return tr;
            }

            _refreshSummaryRow(summary) {
                if (!summary?.row) return;
                const {
                    order,
                    dp,
                    sc
                } = summary.totals;
                const pct = Math.min(100, Math.round(((dp + sc) / Math.max(1, order)) * 100));
                const dpSpan = summary.row.querySelector('[data-summary-dp]');
                const scSpan = summary.row.querySelector('[data-summary-sc]');
                const totalBar = summary.row.querySelector('[data-summary-totalbar]');
                const totalPct = summary.row.querySelector('[data-summary-totalpct]');
                const orderFlip = summary.row.querySelector('[data-label="Order"] .flip');
                if (dpSpan) dpSpan.textContent = dp;
                if (scSpan) scSpan.textContent = sc;
                if (orderFlip) orderFlip.textContent = order.toLocaleString('id-ID');
                if (totalBar) totalBar.style.width = pct + '%';
                if (totalPct) totalPct.textContent = pct + '%';
            }
            refreshSummaries() {
                Object.values(this.summaries).forEach(s => this._refreshSummaryRow(s));
            }

            /* ===== SSE ===== */
            storeOriginalOrder() {
                document.querySelectorAll('.tab-pane table tbody').forEach(tbody => {
                    this.originalOrder.set(tbody, Array.from(tbody.querySelectorAll('tr')));
                });
            }
            getCurrentDate() {
                const inp = document.querySelector('input[name="date"]');
                return inp ? inp.value : new Date().toISOString().split('T')[0];
            }
            createStatusIndicator() {
                const el = document.createElement('div');
                el.id = 'sse-connection-status';
                el.textContent = '● Connecting to updates...';
                document.body.appendChild(el);
                this.statusElement = el;
            }
            addFlipStyles() {
                const st = document.createElement('style');
                st.textContent = `
            .flip{display:inline-block;transition:all .3s ease;transform-style:preserve-3d;transform-origin:bottom center;}
            .animate-flip{animation:flipAnimation .6s ease;}
            @keyframes flipAnimation{0%{transform:rotateX(0);opacity:1;}50%{transform:rotateX(90deg);opacity:0;}51%{transform:rotateX(-90deg);}100%{transform:rotateX(0);opacity:1;}}
            @keyframes continuousBlink{0%,100%{background-color:var(--highlight-color);}50%{background-color:var(--base-bg);}}
            .highlight-beep-direct{--highlight-color:var(--highlight-direct);--base-bg:var(--highlight-base);animation:continuousBlink 1s ease-in-out infinite;}
            .highlight-beep-stock{--highlight-color:var(--highlight-stock);--base-bg:var(--highlight-base);animation:continuousBlink 1s ease-in-out infinite;}
            .highlight-beep-direct td,.highlight-beep-stock td{background-color:inherit!important;}
          `;
                document.head.appendChild(st);
            }

            connect() {
                try {
                    if (this.eventSource) this.eventSource.close();
                    this.eventSource = new EventSource(`/stream/direct-pulling-updates?date=${this.currentDate}`);
                    this.updateConnectionStatus('connecting');
                    this.eventSource.onopen = () => this.updateConnectionStatus('connected');
                    this.eventSource.addEventListener('directPullingUpdate', e => {
                        try {
                            const data = JSON.parse(e.data);
                            if (data.date === this.currentDate) {
                                this.handleUpdates(data.updates || []);
                                this.updateConnectionStatus('connected');
                            }
                        } catch {
                            this.updateConnectionStatus('error', 'parse');
                        }
                    });
                    this.eventSource.onerror = () => {
                        this.updateConnectionStatus('disconnected');
                        this.reconnect();
                    };
                } catch {
                    this.updateConnectionStatus('error', 'EventSource');
                }
            }
            setupDateChangeListener() {
                const inp = document.querySelector('input[name="date"]');
                if (inp) {
                    inp.addEventListener('change', () => {
                        this.currentDate = this.getCurrentDate();
                        this.reconnect();
                    });
                }
            }
            updateConnectionStatus(status, msg = '') {
                const m = {
                    connecting: {
                        text: '● Connecting to updates...',
                        class: 'text-primary border bg-white'
                    },
                    connected: {
                        text: '● Live Updates Active',
                        class: 'text-success border bg-white'
                    },
                    disconnected: {
                        text: '● Connection Lost',
                        class: 'text-danger border bg-white'
                    },
                    error: {
                        text: '● Update Error ' + msg,
                        class: 'text-warning border bg-white'
                    }
                } [status] || {
                    text: '● Update Error',
                    class: 'text-warning border bg-white'
                };
                this.statusElement.className = m.class;
                this.statusElement.textContent = m.text;
            }
            reconnect() {
                if (this.eventSource) this.eventSource.close();
                setTimeout(() => this.connect(), 1500);
            }
            setupErrorHandling() {
                window.addEventListener('beforeunload', () => {
                    if (this.eventSource) this.eventSource.close();
                });
            }

            handleUpdates(updates) {
                updates.forEach(item => {
                    // kalau item termasuk ke ringkasan → adjust total
                    Object.values(this.summaries).forEach(s => {
                        if (!s?.ids) return;
                        if (s.ids.has(String(item.id))) {
                            const prev = s.ids.get(String(item.id)) || {
                                dp: 0,
                                sc: 0,
                                order: 0
                            };
                            const newDP = (item.direct_pulling_qty ?? prev.dp) | 0;
                            const newSC = (item.stock_chute_qty ?? prev.sc) | 0;
                            const newOD = (item.order_qty ?? prev.order) | 0;
                            s.totals.dp += (newDP - prev.dp);
                            s.totals.sc += (newSC - prev.sc);
                            s.totals.order += (newOD - prev.order);
                            s.ids.set(String(item.id), {
                                dp: newDP,
                                sc: newSC,
                                order: newOD
                            });
                            this._refreshSummaryRow(s);
                        }
                    });

                    // update baris normal yang masih di tabel
                    const selDP = `[data-item-id="${item.id}"][data-type="direct-pulling"]`;
                    const selSC = `[data-item-id="${item.id}"][data-type="stock-chute"]`;
                    if (document.querySelector(selDP) || document.querySelector(selSC)) {
                        this.updateQuantity(selDP, item.direct_pulling_qty, 'direct-pulling', item.order_qty);
                        this.updateQuantity(selSC, item.stock_chute_qty, 'stock-chute', item.order_qty);
                        this.updateQuantity(`[data-item-id="${item.id}"][data-type="actual_start"]`, item
                            .actual_start, 'time');
                        this.updateQuantity(`[data-item-id="${item.id}"][data-type="end"]`, item.end, 'time');
                        this.updateQuantity(`[data-item-id="${item.id}"][data-type="balance"]`, item.balance,
                            'time');
                    }
                });

                this.refreshSummaries();
            }

            updateQuantity(selector, newValue, type, targetQty = null) {
                document.querySelectorAll(selector).forEach(el => {
                    const cur = (el.textContent || '').trim();
                    if (cur !== String(newValue)) {
                        el.textContent = newValue ?? '';
                        const td = el.closest('td');

                        if (!isNaN(parseFloat(newValue))) this.updateCellStyle(td, parseFloat(newValue), type,
                            targetQty);
                        else this.updateCellStyle(td, null, type);

                        const bar = td?.querySelector('.qty-progress .bar > i');
                        if (bar && (type === 'direct-pulling' || type === 'stock-chute')) {
                            const row = td.parentElement;
                            const order = this._getOrder(row);
                            const dp = parseInt((row.querySelector('[data-type="direct-pulling"]')
                                ?.textContent || '0'), 10) || 0;
                            const sc = parseInt((row.querySelector('[data-type="stock-chute"]')?.textContent ||
                                '0'), 10) || 0;
                            const val = (type === 'direct-pulling') ? dp : sc;
                            const pct = Math.min(100, Math.round((val / Math.max(1, order)) * 100));
                            bar.style.width = pct + '%';

                            const totCell = row.querySelector('.total-progress');
                            if (totCell) {
                                const tBar = totCell.querySelector('.bar > i');
                                const tPct = totCell.querySelector('.val');
                                const totalVal = dp + sc;
                                const totalPct = Math.min(100, Math.round((totalVal / Math.max(1, order)) *
                                    100));
                                if (tBar) tBar.style.width = totalPct + '%';
                                if (tPct) tPct.textContent = totalPct + '%';
                            }
                        }

                        const f = td?.querySelector('.flip');
                        if (f) {
                            f.classList.add('animate-flip');
                            setTimeout(() => f.classList.remove('animate-flip'), 600);
                        }
                    }
                });
            }
            updateCellStyle(cell, val, type, targetQty = null) {
                if (!cell) return;
                if (type === 'time') return;
                if (val === null) {
                    cell.className = '';
                    return;
                }
                let cls = 'fw-bold ';
                if (type === 'direct-pulling' || type === 'stock-chute') {
                    if (targetQty !== null && !isNaN(targetQty)) {
                        cls += (val >= targetQty) ? 'bg-success bg-opacity-75 text-white' : 'bg-warning bg-opacity-75';
                    } else {
                        cls += (val > 0) ? 'bg-success bg-opacity-25' : 'bg-warning bg-opacity-25';
                    }
                }
                cell.className = cls.trim();
            }

            /* Kompat untuk pemanggilan eksternal */
            updateAllInlineSums() {
                this.refreshSummaries();
            }
        }

        /* Boot */
        document.addEventListener('DOMContentLoaded', () => {
            window.prodPlanSSE = new ProductionPlanSSEClient();
        });

        /* Navigasi tanggal (tetap) */
        function navigateDate(days) {
            const inp = document.querySelector('input[name="date"]');
            if (!inp) return;
            const d = new Date(inp.value);
            d.setDate(d.getDate() + days);
            inp.value = d.toISOString().split('T')[0];
            document.querySelector('form')?.submit();
        }

        function gotoToday() {
            const inp = document.querySelector('input[name="date"]');
            if (!inp) return;
            const iso = new Date().toISOString().split('T')[0];
            inp.value = iso;
            document.querySelector('form')?.submit();
        }

        /* Bridge untuk dropdown/presets eksternal bila ada */
        (function Bridge() {
            window.updateAllInlineSums = function() {
                if (window.prodPlanSSE && typeof window.prodPlanSSE.updateAllInlineSums === 'function') {
                    window.prodPlanSSE.updateAllInlineSums();
                }
            };
        })();
    </script>
    <script>
        /* ======================
                                                               IMPROVED COLUMN HIDE - SAFE MODE (V5, label-based)
                                                               - Hides TD via [data-label]
                                                               - Hides TH leaf via [data-col-key]
                                                               - Fixes group TH (Running Qty, Working Time) colspan
                                                               - Persists by label (stable, tahan rowspan/colspan)
                                                            ====================== */
        (function SafeColumnHideV5() {
            const STORAGE_PREFIX = 'hiddenCols_';
            const tableStates = new Map();
            let isProcessing = false;

            // 1) Canonical order (untuk normalisasi nama)
            const CANON = [
                'Customer', 'Dock', 'Cycle', 'Back No', 'Order',
                'Direct Pulling', 'Stock Chute', 'Cycle Time',
                'Planning Start', 'Actual Start', 'Duration', 'Progress',
                'Delivery Time', 'Delivery Date', 'Balance Time'
            ].map(s => s.toLowerCase());

            const GROUP_MAP = {
                'running qty': ['Direct Pulling', 'Stock Chute'],
                'working time': ['Planning Start', 'Actual Start', 'Duration', 'Progress']
            };

            const onceStyleId = 'colhide_label_style';
            if (!document.getElementById(onceStyleId)) {
                const st = document.createElement('style');
                st.id = onceStyleId;
                st.textContent = `.col-hidden{display:none!important}`;
                document.head.appendChild(st);
            }

            const norm = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();

            // Kembalikan bentuk judul kanonik (case yang rapi), jika ada
            function canonicalize(label) {
                const n = norm(label);
                const idx = CANON.indexOf(n);
                if (idx >= 0) return CANON[idx].replace(/\b\w/g, c => c.toUpperCase());
                // fallback: pakai yang ada
                return (label || '').trim();
            }

            // 2) Baca/simpan hidden set by KEY (label)
            function readHiddenKeys(tableKey) {
                try {
                    const arr = JSON.parse(localStorage.getItem(STORAGE_PREFIX + tableKey) || '[]');
                    return new Set(arr.map(canonicalize));
                } catch {
                    return new Set();
                }
            }

            function saveHiddenKeys(tableKey, set) {
                try {
                    localStorage.setItem(STORAGE_PREFIX + tableKey, JSON.stringify([...set]));
                } catch {}
            }

            // 3) Analisa thead → tentukan LEAF order kiri→kanan + tandai th
            function annotateHeader(container) {
                const thead = container.querySelector('thead');
                if (!thead) return {
                    leafKeys: [],
                    groupHeads: []
                };

                const rows = Array.from(thead.rows);
                // Dengan struktur yang kamu kirim, ada 2 baris
                const r0 = rows[0] || null;
                const r1 = rows[1] || null;

                const leafKeys = [];
                const groupHeads = []; // {el, children:[keys...]}

                if (!r0) return {
                    leafKeys,
                    groupHeads
                };

                // Siapkan pointer ke baris kedua untuk children group
                let childIdx = 0;
                const r1cells = r1 ? Array.from(r1.cells) : [];

                Array.from(r0.cells).forEach(th => {
                    const text = canonicalize(th.textContent);
                    const ntext = norm(text);

                    if ((th.rowSpan || 1) > 1 && (th.colSpan || 1) === 1) {
                        // Leaf di baris 1 (rowspan=2)
                        const key = text;
                        th.setAttribute('data-col-key', key);
                        leafKeys.push(key);
                    } else if ((th.colSpan || 1) > 1) {
                        // Group head: ambil anak di baris 2 sebanyak colspan
                        const span = th.colSpan;
                        const kids = [];
                        for (let i = 0; i < span; i++) {
                            const c = r1cells[childIdx++];
                            if (!c) continue;
                            const k = canonicalize(c.textContent);
                            c.setAttribute('data-col-key', k); // tandai leaf child
                            leafKeys.push(k);
                            kids.push(k);
                        }
                        // tandai group
                        const gChildren = (GROUP_MAP[ntext] || kids);
                        th.setAttribute('data-col-group', gChildren.join('||'));
                        groupHeads.push({
                            el: th,
                            children: gChildren
                        });
                    } else {
                        // edge case lain (jarang)
                        const key = text;
                        th.setAttribute('data-col-key', key);
                        leafKeys.push(key);
                    }
                });

                // Kalau ada baris kedua tanpa group (cadangan)
                if (r1 && groupHeads.length === 0) {
                    r1cells.forEach(c => {
                        const k = canonicalize(c.textContent);
                        c.setAttribute('data-col-key', k);
                        leafKeys.push(k);
                    });
                }

                return {
                    leafKeys,
                    groupHeads
                };
            }

            // 4) Terapkan hide/show ke BODY td dan HEADER th
            function applyHiding(container, hiddenKeys, headerInfo) {
                const {
                    leafKeys,
                    groupHeads
                } = headerInfo;

                // BODY: hide berdasarkan data-label (normalize dulu)
                container.querySelectorAll('tbody td[data-label]').forEach(td => {
                    const raw = td.getAttribute('data-label');
                    const key = canonicalize(raw);
                    if (hiddenKeys.has(key)) td.classList.add('col-hidden');
                    else td.classList.remove('col-hidden');
                });

                // THEAD leaf th
                container.querySelectorAll('thead th[data-col-key]').forEach(th => {
                    const key = th.getAttribute('data-col-key');
                    if (hiddenKeys.has(key)) th.classList.add('col-hidden');
                    else th.classList.remove('col-hidden');
                });

                // THEAD group th → hitung anak visible, update colspan / hide jika 0
                groupHeads.forEach(g => {
                    const visibleCount = g.children.reduce((n, k) => n + (hiddenKeys.has(canonicalize(k)) ? 0 :
                        1), 0);
                    if (visibleCount === 0) {
                        g.el.classList.add('col-hidden');
                        g.el.colSpan = 1; // aman
                    } else {
                        g.el.classList.remove('col-hidden');
                        g.el.colSpan = visibleCount;
                    }
                });
            }

            // 5) Setup per tabel
            document.querySelectorAll('[data-colpicker]').forEach(menu => {
                const tableKey = menu.getAttribute('data-colpicker');
                const container = document.querySelector(`[data-toggle-table="${tableKey}"]`);
                const table = container?.querySelector('table');
                if (!table) return;

                const headerInfo = annotateHeader(container);
                const hiddenKeys = readHiddenKeys(tableKey);

                tableStates.set(tableKey, {
                    container,
                    menu,
                    headerInfo,
                    hiddenKeys
                });

                // Sinkron checkbox:
                // - Preferred: <input class="column-check" data-key="Direct Pulling">
                // - Fallback:  <input class="column-check" data-col="index"> (pakai leaf order)
                menu.querySelectorAll('.column-check').forEach(cb => {
                    let key = cb.dataset.key ? canonicalize(cb.dataset.key) : null;
                    if (!key && cb.dataset.col != null) {
                        const idx = parseInt(cb.dataset.col, 10);
                        const k2 = headerInfo.leafKeys[idx];
                        if (k2) key = canonicalize(k2);
                    }
                    if (!key) return;

                    cb.checked = !hiddenKeys.has(key);
                    cb.addEventListener('change', e => {
                        e.stopPropagation();
                        if (cb.checked) hiddenKeys.delete(key);
                        else hiddenKeys.add(key);
                        saveHiddenKeys(tableKey, hiddenKeys);
                        applyHiding(container, hiddenKeys, headerInfo);
                        // optional: panggil recalcRowspans milik kamu jika perlu
                        if (window.prodPlanSSE?.recalcRowspans) {
                            window.prodPlanSSE.recalcRowspans(container);
                        }
                    });
                });

                // Apply awal
                applyHiding(container, hiddenKeys, headerInfo);
            });

            // 6) Global re-apply (mis. setelah DOM berubah oleh SSE)
            window.__colHideApplyAll = function() {
                if (isProcessing) return;
                isProcessing = true;
                try {
                    tableStates.forEach((state, tableKey) => {
                        const hiddenKeys = readHiddenKeys(tableKey);
                        state.hiddenKeys = hiddenKeys;

                        // sinkron checkbox
                        state.menu.querySelectorAll('.column-check').forEach(cb => {
                            let key = cb.dataset.key ? canonicalize(cb.dataset.key) : null;
                            if (!key && cb.dataset.col != null) {
                                const idx = parseInt(cb.dataset.col, 10);
                                const k2 = state.headerInfo.leafKeys[idx];
                                if (k2) key = canonicalize(k2);
                            }
                            if (!key) return;
                            cb.checked = !hiddenKeys.has(key);
                        });

                        applyHiding(state.container, hiddenKeys, state.headerInfo);
                        if (window.prodPlanSSE?.recalcRowspans) {
                            window.prodPlanSSE.recalcRowspans(state.container);
                        }
                    });
                } finally {
                    isProcessing = false;
                }
            };

            // 7) Observer ringan: jika tbody berubah, cukup re-apply
            try {
                const observer = new MutationObserver(() => {
                    if (!isProcessing) window.__colHideApplyAll();
                });
                document.querySelectorAll('[data-toggle-table]').forEach(container => {
                    const tbody = container.querySelector('table tbody');
                    if (tbody) observer.observe(tbody, {
                        childList: true,
                        subtree: false
                    });
                });
            } catch {}
        })();
    </script>
    <script>
        (function BackNoRenamer() {
            const LS_KEY = 'backnoRenameMap';
            const norm = s => (s || '').trim().toUpperCase();

            function loadMap() {
                try {
                    return JSON.parse(localStorage.getItem(LS_KEY) || '{}');
                } catch {
                    return {};
                }
            }

            function saveMap(map) {
                try {
                    localStorage.setItem(LS_KEY, JSON.stringify(map));
                } catch {}
            }

            function applyMapToContainer(container, map) {
                if (!container) return;
                container.querySelectorAll('tbody tr').forEach(row => {
                    const td = (function() {
                        const wl = 'Back No'.toLowerCase();
                        const cells = Array.from(row.children);
                        for (const c of cells) {
                            const lbl = (c.getAttribute('data-label') || '').replace(/\s+/g, ' ').trim()
                                .toLowerCase();
                            if (lbl === wl) return c;
                        }
                        return null;
                    })();
                    if (!td) return;

                    const el = td.querySelector('.flip') || td;
                    const original = norm(el.dataset.backnoRaw || el.textContent);
                    const alias = map[original];
                    if (alias) {
                        el.dataset.backnoAlias = norm(alias);
                        (td.querySelector('.flip') || el).textContent = alias;
                    } else {
                        // jika sebelumnya pernah di-alias, kembalikan ke raw
                        if (el.dataset.backnoAlias) {
                            (td.querySelector('.flip') || el).textContent = el.dataset.backnoRaw || el
                                .textContent;
                            delete el.dataset.backnoAlias;
                        }
                    }
                });
            }

            function applyAll(map) {
                document.querySelectorAll('[data-toggle-table]').forEach(container => {
                    applyMapToContainer(container, map);
                });
                // kalau perlu hitung ulang rowspan milikmu:
                if (window.prodPlanSSE?.recalcRowspans) {
                    document.querySelectorAll('[data-toggle-table]').forEach(c => window.prodPlanSSE.recalcRowspans(c));
                }
            }

            // ==== Public API ====
            window.setBackNoRenameMap = function(map, {
                persist = true,
                applyNow = true
            } = {}) {
                // normalisasi ke UPPERCASE key/value
                const clean = {};
                Object.entries(map || {}).forEach(([k, v]) => {
                    if (k && v) clean[norm(k)] = norm(v);
                });
                if (persist) saveMap(clean);
                if (applyNow) applyAll(clean);
                return clean;
            };

            window.renameBackNo = function(from, to, {
                persist = true,
                applyNow = true
            } = {}) {
                const map = loadMap();
                if (from && to) {
                    map[norm(from)] = norm(to);
                    if (persist) saveMap(map);
                }
                if (applyNow) applyAll(map);
                return map;
            };

            window.clearBackNoRenameMap = function({
                applyNow = true
            } = {}) {
                saveMap({});
                if (applyNow) applyAll({});
            };

            // Auto-apply saat halaman buka (jika ada mapping tersimpan)
            document.addEventListener('DOMContentLoaded', () => {
                const map = loadMap();
                if (Object.keys(map).length) applyAll(map);
            });
        })();

        setBackNoRenameMap({
            'D403': 'CI18',
            'D111': 'CI12',
            'D500': 'CI19'
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
