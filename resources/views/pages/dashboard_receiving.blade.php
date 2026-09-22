@extends('layouts.root.blank')

@section('main')

    <div class="row">
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
                        <div id="timelineChartWrapper" style="position: relative;">
                            <div id="timelineChart"></div>
                            <!-- Rule 4: popup hover dengan jeda agar mouse sempat pindah ke select -->
                            <div id="hoverChooser" style="display:none; position:absolute; z-index:1060; background:#fff; border:1px solid #ddd; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.15); padding:8px 10px; min-width:230px; pointer-events:auto;">
                                <div id="hoverChooserTitle" style="font-weight:bold; font-size:13px; margin-bottom:4px;"></div>
                                <div id="hoverChooserInfo" style="font-size:12px; color:#555; margin-bottom:6px;"></div>
                                <select id="hoverChooserSelect" class="form-control form-control-sm"></select>
                            </div>
                        </div>
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
    #timelineChart .apexcharts-xaxis-annotation-label {
        pointer-events: none;
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
              <th>Picklist</th>
              <th>Part Number</th>
              <th>Back Number</th>
              <th>Qty Ordered</th>
              <th>UOM</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Rule 4: pilih data jika 1 kotak berisi >1 pengiriman -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mergeModalTitle">Pilih Pengiriman</h5>
      </div>
      <div class="modal-body">
        <select id="mergeSelect" class="form-control"></select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeMergeModal()">Batal</button>
        <button type="button" class="btn btn-primary" onclick="openMergeSelection()">Tampilkan</button>
      </div>
    </div>
  </div>
</div>


