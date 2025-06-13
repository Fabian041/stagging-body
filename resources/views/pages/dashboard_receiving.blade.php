@extends('layouts.root.main')

@section('main')
    <div class="row">
        <div class="col mt-3 text-right">
            <div class="col-md-12">
            </div>
            {{-- <div class="col-md-12">
                <button class="btn btn-lg btn-danger" data-toggle="modal" data-target="#stockModal">Import Stock</button>
            </div> --}}
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header justify-content-center mt-3 ">
                    <h3>Receiving Dashborad Monitoring</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills justify-content-center" id="myTab3" role="tablist">
                       
                    </ul>
                    <div id="timelineChart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection



{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('assets/js/apexcharts.js') }}"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="<https://unpkg.com/mqtt/dist/mqtt.min.js>"></script>

<script>
    const options = {
        chart: {
            type: 'rangeBar',
            height: 500,
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '60%',
            }
        },
        xaxis: {
            type: 'datetime',
        },
        series: {!! json_encode($series) !!},
        tooltip: {
            x: {
                format: 'dd MMM yyyy HH:mm'
            }
        }
    };

    const chart = new ApexCharts(document.querySelector("#timelineChart"), options);
    chart.render();
</script>

