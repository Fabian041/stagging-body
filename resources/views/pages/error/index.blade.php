@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== PAGE FILTER CARD ===== */
        .bella-filter-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 14px;
        }

        .bella-filter-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .bella-filter-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-filter-card-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .bella-filter-card-body {
            padding: 16px 20px 18px;
            background: var(--card);
        }

        .bella-filter-row {
            display: grid;
            grid-template-columns: 1.25fr 1fr 1fr auto;
            gap: 14px;
            align-items: end;
        }

        .bella-filter-group {
            margin-bottom: 0;
        }

        .bella-filter-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .bella-filter-group .form-control,
        .bella-filter-group .custom-select {
            height: 34px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .bella-filter-group .form-control:focus,
        .bella-filter-group .custom-select:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .act-btn {
            height: 34px;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            cursor: pointer;
            transition: .15s;
            white-space: nowrap;
            box-shadow: none !important;
        }

        .act-btn.success {
            background: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .act-btn.success:hover {
            background: #bbf7d0;
            color: #166534;
        }

        /* ===== TABLE CARD ===== */
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 14px;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-table-card-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ===== DATATABLE OVERRIDES ===== */
        .bella-table-card .dataTables_wrapper {
            padding: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length,
        .bella-table-card .dataTables_wrapper .dataTables_filter {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .bella-table-card .dataTables_wrapper .dataTables_length label,
        .bella-table-card .dataTables_wrapper .dataTables_filter label {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length select,
        .bella-table-card .dataTables_wrapper .dataTables_filter input {
            height: 30px;
            border: 1px solid var(--border) !important;
            border-radius: 4px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            padding: 0 8px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
        }

        #loadingList {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12.5px !important;
            margin-bottom: 0 !important;
        }

        #loadingList thead th {
            text-align: center !important;
            padding: 9px 12px !important;
            color: var(--text-muted) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
            border-top: none !important;
            white-space: nowrap !important;
        }

        #loadingList tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
        }

        #loadingList tbody tr:last-child td {
            border-bottom: none !important;
        }

        #loadingList tbody tr:hover td {
            background: var(--bg) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate {
            padding: 10px 16px;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button {
            min-width: 30px !important;
            height: 30px !important;
            border: 1px solid var(--border) !important;
            border-radius: 4px !important;
            background: var(--card) !important;
            color: var(--text-muted) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 0 8px !important;
            margin: 0 2px !important;
            line-height: 28px !important;
            transition: .15s !important;
            box-shadow: none !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--bg) !important;
            color: var(--text) !important;
            border-color: var(--border) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary) !important;
            color: #fff !important;
            border-color: var(--primary) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .bella-table-card .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: .4 !important;
            cursor: not-allowed !important;
            background: var(--card) !important;
            color: var(--text-muted) !important;
        }

        .bella-table-card .dataTables_wrapper .dataTables_info {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .bella-table-card .dataTables_wrapper .dataTables_processing {
            background: rgba(255, 255, 255, .9) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--r) !important;
            color: var(--text-muted) !important;
            font-size: 12px !important;
            box-shadow: var(--shadow-md) !important;
            padding: 10px 20px !important;
        }

        @media (max-width: 992px) {
            .bella-filter-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {

            .bella-filter-card-header,
            .bella-table-card-header {
                padding: 12px 14px;
            }

            .bella-filter-card-body {
                padding: 14px;
            }

            .bella-filter-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .act-btn {
                width: 100%;
            }
        }
    </style>

    {{-- ===== FILTER CARD ===== --}}
    <div class="bella-filter-card">
        <div class="bella-filter-card-header">
            <div>
                <span class="bella-filter-card-title"><i class="fas fa-filter mr-2"></i>Filter Error Logs</span>
                <div class="bella-filter-card-subtitle">Pilih area dan periode untuk menampilkan data error.</div>
            </div>
        </div>
        <div class="bella-filter-card-body">
            <form>
                <div class="bella-filter-row">
                    <div class="bella-filter-group">
                        <label>Area</label>
                        <select class="custom-select" id="area">
                            <option value="">-- Select Area --</option>
                            <option value="admin">Admin</option>
                            <option value="mh">Material Handling</option>
                            <option value="prod">Production</option>
                            <option value="ppic">PPIC</option>
                            <option value="Packing">Packing</option>
                        </select>
                    </div>
                    <div class="bella-filter-group">
                        <label>Start Date</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="bella-filter-group">
                        <label>End Date</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="bella-filter-group">
                        <label class="d-block invisible">Export</label>
                        <button id="export-error-log" class="act-btn success" type="button">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== TABLE CARD ===== --}}
    <div class="bella-table-card">
        <div class="bella-table-card-header">
            <div>
                <span class="bella-table-card-title"><i class="fas fa-bug mr-2"></i>Application Error Logs</span>
                <div class="bella-table-card-subtitle">Monitoring histori error aplikasi berdasarkan area.</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table bella-table" id="loadingList" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Area</th>
                        <th class="text-center">Message</th>
                        <th class="text-center">Expected</th>
                        <th class="text-center">Scanned</th>
                        <th class="text-center">Date</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                </tbody>
            </table>
        </div>
    </div>
@endsection


{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: true,
            ajax: {
                url: `{{ url('error/getErrorLogs') }}`,
                dataType: 'json',
                data: function(d) {
                    d.area = $('#area').val() || '';
                    d.start_date = $('#start_date').val() || '';
                    d.end_date = $('#end_date').val() || '';
                }
            },
            columns: [{
                    data: 'area',
                },
                {
                    data: 'message',
                },
                {
                    data: 'expected',
                },
                {
                    data: 'scanned'
                },
                {
                    data: 'date'
                },
            ]
        });


        $('#area, #start_date, #end_date').on('change', function() {
            table.draw();
        });

        $('#export-error-log').on('click', function() {
            const area = $('#area').val() || '';
            const params = new URLSearchParams();
            if (area) {
                params.append('area', area);
            }
            const startDate = $('#start_date').val() || '';
            const endDate = $('#end_date').val() || '';
            if (startDate) {
                params.append('start_date', startDate);
            }
            if (endDate) {
                params.append('end_date', endDate);
            }

            const baseUrl = `{{ route('error.export') }}`;
            const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

            window.location.href = url;
        });
    });
</script>
