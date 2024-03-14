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
                        <th class="text-center">Part Name</th>
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
                    data: 'part_name'
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
                    data: 'actual_kbn_qty',
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

            console.log(backNumber);

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
