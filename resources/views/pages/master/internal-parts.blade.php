@extends('layouts.root.main')

@section('main')
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
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Master Part</h4>
                <div class="text-muted small">Data internal part untuk proses produksi dan relasi mold.</div>
            </div>
            <a href="{{ route('dashboard.master.index') }}" class="btn btn-sm btn-outline-secondary">Master Home</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Data part belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0"><strong>Tambah / Edit Part</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ $editingPart ? route('dashboard.master.parts.update', $editingPart) : route('dashboard.master.parts.store') }}">
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
                                            <option value="{{ $line->id }}" {{ old('line_id', $editingPart?->line_id) == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
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
                            <button class="btn btn-primary w-100">{{ $editingPart ? 'Update Part' : 'Simpan' }}</button>
                            @if ($editingPart)
                                <a href="{{ route('dashboard.master.parts.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal Edit</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <strong>Daftar Part</strong>
                        <span class="badge bg-light text-dark">{{ $internalPartCount }} record</span>
                    </div>
                    <div class="table-responsive">
                        <table id="internal-parts-table" class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-light">
                                <tr>
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
@endsection

@push('scripts')
    <script>
        $(function() {
            const $lineSelect = $('#line-select');

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
                $('#internal-parts-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('dashboard.master.parts.data') }}',
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[1, 'asc']],
                    autoWidth: false,
                    columns: [
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
            }
        });
    </script>
@endpush
