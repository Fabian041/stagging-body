@extends('layouts.root.dashboard')

@section('main')
    <div class="section-header">
        <h1>Dashboard</h1>
        <div class="section-header-breadcrumb">
            <span class="text-muted">Selamat datang di Bella dashboard. Pilih area di bawah untuk mulai bekerja.</span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card card-primary shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h4 class="mb-2"><i class="fas fa-warehouse mr-2"></i>PPIC - Delivery</h4>
                    <p class="text-muted mb-4">Akses menu Preparation Monitoring, Preparation Detail, dan Kanban Reset.</p>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <!-- <a href="{{ route('board.landing') }}" class="btn btn-primary">
                                <i class="fas fa-calendar-alt mr-1"></i> Production Plan
                            </a> -->
                        <a href="{{ route('dashboard.delivery') }}" class="btn btn-primary">
                            <i class="fas fa-truck-loading mr-1"></i> Preparation Monitoring
                        </a>
                        <a href="{{ route('loadingList.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-truck-loading mr-1"></i> Preparation Detail
                        </a>
                        <a href="{{ route('pulling.manual') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt mr-1"></i> Kanban Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card card-warning shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h4 class="mb-2"><i class="fas fa-qrcode mr-2"></i>PPIC - Packing</h4>
                    <p class="text-muted mb-4">Akses menu Scanning, Scan List, dan Master Data PIS.</p>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <a href="{{ route('pis.index') }}" class="btn btn-warning">
                            <i class="fas fa-barcode mr-1"></i> Scanning
                        </a>
                        <a href="{{ route('pis.scanList') }}" class="btn btn-outline-warning">
                            <i class="fas fa-list-alt mr-1"></i> Scan List
                        </a>
                        <!-- <a href="{{ route('pis.packing') }}" class="btn btn-outline-warning">
                                                                <i class="fas fa-box mr-1"></i> Packing
                                                            </a> -->
                        <a href="{{ route('pis.master') }}" class="btn btn-outline-warning">
                            <i class="fas fa-database mr-1"></i> Master Data
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card card-info shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h4 class="mb-2"><i class="fas fa-industry mr-2"></i>Production</h4>
                    <p class="text-muted mb-4">Akses menu Production Stock, Check Kanban, dan Production Result.</p>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <a href="{{ route('dashboard.productionStock') }}" class="btn btn-info">
                            <i class="fas fa-box mr-1"></i> Production Stock
                        </a>
                        <a href="{{ route('dashboard.kbnCheck') }}" class="btn btn-outline-info">
                            <i class="fas fa-clipboard-list mr-1"></i> Check Kanban
                        </a>
                        <a href="{{ route('dashboard.prodResult') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-line mr-1"></i> Production Result
                        </a>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-12 col-md-6">
            <div class="card card-danger shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h4 class="mb-2">
                        <i class="fas fa-industry mr-2"></i>Evaluation
                    </h4>
                    <p class="text-muted mb-4">Akses menu Error Log untuk melihat log error dari aplikasi.</p>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <a href="{{ route('error.log') }}" class="btn btn-danger">
                            <i class="fas fa-exclamation-circle mr-1"></i> Error Log
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
