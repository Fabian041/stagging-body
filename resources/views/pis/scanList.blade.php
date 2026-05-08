@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== FILTER CARD ===== */
        .bella-filter-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            padding: 28px 24px 20px;
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
            height: 36px;
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            background: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12.5px;
            padding: 0 10px;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
            min-width: 160px;
        }

        .bella-filter-select:focus,
        .bella-filter-input:focus {
            border-color: var(--sky);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
        }

        /* ===== TABLE CARD ===== */
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-live-badge {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--sky);
            color: #fff;
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            letter-spacing: .06em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bella-live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #fff;
            animation: live-pulse 1.5s ease-in-out infinite;
        }

        @keyframes live-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .3;
            }
        }

        /* ===== DATATABLE OVERRIDES ===== */
        .bella-table-card .dataTables_wrapper {
            padding: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length,
        .bella-table-card .dataTables_wrapper .dataTables_filter {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
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

        /* Table itself */
        #pisScanList {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12.5px !important;
        }

        #pisScanList thead th {
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

        #pisScanList tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
        }

        #pisScanList tbody tr:last-child td {
            border-bottom: none !important;
        }

        #pisScanList tbody tr:hover td {
            background: var(--bg) !important;
        }

        /* Pagination */
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
            background: var(--card) !important;
            color: var(--text-muted) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_info {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .bella-table-card .dataTables_wrapper .dataTables_processing {
            background: rgba(255, 255, 255, .9) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--r) !important;
            color: var(--text-muted) !important;
            font-size: 12px !important;
            box-shadow: var(--shadow-md) !important;
            padding: 10px 20px !important;
        }

        .button-cell {
            padding: 6px 8px !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        .button-cell>div {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* ===== PIS SCAN GROUP (in table cell) ===== */
        .pis-scans-group {
            text-align: left;
        }

        .pis-scans-group strong {
            color: var(--navy);
            font-size: 12px;
        }

        .pis-scans-group span {
            font-size: 11px;
            line-height: 1.3;
            display: block;
            max-width: 200px;
            color: var(--text-muted);
        }

        /* ===== MODAL ===== */
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

        .modal-title span {
            color: var(--sky) !important;
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

        /* ===== SCAN LOG DETAILS (inside modal table) ===== */
        .scan-log-details {
            font-size: 12px;
        }

        .scan-log-details summary {
            cursor: pointer;
            color: var(--sky);
            font-weight: 700;
            outline: none;
            list-style: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 10px;
            border: 1px solid var(--border);
            background: var(--bg);
            border-radius: 999px;
            font-size: 11px;
        }

        .scan-log-details summary::-webkit-details-marker {
            display: none;
        }

        .scan-log-details summary::after {
            content: '▼';
            font-size: 9px;
            color: var(--text-muted);
        }

        .scan-log-details[open] summary::after {
            content: '▲';
        }

        .scan-log-list {
            max-height: 150px;
            overflow: auto;
            margin-top: 6px;
            padding: 6px 8px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .scan-log-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 5px 0;
            border-bottom: 1px dashed var(--border);
        }

        .scan-log-row:last-child {
            border-bottom: none;
        }

        .scan-log-time {
            font-size: 11px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .scan-log-label {
            font-size: 11px;
            color: var(--text);
            text-align: right;
            word-break: break-all;
        }

        .scan-log-empty {
            font-size: 11px;
            color: var(--text-muted);
            text-align: center;
            padding: 4px 0;
        }

        /* ===== SPINNER ===== */
        .bella-spinner-wrap {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .bella-spinner {
            display: inline-block;
            width: 36px;
            height: 36px;
            border: 3px solid var(--border);
            border-top-color: var(--sky);
            border-radius: 50%;
            animation: bella-spin 1s linear infinite;
        }

        @keyframes bella-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .bella-spinner-wrap p {
            margin-top: 12px;
            font-size: 12.5px;
            font-weight: 500;
        }

        /* ===== EMPTY / ERROR STATES ===== */
        .acc-empty-state {
            text-align: center;
            padding: 40px 20px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--r);
        }

        .acc-empty-state i {
            font-size: 36px;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: block;
        }

        .acc-empty-state h6 {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .acc-empty-state p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        .acc-error-state {
            text-align: center;
            padding: 40px 20px;
            background: var(--danger-light);
            border: 1px solid #fecaca;
            border-radius: var(--r);
        }

        .acc-error-state i {
            font-size: 36px;
            color: var(--danger);
            margin-bottom: 12px;
            display: block;
        }

        .acc-error-state h6 {
            font-size: 13px;
            font-weight: 700;
            color: var(--danger);
            margin-bottom: 6px;
        }

        .acc-error-state p {
            font-size: 12px;
            color: #991b1b;
            margin: 0;
        }

        /* ===== MODAL INNER TABLE ===== */
        .modal-inner-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .modal-inner-table thead th {
            text-align: center;
            padding: 8px 10px;
            color: var(--text-muted);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .modal-inner-table tbody td {
            text-align: center;
            padding: 9px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            color: var(--text);
        }

        .modal-inner-table tbody tr:last-child td {
            border-bottom: none;
        }

        .modal-inner-table tbody tr:hover td {
            background: var(--bg);
        }

        /* progress bar inside modal table */
        .bella-prog-bar {
            height: 14px;
            background: #E2E8F0;
            border-radius: 99px;
            overflow: hidden;
            min-width: 90px;
        }

        .bella-prog-bar-fill {
            height: 100%;
            border-radius: 99px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            color: #fff;
            transition: width .4s ease;
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

            .pis-scans-group span {
                max-width: 150px;
            }
        }
    </style>

    {{-- ===== FILTER CARD ===== --}}
    <div class="bella-filter-card mt-3">
        <div class="bella-filter-row">
            <div class="bella-filter-group" style="flex:1; min-width:200px;">
                <span class="bella-filter-label">Loading List Number</span>
                <div style="display:flex; gap:0;">
                    <input id="loadingListSearch" type="text" class="bella-filter-input"
                        placeholder="Search loading list number..."
                        style="border-radius: var(--r-sm) 0 0 var(--r-sm); flex:1; min-width:0;">
                    <button id="resetSearch" type="button" class="act-btn danger"
                        style="height:36px; padding:0 14px; font-size:12px; border-radius:0 var(--r-sm) var(--r-sm) 0; letter-spacing:.04em;">
                        RESET
                    </button>
                </div>
            </div>

            <div class="bella-filter-group">
                <span class="bella-filter-label">Start Date</span>
                <input id="filterStartDate" type="date" class="bella-filter-input">
            </div>

            <div class="bella-filter-group">
                <span class="bella-filter-label">End Date</span>
                <input id="filterEndDate" type="date" class="bella-filter-input">
            </div>

            <div class="bella-filter-group" style="justify-content:flex-end;">
                <span class="bella-filter-label">&nbsp;</span>
                <button id="exportExcel" type="button" class="act-btn success"
                    style="height:36px; padding:0 16px; font-size:12px; letter-spacing:.04em;">
                    <i class="fas fa-file-excel" style="margin-right:5px;"></i> DOWNLOAD EXCEL
                </button>
            </div>
        </div>
    </div>

    {{-- ===== TABLE CARD ===== --}}
    <div class="bella-table-card mt-2">
        <div class="bella-table-card-header">
            <span class="bella-table-card-title">PIS Scan Monitoring</span>
            <span class="bella-live-badge">
                <span class="bella-live-dot"></span> LIVE
            </span>
        </div>

        <div class="table-responsive">
            <table class="table bella-table" id="pisScanList" style="width: 100%">
                <thead>
                    <tr>
                        <th class="text-center" style="width:150px;">Scan Time</th>
                        <th class="text-center" style="width:300px;">Loading List Number</th>
                        <th class="text-center" style="width:140px;">No PDS</th>
                        <th class="text-center" style="width:180px;">Customer</th>
                        <th class="text-center" style="width:220px;">PIS Progress</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-center"></tbody>
            </table>
        </div>
    </div>
@endsection

{{-- ===== MODAL PIS SCAN DETAIL ===== --}}
<div class="modal fade" id="pisScanDetailModal" tabindex="-1" role="dialog" aria-labelledby="pisScanDetailModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="max-width:85%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pisScanDetailModalLabel">
                    PIS Scan Details: <span id="modalLoadingListNumber"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pisScanDetailContent">
                {{-- Content loaded via AJAX --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="act-btn secondary bella-btn-sm" data-dismiss="modal"
                    style="height:32px; padding:0 16px; font-size:12px;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="{{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
    $(document).ready(function() {

        let table = $('#pisScanList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: true,
            ajax: {
                url: `{{ url('pis/get-scan-list') }}`,
                dataType: 'json',
                data: function(d) {
                    d.start_date = $('#filterStartDate').val();
                    d.end_date = $('#filterEndDate').val();
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error('pisScanList ajax error', {
                        status: xhr.status,
                        textStatus,
                        errorThrown,
                        responseText: xhr.responseText
                    });
                }
            },
            columns: [{
                    data: 'scan_time',
                    className: 'text-center'
                },
                {
                    data: 'loading_list_number'
                },
                {
                    data: 'pds_number',
                    className: 'text-center'
                },
                {
                    data: 'customer_name',
                    className: 'text-center'
                },
                {
                    data: 'progress'
                },
                {
                    data: 'status',
                    orderable: false,
                    searchable: false,
                    className: 'button-cell',
                    width: '280px'
                }
            ],
            order: [
                [0, 'asc']
            ],
            lengthMenu: [
                [10, 25, 100],
                [10, 25, 100]
            ],
            stateSave: true,
            stateDuration: 60 * 60,
            pageResize: true,
            stateSaveParams: function(settings, data) {
                data.scrollTop = $(window).scrollTop();
            },
            stateLoadParams: function(settings, data) {
                if (data.scrollTop) {
                    setTimeout(function() {
                        $(window).scrollTop(data.scrollTop);
                    }, 100);
                }
            }
        });

        /* ── Auto-refresh ── */
        let autoRefreshInterval;
        let isUserInteracting = false;
        let lastInteractionTime = Date.now();

        function onUserInteraction() {
            isUserInteracting = true;
            lastInteractionTime = Date.now();
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            setTimeout(function() {
                if (Date.now() - lastInteractionTime >= 5000) {
                    isUserInteracting = false;
                    startAutoRefresh();
                }
            }, 5000);
        }

        function refreshTableData() {
            if (isUserInteracting) return;
            const pageInfo = table.page.info();
            const scrollTop = $(window).scrollTop();
            sessionStorage.setItem('tableState', JSON.stringify({
                page: pageInfo.page,
                scrollTop,
                timestamp: Date.now()
            }));
            table.draw(false);
            setTimeout(function() {
                const saved = JSON.parse(sessionStorage.getItem('tableState') || '{}');
                if (saved.scrollTop && Date.now() - saved.timestamp < 1000) {
                    $(window).scrollTop(saved.scrollTop);
                }
            }, 200);
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(function() {
                if (!isUserInteracting) refreshTableData();
            }, 3000);
        }

        $('#pisScanList').on('page.dt length.dt order.dt search.dt', onUserInteraction);
        $('#pisScanList').on('click', 'th', onUserInteraction);
        $(document).on('click', '.dataTables_paginate .paginate_button', onUserInteraction);
        $(document).on('change', '.dataTables_length select', onUserInteraction);
        $('#pisScanList').closest('.table-responsive').on('scroll', onUserInteraction);

        /* ── Search debounce ── */
        let searchTimeout;
        $('#loadingListSearch').on('keyup input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                table.column(1).search($('#loadingListSearch').val()).draw();
                onUserInteraction();
            }, 300);
        });

        /* ── Reset search ── */
        $('#resetSearch').on('click', function() {
            $('#loadingListSearch').val('');
            clearTimeout(searchTimeout);
            table.column(1).search('').draw();
            onUserInteraction();
        });

        /* ── Date filters ── */
        $('#filterStartDate, #filterEndDate').on('change', function() {
            table.draw();
            onUserInteraction();
        });

        /* ── Export Excel ── */
        $('#exportExcel').on('click', function() {
            const baseUrl = `{{ route('pis.scanListExport') }}`;
            const params = new URLSearchParams();
            const startDate = $('#filterStartDate').val();
            const endDate = $('#filterEndDate').val();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            window.location.href = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
        });

        /* ── PIS Scan Detail Modal ── */
        $(document).on('click', '.show-pis-detail', function() {
            const loadingListNumber = $(this).data('loading-list');
            $('#modalLoadingListNumber').text(loadingListNumber);

            $('#pisScanDetailContent').html(`
                <div class="bella-spinner-wrap">
                    <div class="bella-spinner"></div>
                    <p>Loading data...</p>
                </div>
            `);

            $.ajax({
                url: `{{ url('pis/get-scan-details') }}`,
                type: 'GET',
                data: {
                    loading_list_number: loadingListNumber
                },
                success: function(response) {
                    const escapeHtml = (value) => String(value ?? '')
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');

                    let detailHtml = '';

                    if (response.items && response.items.length > 0) {
                        detailHtml += `
                        <div class="table-responsive">
                            <table class="modal-inner-table">
                                <thead>
                                    <tr>
                                        <th>Scan Time</th>
                                        <th>Customer Part No</th>
                                        <th style="width:110px;">Back No</th>
                                        <th>Internal Part No</th>
                                        <th>Qty</th>
                                        <th>Total Scan</th>
                                        <th style="min-width:140px;">Progress</th>
                                        <th style="min-width:180px;">Detail Scanned</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                        response.items.forEach(function(item) {
                            const pct = item.progress_percentage || 0;
                            const isComplete = item.is_complete || false;

                            let progColor;
                            if (isComplete) progColor = 'var(--success)';
                            else if (item.scanned_qty > 0) progColor =
                                'var(--warning)';
                            else progColor = 'var(--text-muted)';

                            const scanTimeDisplay = (item.scanned_qty > 0 && pct >
                                0) ? (item.scanned_at || '-') : '-';
                            const scanLogs = Array.isArray(item.scan_logs) ? item
                                .scan_logs : [];
                            const logsCount = scanLogs.length;
                            const logsListHtml = logsCount > 0 ?
                                scanLogs.map(log => `
                                    <div class="scan-log-row">
                                        <span class="scan-log-time">${escapeHtml(log.scan_time || '-')}</span>
                                        <span class="scan-log-label">${escapeHtml(log.label || '-')}</span>
                                    </div>`).join('') :
                                `<div class="scan-log-empty">Belum ada log scan</div>`;

                            detailHtml += `
                                <tr>
                                    <td>${escapeHtml(scanTimeDisplay)}</td>
                                    <td>${escapeHtml(item.part_number_cust || '-')}</td>
                                    <td>
                                        <span class="bella-badge" style="background:var(--bg);border:1px solid var(--border);color:var(--text);font-weight:600;">
                                            ${escapeHtml(item.back_number || '-')}
                                        </span>
                                    </td>
                                    <td>${escapeHtml(item.part_number_int || '-')}</td>
                                    <td>${item.target_qty  || 0}</td>
                                    <td>${item.scanned_qty || 0}</td>
                                    <td>
                                        <div class="bella-prog-bar">
                                            <div class="bella-prog-bar-fill" style="width:${pct}%;background:${progColor};">
                                                ${pct}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <details class="scan-log-details">
                                            <summary>${logsCount}x scan</summary>
                                            <div class="scan-log-list">${logsListHtml}</div>
                                        </details>
                                    </td>
                                </tr>`;
                        });

                        detailHtml += `</tbody></table></div>`;
                    } else {
                        detailHtml = `
                        <div class="acc-empty-state">
                            <i class="fas fa-inbox"></i>
                            <h6>No Items Found</h6>
                            <p>There are no items available for this loading list.</p>
                        </div>`;
                    }

                    $('#pisScanDetailContent').html(detailHtml);
                },
                error: function() {
                    $('#pisScanDetailContent').html(`
                        <div class="acc-error-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h6>Unable to Load Data</h6>
                            <p>There was an error loading the scan details. Please try again.</p>
                        </div>`);
                }
            });

            $('#pisScanDetailModal').modal('show');
        });

        /* ── Cleanup ── */
        $(window).on('beforeunload', function() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            sessionStorage.removeItem('tableState');
        });

        startAutoRefresh();
    });
</script>
