@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== KANBAN CHECK PAGE - SAME STYLE AS ViewMasterPis ===== */
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 14px;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: relative;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-table-card-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
            line-height: 1.5;
        }

        .bella-table-card-body {
            padding: 18px 20px 20px;
            background: var(--card);
        }

        .kbn-check-wrap {
            max-width: 980px;
            margin: 0 auto;
        }

        .kbn-icon-box {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            background: var(--bg);
            flex-shrink: 0;
        }

        .kbn-form-panel {
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 16px;
            background: var(--card);
        }

        .kbn-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .kbn-form-group {
            margin-bottom: 0;
        }

        .kbn-form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .kbn-form-group .form-control {
            height: 38px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 600;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .kbn-form-group .form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .act-btn {
            height: 38px;
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
            transition: .15s;
            white-space: nowrap;
        }

        .act-btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .act-btn.primary:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .kbn-alert {
            border: 1px solid transparent !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            margin-bottom: 14px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }

        .kbn-alert.alert-danger {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border-color: #fecaca !important;
        }

        .result-card {
            margin-top: 16px;
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            overflow: hidden;
            background: var(--card);
        }

        .result-card-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .result-card-title {
            font-size: 12px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 0;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            font-size: 12.5px !important;
        }

        .result-table th {
            width: 230px;
            padding: 12px 16px !important;
            color: var(--text-muted) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg) !important;
            border: none !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
        }

        .result-table td {
            padding: 12px 16px !important;
            border: none !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
            font-weight: 700;
            letter-spacing: .02em;
            background: var(--card) !important;
        }

        .result-table tr:last-child th,
        .result-table tr:last-child td {
            border-bottom: none !important;
        }

        .bella-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            line-height: 1.4;
        }

        .bella-badge-gray {
            background: #f1f5f9;
            color: #475569;
        }

        .bella-badge-yellow {
            background: #fef3c7;
            color: #b45309;
        }

        .bella-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .bella-badge-dark {
            background: #e5e7eb;
            color: #374151;
        }

        @media (max-width: 768px) {
            .bella-table-card-header {
                align-items: flex-start;
            }

            .bella-table-card-body {
                padding: 14px;
            }

            .kbn-form-panel {
                padding: 14px;
            }

            .kbn-form-grid {
                grid-template-columns: 1fr;
            }

            .act-btn {
                width: 100%;
            }

            .result-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .result-table th,
            .result-table td {
                display: block;
                width: 100%;
            }

            .result-table th {
                border-bottom: none !important;
                padding-bottom: 4px !important;
            }

            .result-table td {
                padding-top: 4px !important;
            }
        }
    </style>

    <div class="kbn-check-wrap">
        <div class="bella-table-card">
            <div class="bella-table-card-header">
                <div class="d-flex align-items-center" style="gap:10px;">
                    <span class="kbn-icon-box"><i class="fas fa-search"></i></span>
                    <div>
                        <span class="bella-table-card-title">Check Kanban Status</span>
                        <div class="bella-table-card-subtitle">
                            Cek status scan kanban berdasarkan Back Number dan Serial Number.
                        </div>
                    </div>
                </div>
            </div>

            <div class="bella-table-card-body">
                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger kbn-alert">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Display Custom Error Message --}}
                @if (session('error'))
                    <div class="alert alert-danger kbn-alert">
                        <i class="fas fa-exclamation-circle mr-1"></i> {!! session('error') !!}
                    </div>
                @elseif (isset($error))
                    <div class="alert alert-danger kbn-alert">
                        <i class="fas fa-exclamation-circle mr-1"></i> {!! $error !!}
                    </div>
                @endif

                <div class="kbn-form-panel">
                    <form method="POST" action="{{ route('dashboard.kbnCheckSubmit') }}">
                        @csrf
                        <div class="kbn-form-grid">
                            <div class="kbn-form-group">
                                <label for="back_number">Back Number</label>
                                <input type="text" id="back_number" name="back_number" class="form-control"
                                    placeholder="Input back number" value="{{ old('back_number', $back_number ?? '') }}"
                                    onkeyup="this.value = this.value.toUpperCase()" autocomplete="off" required>
                            </div>

                            <div class="kbn-form-group">
                                <label for="serial_number">Serial Number</label>
                                <input type="text" id="serial_number" name="serial_number" class="form-control"
                                    placeholder="Input 4 digit serial number"
                                    value="{{ old('serial_number', $serial_number ?? '') }}" autocomplete="off" required>
                            </div>

                            <button type="submit" class="act-btn primary">
                                <i class="fas fa-search"></i> Check
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Display Kanban Result --}}
                @if (isset($internalPart) && isset($kanban))
                    <div class="result-card">
                        <div class="result-card-header">
                            <h5 class="result-card-title">Kanban Detail</h5>
                            @switch($kanban->status)
                                @case(0)
                                    <span class="bella-badge bella-badge-gray">
                                        <i class="fas fa-minus-circle"></i> Belum Pernah di-Scan
                                    </span>
                                @break

                                @case(1)
                                    <span class="bella-badge bella-badge-yellow">
                                        <i class="fas fa-clock"></i> Sudah Scan Produksi / Belum Scan PPIC
                                    </span>
                                @break

                                @case(2)
                                    <span class="bella-badge bella-badge-green">
                                        <i class="fas fa-check-circle"></i> Sudah Scan PPIC
                                    </span>
                                @break

                                @default
                                    <span class="bella-badge bella-badge-dark">
                                        <i class="fas fa-question-circle"></i> Tidak Diketahui
                                    </span>
                            @endswitch
                        </div>

                        <table class="table result-table">
                            <tr>
                                <th>Back Number</th>
                                <td>{{ $internalPart->back_number }}</td>
                            </tr>
                            <tr>
                                <th>Part Name</th>
                                <td>{{ $internalPart->part_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Line</th>
                                <td>{{ $internalPart->line->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Last Scan</th>
                                <td>{{ $kanban->updated_at ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @switch($kanban->status)
                                        @case(0)
                                            <span class="bella-badge bella-badge-gray">
                                                <i class="fas fa-minus-circle"></i> Belum Pernah di-Scan
                                            </span>
                                        @break

                                        @case(1)
                                            <span class="bella-badge bella-badge-yellow">
                                                <i class="fas fa-clock"></i> Sudah Scan Produksi / Belum Scan PPIC
                                            </span>
                                        @break

                                        @case(2)
                                            <span class="bella-badge bella-badge-green">
                                                <i class="fas fa-check-circle"></i> Sudah Scan PPIC
                                            </span>
                                        @break

                                        @default
                                            <span class="bella-badge bella-badge-dark">
                                                <i class="fas fa-question-circle"></i> Tidak Diketahui
                                            </span>
                                    @endswitch
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
