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

        .delivery-sync-control {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-left: auto;
            padding-left: 14px;
            border-left: 1px solid var(--border);
        }

        .delivery-last-sync {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 8px;
            color: #29704a;
            font-size: 10.5px;
            font-weight: 600;
        }

        .delivery-last-sync.is-empty {
            color: var(--text-muted);
            font-weight: 500;
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

        /* ── Delivery sync loader ── */
        .delivery-sync-popup {
            position: relative !important;
            width: min(420px, calc(100vw - 32px)) !important;
            padding: 22px 24px 22px !important;
            border: 1px solid rgba(255, 255, 255, .7) !important;
            border-radius: 20px !important;
            background: #fff !important;
            box-shadow: 0 24px 70px rgba(8, 32, 56, .26) !important;
            overflow: hidden !important;
        }

        .delivery-sync-popup::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 4px;
            background: linear-gradient(90deg, var(--navy), var(--sky));
        }

        .delivery-sync-html {
            margin: 0 !important;
        }

        /* Scene truk */
        .delivery-sync-scene {
            margin: 4px 0 16px;
            border-radius: 16px;
            background: linear-gradient(180deg, #eef6fd, #dfeaf6);
            overflow: hidden;
        }

        .delivery-sync-scene svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .dst-body {
            animation: dstBob 1.1s ease-in-out infinite;
        }

        .dst-wheel {
            transform-box: fill-box;
            transform-origin: center;
            animation: dstSpin .85s linear infinite;
        }

        .dst-dash {
            animation: dstDash .5s linear infinite;
        }

        .dst-bush {
            animation: dstRun 2.4s linear infinite;
        }

        .dst-bush.b {
            animation-delay: -1.2s;
        }

        .dst-puff {
            transform-box: fill-box;
            transform-origin: center;
            animation: dstPuff 1.6s ease-out infinite;
        }

        .dst-puff.b {
            animation-delay: -.8s;
        }

        @keyframes dstBob {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-2px);
            }
        }

        @keyframes dstSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes dstDash {
            to {
                stroke-dashoffset: -34;
            }
        }

        @keyframes dstRun {
            from {
                transform: translateX(330px);
            }

            to {
                transform: translateX(-70px);
            }
        }

        @keyframes dstPuff {
            0% {
                transform: translate(0, 0) scale(.6);
                opacity: .45;
            }

            100% {
                transform: translate(-18px, -13px) scale(1.6);
                opacity: 0;
            }
        }

        .delivery-sync-title {
            margin-bottom: 6px;
            color: var(--navy);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .delivery-sync-copy {
            color: var(--text-muted);
            font-size: 12px;
        }

        .delivery-sync-date {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 5px 11px;
            border: 1px solid rgba(0, 151, 216, .18);
            border-radius: 99px;
            color: var(--navy);
            background: rgba(0, 151, 216, .07);
            font-size: 11px;
            font-weight: 700;
        }

        .delivery-sync-progress {
            position: relative;
            height: 4px;
            border-radius: 99px;
            background: rgba(13, 54, 92, .08);
            overflow: hidden;
            margin-top: 16px;
        }

        .delivery-sync-progress::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 40%;
            height: 100%;
            border-radius: inherit;
            background: var(--sky);
            animation: dstProgress 1.25s ease-in-out infinite;
        }

        @keyframes dstProgress {
            0% {
                left: -40%;
            }

            100% {
                left: 100%;
            }
        }

        .delivery-sync-result-popup {
            width: min(680px, calc(100vw - 32px)) !important;
        }

        .delivery-sync-result-copy {
            margin: 0 0 14px;
            color: var(--text-muted);
            font-size: 13px;
        }

        .delivery-sync-notes {
            border: 1px solid #f3d7a5;
            border-radius: 12px;
            background: #fffaf0;
            text-align: left;
            overflow: hidden;
        }

        .delivery-sync-notes summary {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 12px 14px;
            color: #744b08;
            cursor: pointer;
            list-style: none;
            font-size: 13px;
            font-weight: 700;
        }

        .delivery-sync-notes summary::-webkit-details-marker {
            display: none;
        }

        .delivery-sync-notes summary::after {
            content: '';
            flex: 0 0 auto;
            width: 8px;
            height: 8px;
            margin: -4px 3px 0 1px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg);
            transition: transform .18s ease;
        }

        .delivery-sync-notes[open] summary::after {
            transform: rotate(225deg);
        }

        .delivery-sync-notes-count {
            margin-left: auto;
            padding: 2px 8px;
            border-radius: 99px;
            color: #9a5a00;
            background: #ffedc7;
            font-size: 11px;
        }

        .delivery-sync-notes-list {
            max-height: 320px;
            padding: 0 12px 12px;
            border-top: 1px solid #f3d7a5;
            overflow-y: auto;
        }

        .delivery-sync-note-item {
            padding: 11px 2px;
            border-bottom: 1px solid #f2e8d4;
        }

        .delivery-sync-note-item:last-child {
            border-bottom: 0;
        }

        .delivery-sync-note-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            color: var(--navy);
            font-size: 12px;
        }

        .delivery-sync-note-reason {
            color: #98600a;
            font-size: 11px;
            text-align: right;
        }

        .delivery-sync-note-parts {
            margin-top: 5px;
            color: var(--text-muted);
            font-size: 11px;
            word-break: break-word;
        }

        @media (prefers-reduced-motion: reduce) {

            .dst-body,
            .dst-wheel,
            .dst-dash,
            .dst-bush,
            .dst-puff,
            .delivery-sync-progress::after {
                animation: none !important;
            }
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
            height: 12px; /* SPLIT: 12px */
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

        .weekly-prep-bar.prep-orig {
            top: 36px; /* SPLIT: 36px */
        }

        /* SPLIT-ONLY (pengecil teks bar lama, aktif selama split on) */
        .weekly-prep-bar.prep-orig>span {
            font-size: 8px;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .weekly-prep-bar.prep-split-top {
            top: 2px;
            height: 28px;
            padding: 2px;
            gap: 2px;
            align-items: stretch;
            transition: filter .15s ease, box-shadow .15s ease;
        }

        .weekly-prep-bar.prep-split-top:hover {
            filter: brightness(1.04);
            box-shadow: 0 0 0 2px rgba(0, 151, 216, .45), 0 1px 2px rgba(0, 0, 0, .08);
        }

        .weekly-split-top-seg {
            position: relative;
            flex: 1 1 0%;
            min-width: 0;
            border-radius: 3px;
            background: rgba(255, 255, 255, .45);
            overflow: hidden;
            cursor: pointer;
        }

        .weekly-split-top-seg:hover {
            outline: 2px solid rgba(255, 255, 255, .95);
            outline-offset: -2px;
            filter: brightness(1.1);
            z-index: 2;
        }

        .weekly-split-top-fill {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
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

        /* ── KPI strip ── */
        .delivery-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .dk-card {
            position: relative;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 12px 14px;
            overflow: hidden;
        }

        .dk-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            border-radius: 12px 12px 0 0;
            background: #cbd5e1;
        }

        .dk-card.b::before {
            background: linear-gradient(90deg, var(--navy), var(--sky));
        }

        .dk-card.g::before {
            background: #22c55e;
        }

        .dk-card.r.is-alert::before {
            background: #dc3545;
        }

        .dk-card.a.is-alert::before {
            background: #f59e0b;
        }

        .dk-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 7px;
        }

        .dk-value {
            font-size: 25px;
            font-weight: 800;
            line-height: 1;
            color: var(--navy);
            font-variant-numeric: tabular-nums;
        }

        .dk-card.g .dk-value {
            color: #15803d;
        }

        .dk-card.r.is-alert .dk-value,
        .dk-card.r.is-alert .dk-sub {
            color: #b91c1c;
        }

        .dk-card.a.is-alert .dk-value,
        .dk-card.a.is-alert .dk-sub {
            color: #b45309;
        }

        .dk-sub {
            margin-top: 5px;
            font-size: 10.5px;
            color: var(--text-muted);
        }

        @media (max-width: 640px) {
            .delivery-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Timeline header status tint */
        .delivery-timeline-card .timeline-card-header.is-ok {
            background: #f2fbf5;
            box-shadow: inset 3px 0 0 #22c55e;
        }

        .delivery-timeline-card .timeline-card-header.is-late {
            background: #fff6f6;
            box-shadow: inset 3px 0 0 #dc3545;
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
            height: 14px; /* SPLIT: 14px */
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
            height: 14px; /* SPLIT: 14px */
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
            align-items: stretch;
        }

        /* ── Bar split baru di atas bar lama (tanpa label) ── */
        .delivery-timeline-card .gantt-grid-track-lane.prep-lane .gantt-track {
            height: 54px; /* SPLIT: 54px */
            margin: 2px 0; /* SPLIT: 2px 0 */
            overflow: visible;
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-orig {
            top: 36px; /* SPLIT: 36px */
        }

        /* SPLIT-ONLY (pengecil bar lama, aktif selama split on) */
        .delivery-timeline-card .gantt-bar-wrap.prep-orig .pill-avatar {
            width: 14px;
            height: 14px;
            font-size: 8px;
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-orig .pill-meta {
            padding-left: 3px;
            gap: 2px;
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-orig .pill-offset-badge {
            display: none;
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-orig .gantt-bar-pct {
            font-size: 7px;
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-split-top {
            top: 2px;
            height: 28px;
            min-width: 48px;
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-split-top:hover .gantt-bar-stack.is-split-top {
            outline: 2px solid rgba(0, 151, 216, .45);
            outline-offset: 1px;
            filter: brightness(1.03);
        }

        .delivery-timeline-card .gantt-bar-wrap.prep-split-top .gantt-bar-stack.is-split-top {
            display: flex;
            gap: 2px;
            padding: 2px;
            height: 28px;
            border-radius: 4px;
            background: transparent;
            box-shadow: none;
            align-items: stretch;
            transition: outline .15s ease, filter .15s ease;
        }

        .delivery-timeline-card .prep-split-top-seg {
            position: relative;
            flex: 1 1 0%;
            min-width: 0;
            border-radius: 3px;
            background: #e8ecf1;
            overflow: hidden;
            cursor: pointer;
        }

        .delivery-timeline-card .prep-split-top-seg:hover {
            outline: 2px solid rgba(0, 151, 216, .75);
            outline-offset: -1px;
            filter: brightness(1.08);
            z-index: 2;
        }

        /* ── Hover tooltip info PDS per segmen split ── */
        .prep-pds-tip {
            position: fixed;
            z-index: 9999;
            min-width: 230px;
            max-width: 300px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 12px 32px rgba(8, 32, 56, .22);
            padding: 10px 12px;
            font-size: 12px;
            color: #1f2937;
            pointer-events: none;
        }

        .prep-pds-tip .tip-pds {
            font-weight: 800;
            font-size: 13px;
            color: #0d365c;
            margin-bottom: 4px;
        }

        .prep-pds-tip .tip-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 1px 0;
            color: #4b5563;
        }

        .prep-pds-tip .tip-row strong {
            color: #111827;
        }

        .prep-pds-tip .tip-bar {
            height: 8px;
            border-radius: 99px;
            background: #eef0f3;
            overflow: hidden;
            margin: 6px 0 4px;
        }

        .prep-pds-tip .tip-bar>div {
            height: 100%;
        }

        .prep-pds-tip .tip-status {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 800;
        }

        .prep-pds-tip .tip-status.is-complete {
            background: #dcfce7;
            color: #15803d;
        }

        .prep-pds-tip .tip-status.is-progress {
            background: #fef3c7;
            color: #b45309;
        }

        .prep-pds-tip .tip-status.is-incomplete {
            background: #fee2e2;
            color: #b91c1c;
        }

        .delivery-timeline-card .prep-split-top-fill {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
        }

        .delivery-timeline-card .prep-split-top-fill.ontime {
            background: var(--dm-complete);
        }

        .delivery-timeline-card .prep-split-top-fill.delay {
            background: var(--dm-yellow);
        }

        .delivery-timeline-card .prep-split-top-fill.overdue {
            background: #dc3545;
        }

        .delivery-timeline-card .prep-split-top-fill.empty {
            background: transparent;
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

        .customer-ll-summary {
            display: block;
            margin-top: 4px;
            overflow: hidden;
            color: #2563eb;
            font-size: 9px;
            font-weight: 700;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .delivery-timeline-card .timeline-card-legend {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 18px;
            padding: 12px 16px;
            border-top: 1px solid #eef0f3;
            background: #ffffff;
        }

        .delivery-timeline-card .legend-group {
            min-width: 0;
        }

        .delivery-timeline-card .legend-title {
            margin-bottom: 7px;
            color: #1f2a44;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
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

        @media (max-width: 768px) {
            .delivery-timeline-card .timeline-card-legend {
                grid-template-columns: 1fr;
            }
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
            min-width: calc(var(--gantt-customer-col-width) + var(--gantt-type-col-width) + (var(--weekly-day-count, 1) * 240px));
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

            .delivery-sync-control {
                width: 100%;
                margin-left: 0;
                padding-left: 0;
                border-left: 0;
            }

            .delivery-sync-control .bella-filter-group {
                flex: 1;
            }

            .delivery-last-sync {
                justify-content: flex-start;
            }
        }

        /* ── TV mode ── */
        body.delivery-tv-active {
            overflow: hidden !important;
        }

        .delivery-page-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .delivery-tv-toggle {
            height: 34px;
            padding: 0 13px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #f8fafc;
            color: var(--navy);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .delivery-tv-toggle:hover {
            border-color: var(--sky);
            background: #eef8fd;
        }

        .delivery-dash.is-tv-mode {
            --gantt-customer-col-width: 150px;
            --gantt-type-col-width: 62px;
            --gantt-hour-width: calc((100vw - var(--gantt-left-cols) - 20px) / 24);
            --tv-row-height: 64px;
            position: fixed;
            inset: 0;
            z-index: 1050;
            width: 100vw;
            height: 100vh;
            margin: 0 !important;
            padding: 6px;
            overflow: hidden;
            background: #edf2f7;
        }

        .delivery-dash.is-tv-mode .bella-page-header {
            height: 42px;
            margin-bottom: 6px;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .delivery-dash.is-tv-mode .bella-page-header-title {
            font-size: 14px;
        }

        .delivery-dash.is-tv-mode .bella-page-header>div:first-child>div {
            width: 28px !important;
            height: 28px !important;
        }

        .delivery-dash.is-tv-mode .bella-page-header-clock .clock-label {
            display: none;
        }

        .delivery-dash.is-tv-mode .bella-page-header-clock #deliveryDashLiveTime {
            font-size: 14px;
        }

        .delivery-dash.is-tv-mode .delivery-tv-toggle {
            height: 28px;
            border-color: #fecaca;
            background: #fff1f2;
            color: #b91c1c;
        }

        .delivery-dash.is-tv-mode>.bella-content-card {
            height: calc(100vh - 54px);
            margin: 0;
            border: 0;
            border-radius: 8px;
            box-shadow: none;
            background: transparent;
        }

        .delivery-dash.is-tv-mode .bella-tabs,
        .delivery-dash.is-tv-mode .bella-filter-card,
        .delivery-dash.is-tv-mode .delivery-kpis,
        .delivery-dash.is-tv-mode .timeline-card-legend,
        .delivery-dash.is-tv-mode .timeline-card-footer,
        .delivery-dash.is-tv-mode .delivery-weekly-note {
            display: none !important;
        }

        .delivery-dash.is-tv-mode .bella-tab-content,
        .delivery-dash.is-tv-mode #pane-chart {
            height: 100%;
            padding: 0;
            border: 0;
            border-radius: 8px;
        }

        .delivery-dash.is-tv-mode .delivery-timeline-card,
        .delivery-dash.is-tv-mode .delivery-weekly-card {
            height: 100%;
            margin: 0 !important;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
        }

        .delivery-dash.is-tv-mode .timeline-card-header,
        .delivery-dash.is-tv-mode .weekly-card-header {
            min-height: 34px;
            padding: 5px 10px;
        }

        .delivery-dash.is-tv-mode .timeline-title,
        .delivery-dash.is-tv-mode .weekly-title {
            font-size: 11px;
        }

        .delivery-dash.is-tv-mode .timeline-late-summary {
            max-width: 48vw;
            padding: 2px 6px;
            font-size: 9px;
        }

        .delivery-dash.is-tv-mode .timeline-menu-btn {
            display: none;
        }

        .delivery-dash.is-tv-mode .gantt-wrap,
        .delivery-dash.is-tv-mode .weekly-card-body,
        .delivery-dash.is-tv-mode .weekly-gantt-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
        }

        .delivery-dash.is-tv-mode .gantt-grid-scroll {
            width: 100%;
            height: 100%;
            overflow: auto;
        }

        .delivery-dash.is-tv-mode .gantt-grid {
            width: 100%;
            min-width: 0;
        }

        .delivery-dash.is-tv-mode .gantt-grid-row {
            grid-template-columns: var(--gantt-customer-col-width) var(--gantt-type-col-width) repeat(var(--gantt-slot-count), minmax(0, 1fr));
        }

        .delivery-dash.is-tv-mode .gantt-grid-header .gantt-grid-cell {
            height: 30px;
            min-width: 0;
            padding: 0 2px !important;
            font-size: 8px;
        }

        .delivery-dash.is-tv-mode .gantt-grid-time {
            width: auto;
            min-width: 0;
        }

        .delivery-dash.is-tv-mode .gantt-grid-left-cell,
        .delivery-dash.is-tv-mode .gantt-grid-track-cell {
            height: auto;
            min-height: var(--tv-row-height);
        }

        .delivery-dash.is-tv-mode .gantt-grid-customer {
            padding: 3px 6px;
            font-size: 9px;
            line-height: 1.1;
            word-break: break-word;
        }

        .delivery-dash.is-tv-mode .customer-ll-summary,
        .delivery-dash.is-tv-mode .customer-unconfigured-note {
            display: none;
        }

        .delivery-dash.is-tv-mode .gantt-type-lane {
            padding: 0 2px;
            font-size: 7px !important;
            letter-spacing: 0 !important;
        }

        .delivery-dash.is-tv-mode .gantt-track {
            height: calc((var(--tv-row-height) / 2) - 6px);
            margin: 3px 0;
            background-size: calc(100% / var(--gantt-slot-count)) 100%;
        }

        /* TV + SPLIT: jangan timpa track prep (tinggi 54px + lane dari JS) */
        .delivery-dash.is-tv-mode .gantt-grid-track-lane.prep-lane .gantt-track {
            height: auto;
            min-height: 56px;
            margin: 2px 0;
        }

        .delivery-dash.is-tv-mode .gantt-bar-wrap {
            top: 2px;
            height: calc(100% - 4px);
            min-width: 22px;
        }

        /* TV + SPLIT: kembalikan height split & old bar seperti mode normal */
        .delivery-dash.is-tv-mode .gantt-bar-wrap.prep-split-top {
            top: 2px;
            height: 28px;
        }

        .delivery-dash.is-tv-mode .gantt-bar-wrap.prep-split-top .gantt-bar-stack.is-split-top {
            height: 28px;
        }

        .delivery-dash.is-tv-mode .gantt-bar-wrap.prep-orig {
            top: 36px;
            height: 14px;
        }

        .delivery-dash.is-tv-mode .gantt-bar-stack {
            height: 100%;
            border-radius: 3px;
        }

        .delivery-dash.is-tv-mode .gantt-bar-wrap.prep-orig .gantt-bar-stack {
            height: 14px;
        }

        .delivery-dash.is-tv-mode .gantt-bar-wrap.truck-pill {
            top: 2px;
            width: 30px !important;
            min-width: 30px;
            height: calc(100% - 4px);
        }

        .delivery-dash.is-tv-mode .gantt-bar-wrap.truck-pill .gantt-bar-stack {
            height: 100%;
        }

        .delivery-dash.is-tv-mode .pill-meta {
            gap: 2px;
            padding-left: 3px;
        }

        .delivery-dash.is-tv-mode .pill-avatar {
            width: 16px;
            height: 16px;
            font-size: 7px;
        }

        .delivery-dash.is-tv-mode .pill-offset-badge,
        .delivery-dash.is-tv-mode .prep-late-badge {
            display: none;
        }

        .delivery-dash.is-tv-mode .gantt-bar-pct {
            font-size: 7px !important;
        }

        .delivery-dash.is-tv-mode .gantt-now-label,
        .delivery-dash.is-tv-mode .gantt-truck-label {
            top: -22px;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            line-height: 1.2;
            box-shadow: 0 2px 5px rgba(15, 23, 42, .24);
        }

        .delivery-dash.is-tv-mode .gantt-now-line,
        .delivery-dash.is-tv-mode .gantt-truck-line {
            width: 3px;
        }

        .delivery-dash.is-tv-mode .gantt-now-line {
            box-shadow: 0 0 4px rgba(59, 53, 220, .75);
        }

        .delivery-dash.is-tv-mode .gantt-truck-line {
            box-shadow: 0 0 4px rgba(220, 53, 53, .75);
        }

        .delivery-dash.is-tv-mode .weekly-card-body {
            padding: 6px;
        }

        .delivery-dash.is-tv-mode .weekly-gantt-table {
            width: 100%;
            min-width: 0;
            font-size: 9px;
        }

        .delivery-dash.is-tv-mode .weekly-col-customer {
            padding: 3px 6px !important;
            font-size: 9px !important;
            line-height: 1.1 !important;
        }

        .delivery-dash.is-tv-mode .weekly-col-type,
        .delivery-dash.is-tv-mode .weekly-col-day,
        .delivery-dash.is-tv-mode .weekly-prep-track,
        .delivery-dash.is-tv-mode .weekly-col-day>div {
            height: auto !important;
            min-height: calc(var(--tv-row-height) / 2) !important;
        }

        /* TV + SPLIT weekly: beri ruang minimal untuk 2 bar (split 28px + orig 12px) */
        .delivery-dash.is-tv-mode .weekly-prep-track {
            min-height: 56px !important;
        }

        .delivery-dash.is-tv-mode .weekly-col-type {
            font-size: 7px !important;
            letter-spacing: 0 !important;
        }

        .delivery-dash.is-tv-mode .weekly-prep-bar,
        .delivery-dash.is-tv-mode .weekly-truck-pill {
            top: 3px !important;
            height: calc(100% - 6px) !important;
            min-height: 12px;
            font-size: 7px;
        }

        /* TV + SPLIT weekly: kembalikan posisi split & old bar seperti mode normal */
        .delivery-dash.is-tv-mode .weekly-prep-bar.prep-split-top {
            top: 2px !important;
            height: 28px !important;
        }

        .delivery-dash.is-tv-mode .weekly-prep-bar.prep-orig {
            top: 36px !important;
            height: 12px !important;
        }

        .delivery-dash.is-tv-mode .weekly-offset-badge {
            display: none;
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
            <div class="delivery-page-header-actions">
                <button type="button" class="delivery-tv-toggle" id="btnToggleTvMode" aria-pressed="false"
                    title="Tampilkan timeline secara fullscreen dan compact">
                    <i class="fas fa-expand mr-1" aria-hidden="true"></i>
                    <span>Full Screen</span>
                </button>
                <div class="bella-page-header-clock">
                    <span class="clock-label">Waktu lokal</span>
                    <span id="deliveryDashLiveTime">--:--:--</span>
                </div>
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
                            @can('manage-master')
                                <div class="delivery-sync-control">
                                    <div class="bella-filter-group">
                                        <label class="bella-filter-label" for="syncLoadingListDate">Tanggal awal sync (3
                                            hari)</label>
                                        <input type="date" class="bella-filter-input" id="syncLoadingListDate"
                                            value="{{ now()->toDateString() }}">
                                    </div>
                                    <button type="button" id="btnSyncLoadingLists" class="act-btn success"
                                        style="height:34px;padding:0 16px;font-size:12px;letter-spacing:.04em;">
                                        <i class="fas fa-database mr-1"></i> Sync data
                                    </button>
                                </div>
                            @endcan
                        </div>
                        @can('manage-master')
                            @php($lastSync = $lastLoadingListSync ?? null)
                            <div class="delivery-last-sync {{ $lastSync ? '' : 'is-empty' }}" id="lastSyncStatus">
                                <i class="fas fa-history" aria-hidden="true"></i>
                                <span id="lastSyncStatusText">
                                    @if ($lastSync)
                                        Terakhir sync {{ $lastSync['synced_at'] }} · tanggal {{ $lastSync['date_label'] }} ·
                                        {{ $lastSync['loading_list_count'] }} LL
                                        @if ($lastSync['skipped'] > 0)
                                            · {{ $lastSync['skipped'] }} catatan
                                        @endif
                                    @else
                                        Belum pernah sync data
                                    @endif
                                </span>
                            </div>
                        @endcan
                    </div>

                    <div id="chartEmpty" class="alert d-none mb-2" role="alert"></div>

                    {{-- KPI strip --}}
                    <div class="delivery-kpis" id="deliveryKpis">
                        <div class="dk-card b">
                            <div class="dk-label"><i class="fas fa-truck-loading"></i> Total delivery</div>
                            <div class="dk-value" id="kpiTotal">0</div>
                            <div class="dk-sub" id="kpiTotalSub">entri hari ini</div>
                        </div>
                        <div class="dk-card g">
                            <div class="dk-label"><i class="fas fa-check-circle"></i> Sudah pick up</div>
                            <div class="dk-value" id="kpiPicked">0</div>
                            <div class="dk-sub" id="kpiPickedSub">dari 0 truck</div>
                        </div>
                        <div class="dk-card r" id="kpiPrepCard">
                            <div class="dk-label"><i class="fas fa-exclamation-triangle"></i> Prep telat</div>
                            <div class="dk-value" id="kpiPrepLate">0</div>
                            <div class="dk-sub" id="kpiPrepLateSub">aman</div>
                        </div>
                        <div class="dk-card a" id="kpiTruckCard">
                            <div class="dk-label"><i class="fas fa-truck"></i> Truck delay</div>
                            <div class="dk-value" id="kpiTruckLate">0</div>
                            <div class="dk-sub" id="kpiTruckLateSub">pick up tepat</div>
                        </div>
                    </div>

                    {{-- Gantt timeline --}}
                    <div class="delivery-timeline-card mb-2" id="deliveryTimelineCard">
                        <div class="timeline-card-header d-flex justify-content-between align-items-center"
                            id="timelineCardHeader">
                            <div class="timeline-heading">
                                <h2 class="timeline-title">Timeline delivery</h2>
                                <span class="timeline-late-summary is-empty" id="timelineLateSummary">Tidak ada prep
                                    telat</span>
                            </div>
                            <button type="button" class="timeline-menu-btn" aria-label="Menu"
                                title="Menu">&#8942;</button>
                        </div>
                        <div class="timeline-card-legend">
                            <div class="legend-group">
                                <div class="legend-title">Preparation :</div>
                                <ul class="legend-timeline">
                                    <li>
                                        <span class="legend-dot" style="background: var(--dm-complete);"></span>
                                        Hijau : Close Pulling
                                    </li>
                                    <li>
                                        <span class="legend-dot" style="background: var(--dm-yellow);"></span>
                                        Kuning : On Progress Pulling (On Plan)
                                    </li>
                                    <li>
                                        <span class="legend-dot" style="background: #dc3545;"></span>
                                        Merah : Delay Pulling
                                    </li>
                                </ul>
                            </div>
                            <div class="legend-group">
                                <div class="legend-title">Truck :</div>
                                <ul class="legend-timeline">
                                    <li>
                                        <span class="legend-dot" style="background: #166534;"></span>
                                        Hijau Tua : Sudah Pick Up (On Time)
                                    </li>
                                    <li>
                                        <span class="legend-dot" style="background: #f97316;"></span>
                                        Orange : Delay Pick Up
                                    </li>
                                    <li>
                                        <span class="legend-dot" style="background: #991b1b;"></span>
                                        Merah Tua : Delay Delivery
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="gantt-wrap" id="ganttWrap">
                            <div id="ganttContainer"></div>
                        </div>
                        <div class="timeline-card-footer d-flex justify-content-end align-items-center">
                            <div class="timeline-total" id="timelineTotalSum">Total: 0</div>
                        </div>
                    </div>

                    {{-- Weekly card --}}
                    <div class="delivery-weekly-card mb-2 d-none" id="deliveryWeeklyCard">
                        <div class="weekly-card-header d-flex justify-content-between align-items-center">
                            <h2 class="weekly-title">Ringkasan mingguan delivery</h2>
                            <span class="timeline-late-summary is-empty" id="weeklyLateSummary">Tidak ada preparation
                                telat</span>
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

                    {{-- Master Cycle Table --}}
                    <div class="bella-content-card mb-3">
                        <div
                            style="padding:12px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                            <span
                                style="font-size:11.5px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.05em;">
                                Daftar Master Cycle
                            </span>
                            @can('manage-master')
                                <button type="button" class="act-btn success" id="btnAddMasterCycle"
                                    style="height:32px;padding:0 14px;font-size:12px;">
                                    <i class="fas fa-plus mr-1"></i> Tambah Master Cycle
                                </button>
                            @endcan
                        </div>
                        <div class="table-responsive">
                            <table class="bella-master-table" id="masterCycleTable">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">No</th>
                                        <th>Customer</th>
                                        <th>Cycle</th>
                                        <th>Rentang Preparation</th>
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
            var syncLoadingListsUrl = "{{ route('dashboard.delivery.syncLoadingLists') }}";
            var loadingListUrl = "{{ route('loadingList.index') }}";
            var kanbanSourceFilesUrl = "{{ route('dashboard.kanbanSourceFiles') }}";
            var manifestSourceFilesUrl = "{{ route('dashboard.manifestSourceFiles') }}";
            var skidSourceFilesUrl = "{{ route('dashboard.skidSourceFiles') }}";
            var qzCertUrl = "{{ route('dashboard.kanbanQzCert') }}";
            var qzSignUrl = "{{ route('dashboard.kanbanQzSign') }}";
            window.DOCUMENT_PRINTER_CONFIG = {
                kanban: "{{ config('printing.printers.kanban', 'Printer A') }}",
                manifest: "{{ config('printing.printers.manifest', 'Printer B') }}",
                skid: "{{ config('printing.printers.skid', 'Printer B') }}"
            };
            try {
                if (localStorage.getItem('qz_printer_kanban')) {
                    window.DOCUMENT_PRINTER_CONFIG.kanban = localStorage.getItem('qz_printer_kanban');
                }
                if (localStorage.getItem('qz_printer_manifest')) {
                    window.DOCUMENT_PRINTER_CONFIG.manifest = localStorage.getItem('qz_printer_manifest');
                }
                if (localStorage.getItem('qz_printer_skid')) {
                    window.DOCUMENT_PRINTER_CONFIG.skid = localStorage.getItem('qz_printer_skid');
                }
            } catch (err) {}
            window.DOCUMENT_PAPER_CONFIG = {
                kanban: {
                    width: {{ (int) config('printing.paper.kanban.width', 150) }},
                    height: {{ (int) config('printing.paper.kanban.height', 80) }},
                    units: "{{ config('printing.paper.kanban.units', 'mm') }}",
                    margins: {{ (int) config('printing.paper.kanban.margins', 0) }},
                    scale_content: {{ config('printing.paper.kanban.scale_content', true) ? 'true' : 'false' }},
                    copies: {{ (int) config('printing.paper.kanban.copies', 1) }}
                },
                manifest: {
                    width: {{ (int) config('printing.paper.manifest.width', 210) }},
                    height: {{ (int) config('printing.paper.manifest.height', 297) }},
                    units: "{{ config('printing.paper.manifest.units', 'mm') }}",
                    margins: {{ (int) config('printing.paper.manifest.margins', 0) }},
                    scale_content: {{ config('printing.paper.manifest.scale_content', true) ? 'true' : 'false' }},
                    copies: {{ (int) config('printing.paper.manifest.copies', 1) }}
                },
                skid: {
                    width: {{ (int) config('printing.paper.skid.width', 210) }},
                    height: {{ (int) config('printing.paper.skid.height', 297) }},
                    units: "{{ config('printing.paper.skid.units', 'mm') }}",
                    margins: {{ (int) config('printing.paper.skid.margins', 0) }},
                    scale_content: {{ config('printing.paper.skid.scale_content', true) ? 'true' : 'false' }},
                    copies: {{ (int) config('printing.paper.skid.copies', 1) }}
                }
            };
            // Backward-compatible helpers.
            window.setKanbanPrinter = function(name) {
                try { localStorage.setItem('qz_printer_kanban', String(name || '')); } catch (err) {}
                window.DOCUMENT_PRINTER_CONFIG.kanban = String(name || '');
                return 'Printer silent: ' + String(name || '(default)');
            };
            window.setManifestPrinter = function(name) {
                try { localStorage.setItem('qz_printer_manifest', String(name || '')); } catch (err) {}
                window.DOCUMENT_PRINTER_CONFIG.manifest = String(name || '');
                return 'Printer silent: ' + String(name || '(default)');
            };
            window.setSkidPrinter = function(name) {
                try { localStorage.setItem('qz_printer_skid', String(name || '')); } catch (err) {}
                window.DOCUMENT_PRINTER_CONFIG.skid = String(name || '');
                return 'Printer silent: ' + String(name || '(default)');
            };
            window.setPrinterForDocumentType = function(type, name) {
                if (type === 'manifest') return window.setManifestPrinter(name);
                if (type === 'skid') return window.setSkidPrinter(name);
                return window.setKanbanPrinter(name);
            };
            var notifyUnfinishedUrl = "{{ route('dashboard.delivery.notifyUnfinished') }}";
            var masterIndex = "{{ route('dashboard.delivery.masterCycles.index') }}";
            var masterStore = "{{ route('dashboard.delivery.masterCycles.store') }}";
            var masterBase = "{{ url('/dashboard/delivery/master-cycles') }}";
            var csrf = "{{ csrf_token() }}";

            // Hak akses: hanya admin/ppic (manage-master) yang boleh kelola master cycle
            var canManageMaster = @json(auth()->user()->can('manage-master'));

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

            function updateMasterCycleOffsetHint(event) {
                var prepStart = ($('#mcyclePrepStart').val() || '').trim();
                var prepEnd = ($('#mcyclePrepEnd').val() || '').trim();
                var truckTime = ($('#mcycleTruckTime').val() || '').trim();
                var inferred = inferPrepDayOffsetFromTruck(prepStart, truckTime);
                var shouldInfer = event && ['mcyclePrepStart', 'mcycleTruckTime'].indexOf(event.currentTarget
                    .id) !== -1;
                if (shouldInfer && inferred !== null) {
                    $('#mcyclePrepDayOffset').val(String(inferred));
                }
                var prepOff = $('#mcyclePrepDayOffset').val();
                var endOff = computePrepEndDayOffsetFromForm(prepOff, prepStart, prepEnd);
                var endLbl = formatPrepDayOffsetLabel(endOff);
                var $hint = $('#mcycleEndOffsetLabel');
                $hint.text(endLbl);
                if (shouldInfer && inferred !== null) {
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
                    pds_breakdown: wp.pds_breakdown || [],
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
                            '<div class="gantt-bar-wrap prep-orig is-prep-from-yesterday is-cross-day-continuation' +
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
                var maxDelayMs = 0;
                latePullingItems.forEach(function(item) {
                    maxDelayMs = Math.max(maxDelayMs, item.delayMs || 0);
                });
                var reasons = [];
                if (latePullingItems.length) {
                    reasons.push('Pulling lewat waktu Finish Preparation');
                }
                return {
                    isLate: latePullingItems.length > 0,
                    isPullingLate: latePullingItems.length > 0,
                    isProgressLate: false,
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
                    'Tidak ada preparation telat';
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
                        '<div class="master-cycle-swal-row"><span>Preparation</span><strong>' + escapeHtml(
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

            function resetMasterCycleForm() {
                editMasterId = null;
                var form = document.getElementById('masterCycleForm');
                if (form) {
                    form.reset();
                }
                $('#masterCycleModalTitle').text('Tambah Master Cycle');
                $('#btnMasterSave').html('<i class="fas fa-save mr-1"></i> Simpan');
                updateMasterCycleOffsetHint();
            }

            $('#btnAddMasterCycle').on('click', function() {
                resetMasterCycleForm();
                $('#masterCycleModal').modal('show');
            });

            $('#masterCycleModal').on('hidden.bs.modal', resetMasterCycleForm);

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
                    var updatedId = editMasterId;
                    $.ajax({
                        url: masterBase + '/' + editMasterId,
                        type: 'PUT',
                        data: payload,
                        success: function() {
                            $('#masterCycleModal').modal('hide');
                            fetchMasters(updatedId);
                            loadStackedChart();
                        },
                        error: function(xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ?
                                xhr.responseJSON.message :
                                'Gagal memperbarui master cycle.';
                            showMasterCycleErrorSwal(msg);
                        }
                    });
                } else {
                    var savedCustomerName = $('#mcycleCustomerId option:selected').text().trim();
                    var savedOffsetLabel = $('#mcyclePrepDayOffset option:selected').text().trim();
                    $.post(masterStore, payload)
                        .done(function(res) {
                            var newId = (res && res.data && res.data.id) ? res.data.id : null;
                            $('#masterCycleModal').one('hidden.bs.modal', function() {
                                showMasterCycleAddedSwal(payload, savedCustomerName,
                                    savedOffsetLabel);
                            }).modal('hide');
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
                    resetMasterCycleForm();
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
                    $('#masterCycleModalTitle').text('Edit Master Cycle');
                    $('#btnMasterSave').html('<i class="fas fa-save mr-1"></i> Update');
                    $('#masterCycleModal').modal('show');
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

            function updateTvTimelineLayout() {
                var $dash = $('.delivery-dash');
                if (!$dash.hasClass('is-tv-mode')) {
                    return;
                }

                var isDaily = dashboardViewMode === 'daily';
                var rowCount = isDaily ?
                    $('#ganttContainer .gantt-grid-row').not('.gantt-grid-header').length :
                    Math.ceil($('#weeklyGanttContainer tbody tr').length / 2);
                var $viewport = isDaily ? $('#ganttWrap') : $('#deliveryWeeklyCard .weekly-card-body');
                var $header = isDaily ? $('#ganttContainer .gantt-grid-header') : $(
                    '#weeklyGanttContainer thead');
                var availableHeight = ($viewport.innerHeight() || window.innerHeight) - ($header.outerHeight() ||
                    30);
                if (rowCount > 0) {
                    var rowHeight = Math.max(26, Math.min(88, Math.floor(availableHeight / rowCount)));
                    $dash.css('--tv-row-height', rowHeight + 'px');
                }
            }

            function setTvMode(enabled, manageFullscreen) {
                var $dash = $('.delivery-dash');
                $dash.toggleClass('is-tv-mode', enabled);
                $('body').toggleClass('delivery-tv-active', enabled);
                $('#btnToggleTvMode')
                    .attr('aria-pressed', enabled ? 'true' : 'false')
                    .toggleClass('is-active', enabled)
                    .html(enabled ?
                        '<i class="fas fa-compress mr-1" aria-hidden="true"></i><span>Full Screen</span>' :
                        '<i class="fas fa-expand mr-1" aria-hidden="true"></i><span>Full Screen</span>');

                if (enabled) {
                    $('.bella-tabs a[href="#pane-chart"]').tab('show');
                    if (dashboardViewMode === 'weekly') {
                        renderWeeklySummary();
                    }
                    window.requestAnimationFrame(function() {
                        window.requestAnimationFrame(updateTvTimelineLayout);
                    });
                    if (manageFullscreen !== false && !document.fullscreenElement && document.documentElement
                        .requestFullscreen) {
                        document.documentElement.requestFullscreen().catch(function() {});
                    }
                } else {
                    if (dashboardViewMode === 'weekly') {
                        renderWeeklySummary();
                    }
                    if (manageFullscreen !== false && document.fullscreenElement && document.exitFullscreen) {
                        document.exitFullscreen().catch(function() {});
                    }
                }
            }

            $('#btnToggleTvMode').on('click', function() {
                setTvMode(!$('.delivery-dash').hasClass('is-tv-mode'));
            });

            $(document).on('fullscreenchange', function() {
                if (!document.fullscreenElement && $('.delivery-dash').hasClass('is-tv-mode')) {
                    setTvMode(false, false);
                }
            });

            $(document).on('keydown', function(event) {
                if (event.key === 'Escape' && !document.fullscreenElement && $('.delivery-dash').hasClass(
                        'is-tv-mode')) {
                    setTvMode(false, false);
                }
            });

            $(window).on('resize', updateTvTimelineLayout);

            function classifyTruckStatusForKpi(row) {
                // ponytail: duplikat ringan aturan truck di renderGantt (~5859) biar tak refactor render path
                if (row.truck_status === 'picked' && row.truck_picked_at) {
                    var actual = new Date(row.truck_picked_at);
                    var sched = new Date(getTruckDisplayDate(row) + 'T' + normalizeClock(row.truck_time) +
                        ':00');
                    var early = new Date(sched.getTime() - 15 * 60 * 1000);
                    if (actual > sched) return 'late';
                    if (actual < early) return 'early';
                    return 'ontime';
                }
                return 'pending';
            }

            function updateDeliveryKpis() {
                var rows = chartMergedByCustTime.length ? chartMergedByCustTime :
                    filterRowsWithConfiguredMasterCycle(chartRows);
                rows = (rows || []).filter(rowHasConfiguredMasterCycle);

                var total = rows.length;
                var picked = 0,
                    truckLate = 0,
                    hasTruck = 0;
                rows.forEach(function(row) {
                    if (row.truck_time != null && String(row.truck_time).length) hasTruck++;
                    var st = classifyTruckStatusForKpi(row);
                    if (st !== 'pending') picked++;
                    if (st === 'late') truckLate++;
                });

                var prep = collectPrepLateSummary(rows);
                var prepLate = prep.itemCount;

                $('#kpiTotal').text(total);
                $('#kpiPicked').text(picked);
                $('#kpiPickedSub').text('dari ' + hasTruck + ' truck');
                $('#kpiPrepLate').text(prepLate);
                $('#kpiPrepLateSub').text(prepLate > 0 ? prep.customerCount + ' customer telat' : 'aman');
                $('#kpiTruckLate').text(truckLate);
                $('#kpiTruckLateSub').text(truckLate > 0 ? 'pick up mundur' : 'pick up tepat');

                $('#kpiPrepCard').toggleClass('is-alert', prepLate > 0);
                $('#kpiTruckCard').toggleClass('is-alert', truckLate > 0);

                var anyLate = prepLate > 0 || truckLate > 0;
                $('#timelineCardHeader').toggleClass('is-late', anyLate).toggleClass('is-ok', !anyLate);
            }

            function updateTimelineTotal() {
                var sum = chartMergedByCustTime.reduce(function(acc, r) {
                    return acc + (parseInt(r.total_done, 10) || 0);
                }, 0);
                $('#timelineTotalSum').text('Total: ' + sum);
                updateTimelineLateSummary();
                updateDeliveryKpis();
            }

            function buildWeeklyTooltip(details) {
                if (!details.length) {
                    return 'Tidak ada detail cycle.';
                }
                return details.map(function(d) {
                    return [
                        'Cycle ' + d.cycle_name,
                        'Preparation ' + d.prep_start + (d.prep_end !== '-' ? ' - ' + d.prep_end : ''),
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
                            ll_count: 0,
                            pds_breakdown: [],
                            _pdsSeen: {}
                        });
                    }
                    map[key].total_done += parseInt(row.total_done || 0, 10);
                    map[key].total_target += parseInt(row.total_target || 0, 10);
                    map[key].ll_count += parseInt(row.ll_count || 0, 10);
                    (Array.isArray(row.pds_breakdown) ? row.pds_breakdown : []).forEach(function(b) {
                        var bk = String((b && b.pds_number) || '');
                        if (!bk || map[key]._pdsSeen[bk]) {
                            return;
                        }
                        map[key]._pdsSeen[bk] = true;
                        map[key].pds_breakdown.push(b);
                    });
                });
                Object.keys(map).forEach(function(k) {
                    var m = map[k];
                    m.progress_pct = m.total_target > 0 ? (m.total_done / m.total_target) * 100 : 0;
                    m.pds_breakdown.sort(function(a, b) {
                        return String(a.pds_number || '').localeCompare(String(b.pds_number || ''));
                    });
                    delete m._pdsSeen;
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

            /* Urutan customer operasional (1-18). Di luar daftar: MMKI/TMMIN dulu lalu alfabetis. */
            var CUSTOMER_PRIORITY_ORDER = [
                'TMMINKRW1', 'MMKI', 'TMMINSUNTER', 'SENOPATI', 'FUJITRANS',
                'SUZUKIGA11', 'SUZUKIRKK', 'ADMKAP', 'HINO', 'TBINA', 'PT. OTICS Indonesia',
                'TMMIN53', 'TTI', 'ADMSPD', 'TAMSPD', 'MMKISPD', 'HINOSPD', 'SUZUKISPD'
            ];

            function normalizeCustomerKey(name) {
                return String(name || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
            }

            function customerPriorityRank(name) {
                var key = normalizeCustomerKey(name);
                if (!key) {
                    return CUSTOMER_PRIORITY_ORDER.length;
                }
                var exact = CUSTOMER_PRIORITY_ORDER.indexOf(key);
                if (exact >= 0) {
                    return exact;
                }
                /* Prefix terpanjang (mis. HINOSPD ikut HINO SPD, bukan HINO). */
                var best = -1;
                for (var i = 0; i < CUSTOMER_PRIORITY_ORDER.length; i++) {
                    var want = CUSTOMER_PRIORITY_ORDER[i];
                    if (key.indexOf(want) === 0 && (best < 0 || want.length > CUSTOMER_PRIORITY_ORDER[best].length)) {
                        best = i;
                    }
                }
                return best >= 0 ? best : CUSTOMER_PRIORITY_ORDER.length;
            }

            function compareCustomerPriority(a, b) {
                var pa = customerPriorityRank(a);
                var pb = customerPriorityRank(b);
                if (pa !== pb) {
                    return pa - pb;
                }
                if (pa >= CUSTOMER_PRIORITY_ORDER.length) {
                    var fa = /mmki|tmmin/i.test(String(a || '')) ? 0 : 1;
                    var fb = /mmki|tmmin/i.test(String(b || '')) ? 0 : 1;
                    if (fa !== fb) {
                        return fa - fb;
                    }
                }
                return String(a || '').localeCompare(String(b || ''));
            }

            function buildChartCustomerOrder(rows) {
                var custSet = {};
                (rows || []).forEach(function(row) {
                    var cn = row.customer_name || '-';
                    custSet[cn] = true;
                });
                return Object.keys(custSet).sort(compareCustomerPriority);
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
                        'Atur Preparation di Master Cycle: ' + tipCycles) + '">' +
                    escapeHtml(text) + '</span>';
            }

            function buildCustomerLoadingListSummaryHtml(rows) {
                var seen = {};
                var loadingLists = [];
                (rows || []).forEach(function(row) {
                    (Array.isArray(row.loading_lists) ? row.loading_lists : []).forEach(function(number) {
                        var value = String(number || '').trim();
                        if (value && !seen[value]) {
                            seen[value] = true;
                            loadingLists.push(value);
                        }
                    });
                });
                if (!loadingLists.length) {
                    return '';
                }
                var preview = loadingLists[0] + (loadingLists.length > 1 ? ' +' + (loadingLists.length - 1) : '');
                return '<span class="customer-ll-summary" title="' + escapeAttr(loadingLists.join(', ')) + '">' +
                    loadingLists.length + ' LL · ' + escapeHtml(preview) + '</span>';
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

            /* ── Bar split baru di atas bar lama: tanpa number / % ── */
            function getPrepSplitItems(row) {
                var lists = (row && Array.isArray(row.loading_lists)) ? row.loading_lists.filter(function(v) {
                    return String(v || '').trim().length;
                }) : [];
                if (lists.length > 1) {
                    return lists.map(function(v) {
                        return String(v);
                    }).slice(0, 24);
                }
                var n = parseInt((row && row.ll_count) || 0, 10);
                if (!n || n <= 1) {
                    var pds = (row && Array.isArray(row.pds_numbers)) ? row.pds_numbers.length : 0;
                    n = pds > 1 ? pds : 0;
                }
                if (!n || n <= 1) {
                    return [];
                }
                n = Math.min(n, 24);
                var out = [];
                for (var i = 0; i < n; i++) {
                    out.push(lists[i] || ('#' + (i + 1)));
                }
                return out;
            }

            /* Selalu ada minimal 1 segmen agar bar atas tetap tampil walau data cuma 1 */
            function getPrepSplitTopItems(row) {
                var items = getPrepSplitItems(row);
                if (items.length) {
                    return items;
                }
                var lists = (row && Array.isArray(row.loading_lists)) ? row.loading_lists.filter(function(v) {
                    return String(v || '').trim().length;
                }) : [];
                if (lists.length === 1) {
                    return [String(lists[0])];
                }
                return ['#1'];
            }

            /* Segmen split per PDS + info hover seperti tabel monitoring */
            function getPrepSplitTopSegments(row) {
                if (row && Array.isArray(row.pds_breakdown) && row.pds_breakdown.length) {
                    return row.pds_breakdown.slice(0, 24).map(function(b) {
                        var total = parseInt((b && b.total) || 0, 10);
                        var done = parseInt((b && b.done) || 0, 10);
                        var pct = b && b.progress_pct != null ? parseFloat(b.progress_pct) : (total >
                            0 ? (done / total) * 100 : 0);
                        pct = Math.min(100, Math.max(0, Math.round(pct * 10) / 10));
                        return {
                            pds: String((b && b.pds_number) || '-'),
                            cycle: String((b && b.cycle) || (row.cycle_name || '')),
                            deliveryDate: (b && b.delivery_date) || formatLocalDateKey(row
                                .delivery_date),
                            customer: row.customer_name || '-',
                            total: total,
                            done: done,
                            pct: pct,
                            status: (b && b.status) || (total > 0 && done >= total ? 'COMPLETE' : (
                                done > 0 ? 'IN PROGRESS' : 'INCOMPLETE'))
                        };
                    });
                }
                return getPrepSplitTopItems(row).map(function(ll) {
                    return {
                        pds: String(ll),
                        cycle: String(row.cycle_name || ''),
                        deliveryDate: formatLocalDateKey(row.delivery_date),
                        customer: row.customer_name || '-',
                        total: parseInt(row.total_target || 0, 10),
                        done: parseInt(row.total_done || 0, 10),
                        pct: Math.round(getRowRawProgressPct(row) * 10) / 10,
                        status: '-'
                    };
                });
            }

            function formatPrepSplitSegTitle(seg) {
                var pctLbl = formatGanttBarPctLabel(seg.pct);
                var prog = seg.done + '/' + seg.total;
                return 'PDS ' + seg.pds + ' | ' + seg.customer + ' | Cycle ' + seg.cycle + ' | ' +
                    seg.deliveryDate + ' | ' + prog + ' (' + pctLbl + ') | ' + seg.status;
            }

            function prepSplitSegStatusClass(seg) {
                if (seg.total > 0 && seg.done >= seg.total) {
                    return 'ontime';
                }
                if (seg.done > 0) {
                    return 'delay';
                }
                return 'empty';
            }

            function prepSplitSegWeeklyColor(seg) {
                if (seg.total > 0 && seg.done >= seg.total) {
                    return 'var(--dm-complete)';
                }
                if (seg.done > 0) {
                    return 'var(--dm-yellow)';
                }
                return '#e8ecf1';
            }

            /* Pemisah bar bertumpuk (overlap) ke sub-lajur vertikal agar C1/C2 tidak bertumpuk */
            function assignPrepOverlapLanes(items) {
                var lanes = [];
                var sorted = (items || []).slice().sort(function(a, b) {
                    var al = (a.prepLayout && a.prepLayout.leftPct) || 0;
                    var bl = (b.prepLayout && b.prepLayout.leftPct) || 0;
                    return al - bl;
                });
                sorted.forEach(function(item) {
                    var layout = item.prepLayout || {};
                    var left = parseFloat(layout.leftPct || 0);
                    var w = parseFloat(layout.widthPct || 0);
                    if (layout.continuesTomorrow) {
                        w = Math.max(w, 100 - left);
                    }
                    var right = left + (isNaN(w) ? 0 : w);
                    var laneIdx = -1;
                    for (var i = 0; i < lanes.length; i++) {
                        if (left >= lanes[i] - 0.3) {
                            laneIdx = i;
                            break;
                        }
                    }
                    if (laneIdx < 0) {
                        laneIdx = lanes.length;
                        lanes.push(right);
                    } else {
                        lanes[laneIdx] = right;
                    }
                    item._lane = laneIdx;
                });
                return Math.max(lanes.length, 1);
            }

            function assignWeeklyOverlapLanes(items) {
                var lanes = [];
                items.forEach(function(item) {
                    var left = parseFloat((item.span && item.span.leftPct) || 0);
                    var w = parseFloat((item.span && item.span.widthPct) || (item.span &&
                        item.span.barWidthPct) || 0);
                    var right = left + (isNaN(w) ? 0 : w);
                    var laneIdx = -1;
                    for (var i = 0; i < lanes.length; i++) {
                        if (left >= lanes[i] - 0.3) {
                            laneIdx = i;
                            break;
                        }
                    }
                    if (laneIdx < 0) {
                        laneIdx = lanes.length;
                        lanes.push(right);
                    } else {
                        lanes[laneIdx] = right;
                    }
                    item._lane = laneIdx;
                });
                return Math.max(lanes.length, 1);
            }

            function buildSplitTopStackHtml(row, progressWidth, barClass) {
                var segs = getPrepSplitTopSegments(row);
                if (!segs.length) {
                    return null;
                }
                var html = '<div class="gantt-bar-stack is-split-top">';
                segs.forEach(function(seg, idx) {
                    var segFill = Math.min(100, Math.max(0, parseFloat(seg.pct || 0)));
                    var segClass = prepSplitSegStatusClass(seg);
                    var title = formatPrepSplitSegTitle(seg);
                    html += '<div class="prep-split-top-seg" data-split-index="' + idx +
                        '" data-split-total="' + segs.length + '" data-pds-number="' +
                        escapeAttr(seg.pds) + '" data-customer="' + escapeAttr(seg.customer) +
                        '" data-cycle="' + escapeAttr(seg.cycle) + '" data-delivery="' +
                        escapeAttr(seg.deliveryDate) + '" data-done="' + seg.done + '" data-total="' +
                        seg.total + '" data-pct="' + seg.pct + '" data-status="' + escapeAttr(seg
                            .status) + '" title="' + escapeAttr(title) + '">' +
                        '<div class="prep-split-top-fill ' + segClass + '" style="width:' + segFill +
                        '%;"></div></div>';
                });
                html += '</div>';
                return html;
            }

            function buildSplitTopBarHtmlForItem(item, viewDate, gateRows, currentFrac, overdueLatch, sortIndex) {
                if (!item || !item.row) {
                    return '';
                }
                var row = item.row;
                var prepLayout = item.prepLayout || computePrepBarSegmentOnViewDate(row, viewDate,
                    ganttHourWindow);
                if (!prepLayout) {
                    return '';
                }
                var prepStatus = resolvePrepBarStatus(row, gateRows, viewDate, currentFrac, overdueLatch ||
                    {}, prepLayout);
                var splitStack = buildSplitTopStackHtml(row, prepStatus.progressWidth, prepStatus
                    .barClass);
                if (!splitStack) {
                    return '';
                }
                var laneIdx = parseInt(item._lane || 0, 10) || 0;
                var zIndex = 5 + (parseInt(sortIndex, 10) || 0);
                var inlineStyle = buildPrepBarInlineStyle(prepLayout) + 'z-index:' + zIndex + ';top:' + (
                    2 + laneIdx * 48) + 'px;';
                var html = '<div class="gantt-bar-wrap prep-split-top" style="' + inlineStyle + '"';
                html += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                html += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                html += ' data-delivery-date-from="' + escapeAttr(formatLocalDateKey(row.delivery_date)) +
                    '"';
                html += ' data-delivery-date-to="' + escapeAttr(formatLocalDateKey(row.delivery_date)) + '"';
                html += '>' + splitStack + '</div>';
                return html;
            }

            function buildWeeklySplitTopBarHtml(row, span, pct, barColor, laneIdx) {
                var segs = getPrepSplitTopSegments(row);
                if (!segs.length) {
                    return '';
                }
                var lane = parseInt(laneIdx || 0, 10) || 0;
                var topPx = 2 + lane * 48;
                var html = '<div class="weekly-prep-bar prep-split-top" style="left:' + span.leftPct +
                    '%;width:' + span.barWidthPct + '%;top:' + topPx + 'px;background:transparent;"';
                html += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                html += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                html += ' data-delivery-date-from="' + escapeAttr(formatLocalDateKey(row.delivery_date)) +
                    '"';
                html += ' data-delivery-date-to="' + escapeAttr(formatLocalDateKey(row.delivery_date)) +
                    '"';
                html += '>';
                segs.forEach(function(seg, idx) {
                    var segFill = Math.min(100, Math.max(0, parseFloat(seg.pct || 0)));
                    var segColor = prepSplitSegWeeklyColor(seg);
                    var title = formatPrepSplitSegTitle(seg);
                    html += '<div class="weekly-split-top-seg" data-split-index="' + idx +
                        '" data-split-total="' + segs.length + '" data-pds-number="' +
                        escapeAttr(seg.pds) + '" data-customer="' + escapeAttr(seg.customer) +
                        '" data-cycle="' + escapeAttr(seg.cycle) + '" data-delivery="' +
                        escapeAttr(seg.deliveryDate) + '" data-done="' + seg.done + '" data-total="' +
                        seg.total + '" data-pct="' + seg.pct + '" data-status="' + escapeAttr(seg
                            .status) + '" title="' + escapeAttr(title) + '">' +
                        '<div class="weekly-split-top-fill" style="width:' + segFill + '%;background:' +
                        segColor + ';"></div></div>';
                });
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
                var pullingPastFinish = isRowPullingPastFinishPrep(row);
                var isOverdueNow = !beforeScheduledStart && pullingPastFinish;
                if (isOverdueNow) {
                    overdueLatch[latchKey] = true;
                } else {
                    delete overdueLatch[latchKey];
                }
                var isOverdue = isOverdueNow;
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
                    parts.push('Preparation ' + opts.prepRange);
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
                var laneIdx = parseInt(item._lane || 0, 10) || 0;
                var inlineStyle = buildPrepBarInlineStyle(prepLayout) + 'z-index:' + zIndex + ';top:' + (
                    36 + laneIdx * 48 /* SPLIT: 36 + laneIdx * 48 */) + 'px;';
                if (isContinuationBar) {
                    inlineStyle += '--cross-day-label:&quot;' + escapeAttr(crossDayLabel) + '&quot;;';
                }
                var barTitle = buildGanttTooltip(row, tooltipOpts).replace(/"/g, '&quot;');
                var html = '<div class="gantt-bar-wrap prep-orig' + prepExtraClass + '" style="' + inlineStyle +
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
                custNames.sort(compareCustomerPriority);
                if (!custNames.length) {
                    setGanttSlotCount(24);
                    $('#ganttContainer').empty();
                    updateTimelineTotal();
                    updateTvTimelineLayout();
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
                    var prepLaneCount = assignPrepOverlapLanes(prepBarItems);
                    var prepRowH = 136 + (prepLaneCount - 1) * 56; /* SPLIT: * 56 */
                    var prepTrackH = 54 + (prepLaneCount - 1) * 56; /* SPLIT: 54 + * 56 */
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
                        escapeAttr(cn) + '" style="height:' + prepRowH + 'px;">' +
                        escapeHtml(cn) + buildCustomerLoadingListSummaryHtml(allBucketsForDate) +
                        lateSummary +
                        (unconfiguredHint || '') + '</div>';
                    gridHtml +=
                        '<div class="gantt-grid-cell gantt-grid-type gantt-grid-type-split gantt-grid-left-cell gantt-grid-sticky-col gantt-grid-sticky-type" style="height:' +
                        prepRowH + 'px;">';
                    gridHtml +=
                        '<div class="gantt-type-lane is-prep">PREPARATION</div><div class="gantt-type-lane is-truck">TRUCK</div>';
                    gridHtml += '</div>';
                    gridHtml += '<div class="gantt-grid-cell gantt-grid-track-cell" style="height:' +
                        prepRowH + 'px;">';
                    gridHtml += '<div class="gantt-grid-track-split">';
                    /* PREP lane */
                    gridHtml += '<div class="gantt-grid-track-lane prep-lane"><div class="gantt-track" style="height:' +
                        prepTrackH + 'px;">';
                    gridHtml += '<div class="gantt-window-fill" style="left:' + fillLeft + '%;width:' +
                        fillWidth + '%;"></div>';
                    // SPLIT-ON (tampilkan bar split):
                    prepBarItems.forEach(function(item, sortIdx) {
                        gridHtml += buildSplitTopBarHtmlForItem(item, viewDate, gateRows, currentFrac,
                            overdueLatch, sortIdx);
                    });
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
                updateTvTimelineLayout();
            }

            function openLoadingListFromBar($bar) {                var customer = $bar.attr('data-customer-name') || '';
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

                var lateInfo = parsePrepLateDetailFromBar($bar);
                if (lateInfo) {
                    var latePds = {};
                    var lateLoadingLists = {};
                    (lateInfo.items || []).forEach(function(item) {
                        if (item.pdsNumber && item.pdsNumber !== '-') {
                            latePds[item.pdsNumber] = true;
                        }
                        if (item.loadingList && item.loadingList !== '-') {
                            lateLoadingLists[item.loadingList] = true;
                        }
                    });
                    Object.keys(latePds).slice(0, 50).forEach(function(pdsNumber) {
                        params.append('late_pds', pdsNumber);
                    });
                    Object.keys(lateLoadingLists).slice(0, 80).forEach(function(loadingList) {
                        params.append('late_ll', loadingList);
                    });
                    if (Object.keys(latePds).length || Object.keys(lateLoadingLists).length) {
                        params.set('highlight_late_pulling', '1');
                    }
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
                html += '<div><span>Finish Preparation</span><strong>' + escapeHtml(info.prepDeadlineLabel || '-') +
                    '</strong></div>';
                html += '<div><span>Progress</span><strong>' + escapeHtml(info.progressPct || 0) + '% (' +
                    escapeHtml(info.done || 0) + '/' + escapeHtml(info.target || 0) + ')</strong></div>';
                html += '</div>';
                html += '<div class="prep-late-detail__reasons">' + (reasons ||
                        '<span class="prep-late-detail__reason">Preparation telat</span>') +
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

            /* ── Popup file kanban arsip per segmen split (PDS/manifest) ── */
            function collectSplitBarPds($bar) {
                var pds = [];
                $bar.find('.prep-split-top-seg, .weekly-split-top-seg').each(function() {
                    var v = $(this).attr('data-pds-number') || '';
                    if (v && v !== '-' && pds.indexOf(v) < 0) {
                        pds.push(v);
                    }
                });
                return pds.slice(0, 24);
            }

            function buildDocumentSourceTableHtml(groups, documentType) {
                documentType = documentType || 'kanban';
                var label = documentType === 'manifest' ? 'Manifest' : (documentType === 'skid' ? 'Skid' : 'Kanban');
                var html = '<div style="text-align:left;">';
                html +=
                    '<table class="prep-late-detail__table"><thead><tr><th>PDS / Manifest</th><th>File ' + escapeHtml(label) + '</th><th style="text-align:right;">Aksi</th></tr></thead><tbody>';
                var hasFile = false;
                (groups || []).forEach(function(g) {
                    var files = (g && g.files) || [];
                    if (!files.length) {
                        html += '<tr><td>' + escapeHtml(g.pds || '-') +
                            '</td><td colspan="2"><span style="color:#6b7280;">Belum ada file arsip ' + escapeHtml(label.toLowerCase()) + ' — upload ulang via menu upload.</span></td></tr>';
                        return;
                    }
                    files.forEach(function(f, idx) {
                        hasFile = true;
                        html += '<tr>' + (idx === 0 ?
                            '<td rowspan="' + files.length + '"><strong>' + escapeHtml(g
                                .pds || '-') + '</strong></td>' :
                            '') + '<td>' + escapeHtml(f.file || '-') + '</td>' +
                            '<td style="text-align:right;white-space:nowrap;">' +
                            '<button type="button" class="act-btn primary btn-kanban-print" data-document-type="' + escapeAttr(documentType) + '" data-preview="' +
                            escapeAttr(f.preview || '') +
                            '" style="height:28px;padding:0 10px;font-size:11px;margin-right:4px;"><i class="fas fa-print mr-1"></i>Print</button>' +
                            '<a class="act-btn success" href="' + escapeAttr(f.download || '') +
                            '" style="height:28px;padding:0 10px;font-size:11px;display:inline-flex;align-items:center;text-decoration:none;"><i class="fas fa-download mr-1"></i>Download</a>' +
                            '</td></tr>';
                    });
                });
                html += '</tbody></table></div>';
                return {
                    html: html,
                    hasFile: hasFile
                };
            }

            function buildKanbanSourceTableHtml(groups) {
                return buildDocumentSourceTableHtml(groups, 'kanban');
            }

            function openKanbanSourcePopup($bar, overridePdsList) {
                var pdsList = (overridePdsList && overridePdsList.length) ? overridePdsList : collectSplitBarPds($bar);
                if (!pdsList.length) {
                    Swal.fire({
                        title: 'File Dokumen',
                        html: '<div style="min-height:60px;"></div>',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6b7280'
                    });
                    return;
                }
                Swal.fire({
                    title: 'File Dokumen',
                    html: '<div style="color:#6b7280;font-size:12px;">Memuat daftar file...</div>',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var pending = 3;
                var kanbanGroups = [];
                var manifestGroups = [];
                var skidGroups = [];
                var failed = 0;
                function finish() {
                    pending--;
                    if (pending > 0) {
                        return;
                    }
                    if (failed === 3) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal memuat',
                            text: 'File dokumen tidak bisa dimuat. Coba lagi.',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#6b7280'
                        });
                        return;
                    }
                    var html = '';
                    var kb = buildDocumentSourceTableHtml(kanbanGroups, 'kanban');
                    var mf = buildDocumentSourceTableHtml(manifestGroups, 'manifest');
                    var sk = buildDocumentSourceTableHtml(skidGroups, 'skid');
                    if (kanbanGroups.length) {
                        html += '<div style="margin-bottom:12px;"><strong style="color:#0d365c;">Kanban</strong>' + kb.html + '</div>';
                    }
                    if (manifestGroups.length) {
                        html += '<div style="margin-bottom:12px;"><strong style="color:#0d365c;">Manifest</strong>' + mf.html + '</div>';
                    }
                    if (skidGroups.length) {
                        html += '<div><strong style="color:#0d365c;">Skid</strong>' + sk.html + '</div>';
                    }
                    if (!html) {
                        html = '<div style="color:#6b7280;font-size:12px;">Belum ada file arsip.</div>';
                    }
                    if (failed > 0 && failed < 3) {
                        html += '<div style="color:#b91c1c;font-size:11px;margin-top:8px;">Sebagian data gagal dimuat.</div>';
                    }
                    Swal.fire({
                        title: 'File Dokumen',
                        html: html,
                        width: 760,
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6b7280'
                    });
                }

                $.get(kanbanSourceFilesUrl, {
                    pds: pdsList.join(',')
                }).done(function(res) {
                    kanbanGroups = (res && res.data) || [];
                }).fail(function() {
                    failed++;
                }).always(finish);

                $.get(manifestSourceFilesUrl, {
                    pds: pdsList.join(',')
                }).done(function(res) {
                    manifestGroups = (res && res.data) || [];
                }).fail(function() {
                    failed++;
                }).always(finish);

                $.get(skidSourceFilesUrl, {
                    pds: pdsList.join(',')
                }).done(function(res) {
                    skidGroups = (res && res.data) || [];
                }).fail(function() {
                    failed++;
                }).always(finish);
            }

            /* Print silent QZ: semua halaman, kertas 150mm x 80mm, tanpa tab preview */
            var kanbanPdfJsPromise = null;

            function ensureKanbanPdfJs() {
                if (window.pdfjsLib) {
                    return $.Deferred().resolve().promise();
                }
                if (kanbanPdfJsPromise) {
                    return kanbanPdfJsPromise;
                }
                kanbanPdfJsPromise = $.Deferred(function(dfd) {
                    var s = document.createElement('script');
                    s.src =
                        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js';
                    s.onload = function() {
                        try {
                            window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
                        } catch (err) {}
                        dfd.resolve();
                    };
                    s.onerror = function() {
                        dfd.reject();
                    };
                    document.head.appendChild(s);
                }).promise();
                return kanbanPdfJsPromise;
            }

            /* SILENT PRINT via QZ Tray: kertas 150x80mm, tanpa dialog browser */
            var kanbanQzScriptPromise = null;

            function ensureQzTrayScript() {
                if (window.qz) {
                    return $.Deferred().resolve().promise();
                }
                if (kanbanQzScriptPromise) {
                    return kanbanQzScriptPromise;
                }
                kanbanQzScriptPromise = $.Deferred(function(dfd) {
                    var s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js';
                    s.onload = function() {
                        dfd.resolve();
                    };
                    s.onerror = function() {
                        dfd.reject();
                    };
                    document.head.appendChild(s);
                }).promise();
                return kanbanQzScriptPromise;
            }

            /* QZ message signing (Opsi 1): request di-sign server, popup trust hilang permanen */
            var kanbanQzSecurityArmed = false;

            function armKanbanQzSigning() {
                if (kanbanQzSecurityArmed || !window.qz) {
                    return;
                }
                kanbanQzSecurityArmed = true;
                try {
                    qz.security.setSignatureAlgorithm('SHA512');
                } catch (err) {}
                qz.security.setCertificatePromise(function(resolve, reject) {
                    $.get(qzCertUrl).done(function(cert) {
                        resolve(String(cert || '').trim());
                    }).fail(reject);
                });
                qz.security.setSignaturePromise(function(toSign) {
                    return function(resolve, reject) {
                        $.post(qzSignUrl, {
                            _token: csrf,
                            request: toSign
                        }).done(function(sig) {
                            resolve($.trim(sig));
                        }).fail(reject);
                    };
                });
            }

            function ensureQzConnected() {
                var dfd = $.Deferred();
                ensureQzTrayScript().done(function() {
                    armKanbanQzSigning();
                    try {
                        if (window.qz && qz.websocket.isActive()) {
                            dfd.resolve();
                            return;
                        }
                        qz.websocket.connect().then(function() {
                            dfd.resolve();
                        }).catch(function(err) {
                            dfd.reject(err);
                        });
                    } catch (err) {
                        dfd.reject(err);
                    }
                }).fail(function(err) {
                    dfd.reject(err);
                });
                return dfd.promise();
            }

            function resolveQzPrinter(documentType) {
                documentType = documentType || 'kanban';
                var dfd = $.Deferred();
                var name = (window.DOCUMENT_PRINTER_CONFIG && window.DOCUMENT_PRINTER_CONFIG[documentType]) || '';
                if (name) {
                    dfd.resolve(name);
                    return dfd.promise();
                }
                dfd.reject(new Error('Printer belum dikonfigurasi untuk ' + documentType + '.'));
                return dfd.promise();
            }

            /* Ganti printer silent: localStorage.setItem('kanban_qz_printer','NAMA_PRINTER') */
            window.setKanbanPrinter = function(name) {
                return window.setPrinterForDocumentType('kanban', name);
            };

            function printKanbanIframeFallback(imgList, url, $btn, origHtml) {
                var $frame = $('#kanbanDirectPrintFrame');
                if (!$frame.length) {
                    $frame = $(
                        '<iframe id="kanbanDirectPrintFrame" title="print" style="position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;" aria-hidden="true"></iframe>'
                    );
                    $('body').append($frame);
                }
                var sheets = '';
                (imgList && imgList.length ? imgList : []).forEach(function(imgData) {
                    sheets += '<div class="sheet"><img src="' + imgData + '"></div>';
                });
                /* Jika render gambar gagal total, print PDF apa adanya */
                if (!sheets) {
                    $frame.off('load').on('load', function() {
                        try {
                            $frame[0].contentWindow.focus();
                            $frame[0].contentWindow.print();
                        } catch (err) {}
                        $btn.prop('disabled', false).html(origHtml);
                    });
                    var sep = String(url).indexOf('?') >= 0 ? '&' : '?';
                    $frame.removeAttr('srcdoc').attr('src', url + sep + '_print=' + Date.now());
                    return;
                }
                var doc = $frame[0].contentWindow.document;
                doc.open();
                doc.write(
                    '<!doctype html><html><head><title>Kanban</title>' +
                    '<style>@page{size:150mm 80mm;margin:2mm;}' +
                    'html,body{margin:0;padding:0;background:#fff;}' +
                    '.sheet{width:146mm;height:76mm;page-break-after:always;overflow:hidden;}' +
                    '.sheet:last-child{page-break-after:auto;}' +
                    '.sheet img{display:block;width:100%;height:100%;object-fit:contain;}' +
                    '</style></head><body>' +
                    sheets +
                    '<script>window.onload=function(){setTimeout(function(){window.focus();window.print();},150);}<\/script>' +
                    '</body></html>'
                );
                doc.close();
                $btn.prop('disabled', false).html(origHtml);
            }

            function printKanbanImagesSilent(imgList, url, $btn, origHtml, documentType) {
                documentType = documentType || 'kanban';
                var paperCfg = (window.DOCUMENT_PAPER_CONFIG && window.DOCUMENT_PAPER_CONFIG[documentType]) || window.DOCUMENT_PAPER_CONFIG.kanban;
                if (!imgList || !imgList.length) {
                    $btn.prop('disabled', false).html(origHtml);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal mencetak',
                        text: 'Dokumen tidak dapat dirender untuk dicetak.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6b7280'
                    });
                    return;
                }
                ensureQzConnected().done(function() {
                    resolveQzPrinter(documentType).done(function(printerName) {
                        if (!printerName) {
                            $btn.prop('disabled', false).html(origHtml);
                            Swal.fire({
                                icon: 'error',
                                title: 'Printer tidak ditemukan',
                                text: 'Printer untuk ' + documentType + ' belum dikonfigurasi atau tidak terdeteksi oleh QZ Tray.',
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#6b7280'
                            });
                            return;
                        }
                        try {
                            var config = qz.configs.create(printerName, {
                                size: {
                                    width: paperCfg.width,
                                    height: paperCfg.height
                                },
                                units: paperCfg.units,
                                margins: paperCfg.margins,
                                scaleContent: paperCfg.scale_content,
                                copies: paperCfg.copies
                            });
                            var data = (imgList || []).map(function(imgData) {
                                var b64 = String(imgData || '').split(',')[1] || '';
                                return {
                                    type: 'pixel',
                                    format: 'image',
                                    flavor: 'base64',
                                    data: b64,
                                    options: {
                                        pageWidth: paperCfg.width,
                                        pageHeight: paperCfg.height
                                    }
                                };
                            }).filter(function(d) {
                                return !!d.data;
                            });
                            if (!data.length) {
                                $btn.prop('disabled', false).html(origHtml);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal mencetak',
                                    text: 'Dokumen tidak dapat diproses untuk dicetak.',
                                    confirmButtonText: 'Tutup',
                                    confirmButtonColor: '#6b7280'
                                });
                                return;
                            }
                            qz.print(config, data).then(function() {
                                $btn.prop('disabled', false).html(origHtml);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil dicetak',
                                    text: documentType + ' dikirim ke ' + printerName + '.',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            }).catch(function(err) {
                                $btn.prop('disabled', false).html(origHtml);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal mencetak',
                                    text: (err && err.message) ? err.message : 'Proses cetak QZ Tray gagal.',
                                    confirmButtonText: 'Tutup',
                                    confirmButtonColor: '#6b7280'
                                });
                            });
                        } catch (err) {
                            $btn.prop('disabled', false).html(origHtml);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal mencetak',
                                text: (err && err.message) ? err.message : 'Proses cetak QZ Tray gagal.',
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#6b7280'
                            });
                        }
                    }).fail(function(err) {
                        $btn.prop('disabled', false).html(origHtml);
                        Swal.fire({
                            icon: 'error',
                            title: 'Printer tidak ditemukan',
                            text: (err && err.message) ? err.message : 'Printer untuk ' + documentType + ' belum dikonfigurasi.',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#6b7280'
                        });
                    });
                }).fail(function(err) {
                    $btn.prop('disabled', false).html(origHtml);
                    Swal.fire({
                        icon: 'error',
                        title: 'QZ Tray tidak terhubung',
                        text: (err && err.message) ? err.message : 'Pastikan QZ Tray berjalan dan terhubung.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6b7280'
                    });
                });
            }

            function printKanbanPdfFirstPageOnly(url, $btn, origHtml, documentType) {
                documentType = documentType || 'kanban';
                var cleanUrl = String(url || '').split('#')[0];
                fetch(cleanUrl, {
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        if (!r.ok) {
                            throw new Error('fetch failed');
                        }
                        return r.arrayBuffer();
                    })
                    .then(function(buf) {
                        return window.pdfjsLib.getDocument({
                            data: buf
                        }).promise;
                    })
                    .then(function(pdf) {
                        var total = pdf.numPages || 1;
                        var chain = $.Deferred().resolve([]).promise();
                        for (var p = 1; p <= total; p++) {
                            (function(pageNo) {
                                chain = chain.then(function(imgs) {
                                    return pdf.getPage(pageNo).then(function(page) {
                                        var viewport = page.getViewport({
                                            scale: 2
                                        });
                                        var canvas = document.createElement('canvas');
                                        canvas.width = Math.floor(viewport.width);
                                        canvas.height = Math.floor(viewport.height);
                                        return page.render({
                                            canvasContext: canvas.getContext('2d'),
                                            viewport: viewport
                                        }).promise.then(function() {
                                            imgs.push(canvas.toDataURL('image/png'));
                                            return imgs;
                                        });
                                    });
                                });
                            })(p);
                        }
                        return chain;
                    })
                    .then(function(imgList) {
                        printKanbanImagesSilent(imgList, url, $btn, origHtml, documentType);
                    })
                    .catch(function(err) {
                        $btn.prop('disabled', false).html(origHtml);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal mencetak',
                            text: (err && err.message) ? err.message : 'Dokumen gagal diambil atau diproses.',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#6b7280'
                        });
                    });
            }

            function arrayBufferToBase64(buffer) {
                var binary = '';
                var bytes = new Uint8Array(buffer);
                var len = bytes.byteLength;
                var chunkSize = 0x8000;
                for (var i = 0; i < len; i += chunkSize) {
                    var chunk = bytes.subarray(i, Math.min(i + chunkSize, len));
                    binary += String.fromCharCode.apply(null, chunk);
                }
                return window.btoa(binary);
            }

            function printPdfDirectSilent(url, $btn, origHtml, documentType) {
                documentType = documentType || 'manifest';
                var paperCfg = (window.DOCUMENT_PAPER_CONFIG && window.DOCUMENT_PAPER_CONFIG[documentType]) || window.DOCUMENT_PAPER_CONFIG.manifest;
                var cleanUrl = String(url || '').split('#')[0];

                ensureQzConnected().done(function() {
                    resolveQzPrinter(documentType).done(function(printerName) {
                        if (!printerName) {
                            $btn.prop('disabled', false).html(origHtml);
                            Swal.fire({
                                icon: 'error',
                                title: 'Printer tidak ditemukan',
                                text: 'Printer untuk ' + documentType + ' belum dikonfigurasi atau tidak terdeteksi oleh QZ Tray.',
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#6b7280'
                            });
                            return;
                        }

                        fetch(cleanUrl, {
                                credentials: 'same-origin'
                            })
                            .then(function(r) {
                                if (!r.ok) {
                                    throw new Error('Gagal mengambil file PDF (HTTP ' + r.status + ')');
                                }
                                return r.arrayBuffer();
                            })
                            .then(function(buf) {
                                var b64 = arrayBufferToBase64(buf);
                                if (!b64) {
                                    throw new Error('File PDF kosong atau gagal diproses.');
                                }

                                var configOptions = {
                                    units: paperCfg.units || 'mm',
                                    margins: paperCfg.margins !== undefined ? paperCfg.margins : 0,
                                    scaleContent: paperCfg.scale_content !== undefined ? paperCfg.scale_content : true,
                                    copies: paperCfg.copies || 1,
                                    rasterize: false
                                };
                                if (paperCfg.width && paperCfg.height) {
                                    configOptions.size = {
                                        width: paperCfg.width,
                                        height: paperCfg.height
                                    };
                                }

                                var config = qz.configs.create(printerName, configOptions);
                                var data = [{
                                    type: 'pixel',
                                    format: 'pdf',
                                    flavor: 'base64',
                                    data: b64
                                }];

                                return qz.print(config, data);
                            })
                            .then(function() {
                                $btn.prop('disabled', false).html(origHtml);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil dicetak',
                                    text: documentType + ' dikirim ke ' + printerName + '.',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            })
                            .catch(function(err) {
                                $btn.prop('disabled', false).html(origHtml);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal mencetak',
                                    text: (err && err.message) ? err.message : 'Proses cetak QZ Tray gagal.',
                                    confirmButtonText: 'Tutup',
                                    confirmButtonColor: '#6b7280'
                                });
                            });
                    }).fail(function(err) {
                        $btn.prop('disabled', false).html(origHtml);
                        Swal.fire({
                            icon: 'error',
                            title: 'Printer tidak ditemukan',
                            text: (err && err.message) ? err.message : 'Printer untuk ' + documentType + ' belum dikonfigurasi.',
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#6b7280'
                        });
                    });
                }).fail(function(err) {
                    $btn.prop('disabled', false).html(origHtml);
                    Swal.fire({
                        icon: 'error',
                        title: 'QZ Tray tidak terhubung',
                        text: (err && err.message) ? err.message : 'Pastikan QZ Tray berjalan dan terhubung.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6b7280'
                    });
                });
            }

            $(document).on('click', '.btn-kanban-print', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                if ($btn.prop('disabled')) {
                    return;
                }
                var url = $btn.attr('data-preview') || '';
                if (!url) {
                    return;
                }
                var documentType = $btn.attr('data-document-type') || 'kanban';
                var origHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i>Print...');
                if (documentType === 'manifest' || documentType === 'skid') {
                    printPdfDirectSilent(url, $btn, origHtml, documentType);
                    return;
                }

                ensureKanbanPdfJs().done(function() {
                    printKanbanPdfFirstPageOnly(url, $btn, origHtml, documentType);
                }).fail(function() {
                    $btn.prop('disabled', false).html(origHtml);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal memuat PDF.js',
                        text: 'Tidak dapat memuat pustaka PDF untuk mencetak.',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#6b7280'
                    });
                });
            });

            $('#ganttContainer').on('click', '.gantt-bar-wrap.prep-split-top', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $seg = $(e.target).closest('.prep-split-top-seg');
                if ($seg.length) {
                    var single = $seg.attr('data-pds-number') || '';
                    if (single && single !== '-') {
                        openKanbanSourcePopup($(this), [single]);
                        return;
                    }
                }
                openKanbanSourcePopup($(this));
            });

            $('#ganttContainer').on('click', '.gantt-bar-wrap:not(.prep-split-top)', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $bar = $(this);
                if ($bar.hasClass('is-prep-late')) {
                    openPrepLateDetailFromBar($bar);
                    return;
                }
                openLoadingListFromBar($bar);
            });

            $('#weeklyGanttContainer').on('click', '.weekly-prep-bar.prep-split-top', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $seg = $(e.target).closest('.weekly-split-top-seg');
                if ($seg.length) {
                    var single = $seg.attr('data-pds-number') || '';
                    if (single && single !== '-') {
                        openKanbanSourcePopup($(this), [single]);
                        return;
                    }
                }
                openKanbanSourcePopup($(this));
            });

            $('#weeklyGanttContainer').on('click', '.weekly-prep-bar:not(.prep-split-top)', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $bar = $(this);
                if ($bar.hasClass('is-prep-late')) {
                    openPrepLateDetailFromBar($bar);
                    return;
                }
                openLoadingListFromBar($bar);
            });

            /* ── Hover tooltip info PDS per segmen split ── */
            function buildPrepPdsTipHtml($seg) {
                var pds = $seg.attr('data-pds-number') || '-';
                var customer = $seg.attr('data-customer') || '-';
                var cycle = $seg.attr('data-cycle') || '-';
                var delivery = $seg.attr('data-delivery') || '-';
                var done = $seg.attr('data-done') || '0';
                var total = $seg.attr('data-total') || '0';
                var pct = parseFloat($seg.attr('data-pct') || 0);
                var status = $seg.attr('data-status') || '-';
                var pctLbl = formatGanttBarPctLabel(pct);
                var statusCls = status === 'COMPLETE' ? 'is-complete' : (status ===
                    'IN PROGRESS' ? 'is-progress' : 'is-incomplete');
                var barColor = status === 'COMPLETE' ? '#22c55e' : (status === 'IN PROGRESS' ?
                    '#eab308' : '#e5e7eb');
                return '<div class="tip-pds">' + escapeHtml(pds) + '</div>' +
                    '<div class="tip-row"><span>Customer</span><strong>' + escapeHtml(customer) +
                    '</strong></div>' +
                    '<div class="tip-row"><span>Cycle</span><strong>' + escapeHtml(cycle) +
                    '</strong></div>' +
                    '<div class="tip-row"><span>Delivery</span><strong>' + escapeHtml(delivery) +
                    '</strong></div>' +
                    '<div class="tip-bar"><div style="width:' + Math.min(100, Math.max(0, pct)) +
                    '%;background:' + barColor + ';"></div></div>' +
                    '<div class="tip-row"><span>' + escapeHtml(done + ' / ' + total) + ' · ' +
                    escapeHtml(pctLbl) + '</span><span class="tip-status ' + statusCls + '">' +
                    escapeHtml(status) + '</span></div>';
            }

            function positionPrepPdsTip($tip, e) {
                var x = (e.clientX != null ? e.clientX : 0) + 14;
                var y = (e.clientY != null ? e.clientY : 0) + 14;
                var w = $tip.outerWidth() || 240;
                var h = $tip.outerHeight() || 120;
                if (x + w > window.innerWidth - 8) {
                    x = (e.clientX != null ? e.clientX : 0) - w - 12;
                }
                if (y + h > window.innerHeight - 8) {
                    y = (e.clientY != null ? e.clientY : 0) - h - 12;
                }
                $tip.css({
                    left: Math.max(8, x) + 'px',
                    top: Math.max(8, y) + 'px'
                });
            }

            $(document).on('mouseenter', '.prep-split-top-seg, .weekly-split-top-seg', function(e) {
                $('.prep-pds-tip').remove();
                var $tip = $('<div class="prep-pds-tip"></div>').html(buildPrepPdsTipHtml($(this)));
                $('body').append($tip);
                positionPrepPdsTip($tip, e);
            }).on('mousemove', '.prep-split-top-seg, .weekly-split-top-seg', function(e) {
                var $tip = $('.prep-pds-tip').first();
                if ($tip.length) {
                    positionPrepPdsTip($tip, e);
                }
            }).on('mouseleave', '.prep-split-top-seg, .weekly-split-top-seg', function() {
                $('.prep-pds-tip').remove();
            });

            function resolveWeeklyDayColWidth(nSlots) {
                var custColWidth = 200;
                var typeColWidth = 120;
                var minDayWidth = 240;
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
                var compactTvMode = $('.delivery-dash').hasClass('is-tv-mode');
                var custColWidth = compactTvMode ? 150 : 200;
                var typeColWidth = compactTvMode ? 62 : 120;
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
                    var rowHeight = compactTvMode ? 64 : 136;

                    // ── Baris PREP ──
                    html += '<tr>';
                    // Kolom Customer dengan rowspan=2
                    html += '<td rowspan="2" class="weekly-col-customer" style="' +
                        'vertical-align:middle;text-align:left;' +
                        'font-size:12px;font-weight:500;padding:8px 10px;' +
                        'word-break:break-word;line-height:1.3;' +
                        '">' + escapeHtml(cust) +
                        buildCustomerLoadingListSummaryHtml(filterRowsInWeeklySlots(rows, slots)) +
                        weeklyLateSummary +
                        buildUnconfiguredTimelineHintHtml(filterRowsInWeeklySlots(rows, slots)) +
                        '</td>';
                    // Kolom TYPE – PREP (tinggi mengikuti jumlah sub-lajur anti-tumpuk)
                    var weeklyPrepItems = collectWeeklyPrepBarsForCustomer(cust, rowsForBars, slots);
                    var weeklyLaneCount = assignWeeklyOverlapLanes(weeklyPrepItems);
                    var prepLaneH = (rowHeight / 2) + (weeklyLaneCount - 1) * 56; /* SPLIT: * 56 */
                    html += '<td class="weekly-col-type" style="' +
                        'height:' + prepLaneH + 'px;' +
                        'text-align:center;vertical-align:middle;' +
                        'font-size:10px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:#5c7f2b;' +
                        '">PREPARATION</td>';
                    // Lane PREP mingguan: satu track lintas semua hari (bar boleh melewati garis antarhari)
                    html += '<td colspan="' + nSlots + '" class="weekly-prep-lane-cell">';
                    html += '<div class="weekly-prep-track" style="height:' + prepLaneH + 'px;">';
                    slots.forEach(function(slotDateStr, dayIdx) {
                        var isTodayCol = slotDateStr === todayStr;
                        html += '<div class="weekly-day-column' + (isTodayCol ? ' is-today' : '') +
                            '" style="left:' + ((dayIdx / nSlots) * 100) + '%;width:' + slotWPct +
                            '%;"></div>';
                    });
                    weeklyPrepItems.forEach(function(item) {
                        var row = item.row;
                        var span = item.span;
                        var laneIdx = parseInt(item._lane || 0, 10) || 0;
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
                        // SPLIT-ON (tampilkan bar split):
                        html += buildWeeklySplitTopBarHtml(row, {
                            leftPct: span.leftPct,
                            barWidthPct: barWidthPct
                        }, pct, barColor, laneIdx);
                        html += '<div class="weekly-prep-bar prep-orig' + crossClass + incomingClass +
                            (lateInfo.isLate ? ' is-prep-late' : '') + ' ' + offsetClass +
                            '" title="' + tipText + '" style="left:' +
                            span.leftPct + '%;width:' + barWidthPct + '%;top:' + (36 + laneIdx *
                                48 /* SPLIT: 36 + laneIdx * 48 */) + 'px;' +
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
                updateTvTimelineLayout();
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
            $('#btnSyncLoadingLists').on('click', function() {
                var date = $('#syncLoadingListDate').val() || '';
                if (!date) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal belum dipilih',
                        text: 'Pilih tanggal sync loading list.',
                        confirmButtonColor: '#0097d8'
                    });
                    return;
                }
                var dateTo = addDaysToDateStr(date, 2);

                var $button = $(this);
                var originalHtml = $button.html();
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sync...');

                Swal.fire({
                    html: '<div class="delivery-sync-loader" role="status" aria-live="polite">' +
                        '<div class="delivery-sync-scene" aria-hidden="true">' +
                        '<svg viewBox="0 0 320 128" xmlns="http://www.w3.org/2000/svg">' +
                        '<g class="dst-bush"><rect x="8" y="93" width="4" height="9" rx="1.5" fill="#a9b79f"/><circle cx="10" cy="91" r="9" fill="#cfe0c9"/><circle cx="4" cy="95" r="6" fill="#cfe0c9"/><circle cx="16" cy="95" r="6" fill="#cfe0c9"/></g>' +
                        '<g class="dst-bush b"><rect x="9" y="95" width="3" height="7" rx="1.5" fill="#a9b79f"/><circle cx="10" cy="94" r="6" fill="#d8e6d2"/></g>' +
                        '<line x1="0" y1="104" x2="320" y2="104" stroke="#c3d0dc" stroke-width="4"/>' +
                        '<line class="dst-dash" x1="0" y1="104" x2="320" y2="104" stroke="#ffffff" stroke-width="4" stroke-dasharray="16 18" stroke-linecap="round"/>' +
                        '<ellipse cx="134" cy="106" rx="80" ry="4" fill="rgba(13,54,92,.12)"/>' +
                        '<circle class="dst-puff" cx="52" cy="88" r="4" fill="#cbd7e2"/>' +
                        '<circle class="dst-puff b" cx="52" cy="88" r="3" fill="#d7e1ea"/>' +
                        '<g class="dst-wheel"><circle cx="98" cy="92" r="12" fill="#2b3542"/><line x1="98" y1="81" x2="98" y2="103" stroke="#c2ccd6" stroke-width="2"/><line x1="87" y1="92" x2="109" y2="92" stroke="#c2ccd6" stroke-width="2"/><circle cx="98" cy="92" r="6" fill="#e7edf3"/><circle cx="98" cy="92" r="2" fill="#8b98a6"/></g>' +
                        '<g class="dst-wheel"><circle cx="188" cy="92" r="12" fill="#2b3542"/><line x1="188" y1="81" x2="188" y2="103" stroke="#c2ccd6" stroke-width="2"/><line x1="177" y1="92" x2="199" y2="92" stroke="#c2ccd6" stroke-width="2"/><circle cx="188" cy="92" r="6" fill="#e7edf3"/><circle cx="188" cy="92" r="2" fill="#8b98a6"/></g>' +
                        '<g class="dst-body">' +
                        '<rect x="58" y="86" width="154" height="7" rx="2.5" fill="#0d365c"/>' +
                        '<rect x="58" y="40" width="108" height="47" rx="7" fill="#ffffff" stroke="#0d365c" stroke-width="3"/>' +
                        '<line x1="104" y1="43" x2="104" y2="84" stroke="#cdd9e6" stroke-width="2"/>' +
                        '<rect x="118" y="50" width="28" height="24" rx="2.5" fill="#eaf4fb" stroke="#0097d8" stroke-width="2"/>' +
                        '<line x1="118" y1="62" x2="146" y2="62" stroke="#0097d8" stroke-width="2"/>' +
                        '<line x1="132" y1="50" x2="132" y2="74" stroke="#0097d8" stroke-width="2"/>' +
                        '<path d="M166 50 H188 L206 74 V84 H166 Z" fill="#0097d8" stroke="#0d365c" stroke-width="3" stroke-linejoin="round"/>' +
                        '<path d="M170 54 H186 L198 70 H170 Z" fill="#dff1fb"/>' +
                        '<rect x="201" y="76" width="6" height="6" rx="1.5" fill="#ffd873"/>' +
                        '</g>' +
                        '</svg>' +
                        '</div>' +
                        '<div class="delivery-sync-title">Sinkronisasi loading list</div>' +
                        '<div class="delivery-sync-copy">Mengantar jadwal ke dashboard</div>' +
                        '<div class="delivery-sync-date"><i class="far fa-calendar-alt"></i>' +
                        escapeHtml(date) +
                        ' s.d. ' + escapeHtml(dateTo) +
                        '</div>' +
                        '<div class="delivery-sync-progress" aria-hidden="true"></div>' +
                        '</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    backdrop: 'rgba(7, 25, 43, .72)',
                    customClass: {
                        popup: 'delivery-sync-popup',
                        htmlContainer: 'delivery-sync-html'
                    }
                });

                $.ajax({
                    url: syncLoadingListsUrl,
                    method: 'POST',
                    data: {
                        _token: csrf,
                        date: date
                    }
                }).done(function(response) {
                    loadStackedChart();
                    if (response.last_sync) {
                        var lastSyncText = 'Terakhir sync ' + response.last_sync.synced_at +
                            ' · tanggal ' + response.last_sync.date_label +
                            ' · ' + response.last_sync.loading_list_count + ' LL';
                        if (response.last_sync.skipped > 0) {
                            lastSyncText += ' · ' + response.last_sync.skipped + ' catatan';
                        }
                        $('#lastSyncStatusText').text(lastSyncText);
                        $('#lastSyncStatus').removeClass('is-empty');
                    }
                    var hasNotes = response.status === 'warning';
                    var isEmpty = response.status === 'empty';
                    var skippedItems = Array.isArray(response.skipped_items) ? response
                        .skipped_items : [];
                    var skippedHtml = skippedItems.map(function(item) {
                        var parts = [];
                        if (item.part_number_int) {
                            parts.push('<strong>Internal:</strong> ' + escapeHtml(String(
                                item.part_number_int)));
                        }
                        if (item.part_number_cust) {
                            parts.push('<strong>Customer:</strong> ' + escapeHtml(String(
                                item.part_number_cust)));
                        }

                        return '<div class="delivery-sync-note-item">' +
                            '<div class="delivery-sync-note-head">' +
                            '<strong>' + escapeHtml(String(item.loading_list || '-')) +
                            '</strong>' +
                            '<span class="delivery-sync-note-reason">' + escapeHtml(String(
                                item.reason || '-')) + '</span>' +
                            '</div>' +
                            (parts.length ? '<div class="delivery-sync-note-parts">' + parts
                                .join(' &middot; ') + '</div>' : '') +
                            '</div>';
                    }).join('');
                    var resultHtml = hasNotes ?
                        '<p class="delivery-sync-result-copy">' + escapeHtml(response.message) +
                        '</p>' +
                        '<details class="delivery-sync-notes">' +
                        '<summary><span>Detail data yang dilewati</span>' +
                        '<span class="delivery-sync-notes-count">' + escapeHtml(String(response
                            .skipped || 0)) + '</span></summary>' +
                        '<div class="delivery-sync-notes-list">' + skippedHtml + '</div>' +
                        '</details>' :
                        null;
                    Swal.fire({
                        icon: hasNotes ? 'warning' : (isEmpty ? 'info' : 'success'),
                        title: hasNotes ? 'Berhasil dengan catatan' : (isEmpty ?
                            'Data tidak ditemukan' : 'Sync selesai'),
                        text: hasNotes ? undefined : response.message,
                        html: resultHtml,
                        confirmButtonColor: hasNotes ? '#f59e0b' : (isEmpty ? '#64748b' :
                            '#0097d8'),
                        customClass: hasNotes ? {
                            popup: 'delivery-sync-result-popup'
                        } : {}
                    });
                }).fail(function(xhr) {
                    var response = xhr.responseJSON || {};
                    var detail = response.detail ? '\n\n' + response.detail : '';
                    Swal.fire({
                        icon: 'error',
                        title: 'Sync gagal',
                        text: (response.message || 'Loading list gagal disinkronkan.') +
                            detail,
                        confirmButtonColor: '#0097d8'
                    });
                }).always(function() {
                    $button.prop('disabled', false).html(originalHtml);
                });
            });
            $('#filterDateFrom, #filterDateTo, #filterCustomer').on('change', loadStackedChart);

            /* Samakan urutan dropdown customer dengan urutan baris chart (Semua tetap paling atas). */
            (function sortCustomerDropdown() {
                var $sel = $('#filterCustomer');
                if (!$sel.length) {
                    return;
                }
                var $all = $sel.find('option[value=""]');
                var rest = $sel.find('option[value!=""]').toArray();
                rest.sort(function(x, y) {
                    return compareCustomerPriority($(x).text(), $(y).text());
                });
                $sel.empty().append($all).append(rest);
            })();

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

            // Auto-refresh data dashboard tiap 5 menit (lewati saat tab tidak aktif biar hemat).
            setInterval(function() {
                if (!document.hidden) loadStackedChart();
            }, 300000);

            waNotifyTimer = setInterval(function() {
                checkAndSendUnfinishedWaNotification();
                if (dashboardViewMode === 'daily') {
                    renderGantt();
                }
            }, 60000);

            /* ============================================================
             * DLA: Delay popup + alarm (ISOLATED ADD-ON, tidak ubah logic lain)
             * - Hanya baca chartMergedByCustTime (read-only).
             * - Trigger: progress 0<x<100 (IN PROGRESS) + sudah lewat Finish Prep.
             * - Popup via Swal (sudah ada), suara via Web Audio (tanpa file).
             * - Dedupe per bucket + cooldown agar tidak spam.
             var dlaCooldownMs = 30 * 60 * 1000; _____ 30 menit
             * ============================================================ */
            var dlaShownKey = 'dla_delay_shown_v1';
            var dlaConfirmedKey = 'dla_delay_confirmed_v1';
            var dlaSoundKey = 'dla_sound_enabled_v1';
            var dlaCooldownMs = 60 * 60 * 1000;
            var dlaAudioCtx = null;
            var dlaAlarmTimer = null;
            var dlaSoundEnabled = true;
            try {
                dlaSoundEnabled = localStorage.getItem(dlaSoundKey) !== '0';
            } catch (err) {}

            function dlaGetShownMap() {
                try {
                    return JSON.parse(localStorage.getItem(dlaShownKey) || '{}');
                } catch (err) {
                    return {};
                }
            }

            function dlaSetShownMap(map) {
                try {
                    localStorage.setItem(dlaShownKey, JSON.stringify(map));
                } catch (err) {}
            }

            function dlaGetConfirmedMap() {
                try {
                    return JSON.parse(localStorage.getItem(dlaConfirmedKey) || '{}');
                } catch (err) {
                    return {};
                }
            }

            function dlaSetConfirmedMap(map) {
                try {
                    localStorage.setItem(dlaConfirmedKey, JSON.stringify(map));
                } catch (err) {}
            }

            function dlaGetProgress(row) {
                try {
                    if (typeof getRowRawProgressPct === 'function') {
                        return parseFloat(getRowRawProgressPct(row)) || 0;
                    }
                } catch (err) {}
                var target = parseInt((row && row.total_target) || 0, 10);
                var done = parseInt((row && row.total_done) || 0, 10);
                if (target > 0) {
                    return (done / target) * 100;
                }
                return 0;
            }

            function dlaCurrentFrac() {
                try {
                    if (typeof timeToFrac === 'function') {
                        var n = new Date();
                        var clock = String(n.getHours()).padStart(2, '0') + ':' + String(n
                            .getMinutes()).padStart(2, '0');
                        return timeToFrac(clock);
                    }
                } catch (err) {}
                return null;
            }

            function dlaAllowedDeliveryDates() {
                try {
                    var map = {};
                    var today = '';
                    try {
                        if (typeof todayLocalDateKey === 'function') {
                            today = todayLocalDateKey();
                        }
                    } catch (err) {}
                    if (!today) {
                        var nd = new Date();
                        today = nd.getFullYear() + '-' + String(nd.getMonth() + 1).padStart(2,
                            '0') + '-' + String(nd.getDate()).padStart(2, '0');
                    }
                    if (today) {
                        map[today] = true;
                    }
                    try {
                        if (typeof getDailyFilterViewDate === 'function') {
                            var v = getDailyFilterViewDate();
                            if (typeof formatLocalDateKey === 'function') {
                                v = formatLocalDateKey(v);
                            } else {
                                v = String(v || '').substring(0, 10);
                            }
                            if (v) {
                                map[v] = true;
                            }
                        }
                    } catch (err) {}
                    return map;
                } catch (err) {
                    return {};
                }
            }

            function dlaIsCritical(row) {
                try {
                    if (!row) {
                        return false;
                    }
                    try {
                        var dd = '';
                        if (typeof formatLocalDateKey === 'function') {
                            dd = formatLocalDateKey(row.delivery_date);
                        } else {
                            dd = String((row && row.delivery_date) || '').substring(0, 10);
                        }
                        var allowed = dlaAllowedDeliveryDates();
                        if (dd && Object.keys(allowed).length && !allowed[dd]) {
                            return false;
                        }
                    } catch (err) {}
                    if (typeof rowHasConfiguredMasterCycle === 'function' && !rowHasConfiguredMasterCycle(
                            row)) {
                        return false;
                    }
                    var pct = dlaGetProgress(row);
                    if (!(pct > 0 && pct < 100)) {
                        return false;
                    }
                    if (typeof isRowPastFinishPrep === 'function') {
                        try {
                            if (isRowPastFinishPrep(row, dlaCurrentFrac())) {
                                return true;
                            }
                        } catch (err) {}
                    }
                    if (typeof buildPrepLateInfo === 'function') {
                        try {
                            var info = buildPrepLateInfo(row, {});
                            if (info && info.isLate) {
                                return true;
                            }
                        } catch (err) {}
                    }
                    return false;
                } catch (err) {
                    return false;
                }
            }

            function dlaBucketKey(row) {
                return [(row && row.customer_id) || '-', (row && row.cycle_name) || '-',
                    String((row && row.delivery_date) || '').substring(0, 10),
                    (row && row.cycle_time) || '-'
                ].join('|');
            }

            function dlaUnlockAudio() {
                try {
                    if (!dlaAudioCtx && (window.AudioContext || window.webkitAudioContext)) {
                        var AC = window.AudioContext || window.webkitAudioContext;
                        dlaAudioCtx = new AC();
                    }
                    if (dlaAudioCtx && dlaAudioCtx.state === 'suspended') {
                        dlaAudioCtx.resume();
                    }
                } catch (err) {}
            }

            function dlaBeepOnce() {
                try {
                    if (!dlaSoundEnabled) {
                        return;
                    }
                    dlaUnlockAudio();
                    if (!dlaAudioCtx) {
                        return;
                    }
                    var t = dlaAudioCtx.currentTime;
                    [0, 0.35, 0.7].forEach(function(offset) {
                        var osc = dlaAudioCtx.createOscillator();
                        var gain = dlaAudioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.value = 880;
                        gain.gain.setValueAtTime(0.0001, t + offset);
                        gain.gain.exponentialRampToValueAtTime(0.5, t + offset + 0.03);
                        gain.gain.exponentialRampToValueAtTime(0.0001, t + offset + 0.28);
                        osc.connect(gain);
                        gain.connect(dlaAudioCtx.destination);
                        osc.start(t + offset);
                        osc.stop(t + offset + 0.3);
                    });
                } catch (err) {}
            }

            function dlaStartAlarm() {
                try {
                    dlaStopAlarm();
                    if (!dlaSoundEnabled) {
                        return;
                    }
                    dlaBeepOnce();
                    dlaAlarmTimer = setInterval(dlaBeepOnce, 2500);
                } catch (err) {}
            }

            function dlaStopAlarm() {
                try {
                    if (dlaAlarmTimer) {
                        clearInterval(dlaAlarmTimer);
                        dlaAlarmTimer = null;
                    }
                } catch (err) {}
            }

            function dlaEnsureButton() {
                try {
                    if ($('#dlaSoundBtn').length) {
                        return;
                    }
                    var btn = $(
                        '<button type="button" id="dlaSoundBtn" title="Alarm delay on/off (klik sekali untuk aktifkan suara)"></button>'
                    );
                    btn.css({
                        position: 'fixed',
                        right: '16px',
                        bottom: '16px',
                        zIndex: 1060,
                        width: '44px',
                        height: '44px',
                        borderRadius: '50%',
                        border: '1px solid var(--border)',
                        background: '#fff',
                        boxShadow: '0 4px 14px rgba(0,0,0,.18)',
                        fontSize: '18px',
                        cursor: 'pointer'
                    });
                    var renderIcon = function() {
                        btn.text(dlaSoundEnabled ? '\uD83D\uDD14' : '\uD83D\uDD15');
                        btn.css('opacity', dlaSoundEnabled ? '1' : '.55');
                    };
                    renderIcon();
                    btn.on('click', function() {
                        dlaSoundEnabled = !dlaSoundEnabled;
                        try {
                            localStorage.setItem(dlaSoundKey, dlaSoundEnabled ? '1' : '0');
                        } catch (err) {}
                        if (dlaSoundEnabled) {
                            dlaUnlockAudio();
                            dlaBeepOnce();
                        } else {
                            dlaStopAlarm();
                        }
                        renderIcon();
                    });
                    $('body').append(btn);
                } catch (err) {}
            }

            function dlaShowPopup(rows) {
                try {
                    if (!rows.length || typeof Swal === 'undefined') {
                        return;
                    }
                    if (Swal.isVisible && Swal.isVisible()) {
                        return;
                    }
                    var items = rows.slice(0, 5).map(function(r) {
                        var pct = Math.round(dlaGetProgress(r) * 10) / 10;
                        return '<div style="text-align:left;border:1px solid #fecaca;background:#fff7f7;border-radius:8px;padding:8px 10px;margin-bottom:8px;">' +
                            '<div style="font-weight:800;color:#991b1b;font-size:13px;">' + escapeHtml(
                                (r.customer_name || '-') + ' | Cycle ' + (r.cycle_name || '-')) + '</div>' +
                            '<div style="font-size:12px;color:#444;">Progress ' + pct + '% (' + (r
                                .total_done || 0) + ' / ' + (r.total_target || 0) + ') · ' + (r.ll_count ||
                                0) + ' LL</div>' +
                            '<div style="font-size:11px;color:#777;">Prep ' + escapeHtml((r.cycle_time ||
                                '-') + (r.prep_end_time ? '–' + r.prep_end_time : '')) + ' · Delivery ' +
                            escapeHtml(String(r.delivery_date || '').substring(0, 10)) + '</div>' +
                            '</div>';
                    }).join('');
                    var extra = rows.length > 5 ? '<div style="font-size:11px;color:#777;">+' + (rows.length - 5) +
                        ' lainnya</div>' : '';
                    dlaStartAlarm();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Delay Delivery Terdeteksi',
                        html: '<div style="font-size:12px;color:#555;margin-bottom:10px;">Ada ' + rows
                            .length +
                            ' preparation yang masih On Progress tetapi sudah melewati Finish Preparation.</div>' +
                            items + extra,
                        confirmButtonText: 'Mengerti, matikan alarm',
                        confirmButtonColor: '#dc3545',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showCloseButton: false
                    }).then(function(result) {
                        dlaStopAlarm();
                        try {
                            if (result && result.isConfirmed) {
                                var confirmed = dlaGetConfirmedMap();
                                rows.forEach(function(r) {
                                    confirmed[dlaBucketKey(r)] = Date.now();
                                });
                                dlaSetConfirmedMap(confirmed);
                            }
                        } catch (err) {}
                    });
                } catch (err) {}
            }

            function dlaCheck() {
                try {
                    if (document.hidden) {
                        return;
                    }
                    if (typeof chartMergedByCustTime === 'undefined' || !Array.isArray(
                            chartMergedByCustTime) || !chartMergedByCustTime.length) {
                        return;
                    }
                    var now = Date.now();
                    var shown = dlaGetShownMap();
                    var confirmed = dlaGetConfirmedMap();
                    var fresh = [];
                    chartMergedByCustTime.forEach(function(row) {
                        try {
                            if (!dlaIsCritical(row)) {
                                return;
                            }
                            var key = dlaBucketKey(row);
                            if (confirmed[key]) {
                                return;
                            }
                            var last = parseInt(shown[key] || 0, 10);
                            if (last && (now - last) < dlaCooldownMs) {
                                return;
                            }
                            shown[key] = now;
                            fresh.push(row);
                        } catch (err) {}
                    });
                    dlaSetShownMap(shown);
                    if (fresh.length) {
                        dlaShowPopup(fresh);
                    }
                } catch (err) {}
            }

            try {
                dlaEnsureButton();
                document.addEventListener('click', dlaUnlockAudio, {
                    once: true
                });
                setTimeout(dlaCheck, 8000);
                setInterval(dlaCheck, 45000);
            } catch (err) {}

            tickDeliveryHeaderClock();
            setInterval(tickDeliveryHeaderClock, 1000);
        });
    </script>

    @can('manage-master')
        {{-- ===== MODAL MASTER CYCLE ===== --}}
        <div class="modal fade" id="masterCycleModal" tabindex="-1" role="dialog" aria-labelledby="masterCycleModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form id="masterCycleForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="masterCycleModalTitle">Tambah Master Cycle</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="col-md-8 mb-3">
                                    <label class="bella-form-label">Customer</label>
                                    <select class="bella-form-control" id="mcycleCustomerId" required>
                                        <option value="">Pilih customer</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="bella-form-label">Nama Cycle</label>
                                    <select class="bella-form-control" id="mcycleName" required>
                                        <option value="" selected disabled>Pilih cycle</option>
                                        @foreach (range(1, 10) as $n)
                                            <option value="{{ $n }}">{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7 mb-3">
                                    <label class="bella-form-label">Waktu Rentang Preparation</label>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <input type="time" class="bella-form-control" id="mcyclePrepStart" required
                                            step="60">
                                        <span style="font-size:11px;color:var(--text-muted);white-space:nowrap;">sampai</span>
                                        <input type="time" class="bella-form-control" id="mcyclePrepEnd" required
                                            step="60">
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="bella-form-label">Hari Offset Preparation</label>
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
                                <div class="col-md-5 mb-3">
                                    <label class="bella-form-label">Waktu ETA Truck</label>
                                    <input type="time" class="bella-form-control" id="mcycleTruckTime" required
                                        step="60">
                                </div>
                            </div>
                            <p
                                style="font-size:11px;color:var(--text-muted);margin:0;padding:10px 12px;background:#f8fafc;border-radius:6px;">
                                Hari Offset = tanggal mulai prep terhadap delivery (H). Otomatis H-1 jika jam mulai
                                prep &gt; jam truck (mis. prep 22:00, truck 07:00). Timeline harian 00:00–24:00;
                                bar prep hanya tampil jika sudah/sedang berjalan.
                            </p>
                        </div>
                        <div class="modal-footer" style="gap:8px;">
                            <button type="button" class="act-btn secondary" id="btnMasterCancel" data-dismiss="modal"
                                style="height:32px;padding:0 14px;font-size:12px;">
                                Batal
                            </button>
                            <button type="submit" class="act-btn success" id="btnMasterSave"
                                style="height:32px;padding:0 14px;font-size:12px;">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

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
