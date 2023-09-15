@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow p-4" style="border-radius:8px">
                <span class="badge badge-primary mb-3 pt-3" style="border-radius:4px !important">
                    <h5 style="color: white">Jumat, 15 September 2023</h5>
                </span>
                <canvas id="dashboardChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
@endsection
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.3.3/chart.min.js"
    integrity="sha512-fMPPLjF/Xr7Ga0679WgtqoSyfUoQgdt8IIxJymStR5zV3Fyb6B3u/8DcaZ6R6sXexk5Z64bCgo2TYyn760EdcQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        //get search value from url
        var ctx = document.getElementById("dashboardChart").getContext("2d");

        var data = {
            labels: ['cycle 1', 'cycle 2', 'cycle 3', 'cycle 4', 'cycle 5'],
            datasets: [{
                type: 'bar',
                label: 'Bar Dataset',
                data: [30, 40, 87, 46, 81],
                backgroundColor: 'rgb(54, 162, 235)'

            }, {
                type: 'line',
                label: 'Target',
                data: [100, 100, 100, 100, 100],
                borderColor: 'red',
                backgroundColor: 'rgba(255, 99, 132, 0.2)'
            }],
        };

        var myBarChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                barValueSpacing: 20,
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 0,
                        }
                    }]
                }
            }
        });
    })
</script>
