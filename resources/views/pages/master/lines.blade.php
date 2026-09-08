@extends('layouts.root.main')

@section('main')
    @include('pages.master.partials.styles')

    <style>
        .line-status-field {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
            padding: 9px 11px;
            color: #33415c;
            font-size: 12.5px;
            font-weight: 700;
            background: #f8fafc;
            border: 1px solid #d8e1ee;
            border-radius: 6px;
        }

        .line-status-field input {
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: var(--primary, #0070b7);
        }

        .line-safety-note {
            display: flex;
            gap: 9px;
            margin-top: 14px;
            padding: 11px 12px;
            color: #6b5a18;
            font-size: 11px;
            line-height: 1.5;
            background: #fff9e8;
            border: 1px solid #f0dfa6;
            border-radius: 6px;
        }

        .line-safety-note i {
            margin-top: 2px;
            color: #b18400;
        }

        .line-count {
            min-width: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 8px;
            color: #294795;
            font-weight: 800;
            background: #edf4ff;
            border-radius: 5px;
        }

        .master-help {
            color: var(--text-muted, #64748b);
            font-size: 10.5px;
            line-height: 1.45;
        }
    </style>

    <div class="container-fluid master-page">
        <div class="master-page-header">
            <div>
                <h1 class="master-page-title">Master Line</h1>
                <div class="master-page-subtitle">Kelola line yang dipakai oleh master part, Production, Injection, dan Machine Authorization.</div>
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
                <strong>Master Line belum dapat disimpan.</strong>
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
                        <h2 class="master-panel-title">
                            <i class="fas fa-industry"></i>{{ $editingLine ? 'Edit Line' : 'Tambah Line' }}
                        </h2>
                    </div>
                    <div class="master-panel-body">
                        <form class="master-form" method="POST"
                            action="{{ $editingLine ? route('dashboard.master.lines.update', $editingLine) : route('dashboard.master.lines.store') }}">
                            @csrf
                            @if ($editingLine)
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label" for="line-name">Nama / Kode Line</label>
                                <input type="text" id="line-name" name="name" class="form-control"
                                    maxlength="100" placeholder="Contoh: PC6A"
                                    value="{{ old('name', $editingLine?->name) }}" required autofocus>
                                <div class="master-help mt-1">Otomatis disimpan huruf besar. Gunakan huruf, angka, titik, garis bawah, atau tanda minus.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <label class="line-status-field">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', $editingLine?->is_active ?? true) ? 'checked' : '' }}>
                                    Line aktif dan dapat dipilih untuk data baru
                                </label>
                            </div>

                            <div class="master-submit-row">
                                <button type="submit" class="master-btn primary w-100">
                                    <i class="fas fa-save"></i> {{ $editingLine ? 'Update Line' : 'Simpan Line' }}
                                </button>
                                @if ($editingLine)
                                    <a href="{{ route('dashboard.master.lines.index') }}" class="master-btn w-100">Batal Edit</a>
                                @endif
                            </div>
                        </form>

                        <div class="line-safety-note">
                            <i class="fas fa-shield-alt"></i>
                            <span>Line tidak dihapus permanen agar part dan histori tetap aman. Gunakan status nonaktif jika line sudah tidak dipakai.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="master-panel">
                    <div class="master-panel-header">
                        <h2 class="master-panel-title"><i class="fas fa-list"></i>Daftar Line</h2>
                        <span class="master-record-count">{{ $lineCount }} record</span>
                    </div>
                    <div class="master-table-wrap">
                        <table id="lines-table" class="table master-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Line</th>
                                    <th>Part Terhubung</th>
                                    <th>Mesin Terhubung</th>
                                    <th>Status</th>
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
            if (!$.fn.DataTable) return;

            $('#lines-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('dashboard.master.lines.data') }}',
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc']],
                autoWidth: false,
                columns: [
                    { data: 'name', name: 'name' },
                    {
                        data: 'internal_parts_count',
                        name: 'internal_parts_count',
                        searchable: false,
                        render: function(value) { return '<span class="line-count">' + Number(value || 0) + '</span>'; }
                    },
                    {
                        data: 'machines_count',
                        name: 'machines_count',
                        searchable: false,
                        render: function(value) { return '<span class="line-count">' + Number(value || 0) + '</span>'; }
                    },
                    { data: 'is_active', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    processing: 'Memuat data...',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    zeroRecords: 'Line tidak ditemukan',
                    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });
        });
    </script>
@endpush
