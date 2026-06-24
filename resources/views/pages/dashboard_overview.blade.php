@extends('layouts.root.main')

@section('main')
    <style>
        .ov-wrap { width: 100%; margin: 0 auto; }

        .ov-head { margin: 12px 0 16px; display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .ov-head h4 { font-size: 20px; font-weight: 850; color: var(--navy); margin: 0 0 4px; letter-spacing: -.02em; }
        .ov-head p { font-size: 12px; color: var(--text-muted); margin: 0; font-weight: 600; }
        .ov-live { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: var(--primary); }
        .ov-live .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--primary); animation: ovpulse 1.5s infinite; }
        @keyframes ovpulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

        .ov-filter { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; padding: 12px 14px; border: 1px solid var(--border); border-radius: 14px; background: var(--card); box-shadow: var(--shadow); }
        .ov-filter label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin: 0; }
        .ov-filter input[type=date] { height: 34px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); font-size: 12.5px; padding: 0 10px; outline: none; font-weight: 600; }
        .ov-filter input[type=date]:focus { border-color: var(--sky); box-shadow: 0 0 0 3px rgba(0,151,216,.10); background: #fff; }
        .ov-preset { height: 30px; padding: 0 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text-muted); font-size: 11.5px; font-weight: 800; cursor: pointer; transition: .15s; }
        .ov-preset:hover, .ov-preset.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .ov-filter .sep { flex: 1; }

        .ov-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .ov-kpi { padding: 13px 15px; border: 1px solid var(--border); border-radius: 14px; background: var(--card); box-shadow: var(--shadow); border-left: 3px solid var(--primary); text-decoration: none !important; display: block; transition: transform .15s ease, box-shadow .15s ease; }
        .ov-kpi:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .ov-kpi--danger { border-left-color: #dc2626; }
        .ov-kpi--success { border-left-color: #16a34a; }
        .ov-kpi--amber { border-left-color: #d97706; }
        .ov-kpi__label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
        .ov-kpi__value { margin-top: 6px; font-size: 22px; font-weight: 850; color: var(--navy); line-height: 1; letter-spacing: -.03em; }
        .ov-kpi__value small { font-size: 13px; font-weight: 700; color: var(--text-muted); }
        .ov-kpi__sub { margin-top: 5px; font-size: 11px; color: var(--text-muted); font-weight: 600; }

        .ov-row { display: grid; gap: 14px; margin-bottom: 14px; }
        .ov-row--2a { grid-template-columns: 2fr 1fr; }
        .ov-row--2 { grid-template-columns: 1fr 1fr; }
        .ov-row--2b { grid-template-columns: 1fr 1.3fr; }
        .ov-row--2c { grid-template-columns: 1fr 1.6fr; }
        .ov-panel { border: 1px solid var(--border); border-radius: 16px; background: var(--card); box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column; }
        .ov-panel--alert { border-color: #fecaca; }
        .ov-panel__head { padding: 13px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .ov-panel__title { font-size: 12px; font-weight: 850; text-transform: uppercase; letter-spacing: .06em; color: var(--navy); display: flex; align-items: center; gap: 8px; }
        .ov-panel__title i { color: var(--primary); }
        .ov-panel--alert .ov-panel__title i { color: #dc2626; }
        .ov-panel__body { padding: 14px 18px; flex: 1; }
        .ov-panel__body--tight { padding: 4px 8px; }

        .ov-cycle { display: flex; flex-direction: column; gap: 13px; }
        .ov-cycle__top { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: var(--text); margin-bottom: 5px; }
        .ov-cycle__top span:last-child { color: var(--text-muted); font-weight: 600; }
        .ov-bar { height: 9px; border-radius: 99px; background: #E2E8F0; overflow: hidden; }
        .ov-bar__fill { height: 100%; border-radius: 99px; transition: width .5s ease; }

        .ov-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .ov-table thead th { text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .ov-table tbody td { padding: 8px 10px; border-bottom: 1px solid var(--border); color: var(--text); vertical-align: middle; }
        .ov-table tbody tr:last-child td { border-bottom: none; }
        .ov-table tbody tr:hover td { background: var(--bg); }
        .ov-badge { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 99px; font-size: 11px; font-weight: 800; background: #eef2ff; color: var(--primary); border: 1px solid #dbe3ff; text-decoration: none; }
        .ov-gap { font-weight: 800; color: #dc2626; }
        .ov-area-tag { display: inline-flex; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .ov-empty { padding: 26px 12px; text-align: center; color: var(--text-muted); font-size: 12px; font-weight: 600; }
        .ov-delta { font-weight: 800; }

        @media (max-width: 1100px) {
            .ov-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ov-row--2a, .ov-row--2, .ov-row--2b, .ov-row--2c { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) {
            .ov-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>

    @php $kpi = $d['kpi'] ?? []; @endphp

    <div class="ov-wrap">
        <div class="ov-head">
            <div>
                <h4>Dashboard Overview</h4>
                <p>Ringkasan &amp; analisis lintas modul — PPIC · Production · PIS · Error</p>
            </div>
            <span class="ov-live"><span class="dot"></span> LIVE · <span id="ovUpdated">{{ now()->format('H:i:s') }}</span></span>
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
            <span style="font-size:11px;color:var(--text-muted);font-weight:600;">Auto-refresh tiap 60 dtk · vs periode sebelumnya</span>
        </div>

        {{-- ===== KPI STRIP ===== --}}
        <div class="ov-kpis">
            <a class="ov-kpi" href="{{ route('dashboard.delivery') }}">
                <div class="ov-kpi__label">Preparation</div>
                <div class="ov-kpi__value"><span id="k-prepComplete">{{ $kpi['prepComplete'] ?? 0 }}</span><small>/<span id="k-prepTotal">{{ $kpi['prepTotal'] ?? 0 }}</span></small></div>
                <div class="ov-kpi__sub" id="sub-prep">{{ $kpi['prepPct'] ?? 0 }}% selesai · periode</div>
            </a>
            <a class="ov-kpi" href="{{ route('loadingList.index') }}">
                <div class="ov-kpi__label">Kanban Discan</div>
                <div class="ov-kpi__value" id="k-kanban">{{ number_format($kpi['kanbanScanned'] ?? 0) }}</div>
                <div class="ov-kpi__sub" id="sub-kanban">pulling · periode</div>
            </a>
            <a class="ov-kpi ov-kpi--success" href="{{ route('loadingList.index') }}">
                <div class="ov-kpi__label">Pulling Selesai</div>
                <div class="ov-kpi__value" id="k-pulling">{{ number_format($kpi['pullingDone'] ?? 0) }}</div>
                <div class="ov-kpi__sub" id="sub-pulling">loading list · periode</div>
            </a>
            <a class="ov-kpi" href="{{ route('dashboard.prodResult') }}">
                <div class="ov-kpi__label">Produksi</div>
                <div class="ov-kpi__value" id="k-produksi">{{ number_format($kpi['produksi'] ?? 0) }}</div>
                <div class="ov-kpi__sub" id="sub-produksi">supply · periode</div>
            </a>
            <a class="ov-kpi ov-kpi--danger" href="{{ route('dashboard.productionStock') }}">
                <div class="ov-kpi__label">Stok &lt; Standar</div>
                <div class="ov-kpi__value"><span id="k-understock">{{ number_format($kpi['underStock'] ?? 0) }}</span><small>/<span id="k-totalpart">{{ number_format($kpi['totalPart'] ?? 0) }}</span></small></div>
                <div class="ov-kpi__sub">snapshot saat ini</div>
            </a>
            <a class="ov-kpi ov-kpi--amber" href="{{ route('error.log') }}">
                <div class="ov-kpi__label">Error</div>
                <div class="ov-kpi__value" id="k-errperiod">{{ number_format($kpi['errPeriod'] ?? 0) }}</div>
                <div class="ov-kpi__sub" id="sub-error">dalam periode</div>
            </a>
        </div>

        {{-- ===== DELIVERY BERISIKO (#6) ===== --}}
        <div class="ov-row">
            <div class="ov-panel ov-panel--alert">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-truck-fast"></i> Delivery Berisiko (hari ini &amp; besok)</div>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovAtRisk"></div>
            </div>
        </div>

        {{-- ===== Trend + Stock ===== --}}
        <div class="ov-row ov-row--2a">
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-chart-area"></i> Aktivitas Harian</div></div>
                <div class="ov-panel__body"><div id="ovTrendChart"></div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-boxes"></i> Kesehatan Stok</div></div>
                <div class="ov-panel__body"><div id="ovStockChart"></div></div>
            </div>
        </div>

        {{-- ===== Produksi per Line + Preparation per Customer (#2) ===== --}}
        <div class="ov-row ov-row--2">
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-industry"></i> Produksi per Line</div></div>
                <div class="ov-panel__body" id="ovProdByLine"></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-users"></i> Preparation per Customer</div></div>
                <div class="ov-panel__body" id="ovPrepByCustomer"></div>
            </div>
        </div>

        {{-- ===== Jam tersibuk (#5) ===== --}}
        <div class="ov-row">
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-clock"></i> Jam Tersibuk (heatmap hari × jam)</div></div>
                <div class="ov-panel__body"><div id="ovHourlyChart"></div></div>
            </div>
        </div>

        {{-- ===== Preparation per cycle + Top under-stock ===== --}}
        <div class="ov-row ov-row--2b">
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-layer-group"></i> Preparation per Cycle</div></div>
                <div class="ov-panel__body" id="ovCycle"></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-arrow-down"></i> Part Paling Kekurangan Stok</div></div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovTopUnder"></div>
            </div>
        </div>

        {{-- ===== Error per area + Recent errors ===== --}}
        <div class="ov-row ov-row--2c">
            <div class="ov-panel">
                <div class="ov-panel__head"><div class="ov-panel__title"><i class="fas fa-chart-bar"></i> Error per Area</div></div>
                <div class="ov-panel__body" id="ovErrByArea"></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-exclamation-triangle"></i> Error Terbaru</div>
                    <a href="{{ route('error.log') }}" style="font-size:11px;font-weight:700;color:var(--primary);text-decoration:none;">Lihat semua →</a>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovRecentErrors"></div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script>
        (function () {
            var DATA_URL = @json(route('dashboard.overview.data'));
            var STOCK_URL = @json(route('dashboard.productionStock'));
            var LL_URL = @json(url('/loading-list'));
            var initial = @json($d ?? []);
            var trendChart = null, stockChart = null, hourlyChart = null, timer = null;

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
                });
            }
            function fmt(n) { return Number(n || 0).toLocaleString('id-ID'); }
            function setText(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; }
            function setHtml(id, v) { var el = document.getElementById(id); if (el) el.innerHTML = v; }
            function limit(s, n) { s = String(s == null ? '' : s); return s.length > n ? s.slice(0, n) + '…' : s; }

            // ▲▼ delta. goodUp=true: naik itu bagus (hijau); false: naik itu jelek (merah, mis. error)
            function deltaHtml(delta, goodUp) {
                if (delta === null || delta === undefined) return '';
                var good = delta === 0 ? null : (goodUp ? delta > 0 : delta < 0);
                var color = good === null ? 'var(--text-muted)' : (good ? '#16a34a' : '#dc2626');
                var arrow = delta > 0 ? '▲' : (delta < 0 ? '▼' : '•');
                return '<span class="ov-delta" style="color:' + color + ';">' + arrow + ' ' + Math.abs(delta) + '%</span> vs lalu';
            }

            function renderBars(rows, getLabel, getTop, getPct, getColor) {
                return '<div class="ov-cycle">' + rows.map(function (r) {
                    var pct = getPct(r);
                    return '<div><div class="ov-cycle__top"><span>' + esc(getLabel(r)) + '</span><span>' + getTop(r) + '</span></div>' +
                        '<div class="ov-bar"><div class="ov-bar__fill" style="width:' + pct + '%;background:' + getColor(r) + ';"></div></div></div>';
                }).join('') + '</div>';
            }

            function renderCycle(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada loading list pada periode ini.</div>';
                return renderBars(rows,
                    function (c) { return 'Cycle ' + c.cycle; },
                    function (c) { return c.complete + '/' + c.total + ' · ' + c.pct + '%'; },
                    function (c) { return c.pct; },
                    function (c) { return c.pct >= 100 ? '#16a34a' : (c.pct > 0 ? '#d97706' : '#dc2626'); });
            }

            function renderPrepByCustomer(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada loading list pada periode ini.</div>';
                return renderBars(rows,
                    function (c) { return c.customer; },
                    function (c) { return c.complete + '/' + c.total + ' · ' + c.pct + '%'; },
                    function (c) { return c.pct; },
                    function (c) { return c.pct >= 100 ? '#16a34a' : (c.pct > 0 ? '#d97706' : '#dc2626'); });
            }

            function renderProdByLine(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Belum ada produksi pada periode ini.</div>';
                var max = Math.max.apply(null, rows.map(function (r) { return r.c; })) || 1;
                return renderBars(rows,
                    function (r) { return r.line || '-'; },
                    function (r) { return r.c; },
                    function (r) { return Math.round((r.c / max) * 100); },
                    function () { return '#0097D8'; });
            }

            function renderErrByArea(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada error pada periode ini. 👍</div>';
                var max = Math.max.apply(null, rows.map(function (r) { return r.c; })) || 1;
                return renderBars(rows,
                    function (r) { return r.area || '-'; },
                    function (r) { return r.c; },
                    function (r) { return Math.round((r.c / max) * 100); },
                    function () { return '#dc2626'; });
            }

            function renderTopUnder(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Semua part di atas/standar stok. 👍</div>';
                var body = rows.map(function (p) {
                    var url = STOCK_URL + '?back_number=' + encodeURIComponent(p.back_number || '');
                    return '<tr><td><a class="ov-badge" href="' + url + '">' + esc(p.back_number || '-') + '</a></td>' +
                        '<td>' + esc(p.line || '-') + '</td>' +
                        '<td class="text-right">' + (parseInt(p.cur) || 0) + '</td>' +
                        '<td class="text-right">' + (parseInt(p.std) || 0) + '</td>' +
                        '<td class="text-right ov-gap">-' + (parseInt(p.gap) || 0) + '</td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Back No</th><th>Line</th><th class="text-right">Stok</th><th class="text-right">Std</th><th class="text-right">Kurang</th></tr></thead><tbody>' + body + '</tbody></table>';
            }

            function renderRecentErrors(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Belum ada error tercatat. 👍</div>';
                var body = rows.map(function (e) {
                    return '<tr><td><span class="ov-area-tag">' + esc(e.area || '-') + '</span></td>' +
                        '<td>' + esc(limit(e.message || '-', 60)) + '</td>' +
                        '<td>' + esc(limit(e.expected || '-', 26)) + '</td>' +
                        '<td>' + esc(limit(e.scanned || '-', 26)) + '</td>' +
                        '<td style="white-space:nowrap;color:var(--text-muted);">' + esc(e.date || '-') + '</td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Area</th><th>Pesan</th><th>Expected</th><th>Scanned</th><th>Waktu</th></tr></thead><tbody>' + body + '</tbody></table>';
            }

            function renderAtRisk(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada delivery berisiko hari ini/besok. 👍</div>';
                var body = rows.map(function (r) {
                    var col = r.pct >= 100 ? '#16a34a' : (r.pct > 0 ? '#d97706' : '#dc2626');
                    return '<tr>' +
                        '<td><a class="ov-badge" href="' + LL_URL + '/' + encodeURIComponent(r.id) + '">' + esc(r.number || '-') + '</a></td>' +
                        '<td>' + esc(r.customer || '-') + '</td>' +
                        '<td>' + esc(r.cycle || '-') + '</td>' +
                        '<td>' + esc(r.delivery || '-') + '</td>' +
                        '<td style="min-width:140px;"><div class="ov-cycle__top" style="margin-bottom:3px;"><span>' + r.actual + '/' + r.total + '</span><span>' + r.pct + '%</span></div>' +
                        '<div class="ov-bar"><div class="ov-bar__fill" style="width:' + r.pct + '%;background:' + col + ';"></div></div></td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Loading List</th><th>Customer</th><th>Cycle</th><th>Delivery</th><th>Progress</th></tr></thead><tbody>' + body + '</tbody></table>';
            }

            function applyData(d) {
                var k = d.kpi || {}, dl = d.delta || {};
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
                setText('k-errperiod', fmt(k.errPeriod));
                setHtml('sub-error', deltaHtml(dl.err, false) + ' · rate ' + (d.errRate || 0) + '%');

                var trend = d.trend || [];
                var series = [
                    { name: 'Pulling', data: trend.map(function (t) { return t.pulling; }) },
                    { name: 'Produksi', data: trend.map(function (t) { return t.produksi; }) },
                    { name: 'PIS', data: trend.map(function (t) { return t.pis; }) }
                ];
                if (trendChart) { trendChart.updateOptions({ xaxis: { categories: trend.map(function (t) { return t.label; }) } }, false, false); trendChart.updateSeries(series); }
                var s = d.stock || {};
                if (stockChart) stockChart.updateSeries([s.under || 0, s.low || 0, s.in || 0]);
                if (hourlyChart) hourlyChart.updateSeries(d.heatmap || []);

                setHtml('ovAtRisk', renderAtRisk(d.atRisk));
                setHtml('ovProdByLine', renderProdByLine(d.prodByLine));
                setHtml('ovPrepByCustomer', renderPrepByCustomer(d.prepByCustomer));
                setHtml('ovCycle', renderCycle(d.prepByCycle));
                setHtml('ovTopUnder', renderTopUnder(d.topUnder));
                setHtml('ovErrByArea', renderErrByArea(d.errByArea));
                setHtml('ovRecentErrors', renderRecentErrors(d.recentErrors));
                setText('ovUpdated', new Date().toLocaleTimeString('id-ID'));
            }

            function buildCharts(d) {
                if (!window.ApexCharts) return;
                var trend = d.trend || [];
                trendChart = new ApexCharts(document.getElementById('ovTrendChart'), {
                    chart: { type: 'area', height: 290, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                    series: [
                        { name: 'Pulling', data: trend.map(function (t) { return t.pulling; }) },
                        { name: 'Produksi', data: trend.map(function (t) { return t.produksi; }) },
                        { name: 'PIS', data: trend.map(function (t) { return t.pis; }) }
                    ],
                    colors: ['#0097D8', '#16a34a', '#d97706'],
                    xaxis: { categories: trend.map(function (t) { return t.label; }), labels: { style: { fontSize: '11px' } } },
                    yaxis: { labels: { style: { fontSize: '11px' } } },
                    dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
                    grid: { borderColor: 'rgba(0,0,0,.06)' }, tooltip: { shared: true }
                });
                trendChart.render();

                var st = d.stock || {};
                stockChart = new ApexCharts(document.getElementById('ovStockChart'), {
                    chart: { type: 'donut', height: 290, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                    series: [st.under || 0, st.low || 0, st.in || 0],
                    labels: ['Di Bawah Standar', 'Pas Standar', 'Aman'],
                    colors: ['#dc2626', '#d97706', '#16a34a'],
                    legend: { position: 'bottom', fontSize: '12px' },
                    dataLabels: { enabled: true, formatter: function (val, o) { return o.w.globals.series[o.seriesIndex]; } },
                    plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Total Part', fontSize: '12px' } } } } },
                    stroke: { width: 2 }
                });
                stockChart.render();

                hourlyChart = new ApexCharts(document.getElementById('ovHourlyChart'), {
                    chart: { type: 'heatmap', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                    series: d.heatmap || [],
                    colors: ['#dc2626'],
                    dataLabels: { enabled: false },
                    plotOptions: { heatmap: { radius: 3, enableShades: true, shadeIntensity: 0.6 } },
                    xaxis: { type: 'category', labels: { style: { fontSize: '10px' }, rotate: 0 }, title: { text: 'Jam', style: { fontSize: '10px', color: '#94a3b8' } } },
                    yaxis: { labels: { style: { fontSize: '11px', fontWeight: 700 } } },
                    legend: { show: false },
                    grid: { padding: { right: 8 } },
                    tooltip: { y: { formatter: function (v) { return v + ' aktivitas'; } } }
                });
                hourlyChart.render();
            }

            function refresh() {
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;
                fetch(DATA_URL + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to), {
                    headers: { 'Accept': 'application/json' }, credentials: 'same-origin'
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (d.from) document.getElementById('ovFrom').value = d.from;
                        if (d.to) document.getElementById('ovTo').value = d.to;
                        applyData(d);
                        try { history.replaceState(null, '', '?from=' + d.from + '&to=' + d.to); } catch (e) {}
                    })
                    .catch(function () {});
            }

            function setPreset(p) {
                var to = new Date(), from = new Date();
                if (p === '7') from.setDate(to.getDate() - 6);
                else if (p === '30') from.setDate(to.getDate() - 30);
                var iso = function (dt) { return dt.toISOString().slice(0, 10); };
                document.getElementById('ovFrom').value = iso(from);
                document.getElementById('ovTo').value = iso(to);
                refresh();
            }

            document.addEventListener('DOMContentLoaded', function () {
                buildCharts(initial);
                applyData(initial);
                document.querySelectorAll('.ov-preset').forEach(function (b) {
                    b.addEventListener('click', function () { setPreset(b.getAttribute('data-preset')); });
                });
                document.getElementById('ovFrom').addEventListener('change', refresh);
                document.getElementById('ovTo').addEventListener('change', refresh);
                // hemat query: refresh hanya saat tab aktif
                timer = setInterval(function () { if (!document.hidden) refresh(); }, 60000);
            });
        })();
    </script>
@endsection
