@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-12 m-auto">
            <div class="card card-info shadow" style="border-radius:20px">
                <div class="card-body">
                    <h4 class="card-title mt-3 mb-4 text-dark text-center">LOADING LIST DETAIL</h4>

                    <div class="row mt-5 mb-4 m-auto">
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-dark" style="font-weight: 700">Loading
                                    List No. <p class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->number }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">PDS Number <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->pds_number }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Customer <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->name }}</p>
                                </li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-dark" style="font-weight: 700">Delivery Date <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->delivery_date }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Shipping Date <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->shipping_date }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Cycle <p class="text-right"
                                        style="display: inline;">
                                        : {{ $loadingListDetail->cycle }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="card card-danger mt-3 shadow" style="border-radius:10px">
        <div class="card-body">
            <h5 class="card-title mt-3 text-dark text-center">DETAILS</h5>
            <table class="table table-responsive-lg" id="loadingList" style="width: 100%">
                <thead>
                    <tr>
                        <th class="text-center" style="width:140px;">Action</th>
                        <th class="text-center">Pulling Date</th>
                        <th class="text-center">Production Date</th>
                        <th class="text-center">Customer Part No.</th>
                        <th class="text-center">Internal Part No.</th>
                        <th class="text-center">Customer Back No.</th>
                        <th class="text-center">Internal Back No.</th>
                        <th class="text-center">Kanban Qty</th>
                        <th class="text-center">Total Scan</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody class="text-center"></tbody>
            </table>
        </div>
    </div>
@endsection
{{-- MODAL COMPARE --}}
<div class="modal fade" id="compareModal" tabindex="-1" role="dialog" aria-labelledby="compareModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 1100px;">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title" id="compareModalLabel">Compare Pulling vs Production</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:10px;">
                    <div class="text-muted small" id="compareMeta"></div>

                    <div class="d-flex" style="gap:10px; min-width: 320px;">
                        <input type="text" id="compareSearch" class="form-control"
                            placeholder="Cari serial / tanggal / qty...">
                        <select id="compareFilter" class="form-control" style="max-width: 220px;">
                            <option value="all">All</option>
                            <option value="match">Match</option>
                            <option value="missing_prod">Missing Production</option>
                            <option value="missing_pull">Missing Pulling</option>
                        </select>
                    </div>
                </div>

                <hr>

                <div style="max-height: 520px; overflow:auto; border:1px solid #eee; border-radius:10px;">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 2;">
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th style="width: 200px;">Serial</th>
                                <th>Production</th>
                                <th>Pulling</th>
                                <th style="width: 170px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="compareTbody"></tbody>
                    </table>
                </div>

                <div class="mt-2 small text-muted">
                    Note: “Missing Production” artinya serial ada di pulling (checkout), tapi supply sebelumnya tidak
                    ketemu.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="{{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}"></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
    $(document).ready(function() {
        const loadingList = "{{ $loadingListId }}";
        const requestOptions = {
            method: 'GET',
            headers: {
                "Content-type": "application/json"
            }
        };

        // ====== Compare helpers (robust) ======
        function decodeEscapedBr(s) {
            return String(s || '').replace(/&lt;br\s*\/?&gt;/gi, '<br>');
        }

        function toLines(html) {
            if (!html) return [];
            let s = decodeEscapedBr(html);

            // convert <br>, <br/>, <br /> to \n
            s = s.replace(/<br\s*\/?>/gi, '\n');

            // remove tags (optional) but keep text
            s = s.replace(/<[^>]*>/g, '');

            s = s.replace(/\r/g, '').trim();
            if (!s) return [];

            // if the whole text is N/A
            if (s.toUpperCase() === 'N/A' || s.toUpperCase().includes('N/A')) {
                // IMPORTANT: jangan auto kosong kalau ada serial lain + N/A,
                // jadi kita filter line yang betul-betul N/A saja
            }

            return s.split('\n')
                .map(x => x.trim())
                .filter(x => x && x.toUpperCase() !== 'N/A');
        }

        function extractSerial(line) {
            const m = String(line).match(/\[([^\]]+)\]/);
            return m ? m[1].trim() : null;
        }

        // keep ALL lines per serial (duplikat jangan ilang)
        function mapSerialToLines(html) {
            const map = new Map();
            const lines = toLines(html);
            lines.forEach(line => {
                const serial = extractSerial(line);
                if (!serial) return;
                if (!map.has(serial)) map.set(serial, []);
                map.get(serial).push(line);
            });
            return map;
        }

        function countItems(html) {
            return toLines(html).length;
        }

        let compareData = [];

        function rebuildCompareRows(filterText = '', filterMode = 'all') {
            const q = (filterText || '').toLowerCase();
            const tbody = $('#compareTbody');
            tbody.empty();

            let shown = 0;

            compareData.forEach(item => {
                const prodText = (item.prodLines || []).join(' ');
                const pullText = (item.pullLines || []).join(' ');
                const joined = `${item.serial} ${prodText} ${pullText}`.toLowerCase();

                if (q && !joined.includes(q)) return;
                if (filterMode === 'match' && item.status !== 'MATCH') return;
                if (filterMode === 'missing_prod' && item.status !== 'MISSING_PROD') return;
                if (filterMode === 'missing_pull' && item.status !== 'MISSING_PULL') return;

                shown++;

                const badge = item.status === 'MATCH' ? 'success' :
                    (item.status === 'MISSING_PROD' ? 'danger' : 'warning');

                const statusLabel = item.status === 'MATCH' ? 'Match' :
                    (item.status === 'MISSING_PROD' ? 'Missing Production' : 'Missing Pulling');

                const prodHtml = (item.prodLines && item.prodLines.length) ?
                    item.prodLines.map(l =>
                        `<div class="py-1" style="border-bottom:1px dashed #eee;">${l}</div>`).join(
                        '') :
                    '<span class="text-danger">N/A</span>';

                const pullHtml = (item.pullLines && item.pullLines.length) ?
                    item.pullLines.map(l =>
                        `<div class="py-1" style="border-bottom:1px dashed #eee;">${l}</div>`).join(
                        '') :
                    '<span class="text-danger">N/A</span>';

                tbody.append(`
                    <tr>
                        <td class="text-center">${shown}</td>
                        <td style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                            ${item.serial}
                        </td>
                        <td>${prodHtml}</td>
                        <td>${pullHtml}</td>
                        <td class="text-center"><span class="badge badge-${badge}">${statusLabel}</span></td>
                    </tr>
                `);
            });

            if (shown === 0) {
                tbody.html(`<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data.</td></tr>`);
            }

            $('#compareMeta').text(`Shown: ${shown} / Total serial: ${compareData.length}`);
        }

        // ====== DataTable (tetap seperti lama) ======
        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: false,
            ajax: {
                url: `{{ url('dashboard/getLoadingListDetail') }}` + '/' + loadingList,
                dataType: 'json',
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        // tombol compare + tombol details (yang lama)
                        const pullCount = countItems(row.pulling_date);
                        const prodCount = countItems(row.prod_date);

                        return `
                            <div class="d-flex flex-column align-items-center" style="gap:6px;">
                                <button class="btn btn-outline-primary btn-sm btn-compare" type="button">
                                    Compare
                                </button>
                                <button class="btn btn-info btn-sm details" type="button">Details</button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'pulling_date'
                },
                {
                    data: 'prod_date'
                },
                {
                    data: 'cust_partno'
                },
                {
                    data: 'int_partno'
                },
                {
                    data: 'cust_backno'
                },
                {
                    data: 'int_backno'
                },
                {
                    data: 'kbn_qty'
                },
                {
                    data: 'actual_kbn_qty'
                },
                {
                    data: 'edit',
                    orderable: false,
                    searchable: false
                },
            ],
            lengthMenu: [
                [5, 10, 100],
                [5, 10, 100]
            ],
        });

        // ====== Compare click ======
        $(document).on('click', '.btn-compare', function() {
            const tr = $(this).closest('tr');
            const row = table.row(tr).data();

            const pullMap = mapSerialToLines(row.pulling_date);
            const prodMap = mapSerialToLines(row.prod_date);

            const serialSet = new Set([...pullMap.keys(), ...prodMap.keys()]);
            const serials = Array.from(serialSet).sort();

            compareData = serials.map(serial => {
                const pullLines = pullMap.get(serial) || [];
                const prodLines = prodMap.get(serial) || [];

                let status = 'MATCH';
                if (pullLines.length && !prodLines.length) status = 'MISSING_PROD';
                else if (prodLines.length && !pullLines.length) status = 'MISSING_PULL';

                return {
                    serial,
                    prodLines,
                    pullLines,
                    status
                };
            });

            $('#compareSearch').val('');
            $('#compareFilter').val('all');

            rebuildCompareRows('', 'all');
            $('#compareModal').modal('show');
        });

        $('#compareSearch').on('input', function() {
            rebuildCompareRows($(this).val(), $('#compareFilter').val());
        });

        $('#compareFilter').on('change', function() {
            rebuildCompareRows($('#compareSearch').val(), $(this).val());
        });

        // ====== Details (EDCL) tetap seperti lama ======
        $(document).on('click', '.details', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                let rowData = row.data();

                fetch(`/edcl/detail/${rowData.loading_list_id}/${rowData.customer_part_id}`,
                        requestOptions)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status == 'success') {
                            row.child(formatDetails(data.data)).show();
                        } else if (data.status == 'error') {
                            notif('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.log(error.message);
                        notif('error', error);
                    });

                tr.addClass('shown');
            }
        });

        function formatDetails(data) {
            let rows = '';

            if (!data || data.length === 0) {
                rows = `
                    <tr>
                        <td class="text-center" colspan="8" style="color: dark-grey ; font-weight: bold;">
                            No data available
                        </td>
                    </tr>
                `;
            } else {
                rows = data.map((item) => `
                    <tr>
                        <td class="text-center">${item.id}</td>
                        <td class="text-center">${item.skid_no}</td>
                        <td class="text-center">${item.item_no}</td>
                        <td class="text-center">${item.serial}</td>
                        <td class="text-center">${item.kanban_id}</td>
                        <td class="text-center">${item.message}</td>
                        <td class="text-center">
                            <span class="badge badge-${item.message === 'Success - Confirm Manifest' ? 'success' : 'secondary'}">YES</span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm cancel-manifest">Cancel Manifest</button>
                        </td>
                    </tr>
                `).join('');
            }

            return `
                <table class="table">
                    <thead class="table-success">
                        <tr class="text-white">
                            <th class="text-center" style="color: #006400">ID</th>
                            <th class="text-center" style="color: #006400">Skid Number</th>
                            <th class="text-center" style="color: #006400">Item Number</th>
                            <th class="text-center" style="color: #006400">Serial Number</th>
                            <th class="text-center" style="color: #006400">Customer Kanban</th>
                            <th class="text-center" style="color: #006400">Message</th>
                            <th class="text-center" style="color: #006400">Confirm</th>
                            <th class="text-center" style="color: #006400">Action</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `;
        }

        // cancel manifest
        $(document).on('click', '.cancel-manifest', function() {
            let tr = $(this).closest('tr');
            let rowData = {
                id: tr.find('td:eq(0)').text().trim()
            };

            fetch(`/edcl/cancel/${rowData.id}`, requestOptions)
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {
                        notif('success', data.message);
                        table.ajax.reload(null, false);
                    } else if (data.status == 'error') {
                        notif('error', data.message);
                    }
                })
                .catch(error => {
                    console.log(error.message);
                    notif('error', error);
                });
        });

        // edit/save/cancel actual (tetap seperti lama)
        $(document).on('click', '#loadingList .edit', function() {
            $(this).closest('tr').find('.actual').hide();
            $(this).closest('tr').find('.editActual').show();
            $(this).closest('tr').find('.save').css({
                display: 'inline'
            });
            $(this).closest('tr').find('.cancel').show({
                display: 'inline'
            });
            $(this).closest('tr').find('.edit').hide();
        });

        $(document).on('click', '#loadingList .save', function() {
            let customerPart = $(this).closest('tr').find('.customerPart').html();
            let backNumber = $(this).closest('tr').find('.backNumber').html();
            if (backNumber == '') backNumber = 'null';

            let newActual = $(this).closest('tr').find('.editActual').val();

            fetch(`/loading-list/edit/${loadingList}/${customerPart}/${backNumber}/${newActual}`,
                    requestOptions)
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {
                        notif('success', data.message);
                        $(this).closest('tr').find('.editActual').hide();
                        $(this).closest('tr').find('.save').hide();
                        $(this).closest('tr').find('.cancel').hide();
                        $(this).closest('tr').find('.edit').show();
                        table.ajax.reload(null, false);
                    } else if (data.status == 'error') {
                        notif('error', data.message);
                    }
                })
                .catch(error => {
                    console.log(error.message);
                    notif('error', error);
                });
        });

        $(document).on('click', '#loadingList .cancel', function() {
            $(this).closest('tr').find('.actual').show();
            $(this).closest('tr').find('.editActual').hide();
            $(this).closest('tr').find('.save').hide();
            $(this).closest('tr').find('.cancel').hide();
            $(this).closest('tr').find('.edit').show();
        });

        function notif(type, message) {
            if (type == 'error') {
                iziToast.error({
                    title: 'Error! ' + message,
                    position: 'bottomRight'
                });
            } else if (type == 'success') {
                iziToast.success({
                    title: 'Success! ' + message,
                    position: 'bottomRight'
                });
            }
        }
    });
</script>
