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
            /* CURRENT = biru */
            --next-accent: #10b981;
            /* NEXT    = hijau */
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
            padding-inline: clamp(8px, 1.2vw, 16px);
        }

        @media (min-width:1400px) {
            .row.g-4 {
                --bs-gutter-x: 1.25rem;
            }
        }

        .card-current,
        .card-next {
            min-height: 640px;
            border: 1px solid var(--tile-border);
            border-radius: 18px;
        }

        .card-current {
            box-shadow: 0 0 0 2px color-mix(in oklab, var(--curr-accent) 18%, transparent) inset;
        }

        .card-next {
            box-shadow: 0 0 0 2px color-mix(in oklab, var(--next-accent) 18%, transparent) inset;
        }

        .card-current .card-header {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 20%, transparent), transparent);
        }

        .card-next .card-header {
            background: linear-gradient(90deg, color-mix(in oklab, var(--next-accent) 20%, transparent), transparent);
        }

        .current-value {
            font-size: clamp(3.2rem, 6.2vw, 6.5rem);
            line-height: 1.1;
        }

        .next-value {
            font-size: clamp(2.6rem, 5vw, 5.2rem);
            line-height: 1.15;
        }

        .current-title,
        .next-title {
            letter-spacing: .06em;
            opacity: .85;
        }

        .metric-callout {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.25rem;
            border-radius: 16px;
            border: 1px solid var(--tile-border);
            color: var(--text-strong);
        }

        .metric-callout .metric-label {
            font-weight: 600;
            letter-spacing: .02em;
            color: var(--muted);
        }

        .metric-callout .metric-value {
            font-weight: 800;
        }

        .metric-order {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 30%, transparent), transparent);
        }

        .metric-completed {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 22%, transparent), transparent);
        }

        .metric-balance {
            background: linear-gradient(90deg, color-mix(in oklab, var(--curr-accent) 14%, transparent), transparent);
        }

        .qty-progress.big .bar {
            height: 14px;
        }

        .qty-progress.big .val {
            font-weight: 800;
        }

        .time-pill {
            font-variant-numeric: tabular-nums;
        }

        /* Horizontal list */
        .next-row-wrap {
            overflow-x: auto;
        }

        .tile-grid {
            display: flex;
            gap: 12px;
            padding: 2px 2px 8px;
        }

        .tile-square {
            min-width: 260px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
            box-sizing: border-box;
            background: linear-gradient(90deg,
                    color-mix(in oklab, var(--next-accent) 30%, transparent),
                    color-mix(in oklab, var(--next-accent) 10%, transparent));
            border: 1px solid color-mix(in oklab, var(--next-accent) 25%, var(--tile-border));
        }
    </style>
</head>

@php
    $today = \Carbon\Carbon::parse($selectedDate ?? now());
    $nowStr = $today->format('l, j F Y');

    // Expect $boards['AS003'] & $boards['AS004'] (lihat struktur di atas).
    $boards = $boards ?? [];

    // Helper utk badge status
    $statusClass = function ($s) {
        return in_array($s, ['NS', 'LS1', 'LS3']) ? 'bg-warning-subtle' : 'bg-success-subtle';
    };

    // Pastikan dua key ada (biar aman kalau salah satu kosong)
    foreach (['AS003', 'AS004'] as $__k) {
        if (!isset($boards[$__k])) {
            $boards[$__k] = [];
        }
    }
@endphp

