@extends('layouts.root.main')

@section('main')
    <style>
        .pis-scans-group {
            text-align: left;
        }

        .pis-scans-group strong {
            color: #2c3e50;
            font-size: 12px;
        }

        .pis-scans-group span {
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

        .pis-scan-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #17a2b8;
        }

        .pis-scan-number {
            font-weight: bold;
            color: #2c3e50;
        }

        .pis-scan-progress {
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

            .pis-scans-group span {
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
                                <input id="loadingListSearch" type="text" class="form-control" placeholder="Search Loading List Number...">
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
            <h4 class="card-title mt-3 mb-3 text-dark text-center">PIS SCAN MONITORING</h4>
            <div class="table-responsive-lg">
                <table class="table" id="pisScanList" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 150px;">Scan Time</th>                            
                            <th class="text-center" style="width: 300px;">Loading List Number</th>
                            <th class="text-center" style="width: 220px;">PIS Progress</th>
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

<!-- PIS Scan Detail Modal -->
<div class="modal fade" id="pisScanDetailModal" tabindex="-1" role="dialog" aria-labelledby="pisScanDetailModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 85%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pisScanDetailModalLabel">PIS Scan Details: <span
                        id="modalLoadingListNumber"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pisScanDetailContent">
                <!-- Detail content will be loaded here -->
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
        let table = $('#pisScanList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: true,
            ajax: {
                url: `{{ url('pis/get-scan-list') }}`,
                dataType: 'json',
            },
            columns: [{
                    data: 'scan_time',
                    className: 'text-center',
                    
                },
                {
                    data: 'loading_list_number',
                },
                {
                    data: 'progress',
                },
                {
                    data: 'status',
                    orderable: false,
                    searchable: false,
                    className: 'button-cell',
                    width: '200px'
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

        $('<style>')
            .prop('type', 'text/css')
            .html(`
        .button-cell {
            padding: 4px !important;
            text-align: center !important;
            vertical-align: middle !important;
        }
        .button-cell > div {
            margin: 0 !important;
            padding: 0 !important;
        }
    `)
            .appendTo('head');

        let autoRefreshInterval;
        let isUserInteracting = false;
        let lastInteractionTime = Date.now();

        function onUserInteraction() {
            isUserInteracting = true;
            lastInteractionTime = Date.now();

            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

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
            const currentPage = pageInfo.page;
            const scrollTop = $(window).scrollTop();

            sessionStorage.setItem('tableState', JSON.stringify({
                page: currentPage,
                scrollTop: scrollTop,
                timestamp: Date.now()
            }));

            table.draw(false);

            setTimeout(function() {
                const savedState = JSON.parse(sessionStorage.getItem('tableState') || '{}');
                if (savedState.scrollTop && Date.now() - savedState.timestamp < 1000) {
                    $(window).scrollTop(savedState.scrollTop);
                }
            }, 200);
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            autoRefreshInterval = setInterval(function() {
                if (!isUserInteracting) {
                    refreshTableData();
                }
            }, 3000);
        }

        $('#pisScanList').on('page.dt', onUserInteraction);
        $('#pisScanList').on('length.dt', onUserInteraction);
        $('#pisScanList').on('order.dt', onUserInteraction);
        $('#pisScanList').on('search.dt', onUserInteraction);
        $('#pisScanList').on('click', 'th', onUserInteraction);

        $(document).on('click', '.dataTables_paginate .paginate_button', function() {
            onUserInteraction();
        });

        $(document).on('change', '.dataTables_length select', onUserInteraction);
        $('#pisScanList').closest('.table-responsive-lg').on('scroll', onUserInteraction);
        
        // Search filter for loading list number
        let searchTimeout;
        $('#loadingListSearch').on('keyup input', function() {
            const searchValue = $(this).val();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Debounce search for better performance
            searchTimeout = setTimeout(function() {
                table.column(0).search(searchValue).draw();
                onUserInteraction();
            }, 300);
        });

        // PIS Scan Detail Modal Handler
        $(document).on('click', '.show-pis-detail', function() {
            const loadingListNumber = $(this).data('loading-list');
            $('#modalLoadingListNumber').text(loadingListNumber);

            $('#pisScanDetailContent').html(`
                <div style="text-align: center; padding: 40px 20px; color: #6c757d;">
                    <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="margin-top: 15px; font-size: 14px; font-weight: 500;">Loading data...</p>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                </div>
            `);

            $.ajax({
                url: `{{ url('pis/get-scan-details') }}`,
                type: 'GET',
                data: {
                    loading_list_number: loadingListNumber
                },
                success: function(response) {
                    let detailHtml = '<div style="background: #ffffff;">';
                    
                    if (response.items && response.items.length > 0) {
                        // Header info mirip loadingListDetail (ringkas)
                        detailHtml += `
                        <div class="card border-0 mt-3 shadow-sm" style="border-radius:14px">
                           

                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th>Scan Time</th>
                                                <th>Customer Part No</th>
                                                <th>Internal Part No</th>
                                                <th>Kanban Qty</th>
                                                <th>Total Scan</th>
                                                <th style="min-width:140px">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;


                        response.items.forEach(function(item) {
                            const progressPercentage = item.progress_percentage || 0;
                            const isComplete = item.is_complete || false;
                            
                            let statusClass = '';
                            if (isComplete) {
                                statusClass = 'bg-success';
                            } else if (item.scanned_qty > 0) {
                                statusClass = 'bg-warning';
                            } else {
                                statusClass = 'bg-secondary';
                            }

                            const scanTimeDisplay = (item.scanned_qty > 0 && progressPercentage > 0) ? (item.scanned_at || '-') : '-';
                            detailHtml += `
                                <tr>
                                    <td class="text-center">${scanTimeDisplay}</td>
                                    <td class="text-center">${item.part_number_cust || '-'}</td>
                                    <td class="text-center">${item.part_number_int || '-'}</td>
                                    <td class="text-center">${item.target_qty || 0}</td>
                                    <td class="text-center">${item.scanned_qty || 0}</td>
                                    <td class="text-center" style="min-width:150px;">
                                        <div class="progress" data-height="16" style="height:16px;">
                                            <div class="progress-bar ${statusClass}" role="progressbar"
                                                style="width:${progressPercentage}%;"
                                                aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100">
                                                <small class="text-white font-weight-bold">${progressPercentage}%</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        detailHtml += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        detailHtml += `
                            <div style="text-align: center; padding: 40px 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                                <svg width="48" height="48" fill="#6c757d" viewBox="0 0 16 16" style="margin-bottom: 16px;">
                                    <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                    <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                </svg>
                                <h5 style="color: #6c757d; font-weight: 500; margin-bottom: 8px;">No Items Found</h5>
                                <p style="color: #6c757d; margin: 0; font-size: 14px;">There are no items available for this loading list.</p>
                            </div>
                        `;
                    }

                    detailHtml += '</div>';
                    $('#pisScanDetailContent').html(detailHtml);
                },
                error: function(xhr) {
                    $('#pisScanDetailContent').html(`
                        <div style="text-align: center; padding: 40px 20px; background: #fff5f5; border: 1px solid #fed7d7; color: #e53e3e; border-radius: 8px;">
                            <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16" style="margin-bottom: 16px;">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                            <h5 style="font-weight: 500; margin-bottom: 8px;">Unable to Load Data</h5>
                            <p style="margin: 0; font-size: 14px;">There was an error loading the scan details. Please try again.</p>
                        </div>
                    `);
                }
            });

            $('#pisScanDetailModal').modal('show');
        });

        $('#reset button').on('click', function() {
            $('#loadingListSearch').val('');
            clearTimeout(searchTimeout);
            table.column(0).search('').draw();
            onUserInteraction();
        });

        $(window).on('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            sessionStorage.removeItem('tableState');
        });
    });
</script>
