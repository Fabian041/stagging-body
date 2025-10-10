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
            /* full-bleed */
            padding-inline: clamp(8px, 1.2vw, 16px);
            /* tepi dinamis: 8–16px */
        }

        @media (min-width: 1400px) {
            .row.g-4 {
                --bs-gutter-x: 1.25rem;
            }

            /* default ~1.5rem, ini sedikit lebih rapat */
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

        /* ===== Callout block: Order / Completed / Balance ===== */
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
            color: var(--muted)
        }

        .metric-callout .metric-value {
            font-weight: 800
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
            height: 14px
        }

        .qty-progress.big .val {
            font-weight: 800
        }

        /* Badge jam biar konsisten dengan date-pill */
        .time-pill {
            font-variant-numeric: tabular-nums
        }
    </style>
</head>

@php
    // ===== data =====
    $today = \Carbon\Carbon::parse($selectedDate ?? now());
    $nowStr = $today->format('l, j F Y');

    $cur = $current ?? [];
    $curBack = $cur['back_no'] ?? '—';
    $curCust = $cur['customer'] ?? '—';
    $curDock = $cur['dock'] ?? '—';
    $curOrder = (int) ($cur['order_qty'] ?? 0);
    $curDP = (int) ($cur['dp'] ?? 0);
    $curSC = (int) ($cur['sc'] ?? 0);
    $curDone = max(0, $curDP + $curSC);
    $curPct = $curOrder ? min(100, round(($curDP / $curOrder) * 100)) : 0; // DP/Order
    $curStart = $cur['start'] ?? '--';
    $curBalance = max(0, $curOrder - $curDone);

    $prog = $progress ?? [];
    $progOrder = (int) ($prog['order'] ?? 0);
    $progActual = (int) ($prog['actual'] ?? 0);
    $progPct = $progOrder ? min(100, round(($progActual / $progOrder) * 100)) : 0;
    $progStatus = $prog['status'] ?? 'Normal';
    $warnChipCls = in_array($progStatus, ['NS', 'LS1', 'LS3']) ? 'bg-warning-subtle' : 'bg-success-subtle';

    $next = $nextHighlight ?? [];
    $nextBack = $next['back_no'] ?? '—';
    $nextCust = $next['customer'] ?? '—';
    $nextDock = $next['dock'] ?? '—';
    $nextOrder = (int) ($next['order_qty'] ?? 0);
    $nextTime = $next['delivery_time'] ?? '--';
    $nextDate = $next['delivery_date'] ?? '--';

    $nextList = $nextList ?? [];
@endphp

<body>
    <div class="container-fluid px-2 px-lg-3 py-4 board-container">

        {{-- HEADER: Running time (kiri) & date (kanan) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge date-pill time-pill">
                <span id="rt-hms">00:00:00</span>
            </span>
            <span class="badge date-pill"><span id="boardDate">{{ $nowStr }}</span></span>
        </div>

        <div class="row g-4">
            {{-- LEFT: PROGRESS --}}
            <div class="col-12 col-xl-3 col-xxl-2">
                <div class="card tile radius-4 h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <strong>Progress</strong>
                        <span class="ms-auto badge {{ $warnChipCls }}">{{ $progStatus }}</span>
                    </div>
                    <div class="card-body">
                        <div class="progress-readout">
                            <div class="big-number number">{{ number_format($progActual) }}</div>
                            <div class="sub-label">Completed</div>
                            <div class="small text-secondary">of {{ number_format($progOrder) }}</div>
                        </div>
                        <div class="qty-progress mt-3" title="Actual {{ $progActual }} / {{ $progOrder }}">
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

            {{-- CENTER: CURRENT (besar + biru) --}}
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

                            <!-- ORDER -->
                            <div class="metric-callout metric-order mt-3" title="Order">
                                <div class="metric-label">ORDER</div>
                                <div class="metric-value number">{{ number_format($curOrder) }}</div>
                            </div>

                            <!-- COMPLETED -->
                            <div class="metric-callout metric-completed mt-3" title="Completed">
                                <div class="metric-label">COMPLETED</div>
                                <div class="metric-value number">{{ number_format($curDone) }}</div>
                            </div>

                            <!-- BALANCE = ORDER - COMPLETED -->
                            <div class="metric-callout metric-balance mt-3" title="Balance">
                                <div class="metric-label">BALANCE</div>
                                <div class="metric-value number {{ $curBalance <= 0 ? 'text-success' : '' }}">
                                    {{ $curBalance <= 0 ? 'COMPLETED' : number_format($curBalance) }}
                                </div>
                            </div>

                            <!-- Progress DP/Order -->
                            <div class="qty-progress big mt-3" title="DP {{ $curDP }} / {{ $curOrder }}">
                                <div class="bar"><i style="width: {{ $curPct }}%"></i></div>
                                <span class="val number">{{ $curPct }}%</span>
                            </div>

                            <!-- Note tile dihapus sesuai request -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: NEXT (besar + hijau) --}}
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
                            style="background:linear-gradient(90deg,
                   color-mix(in oklab,var(--next-accent) 30%,transparent),transparent);">
                            <div class="metric-label">ORDER</div>
                            <div class="metric-value display-6 number">{{ number_format($nextOrder) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LIST: NEXT PRODUCTION (horizontal) --}}
        <div class="mt-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="m-0 text-secondary">Next Production list</h5>
            </div>

            <div class="next-row-wrap">
                <div class="tile-grid" id="npRow">
                    @forelse($nextList as $row)
                        @php
                            $bk = $row['back_no'] ?? '—';
                            $cust = $row['customer'] ?? '—';
                            $dock = $row['dock'] ?? '—';
                            $ord = (int) ($row['order_qty'] ?? 0);
                        @endphp
                        <div class="tile-square radius-4">
                            <div class="bk number js-backno">{{ $bk }}</div>

                            <div class="meta-row mt-1">
                                <span class="tag">Dock</span><span>{{ $dock }}</span>
                            </div>

                            <div></div> {{-- spacer biar ORDER nempel bawah --}}

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="{{ asset('assets/js/planning/board.js') }}"></script>

    <script>
        // Running time badge (HH:MM:SS)
        (function clock() {
            const el = document.getElementById('rt-hms');

            function pad(n) {
                return String(n).padStart(2, '0');
            }

            function tick() {
                const now = new Date();
                const t = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                if (el) el.textContent = t;
            }
            tick();
            setInterval(tick, 1000);
        })();

        // Alias Back No (mengikuti mapping dari halaman tabel)
        document.addEventListener('DOMContentLoaded', function() {
            const map = (() => {
                try {
                    return JSON.parse(localStorage.getItem('backnoRenameMap') || '{}')
                } catch {
                    return {}
                }
            })();
            const fallback = {
                'D403': 'CI18',
                'D111': 'CI12',
                'D500': 'CI19'
            };
            const aliasMap = Object.assign({}, fallback, map);
            document.querySelectorAll('.js-backno').forEach(el => {
                const key = (el.textContent || '').trim().toUpperCase();
                if (aliasMap[key]) el.textContent = aliasMap[key];
            });
        });
    </script>
</body>

</html>
