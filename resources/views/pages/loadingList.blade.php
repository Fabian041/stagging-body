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
            align-items: center;
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
        /* Wrapper */
        .bella-table-card .dataTables_wrapper {
            padding: 0;
        }

        /* Top toolbar (length + search) */
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
            padding: 10px 12px !important;
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

        #loadingList tbody td:first-child {
            font-weight: 700;
            color: var(--navy);
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

        /* Info text */
        .bella-table-card .dataTables_wrapper .dataTables_info {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Processing indicator */
        .bella-table-card .dataTables_wrapper .dataTables_processing {
            background: rgba(255, 255, 255, .9) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--r) !important;
            color: var(--text-muted) !important;
            font-size: 12px !important;
            box-shadow: var(--shadow-md) !important;
            padding: 10px 20px !important;
        }

        /* button-cell override */
        .button-cell {
            padding: 6px 8px !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        .button-cell>div {
            margin: 0 !important;
            padding: 0 !important;
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

        /* ===== ACCORDION (inside modal) ===== */
        .bella-acc-item {
            border: 1px solid var(--border);
            border-radius: var(--r);
            margin-bottom: 8px;
            overflow: hidden;
            background: var(--card);
        }

        .accordion-header {
            padding: 12px 16px;
            background: var(--bg);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background .15s;
            border-bottom: 1px solid var(--border);
        }

        .accordion-header:hover {
            background: #E8F0FB;
        }

        .accordion-header.open {
            background: #EEF2FF;
        }

        .acc-title-text {
            font-size: 13px;
            font-weight: 700;
            color: var(--navy);
        }

        .acc-meta-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .acc-kanban-count {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            white-space: nowrap;
        }

        .acc-mini-prog {
            width: 64px;
            height: 6px;
            background: #E2E8F0;
            border-radius: 99px;
            overflow: hidden;
        }

        .acc-mini-prog-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .3s ease;
        }

        .acc-chevron-icon {
            font-size: 10px;
            color: var(--text-muted);
            transition: transform .2s;
        }

        .accordion-header.open .acc-chevron-icon {
            transform: rotate(180deg);
        }

        .acc-body-content {
            display: none;
            padding: 20px;
            background: var(--card);
        }

        .accordion-header.open+.acc-body-content {
            display: block;
        }

        /* Info grid inside accordion */
        .acc-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .acc-info-field label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            font-weight: 700;
            display: block;
            margin-bottom: 3px;
        }

        .acc-info-field p {
            font-size: 13px;
            color: var(--text);
            font-weight: 500;
            margin: 0;
            word-break: break-word;
        }

        /* Progress bar inside accordion */
        .acc-prog-section {
            margin-bottom: 16px;
        }

        .acc-prog-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .acc-prog-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            font-weight: 700;
        }

        .acc-prog-pct {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .acc-prog-bar {
            height: 10px;
            background: #E2E8F0;
            border-radius: 99px;
            overflow: hidden;
        }

        .acc-prog-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .5s ease;
        }

        /* Empty / error states */
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

        /* Loading spinner */
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

    {{-- ===== FILTER CARD ===== --}}
    <div class="bella-filter-card mt-3">
        <div class="bella-filter-row">
            @isset($manifests)
                <select class="select2 bella-filter-select" id="manifest" style="min-width: 200px;">
                    <option disabled selected>-- Select manifest --</option>
                    @foreach ($manifests as $manifest)
                        <option value="{{ $manifest->pds_number }}">{{ $manifest->pds_number }}</option>
                    @endforeach
                </select>
            @endisset

            <select class="bella-filter-select" id="cycle">
                <option selected disabled>-- Select cycle --</option>
                <option value="1">Cycle 1</option>
                <option value="2">Cycle 2</option>
                <option value="3">Cycle 3</option>
                <option value="4">Cycle 4</option>
                <option value="5">Cycle 5</option>
            </select>

            @isset($customers)
                <select class="bella-filter-select" id="customer">
                    <option selected disabled>-- Select customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->name }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            @endisset

            <input id="date" type="date" class="bella-filter-input" placeholder="Delivery date">

            <button class="act-btn danger" id="reset" type="button"
                style="height:36px; padding: 0 16px; font-size: 12px; letter-spacing: .04em;">
                <i class="fas fa-redo-alt" style="margin-right:5px;"></i> RESET
            </button>
        </div>
    </div>

    {{-- ===== TABLE CARD ===== --}}
    <div class="bella-table-card mt-2">
        <div class="bella-table-card-header">
            <span class="bella-table-card-title">Delivery Monitoring</span>
            <span class="bella-live-badge">
                <span class="bella-live-dot"></span> LIVE
            </span>
        </div>

        <div class="table-responsive">
            <table class="table bella-table" id="loadingList" style="width: 100%">
                <thead>
                    <tr>
                        <th class="text-center">PDS Number</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Cycle</th>
                        <th class="text-center">Delivery Date</th>
                        <th class="text-center">Pulling Progress</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-center"></tbody>
            </table>
        </div>
    </div>