@push('scripts')<!-- jQuery harus di atas -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartEl = document.querySelector("#timelineChart");
    // Ambil dari PHP blade
    const startDate = new Date("{{ request('start_date') ?? now()->startOfWeek()->toDateString() }}");
    const endDate = new Date("{{ request('end_date') ?? now()->addDays(14)->toDateString() }}");

    const dailyAnnotations = [];
    let current = new Date(startDate);

    while (current <= endDate) {

        const adjustedTime = current.getTime() - (6 * 60 * 60 * 1000);

        dailyAnnotations.push({
            x: adjustedTime,
            borderColor: '#dddddd',
            strokeDashArray: 4,
            label: {
                text: current.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }),
                orientation: 'vertical',
                position: 'top',
                textAnchor: 'middle',
                offsetY: -70,
                borderColor: '#dddddd',
                style: {
                    color: '#555',
                    background: '#f8f9fa',
                    fontSize: '10px'
                }
            }
        });
        current.setDate(current.getDate() + 1); // next day
    }

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
                    if (dataPoint.isEmpty) {
                        hideHoverChooser();
                        return;
                    }
                    // Rule 4: jika 1 kotak berisi >1 data, tampilkan pilihan dulu
                    if (dataPoint.group && dataPoint.group.length > 1) {
                        showMergeChooser(dataPoint);
                        return;
                    }
                    const pickList = dataPoint.pick_list || null;
                    if (pickList) {
                        showDetailModal(pickList);
                    }
                },
                // Rule 4: hover -> popup khusus dengan jeda agar mouse sempat pindah ke select
                dataPointMouseEnter: function(event, chartContext, config) {
                    const dataPoint = config.w.config.series[config.seriesIndex].data[config.dataPointIndex];
                    if (!dataPoint || dataPoint.isEmpty) {
                        return;
                    }
                    if (dataPoint.group && dataPoint.group.length > 1) {
                        showHoverChooser(event, dataPoint);
                    }
                },
                dataPointMouseLeave: function() {
                    scheduleHideHoverChooser();
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
            },
            // Agar garis merah (now = 22 Sep) selalu masuk view 01-30 Sep,
            // paksa range mengikuti filter tanggal bukan mengikuti data
            min: new Date(startDate).setHours(0, 0, 0, 0),
            max: new Date(endDate).setHours(23, 59, 59, 999)
        },
        grid: {
            padding: {
                top: 100
            }
        },
        tooltip: {
            shared: false,
            intersect: true,
            followCursor: true,
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const point = w.config.series[seriesIndex].data[dataPointIndex];
                if (point.isEmpty) {
                    return `<div class="px-2 py-1 text-sm">
                        <strong>${point.x}</strong><br/>
                        Tidak ada data pengiriman
                    </div>`;
                }
                // Rule 4: info saja, pilihan ada di popup hover (bisa dimasuki mouse)
                if (point.group && point.group.length > 1) {
                    const list = point.group.map((g) => {
                        const t = new Date(g.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        return `${g.pick_list} (${t})`;
                    }).join('<br/>');
                    return `<div class="px-2 py-1 text-sm">
                        <strong>${point.x}</strong><br/>
                        ${point.group.length} pengiriman (0-30 mnt)<br/>
                        ${list}<br/>
                        <span style="color:#007bff;">Arahkan mouse ke kotak pilihan...</span>
                    </div>`;
                }
                return `<div class="px-2 py-1 text-sm">
                    <strong>${point.x}</strong><br/>
                    ${new Date(point.y[0]).toLocaleString()} - ${new Date(point.y[1]).toLocaleTimeString()}<br/>
                    ${point.meta || ''}
                </div>`;
            }
        },
        annotations: {
            xaxis: [
                // Garis merah waktu saat ini + jam di ujung atas (di luar bar)
                {
                    x: Date.now(),
                    borderColor: '#FF0000',
                    borderWidth: 2,
                    strokeDashArray: 0,
                    label: {
                        text: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                        orientation: 'horizontal',
                        position: 'top',
                        textAnchor: 'middle',
                        offsetY: -8,
                        borderColor: '#FF0000',
                        style: {
                            color: '#fff',
                            background: '#FF0000',
                            fontSize: '11px',
                            fontWeight: 'bold'
                        }
                    }
                },
                // Tambahan garis pembatas per tanggal
                ...dailyAnnotations
            ]
        },

        series: {!! json_encode($series) !!}
    };

    const chart = new ApexCharts(chartEl, options);
    chart.render();

    // ⏱ Perbarui anotasi garis setiap 30 detik
    setInterval(() => {
        const now = Date.now();

        chart.updateOptions({
            annotations: {
                xaxis: [{
                    x: now,
                    borderColor: '#FF0000',
                    borderWidth: 2,
                    strokeDashArray: 0,
                    label: {
                        text: new Date(now).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                        orientation: 'horizontal',
                        position: 'top',
                        textAnchor: 'middle',
                        offsetY: -8,
                        borderColor: '#FF0000',
                        style: {
                            color: '#fff',
                            background: '#FF0000',
                            fontSize: '11px',
                            fontWeight: 'bold'
                        }
                    }
                },
                ...dailyAnnotations
                ]
            }
        });
    }, 1000); // setiap 30 detik, bisa ubah ke 1000 untuk real-time per detik

    setInterval(function() {
    window.location.reload();
}, 120000); // 120000 ms = 2 menit
});

