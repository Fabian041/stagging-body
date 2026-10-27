@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== DASHBOARD STOCK - SAME STYLE AS ViewMasterPis ===== */
        .bella-table-card {
            background: var(--card, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 10px;
            box-shadow: var(--shadow, 0 8px 24px rgba(15, 23, 42, .06));
            overflow: hidden;
            margin-top: 14px;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: relative;
            background: var(--card, #ffffff);
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy, #294795);
            text-transform: uppercase;
            letter-spacing: .08em;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .bella-table-card-subtitle {
            font-size: 11px;
            color: var(--text-muted, #64748b);
            margin-top: 2px;
            line-height: 1.5;
        }

        .bella-table-card-body {
            padding: 18px 20px 20px;
            background: var(--card, #ffffff);
        }

        .stock-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .stock-tab-wrap {
            border: 1px solid var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            padding: 10px;
            background: var(--bg, #f8fafc);
            margin-bottom: 16px;
        }

        .stock-tab-wrap .nav-pills {
            gap: 8px;
        }

        .stock-tab-wrap .nav-pills .nav-item {
            margin: 0;
        }

        .stock-tab-wrap .nav-pills .nav-link {
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 6px;
            background: var(--card, #ffffff);
            color: var(--text-muted, #64748b);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 8px 13px;
            min-width: 76px;
            text-align: center;
            transition: .15s;
        }

        .stock-tab-wrap .nav-pills .nav-link:hover {
            color: var(--primary, #0070B7);
            border-color: rgba(0, 112, 183, .30);
        }

        .stock-tab-wrap .nav-pills .nav-link.active,
        .stock-tab-wrap .nav-pills .show>.nav-link {
            background: var(--primary, #0070B7) !important;
            border-color: var(--primary, #0070B7) !important;
            color: #fff !important;
            box-shadow: 0 8px 18px rgba(0, 112, 183, .18);
        }

        .stock-filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .stock-status-filter.active {
            background: rgba(0, 112, 183, .08);
            border-color: rgba(0, 112, 183, .35);
            color: var(--primary, #0070B7);
        }

        .stock-table-wrap {
            border: 1px solid var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            overflow: hidden;
            background: var(--card, #ffffff);
        }

        .stock-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12.5px !important;
            margin-bottom: 0 !important;
        }

        .stock-table thead th {
            text-align: center !important;
            padding: 9px 12px !important;
            color: var(--text-muted, #64748b) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg, #f8fafc) !important;
            border-bottom: 1px solid var(--border, #e5e7eb) !important;
            border-top: none !important;
            white-space: nowrap !important;
        }

        .stock-table tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border, #e5e7eb) !important;
            vertical-align: middle !important;
            color: var(--text, #0f172a) !important;
            background: var(--card, #ffffff) !important;
        }

        .stock-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .stock-table tbody tr:hover td {
            background: var(--bg, #f8fafc) !important;
        }

        .back-number-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 86px;
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid rgba(0, 112, 183, .18);
            background: rgba(0, 112, 183, .07);
            color: var(--primary, #0070B7);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .04em;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .stock-number {
            font-size: 13px;
            font-weight: 800;
            color: var(--text, #0f172a);
        }

        .stock-movement-in {
            color: #15803d;
        }

        .stock-movement-out {
            color: #dc2626;
        }

        .stock-row-actions {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
        }

        .history-direction {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .history-direction-in {
            color: #15803d;
            background: #dcfce7;
        }

        .history-direction-out {
            color: #b91c1c;
            background: #fee2e2;
        }

        .history-adjustment {
            display: inline-flex;
            margin-left: 6px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        #stockHistoryModal .modal-dialog {
            width: calc(100% - 28px);
            max-width: 880px;
            margin: 14px auto;
        }

        #stockHistoryModal .modal-content {
            max-height: calc(100vh - 28px);
        }

        #stockHistoryModal .modal-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px !important;
            background: var(--card, #ffffff) !important;
        }

        .history-modal-title {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .history-modal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 10px;
            color: var(--primary, #0070b7);
            background: rgba(0, 112, 183, .1);
        }

        .history-modal-title h5 {
            margin: 0 0 5px;
            color: var(--text, #0f172a) !important;
            font-size: 16px !important;
            font-weight: 800 !important;
        }

        .history-part-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            color: var(--text-muted, #64748b);
            font-size: 11.5px;
        }

        .history-back-number {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 5px;
            color: var(--primary, #0070b7);
            background: rgba(0, 112, 183, .08);
            font-weight: 800;
        }

        .history-current-summary {
            min-width: 132px;
            padding: 8px 12px;
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 8px;
            background: var(--bg, #f8fafc);
            text-align: right;
        }

        .history-current-summary span {
            display: block;
            margin-bottom: 1px;
            color: var(--text-muted, #64748b);
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .history-current-summary strong {
            color: var(--text, #0f172a);
            font-size: 18px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        #stockHistoryModal .modal-body {
            padding: 0 !important;
            max-height: calc(100vh - 176px);
            overflow-y: auto;
            background: var(--card, #ffffff) !important;
        }

        .history-table {
            width: 100%;
            margin: 0;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }

        .history-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: 10px 14px;
            border: 0;
            border-bottom: 1px solid var(--border, #e5e7eb);
            background: var(--bg, #f8fafc);
            color: var(--text-muted, #64748b);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            white-space: nowrap;
        }

        .history-table tbody td {
            height: 64px;
            padding: 10px 14px;
            border: 0;
            border-bottom: 1px solid var(--border, #e5e7eb);
            background: var(--card, #ffffff);
            color: var(--text, #0f172a);
            font-size: 12.5px;
            vertical-align: middle;
        }

        .history-table tbody tr:hover td {
            background: var(--bg, #f8fafc);
        }

        .history-time {
            white-space: nowrap;
        }

        .history-time strong,
        .history-time span {
            display: block;
        }

        .history-time strong {
            margin-bottom: 2px;
            font-size: 12px;
            font-weight: 750;
        }

        .history-time span {
            color: var(--text-muted, #64748b);
            font-size: 11px;
            font-variant-numeric: tabular-nums;
        }

        .history-mutation {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .history-qty,
        .history-balance,
        .history-code {
            font-variant-numeric: tabular-nums;
        }

        .history-qty,
        .history-balance {
            font-size: 13px;
            font-weight: 800;
        }

        .history-balance {
            color: var(--navy, #294795);
        }

        #stockHistoryModal .modal-footer {
            min-height: 58px;
        }

        .history-pagination-actions {
            display: flex;
            gap: 6px;
        }

        .history-filter-bar {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border, #e5e7eb);
            background: var(--bg, #f8fafc);
        }

        .history-filter-field {
            min-width: 150px;
        }

        .history-filter-field label {
            display: block;
            margin: 0 0 4px;
            color: var(--text-muted, #64748b);
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .history-filter-field .form-control {
            height: 34px;
            padding: 5px 9px;
            border-color: var(--border, #e5e7eb);
            border-radius: 5px;
            background: var(--card, #ffffff);
            color: var(--text, #0f172a);
            font-size: 11.5px;
        }

        .history-download-button {
            margin-left: auto;
        }

        .bella-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .bella-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .bella-badge-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .bella-badge-yellow {
            background: #fef3c7;
            color: #b45309;
        }

        .act-btn {
            height: 34px;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
            transition: .15s;
            white-space: nowrap;
        }

        .act-btn.primary {
            background: var(--primary, #0070B7);
            border-color: var(--primary, #0070B7);
            color: #fff;
        }

        .act-btn.success {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .act-btn.danger {
            background: var(--danger, #dc2626);
            border-color: var(--danger, #dc2626);
            color: #fff;
        }

        .act-btn.secondary {
            background: var(--card, #ffffff);
            border-color: var(--border, #e5e7eb);
            color: var(--text-muted, #64748b);
        }

        .act-btn:hover {
            filter: brightness(.95);
            text-decoration: none;
            color: inherit;
            transform: translateY(-1px);
        }

        .act-btn.primary:hover,
        .act-btn.success:hover,
        .act-btn.danger:hover {
            color: #fff;
        }

        .stock-summary-card {
            border: 1px solid var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            padding: 14px;
            background: var(--card, #ffffff);
            margin-bottom: 14px;
        }

        .stock-summary-label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted, #64748b);
            margin-bottom: 4px;
        }

        .stock-summary-value {
            font-size: 15px;
            font-weight: 800;
            color: var(--navy, #294795);
        }

        .modal-content {
            border: 1px solid var(--border, #e5e7eb) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-md, 0 18px 40px rgba(15, 23, 42, .14)) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            overflow: hidden !important;
        }

        .modal-header {
            background: var(--bg, #f8fafc) !important;
            border-bottom: 1px solid var(--border, #e5e7eb) !important;
            padding: 14px 20px !important;
        }

        .modal-title {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: var(--navy, #294795) !important;
        }

        .modal-header .close {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 5px;
            background: var(--card, #ffffff);
            opacity: 1 !important;
            color: var(--text-muted, #64748b) !important;
            font-size: 16px !important;
            line-height: 26px;
            padding: 0;
            transition: .15s;
        }

        .modal-header .close:hover {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border-color: #fecaca !important;
        }

        .modal-body {
            padding: 16px 20px !important;
            background: var(--bg, #f8fafc) !important;
            overflow-x: visible;
            overflow-y: auto;
        }

        .modal-footer {
            border-top: 1px solid var(--border, #e5e7eb) !important;
            padding: 12px 20px !important;
            background: var(--card, #ffffff) !important;
        }

        .pis-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            align-items: start;
            border: 1px solid var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            padding: 16px;
            margin-bottom: 0;
            background: var(--card, #ffffff);
        }

        .pis-row .form-group {
            margin-bottom: 0;
        }

        .pis-row .form-group.full-span {
            grid-column: 1 / -1;
        }

        .pis-row .form-group label {
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted, #64748b);
            display: block;
        }

        .pis-row .form-control,
        .pis-row .custom-file-label {
            height: 34px;
            border: 1px solid var(--border, #e5e7eb) !important;
            border-radius: 5px !important;
            background: var(--bg, #f8fafc) !important;
            color: var(--text, #0f172a) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .pis-row .custom-file-label::after {
            height: 32px;
            border-left: 1px solid var(--border, #e5e7eb) !important;
            background: var(--card, #ffffff) !important;
            color: var(--text-muted, #64748b) !important;
            font-size: 12px;
        }

        .pis-row .form-control:focus {
            border-color: var(--sky, #0097D8) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .bella-table-card .dataTables_wrapper {
            padding: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length,
        .bella-table-card .dataTables_wrapper .dataTables_filter {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted, #64748b);
        }

        .bella-table-card .dataTables_wrapper .dataTables_length label,
        .bella-table-card .dataTables_wrapper .dataTables_filter label {
            font-size: 12px;
            color: var(--text-muted, #64748b);
            margin: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length select,
        .bella-table-card .dataTables_wrapper .dataTables_filter input {
            height: 30px;
            border: 1px solid var(--border, #e5e7eb) !important;
            border-radius: 4px !important;
            background: var(--bg, #f8fafc) !important;
            color: var(--text, #0f172a) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            padding: 0 8px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--sky, #0097D8) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate {
            padding: 10px 16px;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button {
            min-width: 30px !important;
            height: 30px !important;
            border: 1px solid var(--border, #e5e7eb) !important;
            border-radius: 4px !important;
            background: var(--card, #ffffff) !important;
            color: var(--text-muted, #64748b) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 0 8px !important;
            margin: 0 2px !important;
            line-height: 28px !important;
            transition: .15s !important;
            box-shadow: none !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--bg, #f8fafc) !important;
            color: var(--text, #0f172a) !important;
            border-color: var(--border, #e5e7eb) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary, #0070B7) !important;
            color: #fff !important;
            border-color: var(--primary, #0070B7) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: .4 !important;
            cursor: not-allowed !important;
            background: var(--card, #ffffff) !important;
            color: var(--text-muted, #64748b) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_info {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted, #64748b);
        }

        .stock-table tbody tr.stock-flash>td {
            background: #fff7cc !important;
            transition: background .4s ease;
        }

        /* ===== UPLOAD MODAL - CLEAN MINIMAL ===== */
        .upload-drop-zone {
            border: 1.5px dashed var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            background: var(--bg, #f8fafc);
            padding: 28px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }

        .upload-drop-zone:hover,
        .upload-drop-zone.dragover {
            border-color: #0070B7;
            background: rgba(0, 112, 183, .04);
        }

        .upload-drop-zone .dz-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: var(--card, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .upload-drop-zone .dz-icon-wrap i {
            font-size: 22px;
            color: var(--text-muted, #64748b);
        }

        .upload-drop-zone p {
            font-size: 12.5px;
            color: var(--text-muted, #64748b);
            margin: 0 0 4px;
        }

        .upload-drop-zone span.dz-ext {
            font-size: 11px;
            color: #94a3b8;
        }

        .upload-drop-zone .dz-browse {
            color: var(--primary, #0070B7);
            font-weight: 700;
            text-decoration: underline;
            cursor: pointer;
        }

        .upload-file-card {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--card, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 10px;
        }

        .upload-file-card .ufc-icon {
            width: 34px;
            height: 34px;
            border-radius: 6px;
            background: #dcfce7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .upload-file-card .ufc-icon i {
            font-size: 18px;
            color: #15803d;
        }

        .upload-file-card .ufc-meta {
            flex: 1;
            min-width: 0;
        }

        .upload-file-card .ufc-name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text, #0f172a);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .upload-file-card .ufc-size {
            font-size: 11px;
            color: var(--text-muted, #64748b);
            margin-top: 1px;
        }

        .upload-file-card .ufc-remove {
            width: 26px;
            height: 26px;
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 5px;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted, #64748b);
            font-size: 14px;
            transition: .15s;
            flex-shrink: 0;
        }

        .upload-file-card .ufc-remove:hover {
            background: #fee2e2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .upload-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            font-size: 11px;
            color: #94a3b8;
        }

        .upload-hint i {
            font-size: 13px;
        }

        .upload-hint.success {
            color: #15803d;
        }

        .upload-modal-label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted, #64748b);
            margin-bottom: 8px;
        }

        /* ===== IZITOAST OVERRIDE - MINIMAL FLAT ===== */
        .iziToast {
            border-radius: 8px !important;
            padding: 10px 14px !important;
            min-height: unset !important;
            border: none !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        /* hapus icon besar bawaan */
        .iziToast>.iziToast-icon {
            display: none !important;
        }

        .iziToast>.iziToast-body {
            padding: 0 !important;
            margin-left: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        /* icon kecil custom lewat pseudo */
        .iziToast>.iziToast-body::before {
            content: '';
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: rgba(255, 255, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 13px;
            color: #fff;
            line-height: 28px;
            text-align: center;
        }

        .iziToast-success>.iziToast-body::before {
            content: '\f00c';
        }

        .iziToast-danger>.iziToast-body::before {
            content: '\f00d';
        }

        .iziToast-warning>.iziToast-body::before {
            content: '\f071';
        }

        .iziToast-info>.iziToast-body::before {
            content: '\f05a';
        }

        .iziToast>.iziToast-body .iziToast-texts {
            flex: 1 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .iziToast>.iziToast-body .iziToast-title {
            font-size: 12px !important;
            font-weight: 800 !important;
            color: #fff !important;
            margin-bottom: 1px !important;
            line-height: 1.3 !important;
        }

        .iziToast>.iziToast-body .iziToast-message {
            font-size: 11.5px !important;
            color: rgba(255, 255, 255, .82) !important;
            line-height: 1.4 !important;
            margin: 0 !important;
        }

        /* tombol close */
        .iziToast>.iziToast-close {
            width: 22px !important;
            height: 22px !important;
            border-radius: 4px !important;
            background: rgba(255, 255, 255, .15) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 14'%3E%3Cpath d='M1 1l12 12M13 1L1 13' stroke='white' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") center / 10px no-repeat !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            right: 10px !important;
            opacity: .7 !important;
        }

        .iziToast>.iziToast-close:hover {
            opacity: 1 !important;
            background-color: rgba(255, 255, 255, .25) !important;
        }

        /* progress bar */
        .iziToast>.iziToast-progressbar {
            height: 2px !important;
            bottom: 0 !important;
            border-radius: 0 0 8px 8px !important;
            background: transparent !important;
        }

        .iziToast>.iziToast-progressbar>div {
            background: rgba(255, 255, 255, .4) !important;
            height: 2px !important;
        }

        /* warna per tipe */
        .iziToast-success {
            background: #16a34a !important;
            border-color: #15803d !important;
        }

        .iziToast-danger {
            background: #dc2626 !important;
            border-color: #b91c1c !important;
        }

        .iziToast-warning {
            background: #d97706 !important;
            border-color: #b45309 !important;
        }

        .iziToast-info {
            background: #0070B7 !important;
            border-color: #005a94 !important;
        }

        @media (max-width: 768px) {
            .bella-table-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .stock-header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .stock-tab-wrap .nav-pills {
                justify-content: flex-start !important;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 3px;
            }

            .stock-tab-wrap .nav-pills .nav-link {
                min-width: 72px;
            }

            .pis-row {
                grid-template-columns: 1fr;
            }

            .pis-row .form-group.full-span {
                grid-column: 1;
            }

            #stockHistoryModal .modal-header {
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 10px;
            }

            .history-current-summary {
                order: 3;
                width: 100%;
                text-align: left;
            }

            #stockHistoryModal .modal-header .close {
                margin-left: auto;
            }

            .history-table thead th,
            .history-table tbody td {
                padding-left: 10px;
                padding-right: 10px;
            }

            .history-table th:nth-child(4),
            .history-table td:nth-child(4) {
                display: none;
            }

            .history-filter-bar {
                align-items: stretch;
                flex-wrap: wrap;
            }

            .history-filter-field {
                flex: 1 1 calc(50% - 4px);
                min-width: 130px;
            }

            .history-download-button {
                margin-left: 0;
            }
        }
    </style>

    <div class="bella-table-card mt-3">
        <div class="bella-table-card-header">
            <div>
                <span class="bella-table-card-title"><i class="fas fa-boxes mr-1"></i>Body Plant Stock Monitoring</span>
                <div class="bella-table-card-subtitle">Total IN − Total OUT menghasilkan current stock, termasuk transaksi adjustment.</div>
            </div>
            <div class="stock-header-actions">
                <button type="button" class="act-btn primary" data-toggle="modal" data-target="#stockHistoryExportModal">
                    <i class="fas fa-download"></i> Export Riwayat
                </button>
                <a href="{{ route('dashboard.stock.template') }}" class="act-btn secondary">
                    <i class="fas fa-file-excel"></i> Template Excel
                </a>
                <button type="button" class="act-btn success" data-toggle="modal" data-target="#stockModal">
                    <i class="fas fa-file-import"></i> Import Stock
                </button>
                {{-- <button type="button" class="act-btn success" data-toggle="modal" data-target="#exportMutationModal">
                    <i class="fas fa-file-excel"></i> Export Mutasi
                </button> --}}
            </div>
        </div>

        <div class="bella-table-card-body">
            <div class="stock-tab-wrap">
                <ul class="nav nav-pills justify-content-center" id="myTab3" role="tablist">
                    @forelse ($lines as $line)
                        <li class="nav-item">
                            <a class="nav-link show @if ($line['active']) active @endif"
                                id="{{ $line['key'] }}-tab"
                                data-toggle="tab"
                                data-line="{{ $line['name'] }}"
                                href="#{{ $line['key'] }}"
                                role="tab"
                                aria-controls="{{ $line['key'] }}"
                                aria-selected="{{ $line['active'] ? 'true' : 'false' }}">{{ $line['name'] }}</a>
                        </li>
                    @empty
                        <li class="nav-item">
                            <span class="nav-link disabled">No Line</span>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="stock-filter-bar">
                <button type="button" class="act-btn secondary stock-status-filter active" data-status="">Semua</button>
                <button type="button" class="act-btn secondary stock-status-filter" data-status="Under Stock">Under</button>
                <button type="button" class="act-btn secondary stock-status-filter" data-status="Low Stock">Low</button>
                <button type="button" class="act-btn secondary stock-status-filter" data-status="In Stock">In Stock</button>
            </div>

            <div class="tab-content" id="myTabContent2">
                @forelse ($lines as $line)
                    <div class="tab-pane fade @if ($line['active']) active show @endif"
                        id="{{ $line['key'] }}"
                        data-line="{{ $line['name'] }}"
                        role="tabpanel"
                        aria-labelledby="{{ $line['key'] }}-tab">
                        <div class="stock-table-wrap table-responsive">
                            <table class="table table-bordered table-md stock-table"
                                id="stocks-{{ $line['key'] }}"
                                data-line="{{ $line['name'] }}">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Back Number</th>
                                        <th>Part Number</th>
                                        <th>Standard Stock</th>
                                        <th>Total In</th>
                                        <th>Total Out</th>
                                        <th>Current Stock</th>
                                        <th>Gap</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">Memuat data stock...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">Belum ada line production yang bisa ditampilkan.</div>
                @endforelse
            </div>
        </div>
    </div>

{{-- modal stock --}}
<div class="modal fade" tabindex="-1" role="dialog" id="edit">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('production.adjust') }}" method="POST" enctype="multipart/form-data">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock <code id="title"></code></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="stock-summary-card">
                        <div class="row text-center">
                            <div class="col-6">
                                <span class="stock-summary-label">Standard Stock</span>
                                <span class="stock-summary-value" id="standard_stock"></span>
                            </div>
                            <div class="col-6">
                                <span class="stock-summary-label">Current Stock</span>
                                <span class="stock-summary-value" id="current_stock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="pis-row">
                        <div class="form-group">
                            <label>New Standard Stock</label>
                            <input type="hidden" id="internal_part_id" name="internal_part_id">
                            <input type="hidden" id="active_line_input" name="active_line">
                            <input type="hidden" id="back_number_input" name="back_number">
                            <input type="number" class="form-control" id="standard_stock_input" name="standard_stock" min="0"
                                placeholder="-">
                        </div>
                        <div class="form-group">
                            <label>New Current Stock</label>
                            <input type="number" class="form-control" id="current_stock_input" name="current_stock" min="0"
                                placeholder="-">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="act-btn secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="act-btn primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}

<div class="modal fade" tabindex="-1" role="dialog" id="stockHistoryModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="history-modal-title">
                    <span class="history-modal-icon"><i class="fas fa-exchange-alt"></i></span>
                    <div>
                        <h5>Riwayat Transaksi Stock</h5>
                        <div class="history-part-meta">
                            <span class="history-back-number" id="historyBackNumber">-</span>
                            <span id="historyPartNumber">-</span>
                        </div>
                    </div>
                </div>
                <div class="history-current-summary">
                    <span>Current Stock</span>
                    <strong id="historyCurrentStock">0</strong>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="history-filter-bar">
                    <div class="history-filter-field">
                        <label for="stockHistoryDateFrom">Dari Tanggal</label>
                        <input type="date" class="form-control" id="stockHistoryDateFrom">
                    </div>
                    <div class="history-filter-field">
                        <label for="stockHistoryDateTo">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="stockHistoryDateTo">
                    </div>
                    <button type="button" class="act-btn primary" id="stockHistoryApply">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    <button type="button" class="act-btn secondary" id="stockHistoryReset" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="act-btn success history-download-button" id="stockHistoryDownload">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </button>
                </div>
                <div class="table-responsive mb-0">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th style="width: 24%">Waktu</th>
                                <th style="width: 23%">Mutasi</th>
                                <th style="width: 20%">Serial</th>
                                <th>Operator</th>
                                <th style="width: 18%" class="text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody id="stockHistoryBody">
                            <tr><td colspan="5" class="text-center text-muted py-4">Memuat riwayat transaksi...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <span class="text-muted" id="stockHistoryPageInfo">-</span>
                <div class="history-pagination-actions">
                    <button type="button" class="act-btn secondary" id="stockHistoryPrev"><i class="fas fa-chevron-left"></i> Sebelumnya</button>
                    <button type="button" class="act-btn secondary" id="stockHistoryNext">Berikutnya <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="stockHistoryExportModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.productionStock.transactions.export') }}" method="GET"
                id="stockHistoryExportForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-excel mr-2"></i>Export Riwayat Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="form-group">
                        <label for="stockHistoryExportLine">Line</label>
                        <select class="form-control" name="line" id="stockHistoryExportLine">
                            <option value="">Semua Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line['name'] }}" @selected($line['active'])>{{ $line['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pis-row">
                        <div class="form-group">
                            <label for="stockHistoryExportFrom">Dari Waktu</label>
                            <input type="datetime-local" class="form-control" name="from" id="stockHistoryExportFrom">
                        </div>
                        <div class="form-group">
                            <label for="stockHistoryExportTo">Sampai Waktu</label>
                            <input type="datetime-local" class="form-control" name="to" id="stockHistoryExportTo">
                        </div>
                    </div>
                    <div class="upload-hint mb-0">
                        <i class="fas fa-info-circle"></i>
                        <span>Pilih Semua Line dan kosongkan waktu untuk mengunduh seluruh riwayat.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="act-btn secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="act-btn success">
                        <i class="fas fa-download"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- modal --}}
<div class="modal fade" tabindex="-1" role="dialog" id="stockModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.stock.import') }}" method="POST" enctype="multipart/form-data"
                id="stockForm">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-import mr-1"></i> Import Stock
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span class="upload-modal-label">Stock File</span>

                    <input type="file" id="stockFileInput" name="file" accept=".xlsx,.xls,.csv"
                        style="display:none">

                    <div class="upload-drop-zone" id="stockDropZone"
                        onclick="document.getElementById('stockFileInput').click()">
                        <div class="dz-icon-wrap">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p>Drag &amp; drop file di sini, atau
                            <span class="dz-browse">browse</span>
                        </p>
                        <span class="dz-ext">.xlsx, .xls, .csv &mdash; maks 10 MB</span>
                    </div>

                    <div class="upload-file-card" id="stockFileCard" style="display:none">
                        <div class="ufc-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div class="ufc-meta">
                            <div class="ufc-name" id="stockFileName">—</div>
                            <div class="ufc-size" id="stockFileSize">—</div>
                        </div>
                        <button type="button" class="ufc-remove" id="stockFileRemove" title="Hapus file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="upload-hint" id="stockHint">
                        <i class="fas fa-info-circle"></i>
                        <span>Gunakan template standar sebelum upload.</span>
                    </div>
                    <div class="upload-hint success" id="stockHintOk" style="display:none">
                        <i class="fas fa-check-circle"></i>
                        <span>File siap diimport.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="act-btn secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="act-btn primary" id="stockSubmitBtn" disabled>
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="exportMutationModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.mutation.export') }}" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title">Export Mutasi (Filter Tanggal)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body mt-3">
                    <div class="pis-row">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" class="form-control" name="from"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" class="form-control" name="to"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="act-btn secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="act-btn primary">Download Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- end of modal --}}

@endsection

@push('scripts')
<script>
    (function($) {
        'use strict';

        const stockConfig = {
            dataUrl: @json(route('dashboard.productionStock.data')),
            transactionsUrl: @json(route('dashboard.productionStock.transactions')),
            transactionsExportUrl: @json(route('dashboard.productionStock.transactions.export')),
            activeLine: @json($activeLine),
            focusBackNumber: @json($focusBackNumber),
            refreshMs: 30000,
            statusColumn: 8,
        };

        const flashMessage = {
            error: @json(session('error')),
            success: @json(session('success')),
            warning: @json(session('warning')),
            details: @json(session('import_errors', [])),
            validationErrors: @json($errors->all()),
        };
        const stockNumberFormatter = new Intl.NumberFormat('id-ID');

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDetails(details) {
            if (!details || details.length === 0) {
                return '';
            }

            let html = '<br><br><b>Detail:</b><br>';

            details.slice(0, 8).forEach(function(item) {
                html += '• ' + escapeHtml(item) + '<br>';
            });

            return html;
        }

        function showFlashMessages() {
            if (flashMessage.validationErrors.length > 0) {
                iziToast.error({
                    title: 'File tidak valid',
                    message: formatDetails(flashMessage.validationErrors),
                    position: 'bottomRight',
                    timeout: 9000,
                    class: 'iziToast-danger',
                });
            }

            if (flashMessage.error) {
                iziToast.error({
                    title: 'Gagal',
                    message: escapeHtml(flashMessage.error) + formatDetails(flashMessage.details),
                    position: 'bottomRight',
                    timeout: 10000,
                    class: 'iziToast-danger',
                });
            }

            if (flashMessage.warning) {
                iziToast.warning({
                    title: 'Perhatian',
                    message: escapeHtml(flashMessage.warning) + formatDetails(flashMessage.details),
                    position: 'bottomRight',
                    timeout: 10000,
                    class: 'iziToast-warning',
                });
            }

            if (flashMessage.success) {
                iziToast.success({
                    title: 'Berhasil',
                    message: escapeHtml(flashMessage.success),
                    position: 'bottomRight',
                    timeout: 7000,
                    class: 'iziToast-success',
                });
            }
        }

        function renderStockNumber(value, type) {
            if (type !== 'display') {
                return value;
            }

            return '<span class="stock-number">' + escapeHtml(formatStockNumber(value)) + '</span>';
        }

        function formatStockNumber(value) {
            const number = Number(value);
            return Number.isFinite(number) ? stockNumberFormatter.format(number) : String(value || '0');
        }

        function renderStockMovement(value, type, direction) {
            if (type !== 'display') {
                return value;
            }

            const number = Number(value) || 0;
            const prefix = number === 0 ? '' : (direction === 'in' ? '+' : '−');
            return '<span class="stock-number stock-movement-' + direction + '">' +
                prefix + escapeHtml(formatStockNumber(Math.abs(number))) + '</span>';
        }

        function renderBackNumber(value, type) {
            if (type !== 'display') {
                return value;
            }

            return '<span class="back-number-badge">' + escapeHtml(value) + '</span>';
        }

        function renderGap(value, type, row) {
            const gap = Number(row.qty || 0) - Number(row.standard || 0);

            if (type !== 'display') {
                return gap;
            }

            const label = gap > 0
                ? '+' + formatStockNumber(gap)
                : (gap < 0 ? '−' + formatStockNumber(Math.abs(gap)) : '0');
            const badgeClass = gap < 0 ? 'red' : (gap === 0 ? 'yellow' : 'green');

            return '<span class="bella-badge bella-badge-' + badgeClass + '">' + escapeHtml(label) + '</span>';
        }

        function renderStatus(value, type, row) {
            if (type !== 'display') {
                return value;
            }

            return '<span class="bella-badge bella-badge-' + escapeHtml(row.status_class) + '">' +
                '<i class="' + escapeHtml(row.status_icon) + '"></i> ' + escapeHtml(value) +
                '</span>';
        }

        function renderAction(value, type, row) {
            if (type !== 'display') {
                return row.id;
            }

            return '<span class="stock-row-actions">' +
                '<button type="button" class="act-btn secondary stock-history" ' +
                'data-id="' + escapeHtml(row.id) + '" ' +
                'data-back-number="' + escapeHtml(row.back_number) + '">' +
                '<i class="fas fa-history"></i> Riwayat' +
                '</button>' +
                '<button type="button" class="act-btn primary edit-stock" ' +
                'data-id="' + escapeHtml(row.id) + '" ' +
                'data-back-number="' + escapeHtml(row.back_number) + '" ' +
                'data-standard="' + escapeHtml(row.standard) + '" ' +
                'data-qty="' + escapeHtml(row.qty) + '">' +
                '<i class="fas fa-edit"></i> Edit' +
                '</button></span>';
        }

        let stockHistoryPartId = null;
        let stockHistoryPage = 1;

        function stockHistoryFilters() {
            return {
                date_from: $('#stockHistoryDateFrom').val() || '',
                date_to: $('#stockHistoryDateTo').val() || ''
            };
        }

        function stockHistoryFilterIsValid() {
            const filters = stockHistoryFilters();
            if (filters.date_from && filters.date_to && filters.date_from > filters.date_to) {
                if (window.iziToast) {
                    iziToast.warning({
                        title: 'Filter tanggal tidak valid',
                        message: 'Tanggal mulai tidak boleh setelah tanggal akhir.',
                        position: 'bottomRight',
                        timeout: 4000,
                        class: 'iziToast-warning'
                    });
                }

                return false;
            }

            return true;
        }

        function formatHistoryDate(value) {
            const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}:\d{2}:\d{2})/);
            if (!match) {
                return {
                    date: value || '-',
                    time: ''
                };
            }

            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return {
                date: Number(match[3]) + ' ' + monthNames[Number(match[2]) - 1] + ' ' + match[1],
                time: match[4]
            };
        }

        function renderStockHistoryRow(row) {
            const direction = row.direction === 'in' ? 'in' : 'out';
            const qty = Number(row.qty) || 0;
            const sign = qty === 0 ? '' : (direction === 'in' ? '+' : '−');
            const occurredAt = formatHistoryDate(row.date);
            const adjustment = row.is_adjustment
                ? '<span class="history-adjustment">Adjustment</span>'
                : '';

            return '<tr>' +
                '<td><div class="history-time"><strong>' + escapeHtml(occurredAt.date) + '</strong>' +
                '<span>' + escapeHtml(occurredAt.time) + '</span></div></td>' +
                '<td><div class="history-mutation"><span class="history-direction history-direction-' + direction + '">' +
                direction.toUpperCase() + '</span><span class="history-qty stock-movement-' + direction + '">' +
                sign + escapeHtml(formatStockNumber(Math.abs(qty))) + '</span>' + adjustment + '</div></td>' +
                '<td><span class="history-code">' + escapeHtml(row.serial_number || '-') + '</span></td>' +
                '<td><span class="history-code">' + escapeHtml(row.npk || '-') + '</span></td>' +
                '<td class="text-right"><span class="history-balance">' +
                escapeHtml(formatStockNumber(row.balance_after)) + '</span></td>' +
                '</tr>';
        }

        function loadStockHistory(page) {
            if (!stockHistoryPartId) return;

            stockHistoryPage = page;
            $('#stockHistoryBody').html(
                '<tr><td colspan="5" class="text-center text-muted py-4">Memuat riwayat transaksi...</td></tr>'
            );
            $('#stockHistoryPrev, #stockHistoryNext').prop('disabled', true);

            $.getJSON(stockConfig.transactionsUrl, Object.assign({
                internal_part_id: stockHistoryPartId,
                page: page
            }, stockHistoryFilters())).done(function(response) {
                const rows = response.data || [];
                const pagination = response.pagination || {};
                const part = response.part || {};

                $('#historyBackNumber').text(part.back_number || '-');
                $('#historyPartNumber').text(part.part_number || '-');
                $('#historyCurrentStock').text(formatStockNumber(part.current_stock || 0));
                $('#stockHistoryBody').html(rows.length
                    ? rows.map(renderStockHistoryRow).join('')
                    : '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi IN/OUT.</td></tr>');
                $('#stockHistoryPageInfo').text(
                    'Halaman ' + (pagination.current_page || 1) + ' dari ' + (pagination.last_page || 1) +
                    ' · ' + (pagination.total || 0) + ' transaksi'
                );
                $('#stockHistoryPrev').prop('disabled', (pagination.current_page || 1) <= 1);
                $('#stockHistoryNext').prop('disabled', (pagination.current_page || 1) >= (pagination.last_page || 1));
                $('#stockHistoryDownload').prop('disabled', (pagination.total || 0) === 0);
            }).fail(function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal memuat riwayat transaksi.';
                $('#stockHistoryBody').html(
                    '<tr><td colspan="5" class="text-center text-danger py-4">' + escapeHtml(message) + '</td></tr>'
                );
                $('#stockHistoryPageInfo').text('-');
                $('#stockHistoryDownload').prop('disabled', true);
            });
        }

        function highlightFocusedRow(table) {
            if (!stockConfig.focusBackNumber) {
                return;
            }

            const focusedRow = table
                .rows({
                    search: 'applied'
                })
                .nodes()
                .toArray()
                .find(function(row) {
                    return (row.dataset.backNumber || '').trim() === stockConfig.focusBackNumber;
                });

            if (!focusedRow) {
                return;
            }

            focusedRow.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            focusedRow.classList.add('stock-flash');

            window.setTimeout(function() {
                focusedRow.classList.remove('stock-flash');
            }, 2200);
        }

        function initStockTable(pane) {
            const $pane = $(pane);
            const $table = $pane.find('table.stock-table');

            if (!$table.length) {
                return null;
            }

            if ($.fn.DataTable.isDataTable($table[0])) {
                const existingTable = $table.DataTable();
                existingTable.columns.adjust();

                return existingTable;
            }

            const line = String($table.data('line') || '');

            return $table.DataTable({
                ajax: {
                    url: stockConfig.dataUrl,
                    data: {
                        line: line
                    },
                    dataSrc: 'data',
                    cache: false,
                },
                autoWidth: false,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                processing: true,
                order: [
                    [1, 'asc']
                ],
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'back_number',
                        render: renderBackNumber
                    },
                    {
                        data: 'part_number',
                        render: renderStockNumber
                    },
                    {
                        data: 'standard',
                        render: renderStockNumber
                    },
                    {
                        data: 'stock_in',
                        render: function(value, type) {
                            return renderStockMovement(value, type, 'in');
                        }
                    },
                    {
                        data: 'stock_out',
                        render: function(value, type) {
                            return renderStockMovement(value, type, 'out');
                        }
                    },
                    {
                        data: 'qty',
                        render: renderStockNumber
                    },
                    {
                        data: null,
                        render: renderGap
                    },
                    {
                        data: 'status',
                        render: renderStatus
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: renderAction
                    }
                ],
                createdRow: function(row, data) {
                    row.dataset.backNumber = data.back_number || '';
                },
                initComplete: function() {
                    const table = this.api();

                    if (stockConfig.focusBackNumber && line === stockConfig.activeLine) {
                        $table.one('draw.dt', function() {
                            window.setTimeout(function() {
                                highlightFocusedRow(table);
                            }, 50);
                        });

                        table.search(stockConfig.focusBackNumber).draw();
                    }
                }
            });
        }

        function getStatusFilter() {
            return $('.stock-status-filter.active').data('status') || '';
        }

        function applyStatusFilter(table) {
            table.column(stockConfig.statusColumn).search(getStatusFilter()).draw();
        }

        function reloadActiveTable() {
            const $table = $('#myTabContent2 .tab-pane.active table.stock-table');

            if ($table.length && $.fn.DataTable.isDataTable($table[0])) {
                $table.DataTable().ajax.reload(null, false);
            }
        }

        function setupUploadModal(inputId, dropZoneId, fileCardId, fileNameId, fileSizeId,
            removeId, hintId, hintOkId, submitId) {
            const input = document.getElementById(inputId);
            const dropZone = document.getElementById(dropZoneId);
            const fileCard = document.getElementById(fileCardId);
            const fileName = document.getElementById(fileNameId);
            const fileSize = document.getElementById(fileSizeId);
            const removeBtn = document.getElementById(removeId);
            const hint = document.getElementById(hintId);
            const hintOk = document.getElementById(hintOkId);
            const submitBtn = document.getElementById(submitId);

            if (!input || !dropZone || !fileCard || !fileName || !fileSize || !removeBtn || !hint || !hintOk ||
                !submitBtn) {
                return;
            }

            function formatBytes(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            function showUploadError(message) {
                if (window.iziToast) {
                    iziToast.error({
                        title: 'File tidak valid',
                        message: escapeHtml(message),
                        position: 'bottomRight',
                        timeout: 6000,
                        class: 'iziToast-danger',
                    });

                    return;
                }

                alert(message);
            }

            function setFile(file) {
                const extension = String(file.name).split('.').pop().toLowerCase();

                if (!['xlsx', 'xls', 'csv'].includes(extension)) {
                    clearFile();
                    showUploadError('Gunakan file .xlsx, .xls, atau .csv.');

                    return false;
                }

                if (file.size > 10 * 1024 * 1024) {
                    clearFile();
                    showUploadError('Ukuran file maksimal 10 MB.');

                    return false;
                }

                fileName.textContent = file.name;
                fileSize.textContent = formatBytes(file.size) + ' - Spreadsheet';
                fileCard.style.display = 'flex';
                hint.style.display = 'none';
                hintOk.style.display = 'flex';
                dropZone.classList.add('dragover');
                submitBtn.disabled = false;

                return true;
            }

            function clearFile() {
                input.value = '';
                fileCard.style.display = 'none';
                hint.style.display = 'flex';
                hintOk.style.display = 'none';
                dropZone.classList.remove('dragover');
                submitBtn.disabled = true;
            }

            input.addEventListener('change', function() {
                if (this.files[0]) {
                    setFile(this.files[0]);
                } else {
                    clearFile();
                }
            });

            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                clearFile();
            });

            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', function() {
                if (!input.files[0]) this.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                const file = e.dataTransfer.files[0];
                if (file && setFile(file)) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                }
            });

            $(dropZone).closest('.modal').on('hidden.bs.modal', clearFile);
        }

        $(function() {
            showFlashMessages();
            initStockTable($('#myTabContent2 .tab-pane.active'));

            $('a[data-toggle="tab"][data-line]').on('shown.bs.tab', function(e) {
                const table = initStockTable($(e.target).attr('href'));

                if (table) {
                    applyStatusFilter(table);
                    table.ajax.reload(null, false);
                }
            });

            $('.stock-status-filter').on('click', function() {
                $('.stock-status-filter').removeClass('active');
                $(this).addClass('active');

                const $table = $('#myTabContent2 .tab-pane.active table.stock-table');

                if ($table.length && $.fn.DataTable.isDataTable($table[0])) {
                    applyStatusFilter($table.DataTable());
                }
            });

            window.setInterval(reloadActiveTable, stockConfig.refreshMs);

            setupUploadModal(
                'stockFileInput', 'stockDropZone', 'stockFileCard',
                'stockFileName', 'stockFileSize', 'stockFileRemove',
                'stockHint', 'stockHintOk', 'stockSubmitBtn'
            );
        });

        $(document).on('click', '.edit-stock', function() {
            const button = this;
            const modal = $('#edit');

            if (!modal.length) {
                return;
            }

            $('#title').empty().append($('<h4>').text(button.dataset.backNumber || '-'));
            $('#internal_part_id').val(button.dataset.id || '');
            $('#active_line_input').val($(button).closest('table.stock-table').data('line') || stockConfig.activeLine || '');
            $('#back_number_input').val(button.dataset.backNumber || '');
            $('#standard_stock').text(button.dataset.standard || '0');
            $('#current_stock').text(button.dataset.qty || '0');
            $('#standard_stock_input').val(button.dataset.standard || '0');
            $('#current_stock_input').val(button.dataset.qty || '0');
            modal.modal('show');
        });

        $(document).on('click', '.stock-history', function() {
            stockHistoryPartId = this.dataset.id || null;
            stockHistoryPage = 1;
            $('#historyBackNumber').text(this.dataset.backNumber || '-');
            $('#historyPartNumber').text('-');
            $('#historyCurrentStock').text('0');
            $('#stockHistoryDateFrom, #stockHistoryDateTo').val('');
            $('#stockHistoryModal').modal('show');
            loadStockHistory(1);
        });

        $('#stockHistoryApply').on('click', function() {
            if (stockHistoryFilterIsValid()) loadStockHistory(1);
        });

        $('#stockHistoryReset').on('click', function() {
            $('#stockHistoryDateFrom, #stockHistoryDateTo').val('');
            loadStockHistory(1);
        });

        $('#stockHistoryDownload').on('click', function() {
            if (!stockHistoryPartId || !stockHistoryFilterIsValid()) return;

            const params = new URLSearchParams(Object.assign({
                internal_part_id: stockHistoryPartId
            }, stockHistoryFilters()));
            window.location.href = stockConfig.transactionsExportUrl + '?' + params.toString();
        });

        $('#stockHistoryExportForm').on('submit', function(event) {
            const from = $('#stockHistoryExportFrom').val();
            const to = $('#stockHistoryExportTo').val();

            if (from && to && from > to) {
                event.preventDefault();
                iziToast.warning({
                    title: 'Filter waktu tidak valid',
                    message: 'Waktu mulai tidak boleh setelah waktu akhir.',
                    position: 'bottomRight',
                    timeout: 4000,
                    class: 'iziToast-warning'
                });
            }
        });

        $('#stockHistoryPrev').on('click', function() {
            if (stockHistoryPage > 1) loadStockHistory(stockHistoryPage - 1);
        });

        $('#stockHistoryNext').on('click', function() {
            loadStockHistory(stockHistoryPage + 1);
        });
    })(jQuery);
</script>
@endpush
