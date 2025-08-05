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

                    <form method="GET" class="form-inline mb-3">
                        <label for="area" class="mr-2">Area:</label>
                        <select id="area" name="area" class="form-control mr-5">
                            <option value="">Pilih Area</option>
                            <option value="unit" {{ request('area') == 'unit' ? 'selected' : '' }}>Unit</option>
                            <option value="body" {{ request('area') == 'body' ? 'selected' : '' }}>Body</option>
                        </select>
                        
                        <label for="start_date" class="mr-2">Tanggal Mulai:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control mr-5" value="{{ request('start_date') ?? now()->startOfWeek()->toDateString() }}">

                        <label for="end_date" class="mr-2">Tanggal Akhir:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control mr-5" value="{{ request('end_date') ?? now()->endOfWeek()->toDateString() }}">

                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </form>
                        <div id="timelineChart"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="legend mb-3">
    <strong>Status Legend:</strong>
    <ul class="list-inline mt-2">
        <li class="list-inline-item"><span style="background:#cccccc;" class="legend-box"></span> Sudah Order</li>
        {{-- <li class="list-inline-item"><span style="background:#007bff;" class="legend-box"></span> Dikemas</li>
        <li class="list-inline-item"><span style="background:#f52899;" class="legend-box"></span> Sedang Dijalan</li>
        <li class="list-inline-item"><span style="background:#ffc107;" class="legend-box"></span> Diterima Sebagian</li> --}}
        <li class="list-inline-item"><span style="background:#28a745;" class="legend-box"></span> Diterima Semua</li>
    </ul>
</div>
<style>
    .legend-box {
        display: inline-block;
        width: 16px;
        height: 16px;
        margin-right: 5px;
        vertical-align: middle;
    }
    .modal {
    z-index: 1055;
    }
    .modal-backdrop {
    z-index: 1050;
    }

</style>


@endsection

<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Pick List</h5>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="detailTable">
          <thead>
            <tr>
              <th>Part Number</th>
              <th>Back Number</th>
              <th>Qty Ordered</th>
              <th>Qty Confirmed</th>
              <th>UOM</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>


@push('scripts')<!-- jQuery harus di atas -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartEl = document.querySelector("#timelineChart");

    const options = {
        chart: {
            type: 'rangeBar',
            height: 700,
            zoom: {
                enabled: false
            },
            defaultLocale: 'id',
            locales: [{
                name: 'id',
                options: {
                    toolbar: {
                        exportToSVG: 'Unduh SVG',
                        exportToPNG: 'Unduh PNG',
                        exportToCSV: 'Unduh CSV',
                        menu: 'Menu'
                    },
                    datetime: {
                        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
                    }
                }
            }],
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    const dataPoint = config.w.config.series[config.seriesIndex].data[config.dataPointIndex];
                    const pickList = dataPoint.pick_list || null;
                    if (pickList) {
                        showDetailModal(pickList);
                    }
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: '60%'
            }
        },
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeUTC: false
            }
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
                x: Date.now(),
                borderColor: '#FF0000',
                label: {
                    text: new Date().toLocaleTimeString(),
                    style: {
                        color: '#fff',
                        background: '#FF0000'
                    }
                }
            }]
        },
        series: {!! json_encode($series) !!}
    };

    const chart = new ApexCharts(chartEl, options);
    chart.render();

    // ⏱ Perbarui anotasi garis setiap 30 detik
    setInterval(() => {
        const now = Date.now();
        const nowLabel = new Date().toLocaleTimeString();

        chart.updateOptions({
            annotations: {
                xaxis: [{
                    x: now,
                    borderColor: '#FF0000',
                    label: {
                        text: nowLabel,
                        style: {
                            color: '#fff',
                            background: '#FF0000'
                        }
                    }
                }]
            }
        });
    }, 1000); // setiap 30 detik, bisa ubah ke 1000 untuk real-time per detik
});

function showDetailModal(pickList) {
    console.log("Showing details for pick list:", pickList);

    const modalEl = document.getElementById('detailModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    $('#detailTable tbody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

    $.ajax({
        url: "{{ route('dashboard.receiving.detail') }}",
        type: 'GET',
        data: { pick_list: pickList },
        success: function(response) {
            if (response.length === 0) {
                $('#detailTable tbody').html('<tr><td colspan="5" class="text-center">Data tidak ditemukan</td></tr>');
                return;
            }

            let rows = '';
            response.forEach(item => {
                rows += `<tr>
                    <td>${item.part_number}</td>
                    <td>${item.back_number}</td>
                    <td>${item.qty_ordered}</td>
                    <td>${item.qty_confirmed}</td>
                    <td>${item.uom}</td>
                </tr>`;
            });
            $('#detailTable tbody').html(rows);
        },
        error: function() {
            $('#detailTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Gagal mengambil data</td></tr>');
        }
    });
}


</script>
@endpush



