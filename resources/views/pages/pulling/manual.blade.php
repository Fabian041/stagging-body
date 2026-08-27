@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== MANUAL RESET PAGE - SAME STYLE AS ViewMasterPis ===== */
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
        }

        .bella-table-card-body {
            padding: 18px 20px 20px;
            background: var(--card);
        }

        .manual-reset-wrap {
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .scan-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
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
            font-weight: 600;
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

        .reset-tabs {
            display: inline-flex;
            gap: 6px;
            padding: 4px;
            margin-bottom: 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
        }

        .reset-tab {
            border: 0;
            border-radius: 6px;
            padding: 8px 14px;
            background: transparent;
            color: var(--text-muted);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            cursor: pointer;
            transition: .15s;
        }

        .reset-tab.is-active {
            background: var(--card);
            color: var(--primary);
            box-shadow: var(--shadow);
        }

        .reset-panel {
            display: none;
        }

        .reset-panel.is-active {
            display: grid;
        }

        .manual-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .reset-action-btn {
            height: 42px;
            border: 1px solid var(--primary);
            border-radius: 5px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: var(--primary);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            cursor: pointer;
            transition: .15s;
            white-space: nowrap;
        }

        .reset-action-btn:hover {
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
        }

        .result-table td {
            padding: 12px 16px !important;
            border: none !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            color: var(--text) !important;
            font-weight: 700;
            letter-spacing: .03em;
            background: var(--card) !important;
        }

        .result-table tr:last-child th,
        .result-table tr:last-child td {
            border-bottom: none !important;
        }

        #notif-area .alert {
            border: 1px solid transparent !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            margin-bottom: 14px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }

        #notif-area .alert-success {
            background: #dcfce7 !important;
            color: #15803d !important;
            border-color: #bbf7d0 !important;
        }

        #notif-area .alert-danger {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border-color: #fecaca !important;
        }

        .scan-icon-box {
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

        @media (max-width: 768px) {
            .bella-table-card-header {
                align-items: flex-start;
            }

            .bella-table-card-body {
                padding: 14px;
            }

            .scan-panel {
                padding: 14px;
            }

            .manual-form-grid {
                grid-template-columns: 1fr;
            }

            .reset-action-btn {
                width: 100%;
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

    <div class="manual-reset-wrap">
        <div class="bella-table-card">
            <div class="bella-table-card-header">
                <div class="d-flex align-items-center" style="gap:10px;">
                    <span class="scan-icon-box"><i class="fas fa-barcode"></i></span>
                    <div>
                        <span class="bella-table-card-title">Reset Kanban</span>
                        <div class="bella-table-card-subtitle">Reset dengan scan barcode atau input Back Number dan Serial
                            Number secara manual.</div>
                    </div>
                </div>
            </div>

            <div class="bella-table-card-body">
                <div id="notif-area"></div>

                <div class="reset-tabs" role="tablist" aria-label="Reset Kanban Mode">
                    <button type="button" class="reset-tab is-active" data-target="scan-panel">
                        <i class="fas fa-barcode mr-1"></i> Scan
                    </button>
                    <button type="button" class="reset-tab" data-target="manual-panel">
                        <i class="fas fa-keyboard mr-1"></i> Manual
                    </button>
                </div>

                <div class="scan-panel reset-panel is-active" id="scan-panel">
                    <div class="scan-form-group">
                        <label for="code">Scan Barcode</label>
                        <input type="text" id="code" class="form-control" placeholder="Scan barcode di sini"
                            autocomplete="off" autofocus>
                        <div class="scan-hint">
                            Pastikan cursor berada di input ini sebelum melakukan scan. Data akan otomatis diproses setelah
                            scanner mengirim Enter.
                        </div>
                    </div>
                </div>

                <form class="scan-panel reset-panel" id="manual-panel" autocomplete="off">
                    <div class="manual-form-grid">
                        <div class="scan-form-group">
                            <label for="manual-back-number">Back Number</label>
                            <input type="text" id="manual-back-number" class="form-control"
                                placeholder="Input back number" autocomplete="off">
                        </div>

                        <div class="scan-form-group">
                            <label for="manual-serial-number">Serial Number</label>
                            <input type="text" id="manual-serial-number" class="form-control"
                                placeholder="Input 4 digit serial number" autocomplete="off">
                        </div>

                        <button type="submit" class="reset-action-btn">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>
                    <div class="scan-hint">
                        Gunakan tab ini kalau barcode tidak bisa discan. Back Number mengikuti data master Internal Part.
                    </div>
                </form>

                <div class="result-card" id="result-area">
                    <div class="result-card-header">
                        <h5 class="result-card-title">Information Details</h5>
                        <span class="bella-badge bella-badge-green"><i class="fas fa-check-circle"></i> Processed</span>
                    </div>

                    <table class="table result-table">
                        <tr>
                            <th id="part-label">Internal Part</th>
                            <td id="part-key"></td>
                        </tr>
                        <tr>
                            <th>Serial Number</th>
                            <td id="serial-number"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('code');
        const manualBackNumber = document.getElementById('manual-back-number');
        const manualSerialNumber = document.getElementById('manual-serial-number');
        let barcode = "";

        input.addEventListener('keypress', function(e) {
            const key = e.keyCode || e.which;

            if (key === 13) {
                e.preventDefault();
                const complete = barcode.trim().toUpperCase();
                barcode = "";
                handleBarcode(complete);
            } else {
                barcode += String.fromCharCode(e.which);
            }
        });

        document.querySelectorAll('.reset-tab').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.reset-tab').forEach((tab) => {
                    tab.classList.toggle('is-active', tab === button);
                });

                document.querySelectorAll('.reset-panel').forEach((panel) => {
                    panel.classList.toggle('is-active', panel.id === button.dataset.target);
                });

                if (button.dataset.target === 'manual-panel') {
                    focusManualInput();
                } else {
                    focusInput();
                }
            });
        });

        document.getElementById('manual-panel').addEventListener('submit', function(e) {
            e.preventDefault();

            const backNumber = manualBackNumber.value.trim().toUpperCase();
            const serial = manualSerialNumber.value.trim().toUpperCase();

            if (!backNumber || !serial) {
                showNotif('error', 'Back Number dan Serial Number wajib diisi.');
                focusManualInput();
                return;
            }

            resetKanban({
                back_number: backNumber,
                serial: serial
            }, 'Back Number', backNumber, serial, () => {
                manualBackNumber.value = '';
                manualSerialNumber.value = '';
                focusManualInput();
            });
        });

        function handleBarcode(code) {
            const allowedLengths = [218, 220, 230, 241, 242];

            console.log(code.length);

            if (!allowedLengths.includes(code.length)) {
                showNotif('error', 'Panjang barcode tidak dikenali!');
                focusInput();
                return;
            }

            let internal = "",
                serial = "";

            switch (code.length) {
                case 230:
                    internal = code.substr(41, 19);
                    serial = code.substr(123, 4);
                    break;
                case 220:
                    internal = code.substr(35, 16);
                    serial = code.substr(130, 4);
                    break;
                case 241:
                    internal = code.substr(35, 12);
                    serial = code.substr(127, 4);
                    break;
                case 218:
                    internal = code.substr(41, 16);
                    serial = code.substr(123, 4);
                    break;
                case 242:
                    internal = code.substr(35, 12);
                    serial = code.substr(127, 4);
                    break;
            }

            showNotif('success', 'Barcode berhasil diproses.');

            resetKanban({
                internal: internal,
                serial: serial
            }, 'Internal Part', internal, serial, focusInput);
        }

        function resetKanban(payload, partLabel, partKey, serial, afterReset) {
            document.getElementById('part-label').textContent = partLabel;
            document.getElementById('part-key').textContent = partKey;
            document.getElementById('serial-number').textContent = serial;
            document.getElementById('result-area').style.display = 'block';

            $.ajax({
                url: "{{ route('pulling.manualReset') }}",
                method: "POST",
                data: Object.assign({
                    _token: "{{ csrf_token() }}"
                }, payload),
                success: function(response) {
                    if (response.status === 'success') {
                        showNotif('success', response.message);
                        setTimeout(() => {
                            $('#code').val('');
                            $('#part-key').text('');
                            $('#serial-number').text('');
                            $('#result-area').hide();
                            if (typeof afterReset === 'function') afterReset();
                        }, 5000);
                    } else {
                        showNotif('error', response.message || 'Terjadi kesalahan.');
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal kirim data ke server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showNotif('error', msg);
                    console.error("Gagal kirim data", xhr);
                }
            });
        }

        function showNotif(type, message) {
            const color = type === 'error' ? 'danger' : 'success';
            const notifArea = document.getElementById('notif-area');

            notifArea.innerHTML = `
                <div class="alert alert-${color}">${message}</div>
            `;

            setTimeout(() => {
                notifArea.innerHTML = '';
            }, 5000); // Hapus notif setelah 5 detik
        }

        function focusInput() {
            setTimeout(() => document.getElementById('code').focus(), 300);
        }

        function focusManualInput() {
            setTimeout(() => document.getElementById('manual-back-number').focus(), 300);
        }

        window.clearCustomerPart = function() {
            localStorage.removeItem('customerPart');
            showNotif('success', 'customerPart berhasil dihapus.');
            focusInput();
        }

        window.scanCustomerFirstSound = function() {
            // Play sound or show visual cue
            console.warn('⚠️ Scan customer part dulu!');
        }

        focusInput();
    });
</script>
