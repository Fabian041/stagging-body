@extends('layouts.root.minimal')

@section('main')
    <style>
        /* ===================================================
                     * BELLA DESIGN SYSTEM — Shell overrides for this page
                     * =================================================== */

        /* ── Page wrapper ── */
        .delivery-dash {
            font-size: 14px;
            color: var(--text);
        }

        /* ── Filter bar (reuse bella-filter-card pattern) ── */
        .bella-filter-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 20px 24px 16px;
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
        }

        .bella-filter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--navy), var(--sky));
            border-radius: 16px 16px 0 0;
        }

        .bella-filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .bella-filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .bella-filter-label {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .bella-filter-select,
        .bella-filter-input {
            height: 34px;
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            background: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12.5px;
            padding: 0 10px;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
            min-width: 150px;
        }

        .bella-filter-select:focus,
        .bella-filter-input:focus {
            border-color: var(--sky);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
        }

        /* ── Page header card ── */
        .bella-page-header {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 16px 24px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .bella-page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--navy), var(--sky));
            border-radius: 12px 12px 0 0;
        }

        .bella-page-header-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: .04em;
            text-transform: uppercase;
            margin: 0;
        }

        .bella-page-header-clock {
            text-align: right;
        }

        .bella-page-header-clock .clock-label {
            font-size: 10px;
            color: var(--text-muted);
            display: block;
            margin-bottom: 2px;
        }

        .bella-page-header-clock #deliveryDashLiveTime {
            font-size: 16px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--danger, #dc2626);
        }

        /* ── Tabs ── */
        .bella-tabs {
            display: flex;
            gap: 4px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 0;
            padding: 0 4px;
            background: var(--card);
        }

        .bella-tabs .nav-item {
            list-style: none;
        }

        .bella-tabs .nav-link {
            display: inline-block;
            padding: 9px 16px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color .15s, border-color .15s;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .bella-tabs .nav-link:hover {
            color: var(--navy);
        }

        .bella-tabs .nav-link.active {
            color: var(--navy);
            border-bottom-color: var(--sky);
        }

        .bella-tab-content {
            background: var(--bg);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding: 16px;
        }

        /* ── Tab card wrapper ── */
        .bella-content-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 12px;
        }

        /* ── Master Cycle form ── */
        .bella-form-label {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            display: block;
            margin-bottom: 4px;
        }

        .bella-form-control {
            height: 32px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            padding: 0 8px !important;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
            width: 100%;
        }

        .bella-form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        /* ── Master Cycle table ── */
        .bella-master-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .bella-master-table thead th {
            padding: 8px 12px;
            color: var(--text-muted);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .bella-master-table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text);
        }

        .bella-master-table tbody tr:last-child td {
            border-bottom: none;
        }

        .bella-master-table tbody tr:hover td {
            background: var(--bg);
        }

        .bella-master-table tbody tr.is-master-highlight td {
            background: rgba(0, 151, 216, .14) !important;
            transition: background .4s ease;
            animation: masterRowHighlightPulse 1.2s ease-out;
        }

        @keyframes masterRowHighlightPulse {
            0% {
                background: rgba(0, 151, 216, .32);
            }

            100% {
                background: rgba(0, 151, 216, .14);
            }
        }

        /* ── ETA info pill ── */
        .bella-info-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 99px;
            font-size: 11.5px;
            color: var(--text-muted);
        }

        /* ── Modal ── */
        .modal-content {
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-md) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            overflow: hidden !important;
        }

        .modal-header {
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
            padding: 14px 20px !important;
        }

        .modal-title {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: var(--navy) !important;
        }

        .modal-header .close {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border);
            border-radius: 5px;
            background: var(--card);
            opacity: 1 !important;
            color: var(--text-muted) !important;
            font-size: 16px !important;
            line-height: 26px;
            padding: 0;
            transition: .15s;
        }

        .modal-header .close:hover {
            background: var(--danger-light) !important;
            color: var(--danger) !important;
            border-color: #fecaca !important;
        }

        .modal-body {
            padding: 16px 20px !important;
            background: var(--bg) !important;
        }

        .modal-footer {
            border-top: 1px solid var(--border) !important;
            padding: 12px 20px !important;
            background: var(--card) !important;
        }

        /* ── Empty alert ── */
        #chartEmpty {
            background: var(--bg) !important;
            border: 1px solid var(--border) !important;
            border-radius: 8px !important;
            color: var(--text-muted) !important;
            font-size: 12.5px !important;
        }

        /* ── Legend ── */
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
            border: 0.5px solid var(--border);
            border-radius: 2px;
        }

        /* ====================================================
                     * GANTT CHART — kept exactly as original, do not touch
                     * ==================================================== */
        :root {
            --dm-bg: var(--bs-body-bg, #ffffff);
            --dm-card: var(--bs-light, #f8f9fa);
            --dm-border: color-mix(in srgb, var(--dm-bg) 70%, #6c757d 30%);
            --dm-text: var(--bs-body-color, #2f3542);
            --dm-muted: #6c757d;
            --dm-blue: #B5D4F4;
            --dm-yellow: hsl(58, 100%, 70%);
            --dm-green: #C0DD97;
            --dm-complete: rgb(180, 255, 198);
            --dm-pink: #F7C1C1;
            --gantt-visible-hours: 6;
            --gantt-hour-width: 120px;
            --gantt-customer-col-width: 200px;
            --gantt-type-col-width: 120px;
            --gantt-left-cols: calc(var(--gantt-customer-col-width) + var(--gantt-type-col-width));
            --gantt-slot-count: 24;
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

        .gantt-wrap {
            overflow: auto;
            border: 0.5px solid var(--dm-border);
            border-radius: 6px;
            max-height: 86vh;
            width: 100%;
            max-width: 100%;
        }

        .gantt-table {
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
            height: 64px;
            vertical-align: middle;
            border: 0.5px solid var(--dm-border);
            overflow: visible;
        }

        .gantt-track {
            position: relative;
            height: 40px;
            margin: 18px 4px 6px 4px;
            border-radius: 2px;
            background-color: transparent;
            overflow: visible;
            background-image: linear-gradient(to right, var(--dm-border) 0, var(--dm-border) 1px, transparent 1px, transparent 100%);
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

        .gantt-bar-wrap.is-overnight .gantt-bar-stack {
            border: 2px dashed #f39c12;
            background: rgba(243, 156, 18, 0.12);
            box-shadow: none;
        }

        .gantt-bar-wrap.is-prep-clamped .gantt-bar-stack {
            border-right: 3px solid #e67e22;
        }

        .gantt-bar-wrap.is-prep-clamped.is-continues-tomorrow::after {
            content: 'esok hari';
            position: absolute;
            right: 2px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8px;
            font-weight: 700;
            color: #fff;
            background: #e67e22;
            padding: 1px 4px;
            border-radius: 2px;
            line-height: 1.2;
            white-space: nowrap;
            pointer-events: none;
            z-index: 3;
        }

        .gantt-bar-wrap.is-prep-from-yesterday {
            opacity: 0.9;
            z-index: 1;
        }

        .gantt-bar-wrap.is-prep-from-yesterday .gantt-bar-stack {
            border: 2px dotted #5c7f2b;
            background: rgba(92, 127, 43, 0.15);
        }

        .gantt-bar-wrap.is-prep-from-yesterday::before,
        .gantt-bar-wrap.is-cross-day-continuation::before {
            content: var(--cross-day-label, 'lanjutan');
            position: absolute;
            left: 2px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8px;
            font-weight: 700;
            color: #fff;
            background: #5c7f2b;
            padding: 1px 4px;
            border-radius: 2px;
            line-height: 1.2;
            white-space: nowrap;
            pointer-events: none;
            z-index: 3;
            max-width: calc(100% - 4px);
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gantt-bar-wrap.truck-pill {
            top: 7px;
            height: 26px;
            width: 44px !important;
            min-width: 44px;
        }

        .gantt-bar-wrap.truck-pill.is-cross-day {
            border: 2px dashed #b86f00;
            box-shadow: 0 0 0 1px rgba(184, 111, 0, 0.15);
        }

        .gantt-bar-wrap.truck-pill.is-offset-h-minus {
            border-left: 4px solid #3b82f6;
        }

        .gantt-bar-wrap.truck-pill.is-offset-h-zero {
            border-left: 4px solid #22c55e;
        }

        .gantt-bar-wrap.truck-pill.is-offset-h-plus {
            border-left: 4px solid #f59e0b;
        }

        .delivery-timeline-card .gantt-grid-time.is-next-day-col {
            background: rgba(184, 111, 0, 0.06);
            color: #9a5f00;
        }

        /* Timeline mingguan: satu track lintas kolom hari, bar boleh melewati garis antarhari */
        .weekly-prep-track {
            position: relative;
            overflow: visible;
        }

        .weekly-prep-track .weekly-day-column {
            position: absolute;
            top: 0;
            bottom: 0;
            border-right: 1px solid #eef0f3;
            box-sizing: border-box;
            pointer-events: none;
        }

        .weekly-prep-track .weekly-day-column.is-today {
            background: rgba(255, 235, 235, 0.35);
        }

        .weekly-prep-bar {
            position: absolute;
            top: 6px;
            height: 26px;
            min-width: 48px;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
            cursor: pointer;
            box-sizing: border-box;
            z-index: 2;
            display: flex;
            align-items: center;
            padding: 0 6px;
            overflow: visible;
        }

        .weekly-prep-bar.is-cross-day {
            border: 2px dashed #f39c12;
            z-index: 3;
        }

        .weekly-prep-bar.is-offset-h-minus {
            border-left: 3px solid #3b82f6;
        }

        .weekly-prep-bar.is-offset-h-zero {
            border-left: 3px solid #22c55e;
        }

        .weekly-prep-bar.is-offset-h-plus {
            border-left: 3px solid #f59e0b;
        }

        .weekly-prep-bar.is-incoming-prior-range {
            border-left: 3px solid #5c7f2b;
            padding-left: 6px;
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
            background: rgb(67, 53, 220);
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
            background: rgb(255, 0, 0);
            box-shadow: 0 0 2px rgba(220, 53, 53, 0.45);
        }

        .gantt-now-label {
            position: absolute;
            left: 0;
            top: -15px;
            transform: translateX(-50%);
            font-size: 9px;
            line-height: 1.2;
            background: rgb(59, 53, 220);
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
            background: rgb(220, 53, 53);
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

        .gantt-seg {
            height: 100%;
            min-width: 2px;
        }

        .gantt-seg.ontime {
            background: var(--dm-complete);
        }

        .gantt-seg.delay {
            background: var(--dm-yellow);
        }

        .gantt-seg.overdue {
            background: #dc3545;
        }

        .gantt-seg.empty {
            background: var(--dm-complete);
        }

        .gantt-seg.truck {
            background: #f59e0b;
        }

        .gantt-seg.truck-complete {
            background: var(--dm-blue);
        }

        .gantt-seg.truck-ontime {
            background: #22c55e !important;
        }

        .gantt-seg.truck-early {
            background: #eab308 !important;
        }

        .gantt-seg.truck-late {
            background: #dc3545 !important;
        }

        /* Timeline card */
        .delivery-timeline-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
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
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #1a1d21;
        }

        .timeline-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .timeline-late-summary {
            display: inline-flex;
            align-items: center;
            max-width: min(52vw, 520px);
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #fecaca;
            background: #fff7f7;
            color: #991b1b;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .timeline-late-summary.is-empty {
            border-color: #d1fae5;
            background: #f0fdf4;
            color: #15803d;
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
            grid-template-columns: var(--gantt-customer-col-width) var(--gantt-type-col-width) repeat(var(--gantt-slot-count), var(--gantt-hour-width));
        }

        .delivery-timeline-card .gantt-grid-cell {
            border: 1px solid #eef0f3;
            background: #ffffff;
            box-sizing: border-box;
        }

        .delivery-timeline-card .gantt-grid-sticky-col {
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
            letter-spacing: .05em;
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
            letter-spacing: .05em;
            font-weight: 600;
            font-size: 10px;
        }

        .delivery-timeline-card .gantt-grid-type-split .gantt-type-lane+.gantt-type-lane {
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
            grid-column: 3 / span var(--gantt-slot-count);
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

        .delivery-timeline-card .gantt-grid-track-lane+.gantt-grid-track-lane {
            border-top: 1px solid #eef0f3;
        }

        .delivery-timeline-card .gantt-track {
            margin: 16px 0 6px 0;
            height: 40px;
            border-radius: 0;
            background-color: transparent;
            background-image: repeating-linear-gradient(to right, transparent 0, transparent calc(var(--gantt-hour-width) - 1px), rgba(0, 0, 0, 0) calc(var(--gantt-hour-width) - 1px), #dde1e7 calc(var(--gantt-hour-width) - 1px), #dde1e7 var(--gantt-hour-width), transparent var(--gantt-hour-width));
            background-size: var(--gantt-hour-width) 100%;
            background-position: 0 0;
            overflow: hidden;
        }

        .delivery-timeline-card .gantt-bar-wrap.is-continues-tomorrow {
            min-width: 0 !important;
            max-width: none;
        }

        .delivery-timeline-card .gantt-seg.gantt-trail {
            background: #e8ecf1;
        }

        .delivery-timeline-card .gantt-bar-wrap {
            top: 4px;
            height: 36px;
            min-width: 48px;
            container-type: inline-size;
            container-name: gantt-bar;
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
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
            align-items: stretch;
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill .gantt-bar-stack {
            border-radius: 4px;
            height: 24px;
        }

        .delivery-timeline-card .gantt-seg.ontime:only-child,
        .delivery-timeline-card .gantt-seg.overdue:only-child {
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
            gap: 3px;
            pointer-events: none;
            z-index: 3;
            padding-left: 6px;
            max-width: 72%;
        }

        .delivery-timeline-card .pill-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(0, 0, 0, .08);
            color: #2f3542;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        }

        .pill-offset-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 800;
            letter-spacing: .02em;
            line-height: 1.2;
            white-space: nowrap;
            flex-shrink: 0;
            border: 1px solid transparent;
        }

        .pill-offset-badge.is-offset-h-minus {
            color: #1d4ed8;
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .pill-offset-badge.is-offset-h-zero {
            color: #15803d;
            background: #dcfce7;
            border-color: #86efac;
        }

        .pill-offset-badge.is-offset-h-plus {
            color: #b45309;
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .gantt-bar-wrap.is-offset-h-minus .gantt-bar-stack {
            border-left: 4px solid #3b82f6 !important;
        }

        .gantt-bar-wrap.is-offset-h-zero .gantt-bar-stack {
            border-left: 4px solid #22c55e !important;
        }

        .gantt-bar-wrap.is-offset-h-plus .gantt-bar-stack {
            border-left: 4px solid #f59e0b !important;
        }

        .gantt-bar-wrap.is-prep-from-yesterday.is-offset-h-minus .gantt-bar-stack {
            border-left: 4px solid #1d4ed8 !important;
        }

        .customer-unconfigured-note {
            display: block;
            margin-top: 4px;
            font-size: 9px;
            font-weight: 600;
            color: #8a6d3b;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .customer-late-note {
            display: inline-flex;
            align-items: center;
            margin-left: 6px;
            padding: 1px 5px;
            border-radius: 4px;
            border: 1px solid #fecaca;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.2;
            white-space: nowrap;
        }

        .prep-late-badge {
            position: absolute;
            right: 4px;
            top: 50%;
            z-index: 4;
            transform: translateY(-50%);
            padding: 1px 5px;
            border-radius: 4px;
            background: #dc3545;
            color: #fff;
            font-size: 8px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: 0;
            pointer-events: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .18);
        }

        .gantt-bar-wrap.is-prep-late,
        .weekly-prep-bar.is-prep-late {
            cursor: pointer;
        }

        .weekly-prep-bar .prep-late-badge {
            position: static;
            display: inline-block;
            margin-left: 3px;
            transform: none;
            vertical-align: middle;
        }

        .weekly-prep-bar.is-prep-late {
            background: #dc3545 !important;
        }

        .delivery-timeline-card .gantt-grid-row.is-row-highlight .gantt-grid-cell,
        .delivery-weekly-card tr.is-row-highlight>td {
            background: #fff7ed !important;
            box-shadow: inset 0 0 0 2px rgba(245, 158, 11, .28);
            transition: background .22s ease, box-shadow .22s ease;
        }

        .prep-late-detail {
            text-align: left;
            color: #1f2937;
            font-size: 12px;
        }

        .prep-late-detail__meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        .prep-late-detail__meta div,
        .prep-late-detail__empty {
            border: 1px solid #eef0f3;
            border-radius: 6px;
            padding: 7px 8px;
            background: #fafafa;
        }

        .prep-late-detail__meta span {
            display: block;
        }

        .prep-late-detail__meta span,
        .prep-late-detail__table th {
            color: #6b7280;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .prep-late-detail__table th {
            text-align: left;
            white-space: nowrap;
        }

        .prep-late-detail__reasons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 0 0 10px;
        }

        .prep-late-detail__reason {
            display: inline-flex;
            padding: 2px 7px;
            border-radius: 999px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 11px;
            font-weight: 700;
        }

        .prep-late-detail__table {
            width: 100%;
            border-collapse: collapse;
        }

        .prep-late-detail__table th,
        .prep-late-detail__table td {
            padding: 7px 6px;
            border-bottom: 1px solid #eef0f3;
            vertical-align: top;
        }

        .prep-late-detail__table td:last-child {
            font-weight: 800;
            color: #b91c1c;
            white-space: nowrap;
        }

        .weekly-offset-badge {
            display: inline-block;
            margin-left: 3px;
            padding: 0 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: 800;
            vertical-align: middle;
        }

        .weekly-offset-badge.is-offset-h-minus {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .weekly-offset-badge.is-offset-h-zero {
            color: #15803d;
            background: #dcfce7;
        }

        .weekly-offset-badge.is-offset-h-plus {
            color: #b45309;
            background: #fef3c7;
        }

        .legend-offset-swatch {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 2px;
            border: 1px solid rgba(0, 0, 0, .12);
            vertical-align: middle;
        }

        .legend-offset-swatch.is-offset-h-minus {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }

        .legend-offset-swatch.is-offset-h-zero {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
        }

        .legend-offset-swatch.is-offset-h-plus {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .delivery-timeline-card .gantt-seg.has-pct-label {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            min-width: 0;
        }

        .delivery-timeline-card .gantt-seg .gantt-bar-pct {
            position: static;
            display: block;
            max-width: 100%;
            padding: 0 3px;
            font-size: 8px;
            font-weight: 700;
            color: #1a1d24;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 0 2px rgba(255, 255, 255, .9);
            pointer-events: none;
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill .gantt-seg .gantt-bar-pct {
            font-size: 7px;
            padding: 0 2px;
        }

        @container gantt-bar (max-width: 44px) {
            .delivery-timeline-card .gantt-bar-wrap .gantt-bar-pct {
                display: none;
            }
        }

        @container gantt-bar (min-width: 45px) and (max-width: 64px) {
            .delivery-timeline-card .gantt-bar-wrap .gantt-bar-pct {
                font-size: 7px;
            }
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill .pill-meta {
            left: 50%;
            transform: translateX(-50%);
            padding-left: 0;
            max-width: 100%;
            justify-content: center;
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill .pill-avatar {
            width: auto;
            height: auto;
            min-width: 0;
            padding: 0 2px;
            border: none;
            background: transparent;
            box-shadow: none;
            font-size: 9px;
            font-weight: 700;
            color: #1a1d24;
            text-shadow: 0 0 2px rgba(255, 255, 255, .9);
        }

        .delivery-timeline-card .gantt-bar-wrap.truck-pill .pill-offset-badge {
            display: none;
        }

        .delivery-timeline-card .gantt-bar-wrap.is-instant .gantt-bar-pct {
            display: none;
        }

        .delivery-timeline-card .weekly-prep-bar,
        .delivery-timeline-card .weekly-truck-pill {
            container-type: inline-size;
            container-name: weekly-bar;
        }

        .delivery-timeline-card .weekly-prep-bar .gantt-bar-pct,
        .delivery-timeline-card .weekly-truck-pill .gantt-bar-pct {
            display: inline-block;
            margin-left: 2px;
            max-width: 42%;
            font-size: 7px;
            font-weight: 700;
            color: #1a1d24;
            line-height: 1;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @container weekly-bar (max-width: 52px) {

            .delivery-timeline-card .weekly-prep-bar .gantt-bar-pct,
            .delivery-timeline-card .weekly-truck-pill .gantt-bar-pct {
                display: none;
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

        /* Weekly card */
        .delivery-weekly-card {
            background: #ffffff;
            border: 1px solid #e8eaed;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
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
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #1a1d21;
        }

        .delivery-weekly-card .weekly-card-body {
            padding: 12px 16px;
        }

        .delivery-weekly-card .weekly-axis-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 600;
            color: #5c6370;
            margin: 0 0 8px 0;
            letter-spacing: .02em;
        }

        .delivery-weekly-card .weekly-axis-header .col-customer {
            flex: 0 0 var(--gantt-customer-col-width);
            width: var(--gantt-customer-col-width);
            min-width: var(--gantt-customer-col-width);
            max-width: var(--gantt-customer-col-width);
        }

        .delivery-weekly-card .weekly-axis-header .sep {
            opacity: .6;
        }

        .delivery-weekly-card .weekly-axis-header .col-type {
            flex: 0 0 var(--gantt-type-col-width);
            width: var(--gantt-type-col-width);
            min-width: var(--gantt-type-col-width);
            max-width: var(--gantt-type-col-width);
        }

        .delivery-weekly-card .weekly-gantt-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            width: 100%;
        }

        .delivery-weekly-card .weekly-gantt-table {
            border-collapse: collapse;
            font-size: 13px;
            table-layout: fixed;
            width: 100%;
            min-width: calc(var(--gantt-customer-col-width) + var(--gantt-type-col-width) + (var(--weekly-day-count, 1) * var(--gantt-hour-width)));
        }

        .delivery-weekly-card .weekly-col-customer,
        .delivery-weekly-card .weekly-col-customer-head {
            position: sticky;
            left: 0;
            z-index: 5;
            background: #fff;
            border: 1px solid #eef0f3;
            width: var(--gantt-customer-col-width);
            min-width: var(--gantt-customer-col-width);
            max-width: var(--gantt-customer-col-width);
            box-sizing: border-box;
        }

        .delivery-weekly-card .weekly-col-customer-head {
            z-index: 7;
            text-align: center;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #5c6370;
        }

        .delivery-weekly-card .weekly-col-type,
        .delivery-weekly-card .weekly-col-type-head {
            position: sticky;
            left: var(--gantt-customer-col-width);
            z-index: 5;
            background: #fff;
            border: 1px solid #eef0f3;
            width: var(--gantt-type-col-width);
            min-width: var(--gantt-type-col-width);
            max-width: var(--gantt-type-col-width);
            box-sizing: border-box;
        }

        .delivery-weekly-card .weekly-col-type-head {
            z-index: 7;
            text-align: center;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #5c6370;
        }

        .delivery-weekly-card .weekly-col-day,
        .delivery-weekly-card .weekly-col-day-head {
            width: auto;
            min-width: var(--gantt-hour-width);
            box-sizing: border-box;
        }

        .delivery-weekly-card .weekly-prep-lane-cell {
            position: relative;
            padding: 0;
            border: 1px solid #eef0f3;
            vertical-align: top;
            overflow: visible;
        }

        .delivery-weekly-card .weekly-prep-bar>span,
        .delivery-weekly-card .weekly-truck-pill>span {
            font-size: 9px;
            font-weight: 700;
            color: #2f3542;
            white-space: nowrap;
            line-height: 1;
        }

        .delivery-weekly-card .weekly-col-day-head {
            position: sticky;
            top: 0;
            z-index: 6;
            background: #fff;
            border: 1px solid #eef0f3;
            text-align: center;
            padding: 3px 4px;
            font-size: 10px;
            font-weight: 600;
            color: #5c6370;
            white-space: nowrap;
        }

        .delivery-weekly-row+.delivery-weekly-row {
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

        @media (max-width: 992px) {
            :root {
                --gantt-hour-width: 92px;
            }

            .gantt-wrap,
            .delivery-timeline-card .gantt-wrap {
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .bella-filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .bella-filter-select,
            .bella-filter-input {
                min-width: 0;
                width: 100%;
            }
        }

        .master-cycle-swal-popup {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .master-cycle-swal-detail {
            text-align: left;
            font-size: 13px;
            line-height: 1.5;
            margin-top: 4px;
        }

        .master-cycle-swal-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 0;
            border-bottom: 1px solid #eef0f3;
        }

        .master-cycle-swal-row:last-child {
            border-bottom: none;
        }

        .master-cycle-swal-row span {
            color: #6c757d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            flex-shrink: 0;
        }

        .master-cycle-swal-row strong {
            color: #1a1d21;
            font-weight: 700;
            text-align: right;
        }
    </style>

    <div class="delivery-dash mt-3">

        {{-- ===== PAGE HEADER ===== --}}
        <div class="bella-page-header">
            <div style="display:flex; align-items:center; gap:10px;">
                <div
                    style="width:36px;height:36px;border-radius:8px;background:var(--sky);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-truck" style="color:#fff;font-size:15px;"></i>
                </div>
                <h1 class="bella-page-header-title">Daily Monitoring Delivery</h1>
            </div>
            <div class="bella-page-header-clock">
                <span class="clock-label">Waktu lokal</span>
                <span id="deliveryDashLiveTime">--:--:--</span>
            </div>
        </div>

        {{-- ===== TABS ===== --}}
        <div class="bella-content-card">
            <ul class="bella-tabs nav" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pane-chart" role="tab">
                        <i class="fas fa-chart-area mr-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#pane-master" role="tab">
                        <i class="fas fa-cog mr-1"></i> Master Cycle
                    </a>
                </li>
            </ul>

            <div class="tab-content bella-tab-content">

                {{-- ── DASHBOARD TAB ── --}}
                <div class="tab-pane fade show active" id="pane-chart" role="tabpanel">

                    {{-- Filter bar --}}
                    <div class="bella-filter-card">
                        <div class="bella-filter-row">
                            <div class="bella-filter-group">
                                <span class="bella-filter-label">Delivery date dari</span>
                                <input type="date" class="bella-filter-input" id="filterDateFrom" name="date_from">
                            </div>
                            <div class="bella-filter-group">
                                <span class="bella-filter-label">Sampai</span>
                                <input type="date" class="bella-filter-input" id="filterDateTo" name="date_to">
                            </div>
                            <div class="bella-filter-group">
                                <span class="bella-filter-label">Customer</span>
                                <select class="bella-filter-select" id="filterCustomer" style="min-width:200px;">
                                    <option value="">Semua</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="bella-filter-group" style="justify-content:flex-end;">
                                <span class="bella-filter-label">&nbsp;</span>
                                <button type="button" id="btnReloadChart" class="act-btn primary"
                                    style="height:34px;padding:0 16px;font-size:12px;letter-spacing:.04em;">
                                    <i class="fas fa-sync-alt mr-1"></i> Muat ulang
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="chartEmpty" class="alert d-none mb-2" role="alert"></div>

                    {{-- Gantt timeline --}}
                    <div class="delivery-timeline-card mb-2" id="deliveryTimelineCard">
                        <div class="timeline-card-header d-flex justify-content-between align-items-center">
                            <div class="timeline-heading">
                                <h2 class="timeline-title">Timeline delivery</h2>
                                <span class="timeline-late-summary is-empty" id="timelineLateSummary">Tidak ada prep
                                    telat</span>
                            </div>
                            <button type="button" class="timeline-menu-btn" aria-label="Menu"
                                title="Menu">&#8942;</button>
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
                                <li>
                                    <span class="legend-dot" style="background: #dc3545;"></span>
                                    Prep telat
                                </li>
                                <li>
                                    <span class="legend-offset-swatch is-offset-h-minus"></span>
                                    H-1 (hari sebelum delivery)
                                </li>
                                <li>
                                    <span class="legend-offset-swatch is-offset-h-zero"></span>
                                    H+0 (hari delivery)
                                </li>
                                <li>
                                    <span class="legend-offset-swatch is-offset-h-plus"></span>
                                    H+1 (hari setelah delivery)
                                </li>
                                <li>
                                    <span class="legend-dot" style="background: #22c55e;"></span>
                                    Truck On Time (Hijau)
                                </li>
                                <li>
                                    <span class="legend-dot" style="background: #eab308;"></span>
                                    Truck Early (Kuning)
                                </li>
                                <li>
                                    <span class="legend-dot" style="background: #dc3545;"></span>
                                    Truck Telat (Merah)
                                </li>
                            </ul>
                            <div class="timeline-total" id="timelineTotalSum">Total: 0</div>
                        </div>
                    </div>

                    {{-- Weekly card --}}
                    <div class="delivery-weekly-card mb-2 d-none" id="deliveryWeeklyCard">
                        <div class="weekly-card-header d-flex justify-content-between align-items-center">
                            <h2 class="weekly-title">Ringkasan mingguan delivery</h2>
                            <span class="timeline-late-summary is-empty" id="weeklyLateSummary">Tidak ada prep telat</span>
                        </div>
                        <div class="weekly-card-body">
                            <div class="weekly-axis-header">
                                <span class="col-customer">Customer</span>
                                <span class="sep">|</span>
                                <span class="col-type">Type</span>
                            </div>
                            <div id="weeklyGanttContainer"></div>
                            <div class="delivery-weekly-note">Hover bar untuk melihat detail waktu tiap cycle.</div>
                        </div>
                    </div>
                </div>

                {{-- ── MASTER CYCLE TAB ── --}}
                <div class="tab-pane fade" id="pane-master" role="tabpanel">

                    {{-- ETA Settings --}}
                    <div class="bella-content-card mb-3">
                        <div
                            style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                            <div>
                                <div
                                    style="font-size:11.5px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px;">
                                    Pengaturan Rentang ETA
                                </div>
                                <span class="bella-info-pill" id="etaWindowInfo">
                                    <i class="fas fa-info-circle" style="color:var(--sky);"></i>
                                    Rentang default: ETA TRUCK 0 jam, Finish Preparation 4 jam.
                                </span>
                            </div>
                            <button type="button" class="act-btn primary" id="btnOpenEtaSettingModal"
                                data-toggle="modal" data-target="#etaWindowModal"
                                style="height:32px;padding:0 14px;font-size:12px;">
                                <i class="fas fa-sliders-h mr-1"></i> Setting Rentang
                            </button>
                        </div>
                    </div>

                    {{-- Master Cycle Form --}}
                    <div class="bella-content-card mb-3">
                        <div style="padding:12px 18px; border-bottom:1px solid var(--border);">
                            <span
                                style="font-size:11.5px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.05em;">
                                Tambah / Edit Master Cycle
                            </span>
                        </div>
                        <div style="padding:16px 18px;">
                            <form id="masterCycleForm">
                                <div class="form-row align-items-end">
                                    <div class="col-md-3 mb-3">
                                        <label class="bella-form-label">Customer</label>
                                        <select class="bella-form-control" id="mcycleCustomerId" required>
                                            <option value="">Pilih customer</option>
                                            @foreach ($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="bella-form-label">Nama Cycle</label>
                                        <select class="bella-form-control" id="mcycleName" required>
                                            <option value="" selected disabled>Pilih cycle</option>
                                            @foreach (range(1, 10) as $n)
                                                <option value="{{ $n }}">{{ $n }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="bella-form-label">Waktu Rentang Prep</label>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <input type="time" class="bella-form-control" id="mcyclePrepStart"
                                                required step="60">
                                            <span
                                                style="font-size:11px;color:var(--text-muted);white-space:nowrap;">sampai</span>
                                            <input type="time" class="bella-form-control" id="mcyclePrepEnd" required
                                                step="60">
                                        </div>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="bella-form-label">Hari Offset Prep</label>
                                        <select class="bella-form-control" id="mcyclePrepDayOffset" required>
                                            <option value="0">H+0 (hari delivery)</option>
                                            <option value="-1">H-1</option>
                                            <option value="-2">H-2</option>
                                            <option value="-3">H-3</option>
                                            <option value="-4">H-4</option>
                                            <option value="-5">H-5</option>
                                            <option value="-6">H-6</option>
                                            <option value="-7">H-7</option>
                                            <option value="1">H+1</option>
                                            <option value="2">H+2</option>
                                        </select>
                                        <small id="mcycleOffsetHint"
                                            style="display:block;font-size:10px;color:var(--text-muted);margin-top:4px;">
                                            Selesai prep: <span id="mcycleEndOffsetLabel">H+0</span>
                                        </small>
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="bella-form-label">Waktu ETA Truck</label>
                                        <input type="time" class="bella-form-control" id="mcycleTruckTime" required
                                            step="60">
                                    </div>
                                    <div class="col-md-3 mb-3" style="display:flex;align-items:flex-end;gap:8px;">
                                        <button type="submit" class="act-btn success" id="btnMasterSave"
                                            style="height:32px;padding:0 14px;font-size:12px;">
                                            <i class="fas fa-save mr-1"></i> Simpan
                                        </button>
                                        <button type="button" class="act-btn secondary d-none" id="btnMasterCancel"
                                            style="height:32px;padding:0 12px;font-size:12px;">
                                            Batal edit
                                        </button>
                                    </div>
                                </div>
                                <p style="font-size:11px;color:var(--text-muted);margin:0;">
                                    Hari Offset = tanggal mulai prep terhadap delivery (H). Otomatis H-1 jika jam mulai
                                    prep &gt; jam truck (mis. prep 22:00, truck 07:00). Timeline harian 00:00–24:00;
                                    bar prep hanya tampil jika sudah/sedang berjalan.
                                </p>
                            </form>
                        </div>
                    </div>

                    {{-- Master Cycle Table --}}
                    <div class="bella-content-card mb-3">
                        <div style="padding:12px 18px; border-bottom:1px solid var(--border);">
                            <span
                                style="font-size:11.5px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.05em;">
                                Daftar Master Cycle
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="bella-master-table" id="masterCycleTable">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th>Customer</th>
                                        <th>Cycle</th>
                                        <th>Rentang Prep</th>
                                        <th>Hari Offset</th>
                                        <th>ETA Truck</th>
                                        <th style="width:140px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Customer List --}}
                    <div class="bella-content-card">
                        <div style="padding:12px 18px; border-bottom:1px solid var(--border);">
                            <span
                                style="font-size:11.5px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.05em;">
                                Daftar Customer
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="bella-master-table">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">No</th>
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
                </div>

            </div>{{-- /.tab-content --}}
        </div>{{-- /.bella-content-card --}}
    </div>{{-- /.delivery-dash --}}
@endsection

@section('custom-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        $(function() {
            var stackedUrl = "{{ route('dashboard.delivery.stackedChart') }}";
            var loadingListUrl = "{{ route('loadingList.index') }}";
            var notifyUnfinishedUrl = "{{ route('dashboard.delivery.notifyUnfinished') }}";
            var masterIndex = "{{ route('dashboard.delivery.masterCycles.index') }}";
            var masterStore = "{{ route('dashboard.delivery.masterCycles.store') }}";
            var masterBase = "{{ url('/dashboard/delivery/master-cycles') }}";
            var csrf = "{{ csrf_token() }}";

            // Hak akses: hanya admin/ppic (manage-master) yang boleh kelola master cycle
            var canManageMaster = @json(auth()->user()->can('manage-master'));
            if (!canManageMaster) {
                $('#masterCycleForm').closest('.bella-content-card').hide();
            }

            var customers = @json($customers);
            var chartRows = [];
            var chartRowsPrevDay = [];
            var chartRowsNextDay = [];
            var chartMergedByCustTime = [];
            var chartCustomerOrder = [];
            var chartWeeklyPoints = [];
            /** Indeks keberadaan LL per customer+cycle+tanggal (hanya untuk validasi render, bukan ubah data). */
            var loadingListPresenceIndex = {};
            var dashboardViewMode = 'daily';
            var weeklyChartInstance = null;
            var weeklyNowTimer = null;
            var weeklyGridTimer = null;
            var editMasterId = null;
            var ganttNowTimer = null;
            var waNotifyTimer = null;
            var ganttHourWindow = 24;
            var ganttCols = 24;
            var EVENING_PREP_MINUTES = 18 * 60;
            var MORNING_TRUCK_UNTIL_MINUTES = 12 * 60;
            var etaWindowStorageKey = 'delivery_eta_window_v2';
            var waSentStorageKey = 'delivery_wa_unfinished_sent_v1';
            /** Bar prep: pernah lewat finish prep sebelum 100% → merah, tetap merah meski sudah 100% */
            var ganttOverdueLatchStorageKey = 'delivery_gantt_overdue_latched_v1';
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
                    var label = h === 0 ? '0 jam (saat ini)' : h + ' jam';
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
                $('#etaWindowInfo').html(
                    '<i class="fas fa-info-circle" style="color:var(--sky);"></i> ' +
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
                return 0;
            }

            /** Label jam terakhir pada timeline harian (window 00:00 → selesai slot 23:00). */
            function getTimelineCropEndHour() {
                return 23;
            }

            /** Potong bar PREP overnight di slot 23:00 agar tidak wrap ke kiri timeline. */
            function getPrepOvernightClampEndFrac(hourWindow) {
                var cropClock = String(getTimelineCropEndHour()).padStart(2, '0') + ':00';
                var endFrac = timeToWindowFrac(cropClock);
                if (endFrac !== null && endFrac > 0) {
                    return endFrac;
                }
                return hourWindow || ganttHourWindow || 24;
            }

            function getCurrentDayFrac() {
                var now = new Date();
                var v = now.getHours() + now.getMinutes() / 60 + now.getSeconds() / 3600;
                var startHour = getTimelineStartHour();
                if (v < startHour) {
                    v += 24;
                }
                return (v - startHour) / 24;
            }

            function getTimeOffsetLeftPct(offsetHours) {
                var d = new Date();
                d.setMinutes(d.getMinutes() + (offsetHours * 60));
                var hour = d.getHours(),
                    minute = d.getMinutes(),
                    second = d.getSeconds();
                var startHour = getTimelineStartHour();
                var v = hour + minute / 60 + second / 3600;
                if (v < startHour) {
                    v += 24;
                }
                return Math.max(0, Math.min(100, ((v - startHour) / 24) * 100));
            }

            function getNowLeftPct() {
                return getTimeOffsetLeftPct(etaWindowSettings.eta_offset_hours);
            }

            function getTruckArrivalLeftPct() {
                return getTimeOffsetLeftPct(etaWindowSettings.finish_offset_hours);
            }

            function updateGanttNowMarkers() {
                var nowPct = scaleTimelineMarkerPct(getNowLeftPct());
                var truckPct = scaleTimelineMarkerPct(getTruckArrivalLeftPct());
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
                var hour = parseInt(p[0], 10),
                    minute = parseInt(p[1] || '0', 10);
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

            function normalizeClock(t) {
                return String(t || '').trim().substring(0, 5);
            }

            function addDaysToDateStr(dateStr, days) {
                var base = String(dateStr || '').trim();
                if (!base.length) {
                    return base;
                }
                var parts = base.split('-');
                if (parts.length !== 3) {
                    return base;
                }
                var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                if (isNaN(d.getTime())) {
                    return base;
                }
                d.setDate(d.getDate() + (parseInt(days, 10) || 0));
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d
                    .getDate()).padStart(2, '0');
            }

            function formatPrepDayOffsetLabel(offset) {
                var n = parseInt(offset, 10) || 0;
                if (n === 0) {
                    return 'H+0';
                }
                if (n > 0) {
                    return 'H+' + n;
                }
                return 'H' + n;
            }

            function parsePrepDayOffsetLabel(label) {
                var raw = String(label || '').trim().toUpperCase();
                if (!raw.length) {
                    return null;
                }
                var m = raw.match(/^H([+-]?\d+)$/);
                if (!m) {
                    return null;
                }
                var n = parseInt(m[1], 10);
                return isNaN(n) ? null : n;
            }

            function parseRowNumericOffset(value) {
                if (value === null || value === undefined || value === '') {
                    return null;
                }
                var n = parseInt(value, 10);
                return isNaN(n) ? null : n;
            }

            function getRowPrepDayOffsetStored(row) {
                if (!row) {
                    return 0;
                }
                var fromField = parseRowNumericOffset(row.prep_day_offset);
                if (fromField !== null) {
                    return fromField;
                }
                var fromLabel = parsePrepDayOffsetLabel(row.prep_day_offset_label);
                if (fromLabel !== null) {
                    return fromLabel;
                }
                return 0;
            }

            /**
             * Prep malam sebelum truck pagi: jam mulai prep > jam truck → H-1.
             * Contoh: prep 22:00, truck 07:00 → H-1 (bukan H+0 di hari delivery).
             */
            function inferPrepDayOffsetFromTruck(prepStart, truckTime) {
                var p = normalizeClock(prepStart);
                var t = normalizeClock(truckTime);
                if (!p.length || !t.length) {
                    return null;
                }
                if (timeToMinutes(p) > timeToMinutes(t)) {
                    return -1;
                }
                return null;
            }

            function getEffectivePrepDayOffset(row) {
                if (!row) {
                    return 0;
                }
                var inferred = inferPrepDayOffsetFromTruck(getRowPrepStartClock(row), row.truck_time);
                if (inferred !== null) {
                    return inferred;
                }
                return getRowPrepDayOffsetStored(row);
            }

            function getRowPrepDayOffset(row) {
                return getEffectivePrepDayOffset(row);
            }

            function getEffectivePrepEndDayOffset(row) {
                if (!row) {
                    return 0;
                }
                var startOff = getEffectivePrepDayOffset(row);
                var prepStart = getRowPrepStartClock(row);
                var prepEnd = getRowPrepEndClock(row) || prepStart;
                if (isPrepOvernightByClock(prepStart, prepEnd)) {
                    return startOff + 1;
                }
                return startOff;
            }

            function getRowPrepEndDayOffset(row) {
                return getEffectivePrepEndDayOffset(row);
            }

            function getPrepDateFromDelivery(row, dayOffset) {
                var delivery = formatLocalDateKey(row.delivery_date);
                if (!delivery.length) {
                    return '';
                }
                return addDaysToDateStr(delivery, parseInt(dayOffset, 10) || 0);
            }

            function getPrepStartDate(row) {
                return getPrepDateFromDelivery(row, getRowPrepDayOffset(row));
            }

            function getPrepEndDate(row) {
                return getPrepDateFromDelivery(row, getRowPrepEndDayOffset(row));
            }

            function buildAbsoluteDatetimeLocal(dateStr, clock) {
                var d = formatLocalDateKey(dateStr);
                var t = normalizeClock(clock);
                if (!d.length || !t.length) {
                    return null;
                }
                return d + 'T' + t + ':00';
            }

            /** Parse datetime API sebagai waktu lokal (hindari geser timezone dari ISO Z). */
            function parseAbsoluteDatetimeLocal(iso) {
                if (!iso) {
                    return NaN;
                }
                var s = String(iso).trim();
                var m = s.match(/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})/);
                if (m) {
                    return new Date(m[1] + 'T' + m[2] + ':00').getTime();
                }
                return Date.parse(s);
            }

            function enrichRowAbsoluteDatetimes(row) {
                if (!row) {
                    return row;
                }
                var prepStartClock = getRowPrepStartClock(row);
                if (!prepStartClock.length) {
                    return row;
                }
                var prepEndClock = getRowPrepEndClock(row) || prepStartClock;
                var startIso = buildAbsoluteDatetimeLocal(getPrepStartDate(row), prepStartClock);
                var endIso = buildAbsoluteDatetimeLocal(getPrepEndDate(row), prepEndClock);
                if (startIso) {
                    row.absolute_start_datetime = startIso;
                    row.absolute_start_ms = parseAbsoluteDatetimeLocal(startIso);
                }
                if (endIso) {
                    row.absolute_end_datetime = endIso;
                    row.absolute_end_ms = parseAbsoluteDatetimeLocal(endIso);
                }
                if (row.absolute_start_ms != null && row.absolute_end_ms != null &&
                    row.absolute_end_ms <= row.absolute_start_ms &&
                    isPrepOvernightByClock(prepStartClock, prepEndClock)) {
                    var fixedEndIso = buildAbsoluteDatetimeLocal(
                        getPrepDateFromDelivery(row, getEffectivePrepDayOffset(row) + 1), prepEndClock);
                    if (fixedEndIso) {
                        row.absolute_end_datetime = fixedEndIso;
                        row.absolute_end_ms = parseAbsoluteDatetimeLocal(fixedEndIso);
                    }
                }
                return row;
            }

            function enrichChartRowsCollection(rows) {
                return (rows || []).map(function(r) {
                    return enrichRowAbsoluteDatetimes(Object.assign({}, r));
                });
            }

            function viewDateToMsRange(viewDateStr) {
                var d = formatLocalDateKey(viewDateStr);
                if (!d.length) {
                    return {
                        startMs: 0,
                        endMs: 0
                    };
                }
                var startMs = new Date(d + 'T00:00:00').getTime();
                return {
                    startMs: startMs,
                    endMs: startMs + 86400000
                };
            }

            function msToLocalDateKey(ms) {
                var d = new Date(ms);
                if (isNaN(d.getTime())) {
                    return '';
                }
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d
                    .getDate()).padStart(2, '0');
            }

            /**
             * Bar prep hanya untuk yang sudah / sedang berjalan (bukan jadwal masa depan).
             * Tampil jika: progress > 0, atau waktu sekarang >= absolute_start.
             * (Atau jika sudah ada data Loading List / ll_count > 0).
             */
            function isPrepActiveOrCompleted(row, nowMs) {
                if (!row) {
                    return false;
                }
                if (rowHasLoadingListData(row)) {
                    return true;
                }
                var pct = getRowRawProgressPct(row);
                if (pct > 0) {
                    return true;
                }
                enrichRowAbsoluteDatetimes(row);
                if (row.absolute_start_ms == null) {
                    return false;
                }
                var now = (nowMs != null) ? nowMs : Date.now();
                return now >= row.absolute_start_ms;
            }

            /** Urutan global: absolute_start_datetime (kronologis lintas hari & offset). */
            function comparePrepRowsByAbsolute(a, b) {
                enrichRowAbsoluteDatetimes(a);
                enrichRowAbsoluteDatetimes(b);
                var am = a.absolute_start_ms;
                var bm = b.absolute_start_ms;
                if (am != null && bm != null && am !== bm) {
                    return am - bm;
                }
                if (am != null && bm == null) {
                    return -1;
                }
                if (am == null && bm != null) {
                    return 1;
                }
                return prepRowIdentityKey(a).localeCompare(prepRowIdentityKey(b));
            }

            /**
             * Potongan PREP pada satu hari kalender viewDate dari rentang absolut master cycle.
             */
            function computePrepBarSegmentOnViewDate(row, viewDateStr, hourWindow) {
                if (!rowHasConfiguredMasterCycle(row)) {
                    return null;
                }
                enrichRowAbsoluteDatetimes(row);
                if (row.absolute_start_ms == null || row.absolute_end_ms == null) {
                    return null;
                }
                if (!isPrepActiveOrCompleted(row)) {
                    return null;
                }
                if (!hasLoadingListOnDeliveryDate(row.customer_id, row.customer_name, rowCycleKey(row),
                        formatLocalDateKey(row.delivery_date))) {
                    return null;
                }
                var range = viewDateToMsRange(viewDateStr);
                if (!range.endMs) {
                    return null;
                }
                var segStartMs = Math.max(row.absolute_start_ms, range.startMs);
                var segEndMs = Math.min(row.absolute_end_ms, range.endMs);
                if (segEndMs <= segStartMs) {
                    return null;
                }
                var windowH = hourWindow || ganttHourWindow || 24;
                var startFrac = (segStartMs - range.startMs) / 3600000;
                var durH = (segEndMs - segStartMs) / 3600000;
                var leftPct = (startFrac / windowH) * 100;
                var widthPct = (durH / windowH) * 100;
                var minSlotPct = (100 / windowH);
                if (durH >= (1 / 120)) {
                    widthPct = Math.max(widthPct, minSlotPct);
                }
                var isContinuation = row.absolute_start_ms < range.startMs;
                var continuesTomorrow = row.absolute_end_ms > range.endMs;
                var overnight = (row.absolute_end_ms - row.absolute_start_ms) > 12 * 3600000 || isPrepOvernight(
                    row);
                return {
                    startFrac: startFrac,
                    durH: durH,
                    visibleDurH: durH,
                    overflowH: continuesTomorrow ? (row.absolute_end_ms - range.endMs) / 3600000 : 0,
                    hasOverflow: continuesTomorrow,
                    continuesTomorrow: continuesTomorrow,
                    isOvernightPrep: overnight,
                    isContinuation: isContinuation,
                    kind: isContinuation ? 'continuation' : 'evening',
                    leftPct: leftPct,
                    widthPct: widthPct
                };
            }

            function slotsToMsRange(slots) {
                if (!slots || !slots.length) {
                    return {
                        startMs: 0,
                        endMs: 0
                    };
                }
                var first = formatLocalDateKey(slots[0]);
                var last = formatLocalDateKey(slots[slots.length - 1]);
                var startMs = new Date(first + 'T00:00:00').getTime();
                var endMs = new Date(last + 'T00:00:00').getTime() + 86400000;
                return {
                    startMs: startMs,
                    endMs: endMs
                };
            }

            function computeWeeklyPrepBarSpanFromAbsolute(row, slots) {
                if (!row || !slots || !slots.length) {
                    return null;
                }
                enrichRowAbsoluteDatetimes(row);
                if (row.absolute_start_ms == null || row.absolute_end_ms == null) {
                    return null;
                }
                if (!isPrepActiveOrCompleted(row)) {
                    return null;
                }
                if (!hasLoadingListOnDeliveryDate(row.customer_id, row.customer_name, rowCycleKey(row),
                        formatLocalDateKey(row.delivery_date))) {
                    return null;
                }
                var range = slotsToMsRange(slots);
                var segStartMs = Math.max(row.absolute_start_ms, range.startMs);
                var segEndMs = Math.min(row.absolute_end_ms, range.endMs);
                if (segEndMs <= segStartMs) {
                    return null;
                }
                var totalMs = range.endMs - range.startMs;
                var leftPct = ((segStartMs - range.startMs) / totalMs) * 100;
                var widthPct = ((segEndMs - segStartMs) / totalMs) * 100;
                if (widthPct <= (100 / slots.length / 24 / 120)) {
                    return null;
                }
                var prepStart = getRowPrepStartClock(row);
                var prepEnd = getRowPrepEndClock(row) || '';
                return {
                    leftPct: leftPct,
                    widthPct: widthPct,
                    isOvernightPrep: isPrepOvernight(row),
                    crossesDays: msToLocalDateKey(segStartMs) !== msToLocalDateKey(segEndMs - 1),
                    isIncomingFromPriorRange: row.absolute_start_ms < range.startMs,
                    anchorDate: msToLocalDateKey(row.absolute_start_ms),
                    endDate: msToLocalDateKey(segEndMs - 1),
                    prepStart: prepStart,
                    prepEnd: prepEnd
                };
            }

            function computePrepEndDayOffsetFromForm(prepDayOffset, prepStart, prepEnd) {
                var off = parseInt(prepDayOffset, 10) || 0;
                if (isPrepOvernightByClock(normalizeClock(prepStart), normalizeClock(prepEnd))) {
                    return off + 1;
                }
                return off;
            }

            function updateMasterCycleOffsetHint() {
                var prepStart = ($('#mcyclePrepStart').val() || '').trim();
                var prepEnd = ($('#mcyclePrepEnd').val() || '').trim();
                var truckTime = ($('#mcycleTruckTime').val() || '').trim();
                var inferred = inferPrepDayOffsetFromTruck(prepStart, truckTime);
                if (inferred !== null) {
                    $('#mcyclePrepDayOffset').val(String(inferred));
                }
                var prepOff = $('#mcyclePrepDayOffset').val();
                var endOff = computePrepEndDayOffsetFromForm(prepOff, prepStart, prepEnd);
                var endLbl = formatPrepDayOffsetLabel(endOff);
                var $hint = $('#mcycleEndOffsetLabel');
                $hint.text(endLbl);
                if (inferred !== null) {
                    $hint.parent().css('color', 'var(--sky)');
                    $hint.parent().attr('title',
                        'Otomatis H-1: jam mulai prep (' + prepStart + ') > jam truck (' + truckTime + ')');
                } else if (endOff !== (parseInt(prepOff, 10) || 0)) {
                    $hint.parent().css('color', 'var(--sky)');
                    $hint.parent().removeAttr('title');
                } else {
                    $hint.parent().css('color', 'var(--text-muted)');
                    $hint.parent().removeAttr('title');
                }
            }

            function getRowPrepStartClock(row) {
                if (!row) {
                    return '';
                }
                return normalizeClock(row.cycle_time || row.prep_time || '');
            }

            function getRowPrepEndClock(row) {
                if (!row) {
                    return null;
                }
                var prepEndRaw = (row.prep_end_time != null) ? String(row.prep_end_time).trim() : '';
                if (prepEndRaw.length) {
                    return prepEndRaw.substring(0, 5);
                }
                return null;
            }

            function normalizeCycleKey(name) {
                var raw = String(name || '').trim();
                if (!raw.length) {
                    return '';
                }
                var m = raw.match(/\d+/);
                return m ? m[0] : raw.toUpperCase();
            }

            /** PREP & TRUCK satu baris = satu cycle (C1 PREP ↔ C1 TRUCK dari API/master). */
            function rowCycleKey(row) {
                return normalizeCycleKey(row.cycle_name);
            }

            function isPrepOvernightByClock(prepStart, prepEnd) {
                if (!prepStart || !prepEnd) {
                    return false;
                }
                if (timeToMinutes(prepEnd) <= timeToMinutes(prepStart)) {
                    return true;
                }
                var sFrac = timeToWindowFrac(normalizeClock(prepStart));
                var eFrac = timeToWindowFrac(normalizeClock(prepEnd));
                if (sFrac === null || eFrac === null) {
                    return false;
                }
                return eFrac < sFrac;
            }

            function isPrepOvernight(row) {
                return isPrepOvernightByClock(getRowPrepStartClock(row), getRowPrepEndClock(row));
            }

            /** Style bar malam: dari jam mulai sampai ujung kanan (23:00), tanpa wrap ke 00:00. */
            function buildPrepBarInlineStyle(prepLayout) {
                if (!prepLayout) {
                    return '';
                }
                if (prepLayout.continuesTomorrow && !prepLayout.isContinuation) {
                    return 'left:' + prepLayout.leftPct + '%;right:0;width:auto;min-width:0;max-width:none;';
                }
                return 'left:' + prepLayout.leftPct + '%;width:' + prepLayout.widthPct + '%;';
            }

            function isEveningPrepClock(clock) {
                return !!clock && timeToMinutes(clock) >= EVENING_PREP_MINUTES;
            }

            function isMorningTruckClock(clock) {
                if (!clock) {
                    return false;
                }
                var m = timeToMinutes(clock);
                return m >= (getTimelineStartHour() * 60) && m < MORNING_TRUCK_UNTIL_MINUTES;
            }

            /**
             * TRUCK +1 day berdasarkan rentang PREP cycle yang sama:
             * - PREP overnight (23:00–07:00): TRUCK pagi setelah tengah malam
             * - PREP malam (≥18:00) + TRUCK pagi < jam mulai PREP
             * - TRUCK dini hari sebelum selesai PREP (mis. PREP sore, TRUCK 00:50)
             */
            function isTruckOnNextDay(row) {
                if (!row) {
                    return false;
                }
                var truckAt = normalizeClock(row.truck_time);
                if (!truckAt.length) {
                    return false;
                }
                var prepStart = getRowPrepStartClock(row);
                var prepEnd = getRowPrepEndClock(row) || prepStart;
                if (!prepStart.length) {
                    return false;
                }

                var truckMin = timeToMinutes(truckAt);
                var startMin = timeToMinutes(prepStart);
                var endMin = timeToMinutes(prepEnd);

                if (isPrepOvernight(row)) {
                    if (truckMin >= startMin) {
                        return false;
                    }
                    if (truckMin <= endMin) {
                        return true;
                    }
                    if (truckMin > endMin && truckMin < startMin) {
                        if (truckMin >= EVENING_PREP_MINUTES && truckMin < startMin) {
                            return false;
                        }
                        return true;
                    }
                    return false;
                }

                if ((isEveningPrepClock(prepStart) || isEveningPrepClock(prepEnd)) &&
                    isMorningTruckClock(truckAt) && truckMin < startMin) {
                    return true;
                }

                if (truckMin < endMin && truckMin < startMin) {
                    return true;
                }

                return false;
            }

            function computePrepBarLayout(prepStart, prepEnd, hourWindow) {
                var windowH = hourWindow || ganttHourWindow || 24;
                var startClock = normalizeClock(prepStart);
                if (!startClock.length) {
                    return null;
                }
                var endClock = prepEnd ? normalizeClock(prepEnd) : '';
                var startFrac = timeToWindowFrac(startClock);
                if (startFrac === null) {
                    return null;
                }
                var durH = endClock.length ? calcDurationHours(startClock, endClock) : 1;
                var endFrac = startFrac + durH;
                var overnight = isPrepOvernightByClock(startClock, endClock) || durH > 12;
                var clampEndFrac = overnight ? getPrepOvernightClampEndFrac(windowH) : windowH;
                var visibleDurH = durH;
                var overflowH = 0;
                if (overnight) {
                    if (endFrac > clampEndFrac) {
                        overflowH = endFrac - clampEndFrac;
                    }
                    visibleDurH = Math.max(clampEndFrac - startFrac, (1 / 60));
                } else if (endFrac > clampEndFrac) {
                    overflowH = endFrac - clampEndFrac;
                    visibleDurH = Math.max(clampEndFrac - startFrac, (1 / 60));
                }
                var leftPct = (startFrac / windowH) * 100;
                var widthPct = (visibleDurH / windowH) * 100;
                var continuesTomorrow = overflowH > (1 / 120) && overnight;
                if (continuesTomorrow) {
                    widthPct = Math.max(100 - leftPct, (1 / 60) * 100 / windowH);
                }
                return {
                    startFrac: startFrac,
                    durH: durH,
                    visibleDurH: visibleDurH,
                    overflowH: overflowH,
                    hasOverflow: overflowH > (1 / 120),
                    continuesTomorrow: continuesTomorrow,
                    isOvernightPrep: overnight,
                    leftPct: leftPct,
                    widthPct: widthPct
                };
            }

            function computePrepBarLayoutForRow(row, hourWindow) {
                return computePrepBarLayout(getRowPrepStartClock(row), getRowPrepEndClock(row), hourWindow);
            }

            /** Sisa PREP pagi (00:00 → akhir PREP) di hari berikutnya. */
            function computePrepMorningContinuationLayout(prepEndClock, hourWindow) {
                var windowH = hourWindow || ganttHourWindow || 24;
                var endClock = normalizeClock(prepEndClock);
                if (!endClock.length) {
                    return null;
                }
                var endFrac = timeToWindowFrac(endClock);
                if (endFrac === null || endFrac <= (1 / 120)) {
                    return null;
                }
                var durH = endFrac;
                return {
                    segments: [{
                        leftPct: 0,
                        widthPct: Math.max((durH / windowH) * 100, 100 / windowH)
                    }],
                    prepEndClock: endClock,
                    isContinuation: true
                };
            }

            function weeklyPointToChartRow(wp) {
                if (!wp) {
                    return null;
                }
                return enrichRowAbsoluteDatetimes({
                    customer_id: wp.customer_id,
                    customer_name: wp.customer_name,
                    delivery_date: wp.delivery_date,
                    cycle_name: wp.cycle_name,
                    cycle_time: wp.prep_time || wp.cycle_time,
                    prep_time: wp.prep_time,
                    prep_end_time: wp.prep_end_time,
                    prep_day_offset: wp.prep_day_offset,
                    prep_end_day_offset: wp.prep_end_day_offset,
                    prep_day_offset_label: wp.prep_day_offset_label,
                    prep_end_day_offset_label: wp.prep_end_day_offset_label,
                    prep_crosses_midnight: wp.prep_crosses_midnight,
                    absolute_start_datetime: wp.absolute_start_datetime || wp.start_at,
                    absolute_end_datetime: wp.absolute_end_datetime || wp.end_at,
                    master_cycle_configured: wp.master_cycle_configured,
                    truck_time: wp.truck_time,
                    progress_pct: wp.progress_pct,
                    total_done: wp.total_done,
                    total_target: wp.total_target,
                    latest_pulling_date: wp.latest_pulling_date,
                    pulling_items: wp.pulling_items || [],
                    pds_numbers: wp.pds_numbers || [],
                    loading_lists: wp.loading_lists || [],
                    ll_count: wp.ll_count != null ? wp.ll_count : (wp.total_target > 0 ? 1 : 0)
                });
            }

            function getAllChartRowsForLookup() {
                var seen = {};
                var out = [];

                function pushRow(r) {
                    if (!r) {
                        return;
                    }
                    var k = (r.customer_id || '') + '|' + rowCycleKey(r) + '|' + formatLocalDateKey(
                        r.delivery_date) + '|' + (r.cycle_time || '');
                    if (seen[k]) {
                        return;
                    }
                    seen[k] = true;
                    out.push(r);
                }
                chartRows.forEach(pushRow);
                chartRowsPrevDay.forEach(pushRow);
                chartRowsNextDay.forEach(pushRow);
                (chartWeeklyPoints || []).forEach(function(wp) {
                    pushRow(weeklyPointToChartRow(wp));
                });
                return out;
            }

            function rowHasLoadingListData(row) {
                if (!row) {
                    return false;
                }
                if (parseInt(row.ll_count || 0, 10) > 0) {
                    return true;
                }
                if (parseInt(row.total_target || 0, 10) > 0) {
                    return true;
                }
                return false;
            }

            function makeLoadingListPresenceKey(customerId, customerName, cycleKey, dateStr) {
                var cid = parseInt(customerId, 10) || 0;
                var cn = String(customerName || '-').trim();
                var ck = String(cycleKey || '').trim();
                var d = formatLocalDateKey(dateStr);
                return cid + '|' + cn + '|' + ck + '|' + d;
            }

            function rebuildLoadingListPresenceIndex() {
                loadingListPresenceIndex = {};

                function ingest(row) {
                    if (!rowHasLoadingListData(row)) {
                        return;
                    }
                    var d = formatLocalDateKey(row.delivery_date);
                    if (!d.length) {
                        return;
                    }
                    var k = makeLoadingListPresenceKey(row.customer_id, row.customer_name, rowCycleKey(row), d);
                    loadingListPresenceIndex[k] = true;
                }
                chartRows.forEach(ingest);
                chartRowsPrevDay.forEach(ingest);
                chartRowsNextDay.forEach(ingest);
                (chartWeeklyPoints || []).forEach(ingest);
            }

            /** Ada loading list (customer + cycle) pada tanggal delivery tertentu — validasi render saja. */
            function hasLoadingListOnDeliveryDate(customerId, customerName, cycleKey, dateStr) {
                var d = formatLocalDateKey(dateStr);
                if (!d.length) {
                    return false;
                }
                var k = makeLoadingListPresenceKey(customerId, customerName, cycleKey, d);
                if (loadingListPresenceIndex[k]) {
                    return true;
                }
                var cid = parseInt(customerId, 10) || 0;
                var cn = String(customerName || '-').trim();
                var ck = String(cycleKey || '').trim();
                return getAllChartRowsForLookup().some(function(r) {
                    if (formatLocalDateKey(r.delivery_date) !== d) {
                        return false;
                    }
                    if (String(rowCycleKey(r)) !== ck) {
                        return false;
                    }
                    if (!rowHasLoadingListData(r)) {
                        return false;
                    }
                    if (cid) {
                        return parseInt(r.customer_id, 10) === cid;
                    }
                    return (r.customer_name || '-').trim() === cn;
                });
            }

            function formatDeliveryDateLabel(dateStr) {
                var d = formatLocalDateKey(dateStr);
                if (!d.length) {
                    return String(dateStr || '');
                }
                var dt = new Date(d + 'T12:00:00');
                if (isNaN(dt.getTime())) {
                    return d;
                }
                return dt.toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            }

            function formatDeliveryDateShort(dateStr) {
                var d = formatLocalDateKey(dateStr);
                if (!d.length) {
                    return String(dateStr || '');
                }
                var dt = new Date(d + 'T12:00:00');
                if (isNaN(dt.getTime())) {
                    return d;
                }
                return dt.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short'
                });
            }

            function formatCrossDaySourceLabel(dateStr) {
                var short = formatDeliveryDateShort(dateStr);
                return short.length ? 'Dari ' + short : 'lanjutan';
            }

            function getPrepCrossDaySourceDateKey(row) {
                if (!row) {
                    return '';
                }
                enrichRowAbsoluteDatetimes(row);
                if (row.absolute_start_ms != null) {
                    return msToLocalDateKey(row.absolute_start_ms);
                }
                return getPrepStartDate(row);
            }

            /**
             * Bagian pagi (00:00–07:00) di viewDate hanya jika ada LL di tanggal sebelumnya
             * (delivery_date bar sumber) untuk customer + cycle yang sama.
             */
            function canShowCrossDayPrepContinuation(row, viewDateStr) {
                var view = formatLocalDateKey(viewDateStr);
                var prepStartDate = getPrepStartDate(row);
                var prepEndDate = getPrepEndDate(row);
                if (!view.length || !prepEndDate.length || prepEndDate !== view || prepStartDate >= view) {
                    return false;
                }
                return hasLoadingListOnDeliveryDate(row.customer_id, row.customer_name, rowCycleKey(row),
                    formatLocalDateKey(row.delivery_date));
            }

            /** Layout prep untuk tampilan harian: sembunyikan penanda lintas hari jika tidak ada LL tanggal sumber. */
            function resolvePrepBarLayoutForDisplay(row, hourWindow) {
                var layout = computePrepBarLayoutForRow(row, hourWindow);
                if (!layout || !layout.isOvernightPrep) {
                    return layout;
                }
                if (!hasLoadingListOnDeliveryDate(row.customer_id, row.customer_name, rowCycleKey(row),
                        row.delivery_date)) {
                    layout = Object.assign({}, layout);
                    layout.continuesTomorrow = false;
                    layout.hasOverflow = false;
                    layout.overflowH = 0;
                }
                return layout;
            }

            function slotIndexInWeeklyRange(slots, dateStr) {
                var d = formatLocalDateKey(dateStr);
                for (var i = 0; i < slots.length; i++) {
                    if (formatLocalDateKey(slots[i]) === d) {
                        return i;
                    }
                }
                return -1;
            }

            /** Tampilan mingguan: posisi bar dari absolute_start/end_datetime. */
            function computeWeeklyPrepBarSpan(row, slots) {
                return computeWeeklyPrepBarSpanFromAbsolute(row, slots);
            }

            function collectWeeklyPrepBarsForCustomer(cust, rows, slots) {
                var out = [];
                var rendered = {};

                function pushBar(row, span) {
                    if (!span) {
                        return;
                    }
                    var k = prepRowIdentityKey(row);
                    if (rendered[k]) {
                        return;
                    }
                    rendered[k] = true;
                    out.push({
                        row: row,
                        span: span
                    });
                }
                (rows || []).forEach(function(row) {
                    pushBar(row, computeWeeklyPrepBarSpan(row, slots));
                });
                getAllChartRowsForLookup().forEach(function(row) {
                    if ((row.customer_name || '-') !== cust) {
                        return;
                    }
                    pushBar(row, computeWeeklyPrepBarSpan(row, slots));
                });
                out.sort(function(a, b) {
                    return comparePrepRowsByAbsolute(a.row, b.row);
                });
                return out;
            }

            /** PREP dari hari sebelumnya yang masih berlanjut pagi di viewDate (00:00 → selesai prep). */
            function shouldShowPrepMorningContinuation(row, viewDateStr) {
                var view = formatLocalDateKey(viewDateStr);
                var prepStartDate = getPrepStartDate(row);
                var prepEndDate = getPrepEndDate(row);
                if (!view.length || !prepEndDate.length || prepEndDate !== view || prepStartDate >= view) {
                    return false;
                }
                if (!canShowCrossDayPrepContinuation(row, viewDateStr)) {
                    return false;
                }
                var morningLayout = computePrepMorningContinuationLayout(getRowPrepEndClock(row), ganttHourWindow);
                if (!morningLayout || !morningLayout.segments || !morningLayout.segments.length) {
                    return false;
                }
                var prepLayout = computePrepBarLayoutForRow(row, ganttHourWindow);
                if (prepLayout && (prepLayout.continuesTomorrow || prepLayout.isOvernightPrep)) {
                    return true;
                }
                return isPrepOvernight(row);
            }

            function getPrepContinuationsForViewDate(viewDateStr) {
                if (!viewDateStr) {
                    return [];
                }
                var sourceRows = getAllChartRowsForLookup().filter(function(r) {
                    return shouldShowPrepMorningContinuation(r, viewDateStr);
                });
                return sourceRows.map(function(row) {
                    var layout = computePrepMorningContinuationLayout(getRowPrepEndClock(row),
                        ganttHourWindow);
                    if (!layout) {
                        return null;
                    }
                    return {
                        row: row,
                        layout: layout
                    };
                }).filter(function(x) {
                    return !!x;
                });
            }

            /**
             * Tanggal sumbu timeline harian (00:00–24:00).
             * Rentang filter "dari–sampai" memakai tanggal Sampai (hari delivery),
             * bukan tanggal Dari, agar H-1 tidak salah dijadikan sumbu hari.
             */
            function getDailyFilterViewDate() {
                var from = String($('#filterDateFrom').val() || '').trim();
                var to = String($('#filterDateTo').val() || '').trim();
                if (!from.length && !to.length) {
                    return '';
                }
                if (from.length && to.length) {
                    return from === to ? from : to;
                }
                return from || to;
            }

            function getDeliveryFilterDateRange() {
                var from = String($('#filterDateFrom').val() || '').trim();
                var to = String($('#filterDateTo').val() || '').trim();
                if (!from.length && !to.length) {
                    return {
                        from: '',
                        to: ''
                    };
                }
                if (!to.length) {
                    to = from;
                }
                if (!from.length) {
                    from = to;
                }
                if (from > to) {
                    var swap = from;
                    from = to;
                    to = swap;
                }
                return {
                    from: from,
                    to: to
                };
            }

            function injectPrepContinuationBars(overdueLatch) {
                var viewDate = getDailyFilterViewDate();
                if (!viewDate.length) {
                    return;
                }
                var prepContinuations = getPrepContinuationsForViewDate(viewDate);
                if (!prepContinuations.length) {
                    return;
                }
                var contByCustomer = {};
                prepContinuations.forEach(function(item) {
                    var cn = item.row.customer_name || '-';
                    if (!contByCustomer[cn]) {
                        contByCustomer[cn] = [];
                    }
                    contByCustomer[cn].push(item);
                });
                $('#ganttContainer .gantt-grid-row').each(function() {
                    if ($(this).hasClass('gantt-grid-header')) {
                        return;
                    }
                    var cn = $(this).find('.gantt-grid-customer').first().attr('data-customer-name') || '';
                    var barHtml = buildPrepContinuationBarHtml(cn, contByCustomer, viewDate, overdueLatch);
                    if (!barHtml) {
                        return;
                    }
                    $(this).find('.gantt-grid-track-lane').first().find('.gantt-track').first().append(
                        barHtml);
                });
            }

            function buildPrepContinuationBarHtml(cn, contByCustomer, viewDate, overdueLatch) {
                var html = '';
                var gateRows = getCustomerPrepRowsForSequentialGate(cn, viewDate);
                var currentFrac = timeToFrac(String(new Date().getHours()).padStart(2, '0') + ':' + String(
                    new Date().getMinutes()).padStart(2, '0'));
                (contByCustomer[cn] || []).forEach(function(item) {
                    var row = item.row;
                    if (String(row.delivery_date || '').trim() === String(viewDate || '').trim()) {
                        return;
                    }
                    if (!canShowCrossDayPrepContinuation(row, viewDate)) {
                        return;
                    }
                    var contLayout = item.layout;
                    var segs = contLayout.segments || [];
                    if (!segs.length) {
                        return;
                    }
                    var prepLayout = computePrepBarLayoutForRow(row, ganttHourWindow);
                    var prepStatus = resolvePrepBarStatus(row, gateRows, viewDate, currentFrac,
                        overdueLatch || {},
                        prepLayout);
                    var lateInfo = buildPrepLateInfo(row, prepStatus);
                    var progressWidthC = prepStatus.progressWidth;
                    var barClassC = prepStatus.barClass;
                    var avC = 'C' + String(rowCycleKey(row) || '?').toUpperCase();
                    var srcDate = getPrepCrossDaySourceDateKey(row);
                    var crossDayLabel = formatCrossDaySourceLabel(srcDate);
                    var contTitle = buildGanttTooltip(row, {
                        crossDayFromDate: srcDate,
                        crossDayViewDate: viewDate,
                        prepFromYesterday: true,
                        prepDayOffsetLabel: row.prep_day_offset_label || formatPrepDayOffsetLabel(
                            getRowPrepDayOffset(row)),
                        prepEndDayOffsetLabel: row.prep_end_day_offset_label ||
                            formatPrepDayOffsetLabel(getRowPrepEndDayOffset(row)),
                        prepEndClock: contLayout.prepEndClock,
                        displayPct: prepStatus.isBlocked ? 0 : Math.round(getRowRawProgressPct(
                            row) * 10) / 10,
                        blockedByPrior: prepStatus.isBlocked,
                        lateInfo: lateInfo
                    }).replace(/"/g, '&quot;');
                    segs.forEach(function(seg) {
                        html +=
                            '<div class="gantt-bar-wrap is-prep-from-yesterday is-cross-day-continuation' +
                            (lateInfo.isLate ? ' is-prep-late' : '') + '" style="left:' +
                            seg.leftPct + '%;width:' + Math.max(seg.widthPct, 100 /
                            ganttHourWindow) +
                            '%;--cross-day-label:&quot;' + escapeAttr(crossDayLabel) +
                            '&quot;" title="' +
                            contTitle + '"';
                        html += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        html += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        html += ' data-cross-day-from="' + escapeAttr(srcDate) + '"';
                        html += ' data-delivery-date-from="' + escapeAttr(formatLocalDateKey(
                            row.delivery_date)) + '"';
                        html += ' data-delivery-date-to="' + escapeAttr(formatLocalDateKey(
                            row.delivery_date)) + '"';
                        if (lateInfo.isLate) {
                            html += ' data-prep-late-detail="' + escapeAttr(JSON.stringify(
                                lateInfo)) + '"';
                        }
                        html += '>';
                        html += buildGanttProgressStackHtml(progressWidthC, barClassC,
                            getGanttBarDisplayPct(row, prepStatus), prepLayout);
                        html += buildPrepLateBadgeHtml(lateInfo);
                        html +=
                            '<span class="pill-meta"><span class="pill-avatar" style="opacity:.9">' +
                            escapeHtml(avC) + '</span></span></div>';
                    });
                });
                return html;
            }

            function getTruckDisplayDate(row) {
                var base = formatLocalDateKey(row.delivery_date || $('#filterDateFrom').val() || '');
                if (!base.length) {
                    return base;
                }
                var truckAt = normalizeClock(row.truck_time);
                if (!truckAt.length) {
                    return base;
                }
                var prepStartDate = getPrepStartDate(row);
                var prepEndDate = getPrepEndDate(row);
                var prepStart = getRowPrepStartClock(row);

                /**
                 * PREP lintas malam: truck pagi (jam < jam mulai prep) jatuh pada tanggal selesai prep,
                 * bukan delivery_date + 1. Contoh prep H-1 19:30–00:30, truck 05:30 → tampil di hari delivery (H).
                 */
                if (isPrepOvernight(row) && prepStart.length && prepEndDate.length) {
                    var truckMin = timeToMinutes(truckAt);
                    var startMin = timeToMinutes(prepStart);
                    if (truckMin >= startMin) {
                        return prepStartDate.length ? prepStartDate : base;
                    }
                    return prepEndDate;
                }

                if (isTruckOnNextDay(row)) {
                    var anchor = prepStartDate.length ? prepStartDate : base;
                    return addDaysToDateStr(anchor, 1);
                }

                return prepStartDate.length ? prepStartDate : base;
            }

            function isTruckCrossMidnight(row) {
                if (!row || !normalizeClock(row.truck_time).length) {
                    return false;
                }
                if (isPrepOvernight(row)) {
                    return true;
                }
                if (isTruckOnNextDay(row)) {
                    return true;
                }
                var prepStartDate = getPrepStartDate(row);
                var truckDisplayDate = getTruckDisplayDate(row);
                return prepStartDate.length && truckDisplayDate.length && prepStartDate !== truckDisplayDate;
            }

            function getTruckCrossDaySourceDate(row) {
                if (!isTruckCrossMidnight(row)) {
                    return '';
                }
                return getPrepStartDate(row) || formatLocalDateKey(row.delivery_date);
            }

            function formatTruckCrossDayDateRangeLabel(sourceDate, displayDate) {
                var from = formatDeliveryDateShort(sourceDate);
                var to = formatDeliveryDateShort(displayDate);
                if (!from.length) {
                    return 'Lintas malam';
                }
                if (!to.length || from === to) {
                    return 'Dari ' + from;
                }
                return 'Dari ' + from + ' \u2192 ' + to;
            }

            function buildTruckTooltipOpts(row) {
                var truckAt = normalizeClock(row.truck_time);
                var opts = {
                    truckClock: truckAt,
                    truckNextDay: isTruckOnNextDay(row),
                    prepDayOffsetLabel: getRowPrepOffsetLabel(row),
                    displayPct: Math.round(getRowRawProgressPct(row) * 10) / 10
                };
                var prepStart = getRowPrepStartClock(row);
                var prepEnd = getRowPrepEndClock(row) || '';
                if (prepStart.length) {
                    var ps = formatClockDot(prepStart);
                    var pe = prepEnd.length ? formatClockDot(prepEnd) : '';
                    opts.prepRange = pe.length ? ps + '\u2013' + pe : ps;
                }
                if (isTruckCrossMidnight(row)) {
                    opts.truckCrossMidnight = true;
                    opts.crossDayFromDate = getTruckCrossDaySourceDate(row);
                    opts.truckDisplayDate = getTruckDisplayDate(row);
                }
                return opts;
            }

            function resolveTruckTimelineFrac(row) {
                var truckAt = normalizeClock(row.truck_time);
                if (!truckAt.length) {
                    return null;
                }
                var baseFrac = timeToWindowFrac(truckAt);
                if (baseFrac === null) {
                    return null;
                }
                if (isTruckOnNextDay(row)) {
                    return baseFrac;
                }
                return baseFrac;
            }

            function setGanttSlotCount(count) {
                var n = 24;
                ganttCols = n;
                ganttHourWindow = n;
                $('#deliveryTimelineCard').css('--gantt-slot-count', String(n));
            }

            function scaleTimelineMarkerPct(pctOn24h) {
                return pctOn24h;
            }

            function calcDurationHours(startClock, endClock) {
                var s = timeToWindowFrac(startClock),
                    e = timeToWindowFrac(endClock);
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
                var h = parseInt(p[0] || '0', 10),
                    m = parseInt(p[1] || '0', 10);
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

            function getOverdueLatchMap() {
                try {
                    return JSON.parse(localStorage.getItem(ganttOverdueLatchStorageKey) || '{}');
                } catch (err) {
                    return {};
                }
            }

            function setOverdueLatchMap(map) {
                localStorage.setItem(ganttOverdueLatchStorageKey, JSON.stringify(map));
            }

            function rowOverdueLatchKey(row) {
                enrichRowAbsoluteDatetimes(row);
                var dd = formatLocalDateKey(row.delivery_date) || '';
                var ps = getRowPrepStartClock(row);
                var pe = getRowPrepEndClock(row) || '';
                var ct = (row.cycle_time != null) ? String(row.cycle_time).trim().substring(0, 5) : '';
                return [dd, String(row.customer_id || ''), String(row.customer_name || ''), String(row
                    .cycle_name || ''), ps, pe, ct].join('|');
            }

            function resolveRowFinishPrepDeadlineMs(row) {
                if (!row) {
                    return null;
                }
                enrichRowAbsoluteDatetimes(row);
                var finishClock = getRowFinishPrepClock(row);
                if (!finishClock) {
                    return row.absolute_end_ms || null;
                }
                var finishDate = '';
                if (row.prep_end_time != null && String(row.prep_end_time).trim().length) {
                    finishDate = getPrepEndDate(row);
                } else if (row.cycle_time && String(row.cycle_time).trim().length) {
                    finishDate = formatLocalDateKey(row.delivery_date);
                }
                if (!finishDate.length) {
                    return row.absolute_end_ms || null;
                }
                var iso = buildAbsoluteDatetimeLocal(finishDate, finishClock);
                if (!iso) {
                    return row.absolute_end_ms || null;
                }
                var ms = new Date(iso).getTime();
                return isNaN(ms) ? (row.absolute_end_ms || null) : ms;
            }

            function isRowPastFinishPrepDeadline(row) {
                var deadlineMs = resolveRowFinishPrepDeadlineMs(row);
                return deadlineMs != null && Date.now() >= deadlineMs;
            }

            function getRowPullingItems(row) {
                var rawItems = Array.isArray(row && row.pulling_items) ? row.pulling_items : [];
                if (!rawItems.length && row && (row.latest_pulling_date || row.pulling_date)) {
                    rawItems = [{
                        pds_number: Array.isArray(row.pds_numbers) ? row.pds_numbers[0] : '',
                        loading_list: Array.isArray(row.loading_lists) ? row.loading_lists[0] : '',
                        pulling_date: row.latest_pulling_date || row.pulling_date
                    }];
                }
                return rawItems.map(function(item) {
                    var rawDate = item && (item.pulling_date || item.latest_pulling_date);
                    var ms = parseAbsoluteDatetimeLocal(rawDate);
                    return {
                        pdsNumber: item && item.pds_number ? String(item.pds_number) : '-',
                        loadingList: item && item.loading_list ? String(item.loading_list) : '-',
                        pullingDate: rawDate || '',
                        pullingMs: isNaN(ms) ? null : ms
                    };
                }).filter(function(item) {
                    return item.pullingMs != null;
                });
            }

            function getRowLatePullingItems(row) {
                var deadlineMs = resolveRowFinishPrepDeadlineMs(row);
                if (deadlineMs == null) {
                    return [];
                }
                return getRowPullingItems(row).filter(function(item) {
                    return item.pullingMs > deadlineMs;
                }).map(function(item) {
                    item.delayMs = item.pullingMs - deadlineMs;
                    return item;
                });
            }

            function isRowPullingPastFinishPrep(row) {
                return getRowLatePullingItems(row).length > 0;
            }

            function formatDelayShort(delayMs) {
                var minutes = Math.max(0, Math.round((parseInt(delayMs, 10) || 0) / 60000));
                var hours = Math.floor(minutes / 60);
                var mins = minutes % 60;
                if (hours > 0 && mins > 0) {
                    return '+' + hours + 'j ' + mins + 'm';
                }
                if (hours > 0) {
                    return '+' + hours + 'j';
                }
                return '+' + minutes + 'm';
            }

            function formatDateTimeLabel(ms) {
                if (ms == null) {
                    return '-';
                }
                return new Date(ms).toLocaleString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function buildPrepLateInfo(row, prepStatus) {
                var deadlineMs = resolveRowFinishPrepDeadlineMs(row);
                var rawPct = Math.round(getRowRawProgressPct(row) * 10) / 10;
                var latePullingItems = getRowLatePullingItems(row);
                var progressLate = deadlineMs != null && rawPct < 100 && Date.now() >= deadlineMs;
                var latchedLate = !!(prepStatus && prepStatus.isOverdue) && !progressLate && !latePullingItems
                    .length;
                var maxDelayMs = 0;
                latePullingItems.forEach(function(item) {
                    maxDelayMs = Math.max(maxDelayMs, item.delayMs || 0);
                });
                if (progressLate && deadlineMs != null) {
                    maxDelayMs = Math.max(maxDelayMs, Date.now() - deadlineMs);
                }
                var reasons = [];
                if (latePullingItems.length) {
                    reasons.push('Pulling lewat Finish Prep');
                }
                if (progressLate) {
                    reasons.push('Prep belum 100% saat lewat Finish Prep');
                }
                if (latchedLate) {
                    reasons.push('Pernah lewat Finish Prep sebelum 100%');
                }
                return {
                    isLate: reasons.length > 0,
                    isPullingLate: latePullingItems.length > 0,
                    isProgressLate: progressLate,
                    reasons: reasons,
                    delayMs: maxDelayMs,
                    delayLabel: maxDelayMs > 0 ? formatDelayShort(maxDelayMs) : '',
                    customerName: row.customer_name || '-',
                    cycleName: row.cycle_name || '-',
                    deliveryDate: formatLocalDateKey(row.delivery_date),
                    prepDeadlineLabel: formatDateTimeLabel(deadlineMs),
                    progressPct: rawPct,
                    done: parseInt(row.total_done || 0, 10),
                    target: parseInt(row.total_target || 0, 10),
                    pdsNumbers: Array.isArray(row.pds_numbers) ? row.pds_numbers : [],
                    loadingLists: Array.isArray(row.loading_lists) ? row.loading_lists : [],
                    items: latePullingItems.map(function(item) {
                        return {
                            pdsNumber: item.pdsNumber,
                            loadingList: item.loadingList,
                            pullingLabel: formatDateTimeLabel(item.pullingMs),
                            delayLabel: formatDelayShort(item.delayMs)
                        };
                    })
                };
            }

            function buildPrepLateBadgeHtml(lateInfo) {
                if (!lateInfo || !lateInfo.isLate) {
                    return '';
                }
                var label = lateInfo.delayLabel || (lateInfo.items && lateInfo.items.length ? lateInfo.items
                    .length +
                    ' LL' : 'Telat');
                return '<span class="prep-late-badge">' + escapeHtml(label) + '</span>';
            }

            function buildPrepLateTooltipParts(lateInfo) {
                if (!lateInfo || !lateInfo.isLate) {
                    return [];
                }
                var parts = lateInfo.reasons.slice();
                if (lateInfo.delayLabel) {
                    parts.push('Telat ' + lateInfo.delayLabel);
                }
                if (lateInfo.items && lateInfo.items.length) {
                    parts.push(lateInfo.items.length + ' LL/PDS telat');
                }
                return parts;
            }

            function buildCustomerPrepLateSummaryHtml(rows) {
                var seen = {};
                var count = 0;
                var maxDelayMs = 0;
                var reasonMap = {};
                var latchMap = getOverdueLatchMap();
                (rows || []).forEach(function(row) {
                    if (!row || !rowHasConfiguredMasterCycle(row)) {
                        return;
                    }
                    var key = prepRowIdentityKey(row);
                    if (seen[key]) {
                        return;
                    }
                    seen[key] = true;
                    var info = buildPrepLateInfo(row, {
                        isOverdue: !!latchMap[rowOverdueLatchKey(row)]
                    });
                    if (!info.isLate) {
                        return;
                    }
                    count++;
                    maxDelayMs = Math.max(maxDelayMs, info.delayMs || 0);
                    info.reasons.forEach(function(reason) {
                        reasonMap[reason] = true;
                    });
                });
                if (!count) {
                    return '';
                }
                var title = Object.keys(reasonMap).join(' · ');
                if (maxDelayMs > 0) {
                    title += (title ? ' · ' : '') + 'terlama ' + formatDelayShort(maxDelayMs);
                }
                return '<span class="customer-late-note" title="' + escapeAttr(title) + '">' + count +
                    ' telat</span>';
            }

            function collectPrepLateSummary(rows) {
                var latchMap = getOverdueLatchMap();
                var customers = {};
                var lateItems = {};
                var fallbackItems = 0;
                var maxDelayMs = 0;
                var seenRows = {};
                (rows || []).forEach(function(row) {
                    if (!row || !rowHasConfiguredMasterCycle(row)) {
                        return;
                    }
                    var rowKey = prepRowIdentityKey(row);
                    if (seenRows[rowKey]) {
                        return;
                    }
                    seenRows[rowKey] = true;
                    var info = buildPrepLateInfo(row, {
                        isOverdue: !!latchMap[rowOverdueLatchKey(row)]
                    });
                    if (!info.isLate) {
                        return;
                    }
                    customers[row.customer_name || '-'] = true;
                    maxDelayMs = Math.max(maxDelayMs, info.delayMs || 0);
                    if (info.items && info.items.length) {
                        info.items.forEach(function(item) {
                            lateItems[(item.pdsNumber || '-') + '|' + (item.loadingList || '-')] =
                                true;
                        });
                        return;
                    }
                    var refs = Array.isArray(row.loading_lists) && row.loading_lists.length ? row
                        .loading_lists :
                        (Array.isArray(row.pds_numbers) ? row.pds_numbers : []);
                    if (!refs.length) {
                        fallbackItems++;
                        return;
                    }
                    refs.forEach(function(ref) {
                        lateItems[(row.customer_id || '-') + '|' + String(ref)] = true;
                    });
                });
                return {
                    customerCount: Object.keys(customers).length,
                    itemCount: Object.keys(lateItems).length + fallbackItems,
                    maxDelayMs: maxDelayMs
                };
            }

            function updateTimelineLateSummary() {
                var rows = chartMergedByCustTime.length ? chartMergedByCustTime :
                    filterRowsWithConfiguredMasterCycle(
                        chartRows);
                var summary = collectPrepLateSummary(rows);
                var hasLate = summary.customerCount > 0;
                var text = hasLate ?
                    (summary.customerCount + ' customer telat · ' + summary.itemCount + ' LL telat' +
                        (summary.maxDelayMs > 0 ? ' · terlama ' + formatDelayShort(summary.maxDelayMs) : '')) :
                    'Tidak ada prep telat';
                $('#timelineLateSummary, #weeklyLateSummary').text(text).toggleClass('is-empty', !hasLate);
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
                enrichRowAbsoluteDatetimes(row);

                /** Belum masuk jadwal prep (mis. C1 H+1 malam saat C3 H+0 masih jalan) → belum lewat finish. */
                if (row.absolute_start_ms != null && Date.now() < row.absolute_start_ms) {
                    return false;
                }

                var deadlineMs = resolveRowFinishPrepDeadlineMs(row);
                if (deadlineMs != null) {
                    return Date.now() >= deadlineMs;
                }

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
                var startFrac = timeToFrac(getRowPrepStartClock(row));
                /** Lintas malam: jam selesai pagi bukan "lewat" saat masih siang sebelum mulai malam. */
                if (startFrac !== null && finishFrac < startFrac) {
                    return false;
                }
                return currentFrac >= finishFrac;
            }

            /** Sudah melewati jam selesai PREP terjadwal (prep_end_time) — prep di belakang boleh mulai. */
            function isRowPastScheduledPrepEnd(row, currentFrac) {
                if (currentFrac === null || typeof currentFrac === 'undefined') {
                    return false;
                }
                var prepEnd = getRowPrepEndClock(row);
                if (!prepEnd) {
                    return false;
                }
                var endFrac = timeToFrac(prepEnd);
                if (endFrac === null) {
                    return false;
                }
                var startFrac = timeToFrac(getRowPrepStartClock(row));
                if (startFrac !== null && endFrac < startFrac) {
                    return currentFrac > endFrac && currentFrac < startFrac;
                }
                return currentFrac >= endFrac;
            }

            function resolveCurrentTimelineFrac(currentFrac) {
                if (currentFrac !== null && typeof currentFrac !== 'undefined') {
                    return currentFrac;
                }
                var nowWall = new Date();
                var currentClock = String(nowWall.getHours()).padStart(2, '0') + ':' + String(nowWall
                    .getMinutes()).padStart(2, '0');
                return timeToFrac(currentClock);
            }

            function checkAndSendUnfinishedWaNotification() {
                if (!chartMergedByCustTime.length) {
                    return;
                }
                var now = new Date();
                var currentClock = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes())
                    .padStart(2, '0');
                var currentFrac = timeToFrac(currentClock);
                var dateFromVal = $('#filterDateFrom').val() || '';
                var dateToVal = $('#filterDateTo').val() || '';
                var sentMap = getWaSentMap();
                var toNotify = [];
                var sentKeyBucket = [];
                chartMergedByCustTime.forEach(function(row) {
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
                    var sentKey = [dateFromVal || '-', dateToVal || '-', row.customer_id || '-', row
                        .cycle_name || '-', row.cycle_time || '-'
                    ].join('|');
                    if (sentMap[sentKey]) {
                        return;
                    }
                    sentKeyBucket.push(sentKey);
                    toNotify.push({
                        customer_id: parseInt(row.customer_id || 0, 10),
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
                }).done(function() {
                    sentKeyBucket.forEach(function(k) {
                        sentMap[k] = true;
                    });
                    setWaSentMap(sentMap);
                });
            }

            function slotLabelsForWindow(hourWindow) {
                var a = [];
                for (var i = 0; i < 24; i++) {
                    var h = (getTimelineStartHour() + i) % 24;
                    a.push(formatClockDot(String(h).padStart(2, '0') + ':00'));
                }
                return a;
            }

            function slotLabels24() {
                return slotLabelsForWindow(24);
            }

            function escapeHtml(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function escapeAttr(s) {
                return String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;');
            }

            /* ── renderMasterTable ── */
            function renderMasterTable(rows, highlightId) {
                var $tb = $('#masterCycleTable tbody');
                $tb.empty();
                if (!rows.length) {
                    $tb.append(
                        '<tr><td colspan="7" class="text-center" style="color:var(--text-muted);padding:20px;">Belum ada master cycle.</td></tr>'
                    );
                    return;
                }
                rows.forEach(function(r, i) {
                    var cust = r.customer_name || (r.customer_id ? '#' + r.customer_id : '-');
                    var prepStart = formatClockDot(r.time);
                    var prepEnd = (r.prep_end_time && String(r.prep_end_time).length) ? formatClockDot(r
                        .prep_end_time) : '-';
                    if (prepEnd === prepStart) {
                        prepEnd = '-';
                    }
                    var prepRangeDisp = prepStart + ' – ' + prepEnd;
                    var startOffLbl = r.prep_day_offset_label || formatPrepDayOffsetLabel(r
                        .prep_day_offset || 0);
                    var endOffLbl = r.prep_end_day_offset_label || formatPrepDayOffsetLabel(
                        r.prep_end_day_offset != null ? r.prep_end_day_offset : (r.prep_day_offset || 0)
                        );
                    var offsetDisp = startOffLbl;
                    if (endOffLbl !== startOffLbl) {
                        offsetDisp += ' → ' + endOffLbl;
                    }
                    if (r.prep_crosses_midnight) {
                        offsetDisp +=
                            ' <span style="font-size:10px;color:var(--sky);">(lintas malam)</span>';
                    }
                    var truckDisp = (r.truck_time && String(r.truck_time).length) ? formatClockDot(r
                        .truck_time) : '<span style="color:var(--text-muted);">-</span>';
                    var rowClass = (highlightId && String(r.id) === String(highlightId)) ?
                        ' class="is-master-highlight"' : '';
                    $tb.append(
                        '<tr' + rowClass + ' data-master-id="' + escapeAttr(r.id) + '">' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + escapeHtml(cust) + '</td>' +
                        '<td>' + escapeHtml(r.cycle_name) + '</td>' +
                        '<td>' + prepRangeDisp + '</td>' +
                        '<td>' + offsetDisp + '</td>' +
                        '<td>' + truckDisp + '</td>' +
                        '<td style="white-space:nowrap;">' +
                        (canManageMaster ?
                            ('<a class="act-btn primary btn-edit-master" data-id="' + r.id +
                                '" style="height:24px;padding:0 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;margin-right:4px;">' +
                                '<i class="fas fa-edit"></i> Edit</a>' +
                                '<a class="act-btn danger btn-del-master" data-id="' + r.id +
                                '" style="height:24px;padding:0 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;">' +
                                '<i class="fas fa-trash"></i> Hapus</a>') :
                            '<span style="color:var(--text-muted);">-</span>') +
                        '</td>' +
                        '</tr>'
                    );
                });
            }

            var masterHighlightTimer = null;

            function scrollToMasterRow(highlightId) {
                if (!highlightId) {
                    return;
                }
                var $row = $('#masterCycleTable tbody tr[data-master-id="' + highlightId + '"]');
                if (!$row.length) {
                    return;
                }
                $row[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            function scheduleMasterHighlightClear() {
                if (masterHighlightTimer) {
                    clearTimeout(masterHighlightTimer);
                }
                masterHighlightTimer = setTimeout(function() {
                    $('#masterCycleTable tbody tr.is-master-highlight').removeClass('is-master-highlight');
                    masterHighlightTimer = null;
                }, 5000);
            }

            function fetchMasters(highlightId) {
                $.get(masterIndex, function(res) {
                    renderMasterTable((res && res.data) ? res.data : [], highlightId);
                    if (highlightId) {
                        scrollToMasterRow(highlightId);
                        scheduleMasterHighlightClear();
                    }
                });
            }

            function showMasterCycleAddedSwal(payload, customerName, offsetLabel) {
                var prepRange = formatClockDot(payload.time) + ' – ' + formatClockDot(payload.prep_end_time);
                var offsetText = offsetLabel || formatPrepDayOffsetLabel(payload.prep_day_offset || 0);
                Swal.fire({
                    icon: 'success',
                    title: 'Master Cycle Tersimpan',
                    html: '<div class="master-cycle-swal-detail">' +
                        '<div class="master-cycle-swal-row"><span>Customer</span><strong>' + escapeHtml(
                            customerName || '-') + '</strong></div>' +
                        '<div class="master-cycle-swal-row"><span>Cycle</span><strong>' + escapeHtml(payload
                            .cycle_name || '-') + '</strong></div>' +
                        '<div class="master-cycle-swal-row"><span>Prep</span><strong>' + escapeHtml(
                            prepRange) +
                        '</strong></div>' +
                        '<div class="master-cycle-swal-row"><span>Offset</span><strong>' + escapeHtml(
                            offsetText) +
                        '</strong></div>' +
                        '<div class="master-cycle-swal-row"><span>ETA Truck</span><strong>' + escapeHtml(
                            formatClockDot(payload.truck_time)) + '</strong></div>' +
                        '</div>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0097d8',
                    customClass: {
                        popup: 'master-cycle-swal-popup'
                    }
                });
            }

            function showMasterCycleErrorSwal(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: message || 'Gagal menyimpan master cycle.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0097d8'
                });
            }

            function showMasterCycleValidationSwal(message) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0097d8'
                });
            }

            $('#masterCycleForm').on('submit', function(e) {
                e.preventDefault();
                var prepStart = ($('#mcyclePrepStart').val() || '').trim();
                var prepEnd = ($('#mcyclePrepEnd').val() || '').trim();
                var etaTruck = ($('#mcycleTruckTime').val() || '').trim();
                if (!prepStart || !prepEnd || !etaTruck) {
                    showMasterCycleValidationSwal(
                        'Waktu preparation (mulai-selesai) dan ETA truck wajib diisi.');
                    return;
                }
                var payload = {
                    _token: csrf,
                    cycle_name: $('#mcycleName').val().trim(),
                    time: prepStart,
                    prep_end_time: prepEnd,
                    prep_day_offset: $('#mcyclePrepDayOffset').val(),
                    truck_time: etaTruck,
                    customer_id: $('#mcycleCustomerId').val()
                };
                if (editMasterId) {
                    $.ajax({
                        url: masterBase + '/' + editMasterId,
                        type: 'PUT',
                        data: payload,
                        success: function() {
                            editMasterId = null;
                            $('#btnMasterSave').html('<i class="fas fa-save mr-1"></i> Simpan');
                            $('#btnMasterCancel').addClass('d-none');
                            $('#masterCycleForm')[0].reset();
                            fetchMasters();
                            loadStackedChart();
                        }
                    });
                } else {
                    var savedCustomerName = $('#mcycleCustomerId option:selected').text().trim();
                    var savedOffsetLabel = $('#mcyclePrepDayOffset option:selected').text().trim();
                    $.post(masterStore, payload)
                        .done(function(res) {
                            var newId = (res && res.data && res.data.id) ? res.data.id : null;
                            $('#masterCycleForm')[0].reset();
                            showMasterCycleAddedSwal(payload, savedCustomerName, savedOffsetLabel);
                            fetchMasters(newId);
                            loadStackedChart();
                        })
                        .fail(function(xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ?
                                xhr.responseJSON.message :
                                'Gagal menyimpan master cycle.';
                            showMasterCycleErrorSwal(msg);
                        });
                }
            });

            $('#btnMasterCancel').on('click', function() {
                editMasterId = null;
                $('#btnMasterSave').html('<i class="fas fa-save mr-1"></i> Simpan');
                $('#btnMasterCancel').addClass('d-none');
                $('#masterCycleForm')[0].reset();
            });

            $('#masterCycleTable').on('click', '.btn-del-master', function() {
                var id = $(this).data('id');
                if (!confirm('Hapus master cycle ini?')) {
                    return;
                }
                $.ajax({
                    url: masterBase + '/' + id,
                    type: 'DELETE',
                    data: {
                        _token: csrf
                    },
                    success: function() {
                        fetchMasters();
                        loadStackedChart();
                    }
                });
            });

            $('#mcyclePrepStart, #mcyclePrepEnd, #mcyclePrepDayOffset, #mcycleTruckTime').on('input change',
                updateMasterCycleOffsetHint);
            updateMasterCycleOffsetHint();

            $('#masterCycleTable').on('click', '.btn-edit-master', function() {
                var id = $(this).data('id');
                $.get(masterIndex, function(res) {
                    var rows = (res && res.data) ? res.data : [];
                    var row = rows.find(function(r) {
                        return String(r.id) === String(id);
                    });
                    if (!row) {
                        return;
                    }
                    editMasterId = id;
                    if ($('#mcycleName option[value="' + row.cycle_name + '"]').length) {
                        $('#mcycleName').val(row.cycle_name);
                    }
                    var prepStart = row.time.length === 5 ? row.time : row.time.substring(0, 5);
                    var prepEnd = row.prep_end_time ? (row.prep_end_time.length === 5 ? row
                        .prep_end_time : row.prep_end_time.substring(0, 5)) : '';
                    if (prepEnd === prepStart) {
                        prepEnd = '';
                    }
                    var etaTruck = row.truck_time ? (row.truck_time.length === 5 ? row.truck_time :
                        row.truck_time.substring(0, 5)) : '';
                    $('#mcyclePrepStart').val(prepStart);
                    $('#mcyclePrepEnd').val(prepEnd);
                    $('#mcyclePrepDayOffset').val(String(row.prep_day_offset != null ? row
                        .prep_day_offset : 0));
                    $('#mcycleTruckTime').val(etaTruck);
                    $('#mcycleCustomerId').val(row.customer_id != null ? String(row.customer_id) :
                        '');
                    updateMasterCycleOffsetHint();
                    $('#btnMasterSave').html('<i class="fas fa-save mr-1"></i> Update');
                    $('#btnMasterCancel').removeClass('d-none');
                });
            });

            /* ── Gantt / chart functions (unchanged) ── */
            /* All original Gantt rendering functions are preserved below */

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

            function updateTimelineTotal() {
                var sum = chartMergedByCustTime.reduce(function(acc, r) {
                    return acc + (parseInt(r.total_done, 10) || 0);
                }, 0);
                $('#timelineTotalSum').text('Total: ' + sum);
                updateTimelineLateSummary();
            }

            function buildWeeklyTooltip(details) {
                if (!details.length) {
                    return 'Tidak ada detail cycle.';
                }
                return details.map(function(d) {
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
                if (weeklyGridTimer) {
                    clearInterval(weeklyGridTimer);
                    weeklyGridTimer = null;
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
                            text: cursor.toLocaleDateString('id-ID', {
                                weekday: 'short',
                                day: '2-digit',
                                month: 'short'
                            }),
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
                weeklyNowTimer = setInterval(function() {
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

            function buildWeeklyDateTime(deliveryDate, clock) {
                var datePart = String(deliveryDate || '').trim();
                var timePart = String(clock || '').trim();
                if (!datePart.length || !timePart.length) {
                    return null;
                }
                var normalizedClock = timePart.substring(0, 5);
                var dt = new Date(datePart + 'T' + normalizedClock + ':00');
                if (isNaN(dt.getTime())) {
                    return null;
                }
                return dt;
            }

            // ── Weekly HTML-grid helpers (sesuai kode terbaru) ────────────────
            /** YYYY-MM-DD di timezone lokal (hindari toISOString() yang menggeser hari pertama rentang). */
            function formatLocalDateKey(dt) {
                if (!dt) {
                    return '';
                }
                if (typeof dt === 'string') {
                    return String(dt).trim().substring(0, 10);
                }
                return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt
                    .getDate()).padStart(2, '0');
            }

            function todayLocalDateKey() {
                return formatLocalDateKey(new Date());
            }

            function weeklyDaySlots(dateFrom, dateTo) {
                var slots = [];
                var d = new Date(dateFrom + 'T00:00:00');
                var end = new Date(dateTo + 'T00:00:00');
                if (isNaN(d.getTime()) || isNaN(end.getTime())) {
                    return slots;
                }
                while (d <= end) {
                    slots.push(formatLocalDateKey(d));
                    d.setDate(d.getDate() + 1);
                }
                return slots;
            }

            function weeklyDateLabel(dateKey) {
                var dt = new Date(formatLocalDateKey(dateKey) + 'T12:00:00');
                if (isNaN(dt.getTime())) {
                    return String(dateKey || '');
                }
                return dt.toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short'
                });
            }

            function weeklyFracOfDay(deliveryDate, clockStr) {
                if (!clockStr || clockStr === '-') {
                    return null;
                }
                var frac = timeToWindowFrac(clockStr);
                if (frac === null) {
                    return null;
                }
                return frac / 24;
            }

            function mergeRowsForCustomerTime(rows) {
                var map = {};
                rows.forEach(function(row) {
                    enrichRowAbsoluteDatetimes(row);
                    var key = (row.customer_id || '') + '|' + rowCycleKey(row) + '|' + formatLocalDateKey(
                        row.delivery_date) + '|' + (row.absolute_start_datetime || row.cycle_time || '');
                    if (!map[key]) {
                        map[key] = Object.assign({}, row, {
                            total_done: 0,
                            total_target: 0,
                            ll_count: 0
                        });
                    }
                    map[key].total_done += parseInt(row.total_done || 0, 10);
                    map[key].total_target += parseInt(row.total_target || 0, 10);
                    map[key].ll_count += parseInt(row.ll_count || 0, 10);
                });
                Object.keys(map).forEach(function(k) {
                    var m = map[k];
                    m.progress_pct = m.total_target > 0 ? (m.total_done / m.total_target) * 100 : 0;
                });
                return Object.values(map).sort(function(a, b) {
                    var cn = (a.customer_name || '').localeCompare(b.customer_name || '');
                    if (cn !== 0) {
                        return cn;
                    }
                    return comparePrepRowsByAbsolute(a, b);
                });
            }

            function getPrepOffsetCssClass(offset) {
                var n = parseInt(offset, 10) || 0;
                if (n < 0) {
                    return 'is-offset-h-minus';
                }
                if (n > 0) {
                    return 'is-offset-h-plus';
                }
                return 'is-offset-h-zero';
            }

            function getRowPrepOffsetLabel(row) {
                return row.prep_day_offset_label || formatPrepDayOffsetLabel(getRowPrepDayOffset(row));
            }

            function getRowPrepEndOffsetLabel(row) {
                return row.prep_end_day_offset_label || formatPrepDayOffsetLabel(getRowPrepEndDayOffset(row));
            }

            function getPrepBarDisplayOffset(row, opts) {
                opts = opts || {};
                if (opts.isContinuation) {
                    return null;
                }
                var startOff = getRowPrepDayOffset(row);
                if (opts.continuesTomorrow && startOff < 0) {
                    return {
                        offset: 1,
                        label: formatPrepDayOffsetLabel(1)
                    };
                }
                return {
                    offset: startOff,
                    label: getRowPrepOffsetLabel(row)
                };
            }

            function buildPrepOffsetBadgeHtml(row, extraClass, opts) {
                var display = getPrepBarDisplayOffset(row, opts);
                if (!display) {
                    return '';
                }
                var cls = getPrepOffsetCssClass(display.offset);
                return '<span class="pill-offset-badge ' + cls + (extraClass ? ' ' + extraClass : '') + '">' +
                    escapeHtml(display.label) + '</span>';
            }

            function buildPrepPillMetaHtml(row, avatarText, avatarStyle, opts) {
                var av = avatarText || ('C' + String(rowCycleKey(row) || '?').toUpperCase());
                var styleAttr = avatarStyle ? ' style="' + avatarStyle + '"' : '';
                return '<span class="pill-meta"><span class="pill-avatar"' + styleAttr + '>' + escapeHtml(av) +
                    '</span>' + buildPrepOffsetBadgeHtml(row, '', opts) + '</span>';
            }

            function buildWeeklyOffsetBadgeHtml(row) {
                var lbl = getRowPrepOffsetLabel(row);
                var cls = getPrepOffsetCssClass(getRowPrepDayOffset(row));
                return '<span class="weekly-offset-badge ' + cls + '">' + escapeHtml(lbl) + '</span>';
            }

            function comparePrepRowsSequential(a, b, viewDateStr) {
                return comparePrepRowsByAbsolute(a, b);
            }

            function sortPrepRowsByOrder(rows, viewDateStr) {
                return (rows || []).slice().sort(function(a, b) {
                    return comparePrepRowsByAbsolute(a, b);
                });
            }

            function prepRowIdentityKey(row) {
                return [
                    String(row.customer_id || ''),
                    rowCycleKey(row),
                    String(row.delivery_date || '').trim(),
                    String(row.cycle_time || '').trim()
                ].join('|');
            }

            function getRowKanbanProgressPct(row) {
                if (!row) {
                    return 0;
                }
                var target = parseInt(row.total_target || 0, 10);
                var done = parseInt(row.total_done || 0, 10);
                if (target > 0) {
                    return Math.min(100, Math.max(0, (done / target) * 100));
                }
                return Math.min(100, Math.max(0, parseFloat(row.progress_pct || 0)));
            }

            function getRowRawProgressPct(row) {
                return Math.round(getRowKanbanProgressPct(row) * 10) / 10;
            }

            function isRowPrepKanbanComplete(row) {
                var target = parseInt(row.total_target || 0, 10);
                var done = parseInt(row.total_done || 0, 10);
                if (target > 0) {
                    return done >= target;
                }
                return getRowKanbanProgressPct(row) >= 100;
            }

            /** Cycle punya Master Cycle lengkap — bar timeline boleh dirender. */
            function rowHasConfiguredMasterCycle(row) {
                if (!row) {
                    return false;
                }
                if (row.master_cycle_configured === false) {
                    return false;
                }
                if (row.master_cycle_configured === true) {
                    return true;
                }
                if (row.mapping_source === 'loading_list_only') {
                    return false;
                }
                var prepStart = normalizeClock(row.cycle_time || row.prep_time || '');
                var prepEnd = normalizeClock(row.prep_end_time || '');
                if (!prepStart.length || !prepEnd.length) {
                    return false;
                }
                if (row.master_cycle_id || row.mapping_source === 'master_cycle_name') {
                    return true;
                }
                return prepStart !== prepEnd;
            }

            function filterRowsWithConfiguredMasterCycle(rows) {
                return (rows || []).filter(rowHasConfiguredMasterCycle);
            }

            function buildChartCustomerOrder(rows) {
                var custSet = {};
                (rows || []).forEach(function(row) {
                    var cn = row.customer_name || '-';
                    custSet[cn] = true;
                });
                return Object.keys(custSet).sort(function(a, b) {
                    return a.localeCompare(b);
                });
            }

            function buildUnconfiguredTimelineHintHtml(rows) {
                var cycles = [];
                (rows || []).forEach(function(r) {
                    if (rowHasConfiguredMasterCycle(r)) {
                        return;
                    }
                    var lbl = 'C' + String(rowCycleKey(r) || '?');
                    if (cycles.indexOf(lbl) < 0) {
                        cycles.push(lbl);
                    }
                });
                if (!cycles.length) {
                    return '';
                }
                var tipCycles = cycles.join(', ');
                var text = 'Belum diatur';
                if (cycles.length === 1) {
                    text += ' · ' + cycles[0];
                } else {
                    text += ' · ' + cycles.length + ' cycle';
                }
                return '<span class="customer-unconfigured-note" title="' + escapeAttr(
                        'Atur PREP di Master Cycle: ' + tipCycles) + '">' +
                    escapeHtml(text) + '</span>';
            }

            function filterRowsInWeeklySlots(rows, slots) {
                if (!slots || !slots.length) {
                    return rows || [];
                }
                var slotSet = {};
                slots.forEach(function(s) {
                    slotSet[formatLocalDateKey(s)] = true;
                });
                return (rows || []).filter(function(r) {
                    return slotSet[formatLocalDateKey(r.delivery_date)];
                });
            }

            function formatGanttBarPctLabel(pct) {
                var n = Math.min(100, Math.max(0, parseFloat(pct || 0)));
                n = Math.round(n * 10) / 10;
                if (Math.abs(n - Math.round(n)) < 0.05) {
                    n = Math.round(n);
                }
                return n + '%';
            }

            function buildGanttBarPctHtml(pct) {
                return '<span class="gantt-bar-pct">' + escapeHtml(formatGanttBarPctLabel(pct)) + '</span>';
            }

            function estimateGanttBarWidthPx(prepLayout) {
                var hourW = parseInt($('#deliveryTimelineCard').css('--gantt-hour-width'), 10) || 120;
                var windowH = ganttHourWindow || 24;
                var trackW = windowH * hourW;
                if (!prepLayout) {
                    return 0;
                }
                if (prepLayout.continuesTomorrow) {
                    return Math.max(0, trackW * ((100 - (prepLayout.leftPct || 0)) / 100));
                }
                return Math.max(0, trackW * ((prepLayout.widthPct || 0) / 100));
            }

            function shouldShowGanttBarPct(barWidthPx, progressWidthPct) {
                var progressPct = Math.min(100, Math.max(0, parseFloat(progressWidthPct || 0)));
                if (progressPct <= 0) {
                    return barWidthPx >= 40;
                }
                var progressPx = barWidthPx * (progressPct / 100);
                return progressPx >= 24 || barWidthPx >= 50;
            }

            function buildGanttSegHtml(barClass, widthPct, pct, showPct) {
                var cls = 'gantt-seg ' + barClass + (showPct ? ' has-pct-label' : '');
                var inner = showPct ? buildGanttBarPctHtml(pct) : '';
                return '<span class="' + cls + '" style="width:' + widthPct + '%">' + inner + '</span>';
            }

            function buildGanttProgressStackHtml(progressWidth, barClass, pct, prepLayout) {
                var barW = estimateGanttBarWidthPx(prepLayout);
                var showPct = progressWidth > 0 && shouldShowGanttBarPct(barW, progressWidth);
                var html = '<div class="gantt-bar-stack">';
                if (progressWidth >= 100) {
                    html += buildGanttSegHtml(barClass, 100, pct, showPct);
                } else {
                    html += buildGanttSegHtml(barClass, progressWidth, pct, showPct);
                    html += '<span class="gantt-seg gantt-trail" style="width:' + (100 - progressWidth) +
                        '%"></span>';
                }
                html += '</div>';
                return html;
            }

            function buildTruckBarStackHtml(truckSegClass, pct) {
                var showPct = shouldShowGanttBarPct(42, 100);
                return '<div class="gantt-bar-stack">' + buildGanttSegHtml(truckSegClass, 100, pct, showPct) +
                    '</div>';
            }

            function getGanttBarDisplayPct(row, prepStatus) {
                if (prepStatus && prepStatus.isBlocked) {
                    return 0;
                }
                return Math.round(getRowRawProgressPct(row) * 10) / 10;
            }

            /** Bar PREP normal (bukan instant marker) — aturan berjenjang hanya berlaku di sini. */
            function isStandardPrepBar(prepLayout) {
                return !!prepLayout && prepLayout.durH >= (1 / 60);
            }

            function rowTouchesViewDate(row, viewDateStr) {
                var view = formatLocalDateKey(viewDateStr);
                if (!view.length || !row) {
                    return false;
                }
                if (String(row.delivery_date || '').trim() === view) {
                    return true;
                }
                if (!rowHasConfiguredMasterCycle(row)) {
                    return false;
                }
                enrichRowAbsoluteDatetimes(row);
                if (row.absolute_start_ms != null && row.absolute_end_ms != null) {
                    var range = viewDateToMsRange(view);
                    return row.absolute_start_ms < range.endMs && row.absolute_end_ms > range.startMs;
                }
                var prepStart = getPrepStartDate(row);
                var prepEnd = getPrepEndDate(row);
                return prepStart === view || prepEnd === view;
            }

            function getCustomerPrepRowsForSequentialGate(customerName, viewDate) {
                var viewDateStr = String(viewDate || '').trim();
                var seen = {};
                var rows = [];

                function pushRow(r) {
                    if (!r || !rowHasConfiguredMasterCycle(r)) {
                        return;
                    }
                    var k = prepRowIdentityKey(r);
                    if (seen[k]) {
                        return;
                    }
                    seen[k] = true;
                    rows.push(r);
                }
                getAllChartRowsForLookup().forEach(function(r) {
                    if ((r.customer_name || '-') !== customerName) {
                        return;
                    }
                    if (rowTouchesViewDate(r, viewDateStr)) {
                        pushRow(r);
                    }
                });
                return sortPrepRowsByOrder(rows, viewDateStr);
            }

            function collectCustomerPrepBarItems(cn, viewDate) {
                var viewDateStr = String(viewDate || '').trim();
                var items = [];
                getAllChartRowsForLookup().forEach(function(row) {
                    if ((row.customer_name || '-') !== cn) {
                        return;
                    }
                    var prepLayout = computePrepBarSegmentOnViewDate(row, viewDateStr, ganttHourWindow);
                    if (!prepLayout) {
                        return;
                    }
                    items.push({
                        row: row,
                        kind: prepLayout.kind,
                        prepLayout: prepLayout
                    });
                });
                items.sort(function(a, b) {
                    return comparePrepRowsByAbsolute(a.row, b.row);
                });
                return items;
            }

            /** Bar ETA truck pada viewDate — tanggal tampil dari getTruckDisplayDate, bukan hanya delivery_date. */
            function collectCustomerTruckRows(cn, viewDate) {
                var viewDateStr = formatLocalDateKey(viewDate);
                if (!viewDateStr.length) {
                    return [];
                }
                var seen = {};
                var rows = [];
                getAllChartRowsForLookup().forEach(function(row) {
                    if ((row.customer_name || '-') !== cn) {
                        return;
                    }
                    if (!rowHasConfiguredMasterCycle(row)) {
                        return;
                    }
                    if (!normalizeClock(row.truck_time).length) {
                        return;
                    }
                    if (getTruckDisplayDate(row) !== viewDateStr) {
                        return;
                    }
                    var k = prepRowIdentityKey(row);
                    if (seen[k]) {
                        return;
                    }
                    seen[k] = true;
                    rows.push(row);
                });
                return sortPrepRowsByOrder(rows, viewDateStr);
            }

            function hasPriorIncompletePrep(row, orderedRows, viewDateStr, currentFrac) {
                var effectiveFrac = resolveCurrentTimelineFrac(currentFrac);
                var rowKey = prepRowIdentityKey(row);
                for (var i = 0; i < orderedRows.length; i++) {
                    var prior = orderedRows[i];
                    if (prepRowIdentityKey(prior) === rowKey) {
                        continue;
                    }
                    if (comparePrepRowsByAbsolute(prior, row) >= 0) {
                        continue;
                    }
                    if (!isRowPrepKanbanComplete(prior)) {
                        if (isRowPastScheduledPrepEnd(prior, effectiveFrac)) {
                            continue;
                        }
                        return true;
                    }
                }
                return false;
            }

            function resolvePrepBarStatus(row, orderedRows, viewDateStr, currentFrac, overdueLatch, prepLayout) {
                var rawPct = getRowRawProgressPct(row);
                var progressWidth = rawPct;
                if (isStandardPrepBar(prepLayout) &&
                    rawPct <= 0 &&
                    hasPriorIncompletePrep(row, orderedRows, viewDateStr, currentFrac)) {
                    return {
                        progressWidth: 0,
                        barClass: 'empty',
                        isBlocked: true,
                        isOverdue: false
                    };
                }
                enrichRowAbsoluteDatetimes(row);
                var beforeScheduledStart = row.absolute_start_ms != null && Date.now() < row.absolute_start_ms;
                var latchKey = rowOverdueLatchKey(row);
                var effectiveFrac = resolveCurrentTimelineFrac(currentFrac);
                var pastFinish = isRowPastFinishPrep(row, effectiveFrac);
                var pullingPastFinish = isRowPullingPastFinishPrep(row);
                var isOverdueNow = !beforeScheduledStart && ((progressWidth < 100 && pastFinish) ||
                    pullingPastFinish);
                if (isOverdueNow) {
                    overdueLatch[latchKey] = true;
                } else if (beforeScheduledStart) {
                    delete overdueLatch[latchKey];
                }
                var isOverdue = !beforeScheduledStart && !!overdueLatch[latchKey];
                var barClass = progressWidth >= 100 ?
                    (isOverdue ? 'overdue' : 'ontime') :
                    (isOverdue ? 'overdue' : (progressWidth > 0 ? 'delay' : 'empty'));
                return {
                    progressWidth: progressWidth,
                    barClass: barClass,
                    isBlocked: false,
                    isOverdue: isOverdue
                };
            }

            function prepBarStatusToWeeklyColor(prepStatus, pct) {
                if (!prepStatus || prepStatus.isBlocked) {
                    return '#e8ecf1';
                }
                if (prepStatus.isOverdue || prepStatus.barClass === 'overdue') {
                    return '#dc3545';
                }
                if (pct >= 100) {
                    return 'var(--dm-complete)';
                }
                if (pct > 0) {
                    return 'var(--dm-yellow)';
                }
                return '#e8ecf1';
            }

            function buildGanttTooltip(row, opts) {
                opts = opts || {};
                var pct = (opts.displayPct !== undefined) ?
                    opts.displayPct :
                    getRowRawProgressPct(row);
                var cycleLbl = rowCycleKey(row) || '?';
                var parts = ['C' + cycleLbl, pct + '%'];
                if (opts.prepRange) {
                    parts.push('Prep ' + opts.prepRange);
                }
                if (opts.prepDayOffsetLabel && opts.prepDayOffsetLabel !== 'H+0') {
                    parts.push(opts.prepDayOffsetLabel);
                }
                if (opts.blockedByPrior) {
                    parts.push('Menunggu cycle sebelumnya');
                }
                buildPrepLateTooltipParts(opts.lateInfo).forEach(function(part) {
                    parts.push(part);
                });
                if (opts.truckCrossMidnight && opts.crossDayFromDate) {
                    parts.push(formatTruckCrossDayDateRangeLabel(opts.crossDayFromDate, opts.truckDisplayDate));
                } else if (opts.crossDayFromDate) {
                    parts.push(formatCrossDaySourceLabel(opts.crossDayFromDate));
                } else if (opts.continuesTomorrow || opts.weeklyCrossDaySpan || opts.prepFromYesterday) {
                    parts.push('Lintas hari');
                }
                if (opts.truckClock) {
                    parts.push('Truck ' + formatClockDot(opts.truckClock) +
                        (opts.truckNextDay && !opts.truckCrossMidnight ? ' (+1)' : ''));
                }
                return parts.join(' · ');
            }

            function buildPrepBarHtmlForItem(item, viewDate, gateRows, currentFrac, overdueLatch, sortIndex) {
                if (!item || !item.row) {
                    return '';
                }
                var row = item.row;
                var kind = item.kind || 'evening';
                var zIndex = 2 + (parseInt(sortIndex, 10) || 0);
                var prepLayout = item.prepLayout || computePrepBarSegmentOnViewDate(row, viewDate,
                    ganttHourWindow);
                if (!prepLayout) {
                    return '';
                }
                var isContinuationBar = prepLayout.isContinuation || kind === 'continuation';
                var displayOffset = getPrepBarDisplayOffset(row, {
                    isContinuation: isContinuationBar,
                    continuesTomorrow: prepLayout.continuesTomorrow
                });
                var offsetClass = displayOffset ?
                    getPrepOffsetCssClass(displayOffset.offset) :
                    getPrepOffsetCssClass(getRowPrepDayOffset(row));
                var prepStatus = resolvePrepBarStatus(row, gateRows, viewDate, currentFrac, overdueLatch,
                    prepLayout);
                var lateInfo = buildPrepLateInfo(row, prepStatus);
                var progressWidth = prepStatus.progressWidth;
                var barClass = prepStatus.barClass;
                var prepStartLbl = formatClockDot(getRowPrepStartClock(row));
                var prepEndLbl = formatClockDot(getRowPrepEndClock(row) || '');
                var prepRangeLbl = prepEndLbl.length ? prepStartLbl + '–' + prepEndLbl : prepStartLbl;
                var barOffsetLabel = displayOffset ? displayOffset.label : getRowPrepOffsetLabel(row);
                var tooltipOpts = {
                    prepRange: prepRangeLbl,
                    prepDayOffsetLabel: barOffsetLabel,
                    prepEndDayOffsetLabel: getRowPrepEndOffsetLabel(row),
                    prepEndClock: getRowPrepEndClock(row),
                    displayPct: prepStatus.isBlocked ? 0 : Math.round(getRowRawProgressPct(row) * 10) / 10,
                    blockedByPrior: prepStatus.isBlocked,
                    lateInfo: lateInfo
                };
                var crossDayFrom = getPrepCrossDaySourceDateKey(row);
                var crossDayLabel = formatCrossDaySourceLabel(crossDayFrom);
                var prepExtraClass = isContinuationBar ? '' : (' ' + offsetClass);
                if (isContinuationBar) {
                    prepExtraClass += ' is-prep-from-yesterday is-cross-day-continuation';
                    tooltipOpts.crossDayFromDate = crossDayFrom;
                    tooltipOpts.crossDayViewDate = viewDate;
                    tooltipOpts.prepFromYesterday = true;
                }
                var isInstantPrep = prepLayout.durH < (1 / 60);
                if (prepLayout.isOvernightPrep && !prepLayout.isContinuation && kind !== 'continuation') {
                    prepExtraClass += ' is-overnight';
                }
                if (prepLayout.continuesTomorrow) {
                    prepExtraClass += ' is-prep-clamped is-continues-tomorrow';
                    tooltipOpts.continuesTomorrow = true;
                    tooltipOpts.crossDayFromDate = crossDayFrom;
                } else if (prepLayout.hasOverflow) {
                    prepExtraClass += ' is-prep-clamped';
                    tooltipOpts.prepOverflowHours = prepLayout.overflowH;
                }
                if (isInstantPrep) {
                    prepExtraClass += ' is-instant';
                }
                if (lateInfo.isLate) {
                    prepExtraClass += ' is-prep-late';
                }
                var inlineStyle = buildPrepBarInlineStyle(prepLayout) + 'z-index:' + zIndex + ';';
                if (isContinuationBar) {
                    inlineStyle += '--cross-day-label:&quot;' + escapeAttr(crossDayLabel) + '&quot;;';
                }
                var barTitle = buildGanttTooltip(row, tooltipOpts).replace(/"/g, '&quot;');
                var html = '<div class="gantt-bar-wrap' + prepExtraClass + '" style="' + inlineStyle +
                    '" title="' + barTitle + '"';
                html += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                html += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                html += ' data-prep-offset="' + escapeAttr(String(getRowPrepDayOffset(row))) + '"';
                html += ' data-delivery-date-from="' + escapeAttr(formatLocalDateKey(row.delivery_date)) + '"';
                html += ' data-delivery-date-to="' + escapeAttr(formatLocalDateKey(row.delivery_date)) + '"';
                if (lateInfo.isLate) {
                    html += ' data-prep-late-detail="' + escapeAttr(JSON.stringify(lateInfo)) + '"';
                }
                html += '>';
                html += buildGanttProgressStackHtml(progressWidth, barClass,
                    getGanttBarDisplayPct(row, prepStatus), prepLayout);
                html += buildPrepLateBadgeHtml(lateInfo);
                html += buildPrepPillMetaHtml(row, null, isContinuationBar ? 'opacity:.9' : '', {
                    isContinuation: isContinuationBar,
                    continuesTomorrow: prepLayout.continuesTomorrow
                });
                html += '</div>';
                return html;
            }

            function renderGantt() {
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }
                destroyWeeklyChart();
                setGanttSlotCount(24);
                var nowPct = scaleTimelineMarkerPct(getNowLeftPct());
                var truckPct = scaleTimelineMarkerPct(getTruckArrivalLeftPct());
                var nowDate = new Date();
                nowDate.setMinutes(nowDate.getMinutes() + (etaWindowSettings.eta_offset_hours * 60));
                var nowLbl = formatClockDot(nowDate.toTimeString());
                var truckDate = new Date();
                truckDate.setMinutes(truckDate.getMinutes() + (etaWindowSettings.finish_offset_hours * 60));
                var truckLbl = formatClockDot(truckDate.toTimeString());
                var fillLeft = Math.min(nowPct, truckPct);
                var fillWidth = Math.abs(truckPct - nowPct);
                var labels = slotLabelsForWindow(ganttHourWindow);
                var byCustomer = {};
                chartMergedByCustTime.forEach(function(row) {
                    var cn = row.customer_name || '-';
                    if (!byCustomer[cn]) {
                        byCustomer[cn] = [];
                    }
                    byCustomer[cn].push(row);
                });
                var viewDate = getDailyFilterViewDate();
                var prepContinuations = getPrepContinuationsForViewDate(viewDate);
                var contByCustomer = {};
                prepContinuations.forEach(function(item) {
                    var cn = item.row.customer_name || '-';
                    if (!contByCustomer[cn]) {
                        contByCustomer[cn] = [];
                    }
                    contByCustomer[cn].push(item);
                    if (!byCustomer[cn]) {
                        byCustomer[cn] = [];
                    }
                });
                var custNames = chartCustomerOrder.slice();
                Object.keys(contByCustomer).forEach(function(cn) {
                    if (custNames.indexOf(cn) < 0) {
                        custNames.push(cn);
                    }
                });
                getAllChartRowsForLookup().forEach(function(row) {
                    if (!computePrepBarSegmentOnViewDate(row, viewDate, ganttHourWindow)) {
                        return;
                    }
                    var cn = row.customer_name || '-';
                    if (custNames.indexOf(cn) < 0) {
                        custNames.push(cn);
                    }
                });
                getAllChartRowsForLookup().forEach(function(row) {
                    if (!rowHasConfiguredMasterCycle(row)) {
                        return;
                    }
                    if (getTruckDisplayDate(row) !== formatLocalDateKey(viewDate)) {
                        return;
                    }
                    var cn = row.customer_name || '-';
                    if (custNames.indexOf(cn) < 0) {
                        custNames.push(cn);
                    }
                });
                custNames.sort(function(a, b) {
                    return a.localeCompare(b);
                });
                if (!custNames.length) {
                    setGanttSlotCount(24);
                    $('#ganttContainer').empty();
                    updateTimelineTotal();
                    return;
                }
                var overdueLatch = getOverdueLatchMap();
                var nowWall = new Date();
                var currentClock = String(nowWall.getHours()).padStart(2, '0') + ':' + String(nowWall
                    .getMinutes()).padStart(2, '0');
                var currentFrac = timeToFrac(currentClock);
                var gridHtml = '<div class="gantt-grid-scroll"><div class="gantt-grid">';
                /* Header row */
                var viewDateLabel = viewDate.length ? formatDeliveryDateLabel(viewDate) : '';
                gridHtml += '<div class="gantt-grid-row gantt-grid-header">';
                gridHtml +=
                    '<div class="gantt-grid-cell gantt-grid-sticky-col gantt-grid-sticky-customer" style="font-weight:700;padding:0 10px;display:flex;flex-direction:column;align-items:flex-start;justify-content:center;">' +
                    '<span>Customer</span>' +
                    (viewDateLabel ?
                        '<span style="font-size:9px;font-weight:600;color:var(--dm-muted);margin-top:2px;">Sumbu hari: ' +
                        escapeHtml(viewDateLabel) + '</span>' : '') +
                    '</div>';
                gridHtml +=
                    '<div class="gantt-grid-cell gantt-grid-sticky-col gantt-grid-sticky-type" style="font-weight:700;display:flex;align-items:center;justify-content:center;">Type</div>';
                labels.forEach(function(lbl) {
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-time">' + escapeHtml(lbl) +
                        '</div>';
                });
                gridHtml += '</div>';
                /* Data rows */
                var filterRange = getDeliveryFilterDateRange();
                custNames.forEach(function(cn, custIdx) {
                    var deliveryBucketsForDate = chartRows.filter(function(row) {
                        if ((row.customer_name || '-') !== cn) {
                            return false;
                        }
                        var dd = formatLocalDateKey(row.delivery_date);
                        if (dd === viewDate) {
                            return true;
                        }
                        if (filterRange.from && filterRange.to && dd >= filterRange.from && dd <=
                            filterRange.to) {
                            return rowTouchesViewDate(row, viewDate);
                        }
                        return false;
                    });
                    var prepBarItems = collectCustomerPrepBarItems(cn, viewDate);
                    var truckRows = collectCustomerTruckRows(cn, viewDate);
                    var allBucketsForDate = deliveryBucketsForDate.slice();
                    var gateRows = getCustomerPrepRowsForSequentialGate(cn, viewDate);
                    var unconfiguredHint = buildUnconfiguredTimelineHintHtml(allBucketsForDate);
                    var lateSummary = buildCustomerPrepLateSummaryHtml(gateRows.length ? gateRows :
                        prepBarItems.map(
                            function(item) {
                                return item.row;
                            }));
                    gridHtml += '<div class="gantt-grid-row">';
                    gridHtml +=
                        '<div class="gantt-grid-cell gantt-grid-customer gantt-grid-left-cell gantt-grid-sticky-col gantt-grid-sticky-customer" data-customer-name="' +
                        escapeAttr(cn) + '">' +
                        escapeHtml(cn) + lateSummary + (unconfiguredHint || '') + '</div>';
                    gridHtml +=
                        '<div class="gantt-grid-cell gantt-grid-type gantt-grid-type-split gantt-grid-left-cell gantt-grid-sticky-col gantt-grid-sticky-type">';
                    gridHtml +=
                        '<div class="gantt-type-lane is-prep">PREP</div><div class="gantt-type-lane is-truck">TRUCK</div>';
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-track-cell">';
                    gridHtml += '<div class="gantt-grid-track-split">';
                    /* PREP lane */
                    gridHtml += '<div class="gantt-grid-track-lane"><div class="gantt-track">';
                    gridHtml += '<div class="gantt-window-fill" style="left:' + fillLeft + '%;width:' +
                        fillWidth + '%;"></div>';
                    prepBarItems.forEach(function(item, sortIdx) {
                        gridHtml += buildPrepBarHtmlForItem(item, viewDate, gateRows, currentFrac,
                            overdueLatch, sortIdx);
                    });
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-truck-marker" style="left:' + truckPct + '%">';
                    if (custIdx === 0) {
                        gridHtml += '<span class="gantt-truck-label">ETA TRUCK ' + escapeHtml(truckLbl) +
                            '</span>';
                    }
                    gridHtml += '<div class="gantt-truck-line"></div></div>';
                    gridHtml += '<div class="gantt-now-marker" style="left:' + nowPct + '%">';
                    if (custIdx === 0) {
                        gridHtml += '<span class="gantt-now-label">Finish Preparation ' + escapeHtml(
                            nowLbl) + '</span>';
                    }
                    gridHtml += '<div class="gantt-now-line"></div></div>';
                    gridHtml += '</div>';
                    /* TRUCK lane */
                    gridHtml += '<div class="gantt-grid-track-lane"><div class="gantt-track">';
                    truckRows.forEach(function(row, truckIdx) {
                        var truckAt = (row.truck_time != null && String(row.truck_time).length) ?
                            normalizeClock(row.truck_time) : null;
                        if (!truckAt) {
                            return;
                        }
                        if (getTruckDisplayDate(row) !== viewDate) {
                            return;
                        }
                        var truckStart = resolveTruckTimelineFrac(row);
                        if (truckStart === null) {
                            return;
                        }
                        var truckNextDay = isTruckOnNextDay(row);
                        var truckCrossMidnight = isTruckCrossMidnight(row);
                        var truckLeftPct = (truckStart / ganttHourWindow) * 100;
                        var truckSegClass = 'truck';
                        if (row.truck_status === 'picked' && row.truck_picked_at) {
                            var actualTime = new Date(row.truck_picked_at);
                            var scheduledTime = new Date(getTruckDisplayDate(row) + 'T' +
                                normalizeClock(row.truck_time) + ':00');
                            var earlyThreshold = new Date(scheduledTime.getTime() - 15 * 60 * 1000);

                            if (actualTime > scheduledTime) {
                                truckSegClass = 'truck-late';
                            } else if (actualTime < earlyThreshold) {
                                truckSegClass = 'truck-early';
                            } else {
                                truckSegClass = 'truck-ontime';
                            }
                        } else {
                            var truckComplete = isRowPrepKanbanComplete(row);
                            truckSegClass = truckComplete ? 'truck-complete' : 'truck';
                        }
                        var truckTitle = buildGanttTooltip(row, buildTruckTooltipOpts(row)).replace(
                            /"/g,
                            '&quot;');
                        var truckLabel = 'C' + escapeHtml(String(rowCycleKey(row) || '?'));
                        var truckExtraClass = (truckNextDay || truckCrossMidnight ?
                                ' is-cross-day' : '') + ' ' +
                            getPrepOffsetCssClass(getRowPrepDayOffset(row));
                        gridHtml += '<div class="gantt-bar-wrap truck-pill' + truckExtraClass +
                            '" style="left:' + truckLeftPct + '%;z-index:' + (2 + truckIdx) +
                            ';" title="' +
                            truckTitle + '"';
                        gridHtml += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        gridHtml += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        gridHtml += ' data-delivery-date-from="' + escapeAttr(formatLocalDateKey(
                            row.delivery_date)) + '"';
                        gridHtml += ' data-delivery-date-to="' + escapeAttr(formatLocalDateKey(
                            row.delivery_date)) + '">';
                        gridHtml += buildTruckBarStackHtml(truckSegClass, getRowRawProgressPct(
                        row));
                        gridHtml += buildPrepPillMetaHtml(row, truckLabel);
                        gridHtml += '</div>';
                    });
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-truck-marker" style="left:' + truckPct +
                        '%"><div class="gantt-truck-line"></div></div>';
                    gridHtml += '<div class="gantt-now-marker" style="left:' + nowPct +
                        '%"><div class="gantt-now-line"></div></div>';
                    gridHtml += '</div></div></div>';
                    gridHtml += '</div>';
                });
                gridHtml += '</div></div>';
                $('#ganttContainer').html(gridHtml);
                setOverdueLatchMap(overdueLatch);
                updateTimelineTotal();
                ganttNowTimer = setInterval(updateGanttNowMarkers, 1000);
                updateGanttNowMarkers();
            }

            function openLoadingListFromBar($bar) {
                var customer = $bar.attr('data-customer-name') || '';
                var cycle = $bar.attr('data-cycle') || '';
                var deliveryDateFrom = $bar.attr('data-delivery-date-from') || '';
                var deliveryDateTo = $bar.attr('data-delivery-date-to') || '';
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
            }

            function parsePrepLateDetailFromBar($bar) {
                var raw = $bar.attr('data-prep-late-detail') || '';
                if (!raw.length) {
                    return null;
                }
                try {
                    return JSON.parse(raw);
                } catch (e) {
                    return null;
                }
            }

            function highlightPrepLateRow($bar) {
                var $targets = $bar.hasClass('weekly-prep-bar') ?
                    $bar.closest('tr').add($bar.closest('tr').next('tr')) :
                    $bar.closest('.gantt-grid-row');
                $targets.addClass('is-row-highlight');
                setTimeout(function() {
                    $targets.removeClass('is-row-highlight');
                }, 2200);
            }

            function buildPrepLateDetailHtml(info) {
                var reasons = (info.reasons || []).map(function(reason) {
                    return '<span class="prep-late-detail__reason">' + escapeHtml(reason) + '</span>';
                }).join('');
                var html = '<div class="prep-late-detail">';
                html += '<div class="prep-late-detail__meta">';
                html += '<div><span>Customer</span><strong>' + escapeHtml(info.customerName || '-') +
                    '</strong></div>';
                html += '<div><span>Cycle</span><strong>' + escapeHtml(info.cycleName || '-') + '</strong></div>';
                html += '<div><span>Finish Prep</span><strong>' + escapeHtml(info.prepDeadlineLabel || '-') +
                    '</strong></div>';
                html += '<div><span>Progress</span><strong>' + escapeHtml(info.progressPct || 0) + '% (' +
                    escapeHtml(info.done || 0) + '/' + escapeHtml(info.target || 0) + ')</strong></div>';
                html += '</div>';
                html += '<div class="prep-late-detail__reasons">' + (reasons ||
                        '<span class="prep-late-detail__reason">Prep telat</span>') +
                    '</div>';

                if (info.items && info.items.length) {
                    html += '<table class="prep-late-detail__table"><thead><tr>' +
                        '<th>PDS</th><th>Loading List</th><th>Pulling</th><th>Delay</th>' +
                        '</tr></thead><tbody>';
                    info.items.slice(0, 30).forEach(function(item) {
                        html += '<tr><td>' + escapeHtml(item.pdsNumber || '-') + '</td><td>' +
                            escapeHtml(item.loadingList || '-') + '</td><td>' +
                            escapeHtml(item.pullingLabel || '-') + '</td><td>' +
                            escapeHtml(item.delayLabel || '-') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    var pds = (info.pdsNumbers || []).slice(0, 12).join(', ') || '-';
                    var lls = (info.loadingLists || []).slice(0, 12).join(', ') || '-';
                    html += '<div class="prep-late-detail__empty"><strong>PDS terdampak:</strong> ' +
                        escapeHtml(pds) + '<br><strong>Loading list:</strong> ' + escapeHtml(lls) + '</div>';
                }
                html += '</div>';
                return html;
            }

            function openPrepLateDetailFromBar($bar) {
                var info = parsePrepLateDetailFromBar($bar);
                if (!info) {
                    openLoadingListFromBar($bar);
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Detail Preparation Telat',
                    html: buildPrepLateDetailHtml(info),
                    width: 760,
                    confirmButtonText: 'Buka Loading List',
                    confirmButtonColor: '#dc3545',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup',
                    customClass: {
                        popup: 'master-cycle-swal-popup'
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        openLoadingListFromBar($bar);
                    } else {
                        highlightPrepLateRow($bar);
                    }
                });
            }

            $('#ganttContainer').on('click', '.gantt-bar-wrap', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $bar = $(this);
                if ($bar.hasClass('is-prep-late')) {
                    openPrepLateDetailFromBar($bar);
                    return;
                }
                openLoadingListFromBar($bar);
            });

            $('#weeklyGanttContainer').on('click', '.weekly-prep-bar', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $bar = $(this);
                if ($bar.hasClass('is-prep-late')) {
                    openPrepLateDetailFromBar($bar);
                    return;
                }
                openLoadingListFromBar($bar);
            });

            function resolveWeeklyDayColWidth(nSlots) {
                var custColWidth = 200;
                var typeColWidth = 120;
                var minDayWidth = 120;
                var slots = Math.max(parseInt(nSlots, 10) || 1, 1);
                var $host = $('#deliveryWeeklyCard .weekly-card-body');
                var containerW = $host.length ? $host.innerWidth() : 0;
                if (!containerW || containerW < 320) {
                    containerW = Math.max($(window).width() - 120, 640);
                }
                var available = containerW - custColWidth - typeColWidth;
                return Math.max(minDayWidth, Math.floor(available / slots));
            }

            function renderWeeklySummary() {
                destroyWeeklyChart();
                if (weeklyGridTimer) {
                    clearInterval(weeklyGridTimer);
                    weeklyGridTimer = null;
                }

                if (!chartRows.length) {
                    $('#weeklyGanttContainer').html(
                        '<div class="text-muted small">Tidak ada data untuk ditampilkan.</div>');
                    updateTimelineTotal();
                    return;
                }

                var dateFrom = $('#filterDateFrom').val() || '';
                var dateTo = $('#filterDateTo').val() || dateFrom;
                if (!dateFrom) {
                    $('#weeklyGanttContainer').html('<div class="text-muted small">Pilih rentang tanggal.</div>');
                    updateTimelineTotal();
                    return;
                }

                var slots = weeklyDaySlots(dateFrom, dateTo);
                var nSlots = slots.length || 1;
                var slotWPct = 100 / nSlots;
                $('#deliveryWeeklyCard').css('--weekly-day-count', String(nSlots));

                var custOrder = buildChartCustomerOrder(chartRows);
                var custMap = {};
                chartRows.forEach(function(row) {
                    var c = row.customer_name || '-';
                    if (!custMap[c]) {
                        custMap[c] = [];
                    }
                    custMap[c].push(row);
                });

                // Kolom hari mengisi sisa lebar; Customer/Type tetap statis
                var dayColWidth = resolveWeeklyDayColWidth(nSlots);
                var custColWidth = 200;
                var typeColWidth = 120;
                $('#deliveryWeeklyCard').css('--weekly-day-col-width', dayColWidth + 'px');

                // ── Build HTML ────────────────────────────────────────────────────
                var html = '<div class="weekly-gantt-scroll">';
                html += '<table class="weekly-gantt-table">';

                html += '<colgroup>';
                html += '<col style="width:' + custColWidth + 'px">';
                html += '<col style="width:' + typeColWidth + 'px">';
                slots.forEach(function() {
                    html += '<col>';
                });
                html += '</colgroup>';

                // Header
                html += '<thead><tr>';
                html +=
                    '<th class="weekly-col-customer-head" style="text-align:left;padding-left:10px;">Customer</th>';
                html += '<th class="weekly-col-type-head">TYPE</th>';
                slots.forEach(function(slotDateStr) {
                    html += '<th class="weekly-col-day-head">' + weeklyDateLabel(slotDateStr) + '</th>';
                });
                html += '</tr></thead><tbody>';

                // Hitung posisi "Now" untuk marker
                var todayStr = todayLocalDateKey();
                var nowDayIdx = -1;
                slots.forEach(function(slotDateStr, i) {
                    if (slotDateStr === todayStr) {
                        nowDayIdx = i;
                    }
                });
                var nowFrac = getCurrentDayFrac();
                var nowLeftPct = nowDayIdx >= 0 ? ((nowDayIdx + nowFrac) / nSlots) * 100 : -1;
                var nowLbl = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                var weeklyOverdueLatch = getOverdueLatchMap();
                var trackWidthPx = Math.max(nSlots * dayColWidth, 1);
                var minPrepBarWidthPct = (48 / trackWidthPx) * 100;

                custOrder.forEach(function(cust, custIdx) {
                    var rows = sortPrepRowsByOrder(custMap[cust] || []);
                    var rowsForBars = rows.filter(rowHasConfiguredMasterCycle);
                    var weeklyLateSummary = buildCustomerPrepLateSummaryHtml(filterRowsInWeeklySlots(
                        rowsForBars,
                        slots));
                    var rowHeight = 136; // px – sama dengan daily

                    // ── Baris PREP ──
                    html += '<tr>';
                    // Kolom Customer dengan rowspan=2
                    html += '<td rowspan="2" class="weekly-col-customer" style="' +
                        'vertical-align:middle;text-align:left;' +
                        'font-size:12px;font-weight:500;padding:8px 10px;' +
                        'word-break:break-word;line-height:1.3;' +
                        '">' + escapeHtml(cust) +
                        weeklyLateSummary +
                        buildUnconfiguredTimelineHintHtml(filterRowsInWeeklySlots(rows, slots)) +
                        '</td>';
                    // Kolom TYPE – PREP
                    html += '<td class="weekly-col-type" style="' +
                        'height:' + (rowHeight / 2) + 'px;' +
                        'text-align:center;vertical-align:middle;' +
                        'font-size:10px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#5c7f2b;' +
                        '">PREP</td>';
                    // Lane PREP mingguan: satu track lintas semua hari (bar boleh melewati garis antarhari)
                    var prepLaneH = rowHeight / 2;
                    html += '<td colspan="' + nSlots + '" class="weekly-prep-lane-cell">';
                    html += '<div class="weekly-prep-track" style="height:' + prepLaneH + 'px;">';
                    slots.forEach(function(slotDateStr, dayIdx) {
                        var isTodayCol = slotDateStr === todayStr;
                        html += '<div class="weekly-day-column' + (isTodayCol ? ' is-today' : '') +
                            '" style="left:' + ((dayIdx / nSlots) * 100) + '%;width:' + slotWPct +
                            '%;"></div>';
                    });
                    collectWeeklyPrepBarsForCustomer(cust, rowsForBars, slots).forEach(function(item) {
                        var row = item.row;
                        var span = item.span;
                        var anchorDate = span.anchorDate;
                        var gateDate = span.isIncomingFromPriorRange ? formatLocalDateKey(slots[
                            0]) :
                            anchorDate;
                        var gateRows = getCustomerPrepRowsForSequentialGate(cust, gateDate);
                        var prepLayoutDaily = computePrepBarLayoutForRow(row, 24);
                        var weeklyCurrentFrac = (gateDate === todayStr) ? nowFrac : null;
                        var prepStatus = resolvePrepBarStatus(row, gateRows, gateDate,
                            weeklyCurrentFrac,
                            weeklyOverdueLatch, prepLayoutDaily);
                        var lateInfo = buildPrepLateInfo(row, prepStatus);
                        var rawPct = Math.round(getRowRawProgressPct(row) * 10) / 10;
                        var pct = prepStatus.isBlocked ? 0 : rawPct;
                        var barColor = prepBarStatusToWeeklyColor(prepStatus, pct);
                        var cycleLbl = rowCycleKey(row) || '-';
                        var prepRangeLbl = span.prepStart + (span.prepEnd ? '–' + span.prepEnd :
                        '');
                        var tipText = escapeAttr(buildGanttTooltip(row, {
                            prepRange: prepRangeLbl,
                            prepDayOffsetLabel: row.prep_day_offset_label ||
                                formatPrepDayOffsetLabel(
                                    getRowPrepDayOffset(row)),
                            prepEndDayOffsetLabel: row.prep_end_day_offset_label ||
                                formatPrepDayOffsetLabel(getRowPrepEndDayOffset(row)),
                            crossDayFromDate: span.crossesDays ? (span.anchorDate ||
                                getPrepCrossDaySourceDateKey(row)) : null,
                            weeklyCrossDaySpan: span.crossesDays && !span.anchorDate,
                            displayPct: pct,
                            blockedByPrior: prepStatus.isBlocked,
                            lateInfo: lateInfo
                        }));
                        var av = 'C' + escapeHtml(String(cycleLbl).toUpperCase());
                        var crossClass = span.crossesDays ? ' is-cross-day' : '';
                        var incomingClass = span.isIncomingFromPriorRange ?
                            ' is-incoming-prior-range' : '';
                        var offsetClass = getPrepOffsetCssClass(getRowPrepDayOffset(row));
                        var barWidthPct = Math.max(span.widthPct, (100 / nSlots / 24),
                            minPrepBarWidthPct);
                        html += '<div class="weekly-prep-bar' + crossClass + incomingClass +
                            (lateInfo.isLate ? ' is-prep-late' : '') + ' ' + offsetClass +
                            '" title="' + tipText + '" style="left:' +
                            span.leftPct + '%;width:' + barWidthPct + '%;' +
                            'background:' + barColor + ';"';
                        html += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        html += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        html += ' data-delivery-date-from="' + escapeAttr(formatLocalDateKey(row
                            .delivery_date)) + '"';
                        html += ' data-delivery-date-to="' + escapeAttr(formatLocalDateKey(row
                            .delivery_date)) + '"';
                        if (lateInfo.isLate) {
                            html += ' data-prep-late-detail="' + escapeAttr(JSON.stringify(
                                lateInfo)) + '"';
                        }
                        html += '>';
                        var weeklyBarW = trackWidthPx * (barWidthPct / 100);
                        var weeklyPctHtml = shouldShowGanttBarPct(weeklyBarW, pct) ?
                            buildGanttBarPctHtml(pct) : '';
                        html += '<span>' +
                            av + buildWeeklyOffsetBadgeHtml(row) + weeklyPctHtml +
                            buildPrepLateBadgeHtml(lateInfo) +
                            '</span></div>';
                    });
                    if (nowDayIdx >= 0) {
                        var nowLeftInTrack = ((nowDayIdx + nowFrac) / nSlots) * 100;
                        html += '<div style="position:absolute;top:0;bottom:0;left:' + nowLeftInTrack +
                            '%;width:2px;background:rgb(67,53,220);transform:translateX(-50%);z-index:4;pointer-events:none;">' +
                            '<span style="position:absolute;top:-14px;left:0;transform:translateX(-50%);font-size:8px;background:rgb(59,53,220);color:#fff;padding:1px 4px;border-radius:2px;white-space:nowrap;">Now</span>' +
                            '</div>';
                    }
                    html += '</div></td>';
                    html += '</tr>';

                    // ── Baris ETA TRUCK ──
                    html += '<tr>';
                    // Kolom TYPE – ETA TRUCK
                    html += '<td class="weekly-col-type" style="' +
                        'height:' + (rowHeight / 2) + 'px;' +
                        'text-align:center;vertical-align:middle;' +
                        'font-size:10px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#b86f00;' +
                        '">ETA TRUCK</td>';
                    // Slot hari – lane ETA TRUCK
                    slots.forEach(function(slotDateStr, dayIdx) {
                        var isToday = slotDateStr === todayStr;
                        var bg = isToday ? 'background:rgba(255,235,235,.35);' : '';
                        html +=
                            '<td class="weekly-col-day" style="position:relative;padding:0;border:1px solid #eef0f3;height:' +
                            (rowHeight / 2) +
                            'px;vertical-align:top;overflow:visible;' + bg + '">';
                        html += '<div style="position:relative;height:' + (rowHeight / 2) + 'px;">';
                        rowsForBars.forEach(function(row) {
                            var truckDisplayDate = getTruckDisplayDate(row);
                            if (String(truckDisplayDate || '').trim() !== slotDateStr) {
                                return;
                            }
                            var truckTime = row.truck_time ? String(row.truck_time)
                                .substring(0, 5) : null;
                            if (!truckTime) {
                                return;
                            }
                            var truckFrac = weeklyFracOfDay(slotDateStr, truckTime);
                            if (truckFrac === null) {
                                return;
                            }
                            var leftPct = truckFrac * 100;
                            var pct = getRowRawProgressPct(row);
                            var barColor = isRowPrepKanbanComplete(row) ? 'var(--dm-blue)' :
                                '#f59e0b';
                            if (row.truck_status === 'picked' && row.truck_picked_at) {
                                var actualTime = new Date(row.truck_picked_at);
                                var scheduledTime = new Date(truckDisplayDate + 'T' +
                                    truckTime + ':00');
                                var earlyThreshold = new Date(scheduledTime.getTime() - 15 *
                                    60 * 1000);
                                if (actualTime > scheduledTime) {
                                    barColor = '#dc3545';
                                } else if (actualTime < earlyThreshold) {
                                    barColor = '#eab308';
                                } else {
                                    barColor = '#22c55e';
                                }
                            }
                            var truckNextDay = isTruckOnNextDay(row);
                            var truckCrossMidnight = isTruckCrossMidnight(row);
                            var cycleLblTruck = rowCycleKey(row) || '-';
                            var tipText = escapeAttr(buildGanttTooltip(row,
                                    buildTruckTooltipOpts(row)) +
                                ' | Done ' + (row.total_done || 0) + '/' + (row
                                    .total_target || 0));
                            var truckBorder = (truckNextDay || truckCrossMidnight) ?
                                'border:2px dashed #b86f00;' : '';
                            var av = 'C' + escapeHtml(String(cycleLblTruck).toUpperCase());
                            html += '<div class="weekly-truck-pill" title="' + tipText +
                                '" style="' +
                                'position:absolute;top:8px;height:22px;' +
                                'left:' + leftPct + '%;width:48px;min-width:48px;' +
                                'background:' + barColor + ';border-radius:4px;' +
                                truckBorder +
                                'box-shadow:0 1px 2px rgba(0,0,0,.08);' +
                                'cursor:pointer;box-sizing:border-box;z-index:2;' +
                                'display:flex;align-items:center;justify-content:center;padding:0 4px;' +
                                '">' +
                                '<span>' +
                                av + (shouldShowGanttBarPct(48, pct) ? buildGanttBarPctHtml(
                                    pct) : '') + '</span>' +
                                '</div>';
                        });
                        if (isToday) {
                            var nFrac2 = getCurrentDayFrac();
                            html += '<div style="position:absolute;top:0;bottom:0;left:' + (nFrac2 *
                                    100) +
                                '%;width:2px;background:rgb(67,53,220);transform:translateX(-50%);z-index:4;pointer-events:none;"></div>';
                        }
                        html += '</div></td>';
                    });
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                $('#weeklyGanttContainer').html(html);
                setOverdueLatchMap(weeklyOverdueLatch);

                // Ticker: update garis Now setiap detik
                weeklyGridTimer = setInterval(function() {
                    var todayS = todayLocalDateKey();
                    var nFrac3 = getCurrentDayFrac();
                    slots.forEach(function(slotDateStr, dayIdx) {
                        if (slotDateStr !== todayS) {
                            return;
                        }
                        $('#weeklyGanttContainer [data-wday="' + dayIdx + '"] .weekly-now-line')
                            .css('left', (nFrac3 * 100) +
                                '%');
                    });
                }, 1000);

                updateTimelineTotal();
            }

            function loadStackedChart() {
                var dateFrom = $('#filterDateFrom').val() || '';
                var dateTo = $('#filterDateTo').val() || '';
                var timelineAnchor = getDailyFilterViewDate() || dateTo || dateFrom;
                var params = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    date: timelineAnchor,
                    customer_id: $('#filterCustomer').val() || ''
                };
                $('#chartEmpty').addClass('d-none');
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }
                $('#ganttContainer').empty();
                destroyWeeklyChart();

                function finishStackedChartLoad(res) {
                    chartRows = enrichChartRowsCollection(res.rows || []);
                    chartWeeklyPoints = enrichChartRowsCollection(res.weekly_points || []);
                    rebuildLoadingListPresenceIndex();
                    var viewMode = detectDashboardViewMode();
                    var hint = (res.meta && res.meta.hint) ? res.meta.hint : '';
                    if (!chartRows.length && !chartRowsPrevDay.length) {
                        var msg = hint || 'Tidak ada data delivery untuk filter ini.';
                        $('#chartEmpty').removeClass('d-none').text(msg);
                        chartMergedByCustTime = [];
                        updateTimelineTotal();
                        return;
                    }
                    if (hint && !filterRowsWithConfiguredMasterCycle(chartRows).length) {
                        $('#chartEmpty').removeClass('d-none').text(hint);
                    } else {
                        $('#chartEmpty').addClass('d-none');
                    }
                    chartMergedByCustTime = mergeRowsForCustomerTime(
                        filterRowsWithConfiguredMasterCycle(chartRows));
                    chartCustomerOrder = buildChartCustomerOrder(
                        chartRows.concat(chartRowsPrevDay).concat(chartRowsNextDay));
                    applyDashboardViewMode(viewMode);
                    if (dashboardViewMode === 'daily') {
                        renderGantt();
                    } else {
                        renderWeeklySummary();
                    }
                    checkAndSendUnfinishedWaNotification();
                }

                $.get(stackedUrl, params, function(res) {
                    chartRowsPrevDay = [];
                    chartRowsNextDay = [];
                    var viewDateForAdj = getDailyFilterViewDate() || params.date_from || '';
                    if (!viewDateForAdj) {
                        finishStackedChartLoad(res);
                        return;
                    }
                    var prevParams = $.extend({}, params, {
                        date_from: addDaysToDateStr(viewDateForAdj, -1),
                        date_to: addDaysToDateStr(viewDateForAdj, -1),
                        date: addDaysToDateStr(viewDateForAdj, -1)
                    });
                    var nextParams = $.extend({}, params, {
                        date_from: addDaysToDateStr(viewDateForAdj, 1),
                        date_to: addDaysToDateStr(viewDateForAdj, 1),
                        date: addDaysToDateStr(viewDateForAdj, 1)
                    });
                    var pending = 2;

                    function maybeFinishAdj() {
                        pending--;
                        if (pending <= 0) {
                            finishStackedChartLoad(res);
                        }
                    }
                    $.get(stackedUrl, prevParams, function(prevRes) {
                        chartRowsPrevDay = enrichChartRowsCollection(prevRes.rows || []);
                        maybeFinishAdj();
                    }).fail(function() {
                        chartRowsPrevDay = [];
                        maybeFinishAdj();
                    });
                    $.get(stackedUrl, nextParams, function(nextRes) {
                        chartRowsNextDay = enrichChartRowsCollection(nextRes.rows || []);
                        maybeFinishAdj();
                    }).fail(function() {
                        chartRowsNextDay = [];
                        maybeFinishAdj();
                    });
                }).fail(function() {
                    $('#chartEmpty').removeClass('d-none').text('Gagal memuat Gantt.');
                    $('#timelineTotalSum').text('Total: 0');
                });
            }

            $('#btnReloadChart').on('click', loadStackedChart);
            $('#filterDateFrom, #filterDateTo, #filterCustomer').on('change', loadStackedChart);

            $('#etaWindowModalForm').on('submit', function(e) {
                e.preventDefault();
                etaWindowSettings.eta_offset_hours = normalizeOffsetHours($('#etaOffsetHours').val(), 0);
                etaWindowSettings.finish_offset_hours = normalizeOffsetHours($('#finishOffsetHours').val(),
                    4);
                saveEtaWindowSettings();
                renderEtaWindowFormState();
                $('#etaWindowModal').modal('hide');
                updateGanttNowMarkers();
                alert('Pengaturan rentang ETA TRUCK dan Finish Preparation berhasil disimpan.');
            });

            $('#etaWindowModal').on('show.bs.modal', function() {
                renderEtaWindowFormState();
            });

            if (!$('#filterDateFrom').val()) {
                var today = todayLocalDateKey();
                $('#filterDateFrom').val(today);
                $('#filterDateTo').val(today);
            }

            renderHourDropdownOptions('#etaOffsetHours');
            renderHourDropdownOptions('#finishOffsetHours');
            loadEtaWindowSettings();
            renderEtaWindowFormState();
            fetchMasters();
            loadStackedChart();

            waNotifyTimer = setInterval(function() {
                checkAndSendUnfinishedWaNotification();
                if (dashboardViewMode === 'daily') {
                    renderGantt();
                }
            }, 60000);

            tickDeliveryHeaderClock();
            setInterval(tickDeliveryHeaderClock, 1000);
        });
    </script>

    {{-- ===== MODAL ETA WINDOW ===== --}}
    <div class="modal fade" id="etaWindowModal" tabindex="-1" role="dialog" aria-labelledby="etaWindowModalLabel"
        aria-hidden="true">
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
                        <div class="form-group mb-3">
                            <label class="bella-form-label">ETA TRUCK (jam dari sekarang)</label>
                            <select class="bella-form-control" id="etaOffsetHours" required></select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="bella-form-label">Finish Preparation (jam dari sekarang)</label>
                            <select class="bella-form-control" id="finishOffsetHours" required></select>
                        </div>
                    </div>
                    <div class="modal-footer" style="gap:8px;">
                        <button type="button" class="act-btn secondary" data-dismiss="modal"
                            style="height:32px;padding:0 14px;font-size:12px;">
                            Batal
                        </button>
                        <button type="submit" class="act-btn primary"
                            style="height:32px;padding:0 14px;font-size:12px;">
                            <i class="fas fa-save mr-1"></i> Simpan rentang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
