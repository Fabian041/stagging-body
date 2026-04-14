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
                        <th class="text-center" style="width:140px;">EDCL</th>
                        <th class="text-center" style="width:210px;">Kanban Details</th>
                        <th class="text-center">Customer Part No.</th>
                        <th class="text-center">Internal Part No.</th>
                        <th class="text-center">Customer Back No.</th>
                        <th class="text-center">Internal Back No.</th>
                        <th class="text-center">Kanban Qty</th>
                        <th class="text-center">Total Scan</th>
                        <th class="text-center" style="width:120px;"></th>
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
                                <th style="width: 110px;" class="text-center">Action</th>
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

        // ============================
        // Helpers parse controller HTML
        // ============================
        function decodeEscapedBr(s) {
            return String(s || '').replace(/&lt;br\s*\/?&gt;/gi, '<br>');
        }

        // ✅ filter serial "mask" seperti xxxx / xxx / ***** / ----
        function isMaskedSerial(serial) {
            if (!serial) return true;
            const s = String(serial).trim().toLowerCase();
            if (['xxxx', 'xxx', 'xx', 'x', '*****', '****', '***', '**', '*', '----', '---', '--', '-']
                .includes(s)) return true;
            if (/^(x{2,}|\*{2,}|\-{2,})$/i.test(s)) return true;
            return false;
        }

        function extractSerialFromText(text) {
            const m = String(text).match(/\[([^\]]+)\]/);
            const serial = m ? m[1].trim() : null;
            if (!serial) return null;
            if (isMaskedSerial(serial)) return null;
            return serial;
        }

        /**
         * Parse HTML pulling/prod yang berisi:
         * <span class="mline" data-mid="123">[SERIAL] - [DATE] (qty: X)</span><br>...
         * Return: [{mid, serial, text}]
         */
        function parseMutationHtml(html) {
            if (!html) return [];
            const s = decodeEscapedBr(html);

            const tmp = document.createElement('div');
            tmp.innerHTML = s;

            const nodes = tmp.querySelectorAll('span.mline');
            const arr = [];
            nodes.forEach(n => {
                const mid = n.getAttribute('data-mid') || '';
                const text = (n.textContent || '').trim();
                if (!text || text.toUpperCase() === 'N/A') return;

                const serial = extractSerialFromText(text);
                if (!serial) return; // skip xxxx

                arr.push({
                    mid,
                    serial,
                    text
                });
            });

            return arr;
        }

        function groupBySerial(items) {
            const map = new Map();
            items.forEach(it => {
                if (!map.has(it.serial)) map.set(it.serial, []);
                map.get(it.serial).push(it);
            });
            return map;
        }

        // ============================
        // Compare Context + Data
        // ============================
        let compareData = [];
        let compareCtx = {
            detailId: null
        };

        function rebuildCompareRows(filterText = '', filterMode = 'all') {
            const q = (filterText || '').toLowerCase();
            const tbody = $('#compareTbody');
            tbody.empty();

            let shown = 0;

            compareData.forEach(item => {
                const prodText = (item.prodItems || []).map(x => x.text).join(' ');
                const pullText = (item.pullItems || []).map(x => x.text).join(' ');
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

                const prodHtml = (item.prodItems && item.prodItems.length) ?
                    item.prodItems.map(x =>
                        `<div class="py-1" style="border-bottom:1px dashed #eee;">
            ${x.text}
         </div>`
                    ).join('') :
                    '<span class="text-danger">N/A</span>';

                // ✅ Pulling kolom: HANYA TEXT (tanpa tombol)
                const pullHtml = (item.pullItems && item.pullItems.length) ?
                    item.pullItems.map(x =>
                        `<div class="py-1" style="border-bottom:1px dashed #eee;">
            ${x.text}
         </div>`
                    ).join('') :
                    '<span class="text-danger">N/A</span>';

                // ✅ Action kolom: tombol delete per pulling mutation (stack)
                const actionHtml = (item.pullItems && item.pullItems.length) ?
                    item.pullItems.map(x => {
                        if (!x.mid) return `<div class="py-1" style="border-bottom:1px dashed #eee;">
                                <button class="btn btn-secondary btn-sm" disabled>Delete</button>
                            </div>`;
                        return `<div class="py-1" style="border-bottom:1px dashed #eee;">
                    <button class="btn btn-danger btn-sm btn-del-pulling-mutation"
                        data-mid="${x.mid}"
                        data-serial="${item.serial}">
                        Delete
                    </button>
                </div>`;
                    }).join('') :
                    '';

                tbody.append(`
                    <tr>
                        <td class="text-center">${shown}</td>
                        <td style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                            ${item.serial}
                        </td>
                        <td>${prodHtml}</td>
                        <td>${pullHtml}</td>
                        <td class="text-center"><span class="badge badge-${badge}">${statusLabel}</span></td>
                        <td class="text-center">${actionHtml}</td>
                    </tr>
                `);
            });

            if (shown === 0) {
                tbody.html(`<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data.</td></tr>`);
            }

            $('#compareMeta').text(`Shown: ${shown} / Total serial: ${compareData.length}`);
        }

        // ============================
        // DataTable main list
        // ============================
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
                    className: 'details-control',
                    orderable: false,
                    searchable: false,
                    defaultContent: '<button class="btn btn-info btn-sm details">Details</button>'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex flex-column align-items-center" style="gap:6px;">
                                <button class="btn btn-outline-danger btn-sm btn-compare" type="button">
                                    Compare Kanban
                                </button>
                            </div>
                        `;
                    }
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

        // ============================
        // Compare click: build modal data
        // ============================
        $(document).on('click', '.btn-compare', function() {
            const tr = $(this).closest('tr');
            const row = table.row(tr).data();

            // ✅ simpan LoadingListDetail id
            compareCtx.detailId = row.id;

            const pullItems = parseMutationHtml(row.pulling_date);
            const prodItems = parseMutationHtml(row.prod_date);

            const pullMap = groupBySerial(pullItems);
            const prodMap = groupBySerial(prodItems);

            const serialSet = new Set([...pullMap.keys(), ...prodMap.keys()]);
            const serials = Array.from(serialSet).sort();

            compareData = serials.map(serial => {
                const pullItems = pullMap.get(serial) || [];
                const prodItems = prodMap.get(serial) || [];

                let status = 'MATCH';
                if (pullItems.length && !prodItems.length) status = 'MISSING_PROD';
                else if (prodItems.length && !pullItems.length) status = 'MISSING_PULL';

                return {
                    serial,
                    prodItems,
                    pullItems,
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

        // ============================
        // ✅ Delete per mutation (1 record checkout)
        // ============================
        $(document).on('click', '.btn-del-pulling-mutation', function() {
            const mid = $(this).data('mid');
            const serial = $(this).data('serial');

            if (!mid) return;

            if (!compareCtx.detailId) {
                notif('error', 'Detail ID tidak ditemukan.');
                return;
            }

            if (!confirm(`Hapus 1 data pulling (mutation_id=${mid}) untuk serial ${serial}?`)) return;

            $.ajax({
                url: `/loading-list-detail/${compareCtx.detailId}/pulling-mutation/${mid}`,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.status === 'success') {
                        // ✅ hapus 1 mutation ini saja
                        compareData.forEach(cd => {
                            if (cd.serial === serial) {
                                cd.pullItems = (cd.pullItems || []).filter(x =>
                                    String(x.mid) !== String(mid));

                                if (cd.pullItems.length && !cd.prodItems.length) cd
                                    .status = 'MISSING_PROD';
                                else if (cd.prodItems.length && !cd.pullItems
                                    .length) cd.status = 'MISSING_PULL';
                                else cd.status = 'MATCH';
                            }
                        });

                        rebuildCompareRows($('#compareSearch').val(), $('#compareFilter')
                            .val());
                        table.ajax.reload(null, false);

                        notif('success', res.message || 'Deleted');
                    } else {
                        notif('error', res.message || 'Gagal delete');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal delete';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                        .message;
                    notif('error', msg);
                }
            });
        });

        // ============================
        // Edit / Save / Cancel Total Scan
        // pakai controller lama: /loading-list/edit/{loadingList}/{customerPart}/{backNumber}/{newActual}
        // ============================

        $(document).on('click', '#loadingList .edit', function() {
            const tr = $(this).closest('tr');

            tr.find('.actual').hide();
            tr.find('.editActual').show().focus().select();

            tr.find('.save').css({
                display: 'inline'
            });
            tr.find('.cancel').css({
                display: 'inline'
            });
            tr.find('.edit').hide();
        });

        $(document).on('keydown', '#loadingList .editActual', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('tr').find('.save').trigger('click');
            }
        });

        $(document).on('click', '#loadingList .save', function() {
            const btn = $(this);
            const tr = btn.closest('tr');

            let customerPart = $.trim(tr.find('.customerPart').text());
            let backNumber = $.trim(tr.find('.backNumber').text());
            let newActual = $.trim(tr.find('.editActual').val());

            if (backNumber === '' || backNumber === '-' || backNumber.toLowerCase() === 'null') {
                backNumber = 'null';
            }

            if (newActual === '' || isNaN(newActual) || parseInt(newActual) < 0) {
                notif('error', 'Total Scan harus berupa angka valid');
                tr.find('.editActual').focus();
                return;
            }

            fetch(`/loading-list/edit/${loadingList}/${encodeURIComponent(customerPart)}/${encodeURIComponent(backNumber)}/${parseInt(newActual)}`,
                    requestOptions)
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {
                        let newVal = parseInt(data.data);

                        notif('success', data.message);

                        tr.find('.actual').text(newVal).show();
                        tr.find('.editActual').val(newVal).hide();

                        tr.find('.save').hide();
                        tr.find('.cancel').hide();
                        tr.find('.edit').show();

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
            const tr = $(this).closest('tr');
            const oldVal = $.trim(tr.find('.actual').text());

            tr.find('.editActual').val(oldVal).hide();
            tr.find('.actual').show();

            tr.find('.save').hide();
            tr.find('.cancel').hide();
            tr.find('.edit').show();
        });

        // ============================
        // Details Row (EDCL) tetap seperti lama
        // ============================
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
