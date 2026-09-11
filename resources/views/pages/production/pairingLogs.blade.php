@extends('layouts.root.main')

@section('main')
    <style>
        .bella-table-card {
            background: var(--card, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 10px;
            box-shadow: var(--shadow, 0 8px 24px rgba(15, 23, 42, .06));
            overflow: hidden;
            margin-top: 14px;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            position: relative;
            background: var(--card, #ffffff);
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy, #294795);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .bella-table-card-subtitle {
            font-size: 11px;
            color: var(--text-muted, #64748b);
            margin-top: 2px;
            line-height: 1.5;
        }

        .bella-table-card-body {
            padding: 18px 20px 20px;
            background: var(--card, #ffffff);
        }

        .pairing-log-filter {
            border: 1px solid var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            padding: 14px;
            background: var(--bg, #f8fafc);
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
            color: var(--text-muted, #64748b);
        }

        .pairing-log-form-group .form-control {
            height: 34px;
            min-width: 170px;
            border: 1px solid var(--border, #e5e7eb) !important;
            border-radius: 5px !important;
            background: var(--card, #ffffff) !important;
            color: var(--text, #0f172a) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 600;
            box-shadow: none !important;
        }

        .pairing-log-form-group .form-control:focus {
            border-color: var(--sky, #0097D8) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .pairing-log-search .form-control {
            min-width: 260px;
        }

        .act-btn {
            height: 34px;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
            transition: .15s;
            text-decoration: none !important;
            white-space: nowrap;
        }

        .act-btn.primary {
            background: var(--primary, #0070B7);
            border-color: var(--primary, #0070B7);
            color: #fff;
        }

        .act-btn.secondary {
            background: var(--card, #ffffff);
            border-color: var(--border, #e5e7eb);
            color: var(--text-muted, #64748b);
        }

        .act-btn:hover {
            filter: brightness(.95);
            text-decoration: none;
            color: inherit;
            transform: translateY(-1px);
        }

        .act-btn.primary:hover {
            color: #fff;
        }

        .stock-table-wrap {
            border: 1px solid var(--border, #e5e7eb);
            border-radius: var(--r, 8px);
            overflow: hidden;
            background: var(--card, #ffffff);
        }

        .stock-table {
            width: 100% !important;
            min-width: 1120px;
            border-collapse: collapse !important;
            font-size: 12.5px !important;
            margin-bottom: 0 !important;
        }

        .stock-table thead th {
            text-align: center !important;
            padding: 9px 12px !important;
            color: var(--text-muted, #64748b) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg, #f8fafc) !important;
            border-bottom: 1px solid var(--border, #e5e7eb) !important;
            border-top: none !important;
            white-space: nowrap !important;
        }

        .stock-table tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border, #e5e7eb) !important;
            vertical-align: middle !important;
            color: var(--text, #0f172a) !important;
            background: var(--card, #ffffff) !important;
        }

        .stock-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .stock-table tbody tr:hover td {
            background: var(--bg, #f8fafc) !important;
        }

        .pair-main {
            font-weight: 800;
            color: var(--navy, #294795);
            white-space: nowrap;
        }

        .pair-sub {
            margin-top: 2px;
            font-size: 11px;
            color: var(--text-muted, #64748b);
        }

        .pair-serials {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
        }

        .pair-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 22px;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid rgba(0, 112, 183, .18);
            font-size: 12px;
            font-weight: 800;
            background: rgba(0, 112, 183, .07);
            color: var(--primary, #0070B7);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
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
            color: var(--text-muted, #64748b) !important;
        }

        .pairing-log-pagination {
            margin-top: 14px;
        }
    </style>

    <div class="bella-table-card mt-3">
        <div class="bella-table-card-header">
            <div>
                <span class="bella-table-card-title"><i class="fas fa-link mr-1"></i>Kanban Pairing Trace</span>
                <div class="bella-table-card-subtitle">
                    Trace hasil pairing dari Validation sampai dikonsumsi scan production.
                </div>
            </div>
        </div>

        <div class="bella-table-card-body">
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

                    <button class="act-btn primary" type="submit">
                        <i class="fas fa-search"></i>
                        Filter
                    </button>

                    <a class="act-btn secondary" href="{{ route('dashboard.productionPairingLogs') }}">
                        Reset
                    </a>
                </form>
            </div>

            <div class="stock-table-wrap table-responsive">
                <table class="table table-bordered table-md stock-table">
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
