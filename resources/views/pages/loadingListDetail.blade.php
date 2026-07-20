@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== INFO CARD ===== */
        .bella-info-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-top: 16px;
            margin-bottom: 16px;
        }

        .bella-info-card-header {
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: var(--bg);
        }

        .bella-info-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--navy), var(--sky));
        }

        .bella-info-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        /* Info grid */
        .bella-info-card-body {
            padding: 20px;
        }

        .bella-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .bella-info-field {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: box-shadow .15s, border-color .15s;
        }

        .bella-info-field:hover {
            border-color: #c7d2fe;
            box-shadow: 0 2px 10px rgba(41, 71, 149, .08);
        }

        .bella-info-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bella-info-icon i {
            font-size: 13px;
            color: var(--primary);
        }

        .bella-info-text {
            min-width: 0;
        }

        .bella-info-field label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text-muted);
            margin-bottom: 3px;
            white-space: nowrap;
        }

        .bella-info-field .info-val {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--navy);
            word-break: break-word;
            line-height: 1.2;
        }

        .prep-context-field.is-late {
            border-color: #fecaca;
            background: #fff5f5;
        }

        .prep-context-field.is-safe {
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .prep-status-chip,
        .prep-delta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            width: fit-content;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 10.5px;
            font-weight: 850;
            line-height: 1.25;
            white-space: nowrap;
        }

        .prep-status-chip {
            padding: 4px 7px;
        }

        .prep-delta-chip {
            margin-top: 4px;
            padding: 3px 6px;
        }

        .prep-status-chip.is-safe,
        .prep-delta-chip.is-safe {
            border-color: #bbf7d0;
            background: #dcfce7;
            color: #166534;
        }

        .prep-status-chip.is-late,
        .prep-delta-chip.is-late {
            border-color: #fecaca;
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ===== TABLE CARD ===== */
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 8px;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        /* ===== DATATABLE OVERRIDES ===== */
        .bella-table-card .dataTables_wrapper {
            padding: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length,
        .bella-table-card .dataTables_wrapper .dataTables_filter {
            padding: 10px 16px;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length label,
        .bella-table-card .dataTables_wrapper .dataTables_filter label {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length select,
        .bella-table-card .dataTables_wrapper .dataTables_filter input {
            height: 30px;
            border: 1px solid var(--border) !important;
            border-radius: 4px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            padding: 0 8px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
        }

        #loadingList {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12.5px !important;
        }

        #loadingList thead th {
            text-align: center !important;
            padding: 9px 12px !important;
            color: var(--text-muted) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
            border-top: none !important;
            white-space: nowrap !important;
        }

        #loadingList tbody td {
            text-align: center !important;
            padding: 9px 12px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
        }

        #loadingList tbody tr:last-child td {
            border-bottom: none !important;
        }

        #loadingList tbody tr:hover td {
            background: var(--bg) !important;
        }

        /* Child row (EDCL details) */
        #loadingList tbody tr.shown td {
            background: #EEF2FF !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate {
            padding: 10px 16px;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button {
            min-width: 30px !important;
            height: 30px !important;
            border: 1px solid var(--border) !important;
            border-radius: 4px !important;
            background: var(--card) !important;
            color: var(--text-muted) !important;
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
            background: var(--bg) !important;
            color: var(--text) !important;
            border-color: var(--border) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary) !important;
            color: #fff !important;
            border-color: var(--primary) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: .4 !important;
            cursor: not-allowed !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_info {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ===== EDCL child table ===== */
        .edcl-child-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 0;
        }

        .edcl-child-table thead th {
            background: #EEF2FF !important;
            color: var(--navy) !important;
            font-size: 10px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            padding: 7px 10px !important;
            border-bottom: 1px solid var(--border) !important;
            text-align: center !important;
        }

        .edcl-child-table tbody td {
            padding: 7px 10px !important;
            border-bottom: 1px solid var(--border) !important;
            text-align: center !important;
            vertical-align: middle !important;
            color: var(--text) !important;
        }

        .edcl-child-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        /* ===== INLINE EDIT (total scan) ===== */
        .edit-input {
            display: none;
            width: 70px;
            height: 28px;
            border: 1px solid var(--sky);
            border-radius: 4px;
            background: #fff;
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12.5px;
            text-align: center;
            padding: 0 6px;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
        }

        /* ===== COMPARE MODAL ===== */
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

        /* Compare toolbar */
        .compare-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .compare-meta {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .compare-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .compare-download-btn {
            height: 32px;
            border: 1px solid #bfdbfe;
            border-radius: var(--r-sm);
            background: #eff6ff;
            color: #1d4ed8;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11.5px;
            font-weight: 800;
            padding: 0 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            white-space: nowrap;
            transition: .15s;
        }

        .compare-download-btn:hover:not(:disabled) {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .compare-download-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .compare-search-wrap {
            display: flex;
            align-items: center;
            gap: 7px;
            height: 32px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            padding: 0 10px;
        }

        .compare-search-wrap i {
            font-size: 11px;
            color: var(--text-muted);
        }

        .compare-search-wrap input {
            border: 0;
            background: transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            color: var(--text);
            outline: none;
            width: 180px;
        }

        .compare-filter-select {
            height: 32px;
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            background: var(--card);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            padding: 0 10px;
            outline: none;
            min-width: 180px;
        }

        .compare-filter-select:focus {
            border-color: var(--sky);
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
        }

        /* Compare table container */
        .compare-table-wrap {
            max-height: 480px;
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: var(--r);
            background: var(--card);
        }

        .compare-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .compare-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bg) !important;
            color: var(--text-muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            text-align: center;
            white-space: nowrap;
        }

        .compare-table thead th:nth-child(2) {
            text-align: left;
        }

        .compare-table thead th:nth-child(3),
        .compare-table thead th:nth-child(4) {
            text-align: left;
        }

        .compare-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
            color: var(--text);
            font-size: 12px;
        }

        .compare-table tbody tr:last-child td {
            border-bottom: none;
        }

        .compare-table tbody tr:hover td {
            background: var(--bg);
        }

        .compare-note {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .compare-prep-context {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 12px;
        }

        .compare-prep-item {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--card);
            padding: 9px 10px;
        }

        .compare-prep-item span {
            display: block;
            color: var(--text-muted);
            font-size: 9.5px;
            font-weight: 850;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .compare-prep-item strong {
            display: block;
            color: var(--navy);
            font-size: 12.5px;
            font-weight: 900;
        }

        .compare-table tbody tr.is-prep-late-row td {
            background: #fff5f5;
        }

        /* Serial mono */
        .serial-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            font-size: 12px;
            color: var(--navy);
            font-weight: 600;
        }

        /* Compare mutation rows */
        .mut-row {
            padding: 4px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 11.5px;
            line-height: 1.4;
        }

        .mut-row:last-child {
            border-bottom: none;
        }



        /* ===== ENHANCED DETAIL DASHBOARD ===== */
        .detail-hero-card {
            margin-top: 14px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background:
                radial-gradient(circle at top left, rgba(0, 151, 216, .16), transparent 34%),
                linear-gradient(135deg, var(--navy), #1f3b82 55%, var(--primary));
            box-shadow: var(--shadow-md);
            color: #fff;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            overflow: hidden;
            position: relative;
        }

        .detail-hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            right: -70px;
            top: -80px;
            background: rgba(255, 255, 255, .10);
        }

        .detail-hero-kicker {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            opacity: .78;
            margin-bottom: 5px;
        }

        .detail-hero-title {
            font-size: 22px;
            line-height: 1.2;
            font-weight: 900;
            margin: 0;
        }

        .detail-hero-subtitle {
            margin-top: 7px;
            font-size: 12.5px;
            opacity: .82;
            max-width: 680px;
        }

        .detail-hero-pill {
            min-width: 160px;
            border: 1px solid rgba(255, 255, 255, .22);
            background: rgba(255, 255, 255, .12);
            border-radius: 14px;
            padding: 12px 14px;
            text-align: right;
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
        }

        .detail-hero-pill span {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            opacity: .74;
            font-weight: 800;
        }

        .detail-hero-pill strong {
            display: block;
            margin-top: 4px;
            font-size: 16px;
            font-weight: 900;
        }

        .detail-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin: 14px 0 16px;
        }

        .detail-summary-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 15px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .detail-summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-summary-icon.success {
            background: #DCFCE7;
            color: var(--success);
        }

        .detail-summary-icon.warning {
            background: #FEF3C7;
            color: var(--warning);
        }

        .detail-summary-icon.info {
            background: #E0F2FE;
            color: var(--sky);
        }

        .detail-summary-label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .detail-summary-value {
            display: block;
            font-size: 19px;
            font-weight: 900;
            color: var(--navy);
            line-height: 1.1;
        }

        .table-head-content {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .table-head-subtitle {
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0;
        }

        .part-code-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            max-width: 190px;
            padding: 5px 9px;
            border-radius: 999px;
            background: #EEF2FF;
            border: 1px solid #DDE5FF;
            color: var(--navy);
            font-weight: 800;
            font-size: 11.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .qty-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            padding: 5px 8px;
            border-radius: 999px;
            background: var(--bg);
            border: 1px solid var(--border);
            font-weight: 900;
            color: var(--navy);
        }

        .scan-progress-cell {
            min-width: 120px;
        }

        .scan-progress-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .scan-progress-top .actual {
            color: var(--navy);
            font-size: 13px;
        }

        .mini-progress {
            width: 100%;
            height: 7px;
            border-radius: 999px;
            background: #E5E7EB;
            overflow: hidden;
        }

        .mini-progress>span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--sky), var(--primary));
        }

        .mini-progress.done>span {
            background: linear-gradient(90deg, #16A34A, #22C55E);
        }

        .mini-progress.over>span {
            background: linear-gradient(90deg, #F59E0B, #EF4444);
        }

        .edcl-detail-panel {
            padding: 12px;
            background: #F8FAFC;
            border: 1px solid #E5E7EB;
            border-radius: 14px;
            margin: 8px 10px 12px;
        }

        .edcl-detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .edcl-title-block strong {
            display: block;
            color: var(--navy);
            font-size: 13px;
            font-weight: 900;
        }

        .edcl-title-block span {
            display: block;
            color: var(--text-muted);
            font-size: 11.5px;
            margin-top: 2px;
        }

        .edcl-mini-stats {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .edcl-stat {
            border: 1px solid var(--border);
            background: var(--card);
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
        }

        .edcl-stat strong {
            color: var(--navy);
        }

        .edcl-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .edcl-search-wrap {
            display: flex;
            align-items: center;
            gap: 7px;
            height: 34px;
            min-width: 280px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0 11px;
            background: var(--card);
        }

        .edcl-search-wrap i {
            color: var(--text-muted);
            font-size: 11px;
        }

        .edcl-search-wrap input {
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            width: 100%;
            color: var(--text);
        }

        .edcl-scroll-wrap {
            max-height: 360px;
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--card);
        }

        .edcl-child-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .serial-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            background: #EEF2FF;
            color: var(--navy);
            font-weight: 900;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            font-size: 11.5px;
        }

        .row-hidden-by-search {
            display: none !important;
        }

        @media (max-width: 768px) {
            .bella-info-grid {
                grid-template-columns: 1fr 1fr;
            }

            .compare-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .compare-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .compare-download-btn,
            .compare-search-wrap,
            .compare-filter-select {
                width: 100%;
            }

            .compare-prep-context {
                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 992px) {
            .detail-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .detail-hero-card {
                align-items: flex-start;
                flex-direction: column;
            }

            .detail-hero-pill {
                text-align: left;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .detail-summary-grid {
                grid-template-columns: 1fr;
            }

            .bella-info-grid {
                grid-template-columns: 1fr;
            }

            .detail-hero-title {
                font-size: 18px;
            }

            .edcl-search-wrap {
                min-width: 100%;
            }

            .table-head-content {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    @php
        $prepContext = $prepContext ?? [];
        $prepStatus = $prepContext['status'] ?? 'unknown';
        $prepStatusClass = in_array($prepStatus, ['safe', 'late'], true) ? 'is-' . $prepStatus : '';
    @endphp

    {{-- ===== INFO CARD ===== --}}
    <div class="bella-info-card">
        <div class="bella-info-card-header">
            <span class="bella-info-card-title">Loading List Detail</span>
        </div>
        <div class="bella-info-card-body">
            <div class="bella-info-grid">
                <div class="bella-info-field">
                    <div class="bella-info-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="bella-info-text">
                        <label>Loading List No.</label>
                        <span class="info-val">{{ $loadingListDetail->number }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon"><i class="fas fa-barcode"></i></div>
                    <div class="bella-info-text">
                        <label>PDS Number</label>
                        <span class="info-val">{{ $loadingListDetail->pds_number }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon"><i class="fas fa-building"></i></div>
                    <div class="bella-info-text">
                        <label>Customer</label>
                        <span class="info-val">{{ optional($loadingListDetail->customer)->name ?? '-' }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon" style="background:#DCFCE7;"><i class="fas fa-calendar-check"
                            style="color:var(--success);"></i></div>
                    <div class="bella-info-text">
                        <label>Delivery Date</label>
                        <span class="info-val">{{ $loadingListDetail->delivery_date }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon" style="background:#FEF3C7;"><i class="fas fa-shipping-fast"
                            style="color:var(--warning);"></i></div>
                    <div class="bella-info-text">
                        <label>Shipping Date</label>
                        <span class="info-val">{{ $loadingListDetail->shipping_date }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon" style="background:#EEF2FF;"><i class="fas fa-redo-alt"
                            style="color:var(--navy);"></i></div>
                    <div class="bella-info-text">
                        <label>Cycle</label>
                        <span class="info-val">{{ $loadingListDetail->cycle }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon" style="background:#E0F2FE;"><i class="fas fa-hourglass-half"
                            style="color:var(--sky);"></i></div>
                    <div class="bella-info-text">
                        <label>Preparation Deadline</label>
                        <span class="info-val">{{ $prepContext['deadline_label'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="bella-info-field">
                    <div class="bella-info-icon" style="background:#EEF2FF;"><i class="fas fa-clock"
                            style="color:var(--navy);"></i></div>
                    <div class="bella-info-text">
                        <label>Pulling Time</label>
                        <span class="info-val">{{ $prepContext['pulling_label'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="bella-info-field prep-context-field {{ $prepStatusClass }}">
                    <div class="bella-info-icon" style="background:#F8FAFC;"><i class="fas fa-stopwatch"
                            style="color:var(--text-muted);"></i></div>
                    <div class="bella-info-text">
                        <label>Delta Preparation</label>
                        <span class="info-val">
                            <span class="prep-status-chip {{ $prepStatusClass }}">
                                {{ $prepContext['delta_label'] ?? '-' }}
                                {{ $prepContext['status_label'] ?? '' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SUMMARY ===== --}}
    <div class="detail-summary-grid">
        <div class="detail-summary-card">
            <div class="detail-summary-icon"><i class="fas fa-layer-group"></i></div>
            <div>
                <span class="detail-summary-label">Total Item</span>
                <span class="detail-summary-value" id="sumRows">0</span>
            </div>
        </div>
        <div class="detail-summary-card">
            <div class="detail-summary-icon info"><i class="fas fa-boxes"></i></div>
            <div>
                <span class="detail-summary-label">Kanban Qty</span>
                <span class="detail-summary-value" id="sumTarget">0</span>
            </div>
        </div>
        <div class="detail-summary-card">
            <div class="detail-summary-icon success"><i class="fas fa-barcode"></i></div>
            <div>
                <span class="detail-summary-label">Total Scan</span>
                <span class="detail-summary-value" id="sumActual">0</span>
            </div>
        </div>
        <div class="detail-summary-card">
            <div class="detail-summary-icon warning"><i class="fas fa-chart-line"></i></div>
            <div>
                <span class="detail-summary-label">Progress</span>
                <span class="detail-summary-value" id="sumProgress">0%</span>
            </div>
        </div>
    </div>

    {{-- ===== DETAILS TABLE CARD ===== --}}
    <div class="bella-table-card">
        <div class="bella-table-card-header">
            <div class="table-head-content">
                <span class="bella-table-card-title">Kanban Details</span>
                <span class="table-head-subtitle" id="tableLastSync">Waiting data...</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table bella-table" id="loadingList" style="width: 100%">
                <thead>
                    <tr>
                        <th class="text-center" style="width:120px;">EDCL</th>
                        <th class="text-center" style="width:180px;">Kanban Details</th>
                        <th class="text-center">Customer Part No.</th>
                        <th class="text-center">Internal Part No.</th>
                        <th class="text-center">Customer Back No.</th>
                        <th class="text-center">Internal Back No.</th>
                        <th class="text-center">Kanban Qty</th>
                        <th class="text-center">Total Scan</th>
                        <th class="text-center" style="width:100px;"></th>
                    </tr>
                </thead>
                <tbody class="text-center"></tbody>
            </table>
        </div>
    </div>
@endsection

{{-- ===== MODAL COMPARE ===== --}}
<div class="modal fade" id="compareModal" tabindex="-1" role="dialog" aria-labelledby="compareModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compareModalLabel">
                    <i class="fas fa-code-branch" style="margin-right:7px; color:var(--sky);"></i>
                    Compare Pulling vs Production
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="compare-toolbar">
                    <span class="compare-meta" id="compareMeta"></span>
                    <div class="compare-controls">
                        <button type="button" class="compare-download-btn" id="compareDownload" disabled>
                            <i class="fas fa-file-excel"></i>
                            Download
                        </button>
                        <div class="compare-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="compareSearch" placeholder="Cari serial / tanggal / qty...">
                        </div>
                        <select id="compareFilter" class="compare-filter-select">
                            <option value="all">All</option>
                            <option value="match">Match</option>
                            <option value="missing_prod">Missing Production</option>
                            <option value="missing_pull">Missing Pulling</option>
                            <option value="late_pull">Pulling Telat</option>
                        </select>
                    </div>
                </div>

                <div class="compare-prep-context" id="comparePrepContext">
                    <div class="compare-prep-item">
                        <span>Preparation Deadline</span>
                        <strong id="comparePrepDeadline">{{ $prepContext['deadline_label'] ?? '-' }}</strong>
                    </div>
                    <div class="compare-prep-item">
                        <span>Latest Pulling</span>
                        <strong id="comparePrepPulling">{{ $prepContext['pulling_label'] ?? '-' }}</strong>
                    </div>
                    <div class="compare-prep-item">
                        <span>Delta</span>
                        <strong>
                            <span class="prep-status-chip {{ $prepStatusClass }}" id="comparePrepDelta">
                                {{ $prepContext['delta_label'] ?? '-' }}
                                {{ $prepContext['status_label'] ?? '' }}
                            </span>
                        </strong>
                    </div>
                </div>

                <div class="compare-table-wrap">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th style="width:190px;">Serial</th>
                                <th>Production</th>
                                <th>Pulling</th>
                                <th style="width:160px;">Status</th>
                                <th style="width:100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="compareTbody"></tbody>
                    </table>
                </div>

                <p class="compare-note">
                    <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                    Delta dihitung dari waktu pulling dikurangi Preparation Deadline. "Missing Production" artinya serial ada
                    di pulling (checkout), tapi supply sebelumnya tidak ketemu.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="bella-btn bella-btn-secondary bella-btn-sm"
                    data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const loadingList = "{{ $loadingListId }}";
        const loadingListNumber = @json($loadingListDetail->number ?? 'loading-list');
        const pdsNumber = @json($loadingListDetail->pds_number ?? 'pds');
        const pagePrepContext = @json($prepContext);
        const requestOptions = {
            method: 'GET',
            headers: {
                "Content-type": "application/json"
            }
        };


        function toNumber(v) {
            const n = parseFloat(String(v ?? 0).replace(/[^0-9.-]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function fmtNum(v) {
            return new Intl.NumberFormat('id-ID').format(toNumber(v));
        }

        function escapeHtml(value) {
            return String(value ?? '-')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Beberapa kolom dari API lama sudah mengirim HTML seperti:
        // <span class="customerPart">...</span> / <span class="backNumber">...</span>.
        // Helper ini mengambil isi teksnya saja, lalu kita bungkus ulang dengan style baru.
        function plainCellValue(value) {
            if (value === null || value === undefined || value === '') return '-';

            const str = String(value).trim();
            if (str.indexOf('<') !== -1 && str.indexOf('>') !== -1) {
                const tmp = document.createElement('div');
                tmp.innerHTML = str;
                const text = (tmp.textContent || tmp.innerText || '').trim();
                return text || '-';
            }

            return str;
        }

        function updateSummary(rows) {
            rows = rows || [];
            const totalRows = rows.length;
            const totalTarget = rows.reduce((sum, row) => sum + toNumber(row.kbn_qty), 0);
            const totalActual = rows.reduce((sum, row) => sum + toNumber(row.actual_kbn_qty), 0);
            const progress = totalTarget > 0 ? Math.round((totalActual / totalTarget) * 100) : 0;

            $('#sumRows').text(fmtNum(totalRows));
            $('#sumTarget').text(fmtNum(totalTarget));
            $('#sumActual').text(fmtNum(totalActual));
            $('#sumProgress').text(`${progress}%`);
            $('#tableLastSync').html(
                `<i class="fas fa-sync-alt" style="margin-right:5px;"></i>Last sync: ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`
            );
        }

        function renderProgress(actual, target) {
            actual = toNumber(actual);
            target = toNumber(target);
            const pct = target > 0 ? Math.min(100, Math.round((actual / target) * 100)) : 0;
            const cls = target > 0 && actual > target ? 'over' : (target > 0 && actual >= target ? 'done' : '');
            return `
                <div class="scan-progress-cell">
                    <div class="scan-progress-top">
                        <span class="actual">${fmtNum(actual)}</span>
                        <span>/ ${fmtNum(target)}</span>
                    </div>
                    <input type="number" min="0" class="editActual edit-input" value="${actual}" style="display:none;">
                    <div class="mini-progress ${cls}" title="${pct}%">
                        <span style="width:${pct}%"></span>
                    </div>
                </div>
            `;
        }

        /* =========================================================
         * HELPERS
         * ======================================================= */
        function isMaskedSerial(serial) {
            if (!serial) return true;
            const s = String(serial).trim().toLowerCase();
            if (['xxxx', 'xxx', 'xx', 'x', '*****', '****', '***', '**', '*', '----', '---', '--', '-']
                .includes(s)) return true;
            if (/^(x{2,}|\*{2,}|\-{2,})$/i.test(s)) return true;
            return false;
        }

        function prepDeltaClass(status) {
            if (status === 'safe') return 'is-safe';
            if (status === 'late') return 'is-late';
            return '';
        }

        function prepDeltaText(item) {
            if (!item) return '';
            const label = item.prepDeltaLabel || item.prep_delta_label || '';
            const status = item.prepStatusLabel || item.prep_status_label || '';
            if (!label || label === '-') return '';
            return `${label} ${status}`.trim();
        }

        function renderMutationRows(items, showPrepDelta = false) {
            if (!items || !items.length) return '';

            return items.map(item => {
                const delta = showPrepDelta ? prepDeltaText(item) : '';
                const deltaHtml = delta ?
                    `<span class="prep-delta-chip ${prepDeltaClass(item.prepStatus)}">${escapeHtml(delta)}</span>` :
                    '';

                return `<div class="mut-row">${escapeHtml(item.text)}${deltaHtml}</div>`;
            }).join('');
        }

        function updateComparePrepContext(ctx) {
            ctx = ctx || pagePrepContext || {};
            compareCtx.prepContext = ctx;
            const statusClass = prepDeltaClass(ctx.status);
            const deltaText = `${ctx.delta_label || '-'} ${ctx.status_label || ''}`.trim();

            $('#comparePrepDeadline').text(ctx.deadline_label || '-');
            $('#comparePrepPulling').text(ctx.pulling_label || '-');
            $('#comparePrepDelta')
                .removeClass('is-safe is-late')
                .addClass(statusClass)
                .text(deltaText || '-');
        }

        function mutationItem(item) {
            const serial = String(item.serial_number || '').trim();
            if (!serial || isMaskedSerial(serial)) return null;
            const deltaText = prepDeltaText(item);

            return {
                mid: item.id || '',
                serial,
                text: `[${serial}] - [${item.date || 'N/A'}] (qty: ${item.qty || 0})`,
                prepStatus: item.prep_status || 'unknown',
                prepStatusLabel: item.prep_status_label || '',
                prepDeltaLabel: item.prep_delta_label || '',
                deltaText
            };
        }

        function groupBySerial(items) {
            const map = new Map();
            items.forEach(it => {
                if (!map.has(it.serial)) map.set(it.serial, []);
                map.get(it.serial).push(it);
            });
            return map;
        }

        /* =========================================================
         * COMPARE MODAL
         * ======================================================= */
        let compareData = [];
        let compareCtx = {
            detailId: null,
            partLabel: '-',
            prepContext: pagePrepContext || {}
        };

        function statusBadgeClass(status) {
            if (status === 'MATCH') return 'bella-badge bella-badge-green';
            if (status === 'MISSING_PROD') return 'bella-badge bella-badge-red';
            return 'bella-badge bella-badge-amber';
        }

        function statusLabel(status) {
            if (status === 'MATCH') return 'Match';
            if (status === 'MISSING_PROD') return 'Missing Production';
            return 'Missing Pulling';
        }

        function compareHasLatePulling(item) {
            return (item.pullItems || []).some(x => x.prepStatus === 'late');
        }

        function filteredCompareRows(filterText = '', filterMode = 'all') {
            const q = (filterText || '').toLowerCase();

            return compareData.filter(item => {
                const prodText = (item.prodItems || []).map(x => x.text).join(' ');
                const pullText = (item.pullItems || []).map(x => `${x.text} ${x.deltaText || ''}`).join(' ');
                const joined = `${item.serial} ${prodText} ${pullText}`.toLowerCase();
                const hasLatePulling = compareHasLatePulling(item);

                if (q && !joined.includes(q)) return false;
                if (filterMode === 'match' && item.status !== 'MATCH') return false;
                if (filterMode === 'missing_prod' && item.status !== 'MISSING_PROD') return false;
                if (filterMode === 'missing_pull' && item.status !== 'MISSING_PULL') return false;
                if (filterMode === 'late_pull' && !hasLatePulling) return false;

                return true;
            });
        }

        function rebuildCompareRows(filterText = '', filterMode = 'all') {
            const tbody = $('#compareTbody');
            const rows = filteredCompareRows(filterText, filterMode);
            tbody.empty();
            let shown = 0;

            rows.forEach(item => {
                const hasLatePulling = compareHasLatePulling(item);
                shown++;

                const prodHtml = (item.prodItems && item.prodItems.length) ?
                    renderMutationRows(item.prodItems) :
                    '<span style="color:var(--danger);font-size:11px;">N/A</span>';

                const pullHtml = (item.pullItems && item.pullItems.length) ?
                    renderMutationRows(item.pullItems, true) :
                    '<span style="color:var(--danger);font-size:11px;">N/A</span>';

                const actionHtml = (item.pullItems && item.pullItems.length) ?
                    item.pullItems.map(x => {
                        if (!x.mid)
                            return `<div class="mut-row"><button class="act-btn" disabled style="opacity:.4;">Delete</button></div>`;
                        return `<div class="mut-row">
                            <button class="act-btn danger btn-del-pulling-mutation"
                                data-mid="${x.mid}" data-serial="${escapeHtml(item.serial)}">Delete</button>
                        </div>`;
                    }).join('') :
                    '';

                tbody.append(`
                    <tr class="${hasLatePulling ? 'is-prep-late-row' : ''}">
                        <td style="text-align:center;">${shown}</td>
                        <td><span class="serial-mono">${item.serial}</span></td>
                        <td>${prodHtml}</td>
                        <td>${pullHtml}</td>
                        <td style="text-align:center;"><span class="${statusBadgeClass(item.status)}">${statusLabel(item.status)}</span></td>
                        <td style="text-align:center;">${actionHtml}</td>
                    </tr>
                `);
            });

            if (shown === 0) {
                tbody.html(
                    `<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);font-size:12px;">Tidak ada data.</td></tr>`
                );
            }

            $('#compareMeta').text(`Shown: ${shown} / Total serial: ${compareData.length}`);
            $('#compareDownload').prop('disabled', shown === 0);
        }

        function csvMutationText(items) {
            return (items || [])
                .map(item => [item.text, item.deltaText].filter(Boolean).join(' '))
                .join(' | ');
        }

        function safeFilePart(value) {
            return String(value || 'compare')
                .trim()
                .replace(/[^a-z0-9._-]+/gi, '-')
                .replace(/^-+|-+$/g, '') || 'compare';
        }

        function downloadCompareTable() {
            const rows = filteredCompareRows($('#compareSearch').val(), $('#compareFilter').val());
            if (!rows.length) {
                notif('error', 'Tidak ada data compare untuk didownload.');
                return;
            }

            const prep = compareCtx.prepContext || {};
            const headers = ['No', 'Serial', 'Production', 'Pulling', 'Status', 'Pulling Telat Preparation'];

            const bodyRows = rows.map((item, index) => `
                <tr>
                    <td style="text-align:center;">${index + 1}</td>
                    <td>${escapeHtml(item.serial)}</td>
                    <td>${escapeHtml(csvMutationText(item.prodItems))}</td>
                    <td>${escapeHtml(csvMutationText(item.pullItems))}</td>
                    <td>${escapeHtml(statusLabel(item.status))}</td>
                    <td style="text-align:center;">${compareHasLatePulling(item) ? 'Ya' : 'Tidak'}</td>
                </tr>`).join('');

            const deltaText = `${prep.delta_label || '-'} ${prep.status_label || ''}`.trim();
            const html =
                `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                <head><meta charset="utf-8">
                <style>
                    body { font-family: Arial, Helvetica, sans-serif; color: #1a2340; }
                    h3 { margin: 0 0 4px; }
                    .meta { font-size: 12px; color: #555; margin: 2px 0; }
                    table { border-collapse: collapse; font-size: 12px; margin-top: 10px; }
                    th, td { border: 1px solid #b7c0d0; padding: 6px 9px; vertical-align: top; text-align: left; }
                    th { background: #1f3b82; color: #ffffff; font-weight: bold; text-align: center; }
                    tr:nth-child(even) td { background: #f4f7fb; }
                </style></head>
                <body>
                    <h3>Compare Pulling vs Production</h3>
                    <div class="meta">Loading List: ${escapeHtml(loadingListNumber)} &nbsp;|&nbsp; PDS: ${escapeHtml(pdsNumber)} &nbsp;|&nbsp; Part: ${escapeHtml(compareCtx.partLabel)}</div>
                    <div class="meta">Preparation Deadline: ${escapeHtml(prep.deadline_label || '-')} &nbsp;|&nbsp; Latest Pulling: ${escapeHtml(prep.pulling_label || '-')} &nbsp;|&nbsp; Delta: ${escapeHtml(deltaText || '-')}</div>
                    <table>
                        <thead><tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead>
                        <tbody>${bodyRows}</tbody>
                    </table>
                </body></html>`;

            const blob = new Blob(['\ufeff' + html], {
                type: 'application/vnd.ms-excel;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `compare-kanban-${safeFilePart(loadingListNumber)}-${safeFilePart(compareCtx.partLabel)}.xls`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        /* =========================================================
         * DATATABLE
         * ======================================================= */
        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: true,
            serverSide: false,
            ajax: {
                url: `{{ url('dashboard/getLoadingListDetail') }}` + '/' + loadingList,
                dataType: 'json',
            },
            columns: [{
                    data: null,
                    className: 'details-control',
                    orderable: false,
                    searchable: false,
                    defaultContent: '<button class="act-btn details">Details</button>'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<button class="act-btn btn-compare" type="button">Compare Kanban</button>`;
                    }
                },
                {
                    data: 'cust_partno',
                    render: function(data, type) {
                        const value = plainCellValue(data);
                        if (type !== 'display') return value;
                        return `<span class="part-code-pill customerPart" title="${escapeHtml(value)}">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'int_partno',
                    render: function(data, type) {
                        const value = plainCellValue(data);
                        if (type !== 'display') return value;
                        return `<span class="part-code-pill" title="${escapeHtml(value)}">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'cust_backno',
                    render: function(data, type) {
                        const value = plainCellValue(data);
                        if (type !== 'display') return value;
                        return `<span class="qty-pill backNumber">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'int_backno',
                    render: function(data, type) {
                        const value = plainCellValue(data);
                        if (type !== 'display') return value;
                        return `<span class="qty-pill">${escapeHtml(value)}</span>`;
                    }
                },
                {
                    data: 'kbn_qty',
                    render: function(data) {
                        return `<span class="qty-pill">${fmtNum(data)}</span>`;
                    }
                },
                {
                    data: 'actual_kbn_qty',
                    render: function(data, type, row) {
                        return renderProgress(data, row.kbn_qty);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function() {
                        return `<button class="btn btn-icon btn-primary edit"><i class="far fa-edit"></i></button>
                            <button class="btn btn-icon btn-success save mb-1" style="display:none"><i class="fas fa-check"></i></button>
                            <button class="btn btn-icon btn-danger cancel" style="display:none"><i class="fas fa-times"></i></button>`;
                    }
                },
            ],
            lengthMenu: [
                [5, 10, 100],
                [5, 10, 100]
            ],
            order: [
                [2, 'asc']
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ rows',
                info: 'Showing _START_ to _END_ of _TOTAL_ items',
                emptyTable: 'Tidak ada data loading list detail.'
            }
        });

        table.on('xhr.dt', function(e, settings, json) {
            updateSummary((json && json.data) ? json.data : []);
        });

        /* =========================================================
         * COMPARE CLICK
         * ======================================================= */
        $(document).on('click', '.btn-compare', function() {
            const button = $(this);
            const tr = $(this).closest('tr');
            const row = table.row(tr).data();

            compareCtx.detailId = row.id;
            compareCtx.partLabel = `${plainCellValue(row.cust_partno)}_${plainCellValue(row.int_partno)}`;
            compareData = [];
            $('#compareSearch').val('');
            $('#compareFilter').val('all');
            $('#compareDownload').prop('disabled', true);
            $('#compareMeta').text('Loading comparison...');
            updateComparePrepContext(pagePrepContext);
            $('#compareTbody').html(
                '<tr><td colspan="6" style="text-align:center;padding:28px;color:var(--text-muted);">Loading data...</td></tr>'
            );
            $('#compareModal').modal('show');

            button.prop('disabled', true).text('Loading...');
            $.ajax({
                url: `/loading-list-detail/${row.id}/comparison`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    updateComparePrepContext(response.prep_context || pagePrepContext);
                    const pullItems = (response.pulling || []).map(mutationItem).filter(Boolean);
                    const prodItems = (response.production || []).map(mutationItem).filter(Boolean);
                    const pullMap = groupBySerial(pullItems);
                    const prodMap = groupBySerial(prodItems);
                    const serials = Array.from(new Set([...pullMap.keys(), ...prodMap.keys()])).sort();

                    compareData = serials.map(serial => {
                        const pItems = pullMap.get(serial) || [];
                        const dItems = prodMap.get(serial) || [];
                        let status = 'MATCH';
                        if (pItems.length && !dItems.length) status = 'MISSING_PROD';
                        else if (dItems.length && !pItems.length) status = 'MISSING_PULL';
                        return {
                            serial,
                            prodItems: dItems,
                            pullItems: pItems,
                            status
                        };
                    });

                    rebuildCompareRows('', 'all');
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal memuat data comparison.';
                    $('#compareMeta').text('Failed to load');
                    $('#compareTbody').html(
                        `<tr><td colspan="6" style="text-align:center;padding:28px;color:var(--danger);">${escapeHtml(message)}</td></tr>`
                    );
                    notif('error', message);
                },
                complete: function() {
                    button.prop('disabled', false).text('Compare Kanban');
                }
            });
        });

        $('#compareSearch').on('input', function() {
            rebuildCompareRows($(this).val(), $('#compareFilter').val());
        });

        $('#compareFilter').on('change', function() {
            rebuildCompareRows($('#compareSearch').val(), $(this).val());
        });

        $('#compareDownload').on('click', downloadCompareTable);

        /* =========================================================
         * DELETE PULLING MUTATION
         * ======================================================= */
        $(document).on('click', '.btn-del-pulling-mutation', function() {
            const mid = $(this).data('mid');
            const serial = $(this).data('serial');
            if (!mid) return;
            if (!compareCtx.detailId) {
                notif('error', 'Detail ID tidak ditemukan.');
                return;
            }
            if (!confirm(`Hapus 1 data pulling (mutation_id=${mid}) untuk serial ${serial}?`)) return;

            $.ajax({
                url: `/loading-list-detail/${compareCtx.detailId}/pulling-mutation/${mid}`,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.status === 'success') {
                        compareData.forEach(cd => {
                            if (cd.serial === serial) {
                                cd.pullItems = (cd.pullItems || []).filter(x =>
                                    String(x.mid) !== String(mid));
                                if (cd.pullItems.length && !cd.prodItems.length) cd
                                    .status = 'MISSING_PROD';
                                else if (cd.prodItems.length && !cd.pullItems
                                    .length) cd.status = 'MISSING_PULL';
                                else cd.status = 'MATCH';
                            }
                        });
                        rebuildCompareRows($('#compareSearch').val(), $('#compareFilter')
                            .val());
                        table.ajax.reload(null, false);
                        notif('success', res.message || 'Deleted');
                    } else {
                        notif('error', res.message || 'Gagal delete');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal delete';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                        .message;
                    notif('error', msg);
                }
            });
        });

        /* =========================================================
         * EDIT / SAVE / CANCEL TOTAL SCAN
         * ======================================================= */
        $(document).on('click', '#loadingList .edit', function() {
            const tr = $(this).closest('tr');
            tr.find('.actual').hide();
            tr.find('.editActual').show().focus().select();
            tr.find('.save').css({
                display: 'inline'
            });
            tr.find('.cancel').css({
                display: 'inline'
            });
            tr.find('.edit').hide();
        });

        $(document).on('keydown', '#loadingList .editActual', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('tr').find('.save').trigger('click');
            }
        });

        $(document).on('click', '#loadingList .save', function() {
            const btn = $(this);
            const tr = btn.closest('tr');
            let customerPart = $.trim(tr.find('.customerPart').text());
            let backNumber = $.trim(tr.find('.backNumber').text());
            let newActual = $.trim(tr.find('.editActual').val());

            if (backNumber === '' || backNumber === '-' || backNumber.toLowerCase() === 'null')
                backNumber = 'null';
            if (newActual === '' || isNaN(newActual) || parseInt(newActual) < 0) {
                notif('error', 'Total Scan harus berupa angka valid');
                tr.find('.editActual').focus();
                return;
            }

            fetch(`/loading-list/edit/${loadingList}/${encodeURIComponent(customerPart)}/${encodeURIComponent(backNumber)}/${parseInt(newActual)}`,
                    requestOptions)
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {
                        let newVal = parseInt(data.data);
                        notif('success', data.message);
                        tr.find('.actual').text(newVal).show();
                        tr.find('.editActual').val(newVal).hide();
                        tr.find('.save').hide();
                        tr.find('.cancel').hide();
                        tr.find('.edit').show();
                        table.ajax.reload(null, false);
                    } else {
                        notif('error', data.message);
                    }
                })
                .catch(error => {
                    notif('error', error);
                });
        });

        $(document).on('click', '#loadingList .cancel', function() {
            const tr = $(this).closest('tr');
            const oldVal = $.trim(tr.find('.actual').text());
            tr.find('.editActual').val(oldVal).hide();
            tr.find('.actual').show();
            tr.find('.save').hide();
            tr.find('.cancel').hide();
            tr.find('.edit').show();
        });

        /* =========================================================
         * DETAILS ROW (EDCL)
         * ======================================================= */
        $(document).on('click', '.details', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                let rowData = row.data();
                fetch(`/edcl/detail/${rowData.loading_list_id}/${rowData.customer_part_id}`,
                        requestOptions)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status == 'success') {
                            row.child(formatDetails(data.data)).show();
                        } else {
                            notif('error', data.message);
                        }
                    })
                    .catch(error => {
                        notif('error', error);
                    });
                tr.addClass('shown');
            }
        });

        function formatDetails(data) {
            const childId = `edcl-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            const total = data && data.length ? data.length : 0;
            const confirmed = (data || []).filter(item => item.message === 'Success - Confirm Manifest').length;
            const pending = Math.max(total - confirmed, 0);

            let rows = '';
            if (!data || data.length === 0) {
                rows =
                    `<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--text-muted);font-size:12px;">No data available</td></tr>`;
            } else {
                rows = data.map(item => {
                    const searchText =
                        `${item.id} ${item.skid_no} ${item.item_no} ${item.serial} ${item.kanban_id} ${item.message}`
                        .toLowerCase();
                    return `
                        <tr data-search="${escapeHtml(searchText)}">
                            <td>${escapeHtml(item.id)}</td>
                            <td>${escapeHtml(item.skid_no)}</td>
                            <td>${escapeHtml(item.item_no)}</td>
                            <td><span class="serial-chip">${escapeHtml(item.serial)}</span></td>
                            <td>${escapeHtml(item.kanban_id)}</td>
                            <td>${escapeHtml(item.message)}</td>
                            <td>
                                <span class="bella-badge ${item.message === 'Success - Confirm Manifest' ? 'bella-badge-green' : 'bella-badge-gray'}">${item.message === 'Success - Confirm Manifest' ? 'YES' : 'CHECK'}</span>
                            </td>
                            <td>
                                <button class="act-btn danger cancel-manifest">Cancel Manifest</button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            return `
                <div class="edcl-detail-panel" id="${childId}">
                    <div class="edcl-detail-header">
                        <div class="edcl-title-block">
                            <strong>EDCL Scan Detail</strong>
                            <span>List dibuat compact dengan scroll internal agar halaman utama tidak terlalu panjang.</span>
                        </div>
                        <div class="edcl-mini-stats">
                            <span class="edcl-stat">Total: <strong>${fmtNum(total)}</strong></span>
                            <span class="edcl-stat">Confirmed: <strong>${fmtNum(confirmed)}</strong></span>
                            <span class="edcl-stat">Pending: <strong>${fmtNum(pending)}</strong></span>
                        </div>
                    </div>
                    <div class="edcl-toolbar">
                        <div class="edcl-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" class="edcl-search" placeholder="Cari skid / item / serial / kanban..." data-target="#${childId}">
                        </div>
                        <span class="table-head-subtitle edcl-shown-info">Shown: ${fmtNum(total)} / ${fmtNum(total)}</span>
                    </div>
                    <div class="edcl-scroll-wrap">
                        <table class="edcl-child-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Skid Number</th>
                                    <th>Item Number</th>
                                    <th>Serial Number</th>
                                    <th>Customer Kanban</th>
                                    <th>Message</th>
                                    <th>Confirm</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        $(document).on('input', '.edcl-search', function() {
            const target = $(this).data('target');
            const panel = $(target);
            const q = String($(this).val() || '').toLowerCase().trim();
            const rows = panel.find('.edcl-child-table tbody tr[data-search]');
            let shown = 0;

            rows.each(function() {
                const haystack = String($(this).attr('data-search') || '').toLowerCase();
                const isMatch = !q || haystack.includes(q);
                $(this).toggleClass('row-hidden-by-search', !isMatch);
                if (isMatch) shown++;
            });

            panel.find('.edcl-shown-info').text(`Shown: ${fmtNum(shown)} / ${fmtNum(rows.length)}`);
        });

        /* =========================================================
         * NOTIFICATIONS
         * ======================================================= */
        function notif(type, message) {
            if (type === 'error') {
                iziToast.error({
                    title: 'Error! ' + message,
                    position: 'bottomRight'
                });
            } else if (type === 'success') {
                iziToast.success({
                    title: 'Success! ' + message,
                    position: 'bottomRight'
                });
            }
        }
    });
</script>
@endpush
