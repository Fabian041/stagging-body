@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== PRODUCTION RESULT PAGE - SAME STYLE AS ViewMasterPis ===== */
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
            gap: 12px;
            position: relative;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 0;
        }

        .bella-table-card-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
            line-height: 1.5;
        }

        .bella-table-card-body {
            padding: 18px 20px 20px;
            background: var(--card);
        }

        .prod-result-wrap {
            width: 100%;
            margin: 0 auto;
        }

        .prod-icon-box {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            background: var(--bg);
            flex-shrink: 0;
        }

        .prod-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .prod-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .prod-filter-card {
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 14px;
            background: var(--bg);
            margin-bottom: 16px;
        }

        .prod-filter-form {
            display: flex;
            align-items: end;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0;
        }

        .prod-form-group {
            margin-bottom: 0;
        }

        .prod-form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .prod-form-group .form-control {
            height: 38px;
            min-width: 220px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--card) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 600;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .prod-form-group .form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .act-btn {
            height: 38px;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
            transition: .15s;
            white-space: nowrap;
            text-decoration: none !important;
        }

        .act-btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .act-btn.primary:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .act-btn.success {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .act-btn.success:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .act-btn.soft {
            background: var(--card);
            border-color: var(--border);
            color: var(--text-muted);
        }

        .act-btn.soft:hover {
            background: var(--bg);
            color: var(--text);
        }

        .act-btn.info {
            height: 32px;
            padding: 0 12px;
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
            font-size: 11px;
        }

        .act-btn.info:hover {
            background: #bae6fd;
            color: #075985;
        }

        .prod-tabs {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            background: var(--bg);
            margin-bottom: 16px;
        }

        .prod-tabs .nav-item {
            margin: 0 !important;
        }

        .prod-tabs .nav-link {
            min-height: 34px;
            border: 1px solid var(--border);
            border-radius: 5px !important;
            background: var(--card);
            color: var(--text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 14px;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            transition: .15s;
        }

        .prod-tabs .nav-link:hover {
            color: var(--text);
            background: #fff;
        }

        .prod-tabs .nav-link.active {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #fff !important;
            box-shadow: 0 6px 14px rgba(41, 71, 149, .16);
        }

        .prod-table-wrap {
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            overflow: hidden;
            background: var(--card);
        }

        .prod-table {
            width: 100%;
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            font-size: 12.5px !important;
        }

        .prod-table thead th {
            text-align: center !important;
            padding: 10px 12px !important;
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

        .prod-table tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border: none !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
            background: var(--card) !important;
        }

        .prod-table tbody tr:hover>td {
            background: var(--bg) !important;
        }

        .prod-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .prod-table tbody .detail-row>td {
            padding: 0 !important;
            border-bottom: none !important;
            background: #fbfdff !important;
        }

        .back-number-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 11px;
            border-radius: 99px;
            background: #eef2ff;
            color: var(--primary);
            border: 1px solid #dbe3ff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .line-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            padding: 4px 10px;
            border-radius: 99px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .detail-panel {
            padding: 14px;
            background: #fbfdff;
            border-bottom: 1px solid var(--border);
        }

        .detail-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .detail-panel-title {
            margin-bottom: 0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            padding: 3px 9px;
            border-radius: 999px;
            background: #eef2ff;
            border: 1px solid #dbe3ff;
            color: var(--primary);
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .detail-tools {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .detail-search-wrap {
            position: relative;
            min-width: 260px;
        }

        .detail-search-wrap i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 11px;
            pointer-events: none;
        }

        .detail-search-input {
            width: 100%;
            height: 34px;
            border: 1px solid var(--border) !important;
            border-radius: 6px !important;
            background: var(--card) !important;
            color: var(--text) !important;
            padding: 0 12px 0 30px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            font-weight: 600;
            outline: none !important;
            box-shadow: none !important;
        }

        .detail-search-input:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .detail-empty-filter {
            display: none;
            padding: 18px 12px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 700;
            background: var(--card);
            border-top: 1px solid var(--border);
        }

        .detail-table-wrap {
            border: 1px solid var(--border);
            border-radius: 7px;
            overflow: hidden;
            background: var(--card);
        }

        .detail-scroll-box {
            max-height: 285px;
            overflow-y: auto;
            overflow-x: auto;
        }

        .detail-scroll-box::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .detail-scroll-box::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .detail-scroll-box::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .detail-table {
            width: 100%;
            margin-bottom: 0 !important;
            border-collapse: collapse !important;
            font-size: 12px !important;
        }

        .detail-table thead th {
            padding: 9px 10px !important;
            background: var(--bg) !important;
            color: var(--text-muted) !important;
            border-bottom: 1px solid var(--border) !important;
            font-size: 10px !important;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: center !important;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .detail-table tbody td {
            padding: 9px 10px !important;
            border-bottom: 1px solid var(--border) !important;
            color: var(--text) !important;
            text-align: center !important;
            background: var(--card) !important;
        }

        .detail-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .empty-row {
            padding: 24px 16px !important;
            color: var(--text-muted) !important;
            font-size: 12px;
            font-weight: 600;
        }

        .pagination {
            margin-bottom: 0;
            gap: 4px;
            flex-wrap: wrap;
        }

        .pagination .page-link {
            min-width: 30px;
            height: 30px;
            border: 1px solid var(--border) !important;
            border-radius: 4px !important;
            background: var(--card) !important;
            color: var(--text-muted) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            padding: 0 8px !important;
            line-height: 28px !important;
            box-shadow: none !important;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary) !important;
            color: #fff !important;
            border-color: var(--primary) !important;
        }

        .pagination .page-item.disabled .page-link {
            opacity: .45;
        }

        /* ===== MODAL ===== */
        .modal-content {
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-md) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            overflow: hidden !important;
        }

        .modal-header {
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
            padding: 14px 20px !important;
        }

        .modal-title {
            font-size: 14px !important;
            font-weight: 800 !important;
            color: var(--navy) !important;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .modal-header .close {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border);
            border-radius: 5px;
            background: var(--card);
            opacity: 1 !important;
            color: var(--text-muted) !important;
            font-size: 16px !important;
            line-height: 26px;
            padding: 0;
            transition: .15s;
        }

        .modal-header .close:hover {
            background: #fee2e2;
            color: #dc2626 !important;
            border-color: #fecaca;
        }

        .modal-body {
            padding: 18px 20px !important;
            background: var(--card) !important;
        }

        .modal-footer {
            padding: 14px 20px !important;
            border-top: 1px solid var(--border) !important;
            background: var(--bg) !important;
        }

        .modal-form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .modal-form-group .form-control {
            height: 38px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 600;
            box-shadow: none !important;
        }

        .modal-form-group .form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        @media (max-width: 768px) {

            .bella-table-card-header,
            .prod-toolbar,
            .prod-filter-form {
                align-items: stretch;
                flex-direction: column;
            }

            .prod-header-left {
                width: 100%;
            }

            .act-btn,
            .prod-form-group,
            .prod-form-group .form-control {
                width: 100%;
            }

            .prod-tabs {
                justify-content: flex-start;
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .prod-tabs .nav-link {
                white-space: nowrap;
            }

            .detail-panel-head,
            .detail-tools,
            .detail-search-wrap {
                width: 100%;
            }

            .detail-scroll-box {
                max-height: 240px;
            }

            .bella-table-card-body {
                padding: 14px;
            }

            .prod-table,
            .detail-table {
                min-width: 720px;
            }
        }
    </style>

    <div class="prod-result-wrap">
        <div class="bella-table-card">
            <div class="bella-table-card-header">
                <div class="prod-header-left">
                    <div class="prod-icon-box">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div>
                        <div class="bella-table-card-title">Production Result</div>
                        <div class="bella-table-card-subtitle">Monitoring hasil scan produksi berdasarkan line, back number,
                            dan tanggal terakhir scan.</div>
                    </div>
                </div>
                <button type="button" class="act-btn success" data-toggle="modal" data-target="#exportMutationModal">
                    <i class="fas fa-file-excel"></i>
                    Export Production Result
                </button>
            </div>

            <div class="bella-table-card-body">
                {{-- Nav Tabs --}}
                {{-- tab = filter line client-side (bukan pane terpisah) biar tabel cuma dirender sekali --}}
                <ul class="nav nav-pills prod-tabs" id="myTab3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-line="">All</a>
                    </li>
                    @foreach ($lines as $line)
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-line="{{ $line->line }}">{{ $line->line }}</a>
                        </li>
                    @endforeach
                </ul>

                {{-- Filter Tanggal --}}
                <div class="prod-filter-card">
                    <div class="d-flex align-items-end flex-wrap" style="gap:10px;">
                        <form method="GET" action="{{ route('dashboard.prodResult') }}" class="prod-filter-form" style="margin:0;">
                            <div class="prod-form-group">
                                <label for="date">Filter Date</label>
                                <input type="date" id="date" name="date" class="form-control"
                                    value="{{ $selectedDate ?? \Carbon\Carbon::now()->toDateString() }}">
                            </div>
                            <button type="submit" class="act-btn primary">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                        </form>
                        <div class="prod-form-group">
                            <label for="bnFilter">Cari Back Number</label>
                            <input type="text" id="bnFilter" class="form-control" placeholder="Ketik back number..."
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="table-responsive prod-table-wrap" id="myTabContent2">
                    <table class="prod-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Line</th>
                                <th>Back Number</th>
                                <th>Last Scan Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowIndex = 1; @endphp
                            @forelse ($lines as $line)
                                @foreach ($line->items as $item)
                                    @php $cid = 'collapse-' . $loop->parent->index . '-' . $loop->index; @endphp
                                    <tr data-line="{{ $line->line }}">
                                        <td>{{ $rowIndex++ }}</td>
                                        <td><span class="line-badge">{{ $line->line }}</span></td>
                                        <td><span class="back-number-code">{{ $item['back_number'] }}</span></td>
                                        <td>{{ $item['last_date'] ?? '-' }}</td>
                                        <td class="text-center">
                                            <button type="button" class="act-btn info" data-toggle="collapse"
                                                data-target="#{{ $cid }}">
                                                <i class="fas fa-eye"></i>
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="detail-row">
                                        <td colspan="5" class="p-0">
                                            <div id="{{ $cid }}" class="collapse" data-bn="{{ $item['back_number'] }}"
                                                data-line="{{ $line->line }}">
                                                <div class="detail-panel">
                                                    <div class="detail-panel-head">
                                                        <div class="detail-panel-title">
                                                            <i class="fas fa-list-ul"></i>
                                                            Production Detail
                                                            <span class="detail-count-badge">{{ $item['count'] }} Serial</span>
                                                        </div>
                                                        <div class="detail-tools">
                                                            <div class="detail-search-wrap">
                                                                <i class="fas fa-search"></i>
                                                                <input type="text" class="detail-search-input"
                                                                    placeholder="Search serial number / date...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="detail-table-wrap">
                                                        <div class="detail-scroll-box">
                                                            <table class="detail-table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Serial Number</th>
                                                                        <th>Qty</th>
                                                                        <th>Date</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                        <div class="detail-empty-filter">No matching serial number found.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-row">No production result data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3" id="prodPager"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="exportMutationModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.mutation.export') }}" method="GET">
                    <input type="hidden" name="production_result" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Production Result</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="modal-form-group form-group">
                            <label>Dari Waktu</label>
                            <input type="datetime-local" class="form-control" name="from"
                                value="{{ now()->startOfDay()->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="modal-form-group form-group mb-0">
                            <label>Sampai Waktu</label>
                            <input type="datetime-local" class="form-control" name="to"
                                value="{{ now()->endOfDay()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="act-btn soft" data-dismiss="modal">Close</button>
                        <button type="submit" class="act-btn primary">
                            <i class="fas fa-download"></i>
                            Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- jQuery, DataTables, iziToast sudah dimuat layout (main.blade.php) — jangan dobel.
         Script di-push ke akhir body biar jalan setelah jQuery layout. --}}
    <script>
        var errorMessege = "{!! session('error') !!}";
        var successMessege = "{!! session('success') !!}";

        $(document).ready(function() {
            if (errorMessege) {
                iziToast.error({
                    title: 'Error! ' + errorMessege,
                    position: 'bottomRight'
                });
            } else if (successMessege) {
                iziToast.success({
                    title: 'Success! ' + successMessege,
                    position: 'bottomRight'
                });
            }

            $(document).on('input', '.detail-search-input', function() {
                const keyword = ($(this).val() || '').toLowerCase().trim();
                const detailPanel = $(this).closest('.detail-panel');
                const rows = detailPanel.find('.detail-data-row');
                let visibleRows = 0;

                rows.each(function() {
                    const rowText = $(this).text().toLowerCase();
                    const isMatch = !keyword || rowText.indexOf(keyword) !== -1;
                    $(this).toggle(isMatch);
                    if (isMatch) {
                        visibleRows++;
                    }
                });

                detailPanel.find('.detail-empty-filter').toggle(visibleRows === 0);
            });

            function escHtml(s) {
                return $('<div>').text(s == null ? '' : s).html();
            }
            var PROD_DETAIL_URL = @json(route('dashboard.prodResult.detail'));
            var PROD_DATE = @json($selectedDate ?? now()->toDateString());
            // ambil detail serial via AJAX saat pertama kali dibuka — tidak ikut di-load awal
            $(document).on('show.bs.collapse', '.collapse', function() {
                var $box = $(this);
                if ($box.data('built')) return;
                $box.data('built', true);
                var $tbody = $box.find('.detail-table tbody');
                $tbody.html('<tr><td colspan="3" class="empty-row">Memuat…</td></tr>');
                $.getJSON(PROD_DETAIL_URL, {
                    line: $box.data('line'),
                    back_number: $box.data('bn'),
                    date: PROD_DATE
                }).done(function(res) {
                    var rows = (res && res.details) || [];
                    if (!rows.length) {
                        $tbody.html('<tr><td colspan="3" class="empty-row">Tidak ada detail.</td></tr>');
                        return;
                    }
                    var html = '';
                    rows.forEach(function(d) {
                        html += '<tr class="detail-data-row"><td>' + escHtml(d.serial_number) + '</td><td>' +
                            escHtml(d.qty) + '</td><td>' + escHtml(d.date) + '</td></tr>';
                    });
                    $tbody.html(html);
                }).fail(function() {
                    $box.data('built', false);
                    $tbody.html('<tr><td colspan="3" class="empty-row">Gagal memuat detail.</td></tr>');
                });
            });

            $('.collapse').on('shown.bs.collapse', function() {
                $(this).find('.detail-search-input').trigger('focus');
            });

            $('.date-filter').on('change', function() {
                const selectedDate = $(this).val();
                const targetSelector = $(this).data('target');
                const tables = $(targetSelector);

                if (!selectedDate) {
                    tables.find('tbody tr').show();
                    return;
                }

                tables.each(function() {
                    $(this).find('tbody tr').each(function() {
                        const rowDate = $(this).data('date');
                        if (rowDate === selectedDate) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
            });
        });
    </script>

    <script>
        // Filter back number + pagination client-side untuk tab aktif (All & per-line)
        $(function () {
            const PER_PAGE = 10;
            let currentPage = 1;

            let lineFilter = '';

            function getActiveTable() {
                return document.querySelector('#myTabContent2 .prod-table');
            }

            // Kelompokkan tiap baris utama + baris detail (.detail-row) sesudahnya jadi satu unit
            function getUnits(table) {
                const tbody = table.querySelector('tbody');
                const units = [];
                let cur = null;
                Array.from(tbody.children).forEach(function (tr) {
                    if (tr.classList.contains('detail-row')) {
                        if (cur) cur.rows.push(tr);
                        return;
                    }
                    if (tr.querySelector('.empty-row')) { cur = null; return; }
                    const bnEl = tr.querySelector('.back-number-code');
                    cur = {
                        rows: [tr],
                        backno: (bnEl ? bnEl.textContent : '').toLowerCase().trim(),
                        line: (tr.getAttribute('data-line') || '').toLowerCase().trim()
                    };
                    units.push(cur);
                });
                return units;
            }

            function renderPager(totalPages) {
                const $pager = $('#prodPager');
                if (totalPages <= 1) { $pager.empty(); return; }
                let html = '<ul class="pagination">';
                html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') +
                    '"><a href="#" class="page-link" data-page="' + (currentPage - 1) + '">&lsaquo;</a></li>';
                for (let p = 1; p <= totalPages; p++) {
                    html += '<li class="page-item ' + (p === currentPage ? 'active' : '') +
                        '"><a href="#" class="page-link" data-page="' + p + '">' + p + '</a></li>';
                }
                html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') +
                    '"><a href="#" class="page-link" data-page="' + (currentPage + 1) + '">&rsaquo;</a></li>';
                html += '</ul>';
                $pager.html(html);
            }

            function apply() {
                const table = getActiveTable();
                if (!table) { $('#prodPager').empty(); return; }

                const keyword = ($('#bnFilter').val() || '').toLowerCase().trim();
                const units = getUnits(table);
                const filtered = units.filter(function (u) {
                    const okBn = !keyword || u.backno.indexOf(keyword) !== -1;
                    const okLine = !lineFilter || u.line === lineFilter;
                    return okBn && okLine;
                });

                const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
                if (currentPage > totalPages) currentPage = totalPages;

                units.forEach(function (u) { u.rows.forEach(function (r) { r.style.display = 'none'; }); });
                const start = (currentPage - 1) * PER_PAGE;
                filtered.slice(start, start + PER_PAGE).forEach(function (u) {
                    u.rows.forEach(function (r) { r.style.display = ''; });
                });

                renderPager(totalPages);
            }

            $(document).on('input', '#bnFilter', function () { currentPage = 1; apply(); });
            $(document).on('click', '#prodPager .page-link', function (e) {
                e.preventDefault();
                const p = parseInt($(this).attr('data-page'), 10);
                if (!isNaN(p) && p >= 1) { currentPage = p; apply(); }
            });
            $(document).on('click', '.prod-tabs .nav-link', function (e) {
                e.preventDefault();
                $('.prod-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                lineFilter = ($(this).data('line') || '').toString().toLowerCase().trim();
                currentPage = 1;
                apply();
            });

            apply();

            // Prefill dari global search: ?back_number=XXX
            const bnParam = new URLSearchParams(window.location.search).get('back_number');
            if (bnParam) {
                $('#bnFilter').val(bnParam).trigger('input');
            }
        });
    </script>
@endpush
