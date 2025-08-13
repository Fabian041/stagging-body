<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pulling Day Shift - 05-Jul-25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href={{ asset('assets/modules/fontawesome/css/all.min.css') }}>
    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: monospace;
        }

        h2 {
            color: #00ff99;
            text-shadow: 1px 1px 2px black;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .bg-orange {
            background-color: orange !important;
            color: black;
        }

        .highlight-rfid {
            background-color: #ffeeba !important;
            color: black;
        }

        .highlight-889t {
            background-color: #c3e6cb !important;
            color: #155724 !important;
        }

        .flip {
            display: inline-block;
            transition: all 0.3s ease;
            transform-style: preserve-3d;
            transform-origin: bottom center;
        }

        .animate-flip {
            animation: flipAnimation 0.6s ease;
        }

        @keyframes flipAnimation {
            0% {
                transform: rotateX(0deg);
                opacity: 1;
            }

            50% {
                transform: rotateX(90deg);
                opacity: 0;
            }

            51% {
                transform: rotateX(-90deg);
            }

            100% {
                transform: rotateX(0deg);
                opacity: 1;
            }
        }

        /* Continuous blinking highlight styles */
        @keyframes continuousBlink {

            0%,
            100% {
                background-color: var(--highlight-color);
            }

            50% {
                background-color: var(--base-bg);
                /* bukan transparent */
            }
        }

        .highlight-beep-direct {
            --highlight-color: #12341E;
            /* hijau tua */
            --base-bg: #1E2024;
            /* abu gelap dari background kamu */
            animation: continuousBlink 1s ease-in-out infinite;
        }

        .highlight-beep-stock {
            --highlight-color: #4D3A0A;
            /* coklat tua */
            --base-bg: #1E2024;
            animation: continuousBlink 1s ease-in-out infinite;
        }


        /* Make sure table cells inherit the highlight */
        .highlight-beep-direct td,
        .highlight-beep-stock td {
            background-color: inherit !important;
        }

        /* Status indicator styles */
        #sse-connection-status {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            transition: all 0.3s ease;
        }

        /* Table cell styles for quantities */
        .bg-success.bg-opacity-75 {
            background-color: rgba(40, 167, 69, 0.75) !important;
        }

        .bg-success.bg-opacity-25 {
            background-color: rgba(40, 167, 69, 0.25) !important;
        }

        .bg-warning.bg-opacity-75 {
            background-color: rgba(255, 193, 7, 0.75) !important;
        }

        .bg-warning.bg-opacity-25 {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }

        /* Tab styles */
        .nav-tabs .nav-link {
            color: #ccc;
            background-color: #333;
            border-color: #444;
        }

        .nav-tabs .nav-link.active {
            color: #222;
            background-color: white;
            border-color: #222;
        }

        /* Card styles */
        .card {
            background-color: #222;
            border-color: #333;
        }

        .card-header {
            border-bottom-color: #333;
        }

        .column-toggle-panel .panel-title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #555;
            color: #ffcc00;
        }

        .toggle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
        }

        .toggle-grid label {
            display: flex;
            align-items: center;
            padding: 6px 10px;
            background-color: #2a2d33;
            border: 1px solid #555;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .toggle-grid label:hover {
            background-color: #33373f;
        }

        .toggle-grid input[type="checkbox"] {
            accent-color: #ffcc00;
            /* warna toggle */
            margin-right: 8px;
            transform: scale(1.2);
        }

        .column-toggle-panel {
            background-color: #212529;
            border: 1px solid #444;
            border-radius: 2px;
            color: #f1f1f1;
            font-family: 'Consolas', 'Courier New', monospace;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding-bottom: 5px;
            border-bottom: 2px solid #555;
            color: #ffcc00;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .toggle-icon {
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .toggle-grid-wrapper {
            overflow: hidden;
            max-height: 500px;
            transition: max-height 0.3s ease;
        }

        .toggle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            /* kolom lebih rapat */
            gap: 8px;
            padding-top: 10px;
            font-size: 12px;
            /* ukuran teks lebih kecil */
        }

        .toggle-grid label {
            display: flex;
            align-items: center;
            padding: 4px 6px;
            background-color: #2a2d33;
            border: 1px solid #555;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .toggle-grid label:hover {
            background-color: #33373f;
        }

        .toggle-grid input[type="checkbox"] {
            accent-color: #ffcc00;
            margin-right: 8px;
            transform: scale(1);
            /* normal size, tidak dibesarkan */
        }

        /* Saat minimize */
        .column-toggle-panel.minimized .toggle-grid-wrapper {
            max-height: 0;
            padding-top: 0;
        }

        .column-toggle-panel.minimized .toggle-icon {
            transform: rotate(-90deg);
        }
    </style>
</head>

