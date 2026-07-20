@extends('layouts.root.main')

@section('main')
    <style>
        .detail-checksheet-page {
            --qc-navy: #294795;
            --qc-blue: #0070b7;
            --qc-sky: #0097d8;
            --qc-text: #1a2340;
            --qc-muted: #6b7a99;
            --qc-border: #dbe5f3;
            --qc-soft: #eef6ff;
            --qc-success: #22c55e;
            --qc-warning: #f59e0b;
            --qc-danger: #ef4444;
        }

        .detail-checksheet-page {
            margin-top: 0;
        }

        .qc-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .qc-stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 108px;
            padding: 18px;
            border: 1px solid var(--qc-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(41, 71, 149, .08);
        }

        .qc-stat-label {
            margin-bottom: 6px;
            color: #7182a3;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .qc-stat-value {
            color: var(--qc-navy);
            font-size: 30px;
            line-height: 1;
            font-weight: 850;
        }

        .qc-stat-sub {
            margin-top: 8px;
            color: var(--qc-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .qc-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            border-radius: 14px;
            background: #e6f8ff;
            color: var(--qc-sky);
            font-size: 16px;
        }

        .qc-panel {
            border: 1px solid var(--qc-border);
            border-top: 4px solid var(--qc-blue);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(41, 71, 149, .08);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .qc-panel-body {
            padding: 24px 26px;
        }

        .qc-panel-title {
            margin: 0;
            color: var(--qc-navy);
            font-size: 15px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .qc-panel-subtitle {
            margin: 4px 0 0;
            color: var(--qc-muted);
            font-size: 12px;
            font-weight: 650;
        }

        .qc-filter-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .qc-filter-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qc-filter-form .form-control {
            min-height: 38px;
            border-color: #cfd9e8;
            background: #f3f7fc;
            color: var(--qc-text);
            font-weight: 600;
        }

        .qc-filter-form label {
            color: #627391;
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .qc-reset-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 9px 16px;
            border-radius: 5px;
            background: #ffe0e0;
            color: #ef3333;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .02em;
            text-decoration: none !important;
            text-transform: uppercase;
        }

        .qc-table-panel {
            border: 1px solid var(--qc-border);
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(41, 71, 149, .08);
            overflow: hidden;
        }

        .qc-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--qc-border);
        }

        .qc-table-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 12px 18px;
            border-bottom: 1px solid #edf2f8;
            color: var(--qc-muted);
            font-size: 12px;
            font-weight: 650;
        }

        .qc-table-tools .form-control {
            height: 34px;
            background: #f3f7fc;
            border-color: #d6dfec;
            font-size: 12px;
        }

        .qc-table {
            margin: 0;
            color: var(--qc-text);
        }

        .qc-table thead th {
            padding: 13px 18px;
            border-bottom: 1px solid var(--qc-border);
            background: #eef3fa;
            color: #7182a3;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .qc-table tbody td {
            padding: 14px 18px;
            border-bottom: 1px solid #edf2f8;
            vertical-align: middle;
            font-weight: 650;
        }

        .qc-table tbody tr:nth-child(odd) {
            background: #f8fbff;
        }

        .qc-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 9px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .qc-pill-ok {
            background: #20a84a;
            color: #ffffff;
        }

        .qc-pill-ng {
            border: 1px solid var(--qc-danger);
            background: #ffffff;
            color: var(--qc-danger);
        }

        .qc-pill-progress {
            border: 1px solid var(--qc-warning);
            background: #ffffff;
            color: var(--qc-warning);
        }

        .qc-detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 112px;
            padding: 10px 16px;
            border-radius: 4px;
            background: #1aa0b3;
            color: #ffffff !important;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none !important;
        }

        .qc-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 5px;
            background: var(--qc-sky);
            color: #ffffff;
            font-size: 10px;
            font-weight: 850;
        }

        .qc-card-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 9px 14px;
            border-radius: 6px;
            background: linear-gradient(135deg, var(--qc-navy), var(--qc-blue));
            color: #ffffff !important;
            font-size: 12px;
            font-weight: 850;
            text-decoration: none !important;
        }

        @media (max-width: 1199.98px) {
            .qc-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .qc-stat-grid {
                grid-template-columns: 1fr;
            }

            .qc-filter-head,
            .qc-table-header,
            .qc-table-tools {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>

    @php
        $total = $inspections->total();
        $visible = $inspections->count();
        $okVisible = $inspections->getCollection()->where('judgement', 'OK')->count();
        $ngVisible = $inspections->getCollection()->where('judgement', 'NG')->count();
        $pendingVisible = $inspections->getCollection()->where('approval_status', 'NEED_APPROVAL')->count();
        $avgProgress = $visible > 0 ? round(($okVisible / $visible) * 100) : 0;
    @endphp

    <section class="section detail-checksheet-page">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="qc-stat-grid">
            <div class="qc-stat-card">
                <div>
                    <div class="qc-stat-label">Visible Records</div>
                    <div class="qc-stat-value">{{ number_format($visible) }}</div>
                    <div class="qc-stat-sub">Current table view</div>
                </div>
                <div class="qc-stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            <div class="qc-stat-card">
                <div>
                    <div class="qc-stat-label">Completed</div>
                    <div class="qc-stat-value">{{ number_format($okVisible) }}</div>
                    <div class="qc-stat-sub">OK checksheet</div>
                </div>
                <div class="qc-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="qc-stat-card">
                <div>
                    <div class="qc-stat-label">In Progress</div>
                    <div class="qc-stat-value">{{ number_format($pendingVisible + $ngVisible) }}</div>
                    <div class="qc-stat-sub">NG / need approval</div>
                </div>
                <div class="qc-stat-icon">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>
            <div class="qc-stat-card">
                <div>
                    <div class="qc-stat-label">Avg Progress</div>
                    <div class="qc-stat-value">{{ $avgProgress }}%</div>
                    <div class="qc-stat-sub">{{ number_format($total) }} total records</div>
                </div>
                <div class="qc-stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="qc-panel">
            <div class="qc-panel-body">
                <div class="qc-filter-head">
                    <div>
                        <div class="qc-filter-title">
                            <i class="fas fa-sliders-h text-primary"></i>
                            <h4 class="qc-panel-title">Filter Data</h4>
                        </div>
                        <p class="qc-panel-subtitle">
                            Gunakan kombinasi tanggal, part number, judgement, dan proses untuk mencari detail checksheet.
                        </p>
                    </div>
                    <a href="{{ route('quality.history') }}" class="qc-reset-btn">
                        <i class="fas fa-redo"></i> Reset Filter
                    </a>
                </div>

                <form method="GET" action="{{ route('quality.history') }}" class="qc-filter-form">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Process</label>
                            <select class="form-control" name="process">
                                <option value="">-- Select process --</option>
                                <option value="PPIC" {{ request('process') === 'PPIC' ? 'selected' : '' }}>PPIC</option>
                                <option value="Production" {{ request('process') === 'Production' ? 'selected' : '' }}>Production</option>
                                <option value="PIS" {{ request('process') === 'PIS' ? 'selected' : '' }}>PIS</option>
                                <option value="Quality" {{ request('process') === 'Quality' ? 'selected' : '' }}>Quality</option>
                                <option value="Error Log" {{ request('process') === 'Error Log' ? 'selected' : '' }}>Error Log</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Part Number</label>
                            <input type="text" name="part_number" class="form-control" value="{{ request('part_number') }}"
                                placeholder="-- Input part number --">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label>Judgement</label>
                            <select name="judgement" class="form-control">
                                <option value="">-- All --</option>
                                <option value="OK" {{ request('judgement') === 'OK' ? 'selected' : '' }}>OK</option>
                                <option value="NG" {{ request('judgement') === 'NG' ? 'selected' : '' }}>NG</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="qc-card-link">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="qc-table-panel">
            <div class="qc-table-header">
                <div>
                    <h4 class="qc-panel-title">Scan Detail</h4>
                    <p class="qc-panel-subtitle">Click detail untuk melihat seluruh data scan kanban dan checksheet yang sudah disubmit.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span class="text-muted font-weight-bold" style="font-size: 12px;">Last sync: {{ now()->format('H.i.s') }}</span>
                    <span class="qc-live-badge"><i class="fas fa-circle" style="font-size: 6px;"></i> LIVE</span>
                </div>
            </div>

            <div class="qc-table-tools">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span>Show</span>
                    <select class="form-control" style="width: 74px;">
                        <option selected>{{ $visible ?: 10 }}</option>
                    </select>
                    <span>entries</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span>Search:</span>
                    <input type="text" class="form-control" value="{{ request('part_number') }}" readonly style="width: 174px;">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table qc-table">
                    <thead>
                        <tr>
                            <th>Process</th>
                            <th>Kanban / Part</th>
                            <th>Check Type</th>
                            <th>Check Date</th>
                            <th>Checksheet Progress</th>
                            <th>PIC</th>
                            <th>Status</th>
                            <th width="130">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inspections as $inspection)
                            @php
                                $isOk = $inspection->judgement === 'OK';
                                $statusClass = $isOk ? 'qc-pill-ok' : ($inspection->approval_status === 'NEED_APPROVAL' ? 'qc-pill-progress' : 'qc-pill-ng');
                                $statusLabel = $isOk ? 'Complete' : ($inspection->approval_status === 'NEED_APPROVAL' ? 'In Progress' : 'Incomplete');
                                $progress = $isOk ? 100 : 50;
                            @endphp
                            <tr>
                                <td>Quality</td>
                                <td>
                                    <div class="font-weight-bold">{{ $inspection->kanban_code ?: '-' }}</div>
                                    <small class="text-muted">{{ $inspection->part_number ?: '-' }} · {{ $inspection->part_name ?: '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-light">{{ $inspection->check_type ?: '-' }}</span>
                                    <div><small class="text-muted">{{ $inspection->line ?: '-' }}</small></div>
                                </td>
                                <td>{{ optional($inspection->checked_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td style="min-width: 220px;">
                                    <div class="text-center font-weight-bold text-muted">{{ $progress }}%</div>
                                    <div class="progress" style="height: 20px; background: #e8edf4;">
                                        <div class="progress-bar {{ $isOk ? 'bg-success' : 'bg-warning' }}"
                                            style="width: {{ $progress }}%; font-size: 10px; font-weight: 850;">
                                            {{ $progress }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $inspection->pic ?: '-' }}</td>
                                <td>
                                    <span class="qc-pill {{ $statusClass }}">
                                        @if ($isOk)
                                            <i class="fas fa-check mr-1"></i>
                                        @endif
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('quality.show', $inspection) }}" class="qc-detail-btn">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data checksheet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $inspections->links() }}
            </div>
        </div>
    </section>
@endsection