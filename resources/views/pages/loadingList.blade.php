@extends('layouts.root.main')

@section('main')
    <style>
        .loading-lists-group {
            text-align: left;
        }

        .loading-lists-group strong {
            color: #2c3e50;
            font-size: 12px;
        }

        .loading-lists-group span {
            font-size: 11px;
            line-height: 1.3;
            display: block;
            max-width: 200px;
        }

        .btn-toolbar {
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-toolbar .btn-group {
            margin-bottom: 5px;
        }

        .progress-bar small {
            font-size: 10px;
            line-height: 1;
        }

        .dropdown-menu .dropdown-item {
            padding: 5px 15px;
            font-size: 12px;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        /* Accordion styles */
        .accordion-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 5px;
            border-radius: 6px;
        }

        .accordion-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 15px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .accordion-header:hover {
            background-color: #e9ecef;
        }

        .accordion-body {
            padding: 10px 15px;
            background-color: white;
        }

        .loading-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #17a2b8;
        }

        .loading-list-number {
            font-weight: bold;
            color: #2c3e50;
        }

        .loading-list-progress {
            font-size: 11px;
            color: #6c757d;
        }

        .expand-btn {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .expand-btn:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
        }

        .expand-btn.collapsed::after {
            content: ' ▼';
        }

        .expand-btn:not(.collapsed)::after {
            content: ' ▲';
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .btn-toolbar {
                flex-direction: column;
            }

            .btn-group {
                width: 100%;
                margin-bottom: 10px;
            }

            .loading-lists-group span {
                max-width: 150px;
            }
        }
    </style>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-info shadow" style="padding: 40px;padding-top:60px; border-radius:16px">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="input-group">
                                @isset($manifests)
                                    <select class="select2 form-control select2-hidden-accessible"
                                        style="width: 30%; height: 36px" data-select2-id="select2-data-1-ok7p" tabindex="-1"
                                        aria-hidden="true" id="manifest">
                                        <option data-select2-id="select2-data-3-mma1" disabled>-- Select manifest --</option>
                                        @foreach ($manifests as $manifest)
                                            <option value="{{ $manifest->pds_number }}">{{ $manifest->pds_number }}</option>
                                        @endforeach
                                    </select>
                                @endisset()
                                <select class="custom-select" id="cycle">
                                    <option selected disabled>-- Select cycle --</option>
                                    <option value="1">cycle 1</option>
                                    <option value="2">cycle 2</option>
                                    <option value="3">cycle 3</option>
                                    <option value="4">cycle 4</option>
                                    <option value="5">cycle 5</option>
                                </select>
                                @isset($customers)
                                    <select class="custom-select" id="customer">
                                        <option selected disabled>-- Select customer --</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->name }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                @endisset()
                                <input id="date" type="date" class="form-control" placeholder="Delivery date">
                                <div class="input-group-append" id="reset">
                                    <button class="btn btn-lg btn-danger" type="button">RESET</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-danger mt-2 shadow" style="border-radius:10px">
        <div class="card-body">
            <h4 class="card-title mt-3 mb-3 text-dark text-center">DELIVERY MONITORING</h4>
            <div class="table-responsive-lg">
                <table class="table" id="loadingList" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="text-center">PDS Number</th>
                            <th class="text-center">Customer</th>
                            <th class="text-center">Cycle</th>
                            <th class="text-center">Delivery Date</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
