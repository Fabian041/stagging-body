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
        }
    </style>

    <div class="bella-table-card mt-3">
        <div class="bella-table-card-header">
            <div>
                <span class="bella-table-card-title"><i class="fas fa-boxes mr-1"></i>Body Plant Stock Monitoring</span>
                <div class="bella-table-card-subtitle">Monitoring standard stock, current stock, dan status inventory per
                    line.</div>
            </div>
            <div class="stock-header-actions">
                <button type="button" class="act-btn danger" data-toggle="modal" data-target="#partModal">
                    <i class="fas fa-upload"></i> Upload Master Part
                </button>
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
                    @foreach ($lines as $line)
                        <li class="nav-item">
                            <a class="nav-link show @if ($line->line == 'AS711') active @endif" id="home-tab3"
                                data-toggle="tab" href="#{{ $line->line }}" role="tab" aria-controls="home"
                                aria-selected="true">{{ $line->line }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content" id="myTabContent2">
                @foreach ($lines as $line)
                    <div class="tab-pane fade @if ($line->line == 'AS711') active show @endif" id="{{ $line->line }}"
                        role="tabpanel" aria-labelledby="home-tab3">
                        <div class="stock-table-wrap table-responsive">
                            <table class="table table-bordered table-md stock-table" id="stocks-{{ $line->line }}">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Back Number</th>
                                        <th>Standard Stock</th>
                                        <th>Current Stock</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($line->items as $item)
                                        @php
                                            if ($item['qty'] < $item['standard']) {
                                                $color = 'red';
                                                $status = 'Under Stock';
                                                $icon = 'fas fa-arrow-down';
                                            } elseif ($item['qty'] == $item['standard']) {
                                                $color = 'yellow';
                                                $status = 'Low Stock';
                                                $icon = 'fas fa-minus';
                                            } else {
                                                $color = 'green';
                                                $status = 'In Stock';
                                                $icon = 'fas fa-check';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="back-number-badge">{{ $item['back_number'] }}</span>
                                            </td>
                                            <td><span class="stock-number">{{ $item['standard'] }}</span></td>
                                            <td><span class="stock-number">{{ $item['qty'] }}</span></td>
                                            <td>
                                                <span class="bella-badge bella-badge-{{ $color }}">
                                                    <i class="{{ $icon }}"></i> {{ $status }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="act-btn primary edit-stock"
                                                    data-stock="{{ json_encode($item) }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

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
                            <input type="number" class="form-control" name="standard_stock" min="0"
                                placeholder="-">
                        </div>
                        <div class="form-group">
                            <label>New Current Stock</label>
                            <input type="number" class="form-control" name="current_stock" min="0"
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

{{-- modal --}}
<div class="modal fade" tabindex="-1" role="dialog" id="partModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.part.import') }}" method="POST" enctype="multipart/form-data"
                id="partForm">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-database mr-1"></i> Upload Master Part
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <span class="upload-modal-label">Part File</span>

                    <input type="file" id="partFileInput" name="file" accept=".xlsx,.xls"
                        style="display:none">

                    <div class="upload-drop-zone" id="partDropZone"
                        onclick="document.getElementById('partFileInput').click()">
                        <div class="dz-icon-wrap">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p>Drag &amp; drop file di sini, atau
                            <span class="dz-browse">browse</span>
                        </p>
                        <span class="dz-ext">.xlsx, .xls &mdash; maks 10 MB</span>
                    </div>

                    <div class="upload-file-card" id="partFileCard" style="display:none">
                        <div class="ufc-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div class="ufc-meta">
                            <div class="ufc-name" id="partFileName">—</div>
                            <div class="ufc-size" id="partFileSize">—</div>
                        </div>
                        <button type="button" class="ufc-remove" id="partFileRemove" title="Hapus file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="upload-hint" id="partHint">
                        <i class="fas fa-info-circle"></i>
                        <span>Gunakan template standar sebelum upload.</span>
                    </div>
                    <div class="upload-hint success" id="partHintOk" style="display:none">
                        <i class="fas fa-check-circle"></i>
                        <span>File siap diimport.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="act-btn secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="act-btn primary" id="partSubmitBtn" disabled>
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}

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

                    <input type="file" id="stockFileInput" name="file" accept=".xlsx,.xls"
                        style="display:none">

                    <div class="upload-drop-zone" id="stockDropZone"
                        onclick="document.getElementById('stockFileInput').click()">
                        <div class="dz-icon-wrap">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p>Drag &amp; drop file di sini, atau
                            <span class="dz-browse">browse</span>
                        </p>
                        <span class="dz-ext">.xlsx, .xls &mdash; maks 10 MB</span>
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

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('assets/js/apexcharts.js') }}"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="<https://unpkg.com/mqtt/dist/mqtt.min.js>"></script>
<script>
    // chart option
    var lineData = @json($lines);
    // Declare a global object to store chart instances
    var charts = {};

    const flashMessage = {
        error: @json(session('error')),
        success: @json(session('success')),
        warning: @json(session('warning')),
        details: @json(session('import_errors', [])),
        validationErrors: @json($errors->all()),
    };

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

    $(document).ready(function() {
        if (flashMessage.validationErrors.length > 0) {
            iziToast.error({
                title: 'File tidak valid',
                message: formatDetails(flashMessage.validationErrors),
                position: 'bottomRight',
                timeout: 9000,
                class: 'iziToast-danger', // ← tambahkan ini
            });
        }

        if (flashMessage.error) {
            iziToast.error({
                title: 'Gagal',
                message: escapeHtml(flashMessage.error) + formatDetails(flashMessage.details),
                position: 'bottomRight',
                timeout: 10000,
                class: 'iziToast-danger', // ← tambahkan ini
            });
        }

        if (flashMessage.warning) {
            iziToast.warning({
                title: 'Perhatian',
                message: escapeHtml(flashMessage.warning) + formatDetails(flashMessage.details),
                position: 'bottomRight',
                timeout: 10000,
                class: 'iziToast-warning', // ← tambahkan ini
            });
        }

        if (flashMessage.success) {
            iziToast.success({
                title: 'Berhasil',
                message: escapeHtml(flashMessage.success),
                position: 'bottomRight',
                timeout: 7000,
                class: 'iziToast-success', // ← tambahkan ini
            });
        }

        @if (count($lines))
            $('#stocks-{{ $lines[0]->line }}').DataTable();
        @endif

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href");
            var tableId = target + ' table';

            if (!$.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable();
            }
        });

        lineData.forEach(function(data) {
            if (getElement(data.line)) {
                chart(data.line).render();
            }
        });

        lineData.forEach(function(item) {
            updateChart(item.line, item.items);
        });
    });

    // chart option
    function generateOptions(line) {
        return {
            chart: {
                height: 300,
                columnWidth: 500,
                type: 'bar',
                animations: {
                    enabled: true,
                    easing: 'easein',
                    speed: 800, // Set animation to start from bottom
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    animate: {
                        from: 'bottom'
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    barHeight: '50%',
                    borderRadius: 5,
                    borderRadiusOnAllStackedSeries: false,
                    columnWidth: '60%'
                },
            },
            colors: '#696CFF',
            noData: {
                text: 'No data at line ' + line
            },
            series: [{
                name: 'Quantity',
                data: []
            }],
        };
    }


    // initialize chart
    function chart(line) {
        var options = generateOptions(line);
        var chartInstance = new ApexCharts(document.querySelector(`#${line}-chart`), options);
        charts[line] = chartInstance; // Store the chart instance in the object
        return chartInstance;
    }

    function updateChart(line, data) {
        var chartData = data.map(function(item) {
            var color;

            // Set different colors based on qty value
            if (item.qty <= 200) {
                color = '#ff0000'; // Red color
            } else if (item.qty > 200) {
                color = '#20c997'; // Green color
            } else {
                color = '#0000ff'; // Blue color
            }

            return {
                x: item.back_number,
                y: item.qty,
                fillColor: color // Add the fillColor property with the corresponding color
            };
        });

        charts[line].updateOptions({
            series: [{
                name: 'Stock',
                data: chartData
            }]
        });
    }

    function getElement(line) {
        var el = document.querySelector(`#${line}-chart`);
        return el;
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

        function formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function setFile(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size) + ' · Excel Spreadsheet';
            fileCard.style.display = 'flex';
            hint.style.display = 'none';
            hintOk.style.display = 'flex';
            dropZone.classList.add('dragover');
            submitBtn.disabled = false;
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
            if (this.files[0]) setFile(this.files[0]);
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
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                setFile(file);
            }
        });

        // Reset saat modal ditutup
        $(dropZone).closest('.modal').on('hidden.bs.modal', clearFile);
    }

    $(document).on('click', '.edit-stock', function() {
        const data = $(this).data('stock');
        let modal = $('#edit').modal('show');
        if (modal.length) {
            $('#title').html(`<h4>${data.back_number}</h4>`);
            $('#internal_part_id').val(data.id);
            $('#standard_stock').html(data.standard);
            $('#current_stock').html(data.qty);
            modal.modal('show');
        } else {
            console.error('Modal not found for chart ID:', data.id);
        }
    });

    $(document).ready(function() {
        // Initialize DataTable for the first tab
        @if (count($lines))
            $('#stocks-{{ $lines[0]->line }}').DataTable();
        @endif

        setupUploadModal(
            'partFileInput', 'partDropZone', 'partFileCard',
            'partFileName', 'partFileSize', 'partFileRemove',
            'partHint', 'partHintOk', 'partSubmitBtn'
        );
        setupUploadModal(
            'stockFileInput', 'stockDropZone', 'stockFileCard',
            'stockFileName', 'stockFileSize', 'stockFileRemove',
            'stockHint', 'stockHintOk', 'stockSubmitBtn'
        );

        // Initialize DataTable on tab show event
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href"); // Get the target tab ID
            var tableId = target + ' table'; // ID of the table inside the tab
            if (!$.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable(); // Initialize DataTable if not already initialized
            }
        });

        // render chart
        lineData.forEach(function(data) {
            if (getElement(data.line)) {
                chart(data.line).render();
            }
        });

        lineData.forEach(function(item) {
            updateChart(item.line, item.items);
        });

    });
    Paho.MQTT.DEBUG = true;

    let client;

    function connectMQTT() {
        // Create an MQTT client instance
        clientId = "client_" + Math.random().toString(16).substr(2, 8);
        client = new Paho.MQTT.Client("172.18.3.70", Number(8083), clientId);

        // Set callback handlers
        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        // Connect the client, providing an onConnect callback
        client.connect({
            onSuccess: onConnect,
            onFailure: onFailure,
            // userName: "fabian",
            // password: "1234"
        });
    }

    function onConnect() {
        console.log('Connected');
        client.subscribe("prod/quantity");
    }

    function onFailure(error) {
        console.error('Failed to connect to MQTT broker:', error.errorMessage);
        console.log(error);
        // Implement your own logic for handling connection failure, e.g., retry after a certain interval
        setTimeout(connectMQTT, 5000);
    }

    function onConnectionLost(responseObject) {
        if (responseObject.errorCode !== 0) {
            console.log("Connection Lost: " + responseObject.errorMessage);
            // Implement your own logic for handling connection loss, e.g., retry after a certain interval
            setTimeout(connectMQTT, 5000);
        }
    }

    function onMessageArrived(data) {
        // update chart
        let items = JSON.parse(data.payloadString);
        items.forEach(function(item) {
            updateChart(item.line, item.items);
        });
    }

    connectMQTT();
</script>
