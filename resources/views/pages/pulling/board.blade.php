<!DOCTYPE html>
<html lang="en" data-theme="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <title>Current Production Board</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/planning/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/planning/board.css') }}">

    <style>
        :root {
            --curr-accent: #3b82f6;
            /* biru */
            --next-accent: #10b981;
            /* hijau */
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

        /* Next list */
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

        /* Meta pills current (rapi) */
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

        /* Next compact info */
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
    </style>
</head>

@php
    $nowStr = \Carbon\Carbon::parse($selectedDate ?? now())->format('l, j F Y');
    $lines = ['AS003', 'AS004'];
@endphp

<body>
    <div class="container-fluid px-2 px-lg-3 py-4 board-container">

        {{-- HEADER global --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge date-pill time-pill"><span id="rt-hms">00:00:00</span></span>
            <span class="badge date-pill"><span id="boardDate">{{ $nowStr }}</span></span>
        </div>

        {{-- Tabs line --}}
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
                    $progStatus = $prog['status'] ?? 'Normal';
                    $warnChipCls = in_array($progStatus, ['NS', 'LS1', 'LS3'])
                        ? 'bg-warning-subtle'
                        : 'bg-success-subtle';

                    $next = $data['nextHighlight'] ?? [];
                    $nextBack = $next['back_no'] ?? '—';
                    $nextCust = $next['customer'] ?? '—';
                    $nextDock = $next['dock'] ?? '—';
                    $nextOrder = (int) ($next['order_qty'] ?? 0);
                    $nextTime = $next['delivery_time'] ?? '--';
                    $nextDate = $next['delivery_date'] ?? '--';

                    $nextList = $data['nextList'] ?? [];

                    // >>> Nilai awal Total Back No dari server <<<
                    $progTotalBN = (int) ($data['daily']['totalBackNo'] ?? ($data['totalBackNo'] ?? 0));
                @endphp

                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tab-{{ $L }}"
                    role="tabpanel" data-line="{{ $L }}">
                    <div class="row g-4">

                        {{-- LEFT: PROGRESS --}}
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

                                    <hr class="my-3 d-none">
                                    <div class="metric-callout mt-2" title="Total unique Back Number planned for today">
                                        <div class="metric-label">TOTAL BACK NO (TODAY)</div>
                                        <div class="metric-value number" data-role="prog-total-bn">
                                            {{ number_format($progTotalBN) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CENTER: CURRENT --}}
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

                                    <div class="order-badge mt-3"
                                        style="background:linear-gradient(90deg,
                          color-mix(in oklab,var(--next-accent) 30%, transparent),
                          color-mix(in oklab,var(--next-accent) 10%, transparent));
                          border:1px solid color-mix(in oklab,var(--next-accent) 25%, var(--tile-border));">
                                        <span class="label">ORDER</span>
                                        <strong class="value number"
                                            data-role="next-order">{{ number_format($nextOrder) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- NEXT PRODUCTION LIST --}}
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
        // Jam berjalan
        (function clock() {
            const el = document.getElementById('rt-hms');
            const pad = n => String(n).padStart(2, '0');

            function tick() {
                const now = new Date();
                const t = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                if (el) el.textContent = t;
            }
            tick();
            setInterval(tick, 1000);
        })();

        // Theme toggle (opsional)
        (() => {
            const html = document.documentElement;
            const btn = document.getElementById('themeToggle');
            const apply = theme => {
                html.setAttribute('data-theme', theme);
                html.setAttribute('data-bs-theme', theme);
                localStorage.setItem('board-theme', theme);
                if (btn) btn.querySelector('span').textContent = theme === 'dark' ? 'Dark' : 'Light';
            };
            const saved = localStorage.getItem('board-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            apply(saved || (prefersDark ? 'dark' : 'light'));
            btn?.addEventListener('click', () => {
                const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                apply(next);
            });
        })();

        // Alias Back No
        function applyBacknoAlias(root = document) {
            let map = {};
            try {
                map = JSON.parse(localStorage.getItem('backnoRenameMap') || '{}');
            } catch {}
            const fallback = {
                'D403': 'CI18',
                'D111': 'CI12',
                'D500': 'CI19'
            };
            const aliasMap = Object.assign({}, fallback, map);
            root.querySelectorAll('.js-backno').forEach(el => {
                const raw = (el.textContent || '').trim().toUpperCase();
                if (aliasMap[raw]) el.textContent = aliasMap[raw];
            });
        }
        document.addEventListener('DOMContentLoaded', () => applyBacknoAlias());

        // Wheel → horizontal scroll
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.np-scroll').forEach(sc => {
                sc.addEventListener('wheel', (e) => {
                    if (Math.abs(e.deltaX) >= Math.abs(e.deltaY)) return;
                    const goingRight = e.deltaY > 0,
                        goingLeft = e.deltaY < 0;
                    const canLeft = sc.scrollLeft > 0;
                    const canRight = sc.scrollLeft + sc.clientWidth < sc.scrollWidth - 1;
                    const willScrollHoriz = (goingRight && canRight) || (goingLeft && canLeft);
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

    <!-- ====== SSE Hook ====== -->
    <script>
        (function boardLiveSSE() {
            const dateISO = @json($selectedDate ?? now()->format('Y-m-d'));
            const DEBUG = false;
            const log = (...a) => {
                if (DEBUG) console.debug('[board-sse]', ...a);
            };

            function debounce(fn, wait) {
                let t = null;
                return function() {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, arguments), wait);
                };
            }

            // --- Update DOM satu line ---
            function updateLine(lineKey, payload) {
                const tab = document.querySelector(`[data-line="${lineKey}"]`);
                if (!tab) return;

                // Progress
                const pg = payload.progress || {};
                const pgOrder = +pg.order || 0;
                const pgActual = +pg.actual || 0;
                const pgPct = pgOrder ? Math.min(100, Math.round((pgActual / pgOrder) * 100)) : 0;
                const pgStatus = String(pg.status || 'Normal');

                tab.querySelector('[data-role="prog-label"]')
                    ?.replaceChildren(document.createTextNode(`(${pg.label||''})`));

                const setTxt = (sel, v) => {
                    const el = tab.querySelector(sel);
                    if (el) el.textContent = v;
                };
                setTxt('[data-role="prog-order"]', pgOrder.toLocaleString('id-ID'));
                setTxt('[data-role="prog-actual"]', pgActual.toLocaleString('id-ID'));
                setTxt('[data-role="prog-pct"]', pgPct + '%');
                const pgBar = tab.querySelector('[data-role="prog-bar"]');
                if (pgBar) pgBar.style.width = pgPct + '%';

                const badge = tab.querySelector('[data-role="prog-status"]');
                if (badge) {
                    badge.textContent = pgStatus;
                    badge.classList.remove('bg-warning-subtle', 'bg-success-subtle');
                    badge.classList.add(['NS', 'LS1', 'LS3'].includes(pgStatus) ? 'bg-warning-subtle' :
                        'bg-success-subtle');
                }

                // Current
                const cur = payload.current || {};
                const cOrder = +cur.order_qty || 0;
                const cDone = (+cur.dp || 0) + (+cur.sc || 0);
                const cPct = cOrder ? Math.min(100, Math.round((+cur.dp || 0) / cOrder * 100)) : 0;
                const cBal = Math.max(0, cOrder - cDone);

                setTxt('[data-role="curr-backno"]', cur.back_no || '—');
                setTxt('[data-role="curr-customer"]', cur.customer || '—');
                setTxt('[data-role="curr-dock"]', cur.dock || '—');
                setTxt('[data-role="curr-start"]', cur.start || '--');
                setTxt('[data-role="curr-order"]', cOrder.toLocaleString('id-ID'));
                setTxt('[data-role="curr-done"]', cDone.toLocaleString('id-ID'));
                const balEl = tab.querySelector('[data-role="curr-balance"]');
                if (balEl) {
                    if (cBal <= 0) {
                        balEl.textContent = 'COMPLETED';
                        balEl.classList.add('text-success');
                    } else {
                        balEl.textContent = cBal.toLocaleString('id-ID');
                        balEl.classList.remove('text-success');
                    }
                }
                const cBar = tab.querySelector('[data-role="curr-bar"]');
                if (cBar) cBar.style.width = cPct + '%';
                setTxt('[data-role="curr-pct"]', cPct + '%');

                // Next highlight
                const nx = payload.nextHighlight || {};
                setTxt('[data-role="next-backno"]', nx.back_no || '—');
                setTxt('[data-role="next-customer"]', nx.customer || '—');
                setTxt('[data-role="next-dock"]', nx.dock || '—');
                setTxt('[data-role="next-time"]', nx.delivery_time || '--');
                setTxt('[data-role="next-date"]', nx.delivery_date || '');
                setTxt('[data-role="next-order"]', (+nx.order_qty || 0).toLocaleString('id-ID'));

                // Next list
                const listWrap = tab.querySelector('[data-role="next-list"]');
                if (listWrap) {
                    listWrap.innerHTML = '';
                    const arr = payload.nextList || [];
                    if (!arr.length) {
                        const d = document.createElement('div');
                        d.className = 'text-muted';
                        d.textContent = 'Tidak ada data berikutnya.';
                        listWrap.appendChild(d);
                    } else {
                        arr.forEach(row => {
                            const item = document.createElement('div');
                            item.className = 'tile-square radius-4';
                            item.innerHTML = `
            <div class="bk number js-backno">${(row.back_no||'—')}</div>
            <div class="meta-row mt-1"><span class="tag">Dock</span><span>${(row.dock||'—')}</span></div>
            <div></div>
            <div class="next-order-pill mt-2">
              <div class="label">ORDER</div>
              <div class="value number">${((+row.order_qty||0).toLocaleString('id-ID'))}</div>
            </div>`;
                            listWrap.appendChild(item);
                        });
                        applyBacknoAlias(listWrap);
                    }
                }

                // TOTAL BACK NO (TODAY)
                let totalBN = Number(
                    (payload && payload.daily && payload.daily.totalBackNo) ??
                    payload?.totalBackNo ?? 0
                );
                if (!totalBN) {
                    const seen = new Set();
                    const add = v => {
                        v = (v || '').toString().trim().toUpperCase();
                        if (v && v !== '—') seen.add(v);
                    };
                    add(cur.back_no);
                    add(nx.back_no);
                    (payload.nextList || []).forEach(r => add(r?.back_no));
                    totalBN = seen.size;
                }
                const elTotal = tab.querySelector('[data-role="prog-total-bn"]');
                if (elTotal) elTotal.textContent = totalBN.toLocaleString('id-ID');

                applyBacknoAlias(tab);
            }

            // Fetch state JSON & apply
            const refreshBoard = debounce(function() {
                fetch(`/pulling/board/state?date=${encodeURIComponent(dateISO)}`, {
                        cache: 'no-store'
                    })
                    .then(r => r.ok ? r.json() : Promise.reject(r.status))
                    .then(data => {
                        const boards = data.boards || {};
                        ['AS003', 'AS004'].forEach(L => boards[L] && updateLine(L, boards[L]));
                        log('refreshed');
                    })
                    .catch(err => log('refresh error', err));
            }, 350);

            // EventSource
            let es;
            try {
                es = new EventSource(`/stream/direct-pulling-updates?date=${encodeURIComponent(dateISO)}`);
                es.onopen = () => {
                    log('open');
                    refreshBoard();
                };
                es.onmessage = (e) => {
                    log('message', e.data);
                    refreshBoard();
                };
                ['connected', 'refetching', 'refetched', 'directPullingUpdate', 'ping'].forEach(name => {
                    es.addEventListener(name, (e) => {
                        log(name, e.data);
                        refreshBoard();
                    });
                });
                es.onerror = (e) => {
                    log('error', e); /* auto-reconnect */
                };
                window.addEventListener('beforeunload', () => {
                    try {
                        es && es.close();
                    } catch {}
                });
            } catch (e) {
                log('EventSource construct fail', e);
            }

            // Safety nets
            setInterval(refreshBoard, 15000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) refreshBoard();
            });
            document.addEventListener('DOMContentLoaded', refreshBoard);
        })();
    </script>

    <!-- Drag-to-scroll momentum -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.np-scroll').forEach(sc => {
                let isDown = false,
                    lastX = 0,
                    vel = 0,
                    raf = 0,
                    dragged = false;
                const stopMomentum = () => {
                    if (raf) cancelAnimationFrame(raf);
                    raf = 0;
                };
                const momentum = () => {
                    stopMomentum();
                    let v = vel;
                    const step = () => {
                        if (Math.abs(v) < 0.1) return;
                        sc.scrollLeft -= v;
                        v *= 0.95;
                        raf = requestAnimationFrame(step);
                    };
                    step();
                };

                sc.addEventListener('pointerdown', e => {
                    if (e.button !== undefined && e.button !== 0) return;
                    isDown = true;
                    dragged = false;
                    vel = 0;
                    lastX = e.clientX;
                    sc.classList.add('is-dragging');
                    sc.setPointerCapture?.(e.pointerId);
                    stopMomentum();
                    e.preventDefault();
                });
                sc.addEventListener('pointermove', e => {
                    if (!isDown) return;
                    const x = e.clientX,
                        dx = x - lastX;
                    if (dx !== 0) {
                        sc.scrollLeft -= dx;
                        vel = dx;
                        lastX = x;
                        if (Math.abs(dx) > 3) dragged = true;
                    }
                });
                const end = () => {
                    if (!isDown) return;
                    isDown = false;
                    sc.classList.remove('is-dragging');
                    if (Math.abs(vel) > 0.5) momentum();
                    setTimeout(() => dragged = false, 0);
                };
                sc.addEventListener('pointerup', end);
                sc.addEventListener('pointercancel', end);
                sc.addEventListener('pointerleave', end);
                sc.addEventListener('click', e => {
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
