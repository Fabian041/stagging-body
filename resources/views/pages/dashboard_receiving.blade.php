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
                        <span class="ml-3 text-muted" style="font-size:13px;">Refresh in <span id="refreshCountdown">120</span> dtk</span>
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
      <div class="modal-header" style="flex-direction: column; align-items: center; text-align: center;">
        <h5 class="modal-title">Detail Pick List</h5>
        <div id="detailPickListInfo" style="font-weight: bold; font-size: 15px;"></div>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="detailTable">
          <thead>
            <tr>
              <th style="width:60px;">No</th>
              <th>Part Number</th>
              <th>Back Number</th>
              <th id="qtyOrderedHeader">Qty Ordered (UOM)</th>
              <th>Qty Ordered (KBN)</th>
              <th>QTY/KBN</th>
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

    // Pindahkan garis merah (anotasi) ke belakang bar agar tidak menutupi data.
    // Target elemen <line>-nya langsung; label jam tetap di depan (di area padding atas).
    function sendAnnotationsBehind() {
        try {
            const svg = chartEl.querySelector('svg');
            if (!svg) {
                return;
            }
            const series = svg.querySelector('[class*="bar-series"], [class*="plot-series"], g.apexcharts-series');
            if (!series || !series.parentNode) {
                return;
            }
            const lines = svg.querySelectorAll('line');
            lines.forEach(function(ln) {
                const stroke = (ln.getAttribute('stroke') || '').toLowerCase().replace(/\s/g, '');
                const isRed = stroke === '#ff0000' || stroke === '#f00' || stroke === 'red' || stroke === 'rgb(255,0,0)';
                if (isRed) {
                    series.parentNode.insertBefore(ln, series);
                }
            });
        } catch (e) { /* abaikan, garis tetap di depan */ }
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
                // Garis anotasi (merah + harian) dipindah ke belakang bar agar tidak menutupi data
                mounted: function() { sendAnnotationsBehind(); },
                updated: function() { sendAnnotationsBehind(); },
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
                        showDetailModal(pickList, formatRange(dataPoint.y[0], dataPoint.y[1]));
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
    // Retry: pastikan garis merah di belakang bar setelah render selesai
    setTimeout(sendAnnotationsBehind, 500);
    setTimeout(sendAnnotationsBehind, 1500);

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
        setTimeout(sendAnnotationsBehind, 500);
    }, 1000); // setiap 30 detik, bisa ubah ke 1000 untuk real-time per detik

    // Auto refresh 120 dtk + tampilkan hitung mundur
    let refreshLeft = 120;
    const refreshEl = document.getElementById('refreshCountdown');
    setInterval(function() {
        refreshLeft -= 1;
        if (refreshLeft < 0) {
            refreshLeft = 0;
        }
        if (refreshEl) {
            refreshEl.textContent = refreshLeft;
        }
        if (refreshLeft <= 0) {
            window.location.reload();
        }
    }, 1000);
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
    currentMergeGroup = group;
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
let currentHoverGroup = [];
let currentMergeGroup = [];
// Jam kegiatan: rentang start to finish, cth "22 Sep, 10:00 - 12:00"
function formatRange(startMs, endMs) {
    const s = new Date(startMs);
    const e = new Date(endMs);
    const sStr = s.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    const sameDay = s.toDateString() === e.toDateString();
    const eStr = sameDay
        ? e.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        : e.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    return sStr + ' - ' + eStr;
}
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
    currentHoverGroup = dataPoint.group;
    sel.innerHTML = '<option value="">Pilih data...</option>' + dataPoint.group.map((g) => {
        const t = new Date(g.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        return `<option value="${g.pick_list}">${g.pick_list} - ${t}</option>`;
    }).join('');
    sel.onchange = function() {
        if (this.value) {
            const found = currentHoverGroup.find((g) => g.pick_list === this.value);
            const t = found ? formatRange(found.start, found.end) : '';
            hideHoverChooser();
            showDetailModal(this.value, t);
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
    const found = currentMergeGroup.find((g) => g.pick_list === val);
    const t = found ? formatRange(found.start, found.end) : '';
    hideHoverChooser();
    const mergeEl = document.getElementById('mergeModal');
    const doShow = () => showDetailModal(val, t);
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

function showDetailModal(pickList, timeLabel = '') {
    console.log("Showing details for pick list:", pickList);

    const infoEl = document.getElementById('detailPickListInfo');
    if (infoEl) {
        infoEl.textContent = timeLabel ? pickList + ' - ' + timeLabel : pickList;
    }

    getDetailModal().show();

    $('#detailTable tbody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');

    // Hilangkan .0000 / trailing zero: "100.0000" -> "100", "10.5000" -> "10.5"
    function formatQty(val) {
        if (val === null || val === undefined || val === '') {
            return '-';
        }
        const num = Number(String(val).replace(/,/g, '.'));
        if (isNaN(num)) {
            return val;
        }
        let s = num.toFixed(4);
        s = s.replace(/\.?0+$/, '');
        return s === '-0' ? '0' : s;
    }

    $.ajax({
        url: "{{ route('dashboard.receiving.detail') }}",
        type: 'GET',
        data: { pick_list: pickList },
        success: function(response) {
            if (!response || response.length === 0) {
                $('#detailTable tbody').html('<tr><td colspan="6" class="text-center">Data tidak ditemukan</td></tr>');
                return;
            }

            // Header dinamis: Qty Ordered (UOM), cth: Qty Ordered (PCS)
            const uoms = [...new Set(response.map((item) => (item.uom || '').toString().trim()).filter(Boolean))];
            const headerEl = document.getElementById('qtyOrderedHeader');
            if (headerEl) {
                headerEl.textContent = uoms.length === 1 ? `Qty Ordered (${uoms[0]})` : 'Qty Ordered (UOM)';
            }

            let rows = '';
            response.forEach((item, idx) => {
                rows += `<tr>
                    <td>${idx + 1}</td>
                    <td>${item.part_number ?? '-'}</td>
                    <td>${item.back_number ?? '-'}</td>
                    <td>${formatQty(item.qty_ordered)}</td>
                    <td>${item.qty_ordered_kbn !== null && item.qty_ordered_kbn !== undefined && item.qty_ordered_kbn !== '' ? formatQty(item.qty_ordered_kbn) : '-'}</td>
                    <td>${item.qty_per_kanban !== null && item.qty_per_kanban !== undefined && item.qty_per_kanban !== '' ? formatQty(item.qty_per_kanban) : '-'}</td>
                </tr>`;
            });
            $('#detailTable tbody').html(rows);
        },
        error: function() {
            $('#detailTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Gagal mengambil data</td></tr>');
        }
    });
}


</script>
@endpush



