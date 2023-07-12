@extends('layouts.root.main')

@section('main')
    <div class="row">
        <div class="col mt-3 text-right">
            <div class="col-md-12">
                <button class="btn btn-lg btn-danger" data-toggle="modal" data-target="#partModal">Upload
                    Part</button>
            </div>
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
                <div class="col-6">
                    <div class="card shadow" style="border-radius:8px">
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


{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // chart option
    var lineData = @json($lines);

    // Declare a global object to store chart instances
    var charts = {};

    // chart option
    function generateOptions(line) {
        return {
            chart: {
                height: 200,
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
        console.log(line, data);
        charts[line].updateOptions({
            series: [{
                name: 'Stock',
                data: data.map(function(item) {
                    return {
                        x: item.back_number,
                        y: item.qty
                    }
                })
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
    const mqtt = require('mqtt');

    let client;

    function connectMQTT() {
        // Create an MQTT client instance
        const clientId = "client_" + Math.random().toString(16).substr(2, 8);
        client = mqtt.connect('mqtt://172.18.3.70:1883', {
            clientId: clientId,
            // username: 'fabian',
            // password: '1234'
        });

        // Set callback handlers
        client.on('connect', onConnect);
        client.on('message', onMessageArrived);
        client.on('close', onConnectionLost);
        client.on('error', onFailure);
    }

    function onConnect() {
        console.log('Connected');
        client.subscribe('prod/quantity');
    }

    function onFailure(error) {
        console.error('Failed to connect to MQTT broker:', error.message);
        // Implement your own logic for handling connection failure, e.g., retry after a certain interval
        setTimeout(connectMQTT, 5000);
    }

    function onConnectionLost() {
        console.log('Connection Lost');
        // Implement your own logic for handling connection loss, e.g., retry after a certain interval
        setTimeout(connectMQTT, 5000);
    }

    function onMessageArrived(topic, message) {
        // update chart
        const payload = JSON.parse(message.toString());
        let line = payload[0].line;
        let items = payload;
        console.log(items);
        items.forEach(function(item) {
            if (item.line === line) {
                updateChart(item.line, item.items);
            }
        });
    }

    connectMQTT();
</script>
