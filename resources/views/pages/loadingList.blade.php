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
        });

        // setInterval(() => {
        //     table.ajax.reload(null, false);
        // }, 1000);

        // var pusher = new Pusher('78dc86268a49904a688d', {
        //     cluster: 'ap1',
        //     forceTLS: true
        // });

        // websocket
        // pusher.subscribe('loading-list').bind('loadingListUpdated', function(data) {
        //     table.ajax.reload(null, false);
        // });

        // Function to fetch and update data
        // function fetchAndUpdateData() {
        //     // Get the current scroll position
        //     table.ajax.reload(null, false); // Reload the DataTable data without resetting the current page
        // }

        // // Initial data fetch when the page loads
        // fetchAndUpdateData();

        // Fetch data every second
        // setInterval(fetchAndUpdateData, 1000);

        $('#manifest').on('change', function() {
            // get all filter values
            let manifest = $('#manifest').val();

            if (manifest) {
                table.column(1).search(manifest);
            } else {
                table.column(1).search('');
            }

            table.draw();
        })

        $('#customer').on('change', function() {
            // get all filter values
            let customer = $('#customer').val();

            if (customer) {
                table.column(2).search(customer);
            } else {
                table.column(2).search('');
            }

            table.draw();
        })

        $('#cycle').on('change', function() {
            // get all filter values
            let cycle = $('#cycle').val();

            if (cycle) {
                table.column(3).search(cycle);
            } else {
                table.column(3).search('');
            }

            table.draw();
        })

        $('#date').on('change', function() {
            // get all filter values
            let date = $('#date').val();

            if (date) {
                table.column(4).search(date);
            } else {
                table.column(4).search('');
            }

            table.draw();
        })

        $('#reset').on('click', function() {
            $('#cycle').val('-- Select cycle --').trigger(
                'change'); // Reset the filter and trigger change event
            $('#customer').val('-- Select customer --').trigger(
                'change'); // Reset the filter and trigger change event
            $('#manifest').val('-- Select manifest --').trigger(
                'change'); // Reset the filter and trigger change event
            $('#date').val('').trigger(
                'change'); // Reset the filter and trigger change event
        });
    });
</script>
