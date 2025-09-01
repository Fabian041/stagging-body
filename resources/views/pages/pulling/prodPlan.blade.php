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
   Light Mode Table Header — Soft Slate
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
                                        <th colspan="2">Running Qty</th>
                                        <th rowspan="2">Cycle Time</th>
                                        <th colspan="4">Working Time</th>
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
                                        <th colspan="2">Running Qty</th>
                                        <th rowspan="2">Cycle Time</th>
                                        <th colspan="4">Working Time</th>
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
                                   THEME TOGGLE
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
           SSE CLIENT + INLINE SUMS
           - AS004: sum Back No = CI19 → tampil 1 baris, Order = total
           - AS003: sum Back No = D112 → tampil 1 baris, Back No ditulis CI12, Order = total
           ====================== */
        class ProductionPlanSSEClient {
            constructor() {
                this.eventSource = null;
                this.statusElement = null;
                this.currentDate = this.getCurrentDate();
                this.highlightTimeouts = new Set();
                this.originalOrder = new Map();
                this.orderRestoreTimeouts = new Map();
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

                this.updateAllInlineSums();
            }

            /* ===== Utilities: group & rowspan ===== */
            isGroupStart(row) {
                return !!row.querySelector('[rowspan]');
            }
            getGroupRowsFrom(startRow) {
                const rows = [startRow];
                let p = startRow;
                while (p.nextElementSibling && !this.isGroupStart(p.nextElementSibling)) {
                    p = p.nextElementSibling;
                    rows.push(p);
                }
                return rows;
            }
            findGroupStart(row) {
                let p = row;
                while (p && !this.isGroupStart(p)) p = p.previousElementSibling;
                return p || row;
            }
            recalcRowspans(container) {
                const tbody = container?.querySelector('tbody');
                if (!tbody) return;
                Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                    if (!this.isGroupStart(row)) return;
                    const groupRows = this.getGroupRowsFrom(row);
                    const visibleCount = Math.max(1, groupRows.filter(r => r.style.display !== 'none').length);
                    row.querySelectorAll('[rowspan]').forEach(td => td.rowSpan = visibleCount);
                });
            }

            /* Stable: CLONE sel-rowspan ke baris berikutnya (bukan dipindah), lalu hide baris start */
            hideGroupStartRow(row) {
                const next = row.nextElementSibling;
                if (!next || this.isGroupStart(next)) {
                    row.style.display = 'none';
                    return;
                }

                const startCells = Array.from(row.children).filter(td => td.hasAttribute('rowspan'))
                    .sort((a, b) => a.cellIndex - b.cellIndex);

                startCells.forEach(td => {
                    const clone = td.cloneNode(true);
                    const span = parseInt(td.getAttribute('rowspan') || '1', 10);
                    clone.setAttribute('rowspan', Math.max(1, span - 1));

                    const targetIdx = td.cellIndex;
                    const nextCells = Array.from(next.children);
                    let ref = null;
                    for (let i = 0; i < nextCells.length; i++)
                        if (nextCells[i].cellIndex > targetIdx) {
                            ref = nextCells[i];
                            break;
                        }
                    next.insertBefore(clone, ref);
                });

                row.style.display = 'none';
            }

            /* ===== Scanner & renderer ===== */
            _scanTableBackno(container, targetBackNo) {
                const tgt = String(targetBackNo).toUpperCase();
                const tbody = container?.querySelector('tbody');
                const rows = [];
                let sum = 0;
                let firstRow = null;
                if (!tbody) return {
                    rows,
                    firstRow,
                    sum
                };

                Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                    const flip = row.querySelector('[data-label="Back No"] .flip');
                    if (!flip) return;
                    const text = (flip.dataset.backnoRaw || flip.textContent || '').trim().toUpperCase();
                    if (text === tgt) {
                        rows.push(row);
                        const ordFlip = row.querySelector('[data-label="Order"] .flip');
                        const ordText = ordFlip?.textContent?.trim() || '0';
                        const ord = parseInt(String(ordText).replace(/[^\d-]/g, ''), 10) || 0;
                        sum += ord;
                        if (!firstRow) firstRow = row;
                    }
                });
                return {
                    rows,
                    firstRow,
                    sum
                };
            }

            _renderSingleSum({
                container,
                targetBackNo,
                displayBackNo = null
            }) {
                const {
                    rows: bnRows,
                    firstRow,
                    sum
                } = this._scanTableBackno(container, targetBackNo);

                if (firstRow) {
                    firstRow.style.display = '';

                    // Order → total (simpan angka asli utk progress via data-order-raw)
                    const orderFlip = firstRow.querySelector('[data-label="Order"] .flip');
                    if (orderFlip) {
                        const originalOrd = parseInt(String(orderFlip.textContent || '0').replace(/[^\d-]/g, ''), 10) ||
                            0;
                        orderFlip.dataset.orderRaw = String(originalOrd);
                        orderFlip.textContent = (sum || 0).toLocaleString('id-ID');
                    }

                    // Back No → rename display bila diminta
                    if (displayBackNo) {
                        const bnFlip = firstRow.querySelector('[data-label="Back No"] .flip');
                        if (bnFlip) {
                            bnFlip.dataset.backnoRaw = targetBackNo;
                            bnFlip.textContent = displayBackNo;
                        }
                    }
                }

                // Sembunyikan duplikat lain (start/non-start aman)
                bnRows.forEach((row, idx) => {
                    if (idx === 0) return;
                    if (this.isGroupStart(row)) this.hideGroupStartRow(row);
                    else {
                        row.style.display = 'none';
                        const start = this.findGroupStart(row);
                        start.querySelectorAll('[rowspan]').forEach(td => td.rowSpan = Math.max(1, td.rowSpan -
                            1));
                    }
                });

                this.recalcRowspans(container);
            }

            updateAllInlineSums() {
                // AS004 → CI19
                if (this.AS004) this._renderSingleSum({
                    container: this.AS004,
                    targetBackNo: 'D500',
                    displayBackNo: 'CI19'
                });

                // AS003 → D112 (rename jadi CI12)
                if (this.AS003) this._renderSingleSum({
                    container: this.AS003,
                    targetBackNo: 'D111',
                    displayBackNo: 'CI12'
                });
            }

            /* ===== SSE & UI ===== */
            storeOriginalOrder() {
                document.querySelectorAll('.tab-pane table tbody').forEach(tbody => {
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    this.originalOrder.set(tbody, rows);
                });
            }

            getCurrentDate() {
                const dateInput = document.querySelector('input[name="date"]');
                return dateInput ? dateInput.value : new Date().toISOString().split('T')[0];
            }

            createStatusIndicator() {
                this.statusElement = document.createElement('div');
                this.statusElement.id = 'sse-connection-status';
                this.statusElement.textContent = '● Connecting to updates...';
                document.body.appendChild(this.statusElement);
            }

            addFlipStyles() {
                const style = document.createElement('style');
                style.textContent = `
              .flip{display:inline-block;transition:all .3s ease;transform-style:preserve-3d;transform-origin:bottom center;}
              .animate-flip{animation:flipAnimation .6s ease;}
              @keyframes flipAnimation{0%{transform:rotateX(0);opacity:1;}50%{transform:rotateX(90deg);opacity:0;}51%{transform:rotateX(-90deg);}100%{transform:rotateX(0);opacity:1;}}
              @keyframes continuousBlink{0%,100%{background-color:var(--highlight-color);}50%{background-color:var(--base-bg);}}
              .highlight-beep-direct{--highlight-color:var(--highlight-direct);--base-bg:var(--highlight-base);animation:continuousBlink 1s ease-in-out infinite;}
              .highlight-beep-stock{--highlight-color:var(--highlight-stock);--base-bg:var(--highlight-base);animation:continuousBlink 1s ease-in-out infinite;}
              .highlight-beep-direct td,.highlight-beep-stock td{background-color:inherit!important;}
            `;
                document.head.appendChild(style);
            }

            connect() {
                if (this.eventSource) this.eventSource.close();
                this.eventSource = new EventSource(`/stream/direct-pulling-updates?date=${this.currentDate}`);
                this.updateConnectionStatus('connecting');
                this.eventSource.onopen = () => this.updateConnectionStatus('connected');

                this.eventSource.addEventListener('directPullingUpdate', (e) => {
                    const data = JSON.parse(e.data);
                    if (data.date === this.currentDate) {
                        this.handleUpdates(data.updates || []);
                        this.updateConnectionStatus('connected');
                    }
                });

                this.eventSource.onerror = () => {
                    this.updateConnectionStatus('disconnected');
                    this.reconnect();
                };
            }

            setupDateChangeListener() {
                const dateInput = document.querySelector('input[name="date"]');
                if (dateInput) {
                    dateInput.addEventListener('change', () => {
                        this.currentDate = this.getCurrentDate();
                        this.reconnect();
                    });
                }
            }

            updateConnectionStatus(status, message = '') {
                const statusMap = {
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
                        text: '● Update Error ' + message,
                        class: 'text-warning border bg-white'
                    },
                };
                const s = statusMap[status] || statusMap.error;
                this.statusElement.className = s.class;
                this.statusElement.textContent = s.text;
            }

            _getOrderForCalc(row) {
                const flip = row.querySelector('[data-label="Order"] .flip');
                if (!flip) return 0;
                const raw = parseInt(flip.dataset.orderRaw || '', 10);
                if (!isNaN(raw)) return raw;
                return parseInt(String(flip.textContent || '0').replace(/[^\d-]/g, ''), 10) || 0;
            }

            handleUpdates(updates) {
                const rowsToProcess = new Set();
                updates.forEach(item => {
                    const hasAny =
                        document.querySelector(`[data-item-id="${item.id}"][data-type="direct-pulling"]`) ||
                        document.querySelector(`[data-item-id="${item.id}"][data-type="stock-chute"]`);
                    if (!hasAny) return;

                    this.updateQuantity(`[data-item-id="${item.id}"][data-type="direct-pulling"]`,
                        item.direct_pulling_qty, 'direct-pulling', item.order_qty);
                    this.updateQuantity(`[data-item-id="${item.id}"][data-type="stock-chute"]`,
                        item.stock_chute_qty, 'stock-chute', item.order_qty);
                    this.updateQuantity(`[data-item-id="${item.id}"][data-type="actual_start"]`,
                        item.actual_start, 'time');
                    this.updateQuantity(`[data-item-id="${item.id}"][data-type="end"]`,
                        item.end, 'time');
                    this.updateQuantity(`[data-item-id="${item.id}"][data-type="balance"]`,
                        item.balance, 'time');

                    document.querySelectorAll(`tr:has([data-item-id="${item.id}"])`).forEach(r => rowsToProcess
                        .add(r));
                });

                if (rowsToProcess.size > 0) this.processUpdatedRows(Array.from(rowsToProcess));

                this.updateAllInlineSums();
            }

            processUpdatedRows(rows) {
                const rowGroups = new Map();
                rows.forEach(row => {
                    let groupStart = row;
                    while (groupStart.previousElementSibling && groupStart.previousElementSibling.querySelector(
                            '[rowspan]')) {
                        groupStart = groupStart.previousElementSibling;
                    }
                    const rs = parseInt(groupStart.querySelector('[rowspan]')?.getAttribute('rowspan')) || 1;
                    const group = [groupStart];
                    let walker = groupStart;
                    for (let i = 1; i < rs; i++) {
                        if (walker.nextElementSibling) {
                            group.push(walker.nextElementSibling);
                            walker = walker.nextElementSibling;
                        }
                    }
                    if (!rowGroups.has(groupStart)) rowGroups.set(groupStart, new Set(group));
                    else group.forEach(r => rowGroups.get(groupStart).add(r));
                });

                const tablesProcessed = new Set();
                for (const [groupStart] of rowGroups) {
                    const tbody = groupStart.closest('tbody');
                    if (!tbody || tablesProcessed.has(tbody)) continue;
                    tablesProcessed.add(tbody);

                    if (this.orderRestoreTimeouts.has(tbody)) {
                        clearTimeout(this.orderRestoreTimeouts.get(tbody));
                        this.orderRestoreTimeouts.delete(tbody);
                    }

                    const allRows = Array.from(tbody.querySelectorAll('tr'));
                    const allGroups = [];
                    let cur = allRows[0];
                    while (cur) {
                        const rs = parseInt(cur.querySelector('[rowspan]')?.getAttribute('rowspan') || '1');
                        const g = [cur];
                        let w = cur;
                        for (let i = 1; i < rs && w.nextElementSibling; i++) {
                            g.push(w.nextElementSibling);
                            w = w.nextElementSibling;
                        }
                        allGroups.push(g);
                        cur = w?.nextElementSibling;
                    }

                    const updatedGroups = allGroups.filter(g => g.some(r => rows.includes(r)));

                    if (updatedGroups.length > 0) {
                        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
                        const remaining = allGroups.filter(g => !updatedGroups.includes(g));
                        updatedGroups.forEach(g => g.forEach(r => tbody.appendChild(r)));
                        remaining.forEach(g => g.forEach(r => tbody.appendChild(r)));
                        rows.forEach(r => {
                            if (tbody.contains(r)) this.highlightRow(r, 'mixed');
                        });

                        const restoreTimeout = setTimeout(() => {
                            this.restoreOriginalOrder(tbody);
                            this.orderRestoreTimeouts.delete(tbody);
                            this.updateAllInlineSums();
                        }, 60000);
                        this.orderRestoreTimeouts.set(tbody, restoreTimeout);
                    }
                }
            }

            restoreOriginalOrder(tbody) {
                if (!this.originalOrder.has(tbody)) return;
                const originalRows = this.originalOrder.get(tbody);
                const currentRows = Array.from(tbody.querySelectorAll('tr'));
                if (originalRows.length !== currentRows.length) return;
                while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
                originalRows.forEach(r => tbody.appendChild(r));
            }

            highlightRow(row, type) {
                row.classList.remove('highlight-beep-direct', 'highlight-beep-stock');
                void row.offsetWidth;
                const cls = (type === 'success') ? 'highlight-beep-direct' :
                    (type === 'warning') ? 'highlight-beep-stock' : 'highlight-beep-direct';
                row.classList.add(cls);
                const t = setTimeout(() => {
                    row.classList.remove(cls);
                    this.highlightTimeouts.delete(t);
                }, 60000);
                this.highlightTimeouts.add(t);
            }

            updateQuantity(selector, newValue, type, targetQty = null) {
                document.querySelectorAll(selector).forEach(el => {
                    const cur = el.textContent.trim();
                    if (cur !== String(newValue)) {
                        el.textContent = newValue;
                        const td = el.closest('td');

                        if (!isNaN(parseFloat(newValue))) this.updateCellStyle(td, parseFloat(newValue), type,
                            targetQty);
                        else this.updateCellStyle(td, null, type);

                        const bar = td?.querySelector('.qty-progress .bar > i');
                        if (bar && (type === 'direct-pulling' || type === 'stock-chute')) {
                            const row = td.parentElement;
                            const order = this._getOrderForCalc(row);

                            const dpEl = row.querySelector('[data-type="direct-pulling"]');
                            const scEl = row.querySelector('[data-type="stock-chute"]');
                            const dp = parseInt((dpEl?.textContent || '0'), 10) || 0;
                            const sc = parseInt((scEl?.textContent || '0'), 10) || 0;

                            const val = (type === 'direct-pulling') ? dp : sc;
                            const pct = Math.min(100, Math.round((val / Math.max(1, order)) * 100));
                            bar.style.width = pct + '%';

                            const totalCell = row.querySelector('.total-progress');
                            if (totalCell) {
                                const totalBar = totalCell.querySelector('.bar > i');
                                const totalPctEl = totalCell.querySelector('.val');
                                const totalVal = dp + sc;
                                const totalPct = Math.min(100, Math.round((totalVal / Math.max(1, order)) *
                                    100));
                                if (totalBar) totalBar.style.width = totalPct + '%';
                                if (totalPctEl) totalPctEl.textContent = totalPct + '%';
                            }
                        }

                        this.animateChange(td);
                    }
                });
            }

            updateCellStyle(cell, value, type, targetQty = null) {
                if (!cell) return;
                if (type === 'time') return;
                if (value === null) {
                    cell.className = '';
                    return;
                }
                let classes = 'fw-bold ';
                if (type === 'direct-pulling' || type === 'stock-chute') {
                    if (targetQty !== null && !isNaN(targetQty)) classes += (value >= targetQty) ?
                        'bg-success bg-opacity-75 text-white' : 'bg-warning bg-opacity-75';
                    else classes += (value > 0) ? 'bg-success bg-opacity-25' : 'bg-warning bg-opacity-25';
                }
                cell.className = classes.trim();
            }

            animateChange(td) {
                const f = td?.querySelector('.flip');
                if (f) {
                    f.classList.add('animate-flip');
                    setTimeout(() => f.classList.remove('animate-flip'), 600);
                }
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
        }

        document.addEventListener('DOMContentLoaded', () => {
            window.prodPlanSSE = new ProductionPlanSSEClient();

            const triggers = [].slice.call(document.querySelectorAll('[title]'));
            triggers.forEach(el => {
                el.setAttribute('data-bs-toggle', 'tooltip');
                new bootstrap.Tooltip(el);
            });
        });

        function navigateDate(days) {
            const inp = document.querySelector('input[name="date"]');
            const currentDate = new Date(inp.value);
            currentDate.setDate(currentDate.getDate() + days);
            inp.value = currentDate.toISOString().split('T')[0];
            document.querySelector('form').submit();
        }

        function gotoToday() {
            const inp = document.querySelector('input[name="date"]');
            const iso = new Date().toISOString().split('T')[0];
            inp.value = iso;
            document.querySelector('form').submit();
        }

        /* ======================
           Column Dropdown (rowspan-aware)
           ====================== */
        (function ColumnDropdown() {
            const STORAGE_PREFIX = 'hiddenCols_';
            document.querySelectorAll('[data-colpicker]').forEach(menu => {
                const tableKey = menu.getAttribute('data-colpicker');
                const container = document.querySelector(`[data-toggle-table="${tableKey}"]`);
                const table = container?.querySelector('table');
                if (!table) return;

                const meta = buildMatrix(table);
                const hidden = new Set(JSON.parse(localStorage.getItem(STORAGE_PREFIX + tableKey) || '[]'));
                applyHidden(meta, hidden);

                menu.querySelectorAll('.column-check').forEach(cb => {
                    const idx = parseInt(cb.dataset.col, 10);
                    cb.checked = !hidden.has(idx);
                    cb.addEventListener('change', () => {
                        if (cb.checked) hidden.delete(idx);
                        else hidden.add(idx);
                        localStorage.setItem(STORAGE_PREFIX + tableKey, JSON.stringify([...
                            hidden
                        ]));
                        applyHidden(meta, hidden);
                    });
                });

                window.resetColumns = function(key) {
                    if (key !== tableKey) return;
                    hidden.clear();
                    localStorage.setItem(STORAGE_PREFIX + tableKey, JSON.stringify([]));
                    menu.querySelectorAll('.column-check').forEach(c => c.checked = true);
                    applyHidden(meta, hidden);
                }
            });

            function buildMatrix(table) {
                const rows = Array.from(table.rows);
                const matrix = [];
                let maxCols = 0;
                for (let r = 0; r < rows.length; r++) {
                    if (!matrix[r]) matrix[r] = [];
                    let col = 0;
                    for (const cell of Array.from(rows[r].cells)) {
                        while (matrix[r][col]) col++;
                        cell._origColspan = cell._origColspan || cell.colSpan || 1;
                        cell._origRowspan = cell._origRowspan || cell.rowSpan || 1;
                        cell._startCol = (cell._startCol === undefined) ? col : cell._startCol;
                        for (let rr = 0; rr < cell._origRowspan; rr++) {
                            if (!matrix[r + rr]) matrix[r + rr] = [];
                            for (let cc = 0; cc < cell._origColspan; cc++) matrix[r + rr][col + cc] = cell;
                        }
                        col += cell._origColspan;
                    }
                    if (col > maxCols) maxCols = col;
                }
                return {
                    matrix,
                    maxCols,
                    rows
                };
            }

            function applyHidden(meta, hidden) {
                const {
                    matrix,
                    maxCols
                } = meta;
                const unique = new Set();
                for (let r = 0; r < matrix.length; r++)
                    for (let c = 0; c < maxCols; c++) {
                        const cell = matrix[r][c];
                        if (cell) unique.add(cell);
                    }

                unique.forEach(cell => {
                    const start = cell._startCol,
                        span = cell._origColspan;
                    let visible = 0;
                    for (let k = 0; k < span; k++)
                        if (!hidden.has(start + k)) visible++;
                    cell.style.display = (visible === 0) ? 'none' : '';
                    cell.colSpan = span > 1 ? Math.max(1, visible) : 1;
                    cell.rowSpan = cell._origRowspan;
                });

                if (window.__recalcStickyHeaders) window.__recalcStickyHeaders();
            }
        })();

        /* ======================
           Preset: Risk first + Save view
           ====================== */
        const VIEW_KEY_PREFIX = 'view_';

        function parseBalanceHour(text) {
            if (!text || text === '--') return Infinity;
            const [h, m] = String(text).split(':').map(x => parseInt(x || '0', 10));
            return isNaN(h) ? Infinity : h + (m / 60);
        }

        function groupRowsByRowspan(tbody) {
            const allRows = Array.from(tbody.querySelectorAll('tr'));
            const groups = [];
            let i = 0;
            while (i < allRows.length) {
                const start = allRows[i];
                const rsCell = start.querySelector('[rowspan]');
                const rs = parseInt(rsCell?.getAttribute('rowspan') || '1', 10);
                const bundle = [start];
                for (let k = 1; k < rs && (i + k) < allRows.length; k++) bundle.push(allRows[i + k]);
                groups.push(bundle);
                i += rs;
            }
            return groups;
        }

        function applyPreset(tableKey, preset) {
            const container = document.querySelector(`[data-toggle-table="${tableKey}"]`);
            const tbody = container?.querySelector('tbody');
            if (!tbody) return;

            if (preset === 'default') {
                if (window.prodPlanSSE?.originalOrder?.has(tbody)) window.prodPlanSSE.restoreOriginalOrder(tbody);
                localStorage.removeItem(VIEW_KEY_PREFIX + tableKey);
                if ((tableKey === 'AS004' || tableKey === 'AS003') && window.prodPlanSSE?.updateAllInlineSums) window
                    .prodPlanSSE.updateAllInlineSums();
                return;
            }

            if (preset === 'risk') {
                const groups = groupRowsByRowspan(tbody);
                groups.sort((a, b) => {
                    const aBal = a[0].querySelector('[data-type="balance"]')?.textContent?.trim();
                    const bBal = b[0].querySelector('[data-type="balance"]')?.textContent?.trim();
                    const ah = parseBalanceHour(aBal),
                        bh = parseBalanceHour(bBal);
                    return ah - bh;
                });
                const frag = document.createDocumentFragment();
                groups.forEach(g => g.forEach(r => frag.appendChild(r)));
                tbody.innerHTML = '';
                tbody.appendChild(frag);
                localStorage.setItem(VIEW_KEY_PREFIX + tableKey, JSON.stringify({
                    preset: 'risk'
                }));
                if ((tableKey === 'AS004' || tableKey === 'AS003') && window.prodPlanSSE?.updateAllInlineSums) window
                    .prodPlanSSE.updateAllInlineSums();
            }
        }

        function saveCurrentView(tableKey) {
            localStorage.setItem(VIEW_KEY_PREFIX + tableKey, JSON.stringify({
                preset: 'custom'
            }));
            alert('Current view saved for ' + tableKey);
        }
        document.addEventListener('DOMContentLoaded', () => {
            ['AS003', 'AS004'].forEach(k => {
                const saved = localStorage.getItem(VIEW_KEY_PREFIX + k);
                if (!saved) return;
                const view = JSON.parse(saved);
                if (view.preset === 'risk') applyPreset(k, 'risk');
            });
        });
    </script>

    <script>
        (function stickyHeaderOffsets() {
            function updateStickyOffsets(table) {
                if (!table || !table.tHead || table.tHead.rows.length < 2) return;
                const r1 = table.tHead.rows[0].getBoundingClientRect().height || 40;
                table.style.setProperty('--thead-row1', `${r1.toFixed(2)}px`);
            }

            function updateAll() {
                document.querySelectorAll('[data-toggle-table] table').forEach(updateStickyOffsets);
            }
            document.addEventListener('DOMContentLoaded', updateAll);
            window.addEventListener('resize', updateAll);
            const ro = new ResizeObserver(entries => {
                for (const e of entries) {
                    const table = e.target.closest('table');
                    if (table) updateStickyOffsets(table);
                }
            });
            document.querySelectorAll('[data-toggle-table] table thead').forEach(th => ro.observe(th));
            window.__recalcStickyHeaders = updateAll;
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
