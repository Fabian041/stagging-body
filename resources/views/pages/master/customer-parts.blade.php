@extends('layouts.root.main')

@section('main')
    @include('pages.master.partials.styles')

    <style>
        .mapping-select-wrap {
            position: relative;
        }

        .mapping-select-wrap > .select2-container {
            width: 100% !important;
        }

        .mapping-select-wrap > .select2-container--default .select2-selection--single {
            height: 38px !important;
            background: #fff !important;
            border: 1px solid #d8e1ee !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .mapping-select-wrap > .select2-container--default.select2-container--focus .select2-selection--single,
        .mapping-select-wrap > .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #0070b7 !important;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .12) !important;
        }

        .mapping-select-wrap > .select2-container--default .select2-selection--single .select2-selection__rendered {
            height: 36px;
            padding: 0 58px 0 12px;
            color: #1a2340;
            font-family: inherit;
            font-size: 13px;
            line-height: 36px;
        }

        .mapping-select-wrap > .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #8190aa;
        }

        .mapping-select-wrap > .select2-container--default .select2-selection--single .select2-selection__arrow {
            width: 36px;
            height: 36px;
            top: 1px;
            right: 1px;
        }

        .mapping-select-wrap > .select2-container--default .select2-selection--single .select2-selection__clear {
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

        .mapping-select-wrap > .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #dc2626;
        }

        .mapping-select-wrap > .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6b7a99 transparent transparent transparent;
        }

        .mapping-select-wrap > .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #0070b7 transparent;
        }

        .mapping-select-wrap .select2-dropdown {
            overflow: hidden;
            margin-top: 5px;
            background: #fff !important;
            border: 1px solid #b9d3e8 !important;
            border-radius: 8px !important;
            box-shadow: 0 14px 35px rgba(41, 71, 149, .18) !important;
        }

        .mapping-select-wrap .select2-search--dropdown {
            padding: 8px;
            background: #f7faff;
            border-bottom: 1px solid #e5edf6;
        }

        .mapping-select-wrap .select2-search--dropdown .select2-search__field {
            height: 36px;
            padding: 7px 10px;
            color: #1a2340;
            font-family: inherit;
            border: 1px solid #d8e1ee !important;
            border-radius: 6px;
            outline: none;
        }

        .mapping-select-wrap .select2-search--dropdown .select2-search__field:focus {
            border-color: #0070b7 !important;
            box-shadow: 0 0 0 3px rgba(0, 112, 183, .10);
        }

        .mapping-select-wrap .select2-results__options {
            max-height: 240px !important;
            padding: 5px;
        }

        .mapping-select-wrap .select2-results__option {
            margin: 2px 0;
            padding: 9px 11px;
            color: #33415c;
            font-size: 12.5px;
            border-radius: 6px;
        }

        .mapping-select-wrap .select2-results__option[aria-selected="true"] {
            color: #294795 !important;
            background: #e8f4fd !important;
        }

        .mapping-select-wrap .select2-results__option--highlighted[aria-selected] {
            color: #fff !important;
            background: #0070b7 !important;
        }

        .customer-parts-toolbar {
            justify-content: flex-end;
            gap: 8px;
        }

        .customer-parts-filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .customer-parts-filter label {
            margin: 0;
            color: #66758e;
            font-size: 11px;
            font-weight: 700;
        }

        #customer-parts-customer-filter {
            min-width: 220px;
            height: 35px;
            padding: 6px 30px 6px 10px;
            color: #33415c;
            font-size: 12px;
            background: #fff;
            border: 1px solid #d8e1ee;
            border-radius: 6px;
        }

        @media (max-width: 767.98px) {
            .customer-parts-filter {
                width: 100%;
            }

            #customer-parts-customer-filter {
                flex: 1;
                min-width: 0;
            }
        }
    </style>

    <div class="container-fluid master-page">
        <div class="master-page-header">
            <div>
                <h1 class="master-page-title">Master Customer Part</h1>
                <div class="master-page-subtitle">Kelola mapping customer, internal part, dan quantity per kanban.</div>
            </div>
            <div class="master-actions">
                <a href="{{ route('dashboard.master.index') }}" class="master-btn">
                    <i class="fas fa-th-large"></i> Master Home
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success master-alert">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger master-alert">
                <strong>Mapping part customer belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title"><i class="fas fa-link"></i>{{ $editingCustomerPart ? 'Edit Mapping' : 'Tambah Mapping' }}</h2>
                    </div>
                    <div class="master-panel-body">
                        <form class="master-form" method="POST" action="{{ $editingCustomerPart ? route('dashboard.master.customer-parts.update', $editingCustomerPart) : route('dashboard.master.customer-parts.store') }}">
                            @csrf
                            @if ($editingCustomerPart)
                                @method('PUT')
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Internal Part</label>
                                <div class="mapping-select-wrap">
                                    <select name="internal_part_id" class="customer-mapping-select" required
                                        data-placeholder="Pilih atau cari internal part...">
                                        <option value=""></option>
                                        @foreach ($internalParts as $part)
                                            <option value="{{ $part->id }}" {{ old('internal_part_id', $editingCustomerPart?->internal_part_id) == $part->id ? 'selected' : '' }}>{{ $part->part_number }} / {{ $part->back_number ?? '-' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <div class="mapping-select-wrap">
                                    <select name="customer_id" class="customer-mapping-select" required
                                        data-placeholder="Pilih atau cari customer...">
                                        <option value=""></option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $editingCustomerPart?->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Part Number Customer</label>
                                <input type="text" name="part_number" class="form-control" value="{{ old('part_number', $editingCustomerPart?->part_number ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Back Number Customer</label>
                                <input type="text" name="back_number" class="form-control" value="{{ old('back_number', $editingCustomerPart?->back_number ?? '') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Qty Per Kanban</label>
                                <input type="number" name="qty_per_kanban" class="form-control" min="1" value="{{ old('qty_per_kanban', $editingCustomerPart?->qty_per_kanban ?? '') }}" required>
                            </div>
                            <div class="master-submit-row">
                                <button class="master-btn primary w-100">
                                    <i class="fas fa-save"></i> {{ $editingCustomerPart ? 'Update Mapping' : 'Simpan Mapping' }}
                                </button>
                                @if ($editingCustomerPart)
                                    <a href="{{ route('dashboard.master.customer-parts.index') }}" class="master-btn w-100">Batal Edit</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title"><i class="fas fa-list"></i>Daftar Customer Part</h2>
                        <span class="master-record-count">{{ $customerPartCount }} record</span>
                    </div>
                    <div class="master-table-toolbar customer-parts-toolbar">
                        <div class="customer-parts-filter">
                            <label for="customer-parts-customer-filter"><i class="fas fa-building"></i> Customer</label>
                            <select id="customer-parts-customer-filter" aria-label="Filter berdasarkan customer">
                                <option value="">Semua Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="master-table-wrap">
                        <table id="customer-parts-table" class="table master-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Internal Part</th>
                                    <th>Part Number</th>
                                    <th>Back Number</th>
                                    <th>Qty/Kanban</th>
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
            $('.customer-mapping-select').each(function() {
                const $select = $(this);

                if ($.fn.select2) {
                    $select.select2({
                        width: '100%',
                        placeholder: $select.data('placeholder'),
                        allowClear: true,
                        minimumResultsForSearch: 0,
                        dropdownParent: $select.closest('.mapping-select-wrap'),
                        language: {
                            noResults: function() {
                                return 'Data tidak ditemukan';
                            }
                        }
                    });
                }
            });

            if ($.fn.DataTable) {
                const customerPartsTable = $('#customer-parts-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('dashboard.master.customer-parts.data') }}',
                        data: function(payload) {
                            payload.customer_id = $('#customer-parts-customer-filter').val() || '';
                        }
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[0, 'asc']],
                    autoWidth: false,
                    columns: [
                        { data: 'customer_name', name: 'customers.name' },
                        { data: 'internal_part_number', name: 'internal_parts.part_number' },
                        { data: 'part_number', name: 'customer_parts.part_number' },
                        { data: 'back_number', name: 'customer_parts.back_number' },
                        { data: 'qty_per_kanban', name: 'customer_parts.qty_per_kanban' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                    ],
                    language: {
                        processing: 'Memuat data...',
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Belum ada data',
                        zeroRecords: 'Data part customer tidak ditemukan',
                        paginate: {
                            first: 'Awal',
                            last: 'Akhir',
                            next: 'Berikutnya',
                            previous: 'Sebelumnya'
                        }
                    }
                });

                $('#customer-parts-customer-filter').on('change', function() {
                    customerPartsTable.ajax.reload();
                });
            }
        });
    </script>
@endpush
