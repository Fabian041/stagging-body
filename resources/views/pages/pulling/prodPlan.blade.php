<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <title>Planning Production</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/planning/style.css') }}">
</head>

<body>
    <div class="container py-4">

        <!-- Header / Filters -->
        {{-- <div class="page-head p-3 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-industry text-primary"></i>
                    <strong class="h5 m-0">Production Date</strong>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light border">
                        {{ Carbon\Carbon::parse($selectedDate ?? now())->format('D, M j Y') }}
                    </span>

                    <!-- Theme toggle -->
                   
                </div>
            </div>

            <form method="GET" action="{{ route('dashboard.prodPlan') }}" class="row g-2 align-items-center mt-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border">
                            <i class="far fa-calendar"></i>
                        </span>
                        <input type="date" class="form-control border" name="date"
                            value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                            style="font-weight:600">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel-fill me-1"></i> Filter
                        </button>
                        @if (request()->has('date'))
                            <a href="{{ route('dashboard.prodPlan') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                        @endif
                        <button type="button" class="btn btn-outline-secondary" onclick="navigateDate(-1)">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        @php
                            $selected = $selectedDate ?? now()->format('Y-m-d');
                            $isToday = $selected === now()->format('Y-m-d');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary {{ $isToday ? 'disabled' : '' }}"
                            onclick="navigateDate(1)" {{ $isToday ? 'disabled' : '' }}>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="gotoToday()">Today</button>
                        <button type="submit" name="force_refresh" value="1" class="btn btn-warning">
                            <i class="fas fa-sync-alt me-1"></i> Re-fetch
                        </button>
                    </div>
                </div>
            </form>
        </div> --}}

        @if (isset($message))
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100">
                <div class="toast text-bg-{{ $messageType ?? 'info' }} border-0 shadow" role="alert"
                    aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                    <div class="d-flex align-items-center">
                        <div class="toast-body">
                            {{ $message }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold m-0">Production Pulling Plan –
                {{ Carbon\Carbon::parse($selectedDate ?? now())->format('l, j F Y') }}
            </h2>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft">
                    <i class="far fa-clock me-1"></i> Last Update:
                    {{ \Carbon\Carbon::parse($lastUpdate ?? now())->format('H:i:s') }}
                </span>
                <a class="btn btn-outline-primary" href="/pulling/settings">
                    <i class="fas fa-cog me-1"></i> Settings
                </a>
                <a type="button" id="themeToggle" class="btn btn-outline-secondary theme-toggle">
                    <i class="far fa-moon me-1"></i><span>Dark</span>
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="lineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="line3-tab" data-bs-toggle="tab" data-bs-target="#line3"
                    type="button" role="tab">
                    AS003
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line4-tab" data-bs-toggle="tab" data-bs-target="#line4" type="button"
                    role="tab">
                    AS004
                </button>
            </li>
        </ul>

        <div class="tab-content" id="lineTabsContent">
            <!-- ================== AS003 ================== -->
            <div class="tab-pane fade show active" id="line3" role="tabpanel" aria-labelledby="line3-tab">
                <div data-toggle-table="AS003">
                    @php
                        $as003MorningQty = $grouped['AS003']['morning_shift_qty'] ?? 0;
                        $as003NightQty = $grouped['AS003']['night_shift_qty'] ?? 0;
                        $as003TotalQty = $grouped['AS003']['total_qty'] ?? 0;

                        $as003MorningActual = $grouped['AS003']['morning_shift_actual'] ?? 0;
                        $as003NightActual = $grouped['AS003']['night_shift_actual'] ?? 0;
                        $as003TotalActual = $grouped['AS003']['total_actual'] ?? 0;

                        $as003MorningPct = $as003MorningQty
                            ? min(100, round(($as003MorningActual / $as003MorningQty) * 100))
                            : 0;
                        $as003NightPct = $as003NightQty
                            ? min(100, round(($as003NightActual / $as003NightQty) * 100))
                            : 0;
                        $as003TotalPct = $as003TotalQty
                            ? min(100, round(($as003TotalActual / $as003TotalQty) * 100))
                            : 0;

                        $as003MorningStatus = 'Normal Shift';
                        if ($as003MorningQty > 900) {
                            $as003MorningStatus = 'Advance to LS1';
                        } elseif ($as003MorningQty > 750) {
                            $as003MorningStatus = 'Advance to NS';
                        }

                        $as003NightStatus = 'Normal Shift';
                        if ($as003NightQty > 630) {
                            $as003NightStatus = 'Advance to LS3';
                        }
                    @endphp

                    <!-- AS003 CARD -->
                    <div class="card mb-3 radius-4" data-shift-card="AS003">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3">
                            <!-- MORNING -->
                            <div class="strip-stat" data-line="AS003" data-shift="morning">
                                <div class="title">Morning Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-primary"> {{-- (text-success untuk Night) --}}
                                        <span data-role="shift-order">{{ $as003MorningQty ?? 0 }}</span>
                                    </div>
                                    {{-- placeholder; akan diisi JS --}}
                                    <span class="chip border fw-bolder d-none" data-role="shift-status"></span>
                                    <small class="text-muted" data-role="shift-note"></small>
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as003MorningActual }} / {{ $as003MorningQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as003MorningPct }}%"></i></div>
                                        <span class="val number " data-role="shift-pct">{{ $as003MorningPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as003MorningActual }}</span></div>
                                </div>
                            </div>

                            <!-- NIGHT -->
                            <div class="strip-stat" data-line="AS003" data-shift="night">
                                <div class="title">Night Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-success">
                                        <span data-role="shift-order">{{ $as003NightQty }}</span>
                                    </div>
                                    @if ($as003NightStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-danger-subtle border text-dark fw-bolder">{{ $as003NightStatus }}</span>
                                    @endif
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as003NightActual }} / {{ $as003NightQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as003NightPct }}%"></i></div>
                                        <span class="val number " data-role="shift-pct">{{ $as003NightPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as003NightActual }}</span></div>
                                </div>
                            </div>

                            <!-- TOTAL -->
                            <div class="ms-auto strip-stat" data-line="AS003" data-shift="total">
                                <div class="title">Total</div>
                                <div class="value"><span data-role="shift-order">{{ $as003TotalQty }}</span></div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as003TotalActual }} / {{ $as003TotalQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as003TotalPct }}%"></i></div>
                                        <span class="val number " data-role="shift-pct">{{ $as003TotalPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as003TotalActual }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar: Presets & Columns -->
                    <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                        <button class="btn btn-outline-secondary btn-sm" data-pane-autoscroll="AS003">
                            <i class="fas fa-scroll me-1"></i> Auto Scroll: <span class="state">On</span>
                        </button>

                        <button class="btn btn-outline-info btn-sm" onclick="showSummary('AS003')">
                            <i class="fas fa-list-ol me-1"></i> Summary
                        </button>

                        <!-- Columns -->
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Columns
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 160px"
                                data-colpicker="AS003">
                                @foreach (['Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute', 'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time', 'Delivery Date', 'Balance Time'] as $i => $label)
                                    <li class="form-check form-check-sm d-flex align-items-center gap-2 mb-1">
                                        <input class="form-check-input column-check" type="checkbox"
                                            data-col="{{ $i }}" id="col_AS003_{{ $i }}">
                                        <label class="form-check-label small"
                                            for="col_AS003_{{ $i }}">{{ $label }}</label>
                                    </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item small" href="#"
                                        onclick="resetColumns('AS003');return false;">Reset columns</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="table-responsive auto-scroll">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Customer</th>
                                        <th rowspan="2">Dock</th>
                                        <th rowspan="2">Cycle</th>
                                        <th rowspan="2">Back No</th>
                                        <th rowspan="2">Order</th>
                                        <th colspan="2" class="text-center">Running Qty</th>
                                        <th rowspan="2">Cycle Time</th>
                                        <th colspan="4" class="text-center">Working Time</th>
                                        <th rowspan="2">Delivery Time</th>
                                        <th rowspan="2">Delivery Date</th>
                                        <th rowspan="2">Balance Time</th>
                                    </tr>
                                    <tr>
                                        <th>Direct Pulling</th>
                                        <th>Stock Chute</th>
                                        <th>Planning Start</th>
                                        <th>Actual Start</th>
                                        <th>Duration</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($grouped['AS003']['data'] ?? [] as $key => $rows)
                                        @php
                                            [$customer, $delivery] = explode('|', $key);
                                            $rowspan = $rows->count();
                                            $dock = $rows->first()->dock ?? '--';
                                        @endphp
                                        @foreach ($rows as $index => $item)
                                            @php
                                                $timeParts = explode(':', $item->balance_time ?? '00:00');
                                                $hours = (int) $timeParts[0];

                                                if (!function_exists('getQtyClass')) {
                                                    function getQtyClass($qty, $orderQty)
                                                    {
                                                        if ($qty >= $orderQty) {
                                                            return 'bg-success bg-opacity-75 fw-bold text-white';
                                                        } elseif ($qty > 0) {
                                                            return 'bg-warning bg-opacity-75 fw-bold';
                                                        }
                                                        return 'bg-light fw-semibold text-secondary';
                                                    }
                                                }

                                                $dp = (int) ($item->direct_pulling_qty ?: 0);
                                                $sc = (int) ($item->stock_chute_qty ?: 0);
                                                $ord = max(1, (int) $item->order_qty);
                                                $pct = min(100, round((($dp + $sc) / $ord) * 100));

                                                $startHour = null;
                                                $endHour = null;
                                                try {
                                                    $startHour = $item->working_start
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_start,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                try {
                                                    $endHour = $item->working_end
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_end,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                $isMorning =
                                                    ($startHour !== null && $startHour >= 6 && $startHour < 14) ||
                                                    ($endHour !== null && $endHour >= 6 && $endHour < 14);
                                                $isNight =
                                                    ($startHour !== null && ($startHour >= 22 || $startHour < 6)) ||
                                                    ($endHour !== null && ($endHour >= 22 || $endHour < 6));
                                                $rowShift = $isMorning ? 'morning' : ($isNight ? 'night' : 'other');
                                            @endphp
                                            <tr data-shift="{{ $rowShift }}">
                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Customer"><span
                                                            class="flip">{{ $customer }}</span></td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Dock"><span
                                                            class="flip">{{ $dock }}</span></td>
                                                @endif
                                                <td data-label="Cycle"><span
                                                        class="flip">{{ $item->cycle }}</span></td>
                                                <td data-label="Back No"><span
                                                        class="flip">{{ $item->back_no }}</span></td>
                                                <td data-label="Order"><span
                                                        class="flip">{{ $item->order_qty }}</span></td>

                                                <td class="{{ getQtyClass($item->direct_pulling_qty, $item->order_qty) }}"
                                                    data-label="Direct Pulling">
                                                    <div class="qty-progress"
                                                        title="DP {{ $dp }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($dp / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="direct-pulling"
                                                                data-item-id="{{ $item->id }}">{{ $dp }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="{{ getQtyClass($item->stock_chute_qty, $item->order_qty) }}"
                                                    data-label="Stock Chute">
                                                    <div class="qty-progress"
                                                        title="SC {{ $sc }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($sc / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="stock-chute"
                                                                data-item-id="{{ $item->id }}">{{ $sc }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td data-label="Cycle Time"><span
                                                        class="flip">{{ $item->prod_time }}</span></td>
                                                <td data-label="Planning Start"><span data-type="start"
                                                        data-item-id="{{ $item->id }}">{{ $item->working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Actual Start"><span data-type="actual_start"
                                                        data-item-id="{{ $item->id }}">{{ $item->actual_working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Duration"><span
                                                        class="flip text-warning">{{ $item->working_duration ?? '--' }}</span>
                                                </td>

                                                <!-- Progress total -->
                                                <td data-label="Progress" class="total-progress">
                                                    <div class="qty-progress"
                                                        title="DP+SC {{ $dp + $sc }} / {{ $ord }} ({{ $pct }}%)">
                                                        <div class="bar"><i
                                                                style="width: {{ $pct }}%"></i></div>
                                                        <span class="val">{{ $pct }}%</span>
                                                    </div>
                                                </td>

                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Time">
                                                        <span class="flip">{{ $delivery }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Date">
                                                        <span
                                                            class="flip">{{ $item->delivery_date ? Carbon\Carbon::parse($item->delivery_date)->format('m/d') : '--' }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Balance Time"
                                                        class="{{ $item->balance_time && $hours < 3 ? 'table-danger-subtle' : '' }}">
                                                        <span data-type="balance"
                                                            data-item-id="{{ $item->id }}">{{ $item->balance_time ?? '--' }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center py-4 text-muted">Belum ada plan
                                                untuk tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================== AS004 ================== -->
            <div class="tab-pane fade" id="line4" role="tabpanel" aria-labelledby="line4-tab">
                <div data-toggle-table="AS004">
                    @php
                        $as004MorningQty = $grouped['AS004']['morning_shift_qty'] ?? 0;
                        $as004NightQty = $grouped['AS004']['night_shift_qty'] ?? 0;
                        $as004TotalQty = $grouped['AS004']['total_qty'] ?? 0;

                        $as004MorningActual = $grouped['AS004']['morning_shift_actual'] ?? 0;
                        $as004NightActual = $grouped['AS004']['night_shift_actual'] ?? 0;
                        $as004TotalActual = $grouped['AS004']['total_actual'] ?? 0;

                        $as004MorningPct = $as004MorningQty
                            ? min(100, round(($as004MorningActual / $as004MorningQty) * 100))
                            : 0;
                        $as004NightPct = $as004NightQty
                            ? min(100, round(($as004NightActual / $as004NightQty) * 100))
                            : 0;
                        $as004TotalPct = $as004TotalQty
                            ? min(100, round(($as004TotalActual / $as004TotalQty) * 100))
                            : 0;

                        $as004MorningStatus = 'Normal Shift';
                        if ($as004MorningQty > 900) {
                            $as004MorningStatus = 'Advance to LS1';
                        } elseif ($as004MorningQty > 750) {
                            $as004MorningStatus = 'Advance to NS';
                        }

                        $as004NightStatus = 'Normal Shift';
                        if ($as004NightQty > 630) {
                            $as004NightStatus = 'Advance to LS3';
                        }
                    @endphp

                    <div class="card mb-3 radius-4" data-shift-card="AS004">
                        <div class="card-body d-flex flex-wrap align-items-end gap-3">
                            <!-- MORNING -->
                            <div class="strip-stat" data-line="AS004" data-shift="morning">
                                <div class="title">Day Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-primary"> {{-- (text-success untuk Night) --}}
                                        <span data-role="shift-order">{{ $as004MorningQty ?? 0 }}</span>
                                    </div>
                                    {{-- placeholder; akan diisi JS --}}
                                    <span class="chip border fw-bolder d-none" data-role="shift-status"></span>
                                    <small class="text-muted" data-role="shift-note"></small>
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as004MorningActual }} / {{ $as004MorningQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as004MorningPct }}%"></i></div>
                                        <span class="val number " data-role="shift-pct">{{ $as004MorningPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as004MorningActual }}</span></div>
                                </div>
                            </div>

                            <!-- NIGHT -->
                            <div class="strip-stat" data-line="AS004" data-shift="night">
                                <div class="title">Night Shift Order</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="value text-success">
                                        <span data-role="shift-order">{{ $as004NightQty }}</span>
                                    </div>
                                    @if ($as004NightStatus != 'Normal Shift')
                                        <span
                                            class="chip bg-danger-subtle border text-dark fw-bolder">{{ $as004NightStatus }}</span>
                                    @endif
                                </div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as004NightActual }} / {{ $as004NightQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as004NightPct }}%"></i></div>
                                        <span class="val number " data-role="shift-pct">{{ $as004NightPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as004NightActual }}</span></div>
                                </div>
                            </div>

                            <!-- TOTAL -->
                            <div class="ms-auto strip-stat" data-line="AS004" data-shift="total">
                                <div class="title">Total</div>
                                <div class="value"><span data-role="shift-order">{{ $as004TotalQty }}</span></div>
                                <div class="kpi-mini">
                                    <div class="qty-progress"
                                        title="Actual {{ $as004TotalActual }} / {{ $as004TotalQty }}">
                                        <div class="bar"><i data-role="shift-bar"
                                                style="width: {{ $as004TotalPct }}%"></i></div>
                                        <span class="val number " data-role="shift-pct">{{ $as004TotalPct }}%</span>
                                    </div>
                                    <div class="meta">Actual: <span class="fw-bold"
                                            data-role="shift-actual">{{ $as004TotalActual }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Toolbar: Presets & Columns -->
                    <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                        <button class="btn btn-outline-secondary btn-sm" data-pane-autoscroll="AS004">
                            <i class="fas fa-scroll me-1"></i> Auto Scroll: <span class="state">On</span>
                        </button>

                        <button class="btn btn-outline-info btn-sm" onclick="showSummary('AS004')">
                            <i class="fas fa-list-ol me-1"></i> Summary
                        </button>

                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Columns
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 160px"
                                data-colpicker="AS004">
                                @foreach (['Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute', 'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time', 'Delivery Date', 'Balance Time'] as $i => $label)
                                    <li class="form-check form-check-sm d-flex align-items-center gap-2 mb-1">
                                        <input class="form-check-input column-check" type="checkbox"
                                            data-col="{{ $i }}" id="col_AS004_{{ $i }}">
                                        <label class="form-check-label small"
                                            for="col_AS004_{{ $i }}">{{ $label }}</label>
                                    </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item small" href="#"
                                        onclick="resetColumns('AS004');return false;">Reset columns</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="table-responsive auto-scroll">
                            <table class="table table-hover table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Customer</th>
                                        <th rowspan="2">Dock</th>
                                        <th rowspan="2">Cycle</th>
                                        <th rowspan="2">Back No</th>
                                        <th rowspan="2">Order</th>
                                        <th colspan="2" class="text-center">Running Qty</th>
                                        <th rowspan="2">Cycle Time</th>
                                        <th colspan="4" class="text-center">Working Time</th>
                                        <th rowspan="2">Delivery Time</th>
                                        <th rowspan="2">Delivery Date</th>
                                        <th rowspan="2">Balance Time</th>
                                    </tr>
                                    <tr>
                                        <th>Direct Pulling</th>
                                        <th>Stock Chute</th>
                                        <th>Planning Start</th>
                                        <th>Actual Start</th>
                                        <th>Duration</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($grouped['AS004']['data'] ?? [] as $key => $rows)
                                        @php
                                            [$customer, $delivery] = explode('|', $key);
                                            $rowspan = $rows->count();
                                            $dock = $rows->first()->dock ?? '--';
                                        @endphp
                                        @foreach ($rows as $index => $item)
                                            @php
                                                $timeParts = explode(':', $item->balance_time ?? '00:00');
                                                $hours = (int) $timeParts[0];

                                                if (!function_exists('getQtyClass')) {
                                                    function getQtyClass($qty, $orderQty)
                                                    {
                                                        if ($qty >= $orderQty) {
                                                            return 'bg-success bg-opacity-75 fw-bold text-white';
                                                        } elseif ($qty > 0) {
                                                            return 'bg-warning bg-opacity-75 fw-bold';
                                                        }
                                                        return 'bg-light fw-semibold text-secondary';
                                                    }
                                                }

                                                $dp = (int) ($item->direct_pulling_qty ?: 0);
                                                $sc = (int) ($item->stock_chute_qty ?: 0);
                                                $ord = max(1, (int) $item->order_qty);
                                                $pct = min(100, round((($dp + $sc) / $ord) * 100));

                                                $startHour = null;
                                                $endHour = null;
                                                try {
                                                    $startHour = $item->working_start
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_start,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                try {
                                                    $endHour = $item->working_end
                                                        ? (int) \Carbon\Carbon::createFromFormat(
                                                            'H:i',
                                                            $item->working_end,
                                                        )->format('H')
                                                        : null;
                                                } catch (\Exception $e) {
                                                }
                                                $isMorning =
                                                    ($startHour !== null && $startHour >= 6 && $startHour < 14) ||
                                                    ($endHour !== null && $endHour >= 6 && $endHour < 14);
                                                $isNight =
                                                    ($startHour !== null && ($startHour >= 22 || $startHour < 6)) ||
                                                    ($endHour !== null && ($endHour >= 22 || $endHour < 6));
                                                $rowShift = $isMorning ? 'morning' : ($isNight ? 'night' : 'other');
                                            @endphp
                                            <tr data-shift="{{ $rowShift }}">
                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Customer"><span
                                                            class="flip">{{ $customer }}</span></td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Dock"><span
                                                            class="flip">{{ $dock }}</span></td>
                                                @endif
                                                <td data-label="Cycle"><span
                                                        class="flip">{{ $item->cycle }}</span></td>
                                                <td data-label="Back No"><span
                                                        class="flip">{{ $item->back_no }}</span></td>
                                                <td data-label="Order"><span
                                                        class="flip">{{ $item->order_qty }}</span></td>

                                                <td class="{{ getQtyClass($item->direct_pulling_qty, $item->order_qty) }}"
                                                    data-label="Direct Pulling">
                                                    <div class="qty-progress"
                                                        title="DP {{ $dp }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($dp / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="direct-pulling"
                                                                data-item-id="{{ $item->id }}">{{ $dp }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="{{ getQtyClass($item->stock_chute_qty, $item->order_qty) }}"
                                                    data-label="Stock Chute">
                                                    <div class="qty-progress"
                                                        title="SC {{ $sc }} / {{ $ord }}">
                                                        <div class="bar"><i
                                                                style="width: {{ min(100, round(($sc / $ord) * 100)) }}%"></i>
                                                        </div>
                                                        <span class="val">
                                                            <span class="flip" data-type="stock-chute"
                                                                data-item-id="{{ $item->id }}">{{ $sc }}</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td data-label="Cycle Time"><span
                                                        class="flip">{{ $item->prod_time }}</span></td>
                                                <td data-label="Planning Start"><span data-type="start"
                                                        data-item-id="{{ $item->id }}">{{ $item->working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Actual Start"><span data-type="actual_start"
                                                        data-item-id="{{ $item->id }}">{{ $item->actual_working_start ?? '--' }}</span>
                                                </td>
                                                <td data-label="Duration"><span
                                                        class="flip text-warning">{{ $item->working_duration ?? '--' }}</span>
                                                </td>

                                                <td data-label="Progress" class="total-progress">
                                                    <div class="qty-progress"
                                                        title="DP+SC {{ $dp + $sc }} / {{ $ord }} ({{ $pct }}%)">
                                                        <div class="bar"><i
                                                                style="width: {{ $pct }}%"></i></div>
                                                        <span class="val">{{ $pct }}%</span>
                                                    </div>
                                                </td>

                                                @if ($index === 0)
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Time">
                                                        <span class="flip">{{ $delivery }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Delivery Date">
                                                        <span
                                                            class="flip">{{ $item->delivery_date ? Carbon\Carbon::parse($item->delivery_date)->format('m/d') : '--' }}</span>
                                                    </td>
                                                    <td rowspan="{{ $rowspan }}" data-label="Balance Time"
                                                        class="{{ $item->balance_time && $hours < 3 ? 'table-danger-subtle' : '' }}">
                                                        <span data-type="balance"
                                                            data-item-id="{{ $item->id }}">{{ $item->balance_time ?? '--' }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center py-4 text-muted">Belum ada plan
                                                untuk tanggal ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /tab-content -->
    </div><!-- /container -->

    <!-- Order Summary Modal -->
    <!-- Summary Modal -->
    <div class="modal fade" id="summaryModal" tabindex="-1" aria-labelledby="summaryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="summaryModalLabel">
                        <i class="fas fa-chart-bar me-2"></i>
                        Order Summary by Back Number - <span id="modalLineTitle">AS003</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="totalBackNumbers">0</span>
                                <div class="label">Total Model</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="totalOrders">0</span>
                                <div class="label">Total Orders</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="avgOrderPerBack">0</span>
                                <div class="label">Avg per Back No</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="summary-stat">
                                <span class="number" id="completedOrders">0</span>
                                <div class="label">Complete Order</div>
                            </div>
                        </div>
                    </div>


                    <!-- Back Number List -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-secondary text-uppercase mb-3" style="letter-spacing:.5px;">
                            Order Details by Back Number
                        </h6>
                        <div id="backNumberList"><!-- populated by JS --></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="exportSummary()">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="{{ asset('assets/js/page/planning/script.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toast').forEach(function(el) {
                try {
                    new bootstrap.Toast(el).show();
                } catch (e) {}
            });

            // Helper umum untuk dipakai dari JS lain
            window.showToast = function({
                type = 'info',
                message = '',
                delay = 4000
            } = {}) {
                const wrap = document.querySelector('.toast-container') ??
                    (() => {
                        const d = document.createElement('div');
                        d.className = 'toast-container position-fixed top-0 end-0 p-3';
                        d.style.zIndex = 1100;
                        document.body.appendChild(d);
                        return d;
                    })();
                const el = document.createElement('div');
                el.className = `toast text-bg-${type} border-0 shadow`;
                el.setAttribute('role', 'alert');
                el.setAttribute('aria-live', 'assertive');
                el.setAttribute('aria-atomic', 'true');
                el.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
                wrap.appendChild(el);
                const t = new bootstrap.Toast(el, {
                    delay,
                    autohide: true
                });
                t.show();
                el.addEventListener('hidden.bs.toast', () => el.remove());
            };
        });
    </script>
</body>

</html>
