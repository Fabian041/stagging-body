@extends('layouts.root.main')

@section('main')
    <style>
        .ov-wrap {
            width: 100%;
            margin: 0 auto;
        }

        .ov-head {
            margin: 12px 0 16px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .ov-head h4 {
            font-size: 20px;
            font-weight: 850;
            color: var(--navy);
            margin: 0 0 4px;
            letter-spacing: -.02em;
        }

        .ov-head p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
            font-weight: 600;
        }

        .ov-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: var(--primary);
        }

        .ov-live .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
            animation: ovpulse 1.5s infinite;
        }

        @keyframes ovpulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .3;
            }
        }

        .ov-filter {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
            box-shadow: var(--shadow);
        }

        .ov-filter label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            margin: 0;
        }

        .ov-filter input[type=date] {
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text);
            font-size: 12.5px;
            padding: 0 10px;
            outline: none;
            font-weight: 600;
        }

        .ov-filter input[type=date]:focus {
            border-color: var(--sky);
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
            background: #fff;
        }

        .ov-preset {
            height: 30px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 800;
            cursor: pointer;
            transition: .15s;
        }

        .ov-preset:hover,
        .ov-preset.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .ov-filter .sep {
            flex: 1;
        }

        .ov-filter-loading {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
        }

        .ov-filter-loading::before {
            content: '';
            width: 14px;
            height: 14px;
            border: 2px solid rgba(0, 151, 216, .2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: ov-spin .7s linear infinite;
        }

        .ov-wrap.is-filter-loading .ov-filter input,
        .ov-wrap.is-filter-loading .ov-preset {
            pointer-events: none;
            opacity: .65;
        }

        .ov-kpis {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .ov-kpi {
            padding: 13px 15px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
            box-shadow: var(--shadow);
            border-left: 3px solid var(--primary);
            text-decoration: none !important;
            display: block;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .ov-kpi:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .ov-kpi--danger {
            border-left-color: #dc2626;
        }

        .ov-kpi--success {
            border-left-color: #16a34a;
        }

        .ov-kpi--amber {
            border-left-color: #d97706;
        }

        .ov-kpi__label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .ov-kpi__value {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 850;
            color: var(--navy);
            line-height: 1;
            letter-spacing: -.03em;
        }

        .ov-kpi__value small {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .ov-kpi__sub {
            margin-top: 5px;
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .ov-row {
            display: grid;
            gap: 14px;
            margin-bottom: 14px;
        }

        .ov-row--2a {
            grid-template-columns: 2fr 1fr;
        }

        .ov-row--2 {
            grid-template-columns: 1fr 1fr;
        }

        .ov-row--2b {
            grid-template-columns: 1fr 1.3fr;
        }

        .ov-row--2c {
            grid-template-columns: 1fr 1.6fr;
        }

        .ov-panel {
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .ov-panel--alert {
            border-color: #fecaca;
        }

        .ov-panel__head {
            padding: 13px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .ov-panel__title {
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-panel__title i {
            color: var(--primary);
        }

        .ov-panel--alert .ov-panel__title i {
            color: #dc2626;
        }

        .ov-panel__body {
            padding: 14px 18px;
            flex: 1;
        }

        .ov-panel__body--tight {
            padding: 4px 8px;
        }

        .ov-loading {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .ov-loading::before {
            content: '';
            width: 15px;
            height: 15px;
            margin-right: 8px;
            border: 2px solid rgba(0, 151, 216, .2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: ov-spin .7s linear infinite;
        }

        .ov-chart-loading {
            min-height: 290px;
        }

        .ov-activity-tabs {
            display: inline-flex;
            padding: 3px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--bg);
        }

        .ov-activity-tab {
            height: 28px;
            padding: 0 12px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
        }

        .ov-activity-tab.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 6px 14px rgba(0, 151, 216, .18);
        }

        .ov-activity-tab[data-activity-mode="produksi"].active {
            background: #16a34a;
            box-shadow: 0 6px 14px rgba(22, 163, 74, .18);
        }

        .ov-activity-tab[data-activity-mode="all"].active {
            background: #d97706;
            box-shadow: 0 6px 14px rgba(217, 119, 6, .18);
        }

        .ov-activity-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            margin: -2px 0 8px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .ov-activity-legend__label {
            margin: 0 2px;
        }

        .ov-activity-legend__swatch {
            width: 24px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid rgba(15, 23, 42, .08);
        }

        .ov-heatmap-peak {
            animation: ov-peak-glow 1.4s ease-in-out infinite;
            filter: brightness(1.08) drop-shadow(0 0 3px rgba(255, 255, 255, .65));
            stroke: #fff !important;
            stroke-width: 1.2px !important;
        }

        @keyframes ov-peak-glow {

            0%,
            100% {
                filter: brightness(1.08) drop-shadow(0 0 3px rgba(255, 255, 255, .65));
            }

            50% {
                filter: brightness(1.28) drop-shadow(0 0 9px rgba(255, 255, 255, .95));
            }
        }

        .ov-peak-mark {
            fill: #fff;
            stroke: rgba(15, 23, 42, .5);
            paint-order: stroke;
            font-weight: 800;
            font-family: inherit;
            pointer-events: none;
        }

        .ov-activity-insight {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .ov-insight-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            margin: 0 0 9px;
        }

        .ov-insight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
            gap: 10px;
        }

        .ov-chip {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
        }

        .ov-chip__l {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .ov-chip__l i {
            color: #d97706;
        }

        .ov-chip__v {
            font-size: 18px;
            font-weight: 850;
            color: var(--navy);
            letter-spacing: -.02em;
        }

        .ov-chip__s {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 1px;
        }

        .ov-t3 {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .ov-t3__item {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 99px;
            padding: 4px 12px 4px 4px;
        }

        .ov-t3__rank {
            width: 19px;
            height: 19px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
            background: #fef3c7;
            color: #92400e;
        }

        .ov-t3__rank--1 {
            background: #d97706;
            color: #fff;
        }

        .ov-t3__name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
        }

        .ov-t3__val {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--navy);
        }

        .ov-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1080;
            padding: 16px;
        }

        .ov-modal.open {
            display: flex;
        }

        .ov-modal__card {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            max-height: 80vh;
            overflow: auto;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
        }

        .ov-modal__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            background: var(--card);
        }

        .ov-modal__title {
            font-size: 14px;
            font-weight: 850;
            color: var(--navy);
        }

        .ov-modal__title small {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .ov-modal__close {
            border: 0;
            background: var(--bg);
            color: var(--text-muted);
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            flex-shrink: 0;
        }

        .ov-modal__body {
            padding: 14px 18px;
        }

        .ov-empty {
            padding: 24px 12px;
            color: var(--text-muted);
            font-size: 12px;
            text-align: center;
        }

        @keyframes ov-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .ov-cycle {
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .ov-cycle__top {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
        }

        .ov-cycle__top span:last-child {
            color: var(--text-muted);
            font-weight: 600;
        }

        .ov-bar {
            height: 9px;
            border-radius: 99px;
            background: #E2E8F0;
            overflow: hidden;
        }

        .ov-bar__fill {
            height: 100%;
            border-radius: 99px;
            transition: width .5s ease;
        }

        .ov-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .ov-table thead th {
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            font-weight: 800;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .ov-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .ov-table tbody tr:last-child td {
            border-bottom: none;
        }

        .ov-table tbody tr:hover td {
            background: var(--bg);
        }

        /* tabel error terbaru: paksa satu baris (truncate) biar tinggi kartu konsisten */
        .ov-table--fixed {
            table-layout: fixed;
        }

        .ov-table--fixed td.ov-ellip {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ov-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            background: #eef2ff;
            color: var(--primary);
            border: 1px solid #dbe3ff;
            text-decoration: none;
        }

        .ov-gap {
            font-weight: 800;
            color: #dc2626;
        }

        .ov-area-tag {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .ov-empty {
            padding: 26px 12px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .ov-delta {
            font-weight: 800;
        }

        .ov-errtrend {
            margin-top: 16px;
            padding-top: 13px;
            border-top: 1px solid var(--border);
        }

        .ov-errtrend__head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 9px;
        }

        .ov-errtrend__lbl {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .ov-errtrend__bars {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 52px;
        }

        .ov-errtrend__bar {
            flex: 1;
            border-radius: 2px;
            min-height: 2px;
        }

        /* 🥚 easter egg: minesweeper di section heatmap */
        .ov-ms {
            padding: 6px 0 2px;
        }

        .ov-ms__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .ov-ms__hint {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #d97706;
        }

        .ov-ms__hud {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-ms__counter {
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 800;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 9px;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .ov-ms__btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
        }

        .ov-ms__btn:hover {
            background: var(--bg);
        }

        .ov-ms__btn.on {
            background: #d97706;
            border-color: #d97706;
            color: #fff;
        }

        .ov-ms__grid {
            display: grid;
            gap: 3px;
            justify-content: center;
            margin: 2px auto 12px;
            max-width: 100%;
            overflow-x: auto;
        }

        .ov-ms__cell {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            user-select: none;
            border: none;
            padding: 0;
        }

        .ov-ms__cell.cov:hover {
            filter: brightness(1.08);
        }

        .ov-ms__cell.rev {
            background: var(--bg);
            cursor: default;
        }

        .ov-ms__cell.mine {
            background: #dc2626 !important;
            color: #fff;
        }

        .ov-ms__foot {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            font-weight: 600;
        }

        @media (max-width: 1100px) {
            .ov-kpis {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .ov-row--2a,
            .ov-row--2,
            .ov-row--2b,
            .ov-row--2c {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .ov-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ov-panel__head--activity {
                align-items: flex-start;
                flex-direction: column;
            }

        }
    </style>

    <div class="ov-wrap">
        <div class="ov-head">
            <div>
                <h4>Dashboard Overview</h4>
                <p>Ringkasan &amp; analisis lintas modul — PPIC · Production · PIS · Error</p>
            </div>
            <span class="ov-live"><span class="dot"></span> LIVE · <span
                    id="ovUpdated">{{ now()->format('H:i:s') }}</span></span>
        </div>

        {{-- ===== FILTER ===== --}}
        <div class="ov-filter">
            <label>Dari</label>
            <input type="date" id="ovFrom" value="{{ $from }}">
            <label>Sampai</label>
            <input type="date" id="ovTo" value="{{ $to }}">
            <button type="button" class="ov-preset" data-preset="today">Hari Ini</button>
            <button type="button" class="ov-preset" data-preset="7">7 Hari</button>
            <button type="button" class="ov-preset" data-preset="30">30 Hari</button>
            <span class="sep"></span>
            <span style="font-size:11px;color:var(--text-muted);font-weight:600;">Auto-refresh tiap 60 dtk · vs periode
                sebelumnya</span>
            <span id="ovFilterLoading" class="ov-filter-loading" hidden>Memuat data periode dipilih</span>
        </div>

        {{-- ===== KPI STRIP ===== --}}
        <div class="ov-kpis">
            <a class="ov-kpi" href="{{ route('dashboard.delivery') }}">
                <div class="ov-kpi__label">Preparation</div>
                <div class="ov-kpi__value"><span id="k-prepComplete">—</span><small>/<span
                            id="k-prepTotal">—</span></small></div>
                <div class="ov-kpi__sub" id="sub-prep">memuat data…</div>
            </a>
            <a class="ov-kpi" href="{{ route('loadingList.index') }}">
                <div class="ov-kpi__label">Kanban Discan</div>
                <div class="ov-kpi__value" id="k-kanban">—</div>
                <div class="ov-kpi__sub" id="sub-kanban">memuat data…</div>
            </a>
            <a class="ov-kpi ov-kpi--success" href="{{ route('loadingList.index') }}">
                <div class="ov-kpi__label">Pulling Selesai</div>
                <div class="ov-kpi__value" id="k-pulling">—</div>
                <div class="ov-kpi__sub" id="sub-pulling">memuat data…</div>
            </a>
            <a class="ov-kpi" href="{{ route('dashboard.prodResult') }}">
                <div class="ov-kpi__label">Produksi</div>
                <div class="ov-kpi__value" id="k-produksi">—</div>
                <div class="ov-kpi__sub" id="sub-produksi">memuat data…</div>
            </a>
            <a class="ov-kpi ov-kpi--danger" href="{{ route('dashboard.productionStock') }}">
                <div class="ov-kpi__label">Stok &lt; Standar</div>
                <div class="ov-kpi__value"><span
                        id="k-understock">—</span><small>/<span id="k-totalpart">—</span></small></div>
                <div class="ov-kpi__sub" id="sub-understock">memuat data…</div>
            </a>
            <a class="ov-kpi ov-kpi--amber" href="{{ route('error.log') }}">
                <div class="ov-kpi__label">Error</div>
                <div class="ov-kpi__value" id="k-errperiod">—</div>
                <div class="ov-kpi__sub" id="sub-error">memuat data…</div>
            </a>
        </div>

        {{-- ===== DELIVERY BERISIKO (#6) ===== --}}
        <div class="ov-row">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-truck-fast"></i> Delivery Berisiko (hari ini &amp; besok)
                    </div>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovAtRisk"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>

        {{-- ===== Trend + Stock ===== --}}
        <div class="ov-row ov-row--2a">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-chart-area"></i> Aktivitas Harian</div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovTrendChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
                </div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-boxes"></i> Kesehatan Stok</div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovStockChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
                </div>
            </div>
        </div>

        {{-- ===== Produksi per Line + Preparation per Customer (#2) ===== --}}
        <div class="ov-row ov-row--2">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-industry"></i> Produksi per Line</div>
                </div>
                <div class="ov-panel__body" id="ovProdByLine"><div class="ov-loading">Memuat data</div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-users"></i> Preparation per Customer</div>
                </div>
                <div class="ov-panel__body" id="ovPrepByCustomer"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>

        {{-- ===== Jam tersibuk (#5) ===== --}}
        <div class="ov-row">
            <div class="ov-panel">
                <div class="ov-panel__head ov-panel__head--activity">
                    <div class="ov-panel__title" id="ovHeatmapTitle" style="user-select:none;"><i
                            class="fas fa-clock"></i> Jam Tersibuk — Aktivitas Pulling &amp;
                        Produksi (hari × jam)</div>
                    <div class="ov-activity-tabs" role="group" aria-label="Filter heatmap activity">
                        <button type="button" class="ov-activity-tab" data-activity-mode="produksi">Prod</button>
                        <button type="button" class="ov-activity-tab" data-activity-mode="pulling">Pulling</button>
                        <button type="button" class="ov-activity-tab active" data-activity-mode="all">All</button>
                    </div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovActivityLegend" class="ov-activity-legend"></div>
                    <div id="ovHourlyChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
                    <div id="ovActivityInsight" class="ov-activity-insight" hidden></div>
                    {{-- 🥚 easter egg: minesweeper (muncul kalau judul diklik 5x cepat) --}}
                    <div id="ovMinesweeper" class="ov-ms" hidden>
                        <div class="ov-ms__head">
                            <span class="ov-ms__hint">Easter egg · Minesweeper</span>
                            <div class="ov-ms__hud">
                                <span class="ov-ms__counter"><i class="fas fa-flag"></i> <span
                                        id="ovMsCount">18</span></span>
                                <button type="button" class="ov-ms__btn" id="ovMsFlag" title="Mode tandai ranjau"
                                    aria-label="Mode tandai ranjau"><i class="fas fa-flag"></i></button>
                                <button type="button" class="ov-ms__btn" id="ovMsReset" title="Main lagi"
                                    aria-label="Main lagi"><i class="fas fa-face-smile"></i></button>
                                <button type="button" class="ov-ms__btn" id="ovMsClose" title="Kembali ke heatmap"
                                    aria-label="Kembali ke heatmap"><i class="fas fa-xmark"></i></button>
                            </div>
                        </div>
                        <div class="ov-ms__grid" id="ovMsGrid"></div>
                        <div class="ov-ms__foot" id="ovMsFoot">Klik kiri buka · klik kanan / tombol bendera tandai ranjau
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Preparation per cycle + Top under-stock ===== --}}
        <div class="ov-row ov-row--2b">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-layer-group"></i> Preparation per Cycle</div>
                </div>
                <div class="ov-panel__body" id="ovCycle"><div class="ov-loading">Memuat data</div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-arrow-down"></i> Part Paling Kekurangan Stok</div>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovTopUnder"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>

        {{-- ===== Error per area + Recent errors ===== --}}
        <div class="ov-row ov-row--2c">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-chart-bar"></i> Error per Area</div>
                </div>
                <div class="ov-panel__body" id="ovErrByArea"><div class="ov-loading">Memuat data</div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-exclamation-triangle"></i> Error Terbaru</div>
                    <a href="{{ route('error.log') }}"
                        style="font-size:11px;font-weight:700;color:var(--primary);text-decoration:none;">Lihat semua →</a>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovRecentErrors"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>
    </div>

    {{-- ===== Modal drill-down jam tersibuk (#4) ===== --}}
    <div class="ov-modal" id="ovSlotModal" role="dialog" aria-modal="true" aria-labelledby="ovSlotTitle">
        <div class="ov-modal__card">
            <div class="ov-modal__head">
                <div class="ov-modal__title" id="ovSlotTitle">Detail slot</div>
                <button type="button" class="ov-modal__close" id="ovSlotClose" aria-label="Tutup">&times;</button>
            </div>
            <div class="ov-modal__body" id="ovSlotBody"><div class="ov-loading">Memuat data</div></div>
        </div>
    </div>

    <script>
        (function() {
            var DATA_URL = @json(route('dashboard.overview.data'));
            var SLOT_URL = @json(route('dashboard.overview.slot'));
            var STOCK_URL = @json(route('dashboard.productionStock'));
            var LL_URL = @json(url('/loading-list'));
            var CHART_URL = @json(asset('assets/js/apexcharts.js'));
            var initial = @json($d ?? null);
            var trendChart = null,
                stockChart = null,
                hourlyChart = null,
                timer = null,
                chartsLoading = null,
                latestData = null,
                activityMode = 'all',
                requestId = 0;

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;'
                    } [c];
                });
            }

            function fmt(n) {
                return Number(n || 0).toLocaleString('id-ID');
            }

            var activityPalettes = {
                pulling: {
                    base: '#0097D8',
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', label: '0' },
                        { from: 1, to: 10, color: '#dbeafe', label: '1-10' },
                        { from: 11, to: 25, color: '#bfdbfe', label: '11-25' },
                        { from: 26, to: 50, color: '#7dd3fc', label: '26-50' },
                        { from: 51, to: 100, color: '#38bdf8', label: '51-100' },
                        { from: 101, to: 200, color: '#0ea5e9', label: '101-200' },
                        { from: 201, to: 999999, color: '#0369a1', label: '201+' }
                    ]
                },
                produksi: {
                    base: '#16a34a',
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', label: '0' },
                        { from: 1, to: 10, color: '#dcfce7', label: '1-10' },
                        { from: 11, to: 25, color: '#bbf7d0', label: '11-25' },
                        { from: 26, to: 50, color: '#86efac', label: '26-50' },
                        { from: 51, to: 100, color: '#4ade80', label: '51-100' },
                        { from: 101, to: 200, color: '#22c55e', label: '101-200' },
                        { from: 201, to: 999999, color: '#15803d', label: '201+' }
                    ]
                },
                all: {
                    base: '#d97706',
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', label: '0' },
                        { from: 1, to: 20, color: '#fef3c7', label: '1-20' },
                        { from: 21, to: 50, color: '#fde68a', label: '21-50' },
                        { from: 51, to: 100, color: '#fbbf24', label: '51-100' },
                        { from: 101, to: 150, color: '#f59e0b', label: '101-150' },
                        { from: 151, to: 200, color: '#d97706', label: '151-200' },
                        { from: 201, to: 999999, color: '#92400e', label: '201+' }
                    ]
                }
            };

            function activityPalette(mode) {
                return activityPalettes[mode] || activityPalettes.all;
            }

            function setText(id, v) {
                var el = document.getElementById(id);
                if (el) el.textContent = v;
            }

            function setHtml(id, v) {
                var el = document.getElementById(id);
                if (el) el.innerHTML = v;
            }

            function setFilterLoading(active) {
                var wrap = document.querySelector('.ov-wrap');
                var label = document.getElementById('ovFilterLoading');
                if (wrap) {
                    wrap.classList.toggle('is-filter-loading', active);
                    wrap.setAttribute('aria-busy', active ? 'true' : 'false');
                }
                if (label) label.hidden = !active;
            }

            function resetChart(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.classList.add('ov-loading', 'ov-chart-loading');
                el.textContent = 'Memuat grafik';
            }

            function setChartError(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('ov-loading', 'ov-chart-loading');
                el.innerHTML = '<div class="ov-empty">Grafik belum berhasil dimuat.</div>';
            }

            function showFilterLoadingState() {
                if (trendChart) trendChart.destroy();
                if (stockChart) stockChart.destroy();
                if (hourlyChart) hourlyChart.destroy();
                trendChart = stockChart = hourlyChart = null;

                setText('k-prepComplete', '—');
                setText('k-prepTotal', '—');
                setText('k-kanban', '—');
                setText('k-pulling', '—');
                setText('k-produksi', '—');
                setText('k-understock', '—');
                setText('k-totalpart', '—');
                setText('k-errperiod', '—');
                ['sub-prep', 'sub-kanban', 'sub-pulling', 'sub-produksi', 'sub-understock', 'sub-error'].forEach(function(id) {
                    setText(id, 'memuat data…');
                });

                resetChart('ovTrendChart');
                resetChart('ovStockChart');
                resetChart('ovHourlyChart');
                setHtml('ovActivityLegend', '');
                ['ovAtRisk', 'ovProdByLine', 'ovPrepByCustomer', 'ovCycle', 'ovTopUnder', 'ovErrByArea',
                    'ovRecentErrors'
                ].forEach(function(id) {
                    setHtml(id, '<div class="ov-loading">Memuat data</div>');
                });
            }

            function limit(s, n) {
                s = String(s == null ? '' : s);
                return s.length > n ? s.slice(0, n) + '…' : s;
            }

            // ▲▼ delta. goodUp=true: naik itu bagus (hijau); false: naik itu jelek (merah, mis. error)
            function deltaHtml(delta, goodUp) {
                if (delta === null || delta === undefined) return '';
                var good = delta === 0 ? null : (goodUp ? delta > 0 : delta < 0);
                var color = good === null ? 'var(--text-muted)' : (good ? '#16a34a' : '#dc2626');
                var arrow = delta > 0 ? '▲' : (delta < 0 ? '▼' : '•');
                return '<span class="ov-delta" style="color:' + color + ';">' + arrow + ' ' + Math.abs(delta) +
                    '%</span> vs lalu';
            }

            function renderBars(rows, getLabel, getTop, getPct, getColor) {
                return '<div class="ov-cycle">' + rows.map(function(r) {
                    var pct = getPct(r);
                    return '<div><div class="ov-cycle__top"><span>' + esc(getLabel(r)) + '</span><span>' +
                        getTop(r) + '</span></div>' +
                        '<div class="ov-bar"><div class="ov-bar__fill" style="width:' + pct + '%;background:' +
                        getColor(r) + ';"></div></div></div>';
                }).join('') + '</div>';
            }

            function renderCycle(rows) {
                if (!rows || !rows.length)
            return '<div class="ov-empty">Tidak ada loading list pada periode ini.</div>';
                return renderBars(rows,
                    function(c) {
                        return 'Cycle ' + c.cycle;
                    },
                    function(c) {
                        return c.complete + '/' + c.total + ' · ' + c.pct + '%';
                    },
                    function(c) {
                        return c.pct;
                    },
                    function(c) {
                        return c.pct >= 100 ? '#16a34a' : (c.pct > 0 ? '#d97706' : '#dc2626');
                    });
            }

            function renderPrepByCustomer(rows) {
                if (!rows || !rows.length)
            return '<div class="ov-empty">Tidak ada loading list pada periode ini.</div>';
                return renderBars(rows,
                    function(c) {
                        return c.customer;
                    },
                    function(c) {
                        return c.complete + '/' + c.total + ' · ' + c.pct + '%';
                    },
                    function(c) {
                        return c.pct;
                    },
                    function(c) {
                        return c.pct >= 100 ? '#16a34a' : (c.pct > 0 ? '#d97706' : '#dc2626');
                    });
            }

            function renderProdByLine(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Belum ada produksi pada periode ini.</div>';
                var max = Math.max.apply(null, rows.map(function(r) {
                    return r.c;
                })) || 1;
                return renderBars(rows,
                    function(r) {
                        return r.line || '-';
                    },
                    function(r) {
                        return r.c;
                    },
                    function(r) {
                        return Math.round((r.c / max) * 100);
                    },
                    function() {
                        return '#0097D8';
                    });
            }

            function errDeltaArrow(d) {
                if (d === null || d === undefined || d === 0) return '';
                // naik = jelek untuk error (merah), turun = bagus (hijau)
                var color = d > 0 ? '#dc2626' : '#16a34a';
                return ' <span style="font-weight:800;color:' + color + ';">' + (d > 0 ? '▲' : '▼') +
                    Math.abs(d) + '%</span>';
            }

            function renderErrTrend() {
                var trend = (latestData && latestData.trend) || [];
                var vals = trend.map(function(t) {
                    return parseInt(t.err) || 0;
                });
                var max = vals.length ? Math.max.apply(null, vals) : 0;
                if (!max) return '';
                var n = vals.length;
                var bars = vals.map(function(v, i) {
                    var h = Math.max(2, Math.round(v / max * 50));
                    var shade = i >= n - Math.ceil(n / 4) ? '#dc2626' : (i >= n / 2 ? '#ef4444' : '#fca5a5');
                    return '<div class="ov-errtrend__bar" style="height:' + h + 'px;background:' + shade +
                        ';" title="' + esc(trend[i].label) + ': ' + fmt(v) + ' error"></div>';
                }).join('');
                var dErr = latestData && latestData.delta ? latestData.delta.err : null;
                var head = '<span class="ov-errtrend__lbl">Tren error harian</span>' +
                    (dErr ? '<span style="font-size:11px;font-weight:800;color:' + (dErr > 0 ? '#dc2626' :
                        '#16a34a') + ';">' + (dErr > 0 ? '▲' : '▼') + ' ' + Math.abs(dErr) + '% vs lalu</span>' : '');
                return '<div class="ov-errtrend"><div class="ov-errtrend__head">' + head +
                    '</div><div class="ov-errtrend__bars">' + bars + '</div></div>';
            }

            function renderErrByArea(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada error pada periode ini. 👍</div>';
                var max = Math.max.apply(null, rows.map(function(r) {
                    return r.c;
                })) || 1;
                var bars = renderBars(rows,
                    function(r) {
                        return r.area || '-';
                    },
                    function(r) {
                        return fmt(r.c) + errDeltaArrow(r.delta);
                    },
                    function(r) {
                        return Math.round((r.c / max) * 100);
                    },
                    function() {
                        return '#dc2626';
                    });
                return bars + renderErrTrend();
            }

            function activityChartOptions(mode) {
                var palette = activityPalette(mode);
                return {
                    colors: [palette.base],
                    plotOptions: {
                        heatmap: {
                            radius: 3,
                            enableShades: true,
                            shadeIntensity: 0.55,
                            colorScale: {
                                ranges: palette.ranges.map(function(r) {
                                    return {
                                        from: r.from,
                                        to: r.to,
                                        color: r.color
                                    };
                                })
                            }
                        }
                    }
                };
            }

            function renderActivityLegend(mode) {
                var swatches = activityPalette(mode).ranges.map(function(r) {
                    return '<span class="ov-activity-legend__swatch" title="' + esc(r.label) +
                        ' aktivitas" style="background:' + r.color + ';"></span>';
                }).join('');
                return '<span class="ov-activity-legend__label">sepi</span>' + swatches +
                    '<span class="ov-activity-legend__label">ramai</span>';
            }

            function activityDayName(name) {
                return String(name || '').replace(/\s+(Pulling|Produksi)$/, '');
            }

            function activitySeries(rows, mode) {
                rows = rows || [];
                if (mode === 'pulling' || mode === 'produksi') {
                    var suffix = mode === 'pulling' ? ' Pulling' : ' Produksi';
                    return rows.filter(function(row) {
                        return String(row.name || '').slice(-suffix.length) === suffix;
                    }).map(function(row) {
                        return {
                            name: activityDayName(row.name),
                            data: row.data || []
                        };
                    });
                }

                var order = [],
                    byDay = {};
                rows.forEach(function(row) {
                    var day = activityDayName(row.name);
                    if (!byDay[day]) {
                        order.push(day);
                        byDay[day] = {
                            name: day,
                            data: (row.data || []).map(function(point) {
                                return {
                                    x: point.x,
                                    y: 0
                                };
                            })
                        };
                    }
                    (row.data || []).forEach(function(point, i) {
                        if (!byDay[day].data[i]) byDay[day].data[i] = {
                            x: point.x,
                            y: 0
                        };
                        byDay[day].data[i].y += parseInt(point.y) || 0;
                    });
                });

                return order.map(function(day) {
                    return byDay[day];
                });
            }

            function peakActivity(series) {
                var best = {
                        seriesIndex: -1,
                        dataPointIndex: -1,
                        value: 0
                    };
                (series || []).forEach(function(row, seriesIndex) {
                    (row.data || []).forEach(function(point, dataPointIndex) {
                        var value = parseInt(point.y) || 0;
                        if (value > best.value) {
                            best = {
                                seriesIndex: seriesIndex,
                                dataPointIndex: dataPointIndex,
                                value: value
                            };
                        }
                    });
                });

                return best.value > 0 ? best : null;
            }

            function highlightPeakActivity() {
                var chartEl = document.getElementById('ovHourlyChart');
                if (!chartEl || !latestData) return;
                var rects = chartEl.querySelectorAll('.apexcharts-heatmap-rect, .apexcharts-heatmap-series rect');
                rects.forEach(function(rect) {
                    rect.classList.remove('ov-heatmap-peak');
                });
                var oldMark = chartEl.querySelector('.ov-peak-mark');
                if (oldMark) oldMark.remove();

                var peak = peakActivity(activitySeries(latestData.heatmap, activityMode));
                if (!peak) return;

                var peakRect = chartEl.querySelector('.apexcharts-heatmap-rect[index="' + peak.seriesIndex +
                    '"][j="' + peak.dataPointIndex + '"]');
                if (!peakRect) {
                    rects.forEach(function(rect) {
                        if ((parseInt(rect.getAttribute('val')) || 0) === peak.value) peakRect = rect;
                    });
                }
                if (peakRect) {
                    peakRect.classList.add('ov-heatmap-peak');

                    var bb = peakRect.getBBox();
                    var label = fmt(peak.value);
                    // ukuran font nyesuain lebar cell DAN jumlah karakter biar angka ribuan tetap muat
                    var fontSize = Math.max(8, Math.min(bb.height * 0.55, bb.width * 0.85 / (label.length * 0.6)));
                    var mark = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    mark.setAttribute('class', 'ov-peak-mark');
                    mark.setAttribute('x', bb.x + bb.width / 2);
                    mark.setAttribute('y', bb.y + bb.height / 2);
                    mark.setAttribute('text-anchor', 'middle');
                    mark.setAttribute('dominant-baseline', 'central');
                    mark.setAttribute('font-size', fontSize);
                    mark.style.strokeWidth = (fontSize * 0.22) + 'px';
                    mark.textContent = label;
                    peakRect.parentNode.appendChild(mark);
                }
            }

            function chip(icon, label, value, sub) {
                return '<div class="ov-chip"><div class="ov-chip__l"><i class="fas ' + icon + '"></i> ' +
                    esc(label) + '</div><div class="ov-chip__v">' + esc(value) + '</div>' +
                    '<div class="ov-chip__s">' + esc(sub) + '</div></div>';
            }

            // #1 insight strip + #2 top-3 slot — dihitung dari data yang sudah ke-load
            function renderActivityInsight() {
                var el = document.getElementById('ovActivityInsight');
                if (!el) return;
                if (!latestData) {
                    el.hidden = true;
                    el.innerHTML = '';
                    return;
                }

                var series = activitySeries(latestData.heatmap, activityMode);
                var flat = [],
                    hourTotals = {},
                    nonzero = [];
                series.forEach(function(row) {
                    (row.data || []).forEach(function(p) {
                        var v = parseInt(p.y) || 0,
                            hour = parseInt(p.x) || 0;
                        hourTotals[hour] = (hourTotals[hour] || 0) + v;
                        if (v > 0) {
                            flat.push({ day: row.name, hour: hour, value: v });
                            nonzero.push(v);
                        }
                    });
                });

                if (!flat.length) {
                    el.hidden = false;
                    el.innerHTML = '<div class="ov-empty">Belum ada aktivitas pada periode ini.</div>';
                    return;
                }

                flat.sort(function(a, b) {
                    return b.value - a.value;
                });
                var peak = flat[0],
                    top3 = flat.slice(0, 3);

                // band 3 jam tersibuk (sliding window)
                var band = { start: 0, sum: -1 };
                for (var h = 0; h <= 21; h++) {
                    var s = (hourTotals[h] || 0) + (hourTotals[h + 1] || 0) + (hourTotals[h + 2] || 0);
                    if (s > band.sum) band = { start: h, sum: s };
                }

                // jam paling lengang di antara jam yang ada aktivitasnya
                var activeHours = Object.keys(hourTotals).map(Number).filter(function(hh) {
                    return hourTotals[hh] > 0;
                });
                var quiet = activeHours.reduce(function(min, hh) {
                    return hourTotals[hh] < hourTotals[min] ? hh : min;
                }, activeHours[0]);

                var avg = nonzero.reduce(function(a, b) {
                    return a + b;
                }, 0) / nonzero.length;
                var ratio = avg > 0 ? (peak.value / avg) : 0;

                var pad = function(x) {
                    return (x < 10 ? '0' : '') + x;
                };
                var hhmm = function(x) {
                    return pad(x) + ':00';
                };

                var chips =
                    chip('fa-fire', 'Tersibuk', fmt(peak.value), peak.day + ' ' + hhmm(peak.hour)) +
                    chip('fa-clock', 'Jam puncak', pad(band.start) + '–' + pad(band.start + 2), 'band tersibuk') +
                    chip('fa-moon', 'Paling lengang', hhmm(quiet), 'cocok maintenance') +
                    chip('fa-bolt', 'Puncak vs rata²', ratio.toFixed(1) + '×', ratio >= 2 ? 'beban spiky' :
                        'beban merata');

                var rows = top3.map(function(t, i) {
                    return '<span class="ov-t3__item">' +
                        '<span class="ov-t3__rank ov-t3__rank--' + (i + 1) + '">' + (i + 1) + '</span>' +
                        '<span class="ov-t3__name">' + esc(t.day) + ' ' + hhmm(t.hour) + '</span>' +
                        '<span class="ov-t3__val">' + fmt(t.value) + '</span></span>';
                }).join('');

                el.hidden = false;
                el.innerHTML =
                    '<p class="ov-insight-label">Ringkasan &amp; tindak lanjut</p>' +
                    '<div class="ov-insight-grid">' + chips + '</div>' +
                    '<p class="ov-insight-label" style="margin:12px 0 0;">3 slot tersibuk · klik cell untuk detail</p>' +
                    '<div class="ov-t3">' + rows + '</div>';
            }

            // #4 drill-down: klik cell heatmap → detail per tanggal pada slot tsb
            var slotReqId = 0;

            function openSlotDetail(dayName, hour) {
                var dowByDay = { 'Min': 1, 'Sen': 2, 'Sel': 3, 'Rab': 4, 'Kam': 5, 'Jum': 6, 'Sab': 7 };
                var day = String(dayName || '').replace(/\s+(Pulling|Produksi)$/, '');
                var dow = dowByDay[day];
                if (!dow) return;
                var pad = function(x) {
                    return (x < 10 ? '0' : '') + x;
                };
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;

                document.getElementById('ovSlotTitle').innerHTML = esc(day) + ' ' + pad(hour) + ':00' +
                    '<small>' + esc(from) + ' s/d ' + esc(to) + ' · mode ' + esc(activityMode) + '</small>';
                document.getElementById('ovSlotBody').innerHTML = '<div class="ov-loading">Memuat data</div>';
                document.getElementById('ovSlotModal').classList.add('open');

                var rid = ++slotReqId;
                var qs = '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) +
                    '&dow=' + dow + '&hour=' + hour + '&mode=' + encodeURIComponent(activityMode);
                fetch(SLOT_URL + qs, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        if (!r.ok) throw new Error('slot');
                        return r.json();
                    })
                    .then(function(d) {
                        if (rid !== slotReqId) return;
                        document.getElementById('ovSlotBody').innerHTML = renderSlotDetail(d);
                    })
                    .catch(function() {
                        if (rid !== slotReqId) return;
                        document.getElementById('ovSlotBody').innerHTML =
                            '<div class="ov-empty">Gagal memuat detail slot.</div>';
                    });
            }

            function renderSlotDetail(d) {
                var rows = (d && d.detail) || [];
                if (!rows.length) return '<div class="ov-empty">Tidak ada aktivitas pada slot ini.</div>';
                var showP = activityMode !== 'produksi',
                    showS = activityMode !== 'pulling';
                var totP = 0,
                    totS = 0;
                var body = rows.map(function(r) {
                    var p = parseInt(r.pulling) || 0,
                        s = parseInt(r.produksi) || 0;
                    totP += p;
                    totS += s;
                    return '<tr><td>' + esc(r.date) + '</td>' +
                        (showP ? '<td class="text-right">' + fmt(p) + '</td>' : '') +
                        (showS ? '<td class="text-right">' + fmt(s) + '</td>' : '') +
                        '<td class="text-right" style="font-weight:800;color:var(--navy);">' + fmt(p + s) +
                        '</td></tr>';
                }).join('');
                var head = '<tr><th>Tanggal</th>' +
                    (showP ? '<th class="text-right">Pulling</th>' : '') +
                    (showS ? '<th class="text-right">Produksi</th>' : '') +
                    '<th class="text-right">Total</th></tr>';
                var foot = '<tr><td style="font-weight:800;">Total</td>' +
                    (showP ? '<td class="text-right" style="font-weight:800;">' + fmt(totP) + '</td>' : '') +
                    (showS ? '<td class="text-right" style="font-weight:800;">' + fmt(totS) + '</td>' : '') +
                    '<td class="text-right" style="font-weight:800;color:var(--navy);">' + fmt(totP + totS) +
                    '</td></tr>';
                return '<table class="ov-table"><thead>' + head + '</thead><tbody>' + body + foot +
                    '</tbody></table>';
            }

            function schedulePeakHighlight() {
                renderActivityInsight();
                setTimeout(highlightPeakActivity, 80);
            }

            function setActivityMode(mode) {
                activityMode = mode || 'all';
                document.querySelectorAll('.ov-activity-tab').forEach(function(button) {
                    var active = button.getAttribute('data-activity-mode') === activityMode;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                if (hourlyChart && latestData) {
                    hourlyChart.updateOptions(activityChartOptions(activityMode), false, false);
                    hourlyChart.updateSeries(activitySeries(latestData.heatmap, activityMode));
                    schedulePeakHighlight();
                }
                setHtml('ovActivityLegend', renderActivityLegend(activityMode));
            }

            function renderTopUnder(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Semua part di atas/standar stok. 👍</div>';
                var body = rows.map(function(p) {
                    var url = STOCK_URL + '?back_number=' + encodeURIComponent(p.back_number || '');
                    return '<tr><td><a class="ov-badge" href="' + url + '">' + esc(p.back_number || '-') +
                        '</a></td>' +
                        '<td>' + esc(p.line || '-') + '</td>' +
                        '<td class="text-right">' + (parseInt(p.cur) || 0) + '</td>' +
                        '<td class="text-right">' + (parseInt(p.std) || 0) + '</td>' +
                        '<td class="text-right ov-gap">-' + (parseInt(p.gap) || 0) + '</td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Back No</th><th>Line</th><th class="text-right">Stok</th><th class="text-right">Std</th><th class="text-right">Kurang</th></tr></thead><tbody>' +
                    body + '</tbody></table>';
            }

            function renderRecentErrors(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Belum ada error tercatat. 👍</div>';
                var body = rows.map(function(e) {
                    return '<tr><td><span class="ov-area-tag">' + esc(e.area || '-') + '</span></td>' +
                        '<td class="ov-ellip" title="' + esc(e.message || '-') + '">' + esc(limit(e.message ||
                            '-', 60)) + '</td>' +
                        '<td class="ov-ellip" title="' + esc(e.expected || '-') + '">' + esc(e.expected || '-') +
                        '</td>' +
                        '<td class="ov-ellip" title="' + esc(e.scanned || '-') + '">' + esc(e.scanned || '-') +
                        '</td>' +
                        '<td style="white-space:nowrap;color:var(--text-muted);">' + esc(e.date || '-') +
                        '</td></tr>';
                }).join('');
                return '<table class="ov-table ov-table--fixed"><thead><tr>' +
                    '<th style="width:70px;">Area</th><th>Pesan</th>' +
                    '<th style="width:22%;">Expected</th><th style="width:22%;">Scanned</th>' +
                    '<th style="width:92px;">Waktu</th></tr></thead><tbody>' +
                    body + '</tbody></table>';
            }

            function urgencyColor(u) {
                return u === 'overdue' ? '#dc2626' : (u === 'urgent' ? '#ea580c' : (u === 'soon' ? '#d97706' : (u ===
                    'ok' ? '#16a34a' : '#94a3b8')));
            }

            function urgencyLabel(u) {
                return u === 'overdue' ? 'LEWAT' : (u === 'urgent' ? 'URGENT' : (u === 'soon' ? 'SEGERA' : (u === 'ok' ?
                    'AMAN' : '—')));
            }

            function remLabel(min) {
                if (min === null || min === undefined) return '';
                var neg = min < 0,
                    m = Math.abs(min),
                    h = Math.floor(m / 60),
                    mm = m % 60;
                var s = (h > 0 ? h + 'j ' : '') + mm + 'm';
                return neg ? ('lewat ' + s) : (s + ' lagi');
            }

            function renderAtRisk(rows) {
                if (!rows || !rows.length)
                return '<div class="ov-empty">Tidak ada delivery berisiko hari ini/besok. 👍</div>';
                var body = rows.map(function(r) {
                    var col = r.pct >= 100 ? '#16a34a' : (r.pct > 0 ? '#d97706' : '#dc2626');
                    var uc = urgencyColor(r.urgency);
                    var dlCell = r.deadline ?
                        '<span style="font-weight:800;">' + esc(r.deadline) + '</span>' :
                        '<span style="color:var(--text-muted);font-size:11px;">belum diset</span>';
                    var truckCell = r.truck ?
                        '<span style="font-weight:700;"><i class="fas fa-truck" style="color:var(--text-muted);margin-right:4px;"></i>' +
                        esc(r.truck) + '</span>' :
                        '<span style="color:var(--text-muted);">—</span>';
                    return '<tr>' +
                        '<td><a class="ov-badge" href="' + LL_URL + '/' + encodeURIComponent(r.id) + '">' + esc(
                            r.number || '-') + '</a></td>' +
                        '<td>' + esc(r.customer || '-') + '</td>' +
                        '<td>' + esc(r.cycle || '-') + '</td>' +
                        '<td style="white-space:nowrap;">' + dlCell + '</td>' +
                        '<td style="white-space:nowrap;">' + truckCell + '</td>' +
                        '<td style="white-space:nowrap;"><span class="ov-area-tag" style="background:' + uc +
                        '1f;color:' + uc + ';border-color:' + uc + '66;">' + urgencyLabel(r.urgency) +
                        '</span>' +
                        (r.remaining !== null && r.remaining !== undefined ?
                            ' <span style="font-size:11px;font-weight:700;color:' + uc + ';">' + remLabel(r
                                .remaining) + '</span>' : '') + '</td>' +
                        '<td style="min-width:130px;"><div class="ov-cycle__top" style="margin-bottom:3px;"><span>' +
                        r.actual + '/' + r.total + '</span><span>' + r.pct + '%</span></div>' +
                        '<div class="ov-bar"><div class="ov-bar__fill" style="width:' + r.pct +
                        '%;background:' + col + ';"></div></div></td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Loading List</th><th>Customer</th><th>Cycle</th><th>Deadline Prep</th><th>ETA Truck</th><th>Status</th><th>Progress</th></tr></thead><tbody>' +
                    body + '</tbody></table>';
            }

            function applyData(d) {
                latestData = d;
                var k = d.kpi || {},
                    dl = d.delta || {};
                setText('k-prepComplete', k.prepComplete || 0);
                setText('k-prepTotal', k.prepTotal || 0);
                setText('sub-prep', (k.prepPct || 0) + '% selesai · periode');
                setText('k-kanban', fmt(k.kanbanScanned));
                setHtml('sub-kanban', deltaHtml(dl.kanban, true));
                setText('k-pulling', fmt(k.pullingDone));
                setHtml('sub-pulling', deltaHtml(dl.pulling, true));
                setText('k-produksi', fmt(k.produksi));
                setHtml('sub-produksi', deltaHtml(dl.produksi, true));
                setText('k-understock', fmt(k.underStock));
                setText('k-totalpart', fmt(k.totalPart));
                setText('sub-understock', 'snapshot saat ini');
                setText('k-errperiod', fmt(k.errPeriod));
                setHtml('sub-error', deltaHtml(dl.err, false) + ' · rate ' + (d.errRate || 0) + '%');

                var trend = d.trend || [];
                var series = [{
                        name: 'Pulling',
                        data: trend.map(function(t) {
                            return t.pulling;
                        })
                    },
                    {
                        name: 'Produksi',
                        data: trend.map(function(t) {
                            return t.produksi;
                        })
                    },
                    {
                        name: 'PIS',
                        data: trend.map(function(t) {
                            return t.pis;
                        })
                    }
                ];
                if (trendChart) {
                    trendChart.updateOptions({
                        xaxis: {
                            categories: trend.map(function(t) {
                                return t.label;
                            })
                        }
                    }, false, false);
                    trendChart.updateSeries(series);
                }
                var s = d.stock || {};
                if (stockChart) stockChart.updateSeries([s.under || 0, s.low || 0, s.in || 0]);
                if (hourlyChart) {
                    hourlyChart.updateSeries(activitySeries(d.heatmap, activityMode));
                    schedulePeakHighlight();
                }

                setHtml('ovAtRisk', renderAtRisk(d.atRisk));
                setHtml('ovProdByLine', renderProdByLine(d.prodByLine));
                setHtml('ovPrepByCustomer', renderPrepByCustomer(d.prepByCustomer));
                setHtml('ovCycle', renderCycle(d.prepByCycle));
                setHtml('ovTopUnder', renderTopUnder(d.topUnder));
                setHtml('ovErrByArea', renderErrByArea(d.errByArea));
                setHtml('ovRecentErrors', renderRecentErrors(d.recentErrors));
                setText('ovUpdated', new Date().toLocaleTimeString('id-ID'));
            }

            function loadCharts() {
                if (window.ApexCharts) return Promise.resolve();
                if (chartsLoading) return chartsLoading;

                chartsLoading = new Promise(function(resolve, reject) {
                    var script = document.createElement('script');
                    script.src = CHART_URL;
                    script.async = true;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                return chartsLoading;
            }

            function ensureCharts(d) {
                latestData = d || latestData;
                return loadCharts().then(function() {
                    if (!trendChart && latestData) buildCharts(latestData);
                }).catch(function() {
                    ['ovTrendChart', 'ovStockChart', 'ovHourlyChart'].forEach(function(id) {
                        setHtml(id, '<div class="ov-empty">Grafik gagal dimuat.</div>');
                    });
                });
            }

            function buildCharts(d) {
                if (!window.ApexCharts || trendChart) return;
                ['ovTrendChart', 'ovStockChart', 'ovHourlyChart'].forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.classList.remove('ov-loading', 'ov-chart-loading');
                        el.textContent = '';
                    }
                });
                var trend = d.trend || [];
                trendChart = new ApexCharts(document.getElementById('ovTrendChart'), {
                    chart: {
                        type: 'area',
                        height: 290,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'Plus Jakarta Sans, sans-serif'
                    },
                    series: [{
                            name: 'Pulling',
                            data: trend.map(function(t) {
                                return t.pulling;
                            })
                        },
                        {
                            name: 'Produksi',
                            data: trend.map(function(t) {
                                return t.produksi;
                            })
                        },
                        {
                            name: 'PIS',
                            data: trend.map(function(t) {
                                return t.pis;
                            })
                        }
                    ],
                    colors: ['#0097D8', '#16a34a', '#d97706'],
                    xaxis: {
                        categories: trend.map(function(t) {
                            return t.label;
                        }),
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.35,
                            opacityTo: 0.02
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '12px'
                    },
                    grid: {
                        borderColor: 'rgba(0,0,0,.06)'
                    },
                    tooltip: {
                        shared: true
                    }
                });
                trendChart.render();

                var st = d.stock || {};
                stockChart = new ApexCharts(document.getElementById('ovStockChart'), {
                    chart: {
                        type: 'donut',
                        height: 290,
                        fontFamily: 'Plus Jakarta Sans, sans-serif'
                    },
                    series: [st.under || 0, st.low || 0, st.in || 0],
                    labels: ['Di Bawah Standar', 'Pas Standar', 'Aman'],
                    colors: ['#dc2626', '#d97706', '#16a34a'],
                    legend: {
                        position: 'bottom',
                        fontSize: '12px'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, o) {
                            return o.w.globals.series[o.seriesIndex];
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '62%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Part',
                                        fontSize: '12px'
                                    }
                                }
                            }
                        }
                    },
                    stroke: {
                        width: 2
                    }
                });
                stockChart.render();

                var heatmapOptions = activityChartOptions(activityMode);
                hourlyChart = new ApexCharts(document.getElementById('ovHourlyChart'), {
                    chart: {
                        type: 'heatmap',
                        height: 390,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        events: {
                            dataPointSelection: function(e, ctx, cfg) {
                                var srs = cfg.w.config.series[cfg.seriesIndex];
                                if (!srs) return;
                                var pt = (srs.data || [])[cfg.dataPointIndex] || {};
                                openSlotDetail(srs.name, parseInt(pt.x) || 0);
                            }
                        }
                    },
                    states: {
                        active: {
                            filter: {
                                type: 'none'
                            }
                        }
                    },
                    series: activitySeries(d.heatmap, activityMode),
                    colors: heatmapOptions.colors,
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: heatmapOptions.plotOptions,
                    xaxis: {
                        type: 'category',
                        labels: {
                            style: {
                                fontSize: '10px'
                            },
                            rotate: 0
                        },
                        title: {
                            text: 'Jam',
                            style: {
                                fontSize: '10px',
                                color: '#94a3b8'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '10px',
                                fontWeight: 700
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    grid: {
                        padding: {
                            right: 8
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(v) {
                                return v + ' aktivitas';
                            }
                        }
                    }
                });
                hourlyChart.render();
                schedulePeakHighlight();
            }

            function refresh(showLoading) {
                var currentRequestId = ++requestId;
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;
                // tukar otomatis kalau urutan kebalik (ISO date aman dibanding string)
                if (from && to && from > to) {
                    var tmp = from;
                    from = to;
                    to = tmp;
                    document.getElementById('ovFrom').value = from;
                    document.getElementById('ovTo').value = to;
                }
                if (showLoading) {
                    setFilterLoading(true);
                    showFilterLoadingState();
                }
                fetch(DATA_URL + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to), {
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        if (!r.ok) throw new Error('Dashboard data request failed');
                        return r.json();
                    })
                    .then(function(d) {
                        if (currentRequestId !== requestId) return;
                        setFilterLoading(false);
                        if (d.from) document.getElementById('ovFrom').value = d.from;
                        if (d.to) document.getElementById('ovTo').value = d.to;
                        applyData(d);
                        ensureCharts(d);
                        try {
                            history.replaceState(null, '', '?from=' + d.from + '&to=' + d.to);
                        } catch (e) {}
                    })
                    .catch(function() {
                        if (currentRequestId !== requestId) return;
                        setFilterLoading(false);
                        var message = '<div class="ov-empty">Data belum berhasil dimuat. Silakan coba lagi.</div>';
                        ['ovAtRisk', 'ovProdByLine', 'ovPrepByCustomer', 'ovCycle', 'ovTopUnder',
                            'ovErrByArea', 'ovRecentErrors'
                        ].forEach(function(id) {
                            setHtml(id, message);
                        });
                        ['ovTrendChart', 'ovStockChart', 'ovHourlyChart'].forEach(function(id) {
                            setChartError(id);
                        });
                        setText('sub-prep', 'gagal memuat data');
                        setText('sub-kanban', 'gagal memuat data');
                        setText('sub-pulling', 'gagal memuat data');
                        setText('sub-produksi', 'gagal memuat data');
                        setText('sub-understock', 'gagal memuat data');
                        setText('sub-error', 'gagal memuat data');
                    });
            }

            function setPreset(p) {
                var to = new Date(),
                    from = new Date();
                if (p === '7') from.setDate(to.getDate() - 6);
                else if (p === '30') from.setDate(to.getDate() - 29);
                // pakai tanggal lokal, bukan UTC — toISOString() bisa mundur 1 hari di WIB
                var iso = function(dt) {
                    return new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
                };
                document.getElementById('ovFrom').value = iso(from);
                document.getElementById('ovTo').value = iso(to);
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.classList.toggle('active', b.getAttribute('data-preset') === p);
                });
                refresh(true);
            }

            function clearActivePreset() {
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.classList.remove('active');
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.ov-activity-tab').forEach(function(button) {
                    button.addEventListener('click', function() {
                        setActivityMode(button.getAttribute('data-activity-mode'));
                    });
                });
                setActivityMode(activityMode);
                loadCharts();
                if (initial && initial.kpi) {
                    applyData(initial);
                    ensureCharts(initial);
                } else {
                    refresh(true);
                }
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.addEventListener('click', function() {
                        setPreset(b.getAttribute('data-preset'));
                    });
                });
                document.getElementById('ovFrom').addEventListener('change', function() {
                    clearActivePreset();
                    refresh(true);
                });
                document.getElementById('ovTo').addEventListener('change', function() {
                    clearActivePreset();
                    refresh(true);
                });
                // modal drill-down: tutup via tombol, klik backdrop, atau Esc
                var slotModal = document.getElementById('ovSlotModal');
                document.getElementById('ovSlotClose').addEventListener('click', function() {
                    slotModal.classList.remove('open');
                });
                slotModal.addEventListener('click', function(e) {
                    if (e.target === slotModal) slotModal.classList.remove('open');
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') slotModal.classList.remove('open');
                });
                // hemat query: refresh hanya saat tab aktif
                timer = setInterval(function() {
                    if (!document.hidden) refresh();
                }, 60000);
                // langsung segarkan data begitu user balik ke tab (jangan nunggu tick 60s)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) refresh();
                });
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: klik judul heatmap 5x cepat → Minesweeper. Chart ApexCharts asli tetap utuh di belakang.
        (function() {
            var title = document.getElementById('ovHeatmapTitle');
            var ms = document.getElementById('ovMinesweeper');
            if (!title || !ms) return;

            var ROWS = 8,
                COLS = 12,
                MINES = 18;
            var heat = ['#fef3c7', '#fde68a', '#fbbf24', '#f59e0b', '#d97706'];
            var numColor = {
                1: '#185FA5',
                2: '#3B6D11',
                3: '#A32D2D',
                4: '#26215C',
                5: '#854F0B',
                6: '#0F6E56',
                7: '#444441',
                8: '#5F5E5A'
            };
            var gridEl = document.getElementById('ovMsGrid');
            var countEl = document.getElementById('ovMsCount');
            var footEl = document.getElementById('ovMsFoot');
            var flagBtn = document.getElementById('ovMsFlag');
            var resetBtn = document.getElementById('ovMsReset');
            var closeBtn = document.getElementById('ovMsClose');
            var hideIds = ['ovHourlyChart', 'ovActivityLegend', 'ovActivityInsight'];
            var board, started, over, won, flags, flagMode, shade, built = false;

            gridEl.style.gridTemplateColumns = 'repeat(' + COLS + ', 28px)';

            function init() {
                board = [];
                shade = [];
                for (var r = 0; r < ROWS; r++) {
                    board.push([]);
                    shade.push([]);
                    for (var c = 0; c < COLS; c++) {
                        board[r].push({ mine: false, rev: false, flag: false, n: 0 });
                        shade[r].push(heat[Math.floor(Math.random() * heat.length)]);
                    }
                }
                started = false;
                over = false;
                won = false;
                flags = 0;
                flagMode = false;
                flagBtn.classList.remove('on');
                footEl.textContent = 'Klik kiri buka · klik kanan / tombol bendera tandai ranjau';
                render();
                updateHud();
            }

            function neigh(r, c) {
                var out = [];
                for (var dr = -1; dr <= 1; dr++)
                    for (var dc = -1; dc <= 1; dc++) {
                        if (dr === 0 && dc === 0) continue;
                        var nr = r + dr,
                            nc = c + dc;
                        if (nr >= 0 && nr < ROWS && nc >= 0 && nc < COLS) out.push([nr, nc]);
                    }
                return out;
            }

            function placeMines(sr, sc) {
                var safe = {};
                safe[sr + ',' + sc] = 1;
                neigh(sr, sc).forEach(function(p) {
                    safe[p[0] + ',' + p[1]] = 1;
                });
                var placed = 0;
                while (placed < MINES) {
                    var r = Math.floor(Math.random() * ROWS),
                        c = Math.floor(Math.random() * COLS);
                    if (board[r][c].mine || safe[r + ',' + c]) continue;
                    board[r][c].mine = true;
                    placed++;
                }
                for (var r2 = 0; r2 < ROWS; r2++)
                    for (var c2 = 0; c2 < COLS; c2++) {
                        if (board[r2][c2].mine) continue;
                        board[r2][c2].n = neigh(r2, c2).filter(function(p) {
                            return board[p[0]][p[1]].mine;
                        }).length;
                    }
            }

            function reveal(r, c) {
                var cell = board[r][c];
                if (over || won || cell.flag || cell.rev) return;
                if (!started) {
                    placeMines(r, c);
                    started = true;
                }
                cell.rev = true;
                if (cell.mine) {
                    over = true;
                    revealMines();
                    footEl.textContent = 'Boom! Kena ranjau jam sibuk — klik wajah buat main lagi.';
                    render();
                    updateHud();
                    return;
                }
                if (cell.n === 0) {
                    var stack = [[r, c]];
                    while (stack.length) {
                        var cur = stack.pop();
                        neigh(cur[0], cur[1]).forEach(function(p) {
                            var nb = board[p[0]][p[1]];
                            if (!nb.rev && !nb.flag && !nb.mine) {
                                nb.rev = true;
                                if (nb.n === 0) stack.push(p);
                            }
                        });
                    }
                }
                checkWin();
                render();
                updateHud();
            }

            function toggleFlag(r, c) {
                var cell = board[r][c];
                if (over || won || cell.rev) return;
                cell.flag = !cell.flag;
                flags += cell.flag ? 1 : -1;
                render();
                updateHud();
            }

            function revealMines() {
                for (var r = 0; r < ROWS; r++)
                    for (var c = 0; c < COLS; c++)
                        if (board[r][c].mine) board[r][c].rev = true;
            }

            function checkWin() {
                var ok = true;
                for (var r = 0; r < ROWS; r++)
                    for (var c = 0; c < COLS; c++) {
                        var x = board[r][c];
                        if (!x.mine && !x.rev) ok = false;
                    }
                if (ok) {
                    won = true;
                    footEl.textContent = 'Mantap! Semua jam aman dipetakan. Heatmap bersih dari ranjau.';
                }
            }

            function updateHud() {
                countEl.textContent = Math.max(0, MINES - flags);
                var ic = resetBtn.querySelector('i');
                ic.className = won ? 'fas fa-trophy' : (over ? 'fas fa-face-frown' : 'fas fa-face-smile');
            }

            function render() {
                gridEl.innerHTML = '';
                for (var r = 0; r < ROWS; r++)
                    for (var c = 0; c < COLS; c++) {
                        (function(r, c) {
                            var cell = board[r][c];
                            var el = document.createElement('button');
                            el.type = 'button';
                            el.className = 'ov-ms__cell';
                            if (cell.rev) {
                                if (cell.mine) {
                                    el.className += ' rev mine';
                                    el.innerHTML = '<i class="fas fa-bomb"></i>';
                                } else {
                                    el.className += ' rev';
                                    if (cell.n > 0) {
                                        el.textContent = cell.n;
                                        el.style.color = numColor[cell.n];
                                    }
                                }
                            } else {
                                el.className += ' cov';
                                el.style.background = shade[r][c];
                                if (cell.flag) el.innerHTML = '<i class="fas fa-flag" style="color:#dc2626"></i>';
                            }
                            el.addEventListener('click', function() {
                                flagMode ? toggleFlag(r, c) : reveal(r, c);
                            });
                            el.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                toggleFlag(r, c);
                            });
                            gridEl.appendChild(el);
                        })(r, c);
                    }
            }

            function activate() {
                hideIds.forEach(function(id) {
                    var e = document.getElementById(id);
                    if (e) e.style.display = 'none';
                });
                ms.hidden = false;
                if (!built) {
                    built = true;
                    init();
                }
            }

            function deactivate() {
                ms.hidden = true;
                hideIds.forEach(function(id) {
                    var e = document.getElementById(id);
                    if (e) e.style.display = '';
                });
            }

            flagBtn.addEventListener('click', function() {
                flagMode = !flagMode;
                flagBtn.classList.toggle('on', flagMode);
            });
            resetBtn.addEventListener('click', init);
            closeBtn.addEventListener('click', deactivate);

            // trigger: 5 klik cepat di judul (reset hitungan kalau jeda > 700ms)
            var clicks = 0,
                t = null;
            title.addEventListener('click', function() {
                clicks++;
                clearTimeout(t);
                t = setTimeout(function() {
                    clicks = 0;
                }, 700);
                if (clicks >= 5) {
                    clicks = 0;
                    activate();
                }
            });
        })();
    </script>
@endsection
