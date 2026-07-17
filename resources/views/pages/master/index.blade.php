@extends('layouts.root.main')

@section('main')
    <div class="container-fluid py-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small text-uppercase">Mold</div>
                                <h3 class="mb-0 mt-2">{{ $stats['molds'] }}</h3>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">Master</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small text-uppercase">Part</div>
                                <h3 class="mb-0 mt-2">{{ $stats['internalParts'] }}</h3>
                            </div>
                            <span class="badge bg-success-subtle text-success">Internal</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small text-uppercase">Part Customer</div>
                                <h3 class="mb-0 mt-2">{{ $stats['customerParts'] }}</h3>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">Customer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Master Mold</h5>
                            <a href="{{ route('dashboard.master.molds.index') }}" class="btn btn-sm btn-primary">Open</a>
                        </div>
                        <p class="text-muted mb-0">Kelola tag mold, nama mold, dan relasinya ke master part.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Master Part</h5>
                            <a href="{{ route('dashboard.master.parts.index') }}" class="btn btn-sm btn-primary">Open</a>
                        </div>
                        <p class="text-muted mb-0">Pusat data internal part yang dipakai oleh line dan injection.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Master Part Customer</h5>
                            <a href="{{ route('dashboard.master.customer-parts.index') }}" class="btn btn-sm btn-primary">Open</a>
                        </div>
                        <p class="text-muted mb-0">Mapping antara internal part, customer, dan qty per kanban.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
