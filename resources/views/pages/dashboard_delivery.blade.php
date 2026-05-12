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
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
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

        .delivery-timeline-card .pill-pct {
            display: none;
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
            min-width: 190px;
        }

        .delivery-weekly-card .weekly-axis-header .sep {
            opacity: .6;
        }

        .delivery-weekly-card .weekly-axis-header .col-type {
            min-width: 80px;
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
                            <h2 class="timeline-title">Timeline delivery</h2>
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
                            </ul>
                            <div class="timeline-total" id="timelineTotalSum">Total: 0</div>
                        </div>
                    </div>

                    {{-- Weekly card --}}
                    <div class="delivery-weekly-card mb-2 d-none" id="deliveryWeeklyCard">
                        <div class="weekly-card-header">
                            <h2 class="weekly-title">Ringkasan mingguan delivery</h2>
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
                                    <div class="col-md-4 mb-3">
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
                                    Field prep range dan ETA truck berdiri sendiri, tanpa asumsi otomatis.
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

            var customers = @json($customers);
            var chartRows = [];
            var chartMergedByCustTime = [];
            var chartCustomerOrder = [];
            var chartWeeklyPoints = [];
            var dashboardViewMode = 'daily';
            var weeklyChartInstance = null;
            var weeklyNowTimer = null;
            var weeklyGridTimer = null;
            var editMasterId = null;
            var ganttNowTimer = null;
            var waNotifyTimer = null;
            var ganttHourWindow = 24;
            var ganttCols = 24;
            var etaWindowStorageKey = 'delivery_eta_window_v2';
            var waSentStorageKey = 'delivery_wa_unfinished_sent_v1';
            /** Bar prep: pernah lewat finish prep & belum 100% → merah, tetap merah sampai 100% */
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
                var df = $('#filterDateFrom').val() || '';
                var dt = $('#filterDateTo').val() || '';
                var pe = (row.prep_end_time != null) ? String(row.prep_end_time).trim().substring(0, 5) : '';
                var ct = (row.cycle_time != null) ? String(row.cycle_time).trim().substring(0, 5) : '';
                return [df, dt, String(row.customer_id || ''), String(row.customer_name || ''), String(row
                        .cycle_name || ''), ct, pe].join('|');
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
                return String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;');
            }

            /* ── renderMasterTable ── */
            function renderMasterTable(rows) {
                var $tb = $('#masterCycleTable tbody');
                $tb.empty();
                if (!rows.length) {
                    $tb.append(
                        '<tr><td colspan="6" class="text-center" style="color:var(--text-muted);padding:20px;">Belum ada master cycle.</td></tr>'
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
                    var truckDisp = (r.truck_time && String(r.truck_time).length) ? formatClockDot(r
                        .truck_time) : '<span style="color:var(--text-muted);">-</span>';
                    $tb.append(
                        '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + escapeHtml(cust) + '</td>' +
                        '<td>' + escapeHtml(r.cycle_name) + '</td>' +
                        '<td>' + prepRangeDisp + '</td>' +
                        '<td>' + truckDisp + '</td>' +
                        '<td style="white-space:nowrap;">' +
                        '<a class="act-btn primary btn-edit-master" data-id="' + r.id +
                        '" style="height:24px;padding:0 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;margin-right:4px;">' +
                        '<i class="fas fa-edit"></i> Edit</a>' +
                        '<a class="act-btn danger btn-del-master" data-id="' + r.id +
                        '" style="height:24px;padding:0 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;">' +
                        '<i class="fas fa-trash"></i> Hapus</a>' +
                        '</td>' +
                        '</tr>'
                    );
                });
            }

            function fetchMasters() {
                $.get(masterIndex, function(res) {
                    renderMasterTable((res && res.data) ? res.data : []);
                });
            }

            $('#masterCycleForm').on('submit', function(e) {
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
                    $.post(masterStore, payload, function() {
                        $('#masterCycleForm')[0].reset();
                        fetchMasters();
                        loadStackedChart();
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
                    $('#mcycleTruckTime').val(etaTruck);
                    $('#mcycleCustomerId').val(row.customer_id != null ? String(row.customer_id) :
                        '');
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
            function weeklyDaySlots(dateFrom, dateTo) {
                // Kembalikan array tanggal dari dateFrom s/d dateTo (string 'YYYY-MM-DD')
                var slots = [];
                var d = new Date(dateFrom + 'T00:00:00');
                var end = new Date(dateTo + 'T00:00:00');
                while (d <= end) {
                    slots.push(new Date(d));
                    d.setDate(d.getDate() + 1);
                }
                return slots;
            }

            function weeklyDateLabel(dt) {
                return dt.toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short'
                });
            }

            function weeklyFracOfDay(deliveryDate, clockStr) {
                // Kembalikan posisi 0-1 pada sumbu hari slot yang tepat (per deliveryDate)
                if (!clockStr || clockStr === '-') return null;
                var p = clockStr.substring(0, 5).split(':');
                var h = parseInt(p[0], 10);
                var m = parseInt(p[1] || '0', 10);
                if (isNaN(h) || isNaN(m)) return null;
                return h / 24 + m / 1440;
            }

            function mergeRowsForCustomerTime(rows) {
                var map = {};
                rows.forEach(function(row) {
                    var key = (row.customer_id || '') + '|' + (row.cycle_name || '') + '|' + (row
                        .cycle_time || '');
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
                    return timeToMinutes(a.cycle_time) - timeToMinutes(b.cycle_time);
                });
            }

            function buildGanttTooltip(row) {
                var pct = Math.round(parseFloat(row.progress_pct || 0) * 10) / 10;
                var done = parseInt(row.total_done || 0, 10);
                var tgt = parseInt(row.total_target || 0, 10);
                return (row.customer_name || '-') + ' · C' + (row.cycle_name || '?') + ' · ' + pct + '% (' + done +
                    '/' + tgt + ')';
            }

            function renderGantt() {
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }
                destroyWeeklyChart();
                ganttHourWindow = 24;
                ganttCols = 24;
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
                var labels = slotLabels24();
                var byCustomer = {};
                chartMergedByCustTime.forEach(function(row) {
                    var cn = row.customer_name || '-';
                    if (!byCustomer[cn]) {
                        byCustomer[cn] = [];
                    }
                    byCustomer[cn].push(row);
                });
                var custNames = chartCustomerOrder.filter(function(cn) {
                    return byCustomer[cn];
                });
                if (!custNames.length) {
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
                gridHtml += '<div class="gantt-grid-row gantt-grid-header">';
                gridHtml +=
                    '<div class="gantt-grid-cell gantt-grid-sticky-col gantt-grid-sticky-customer" style="font-weight:700;padding:0 10px;display:flex;align-items:center;">Customer</div>';
                gridHtml +=
                    '<div class="gantt-grid-cell gantt-grid-sticky-col gantt-grid-sticky-type" style="font-weight:700;display:flex;align-items:center;justify-content:center;">Type</div>';
                labels.forEach(function(lbl) {
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-time">' + escapeHtml(lbl) +
                        '</div>';
                });
                gridHtml += '</div>';
                /* Data rows */
                custNames.forEach(function(cn, custIdx) {
                    var buckets = byCustomer[cn];
                    gridHtml += '<div class="gantt-grid-row">';
                    gridHtml +=
                        '<div class="gantt-grid-cell gantt-grid-customer gantt-grid-left-cell gantt-grid-sticky-col gantt-grid-sticky-customer">' +
                        escapeHtml(cn) + '</div>';
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
                    buckets.forEach(function(row) {
                        var cycleAt = row.cycle_time,
                            prepEnd = row.prep_end_time;
                        var startFrac = timeToWindowFrac(cycleAt);
                        if (startFrac === null) {
                            return;
                        }
                        var durH = (prepEnd && String(prepEnd).length) ? calcDurationHours(cycleAt,
                            prepEnd) : 1;
                        var leftPct = (startFrac / ganttHourWindow) * 100;
                        var widthPct = (durH / ganttHourWindow) * 100;
                        var pct = parseFloat(row.progress_pct || 0);
                        var progressWidth = Math.min(100, Math.max(0, pct));
                        var latchKey = rowOverdueLatchKey(row);
                        var isOverdueNow = progressWidth < 100 && isRowPastFinishPrep(row, currentFrac);
                        if (isOverdueNow) {
                            overdueLatch[latchKey] = true;
                        }
                        if (progressWidth >= 100) {
                            delete overdueLatch[latchKey];
                        }
                        var isOverdue = !!overdueLatch[latchKey];
                        var barClass = progressWidth >= 100 ? 'ontime' : (isOverdue ? 'overdue' : (
                            progressWidth > 0 ? 'delay' : 'empty'));
                        var isOvernightPrep = durH > 12;
                        var isInstantPrep = durH < (1 / 60);
                        var barTitle = buildGanttTooltip(row).replace(/"/g, '&quot;') +
                            ' — Klik untuk buka Loading List';
                        var av = String(row.cycle_name || '?');
                        av = 'C' + av.toUpperCase();
                        var prepExtraClass = isOvernightPrep ? ' is-overnight' : (isInstantPrep ?
                            ' is-instant' : '');
                        gridHtml += '<div class="gantt-bar-wrap' + prepExtraClass +
                            '" style="left:' + leftPct + '%;width:' + widthPct + '%" title="' +
                            barTitle + '"';
                        gridHtml += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        gridHtml += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        gridHtml += ' data-delivery-date-from="' + escapeAttr($('#filterDateFrom')
                            .val() || '') + '"';
                        gridHtml += ' data-delivery-date-to="' + escapeAttr($('#filterDateTo')
                            .val() || '') + '">';
                        gridHtml += '<div class="gantt-bar-stack">';
                        if (progressWidth >= 100) {
                            gridHtml += '<span class="gantt-seg ontime" style="width:100%"></span>';
                        } else {
                            gridHtml += '<span class="gantt-seg ' + barClass + '" style="width:' +
                                progressWidth + '%"></span>';
                            gridHtml += '<span class="gantt-seg gantt-trail" style="width:' + (100 -
                                progressWidth) + '%"></span>';
                        }
                        gridHtml += '</div>';
                        gridHtml += '<span class="pill-meta"><span class="pill-avatar">' +
                            escapeHtml(av) + '</span></span>';
                        gridHtml += '</div>';
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
                    buckets.forEach(function(row) {
                        var truckAt = (row.truck_time != null && String(row.truck_time).length) ?
                            row.truck_time : null;
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
                        var truckTitle = buildGanttTooltip(row).replace(/"/g, '&quot;') +
                            ' — Klik untuk buka Loading List';
                        var truckLabel = 'C' + escapeHtml(String(row.cycle_name || '?'));
                        gridHtml += '<div class="gantt-bar-wrap truck-pill" style="left:' +
                            truckLeftPct + '%" title="' + truckTitle + '"';
                        gridHtml += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        gridHtml += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        gridHtml += ' data-delivery-date-from="' + escapeAttr($('#filterDateFrom')
                            .val() || '') + '"';
                        gridHtml += ' data-delivery-date-to="' + escapeAttr($('#filterDateTo')
                            .val() || '') + '">';
                        gridHtml += '<div class="gantt-bar-stack"><span class="gantt-seg ' +
                            truckSegClass + '" style="width:100%"></span></div>';
                        gridHtml += '<span class="pill-meta"><span class="pill-avatar">' +
                            truckLabel + '</span></span>';
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

            $('#ganttContainer').on('click', '.gantt-bar-wrap', function(e) {
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

            function renderWeeklySummary() {
                destroyWeeklyChart();
                if (weeklyGridTimer) {
                    clearInterval(weeklyGridTimer);
                    weeklyGridTimer = null;
                }

                if (!chartWeeklyPoints.length) {
                    $('#weeklyGanttContainer').html('<div class="text-muted small">Tidak ada data untuk ditampilkan.</div>');
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

                // Kelompokkan chartWeeklyPoints per customer
                var custMap = {};
                var custOrder = [];
                chartWeeklyPoints.forEach(function(row) {
                    var c = row.customer_name || '-';
                    if (!custMap[c]) {
                        custMap[c] = [];
                        custOrder.push(c);
                    }
                    custMap[c].push(row);
                });
                custOrder.sort(function(a, b) {
                    return a.localeCompare(b);
                });

                // CSS variabel lebar per hari (mirip --gantt-hour-width tapi per hari)
                var dayColWidth = 120; // px per hari (minimum)
                var custColWidth = 160;
                var typeColWidth = 120;
                var minTableWidth = custColWidth + typeColWidth + (nSlots * dayColWidth);

                // ── Build HTML ────────────────────────────────────────────────────
                var gridStyle = [
                    'min-width:' + minTableWidth + 'px',
                    'width:100%',
                    'border-collapse:collapse',
                    'font-size:13px',
                    'table-layout:fixed'
                ].join(';');

                var html = '<div style="overflow-x:auto;overflow-y:hidden;width:100%;min-width:100%;">';
                html += '<table style="' + gridStyle + '">';

                // Header
                html += '<thead><tr>';
                html += '<th style="position:sticky;top:0;left:0;z-index:7;background:#fff;border:1px solid #eef0f3;width:' +
                    custColWidth + 'px;text-align:center;padding:4px 8px;font-size:11px;font-weight:600;color:#5c6370;">Customer</th>';
                html += '<th style="position:sticky;top:0;left:' + custColWidth +
                    'px;z-index:7;background:#fff;border:1px solid #eef0f3;width:' + typeColWidth +
                    'px;text-align:center;padding:4px 8px;font-size:11px;font-weight:600;color:#5c6370;">TYPE</th>';
                slots.forEach(function(dt) {
                    html += '<th style="position:sticky;top:0;z-index:6;background:#fff;border:1px solid #eef0f3;width:' +
                        dayColWidth +
                        'px;text-align:center;padding:3px 4px;font-size:10px;font-weight:600;color:#5c6370;white-space:nowrap;">' +
                        weeklyDateLabel(dt) + '</th>';
                });
                html += '</tr></thead><tbody>';

                // Hitung posisi "Now" untuk marker
                var todayStr = new Date().toISOString().slice(0, 10);
                var nowDayIdx = -1;
                slots.forEach(function(dt, i) {
                    if (dt.toISOString().slice(0, 10) === todayStr) nowDayIdx = i;
                });
                var nowFrac = (new Date().getHours() + new Date().getMinutes() / 60) / 24;
                var nowLeftPct = nowDayIdx >= 0 ? ((nowDayIdx + nowFrac) / nSlots) * 100 : -1;
                var nowLbl = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                custOrder.forEach(function(cust, custIdx) {
                    var rows = custMap[cust];
                    var rowHeight = 136; // px – sama dengan daily

                    // ── Baris PREP ──
                    html += '<tr>';
                    // Kolom Customer dengan rowspan=2
                    html += '<td rowspan="2" style="' +
                        'position:sticky;left:0;z-index:5;' +
                        'background:#fff;border:1px solid #eef0f3;' +
                        'width:' + custColWidth + 'px;' +
                        'vertical-align:middle;text-align:center;' +
                        'font-size:12px;font-weight:500;padding:8px 10px;' +
                        'word-break:break-word;line-height:1.3;' +
                        '">' + escapeHtml(cust) + '</td>';
                    // Kolom TYPE – PREP
                    html += '<td style="' +
                        'position:sticky;left:' + custColWidth + 'px;z-index:5;' +
                        'background:#fff;border:1px solid #eef0f3;' +
                        'width:' + typeColWidth + 'px;height:' + (rowHeight / 2) + 'px;' +
                        'text-align:center;vertical-align:middle;' +
                        'font-size:10px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#5c7f2b;' +
                        '">PREP</td>';
                    // Slot hari – lane PREP
                    slots.forEach(function(dt, dayIdx) {
                        var slotDateStr = dt.toISOString().slice(0, 10);
                        var isToday = slotDateStr === todayStr;
                        var bg = isToday ? 'background:rgba(255,235,235,.35);' : '';
                        html += '<td style="position:relative;padding:0;border:1px solid #eef0f3;width:' +
                            dayColWidth + 'px;height:' + (rowHeight / 2) +
                            'px;vertical-align:top;overflow:visible;' + bg + '">';
                        html += '<div style="position:relative;height:' + (rowHeight / 2) + 'px;">';
                        // Bar PREP untuk hari ini
                        rows.forEach(function(row) {
                            if (String(row.delivery_date || '').trim() !== slotDateStr) return;
                            var prepStart = row.prep_time ? String(row.prep_time).substring(0, 5) : null;
                            var prepEnd = row.prep_end_time ? String(row.prep_end_time).substring(0, 5) : null;
                            if (!prepStart) return;
                            var startFrac = weeklyFracOfDay(slotDateStr, prepStart);
                            var endFrac = prepEnd ? weeklyFracOfDay(slotDateStr, prepEnd) : null;
                            if (endFrac === null || endFrac <= startFrac) endFrac = startFrac + (30 / 1440);
                            var leftPct = startFrac * 100;
                            var widthPct = Math.max((endFrac - startFrac) * 100, 4);
                            var pct = Math.round(parseFloat(row.progress_pct || 0) * 10) / 10;
                            var barColor = pct >= 100 ? 'var(--dm-complete)' : 'var(--dm-yellow)';
                            var tipText = escapeAttr('Prep ' + prepStart + (prepEnd ? ' - ' + prepEnd : '') +
                                ' | Cycle ' + (row.cycle_name || '-') + ' | Progress ' + pct +
                                '% | Done ' + (row.total_done || 0) + '/' + (row.total_target || 0));
                            var av = 'C' + escapeHtml(String(row.cycle_name || '?').toUpperCase());
                            html += '<div title="' + tipText + '" style="' +
                                'position:absolute;top:6px;height:26px;' +
                                'left:' + leftPct + '%;width:' + widthPct + '%;min-width:28px;' +
                                'background:' + barColor + ';border-radius:4px;' +
                                'box-shadow:0 1px 2px rgba(0,0,0,.08);' +
                                'overflow:hidden;cursor:pointer;box-sizing:border-box;z-index:2;' +
                                'display:flex;align-items:center;padding-left:4px;' +
                                '">' +
                                '<span style="font-size:9px;font-weight:700;color:#2f3542;white-space:nowrap;">' + av +
                                '</span>' +
                                '</div>';
                        });
                        // Garis Now pada lane PREP jika hari ini
                        if (isToday) {
                            var nFrac = (new Date().getHours() + new Date().getMinutes() / 60) / 24;
                            html += '<div style="position:absolute;top:0;bottom:0;left:' + (nFrac * 100) +
                                '%;width:2px;background:rgb(67,53,220);transform:translateX(-50%);z-index:4;pointer-events:none;">' +
                                '<span style="position:absolute;top:-14px;left:0;transform:translateX(-50%);font-size:8px;background:rgb(59,53,220);color:#fff;padding:1px 4px;border-radius:2px;white-space:nowrap;">Now</span>' +
                                '</div>';
                        }
                        html += '</div></td>';
                    });
                    html += '</tr>';

                    // ── Baris ETA TRUCK ──
                    html += '<tr>';
                    // Kolom TYPE – ETA TRUCK
                    html += '<td style="' +
                        'position:sticky;left:' + custColWidth + 'px;z-index:5;' +
                        'background:#fff;border:1px solid #eef0f3;' +
                        'width:' + typeColWidth + 'px;height:' + (rowHeight / 2) + 'px;' +
                        'text-align:center;vertical-align:middle;' +
                        'font-size:10px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#b86f00;' +
                        '">ETA TRUCK</td>';
                    // Slot hari – lane ETA TRUCK
                    slots.forEach(function(dt, dayIdx) {
                        var slotDateStr = dt.toISOString().slice(0, 10);
                        var isToday = slotDateStr === todayStr;
                        var bg = isToday ? 'background:rgba(255,235,235,.35);' : '';
                        html += '<td style="position:relative;padding:0;border:1px solid #eef0f3;width:' +
                            dayColWidth + 'px;height:' + (rowHeight / 2) +
                            'px;vertical-align:top;overflow:visible;' + bg + '">';
                        html += '<div style="position:relative;height:' + (rowHeight / 2) + 'px;">';
                        rows.forEach(function(row) {
                            if (String(row.delivery_date || '').trim() !== slotDateStr) return;
                            var truckTime = row.truck_time ? String(row.truck_time).substring(0, 5) : null;
                            if (!truckTime) return;
                            var truckFrac = weeklyFracOfDay(slotDateStr, truckTime);
                            if (truckFrac === null) return;
                            var leftPct = truckFrac * 100;
                            var pct = Math.round(parseFloat(row.progress_pct || 0) * 10) / 10;
                            var barColor = pct >= 100 ? 'var(--dm-blue)' : '#f59e0b';
                            var tipText = escapeAttr('ETA Truck ' + truckTime + ' | Cycle ' + (row.cycle_name || '-') +
                                ' | Progress ' + pct + '% | Done ' + (row.total_done || 0) + '/' + (row.total_target || 0));
                            var av = 'C' + escapeHtml(String(row.cycle_name || '?').toUpperCase());
                            html += '<div title="' + tipText + '" style="' +
                                'position:absolute;top:8px;height:22px;' +
                                'left:' + leftPct + '%;width:42px;min-width:42px;' +
                                'background:' + barColor + ';border-radius:4px;' +
                                'box-shadow:0 1px 2px rgba(0,0,0,.08);' +
                                'cursor:pointer;box-sizing:border-box;z-index:2;' +
                                'display:flex;align-items:center;padding-left:4px;' +
                                '">' +
                                '<span style="font-size:9px;font-weight:700;color:#2f3542;white-space:nowrap;">' + av +
                                '</span>' +
                                '</div>';
                        });
                        if (isToday) {
                            var nFrac2 = (new Date().getHours() + new Date().getMinutes() / 60) / 24;
                            html += '<div style="position:absolute;top:0;bottom:0;left:' + (nFrac2 * 100) +
                                '%;width:2px;background:rgb(67,53,220);transform:translateX(-50%);z-index:4;pointer-events:none;"></div>';
                        }
                        html += '</div></td>';
                    });
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                $('#weeklyGanttContainer').html(html);

                // Ticker: update garis Now setiap detik
                weeklyGridTimer = setInterval(function() {
                    var now2 = new Date();
                    var nFrac3 = (now2.getHours() + now2.getMinutes() / 60 + now2.getSeconds() / 3600) / 24;
                    var todayS = now2.toISOString().slice(0, 10);
                    slots.forEach(function(dt, dayIdx) {
                        if (dt.toISOString().slice(0, 10) !== todayS) return;
                        $('#weeklyGanttContainer [data-wday="' + dayIdx + '"] .weekly-now-line').css('left', (nFrac3 * 100) +
                            '%');
                    });
                }, 1000);

                updateTimelineTotal();
            }

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
                $.get(stackedUrl, params, function(res) {
                    chartRows = res.rows || [];
                    chartWeeklyPoints = res.weekly_points || [];
                    var viewMode = detectDashboardViewMode();
                    var hint = (res.meta && res.meta.hint) ? res.meta.hint : '';
                    if (!chartRows.length) {
                        var msg = hint || 'Tidak ada data delivery untuk filter ini.';
                        $('#chartEmpty').removeClass('d-none').text(msg);
                        $('#timelineTotalSum').text('Total: 0');
                        return;
                    }
                    chartMergedByCustTime = mergeRowsForCustomerTime(chartRows);
                    var custSet = {};
                    chartMergedByCustTime.forEach(function(m) {
                        custSet[m.customer_name] = true;
                    });
                    chartCustomerOrder = Object.keys(custSet).sort(function(a, b) {
                        return a.localeCompare(b);
                    });
                    applyDashboardViewMode(viewMode);
                    if (dashboardViewMode === 'daily') {
                        renderGantt();
                    } else {
                        renderWeeklySummary();
                    }
                    checkAndSendUnfinishedWaNotification();
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
                var today = new Date().toISOString().slice(0, 10);
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
