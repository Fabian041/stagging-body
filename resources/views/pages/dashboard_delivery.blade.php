@extends('layouts.root.minimal')

@section('main')
    <style>
        :root {
            --dm-bg: var(--bs-body-bg, #ffffff);
            --dm-card: var(--bs-light, #f8f9fa);
            --dm-border: color-mix(in srgb, var(--dm-bg) 70%, #6c757d 30%);
            --dm-text: var(--bs-body-color, #2f3542);
            --dm-muted: #6c757d;
            --dm-blue: #B5D4F4;
            --dm-yellow:hsl(58, 100%, 70%);
            --dm-green: #C0DD97;
            --dm-complete:rgb(180, 255, 198); 
            --dm-pink: #F7C1C1;
        }

        .delivery-dash {
            font-size: 14px;
            color: var(--dm-text);
        }

        .delivery-dash .card {
            border: 0.5px solid var(--dm-border);
        }

        .delivery-dash .tab-content {
            border: 0.5px solid var(--dm-border);
            border-top: 0;
            padding: 10px;
            background: var(--dm-bg);
        }

        .delivery-dash .nav-tabs .nav-link {
            padding: 6px 10px;
            font-size: 14px;
        }

        .delivery-dash .table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .delivery-dash .table td,
        .delivery-dash .table th {
            border: 0.5px solid var(--dm-border);
            padding: 4px 6px;
            vertical-align: middle;
        }

        .delivery-dash .form-control,
        .delivery-dash .custom-select {
            font-size: 12px;
            border-width: 0.5px;
        }

        .gantt-wrap {
            overflow: auto;
            border: 0.5px solid var(--dm-border);
            border-radius: 6px;
            max-height: 86vh;
        }

        .gantt-table {
            min-width: 1400px;
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 0;
        }

        .gantt-table thead th {
            position: sticky;
            top: 0;
            background: var(--dm-card);
            z-index: 1;
            border: 0.5px solid var(--dm-border);
            padding: 3px 4px;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .gantt-sticky-col {
            position: sticky;
            left: 0;
            background: var(--dm-bg);
            z-index: 2;
            min-width: 200px;
            max-width: 260px;
            text-align: left;
            font-weight: 500;
        }

        .gantt-time-col {
            min-width: 38px;
        }

        .gantt-track-cell {
            position: relative;
            padding: 0 !important;
            height: 46px;
            vertical-align: middle;
            border: 0.5px solid var(--dm-border);
        }

        .gantt-track {
            position: relative;
            height: 40px;
            margin: 3px 4px;
            border-radius: 2px;
            background-color: transparent;
            /* Garis vertikal 1px per jam — tidak hilang saat zoom out (bukan subpixel dari calc(... - 0.5px)). */
            background-image: linear-gradient(
                to right,
                var(--dm-border) 0,
                var(--dm-border) 1px,
                transparent 1px,
                transparent 100%
            );
            background-size: calc(100% / 24);
            background-repeat: repeat;
            background-position: 0 0;
        }

        .gantt-bar-wrap {
            position: absolute;
            top: 3px;
            min-width: 10px;
            height: 34px;
            box-sizing: border-box;
            cursor: pointer;
            z-index: 2;
        }

        .gantt-now-marker {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 0;
            z-index: 4;
            pointer-events: none;
        }

        .gantt-now-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            transform: translateX(-50%);
            background:rgb(155, 0, 0);
            box-shadow: 0 0 2px rgba(255, 0, 0, 0.35);
        }

        .gantt-now-label {
            position: absolute;
            left: 0;
            top: -15px;
            transform: translateX(-50%);
            font-size: 9px;
            line-height: 1.2;
            background: #ff0000;
            color: #fff;
            padding: 1px 5px;
            border-radius: 2px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .gantt-bar-stack {
            display: flex;
            width: 100%;
            height: 100%;
            border-radius: 3px;
            overflow: hidden;
            border: 0.5px solid var(--dm-border);
        }

        .gantt-seg { height: 100%; min-width: 2px; }
        .gantt-seg.ontime { background: var(--dm-complete); }
        .gantt-seg.delay { background: var(--dm-yellow); }
        .gantt-seg.empty { background: var(--dm-complete); }

        .legend-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin: 0 0 8px 0;
            padding: 0;
            list-style: none;
            font-size: 11px;
        }

        .legend-row li {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .legend-sq {
            width: 14px;
            height: 14px;
            border: 0.5px solid var(--dm-border);
            border-radius: 2px;
        }
    </style>

    <div class="row delivery-dash">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header py-2 d-flex align-items-center flex-wrap">
                    <div class="flex-grow-1" aria-hidden="true"></div>
                    <div class="text-center px-2">
                        <h3 class="mb-0" style="font-size: 32px;">
                            Daily Monitoring Delivery
                        </h3>
                    </div>
                    <div class="flex-grow-1 text-right mt-2 mt-md-0 pl-md-3">
                        <span class="text-muted d-block" style="font-size: 10px;">Waktu lokal</span>
                        <span id="deliveryDashLiveTime" class="font-weight-bold text-danger" style="font-size: 14px; font-variant-numeric: tabular-nums;">--:--:--</span>
                    </div>
                </div>
                <div class="card-body p-2">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#pane-chart" role="tab">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pane-master" role="tab">Master Cycle</a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#pane-legend" role="tab">Keterangan</a>
                        </li> -->
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pane-chart" role="tabpanel">
                            <form class="form-inline flex-wrap align-items-end mb-2" id="chartFilterForm" onsubmit="return false;" style="gap: 8px;">
                                <div class="mb-2 mb-sm-0">
                                    <label class="mb-0 mr-1 d-block" style="font-size: 11px;">Delivery date <span class="text-muted font-weight-normal">(kosong = semua)</span></label>
                                    <input type="date" class="form-control form-control-sm" id="filterDate" name="date" title="Kosongkan untuk mengambil semua tanggal (maks. 10.000 baris)">
                                </div>
                                <div class="mb-2 mb-sm-0">
                                    <label class="mb-0 mr-1 d-block" style="font-size: 11px;">Customer</label>
                                    <select class="form-control form-control-sm" id="filterCustomer" style="min-width: 200px;">
                                        <option value="">Semua</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mb-2 mb-sm-0" id="btnReloadChart">Muat ulang</button>
                            </form>

                            <ul class="legend-row">
                                <li><span class="legend-sq" style="background: var(--dm-complete);"></span> Progress 100%</li>
                                <li><span class="legend-sq" style="background: var(--dm-yellow);"></span> Belum selesai (&lt; 100%)</li>
                            </ul>

                            <div id="chartEmpty" class="alert alert-light border small mb-2 d-none" role="alert"></div>
                            <div class="gantt-wrap" id="ganttWrap">
                                <div id="ganttContainer"></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-master" role="tabpanel">
                            <form id="masterCycleForm" class="mb-3">
                                <div class="form-row align-items-end">
                                       <div class="col-md-4 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Customer</label>
                                        <select class="form-control form-control-sm" id="mcycleCustomerId" required>
                                            <option value="">Pilih customer</option>
                                            @foreach ($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Nama cycle</label>
                                        <select class="form-control form-control-sm" id="mcycleName" required>
                                            <option value="" selected disabled>Pilih cycle</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="mb-1" style="font-size: 11px;">Waktu (referensi)</label>
                                        <input type="time" class="form-control form-control-sm" id="mcycleTime" required step="60">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button type="submit" class="btn btn-sm btn-success" id="btnMasterSave">Simpan</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnMasterCancel">Batal edit</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered" id="masterCycleTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Customer</th>
                                            <th>Cycle</th>
                                            <th>Waktu</th>
                                            <th style="width: 130px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <h6 class="mb-2" style="font-size: 12px;">Daftar customer</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Nama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customers as $i => $c)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $c->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-legend" role="tabpanel">
                            <p class="small text-muted mb-2">Bar hijau untuk progress 100%, kuning untuk progress belum selesai.</p>
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Warna</th>
                                        <th>Arti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="legend-sq" style="background: var(--dm-complete);"></span></td>
                                        <td class="small">Progress sudah selesai (100%).</td>
                                    </tr>
                                    <tr>
                                        <td><span class="legend-sq" style="background: var(--dm-yellow);"></span></td>
                                        <td class="small">Progress belum selesai (&lt; 100%).</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="small mb-0">
                                <strong>Master cycle</strong> menyetel <em>nama cycle</em> dan <em>waktu referensi (time)</em> per customer.
                                Data loading list memakai kolom <code>cycle</code> yang dicocokkan ke <code>cycle_name</code> master berdasarkan
                                kombinasi <code>customer_id</code> + <code>cycle_name</code>.
                                Scan time dipakai untuk filter data, bukan acuan penentuan cycle.
                                Progress % cycle dihitung sebagai penjumlahan progress tiap data LL pada cycle tersebut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        $(function () {
            var stackedUrl = "{{ route('dashboard.delivery.stackedChart') }}";
            var loadingListUrl = "{{ route('loadingList.index') }}";
            var masterIndex = "{{ route('dashboard.delivery.masterCycles.index') }}";
            var masterStore = "{{ route('dashboard.delivery.masterCycles.store') }}";
            var masterBase = "{{ url('/dashboard/delivery/master-cycles') }}";
            var csrf = "{{ csrf_token() }}";

            var customers = @json($customers);
            var chartRows = [];
            var chartMergedByCustTime = [];
            var chartCustomerOrder = [];
            var editMasterId = null;
            var ganttNowTimer = null;

            function tickDeliveryHeaderClock() {
                var lbl = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                $('#deliveryDashLiveTime').text(lbl);
            }

            function getNowLeftPct() {
                var d = new Date();
                var hour = d.getHours();
                var minute = d.getMinutes();
                var second = d.getSeconds();
                var v = hour + minute / 60 + second / 3600;
                if (v < 6) {
                    v += 24;
                }
                var fr = v - 6;
                return Math.max(0, Math.min(100, (fr / 24) * 100));
            }

            function updateGanttNowMarkers() {
                var pct = getNowLeftPct();
                var lbl = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                $('#ganttContainer .gantt-now-marker').css('left', pct + '%');
                $('#ganttContainer .gantt-now-label').text(lbl);
            }

            function timeToMinutes(t) {
                var p = (t || '00:00').substring(0, 5).split(':');
                return parseInt(p[0], 10) * 60 + parseInt(p[1] || '0', 10);
            }

            function timeToFrac(t) {
                var p = (t || '06:00').substring(0, 5).split(':');
                var hour = parseInt(p[0], 10);
                var minute = parseInt(p[1] || '0', 10);
                var v = hour + minute / 60;
                if (v < 6) {
                    v += 24;
                }
                return v - 6;
            }

            function slotLabels24() {
                var a = [];
                for (var i = 0; i < 24; i++) {
                    var h = (6 + i) % 24;
                    a.push(String(h).padStart(2, '0') + ':00');
                }
                return a;
            }

            function escapeHtml(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function escapeAttr(s) {
                return String(s || '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;');
            }

            /*
             * Contoh setara untuk Chart.js (bar chart): tambahkan onClick pada opsi chart:
             *
             * options: {
             *   onClick: function (evt, elements, chart) {
             *     if (!elements.length) return;
             *     var i = elements[0].index;
             *     var ds = elements[0].datasetIndex;
             *     var label = chart.data.labels[i];
             *     var cycle = chart.data.datasets[ds].label;
             *     var params = new URLSearchParams();
             *     if (label) params.set('customer', label);
             *     if (cycle) params.set('cycle', cycle);
             *     if (deliveryDateFromFilter) params.set('delivery_date', deliveryDateFromFilter);
             *     window.location.href = loadingListUrl + '?' + params.toString();
             *   }
             * }
             */

            function buildGanttTooltip(row) {
                return [
                    'Cycle: ' + row.cycle_name + ' @ ' + row.cycle_time,
                    'Jumlah LL: ' + row.ll_count,
                    'Persentase progress: ' + row.progress_pct + '%',
                    'Target: ' + row.total_target,
                    'Done: ' + row.total_done
                ].join(' | ');
            }

            function mergeRowsForCustomerTime(rows) {
                var map = {};
                rows.forEach(function (r) {
                    var key = String(r.customer_id) + '|' + String(r.cycle_name) + '|' + String(r.cycle_time);
                    if (!map[key]) {
                        map[key] = {
                            customer_id: r.customer_id,
                            customer_name: r.customer_name,
                            cycle_time: r.cycle_time,
                            cycle_name: r.cycle_name,
                            on_time: 0,
                            delay: 0,
                            no_order: 0,
                            ll_count: 0,
                            total_target: 0,
                            total_done: 0,
                            progress_pct: 0,
                            mapping_source: r.mapping_source
                        };
                    }
                    var m = map[key];
                    m.on_time += r.on_time;
                    m.delay += r.delay;
                    m.no_order += r.no_order;
                    m.ll_count += r.ll_count;
                    m.total_target += r.total_target;
                    m.total_done += r.total_done;
                });
                return Object.keys(map).map(function (k) {
                    var m = map[k];
                    // Samakan dengan loading list: progress = total_done / total_target.
                    var pct = m.total_target > 0 ? (m.total_done / m.total_target) * 100 : 0;
                    m.progress_pct = Math.round(Math.max(0, Math.min(100, pct)) * 10) / 10;
                    return m;
                });
            }

            function renderGantt() {
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }

                var slots = slotLabels24();
                var html = '<table class="gantt-table"><thead><tr>';
                html += '<th class="gantt-sticky-col">Customer</th>';
                slots.forEach(function (s) {
                    html += '<th class="gantt-time-col">' + s + '</th>';
                });
                html += '</tr></thead><tbody>';

                var nowPct = getNowLeftPct();
                var nowLbl = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                chartCustomerOrder.forEach(function (cust, custIdx) {
                    html += '<tr><td class="gantt-sticky-col">' + escapeHtml(cust) + '</td>';
                    html += '<td class="gantt-track-cell" colspan="24"><div class="gantt-track">';

                    var buckets = chartMergedByCustTime.filter(function (m) {
                        return m.customer_name === cust;
                    });
                    buckets.sort(function (a, b) {
                        return timeToFrac(a.cycle_time) - timeToFrac(b.cycle_time);
                    });

                    buckets.forEach(function (row) {
                        var start = timeToFrac(row.cycle_time);
                        var durHours = 1;
                        var leftPct = (start / 24) * 100;
                        var widthPct = Math.max((durHours / 24) * 100, 0.8);
                        var progressWidth = Math.max(0, Math.min(100, row.progress_pct || 0));
                        var barClass = progressWidth < 100 ? 'delay' : 'ontime';
                        var tip = buildGanttTooltip(row).replace(/"/g, '&quot;');
                        var dateVal = $('#filterDate').val() || '';
                        var barTitle = tip + ' — Klik untuk buka Loading List';
                        html += '<div class="gantt-bar-wrap" style="left:' + leftPct + '%;width:' + widthPct + '%" title="' + barTitle + '"';
                        html += ' data-customer-name="' + escapeAttr(row.customer_name) + '"';
                        html += ' data-cycle="' + escapeAttr(row.cycle_name) + '"';
                        html += ' data-delivery-date="' + escapeAttr(dateVal) + '">';
                        html += '<div class="gantt-bar-stack">';
                        html += '<span class="gantt-seg ' + barClass + '" style="width:' + progressWidth + '%"></span>';
                        html += '</div></div>';
                    });

                    html += '<div class="gantt-now-marker" style="left:' + nowPct + '%">';
                    if (custIdx === 0) {
                        html += '<span class="gantt-now-label">' + escapeHtml(nowLbl) + '</span>';
                    }
                    html += '<div class="gantt-now-line"></div></div>';

                    html += '</div></td></tr>';
                });

                html += '</tbody></table>';
                $('#ganttContainer').html(html);

                ganttNowTimer = setInterval(updateGanttNowMarkers, 1000);
                updateGanttNowMarkers();
            }

            $('#ganttContainer').on('click', '.gantt-bar-wrap', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var customer = $(this).attr('data-customer-name') || '';
                var cycle = $(this).attr('data-cycle') || '';
                var deliveryDate = $(this).attr('data-delivery-date') || '';
                var params = new URLSearchParams();
                if (customer) {
                    params.set('customer', customer);
                }
                if (cycle) {
                    params.set('cycle', cycle);
                }
                if (deliveryDate) {
                    params.set('delivery_date', deliveryDate);
                }
                var qs = params.toString();
                window.location.href = loadingListUrl + (qs ? '?' + qs : '');
            });

            function loadStackedChart() {
                var params = {
                    date: $('#filterDate').val() || '',
                    customer_id: $('#filterCustomer').val() || ''
                };

                $('#chartEmpty').addClass('d-none');
                if (ganttNowTimer) {
                    clearInterval(ganttNowTimer);
                    ganttNowTimer = null;
                }
                $('#ganttContainer').empty();

                $.get(stackedUrl, params, function (res) {
                    chartRows = res.rows || [];
                    var hint = (res.meta && res.meta.hint) ? res.meta.hint : '';
                    if (!chartRows.length) {
                        var msg = hint || 'Tidak ada data delivery untuk filter ini.';
                        $('#chartEmpty').removeClass('d-none').text(msg);
                        return;
                    }

                    chartMergedByCustTime = mergeRowsForCustomerTime(chartRows);

                    var custSet = {};
                    chartMergedByCustTime.forEach(function (m) {
                        custSet[m.customer_name] = true;
                    });
                    chartCustomerOrder = Object.keys(custSet).sort(function (a, b) {
                        return a.localeCompare(b);
                    });

                    renderGantt();
                }).fail(function () {
                    $('#chartEmpty').removeClass('d-none').text('Gagal memuat Gantt.');
                });
            }

            $('#btnReloadChart').on('click', loadStackedChart);
            $('#filterDate, #filterCustomer').on('change', loadStackedChart);

            // Default ke tanggal hari ini agar tidak langsung menarik seluruh data historis.
            if (!$('#filterDate').val()) {
                $('#filterDate').val(new Date().toISOString().slice(0, 10));
            }

            function renderMasterTable(rows) {
                var $tb = $('#masterCycleTable tbody');
                $tb.empty();
                if (!rows.length) {
                    $tb.append('<tr><td colspan="5" class="text-center text-muted">Belum ada master cycle.</td></tr>');
                    return;
                }
                rows.forEach(function (r, i) {
                    var cust = r.customer_name || (r.customer_id ? '#' + r.customer_id : '-');
                    $tb.append(
                        '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + cust + '</td>' +
                        '<td>' + r.cycle_name + '</td>' +
                        '<td>' + r.time + '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary py-0 btn-edit-master" data-id="' + r.id + '">Edit</button> ' +
                        '<button type="button" class="btn btn-sm btn-outline-danger py-0 btn-del-master" data-id="' + r.id + '">Hapus</button>' +
                        '</td>' +
                        '</tr>'
                    );
                });
            }

            function fetchMasters() {
                $.get(masterIndex, function (res) {
                    var rows = (res && res.data) ? res.data : [];
                    renderMasterTable(rows);
                });
            }

            $('#masterCycleForm').on('submit', function (e) {
                e.preventDefault();
                var payload = {
                    _token: csrf,
                    cycle_name: $('#mcycleName').val().trim(),
                    time: $('#mcycleTime').val(),
                    customer_id: $('#mcycleCustomerId').val()
                };

                if (editMasterId) {
                    $.ajax({
                        url: masterBase + '/' + editMasterId,
                        type: 'PUT',
                        data: payload,
                        success: function () {
                            editMasterId = null;
                            $('#btnMasterSave').text('Simpan');
                            $('#btnMasterCancel').addClass('d-none');
                            $('#masterCycleForm')[0].reset();
                            fetchMasters();
                            loadStackedChart();
                        }
                    });
                } else {
                    $.post(masterStore, payload, function () {
                        $('#masterCycleForm')[0].reset();
                        fetchMasters();
                        loadStackedChart();
                    });
                }
            });

            $('#btnMasterCancel').on('click', function () {
                editMasterId = null;
                $('#btnMasterSave').text('Simpan');
                $('#btnMasterCancel').addClass('d-none');
                $('#masterCycleForm')[0].reset();
            });

            $('#masterCycleTable').on('click', '.btn-del-master', function () {
                var id = $(this).data('id');
                if (!confirm('Hapus master cycle ini?')) {
                    return;
                }
                $.ajax({
                    url: masterBase + '/' + id,
                    type: 'DELETE',
                    data: { _token: csrf },
                    success: function () {
                        fetchMasters();
                        loadStackedChart();
                    }
                });
            });

            $('#masterCycleTable').on('click', '.btn-edit-master', function () {
                var id = $(this).data('id');
                $.get(masterIndex, function (res) {
                    var rows = (res && res.data) ? res.data : [];
                    var row = rows.find(function (r) { return String(r.id) === String(id); });
                    if (!row) {
                        return;
                    }
                    editMasterId = id;
                    // cycle dropdown: fallback jika data lama bukan 1-5
                    if ($('#mcycleName option[value="' + row.cycle_name + '"]').length) {
                        $('#mcycleName').val(row.cycle_name);
                    } else {
                        if ($('#mcycleName option[value=""]').length) {
                            $('#mcycleName').val('');
                        }
                    }
                    $('#mcycleTime').val(row.time.length === 5 ? row.time : row.time.substring(0, 5));
                    $('#mcycleCustomerId').val(row.customer_id != null ? String(row.customer_id) : '');
                    $('#btnMasterSave').text('Update');
                    $('#btnMasterCancel').removeClass('d-none');
                });
            });

            fetchMasters();
            loadStackedChart();

            tickDeliveryHeaderClock();
            setInterval(tickDeliveryHeaderClock, 1000);
        });
    </script>
@endsection
