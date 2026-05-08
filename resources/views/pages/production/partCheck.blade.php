@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== PART CHECK PAGE - SAME STYLE AS ViewMasterPis ===== */
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

        .part-check-wrap {
            max-width: 980px;
            margin: 0 auto;
        }

        .part-icon-box {
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

        .scan-panel {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 16px;
            background: var(--card);
        }

        .scan-form-group {
            margin-bottom: 0;
        }

        .scan-form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .scan-form-group .form-control {
            height: 42px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 13px !important;
            font-weight: 700;
            letter-spacing: .02em;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .scan-form-group .form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .scan-hint {
            margin-top: 7px;
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .act-btn {
            height: 42px;
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

        .result-card {
            display: none;
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

        .bella-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .bella-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            font-size: 12.5px !important;
        }

        .result-table th {
            width: 220px;
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
            white-space: nowrap;
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
            word-break: break-word;
        }

        .result-table tr:last-child th,
        .result-table tr:last-child td {
            border-bottom: none !important;
        }

        #notif-area .alert,
        .part-alert {
            border: 1px solid transparent !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            margin-bottom: 14px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }

        #notif-area .alert-success,
        .part-alert.alert-success {
            background: #dcfce7 !important;
            color: #15803d !important;
            border-color: #bbf7d0 !important;
        }

        #notif-area .alert-danger,
        .part-alert.alert-danger {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border-color: #fecaca !important;
        }

        .part-alert ul {
            padding-left: 18px;
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .bella-table-card-header {
                align-items: flex-start;
            }

            .bella-table-card-body {
                padding: 14px;
            }

            .scan-panel {
                grid-template-columns: 1fr;
                padding: 14px;
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
                width: 100% !important;
            }

            .result-table th {
                padding-bottom: 4px !important;
                border-bottom: none !important;
            }

            .result-table td {
                padding-top: 4px !important;
            }
        }
    </style>

    <div class="bella-table-card">
        <div class="bella-table-card-header">
            <div class="d-flex align-items-center" style="gap: 10px;">
                <span class="part-icon-box">
                    <i class="fas fa-barcode"></i>
                </span>
                <div>
                    <div class="bella-table-card-title">Check Part Status</div>
                    <div class="bella-table-card-subtitle">Scan barcode untuk melihat status dan detail part terakhir.</div>
                </div>
            </div>
        </div>

        <div class="bella-table-card-body">
            <div class="part-check-wrap">
                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger part-alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Display Custom Error Message --}}
                @if (session('error'))
                    <div class="alert alert-danger part-alert">
                        {!! session('error') !!}
                    </div>
                @elseif (isset($error))
                    <div class="alert alert-danger part-alert">
                        {!! $error !!}
                    </div>
                @endif

                <div id="notif-area"></div>

                <div class="scan-panel">
                    <div class="scan-form-group">
                        <label for="code">Scan Barcode</label>
                        <input type="text" id="code" class="form-control" placeholder="Scan barcode di sini"
                            autocomplete="off" autofocus>
                        <div class="scan-hint">Tekan Enter setelah scan barcode untuk melakukan pengecekan data part.</div>
                    </div>

                    <button type="button" id="btn-check-part" class="act-btn primary">
                        <i class="fas fa-search"></i>
                        Check
                    </button>
                </div>

                <div class="result-card" id="result-area">
                    <div class="result-card-header">
                        <h5 class="result-card-title">
                            <i class="fas fa-info-circle mr-1"></i>Information Details
                        </h5>
                        <span class="bella-badge bella-badge-green">
                            <i class="fas fa-check-circle"></i> Data Found
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table result-table">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td id="result-id"></td>
                                </tr>
                                <tr>
                                    <th>Line</th>
                                    <td id="result-line"></td>
                                </tr>
                                <tr>
                                    <th>Model</th>
                                    <td id="result-model"></td>
                                </tr>
                                <tr>
                                    <th>Dandori Board</th>
                                    <td id="result-dandori-board"></td>
                                </tr>
                                <tr>
                                    <th>Barcode</th>
                                    <td id="result-barcode"></td>
                                </tr>
                                <tr>
                                    <th>Last 4</th>
                                    <td id="result-last4"></td>
                                </tr>
                                <tr>
                                    <th>Scan Date</th>
                                    <td id="result-scan-date"></td>
                                </tr>
                                <tr>
                                    <th>Scanned At</th>
                                    <td id="result-scanned-at"></td>
                                </tr>
                                <tr>
                                    <th>Kanban ID</th>
                                    <td id="result-kanban-id"></td>
                                </tr>
                                <tr>
                                    <th>Serial Kanban</th>
                                    <td id="result-serial"></td>
                                </tr>
                                <tr>
                                    <th>Back Number</th>
                                    <td id="result-back-number"></td>
                                </tr>
                                <tr>
                                    <th>Part Name</th>
                                    <td id="result-part-name"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        $(document).ready(function() {
            $('#code').focus();

            function showNotif(message, type = 'danger') {
                $('#notif-area').html(`
                <div class="alert alert-${type}">
                    ${message}
                </div>
            `);
            }

            function clearNotif() {
                $('#notif-area').html('');
            }

            function clearResult() {
                $('#result-area').hide();

                $('#result-id').text('');
                $('#result-line').text('');
                $('#result-model').text('');
                $('#result-dandori-board').text('');
                $('#result-barcode').text('');
                $('#result-last4').text('');
                $('#result-scan-date').text('');
                $('#result-scanned-at').text('');
                $('#result-kanban-id').text('');
                $('#result-serial').text('');
                $('#result-back-number').text('');
                $('#result-part-name').text('');
            }

            function fillResult(data) {
                $('#result-id').text(data.id ?? '-');
                $('#result-line').text(data.line ?? '-');
                $('#result-model').text(data.model ?? '-');
                $('#result-dandori-board').text(data.dandori_board ?? '-');
                $('#result-barcode').text(data.barcode ?? '-');
                $('#result-last4').text(data.last4 ?? '-');
                $('#result-scan-date').text(data.scan_date ?? '-');
                $('#result-scanned-at').text(data.scanned_at ?? '-');
                $('#result-kanban-id').text(data.kanban_id ?? '-');
                $('#result-serial').text(data.serial ?? '-');
                $('#result-back-number').text(data.back_number ?? '-');
                $('#result-part-name').text(data.part_name ?? '-');

                $('#result-area').show();
            }

            function submitBarcode() {
                let barcode = $('#code').val().trim();

                clearNotif();
                clearResult();

                if (barcode === '') {
                    showNotif('Barcode tidak boleh kosong.');
                    $('#code').val('').focus();
                    return;
                }

                console.log('submit jalan:', barcode);

                $.ajax({
                    url: "{{ route('dashboard.partCheckSubmit') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode
                    },
                    success: function(response) {
                        console.log('success:', response);

                        if (response.status) {
                            showNotif('Data part ditemukan.', 'success');
                            fillResult(response.data);
                        } else {
                            showNotif(response.message ?? 'Data tidak ditemukan.');
                        }

                        $('#code').val('').focus();
                    },
                    error: function(xhr) {
                        console.log('error:', xhr);

                        let message = 'Terjadi kesalahan.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        showNotif(message, 'danger');
                        $('#code').val('').focus();
                    }
                });
            }

            $('#code').on('keydown', function(e) {
                if (e.key === 'Enter' || e.which === 13) {
                    e.preventDefault();
                    submitBarcode();
                }
            });

            $('#btn-check-part').on('click', function(e) {
                e.preventDefault();
                submitBarcode();
            });
        });
    </script>
@endsection
