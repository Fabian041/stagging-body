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

        /* KPI strip */
        .ov-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .ov-kpi { padding: 13px 15px; border: 1px solid var(--border); border-radius: 14px; background: var(--card); box-shadow: var(--shadow); border-left: 3px solid var(--primary); }
        .ov-kpi--danger { border-left-color: #dc2626; }
        .ov-kpi--success { border-left-color: #16a34a; }
        .ov-kpi--amber { border-left-color: #d97706; }
        .ov-kpi__label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
        .ov-kpi__value { margin-top: 6px; font-size: 22px; font-weight: 850; color: var(--navy); line-height: 1; letter-spacing: -.03em; }
        .ov-kpi__value small { font-size: 13px; font-weight: 700; color: var(--text-muted); }
        .ov-kpi__sub { margin-top: 5px; font-size: 11px; color: var(--text-muted); font-weight: 600; }

        /* Panels */
        .ov-row { display: grid; gap: 14px; margin-bottom: 14px; }
        .ov-row--2a { grid-template-columns: 2fr 1fr; }
        .ov-row--2b { grid-template-columns: 1fr 1.3fr; }
        .ov-panel { border: 1px solid var(--border); border-radius: 16px; background: var(--card); box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column; }
        .ov-panel__head { padding: 13px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .ov-panel__title { font-size: 12px; font-weight: 850; text-transform: uppercase; letter-spacing: .06em; color: var(--navy); display: flex; align-items: center; gap: 8px; }
        .ov-panel__title i { color: var(--primary); }
        .ov-panel__body { padding: 14px 18px; flex: 1; }

        /* prep by cycle bars */
        .ov-cycle { display: flex; flex-direction: column; gap: 13px; }
        .ov-cycle__row { }
        .ov-cycle__top { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: var(--text); margin-bottom: 5px; }
        .ov-cycle__top span:last-child { color: var(--text-muted); font-weight: 600; }
        .ov-bar { height: 9px; border-radius: 99px; background: #E2E8F0; overflow: hidden; }
        .ov-bar__fill { height: 100%; border-radius: 99px; transition: width .5s ease; }

        /* tables */
        .ov-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .ov-table thead th { text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border); white-space: nowrap; }
        .ov-table tbody td { padding: 8px 10px; border-bottom: 1px solid var(--border); color: var(--text); vertical-align: middle; }
        .ov-table tbody tr:last-child td { border-bottom: none; }
        .ov-table tbody tr:hover td { background: var(--bg); }
        .ov-badge { display: inline-flex; align-items: center; padding: 2px 9px; border-radius: 99px; font-size: 11px; font-weight: 800; background: #eef2ff; color: var(--primary); border: 1px solid #dbe3ff; }
        .ov-gap { font-weight: 800; color: #dc2626; }
        .ov-area-tag { display: inline-flex; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .ov-empty { padding: 26px 12px; text-align: center; color: var(--text-muted); font-size: 12px; font-weight: 600; }

        @media (max-width: 1100px) {
            .ov-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ov-row--2a, .ov-row--2b { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) {
            .ov-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>

    @php
        $d = $d ?? [];
        $kpi = $d['kpi'] ?? [];
        $prepByCycle = $d['prepByCycle'] ?? collect();
        $topUnder = $d['topUnder'] ?? collect();
        $recentErrors = $d['recentErrors'] ?? collect();
    @endphp

    <div class="ov-wrap">
        <div class="ov-head">
            <div>
                <h4>Dashboard Overview</h4>
                <p>Ringkasan & analisis lintas modul — PPIC · Production · PIS · Error</p>
            </div>
            <span class="ov-live"><span class="dot"></span> LIVE · {{ now()->format('d M Y, H:i') }}</span>
        </div>

        {{-- ===== KPI STRIP ===== --}}
        <div class="ov-kpis">
            <div class="ov-kpi">
                <div class="ov-kpi__label">Preparation</div>
                <div class="ov-kpi__value">{{ $kpi['prepComplete'] ?? 0 }}<small>/{{ $kpi['prepTotal'] ?? 0 }}</small></div>
                <div class="ov-kpi__sub">{{ $kpi['prepPct'] ?? 0 }}% selesai · hari ini</div>
            </div>
            <div class="ov-kpi">
                <div class="ov-kpi__label">Kanban Discan</div>
                <div class="ov-kpi__value">{{ number_format($kpi['kanbanScanned'] ?? 0) }}</div>
                <div class="ov-kpi__sub">pulling · hari ini</div>
            </div>
            <div class="ov-kpi ov-kpi--success">
                <div class="ov-kpi__label">Pulling Selesai</div>
                <div class="ov-kpi__value">{{ number_format($kpi['pullingDone'] ?? 0) }}</div>
                <div class="ov-kpi__sub">loading list · hari ini</div>
            </div>
            <div class="ov-kpi">
                <div class="ov-kpi__label">Produksi</div>
                <div class="ov-kpi__value">{{ number_format($kpi['produksi'] ?? 0) }}</div>
                <div class="ov-kpi__sub">supply · hari ini</div>
            </div>
            <div class="ov-kpi ov-kpi--danger">
                <div class="ov-kpi__label">Stok &lt; Standar</div>
                <div class="ov-kpi__value">{{ number_format($kpi['underStock'] ?? 0) }}<small>/{{ number_format($kpi['totalPart'] ?? 0) }}</small></div>
                <div class="ov-kpi__sub">part di bawah standar</div>
            </div>
            <div class="ov-kpi ov-kpi--amber">
                <div class="ov-kpi__label">Error</div>
                <div class="ov-kpi__value">{{ number_format($kpi['errToday'] ?? 0) }}</div>
                <div class="ov-kpi__sub">{{ number_format($kpi['errWeek'] ?? 0) }} dalam 7 hari</div>
            </div>
        </div>

        {{-- ===== ROW: Trend + Stock health ===== --}}
        <div class="ov-row ov-row--2a">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-chart-area"></i> Aktivitas 7 Hari Terakhir</div>
                </div>
                <div class="ov-panel__body"><div id="ovTrendChart"></div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-boxes"></i> Kesehatan Stok</div>
                </div>
                <div class="ov-panel__body"><div id="ovStockChart"></div></div>
            </div>
        </div>

        {{-- ===== ROW: Preparation per cycle + Top under-stock ===== --}}
        <div class="ov-row ov-row--2b">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-layer-group"></i> Preparation per Cycle (hari ini)</div>
                </div>
                <div class="ov-panel__body">
                    @if ($prepByCycle->isEmpty())
                        <div class="ov-empty">Tidak ada loading list delivery hari ini.</div>
                    @else
                        <div class="ov-cycle">
                            @foreach ($prepByCycle as $c)
                                @php $col = $c['pct'] >= 100 ? '#16a34a' : ($c['pct'] > 0 ? '#d97706' : '#dc2626'); @endphp
                                <div class="ov-cycle__row">
                                    <div class="ov-cycle__top">
                                        <span>Cycle {{ $c['cycle'] }}</span>
                                        <span>{{ $c['complete'] }}/{{ $c['total'] }} · {{ $c['pct'] }}%</span>
                                    </div>
                                    <div class="ov-bar"><div class="ov-bar__fill" style="width: {{ $c['pct'] }}%; background: {{ $col }};"></div></div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-arrow-down"></i> Part Paling Kekurangan Stok</div>
                </div>
                <div class="ov-panel__body" style="padding: 4px 8px;">
                    @if ($topUnder->isEmpty())
                        <div class="ov-empty">Semua part di atas/standar stok. 👍</div>
                    @else
                        <table class="ov-table">
                            <thead>
                                <tr><th>Back No</th><th>Line</th><th class="text-right">Stok</th><th class="text-right">Std</th><th class="text-right">Kurang</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($topUnder as $p)
                                    <tr>
                                        <td><span class="ov-badge">{{ $p->back_number ?? '-' }}</span></td>
                                        <td>{{ $p->line ?? '-' }}</td>
                                        <td class="text-right">{{ (int) $p->cur }}</td>
                                        <td class="text-right">{{ (int) $p->std }}</td>
                                        <td class="text-right ov-gap">-{{ (int) $p->gap }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== ROW: Recent errors ===== --}}
        <div class="ov-row">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-exclamation-triangle"></i> Error Terbaru</div>
                    <a href="{{ route('error.log') }}" style="font-size:11px;font-weight:700;color:var(--primary);text-decoration:none;">Lihat semua →</a>
                </div>
                <div class="ov-panel__body" style="padding: 4px 8px;">
                    @if ($recentErrors->isEmpty())
                        <div class="ov-empty">Belum ada error tercatat. 👍</div>
                    @else
                        <table class="ov-table">
                            <thead>
                                <tr><th>Area</th><th>Pesan</th><th>Expected</th><th>Scanned</th><th>Waktu</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($recentErrors as $e)
                                    <tr>
                                        <td><span class="ov-area-tag">{{ $e->area ?? '-' }}</span></td>
                                        <td>{{ \Illuminate\Support\Str::limit($e->message ?? '-', 60) }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($e->expected ?? '-', 28) }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($e->scanned ?? '-', 28) }}</td>
                                        <td style="white-space:nowrap;color:var(--text-muted);">{{ $e->date ? \Carbon\Carbon::parse($e->date)->format('d/m H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script>
        (function () {
            var trend = @json($d['trend'] ?? []);
            var stock = @json($d['stock'] ?? ['under' => 0, 'low' => 0, 'in' => 0]);

            var cats = trend.map(function (t) { return t.label; });

            if (window.ApexCharts && document.getElementById('ovTrendChart')) {
                new ApexCharts(document.getElementById('ovTrendChart'), {
                    chart: { type: 'area', height: 290, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                    series: [
                        { name: 'Pulling', data: trend.map(function (t) { return t.pulling; }) },
                        { name: 'Produksi', data: trend.map(function (t) { return t.produksi; }) },
                        { name: 'PIS', data: trend.map(function (t) { return t.pis; }) }
                    ],
                    colors: ['#0097D8', '#16a34a', '#d97706'],
                    xaxis: { categories: cats, labels: { style: { fontSize: '11px' } } },
                    yaxis: { labels: { style: { fontSize: '11px' } } },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
                    grid: { borderColor: 'rgba(0,0,0,.06)' },
                    tooltip: { shared: true }
                }).render();
            }

            if (window.ApexCharts && document.getElementById('ovStockChart')) {
                new ApexCharts(document.getElementById('ovStockChart'), {
                    chart: { type: 'donut', height: 290, fontFamily: 'Plus Jakarta Sans, sans-serif' },
                    series: [stock.under || 0, stock.low || 0, stock.in || 0],
                    labels: ['Di Bawah Standar', 'Pas Standar', 'Aman'],
                    colors: ['#dc2626', '#d97706', '#16a34a'],
                    legend: { position: 'bottom', fontSize: '12px' },
                    dataLabels: { enabled: true, formatter: function (val, o) { return o.w.globals.series[o.seriesIndex]; } },
                    plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Total Part', fontSize: '12px' } } } } },
                    stroke: { width: 2 }
                }).render();
            }
        })();
    </script>
@endsection