<body>
    <div class="container py-4">
        {{-- <div class="mb-3">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div> --}}

        {{-- <div class="card mb-4 border-dark bg-light">
            <div class="card-header bg-dark text-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-date me-2"></i>
                        <strong>PRODUCTION DATE SELECTOR</strong>
                    </div>
                    <div class="badge bg-warning text-dark">
                        {{ Carbon\Carbon::parse($selectedDate ?? now())->format('D, M j Y') }}
                    </div>
                </div>
            </div>
            <div class="card-body p-2 bg-secondary bg-opacity-10">
                <form method="GET" action="{{ route('dashboard.prodPlan') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-dark text-white">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <input type="date" class="form-control border-dark bg-light" name="date"
                                value="{{ $selectedDate ?? now()->format('Y-m-d') }}" style="font-weight: bold;"
                                max="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-group" role="group">
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-funnel-fill me-1"></i> FILTER
                            </button>
                            @if (request()->has('date'))
                                <button type="submit" class="btn btn-outline-dark">
                                    <a href="{{ route('dashboard.prodPlan') }}"
                                        style="color: black; text-decoration:none">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> RESET
                                    </a>
                                </button>
                            @endif
                            <button type="button" class="btn btn-outline-dark" onclick="navigateDate(-1)">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            @php
                                $selected = $selectedDate ?? now()->format('Y-m-d');
                                $isToday = $selected === now()->format('Y-m-d');
                            @endphp

                            <button type="button" class="btn btn-outline-dark {{ $isToday ? 'disabled' : '' }}"
                                onclick="navigateDate(1)" {{ $isToday ? 'disabled' : '' }}>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <!-- Add the Re-fetch Data button -->
                            <button type="submit" name="force_refresh" value="1" class="btn btn-warning">
                                <i class="fas fa-sync-alt me-1"></i> RE-FETCH
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div> --}}

        <!-- Add message display area -->
        @if (isset($message))
            <div class="alert alert-{{ $messageType ?? 'info' }} alert-dismissible fade show mb-3" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-uppercase fw-bold" style="letter-spacing: 1px;">
                PRODUCTION PULLING PLAN - {{ Carbon\Carbon::parse($selectedDate ?? now())->format('l, j F Y') }}
            </h2>
            <div>
                <span class="badge bg-secondary me-2">
                    Last Update: {{ \Carbon\Carbon::parse($lastUpdate ?? now())->format('H:i:s') }}
                </span>
                <a class="btn btn-outline-warning" href="/pulling/settings">
                    <i class="fas fa-cog me-1"></i> SETTINGS
                </a>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="lineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="line3-tab" data-bs-toggle="tab" data-bs-target="#line3"
                    type="button" role="tab">AS003</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line4-tab" data-bs-toggle="tab" data-bs-target="#line4" type="button"
                    role="tab">AS004</button>
            </li>
        </ul>

        <div class="tab-content" id="lineTabsContent">
            <!-- AS003 Tab -->
            <div class="tab-pane fade show active" id="line3" role="tabpanel" aria-labelledby="line3-tab">
                <div data-toggle-table="AS003">
                    @php
                        $as003MorningQty = $grouped['AS003']['morning_shift_qty'] ?? 0;
                        $as003NightQty = $grouped['AS003']['night_shift_qty'] ?? 0;
                        $as003TotalQty = $grouped['AS003']['total_qty'] ?? 0;

                        // Morning shift status
                        $as003MorningStatus = 'Normal Shift';
                        if ($as003MorningQty > 900) {
                            $as003MorningStatus = 'Advance to LS1';
                        } elseif ($as003MorningQty > 750) {
                            $as003MorningStatus = 'Advance to NS';
                        }

                        // Night shift status
                        $as003NightStatus = 'Normal Shift';
                        if ($as003NightQty > 630) {
                            $as003NightStatus = 'Advance to LS3';
                        }
                    @endphp
                    <div class="alert alert-dark p-2 mb-4"
                        style="background-color: #2a2a2a; border-left: 4px solid #ff6b00;">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Shift Data - Industrial Style -->
                            <div class="d-flex gap-3 align-items-end">
                                <!-- Morning Shift -->
                                <div class="industrial-shift-box bg-dark p-2" style="border: 1px solid #555;">
                                    <div class="text-uppercase small" style="color: #aaa; letter-spacing: 1px;">MORNING
                                        SHIFT ORDER
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fs-4 fw-bold" style="color: #ff6b00;">{{ $as003MorningQty }}</span>
                                        @if ($as003MorningStatus != 'Normal Shift')
                                            <span class="badge rounded-0"
                                                style="background-color: #ff9e00; color: #000; font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                                {{ $as003MorningStatus }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Night Shift -->
                                <div class="industrial-shift-box bg-dark p-2" style="border: 1px solid #555;">
                                    <div class="text-uppercase small" style="color: #aaa; letter-spacing: 1px;">NIGHT
                                        SHIFT ORDER
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fs-4 fw-bold" style="color: #00b4ff;">{{ $as003NightQty }}</span>
                                        @if ($as003NightStatus != 'Normal Shift')
                                            <span class="badge rounded-0"
                                                style="background-color: #ff3d3d; color: #fff; font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                                {{ $as003NightStatus }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="industrial-total-box bg-dark p-2 ms-2" style="border: 1px solid #666;">
                                    <div class="text-uppercase small" style="color: #aaa; letter-spacing: 1px;">TOTAL
                                    </div>
                                    <div class="fs-4 fw-bold" style="color: #fff;">{{ $as003TotalQty }}</div>
                                </div>
                            </div>

                            <!-- Status Indicator (if any) -->
                            @if ($as003MorningStatus != 'Normal Shift' || $as003NightStatus != 'Normal Shift')
                                <div class="d-flex gap-2">
                                    @if ($as003MorningStatus != 'Normal Shift')
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="text-uppercase" style="font-size: 0.6rem; color: #aaa;">
                                                MORNING
                                            </div>
                                            <span class="badge rounded-0 mt-1"
                                                style="background-color: #ff9e00; color: #000; padding: 0.35rem 0.75rem; font-weight: 600;">
                                                {{ $as003MorningStatus }}
                                            </span>
                                        </div>
                                    @endif
                                    @if ($as003NightStatus != 'Normal Shift')
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="text-uppercase" style="font-size: 0.6rem; color: #aaa;">NIGHT
                                            </div>
                                            <span class="badge rounded-0 mt-1"
                                                style="background-color: #ff3d3d; color: #fff; padding: 0.35rem 0.75rem; font-weight: 600;">
                                                {{ $as003NightStatus }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="column-toggle-panel p-3 minimized">
                        <div class="panel-header pb-3" onclick="togglePanel(this)">
                            <i class="fas fa-table-columns"></i>
                            <i class="toggle-icon fas fa-chevron-down" style="margin-left: auto;"></i>
                        </div>
                        <div class="toggle-grid-wrapper">
                            <div class="toggle-grid">
                                <label><input type="checkbox" class="toggle-col" data-col="0" checked>
                                    Customer</label>
                                <label><input type="checkbox" class="toggle-col" data-col="1" checked> Dock</label>
                                <label><input type="checkbox" class="toggle-col" data-col="2" checked>
                                    Cycle</label>
                                <label><input type="checkbox" class="toggle-col" data-col="3" checked> Back
                                    No</label>
                                <label><input type="checkbox" class="toggle-col" data-col="4" checked>
                                    Order</label>
                                <label><input type="checkbox" class="toggle-col" data-col="5" checked> Direct
                                    Pulling</label>
                                <label><input type="checkbox" class="toggle-col" data-col="6" checked> Stock
                                    Chute</label>
                                <label><input type="checkbox" class="toggle-col" data-col="7" checked> Cycle
                                    Time</label>
                                <label><input type="checkbox" class="toggle-col" data-col="8" checked>
                                    Start</label>
                                <label><input type="checkbox" class="toggle-col" data-col="9" checked>
                                    Duration</label>
                                <label><input type="checkbox" class="toggle-col" data-col="10" checked>
                                    Target</label>
                                <label><input type="checkbox" class="toggle-col" data-col="11" checked> Delivery
                                    Time</label>
                                <label><input type="checkbox" class="toggle-col" data-col="12" checked> Delivery
                                    Date</label>
                                <label><input type="checkbox" class="toggle-col" data-col="13" checked> Balance
                                    Time</label>
                            </div>
                        </div>
                    </div>
                    <div style="max-height: 800px; overflow-y: auto;">
                        <table class="table table-bordered table-hover text-center align-middle table-dark">
                            <thead
                                style="position: sticky; top: 0; z-index: 100; background-color: #343a40; color: white;">
                                <tr>
                                    <th rowspan="2">Customer</th>
                                    <th rowspan="2">Dock</th>
                                    <th rowspan="2">Cycle</th>
                                    <th rowspan="2">Back No</th>
                                    <th rowspan="2">Order</th>
                                    <th colspan="2">Running Qty</th>
                                    <th rowspan="2">Cycle Time</th>
                                    <th colspan="3">Working Time</th>
                                    <th rowspan="2">Delivery Time</th>
                                    <th rowspan="2">Delivery Date</th>
                                    <th rowspan="2">Balance Time</th>
                                </tr>
                                <tr>
                                    <th>Direct Pulling</th>
                                    <th>Stock Chute</th>
                                    <th>Start</th>
                                    <th>Duration</th>
                                    <th>Target</th>
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
                                                        return 'bg-warning bg-opacity-75 fw-bold text-dark';
                                                    } else {
                                                        return 'bg-secondary bg-opacity-25 fw-bold text-secondary';
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            @if ($index === 0)
                                                <td rowspan="{{ $rowspan }}"><span
                                                        class="flip">{{ $customer }}</span></td>
                                                <td rowspan="{{ $rowspan }}"><span
                                                        class="flip">{{ $dock }}</span></td>
                                            @endif
                                            <td><span class="flip">{{ $item->cycle }}</span></td>
                                            <td><span class="flip">{{ $item->back_no }}</span></td>
                                            <td><span class="flip">{{ $item->order_qty }}</span></td>
                                            <td
                                                class="{{ getQtyClass($item->direct_pulling_qty, $item->order_qty) }}">
                                                <span class="flip" data-type="direct-pulling"
                                                    data-item-id="{{ $item->id }}">
                                                    {{ $item->direct_pulling_qty ?: '0' }}
                                                </span>
                                            </td>
                                            <td class="{{ getQtyClass($item->stock_chute_qty, $item->order_qty) }}">
                                                <span class="flip" data-type="stock-chute"
                                                    data-item-id="{{ $item->id }}">
                                                    {{ $item->stock_chute_qty ?: '0' }}
                                                </span>
                                            </td>
                                            <td><span class="flip">{{ $item->prod_time }}</span></td>
                                            <td>
                                                <span data-type="start"
                                                    data-item-id="{{ $item->id }}">{{ $item->working_start ?? '--' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="flip text-warning  ">
                                                    {{ $item->working_duration ?? '--' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span data-type="end"
                                                    data-item-id="{{ $item->id }}">{{ $item->working_end ?? '--' }}
                                                </span>
                                            </td>
                                            @if ($index === 0)
                                                <td rowspan="{{ $rowspan }}"><span
                                                        class="flip">{{ $delivery }}</span></td>
                                                <td rowspan="{{ $rowspan }}">
                                                    <span class="flip">
                                                        {{ $item->delivery_date ? Carbon\Carbon::parse($item->delivery_date)->format('m/d') : '--' }}
                                                    </span>
                                                </td>
                                                <td rowspan="{{ $rowspan }}"
                                                    class="{{ $item->balance_time && $hours < 3 ? 'table-danger' : '' }}">
                                                    <span data-type="balance" data-item-id="{{ $item->id }}">
                                                        {{ $item->balance_time ?? '--' }}
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center">No data for AS003.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- AS004 Tab -->
            <div class="tab-pane fade" id="line4" role="tabpanel" aria-labelledby="line4-tab">
                <div data-toggle-table="AS004">
                    @php
                        $as004MorningQty = $grouped['AS004']['morning_shift_qty'] ?? 0;
                        $as004NightQty = $grouped['AS004']['night_shift_qty'] ?? 0;
                        $as004TotalQty = $grouped['AS004']['total_qty'] ?? 0;

                        // Morning shift status
                        $as004MorningStatus = 'Normal Shift';
                        if ($as004MorningQty > 900) {
                            $as004MorningStatus = 'Advance to LS1';
                        } elseif ($as004MorningQty > 750) {
                            $as004MorningStatus = 'Advance to NS';
                        }

                        // Night shift status
                        $as004NightStatus = 'Normal Shift';
                        if ($as004NightQty > 630) {
                            $as004NightStatus = 'Advance to LS3';
                        }
                    @endphp
                    <div class="alert alert-dark p-2 mb-4"
                        style="background-color: #2a2a2a; border-left: 4px solid #ff6b00;">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Shift Data - Industrial Style -->
                            <div class="d-flex gap-3 align-items-end">
                                <!-- Morning Shift -->
                                <div class="industrial-shift-box bg-dark p-2" style="border: 1px solid #555;">
                                    <div class="text-uppercase small" style="color: #aaa; letter-spacing: 1px;">
                                        MORNING SHIFT ORDER
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fs-4 fw-bold"
                                            style="color: #ff6b00;">{{ $as004MorningQty }}</span>
                                        @if ($as004MorningStatus != 'Normal Shift')
                                            <span class="badge rounded-0"
                                                style="background-color: #ff9e00; color: #000; font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                                {{ $as004MorningStatus }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Night Shift -->
                                <div class="industrial-shift-box bg-dark p-2" style="border: 1px solid #555;">
                                    <div class="text-uppercase small" style="color: #aaa; letter-spacing: 1px;">
                                        NIGHT SHIFT ORDER
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fs-4 fw-bold"
                                            style="color: #00b4ff;">{{ $as004NightQty }}</span>
                                        @if ($as004NightStatus != 'Normal Shift')
                                            <span class="badge rounded-0"
                                                style="background-color: #ff3d3d; color: #fff; font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                                                {{ $as004NightStatus }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="industrial-total-box bg-dark p-2 ms-2" style="border: 1px solid #666;">
                                    <div class="text-uppercase small" style="color: #aaa; letter-spacing: 1px;">
                                        TOTAL
                                    </div>
                                    <div class="fs-4 fw-bold" style="color: #fff;">{{ $as004TotalQty }}</div>
                                </div>
                            </div>

                            <!-- Status Indicator (if any) -->
                            @if ($as004MorningStatus != 'Normal Shift' || $as004NightStatus != 'Normal Shift')
                                <div class="d-flex gap-2">
                                    @if ($as004MorningStatus != 'Normal Shift')
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="text-uppercase" style="font-size: 0.6rem; color: #aaa;">
                                                MORNING
                                            </div>
                                            <span class="badge rounded-0 mt-1"
                                                style="background-color: #ff9e00; color: #000; padding: 0.35rem 0.75rem; font-weight: 600;">
                                                {{ $as004MorningStatus }}
                                            </span>
                                        </div>
                                    @endif
                                    @if ($as004NightStatus != 'Normal Shift')
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="text-uppercase" style="font-size: 0.6rem; color: #aaa;">
                                                NIGHT
                                            </div>
                                            <span class="badge rounded-0 mt-1"
                                                style="background-color: #ff3d3d; color: #fff; padding: 0.35rem 0.75rem; font-weight: 600;">
                                                {{ $as004NightStatus }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="column-toggle-panel p-3 minimized">
                        <div class="panel-header pb-3" onclick="togglePanel(this)">
                            <i class="fas fa-table-columns"></i>
                            <i class="toggle-icon fas fa-chevron-down" style="margin-left: auto;"></i>
                        </div>
                        <div class="toggle-grid-wrapper">
                            <div class="toggle-grid">
                                <label><input type="checkbox" class="toggle-col" data-col="0" checked>
                                    Customer</label>
                                <label><input type="checkbox" class="toggle-col" data-col="1" checked>
                                    Dock</label>
                                <label><input type="checkbox" class="toggle-col" data-col="2" checked>
                                    Cycle</label>
                                <label><input type="checkbox" class="toggle-col" data-col="3" checked> Back
                                    No</label>
                                <label><input type="checkbox" class="toggle-col" data-col="4" checked>
                                    Order</label>
                                <label><input type="checkbox" class="toggle-col" data-col="5" checked> Direct
                                    Pulling</label>
                                <label><input type="checkbox" class="toggle-col" data-col="6" checked> Stock
                                    Chute</label>
                                <label><input type="checkbox" class="toggle-col" data-col="7" checked> Cycle
                                    Time</label>
                                <label><input type="checkbox" class="toggle-col" data-col="8" checked>
                                    Start</label>
                                <label><input type="checkbox" class="toggle-col" data-col="9" checked>
                                    Duration</label>
                                <label><input type="checkbox" class="toggle-col" data-col="10" checked>
                                    Target</label>
                                <label><input type="checkbox" class="toggle-col" data-col="11" checked> Delivery
                                    Time</label>
                                <label><input type="checkbox" class="toggle-col" data-col="12" checked> Delivery
                                    Date</label>
                                <label><input type="checkbox" class="toggle-col" data-col="13" checked> Balance
                                    Time</label>
                            </div>
                        </div>
                    </div>
                    <div style="max-height: 800px; overflow-y: auto;">
                        <table class="table table-bordered table-hover text-center align-middle table-dark">
                            <thead
                                style="position: sticky; top: 0; z-index: 100; background-color: #343a40; color: white;">
                                <tr>
                                    <th rowspan="2">Customer</th>
                                    <th rowspan="2">Dock</th>
                                    <th rowspan="2">Cycle</th>
                                    <th rowspan="2">Back No</th>
                                    <th rowspan="2">Order</th>
                                    <th colspan="2">Running Qty</th>
                                    <th rowspan="2">Cycle Time</th>
                                    <th colspan="3">Working Time</th>
                                    <th rowspan="2">Delivery Time</th>
                                    <th rowspan="2">Delivery Date</th>
                                    <th rowspan="2">Balance Time</th>
                                </tr>
                                <tr>
                                    <th>Direct Pulling</th>
                                    <th>Stock Chute</th>
                                    <th>Start</th>
                                    <th>Duration</th>
                                    <th>Target</th>
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
                                                        return 'bg-warning bg-opacity-75 fw-bold text-dark';
                                                    } else {
                                                        return 'bg-secondary bg-opacity-25 fw-bold text-secondary';
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            @if ($index === 0)
                                                <td rowspan="{{ $rowspan }}"><span
                                                        class="flip">{{ $customer }}</span></td>
                                                <td rowspan="{{ $rowspan }}"><span
                                                        class="flip">{{ $dock }}</span></td>
                                            @endif
                                            <td><span class="flip">{{ $item->cycle }}</span></td>
                                            <td><span class="flip">{{ $item->back_no }}</span></td>
                                            <td><span class="flip">{{ $item->order_qty }}</span></td>
                                            <td
                                                class="{{ getQtyClass($item->direct_pulling_qty, $item->order_qty) }}">
                                                <span class="flip" data-type="direct-pulling"
                                                    data-item-id="{{ $item->id }}">
                                                    {{ $item->direct_pulling_qty ?: '0' }}
                                                </span>
                                            </td>
                                            <td class="{{ getQtyClass($item->stock_chute_qty, $item->order_qty) }}">
                                                <span class="flip" data-type="stock-chute"
                                                    data-item-id="{{ $item->id }}">
                                                    {{ $item->stock_chute_qty ?: '0' }}
                                                </span>
                                            </td>
                                            <td><span class="flip">{{ $item->prod_time }}</span></td>
                                            <td>
                                                <span data-type="start"
                                                    data-item-id="{{ $item->id }}">{{ $item->working_start ?? '--' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="flip text-warning">
                                                    {{ $item->working_duration ?? '--' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span data-type="end"
                                                    data-item-id="{{ $item->id }}">{{ $item->working_end ?? '--' }}
                                                </span>
                                            </td>
                                            @if ($index === 0)
                                                <td rowspan="{{ $rowspan }}"><span
                                                        class="flip">{{ $delivery }}</span></td>
                                                <td rowspan="{{ $rowspan }}">
                                                    <span class="flip">
                                                        {{ $item->delivery_date ? Carbon\Carbon::parse($item->delivery_date)->format('m/d') : '--' }}
                                                    </span>
                                                </td>
                                                <td rowspan="{{ $rowspan }}"
                                                    class="{{ $item->balance_time && $hours < 3 ? 'table-danger' : '' }}">
                                                    <span data-type="balance" data-item-id="{{ $item->id }}">
                                                        {{ $item->balance_time ?? '--' }}
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center">No data for AS004.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        class ProductionPlanSSEClient {
            constructor() {
                this.eventSource = null;
                this.statusElement = null;
                this.currentDate = this.getCurrentDate();
                this.highlightTimeouts = new Set();
                this.lastHighlightTime = 0;
                this.originalOrder = new Map(); // Stores original order of rows for each table
                this.orderRestoreTimeouts = new Map(); // Timeouts for restoring original order
                this.init();
            }

            init() {
                this.createStatusIndicator();
                this.addFlipStyles();
                this.connect();
                this.setupDateChangeListener();
                this.setupErrorHandling();
                this.storeOriginalOrder(); // Store original order on initialization
            }

            storeOriginalOrder() {
                // Store original order of all rows in each table
                document.querySelectorAll('.tab-pane table tbody').forEach(tbody => {
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    this.originalOrder.set(tbody, rows);
                });
            }

            getCurrentDate() {
                const dateInput = document.querySelector('input[name="date"]');
                return dateInput ? dateInput.value : new Date().toISOString().split('T')[0];
            }

            createStatusIndicator() {
                this.statusElement = document.createElement('div');
                this.statusElement.id = 'sse-connection-status';
                this.statusElement.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            transition: all 0.3s ease;
        `;
                document.body.appendChild(this.statusElement);
            }

            addFlipStyles() {
                const style = document.createElement('style');
                style.textContent = `
            .flip {
                display: inline-block;
                transition: all 0.3s ease;
                transform-style: preserve-3d;
                transform-origin: bottom center;
            }
            .animate-flip {
                animation: flipAnimation 0.6s ease;
            }
            @keyframes flipAnimation {
                0% { transform: rotateX(0deg); opacity: 1; }
                50% { transform: rotateX(90deg); opacity: 0; }
                51% { transform: rotateX(-90deg); }
                100% { transform: rotateX(0deg); opacity: 1; }
            }
            
            /* Continuous blinking highlight styles */
            @keyframes continuousBlink {
                0%, 100% { background-color: var(--highlight-color); }
                50% { background-color: var(--base-bg); }
            }
            .highlight-beep-direct {
                --highlight-color: #12341E;
                --base-bg: #1E2024;
                animation: continuousBlink 1s ease-in-out infinite;
            }
            .highlight-beep-stock {
                --highlight-color: #4D3A0A;
                --base-bg: #1E2024;
                animation: continuousBlink 1s ease-in-out infinite;
            }
            .highlight-beep-direct td,
            .highlight-beep-stock td {
                background-color: inherit !important;
            }
        `;
                document.head.appendChild(style);
            }

            connect() {
                if (this.eventSource) {
                    this.eventSource.close();
                }

                this.eventSource = new EventSource(`/stream/direct-pulling-updates?date=${this.currentDate}`);
                this.updateConnectionStatus('connecting');

                this.eventSource.onopen = () => {
                    this.updateConnectionStatus('connected');
                };

                this.eventSource.addEventListener('directPullingUpdate', (e) => {
                    const data = JSON.parse(e.data);
                    if (data.date === this.currentDate) {
                        this.handleUpdates(data.updates || []);
                        this.updateConnectionStatus('connected');
                    }
                });

                this.eventSource.onerror = (e) => {
                    console.error('SSE Error:', e);
                    this.updateConnectionStatus('disconnected');
                    this.reconnect();
                };
            }

            setupDateChangeListener() {
                const dateInput = document.querySelector('input[name="date"]');
                if (dateInput) {
                    dateInput.addEventListener('change', () => {
                        this.currentDate = this.getCurrentDate();
                        this.reconnect();
                    });
                }
            }

            updateConnectionStatus(status, message = '') {
                const statusConfig = {
                    connecting: {
                        text: '● Connecting to updates...',
                        style: 'background: #17a2b8; color: white;'
                    },
                    connected: {
                        text: '● Live Updates Active',
                        style: 'background: #28a745; color: white;'
                    },
                    disconnected: {
                        text: '● Connection Lost',
                        style: 'background: #dc3545; color: white;'
                    },
                    error: {
                        text: '● Update Error' + (message ? `: ${message}` : ''),
                        style: 'background: #ffc107; color: black;'
                    }
                };

                const config = statusConfig[status] || statusConfig.error;
                this.statusElement.textContent = config.text;
                this.statusElement.style.cssText += config.style;
            }

            handleUpdates(updates) {
                console.log('Processing updates:', updates);

                // Track all rows that need processing
                const rowsToProcess = new Set();

                updates.forEach(item => {
                    const directPullingElements = document.querySelectorAll(
                        `[data-item-id="${item.id}"][data-type="direct-pulling"]`
                    );
                    const stockChuteElements = document.querySelectorAll(
                        `[data-item-id="${item.id}"][data-type="stock-chute"]`
                    );
                    const startElements = document.querySelectorAll(
                        `[data-item-id="${item.id}"][data-type="start"]`
                    );
                    const endElements = document.querySelectorAll(
                        `[data-item-id="${item.id}"][data-type="end"]`
                    );
                    const balanceElements = document.querySelectorAll(
                        `[data-item-id="${item.id}"][data-type="balance"]`
                    );

                    // Update quantities if elements found
                    if (directPullingElements.length > 0 || stockChuteElements.length > 0) {
                        this.updateQuantity(
                            `[data-item-id="${item.id}"][data-type="direct-pulling"]`,
                            item.direct_pulling_qty,
                            'direct-pulling',
                            item.order_qty // <-- tambahkan target order
                        );
                        this.updateQuantity(
                            `[data-item-id="${item.id}"][data-type="stock-chute"]`,
                            item.stock_chute_qty,
                            'stock-chute',
                            item.order_qty // <-- tambahkan target order
                        );
                        this.updateQuantity(
                            `[data-item-id="${item.id}"][data-type="start"]`,
                            item.start,
                            'time'
                        );
                        this.updateQuantity(
                            `[data-item-id="${item.id}"][data-type="end"]`,
                            item.end,
                            'time'
                        );
                        this.updateQuantity(
                            `[data-item-id="${item.id}"][data-type="balance"]`,
                            item.balance,
                            'time'
                        );

                        // Find all rows containing this item
                        const rows = document.querySelectorAll(`tr:has([data-item-id="${item.id}"])`);
                        rows.forEach(row => rowsToProcess.add(row));
                    }
                });

                // Process all affected rows
                if (rowsToProcess.size > 0) {
                    this.processUpdatedRows(Array.from(rowsToProcess));
                }
            }

            processUpdatedRows(rows) {
                // First, group rows by their rowspan groups (customer+dock groups)
                const rowGroups = new Map();
                rows.forEach(row => {
                    // Find the first row of this rowspan group (the one with rowspan attributes)
                    let groupStartRow = row;
                    while (groupStartRow.previousElementSibling &&
                        groupStartRow.previousElementSibling.querySelector('[rowspan]')) {
                        groupStartRow = groupStartRow.previousElementSibling;
                    }

                    // Get all rows in this group
                    const rowspan = parseInt(groupStartRow.querySelector('[rowspan]')?.getAttribute(
                        'rowspan')) || 1;
                    const groupRows = [groupStartRow];
                    for (let i = 1; i < rowspan; i++) {
                        if (groupStartRow.nextElementSibling) {
                            groupRows.push(groupStartRow.nextElementSibling);
                            groupStartRow = groupStartRow.nextElementSibling;
                        }
                    }

                    // Add to our groups map
                    if (!rowGroups.has(groupStartRow)) {
                        rowGroups.set(groupStartRow, new Set(groupRows));
                    } else {
                        groupRows.forEach(r => rowGroups.get(groupStartRow).add(r));
                    }
                });

                // Now process each table's groups
                const tablesProcessed = new Set();
                for (const [groupStart, groupRows] of rowGroups) {
                    const tbody = groupStart.closest('tbody');
                    if (!tbody || tablesProcessed.has(tbody)) continue;

                    tablesProcessed.add(tbody);

                    // Cancel any pending restore for this table
                    if (this.orderRestoreTimeouts.has(tbody)) {
                        clearTimeout(this.orderRestoreTimeouts.get(tbody));
                        this.orderRestoreTimeouts.delete(tbody);
                    }

                    // Get all rows in the table
                    const allRows = Array.from(tbody.querySelectorAll('tr'));

                    // Find all groups in this table
                    const allGroups = [];
                    let currentRow = allRows[0];

                    while (currentRow) {
                        const rowspan = parseInt(currentRow.querySelector('[rowspan]')?.getAttribute(
                            'rowspan') || '1');
                        const group = [currentRow];

                        for (let i = 1; i < rowspan && currentRow.nextElementSibling; i++) {
                            group.push(currentRow.nextElementSibling);
                            currentRow = currentRow.nextElementSibling;
                        }

                        allGroups.push(group);
                        currentRow = currentRow?.nextElementSibling;
                    }

                    // Find which groups contain updated rows
                    const updatedGroups = allGroups.filter(group =>
                        group.some(row => rows.includes(row))
                    );

                    // Move updated groups to the top while maintaining their order
                    if (updatedGroups.length > 0) {
                        // Remove all rows from the table
                        while (tbody.firstChild) {
                            tbody.removeChild(tbody.firstChild);
                        }

                        // Rebuild the table with updated groups first, then others
                        const remainingGroups = allGroups.filter(group =>
                            !updatedGroups.includes(group)
                        );

                        // Add updated groups first
                        updatedGroups.forEach(group => {
                            group.forEach(row => tbody.appendChild(row));
                        });

                        // Then add remaining groups
                        remainingGroups.forEach(group => {
                            group.forEach(row => tbody.appendChild(row));
                        });

                        // Highlight all updated rows
                        rows.forEach(row => {
                            if (tbody.contains(row)) {
                                this.highlightRow(row, 'mixed');
                            }
                        });

                        // Schedule restoration of original order after 1 minute
                        const restoreTimeout = setTimeout(() => {
                            this.restoreOriginalOrder(tbody);
                            this.orderRestoreTimeouts.delete(tbody);
                        }, 60000);

                        this.orderRestoreTimeouts.set(tbody, restoreTimeout);
                    }
                }
            }

            restoreOriginalOrder(tbody) {
                if (!this.originalOrder.has(tbody)) return;

                const originalRows = this.originalOrder.get(tbody);
                const currentRows = Array.from(tbody.querySelectorAll('tr'));

                // Only restore if the number of rows matches
                if (originalRows.length !== currentRows.length) {
                    console.warn('Row count mismatch, skipping restore');
                    return;
                }

                // Remove all current rows
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }

                // Add back rows in original order
                originalRows.forEach(row => {
                    tbody.appendChild(row);
                });
            }

            highlightRow(row, updateType) {
                // Remove all highlight classes first
                row.classList.remove(
                    'highlight-beep-direct',
                    'highlight-beep-stock'
                );

                // Force reflow to reset animation
                void row.offsetWidth;

                // Add appropriate highlight class
                const highlightClass = updateType === 'success' ?
                    'highlight-beep-direct' :
                    updateType === 'warning' ?
                    'highlight-beep-stock' :
                    'highlight-beep-direct';

                row.classList.add(highlightClass);

                // Set timeout to remove highlight after 5 seconds
                const timeoutId = setTimeout(() => {
                    row.classList.remove(highlightClass);
                    this.highlightTimeouts.delete(timeoutId);
                }, 60000);

                this.highlightTimeouts.add(timeoutId);
            }

            updateQuantity(selector, newValue, type, targetQty = null) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    const currentValue = el.textContent.trim();

                    if (currentValue !== String(newValue)) {
                        el.textContent = newValue;

                        if (!isNaN(parseFloat(newValue))) {
                            this.updateCellStyle(el.closest('td'), parseFloat(newValue), type, targetQty);
                        } else {
                            this.updateCellStyle(el.closest('td'), null, type);
                        }

                        this.animateChange(el.closest('td'));
                    }
                });
            }

            updateCellStyle(cell, value, type, targetQty = null) {
                // Jangan ubah style untuk waktu
                if (type === 'time') return;

                // Reset style kalau value null
                if (value === null) {
                    cell.className = '';
                    return;
                }

                // Default warna
                let bgClass = 'bg-secondary';
                let textClass = 'text-dark';

                if (type === 'direct-pulling' || type === 'stock-chute') {
                    if (targetQty !== null && !isNaN(targetQty)) {
                        if (value >= targetQty) {
                            bgClass = 'bg-success'; // Hijau jika sudah complete
                            textClass = 'text-white';
                        } else {
                            bgClass = 'bg-warning'; // Kuning jika belum complete
                            textClass = 'text-dark';
                        }
                    } else {
                        bgClass = value > 0 ? 'bg-success' : 'bg-warning';
                        textClass = 'text-white';
                    }
                }

                cell.className = `${bgClass} bg-opacity-75 fw-bold ${textClass}`;
            }


            animateChange(element) {
                const flipElement = element.querySelector('.flip');
                if (flipElement) {
                    flipElement.classList.add('animate-flip');
                    setTimeout(() => flipElement.classList.remove('animate-flip'), 600);
                }
            }

            clearAllHighlights() {
                this.highlightTimeouts.forEach(timeoutId => {
                    clearTimeout(timeoutId);
                });
                this.highlightTimeouts.clear();

                document.querySelectorAll('.highlight-beep-direct, .highlight-beep-stock').forEach(el => {
                    el.classList.remove('highlight-beep-direct', 'highlight-beep-stock');
                });
            }

            reconnect() {
                this.updateConnectionStatus('connecting', 'Reconnecting...');
                if (this.eventSource) {
                    this.eventSource.close();
                }
                setTimeout(() => this.connect(), 3000);
            }

            setupErrorHandling() {
                window.addEventListener('beforeunload', () => {
                    if (this.eventSource) {
                        this.eventSource.close();
                    }
                });
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.prodPlanSSE = new ProductionPlanSSEClient();
        });

        // Date navigation function
        function navigateDate(days) {
            const currentDate = new Date(document.querySelector('input[name="date"]').value);
            currentDate.setDate(currentDate.getDate() + days);
            const newDate = currentDate.toISOString().split('T')[0];
            document.querySelector('input[name="date"]').value = newDate;
            document.querySelector('form').submit();
        }

        (function() {
            // cari semua container yang punya tabel & toggle
            document.querySelectorAll('[data-toggle-table]').forEach(container => {
                const containerId = container.getAttribute('data-toggle-table');
                const table = container.querySelector('table');
                if (!table) return console.warn('Tidak menemukan tabel di ' + containerId);

                const manager = {
                    table,
                    meta: null,
                    hiddenCols: new Set(JSON.parse(localStorage.getItem('hiddenCols_' + containerId) ||
                        '[]'))
                };

                function initMeta() {
                    const rows = Array.from(table.rows);
                    const matrix = [];
                    let maxCols = 0;

                    for (let r = 0; r < rows.length; r++) {
                        if (!matrix[r]) matrix[r] = [];
                        let col = 0;
                        for (const cell of Array.from(rows[r].cells)) {
                            while (matrix[r][col]) col++;
                            if (!cell.dataset.origColspan) cell.dataset.origColspan = cell.colSpan;
                            if (!cell.dataset.origRowspan) cell.dataset.origRowspan = cell.rowSpan;

                            cell._origColspan = parseInt(cell.dataset.origColspan, 10) || 1;
                            cell._origRowspan = parseInt(cell.dataset.origRowspan, 10) || 1;
                            cell._startCol = col;

                            for (let rr = 0; rr < cell._origRowspan; rr++) {
                                if (!matrix[r + rr]) matrix[r + rr] = [];
                                for (let cc = 0; cc < cell._origColspan; cc++) {
                                    matrix[r + rr][col + cc] = cell;
                                }
                            }

                            col += cell._origColspan;
                        }
                        if (col > maxCols) maxCols = col;
                    }

                    manager.meta = {
                        matrix,
                        maxCols,
                        rows
                    };
                }

                function updateVisibility() {
                    const {
                        matrix,
                        maxCols
                    } = manager.meta;
                    const unique = new Set();
                    for (let r = 0; r < matrix.length; r++) {
                        for (let c = 0; c < maxCols; c++) {
                            const cell = matrix[r][c];
                            if (cell) unique.add(cell);
                        }
                    }

                    unique.forEach(cell => {
                        const start = cell._startCol;
                        const ospan = cell._origColspan;
                        let visibleCount = 0;

                        for (let k = 0; k < ospan; k++) {
                            if (!manager.hiddenCols.has(start + k)) visibleCount++;
                        }

                        if (visibleCount === 0) {
                            cell.style.display = 'none';
                        } else {
                            cell.style.display = '';
                            cell.colSpan = ospan > 1 ? visibleCount : 1;
                        }
                        cell.rowSpan = cell._origRowspan;
                    });

                    localStorage.setItem('hiddenCols_' + containerId, JSON.stringify([...manager.hiddenCols]));
                }

                function toggleColumn(index, show) {
                    if (show) manager.hiddenCols.delete(index);
                    else manager.hiddenCols.add(index);
                    updateVisibility();
                }

                initMeta();
                updateVisibility();

                container.querySelectorAll('.toggle-col').forEach(cb => {
                    const idx = parseInt(cb.dataset.col, 10);
                    if (isNaN(idx)) return;
                    cb.checked = !manager.hiddenCols.has(idx);
                    cb.addEventListener('change', () => toggleColumn(idx, cb.checked));
                });

                window[`__colToggleReset_${containerId}`] = function() {
                    manager.hiddenCols.clear();
                    updateVisibility();
                    container.querySelectorAll('.toggle-col').forEach(cb => cb.checked = true);
                };
            });
        })();


        function togglePanel(header) {
            const panel = header.closest(".column-toggle-panel");
            panel.classList.toggle("minimized");
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
