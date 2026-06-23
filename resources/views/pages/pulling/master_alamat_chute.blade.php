@extends('layouts.root.main')

@section('main')
    <div class="main-section">
        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="bella-table-card mt-3">
                        <div class="bella-table-card-header">
                            <div>
                                <span class="bella-table-card-title"><i class="fas fa-map-marker-alt mr-2"></i>Data Master Alamat Chute</span>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    Impor Excel: baris 1 = header. Format kolom: <strong>Part no customer</strong> | <strong>Back No</strong> | <strong>Part name</strong> | <strong>Alamat Chute</strong>.
                                    Untuk ganti seluruh master: <strong>Clear Data</strong> lalu impor ulang. Untuk menambah atau memperbarui part: impor langsung (Part no + Back No yang sama akan diperbarui).
                                </div>
                            </div>
                            <div class="mac-toolbar">
                                <a href="{{ route('pulling.master-alamat-chute.template') }}" class="act-btn"
                                    style="height:34px; padding:0 14px; font-size:12px; letter-spacing:.04em; text-decoration:none; display:inline-flex; align-items:center;">
                                    <i class="fas fa-file-download mr-1"></i> Template Excel
                                </a>
                                <form method="POST" action="{{ route('pulling.master-alamat-chute.import') }}" enctype="multipart/form-data"
                                    class="d-flex flex-wrap align-items-center m-0" style="gap:6px;">
                                    @csrf
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                        class="form-control form-control-sm"
                                        style="max-width:200px; height:34px; font-size:11px; padding:4px 8px;">
                                    <button type="submit" class="act-btn primary"
                                        style="height:34px; padding:0 14px; font-size:12px; letter-spacing:.04em;">
                                        <i class="fas fa-file-upload mr-1"></i> Import Excel
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('pulling.master-alamat-chute.clear-all') }}"
                                    id="formClearAllMasterChute" class="m-0">
                                    @csrf
                                    <button type="button" class="act-btn danger" id="btnClearAllMasterChute"
                                        style="height:34px; padding:0 14px; font-size:12px; letter-spacing:.04em;">
                                        <i class="fas fa-eraser mr-1"></i> Clear Data
                                    </button>
                                </form>
                                <button type="button" class="act-btn primary" id="btnOpenAddMasterChute"
                                    style="height:34px; padding:0 14px; font-size:12px; letter-spacing:.04em;">
                                    <i class="fas fa-plus mr-1"></i> Add Alamat Chute
                                </button>
                            </div>
                        </div>

                        <div class="card-body pt-0">

                            <div class="mt-3">
                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0 pl-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('import_notice'))
                                    <div class="alert alert-warning mb-0" style="white-space: pre-wrap; font-size:12px;">{{ session('import_notice') }}</div>
                                @endif

                                <div class="table-responsive">
                                    <table id="masterChuteTable" class="table bella-table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Part no customer</th>
                                                <th>Back No</th>
                                                <th>Part name</th>
                                                <th>Alamat Chute</th>
                                                <th style="width: 180px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($masters as $item)
                                                <tr>
                                                    <td>{{ $item->part_no_customer }}</td>
                                                    <td>{{ $item->back_no !== '' ? $item->back_no : '-' }}</td>
                                                    <td>{{ $item->part_name !== '' ? $item->part_name : '-' }}</td>
                                                    <td>{{ $item->alamat_chute }}</td>
                                                    <td>
                                                        <div class="d-flex justify-content-center align-items-center" style="gap:6px;">
                                                            <button
                                                                type="button"
                                                                class="act-btn primary btn-edit-master-chute"
                                                                data-master-id="{{ $item->id }}"
                                                                data-part-no-customer="{{ e($item->part_no_customer) }}"
                                                                data-back-no="{{ e($item->back_no) }}"
                                                                data-part-name="{{ e($item->part_name) }}"
                                                                data-alamat="{{ e($item->alamat_chute) }}"
                                                                title="Edit"
                                                                style="height:28px; padding:0 10px; font-size:11px;">
                                                                <i class="fas fa-edit mr-1"></i> Edit
                                                            </button>

                                                            <form method="POST" action="{{ route('pulling.master-alamat-chute.delete', $item->id) }}" class="form-delete-master-chute m-0">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button
                                                                    type="button"
                                                                    class="act-btn danger btn-delete-master-chute"
                                                                    data-part="{{ e($item->part_no_customer) }}"
                                                                    style="height:28px; padding:0 10px; font-size:11px;">
                                                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalConfirmMasterChute" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"
                data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title font-weight-bold" id="modalConfirmMasterChuteTitle">Konfirmasi</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body pt-2">
                            <div class="text-center mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                    style="width:56px; height:56px; background:#fde8e8; color:#c0392b;">
                                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                                </span>
                            </div>
                            <p id="modalConfirmMasterChuteMessage" class="mb-0 text-center" style="font-size:13px; line-height:1.5; color:var(--text);"></p>
                        </div>
                        <div class="modal-footer border-0 pt-0 justify-content-center" style="gap:8px;">
                            <button type="button" class="act-btn" data-dismiss="modal"
                                style="height:36px; padding:0 18px; font-size:12px;">
                                Batal
                            </button>
                            <button type="button" class="act-btn danger" id="btnConfirmMasterChuteOk"
                                style="height:36px; padding:0 18px; font-size:12px;">
                                <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalMasterChute" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalMasterChuteTitle"><b>TAMBAH MASTER ALAMAT CHUTE</b></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="formMasterChute" method="POST" action="{{ route('pulling.master-alamat-chute.store') }}">
                                @csrf
                                <span id="formMasterChuteMethodOverride"></span>

                                <div class="form-group">
                                    <label for="part_no_customer">Part no customer</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="part_no_customer"
                                        name="part_no_customer"
                                        maxlength="100"
                                        value="{{ old('part_no_customer') }}"
                                        placeholder="Contoh: 11310-BZ150-001"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="back_no">Back No</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="back_no"
                                        name="back_no"
                                        maxlength="50"
                                        value="{{ old('back_no') }}"
                                        placeholder="Contoh: CI13"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="part_name">Part name</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="part_name"
                                        name="part_name"
                                        maxlength="150"
                                        value="{{ old('part_name') }}"
                                        placeholder="Contoh: Timing Chain Cover"
                                    >
                                </div>

                                <div class="form-group mb-0">
                                    <label for="alamat_chute">Alamat Chute</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="alamat_chute"
                                        name="alamat_chute"
                                        maxlength="100"
                                        value="{{ old('alamat_chute') }}"
                                        placeholder="Contoh: CHUTE-01-A"
                                        required
                                    >
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-save mr-1"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('custom-script')
    <style>
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
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }

        .mac-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .mac-toolbar .act-btn,
        .mac-toolbar .form-control {
            height: 34px;
            font-size: 12px;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-table-card .dataTables_wrapper .dataTables_length,
        .bella-table-card .dataTables_wrapper .dataTables_filter,
        .bella-table-card .dataTables_wrapper .dataTables_info,
        .bella-table-card .dataTables_wrapper .dataTables_paginate {
            padding: 10px 16px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .bella-table-card .dataTables_wrapper .dataTables_length select,
        .bella-table-card .dataTables_wrapper .dataTables_filter input {
            height: 30px;
            border: 1px solid var(--border) !important;
            border-radius: 4px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-size: 12px !important;
            box-shadow: none !important;
        }

        #masterChuteTable thead th {
            text-align: center !important;
            padding: 9px 12px !important;
            color: var(--text-muted) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
            white-space: nowrap !important;
        }

        #masterChuteTable tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
        }

        #masterChuteTable tbody tr:hover td {
            background: var(--bg) !important;
        }

        .modal-backdrop {
            z-index: 1000 !important;
        }

        .modal-backdrop.fade.show {
            z-index: 1000 !important;
        }

        #modalMasterChute.modal {
            z-index: 20000 !important;
        }

        #modalMasterChute .modal-dialog {
            z-index: 20001;
            position: relative;
            pointer-events: auto !important;
        }

        #modalMasterChute .modal-content {
            pointer-events: auto !important;
            position: relative;
            z-index: 20002;
        }

        #modalMasterChute .modal-body {
            pointer-events: auto !important;
            overflow-x: visible;
            overflow-y: auto;
        }

        #modalMasterChute .modal-body input,
        #modalMasterChute .modal-body select,
        #modalMasterChute .modal-body button,
        #modalMasterChute .modal-body textarea,
        #modalMasterChute .modal-body label {
            pointer-events: auto !important;
        }

        .main-wrapper,
        .main-content {
            position: relative;
            z-index: auto !important;
        }

        #modalConfirmMasterChute.modal {
            z-index: 21000 !important;
        }

        #modalConfirmMasterChute .modal-dialog {
            z-index: 21001;
            position: relative;
            pointer-events: auto !important;
        }

        #modalConfirmMasterChute .modal-content {
            pointer-events: auto !important;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
    </style>

    <script>
        $(function() {
            const storeUrl = @json(route('pulling.master-alamat-chute.store'));
            const updateUrlBase = @json(url('/pulling/master-alamat-chute'));

            let pendingConfirmAction = null;

            function openConfirmMasterChute(options) {
                const title = options.title || 'Konfirmasi';
                const message = options.message || '';
                const confirmLabel = options.confirmLabel || 'Ya, Hapus';
                const confirmIcon = options.confirmIcon || 'fa-trash-alt';

                $('#modalConfirmMasterChuteTitle').text(title);
                $('#modalConfirmMasterChuteMessage').html(message);
                $('#btnConfirmMasterChuteOk').html(
                    '<i class="fas ' + confirmIcon + ' mr-1"></i> ' + confirmLabel
                );
                pendingConfirmAction = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                $('#modalConfirmMasterChute').modal('show');
            }

            $('#btnConfirmMasterChuteOk').on('click', function() {
                if (pendingConfirmAction) {
                    pendingConfirmAction();
                }
                pendingConfirmAction = null;
                $('#modalConfirmMasterChute').modal('hide');
            });

            $('#modalConfirmMasterChute').on('hidden.bs.modal', function() {
                pendingConfirmAction = null;
            });

            $('#modalConfirmMasterChute').on('show.bs.modal', function() {
                $(this).appendTo('body');
            });

            function clearMethodOverride() {
                $('#formMasterChuteMethodOverride').empty();
            }

            function prepareMasterChuteForm(mode, data) {
                if (mode === 'create') {
                    clearMethodOverride();
                    $('#formMasterChute').attr('action', storeUrl);
                    $('#modalMasterChuteTitle').html('<b>TAMBAH MASTER ALAMAT CHUTE</b>');
                    $('#part_no_customer').val('');
                    $('#back_no').val('');
                    $('#part_name').val('');
                    $('#alamat_chute').val('');
                } else {
                    $('#formMasterChute').attr('action', updateUrlBase + '/' + encodeURIComponent(data.id));
                    $('#formMasterChuteMethodOverride').html(
                        '<input type="hidden" name="_method" value="PUT">'
                    );
                    $('#modalMasterChuteTitle').html('<b>UBAH MASTER ALAMAT CHUTE</b>');
                    $('#part_no_customer').val(data.partNoCustomer || '');
                    $('#back_no').val(data.backNo || '');
                    $('#part_name').val(data.partName || '');
                    $('#alamat_chute').val(data.alamat || '');
                }
            }

            $('#btnOpenAddMasterChute').on('click', function() {
                prepareMasterChuteForm('create');
                $('#modalMasterChute').modal('show');
            });

            $(document).on('click', '.btn-edit-master-chute', function() {
                const $btn = $(this);
                prepareMasterChuteForm('edit', {
                    id: $btn.attr('data-master-id'),
                    partNoCustomer: $btn.attr('data-part-no-customer') || '',
                    backNo: $btn.attr('data-back-no') || '',
                    partName: $btn.attr('data-part-name') || '',
                    alamat: $btn.attr('data-alamat') || '',
                });
                $('#modalMasterChute').modal('show');
            });

            $(document).on('click', '.btn-delete-master-chute', function() {
                const $form = $(this).closest('.form-delete-master-chute');
                const part = $(this).attr('data-part') || '-';
                openConfirmMasterChute({
                    title: 'Hapus Data',
                    message:
                        'Hapus master alamat chute untuk part <strong>' + $('<span>').text(part).html() + '</strong>?<br><br>' +
                        '<span style="color:var(--text-muted); font-size:12px;">Tindakan ini tidak dapat dibatalkan.</span>',
                    confirmLabel: 'Ya, Hapus',
                    confirmIcon: 'fa-trash-alt',
                    onConfirm: function() {
                        $form[0].submit();
                    },
                });
            });

            $('#btnClearAllMasterChute').on('click', function() {
                const $form = $('#formClearAllMasterChute');
                const rowCount = $('#masterChuteTable tbody tr').length;
                const countHint = rowCount > 0
                    ? '<br><br>Saat ini terdapat sekitar <strong>' + rowCount + '</strong> baris data di tabel.'
                    : '';
                openConfirmMasterChute({
                    title: 'Clear Data',
                    message:
                        'Hapus <strong>seluruh</strong> data master alamat chute?' +
                        countHint +
                        '<br><br>Setelah dihapus, Anda dapat mengimpor ulang file Excel terbaru.' +
                        '<br><br><span style="color:#c0392b; font-size:12px;">Tindakan ini tidak dapat dibatalkan.</span>',
                    confirmLabel: 'Ya, Hapus Semua',
                    confirmIcon: 'fa-eraser',
                    onConfirm: function() {
                        $form[0].submit();
                    },
                });
            });

            $('#modalMasterChute').on('hidden.bs.modal', function() {
                clearMethodOverride();
                $('#formMasterChute').attr('action', storeUrl);
                $('#modalMasterChuteTitle').html('<b>TAMBAH MASTER ALAMAT CHUTE</b>');
            });

            $('#modalMasterChute').on('show.bs.modal', function() {
                $(this).appendTo('body');
                $('.modal-backdrop').remove();
                $(this).css('z-index', 20000);
            });

            $('#modalMasterChute').on('shown.bs.modal', function() {
                $('#modalMasterChute input').prop('readonly', false).prop('disabled', false);
                $('#modalMasterChute .modal-dialog, #modalMasterChute .modal-content').css('pointer-events', 'auto');
            });

            try {
                if ($('#masterChuteTable').length) {
                    $('#masterChuteTable').DataTable({
                        language: {
                            emptyTable: 'Belum ada data master.',
                            zeroRecords: 'Belum ada data master.',
                        },
                    });
                }
            } catch (e) {
                console.warn('masterChuteTable DataTable:', e);
            }
        });
    </script>
@endsection
