@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== TABLE CARD ===== */
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
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

        /* Table itself */
        #example1 {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 12.5px !important;
        }

        #example1 thead th {
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

        #example1 tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
        }

        #example1 tbody tr:last-child td {
            border-bottom: none !important;
        }

        #example1 tbody tr:hover td {
            background: var(--bg) !important;
        }

        /* Pagination */
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

        /* Status picture badge */
        .bella-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .bella-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .bella-badge-red {
            background: #fee2e2;
            color: #dc2626;
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
            font-weight: 700 !important;
            color: var(--navy) !important;
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
            background: var(--danger-light) !important;
            color: var(--danger) !important;
            border-color: #fecaca !important;
        }

        .modal-body {
            padding: 16px 20px !important;
            background: var(--bg) !important;
            overflow-x: visible;
            overflow-y: auto;
        }

        .modal-footer {
            border-top: 1px solid var(--border) !important;
            padding: 12px 20px !important;
            background: var(--card) !important;
        }

        /* ===== FORM INSIDE MODAL ===== */
        .pis-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            align-items: start;
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 16px;
            margin-bottom: 12px;
            background: var(--card);
        }

        .pis-row>.d-flex {
            grid-column: 1 / -1;
        }

        .pis-row .part_number_aiia_group {
            grid-column: 1 / -1 !important;
        }

        .pis-row .part_number_customer_group {
            grid-column: 1 !important;
        }

        .pis-row .back_number_group {
            grid-column: 2 !important;
        }

        .pis-row .qty_group {
            grid-column: 1 !important;
        }

        .pis-row .part_kind_group {
            grid-column: 2 !important;
        }

        .pis-row .part_dock_show {
            grid-column: 1 / -1 !important;
        }

        .pis-row .pis_picture_group {
            grid-column: 1 / -1 !important;
        }

        .pis-row .form-group {
            margin-bottom: 0;
        }

        .pis-row .form-group label {
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            display: block;
        }

        .pis-row .form-control {
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

        .pis-row .form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .pis-row .pis_picture {
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .pis-row {
                grid-template-columns: 1fr;
            }

            .pis-row>.form-group {
                grid-column: 1 !important;
            }
        }

        /* ===== MODAL BACKDROP FIX ===== */
        .modal-backdrop {
            z-index: 1040 !important;
        }

        #myModal.modal {
            z-index: 1050 !important;
        }

        #myModal .modal-dialog {
            z-index: 1050;
            position: relative;
            pointer-events: auto !important;
        }

        #myModal .modal-content {
            pointer-events: auto !important;
            position: relative;
            z-index: 1;
        }

        #myModal .modal-body {
            pointer-events: auto !important;
        }

        #myModal .modal-body input,
        #myModal .modal-body select,
        #myModal .modal-body button,
        #myModal .modal-body textarea,
        #myModal .modal-body label {
            pointer-events: auto !important;
        }

        #myModal .modal-body input,
        #myModal .modal-body textarea {
            cursor: text !important;
        }

        #myModal .modal-body button,
        #myModal .modal-body select {
            cursor: pointer !important;
        }

        .select2-container {
            z-index: 1060 !important;
        }

        .select2-dropdown {
            z-index: 1060 !important;
        }

        .main-wrapper,
        .main-content {
            position: relative;
            z-index: auto !important;
        }
    </style>

    {{-- ===== TABLE CARD ===== --}}
    <div class="bella-table-card mt-3">
        <div class="bella-table-card-header">
            <div>
                <span class="bella-table-card-title"><i class="fas fa-database mr-2"></i>Data Master PIS</span>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Kelola master part PIS.</div>
            </div>
            <button type="button" class="act-btn primary" data-toggle="modal" data-target="#myModal"
                style="height:34px; padding:0 14px; font-size:12px; letter-spacing:.04em;">
                <i class="fas fa-plus" style="margin-right:5px;"></i> Add New PIS
            </button>
        </div>

        @if (Session::has('flash_message'))
            <div class="alert alert-success mx-3 mt-3 mb-0" style="font-size:12.5px;">
                <i class="fas fa-check-circle mr-1"></i> {!! session('flash_message') !!}
            </div>
        @endif

        <div class="table-responsive">
            <table id="example1" class="table bella-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Part No</th>
                        <th>Back No</th>
                        <th>Qty</th>
                        <th>Type</th>
                        <th>Destination</th>
                        <th>Picture</th>
                        <th>Status Picture</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($part_piss) &&
                            (is_array($part_piss) || $part_piss instanceof \Illuminate\Support\Collection) &&
                            count($part_piss) > 0)
                        @foreach ($part_piss as $part_pis)
                            <tr>
                                <td>{{ $part_pis->part_number_customer }}</td>
                                <td>{{ $part_pis->back_number }}</td>
                                <td>{{ $part_pis->qty_kanban }}</td>
                                <td>{{ $part_pis->part_kind }}</td>
                                <td>{{ $part_pis->part_dock }}</td>
                                <td>
                                    <a href="{{ url('pis/preview/' . $part_pis->img_path) }}" target="_blank"
                                        onclick="window.open('{{ url('pis/preview/' . $part_pis->img_path) }}', 'popup', 'height=540, width=650, top=120, left=350'); return false;"
                                        style="font-size:12px; color:var(--sky);">
                                        {{ $part_pis->img_path }}
                                    </a>
                                </td>
                                <td>
                                    <span
                                        class="bella-badge {{ $part_pis->validasi == 'Ada' ? 'bella-badge-green' : 'bella-badge-red' }}">
                                        <i class="fas {{ $part_pis->validasi == 'Ada' ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $part_pis->validasi }}
                                    </span>
                                </td>
                                <td>
                                    <a class="act-btn primary" href="{{ url('/pis/edit/' . $part_pis->id) }}"
                                        style="height:28px; padding:0 10px; font-size:11px; display:inline-flex; align-items:center; gap:4px; text-decoration:none; border-radius:5px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL ADD NEW PIS ===== --}}
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><b>CREATE PART</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="pisForm" role="form" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="img_path" value="">
                        <input type="hidden" name="id" value="">

                        <div id="pisRowsContainer">
                            <div class="pis-row" data-index="0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span style="font-size:12px; font-weight:700; color:var(--navy);">
                                        Master PIS #<span class="row-number">1</span>
                                    </span>
                                    <button type="button" class="act-btn danger remove-row"
                                        style="display:none; height:26px; padding:0 10px; font-size:11px;">
                                        <i class="fas fa-times"></i> Hapus
                                    </button>
                                </div>

                                <div class="form-group part_number_aiia_group">
                                    <label>Part Number AIIA</label>
                                    <input type="text" class="form-control part_number_aiia" name="part_number_aiia[]"
                                        placeholder="Part Number AIIA" onkeyup="this.value = this.value.toUpperCase()"
                                        autocomplete="off">
                                </div>

                                <div class="form-group part_number_customer_group">
                                    <label>Part Number Customer</label>
                                    <input type="text" class="form-control part_number_customer"
                                        name="part_number_customer[]" placeholder="Part Number Customer"
                                        onkeyup="this.value = this.value.toUpperCase()" autocomplete="off">
                                </div>

                                <div class="form-group back_number_group">
                                    <label>Back No</label>
                                    <input type="text" class="form-control back_number" name="back_number[]"
                                        placeholder="Back No" onkeyup="this.value = this.value.toUpperCase()"
                                        autocomplete="off">
                                </div>

                                <div class="form-group qty_group">
                                    <label>Qty</label>
                                    <input type="number" class="form-control qty_kanban" name="qty_kanban[]"
                                        placeholder="Qty" autocomplete="off">
                                </div>

                                <div class="form-group part_kind_group">
                                    <label>Type</label>
                                    <select class="form-control part_kind" name="part_kind[]">
                                        <option value="">-- Select Type --</option>
                                        <option value="OEM">OEM</option>
                                        <option value="DANDORY">DANDORY</option>
                                    </select>
                                </div>

                                <div class="form-group part_dock_show">
                                    <label>Destination</label>
                                    <select class="form-control part_dock" name="part_dock[]">
                                        <option value="">-- Select Destination --</option>
                                        <option value="TMMIN SPD">TMMIN SPD</option>
                                        <option value="TMMIN SPD-ADM">TMMIN SPD-ADM</option>
                                        <option value="43">43</option>
                                        <option value="53">53</option>
                                        <option value="1L">1L</option>
                                        <option value="1N">1N</option>
                                        <option value="HINO-SPD">HINO-SPD</option>
                                        <option value="SIM-SPD">SIM-SPD</option>
                                        <option value="MMKI">MMKI</option>
                                        <option value="MMKI-SPD">MMKI-SPD</option>
                                        <option value="6I">6I</option>
                                        <option value="TAM-SPD">TAM-SPD</option>
                                        <option value="TAM-ADM">TAM-ADM</option>
                                        <option value="TAM-HINO">TAM-HINO</option>
                                        <option value="ADM-AS">ADM-AS</option>
                                        <option value="ADM-KP">ADM-KP</option>
                                        <option value="YHA">YHA</option>
                                        <option value="ADM">ADM</option>
                                        <option value="TTI">TTI</option>
                                    </select>
                                </div>

                                <div class="form-group pis_picture_group">
                                    <label>Picture (.JPG)</label>
                                    <input type="file" class="pis_picture form-control-file" name="pis_picture[]"
                                        accept=".jpg,.jpeg,.png" style="font-size:12px;">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" id="addPisRow" class="act-btn success"
                                style="height:32px; padding:0 14px; font-size:12px;">
                                <i class="fas fa-plus" style="margin-right:4px;"></i> Tambah Baris
                            </button>
                            <div style="display:flex; gap:8px;">
                                <button type="button" class="act-btn primary" onclick="cekData()"
                                    style="height:32px; padding:0 16px; font-size:12px;">
                                    <i class="fas fa-save" style="margin-right:4px;"></i> SAVE ALL
                                </button>
                                <button type="reset" class="act-btn danger"
                                    style="height:32px; padding:0 16px; font-size:12px;">
                                    <i class="fas fa-redo-alt" style="margin-right:4px;"></i> RESET
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('custom-script')
    <script>
        $(document).ready(function() {
            $('input[type="search"]').removeClass('form-control').removeClass('input-sm');
            $('.dataTables_filter').addClass('pull-right');
            $('.pagination').addClass('pull-right');

            // Move modal to body root to fix z-index stacking
            $('#myModal').appendTo('body');
        });

        $('table').dataTable({
            "searching": true,
            "iDisplayLength": 10
        });
    </script>

    <script>
        $(function() {
            $('#example1').DataTable();
        });
    </script>

    <script>
        $(document).ready(function() {
            // Tambah baris baru
            $('#addPisRow').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var $container = $('#pisRowsContainer');
                var $lastRow = $container.find('.pis-row').last();
                var newIndex = $container.find('.pis-row').length;

                var $newRow = $lastRow.clone(false);
                $newRow.attr('data-index', newIndex);
                $newRow.find('.row-number').text(newIndex + 1);
                $newRow.find('input[type="text"], input[type="number"], input[type="file"]').val('');
                $newRow.find('.part_kind, .part_dock').val('');

                $container.append($newRow);

                if ($container.find('.pis-row').length > 1) {
                    $container.find('.pis-row .remove-row').show();
                }

                var $modalBody = $('#myModal .modal-body');
                $modalBody.animate({
                    scrollTop: $modalBody[0].scrollHeight
                }, 300);
            });

            // Hapus baris
            $('#pisRowsContainer').on('click', '.remove-row', function() {
                var $container = $('#pisRowsContainer');
                $(this).closest('.pis-row').remove();

                $container.find('.pis-row').each(function(idx) {
                    $(this).attr('data-index', idx);
                    $(this).find('.row-number').text(idx + 1);
                });

                if ($container.find('.pis-row').length === 1) {
                    $container.find('.pis-row .remove-row').hide();
                }
            });

            // Reset form
            $('#pisForm').on('reset', function() {
                setTimeout(function() {
                    var $container = $('#pisRowsContainer');
                    $container.find('.pis-row').not(':first').remove();
                    var $first = $container.find('.pis-row').first();
                    $first.attr('data-index', 0);
                    $first.find('.row-number').text(1);
                    $first.find('input[type="text"], input[type="number"], input[type="file"]').val(
                        '');
                    $first.find('.part_kind, .part_dock').val('');
                    $container.find('.remove-row').hide();
                }, 10);
            });

            $('#myModal').on('hidden.bs.modal', function() {
                $('#pisForm')[0].reset();
            });

            $('#myModal').on('show.bs.modal', function() {
                $(this).css('z-index', 1050);
                setTimeout(function() {
                    $('.modal-backdrop').css('z-index', 1040);
                }, 0);
            });

            $('#myModal').on('shown.bs.modal', function() {
                $('#myModal input').prop('readonly', false).prop('disabled', false);
                $('#myModal select').prop('disabled', false);
                $('#myModal textarea').prop('readonly', false).prop('disabled', false);
                $('#myModal .modal-dialog, #myModal .modal-content').css('pointer-events', 'auto');
            });
        });
    </script>

    <script>
        function cekData() {
            var $rows = $('#pisRowsContainer .pis-row');
            if ($rows.length === 0) {
                alert('Tidak ada data yang akan disimpan.');
                return;
            }

            var $saveBtn = $('button[onclick="cekData()"]');
            var originalText = $saveBtn.html();
            $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

            var currentIndex = 0;

            function processNextRow() {
                if (currentIndex >= $rows.length) {
                    $('#myModal').modal('hide');
                    window.location.reload();
                    return;
                }

                var $row = $($rows[currentIndex]);
                var part_number_aiia = $row.find('.part_number_aiia').val();
                var part_number_customer = $row.find('.part_number_customer').val();
                var part_kind = $row.find('.part_kind').val();
                var part_dock = $row.find('.part_dock').val();
                var back_number = $row.find('.back_number').val();
                var qty_kanban = $row.find('.qty_kanban').val();
                var pis_pictureInput = $row.find('.pis_picture')[0];
                var pis_picture = pis_pictureInput && pis_pictureInput.files[0] ? pis_pictureInput.files[0] : null;
                var part_number = part_number_aiia;

                if (!part_number_aiia || !part_number_customer || !back_number || !qty_kanban || !pis_picture) {
                    alert('Baris #' + (currentIndex + 1) +
                        ': lengkapi Part Number AIIA, Part Number Customer, Back No, Qty dan Picture.');
                    $saveBtn.prop('disabled', false).html(originalText);
                    return;
                }

                if (!part_kind || !part_dock) {
                    alert('Baris #' + (currentIndex + 1) + ': pilih Type dan Destination.');
                    $saveBtn.prop('disabled', false).html(originalText);
                    return;
                }

                var formData = new FormData();
                var path = "{{ url('/pis/addpis') }}";

                formData.append('part_number', part_number);
                formData.append('part_number_customer', part_number_customer);
                formData.append('back_number', back_number);
                formData.append('part_kind', part_kind);
                formData.append('part_dock', part_dock);
                formData.append('qty_kanban', qty_kanban);
                formData.append('pis_picture', pis_picture);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: path,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        currentIndex++;
                        processNextRow();
                    },
                    error: function(xhr) {
                        var errorMsg = 'Error saving data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                var parser = new DOMParser();
                                var doc = parser.parseFromString(xhr.responseText, 'text/html');
                                var errorElement = doc.querySelector('.alert-danger, .error');
                                if (errorElement) errorMsg = errorElement.textContent.trim();
                            } catch (e) {}
                        }
                        alert('Baris #' + (currentIndex + 1) + ' gagal disimpan: ' + errorMsg);
                        $saveBtn.prop('disabled', false).html(originalText);
                    }
                });
            }

            processNextRow();
        }
    </script>
@endsection
