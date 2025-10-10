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

        /* callout metric */
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

        /* Next list horizontal */
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

        /* ORDER pill full width */
        .next-order-pill {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1rem;
            border-radius: 14px;
            width: 100%;
            background: linear-gradient(90deg,
                    color-mix(in oklab, var(--next-accent) 30%, transparent),
                    color-mix(in oklab, var(--next-accent) 10%, transparent));
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
    </style>
</head>

@php
    $nowStr = \Carbon\Carbon::parse($selectedDate ?? now())->format('l, j F Y');
    $lines = ['AS003', 'AS004'];
@endphp

<body>
    <div class="container-fluid px-2 px-lg-3 py-4 board-container">

        {{-- HEADER global: waktu berjalan & tanggal --}}
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
                @endphp

                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tab-{{ $L }}"
                    role="tabpanel">

                    <div class="row g-4">
                        {{-- LEFT: PROGRESS --}}
                        <div class="col-12 col-xl-3 col-xxl-2">
                            <div class="card tile radius-4 h-100">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Progress ({{ $data['progress']['label'] ?? '' }})</strong>
                                    <span class="ms-auto badge {{ $warnChipCls }}">{{ $progStatus }}</span>
                                </div>
                                <div class="card-body">
                                    <div class="progress-readout">
                                        <div class="big-number number">{{ number_format($progActual) }}</div>
                                        <div class="sub-label">Completed</div>
                                        <div class="small text-secondary">of {{ number_format($progOrder) }}</div>
                                    </div>
                                    <div class="qty-progress mt-3"
                                        title="Actual {{ $progActual }} / {{ $progOrder }}">
                                        <div class="bar"><i style="width: {{ $progPct }}%"></i></div>
                                        <span class="val number">{{ $progPct }}%</span>
                                    </div>

                                    <hr class="my-4">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-secondary btn-sm">Shift Summary</button>
                                        <button class="btn btn-outline-secondary btn-sm">Warnings &amp; Alarms</button>
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
                                        <div class="current-value fw-bold number js-backno">{{ $curBack }}</div>

                                        <div class="meta-row">
                                            <span class="tag">Customer</span><span>{{ $curCust }}</span>
                                            <span>•</span>
                                            <span class="tag">Dock</span><span>{{ $curDock }}</span>
                                            <span>•</span>
                                            <span class="tag">Start</span><span>{{ $curStart }}</span>
                                        </div>

                                        <div class="metric-callout metric-order mt-3" title="Order">
                                            <div class="metric-label">ORDER</div>
                                            <div class="metric-value number">{{ number_format($curOrder) }}</div>
                                        </div>

                                        <div class="metric-callout metric-completed mt-3" title="Completed">
                                            <div class="metric-label">COMPLETED</div>
                                            <div class="metric-value number">{{ number_format($curDone) }}</div>
                                        </div>

                                        <div class="metric-callout metric-balance mt-3" title="Balance">
                                            <div class="metric-label">BALANCE</div>
                                            <div
                                                class="metric-value number {{ $curBalance <= 0 ? 'text-success' : '' }}">
                                                {{ $curBalance <= 0 ? 'COMPLETED' : number_format($curBalance) }}
                                            </div>
                                        </div>

                                        <div class="qty-progress big mt-3"
                                            title="DP {{ $curDP }} / {{ $curOrder }}">
                                            <div class="bar"><i style="width: {{ $curPct }}%"></i></div>
                                            <span class="val number">{{ $curPct }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: NEXT --}}
                        <div class="col-12 col-xl-3 col-xxl-2">
                            <div class="card tile radius-4 h-100 card-next">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Next Production</strong>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="next-title">BACK NUMBER</div>
                                        <div class="next-value fw-bold number js-backno">{{ $nextBack }}</div>
                                        <div class="small text-secondary">{{ $nextCust }}</div>
                                        <div class="meta-row mt-2">
                                            <span class="tag">Dock</span><span>{{ $nextDock }}</span>
                                            <span>•</span>
                                            <span class="tag">Time</span><span>{{ $nextTime }}</span>
                                            <span>·</span>
                                            <span>{{ $nextDate }}</span>
                                        </div>
                                    </div>

                                    <div class="metric-callout mt-4"
                                        style="background:linear-gradient(90deg,color-mix(in oklab,var(--next-accent) 30%,transparent),transparent);">
                                        <div class="metric-label">ORDER</div>
                                        <div class="metric-value display-6 number">{{ number_format($nextOrder) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- NEXT PRODUCTION LIST (horizontal) --}}
                    <div class="mt-4 np-section">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="m-0 text-secondary">Next Production list</h5>
                        </div>

                        <div class="tile-grid np-scroll">
                            @forelse($nextList as $row)
                                @php
                                    $bk = $row['back_no'] ?? '—';
                                    $dock = $row['dock'] ?? '—';
                                    $ord = (int) ($row['order_qty'] ?? 0);
                                @endphp
                                <div class="tile-square radius-4">
                                    <div class="bk number js-backno">{{ $bk }}</div>
                                    <div class="meta-row mt-1">
                                        <span class="tag">Dock</span><span>{{ $dock }}</span>
                                    </div>
                                    <div></div> {{-- spacer biar pill nempel bawah --}}
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
            @endforeach
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="{{ asset('assets/js/planning/board.js') }}"></script>

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

        // Theme toggle (opsional, kalau ada tombol #themeToggle)
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

        // Alias Back No (sync dengan mapping di halaman tabel)
        (function backnoAliasBoard() {
            const LS_KEY = 'backnoRenameMap';
            let map = {};
            try {
                map = JSON.parse(localStorage.getItem(LS_KEY) || '{}')
            } catch {}
            const fallback = {
                'D403': 'CI18',
                'D111': 'CI12',
                'D500': 'CI19'
            };
            const aliasMap = Object.assign({}, fallback, map);
            document.querySelectorAll('.js-backno').forEach(el => {
                const raw = (el.textContent || '').trim().toUpperCase();
                const alias = aliasMap[raw];
                if (alias) el.textContent = alias;
            });
        })();

        // Horizontal scroll (trackpad/wheel vertikal → geser kanan/kiri)
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.np-scroll').forEach(sc => {
                sc.addEventListener('wheel', (e) => {
                    if (Math.abs(e.deltaX) >= Math.abs(e.deltaY))
                        return; // user memang scroll horizontal
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
</body>

</html>