<body>
    <div class="container-fluid px-2 px-lg-3 py-4 board-container">

        <!-- HEADER: Running time (kiri) & date (kanan) -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge date-pill time-pill"><span id="rt-hms">00:00:00</span></span>
            <span class="badge date-pill"><span id="boardDate">{{ $nowStr }}</span></span>
        </div>

        <!-- TABS -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-AS003" data-bs-toggle="tab" data-bs-target="#pane-AS003"
                    type="button" role="tab">
                    AS003
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-AS004" data-bs-toggle="tab" data-bs-target="#pane-AS004" type="button"
                    role="tab">
                    AS004
                </button>
            </li>
        </ul>

        <div class="tab-content">
            @foreach (['AS003', 'AS004'] as $line)
                @php
                    $b = $boards[$line] ?? [];
                    $cur = $b['current'] ?? [];
                    $prog = $b['progress'] ?? [];
                    $nxh = $b['nextHighlight'] ?? [];
                    $list = $b['nextList'] ?? [];

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

                    $progOrder = (int) ($prog['order'] ?? 0);
                    $progActual = (int) ($prog['actual'] ?? 0);
                    $progPct = $progOrder ? min(100, round(($progActual / $progOrder) * 100)) : 0;
                    $progStatus = $prog['status'] ?? 'Normal';

                    $nextBack = $nxh['back_no'] ?? '—';
                    $nextCust = $nxh['customer'] ?? '—';
                    $nextDock = $nxh['dock'] ?? '—';
                    $nextOrder = (int) ($nxh['order_qty'] ?? 0);
                    $nextTime = $nxh['delivery_time'] ?? '--';
                    $nextDate = $nxh['delivery_date'] ?? '--';
                @endphp

                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $line }}"
                    role="tabpanel" aria-labelledby="tab-{{ $line }}" data-board-line="{{ $line }}">
                    <div class="row g-4">
                        <!-- LEFT: PROGRESS -->
                        <div class="col-12 col-xl-3 col-xxl-2">
                            <div class="card tile radius-4 h-100">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Progress</strong>
                                    <span
                                        class="ms-auto badge {{ $statusClass($progStatus) }}">{{ $progStatus }}</span>
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

                        <!-- CENTER: CURRENT -->
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

                                        <div class="metric-callout metric-order mt-3">
                                            <div class="metric-label">ORDER</div>
                                            <div class="metric-value number">{{ number_format($curOrder) }}</div>
                                        </div>
                                        <div class="metric-callout metric-completed mt-3">
                                            <div class="metric-label">COMPLETED</div>
                                            <div class="metric-value number">{{ number_format($curDone) }}</div>
                                        </div>
                                        <div class="metric-callout metric-balance mt-3">
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

                        <!-- RIGHT: NEXT -->
                        <div class="col-12 col-xl-3 col-xxl-2">
                            <div class="card tile radius-4 h-100 card-next">
                                <div class="card-header d-flex align-items-center gap-2">
                                    <strong>Next Production</strong>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="next-title">BACK NUMBER</div>
                                        <div class="next-value fw-bold number js-backno"
                                            id="nextBack-{{ $line }}">{{ $nextBack }}</div>
                                        <div class="small text-secondary" id="nextCust-{{ $line }}">
                                            {{ $nextCust }}</div>
                                        <div class="meta-row mt-2">
                                            <span class="tag">Dock</span><span
                                                id="nextDock-{{ $line }}">{{ $nextDock }}</span>
                                            <span>•</span>
                                            <span class="tag">Time</span><span
                                                id="nextTime-{{ $line }}">{{ $nextTime }}</span>
                                            <span>·</span>
                                            <span id="nextDate-{{ $line }}">{{ $nextDate }}</span>
                                        </div>
                                    </div>

                                    <div class="metric-callout mt-4"
                                        style="background:linear-gradient(90deg,
                         color-mix(in oklab,var(--next-accent) 30%,transparent),transparent);">
                                        <div class="metric-label">ORDER</div>
                                        <div class="metric-value display-6 number"
                                            id="nextOrderVal-{{ $line }}">{{ number_format($nextOrder) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LIST: NEXT PRODUCTION (horizontal) -->
                    <div class="mt-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="m-0 text-secondary">Next Production list — {{ $line }}</h5>
                        </div>

                        <div class="next-row-wrap np-scroll">
                            <div class="tile-grid" id="npRow-{{ $line }}">
                                @forelse($list as $row)
                                    @php
                                        $bk = $row['back_no'] ?? '—';
                                        $cust = $row['customer'] ?? '—';
                                        $dock = $row['dock'] ?? '—';
                                        $ord = (int) ($row['order_qty'] ?? 0);
                                        $dt = $row['delivery_time'] ?? ''; // "HH:mm"
                                        $dd = $row['delivery_date'] ?? ''; // "M/D"
                                    @endphp
                                    <div class="tile-square radius-4" data-backno="{{ $bk }}"
                                        data-customer="{{ $cust }}" data-dock="{{ $dock }}"
                                        data-order="{{ $ord }}" data-delivery-time="{{ $dt }}"
                                        data-delivery-date="{{ $dd }}" data-seq="{{ $loop->iteration }}">
                                        <div class="bk number js-backno">{{ $bk }}</div>

                                        <div class="meta-row mt-1">
                                            <span class="tag">Dock</span><span>{{ $dock }}</span>
                                        </div>

                                        <div></div> <!-- spacer -->

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
    <script defer src="{{ asset('assets/js/planning/board.js') }}"></script>

    <script>
        /* Theme toggle (aman kalau tombol tak ada) */
        (() => {
            const html = document.documentElement;
            const btn = document.getElementById('themeToggle');

            function apply(theme) {
                html.setAttribute('data-theme', theme);
                html.setAttribute('data-bs-theme', theme);
                localStorage.setItem('board-theme', theme);
                if (btn) btn.querySelector('span').textContent = theme === 'dark' ? 'Dark' : 'Light';
            }
            const saved = localStorage.getItem('board-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            apply(saved || (prefersDark ? 'dark' : 'light'));
            btn?.addEventListener('click', () => {
                const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                apply(next);
            });
        })();

        /* Running time badge */
        (function clock() {
            const el = document.getElementById('rt-hms');
            const pad = n => String(n).padStart(2, '0');

            function tick() {
                const now = new Date();
                el && (el.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`);
            }
            tick();
            setInterval(tick, 1000);
        })();

        /* Alias Back No */
        (function aliasBackNo() {
            const LS_KEY = 'backnoRenameMap';
            let map = {};
            try {
                map = JSON.parse(localStorage.getItem(LS_KEY) || '{}');
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

            window.__aliasBackNo = (txt) => {
                const raw = String(txt || '').trim().toUpperCase();
                return aliasMap[raw] || txt;
            };
        })();

        /* Horizontal wheel scroll untuk semua list */
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.np-scroll').forEach(sc => {
                sc.addEventListener('wheel', (e) => {
                    if (Math.abs(e.deltaX) >= Math.abs(e.deltaY)) return;
                    const goingRight = e.deltaY > 0;
                    const goingLeft = e.deltaY < 0;
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

        /* Urut & sinkron per TAB (AS003/AS004) */
        (function boardPerLine() {
            const pad = n => String(n).padStart(2, '0');

            function timeToMinutes(txt) {
                const m = String(txt || '').trim().match(/^(\d{1,2})\s*:\s*(\d{2})$/);
                if (!m) return null;
                return (+m[1]) * 60 + (+m[2]);
            }

            function mdToISO(md, refISO) {
                const m = String(md || '').trim().match(/^(\d{1,2})\s*\/\s*(\d{1,2})$/);
                if (!m) return '';
                const y = (refISO || new Date().toISOString().slice(0, 10)).slice(0, 4);
                return `${y}-${pad(+m[1])}-${pad(+m[2])}`;
            }

            function key(el, refISO) {
                const iso = mdToISO(el.dataset.deliveryDate || '', refISO) || '9999-12-31';
                const mins = timeToMinutes(el.dataset.deliveryTime || '');
                const seq = +(el.dataset.seq || 0);
                return {
                    iso,
                    mins: (mins == null ? 1e9 : mins),
                    seq
                };
            }

            function applyNextFromTile(line, tile) {
                const back = tile.dataset.backno || tile.querySelector('.bk')?.textContent?.trim() || '—';
                const cust = tile.dataset.customer || '—';
                const dock = tile.dataset.dock || '—';
                const time = tile.dataset.deliveryTime || '--';
                const date = tile.dataset.deliveryDate || '--';
                const order = parseInt(tile.dataset.order || '0', 10) || 0;

                const aliasBack = window.__aliasBackNo ? window.__aliasBackNo(back) : back;
                const set = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val;
                };

                set(`nextBack-${line}`, aliasBack);
                set(`nextCust-${line}`, cust);
                set(`nextDock-${line}`, dock);
                set(`nextTime-${line}`, time);
                set(`nextDate-${line}`, date);
                const ov = document.getElementById(`nextOrderVal-${line}`);
                if (ov) ov.textContent = order.toLocaleString('id-ID');
            }

            window.orderBoardLikePlan = function(line) {
                const row = document.getElementById(`npRow-${line}`);
                if (!row) return;

                const tiles = [...row.querySelectorAll('.tile-square')];
                const refISO = new Date().toISOString().slice(0, 10);

                tiles.sort((a, b) => {
                    const A = key(a, refISO),
                        B = key(b, refISO);
                    if (A.iso !== B.iso) return A.iso < B.iso ? -1 : 1;
                    if (A.mins !== B.mins) return A.mins - B.mins;
                    return A.seq - B.seq;
                }).forEach(t => row.appendChild(t));

                const first = tiles.find(t => t.offsetParent !== null);
                if (first) applyNextFromTile(line, first);
            };

            // Init untuk semua pane yang ada
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-board-line]').forEach(pane => {
                    const line = pane.getAttribute('data-board-line');
                    window.orderBoardLikePlan(line);
                });
            });

            // Saat tab diganti, re-apply sorting + sinkron card untuk tab aktif
            document.addEventListener('shown.bs.tab', (e) => {
                const target = document.querySelector(e.target.getAttribute('data-bs-target'));
                const line = target?.getAttribute('data-board-line');
                if (line) window.orderBoardLikePlan(line);
            });
        })();
    </script>
</body>

</html>
