@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-12 m-auto">
            <div class="card card-info shadow" style="border-radius:20px">
                <div class="card-body">
                    <h4 class="card-title mt-3 mb-4 text-dark text-center">LOADING LIST DETAIL</h4>

                    <div class="row mt-5 mb-4 m-auto">
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-dark" style="font-weight: 700">Loading
                                    List No. <p class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->number }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">PDS Number <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->pds_number }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Customer <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->name }}</p>
                                </li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-dark" style="font-weight: 700">Delivery Date <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->delivery_date }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Shipping Date <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->shipping_date }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Cycle <p class="text-right"
                                        style="display: inline;">
                                        : {{ $loadingListDetail->cycle }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="card card-danger mt-3 shadow" style="border-radius:10px">
        <div class="card-body">
            <h5 class="card-title mt-3 text-dark text-center">DETAILS</h5>
            <table class="table table-responsive-lg" id="loadingList" style="width: 100%">
                <thead>
                    <tr>
                        <th class="text-center"></th> <!-- New column for the Details button -->
                        <th class="text-center">Pulling Date</th>
                        <th class="text-center">Production Date</th>
                        <th class="text-center">Customer Part No.</th>
                        <th class="text-center">Internal Part No.</th>
                        <th class="text-center">Customer Back No.</th>
                        <th class="text-center">Internal Back No.</th>
                        <th class="text-center">Kanban Qty</th>
                        <th class="text-center">Total Scan</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody class="text-center">

                </tbody>
            </table>
        </div>
    </div>
@endsection

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
    $(document).ready(function() {
        const loadingList = "{{ $loadingListId }}";
        const requestOptions = {
            method: 'GET',
            headers: {
                "Content-type": "application/json",
            }
        }

        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: false,
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
                    defaultContent: '<button class="btn btn-info btn-sm details">Details</button>'
                },
                {
                    data: 'pulling_date'
                },
                {
                    data: 'prod_date'
                },
                {
                    data: 'cust_partno'
                },
                {
                    data: 'int_partno'
                },
                {
                    data: 'cust_backno'
                },
                {
                    data: 'int_backno'
                },
                {
                    data: 'kbn_qty'
                },
                {
                    data: 'actual_kbn_qty'
                },
                {
                    data: 'edit',
                    orderable: false,
                    searchable: false
                },
            ],
            lengthMenu: [
                [5, 10, 100],
                [5, 10, 100]
            ],
        });

        // Toggle Details Row
        $(document).on('click', '.details', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                let rowData = row.data();

                // fetch skid
                fetch(`/edcl/detail/${rowData.loading_list_id}/${rowData.customer_part_id}`,
                        requestOptions)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status == 'success') {

                            row.child(formatDetails(data.data)).show();

                        } else if (data.status == 'error') {
                            notif('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.log(error.message);
                        notif('error', error);
                    })

                tr.addClass('shown');
            }
        });

        // Function to format the details row
        function formatDetails(data) {
            let rows = '';

            if (!data || data.length === 0) {
                rows = `
            <tr>
                <td class="text-center" colspan="8" style="color: dark-grey ; font-weight: bold;">
                    No data available
                </td>
            </tr>
        `;
            } else {
                rows = data.map((item, index) => `
                    <tr>
                        <td class="text-center">${item.id}</td>
                        <td class="text-center">${item.skid_no}</td>
                        <td class="text-center">${item.item_no}</td>
                        <td class="text-center">${item.serial}</td>
                        <td class="text-center">${item.kanban_id}</td>
                        <td class="text-center">${item.message}</td>
                        <td class="text-center">
                            <span class="badge badge-${item.message === 'Success - Confirm Manifest' ? 'success' : 'secondary'}">YES</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm cancel-manifest">Cancel Manifest</button>
                        </td>
                    </tr>
                `).join('');
            }

            return `
                <table class="table">
                    <thead class="table-success">
                        <tr class="text-white">
                            <th class="text-center" style="color: #006400">ID</th>
                            <th class="text-center" style="color: #006400">Skid Number</th>
                            <th class="text-center" style="color: #006400">Item Number</th>
                            <th class="text-center" style="color: #006400">Serial Number</th>
                            <th class="text-center" style="color: #006400">Customer Kanban</th>
                            <th class="text-center" style="color: #006400">Message</th>
                            <th class="text-center" style="color: #006400">Confirm</th>
                            <th class="text-center" style="color: #006400">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            `;
        }


        $(document).on('click', '.cancel-manifest', function() {
            // hide span
            let tr = $(this).closest('tr'); // Get the closest row
            let rowData = {
                id: tr.find('td:eq(0)').text().trim(),
            };

            // store cancel
            fetch(`/edcl/cancel/${rowData.id}`,
                    requestOptions)
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {
                        notif('success', data.message);
                        table.ajax.reload(null,
                            false); // Reload the DataTable data without resetting the current page
                    } else if (data.status == 'error') {
                        notif('error', data.message);
                    }
                })
                .catch(error => {
                    console.log(error.message);
                    notif('error', error);
                })
        });

        $(document).on('click', '#loadingList .edit', function() {
            // hide span
            $(this).closest('tr').find('.actual').hide();

            // show input
            $(this).closest('tr').find('.editActual').show();

            // show save button
            $(this).closest('tr').find('.save').css({
                display: 'inline'
            });

            // show cancel button
            $(this).closest('tr').find('.cancel').show({
                display: 'inline'
            });

            // hide edit button
            $(this).closest('tr').find('.edit').hide();
        });

        $(document).on('click', '#loadingList .save', function() {
            // get customer part
            let customerPart = $(this).closest('tr').find('.customerPart').html();

            // get customer part
            let backNumber = $(this).closest('tr').find('.backNumber').html();

            if (backNumber == '') {
                backNumber = 'null';
            }

            // get edit value
            let newActual = $(this).closest('tr').find('.editActual').val();

            fetch(`/loading-list/edit/${loadingList}/${customerPart}/${backNumber}/${newActual}`,
                    requestOptions)
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {

                        let newVal = parseInt(data.data);
                        notif('success', data.message);

                        // hide span
                        $(this).closest('tr').find('.actual').val(newVal);

                        // show input
                        $(this).closest('tr').find('.editActual').hide();

                        // show save button
                        $(this).closest('tr').find('.save').hide();

                        // show cancel button
                        $(this).closest('tr').find('.cancel').hide();

                        // hide edit button
                        $(this).closest('tr').find('.edit').show();

                        table.ajax.reload(null,
                            false); // Reload the DataTable data without resetting the current page

                    } else if (data.status == 'error') {
                        notif('error', data.message);
                    }
                })
                .catch(error => {
                    console.log(error.message);
                    notif('error', error);
                })
        });

        $(document).on('click', '#loadingList .cancel', function() {
            // hide span
            $(this).closest('tr').find('.actual').show();

            // show input
            $(this).closest('tr').find('.editActual').hide();

            // show save button
            $(this).closest('tr').find('.save').hide();

            // show cancel button
            $(this).closest('tr').find('.cancel').hide();

            // hide edit button
            $(this).closest('tr').find('.edit').show();
        });

        function notif(type, message) {
            if (type == 'error') {
                iziToast.error({
                    title: 'Error! ' + message,
                    position: 'bottomRight'
                });
            } else if (type == 'success') {
                iziToast.success({
                    title: 'Success! ' + message,
                    position: 'bottomRight'
                });
            }
        }
    });
</script>