// Rule 4: klik kotak gabungan -> pilih salah satu dari 2+ data (satu instance agar hide/show konsisten)
let mergeModalInst = null;
let detailModalInst = null;
function getMergeModal() {
    if (!mergeModalInst) {
        mergeModalInst = new bootstrap.Modal(document.getElementById('mergeModal'));
    }
    return mergeModalInst;
}
function getDetailModal() {
    if (!detailModalInst) {
        detailModalInst = new bootstrap.Modal(document.getElementById('detailModal'));
    }
    return detailModalInst;
}
function showMergeChooser(dataPoint) {
    hideHoverChooser();
    const group = dataPoint.group || [];
    const titleEl = document.getElementById('mergeModalTitle');
    if (titleEl) {
        titleEl.textContent = 'Pilih Pengiriman - ' + dataPoint.x;
    }
    const sel = document.getElementById('mergeSelect');
    sel.innerHTML = group.map((g) => {
        const t = new Date(g.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        return `<option value="${g.pick_list}">${g.pick_list} - ${t}</option>`;
    }).join('');
    getMergeModal().show();
}

// Rule 4: popup hover dengan jeda 500ms agar mouse sempat pindah ke select
let hoverHideTimer = null;
function showHoverChooser(event, dataPoint) {
    if (hoverHideTimer) {
        clearTimeout(hoverHideTimer);
        hoverHideTimer = null;
    }
    const wrap = document.getElementById('timelineChartWrapper');
    const popup = document.getElementById('hoverChooser');
    const titleEl = document.getElementById('hoverChooserTitle');
    const infoEl = document.getElementById('hoverChooserInfo');
    const sel = document.getElementById('hoverChooserSelect');
    if (!wrap || !popup || !sel) {
        return;
    }
    titleEl.textContent = dataPoint.x + ' (' + dataPoint.group.length + ' pengiriman)';
    infoEl.textContent = 'Pilih salah satu, detail otomatis terbuka:';
    sel.innerHTML = '<option value="">Pilih data...</option>' + dataPoint.group.map((g) => {
        const t = new Date(g.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        return `<option value="${g.pick_list}">${g.pick_list} - ${t}</option>`;
    }).join('');
    sel.onchange = function() {
        if (this.value) {
            hideHoverChooser();
            showDetailModal(this.value);
            this.value = '';
        }
    };
    // Posisi popup dekat mouse, tetap di dalam wrapper
    const rect = wrap.getBoundingClientRect();
    let px = (event.clientX - rect.left) + 12;
    let py = (event.clientY - rect.top) + 12;
    px = Math.max(8, Math.min(px, rect.width - 250));
    py = Math.max(8, Math.min(py, rect.height - 130));
    popup.style.left = px + 'px';
    popup.style.top = py + 'px';
    popup.style.display = 'block';
}
function scheduleHideHoverChooser() {
    if (hoverHideTimer) {
        clearTimeout(hoverHideTimer);
    }
    hoverHideTimer = setTimeout(hideHoverChooser, 500);
}
function hideHoverChooser() {
    if (hoverHideTimer) {
        clearTimeout(hoverHideTimer);
        hoverHideTimer = null;
    }
    const popup = document.getElementById('hoverChooser');
    if (popup) {
        popup.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById('hoverChooser');
    if (popup) {
        popup.addEventListener('mouseenter', function() {
            if (hoverHideTimer) {
                clearTimeout(hoverHideTimer);
                hoverHideTimer = null;
            }
        });
        popup.addEventListener('mouseleave', scheduleHideHoverChooser);
    }
});

function closeMergeModal() {
    const mergeEl = document.getElementById('mergeModal');
    if (!mergeEl) {
        return;
    }
    try {
        getMergeModal().hide();
    } catch (e) {
        mergeEl.style.display = 'none';
        mergeEl.classList.remove('show');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
    }
}

function openMergeSelection() {
    const sel = document.getElementById('mergeSelect');
    const val = sel ? sel.value : null;
    if (!val) {
        return;
    }
    hideHoverChooser();
    const mergeEl = document.getElementById('mergeModal');
    const doShow = () => showDetailModal(val);
    try {
        const inst = getMergeModal();
        let shown = false;
        const onceShow = () => {
            if (shown) {
                return;
            }
            shown = true;
            doShow();
        };
        const handler = function() {
            mergeEl.removeEventListener('hidden.bs.modal', handler);
            onceShow();
        };
        mergeEl.addEventListener('hidden.bs.modal', handler);
        inst.hide();
        // Fallback 600ms: pastikan merge + backdrop benar-benar hilang sebelum detail tampil
        setTimeout(onceShow, 600);
    } catch (e) {
        doShow();
    }
}

function showDetailModal(pickList) {
    console.log("Showing details for pick list:", pickList);

    getDetailModal().show();

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
                    <td>${item.pick_list}</td>
                    <td>${item.part_number}</td>
                    <td>${item.back_number}</td>
                    <td>${item.qty_ordered}</td>
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



