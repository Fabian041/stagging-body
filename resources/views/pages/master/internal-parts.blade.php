@extends('layouts.root.main')

@section('main')
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

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0"><strong>Tambah Part</strong></div>
                    <div class="card-body">
                        @php
                            $editId = request('edit');
                            $editingPart = $editId ? $internalParts->firstWhere('id', $editId) : null;
                        @endphp

                        <form method="POST" action="{{ $editingPart ? route('dashboard.master.parts.update', $editingPart) : route('dashboard.master.parts.store') }}">
                            @csrf
                            @if ($editingPart)
                                @method('PUT')
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Line</label>
                                <select name="line_id" class="form-select">
                                    <option value="">-- pilih line --</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->id }}" {{ old('line_id', $editingPart?->line_id) == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                                    @endforeach
                                </select>
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
                        <span class="badge bg-light text-dark">{{ $internalParts->count() }} record</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Line</th>
                                    <th>Part Number</th>
                                    <th>Back Number</th>
                                    <th>Part Name</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($internalParts as $part)
                                    <tr>
                                        <td>{{ $part->line?->name ?? '-' }}</td>
                                        <td>{{ $part->part_number }}</td>
                                        <td>{{ $part->back_number ?? '-' }}</td>
                                        <td>{{ $part->part_name ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('dashboard.master.parts.index', ['edit' => $part->id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" action="{{ route('dashboard.master.parts.destroy', $part) }}" onsubmit="return confirm('Hapus part ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada data part.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
