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
        @empty($lines)
            <div class="col-12">
                <div class="card shadow" style="border-radius:8px">
                    <div class="row">
                        <div class="col">
                            <div class="card p-3 text-center">
                                <h1>Belum ada part yang terdaftar</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @foreach ($lines as $line)
                <div class="col-12">
                    <div class="card card-info shadow" style="border-radius:10px">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="text-dark" style="font-weight: bolder !important">LINE
                                            {{ $line->line }}
                                        </h4>
                                        <div class="card-header-action">
                                            <a data-collapse="#mycard-collapse-{{ $line->line }}"
                                                class="btn btn-icon btn-primary" href="#"><i class="fas fa-minus"></i></a>
                                        </div>
                                    </div>
                                    <div class="collapse show" id="mycard-collapse-{{ $line->line }}">
                                        <div class="card-body">
                                            <!-- Place the chart element inside this container -->
                                            <div id="{{ $line->line }}-chart" style="justify-content: center;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endempty
    </div>
@endsection
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
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="<https://unpkg.com/mqtt/dist/mqtt.min.js>"></script>
<script>
    // chart option
    var lineData = @json($lines);
    // Declare a global object to store chart instances
    var charts = {};

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

    $(document).ready(function() {
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