<!-- Loading Lists Accordion Modal -->
<div class="modal fade" id="loadingListModal" tabindex="-1" role="dialog" aria-labelledby="loadingListModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loadingListModalLabel">Loading Lists for PDS: <span
                        id="modalPdsNumber"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="loadingListAccordion">
                <!-- Accordion content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: true,
            ajax: {
                url: `{{ url('dashboard/getLoadingList') }}`,
                dataType: 'json',
            },
            columns: [{
                    data: 'pds_number'
                },
                {
                    data: 'customer',
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
                    searchable: false
                }
            ],
            order: [
                [3, 'dsc']
            ],
            lengthMenu: [
                [10, 25, 100],
                [10, 25, 100]
            ],
            // Enable state saving to remember pagination
            stateSave: true,
            stateDuration: 60 * 60, // 1 hour
            // Add these options for better state management
            pageResize: true,
            stateSaveParams: function(settings, data) {
                // Save additional state info
                data.scrollTop = $(window).scrollTop();
            },
            stateLoadParams: function(settings, data) {
                // Restore scroll position
                if (data.scrollTop) {
                    setTimeout(function() {
                        $(window).scrollTop(data.scrollTop);
                    }, 100);
                }
            }
        });

        let autoRefreshInterval;
        let isUserInteracting = false;
        let lastInteractionTime = Date.now();

        // Enhanced user interaction detection
        function onUserInteraction() {
            isUserInteracting = true;
            lastInteractionTime = Date.now();

            // Stop auto-refresh temporarily when user is interacting
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            // Resume auto-refresh after user stops interacting for 5 seconds
            setTimeout(function() {
                if (Date.now() - lastInteractionTime >= 5000) {
                    isUserInteracting = false;
                    startAutoRefresh();
                }
            }, 5000);
        }

        // Modified refresh function that preserves pagination state
        function refreshTableData() {
            if (isUserInteracting) return;

            // Store current state before refresh
            const pageInfo = table.page.info();
            const currentPage = pageInfo.page;
            const scrollTop = $(window).scrollTop();

            // Store in sessionStorage as backup
            sessionStorage.setItem('tableState', JSON.stringify({
                page: currentPage,
                scrollTop: scrollTop,
                timestamp: Date.now()
            }));

            // Use draw() instead of ajax.reload() to maintain server-side state
            table.draw(false); // false = don't reset paging

            // Restore scroll position after draw
            setTimeout(function() {
                const savedState = JSON.parse(sessionStorage.getItem('tableState') || '{}');
                if (savedState.scrollTop && Date.now() - savedState.timestamp < 1000) {
                    $(window).scrollTop(savedState.scrollTop);
                }
            }, 200);
        }

        // Smart refresh function - only refresh if data has changed
        let lastDataHash = '';
        let lastRecordCount = 0;

        function smartRefresh() {
            if (isUserInteracting) return;

            // Get current visible PDS numbers (if any)
            const visiblePdsNumbers = table.rows({
                    page: 'current'
                }).data().toArray()
                .map(row => row.pds_number)
                .filter((v, i, a) => a.indexOf(v) === i); // Unique values

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

                    // More aggressive refresh if we suspect deletions
                    const countMismatch = response.totalRecords !== lastRecordCount;
                    const forceRefresh = countMismatch ||
                        (response.deletedCount && response.deletedCount > 0);

                    if (forceRefresh) {
                        lastRecordCount = response.totalRecords;
                        refreshTableData();
                        return;
                    }

                    // Check if data has changed (existing records or count)
                    const dataChanged = (response.dataHash && response.dataHash !== lastDataHash) ||
                        (response.totalRecords !== lastRecordCount);

                    // Additional check for new PDS numbers
                    const hasNewPds = response.latestPdsNumbers &&
                        (!lastPdsNumbers ||
                            response.latestPdsNumbers.some(pds => !lastPdsNumbers.includes(pds)));

                    if (response.hasNewData) {
                        lastRecordCount = response.serverPdsCount;
                        lastPdsNumbers = response.serverLatestPds;
                        refreshTableData();
                    } else {
                        // Still check for updates to existing rows
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
                    // Fallback to regular refresh occasionally
                    if (Math.random() < 0.1) {
                        refreshTableData();
                    }
                    console.warn('Smart refresh check failed:', status, error);
                }
            });
        }

        // Alternative approach: Manual row updates (most effective for real-time data)
        function updateSpecificRows() {
            if (isUserInteracting) return;

            const visibleData = table.rows({
                page: 'current'
            }).data();
            const visibleIds = [];

            for (let i = 0; i < visibleData.length; i++) {
                if (visibleData[i] && visibleData[i].id) {
                    const cleanId = visibleData[i].id.replace('row-', '');
                    visibleIds.push(cleanId);
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
                    // Handle deleted rows first
                    if (response.deletedRows && response.deletedRows.length > 0) {
                        response.deletedRows.forEach(function(deletedRowId) {
                            const row = table.row('#row-' + deletedRowId);
                            if (row.any()) {
                                row.remove().draw(false);
                            }
                        });
                    }

                    // Then handle updated rows
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

                    // If we had any deletions or updates, the table might need reordering
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

        // Start auto-refresh function
        function startAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            let refreshCount = 0;

            autoRefreshInterval = setInterval(function() {
                refreshCount++;

                // Every 10 refreshes (30 seconds), do a full refresh regardless
                if (refreshCount >= 10) {
                    refreshCount = 0;
                    refreshTableData();
                    return;
                }

                // Try specific row updates first (fastest)
                if (typeof updateSpecificRows === 'function') {
                    updateSpecificRows();
                } else {
                    smartRefresh();
                }
            }, 3000); // 3 seconds
        }

        // Enhanced event detection
        $('#loadingList').on('page.dt', onUserInteraction);
        $('#loadingList').on('length.dt', onUserInteraction);
        $('#loadingList').on('order.dt', onUserInteraction);
        $('#loadingList').on('search.dt', onUserInteraction);
        $('#loadingList').on('click', 'th', onUserInteraction);

        $(document).on('click', '.dataTables_paginate .paginate_button', function() {
            onUserInteraction();
        });

        $(document).on('change', '.dataTables_length select', onUserInteraction);
        $('#loadingList').closest('.table-responsive-lg').on('scroll', onUserInteraction);
        $('#manifest, #customer, #cycle, #date').on('change', onUserInteraction);

        // Loading List Accordion Modal Handler
        $(document).on('click', '.show-loading-lists', function() {
            const pdsNumber = $(this).data('pds');
            $('#modalPdsNumber').text(pdsNumber);

            // Show loading spinner
            $('#loadingListAccordion').html(
                '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

            // Load loading lists for this PDS
            $.ajax({
                url: `{{ url('dashboard/getLoadingListsByPds') }}`,
                type: 'GET',
                data: {
                    pds_number: pdsNumber
                },
                success: function(response) {
                    let accordionHtml =
                        '<div class="accordion" id="accordionLoadingLists">';

                    if (response.loading_lists && response.loading_lists.length > 0) {
                        response.loading_lists.forEach(function(loadingList, index) {
                            const collapseId = 'collapse' + index;
                            const headingId = 'heading' + index;

                            // Calculate progress for individual loading list
                            const progressPercentage = loadingList.total_kanban >
                                0 ?
                                Math.round((loadingList.actual_kanban / loadingList
                                    .total_kanban) * 100) : 0;

                            let statusBadge = '';
                            let progressColor = '';

                            if (loadingList.actual_kanban >= loadingList
                                .total_kanban && loadingList.total_kanban > 0) {
                                statusBadge =
                                    '<span class="badge badge-success ml-2">Complete</span>';
                                progressColor = 'bg-success';
                            } else if (loadingList.actual_kanban > 0) {
                                statusBadge =
                                    '<span class="badge badge-warning ml-2">In Progress</span>';
                                progressColor = 'bg-warning';
                            } else {
                                statusBadge =
                                    '<span class="badge badge-danger ml-2">Incomplete</span>';
                                progressColor = 'bg-danger';
                            }

                            accordionHtml += `
                                <div class="card accordion-card">
                                    <div class="card-header accordion-header" id="${headingId}">
                                        <button class="btn text-left w-100 d-flex justify-content-between align-items-center" 
                                                type="button" data-toggle="collapse" data-target="#${collapseId}" 
                                                aria-expanded="${index === 0 ? 'true' : 'false'}" aria-controls="${collapseId}">
                                            <div>
                                                <strong>${loadingList.number}</strong>
                                                ${statusBadge}
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted">${loadingList.actual_kanban} / ${loadingList.total_kanban}</small>
                                                <div class="progress ml-2" style="width: 60px; height: 6px;">
                                                    <div class="progress-bar ${progressColor}" style="width: ${progressPercentage}%"></div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                    <div id="${collapseId}" class="collapse ${index === 0 ? 'show' : ''}" 
                                         aria-labelledby="${headingId}" data-parent="#accordionLoadingLists">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Loading List:</strong> ${loadingList.number}</p>
                                                    <p><strong>Customer:</strong> ${loadingList.customer_name || 'N/A'}</p>
                                                    <p><strong>Cycle:</strong> ${loadingList.cycle || 'N/A'}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Delivery Date:</strong> ${loadingList.delivery_date || 'N/A'}</p>
                                                    <p><strong>Progress:</strong> ${progressPercentage}%</p>
                                                    <p><strong>Kanban:</strong> ${loadingList.actual_kanban} / ${loadingList.total_kanban}</p>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar ${progressColor}" 
                                                             style="width: ${progressPercentage}%" 
                                                             role="progressbar" 
                                                             aria-valuenow="${progressPercentage}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            ${progressPercentage}%
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-12 text-right">
                                                    <a href="/loading-list/${loadingList.id}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        accordionHtml +=
                            '<div class="alert alert-info">No loading lists found for this PDS number.</div>';
                    }

                    accordionHtml += '</div>';

                    $('#loadingListAccordion').html(accordionHtml);
                },
                error: function() {
                    $('#loadingListAccordion').html(
                        '<div class="alert alert-danger">Error loading loading lists. Please try again.</div>'
                    );
                }
            });

            $('#loadingListModal').modal('show');
        });

        // Initial load
        startAutoRefresh();

        // Filter handlers
        $('#manifest').on('change', function() {
            let manifest = $('#manifest').val();
            if (manifest) {
                table.column(0).search(manifest);
            } else {
                table.column(0).search('');
            }
            table.draw();
        });

        $('#customer').on('change', function() {
            let customer = $('#customer').val();
            if (customer) {
                table.column(1).search(customer);
            } else {
                table.column(1).search('');
            }
            table.draw();
        });

        $('#cycle').on('change', function() {
            let cycle = $('#cycle').val();
            if (cycle) {
                table.column(2).search(cycle);
            } else {
                table.column(2).search('');
            }
            table.draw();
        });

        $('#date').on('change', function() {
            let date = $('#date').val();
            if (date) {
                table.column(3).search(date);
            } else {
                table.column(3).search('');
            }
            table.draw();
        });

        $('#reset').on('click', function() {
            $('#cycle').val('-- Select cycle --').trigger('change');
            $('#customer').val('-- Select customer --').trigger('change');
            $('#manifest').val('-- Select manifest --').trigger('change');
            $('#date').val('').trigger('change');
            onUserInteraction();
        });

        // Cleanup
        $(window).on('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            sessionStorage.removeItem('tableState');
        });
    });
</script>
