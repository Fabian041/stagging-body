@extends('layouts.root.main')

@section('main')
    <style>
        .pairing-log-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 14px;
        }

        .pairing-log-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pairing-log-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 0;
        }

        .pairing-log-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .pairing-log-body {
            padding: 18px 20px 20px;
        }

        .pairing-log-filter {
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 14px;
            background: var(--bg);
            margin-bottom: 16px;
        }

        .pairing-log-form {
            display: flex;
            align-items: end;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0;
        }

        .pairing-log-form-group {
            margin-bottom: 0;
        }

        .pairing-log-form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .pairing-log-form-group .form-control {
            height: 38px;
            min-width: 170px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--card) !important;
            color: var(--text) !important;
            font-size: 12.5px !important;
            font-weight: 600;
            box-shadow: none !important;
        }

        .pairing-log-search .form-control {
            min-width: 260px;
        }

        .pairing-log-btn {
            height: 38px;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-decoration: none !important;
            white-space: nowrap;
        }

        .pairing-log-btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .pairing-log-btn.soft {
            background: var(--card);
            border-color: var(--border);
            color: var(--text-muted);
        }

        .pairing-log-table-wrap {
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            overflow-x: auto;
            background: var(--card);
        }

        .pairing-log-table {
            width: 100%;
            min-width: 1120px;
            margin: 0;
            border-collapse: collapse;
            font-size: 12px;
        }

        .pairing-log-table thead th {
            background: var(--navy);
            color: #fff;
            padding: 10px 12px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-size: 10.5px;
            white-space: nowrap;
        }

        .pairing-log-table tbody td {
            padding: 11px 12px;
            border-top: 1px solid var(--border);
            color: var(--text);
            vertical-align: top;
        }

        .pair-main {
            font-weight: 800;
            color: var(--navy);
            white-space: nowrap;
        }

        .pair-sub {
            margin-top: 2px;
            font-size: 11px;
            color: var(--text-muted);
        }

        .pair-serials {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .pair-badge {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            background: #eef2ff;
            color: #3730a3;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-ready {
            background: #dcfce7;
            color: #166534;
        }

        .status-consumed {
            background: #e0f2fe;
            color: #075985;
        }

        .empty-row {
            padding: 28px 12px !important;
            text-align: center;
            color: var(--text-muted) !important;
        }

        .pairing-log-pagination {
            margin-top: 14px;
        }
    </style>

    <div class="pairing-log-card">
        <div class="pairing-log-header">
            <div>
                <h1 class="pairing-log-title">Kanban Pairing Trace</h1>
                <div class="pairing-log-subtitle">
                    Trace hasil pairing dari Validation sampai dikonsumsi scan production.
                </div>
            </div>
        </div>

        <div class="pairing-log-body">
            <div class="pairing-log-filter">
                <form class="pairing-log-form" method="GET" action="{{ route('dashboard.productionPairingLogs') }}">
                    <div class="pairing-log-form-group">
                        <label for="project">Project</label>
                        <select id="project" name="project" class="form-control">
                            <option value="">All project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project }}" @selected(request('project') === $project)>{{ $project }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pairing-log-form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">All status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) request('status') === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pairing-log-form-group">
                        <label for="date_from">From</label>
                        <input id="date_from" name="date_from" type="date" class="form-control"
                            value="{{ request('date_from') }}">
                    </div>

                    <div class="pairing-log-form-group">
                        <label for="date_to">To</label>
                        <input id="date_to" name="date_to" type="date" class="form-control"
                            value="{{ request('date_to') }}">
                    </div>

                    <div class="pairing-log-form-group pairing-log-search">
                        <label for="q">Search</label>
                        <input id="q" name="q" type="text" class="form-control"
                            placeholder="Part, back number, serial, NPK..." value="{{ request('q') }}">
                    </div>

                    <button class="pairing-log-btn primary" type="submit">
                        <i class="fas fa-search"></i>
                        Filter
                    </button>

                    <a class="pairing-log-btn soft" href="{{ route('dashboard.productionPairingLogs') }}">
                        Reset
                    </a>
                </form>
            </div>

            <div class="pairing-log-table-wrap">
                <table class="pairing-log-table">
                    <thead>
                        <tr>
                            <th>Paired At</th>
                            <th>Project</th>
                            <th>Assembly / Process Part</th>
                            <th>Assembly Serial</th>
                            <th>Pairing Part</th>
                            <th>Pairing Serials</th>
                            <th>Status</th>
                            <th>Consumed At</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $paintingSerials = $log->painting_serial_numbers ?? [];
                                if (! is_array($paintingSerials)) {
                                    $paintingSerials = json_decode((string) $paintingSerials, true) ?: [];
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="pair-main">{{ optional($log->paired_at)->format('d M Y H:i') ?? '-' }}</div>
                                    <div class="pair-sub">#{{ $log->id }}</div>
                                </td>
                                <td>{{ $log->project ?? '-' }}</td>
                                <td>
                                    <div class="pair-main">{{ $log->assembly_part }}</div>
                                    <div class="pair-sub">Back No: {{ $log->assembly_back_number ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="pair-badge">{{ $log->assembly_serial_number }}</span>
                                </td>
                                <td>
                                    <div class="pair-main">{{ $log->painting_part ?? '-' }}</div>
                                    <div class="pair-sub">Back No: {{ $log->painting_back_number ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="pair-serials">
                                        @forelse ($paintingSerials as $serial)
                                            <span class="pair-badge">{{ $serial }}</span>
                                        @empty
                                            <span class="pair-sub">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @if ((int) $log->status === \App\Models\KanbanPairingLog::STATUS_READY)
                                        <span class="status-badge status-ready">
                                            <i class="fas fa-check-circle"></i>
                                            Ready
                                        </span>
                                    @else
                                        <span class="status-badge status-consumed">
                                            <i class="fas fa-industry"></i>
                                            Consumed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="pair-main">{{ optional($log->consumed_at)->format('d M Y H:i') ?? '-' }}</div>
                                    @if ($log->consumed_mutation_id)
                                        <div class="pair-sub">Mutation #{{ $log->consumed_mutation_id }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>Pair: {{ $log->paired_by ?? '-' }}</div>
                                    <div class="pair-sub">Prod: {{ $log->consumed_by ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="empty-row" colspan="9">Belum ada data pairing sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pairing-log-pagination">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
