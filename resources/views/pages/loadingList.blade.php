@extends('layouts.root.main')

@section('main')
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
                            <th class="text-center">Loading List Number</th>
                            <th class="text-center">PDS Number</th>
                            <th class="text-center">Customer</th>
                            <th class="text-center">Cycle</th>
                            <th class="text-center">Delivery Date</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="text-center">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

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
                    data: 'number'
                },
                {
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
                    data: 'detail',
                    orderable: false,
                    searchable: false
                },
            ],
            order: [
                [4, 'dsc']
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

            $.ajax({
                url: `{{ url('dashboard/checkLoadingListUpdates') }}`,
                type: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function(response) {
                    if (response.error) {
                        refreshTableData();
                        return;
                    }

                    // Check if data has changed
                    const dataChanged = (response.dataHash && response.dataHash !== lastDataHash) ||
                        (response.totalRecords !== lastRecordCount);

                    if (dataChanged) {
                        lastDataHash = response.dataHash || '';
                        lastRecordCount = response.totalRecords || 0;
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

            // Get visible row IDs on current page
            const visibleData = table.rows({
                page: 'current'
            }).data();
            const visibleIds = [];

            for (let i = 0; i < visibleData.length; i++) {
                if (visibleData[i] && visibleData[i].id) {
                    visibleIds.push(visibleData[i].id);
                }
            }

            if (visibleIds.length === 0) {
                smartRefresh();
                return;
            }

            // Check only visible rows for updates
            $.ajax({
                url: `{{ url('dashboard/getLoadingListUpdates') }}`,
                type: 'POST',
                data: {
                    ids: visibleIds,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    if (response.updatedRows && response.updatedRows.length > 0) {
                        // Update only changed rows without full refresh
                        response.updatedRows.forEach(function(updatedRow) {
                            const rowNode = table.row('#row-' + updatedRow.id).node();
                            if (rowNode) {
                                // Update specific cells that changed
                                table.cell(rowNode, 5).data(updatedRow
                                    .progress); // progress column
                                table.cell(rowNode, 6).data(updatedRow
                                    .detail); // detail column
                            }
                        });
                    } else {
                        // No specific updates, do smart refresh
                        smartRefresh();
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

            // Use the most appropriate refresh method
            autoRefreshInterval = setInterval(function() {
                // Try specific row updates first (fastest)
                if (typeof updateSpecificRows === 'function') {
                    updateSpecificRows();
                } else {
                    smartRefresh();
                }
            }, 3000);
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

        // Initial load
        startAutoRefresh();

        // Filter handlers
        $('#manifest').on('change', function() {
            let manifest = $('#manifest').val();
            if (manifest) {
                table.column(1).search(manifest);
            } else {
                table.column(1).search('');
            }
            table.draw();
        });

        $('#customer').on('change', function() {
            let customer = $('#customer').val();
            if (customer) {
                table.column(2).search(customer);
            } else {
                table.column(2).search('');
            }
            table.draw();
        });

        $('#cycle').on('change', function() {
            let cycle = $('#cycle').val();
            if (cycle) {
                table.column(3).search(cycle);
            } else {
                table.column(3).search('');
            }
            table.draw();
        });

        $('#date').on('change', function() {
            let date = $('#date').val();
            if (date) {
                table.column(4).search(date);
            } else {
                table.column(4).search('');
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
