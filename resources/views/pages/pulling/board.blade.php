<!DOCTYPE html>
<html lang="en" data-theme="dark" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <title>Current Production Board</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    {{-- pakai style lama + add-on board --}}
    <link rel="stylesheet" href="{{ asset('assets/css/planning/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/planning/board.css') }}">
</head>

@php
    // ---- contoh binding data (aman kalau null)
    $today = \Carbon\Carbon::parse($selectedDate ?? now());
    $nowStr = $today->format('l, j F Y');

    // data current production
    $cur = $current ?? []; // ['back_no','customer','dock','order_qty','dp','sc','start','progress_note']
    $curBack = $cur['back_no'] ?? '—';
    $curCust = $cur['customer'] ?? '—';
    $curDock = $cur['dock'] ?? '—';
    $curOrder = (int) ($cur['order_qty'] ?? 0);
    $curDP = (int) ($cur['dp'] ?? 0);
    $curSC = (int) ($cur['sc'] ?? 0);
    $curDone = max(0, $curDP + $curSC);
    $curPct = $curOrder ? min(100, round(($curDP / $curOrder) * 100)) : 0; // konsisten dengan halaman lama (DP/Order)
    $curStart = $cur['start'] ?? '--';
    $curNote = $cur['progress_note'] ?? 'Back no detail information';

    // ringkas progress shift
    $prog = $progress ?? []; // ['label'=>'Morning','order'=>1234,'actual'=>1110,'status'=>'S1']
    $progLabel = $prog['label'] ?? 'Progress';
    $progOrder = (int) ($prog['order'] ?? 0);
    $progActual = (int) ($prog['actual'] ?? 0);
    $progPct = $progOrder ? min(100, round(($progActual / $progOrder) * 100)) : 0;
    $progStatus = $prog['status'] ?? 'Normal';
    $warnChipCls = in_array($progStatus, ['NS', 'LS1', 'LS3']) ? 'bg-warning-subtle' : 'bg-success-subtle';

    // next single highlight
    $next = $nextHighlight ?? []; // ['back_no','customer','dock','order_qty','delivery_time','delivery_date']
    $nextBack = $next['back_no'] ?? '—';
    $nextCust = $next['customer'] ?? '—';
    $nextDock = $next['dock'] ?? '—';
    $nextOrder = (int) ($next['order_qty'] ?? 0);
    $nextTime = $next['delivery_time'] ?? '--';
    $nextDate = $next['delivery_date'] ?? '--';

    // list berikutnya
    $nextList = $nextList ?? []; // array of ['back_no','customer','dock','order_qty']
@endphp

<body>
    <div class="container py-4 board-container">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold m-0">Current Production</h2>
            <div class="d-flex align-items-center gap-2">
                <span class="badge date-pill">
                    <span id="boardDate">{{ $nowStr }}</span>
                </span>
                <a id="themeToggle" class="btn btn-outline-secondary btn-sm"><span>Light</span></a>
            </div>
        </div>

        {{-- 3-COLUMN LAYOUT --}}
        <div class="row g-4">

            {{-- LEFT: PROGRESS --}}
            <div class="col-12 col-lg-3">
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

            {{-- CENTER: CURRENT BACK NUMBER --}}
            <div class="col-12 col-lg-6">
                <div class="card tile radius-4 h-100">
                    <div class="card-body">
                        <div class="current-block">
                            <div class="current-title">Back Number</div>
                            <div class="current-value display-5 fw-bold number">{{ $curBack }}</div>
                            <div class="meta-row">
                                <span class="tag">Customer</span><span>{{ $curCust }}</span>
                                <span>•</span>
                                <span class="tag">Dock</span><span>{{ $curDock }}</span>
                                <span>•</span>
                                <span class="tag">Start</span><span>{{ $curStart }}</span>
                            </div>

                            <div class="row g-3 align-items-end my-3">
                                <div class="col-6">
                                    <div class="stat-box">
                                        <div class="label">Order</div>
                                        <div class="value number">{{ number_format($curOrder) }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-box">
                                        <div class="label">Completed (DP+SC)</div>
                                        <div class="value number">{{ number_format($curDone) }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- progress bar (DP/Order) selaras halaman utama --}}
                            <div class="qty-progress big" title="DP {{ $curDP }} / {{ $curOrder }}">
                                <div class="bar"><i style="width: {{ $curPct }}%"></i></div>
                                <span class="val number">{{ $curPct }}%</span>
                            </div>

                            <div class="note-tile mt-3">{{ $curNote }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: NEXT PROD HIGHLIGHT --}}
            <div class="col-12 col-lg-3">
                <div class="card tile radius-4 h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <strong>Next Prod</strong>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="next-highlight">
                            <div class="next-title">Back Number</div>
                            <div class="next-value display-6 fw-bold number">{{ $nextBack }}</div>
                            <div class="small text-secondary">{{ $nextCust }}</div>
                            <div class="meta-row mt-2">
                                <span class="tag">Dock</span><span>{{ $nextDock }}</span>
                                <span>•</span>
                                <span class="tag">Time</span><span>{{ $nextTime }}</span>
                                <span>·</span>
                                <span>{{ $nextDate }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="label mb-1">Order</div>
                            <div class="display-6 number">{{ number_format($nextOrder) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LIST: NEXT PRODUCTION --}}
        <div class="mt-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="m-0 text-secondary">Next Production list</h5>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="/pulling">Go to Table</a>
                    <button id="btn-download-excel" class="btn btn-outline-success btn-sm">Export</button>
                </div>
            </div>

            <div class="tile-grid">
                @forelse($nextList as $row)
                    @php
                        $bk = $row['back_no'] ?? '—';
                        $cust = $row['customer'] ?? '—';
                        $dock = $row['dock'] ?? '—';
                        $ord = (int) ($row['order_qty'] ?? 0);
                    @endphp
                    <div class="tile-square radius-4">
                        <div class="bk number">{{ $bk }}</div>
                        <div class="small text-secondary">{{ $cust }}</div>
                        <div class="meta-row">
                            <span class="tag">Dock</span><span>{{ $dock }}</span>
                        </div>
                        <div class="ord number">{{ number_format($ord) }}</div>
                    </div>
                @empty
                    <div class="text-muted">Tidak ada data berikutnya.</div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="{{ asset('assets/js/planning/board.js') }}"></script>
</body>

</html>
