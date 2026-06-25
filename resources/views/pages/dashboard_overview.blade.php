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

        .ov-activity-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .ov-activity-card {
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-left-width: 3px;
            border-radius: 8px;
            background: #fff;
        }

        .ov-activity-card--pulling {
            border-left-color: #0097D8;
        }

        .ov-activity-card--produksi {
            border-left-color: #16a34a;
        }

        .ov-activity-card--total {
            border-left-color: #d97706;
        }

        .ov-activity-card__label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .ov-activity-card__value {
            margin-top: 4px;
            font-size: 18px;
            font-weight: 900;
            color: var(--text);
        }

        .ov-activity-card__note {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
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

            .ov-activity-summary {
                grid-template-columns: 1fr;
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
                    <div class="ov-panel__title"><i class="fas fa-clock"></i> Jam Tersibuk — Aktivitas Pulling &amp;
                        Produksi (hari × jam)</div>
                    <div class="ov-activity-tabs" role="group" aria-label="Filter heatmap activity">
                        <button type="button" class="ov-activity-tab" data-activity-mode="produksi">Prod</button>
                        <button type="button" class="ov-activity-tab" data-activity-mode="pulling">Pulling</button>
                        <button type="button" class="ov-activity-tab active" data-activity-mode="all">All</button>
                    </div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovActivitySummary" class="ov-activity-summary">
                        <div class="ov-loading">Memuat ringkasan</div>
                    </div>
                    <div id="ovActivityLegend" class="ov-activity-legend"></div>
                    <div id="ovHourlyChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
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

    <script>
        (function() {
            var DATA_URL = @json(route('dashboard.overview.data'));
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

            function renderErrByArea(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada error pada periode ini. 👍</div>';
                var max = Math.max.apply(null, rows.map(function(r) {
                    return r.c;
                })) || 1;
                return renderBars(rows,
                    function(r) {
                        return r.area || '-';
                    },
                    function(r) {
                        return r.c;
                    },
                    function(r) {
                        return Math.round((r.c / max) * 100);
                    },
                    function() {
                        return '#dc2626';
                    });
            }

            function renderActivitySummary(s) {
                s = s || {};
                var pulling = parseInt(s.pulling) || 0;
                var produksi = parseInt(s.produksi) || 0;
                var total = pulling + produksi;
                var share = total ? Math.round((pulling / total) * 100) : 0;
                var peakText = function(p) {
                    var count = p ? (parseInt(p.count) || 0) : 0;
                    return count ? esc(p.label || '-') + ' &middot; ' + fmt(count) + ' aktivitas' : '-';
                };

                return '<div class="ov-activity-card ov-activity-card--pulling">' +
                    '<div class="ov-activity-card__label">Pulling</div>' +
                    '<div class="ov-activity-card__value">' + fmt(pulling) + '</div>' +
                    '<div class="ov-activity-card__note">Puncak: ' + peakText(s.peakPulling) + '</div>' +
                    '</div>' +
                    '<div class="ov-activity-card ov-activity-card--produksi">' +
                    '<div class="ov-activity-card__label">Produksi</div>' +
                    '<div class="ov-activity-card__value">' + fmt(produksi) + '</div>' +
                    '<div class="ov-activity-card__note">Puncak: ' + peakText(s.peakProduksi) + '</div>' +
                    '</div>' +
                    '<div class="ov-activity-card ov-activity-card--total">' +
                    '<div class="ov-activity-card__label">Total Aktivitas</div>' +
                    '<div class="ov-activity-card__value">' + fmt(total) + '</div>' +
                    '<div class="ov-activity-card__note">' + (total ? ('Pulling ' + share + '% · Produksi ' + (100 - share) + '%') : 'Belum ada aktivitas') + '</div>' +
                    '</div>';
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
                        '<td>' + esc(limit(e.message || '-', 60)) + '</td>' +
                        '<td>' + esc(limit(e.expected || '-', 26)) + '</td>' +
                        '<td>' + esc(limit(e.scanned || '-', 26)) + '</td>' +
                        '<td style="white-space:nowrap;color:var(--text-muted);">' + esc(e.date || '-') +
                        '</td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Area</th><th>Pesan</th><th>Expected</th><th>Scanned</th><th>Waktu</th></tr></thead><tbody>' +
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
                if (hourlyChart) hourlyChart.updateSeries(activitySeries(d.heatmap, activityMode));

                setHtml('ovActivitySummary', renderActivitySummary(d.heatmapSummary));
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
                        fontFamily: 'Plus Jakarta Sans, sans-serif'
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
            }

            function refresh() {
                var currentRequestId = ++requestId;
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;
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
                        var message = '<div class="ov-empty">Data belum berhasil dimuat. Silakan coba lagi.</div>';
                        ['ovActivitySummary', 'ovAtRisk', 'ovProdByLine', 'ovPrepByCustomer', 'ovCycle', 'ovTopUnder',
                            'ovErrByArea', 'ovRecentErrors'
                        ].forEach(function(id) {
                            setHtml(id, message);
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
                else if (p === '30') from.setDate(to.getDate() - 30);
                var iso = function(dt) {
                    return dt.toISOString().slice(0, 10);
                };
                document.getElementById('ovFrom').value = iso(from);
                document.getElementById('ovTo').value = iso(to);
                refresh();
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
                    refresh();
                }
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.addEventListener('click', function() {
                        setPreset(b.getAttribute('data-preset'));
                    });
                });
                document.getElementById('ovFrom').addEventListener('change', refresh);
                document.getElementById('ovTo').addEventListener('change', refresh);
                // hemat query: refresh hanya saat tab aktif
                timer = setInterval(function() {
                    if (!document.hidden) refresh();
                }, 60000);
            });
        })();
    </script>
@endsection
