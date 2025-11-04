<!DOCTYPE html>
<html lang="en" data-theme="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Current Production Board</title>

    <!-- Theme pre-init to avoid flicker -->
    <script>
        (function() {
            try {
                var html = document.documentElement;
                var saved = localStorage.getItem('board-theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = saved || (prefersDark ? 'dark' : 'light');
                html.setAttribute('data-theme', theme);
                html.setAttribute('data-bs-theme', theme);
            } catch (e) {}
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/planning/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/planning/board.css') }}">

    <style>
        :root {
            --curr-accent: #3b82f6;
            --next-accent: #10b981;
            --tile-border: rgba(255, 255, 255, .06);
            --muted: rgba(255, 255, 255, .65);
            --text-strong: #fff;
        }

        [data-theme="light"] {
            --tile-border: rgba(0, 0, 0, .08);
            --muted: rgba(0, 0, 0, .60);
            --text-strong: #0f172a;
        }

        .board-container {
            max-width: none;
            padding-inline: clamp(8px, 1.2vw, 16px)
        }

        .card-current,
        .card-next {
            min-height: 640px;
            border: 1px solid var(--tile-border);
            border-radius: 18px
        }

        .card-current {
            box-shadow: 0 0 0 2px color-mix(in oklab, var(--curr-accent) 18%, transparent) inset
        }

        .card-next {
            box-shadow: 0 0 0 2px color-mix(in oklab, var(--next-accent) 18%, transparent) inset
        }

        .card-current .card-header {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 20%, transparent), transparent)
        }

        .card-next .card-header {
            background: linear-gradient(90deg, color-mix(in oklab, var(--next-accent) 20%, transparent), transparent)
        }

        .current-value {
            font-size: clamp(3.2rem, 6.2vw, 6.5rem);
            line-height: 1.1
        }

        .next-value {
            font-size: clamp(2.6rem, 5vw, 5.2rem);
            line-height: 1.15
        }

        .current-title,
        .next-title {
            letter-spacing: .06em;
            opacity: .85
        }

        .time-pill {
            font-variant-numeric: tabular-nums
        }

        .metric-callout {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.25rem;
            border-radius: 16px;
            border: 1px solid var(--tile-border);
            color: var(--text-strong)
        }

        .metric-callout .metric-label {
            font-weight: 600;
            letter-spacing: .02em;
            color: var(--muted)
        }

        .metric-callout .metric-value {
            font-weight: 800
        }

        .metric-order {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 30%, transparent), transparent)
        }

        .metric-completed {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 22%, transparent), transparent)
        }

        .metric-balance {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 14%, transparent), transparent)
        }

        .qty-progress.big .bar {
            height: 14px
        }

        .qty-progress.big .val {
            font-weight: 800
        }

        .np-section .tile-grid {
            display: flex;
            gap: 12px;
            overflow: auto;
            padding: 4px
        }

        .np-section .tile-square {
            flex: 0 0 280px;
            display: grid;
            grid-template-rows: auto auto 1fr auto;
            gap: .25rem;
            border: 1px solid var(--tile-border);
            border-radius: 14px;
            padding: 12px
        }

        .np-section .bk {
            font-size: 1.4rem;
            font-weight: 800
        }

        .np-section .meta-row {
            font-size: .9rem;
            opacity: .85
        }

        .next-order-pill {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1rem;
            border-radius: 14px;
            width: 100%;
            background: linear-gradient(90deg, color-mix(in oklab, var(--next-accent) 30%, transparent), color-mix(in oklab, var(--next-accent) 10%, transparent));
            border: 1px solid color-mix(in oklab, var(--next-accent) 25%, var(--tile-border));
        }

        .next-order-pill .label {
            font-weight: 600;
            letter-spacing: .02em;
            color: var(--muted)
        }

        .next-order-pill .value {
            font-weight: 800
        }

        .current-block .meta-row {
            display: flex;
            align-items: center;
            gap: .75rem 1rem;
            flex-wrap: wrap;
            opacity: .95;
            margin-top: .35rem
        }

        .current-block .meta-row .tag {
            padding: .45rem .9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .03);
            border: 1px solid var(--tile-border);
            font-size: .9rem;
            font-weight: 600;
            letter-spacing: .03em;
            color: var(--muted)
        }

        .current-block .meta-row .val {
            font-weight: 800;
            font-size: 1.05rem;
            line-height: 1;
            margin-right: .2rem;
            text-transform: uppercase
        }

        .current-block .meta-row .dot {
            opacity: .45;
            padding: 0 .15rem;
            line-height: 1
        }

        .card-next .card-body {
            display: flex;
            flex-direction: column;
            padding: .9rem 1rem .5rem
        }

        .np-head {
            margin-bottom: .4rem
        }

        .np-backno {
            font-size: clamp(2.6rem, 5vw, 4.4rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: .02em
        }

        .np-customer {
            margin-top: .15rem;
            font-size: .9rem;
            font-weight: 600;
            opacity: .9;
            text-transform: uppercase;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden
        }

        .np-rows {
            display: grid;
            grid-template-columns: max-content 1fr;
            column-gap: .6rem;
            row-gap: .25rem;
            align-items: center;
            margin-top: .35rem
        }

        .np-rows dt {
            display: inline-flex;
            align-items: center;
            padding: .28rem .6rem;
            border-radius: 999px;
            border: 1px solid var(--tile-border);
            background: rgba(255, 255, 255, .03);
            font-size: .70rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap
        }

        .np-rows dd {
            margin: 0;
            font-size: .80rem;
            font-weight: 800;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .card-next .order-badge {
            margin-top: auto;
            padding: .85rem 1rem;
            border-radius: 14px
        }

        .card-next .order-badge .value {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 900
        }

        .btn-theme {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .06);
            border: 1px solid var(--tile-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: inherit;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
            cursor: pointer;
        }

        .btn-theme:hover {
            border-color: color-mix(in oklab, var(--curr-accent) 40%, var(--tile-border))
        }

        .btn-theme svg {
            width: 22px;
            height: 22px;
            display: none
        }

        html[data-theme="light"] .btn-theme .icon-sun {
            display: block
        }

        html[data-theme="dark"] .btn-theme .icon-moon {
            display: block
        }

        .btn-glass-back {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            height: 42px;
            padding: .45rem .9rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid var(--tile-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: inherit;
            text-decoration: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
        }

        .btn-glass-back:hover {
            border-color: color-mix(in oklab, var(--curr-accent) 40%, var(--tile-border))
        }

        .btn-glass-back .ico {
            width: 18px;
            height: 18px;
            flex: 0 0 18px
        }

        @media (max-width:576px) {
            .btn-back-label {
                display: none
            }
        }
    </style>
</head>

@php
    // ==== gunakan variabel dari controller ====
    $group = strtoupper($group ?? request('group', 'AS')); // 'AS' or 'MA'
    $selectedDate = $selectedDate ?? request('date', now()->format('Y-m-d'));
    $nowStr = \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, j F Y');
    // PENTING: JANGAN override $lines — biarkan dari controller (sudah dibangun via buildBoardsForDate_)
    // $lines = $lines ?? [];
@endphp

<body>
    <div class="container-fluid px-2 px-lg-3 py-4 board-container">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('board.landing') }}" class="btn-glass-back" id="btnBack" title="Back (Alt+←)">
                    <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span class="btn-back-label">Back</span>
                </a>

                <span class="badge date-pill time-pill"><span id="rt-hms">00:00:00</span></span>
                <span class="badge date-pill">Group: <strong class="ms-1">{{ $group }}</strong></span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge date-pill"><span id="boardDate">{{ $nowStr }}</span></span>
            </div>
        </div>

        <!-- Tabs line -->
        <ul class="nav nav-tabs mb-3" id="lineTabs" role="tablist">
            @foreach ($lines as $i => $L)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $i === 0 ? 'active' : '' }}" id="tab-{{ $L }}-btn"
                        data-bs-toggle="tab" data-bs-target="#tab-{{ $L }}" type="button" role="tab">
                        {{ $L }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach ($lines as $i => $L)
                @php
                    $data = $boards[$L] ?? [
                        'progress' => ['label' => '', 'order' => 0, 'actual' => 0, 'status' => ''],
                        'current' => [
                            'back_no' => '—',
                            'customer' => '—',
                            'dock' => '—',
                            'order_qty' => 0,
                            'dp' => 0,
                            'sc' => 0,
                            'start' => '--',
                        ],
                        'nextHighlight' => [
                            'back_no' => '—',
                            'customer' => '—',
                            'dock' => '—',
                            'order_qty' => 0,
                            'delivery_time' => '--',
                            'delivery_date' => '',
                        ],
                        'nextList' => [],
                        'daily' => ['totalBackNo' => 0],
                        'totalBackNo' => 0,
                    ];

                    $cur = $data['current'] ?? [];
                    $curBack = $cur['back_no'] ?? '—';
                    $curCust = $cur['customer'] ?? '—';
                    $curDock = $cur['dock'] ?? '—';
                    $curOrder = (int) ($cur['order_qty'] ?? 0);
                    $curDP = (int) ($cur['dp'] ?? 0);
                    $curSC = (int) ($cur['sc'] ?? 0);
                    $curDone = max(0, $curDP + $curSC);
                    $curPct = $curOrder ? min(100, round(($curDP / $curOrder) * 100)) : 0;
                    $curStart = $cur['start'] ?? '--';
                    $curBalance = max(0, $curOrder - $curDone);

                    $prog = $data['progress'] ?? [];
                    $progOrder = (int) ($prog['order'] ?? 0);
                    $progActual = (int) ($prog['actual'] ?? 0);
                    $progPct = $progOrder ? min(100, round(($progActual / $progOrder) * 100)) : 0;

                    $next = $data['nextHighlight'] ?? [];
                    $nextBack = $next['back_no'] ?? '—';
                    $nextCust = $next['customer'] ?? '—';
                    $nextDock = $next['dock'] ?? '—';
                    $nextOrder = (int) ($next['order_qty'] ?? 0);
                    $nextTime = $next['delivery_time'] ?? '--';
                    $nextDate = $next['delivery_date'] ?? '--';

                    $nextList = $data['nextList'] ?? [];
                    $progTotalBN = (int) ($data['daily']['totalBackNo'] ?? ($data['totalBackNo'] ?? 0));
                @endphp

                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tab-{{ $L }}"
                    role="tabpanel" data-line="{{ $L }}">
                    <div class="row g-4">

                        <!-- LEFT: PROGRESS -->
                        <div class="col-12 col-xl-3 col-xxl-2">
                            <div class="card tile radius-4 h-100">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Overall Progress</strong>
                                </div>
                                <div class="card-body">
                                    <div class="progress-readout">
                                        <div class="big-number number" data-role="prog-actual">
                                            {{ number_format($progActual) }}</div>
                                        <div class="sub-label">Completed</div>
                                        <div class="small text-secondary">of <span
                                                data-role="prog-order">{{ number_format($progOrder) }}</span></div>
                                    </div>
                                    <div class="qty-progress mt-3"
                                        title="Actual {{ $progActual }} / {{ $progOrder }}">
                                        <div class="bar"><i data-role="prog-bar"
                                                style="width: {{ $progPct }}%"></i></div>
                                        <span class="val number" data-role="prog-pct">{{ $progPct }}%</span>
                                    </div>

                                    <div class="kpi-tile mt-3" aria-label="Total Back Number Today">
                                        <div class="kpi-icon" aria-hidden="true">BN</div>
                                        <div class="kpi-label">Total Model</div>
                                        <div class="kpi-value number" data-role="prog-total-bn">
                                            {{ number_format($progTotalBN) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CENTER: CURRENT -->
                        <div class="col-12 col-xl-6 col-xxl-8">
                            <div class="card tile radius-4 h-100 card-current">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Current Production</strong>
                                </div>
                                <div class="card-body">
                                    <div class="current-block">
                                        <div class="current-title">BACK NUMBER</div>
                                        <div class="current-value fw-bold number js-backno" data-role="curr-backno">
                                            {{ $curBack }}</div>

                                        <div class="meta-row">
                                            <span class="tag">Customer</span><span class="val"
                                                data-role="curr-customer">{{ $curCust }}</span><span
                                                class="dot">•</span>
                                            <span class="tag">Dock</span><span class="val"
                                                data-role="curr-dock">{{ $curDock }}</span><span
                                                class="dot">•</span>
                                            <span class="tag">Start</span><span class="val time-pill"
                                                data-role="curr-start">{{ $curStart }}</span>
                                        </div>

                                        <div class="metrics-grid mt-3">
                                            <div class="metric-callout metric-order" title="Order">
                                                <div class="metric-label">ORDER</div>
                                                <div class="metric-value number" data-role="curr-order">
                                                    {{ number_format($curOrder) }}</div>
                                            </div>
                                            <div class="metric-callout metric-completed" title="Completed">
                                                <div class="metric-label">COMPLETED</div>
                                                <div class="metric-value number" data-role="curr-done">
                                                    {{ number_format($curDone) }}</div>
                                            </div>
                                            <div class="metric-callout metric-balance" title="Balance">
                                                <div class="metric-label">BALANCE</div>
                                                <div class="metric-value number {{ $curBalance <= 0 ? 'text-success' : '' }}"
                                                    data-role="curr-balance">
                                                    {{ $curBalance <= 0 ? 'COMPLETED' : number_format($curBalance) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="qty-progress big mt-3"
                                            title="DP {{ $curDP }} / {{ $curOrder }}">
                                            <div class="bar"><i data-role="curr-bar"
                                                    style="width: {{ $curPct }}%"></i></div>
                                            <span class="val number" data-role="curr-pct">{{ $curPct }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: NEXT -->
                        <div class="col-12 col-xl-3 col-xxl-2">
                            <div class="card tile radius-4 h-100 card-next">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Next Production</strong>
                                </div>
                                <div class="card-body">
                                    <div class="np-head">
                                        <div class="eyebrow">Back Number</div>
                                        <div class="np-backno number js-backno" data-role="next-backno">
                                            {{ $nextBack }}</div>
                                        <div class="np-customer" data-role="next-customer">{{ $nextCust }}</div>
                                    </div>

                                    <dl class="np-rows">
                                        <dt>Dock</dt>
                                        <dd data-role="next-dock">{{ $nextDock }}</dd>
                                        <dt>Time</dt>
                                        <dd class="time-pill" data-role="next-time">{{ $nextTime }}</dd>
                                        <dt>Date</dt>
                                        <dd data-role="next-date">{{ $nextDate }}</dd>
                                    </dl>

                                    <div class="order-badge mt-3 mb-3"
                                        style="background:linear-gradient(90deg,color-mix(in oklab,var(--next-accent) 30%, transparent),color-mix(in oklab,var(--next-accent) 10%, transparent));border:1px solid color-mix(in oklab,var(--next-accent) 25%, var(--tile-border));">
                                        <span class="label">ORDER</span>
                                        <strong class="value number"
                                            data-role="next-order">{{ number_format($nextOrder) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- NEXT PRODUCTION LIST -->
                    <div class="mt-4 np-section">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="m-0 text-secondary">Next Production list</h5>
                        </div>

                        <div class="next-row-wrap">
                            <div class="tile-grid np-scroll" data-role="next-list">
                                @forelse($nextList as $row)
                                    @php
                                        $bk = $row['back_no'] ?? '—';
                                        $dock = $row['dock'] ?? '—';
                                        $ord = (int) ($row['order_qty'] ?? 0);
                                    @endphp
                                    <div class="tile-square radius-4">
                                        <div class="bk number js-backno">{{ $bk }}</div>
                                        <div class="meta-row mt-1"><span
                                                class="tag">Dock</span><span>{{ $dock }}</span></div>
                                        <div></div>
                                        <div class="next-order-pill mt-2">
                                            <div class="label">ORDER</div>
                                            <div class="value number">{{ number_format($ord) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">Tidak ada data berikutnya.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /* ==== Clock ==== */
        (function clock() {
            var el = document.getElementById('rt-hms');
            var pad = function(n) {
                return String(n).padStart(2, '0');
            };

            function tick() {
                var now = new Date();
                var t = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                if (el) el.textContent = t;
            }
            tick();
            setInterval(tick, 1000);
        })();

        /* ==== Alias Back No (robust) ==== */
        function buildAliasMap() {
            var base = {
                D111: 'CI12',
                CI12: 'CI12',
                D500: 'CI19',
                CI19: 'CI19',
                D403: 'CI18',
                CI18: 'CI18'
            };
            var custom = {};
            try {
                custom = JSON.parse(localStorage.getItem('backnoRenameMap') || '{}');
            } catch (e) {}
            var normCustom = {};
            for (var k in (custom || {})) {
                if (!Object.prototype.hasOwnProperty.call(custom, k)) continue;
                var K = String(k).trim().toUpperCase();
                var V = String(custom[k] || '').trim().toUpperCase();
                if (K) normCustom[K] = V || K;
            }
            for (var c in normCustom) base[c] = normCustom[c];
            return base;
        }

        function aliasBackNo(raw, map) {
            var B = String(raw || '').trim().toUpperCase();
            return map[B] || B;
        }

        function applyBacknoAlias(root) {
            root = root || document;
            var map = buildAliasMap();
            var els = root.querySelectorAll('.js-backno');
            els.forEach(function(el) {
                var before = el.textContent;
                var after = aliasBackNo(before, map);
                if (after !== before) el.textContent = after;
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            applyBacknoAlias(document);
        });

        /* ==== Wheel → horizontal scroll ==== */
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.np-scroll').forEach(function(sc) {
                sc.addEventListener('wheel', function(e) {
                    if (Math.abs(e.deltaX) >= Math.abs(e.deltaY)) return;
                    var goingRight = e.deltaY > 0,
                        goingLeft = e.deltaY < 0;
                    var canLeft = sc.scrollLeft > 0;
                    var canRight = sc.scrollLeft + sc.clientWidth < sc.scrollWidth - 1;
                    var willScrollHoriz = (goingRight && canRight) || (goingLeft && canLeft);
                    if (willScrollHoriz) {
                        e.preventDefault();
                        sc.scrollLeft += e.deltaY;
                    }
                }, {
                    passive: false
                });
            });
        });
    </script>

    <!-- ====== SSE Hook (support GROUP) ====== -->
    <script>
        (function boardLiveSSE() {
            var dateISO = @json($selectedDate);
            var GROUP = @json($group);
            var LINES = @json($lines);

            var DEBUG = false;
            var log = function() {
                if (DEBUG) console.debug('[board-sse]', ...arguments);
            };

            /* ===== helpers merge 6I (khusus AS) ===== */
            var normU = function(s) {
                return String(s || '').trim().toUpperCase();
            };
            var isDock6I = function(v) {
                return normU(v) === '6I';
            };
            var isASLine = function(L) {
                return /^AS/i.test(String(L || ''));
            };

            function unifyAlias(bn) {
                return aliasBackNo(bn, buildAliasMap());
            }

            function unifyBacknoForLine(bn, lineKey) {
                if (!isASLine(lineKey)) return null;
                var B = normU(unifyAlias(bn));
                if (lineKey === 'AS003') return (B === 'CI12') ? 'CI12' : null;
                if (lineKey === 'AS004') return (B === 'CI19') ? 'CI19' : null;
                return null;
            }

            function mdToISO(md, refISO) {
                if (!md) return '';
                var m = String(md).trim().match(/^(\d{1,2})\s*\/\s*(\d{1,2})$/);
                if (!m) return String(md);
                var y = String((refISO || dateISO || new Date().toISOString().slice(0, 10))).slice(0, 4);
                var MM = String(m[1]).padStart(2, '0');
                var DD = String(m[2]).padStart(2, '0');
                return y + '-' + MM + '-' + DD;
            }

            function key6I(lineKey, row) {
                var bnUnified = unifyBacknoForLine(row && row.back_no, lineKey);
                if (!bnUnified || !isDock6I(row && row.dock)) return null;
                var tm = (row && row.delivery_time ? String(row.delivery_time).trim() : '');
                var d = (row && row.delivery_date) || '';
                var iso = /^\d{4}-\d{2}-\d{2}$/.test(d) ? d : mdToISO(d, dateISO);
                if (!tm || !iso) return null;
                return iso + '|' + tm + '|' + bnUnified;
            }

            function apply6IMerge(lineKey, payload) {
                if (!payload) return payload;
                var nx = payload.nextHighlight || null;
                var nl = Array.isArray(payload.nextList) ? payload.nextList.slice() : [];

                var groups = new Map();

                function add(row) {
                    var k = key6I(lineKey, row);
                    if (!k) return null;
                    var sum = (+row.order_qty || 0);
                    var bnU = unifyBacknoForLine(row.back_no, lineKey);
                    var rec = groups.get(k) || {
                        sum: 0,
                        rep: Object.assign({}, row),
                        unified: bnU
                    };
                    rec.sum += sum;
                    rec.rep = Object.assign({}, rec.rep, {
                        back_no: bnU,
                        dock: '6I',
                        order_qty: rec.sum
                    });
                    groups.set(k, rec);
                    return k;
                }
                var nxKey = nx ? add(nx) : null;
                nl.forEach(add);

                if (nx && nxKey && groups.has(nxKey)) {
                    payload.nextHighlight = Object.assign({}, groups.get(nxKey).rep);
                } else if (nx) {
                    payload.nextHighlight = Object.assign({}, nx, {
                        back_no: unifyAlias(nx.back_no)
                    });
                }

                var emitted = new Set(nxKey ? [nxKey] : []);
                var out = [];
                nl.forEach(function(row) {
                    var k = key6I(lineKey, row);
                    if (!k) {
                        out.push(Object.assign({}, row, {
                            back_no: unifyAlias(row.back_no)
                        }));
                        return;
                    }
                    if (emitted.has(k)) return;
                    var rec = groups.get(k);
                    if (rec) {
                        out.push(Object.assign({}, rec.rep));
                        emitted.add(k);
                    }
                });

                payload.nextList = out;
                return payload;
            }
            /* ===================================== */

            function debounce(fn, wait) {
                var t = null;
                return function() {
                    clearTimeout(t);
                    t = setTimeout(fn.bind(this, ...arguments), wait);
                };
            }

            function setTxt(tab, sel, v) {
                var el = tab.querySelector(sel);
                if (el) el.textContent = v;
            }

            // --- helper angka aman
            function toInt(v, d = 0) {
                v = parseInt(v, 10);
                return isNaN(v) ? d : v;
            }

            // --- parse "HH:mm" ⇒ menit sejak 00:00 (untuk sort)
            function timeToMin(hhmm) {
                var m = String(hhmm || '').trim().match(/^(\d{1,2}):(\d{2})$/);
                if (!m) return 24 * 60 + 59; // taruh paling belakang jika tak valid
                return toInt(m[1]) * 60 + toInt(m[2]);
            }

            // --- tanggal "M/D" ⇒ "YYYY-MM-DD" (sudah ada mdToISO di board, reuse)
            // gunakan mdToISO(md, refISO) yang sudah didefinisikan di atas

            // --- baca siklus jika payload nextList menyertakan "cycle" (opsional)
            function getCycle(row) {
                var c = row && row.cycle;
                if (c == null) return null;
                var n = parseInt(String(c).match(/\d+/)?.[0] || '', 10);
                return isNaN(n) ? null : (((n - 1) % 8) + 1);
            }

            // --- normalisasi dock
            function normDock(v) {
                return String(v || '').trim().toUpperCase();
            }

            // --- skor prioritas back number (meniru logika prodplan secara ringkas)
            function backnoPriority(lineKey, row) {
                var bn = String(row?.back_no || '').trim().toUpperCase();
                bn = aliasBackNo(bn, buildAliasMap()); // konsisten dengan alias (D111→CI12, D500→CI19, D403→CI18)
                var dock = normDock(row?.dock);
                var cyc = getCycle(row); // bisa null kalau BE belum kirim

                var base = 100;

                // Prioritas khusus per line:
                if (lineKey === 'AS003') { // fokus CI12
                    if (bn === 'CI12') base = 10;
                    // kalau cycle tersedia, pecah CI12 (C4–7) lebih dulu seperti di prodplan
                    if (bn === 'CI12' && cyc != null) {
                        if (cyc >= 4 && cyc <= 7) base = 5; // CI12 (C4–7) paling atas
                        else base = 12; // CI12 (C8–3) setelahnya
                    }
                    // (opsional) CI18 sedikit diprioritaskan dibanding lain
                    if (bn === 'CI18' && base === 100) base = 20;
                } else if (lineKey === 'AS004') { // fokus CI19
                    if (bn === 'CI19') base = 10;
                    if (bn === 'CI18' && base === 100) base = 20; // optional
                }

                // STR biasanya ditempatkan belakang dibanding non-STR
                if (dock === 'STR') base += 15;

                // tie-breaker: waktu & tanggal
                var tMin = timeToMin(row?.delivery_time);
                var dISO = mdToISO(row?.delivery_date, (new Date()).toISOString().slice(0, 10));
                // YYYY-MM-DD ⇒ angka sederhana untuk banding
                var dKey = dISO && /^\d{4}-\d{2}-\d{2}$/.test(dISO) ? dISO : '9999-12-31';

                return {
                    base,
                    tMin,
                    dKey,
                    bn
                };
            }

            // --- comparator
            function cmpBackno(lineKey, a, b) {
                var A = backnoPriority(lineKey, a),
                    B = backnoPriority(lineKey, b);
                if (A.base !== B.base) return A.base - B.base;
                if (A.tMin !== B.tMin) return A.tMin - B.tMin;
                if (A.dKey !== B.dKey) return (A.dKey < B.dKey ? -1 : 1);
                // terakhir, leksikal Back No
                return (A.bn < B.bn ? -1 : (A.bn > B.bn ? 1 : 0));
            }

            // --- re-order payload.nextHighlight & payload.nextList sesuai aturan di atas
            function reorderNextPayload(lineKey, payload) {
                var nx = payload?.nextHighlight || null;
                var nl = Array.isArray(payload?.nextList) ? payload.nextList.slice() : [];
                var arr = [];
                if (nx && (nx.back_no || nx.order_qty || nx.dock)) arr.push(nx);
                arr.push(...nl);

                if (!arr.length) return payload;

                arr.sort((x, y) => cmpBackno(lineKey, x, y));

                payload.nextHighlight = arr[0] || {};
                payload.nextList = arr.slice(1);
                return payload;
            }

            function updateLine(lineKey, payload) {
                payload = apply6IMerge(lineKey, payload ? Object.assign({}, payload) : payload);
                payload = reorderNextPayload(lineKey, payload);

                var tab = document.querySelector('[data-line="' + lineKey + '"]');
                if (!tab) return;

                var pg = payload.progress || {};
                var pgOrder = +pg.order || 0;
                var pgActual = +pg.actual || 0;
                var pgPct = pgOrder ? Math.min(100, Math.round((pgActual / pgOrder) * 100)) : 0;

                setTxt(tab, '[data-role="prog-order"]', pgOrder.toLocaleString('id-ID'));
                setTxt(tab, '[data-role="prog-actual"]', pgActual.toLocaleString('id-ID'));
                setTxt(tab, '[data-role="prog-pct"]', pgPct + '%');
                var pgBar = tab.querySelector('[data-role="prog-bar"]');
                if (pgBar) pgBar.style.width = pgPct + '%';

                var cur = payload.current || {};
                var cOrder = +cur.order_qty || 0;
                var cDone = (+cur.dp || 0) + (+cur.sc || 0);
                var cPct = cOrder ? Math.min(100, Math.round((+cur.dp || 0) / cOrder * 100)) : 0;
                var cBal = Math.max(0, cOrder - cDone);

                setTxt(tab, '[data-role="curr-backno"]', unifyAlias(cur.back_no) || '—');
                setTxt(tab, '[data-role="curr-customer"]', cur.customer || '—');
                setTxt(tab, '[data-role="curr-dock"]', cur.dock || '—');
                setTxt(tab, '[data-role="curr-start"]', cur.start || '--');
                setTxt(tab, '[data-role="curr-order"]', cOrder.toLocaleString('id-ID'));
                setTxt(tab, '[data-role="curr-done"]', cDone.toLocaleString('id-ID'));
                var balEl = tab.querySelector('[data-role="curr-balance"]');
                if (balEl) {
                    if (cBal <= 0) {
                        balEl.textContent = 'COMPLETED';
                        balEl.classList.add('text-success');
                    } else {
                        balEl.textContent = cBal.toLocaleString('id-ID');
                        balEl.classList.remove('text-success');
                    }
                }
                var cBar = tab.querySelector('[data-role="curr-bar"]');
                if (cBar) cBar.style.width = cPct + '%';
                setTxt(tab, '[data-role="curr-pct"]', cPct + '%');

                var nx = payload.nextHighlight || {};
                setTxt(tab, '[data-role="next-backno"]', unifyAlias(nx.back_no) || '—');
                setTxt(tab, '[data-role="next-customer"]', nx.customer || '—');
                setTxt(tab, '[data-role="next-dock"]', nx.dock || '—');
                setTxt(tab, '[data-role="next-time"]', nx.delivery_time || '--');
                setTxt(tab, '[data-role="next-date"]', nx.delivery_date || '');
                setTxt(tab, '[data-role="next-order"]', (+nx.order_qty || 0).toLocaleString('id-ID'));

                var listWrap = tab.querySelector('[data-role="next-list"]');
                if (listWrap) {
                    listWrap.innerHTML = '';
                    var arr = payload.nextList || [];
                    if (!arr.length) {
                        var d = document.createElement('div');
                        d.className = 'text-muted';
                        d.textContent = 'Tidak ada data berikutnya.';
                        listWrap.appendChild(d);
                    } else {
                        var map = buildAliasMap();
                        arr.forEach(function(row) {
                            var item = document.createElement('div');
                            item.className = 'tile-square radius-4';
                            var bk = aliasBackNo(row.back_no, map);
                            item.innerHTML =
                                '<div class="bk number js-backno">' + bk + '</div>' +
                                '<div class="meta-row mt-1"><span class="tag">Dock</span><span>' + (row.dock ||
                                    '—') + '</span></div>' +
                                '<div></div>' +
                                '<div class="next-order-pill mt-2"><div class="label">ORDER</div><div class="value number">' +
                                ((+row.order_qty || 0).toLocaleString('id-ID')) + '</div></div>';
                            listWrap.appendChild(item);
                        });
                        applyBacknoAlias(listWrap);
                    }
                }

                var totalBN = Number((payload.daily && payload.daily.totalBackNo) ?? payload.totalBackNo ?? 0);
                if (!totalBN) {
                    var seen = new Set();

                    function add(v) {
                        v = unifyAlias((v || '').toString()).trim().toUpperCase();
                        if (v && v !== '—') seen.add(v);
                    }
                    add(cur.back_no);
                    add(nx.back_no);
                    (payload.nextList || []).forEach(function(r) {
                        add(r && r.back_no);
                    });
                    totalBN = seen.size;
                }
                var elTotal = tab.querySelector('[data-role="prog-total-bn"]');
                if (elTotal) elTotal.textContent = totalBN.toLocaleString('id-ID');

                applyBacknoAlias(tab);
            }

            function debounce(fn, wait) {
                var t = null;
                return function() {
                    clearTimeout(t);
                    t = setTimeout(fn.bind(this, ...arguments), wait);
                };
            }
            var refreshBoard = debounce(function() {
                var url = '/dashboard/production/board/state?date=' + encodeURIComponent(dateISO) + '&group=' +
                    encodeURIComponent(GROUP);
                fetch(url, {
                        cache: 'no-store'
                    })
                    .then(function(r) {
                        return r.ok ? r.json() : Promise.reject(r.status);
                    })
                    .then(function(data) {
                        var boards = data.boards || {};
                        LINES.forEach(function(L) {
                            if (boards[L]) updateLine(L, boards[L]);
                        });
                    })
                    .catch(function(err) {
                        /* silent */
                    });
            }, 350);

            var es;
            try {
                es = new EventSource('/stream/direct-pulling-updates?date=' + encodeURIComponent(dateISO));
                es.onopen = function() {
                    refreshBoard();
                };
                es.onmessage = function() {
                    refreshBoard();
                };
                ['connected', 'refetching', 'refetched', 'directPullingUpdate', 'ping'].forEach(function(name) {
                    es.addEventListener(name, function() {
                        refreshBoard();
                    });
                });
                es.onerror = function() {
                    /* silent */
                };
                window.addEventListener('beforeunload', function() {
                    try {
                        es && es.close();
                    } catch (_) {}
                });
            } catch (e) {}

            setInterval(refreshBoard, 15000);
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) refreshBoard();
            });
            document.addEventListener('DOMContentLoaded', refreshBoard);
        })();
    </script>

    <!-- Drag-to-scroll momentum -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.np-scroll').forEach(function(sc) {
                var isDown = false,
                    lastX = 0,
                    vel = 0,
                    raf = 0,
                    dragged = false;

                function stopMomentum() {
                    if (raf) cancelAnimationFrame(raf);
                    raf = 0;
                }

                function momentum() {
                    stopMomentum();
                    var v = vel;
                    (function step() {
                        if (Math.abs(v) < 0.1) return;
                        sc.scrollLeft -= v;
                        v *= 0.95;
                        raf = requestAnimationFrame(step);
                    })();
                }
                sc.addEventListener('pointerdown', function(e) {
                    if (e.button !== undefined && e.button !== 0) return;
                    isDown = true;
                    dragged = false;
                    vel = 0;
                    lastX = e.clientX;
                    sc.classList.add('is-dragging');
                    sc.setPointerCapture && sc.setPointerCapture(e.pointerId);
                    stopMomentum();
                    e.preventDefault();
                });
                sc.addEventListener('pointermove', function(e) {
                    if (!isDown) return;
                    var x = e.clientX,
                        dx = x - lastX;
                    if (dx !== 0) {
                        sc.scrollLeft -= dx;
                        vel = dx;
                        lastX = x;
                        if (Math.abs(dx) > 3) dragged = true;
                    }
                });

                function end() {
                    if (!isDown) return;
                    isDown = false;
                    sc.classList.remove('is-dragging');
                    if (Math.abs(vel) > 0.5) momentum();
                    setTimeout(function() {
                        dragged = false;
                    }, 0);
                }
                sc.addEventListener('pointerup', end);
                sc.addEventListener('pointercancel', end);
                sc.addEventListener('pointerleave', end);
                sc.addEventListener('click', function(e) {
                    if (dragged) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }, true);
            });
        });
    </script>
</body>

</html>
