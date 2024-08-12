@extends('layouts.root.main')

@section('main')
    <div class="row">
        <div class="col mt-3 text-right">
            <div class="col-md-12">
                <button class="btn btn-lg btn-danger" data-toggle="modal" data-target="#partModal">Upload
                    Part</button>
            </div>
            {{-- <div class="col-md-12">
                <button class="btn btn-lg btn-danger" data-toggle="modal" data-target="#stockModal">Import Stock</button>
            </div> --}}
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header justify-content-center mt-3">
                    <h3>Body Plant Stock Monitoring</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills justify-content-center" id="myTab3" role="tablist">
                        @foreach ($lines as $line)
                            <li class="nav-item">
                                <a class="nav-link show @if ($line->line == 'AS711') active @endif" id="home-tab3"
                                    data-toggle="tab" href="#{{ $line->line }}" role="tab" aria-controls="home"
                                    aria-selected="true">{{ $line->line }}</a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="myTabContent2">
                        @foreach ($lines as $line)
                            <div class="tab-pane fade @if ($line->line == 'AS711') active show @endif"
                                id="{{ $line->line }}" role="tabpanel" aria-labelledby="home-tab3">
                                <div class="col-12 col-md-12 col-lg-12 mt-5">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-md" id="stocks-{{ $line->line }}">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Back Number</th>
                                                    <th>Standard Stock</th>
                                                    <th>Current Stock</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($line->items as $item)
                                                    @php
                                                        if ($item['qty'] < $item['standard']) {
                                                            $color = 'danger';
                                                            $status = 'Under Stock';
                                                        } elseif ($item['qty'] == $item['standard']) {
                                                            $color = 'warning';
                                                            $status = 'Low Stock';
                                                        } else {
                                                            $color = 'success';
                                                            $status = 'In Stock';
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            <h3><code> {{ $item['back_number'] }}</code></h3>
                                                        </td>
                                                        <td>{{ $item['standard'] }}</td>
                                                        <td>{{ $item['qty'] }}</td>
                                                        <td>
                                                            <div class="badge badge-{{ $color }} ">
                                                                {{ $status }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-info edit-stock"
                                                                data-stock="{{ json_encode($item) }}">Edit</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


{{-- modal stock --}}
<div class="modal fade" tabindex="-1" role="dialog" id="edit">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('production.adjust') }}" method="POST" enctype="multipart/form-data">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock <code id="title"></code></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="card card-danger p-3">
                        <div class="row text-center">
                            <div class="col-6"><code>Standard Stock : </code>
                                <span id="standard_stock">
                                </span>
                            </div>
                            <div class="col-6"><code>Current Stock : </code>
                                <span id="current_stock">
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>New Standard Stock</label>
                        <input type="hidden" id="internal_part_id" name="internal_part_id">
                        <input type="number" class="form-control" name="standard_stock" min="0" placeholder="-">
                    </div>
                    <div class="form-group">
                        <label>New Current Stock</label>
                        <input type="number" class="form-control" name="current_stock" min="0" placeholder="-">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}

{{-- modal --}}
<div class="modal fade" tabindex="-1" role="dialog" id="partModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.part.import') }}" method="POST" enctype="multipart/form-data">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Part</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="customFile" name="file">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}

