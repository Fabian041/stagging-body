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
                                    <select class="custom-select" id="manifest">
                                        <option selected disabled>-- Select manifest --</option>
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
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

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
                    data: 'customer_name'
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
            lengthMenu: [
                [25, 'All'],
                [25, 'All']
            ],
        });

        // Variable to store the current scroll position
        var scrollPosition = 0;

        // Function to fetch and update data
        function fetchAndUpdateData() {
            // Get the current scroll position
            scrollPosition = $('#loadingList').parent().scrollTop();

            // Reload the DataTable
            table.ajax.reload(function() {
                // Callback function after the data is reloaded
                // Restore the scroll position
                $('#loadingList').parent().scrollTop(scrollPosition);
            }, false);
        }

        // Initial data fetch when the page loads
        fetchAndUpdateData();

        // Fetch data every second
        setInterval(fetchAndUpdateData, 1000);

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
