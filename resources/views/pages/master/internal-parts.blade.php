@extends('layouts.root.main')

@section('main')
    @include('pages.master.partials.styles')

    <style>
        .line-select-wrap {
            position: relative;
        }

        .line-select-wrap > .select2-container {
            width: 100% !important;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single {
            height: 38px !important;
            background: #fff !important;
            border: 1px solid #d8e1ee !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .line-select-wrap > .select2-container--default.select2-container--focus .select2-selection--single,
        .line-select-wrap > .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #0070b7 !important;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .12) !important;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single .select2-selection__rendered {
            height: 36px;
            padding: 0 38px 0 12px;
            color: #1a2340;
            font-family: inherit;
            font-size: 13px;
            line-height: 36px;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #8190aa;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single .select2-selection__arrow {
            width: 36px;
            height: 36px;
            top: 1px;
            right: 1px;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            top: 6px;
            right: 34px;
            z-index: 2;
            width: 24px;
            height: 24px;
            margin: 0;
            padding: 0;
            color: #8190aa;
            font-size: 17px;
            line-height: 22px;
            text-align: center;
            background: transparent;
            border: 0;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #dc2626;
        }

        .line-select-wrap > .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6b7a99 transparent transparent transparent;
        }

        .line-select-wrap > .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #0070b7 transparent;
        }

        .line-select-wrap .select2-dropdown {
            overflow: hidden;
            margin-top: 5px;
            background: #fff !important;
            border: 1px solid #b9d3e8 !important;
            border-radius: 8px !important;
            box-shadow: 0 14px 35px rgba(41, 71, 149, .18) !important;
        }

        .line-select-wrap .select2-search--dropdown {
            padding: 8px;
            background: #f7faff;
            border-bottom: 1px solid #e5edf6;
        }

        .line-select-wrap .select2-search--dropdown .select2-search__field {
            height: 36px;
            padding: 7px 10px;
            color: #1a2340;
            font-family: inherit;
            border: 1px solid #d8e1ee !important;
            border-radius: 6px;
            outline: none;
        }

        .line-select-wrap .select2-search--dropdown .select2-search__field:focus {
            border-color: #0070b7 !important;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .10);
        }

        .line-select-wrap .select2-results__options {
            max-height: 220px !important;
            padding: 5px;
        }

        .line-select-wrap .select2-results__option {
            margin: 2px 0;
            padding: 9px 11px;
            color: #33415c;
            font-size: 12.5px;
            border-radius: 6px;
        }

        .line-select-wrap .select2-results__option[aria-selected="true"] {
            color: #294795 !important;
            background: #e8f4fd !important;
        }

        .line-select-wrap .select2-results__option--highlighted[aria-selected] {
            color: #fff !important;
            background: #0070b7 !important;
        }

        .internal-parts-toolbar {
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .part-line-filter-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .part-line-filter-wrap label {
            margin: 0;
            color: #66758e;
            font-size: 11px;
            font-weight: 700;
        }

        #part-line-filter {
            min-width: 190px;
            height: 35px;
            padding: 6px 30px 6px 10px;
            color: #33415c;
            font-size: 12px;
            background: #fff;
            border: 1px solid #d8e1ee;
            border-radius: 6px;
        }

        @media (max-width: 767.98px) {
            .part-line-filter-wrap {
                width: 100%;
                margin-left: 0;
            }

            #part-line-filter {
                flex: 1;
                min-width: 0;
            }
        }
    </style>

    <div class="container-fluid master-page">
        <div class="master-page-header">
            <div>
                <h1 class="master-page-title">Master Internal Part</h1>
                <div class="master-page-subtitle">Kelola identitas part, line produksi, dan foto referensi PIS.</div>
            </div>
            <div class="master-actions">
                <a href="{{ route('dashboard.part.template') }}" class="master-btn">
                    <i class="fas fa-download"></i> Template Excel
                </a>
                <button type="button" class="master-btn success" data-toggle="modal" data-target="#partImportModal">
                    <i class="fas fa-file-import"></i> Import Master Part
                </button>
                <a href="{{ route('dashboard.master.index') }}" class="master-btn">
                    <i class="fas fa-th-large"></i> Master Home
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success master-alert">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning master-alert">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger master-alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger master-alert">
                <strong>Data part belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('import_errors'))
            <div class="alert alert-warning master-alert">
                <strong>Detail import</strong>
                <ul class="mb-0 mt-2">
                    @foreach (session('import_errors') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title"><i class="fas fa-cube"></i>{{ $editingPart ? 'Edit Internal Part' : 'Tambah Internal Part' }}</h2>
                    </div>
                    <div class="master-panel-body">
                        <form class="master-form" method="POST" enctype="multipart/form-data"
                            action="{{ $editingPart ? route('dashboard.master.parts.update', $editingPart) : route('dashboard.master.parts.store') }}">
                            @csrf
                            @if ($editingPart)
                                @method('PUT')
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Line</label>
                                <div class="line-select-wrap">
                                    <select name="line_id" id="line-select" data-placeholder="Pilih atau cari line...">
                                        <option value=""></option>
                                        @foreach ($lines as $line)
                                            @php
                                                $isCurrentPartLine = (string) $editingPart?->line_id === (string) $line->id;
                                            @endphp
                                            <option value="{{ $line->id }}"
                                                {{ old('line_id', $editingPart?->line_id) == $line->id ? 'selected' : '' }}
                                                {{ ! $line->is_active && ! $isCurrentPartLine ? 'disabled' : '' }}>
                                                {{ $line->name }}{{ ! $line->is_active ? ' (Nonaktif)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" name="part_number" class="form-control" value="{{ old('part_number', $editingPart?->part_number ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Back Number</label>
                                <input type="text" name="back_number" class="form-control" value="{{ old('back_number', $editingPart?->back_number ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Part Name</label>
                                <input type="text" name="part_name" class="form-control" value="{{ old('part_name', $editingPart?->part_name ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto PIS</label>
                                <div class="part-photo-field">
                                    <div class="part-photo-preview" id="partPhotoPreview">
                                        @if ($editingPhotoUrl)
                                            <img src="{{ $editingPhotoUrl }}" alt="Foto {{ $editingPart?->back_number }}">
                                        @else
                                            <i class="fas fa-image"></i>
                                        @endif
                                    </div>
                                    <div class="part-photo-copy">
                                        <label for="partPhotoInput" class="master-btn mb-0">
                                            <i class="fas fa-camera"></i> Pilih Foto
                                        </label>
                                        <input type="file" name="photo" id="partPhotoInput"
                                            accept="image/jpeg,image/png,image/webp" hidden>
                                        <div class="part-photo-name" id="partPhotoName">
                                            {{ $editingPart?->photo ?: 'JPG, PNG, atau WebP - maks. 5 MB' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="master-submit-row">
                                <button class="master-btn primary w-100">
                                    <i class="fas fa-save"></i> {{ $editingPart ? 'Update Part' : 'Simpan Part' }}
                                </button>
                                @if ($editingPart)
                                    <a href="{{ route('dashboard.master.parts.index') }}" class="master-btn w-100">Batal Edit</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title"><i class="fas fa-list"></i>Daftar Internal Part</h2>
                        <span class="master-record-count">{{ $internalPartCount }} record</span>
                    </div>
                    <div class="master-table-toolbar internal-parts-toolbar">
                        <div class="master-segmented" aria-label="Filter status foto">
                            <button type="button" class="part-photo-filter active" data-missing-photo="0">
                                Semua Part
                            </button>
                            <button type="button" class="part-photo-filter" data-missing-photo="1">
                                <i class="fas fa-image"></i> Belum Ada Foto
                            </button>
                        </div>
                        <div class="part-line-filter-wrap">
                            <label for="part-line-filter"><i class="fas fa-industry"></i> Line</label>
                            <select id="part-line-filter" aria-label="Filter berdasarkan line">
                                <option value="">Semua Line</option>
                                @foreach ($lines as $line)
                                    <option value="{{ $line->id }}">{{ $line->name }}{{ ! $line->is_active ? ' (Nonaktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="master-table-wrap">
                        <table id="internal-parts-table" class="table master-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Line</th>
                                    <th>Part Number</th>
                                    <th>Back Number</th>
                                    <th>Part Name</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.master.partials.part-import-modal')
@endsection

@push('scripts')
    <script>
        $(function() {
            const $lineSelect = $('#line-select');
            const photoInput = document.getElementById('partPhotoInput');
            const photoPreview = document.getElementById('partPhotoPreview');
            const photoName = document.getElementById('partPhotoName');
            const importInput = document.getElementById('partImportFile');
            const importDropzone = document.getElementById('partImportDropzone');
            const importFileCard = document.getElementById('partImportFileCard');
            const importFileName = document.getElementById('partImportFileName');
            const importFileSize = document.getElementById('partImportFileSize');
            const importRemove = document.getElementById('partImportRemove');
            const importSubmit = document.getElementById('partImportSubmit');
            const importError = document.getElementById('partImportError');

            const formatBytes = function(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            };

            const showImportError = function(message) {
                if (!importError) return;
                importError.textContent = message;
                importError.classList.remove('d-none');
            };

            const clearImportFile = function() {
                if (importInput) importInput.value = '';
                if (importFileCard) importFileCard.classList.remove('is-visible');
                if (importDropzone) importDropzone.classList.remove('is-active');
                if (importSubmit) importSubmit.disabled = true;
                if (importError) importError.classList.add('d-none');
            };

            const acceptImportFile = function(file) {
                const extension = String(file?.name || '').split('.').pop().toLowerCase();

                if (!['xlsx', 'xls', 'csv'].includes(extension)) {
                    clearImportFile();
                    showImportError('Format file harus .xlsx, .xls, atau .csv.');
                    return false;
                }

                if (file.size > 10 * 1024 * 1024) {
                    clearImportFile();
                    showImportError('Ukuran file maksimal 10 MB.');
                    return false;
                }

                importFileName.textContent = file.name;
                importFileSize.textContent = formatBytes(file.size);
                importFileCard.classList.add('is-visible');
                importDropzone.classList.add('is-active');
                importSubmit.disabled = false;
                importError.classList.add('d-none');
                return true;
            };

            if (photoInput) {
                photoInput.addEventListener('change', function() {
                    const file = this.files?.[0];
                    if (!file) return;

                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
                        this.value = '';
                        photoName.textContent = 'Gunakan JPG, PNG, atau WebP maksimal 5 MB.';
                        return;
                    }

                    const imageUrl = URL.createObjectURL(file);
                    photoPreview.innerHTML = '';
                    const image = document.createElement('img');
                    image.src = imageUrl;
                    image.alt = 'Preview foto part';
                    image.onload = function() { URL.revokeObjectURL(imageUrl); };
                    photoPreview.appendChild(image);
                    photoName.textContent = file.name + ' - ' + formatBytes(file.size);
                });
            }

            if (importInput && importDropzone) {
                importDropzone.addEventListener('click', function() { importInput.click(); });
                importDropzone.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        importInput.click();
                    }
                });
                importDropzone.addEventListener('dragover', function(event) {
                    event.preventDefault();
                    this.classList.add('is-active');
                });
                importDropzone.addEventListener('dragleave', function() {
                    if (!importInput.files?.length) this.classList.remove('is-active');
                });
                importDropzone.addEventListener('drop', function(event) {
                    event.preventDefault();
                    const file = event.dataTransfer.files?.[0];
                    if (!file || !acceptImportFile(file)) return;

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    importInput.files = transfer.files;
                });
                importInput.addEventListener('change', function() {
                    if (this.files?.[0]) acceptImportFile(this.files[0]);
                });
                importRemove.addEventListener('click', clearImportFile);
                $('#partImportModal').on('hidden.bs.modal', clearImportFile);
            }

            if ($lineSelect.length && $.fn.select2) {
                $lineSelect.select2({
                    width: '100%',
                    placeholder: $lineSelect.data('placeholder'),
                    allowClear: true,
                    minimumResultsForSearch: 0,
                    dropdownParent: $lineSelect.closest('.line-select-wrap'),
                    language: {
                        noResults: function() {
                            return 'Line tidak ditemukan';
                        }
                    }
                });
            }

            if ($.fn.DataTable) {
                const internalPartsTable = $('#internal-parts-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('dashboard.master.parts.data') }}',
                        data: function(payload) {
                            payload.missing_photo = $('.part-photo-filter.active').data('missing-photo') || 0;
                            payload.line_id = $('#part-line-filter').val() || '';
                        }
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[2, 'asc']],
                    autoWidth: false,
                    columns: [
                        { data: 'photo_preview', name: 'photo_preview', orderable: false, searchable: false },
                        { data: 'line_name', name: 'lines.name' },
                        { data: 'part_number', name: 'internal_parts.part_number' },
                        { data: 'back_number', name: 'internal_parts.back_number' },
                        { data: 'part_name', name: 'internal_parts.part_name' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                    ],
                    language: {
                        processing: 'Memuat data...',
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Belum ada data',
                        zeroRecords: 'Data part tidak ditemukan',
                        paginate: {
                            first: 'Awal',
                            last: 'Akhir',
                            next: 'Berikutnya',
                            previous: 'Sebelumnya'
                        }
                    }
                });

                $('.part-photo-filter').on('click', function() {
                    $('.part-photo-filter').removeClass('active');
                    $(this).addClass('active');
                    internalPartsTable.ajax.reload();
                });

                $('#part-line-filter').on('change', function() {
                    internalPartsTable.ajax.reload();
                });
            }
        });
    </script>
@endpush
