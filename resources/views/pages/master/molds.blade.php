@extends('layouts.root.main')

@section('main')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Master Mold</h4>
                <div class="text-muted small">Kelola mold dan pilih part yang masuk dalam satu mold.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard.master.index') }}" class="btn btn-sm btn-outline-secondary">Master Home</a>
                <a href="{{ route('injection.indexprdreport') }}" class="btn btn-sm btn-primary">Open Injection</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <strong>Tambah / Edit Mold</strong>
                    </div>
                    <div class="card-body">
                        @php
                            $editId = request('edit');
                            $editingMold = $editId ? $molds->firstWhere('id', $editId) : null;
                        @endphp

                        <form method="POST" action="{{ $editingMold ? route('dashboard.master.molds.update', $editingMold) : route('dashboard.master.molds.store') }}">
                            @csrf
                            @if ($editingMold)
                                @method('PUT')
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Tag Mold</label>
                                <input type="text" name="tag" class="form-control" placeholder="MOLD-001" value="{{ old('tag', $editingMold?->tag ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Mold</label>
                                <input type="text" name="name" class="form-control" placeholder="Mold Injection A" value="{{ old('name', $editingMold?->name ?? '') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Master Part</label>
                                <select name="internal_part_ids[]" class="form-select" multiple size="8">
                                    @foreach ($internalParts as $part)
                                        <option value="{{ $part->id }}" {{ $editingMold && $editingMold->moldHasParts->contains('internal_part_id', $part->id) ? 'selected' : '' }}>
                                            {{ $part->part_number }} / {{ $part->back_number ?? '-' }} / {{ $part->part_name ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih satu atau beberapa part yang masuk ke mold ini.</small>
                            </div>
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $editingMold?->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label">Aktif</label>
                            </div>
                            <button class="btn btn-primary w-100">{{ $editingMold ? 'Update Mold' : 'Simpan Mold' }}</button>
                            @if ($editingMold)
                                <a href="{{ route('dashboard.master.molds.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal Edit</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <strong>Daftar Mold</strong>
                        <span class="badge bg-light text-dark">{{ $molds->count() }} record</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tag</th>
                                        <th>Nama</th>
                                        <th>Part Terhubung</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($molds as $mold)
                                        <tr>
                                            <td><strong>{{ $mold->tag }}</strong></td>
                                            <td>{{ $mold->name }}</td>
                                            <td>
                                                @forelse ($mold->moldHasParts as $relation)
                                                    <span class="badge bg-secondary-subtle text-secondary me-1 mb-1">{{ $relation->internalPart?->part_number ?? '-' }}</span>
                                                @empty
                                                    <span class="text-muted">Belum ada part</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                @if ($mold->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('dashboard.master.molds.index', ['edit' => $mold->id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <form method="POST" action="{{ route('dashboard.master.molds.destroy', $mold) }}" onsubmit="return confirm('Hapus mold ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data mold.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
