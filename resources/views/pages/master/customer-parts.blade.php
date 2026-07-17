@extends('layouts.root.main')

@section('main')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Master Part Customer</h4>
                <div class="text-muted small">Mapping customer terhadap internal part dan qty per kanban.</div>
            </div>
            <a href="{{ route('dashboard.master.index') }}" class="btn btn-sm btn-outline-secondary">Master Home</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0"><strong>Tambah Mapping</strong></div>
                    <div class="card-body">
                        @php
                            $editId = request('edit');
                            $editingCustomerPart = $editId ? $customerParts->firstWhere('id', $editId) : null;
                        @endphp

                        <form method="POST" action="{{ $editingCustomerPart ? route('dashboard.master.customer-parts.update', $editingCustomerPart) : route('dashboard.master.customer-parts.store') }}">
                            @csrf
                            @if ($editingCustomerPart)
                                @method('PUT')
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Internal Part</label>
                                <select name="internal_part_id" class="form-select" required>
                                    <option value="">-- pilih part --</option>
                                    @foreach ($internalParts as $part)
                                        <option value="{{ $part->id }}" {{ old('internal_part_id', $editingCustomerPart?->internal_part_id) == $part->id ? 'selected' : '' }}>{{ $part->part_number }} / {{ $part->back_number ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">-- pilih customer --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id', $editingCustomerPart?->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
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
                                <input type="number" name="qty_per_kanban" class="form-control" min="0" value="{{ old('qty_per_kanban', $editingCustomerPart?->qty_per_kanban ?? '') }}">
                            </div>
                            <button class="btn btn-primary w-100">{{ $editingCustomerPart ? 'Update Mapping' : 'Simpan' }}</button>
                            @if ($editingCustomerPart)
                                <a href="{{ route('dashboard.master.customer-parts.index') }}" class="btn btn-outline-secondary w-100 mt-2">Batal Edit</a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <strong>Daftar Part Customer</strong>
                        <span class="badge bg-light text-dark">{{ $customerParts->count() }} record</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Internal Part</th>
                                    <th>Part Number</th>
                                    <th>Back Number</th>
                                    <th>Qty/Kanban</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customerParts as $cp)
                                    <tr>
                                        <td>{{ $cp->customer?->name ?? '-' }}</td>
                                        <td>{{ $cp->internalPart?->part_number ?? '-' }}</td>
                                        <td>{{ $cp->part_number }}</td>
                                        <td>{{ $cp->back_number ?? '-' }}</td>
                                        <td>{{ $cp->qty_per_kanban ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('dashboard.master.customer-parts.index', ['edit' => $cp->id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" action="{{ route('dashboard.master.customer-parts.destroy', $cp) }}" onsubmit="return confirm('Hapus mapping ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada mapping customer-part.</td>
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
