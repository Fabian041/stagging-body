@extends('layouts.root.main')

@section('main')

    @include('pages.master.partials.styles')

    <style>
        .mold-part-select-wrap>.select2-container {
            width: 100% !important;
        }

        .mold-part-select-wrap {
            position: relative;
        }

        .mold-part-select-wrap>.select2-container--default .select2-selection--multiple {
            min-height: 46px !important;
            height: auto !important;
            padding: 5px 7px;
            background: #fff !important;
            border: 1px solid #d8e1ee !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .mold-part-select-wrap>.select2-container--default.select2-container--focus .select2-selection--multiple,
        .mold-part-select-wrap>.select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: #0070b7 !important;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .12) !important;
        }

        .mold-part-select-wrap>.select2-container .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
            width: 100%;
            min-height: 34px;
            margin: 0;
            padding: 0 !important;
            overflow: visible !important;
        }

        .mold-part-select-wrap>.select2-container--default .select2-selection--multiple .select2-selection__choice {
            position: relative;
            margin: 0 !important;
            padding: 5px 10px 5px 29px !important;
            color: #294795 !important;
            font-size: 12px;
            font-weight: 600;
            line-height: 20px;
            background: #e8f4fd !important;
            border: 1px solid #b9dcf3 !important;
            border-radius: 6px !important;
            box-shadow: none !important;
        }

        .mold-part-select-wrap>.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            inset: 0 auto 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 23px;
            margin: 0 !important;
            padding: 0 !important;
            color: #0070b7 !important;
            background: rgba(0, 112, 183, .08) !important;
            border: 0 !important;
            border-right: 1px solid #b9dcf3 !important;
            border-radius: 5px 0 0 5px !important;
        }

        .mold-part-select-wrap>.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff !important;
            background: #0070b7 !important;
        }

        .mold-part-select-wrap>.select2-container .select2-search--inline {
            flex: 1 1 145px;
            min-width: 125px;
            margin: 0 !important;
            padding: 0;
            border: 0;
        }

        .mold-part-select-wrap>.select2-container .select2-search--inline .select2-search__field {
            width: 100% !important;
            min-width: 125px;
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
            margin: 0 !important;
            padding: 4px 8px !important;
            color: #1a2340;
            font-family: inherit;
            line-height: 22px;
            background: #f7faff !important;
            border: 1px solid #d8e1ee !important;
            border-radius: 6px;
            box-sizing: border-box !important;
            resize: none !important;
            overflow: hidden !important;
        }

        .mold-part-select-wrap .select2-dropdown {
            overflow: hidden;
            background: #fff !important;
            border: 1px solid #b9d3e8 !important;
            border-radius: 8px !important;
            box-shadow: 0 14px 35px rgba(41, 71, 149, .18) !important;
        }

        .mold-part-select-wrap .select2-search--dropdown {
            padding: 8px;
            background: #f7faff;
            border-bottom: 1px solid #e5edf6;
        }

        .mold-part-select-wrap .select2-search--dropdown .select2-search__field {
            height: 38px;
            padding: 7px 10px;
            color: #1a2340;
            font-family: inherit;
            border: 1px solid #d8e1ee !important;
            border-radius: 6px;
            outline: none;
        }

        .mold-part-select-wrap .select2-search--dropdown .select2-search__field:focus {
            border-color: #0070b7 !important;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .10);
        }

        .mold-part-select-wrap .select2-results__options {
            max-height: 240px !important;
            padding: 5px;
        }

        .mold-part-select-wrap .select2-results__option {
            margin: 2px 0;
            padding: 9px 11px;
            color: #33415c;
            font-size: 12.5px;
            border-radius: 6px;
        }

        .mold-part-select-wrap .select2-results__option[aria-selected="true"] {
            color: #294795 !important;
            background: #e8f4fd !important;
        }

        .mold-part-select-wrap .select2-results__option--highlighted[aria-selected] {
            color: #fff !important;
            background: #0070b7 !important;
        }

        /*
            |--------------------------------------------------------------------------
            | QR MODAL
            |--------------------------------------------------------------------------
            */

        .mold-qr-modal .modal-dialog {
            max-width: 430px;
        }

        .mold-qr-modal .modal-content {
            overflow: hidden;
            border: 0 !important;
            border-radius: 12px !important;
        }

        .mold-qr-modal .modal-header {
            padding: 16px 20px;
            background: #f7faff;
            border-bottom: 1px solid #e5edf6;
        }

        .mold-qr-modal .modal-title {
            color: #1a2340;
            font-size: 16px;
            font-weight: 700;
        }

        .mold-qr-modal .modal-body {
            background: #f8fafc;
        }

        .mold-qr-card {
            width: 100%;
            max-width: 330px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            background: #fff;
            border: 1px solid #d8e1ee;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(41, 71, 149, .10);
        }

        .mold-qr-image-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 270px;
            margin-bottom: 14px;
            padding: 10px;
            overflow: hidden;
            background: #fff;
            border-radius: 8px;
        }

        /*
             * PENTING:
             * Jangan pakai display:block !important pada img/canvas.
             * QRCode.js membuat canvas + img dan mengatur salah satunya hidden.
             */
        .mold-qr-container {
            display: none;
            width: 250px;
            height: 250px;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
        }

        .mold-qr-container img,
        .mold-qr-container canvas {
            width: 250px !important;
            height: 250px !important;
            max-width: 100% !important;
            margin: 0 auto;
        }

        .mold-qr-loading {
            color: #6b7a99;
            font-size: 13px;
        }

        .mold-qr-error {
            display: none;
            padding: 12px;
            color: #b42318;
            background: #fff1f0;
            border: 1px solid #ffd2cf;
            border-radius: 8px;
        }

        .mold-qr-tag {
            display: inline-block;
            padding: 7px 13px;
            color: #294795;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .04em;
            background: #e8f4fd;
            border: 1px solid #b9dcf3;
            border-radius: 7px;
        }

        /*
            |--------------------------------------------------------------------------
            | PHOTO
            |--------------------------------------------------------------------------
            */

        .mold-photo-field {
            overflow: hidden;
            border: 1px solid #d8e1ee;
            border-radius: 10px;
            background: #f8fbff;
        }

        .mold-photo-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 170px;
            padding: 12px;
            color: #8190aa;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid #e5edf6;
        }

        .mold-photo-preview img {
            width: 100%;
            height: 170px;
            object-fit: contain;
            border-radius: 7px;
            background: #fff;
        }

        .mold-photo-input {
            padding: 10px 12px;
            background: #fff;
        }

        .mold-photo-input input {
            width: 100%;
            font-size: 12px;
        }
    </style>


    <div class="container-fluid master-page">

        <div class="master-page-header">

            <div>
                <h1 class="master-page-title">
                    Master Mold
                </h1>

                <div class="master-page-subtitle">
                    Kelola mold, status penggunaan, dan internal part yang terhubung.
                </div>
            </div>


            <div class="master-actions">

                <a href="{{ route('injection.indexprdreport') }}" class="master-btn primary">
                    <i class="fas fa-external-link-alt"></i>
                    Open Injection
                </a>

                <a href="{{ route('dashboard.master.index') }}" class="master-btn">
                    <i class="fas fa-th-large"></i>
                    Master Home
                </a>

            </div>

        </div>


        @if (session('success'))
            <div class="alert alert-success master-alert">
                {{ session('success') }}
            </div>
        @endif


        @if ($errors->any())
            <div class="alert alert-danger master-alert">

                <strong>
                    Data mold belum dapat disimpan.
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach

                </ul>

            </div>
        @endif


        <div class="row g-3">

            {{-- FORM --}}
            <div class="col-lg-4">

                <div class="master-panel">

                    <div class="master-panel-header">

                        <h2 class="master-panel-title">

                            <i class="fas fa-shapes"></i>

                            {{ $editingMold ? 'Edit Mold' : 'Tambah Mold' }}

                        </h2>

                    </div>


                    <div class="master-panel-body">

                        <form class="master-form" method="POST" enctype="multipart/form-data"
                            action="{{ $editingMold ? route('dashboard.master.molds.update', $editingMold) : route('dashboard.master.molds.store') }}">

                            @csrf

                            @if ($editingMold)
                                @method('PUT')
                            @endif


                            {{-- TAG --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Tag Mold
                                </label>

                                <input type="text" name="tag" class="form-control" placeholder="MOLD-001"
                                    value="{{ old('tag', $editingMold?->tag ?? '') }}" required>

                            </div>


                            {{-- NAME --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Nama Mold
                                </label>

                                <input type="text" name="name" class="form-control" placeholder="Mold Injection A"
                                    value="{{ old('name', $editingMold?->name ?? '') }}" required>

                            </div>


                            {{-- PHOTO --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Gambar Mold
                                </label>

                                <div class="mold-photo-field">

                                    <div class="mold-photo-preview" id="moldPhotoPreview">

                                        @if ($editingMoldPhotoUrl)
                                            <img src="{{ $editingMoldPhotoUrl }}" alt="Gambar {{ $editingMold?->tag }}">
                                        @else
                                            <span>
                                                GAMBAR TIDAK TERSEDIA
                                            </span>
                                        @endif

                                    </div>


                                    <div class="mold-photo-input">

                                        <input type="file" name="photo" id="moldPhotoInput"
                                            accept="image/jpeg,image/png,image/webp">

                                    </div>

                                </div>

                                <small class="text-muted">
                                    JPG, PNG, atau WebP - maksimal 5 MB.
                                </small>

                            </div>


                            {{-- MASTER PART --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Master Part
                                </label>

                                <div class="mold-part-select-wrap">

                                    <select name="internal_part_ids[]" id="mold-part-select" multiple
                                        data-placeholder="Cari back number...">

                                        @foreach ($internalParts as $part)
                                            <option value="{{ $part->id }}"
                                                {{ in_array(
                                                    $part->id,
                                                    old('internal_part_ids', $editingMold?->moldHasParts->pluck('internal_part_id')->all() ?? []),
                                                )
                                                    ? 'selected'
                                                    : '' }}>
                                                {{ $part->back_number ?? '-' }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <small class="text-muted">
                                    Ketik back number untuk mencari, lalu pilih satu atau beberapa part.
                                </small>

                            </div>


                            {{-- STATUS --}}
                            <div class="mb-3 form-check">

                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    {{ old('is_active', $editingMold?->is_active ?? true) ? 'checked' : '' }}>

                                <label class="form-check-label">
                                    Aktif
                                </label>

                            </div>


                            <div class="master-submit-row">

                                <button type="submit" class="master-btn primary w-100">

                                    <i class="fas fa-save"></i>

                                    {{ $editingMold ? 'Update Mold' : 'Simpan Mold' }}

                                </button>


                                @if ($editingMold)
                                    <a href="{{ route('dashboard.master.molds.index') }}" class="master-btn w-100">
                                        Batal Edit
                                    </a>
                                @endif

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            {{-- TABLE --}}
            <div class="col-lg-8">

                <div class="master-panel">

                    <div class="master-panel-header">

                        <h2 class="master-panel-title">

                            <i class="fas fa-list"></i>

                            Daftar Mold

                        </h2>


                        <span class="master-record-count">
                            {{ $moldCount }} record
                        </span>

                    </div>


                    <div class="master-table-wrap">

                        <table id="molds-table" class="table master-table align-middle w-100">

                            <thead>

                                <tr>

                                    <th>Tag</th>

                                    <th>Nama</th>

                                    <th>Part Terhubung</th>

                                    <th>Status</th>

                                    <th class="text-end">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>

                            <tbody></tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- QR MODAL --}}
    <div class="modal fade mold-qr-modal" id="moldQrModal" tabindex="-1" role="dialog" aria-labelledby="moldQrModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered" role="document">

            <div class="modal-content">

                {{-- HEADER --}}
                <div class="modal-header">

                    <div>

                        <h5 class="modal-title" id="moldQrModalLabel">
                            QR Code Mold
                        </h5>

                        <div class="text-muted small" id="moldQrName">
                            -
                        </div>

                    </div>


                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">

                        <span aria-hidden="true">
                            &times;
                        </span>

                    </button>

                </div>


                {{-- BODY --}}
                <div class="modal-body p-4">

                    <div class="mold-qr-card">

                        <div class="mold-qr-image-wrap">

                            {{-- LOADING --}}
                            <div class="mold-qr-loading" id="moldQrLoading">

                                <i class="fas fa-circle-notch fa-spin mr-2"></i>

                                Membuat QR Code...

                            </div>


                            {{-- ERROR --}}
                            <div class="mold-qr-error" id="moldQrError">
                                QR Code gagal dibuat.
                            </div>


                            {{-- QR CONTAINER --}}
                            <div class="mold-qr-container" id="moldQrCanvas" role="img" aria-label="QR Code Mold">
                            </div>

                        </div>


                        <div class="text-muted small mb-2">
                            Tag Mold
                        </div>


                        <code class="mold-qr-tag" id="moldQrTag">
                            -
                        </code>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="modal-footer">

                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        Tutup
                    </button>

                </div>

            </div>

        </div>

    </div>

@endsection


@push('scripts')
    {{-- QR CODE JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>


    <script>
        $(function() {

            const $moldPartSelect = $('#mold-part-select');

            const $qrModal = $('#moldQrModal');

            const $qrContainer = $('#moldQrCanvas');

            const $qrLoading = $('#moldQrLoading');

            const $qrError = $('#moldQrError');

            const moldPhotoInput = document.getElementById('moldPhotoInput');

            const moldPhotoPreview = document.getElementById('moldPhotoPreview');


            /*
            |--------------------------------------------------------------------------
            | PHOTO PREVIEW
            |--------------------------------------------------------------------------
            */

            if (moldPhotoInput && moldPhotoPreview) {

                moldPhotoInput.addEventListener('change', function() {

                    const file = this.files && this.files[0];

                    if (!file || !file.type.startsWith('image/')) {
                        return;
                    }


                    const previewUrl = URL.createObjectURL(file);

                    const image = document.createElement('img');


                    image.src = previewUrl;

                    image.alt = 'Preview gambar mold';


                    image.onload = function() {
                        URL.revokeObjectURL(previewUrl);
                    };


                    moldPhotoPreview.innerHTML = '';

                    moldPhotoPreview.appendChild(image);

                });

            }


            /*
            |--------------------------------------------------------------------------
            | SELECT2
            |--------------------------------------------------------------------------
            */

            if ($moldPartSelect.length && $.fn.select2) {

                $moldPartSelect.select2({

                    width: '100%',

                    placeholder: $moldPartSelect.data('placeholder'),

                    closeOnSelect: false,

                    allowClear: false,

                    dropdownParent: $moldPartSelect.closest('.mold-part-select-wrap'),

                    language: {

                        noResults: function() {
                            return 'Back number tidak ditemukan';
                        }

                    }

                });


                const keepSearchVisible = function() {

                    $moldPartSelect
                        .next('.select2-container')
                        .find('.select2-search__field')
                        .attr(
                            'placeholder',
                            'Cari back number...'
                        );

                };


                keepSearchVisible();


                $moldPartSelect.on(
                    'select2:select select2:unselect select2:clear',
                    function() {

                        setTimeout(
                            keepSearchVisible,
                            0
                        );

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | DATATABLE
            |--------------------------------------------------------------------------
            */

            if ($.fn.DataTable) {

                $('#molds-table').DataTable({

                    processing: true,

                    serverSide: true,

                    ajax: '{{ route('dashboard.master.molds.data') }}',

                    pageLength: 10,

                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],

                    order: [
                        [0, 'asc']
                    ],

                    autoWidth: false,


                    columns: [

                        {
                            data: 'tag',
                            name: 'molds.tag'
                        },

                        {
                            data: 'name',
                            name: 'molds.name'
                        },

                        {
                            data: 'parts',
                            name: 'parts',
                            orderable: false,
                            searchable: false
                        },

                        {
                            data: 'is_active',
                            name: 'molds.is_active'
                        },

                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-right'
                        }

                    ],


                    language: {

                        processing: 'Memuat data...',

                        search: 'Cari:',

                        lengthMenu: 'Tampilkan _MENU_ data',

                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',

                        infoEmpty: 'Belum ada data',

                        zeroRecords: 'Data mold tidak ditemukan',

                        paginate: {

                            first: 'Awal',

                            last: 'Akhir',

                            next: 'Berikutnya',

                            previous: 'Sebelumnya'

                        }

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | SHOW QR ERROR
            |--------------------------------------------------------------------------
            */

            const showQrError = function(message) {

                $qrLoading.hide();

                $qrContainer
                    .hide()
                    .empty();

                $qrError
                    .text(
                        message || 'QR Code gagal dibuat.'
                    )
                    .show();

            };


            /*
            |--------------------------------------------------------------------------
            | GENERATE QR
            |--------------------------------------------------------------------------
            */

            const renderLocalQr = function(tag) {

                if (!tag) {

                    showQrError(
                        'Tag mold tidak tersedia.'
                    );

                    return;

                }


                if (typeof window.QRCode === 'undefined') {

                    showQrError(
                        'Generator QR tidak tersedia. Muat ulang halaman dan coba kembali.'
                    );

                    return;

                }


                try {

                    const qrElement = document.getElementById(
                        'moldQrCanvas'
                    );


                    if (!qrElement) {

                        throw new Error(
                            'Container QR Code tidak ditemukan.'
                        );

                    }


                    /*
                     * Clear QR sebelumnya
                     */
                    qrElement.innerHTML = '';


                    /*
                     * Generate QR
                     */
                    new QRCode(
                        qrElement, {
                            text: tag,

                            width: 250,

                            height: 250,

                            colorDark: '#1A2340',

                            colorLight: '#FFFFFF',

                            correctLevel: QRCode.CorrectLevel.M
                        }
                    );


                    /*
                     * QRCode.js dapat membuat canvas + img.
                     * Biarkan library mengatur mana yang tampil.
                     * Jangan force display kedua elemen.
                     */
                    $qrLoading.hide();

                    $qrError.hide();

                    $qrContainer.show();


                } catch (error) {

                    console.error(
                        'QR generation error:',
                        error
                    );


                    showQrError(
                        'QR Code gagal dibuat.'
                    );

                }

            };


            /*
            |--------------------------------------------------------------------------
            | OPEN QR MODAL
            |--------------------------------------------------------------------------
            */

            $qrModal.on(
                'show.bs.modal',
                function(event) {

                    const $button = $(event.relatedTarget);


                    const tag = String(
                        $button.data('tag') || ''
                    ).trim();


                    const name = String(
                        $button.data('name') || ''
                    ).trim();


                    /*
                     * Mold Information
                     */
                    $('#moldQrName').text(
                        name || '-'
                    );


                    $('#moldQrTag').text(
                        tag || '-'
                    );


                    /*
                     * Simpan active tag
                     */
                    $qrModal.data(
                        'qr-tag',
                        tag
                    );


                    /*
                     * Reset QR
                     */
                    $qrContainer
                        .hide()
                        .empty()
                        .attr(
                            'aria-label',
                            'QR Code Mold ' + tag
                        );


                    $qrError.hide();

                    $qrLoading.show();


                    /*
                     * Generate
                     */
                    renderLocalQr(tag);

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CLOSE QR MODAL
            |--------------------------------------------------------------------------
            */

            $qrModal.on(
                'hidden.bs.modal',
                function() {

                    $qrModal.removeData(
                        'qr-tag'
                    );


                    $qrContainer
                        .hide()
                        .empty();


                    $qrLoading.hide();

                    $qrError.hide();


                    $('#moldQrName').text('-');

                    $('#moldQrTag').text('-');

                }
            );


        });
    </script>
@endpush
