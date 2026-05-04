@extends('layouts.root.minimal')

@section('main')
    <style>
        :root {
            --dm-bg: var(--bs-body-bg, #ffffff);
            --dm-card: var(--bs-light, #f8f9fa);
            --dm-border: color-mix(in srgb, var(--dm-bg) 70%, #6c757d 30%);
            --dm-text: var(--bs-body-color, #2f3542);
            --dm-muted: #6c757d;
            --dm-blue: #B5D4F4;
            --dm-yellow:hsl(58, 100%, 70%);
            --dm-green: #C0DD97;
            --dm-complete:rgb(180, 255, 198); 
            --dm-pink: #F7C1C1;
            /* Gantt scale: 24 jam (06:00–05:00), tampil awal 6 jam agar bar panjang & jelas */
            --gantt-visible-hours: 6;
            /* 100–150px per jam (default 120px) */
            --gantt-hour-width: 120px;
            --gantt-customer-col-width: 200px;
            --gantt-type-col-width: 120px;
            --gantt-left-cols: calc(var(--gantt-customer-col-width) + var(--gantt-type-col-width));
            --gantt-slot-count: 24;
        }

        .delivery-dash {
            font-size: 14px;
            color: var(--dm-text);
        }

        .delivery-dash .card {
            border: 0.5px solid var(--dm-border);
        }

        .delivery-dash .tab-content {
            border: 0.5px solid var(--dm-border);
            border-top: 0;
            padding: 10px;
            background: var(--dm-bg);
        }

        .delivery-dash .nav-tabs .nav-link {
            padding: 6px 10px;
            font-size: 14px;
        }

        .delivery-dash .table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .delivery-dash .table td,
        .delivery-dash .table th {
            border: 0.5px solid var(--dm-border);
            padding: 4px 6px;
            vertical-align: middle;
        }

        .delivery-dash .form-control,
        .delivery-dash .custom-select {
            font-size: 12px;
            border-width: 0.5px;
        }

        .gantt-wrap {
            overflow: auto;
            border: 0.5px solid var(--dm-border);
            border-radius: 6px;
            max-height: 86vh;
            /* Ikuti lebar container; scroll handle sisanya */
            width: 100%;
            max-width: 100%;
        }

        .gantt-table {
            /* Lebar total = kolom kiri + 24 jam */
            min-width: calc(var(--gantt-left-cols) + (24 * var(--gantt-hour-width)));
            width: auto;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 0;
            table-layout: fixed;
        }

        .gantt-table thead th {
            position: sticky;
            top: 0;
            background: var(--dm-card);
            z-index: 6;
            border: 0.5px solid var(--dm-border);
            padding: 3px 4px;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .gantt-sticky-col {
            position: sticky;
            left: 0;
            background: var(--dm-bg);
            z-index: 5;
            min-width: 200px;
            max-width: 260px;
            text-align: left;
            font-weight: 500;
        }

        .gantt-type-col {
            position: sticky;
            left: 200px;
            z-index: 5;
            min-width: 120px;
            max-width: 120px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
            background: var(--dm-bg);
            color: var(--dm-muted);
        }

        /* Header + sticky columns harus di atas bar gantt */
        .gantt-table thead th.gantt-sticky-col,
        .gantt-table thead th.gantt-type-col {
            z-index: 7;
        }

        .gantt-type-col.is-prep {
            color: #5c7f2b;
        }

        .gantt-type-col.is-truck {
            color: #b86f00;
        }

        .gantt-time-col {
            width: var(--gantt-hour-width);
            min-width: var(--gantt-hour-width);
        }

        .gantt-track-cell {
            position: relative;
            padding: 0 !important;
            /* Tinggi row ditambah supaya label marker tidak overlap bar */
            height: 64px;
            vertical-align: middle;
            border: 0.5px solid var(--dm-border);
            /* Biarkan label marker tampil penuh tanpa kepotong */
            overflow: visible;
        }

        .gantt-track {
            position: relative;
            height: 40px;
            /* beri ruang di atas track untuk label */
            margin: 18px 4px 6px 4px;
            border-radius: 2px;
            background-color: transparent;
            /* label marker diposisikan di atas track */
            overflow: visible;
            /* Garis vertikal 1px per jam — tidak hilang saat zoom out (bukan subpixel dari calc(... - 0.5px)). */
            background-image: linear-gradient(
                to right,
                var(--dm-border) 0,
                var(--dm-border) 1px,
                transparent 1px,
                transparent 100%
            );
            /* 1 jam = 1 kolom tetap (sinkron dengan header) */
            background-size: var(--gantt-hour-width) 100%;
            background-repeat: repeat;
            background-position: 0 0;
        }

        .gantt-bar-wrap {
            position: absolute;
            top: 3px;
            min-width: 10px;
            height: 34px;
            box-sizing: border-box;
            cursor: pointer;
            z-index: 2;
        }

        .gantt-bar-wrap.is-instant {
            min-width: 2px;
            width: 2px !important;
        }

        .gantt-bar-wrap.is-overnight {
            min-width: 28px;
            width: 28px !important;
        }

        .gantt-bar-wrap.is-overnight .gantt-bar-stack {
            border: 2px dashed #f39c12;
            background: rgba(243, 156, 18, 0.12);
            box-shadow: none;
        }

        .gantt-bar-wrap.truck-pill {
            top: 7px;
            height: 26px;
            width: 44px !important;
            min-width: 44px;
        }

        .gantt-now-marker {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 0;
            z-index: 4;
            pointer-events: none;
        }

        .gantt-now-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            transform: translateX(-50%);
            background:rgb(67, 53, 220);
            box-shadow: 0 0 2px rgba(0, 17, 255, 0.45);
        }

        .gantt-window-fill {
            position: absolute;
            top: 0;
            bottom: 0;
            background: color-mix(in srgb, var(--dm-pink) 55%, transparent);
            pointer-events: none;
            z-index: 1;
        }

        .gantt-truck-marker {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 0;
            z-index: 4;
            pointer-events: none;
        }

        .gantt-truck-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            transform: translateX(-50%);
            background:rgb(255, 0, 0);
            box-shadow: 0 0 2px rgba(220, 53, 53, 0.45);
        }

        .gantt-now-label {
            position: absolute;
            left: 0;
            top: -15px;
            transform: translateX(-50%);
            font-size: 9px;
            line-height: 1.2;
            background:rgb(59, 53, 220);
            color: #fff;
            padding: 1px 5px;
            border-radius: 2px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .gantt-truck-label {
            position: absolute;
            left: 0;
            top: -15px;
            transform: translateX(-50%);
            font-size: 9px;
            line-height: 1.2;
            background:rgb(220, 53, 53);
            color: #fff;
            padding: 1px 5px;
            border-radius: 2px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .gantt-bar-stack {
            display: flex;
            width: 100%;
            height: 100%;
            border-radius: 3px;
            overflow: hidden;
            border: 0.5px solid var(--dm-border);
        }

        .gantt-seg { height: 100%; min-width: 2px; }
        .gantt-seg.ontime { background: var(--dm-complete); }
        .gantt-seg.delay { background: var(--dm-yellow); }
        .gantt-seg.overdue { background: #dc3545; }
        .gantt-seg.empty { background: var(--dm-complete); }
        .gantt-seg.truck { background: #f59e0b; }
        .gantt-seg.truck-complete { background: var(--dm-blue); }

        .legend-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin: 0 0 8px 0;
            padding: 0;
            list-style: none;
            font-size: 11px;
        }

        .legend-row li {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .legend-sq {
            width: 14px;
            height: 14px;
            border: 0.5px solid var(--dm-border);
            border-radius: 2px;
        }

        /* Timeline card (referensi gantt horizontal, tema putih) */
        .delivery-timeline-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .delivery-timeline-card .timeline-card-header {
            padding: 14px 16px;
            border-bottom: 1px solid #eef0f3;
            background: #ffffff;
            align-items: center;
        }

        .delivery-timeline-card .timeline-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #1a1d21;
        }

        .delivery-timeline-card .timeline-menu-btn {
            border: none;
            background: transparent;
            color: #6c757d;
            padding: 4px 8px;
            border-radius: 6px;
            line-height: 1;
            font-size: 18px;
        }

        .delivery-timeline-card .timeline-menu-btn:hover {
            background: #f3f4f6;
            color: #1a1d21;
        }

        .delivery-timeline-card .gantt-wrap {
            border: none;
            border-radius: 0;
            max-height: none;
            background: #ffffff;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .delivery-timeline-card .gantt-grid-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            width: 100%;
        }

        .delivery-timeline-card .gantt-grid {
            min-width: calc(var(--gantt-left-cols) + (var(--gantt-slot-count) * var(--gantt-hour-width)));
            background: #ffffff;
        }

        .delivery-timeline-card .gantt-grid-row {
            display: grid;
            grid-template-columns:
                var(--gantt-customer-col-width)
                var(--gantt-type-col-width)
                repeat(var(--gantt-slot-count), var(--gantt-hour-width));
        }

        .delivery-timeline-card .gantt-grid-cell {
            border: 1px solid #eef0f3;
            background: #ffffff;
            box-sizing: border-box;
        }

        .delivery-timeline-card .gantt-grid-sticky-col {
            /* Kolom kiri ikut scroll bersama timeline (tidak sticky). */
            position: static;
            z-index: auto;
            background: #ffffff;
        }

        .delivery-timeline-card .gantt-grid-sticky-customer {
            left: auto;
            z-index: auto;
        }

        .delivery-timeline-card .gantt-grid-sticky-type {
            left: auto;
            z-index: auto;
        }

        .delivery-timeline-card .gantt-grid-header .gantt-grid-cell {
            background: #ffffff;
            border-color: #eef0f3;
            color: #5c6370;
            font-size: 11px;
            font-weight: 600;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delivery-timeline-card .gantt-grid-customer {
            background: #ffffff;
            border-color: #eef0f3;
            color: #2f3542;
            font-size: 12px;
            padding: 8px 10px;
            width: var(--gantt-customer-col-width);
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .delivery-timeline-card .gantt-grid-type {
            background: #ffffff;
            border-color: #eef0f3;
            font-size: 10px;
            padding: 6px 4px;
            width: var(--gantt-type-col-width);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            color: var(--dm-muted);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delivery-timeline-card .gantt-grid-left-cell {
            height: 136px;
        }

        .delivery-timeline-card .gantt-grid-type.is-prep {
            color: #5c7f2b;
        }

        .delivery-timeline-card .gantt-grid-type.is-truck {
            color: #b86f00;
        }

        .delivery-timeline-card .gantt-grid-type-split {
            display: flex;
            flex-direction: column;
            justify-content: stretch;
            align-items: stretch;
            padding: 0;
            overflow: hidden;
        }

        .delivery-timeline-card .gantt-grid-type-split .gantt-type-lane {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            font-size: 10px;
        }

        .delivery-timeline-card .gantt-grid-type-split .gantt-type-lane + .gantt-type-lane {
            border-top: 1px solid #eef0f3;
        }

        .delivery-timeline-card .gantt-grid-type-split .gantt-type-lane.is-prep {
            color: #5c7f2b;
        }

        .delivery-timeline-card .gantt-grid-type-split .gantt-type-lane.is-truck {
            color: #b86f00;
        }

        .delivery-timeline-card .gantt-grid-time {
            width: var(--gantt-hour-width);
            min-width: var(--gantt-hour-width);
        }

        .delivery-timeline-card .gantt-grid-track-cell {
            grid-column: 3 / span 24;
            border-color: #eef0f3;
            height: 136px;
            padding: 0;
            position: relative;
            overflow: visible;
        }

        .delivery-timeline-card .gantt-grid-track-split {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
        }

        .delivery-timeline-card .gantt-grid-track-lane {
            position: relative;
            flex: 1 1 50%;
            overflow: visible;
        }

        .delivery-timeline-card .gantt-grid-track-lane + .gantt-grid-track-lane {
            border-top: 1px solid #eef0f3;
        }

        .delivery-timeline-card .gantt-track {
            margin: 16px 0 6px 0;
            height: 40px;
            border-radius: 0;
            background-color: transparent;
            background-image: repeating-linear-gradient(
                to right,
                transparent 0,
                transparent calc(var(--gantt-hour-width) - 1px),
                rgba(0, 0, 0, 0) calc(var(--gantt-hour-width) - 1px),
                #dde1e7 calc(var(--gantt-hour-width) - 1px),
                #dde1e7 var(--gantt-hour-width),
                transparent var(--gantt-hour-width)
            );
            background-size: var(--gantt-hour-width) 100%;
            background-position: 0 0;
        }

        .delivery-timeline-card .gantt-seg.gantt-trail {
            background: #e8ecf1;
        }

        .delivery-timeline-card .gantt-bar-wrap {
            top: 4px;
            height: 36px;
            min-width: 48px;
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill {
            top: 9px;
            height: 24px;
            min-width: 42px;
            width: 42px !important;
        }

        .delivery-timeline-card .gantt-bar-stack {
            border: none;
            border-radius: 6px;
            overflow: hidden;
            height: 36px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
            align-items: stretch;
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill .gantt-bar-stack {
            border-radius: 4px;
            height: 24px;
        }

        .delivery-timeline-card .gantt-seg.ontime:only-child {
            border-radius: 6px;
        }

        .delivery-timeline-card .gantt-seg.delay:first-child {
            border-radius: 6px 0 0 6px;
        }

        .delivery-timeline-card .gantt-seg.gantt-trail:last-child {
            border-radius: 0 6px 6px 0;
        }

        .delivery-timeline-card .pill-meta {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            pointer-events: none;
            z-index: 3;
            padding-left: 6px;
            max-width: 42%;
        }

        .delivery-timeline-card .pill-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(0, 0, 0, 0.08);
            color: #2f3542;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        .delivery-timeline-card .pill-pct { display: none; }

        /* Responsif: di layar kecil, gunakan lebar container penuh (tetap bisa scroll) */
        @media (max-width: 992px) {
            :root {
                --gantt-hour-width: 92px;
            }
            .gantt-wrap,
            .delivery-timeline-card .gantt-wrap {
                max-width: 100%;
            }
        }

        .delivery-timeline-card .timeline-card-footer {
            padding: 10px 16px 14px;
            border-top: 1px solid #eef0f3;
            background: #ffffff;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .delivery-timeline-card .legend-timeline {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 12px;
            color: #2f3542;
        }

        .delivery-timeline-card .legend-timeline li {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .delivery-timeline-card .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .delivery-timeline-card .timeline-total {
            font-size: 13px;
            font-weight: 600;
            color: #1a1d21;
            margin-left: auto;
            font-variant-numeric: tabular-nums;
        }

        .delivery-timeline-card .gantt-now-label,
        .delivery-timeline-card .gantt-truck-label {
            font-size: 8px;
            padding: 2px 4px;
        }

        .delivery-weekly-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .delivery-weekly-card .weekly-card-header {
            padding: 14px 16px;
            border-bottom: 1px solid #eef0f3;
            background: #ffffff;
        }

        .delivery-weekly-card .weekly-title {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #1a1d21;
        }

        .delivery-weekly-card .weekly-card-body {
            padding: 12px 16px;
        }

        .delivery-weekly-row + .delivery-weekly-row {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eef0f3;
        }

        .delivery-weekly-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .delivery-weekly-progress {
            height: 18px;
            border-radius: 999px;
            background: #eef0f3;
            overflow: hidden;
        }

        .delivery-weekly-progress .progress-bar {
            font-size: 11px;
            font-weight: 600;
        }

        .delivery-weekly-note {
            font-size: 11px;
            color: var(--dm-muted);
            margin-top: 4px;
        }
    </style>

    <div class="row delivery-dash">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header py-2 d-flex align-items-center flex-wrap">
                    <div class="flex-grow-1" aria-hidden="true"></div>
                    <div class="text-center px-2">
                        <h3 class="mb-0" style="font-size: 32px;">
                            Daily Monitoring Delivery
                        </h3>
                    </div>
                    <div class="flex-grow-1 text-right mt-2 mt-md-0 pl-md-3">
                        <span class="text-muted d-block" style="font-size: 10px;">Waktu lokal</span>
                        <span id="deliveryDashLiveTime" class="font-weight-bold text-danger" style="font-size: 14px; font-variant-numeric: tabular-nums;">--:--:--</span>
                    </div>
                </div>
                <div class="card-body p-2">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#pane-chart" role="tab">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pane-master" role="tab">Master Cycle</a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pane-legend" role="tab">Keterangan</a>
                        </li> -->
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pane-chart" role="tabpanel">
                            <form class="form-inline flex-wrap align-items-end mb-2" id="chartFilterForm" onsubmit="return false;" style="gap: 8px;">
                                <div class="mb-2 mb-sm-0">
                                    <label class="mb-0 mr-1 d-block" style="font-size: 11px;">Delivery date dari</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateFrom" name="date_from" title="Tanggal awal delivery">
                                </div>
                                <div class="mb-2 mb-sm-0">
                                    <label class="mb-0 mr-1 d-block" style="font-size: 11px;">sampai</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateTo" name="date_to" title="Tanggal akhir delivery (kosong = tanggal awal)">
                                </div>
                                <div class="mb-2 mb-sm-0">
                                    <label class="mb-0 mr-1 d-block" style="font-size: 11px;">Customer</label>
                                    <select class="form-control form-control-sm" id="filterCustomer" style="min-width: 200px;">
                                        <option value="">Semua</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mb-2 mb-sm-0" id="btnReloadChart">Muat ulang</button>
                            </form>

                            <div id="chartEmpty" class="alert alert-light border small mb-2 d-none" role="alert"></div>

                            <div class="delivery-timeline-card mb-2" id="deliveryTimelineCard">
                                <div class="timeline-card-header d-flex justify-content-between align-items-center">
                                    <h2 class="timeline-title">Timeline delivery</h2>
                                    <button type="button" class="timeline-menu-btn" aria-label="Menu" title="Menu">&#8942;</button>
                                </div>
                                <div class="gantt-wrap" id="ganttWrap">
                                    <div id="ganttContainer"></div>
                                </div>
                                <div class="timeline-card-footer d-flex justify-content-between align-items-center">
                                    <ul class="legend-timeline">
                                        <li>
                                            <span class="legend-dot" style="background: var(--dm-complete);"></span>
                                            Progress 100%
                                        </li>
                                        <li>
                                            <span class="legend-dot" style="background: var(--dm-yellow);"></span>
                                            Belum selesai (&lt; 100%)
                                        </li>
                                    </ul>
                                    <div class="timeline-total" id="timelineTotalSum">Total: 0</div>
                                </div>
                            </div>

                            <div class="delivery-weekly-card mb-2 d-none" id="deliveryWeeklyCard">
                                <div class="weekly-card-header">
                                    <h2 class="weekly-title">Ringkasan mingguan delivery</h2>
                                </div>
                                <div class="weekly-card-body">
                                    <div id="weeklyGanttContainer"></div>
                                    <div class="delivery-weekly-note">Hover bar untuk melihat detail waktu tiap cycle.</div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-master" role="tabpanel">
                            <div class="card border mb-3">
                                <div class="card-body py-2">
                                    <h6 class="mb-2" style="font-size: 12px;">Pengaturan rentang ETA TRUCK &amp; Finish Preparation</h6>
                                    <button type="button" class="btn btn-sm btn-primary mb-2" id="btnOpenEtaSettingModal" data-toggle="modal" data-target="#etaWindowModal">
                                        Setting Rentang
                                    </button>
                                    <small id="etaWindowInfo" class="text-muted d-block">Rentang default: ETA TRUCK 0 jam, Finish Preparation 4 jam.</small>
                                </div>
                            </div>

                            <form id="masterCycleForm" class="mb-3">
                                <div class="form-row align-items-end">
                                    <div class="col-md-3 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Customer</label>
                                        <select class="form-control form-control-sm" id="mcycleCustomerId" required>
                                            <option value="">Pilih customer</option>
                                            @foreach ($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Nama cycle</label>
                                        <select class="form-control form-control-sm" id="mcycleName" required>
                                            <option value="" selected disabled>Pilih cycle</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Waktu rentang prep (jam mulai)</label>
                                        <div class="d-flex align-items-center">
                                            <input type="time" class="form-control form-control-sm" id="mcyclePrepStart" required step="60">
                                            <span class="mx-2 small text-muted">sampai</span>
                                            <input type="time" class="form-control form-control-sm" id="mcyclePrepEnd" required step="60">
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Waktu ETA truck</label>
                                        <input type="time" class="form-control form-control-sm" id="mcycleTruckTime" required step="60">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button type="submit" class="btn btn-sm btn-success" id="btnMasterSave">Simpan</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnMasterCancel">Batal edit</button>
                                    </div>
                                </div>
                                <p class="small text-muted mb-0">Field prep range dan ETA truck berdiri sendiri, tanpa asumsi otomatis.</p>
                            </form>

                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered" id="masterCycleTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Customer</th>
                                            <th>Cycle</th>
                                            <th>Rentang prep</th>
                                            <th>ETA truck</th>
                                            <th style="width: 130px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <h6 class="mb-2" style="font-size: 12px;">Daftar customer</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Nama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customers as $i => $c)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $c->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-legend" role="tabpanel">
                            <p class="small text-muted mb-2">Bar hijau untuk progress 100%, kuning untuk progress belum selesai.</p>
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Warna</th>
                                        <th>Arti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="legend-sq" style="background: var(--dm-complete);"></span></td>
                                        <td class="small">Progress sudah selesai (100%).</td>
                                    </tr>
                                    <tr>
                                        <td><span class="legend-sq" style="background: var(--dm-yellow);"></span></td>
                                        <td class="small">Progress belum selesai (&lt; 100%).</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="small mb-0">
                                <strong>Master cycle</strong> menyetel <em>nama cycle</em>, <em>waktu referensi prep</em>, dan opsional
                                <em>waktu ETA truck</em> (beda dengan prep) per customer+ cycle.
                                Data loading list memakai kolom <code>cycle</code> yang dicocokkan ke <code>cycle_name</code> master berdasarkan
                                kombinasi <code>customer_id</code> + <code>cycle_name</code>.
                                Scan time dipakai untuk filter data, bukan acuan penentuan cycle.
                                Progress % cycle dihitung sebagai penjumlahan progress tiap data LL pada cycle tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        $(function () {
            var stackedUrl = "{{ route('dashboard.delivery.stackedChart') }}";
            var loadingListUrl = "{{ route('loadingList.index') }}";
            var notifyUnfinishedUrl = "{{ route('dashboard.delivery.notifyUnfinished') }}";
            var masterIndex = "{{ route('dashboard.delivery.masterCycles.index') }}";
            var masterStore = "{{ route('dashboard.delivery.masterCycles.store') }}";
            var masterBase = "{{ url('/dashboard/delivery/master-cycles') }}";
            var csrf = "{{ csrf_token() }}";

            var customers = @json($customers);
            var chartRows = [];
            var chartMergedByCustTime = [];
            var chartCustomerOrder = [];
            var chartWeeklyPoints = [];
            var dashboardViewMode = 'daily';
            var weeklyChartInstance = null;
            var weeklyNowTimer = null;
            var editMasterId = null;
            var ganttNowTimer = null;
            var waNotifyTimer = null;
            var ganttHourWindow = 24;
            var ganttCols = 24;
            var etaWindowStorageKey = 'delivery_eta_window_v2';
            var waSentStorageKey = 'delivery_wa_unfinished_sent_v1';
            var etaWindowSettings = {
                eta_offset_hours: 0,
                finish_offset_hours: 4
            };

            function normalizeOffsetHours(v, fallback) {
                var n = parseInt(v, 10);
                if (isNaN(n)) {
                    return fallback;
                }
                if (n < -24) {
                    return -24;
                }
                if (n > 24) {
                    return 24;
                }
                return n;
            }

            function renderHourDropdownOptions(selector) {
                var html = '';
                for (var h = -24; h <= 24; h++) {
                    var label = h + ' jam';
                    if (h === 0) {
                        label = '0 jam (saat ini)';
                    }
                    html += '<option value="' + h + '">' + label + '</option>';
                }
                $(selector).html(html);
            }

            function loadEtaWindowSettings() {
                try {
                    var raw = localStorage.getItem(etaWindowStorageKey);
                    if (!raw) {
                        return;
                    }
                    var parsed = JSON.parse(raw);
                    etaWindowSettings.eta_offset_hours = normalizeOffsetHours(parsed.eta_offset_hours, 0);
                    etaWindowSettings.finish_offset_hours = normalizeOffsetHours(parsed.finish_offset_hours, 4);
                } catch (err) {
                    etaWindowSettings.eta_offset_hours = 0;
                    etaWindowSettings.finish_offset_hours = 4;
                }
            }

            function saveEtaWindowSettings() {
                localStorage.setItem(etaWindowStorageKey, JSON.stringify(etaWindowSettings));
            }

            function renderEtaWindowFormState() {
                $('#etaOffsetHours').val(String(etaWindowSettings.eta_offset_hours));
                $('#finishOffsetHours').val(String(etaWindowSettings.finish_offset_hours));
                $('#etaWindowInfo').text(
                    'Rentang aktif: ETA TRUCK ' + etaWindowSettings.eta_offset_hours +
                    ' jam, Finish Preparation ' + etaWindowSettings.finish_offset_hours + ' jam.'
                );
            }

            function tickDeliveryHeaderClock() {
                var lbl = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                $('#deliveryDashLiveTime').text(lbl);
            }

            function normalizeHour24(v) {
                var n = parseInt(v, 10) || 0;
                while (n < 0) {
                    n += 24;
                }
                return n % 24;
            }

            function getTimelineStartHour() {
                // Gunakan window 00:00-23:59 agar jam dini hari tidak "melompat" ke ujung kanan.
                return 0;
            }

            function getTimeOffsetLeftPct(offsetHours) {
                var d = new Date();
                d.setMinutes(d.getMinutes() + (offsetHours * 60));
                var hour = d.getHours();
                var minute = d.getMinutes();
                var second = d.getSeconds();
                var startHour = getTimelineStartHour();
                var v = hour + minute / 60 + second / 3600;
                if (v < startHour) {
                    v += 24;
                }
                var fr = v - startHour;
                return Math.max(0, Math.min(100, (fr / 24) * 100));
            }

            function getNowLeftPct() {
                // Marker biru = Finish Preparation.
                return getTimeOffsetLeftPct(etaWindowSettings.eta_offset_hours);
            }

            function getTruckArrivalLeftPct() {
                // Marker merah = ETA TRUCK.
                return getTimeOffsetLeftPct(etaWindowSettings.finish_offset_hours);
            }

            function updateGanttNowMarkers() {
                var nowPct = getNowLeftPct();
                var truckPct = getTruckArrivalLeftPct();
                var nowDate = new Date();
                nowDate.setMinutes(nowDate.getMinutes() + (etaWindowSettings.eta_offset_hours * 60));
                var nowLbl = formatClockDot(nowDate.toTimeString());
                var truckDate = new Date();
                truckDate.setMinutes(truckDate.getMinutes() + (etaWindowSettings.finish_offset_hours * 60));
                var truckLbl = formatClockDot(truckDate.toTimeString());
                var fillLeft = Math.min(nowPct, truckPct);
                var fillWidth = Math.abs(truckPct - nowPct);

                $('#ganttContainer .gantt-now-marker').css('left', nowPct + '%');
                $('#ganttContainer .gantt-now-label').text('Finish Preparation ' + nowLbl);
                $('#ganttContainer .gantt-truck-marker').css('left', truckPct + '%');
                $('#ganttContainer .gantt-truck-label').text('ETA TRUCK ' + truckLbl);
                $('#ganttContainer .gantt-window-fill').css({
                    left: fillLeft + '%',
                    width: fillWidth + '%'
                });
            }

            function timeToMinutes(t) {
                var p = (t || '00:00').substring(0, 5).split(':');
                return parseInt(p[0], 10) * 60 + parseInt(p[1] || '0', 10);
            }

            function timeToFrac(t) {
                var raw = String(t || '').trim();
                if (!raw.length) {
                    return null;
                }
                var p = raw.substring(0, 5).split(':');
                var hour = parseInt(p[0], 10);
                var minute = parseInt(p[1] || '0', 10);
                if (isNaN(hour) || isNaN(minute)) {
                    return null;
                }
                var startHour = getTimelineStartHour();
                var v = hour + minute / 60;
                if (v < startHour) {
                    v += 24;
                }
                return v - startHour;
            }

            function timeToWindowFrac(t) {
                return timeToFrac(t);
            }

            function calcDurationHours(startClock, endClock) {
                var s = timeToWindowFrac(startClock);
                var e = timeToWindowFrac(endClock);
                if (s === null || e === null) {
                    return 1;
                }
                var diff = e - s;
                if (diff <= 0) {
                    diff += 24;
                }
                return Math.max(diff, (1 / 60));
            }

            function addHoursToClockTime(timeStr, hourOffset) {
                var p = (timeStr || '00:00').substring(0, 5).split(':');
                var h = parseInt(p[0] || '0', 10);
                var m = parseInt(p[1] || '0', 10);
                if (isNaN(h)) {
                    h = 0;
                }
                if (isNaN(m)) {
                    m = 0;
                }
                var base = new Date();
                base.setHours(h, m, 0, 0);
                base.setHours(base.getHours() + parseInt(hourOffset || 0, 10));
                return String(base.getHours()).padStart(2, '0') + ':' + String(base.getMinutes()).padStart(2, '0');
            }

            function formatClockDot(timeStr) {
                var t = String(timeStr || '').trim();
                if (!t.length) {
                    return '-';
                }
                return t.substring(0, 5).replace(':', '.');
            }

            function getWaSentMap() {
                try {
                    return JSON.parse(localStorage.getItem(waSentStorageKey) || '{}');
                } catch (err) {
                    return {};
                }
            }

            function setWaSentMap(map) {
                localStorage.setItem(waSentStorageKey, JSON.stringify(map));
            }

            function getRowFinishPrepClock(row) {
                if (!row) {
                    return null;
                }
                var prepEndRaw = (row.prep_end_time != null) ? String(row.prep_end_time).trim() : '';
                if (prepEndRaw.length) {
                    return prepEndRaw.substring(0, 5);
                }
                if (!row.cycle_time || !String(row.cycle_time).trim().length) {
                    return null;
                }
                return addHoursToClockTime(row.cycle_time, etaWindowSettings.finish_offset_hours);
            }

            function isRowPastFinishPrep(row, currentFrac) {
                if (currentFrac === null || typeof currentFrac === 'undefined') {
                    return false;
                }
                var finishClock = getRowFinishPrepClock(row);
                if (!finishClock) {
                    return false;
                }
                var finishFrac = timeToFrac(finishClock);
                if (finishFrac === null) {
                    return false;
                }
                return currentFrac >= finishFrac;
            }

            function checkAndSendUnfinishedWaNotification() {
                if (!chartMergedByCustTime.length) {
                    return;
                }

                var now = new Date();
                var currentClock = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                var currentFrac = timeToFrac(currentClock);
                var dateFromVal = $('#filterDateFrom').val() || '';
                var dateToVal = $('#filterDateTo').val() || '';
                var sentMap = getWaSentMap();
                var toNotify = [];
                var sentKeyBucket = [];

                chartMergedByCustTime.forEach(function (row) {
                    var progress = parseFloat(row.progress_pct || 0);
                    if (progress >= 100) {
                        return;
                    }
                    if (!row.cycle_time || !String(row.cycle_time).trim().length) {
                        return;
                    }

                    if (!isRowPastFinishPrep(row, currentFrac)) {
                        return;
                    }

                    var sentKey = [
                        dateFromVal || '-',
                        dateToVal || '-',
                        row.customer_id || '-',
                        row.cycle_name || '-',
                        row.cycle_time || '-'
                    ].join('|');
                    if (sentMap[sentKey]) {
                        return;
                    }

                    sentKeyBucket.push(sentKey);
                    toNotify.push({
                        customer_name: row.customer_name || '-',
                        cycle_name: row.cycle_name || '-',
                        cycle_time: row.cycle_time || '00:00',
                        prep_end_time: row.prep_end_time || null,
                        progress_pct: Math.round(progress * 10) / 10,
                        total_done: parseInt(row.total_done || 0, 10),
                        total_target: parseInt(row.total_target || 0, 10),
                        ll_count: parseInt(row.ll_count || 0, 10)
                    });
                });

                if (!toNotify.length) {
                    return;
                }

                $.ajax({
                    url: notifyUnfinishedUrl,
                    method: 'POST',
                    data: {
                        _token: csrf,
                        date_from: dateFromVal,
                        date_to: dateToVal,
                        finish_offset_hours: etaWindowSettings.finish_offset_hours,
                        pending_rows: toNotify
                    }
                }).done(function () {
                    sentKeyBucket.forEach(function (k) {
                        sentMap[k] = true;
                    });
                    setWaSentMap(sentMap);
                });
            }

            function slotLabels24() {
                var a = [];
                for (var i = 0; i < 24; i++) {
                    var h = (getTimelineStartHour() + i) % 24;
                    a.push(String(h).padStart(2, '0') + ':00');
                }
                return a;
            }

            function escapeHtml(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function escapeAttr(s) {
                return String(s || '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;');
            }

            /*
             * Contoh setara untuk Chart.js (bar chart): tambahkan onClick pada opsi chart:
             *
             * options: {
             *   onClick: function (evt, elements, chart) {
             *     if (!elements.length) return;
             *     var i = elements[0].index;
             *     var ds = elements[0].datasetIndex;
             *     var label = chart.data.labels[i];
             *     var cycle = chart.data.datasets[ds].label;
             *     var params = new URLSearchParams();
             *     if (label) params.set('customer', label);
             *     if (cycle) params.set('cycle', cycle);
             *     if (deliveryDateFromFilter) params.set('delivery_date', deliveryDateFromFilter);
             *     window.location.href = loadingListUrl + '?' + params.toString();
             *   }
             * }
             */

            function buildGanttTooltip(row) {
                var et = (row.truck_time != null && String(row.truck_time).length) ? String(row.truck_time).substring(0, 5) : '-';
                var prep = (row.cycle_time != null && String(row.cycle_time).trim().length) ? String(row.cycle_time).substring(0, 5) : '-';
                var prepEnd = (row.prep_end_time != null && String(row.prep_end_time).trim().length) ? String(row.prep_end_time).substring(0, 5) : '-';
                if (prepEnd !== '-' && prep !== '-' && prepEnd === prep) {
                    prepEnd = '-';
                }
                var lines = [
                    'Cycle: ' + row.cycle_name + ' | Prep @ ' + prep + ' - ' + prepEnd + (et !== '-' ? ' | ETA truck @ ' + et : ''),
                    'Jumlah PDS: ' + row.ll_count,
                    'Persentase progress: ' + row.progress_pct + '%',
                    'Target: ' + row.total_target,
                    'Done: ' + row.total_done
                ];
                return lines.join(' | ');
            }

            function mergeRowsForCustomerTime(rows) {
                var map = {};
                rows.forEach(function (r) {
                    var key = String(r.customer_id) + '|' + String(r.cycle_name) + '|' + String(r.cycle_time);
                    if (!map[key]) {
                        map[key] = {
                            customer_id: r.customer_id,
                            customer_name: r.customer_name,
                            cycle_time: r.cycle_time,
                            prep_end_time: (r.prep_end_time != null && String(r.prep_end_time).trim().length) ? r.prep_end_time : null,
                            truck_time: (r.truck_time != null && r.truck_time !== '') ? r.truck_time : null,
                            cycle_name: r.cycle_name,
                            on_time: 0,
                            delay: 0,
                            no_order: 0,
                            ll_count: 0,
                            total_target: 0,
                            total_done: 0,
                            progress_pct: 0,
                            mapping_source: r.mapping_source
                        };
                    }
                    var m = map[key];
                    m.on_time += r.on_time;
                    m.delay += r.delay;
                    m.no_order += r.no_order;
                    m.ll_count += r.ll_count;
                    m.total_target += r.total_target;
                    m.total_done += r.total_done;
                });
                return Object.keys(map).map(function (k) {
                    var m = map[k];
                    // Samakan dengan loading list: progress = total_done / total_target.
                    var pct = m.total_target > 0 ? (m.total_done / m.total_target) * 100 : 0;
                    m.progress_pct = Math.round(Math.max(0, Math.min(100, pct)) * 10) / 10;
                    return m;
                });
            }

            function updateTimelineTotal() {
                var sum = chartMergedByCustTime.reduce(function (acc, r) {
                    return acc + (parseInt(r.total_done, 10) || 0);
                }, 0);
                $('#timelineTotalSum').text('Total: ' + sum);
            }

            function getDayRangeInclusive(fromDate, toDate) {
                var from = String(fromDate || '').trim();
                var to = String(toDate || '').trim();
                if (!from.length && !to.length) {
                    return 0;
                }
                if (!to.length) {
                    to = from;
                }
                if (!from.length) {
                    from = to;
                }
                var dFrom = new Date(from + 'T00:00:00');
                var dTo = new Date(to + 'T00:00:00');
                if (isNaN(dFrom.getTime()) || isNaN(dTo.getTime())) {
                    return 0;
                }
                var minMs = Math.min(dFrom.getTime(), dTo.getTime());
                var maxMs = Math.max(dFrom.getTime(), dTo.getTime());
                return Math.floor((maxMs - minMs) / 86400000) + 1;
            }

            function detectDashboardViewMode() {
                var daySpan = getDayRangeInclusive($('#filterDateFrom').val(), $('#filterDateTo').val());
                return daySpan <= 1 ? 'daily' : 'weekly';
            }

            function applyDashboardViewMode(mode) {
                dashboardViewMode = mode;
                var isDaily = mode === 'daily';
                $('#deliveryTimelineCard').toggleClass('d-none', !isDaily);
                $('#deliveryWeeklyCard').toggleClass('d-none', isDaily);
                $('.timeline-title').text(isDaily ? 'Timeline delivery' : 'Timeline delivery (ringkas)');
            }

            function buildWeeklyTooltip(details) {
                if (!details.length) {
                    return 'Tidak ada detail cycle.';
                }
                return details.map(function (d) {
                    return [
                        'Cycle ' + d.cycle_name,
                        'Prep ' + d.prep_start + (d.prep_end !== '-' ? ' - ' + d.prep_end : ''),
                        'ETA ' + d.truck_time,
                        'Progress ' + d.progress_pct + '%'
                    ].join(' | ');
                }).join(' || ');
            }

            function destroyWeeklyChart() {
                if (weeklyNowTimer) {
                    clearInterval(weeklyNowTimer);
                    weeklyNowTimer = null;
                }
                if (weeklyChartInstance) {
                    weeklyChartInstance.destroy();
                    weeklyChartInstance = null;
                }
                $('#weeklyGanttContainer').empty();
            }

            function buildWeeklyDailyAnnotations(startDateMs, endDateMs) {
                var out = [];
                if (!startDateMs || !endDateMs) {
                    return out;
                }
                var cursor = new Date(startDateMs);
                cursor.setHours(0, 0, 0, 0);
                var limit = new Date(endDateMs);
                limit.setHours(23, 59, 59, 999);

                while (cursor.getTime() <= limit.getTime()) {
                    out.push({
                        x: cursor.getTime(),
                        borderColor: '#d9dde3',
                        strokeDashArray: 4,
                        label: {
                            text: cursor.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short' }),
                            style: {
                                color: '#555',
                                background: '#f8f9fa',
                                fontSize: '10px'
                            }
                        }
                    });
                    cursor.setDate(cursor.getDate() + 1);
                }
                return out;
            }

            function buildWeeklyNowAnnotation() {
                return {
                    x: Date.now(),
                    borderColor: '#FF0000',
                    strokeDashArray: 0,
                    label: {
                        text: 'Now ' + new Date().toLocaleTimeString('id-ID'),
                        style: {
                            color: '#fff',
                            background: '#FF0000',
                            fontSize: '10px'
                        }
                    }
                };
            }

            function startWeeklyNowTicker(dailyAnnotations) {
                if (weeklyNowTimer) {
                    clearInterval(weeklyNowTimer);
                    weeklyNowTimer = null;
                }
                weeklyNowTimer = setInterval(function () {
                    if (!weeklyChartInstance) {
                        return;
                    }
                    weeklyChartInstance.updateOptions({
                        annotations: {
                            xaxis: [buildWeeklyNowAnnotation()].concat(dailyAnnotations || [])
                        }
                    }, false, false);
                }, 1000);
            }

            function renderWeeklySummary() {
                if (!chartWeeklyPoints.length) {
                    destroyWeeklyChart();
                    $('#weeklyGanttContainer').html('<div class="text-muted small">Tidak ada data untuk ditampilkan.</div>');
                } else {
                    var dataPoints = [];
                    chartWeeklyPoints.forEach(function (row) {
                        var startMs = new Date(row.start_at).getTime();
                        var endMs = new Date(row.end_at).getTime();
                        if (isNaN(startMs) || isNaN(endMs) || endMs <= startMs) {
                            return;
                        }
                        var roundedPct = Math.round(parseFloat(row.progress_pct || 0) * 10) / 10;
                        var prepStart = row.prep_time ? String(row.prep_time).substring(0, 5) : '-';
                        var prepEnd = row.prep_end_time ? String(row.prep_end_time).substring(0, 5) : '-';
                        var truckTime = row.truck_time ? String(row.truck_time).substring(0, 5) : '-';
                        var detailText = [
                            'Tanggal ' + (row.delivery_date || '-'),
                            'Cycle ' + (row.cycle_name || '-'),
                            'Prep ' + prepStart + (prepEnd !== '-' ? ' - ' + prepEnd : ''),
                            'ETA ' + truckTime,
                            'Progress ' + roundedPct + '%'
                        ].join(' | ');
                        dataPoints.push({
                            x: row.customer_name || '-',
                            y: [startMs, endMs],
                            meta: {
                                total_done: parseInt(row.total_done || 0, 10),
                                total_target: parseInt(row.total_target || 0, 10),
                                progress_pct: roundedPct,
                                details_text: detailText
                            }
                        });
                    });

                    destroyWeeklyChart();
                    var dateFrom = $('#filterDateFrom').val() || '';
                    var dateTo = $('#filterDateTo').val() || dateFrom;
                    var startDateMs = dateFrom ? new Date(dateFrom + 'T00:00:00').getTime() : null;
                    var endDateMs = dateTo ? new Date(dateTo + 'T23:59:59').getTime() : null;
                    var options = {
                        chart: {
                            type: 'rangeBar',
                            height: Math.max(460, Math.min(980, dataPoints.length * 30)),
                            toolbar: {
                                show: false,
                                tools: {
                                    download: false,
                                    selection: false,
                                    zoom: false,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: false,
                                    reset: false
                                }
                            },
                            zoom: {
                                enabled: false
                            },
                            selection: {
                                enabled: false
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '52%',
                                distributed: false,
                                rangeBarGroupRows: true
                            }
                        },
                        colors: [function (ctx) {
                            var point = ctx.w.config.series[ctx.seriesIndex].data[ctx.dataPointIndex];
                            var pct = point && point.meta ? parseFloat(point.meta.progress_pct || 0) : 0;
                            return pct >= 100 ? '#C0DD97' : 'hsl(58, 100%, 70%)';
                        }],
                        series: [{
                            name: 'Progress',
                            data: dataPoints
                        }],
                        xaxis: {
                            type: 'datetime',
                            min: startDateMs || undefined,
                            max: endDateMs || undefined,
                            labels: {
                                datetimeUTC: false
                            }
                        },
                        yaxis: {
                            labels: {
                                maxWidth: 260
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        tooltip: {
                            custom: function (ctx) {
                                var point = ctx.w.config.series[ctx.seriesIndex].data[ctx.dataPointIndex];
                                var meta = point.meta || {};
                                return '<div class="px-2 py-1 text-sm">' +
                                    '<strong>' + escapeHtml(point.x) + '</strong><br/>' +
                                    'Done: ' + (meta.total_done || 0) + ' / ' + (meta.total_target || 0) + '<br/>' +
                                    escapeHtml(meta.details_text || '-') +
                                    '</div>';
                            }
                        },
                        grid: {
                            borderColor: '#eef0f3'
                        },
                        annotations: {
                            xaxis: (function () {
                                var daily = buildWeeklyDailyAnnotations(startDateMs, endDateMs);
                                return [buildWeeklyNowAnnotation()].concat(daily);
                            })()
                        },
                        legend: { show: false }
                    };
                    weeklyChartInstance = new ApexCharts(document.querySelector('#weeklyGanttContainer'), options);
                    weeklyChartInstance.render().then(function () {
                        startWeeklyNowTicker(buildWeeklyDailyAnnotations(startDateMs, endDateMs));
                    });
                }
                updateTimelineTotal();
            }

            function renderGantt() {
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }

                var slots = slotLabels24();
                var gridHtml = '<div class="gantt-grid-scroll" id="ganttTimelineScroll"><div class="gantt-grid">';
                gridHtml += '<div class="gantt-grid-row gantt-grid-header">';
                gridHtml += '<div class="gantt-grid-cell gantt-grid-customer gantt-grid-sticky-col gantt-grid-sticky-customer">Customer</div>';
                gridHtml += '<div class="gantt-grid-cell gantt-grid-type gantt-grid-sticky-col gantt-grid-sticky-type">Type</div>';
                slots.forEach(function (s) {
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-time">' + s + '</div>';
                });
                gridHtml += '</div>';

                var nowPct = getNowLeftPct();
                var nowDate = new Date();
                nowDate.setMinutes(nowDate.getMinutes() + (etaWindowSettings.eta_offset_hours * 60));
                var nowLbl = formatClockDot(nowDate.toTimeString());
                var truckPct = getTruckArrivalLeftPct();
                var truckDate = new Date();
                truckDate.setMinutes(truckDate.getMinutes() + (etaWindowSettings.finish_offset_hours * 60));
                var truckLbl = formatClockDot(truckDate.toTimeString());
                var fillLeft = Math.min(nowPct, truckPct);
                var fillWidth = Math.abs(truckPct - nowPct);
                var currentClock = String(new Date().getHours()).padStart(2, '0') + ':' + String(new Date().getMinutes()).padStart(2, '0');
                var currentFrac = timeToFrac(currentClock);

                chartCustomerOrder.forEach(function (cust, custIdx) {
                    var buckets = chartMergedByCustTime.filter(function (m) {
                        return m.customer_name === cust;
                    });
                    buckets.sort(function (a, b) {
                        var af = timeToFrac(a.cycle_time);
                        var bf = timeToFrac(b.cycle_time);
                        if (af === null && bf === null) {
                            return String(a.cycle_name || '').localeCompare(String(b.cycle_name || ''));
                        }
                        if (af === null) {
                            return 1;
                        }
                        if (bf === null) {
                            return -1;
                        }
                        return af - bf;
                    });

                    gridHtml += '<div class="gantt-grid-row">';
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-customer gantt-grid-left-cell gantt-grid-sticky-col gantt-grid-sticky-customer">' + escapeHtml(cust) + '</div>';
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-type gantt-grid-left-cell gantt-grid-type-split gantt-grid-sticky-col gantt-grid-sticky-type">';
                    gridHtml += '<div class="gantt-type-lane is-prep">PREP</div>';
                    gridHtml += '<div class="gantt-type-lane is-truck">ETA TRUCK</div>';
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-track-cell"><div class="gantt-grid-track-split">';
                    gridHtml += '<div class="gantt-grid-track-lane"><div class="gantt-track">';
                    gridHtml += '<div class="gantt-window-fill" style="left:' + fillLeft + '%;width:' + fillWidth + '%"></div>';

                    buckets.forEach(function (row) {
                        var start = timeToWindowFrac(row.cycle_time);
                        if (start === null) {
                            return;
                        }
                        var prepStartClock = row.cycle_time;
                        var prepEndClockRaw = (row.prep_end_time != null && String(row.prep_end_time).trim().length) ? String(row.prep_end_time) : null;
                        var prepEndClock = prepEndClockRaw || prepStartClock;
                        var durHours = calcDurationHours(row.cycle_time, prepEndClock);
                        var leftPct = (start / ganttHourWindow) * 100;
                        var hasPrepEndSetting = prepEndClockRaw !== null && prepEndClockRaw.substring(0, 5) !== String(prepStartClock || '').substring(0, 5);
                        var startMin = timeToMinutes(prepStartClock);
                        var endMin = timeToMinutes(prepEndClock);
                        var isOvernightPrep = hasPrepEndSetting && endMin < startMin;
                        var isInstantPrep = !hasPrepEndSetting || durHours <= 0;
                        var widthPct = isInstantPrep ? 0 : Math.max((durHours / ganttHourWindow) * 100, 0.8);
                        if (isOvernightPrep) {
                            // Untuk prep lintas hari, tampilkan indikator ringkas (tidak memanjang melewati timeline).
                            widthPct = 0;
                        }
                        var progressWidth = Math.max(0, Math.min(100, row.progress_pct || 0));
                        var isOverdue = (progressWidth < 100) && isRowPastFinishPrep(row, currentFrac);
                        var barClass = progressWidth < 100 ? (isOverdue ? 'overdue' : 'delay') : 'ontime';
                        var tip = buildGanttTooltip(row).replace(/"/g, '&quot;');
                        if (isOvernightPrep) {
                            tip += ' | Prep lintas hari: berlanjut ke hari berikutnya sampai ' + formatClockDot(prepEndClock);
                        }
                        if (isOverdue) {
                            tip += ' | Status: Melewati finish preparation';
                        }
                        var dateFromVal = $('#filterDateFrom').val() || '';
                        var dateToVal = $('#filterDateTo').val() || '';
                        var barTitle = tip + ' — Klik untuk buka Loading List';
                        var av = String(row.cycle_name != null ? row.cycle_name : '').trim();
                        if (!av.length) {
                            av = '?';
                        }
                        av = 'C' + av.toUpperCase();
                        var prepExtraClass = '';
                        if (isOvernightPrep) {
                            prepExtraClass += ' is-overnight';
                        } else if (isInstantPrep) {
                            prepExtraClass += ' is-instant';
                        }
                        gridHtml += '<div class="gantt-bar-wrap' + prepExtraClass + '" style="left:' + leftPct + '%;width:' + widthPct + '%" title="' + barTitle + '"';
                        gridHtml += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        gridHtml += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        gridHtml += ' data-delivery-date-from="' + escapeAttr(dateFromVal) + '"';
                        gridHtml += ' data-delivery-date-to="' + escapeAttr(dateToVal) + '">';
                        gridHtml += '<div class="gantt-bar-stack">';
                        if (progressWidth >= 100) {
                            gridHtml += '<span class="gantt-seg ontime" style="width:100%"></span>';
                        } else {
                            gridHtml += '<span class="gantt-seg ' + barClass + '" style="width:' + progressWidth + '%"></span>';
                            gridHtml += '<span class="gantt-seg gantt-trail" style="width:' + (100 - progressWidth) + '%"></span>';
                        }
                        gridHtml += '</div>';
                        gridHtml += '<span class="pill-meta"><span class="pill-avatar">' + escapeHtml(av) + '</span></span>';
                        gridHtml += '</div>';
                    });

                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-truck-marker" style="left:' + truckPct + '%">';
                    if (custIdx === 0) {
                        gridHtml += '<span class="gantt-truck-label">ETA TRUCK ' + escapeHtml(truckLbl) + '</span>';
                    }
                    gridHtml += '<div class="gantt-truck-line"></div></div>';
                    gridHtml += '<div class="gantt-now-marker" style="left:' + nowPct + '%">';
                    if (custIdx === 0) {
                        gridHtml += '<span class="gantt-now-label">Finish Preparation ' + escapeHtml(nowLbl) + '</span>';
                    }
                    gridHtml += '<div class="gantt-now-line"></div></div>';
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-grid-track-lane"><div class="gantt-track">';
                    buckets.forEach(function (row) {
                        var truckAt = (row.truck_time != null && String(row.truck_time).length) ? row.truck_time : null;
                        if (!truckAt) {
                            return;
                        }
                        var truckStart = timeToWindowFrac(truckAt);
                        if (truckStart === null) {
                            return;
                        }
                        var truckLeftPct = (truckStart / ganttHourWindow) * 100;
                        var truckComplete = (parseFloat(row.progress_pct || 0) >= 100);
                        var truckSegClass = truckComplete ? 'truck-complete' : 'truck';
                        var truckTitle = buildGanttTooltip(row).replace(/"/g, '&quot;') + ' — Klik untuk buka Loading List';
                        var dateFromVal = $('#filterDateFrom').val() || '';
                        var dateToVal = $('#filterDateTo').val() || '';
                        var truckLabel = 'C' + escapeHtml(String(row.cycle_name || '?'));
                        gridHtml += '<div class="gantt-bar-wrap truck-pill" style="left:' + truckLeftPct + '%" title="' + truckTitle + '"';
                        gridHtml += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        gridHtml += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        gridHtml += ' data-delivery-date-from="' + escapeAttr(dateFromVal) + '"';
                        gridHtml += ' data-delivery-date-to="' + escapeAttr(dateToVal) + '">';
                        gridHtml += '<div class="gantt-bar-stack">';
                        gridHtml += '<span class="gantt-seg ' + truckSegClass + '" style="width:100%"></span>';
                        gridHtml += '</div>';
                        gridHtml += '<span class="pill-meta"><span class="pill-avatar">' + truckLabel + '</span></span>';
                        gridHtml += '</div>';
                    });
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-truck-marker" style="left:' + truckPct + '%"><div class="gantt-truck-line"></div></div>';
                    gridHtml += '<div class="gantt-now-marker" style="left:' + nowPct + '%"><div class="gantt-now-line"></div></div>';
                    gridHtml += '</div></div></div>';
                });

                gridHtml += '</div></div>';
                $('#ganttContainer').html(gridHtml);

                updateTimelineTotal();

                ganttNowTimer = setInterval(updateGanttNowMarkers, 1000);
                updateGanttNowMarkers();
            }

            $('#ganttContainer').on('click', '.gantt-bar-wrap', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var customer = $(this).attr('data-customer-name') || '';
                var cycle = $(this).attr('data-cycle') || '';
                var deliveryDateFrom = $(this).attr('data-delivery-date-from') || '';
                var deliveryDateTo = $(this).attr('data-delivery-date-to') || '';
                var params = new URLSearchParams();
                if (customer) {
                    params.set('customer', customer);
                }
                if (cycle) {
                    params.set('cycle', cycle);
                }
                if (deliveryDateFrom) {
                    params.set('delivery_date_from', deliveryDateFrom);
                    params.set('delivery_date', deliveryDateFrom);
                }
                if (deliveryDateTo) {
                    params.set('delivery_date_to', deliveryDateTo);
                }
                var qs = params.toString();
                window.location.href = loadingListUrl + (qs ? '?' + qs : '');
            });

            function loadStackedChart() {
                var dateFrom = $('#filterDateFrom').val() || '';
                var dateTo = $('#filterDateTo').val() || '';
                var params = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    date: dateFrom,
                    customer_id: $('#filterCustomer').val() || ''
                };

                $('#chartEmpty').addClass('d-none');
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }
                $('#ganttContainer').empty();
                destroyWeeklyChart();

                $.get(stackedUrl, params, function (res) {
                    chartRows = res.rows || [];
                    chartWeeklyPoints = res.weekly_points || [];
                    applyDashboardViewMode(detectDashboardViewMode());
                    var hint = (res.meta && res.meta.hint) ? res.meta.hint : '';
                    if (!chartRows.length) {
                        var msg = hint || 'Tidak ada data delivery untuk filter ini.';
                        $('#chartEmpty').removeClass('d-none').text(msg);
                        $('#timelineTotalSum').text('Total: 0');
                        return;
                    }

                    chartMergedByCustTime = mergeRowsForCustomerTime(chartRows);

                    var custSet = {};
                    chartMergedByCustTime.forEach(function (m) {
                        custSet[m.customer_name] = true;
                    });
                    chartCustomerOrder = Object.keys(custSet).sort(function (a, b) {
                        return a.localeCompare(b);
                    });

                    if (dashboardViewMode === 'daily') {
                        renderGantt();
                    } else {
                        renderWeeklySummary();
                    }
                    checkAndSendUnfinishedWaNotification();
                }).fail(function () {
                    $('#chartEmpty').removeClass('d-none').text('Gagal memuat Gantt.');
                    $('#timelineTotalSum').text('Total: 0');
                });
            }

            $('#btnReloadChart').on('click', loadStackedChart);
            $('#filterDateFrom, #filterDateTo, #filterCustomer').on('change', loadStackedChart);
            $('#etaWindowModalForm').on('submit', function (e) {
                e.preventDefault();
                etaWindowSettings.eta_offset_hours = normalizeOffsetHours($('#etaOffsetHours').val(), 0);
                etaWindowSettings.finish_offset_hours = normalizeOffsetHours($('#finishOffsetHours').val(), 4);
                saveEtaWindowSettings();
                renderEtaWindowFormState();
                $('#etaWindowModal').modal('hide');
                updateGanttNowMarkers();
                alert('Pengaturan rentang ETA TRUCK dan Finish Preparation berhasil disimpan.');
            });

            $('#etaWindowModal').on('show.bs.modal', function () {
                renderEtaWindowFormState();
            });

            // Default ke hari ini (dari & sampai sama) agar tampilan awal mode harian.
            if (!$('#filterDateFrom').val()) {
                var today = new Date().toISOString().slice(0, 10);
                $('#filterDateFrom').val(today);
                $('#filterDateTo').val(today);
            }

            function renderMasterTable(rows) {
                var $tb = $('#masterCycleTable tbody');
                $tb.empty();
                if (!rows.length) {
                    $tb.append('<tr><td colspan="6" class="text-center text-muted">Belum ada master cycle.</td></tr>');
                    return;
                }
                rows.forEach(function (r, i) {
                    var cust = r.customer_name || (r.customer_id ? '#' + r.customer_id : '-');
                    var prepStart = formatClockDot(r.time);
                    var prepEnd = (r.prep_end_time && String(r.prep_end_time).length) ? formatClockDot(r.prep_end_time) : '-';
                    if (prepEnd === prepStart) {
                        prepEnd = '-';
                    }
                    var prepRangeDisp = prepStart + ' - ' + prepEnd;
                    var truckDisp = (r.truck_time && String(r.truck_time).length) ? formatClockDot(r.truck_time) : '<span class="text-muted">-</span>';
                    $tb.append(
                        '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + cust + '</td>' +
                        '<td>' + r.cycle_name + '</td>' +
                        '<td>' + prepRangeDisp + '</td>' +
                        '<td>' + truckDisp + '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary py-0 btn-edit-master" data-id="' + r.id + '">Edit</button> ' +
                        '<button type="button" class="btn btn-sm btn-outline-danger py-0 btn-del-master" data-id="' + r.id + '">Hapus</button>' +
                        '</td>' +
                        '</tr>'
                    );
                });
            }

            function fetchMasters() {
                $.get(masterIndex, function (res) {
                    var rows = (res && res.data) ? res.data : [];
                    renderMasterTable(rows);
                });
            }

            $('#masterCycleForm').on('submit', function (e) {
                e.preventDefault();
                var prepStart = ($('#mcyclePrepStart').val() || '').trim();
                var prepEnd = ($('#mcyclePrepEnd').val() || '').trim();
                var etaTruck = ($('#mcycleTruckTime').val() || '').trim();
                if (!prepStart || !prepEnd || !etaTruck) {
                    alert('Waktu preparation (mulai-selesai) dan ETA truck wajib diisi.');
                    return;
                }
                var payload = {
                    _token: csrf,
                    cycle_name: $('#mcycleName').val().trim(),
                    time: prepStart,
                    prep_end_time: prepEnd,
                    truck_time: etaTruck,
                    customer_id: $('#mcycleCustomerId').val()
                };

                if (editMasterId) {
                    $.ajax({
                        url: masterBase + '/' + editMasterId,
                        type: 'PUT',
                        data: payload,
                        success: function () {
                            editMasterId = null;
                            $('#btnMasterSave').text('Simpan');
                            $('#btnMasterCancel').addClass('d-none');
                            $('#masterCycleForm')[0].reset();
                            fetchMasters();
                            loadStackedChart();
                        }
                    });
                } else {
                    $.post(masterStore, payload, function () {
                        $('#masterCycleForm')[0].reset();
                        fetchMasters();
                        loadStackedChart();
                    });
                }
            });

            $('#btnMasterCancel').on('click', function () {
                editMasterId = null;
                $('#btnMasterSave').text('Simpan');
                $('#btnMasterCancel').addClass('d-none');
                $('#masterCycleForm')[0].reset();
            });

            $('#masterCycleTable').on('click', '.btn-del-master', function () {
                var id = $(this).data('id');
                if (!confirm('Hapus master cycle ini?')) {
                    return;
                }
                $.ajax({
                    url: masterBase + '/' + id,
                    type: 'DELETE',
                    data: { _token: csrf },
                    success: function () {
                        fetchMasters();
                        loadStackedChart();
                    }
                });
            });

            $('#masterCycleTable').on('click', '.btn-edit-master', function () {
                var id = $(this).data('id');
                $.get(masterIndex, function (res) {
                    var rows = (res && res.data) ? res.data : [];
                    var row = rows.find(function (r) { return String(r.id) === String(id); });
                    if (!row) {
                        return;
                    }
                    editMasterId = id;
                    // cycle dropdown: fallback jika data lama bukan 1-5
                    if ($('#mcycleName option[value="' + row.cycle_name + '"]').length) {
                        $('#mcycleName').val(row.cycle_name);
                    } else {
                        if ($('#mcycleName option[value=""]').length) {
                            $('#mcycleName').val('');
                        }
                    }
                    var prepStart = row.time.length === 5 ? row.time : row.time.substring(0, 5);
                    var prepEnd = row.prep_end_time
                        ? (row.prep_end_time.length === 5 ? row.prep_end_time : row.prep_end_time.substring(0, 5))
                        : '';
                    if (prepEnd === prepStart) {
                        prepEnd = '';
                    }
                    var etaTruck = row.truck_time
                        ? (row.truck_time.length === 5 ? row.truck_time : row.truck_time.substring(0, 5))
                        : '';
                    $('#mcyclePrepStart').val(prepStart);
                    $('#mcyclePrepEnd').val(prepEnd);
                    $('#mcycleTruckTime').val(etaTruck);
                    $('#mcycleCustomerId').val(row.customer_id != null ? String(row.customer_id) : '');
                    $('#btnMasterSave').text('Update');
                    $('#btnMasterCancel').removeClass('d-none');
                });
            });

            renderHourDropdownOptions('#etaOffsetHours');
            renderHourDropdownOptions('#finishOffsetHours');
            loadEtaWindowSettings();
            renderEtaWindowFormState();
            fetchMasters();
            loadStackedChart();
            waNotifyTimer = setInterval(function () {
                checkAndSendUnfinishedWaNotification();
                if (dashboardViewMode === 'daily') {
                    renderGantt();
                }
            }, 60000);

            tickDeliveryHeaderClock();
            setInterval(tickDeliveryHeaderClock, 1000);
        });
    </script>

    <div class="modal fade" id="etaWindowModal" tabindex="-1" role="dialog" aria-labelledby="etaWindowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="etaWindowModalForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="etaWindowModalLabel">Setting Rentang</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="mb-1" style="font-size: 11px;">ETA TRUCK (jam dari sekarang)</label>
                            <select class="form-control form-control-sm" id="etaOffsetHours" required></select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="mb-1" style="font-size: 11px;">Finish Preparation (jam dari sekarang)</label>
                            <select class="form-control form-control-sm" id="finishOffsetHours" required></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan rentang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
