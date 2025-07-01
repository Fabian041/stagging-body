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
                        <div id="timelineChart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const options = {
        chart: {
            type: 'rangeBar',
            height: 700
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '60%'
            }
        },
        xaxis: {
            type: 'datetime'
        },
        tooltip: {
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const point = w.config.series[seriesIndex].data[dataPointIndex];
                return `<div class="px-2 py-1 text-sm">
                    <strong>${point.x}</strong><br/>
                    ${new Date(point.y[0]).toLocaleString()} - ${new Date(point.y[1]).toLocaleTimeString()}<br/>
                    ${point.meta || ''}
                </div>`;
            }
        },
        annotations: {
            xaxis: [{
                x: {{ $annotationTimestamp }},
                borderColor: '#FF0000',
                label: {
                    text: 'Hari Ini',
                    style: {
                        color: '#fff',
                        background: '#FF0000'
                    }
                }
            }]
        },
        series: {!! json_encode($series) !!}
    };

    const chart = new ApexCharts(document.querySelector("#timelineChart"), options);
    chart.render();
});
</script>

@endpush