{{-- modal --}}
<div class="modal fade" tabindex="-1" role="dialog" id="stockModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.stock.import') }}" method="POST" enctype="multipart/form-data">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Stock</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="customFile" name="file">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asser('assets/js/apexcharts.js') }}"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="<https://unpkg.com/mqtt/dist/mqtt.min.js>"></script>
<script>
    // chart option
    var lineData = @json($lines);
    // Declare a global object to store chart instances
    var charts = {};

    var errorMessege = "{!! session('error') !!}";
    var successMessege = "{!! session('success') !!}";

    // chart option
    function generateOptions(line) {
        return {
            chart: {
                height: 300,
                columnWidth: 500,
                type: 'bar',
                animations: {
                    enabled: true,
                    easing: 'easein',
                    speed: 800, // Set animation to start from bottom
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    animate: {
                        from: 'bottom'
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    barHeight: '50%',
                    borderRadius: 5,
                    borderRadiusOnAllStackedSeries: false,
                    columnWidth: '60%'
                },
            },
            colors: '#696CFF',
            noData: {
                text: 'No data at line ' + line
            },
            series: [{
                name: 'Quantity',
                data: []
            }],
        };
    }


    // initialize chart
    function chart(line) {
        var options = generateOptions(line);
        var chartInstance = new ApexCharts(document.querySelector(`#${line}-chart`), options);
        charts[line] = chartInstance; // Store the chart instance in the object
        return chartInstance;
    }

    function updateChart(line, data) {
        var chartData = data.map(function(item) {
            var color;

            // Set different colors based on qty value
            if (item.qty <= 200) {
                color = '#ff0000'; // Red color
            } else if (item.qty > 200) {
                color = '#20c997'; // Green color
            } else {
                color = '#0000ff'; // Blue color
            }

            return {
                x: item.back_number,
                y: item.qty,
                fillColor: color // Add the fillColor property with the corresponding color
            };
        });

        charts[line].updateOptions({
            series: [{
                name: 'Stock',
                data: chartData
            }]
        });
    }

    function getElement(line) {
        var el = document.querySelector(`#${line}-chart`);
        return el;
    }

    $(document).on('click', '.edit-stock', function() {
        const data = $(this).data('stock');
        let modal = $('#edit').modal('show');
        if (modal.length) {
            $('#title').html(`<h4>${data.back_number}</h4>`);
            $('#internal_part_id').val(data.id);
            $('#standard_stock').html(data.standard);
            $('#current_stock').html(data.qty);
            modal.modal('show');
        } else {
            console.error('Modal not found for chart ID:', data.id);
        }
    });

    $(document).ready(function() {
        if (errorMessege) {
            iziToast.error({
                title: 'Error! ' + errorMessege,
                position: 'bottomRight'
            });
        } else if (successMessege) {
            iziToast.success({
                title: 'Success! ' + successMessege,
                position: 'bottomRight'
            });
        }
        // Initialize DataTable for the first tab
        $('#stocks-{{ $lines[0]->line }}').DataTable();

        // Initialize DataTable on tab show event
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var target = $(e.target).attr("href"); // Get the target tab ID
            var tableId = target + ' table'; // ID of the table inside the tab
            if (!$.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable(); // Initialize DataTable if not already initialized
            }
        });

        // render chart
        lineData.forEach(function(data) {
            if (getElement(data.line)) {
                chart(data.line).render();
            }
        });

        lineData.forEach(function(item) {
            updateChart(item.line, item.items);
        });

    });
    Paho.MQTT.DEBUG = true;

    let client;

    function connectMQTT() {
        // Create an MQTT client instance
        clientId = "client_" + Math.random().toString(16).substr(2, 8);
        client = new Paho.MQTT.Client("172.18.3.70", Number(8083), clientId);

        // Set callback handlers
        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        // Connect the client, providing an onConnect callback
        client.connect({
            onSuccess: onConnect,
            onFailure: onFailure,
            // userName: "fabian",
            // password: "1234"
        });
    }

    function onConnect() {
        console.log('Connected');
        client.subscribe("prod/quantity");
    }

    function onFailure(error) {
        console.error('Failed to connect to MQTT broker:', error.errorMessage);
        console.log(error);
        // Implement your own logic for handling connection failure, e.g., retry after a certain interval
        setTimeout(connectMQTT, 5000);
    }

    function onConnectionLost(responseObject) {
        if (responseObject.errorCode !== 0) {
            console.log("Connection Lost: " + responseObject.errorMessage);
            // Implement your own logic for handling connection loss, e.g., retry after a certain interval
            setTimeout(connectMQTT, 5000);
        }
    }

    function onMessageArrived(data) {
        // update chart
        let items = JSON.parse(data.payloadString);
        items.forEach(function(item) {
            updateChart(item.line, item.items);
        });
    }

    connectMQTT();
</script>
