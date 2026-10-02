@extends('layouts.root.main')

@section('main')
    @include('pages.master.partials.styles')

    <style>
        .pairing-section {
            margin-bottom: 16px;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e3eaf2;
            border-radius: 7px;
        }

        .pairing-section-title {
            margin-bottom: 12px;
            color: #294795;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .pairing-filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pairing-filter label {
            margin: 0;
            color: #66758e;
            font-size: 11px;
            font-weight: 700;
        }

        #pairing-project-filter {
            min-width: 180px;
            height: 35px;
            padding: 6px 30px 6px 10px;
            color: #33415c;
            font-size: 12px;
            background: #fff;
            border: 1px solid #d8e1ee;
            border-radius: 6px;
        }

        #kanban-pairings-table td {
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {
            .pairing-filter {
                width: 100%;
            }

            #pairing-project-filter {
                min-width: 0;
                flex: 1;
            }
        }
    </style>

    <div class="container-fluid master-page">
        <div class="master-page-header">
            <div>
                <h1 class="master-page-title">Master Kanban Pairing</h1>
                <div class="master-page-subtitle">Kelola pasangan kanban assembly dan painting beserta rasio scan-nya.</div>
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
                <strong>Master pairing belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title">
                            <i class="fas fa-link"></i>{{ $editingPairing ? 'Edit Pairing' : 'Tambah Pairing' }}
                        </h2>
                    </div>
                    <div class="master-panel-body">
                        <form class="master-form" method="POST"
                            action="{{ $editingPairing ? route('dashboard.master.kanban-pairings.update', $editingPairing) : route('dashboard.master.kanban-pairings.store') }}">
                            @csrf
                            @if ($editingPairing)
                                @method('PUT')
                            @endif

                            <div class="pairing-section">
                                <div class="pairing-section-title">Informasi Umum</div>
                                <div class="mb-3">
                                    <label class="form-label">Project</label>
                                    <input type="text" name="project" class="form-control" list="pairing-project-options"
                                        value="{{ old('project', $editingPairing?->project ?? '') }}" placeholder="Contoh: 560B" required>
                                    <datalist id="pairing-project-options">
                                        @foreach ($projects as $project)
                                            <option value="{{ $project }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Part Number Customer</label>
                                    <input type="text" name="part_number_customer" class="form-control"
                                        value="{{ old('part_number_customer', $editingPairing?->part_number_customer ?? '') }}" required>
                                </div>
                            </div>

                            <div class="pairing-section">
                                <div class="pairing-section-title">Kanban Assembly</div>
                                <div class="mb-3">
                                    <label class="form-label">Part Number Assembly</label>
                                    <input type="text" name="assembly_part" class="form-control"
                                        value="{{ old('assembly_part', $editingPairing?->assembly_part ?? '') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Back Number Assembly</label>
                                    <input type="text" name="assembly_back_number" class="form-control"
                                        value="{{ old('assembly_back_number', $editingPairing?->assembly_back_number ?? '') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Part Assembly</label>
                                    <input type="text" name="part_name" class="form-control"
                                        value="{{ old('part_name', $editingPairing?->part_name ?? '') }}" required>
                                </div>
                                <div>
                                    <label class="form-label">Qty Assembly</label>
                                    <input type="number" name="qty_assy" class="form-control" min="1"
                                        value="{{ old('qty_assy', $editingPairing?->qty_assy ?? 1) }}" required>
                                </div>
                            </div>

                            <div class="pairing-section">
                                <div class="pairing-section-title">Kanban Painting</div>
                                <div class="mb-3">
                                    <label class="form-label">Part Number Painting</label>
                                    <input type="text" name="painting_part" class="form-control"
                                        value="{{ old('painting_part', $editingPairing?->painting_part ?? '') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Back Number Painting</label>
                                    <input type="text" name="painting_back_number" class="form-control"
                                        value="{{ old('painting_back_number', $editingPairing?->painting_back_number ?? '') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product / Nama Part Painting</label>
                                    <input type="text" name="product" class="form-control"
                                        value="{{ old('product', $editingPairing?->product ?? '') }}" required>
                                </div>
                                <div>
                                    <label class="form-label">Qty Painting</label>
                                    <input type="number" name="qty_painting" class="form-control" min="1"
                                        value="{{ old('qty_painting', $editingPairing?->qty_painting ?? 1) }}" required>
                                </div>
                            </div>

                            <div class="master-submit-row">
                                <button class="master-btn primary w-100">
                                    <i class="fas fa-save"></i> {{ $editingPairing ? 'Update Pairing' : 'Simpan Pairing' }}
                                </button>
                                @if ($editingPairing)
                                    <a href="{{ route('dashboard.master.kanban-pairings.index') }}" class="master-btn w-100">Batal Edit</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title"><i class="fas fa-list"></i>Daftar Kanban Pairing</h2>
                        <span class="master-record-count">{{ $pairingCount }} record</span>
                    </div>
                    <div class="master-table-toolbar justify-content-end">
                        <div class="pairing-filter">
                            <label for="pairing-project-filter"><i class="fas fa-filter"></i> Project</label>
                            <select id="pairing-project-filter" aria-label="Filter berdasarkan project">
                                <option value="">Semua Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project }}">{{ $project }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="master-table-wrap">
                        <table id="kanban-pairings-table" class="table master-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Assembly Part</th>
                                    <th>Back No. Assy</th>
                                    <th>Qty Assy</th>
                                    <th>Painting Part</th>
                                    <th>Back No. Painting</th>
                                    <th>Qty Painting</th>
                                    <th>Part Customer</th>
                                    <th>Product</th>
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
            if (!$.fn.DataTable) {
                return;
            }

            const text = $.fn.dataTable.render.text();
            const table = $('#kanban-pairings-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('dashboard.master.kanban-pairings.data') }}',
                    data: function(payload) {
                        payload.project = $('#pairing-project-filter').val() || '';
                    }
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc'], [1, 'asc']],
                autoWidth: false,
                columns: [
                    { data: 'project', name: 'project', render: text },
                    { data: 'assembly_part', name: 'assembly_part', render: text },
                    { data: 'assembly_back_number', name: 'assembly_back_number', render: text },
                    { data: 'qty_assy', name: 'qty_assy', render: text },
                    { data: 'painting_part', name: 'painting_part', render: text },
                    { data: 'painting_back_number', name: 'painting_back_number', render: text },
                    { data: 'qty_painting', name: 'qty_painting', render: text },
                    { data: 'part_number_customer', name: 'part_number_customer', render: text },
                    { data: 'product', name: 'product', render: text },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    processing: 'Memuat data...',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    zeroRecords: 'Master pairing tidak ditemukan',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            $('#pairing-project-filter').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
