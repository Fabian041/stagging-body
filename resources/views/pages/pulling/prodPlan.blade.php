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

        .strip-stat .kpi-mini {
            margin-top: .2rem;
            min-width: 240px
        }

        .strip-stat .kpi-mini .qty-progress .bar {
            height: 6px
        }

        /* lebih tipis */
        .strip-stat .kpi-mini .qty-progress .val {
            min-width: 48px;
            font-size: .8rem
        }

        .strip-stat .kpi-mini .meta {
            font-size: .8rem;
            color: var(--muted)
        }

        /* teks kecil */

        /* ======================
   MODAL STYLES
   ====================== */
        html[data-theme="dark"] .modal-content {
            background: var(--surface);
            border-color: var(--border);
            color: white;
        }

        html[data-theme="dark"] .modal-header {
            border-bottom-color: var(--border);
        }

        html[data-theme="dark"] .modal-footer {
            border-top-color: var(--border);
        }

        .summary-stat {
            text-align: center;
            padding: 1rem;
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-subtle) 100%);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .summary-stat .number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--brand-primary);
            display: block;
        }

        .summary-stat .label {
            font-size: .85rem;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .back-number-item {
            padding: .75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            margin-bottom: .5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            transition: all .2s ease;
        }

        .back-number-item:hover {
            background: var(--table-hover);
            border-color: var(--brand-primary);
        }

        .back-number-item .back-no {
            font-weight: 700;
            font-size: 1rem;
            color: var(--ink);
        }

        .back-number-item .order-qty {
            font-weight: 600;
            font-size: .95rem;
            padding: .25rem .5rem;
            background: var(--chip-bg);
            color: var(--chip-ink);
            border: 1px solid var(--chip-border);
            border-radius: 4px;
        }

        /* Responsive modal width */
        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 95% !important;
                /* hampir full screen di mobile */
                margin: 0.5rem auto;
            }

            .summary-stat {
                padding: 0.75rem;
            }

            .summary-stat .number {
                font-size: 1.5rem;
            }

            .summary-stat .label {
                font-size: 0.75rem;
            }
        }

        /* Biar kolom statistik rapi jadi 2 kolom di tablet & 1 kolom di hp */
        @media (max-width: 992px) {
            .summary-stat {
                margin-bottom: 1rem;
            }
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

                        $as003MorningActual = $grouped['AS003']['morning_shift_actual'] ?? 0;
                        $as003NightActual = $grouped['AS003']['night_shift_actual'] ?? 0;
                        $as003TotalActual = $grouped['AS003']['total_actual'] ?? 0;

                        $as003MorningPct = $as003MorningQty
                            ? min(100, round(($as003MorningActual / $as003MorningQty) * 100))
                            : 0;
                        $as003NightPct = $as003NightQty
                            ? min(100, round(($as003NightActual / $as003NightQty) * 100))
                            : 0;
                        $as003TotalPct = $as003TotalQty
                            ? min(100, round(($as003TotalActual / $as003TotalQty) * 100))
                            : 0;

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

                    <!-- AS003 CARD -->
                    <div class="card mb-3 radius-4" data-shift-card="AS003">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3">
                            <!-- MORNING -->
                            <div class="strip-stat" data-line="AS003" data-shift="morning">
                                <div class="title">Morning Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-primary">
                                        <span data-role="shift-order">{{ $as003MorningQty }}</span>
                                    </div>
                                    @if ($as003MorningStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-warning-subtle border text-dark fw-bolder">{{ $as003MorningStatus }}</span>
                                    @endif
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as003MorningActual }} / {{ $as003MorningQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as003MorningPct }}%"></i></div>
                                        <span class="val" data-role="shift-pct">{{ $as003MorningPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as003MorningActual }}</span></div>
                                </div>
                            </div>

                            <!-- NIGHT -->
                            <div class="strip-stat" data-line="AS003" data-shift="night">
                                <div class="title">Night Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-success">
                                        <span data-role="shift-order">{{ $as003NightQty }}</span>
                                    </div>
                                    @if ($as003NightStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-danger-subtle border text-dark fw-bolder">{{ $as003NightStatus }}</span>
                                    @endif
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as003NightActual }} / {{ $as003NightQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as003NightPct }}%"></i></div>
                                        <span class="val" data-role="shift-pct">{{ $as003NightPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as003NightActual }}</span></div>
                                </div>
                            </div>

                            <!-- TOTAL -->
                            <div class="ms-auto strip-stat" data-line="AS003" data-shift="total">
                                <div class="title">Total</div>
                                <div class="value"><span data-role="shift-order">{{ $as003TotalQty }}</span></div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as003TotalActual }} / {{ $as003TotalQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as003TotalPct }}%"></i></div>
                                        <span class="val" data-role="shift-pct">{{ $as003TotalPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as003TotalActual }}</span></div>
                                </div>
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

                        <button class="btn btn-outline-success btn-sm" onclick="showSummary('AS003')">
                            <i class="fas fa-list-ol me-1"></i> Summary
                        </button>

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

                                                $startHour = null;
                                                $endHour = null;
                                                try {
                                                    $startHour = $item->working_start
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_start,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                try {
                                                    $endHour = $item->working_end
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_end,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                $isMorning =
                                                    ($startHour !== null && $startHour >= 6 && $startHour < 14) ||
                                                    ($endHour !== null && $endHour >= 6 && $endHour < 14);
                                                $isNight =
                                                    ($startHour !== null && ($startHour >= 22 || $startHour < 6)) ||
                                                    ($endHour !== null && ($endHour >= 22 || $endHour < 6));
                                                $rowShift = $isMorning ? 'morning' : ($isNight ? 'night' : 'other');
                                            @endphp
                                            <tr data-shift="{{ $rowShift }}">
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

                        $as004MorningActual = $grouped['AS004']['morning_shift_actual'] ?? 0;
                        $as004NightActual = $grouped['AS004']['night_shift_actual'] ?? 0;
                        $as004TotalActual = $grouped['AS004']['total_actual'] ?? 0;

                        $as004MorningPct = $as004MorningQty
                            ? min(100, round(($as004MorningActual / $as004MorningQty) * 100))
                            : 0;
                        $as004NightPct = $as004NightQty
                            ? min(100, round(($as004NightActual / $as004NightQty) * 100))
                            : 0;
                        $as004TotalPct = $as004TotalQty
                            ? min(100, round(($as004TotalActual / $as004TotalQty) * 100))
                            : 0;

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

                    <div class="card mb-3 radius-4" data-shift-card="AS004">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3">
                            <!-- MORNING -->
                            <div class="strip-stat" data-line="AS004" data-shift="morning">
                                <div class="title">Morning Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-primary">
                                        <span data-role="shift-order">{{ $as004MorningQty }}</span>
                                    </div>
                                    @if ($as004MorningStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-warning-subtle border text-dark fw-bolder">{{ $as004MorningStatus }}</span>
                                    @endif
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as004MorningActual }} / {{ $as004MorningQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as004MorningPct }}%"></i></div>
                                        <span class="val" data-role="shift-pct">{{ $as004MorningPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as004MorningActual }}</span></div>
                                </div>
                            </div>

                            <!-- NIGHT -->
                            <div class="strip-stat" data-line="AS004" data-shift="night">
                                <div class="title">Night Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-success">
                                        <span data-role="shift-order">{{ $as004NightQty }}</span>
                                    </div>
                                    @if ($as004NightStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-danger-subtle border text-dark fw-bolder">{{ $as004NightStatus }}</span>
                                    @endif
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as004NightActual }} / {{ $as004NightQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as004NightPct }}%"></i></div>
                                        <span class="val" data-role="shift-pct">{{ $as004NightPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as004NightActual }}</span></div>
                                </div>
                            </div>

                            <!-- TOTAL -->
                            <div class="ms-auto strip-stat" data-line="AS004" data-shift="total">
                                <div class="title">Total</div>
                                <div class="value"><span data-role="shift-order">{{ $as004TotalQty }}</span></div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as004TotalActual }} / {{ $as004TotalQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as004TotalPct }}%"></i></div>
                                        <span class="val" data-role="shift-pct">{{ $as004TotalPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as004TotalActual }}</span></div>
                                </div>
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

                        <button class="btn btn-outline-success btn-sm" onclick="showSummary('AS004')">
                            <i class="fas fa-list-ol me-1"></i> Summary
                        </button>

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

                                                $startHour = null;
                                                $endHour = null;
                                                try {
                                                    $startHour = $item->working_start
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_start,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                try {
                                                    $endHour = $item->working_end
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_end,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                $isMorning =
                                                    ($startHour !== null && $startHour >= 6 && $startHour < 14) ||
                                                    ($endHour !== null && $endHour >= 6 && $endHour < 14);
                                                $isNight =
                                                    ($startHour !== null && ($startHour >= 22 || $startHour < 6)) ||
                                                    ($endHour !== null && ($endHour >= 22 || $endHour < 6));
                                                $rowShift = $isMorning ? 'morning' : ($isNight ? 'night' : 'other');
                                            @endphp
                                            <tr data-shift="{{ $rowShift }}">
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

    <!-- Order Summary Modal -->
    <!-- Summary Modal -->
    <div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="summaryModalLabel">
                        <i class="fas fa-chart-bar me-2"></i>
                        Order Summary by Back Number - <span id="modalLineTitle">AS003</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="totalBackNumbers">0</span>
                                <div class="label">Total Model</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="totalOrders">0</span>
                                <div class="label">Total Orders</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="avgOrderPerBack">0</span>
                                <div class="label">Avg per Back No</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="completedOrders">0</span>
                                <div class="label">Complete Order</div>
                            </div>
                        </div>
                    </div>


                    <!-- Back Number List -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary text-uppercase mb-3" style="letter-spacing:.5px;">
                            Order Details by Back Number
                        </h6>
                        <div id="backNumberList"><!-- populated by JS --></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="exportSummary()">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            if (window.$u) return;

            const normLabel = s => String(s || '').replace(/\s+/g, ' ').trim().toLowerCase();
            const normUpper = s => String(s || '').trim().toUpperCase();
            const int = v => {
                const n = parseInt(String(v ?? '').replace(/[^\d-]/g, ''), 10);
                return isNaN(n) ? 0 : n;
            };
            const getCellByLabel = (row, wanted) => {
                const wl = normLabel(wanted);
                const tds = Array.from(row?.children || []);
                for (const c of tds)
                    if (normLabel(c.getAttribute('data-label')) === wl) return c;
                for (const c of tds) {
                    const l = normLabel(c.getAttribute('data-label'));
                    if (l && (l.includes(wl) || wl.includes(l))) return c;
                }
                return null;
            };

            // --- time & date helpers ---
            const timeToMinutes = txt => {
                if (!txt) return null;
                const m = String(txt).trim().match(/^(\d{1,2})\s*:\s*(\d{2})/);
                if (!m) {
                    const h = parseInt(String(txt).trim().match(/^(\d{1,2})/)?.[1] ?? '', 10);
                    return isNaN(h) ? null : h * 60;
                }
                const hh = parseInt(m[1], 10),
                    mm = parseInt(m[2], 10);
                if (isNaN(hh) || isNaN(mm)) return null;
                return hh * 60 + mm;
            };
            const getCurrentISO = () =>
                (document.querySelector('input[name="date"]')?.value || new Date().toISOString()).slice(0, 10);
            const isoAddDays = (iso, days) => {
                const d = new Date(iso + 'T00:00:00');
                d.setDate(d.getDate() + (days | 0));
                return d.toISOString().slice(0, 10);
            };
            // Convert "MM/DD" (dari tabel) -> "YYYY-MM-DD" (ambil tahun dari current date)
            const mdToISO = (mdText, refISO = getCurrentISO()) => {
                if (!mdText) return null;
                const m = String(mdText).trim().match(/^(\d{1,2})\s*\/\s*(\d{1,2})/);
                if (!m) return null;
                const [, MMs, DDs] = m;
                const MM = parseInt(MMs, 10),
                    DD = parseInt(DDs, 10);
                if (isNaN(MM) || isNaN(DD)) return null;
                const ref = new Date(refISO + 'T00:00:00');
                const y = ref.getFullYear();
                const dSame = new Date(y, MM - 1, DD);
                return dSame.toISOString().slice(0, 10);
            };
            // Klasifikasi shift sesuai aturan 09:40 + tanggal
            const toShiftByDateTime = (currentISO, deliveryISO, timeText) => {
                if (!currentISO || !deliveryISO || !timeText) return 'other';
                const mins = timeToMinutes(timeText);
                if (mins == null) return 'other';
                const THRESH = 9 * 60 + 40; // 09:40
                const nextISO = isoAddDays(currentISO, 1);
                if (deliveryISO === currentISO && mins >= THRESH) return 'morning';
                if (deliveryISO === nextISO && mins < THRESH) return 'night';
                return 'other';
            };

            // (dipakai di tempat lain – biarkan)
            const hourFromText = txt => {
                if (!txt) return null;
                const m = String(txt).trim().match(/^(\d{1,2})/);
                if (!m) return null;
                const h = parseInt(m[1], 10);
                return isNaN(h) ? null : h;
            };
            const canonicalBackNoSplit = s => String(s || '').toUpperCase()
                .replace(/\s*\(C\d(?:[–-])?\d\)\s*$/, '').trim();

            window.$u = {
                normLabel,
                normUpper,
                int,
                getCellByLabel,
                // baru:
                timeToMinutes,
                getCurrentISO,
                isoAddDays,
                mdToISO,
                toShiftByDateTime,
                // legacy:
                hourFromText,
                canonicalBackNoSplit
            };
        })();
    </script>

    <script>
        /* ======================
                                                       THEME TOGGLE
                                                       ====================== */
        (function themeInit() {
            const key = 'pulling_theme';
            const el = document.documentElement;
            const apply = mode => {
                el.setAttribute('data-theme', mode);
                const btn = document.getElementById('themeToggle');
                if (!btn) return;
                const icon = btn.querySelector('i');
                const label = btn.querySelector('span');
                if (mode === 'dark') {
                    if (icon) icon.className = 'far fa-sun me-1';
                    if (label) label.textContent = 'Light';
                } else {
                    if (icon) icon.className = 'far fa-moon me-1';
                    if (label) label.textContent = 'Dark';
                }
            };
            const saved = localStorage.getItem(key);
            apply(saved || (window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ? 'dark' : 'light'));
            document.getElementById('themeToggle')?.addEventListener('click', () => {
                const next = (el.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
                localStorage.setItem(key, next);
                apply(next);
            });
        })();
    </script>

    <script>
        const COL_ORDER = [
            'Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute',
            'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time', 'Delivery Date',
            'Balance Time'
        ];

        class ProductionPlanSSEClient {
            constructor() {
                this.eventSource = null;
                this.statusElement = null;
                this.currentDate = this.getCurrentDate();
                this.highlightTimeouts = new Set();
                this.originalOrder = new Map();
                this.orderRestoreTimeouts = new Map();
                this.summaries = {};
                this.HIGHLIGHT_DURATION_MS = 40000;
                this.init();
            }

            static normLabel = $u.normLabel;
            static colOrder = COL_ORDER.map($u.normLabel);

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
                    document.querySelectorAll('[title]').forEach(el => {
                        el.setAttribute('data-bs-toggle', 'tooltip');
                        try {
                            new bootstrap.Tooltip(el);
                        } catch {}
                    });
                }
            }

            _cell(row, wanted) {
                return $u.getCellByLabel(row, wanted);
            }

            prefillRawAttrs(container) {
                if (!container) return;
                container.querySelectorAll('tbody tr').forEach(row => {
                    const bnTd = this._cell(row, 'Back No');
                    const bnEl = bnTd?.querySelector('.flip') || bnTd;
                    if (bnEl && !bnEl.dataset.backnoRaw) bnEl.dataset.backnoRaw = (bnEl.textContent || '')
                        .trim();

                    const odTd = this._cell(row, 'Order');
                    const odEl = odTd?.querySelector('.flip') || odTd;
                    if (odEl && !odEl.dataset.orderRaw) odEl.dataset.orderRaw = String($u.int(odEl
                        .textContent || '0'));
                });
            }

            _getBackNo(row) {
                const td = this._cell(row, 'Back No');
                const el = td?.querySelector('.flip') || td;
                let val = (el?.dataset?.backnoAlias || el?.dataset?.backnoRaw || el?.textContent || '').trim();
                if (val) return val.toUpperCase();
                const text = (row.textContent || '').toUpperCase();
                const m = text.match(/\b(?:D\d{2,4}|CI\d{2,4})\b/);
                return m ? m[0] : '';
            }

            _getOrder(row) {
                const td = this._cell(row, 'Order');
                const el = td?.querySelector('.flip') || td;
                if (!el) return 0;
                const raw = parseInt(el.dataset?.orderRaw || '', 10);
                return isNaN(raw) ? $u.int(el.textContent || '0') : raw;
            }

            _getDP(row) {
                return $u.int(row?.querySelector('[data-type="direct-pulling"]')?.textContent);
            }
            _getSC(row) {
                return $u.int(row?.querySelector('[data-type="stock-chute"]')?.textContent);
            }

            _getId(row) {
                const el = row?.querySelector('[data-type="direct-pulling"]') || row?.querySelector(
                    '[data-type="stock-chute"]');
                return el ? el.getAttribute('data-item-id') : null;
            }

            _getCycle(row) {
                const td = this._cell(row, 'Cycle');
                const m = (td?.textContent || '').trim().match(/\d+/);
                const n = m ? parseInt(m[0], 10) : NaN;
                return isNaN(n) ? null : (((n - 1) % 8) + 1);
            }

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

            _purgePinnedSummaries(container) {
                const tbody = container?.querySelector('tbody');
                if (!tbody) return;
                tbody.querySelectorAll('tr[data-summary-row="1"]').forEach(n => n.remove());
            }

            _removeSummaryRowIfExists(tbody, label) {
                tbody.querySelectorAll(`tr[data-summary-row="1"][data-summary-label="${label}"]`).forEach(n => n
                    .remove());
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

            _indexByLabel(td) {
                const lbl = ProductionPlanSSEClient.normLabel(td?.getAttribute('data-label'));
                const idx = ProductionPlanSSEClient.colOrder.indexOf(lbl);
                return idx >= 0 ? idx : (td?.cellIndex ?? 9999);
            }

            _moveRowspanCellsTo(startRow, hostRow) {
                if (!startRow || !hostRow || hostRow === startRow) return;
                hostRow.querySelectorAll('[data-cloned-header]').forEach(n => n.remove());
                const startCells = Array.from(startRow.children)
                    .filter(td => td.hasAttribute('rowspan'))
                    .sort((a, b) => this._indexByLabel(a) - this._indexByLabel(b));
                startCells.forEach(td => {
                    const clone = td.cloneNode(true);
                    clone.setAttribute('rowspan', 1);
                    clone.setAttribute('data-cloned-header', '1');
                    const wantIdx = this._indexByLabel(td);
                    let ref = null;
                    for (const ex of Array.from(hostRow.children)) {
                        if (this._indexByLabel(ex) > wantIdx) {
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

            buildSummaries() {
                Object.values(this.summaries).flat().forEach(s => s?.row?.remove?.());
                this.summaries = {};

                if (this.AS003) {
                    this._purgePinnedSummaries(this.AS003);
                    this.summaries.AS003 = this._extractAndPinSummaryCI12Split({
                        container: this.AS003,
                        targets: ['D111', 'CI12'],
                        baseLabel: 'CI12'
                    });
                }
                if (this.AS004) {
                    this._purgePinnedSummaries(this.AS004);
                    const one = this._extractAndPinSummaryGeneral({
                        container: this.AS004,
                        targets: ['D500', 'CI19'],
                        label: 'CI19'
                    });
                    this.summaries.AS004 = one ? [one] : [];
                }
            }

            _extractAndPinSummaryGeneral({
                container,
                targets,
                label
            }) {
                const tgtSet = new Set(targets.map(t => String(t).toUpperCase()));
                const tbody = container?.querySelector('tbody');
                if (!tbody) return null;

                const summary = {
                    row: null,
                    totals: {
                        order: 0,
                        dp: 0,
                        sc: 0
                    },
                    ids: new Map()
                };

                const allRows = Array.from(tbody.querySelectorAll('tr'));

                // Kelompokkan per group (rowspan)
                let i = 0,
                    groups = [];
                while (i < allRows.length) {
                    const start = allRows[i];
                    const rs = this.isGroupStart(start) ?
                        parseInt(start.querySelector('[rowspan]')?.getAttribute('rowspan') || '1', 10) :
                        1;
                    const g = [start];
                    for (let k = 1; k < rs && (i + k) < allRows.length; k++) g.push(allRows[i + k]);
                    groups.push(g);
                    i += Math.max(1, rs);
                }

                const customerBag = [];
                groups.forEach(groupRows => {
                    const startRow = groupRows[0];
                    const matches = groupRows.filter(r => tgtSet.has(this._getBackNo(r)));
                    if (!matches.length) return;

                    const custTd = this._cell(startRow, 'Customer');
                    const custText = (custTd?.querySelector('.flip')?.textContent || custTd?.textContent || '')
                        .trim();
                    if (custText) customerBag.push(custText);

                    matches.forEach(r => {
                        const id = this._getId(r),
                            dp = this._getDP(r),
                            sc = this._getSC(r),
                            od = this._getOrder(r);
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
                    if (!keepRows.length) {
                        groupRows.forEach(r => r.remove());
                    } else {
                        if (matches.includes(startRow)) this._moveRowspanCellsTo(startRow, keepRows[0]);
                        matches.forEach(r => {
                            if (r !== startRow) r.remove();
                        });
                    }
                });

                let customerText = '--';
                if (customerBag.length) {
                    const freq = customerBag.reduce((m, s) => (m[s] = (m[s] || 0) + 1, m), {});
                    customerText = Object.entries(freq).sort((a, b) => b[1] - a[1])[0][0];
                }

                if (summary.totals.order + summary.totals.dp + summary.totals.sc > 0) {
                    this._removeSummaryRowIfExists(tbody, label);
                    summary.row = this._createSummaryRow({
                        label,
                        totals: summary.totals,
                        customerText
                    });

                    // === PIN DI PALING BAWAH ===
                    tbody.appendChild(summary.row);
                }

                this.recalcRowspans(container);
                return summary;
            }


            _normalize(s) {
                return String(s || '').replace(/\s+/g, ' ').trim().toUpperCase();
            }
            _cellText(row, label) {
                const td = this._cell(row, label);
                const el = td?.querySelector('.flip') || td;
                return (el?.textContent || '').trim();
            }
            _findAnchorRow(container, {
                customer,
                dock,
                cycle
            }) {
                const tbody = container?.querySelector('tbody');
                if (!tbody) return null;
                const wantCust = this._normalize(customer),
                    wantDock = this._normalize(dock),
                    wantCycle = Number(cycle);
                for (const tr of Array.from(tbody.querySelectorAll('tr'))) {
                    if (!this.isGroupStart(tr)) continue;
                    const custTxt = this._normalize(this._cellText(tr, 'Customer'));
                    const dockTxt = this._normalize(this._cellText(tr, 'Dock'));
                    const cyc = this._getCycle(tr);
                    if (custTxt.includes(wantCust) && dockTxt.includes(wantDock) && cyc === wantCycle) return tr;
                }
                return null;
            }

            _extractAndPinSummaryCI12Split({
                container,
                targets,
                baseLabel
            }) {
                const tgtSet = new Set(targets.map(t => String(t).toUpperCase()));
                const tbody = container?.querySelector('tbody');
                const result = [];
                if (!tbody) return result;

                const S47 = {
                    row: null,
                    totals: {
                        order: 0,
                        dp: 0,
                        sc: 0
                    },
                    ids: new Map(),
                    label: `${baseLabel} (C4–7)`,
                    customers: []
                };
                const S83 = {
                    row: null,
                    totals: {
                        order: 0,
                        dp: 0,
                        sc: 0
                    },
                    ids: new Map(),
                    label: `${baseLabel} (C8–3)`,
                    customers: []
                };

                const allRows = Array.from(tbody.querySelectorAll('tr'));
                let i = 0,
                    groups = [];
                while (i < allRows.length) {
                    const start = allRows[i];
                    const rs = this.isGroupStart(start) ? parseInt(start.querySelector('[rowspan]')?.getAttribute(
                        'rowspan') || '1', 10) : 1;
                    const g = [start];
                    for (let k = 1; k < rs && (i + k) < allRows.length; k++) g.push(allRows[i + k]);
                    groups.push(g);
                    i += Math.max(1, rs);
                }

                groups.forEach(groupRows => {
                    const startRow = groupRows[0];
                    const matchesAll = groupRows.filter(r => tgtSet.has(this._getBackNo(r)));
                    if (!matchesAll.length) return;

                    const custTd = this._cell(startRow, 'Customer');
                    const custText = (custTd?.querySelector('.flip')?.textContent || custTd?.textContent || '')
                        .trim();
                    if (custText) {
                        S47.customers.push(custText);
                        S83.customers.push(custText);
                    }

                    const in47 = [],
                        in83 = [];
                    matchesAll.forEach(r => {
                        const cyc = this._getCycle(r);
                        if (cyc == null) in83.push(r);
                        else if (cyc >= 4 && cyc <= 7) in47.push(r);
                        else in83.push(r);
                    });

                    const collect = (bucket, rows) => {
                        rows.forEach(r => {
                            const id = this._getId(r),
                                dp = this._getDP(r),
                                sc = this._getSC(r),
                                od = this._getOrder(r);
                            bucket.totals.dp += dp;
                            bucket.totals.sc += sc;
                            bucket.totals.order += od;
                            if (id) bucket.ids.set(id, {
                                dp,
                                sc,
                                order: od
                            });
                        });
                    };
                    collect(S47, in47);
                    collect(S83, in83);

                    const keepRows = groupRows.filter(r => !matchesAll.includes(r));
                    if (!keepRows.length) groupRows.forEach(r => r.remove());
                    else {
                        if (matchesAll.includes(startRow)) this._moveRowspanCellsTo(startRow, keepRows[0]);
                        matchesAll.forEach(r => {
                            if (r !== startRow) r.remove();
                        });
                    }
                });

                const modeOf = arr => {
                    if (!arr.length) return '--';
                    const freq = arr.reduce((m, s) => (m[s] = (m[s] || 0) + 1, m), {});
                    return Object.entries(freq).sort((a, b) => b[1] - a[1])[0][0];
                };
                const cust47 = modeOf(S47.customers);
                const cust83 = modeOf(S83.customers);

                const anchor47 = this._findAnchorRow(container, {
                    customer: 'TMMIN KARAWANG PLANT 3',
                    dock: '6I',
                    cycle: 7
                });
                const anchor83 = this._findAnchorRow(container, {
                    customer: 'ADM ENGINE PLANT',
                    dock: 'EXP',
                    cycle: 1
                });

                if (S47.totals.order + S47.totals.dp + S47.totals.sc > 0) {
                    this._removeSummaryRowIfExists(tbody, S47.label);
                    S47.row = this._createSummaryRow({
                        label: S47.label,
                        totals: S47.totals,
                        customerText: cust47
                    });
                    tbody.insertBefore(S47.row, anchor47 || tbody.firstChild || null);
                    result.push(S47);
                }
                if (S83.totals.order + S83.totals.dp + S83.totals.sc > 0) {
                    this._removeSummaryRowIfExists(tbody, S83.label);
                    S83.row = this._createSummaryRow({
                        label: S83.label,
                        totals: S83.totals,
                        customerText: cust83
                    });
                    if (anchor83) tbody.insertBefore(S83.row, anchor83);
                    else tbody.appendChild(S83.row);
                    result.push(S83);
                }

                this.recalcRowspans(container);
                return result;
            }

            _createSummaryRow({
                label,
                totals,
                customerText = '--'
            }) {
                const tr = document.createElement('tr');
                tr.className = 'fw-bold';
                tr.setAttribute('data-summary-row', '1');
                tr.setAttribute('data-summary-label', label);

                const pct = Math.min(100, Math.round(((totals.dp + totals.sc) / Math.max(1, totals.order)) * 100));
                const td = (text, attrs = {}) => {
                    const el = document.createElement('td');
                    if (text != null) el.innerHTML = `<span class="flip">${text}</span>`;
                    for (const k in attrs) el.setAttribute(k, attrs[k]);
                    return el;
                };

                tr.appendChild(td(customerText, {
                    'data-label': 'Customer',
                    rowspan: '1'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Dock',
                    rowspan: '1'
                }));
                tr.appendChild(td('--', {
                    'data-label': 'Cycle',
                    rowspan: '1'
                }));
                tr.appendChild(td(label, {
                    'data-label': 'Back No'
                }));
                tr.appendChild(td(totals.order.toLocaleString('id-ID'), {
                    'data-label': 'Order'
                }));

                const dpCell = document.createElement('td');
                dpCell.setAttribute('data-label', 'Direct Pulling');
                dpCell.innerHTML = `
      <div class="qty-progress" title="DP ${totals.dp} / ${totals.order}">
        <div class="bar"><i style="width:${Math.min(100, Math.round((totals.dp/Math.max(1,totals.order))*100))}%"></i></div>
        <span class="val"><span class="flip" data-summary-dp>${totals.dp}</span></span>
      </div>`;
                tr.appendChild(dpCell);

                const scCell = document.createElement('td');
                scCell.setAttribute('data-label', 'Stock Chute');
                scCell.innerHTML = `
      <div class="qty-progress" title="SC ${totals.sc} / ${totals.order}">
        <div class="bar"><i style="width:${Math.min(100, Math.round((totals.sc/Math.max(1,totals.order))*100))}%"></i></div>
        <span class="val"><span class="flip" data-summary-sc>${totals.sc}</span></span>
      </div>`;
                tr.appendChild(scCell);

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

                const prog = document.createElement('td');
                prog.className = 'total-progress';
                prog.setAttribute('data-label', 'Progress');
                prog.innerHTML = `
      <div class="qty-progress" title="DP+SC ${totals.dp + totals.sc} / ${totals.order} (${pct}%)">
        <div class="bar"><i data-summary-totalbar style="width:${pct}%"></i></div>
        <span class="val" data-summary-totalpct>${pct}%</span>
      </div>`;
                tr.appendChild(prog);

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
                Object.values(this.summaries).flat().forEach(s => this._refreshSummaryRow(s));
            }

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
.highlight-beep-direct td,.highlight-beep-stock td{background-color:inherit!important;}`;
                document.head.appendChild(st);
            }

            _restorePinnedRow(row) {
                const tbody = row?.parentElement;
                if (!tbody) return;
                Array.from(row.querySelectorAll('[data-cloned-header]')).forEach(n => n.remove());
                const ph = row._pinPlaceholder;
                if (ph && ph.parentNode === tbody) {
                    tbody.insertBefore(row, ph);
                    ph.remove();
                    row._pinPlaceholder = null;
                } else {
                    this._restoreOriginalOrder?.(tbody);
                }
                row.classList.remove('is-pinned');
                const container = tbody.closest('[data-toggle-table]') || tbody.closest('table')?.parentElement;
                this.recalcRowspans(container);
            }

            triggerHighlight(row, type = 'direct-pulling') {
                if (!row) return;
                const cls = (type === 'stock-chute') ? 'highlight-beep-stock' : 'highlight-beep-direct';
                row.classList.remove('highlight-beep-direct', 'highlight-beep-stock');
                void row.offsetWidth;
                row.classList.add(cls);
                clearTimeout(row._blinkTimer);
                row._blinkTimer = setTimeout(() => row.classList.remove(cls), this.HIGHLIGHT_DURATION_MS);
                this._pinRowToTop(row);
                clearTimeout(row._pinRestoreTimer);
                row._pinRestoreTimer = setTimeout(() => this._restorePinnedRow(row), this.HIGHLIGHT_DURATION_MS);
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
                if (inp) inp.addEventListener('change', () => {
                    this.currentDate = this.getCurrentDate();
                    this.reconnect();
                });
            }

            updateConnectionStatus(status, msg = '') {
                const m = ({
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
                } [status]) || {
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
                    try {
                        this.eventSource?.close();
                    } catch {}
                });
            }

            handleUpdates(updates) {
                updates.forEach(item => {
                    Object.values(this.summaries).flat().forEach(s => {
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

                    const idSel = id => `[data-item-id="${id}"]`;
                    if (document.querySelector(`${idSel(item.id)}[data-type="direct-pulling"]`) || document
                        .querySelector(`${idSel(item.id)}[data-type="stock-chute"]`)) {
                        this.updateQuantity(`${idSel(item.id)}[data-type="direct-pulling"]`, item
                            .direct_pulling_qty, 'direct-pulling', item.order_qty);
                        this.updateQuantity(`${idSel(item.id)}[data-type="stock-chute"]`, item.stock_chute_qty,
                            'stock-chute', item.order_qty);
                        this.updateQuantity(`${idSel(item.id)}[data-type="actual_start"]`, item.actual_start,
                            'time');
                        this.updateQuantity(`${idSel(item.id)}[data-type="end"]`, item.end, 'time');
                        this.updateQuantity(`${idSel(item.id)}[data-type="balance"]`, item.balance, 'time');
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
                        const row = td?.parentElement;

                        if (!isNaN(parseFloat(newValue))) this.updateCellStyle(td, parseFloat(newValue), type,
                            targetQty);
                        else this.updateCellStyle(td, null, type);

                        const bar = td?.querySelector('.qty-progress .bar > i');
                        if (bar && (type === 'direct-pulling' || type === 'stock-chute')) {
                            const order = this._getOrder(row);
                            const dp = $u.int(row.querySelector('[data-type="direct-pulling"]')?.textContent);
                            const sc = $u.int(row.querySelector('[data-type="stock-chute"]')?.textContent);
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

                        if (row && (type === 'direct-pulling' || type === 'stock-chute')) this.triggerHighlight(
                            row, type);

                        const f = td?.querySelector('.flip');
                        if (f) {
                            f.classList.add('animate-flip');
                            setTimeout(() => f.classList.remove('animate-flip'), 600);
                        }
                    }
                });
            }

            updateCellStyle(cell, val, type, targetQty = null) {
                if (!cell || type === 'time') return;
                if (val === null) {
                    cell.className = '';
                    return;
                }
                let cls = 'fw-bold ';
                if (type === 'direct-pulling' || type === 'stock-chute') {
                    if (targetQty !== null && !isNaN(targetQty)) cls += (val >= targetQty) ?
                        'bg-success bg-opacity-75 text-white' : 'bg-warning bg-opacity-75';
                    else cls += (val > 0) ? 'bg-success bg-opacity-25' : 'bg-warning bg-opacity-25';
                }
                cell.className = cls.trim();
            }

            _findGroupStartRow(row) {
                let p = row;
                while (p && !this.isGroupStart(p)) p = p.previousElementSibling;
                return (p && this.isGroupStart(p)) ? p : null;
            }

            _cloneRowspanCellsToRow(startRow, hostRow) {
                const hasByLabel = (r, lbl) => Array.from(r.children).some(td => ProductionPlanSSEClient.normLabel(td
                    .getAttribute('data-label')) === ProductionPlanSSEClient.normLabel(lbl));
                const startCells = Array.from(startRow.children).filter(td => td.hasAttribute('rowspan'));
                startCells.forEach(src => {
                    const lbl = src.getAttribute('data-label') || '';
                    if (hasByLabel(hostRow, lbl)) return;
                    const clone = src.cloneNode(true);
                    clone.setAttribute('rowspan', 1);
                    clone.setAttribute('data-cloned-header', '1');
                    const wantIdx = this._indexByLabel(src);
                    let ref = null;
                    for (const ex of Array.from(hostRow.children)) {
                        if (this._indexByLabel(ex) > wantIdx) {
                            ref = ex;
                            break;
                        }
                    }
                    hostRow.insertBefore(clone, ref);
                });
            }

            _pinRowToTop(row) {
                const tbody = row?.parentElement;
                if (!tbody) return;
                if (!row._pinPlaceholder) {
                    row._pinPlaceholder = document.createComment('pin-anchor');
                    tbody.insertBefore(row._pinPlaceholder, row);
                }
                const startRow = this._findGroupStartRow(row);
                if (startRow) this._cloneRowspanCellsToRow(startRow, row);

                const rows = Array.from(tbody.querySelectorAll('tr'));
                const firstNonSummary = rows.find(tr => tr.getAttribute('data-summary-row') !== '1' && tr !== row);
                if (firstNonSummary) tbody.insertBefore(row, firstNonSummary);
                else tbody.insertBefore(row, tbody.firstChild);
                row.classList.add('is-pinned');

                const container = tbody.closest('[data-toggle-table]') || tbody.closest('table')?.parentElement;
                this.recalcRowspans(container);
            }

            _restoreOriginalOrder(tbody) {
                const orig = this.originalOrder.get(tbody);
                if (!orig) return;
                Array.from(tbody.querySelectorAll('tr [data-cloned-header]')).forEach(n => n.remove());
                orig.forEach(r => {
                    if (r && r.parentElement === tbody && r.getAttribute('data-summary-row') !== '1') tbody
                        .appendChild(r);
                });
                const container = tbody.closest('[data-toggle-table]') || tbody.closest('table')?.parentElement;
                this.recalcRowspans(container);
            }

            updateAllInlineSums() {
                this.refreshSummaries();
            }
        }

        /* Boot */
        document.addEventListener('DOMContentLoaded', () => {
            window.prodPlanSSE = new ProductionPlanSSEClient();
        });

        /* Navigasi tanggal */
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
            inp.value = new Date().toISOString().split('T')[0];
            document.querySelector('form')?.submit();
        }

        /* Bridge eksternal */
        (function Bridge() {
            window.updateAllInlineSums = () => {
                window.prodPlanSSE?.updateAllInlineSums?.();
            };
        })();
    </script>

    <script>
        /* ======================
                                                       SAFE COLUMN HIDE V5 (as-is, minor tidy)
                                                       ====================== */
        (function SafeColumnHideV5() {
            const STORAGE_PREFIX = 'hiddenCols_';
            const tableStates = new Map();
            let isProcessing = false;

            const CANON = [
                'Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute',
                'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time',
                'Delivery Date', 'Balance Time'
            ].map(s => s.toLowerCase());

            const GROUP_MAP = {
                'running qty': ['Direct Pulling', 'Stock Chute'],
                'working time': ['Planning Start', 'Actual Start', 'Duration', 'Progress']
            };

            if (!document.getElementById('colhide_label_style')) {
                const st = document.createElement('style');
                st.id = 'colhide_label_style';
                st.textContent = '.col-hidden{display:none!important}';
                document.head.appendChild(st);
            }

            const norm = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();
            const canonicalize = label => {
                const n = norm(label);
                const idx = CANON.indexOf(n);
                return idx >= 0 ? CANON[idx].replace(/\b\w/g, c => c.toUpperCase()) : (label || '').trim();
            };

            const readHiddenKeys = tableKey => {
                try {
                    return new Set(JSON.parse(localStorage.getItem(STORAGE_PREFIX + tableKey) || '[]').map(
                        canonicalize));
                } catch {
                    return new Set();
                }
            };
            const saveHiddenKeys = (tableKey, set) => {
                try {
                    localStorage.setItem(STORAGE_PREFIX + tableKey, JSON.stringify([...set]));
                } catch {}
            };

            function annotateHeader(container) {
                const thead = container.querySelector('thead');
                if (!thead) return {
                    leafKeys: [],
                    groupHeads: []
                };
                const rows = Array.from(thead.rows);
                const r0 = rows[0] || null;
                const r1 = rows[1] || null;
                const leafKeys = [];
                const groupHeads = [];
                if (!r0) return {
                    leafKeys,
                    groupHeads
                };

                let childIdx = 0;
                const r1cells = r1 ? Array.from(r1.cells) : [];
                Array.from(r0.cells).forEach(th => {
                    const text = canonicalize(th.textContent);
                    const ntext = norm(text);
                    if ((th.rowSpan || 1) > 1 && (th.colSpan || 1) === 1) {
                        const key = text;
                        th.setAttribute('data-col-key', key);
                        leafKeys.push(key);
                    } else if ((th.colSpan || 1) > 1) {
                        const span = th.colSpan,
                            kids = [];
                        for (let i = 0; i < span; i++) {
                            const c = r1cells[childIdx++];
                            if (!c) continue;
                            const k = canonicalize(c.textContent);
                            c.setAttribute('data-col-key', k);
                            leafKeys.push(k);
                            kids.push(k);
                        }
                        const gChildren = (GROUP_MAP[ntext] || kids);
                        th.setAttribute('data-col-group', gChildren.join('||'));
                        groupHeads.push({
                            el: th,
                            children: gChildren
                        });
                    } else {
                        const key = text;
                        th.setAttribute('data-col-key', key);
                        leafKeys.push(key);
                    }
                });
                if (r1 && groupHeads.length === 0) r1cells.forEach(c => {
                    const k = canonicalize(c.textContent);
                    c.setAttribute('data-col-key', k);
                    leafKeys.push(k);
                });
                return {
                    leafKeys,
                    groupHeads
                };
            }

            function applyHiding(container, hiddenKeys, headerInfo) {
                const {
                    groupHeads
                } = headerInfo;
                container.querySelectorAll('tbody td[data-label]').forEach(td => {
                    const key = canonicalize(td.getAttribute('data-label'));
                    td.classList.toggle('col-hidden', hiddenKeys.has(key));
                });
                container.querySelectorAll('thead th[data-col-key]').forEach(th => {
                    const key = th.getAttribute('data-col-key');
                    th.classList.toggle('col-hidden', hiddenKeys.has(key));
                });
                groupHeads.forEach(g => {
                    const visibleCount = g.children.reduce((n, k) => n + (hiddenKeys.has(canonicalize(k)) ? 0 :
                        1), 0);
                    if (visibleCount === 0) {
                        g.el.classList.add('col-hidden');
                        g.el.colSpan = 1;
                    } else {
                        g.el.classList.remove('col-hidden');
                        g.el.colSpan = visibleCount;
                    }
                });
            }

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
                        window.prodPlanSSE?.recalcRowspans?.(container);
                    });
                });
                applyHiding(container, hiddenKeys, headerInfo);
            });

            window.__colHideApplyAll = function() {
                if (isProcessing) return;
                isProcessing = true;
                try {
                    tableStates.forEach((state, tableKey) => {
                        const hiddenKeys = readHiddenKeys(tableKey);
                        state.hiddenKeys = hiddenKeys;
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
                        window.prodPlanSSE?.recalcRowspans?.(state.container);
                    });
                } finally {
                    isProcessing = false;
                }
            };

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
        /* ======================
                                                       BACK NO RENAMER (trim using $u)
                                                       ====================== */
        (function BackNoRenamer() {
            const LS_KEY = 'backnoRenameMap';
            const loadMap = () => {
                try {
                    return JSON.parse(localStorage.getItem(LS_KEY) || '{}');
                } catch {
                    return {};
                }
            };
            const saveMap = map => {
                try {
                    localStorage.setItem(LS_KEY, JSON.stringify(map));
                } catch {}
            };

            function applyMapToContainer(container, map) {
                if (!container) return;
                container.querySelectorAll('tbody tr').forEach(row => {
                    const td = $u.getCellByLabel(row, 'Back No');
                    if (!td) return;
                    const el = td.querySelector('.flip') || td;
                    const original = $u.normUpper(el.dataset.backnoRaw || el.textContent);
                    const alias = map[original];
                    if (alias) {
                        el.dataset.backnoAlias = $u.normUpper(alias);
                        (td.querySelector('.flip') || el).textContent = alias;
                    } else if (el.dataset.backnoAlias) {
                        (td.querySelector('.flip') || el).textContent = el.dataset.backnoRaw || el.textContent;
                        delete el.dataset.backnoAlias;
                    }
                });
            }

            function applyAll(map) {
                document.querySelectorAll('[data-toggle-table]').forEach(container => applyMapToContainer(container,
                    map));
                document.querySelectorAll('[data-toggle-table]').forEach(c => window.prodPlanSSE?.recalcRowspans?.(c));
            }

            window.setBackNoRenameMap = function(map, {
                persist = true,
                applyNow = true
            } = {}) {
                const clean = {};
                Object.entries(map || {}).forEach(([k, v]) => {
                    if (k && v) clean[$u.normUpper(k)] = $u.normUpper(v);
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
                    map[$u.normUpper(from)] = $u.normUpper(to);
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

            document.addEventListener('DOMContentLoaded', () => {
                const map = loadMap();
                if (Object.keys(map).length) applyAll(map);
            });
        })();

        /* default alias set */
        setBackNoRenameMap({
            'D403': 'CI18',
            'D111': 'CI12',
            'D500': 'CI19'
        });
    </script>

    <script>
        /* ======================
                                                       SHIFT CARDS (FixShiftCardsV3) – trimmed helpers via $u
                                                       ====================== */
        (function FixShiftCardsV3() {
            if (window.__fixShiftCardsV3Installed) return;
            window.__fixShiftCardsV3Installed = true;

            const LINES = ['AS003', 'AS004'];
            const IDLOCALE = 'id-ID';
            const norm = $u.normUpper;
            const isSummaryRow = tr => tr?.getAttribute('data-summary-row') === '1';
            const getLineKeyOfRow = tr => tr?.closest('[data-toggle-table]')?.getAttribute('data-toggle-table') || '';

            function cellByLabel(row, wanted) {
                return $u.getCellByLabel(row, wanted);
            }

            function readSummaryLabel(tr) {
                return norm(tr?.getAttribute('data-summary-label') || tr?.getAttribute('data-summary-key') || tr
                    ?.textContent || '');
            }

            function readOrder(tr) {
                if (isSummaryRow(tr)) {
                    const ds = tr.querySelector('[data-summary-order]');
                    if (ds) return $u.int(ds.textContent);
                    const td = cellByLabel(tr, 'Order');
                    const el = td?.querySelector('.flip') || td;
                    return $u.int(el?.textContent);
                }
                const td = cellByLabel(tr, 'Order');
                const el = td?.querySelector('.flip') || td;
                const raw = el?.dataset?.orderRaw;
                return raw != null && raw !== '' ? $u.int(raw) : $u.int(el?.textContent);
            }
            const readDP = tr => isSummaryRow(tr) ? $u.int(tr.querySelector('[data-summary-dp]')?.textContent) : $u.int(
                (tr.querySelector('[data-type="direct-pulling"]') || cellByLabel(tr, 'Direct Pulling')
                    ?.querySelector('.flip'))?.textContent);
            const readSC = tr => isSummaryRow(tr) ? $u.int(tr.querySelector('[data-summary-sc]')?.textContent) : $u.int(
                (tr.querySelector('[data-type="stock-chute"]') || cellByLabel(tr, 'Stock Chute')?.querySelector(
                    '.flip'))?.textContent);

            function readDeliveryHourForRow(row) {
                let r = row;
                while (r) {
                    const td = cellByLabel(r, 'Delivery Time');
                    if (td) return $u.hourFromText(td.textContent);
                    const prev = r.previousElementSibling;
                    if (!prev) break;
                    r = prev;
                }
                return null;
            }

            function readHourFromRowTimes(row) {
                const asEl = row.querySelector('[data-type="actual_start"]');
                const psEl = row.querySelector('[data-type="start"]');
                let h = $u.hourFromText(asEl?.textContent || '') || $u.hourFromText(cellByLabel(row, 'Actual Start')
                    ?.textContent);
                if (h == null) {
                    h = $u.hourFromText(psEl?.textContent || '') || $u.hourFromText(cellByLabel(row, 'Planning Start')
                        ?.textContent);
                }
                return h;
            }

            function specialSummaryShift(lineKey, row) {
                if (!isSummaryRow(row)) return null;
                const label = readSummaryLabel(row);
                if (lineKey === 'AS003') {
                    if (/^CI12\b/i.test(label)) {
                        if (/\bC\s*4\s*[–-]\s*7\b/i.test(label)) return 'morning';
                        if (/\bC\s*8\s*[–-]\s*3\b/i.test(label)) return 'night';
                    }
                }
                if (lineKey === 'AS004') {
                    if (/\bCI19\b/i.test(label)) return 'morning';
                }
                return null;
            }

            function readDeliveryTimeTextForRow(row) {
                let r = row;
                while (r) {
                    const td = cellByLabel(r, 'Delivery Time');
                    if (td) {
                        const t = (td.textContent || '').trim();
                        return t || null; // "HH:mm"
                    }
                    const prev = r.previousElementSibling;
                    if (!prev) break;
                    r = prev;
                }
                return null;
            }

            function readDeliveryDateMDForRow(row) {
                let r = row;
                while (r) {
                    const td = cellByLabel(r, 'Delivery Date');
                    if (td) {
                        const t = (td.textContent || '').trim(); // "M/D" atau "MM/DD"
                        return t || null;
                    }
                    const prev = r.previousElementSibling;
                    if (!prev) break;
                    r = prev;
                }
                return null;
            }


            function classifyShift(row, lineKey) {
                // hormati cache kalau sudah pernah ditandai
                const tag = row.getAttribute('data-shift');
                if (tag === 'morning' || tag === 'night') return tag;

                // RULE UTAMA: pakai Delivery Time kalau ada
                const Hdlv = $u.hourFromText(readDeliveryTimeTextForRow(row));
                if (Hdlv != null) {
                    return (Hdlv >= 10 && Hdlv <= 22) ? 'morning' :
                        ((Hdlv >= 0 && Hdlv <= 9) || Hdlv === 23) ? 'night' :
                        'other';
                }

                // FALLBACK: pakai Actual/Planning Start
                const H = readHourFromRowTimes(row);
                if (H != null) {
                    return (H >= 10 && H <= 22) ? 'morning' :
                        ((H >= 0 && H <= 9) || H === 23) ? 'night' :
                        'other';
                }

                return 'other';
            }



            const rowCountable = tr => tr && tr.style.display !== 'none';

            function computeLine(lineKey) {
                const wrap = document.querySelector(`[data-toggle-table="${lineKey}"]`);
                const sums = {
                    morning: {
                        order: 0,
                        actual: 0
                    },
                    night: {
                        order: 0,
                        actual: 0
                    },
                    total: {
                        order: 0,
                        actual: 0
                    }
                };
                if (!wrap) return sums;

                wrap.querySelectorAll('tbody tr').forEach(tr => {
                    if (!rowCountable(tr)) return;

                    const order = readOrder(tr);
                    const dp = readDP(tr); // actual = DP (sesuai setup kamu)
                    const lineShift = classifyShift(tr, lineKey);

                    // tandai barisnya (biar konsisten untuk render/refresh berikutnya)
                    if (tr.getAttribute('data-shift') !== lineShift) {
                        tr.setAttribute('data-shift', lineShift);
                    }

                    if (lineShift === 'morning' || lineShift === 'night') {
                        sums[lineShift].order += order;
                        sums[lineShift].actual += dp;
                    }
                });

                // TOTAL = Morning + Night (baris "other" tidak ikut)
                sums.total.order = sums.morning.order + sums.night.order;
                sums.total.actual = sums.morning.actual + sums.night.actual;
                return sums;
            }

            const pct = (a, o) => o > 0 ? Math.min(100, Math.round((a / o) * 100)) : 0;

            function renderBlock(lineKey, shift, data) {
                const root = document.querySelector(
                    `[data-shift-card="${lineKey}"] .strip-stat[data-line="${lineKey}"][data-shift="${shift}"]`);
                if (!root) return;
                const O = data.order,
                    A = data.actual,
                    P = pct(A, O);
                const q = sel => root.querySelector(sel);
                const elO = q('[data-role="shift-order"]');
                const elA = q('[data-role="shift-actual"]');
                const elP = q('[data-role="shift-pct"]');
                const elB = q('[data-role="shift-bar"]');
                if (elO) elO.textContent = O.toLocaleString(IDLOCALE);
                if (elA) elA.textContent = A.toLocaleString(IDLOCALE);
                if (elP) elP.textContent = P + '%';
                if (elB) elB.style.width = P + '%';
            }

            function recompute(lineKey) {
                const sums = computeLine(lineKey);
                renderBlock(lineKey, 'morning', sums.morning);
                renderBlock(lineKey, 'night', sums.night);
                renderBlock(lineKey, 'total', sums.total);
            }

            function recomputeAll() {
                LINES.forEach(recompute);
            }
            window.recomputeAllShiftCards = recomputeAll;

            function updateBarsForRow(row) {
                const order = readOrder(row),
                    dp = readDP(row),
                    sc = readSC(row);
                const dpBar = row.querySelector('[data-label][data-label*="Direct Pulling" i] .qty-progress .bar > i');
                if (dpBar) dpBar.style.width = (order > 0 ? Math.min(100, Math.round((dp / order) * 100)) : 0) + '%';
                const scBar = row.querySelector('[data-label][data-label*="Stock Chute" i] .qty-progress .bar > i');
                if (scBar) scBar.style.width = (order > 0 ? Math.min(100, Math.round((sc / order) * 100)) : 0) + '%';
                const totCell = row.querySelector('.total-progress');
                const tBar = totCell?.querySelector('.bar > i');
                const thePct = (order > 0 ? Math.min(100, Math.round(((dp + sc) / order) * 100)) : 0);
                if (tBar) tBar.style.width = thePct + '%';
                const tPct = totCell?.querySelector('.val');
                if (tPct) tPct.textContent = thePct + '%';
            }

            function applyUpdateItem(it) {
                const id = String(it.id);
                const el = document.querySelector(`[data-item-id="${id}"][data-type="direct-pulling"]`) ||
                    document.querySelector(`[data-item-id="${id}"][data-type="stock-chute"]`) ||
                    document.querySelector(`[data-item-id="${id}"][data-type="actual_start"]`) ||
                    document.querySelector(`[data-item-id="${id}"][data-type="start"]`) ||
                    document.querySelector(`[data-item-id="${id}"][data-type="balance"]`);
                if (!el) return;
                const row = el.closest('tr');
                if (!row) return;

                if (typeof it.direct_pulling_qty === 'number') {
                    const dpEl = row.querySelector(`[data-item-id="${id}"][data-type="direct-pulling"]`);
                    if (dpEl && dpEl.textContent.trim() !== String(it.direct_pulling_qty)) dpEl.textContent = it
                        .direct_pulling_qty;
                }
                if (typeof it.stock_chute_qty === 'number') {
                    const scEl = row.querySelector(`[data-item-id="${id}"][data-type="stock-chute"]`);
                    if (scEl && scEl.textContent.trim() !== String(it.stock_chute_qty)) scEl.textContent = it
                        .stock_chute_qty;
                }
                if (typeof it.order_qty === 'number') {
                    const tdOrder = cellByLabel(row, 'Order');
                    const flip = tdOrder?.querySelector('.flip') || tdOrder;
                    if (flip) {
                        flip.dataset.orderRaw = String(it.order_qty);
                        flip.textContent = Number(it.order_qty).toLocaleString(IDLOCALE);
                    }
                }
                if (typeof it.actual_start === 'string') {
                    const asEl = row.querySelector(`[data-item-id="${id}"][data-type="actual_start"]`);
                    if (asEl) asEl.textContent = it.actual_start || '--';
                }
                if (typeof it.end === 'string') {
                    const endEl = row.querySelector(`[data-item-id="${id}"][data-type="end"]`);
                    if (endEl) endEl.textContent = it.end || '--';
                }
                if (typeof it.balance === 'string') {
                    const balEl = row.querySelector(`[data-item-id="${id}"][data-type="balance"]`);
                    if (balEl) balEl.textContent = it.balance || '--';
                }

                const lineKey = getLineKeyOfRow(row);
                const newShift = classifyShift(row, lineKey);
                if (newShift !== (row.getAttribute('data-shift') || 'other')) row.setAttribute('data-shift', newShift);
                updateBarsForRow(row);
            }

            document.addEventListener('DOMContentLoaded', recomputeAll);

            document.addEventListener('DOMContentLoaded', () => {
                const currentDate = (document.querySelector('input[name="date"]')?.value) || new Date()
                    .toISOString().slice(0, 10);
                const onDirectUpdates = updates => {
                    (updates || []).forEach(applyUpdateItem);
                    recomputeAll();
                };
                if (window.prodPlanSSE?.eventSource && !window.__shiftCardsHookedV3) {
                    window.__shiftCardsHookedV3 = true;
                    window.prodPlanSSE.eventSource.addEventListener('directPullingUpdate', (e) => {
                        try {
                            const data = JSON.parse(e.data);
                            if (data?.date === currentDate) onDirectUpdates(data.updates);
                        } catch {}
                    });
                } else {
                    try {
                        const es = new EventSource(`/stream/direct-pulling-updates?date=${currentDate}`);
                        es.addEventListener('directPullingUpdate', (e) => {
                            try {
                                const data = JSON.parse(e.data);
                                if (data?.date === currentDate) onDirectUpdates(data.updates);
                            } catch {}
                        });
                        window.addEventListener('beforeunload', () => es.close());
                    } catch {}
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-toggle-table] tbody').forEach(tbody => {
                    try {
                        const mo = new MutationObserver(() => recomputeAll());
                        mo.observe(tbody, {
                            childList: true,
                            subtree: false
                        });
                    } catch {}
                });
            });
        })();
    </script>

    <script>
        (function() {
            const IDLOCALE = 'id-ID';

            const isSummaryRow = tr => tr?.getAttribute('data-summary-row') === '1';

            function readBackNo(tr) {
                // Untuk baris summary, pakai label apa adanya (biar C4–7 & C8–3 jadi entri terpisah)
                if (isSummaryRow(tr)) {
                    const td = $u.getCellByLabel(tr, 'Back No');
                    const el = td?.querySelector('.flip') || td;
                    return String(el?.textContent || '').trim(); // contoh: "CI12 (C4–7)"
                }
                // Untuk baris normal, pakai canonical (hapus suffix siklus kalau ada)
                const td = $u.getCellByLabel(tr, 'Back No');
                const el = td?.querySelector('.flip') || td;
                const raw = (el?.dataset?.backnoAlias || el?.dataset?.backnoRaw || el?.textContent || '').trim();
                if (!raw || raw === '--') return '';
                return $u.canonicalBackNoSplit(raw); // contoh: "CI12"
            }

            function readOrder(tr, isSummary) {
                if (isSummary) {
                    const txt = $u.getCellByLabel(tr, 'Order')?.querySelector('.flip')?.textContent;
                    return $u.int(txt);
                }
                const el = $u.getCellByLabel(tr, 'Order')?.querySelector('.flip') || $u.getCellByLabel(tr, 'Order');
                const ds = el?.dataset?.orderRaw;
                return ds != null && ds !== '' ? $u.int(ds) : $u.int(el?.textContent);
            }

            const readDP = (tr, isSummary) =>
                isSummary ? $u.int(tr.querySelector('[data-summary-dp]')?.textContent) :
                $u.int(tr.querySelector('[data-type="direct-pulling"]')?.textContent);

            const readSC = (tr, isSummary) =>
                isSummary ? $u.int(tr.querySelector('[data-summary-sc]')?.textContent) :
                $u.int(tr.querySelector('[data-type="stock-chute"]')?.textContent);

            function readCustomer(tr) {
                const td = $u.getCellByLabel(tr, 'Customer');
                const el = td?.querySelector('.flip') || td;
                return (el?.textContent || '').trim() || '--';
            }

            function collect(lineCode) {
                const wrap = document.querySelector(`[data-toggle-table="${lineCode}"]`);
                const tbody = wrap?.querySelector('tbody');
                const map = new Map();
                if (!tbody) return [];

                Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
                    // Lewati baris yang disembunyikan (placeholder hasil move/clone)
                    if (tr.style?.display === 'none') return;

                    const summary = isSummaryRow(tr);
                    const bn = readBackNo(tr);
                    if (!bn) return;

                    const ord = readOrder(tr, summary);
                    const dp = readDP(tr, summary);
                    const sc = readSC(tr, summary);
                    const cust = readCustomer(tr);

                    // Kunci pakai nama apa adanya:
                    //  - Summary: "CI12 (C4–7)" atau "CI12 (C8–3)" -> TERPISAH
                    //  - Normal : "CI12" (canonical)
                    const key = bn;
                    const rec = map.get(key) || {
                        backNo: key,
                        customer: cust,
                        orderQty: 0,
                        dp: 0,
                        sc: 0
                    };
                    rec.orderQty += ord;
                    rec.dp += dp;
                    rec.sc += sc;
                    if (!rec.customer || rec.customer === '--') rec.customer = cust;
                    map.set(key, rec);
                });

                // Urutkan by OrderQty desc, lalu alfabet backNo
                return Array.from(map.values()).sort((a, b) => (b.orderQty - a.orderQty) || a.backNo.localeCompare(b
                    .backNo));
            }

            let __lastSummary = {
                line: '',
                rows: []
            };

            function renderModal(lineCode) {
                const rows = collect(lineCode);
                __lastSummary = {
                    line: lineCode,
                    rows
                };

                // Statistik atas modal – dibuat dari data yang sama dengan card
                const totalBack = rows.length;
                const totalOrders = rows.reduce((s, r) => s + r.orderQty, 0);
                const completed = rows.reduce((s, r) => s + r.dp + r.sc, 0);
                const avg = totalBack > 0 ? Math.round(totalOrders / totalBack) : 0;

                document.getElementById('modalLineTitle').textContent = lineCode;
                document.getElementById('totalBackNumbers').textContent = totalBack.toLocaleString(IDLOCALE);
                document.getElementById('totalOrders').textContent = totalOrders.toLocaleString(IDLOCALE);
                document.getElementById('avgOrderPerBack').textContent = avg.toLocaleString(IDLOCALE);
                document.getElementById('completedOrders').textContent = completed.toLocaleString(IDLOCALE);

                const list = document.getElementById('backNumberList');
                list.innerHTML = '';
                if (!rows.length) {
                    list.innerHTML = '<div class="text-center text-muted py-4">No data available</div>';
                } else {
                    rows.forEach(r => {
                        const done = r.dp + r.sc;
                        const pct = r.orderQty > 0 ? Math.round((done / r.orderQty) * 100) : 0;
                        const status = done >= r.orderQty ? 'Complete' : 'In Progress';
                        const color = status === 'Complete' ? 'success' : 'warning';
                        const div = document.createElement('div');
                        div.className = 'back-number-item';
                        div.innerHTML = `
          <div class="d-flex flex-column">
            <div class="back-no">${r.backNo}</div>
            <div class="small number">${r.customer || '--'}</div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="text-end">
              <div class="order-qty">${r.orderQty.toLocaleString(IDLOCALE)}</div>
              <div class="small number">Order Qty</div>
            </div>
            <div class="text-end">
              <div class="fw-bold text-${color}">${done.toLocaleString(IDLOCALE)}</div>
              <div class="small number">Completed</div>
            </div>
            <div class="text-end">
              <div class="fw-bold">${pct}%</div>
              <div class="small number">Progress</div>
            </div>
          </div>`;
                        list.appendChild(div);
                    });
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('summaryModal')).show();
            }

            window.showSummary = renderModal;

            window.exportSummary = function() {
                const {
                    line,
                    rows
                } = __lastSummary;
                if (!rows.length) return;
                const header = ['Back Number', 'Customer', 'Order Qty', 'Direct Pulling', 'Stock Chute',
                    'Completed', 'Progress %', 'Status'
                ];
                const csv = [
                    header.join(','),
                    ...rows.map(r => {
                        const done = r.dp + r.sc;
                        const pct = r.orderQty > 0 ? Math.round((done / r.orderQty) * 100) : 0;
                        const status = done >= r.orderQty ? 'Complete' : 'In Progress';
                        return [
                            `"${r.backNo.replace(/"/g,'""')}"`,
                            `"${(r.customer||'--').replace(/"/g,'""')}"`,
                            r.orderQty, r.dp, r.sc, done, pct + '%', status
                        ].join(',');
                    })
                ].join('\n');

                const blob = new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `summary_${line}_${new Date().toISOString().slice(0,10)}.csv`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            };
        })();
    </script>

    <script>
        (function CardTotalsFromDOM() {
            /* disabled: handled by FixShiftCardsV3 */
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