@endsection

{{-- ===== MODAL ===== --}}
<div class="modal fade" id="loadingListModal" tabindex="-1" role="dialog" aria-labelledby="loadingListModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loadingListModalLabel">
                    Loading Lists for PDS: <span id="modalPdsNumber"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="loadingListAccordion">
                {{-- Accordion content loaded via AJAX --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="bella-btn bella-btn-secondary bella-btn-sm"
                    data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="{{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}"></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
    $(document).ready(function() {

        /* =========================================================
         * HELPERS
         * ======================================================= */
        function getFilterCustomer() {
            const v = $('#customer').val();
            return (v && v !== '-- Select customer --') ? v : '';
        }

        function getFilterCycle() {
            const v = $('#cycle').val();
            return (v && v !== '-- Select cycle --') ? v : '';
        }

        /* Apply query-string filters on load */
        (function applyQueryStringFilters() {
            const params = new URLSearchParams(window.location.search);
            const customer = params.get('customer');
            const cycle = params.get('cycle');
            const deliveryDate = params.get('delivery_date');

            if (customer) {
                const $match = $('#customer option').filter(function() {
                    return $(this).val() === customer;
                });
                if ($match.length) $('#customer').val(customer);
            }
            if (cycle) {
                let $opt = $('#cycle option').filter(function() {
                    return $(this).val() === cycle;
                });
                if (!$opt.length) $('#cycle').append($('<option>', {
                    value: cycle,
                    text: cycle
                }));
                $('#cycle').val(cycle);
            }
            if (deliveryDate) $('#date').val(deliveryDate);
        })();

        /* =========================================================
         * DATATABLE INIT
         * ======================================================= */
        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: true,
            ajax: {
                url: `{{ url('dashboard/getLoadingList') }}`,
                dataType: 'json',
                data: function(d) {
                    d.customer = getFilterCustomer();
                    d.cycle = getFilterCycle();
                    d.delivery_date = $('#date').val() || '';
                }
            },
            columns: [{
                    data: 'pds_number'
                },
                {
                    data: 'customer'
                },
                {
                    data: 'cycle'
                },
                {
                    data: 'delivery_date'
                },
                {
                    data: 'progress'
                },
                {
                    data: 'loading_and_status',
                    orderable: false,
                    searchable: false,
                    className: 'button-cell',
                    width: '280px'
                }
            ],
            order: [
                [3, 'dsc']
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

        /* =========================================================
         * AUTO-REFRESH LOGIC
         * ======================================================= */
        let autoRefreshInterval;
        let isUserInteracting = false;
        let lastInteractionTime = Date.now();
        let lastDataHash = '';
        let lastRecordCount = 0;
        let lastPdsNumbers = [];

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
                scrollTop: scrollTop,
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

        function smartRefresh() {
            if (isUserInteracting) return;
            const visiblePdsNumbers = table.rows({
                    page: 'current'
                }).data().toArray()
                .map(row => row.pds_number)
                .filter((v, i, a) => a.indexOf(v) === i);

            $.ajax({
                url: `{{ url('dashboard/checkLoadingListUpdates') }}`,
                type: 'GET',
                dataType: 'json',
                data: {
                    state: {
                        pdsCount: lastRecordCount,
                        latestPdsNumbers: lastPdsNumbers,
                        visiblePdsNumbers: visiblePdsNumbers
                    }
                },
                timeout: 5000,
                success: function(response) {
                    if (response.error) {
                        refreshTableData();
                        return;
                    }

                    const countMismatch = response.totalRecords !== lastRecordCount;
                    const forceRefresh = countMismatch || (response.deletedCount && response
                        .deletedCount > 0);
                    if (forceRefresh) {
                        lastRecordCount = response.totalRecords;
                        refreshTableData();
                        return;
                    }

                    const dataChanged = (response.dataHash && response.dataHash !== lastDataHash) ||
                        (response.totalRecords !== lastRecordCount);
                    const hasNewPds = response.latestPdsNumbers &&
                        (!lastPdsNumbers || response.latestPdsNumbers.some(pds => !lastPdsNumbers
                            .includes(pds)));

                    if (response.hasNewData) {
                        lastRecordCount = response.serverPdsCount;
                        lastPdsNumbers = response.serverLatestPds;
                        refreshTableData();
                    } else {
                        updateSpecificRows();
                    }

                    if (dataChanged || hasNewPds) {
                        lastDataHash = response.dataHash || '';
                        lastRecordCount = response.totalRecords || 0;
                        lastPdsNumbers = response.latestPdsNumbers || [];
                        refreshTableData();
                    }
                },
                error: function(xhr, status, error) {
                    if (Math.random() < 0.1) refreshTableData();
                    console.warn('Smart refresh check failed:', status, error);
                }
            });
        }

        function updateSpecificRows() {
            if (isUserInteracting) return;
            const visibleData = table.rows({
                page: 'current'
            }).data();
            const visibleIds = [];
            for (let i = 0; i < visibleData.length; i++) {
                if (visibleData[i] && visibleData[i].id) {
                    visibleIds.push(visibleData[i].id.replace('row-', ''));
                }
            }
            if (visibleIds.length === 0) {
                smartRefresh();
                return;
            }

            $.ajax({
                url: `{{ url('dashboard/getLoadingListUpdates') }}`,
                type: 'POST',
                data: {
                    ids: visibleIds,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    if (response.deletedRows && response.deletedRows.length > 0) {
                        response.deletedRows.forEach(function(id) {
                            const row = table.row('#row-' + id);
                            if (row.any()) row.remove().draw(false);
                        });
                    }
                    if (response.updatedRows && response.updatedRows.length > 0) {
                        response.updatedRows.forEach(function(updatedRow) {
                            const row = table.row('#row-' + updatedRow.id);
                            if (row.any()) {
                                const rowData = row.data();
                                if (updatedRow.progress) rowData.progress = updatedRow
                                    .progress;
                                if (updatedRow.detail) rowData.loading_and_status =
                                    updatedRow.detail;
                                row.data(rowData).draw(false);
                            }
                        });
                    }
                    if ((response.deletedRows && response.deletedRows.length > 0) ||
                        (response.updatedRows && response.updatedRows.length > 0)) {
                        table.order([3, 'desc']).draw(false);
                    }
                },
                error: function() {
                    smartRefresh();
                }
            });
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            let refreshCount = 0;
            autoRefreshInterval = setInterval(function() {
                refreshCount++;
                if (refreshCount >= 10) {
                    refreshCount = 0;
                    refreshTableData();
                    return;
                }
                if (typeof updateSpecificRows === 'function') {
                    updateSpecificRows();
                } else {
                    smartRefresh();
                }
            }, 3000);
        }

        /* =========================================================
         * DATATABLE EVENT LISTENERS
         * ======================================================= */
        $('#loadingList').on('page.dt length.dt order.dt search.dt', onUserInteraction);
        $('#loadingList').on('click', 'th', onUserInteraction);
        $(document).on('click', '.dataTables_paginate .paginate_button', onUserInteraction);
        $(document).on('change', '.dataTables_length select', onUserInteraction);
        $('#loadingList').closest('.table-responsive').on('scroll', onUserInteraction);
        $('#manifest, #customer, #cycle, #date').on('change', onUserInteraction);

        /* =========================================================
         * MODAL: LOADING LISTS ACCORDION
         * ======================================================= */
        $(document).on('click', '.show-loading-lists', function() {
            const pdsNumber = $(this).data('pds');
            $('#modalPdsNumber').text(pdsNumber);

            // Spinner
            $('#loadingListAccordion').html(`
                <div class="bella-spinner-wrap">
                    <div class="bella-spinner"></div>
                    <p>Loading data...</p>
                </div>
            `);

            $.ajax({
                url: `{{ url('dashboard/getLoadingListsByPds') }}`,
                type: 'GET',
                data: {
                    pds_number: pdsNumber
                },
                success: function(response) {
                    let html = '';

                    if (response.loading_lists && response.loading_lists.length > 0) {
                        response.loading_lists.forEach(function(ll, index) {
                            const collapseId = 'acc-collapse-' + index;
                            const pct = ll.total_kanban > 0 ?
                                Math.round((ll.actual_kanban / ll.total_kanban) *
                                    100) : 0;

                            let statusClass, statusText, progColor;
                            if (ll.actual_kanban == ll.total_kanban && ll
                                .total_kanban > 0) {
                                statusClass = 'bella-badge bella-badge-green';
                                statusText = 'Complete';
                                progColor = 'var(--success)';
                            } else if (ll.actual_kanban > 0) {
                                statusClass = 'bella-badge bella-badge-amber';
                                statusText = 'In Progress';
                                progColor = 'var(--warning)';
                            } else {
                                statusClass = 'bella-badge bella-badge-gray';
                                statusText = 'Uncomplete';
                                progColor = 'var(--text-muted)';
                            }

                            html += `
                            <div class="bella-acc-item">
                                <div class="accordion-header ${index === 0 ? 'open' : ''}" data-target="${collapseId}">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                        <span class="acc-title-text">${ll.number}</span>
                                        <span class="${statusClass}">${statusText}</span>
                                    </div>
                                    <div class="acc-meta-right">
                                        <span class="acc-kanban-count">${ll.actual_kanban} / ${ll.total_kanban}</span>
                                        <div class="acc-mini-prog">
                                            <div class="acc-mini-prog-fill" style="width:${pct}%;background:${progColor};"></div>
                                        </div>
                                        <i class="fas fa-chevron-down acc-chevron-icon"></i>
                                    </div>
                                </div>
                                <div class="acc-body-content" id="${collapseId}" style="${index === 0 ? 'display:block;' : ''}">
                                    <div class="acc-info-grid">
                                        <div>
                                            <div class="acc-info-field" style="margin-bottom:10px;">
                                                <label>Loading List</label>
                                                <p>${ll.number}</p>
                                            </div>
                                            <div class="acc-info-field" style="margin-bottom:10px;">
                                                <label>Customer</label>
                                                <p>${ll.customer_name || 'Not specified'}</p>
                                            </div>
                                            <div class="acc-info-field">
                                                <label>Cycle</label>
                                                <p>${ll.cycle || 'Not specified'}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="acc-info-field" style="margin-bottom:10px;">
                                                <label>Delivery Date</label>
                                                <p>${ll.delivery_date || 'Not specified'}</p>
                                            </div>
                                            <div class="acc-info-field" style="margin-bottom:10px;">
                                                <label>Progress</label>
                                                <p style="color:var(--navy);font-weight:700;">${pct}%</p>
                                            </div>
                                            <div class="acc-info-field">
                                                <label>Kanban Status</label>
                                                <p>${ll.actual_kanban} of ${ll.total_kanban} completed</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="acc-prog-section">
                                        <div class="acc-prog-header">
                                            <span class="acc-prog-label">Overall Progress</span>
                                            <span class="acc-prog-pct">${pct}%</span>
                                        </div>
                                        <div class="acc-prog-bar">
                                            <div class="acc-prog-bar-fill" style="width:${pct}%;background:${progColor};"></div>
                                        </div>
                                    </div>
                                    <div style="text-align:right;margin-top:4px;">
                                        <a href="/loading-list/${ll.id}" class="bella-btn bella-btn-primary bella-btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>`;
                        });
                    } else {
                        html = `
                        <div class="acc-empty-state">
                            <i class="fas fa-inbox"></i>
                            <h6>No Loading Lists Found</h6>
                            <p>There are no loading lists available for PDS number ${pdsNumber}.</p>
                        </div>`;
                    }

                    $('#loadingListAccordion').html(html);

                    // Accordion toggle
                    $(document).off('click', '.accordion-header').on('click',
                        '.accordion-header',
                        function() {
                            const targetId = $(this).data('target');
                            const $body = $('#' + targetId);
                            const isOpen = $(this).hasClass('open');
                            if (isOpen) {
                                $(this).removeClass('open');
                                $body.hide();
                            } else {
                                $(this).addClass('open');
                                $body.show();
                            }
                        });
                },
                error: function() {
                    $('#loadingListAccordion').html(`
                        <div class="acc-error-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h6>Unable to Load Data</h6>
                            <p>There was an error loading the loading lists. Please try again.</p>
                        </div>
                    `);
                }
            });

            $('#loadingListModal').modal('show');
        });

        /* =========================================================
         * FILTER HANDLERS
         * ======================================================= */
        $('#manifest').on('change', function() {
            table.column(0).search($(this).val() || '').draw();
        });
        $('#customer').on('change', function() {
            table.column(1).search($(this).val() || '').draw();
        });
        $('#cycle').on('change', function() {
            table.column(2).search($(this).val() || '').draw();
        });
        $('#date').on('change', function() {
            table.column(3).search($(this).val() || '').draw();
        });

        $('#reset').on('click', function() {
            $('#cycle').val('-- Select cycle --').trigger('change');
            $('#customer').val('-- Select customer --').trigger('change');
            $('#manifest').val(null).trigger('change'); // select2 reset
            $('#date').val('').trigger('change');
            onUserInteraction();
        });

        /* =========================================================
         * CLEANUP
         * ======================================================= */
        $(window).on('beforeunload', function() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            sessionStorage.removeItem('tableState');
        });

        /* Start auto-refresh */
        startAutoRefresh();
    });
</script>
