@extends('layouts.root.dashboard')

@section('main')
    <style>
        .dashboard-card {
            border-radius: 14px;
            transition: all 0.2s ease;
            border: none;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .icon-circle {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .gap-2 {
            gap: 8px;
        }
    </style>

    <div class="section-header">
        <h1>Dashboard</h1>
        <div class="section-header-breadcrumb">
            <span class="text-muted">
                Selamat datang di Bella dashboard. Pilih area di bawah untuk mulai bekerja.
            </span>
        </div>
    </div>

    <div class="row">

        @php
            $menus = [
                [
                    'title' => 'PPIC - Delivery',
                    'icon' => 'fa-warehouse',
                    'color' => 'primary',
                    'desc' => 'Preparation Monitoring, Preparation Detail, dan Kanban Reset.',
                    'buttons' => [
                        [
                            'route' => 'dashboard.delivery',
                            'label' => 'Preparation Monitoring',
                            'icon' => 'fa-truck-loading',
                            'type' => 'solid',
                        ],
                        [
                            'route' => 'loadingList.index',
                            'label' => 'Preparation Detail',
                            'icon' => 'fa-list',
                            'type' => 'outline',
                        ],
                        [
                            'route' => 'pulling.manual',
                            'label' => 'Kanban Reset',
                            'icon' => 'fa-sync-alt',
                            'type' => 'outline',
                        ],
                    ],
                ],
                [
                    'title' => 'PPIC - Packing',
                    'icon' => 'fa-qrcode',
                    'color' => 'warning',
                    'desc' => 'Scanning, Scan List, dan Master Data PIS.',
                    'buttons' => [
                        ['route' => 'pis.index', 'label' => 'Scanning', 'icon' => 'fa-barcode', 'type' => 'solid'],
                        [
                            'route' => 'pis.scanList',
                            'label' => 'Scan List',
                            'icon' => 'fa-list-alt',
                            'type' => 'outline',
                        ],
                        [
                            'route' => 'pis.master',
                            'label' => 'Master Data',
                            'icon' => 'fa-database',
                            'type' => 'outline',
                        ],
                    ],
                ],
                [
                    'title' => 'Production',
                    'icon' => 'fa-industry',
                    'color' => 'info',
                    'desc' => 'Production Stock, Check Kanban, dan Production Result.',
                    'buttons' => [
                        [
                            'route' => 'dashboard.productionStock',
                            'label' => 'Production Stock',
                            'icon' => 'fa-box',
                            'type' => 'solid',
                        ],
                        [
                            'route' => 'dashboard.kbnCheck',
                            'label' => 'Check Kanban',
                            'icon' => 'fa-clipboard-list',
                            'type' => 'outline',
                        ],
                        [
                            'route' => 'dashboard.prodResult',
                            'label' => 'Production Result',
                            'icon' => 'fa-chart-line',
                            'type' => 'outline',
                        ],
                    ],
                ],
                [
                    'title' => 'Evaluation',
                    'icon' => 'fa-exclamation-triangle',
                    'color' => 'danger',
                    'desc' => 'Melihat log error dari aplikasi.',
                    'buttons' => [
                        [
                            'route' => 'error.log',
                            'label' => 'Error Log',
                            'icon' => 'fa-exclamation-circle',
                            'type' => 'solid',
                        ],
                    ],
                ],
            ];
        @endphp

        @foreach ($menus as $menu)
            <div class="col-12 col-md-6 mb-4">
                <div class="card shadow-sm h-100 dashboard-card">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-{{ $menu['color'] }} text-white mr-3">
                                <i class="fas {{ $menu['icon'] }}"></i>
                            </div>
                            <h5 class="mb-0 font-weight-bold">{{ $menu['title'] }}</h5>
                        </div>

                        <p class="text-muted small mb-4">
                            {{ $menu['desc'] }}
                        </p>

                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($menu['buttons'] as $btn)
                                <a href="{{ route($btn['route']) }}"
                                    class="btn btn-{{ $btn['type'] == 'solid' ? $menu['color'] : 'outline-' . $menu['color'] }} btn-sm">
                                    <i class="fas {{ $btn['icon'] }} mr-1"></i>
                                    {{ $btn['label'] }}
                                </a>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection
