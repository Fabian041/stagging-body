@extends('layouts.root.auth')

@section('title', 'Bella - Quality Scan')

@section('main')
    <style>
        .quality-scan {
            --primary: #0d7ff2;
            --text: #1a2340;
            --muted: #6b7a99;
            --border: #dde3ef;
            --soft: #eef7ff;
            --success: #16a34a;
            --success-soft: #dff3e6;
            --danger: #dc2626;
            width: calc(100vw - 28px);
            max-width: none;
            margin-left: calc(50% - 50vw + 14px);
            margin-right: calc(50% - 50vw + 14px);
            margin-top: 14px;
        }

        html,
        body,
        body #app,
        body .main-wrapper {
            background: #f0f4f9 !important;
        }

        body .navbar-bg {
            display: none !important;
        }

        body .main-content {
            padding-top: 0 !important;
        }

        .scanner-layout {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 8px;
            padding: 8px;
            min-height: calc(100vh - 145px);
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fff;
        }

        .scanner-panel {
            margin-bottom: 8px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }

        .scanner-panel-title {
            margin: 0 0 10px;
            color: var(--text);
            font-size: 15px;
            font-weight: 850;
        }

        .scanner-help {
            margin: -4px 0 12px;
            color: var(--muted);
            font-size: 13px;
        }

        .mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 10px;
            border-radius: 999px;
            background: #e8f4fd;
            color: #0d5ea8;
            font-size: 12px;
            font-weight: 900;
        }

        .sampling-type-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        .sampling-type-option {
            position: relative;
        }

        .sampling-type-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .sampling-type-option span {
            display: block;
            padding: 10px;
            border: 1px solid #cde7fa;
            border-radius: 10px;
            background: #f8fbff;
            color: var(--text);
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
        }

        .sampling-type-option input:checked + span {
            border-color: #0d7ff2;
            background: #e8f4fd;
            color: #0d5ea8;
            box-shadow: 0 0 0 3px rgba(13, 127, 242, .1);
        }

        .scan-mode-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
        }

        .scan-mode-option {
            display: block;
            padding: 10px;
            border: 1px solid #cde7fa;
            border-radius: 10px;
            background: #f8fbff;
            color: var(--text);
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none !important;
        }

        .scan-mode-option.active {
            border-color: #0d7ff2;
            background: #e8f4fd;
            color: #0d5ea8;
            box-shadow: 0 0 0 3px rgba(13, 127, 242, .1);
        }

        .scanner-input {
            height: 48px;
            border-radius: 14px !important;
            border: 1px solid #cfd7e3 !important;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .scanner-button {
            height: 40px;
            margin-top: 12px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(90deg, #2d4fa3, #057cb8);
            color: #fff;
            font-size: 13px;
            font-weight: 850;
        }

        .scanner-counter {
            padding: 6px 0 10px;
            text-align: center;
            color: var(--text);
            font-size: 48px;
            line-height: 1;
            font-weight: 900;
        }

        .scanner-counter small {
            font-size: .75em;
            font-weight: 900;
        }

        .summary-list {
            display: grid;
            gap: 8px;
        }

        .summary-item {
            padding: 10px 12px;
            border: 1px solid #cde7fa;
            border-radius: 10px;
            background: var(--soft);
        }

        .summary-item label {
            display: block;
            margin: 0 0 4px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .summary-item strong {
            display: block;
            color: var(--text);
            font-size: 15px;
            font-weight: 850;
        }

        .step-bar {
            margin-bottom: 8px;
            padding: 4px 10px;
            border: 1px solid #9ecaf4;
            border-radius: 6px;
            background: #cfe7ff;
            color: #00437d;
            text-align: center;
            font-size: 12px;
            font-weight: 850;
        }

        .status-bar {
            margin-bottom: 8px;
            padding: 11px 10px;
            border: 1px solid #b9dfc1;
            border-radius: 4px;
            background: var(--success-soft);
            color: #075d22;
            text-align: center;
            font-weight: 850;
        }

        .status-title {
            display: block;
            margin-bottom: 3px;
            font-size: 18px;
        }

        .quality-alert {
            display: none;
            margin-bottom: 8px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 800;
        }

        .quality-alert.success {
            display: block;
            background: rgba(22, 163, 74, .12);
            color: var(--success);
        }

        .quality-alert.danger {
            display: block;
            background: rgba(220, 38, 38, .1);
            color: var(--danger);
        }

        .success-toast {
            position: fixed;
            top: 90px;
            left: 50%;
            right: auto;
            z-index: 10600;
            display: flex;
            align-items: center;
            gap: 12px;
            width: min(360px, calc(100vw - 32px));
            padding: 16px 18px;
            border: 1px solid rgba(22, 163, 74, .28);
            border-radius: 18px;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
            color: #14532d;
            opacity: 0;
            transform: translate(-50%, -12px) scale(.96);
            pointer-events: none;
            transition: opacity .25s ease, transform .25s ease;
        }

        .success-toast.show {
            opacity: 1;
            transform: translate(-50%, 0) scale(1);
        }

        .success-toast-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #16a34a;
            color: #fff;
            font-size: 18px;
            box-shadow: 0 10px 22px rgba(22, 163, 74, .28);
        }

        .success-toast-title {
            margin-bottom: 2px;
            font-size: 15px;
            font-weight: 950;
        }

        .success-toast-message {
            font-size: 12px;
            line-height: 1.45;
            font-weight: 750;
            color: #166534;
        }

        .scanner-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 12px;
            align-items: stretch;
        }

        .photo-stage {
            min-height: calc(100vh - 310px);
            border: 1px solid #bfe5ff;
            border-radius: 16px;
            background: var(--soft);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
        }

        .photo-stage img {
            max-width: 100%;
            max-height: calc(100vh - 370px);
            object-fit: contain;
            border-radius: 8px;
        }

        .photo-placeholder {
            color: var(--muted);
            font-size: 15px;
            font-weight: 700;
        }

        .photo-placeholder i {
            display: block;
            margin-bottom: 12px;
            font-size: 38px;
        }

        .part-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .part-info-item {
            padding: 16px;
            min-height: 76px;
            border: 1px solid #cde7fa;
            border-radius: 16px;
            background: var(--soft);
        }

        .part-info-item label {
            display: block;
            margin: 0 0 7px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .part-info-item strong {
            display: block;
            color: var(--text);
            font-size: 16px;
            font-weight: 900;
            word-break: break-word;
        }

        .prototype-checksheet {
            display: none;
            margin-top: 16px;
            border: 1px solid #cfe0f5;
            border-radius: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            overflow: hidden;
            box-shadow: 0 14px 36px rgba(13, 127, 242, .08);
        }

        .prototype-checksheet-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #eef6ff, #f8fbff);
            border-bottom: 1px solid #dbe7f4;
        }

        .prototype-checksheet-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
            font-size: 16px;
            font-weight: 900;
        }

        .prototype-checksheet-title i {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #2d4fa3, #057cb8);
            color: #fff;
            box-shadow: 0 10px 24px rgba(5, 124, 184, .22);
        }

        .prototype-checksheet-subtitle {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .prototype-badge {
            padding: 7px 12px;
            border-radius: 999px;
            background: #e8f4fd;
            color: #0d5ea8;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .prototype-checksheet-body {
            padding: 18px;
        }

        .checksheet-form-list {
            display: grid;
            gap: 14px;
        }

        .checksheet-card {
            display: grid;
            grid-template-columns: 1.2fr .9fr .9fr 1fr;
            gap: 12px;
            padding: 16px;
            border: 1px solid #d7e2ef;
            border-radius: 18px;
            background: #fff;
        }

        .checksheet-field {
            min-width: 0;
        }

        .checksheet-field label {
            display: block;
            margin-bottom: 7px;
            color: #6b7a99;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .checksheet-item-title {
            color: var(--text);
            font-size: 15px;
            font-weight: 900;
        }

        .checksheet-standard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .checksheet-standard-value {
            padding: 10px 12px;
            border-radius: 12px;
            background: #f3f8fe;
            color: #0d5ea8;
            font-size: 14px;
            font-weight: 900;
        }

        .checksheet-standard-value span {
            display: block;
            margin-bottom: 4px;
            color: #6b7a99;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .checksheet-empty {
            padding: 16px;
            border: 1px dashed #b8c9df;
            border-radius: 16px;
            background: #f8fbff;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .checksheet-card {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .checksheet-card {
                grid-template-columns: 1fr;
            }
        }

        .prototype-pic-panel {
            margin-bottom: 14px;
            padding: 14px;
            border: 1px solid #cde7fa;
            border-radius: 16px;
            background: #eef7ff;
        }

        .prototype-pic-panel label {
            display: block;
            margin-bottom: 7px;
            color: #0d5ea8;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .prototype-pic-warning {
            display: none;
            margin-top: 8px;
            padding: 9px 11px;
            border-radius: 10px;
            background: rgba(220, 38, 38, .1);
            color: var(--danger);
            font-size: 12px;
            font-weight: 850;
        }

        .prototype-pic-warning.show {
            display: block;
        }

        .prototype-value-warning {
            display: none;
            margin-top: 8px;
            padding: 9px 11px;
            border-radius: 10px;
            background: rgba(220, 38, 38, .1);
            color: var(--danger);
            font-size: 12px;
            font-weight: 850;
        }

        .prototype-value-warning.show {
            display: block;
        }

        .prototype-value-input.is-invalid,
        .prototype-pic-input.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, .1);
        }

        .prototype-value-input,
        .prototype-pic-input,
        .prototype-note-input {
            width: 100%;
            border: 1px solid #cfd7e3;
            border-radius: 12px;
            background: #fff;
            color: #1a2340;
            font-size: 13px;
            font-weight: 800;
            transition: all .18s ease;
        }

        .prototype-value-input,
        .prototype-pic-input {
            height: 42px;
            padding: 0 12px;
        }

        .prototype-result-badge {
            width: 100%;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border: 1px solid #cfd7e3;
            border-radius: 12px;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            font-weight: 900;
            text-align: center;
        }

        .prototype-result-badge.ok {
            border-color: rgba(22, 163, 74, .35);
            background: rgba(22, 163, 74, .12);
            color: #15803d;
        }

        .prototype-result-badge.ng {
            border-color: rgba(220, 38, 38, .35);
            background: rgba(220, 38, 38, .12);
            color: #b91c1c;
        }

        .full-ok-button {
            display: none;
            border: 0;
            border-radius: 12px;
            background: #16a34a;
            color: #fff;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 900;
        }

        .full-ok-button.show {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .quality-scan.full-mode .item-cell {
            cursor: pointer;
        }

        .prototype-note-input {
            min-height: 42px;
            padding: 10px 12px;
            resize: vertical;
        }

        .prototype-value-input:focus,
        .prototype-pic-input:focus,
        .prototype-note-input:focus {
            outline: 0;
            border-color: #7cb8f2;
            box-shadow: 0 0 0 4px rgba(13, 127, 242, .12);
        }

        .prototype-hint {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .prototype-hint-item {
            padding: 10px 12px;
            border-radius: 12px;
            background: #f3f8fe;
            color: #51627f;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .scanner-layout,
            .scanner-content {
                grid-template-columns: 1fr;
            }

            .part-info {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .quality-scan {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }

            .part-info {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="section quality-scan {{ $mode === 'full' ? 'full-mode' : 'sampling-mode' }}">
        <div class="scanner-layout">
            <aside class="scanner-left">
                <div class="scanner-panel">
                    <h6 class="scanner-panel-title">Metode Quality Check</h6>
                    <div class="mode-badge">
                        <i class="fas {{ $mode === 'full' ? 'fa-boxes' : 'fa-vials' }}"></i>
                        {{ $mode === 'full' ? '100% Check / Full Inspection' : 'Sampling Check' }}
                    </div>
                    <p class="scanner-help mt-2 mb-0">
                        @if ($mode === 'full')
                            Ambil part 1 box, ambil kanban, scan kanban, lalu klik OK sekali jika semua part dalam box OK.
                        @else
                            Pilih Scan Kanban First atau Last untuk pengecekan sampling.
                        @endif
                    </p>
                    <div class="scan-mode-group">
                        <a href="{{ route('quality.scan', ['mode' => 'full']) }}" class="scan-mode-option {{ $mode === 'full' ? 'active' : '' }}">100% Cek</a>
                        <a href="{{ route('quality.scan', ['mode' => 'sampling']) }}" class="scan-mode-option {{ $mode !== 'full' ? 'active' : '' }}">Sampling</a>
                    </div>
                    @if ($mode !== 'full')
                        <div class="sampling-type-group">
                            <label class="sampling-type-option">
                                <input type="radio" name="sampling_type" value="FIRST" checked>
                                <span>Scan First</span>
                            </label>
                            <label class="sampling-type-option">
                                <input type="radio" name="sampling_type" value="LAST">
                                <span>Scan Last</span>
                            </label>
                        </div>
                    @endif
                </div>

                <div class="scanner-panel">
                    <h6 class="scanner-panel-title"><i class="fas fa-barcode mr-1"></i> Barcode Kanban</h6>
                    <p class="scanner-help" id="scan-help-text">Scan barcode kanban atau tempel data kanban lalu tekan Enter.</p>
                    <form id="lookup-form">
                        @csrf
                        <input type="text" id="kanban_code" class="form-control scanner-input" autocomplete="off"
                            placeholder="SCAN KANBAN..." autofocus>
                        <button type="submit" class="btn btn-block scanner-button">
                            <i class="fas fa-search mr-1"></i> Tampilkan Detail Part
                        </button>
                    </form>
                </div>

                <div class="scanner-panel">
                    <h6 class="scanner-panel-title">Counter</h6>
                    <div class="scanner-counter">
                        <span id="total_scan">0</span><small>/<span id="total_part">0</span></small>
                    </div>
                </div>

                <div class="scanner-panel">
                    <h6 class="scanner-panel-title">Summary Scan</h6>
                    <div class="summary-list">
                        <div class="summary-item">
                            <label>Total Scan</label>
                            <strong id="summary_total_scan">0</strong>
                        </div>
                        <div class="summary-item">
                            <label>Time / Box</label>
                            <strong id="time_per_box">00:00</strong>
                        </div>
                        <div class="summary-item">
                            <label>Total Part</label>
                            <strong id="summary_total_part">0</strong>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="scanner-main">
                <div class="step-bar">
                    @if ($mode === 'full')
                        STEP 1 <i class="fas fa-box"></i> Ambil Part 1 Box &nbsp;→&nbsp;
                        STEP 2 <i class="fas fa-barcode"></i> Ambil & Scan Kanban &nbsp;→&nbsp;
                        STEP 3 <i class="fas fa-check-double"></i> Cek Sampai Habis & Klik OK
                    @else
                        STEP 1 <i class="fas fa-barcode"></i> Scan Kanban First / Last &nbsp;→&nbsp;
                        STEP 2 <i class="fas fa-image"></i> Tampilkan Detail Part
                    @endif
                </div>

                <div class="status-bar">
                    <span class="status-title"><i class="fas fa-check-circle mr-1"></i><span id="scan-status-title">Ready</span></span>
                    <span id="scan-status-message">Silahkan Scan Kanban untuk memulai</span>
                </div>

                <div id="scan-alert" class="quality-alert"></div>

                <div id="success-toast" class="success-toast" role="status" aria-live="polite">
                    <span class="success-toast-icon"><i class="fas fa-check"></i></span>
                    <span>
                        <div class="success-toast-title">Success</div>
                        <div class="success-toast-message" id="success-toast-message">Data checksheet berhasil disimpan.</div>
                    </span>
                </div>

                <div class="scanner-content">
                    <div class="photo-stage">
                        <div class="photo-placeholder">
                            <i class="fas fa-image"></i>
                            <span id="photo-placeholder">Foto tidak tersedia</span>
                            <img id="part-photo-img" style="display: none;" src="" alt="Part Photo">
                        </div>
                    </div>

                    <div class="part-info">
                        <div class="part-info-item">
                            <label>Part Name</label>
                            <strong id="part_name">-</strong>
                        </div>
                        <div class="part-info-item">
                            <label>Part Number</label>
                            <strong id="part_number">-</strong>
                        </div>
                        <div class="part-info-item">
                            <label>Back Number</label>
                            <strong id="back_number">-</strong>
                        </div>
                        <div class="part-info-item">
                            <label>Qty / Box</label>
                            <strong id="qty_per_box">-</strong>
                        </div>
                        <div class="part-info-item">
                            <label>Customer</label>
                            <strong id="customer">-</strong>
                        </div>
                        <div class="part-info-item">
                            <label>Line</label>
                            <strong id="line_info">QC GATE R1</strong>
                        </div>
                    </div>
                </div>

                <div class="prototype-checksheet" id="prototype-checksheet">
                    <div class="prototype-checksheet-head">
                        <div>
                            <div class="prototype-checksheet-title">
                                <i class="fas fa-tools"></i>
                                <span>Detail Checksheet</span>
                            </div>
                            <div class="prototype-checksheet-subtitle">
                                {{ $mode === 'full' ? 'Untuk 100% cek, klik OK sekali jika semua item/part dalam box sudah OK.' : 'Input nilai actual, lalu result otomatis terbaca OK / NG berdasarkan range standard' }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <button type="button" class="full-ok-button {{ $mode === 'full' ? 'show' : '' }}" id="full-ok-all-btn">
                                <i class="fas fa-check-double"></i> OK Semua Item
                            </button>
                            <div class="prototype-badge" id="checksheet-badge">Quality Checksheet</div>
                        </div>
                    </div>

                    <div class="prototype-checksheet-body">
                        <div class="prototype-pic-panel">
                            <label for="inspection-pic">PIC Pengecek / Scanner</label>
                            <input type="text" id="inspection-pic" class="prototype-pic-input"
                                autocomplete="off" maxlength="100">
                            <div id="inspection-pic-warning" class="prototype-pic-warning">
                                <i class="fas fa-exclamation-circle mr-1"></i> PIC harus diisi sebelum submit.
                            </div>
                        </div>

                        <div class="checksheet-form-list" id="prototype-checksheet-body">
                            <div class="checksheet-empty">Scan kanban untuk menampilkan form input checksheet.</div>
                        </div>

                        <div class="prototype-hint">
                            <div class="prototype-hint-item">
                                <i class="fas fa-keyboard mr-1"></i> {{ $mode === 'full' ? '100% cek bisa klik OK Semua Item setelah 1 box selesai dicek' : 'Actual bisa diinput manual' }}
                            </div>
                            <div class="prototype-hint-item">
                                <i class="fas fa-ruler-combined mr-1"></i> Contoh pembacaan: lower 140 gr, upper 158 gr, actual 150 gr = OK
                            </div>
                            <div class="prototype-hint-item">
                                <i class="fas fa-save mr-1"></i> Setelah input actual, klik simpan agar masuk ke history dan detail checksheet
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn scanner-button px-4" id="save-inspection-btn" disabled>
                                <i class="fas fa-save mr-1"></i> Simpan Checksheet
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </section>
@endsection

@section('custom-script')
    <script>
        const qualityCheckMode = '{{ $mode === 'full' ? 'FULL' : 'SAMPLING' }}';
        let totalScan = 0;
        let lastScanTime = null;
        let currentScanData = null;

        function formatDuration(milliseconds) {
            const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
            const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
            const seconds = String(totalSeconds % 60).padStart(2, '0');

            return `${minutes}:${seconds}`;
        }

        function updateScanSummary(qtyPerBox) {
            const now = new Date();

            totalScan += 1;

            if (lastScanTime) {
                $('#time_per_box').text(formatDuration(now - lastScanTime));
            } else {
                $('#time_per_box').text('00:00');
            }

            lastScanTime = now;

            const qty = parseInt(qtyPerBox, 10) || 0;
            const totalPart = totalScan * qty;

            $('#total_scan, #summary_total_scan').text(totalScan);
            $('#total_part, #summary_total_part').text(totalPart);
        }

        function showAlert(type, message) {
            $('#scan-alert').removeClass('success danger').addClass(type).text(message);
            document.getElementById('scan-alert')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function showSuccessToast(message) {
            const $toast = $('#success-toast');

            $('#success-toast-message').text(message || 'Data checksheet berhasil disimpan.');
            $toast.addClass('show');

            clearTimeout(window.qualitySuccessToastTimer);
            window.qualitySuccessToastTimer = setTimeout(function() {
                $toast.removeClass('show');
            }, 5000);
        }

        function normalizeDecimalValue(value) {
            const match = String(value || '').replace(',', '.').match(/-?\d+(\.\d+)?/);

            return match ? parseFloat(match[0]) : null;
        }

        function judgePrototypeResult(actualValue, lowerValue, upperValue) {
            const actual = normalizeDecimalValue(actualValue);
            const lower = normalizeDecimalValue(lowerValue);
            const upper = normalizeDecimalValue(upperValue);

            if (actual === null || lower === null || upper === null) {
                return 'PENDING';
            }

            return actual >= lower && actual <= upper ? 'OK' : 'NG';
        }

        function updatePrototypeAutoJudge(input) {
            const $input = $(input);
            const $row = $input.closest('.checksheet-card');
            if (qualityCheckMode === 'FULL' && String($input.val()).trim().toUpperCase() === 'OK') {
                setChecksheetRowOk($row);
                return;
            }

            const result = judgePrototypeResult(
                $input.val(),
                $input.data('std-lower'),
                $input.data('std-upper')
            );
            const $badge = $row.find('.prototype-result-badge');

            $badge
                .removeClass('ok ng')
                .addClass(result === 'OK' ? 'ok' : (result === 'NG' ? 'ng' : ''))
                .text(result)
                .attr('data-result', result);

            if ($input.val().trim() && result !== 'PENDING') {
                $input.removeClass('is-invalid');
                $row.find('.prototype-value-warning').removeClass('show');
            }
        }

        function setChecksheetRowOk($row) {
            $row.find('.prototype-value-input')
                .val('OK')
                .removeClass('is-invalid');
            $row.find('.prototype-result-badge')
                .removeClass('ng')
                .addClass('ok')
                .text('OK')
                .attr('data-result', 'OK');
            $row.find('.prototype-value-warning').removeClass('show');

            const $remark = $row.find('.prototype-note-input');
            if (!$remark.val().trim()) {
                $remark.val('100% check OK');
            }
        }

        function markAllFullChecksheetOk() {
            if (qualityCheckMode !== 'FULL') {
                return;
            }

            const $rows = $('#prototype-checksheet-body .checksheet-card').not('[data-source="FIRST"]');
            if (!$rows.length) {
                showAlert('danger', 'Scan kanban terlebih dahulu untuk menampilkan item cek.');
                return;
            }

            $rows.each(function() {
                setChecksheetRowOk($(this));
            });

            showAlert('success', 'Semua item cek 100% sudah diisi OK. Isi PIC lalu simpan checksheet.');
            $('#scan-status-title').text('100% Check OK');
            $('#scan-status-message').text('Semua item check sudah OK untuk box ini. Klik Simpan Checksheet untuk lanjut ke pengecekan berikutnya.');
        }

        function showActualInputWarnings() {
            let $firstInvalid = null;

            $('#prototype-checksheet-body .checksheet-card').each(function() {
                const $row = $(this);
                const $input = $row.find('.prototype-value-input');
                const result = $row.find('.prototype-result-badge').attr('data-result') || 'PENDING';
                const isInvalid = !$input.val().trim() || result === 'PENDING';

                $input.toggleClass('is-invalid', isInvalid);
                $row.find('.prototype-value-warning').toggleClass('show', isInvalid);

                if (isInvalid && !$firstInvalid) {
                    $firstInvalid = $input;
                }
            });

            return $firstInvalid;
        }

        function renderFirstChecksheet(firstItems) {
            const items = Array.isArray(firstItems) ? firstItems : [];

            if (!items.length) {
                return '';
            }

            const firstPic = items.find(function(item) {
                return item.pic;
            })?.pic || '-';

            const rows = items.map(function(item) {
                return `
                    <div class="checksheet-card" data-source="FIRST">
                        <div class="checksheet-field">
                            <label>Scan FIRST - Item</label>
                            <div class="checksheet-item-title">${item.item || '-'}</div>
                        </div>
                        <div class="checksheet-field">
                            <label>Standard FIRST</label>
                            <div class="checksheet-standard-grid">
                                <div class="checksheet-standard-value"><span>Std Upper</span>${item.std_upper || '-'}</div>
                                <div class="checksheet-standard-value"><span>Std Lower</span>${item.std_lower || '-'}</div>
                            </div>
                        </div>
                        <div class="checksheet-field">
                            <label>Actual FIRST</label>
                            <input type="text" class="prototype-value-input" value="${item.actual || '-'}" readonly>
                        </div>
                        <div class="checksheet-field">
                            <label>Result FIRST</label>
                            <span class="prototype-result-badge ${item.result === 'OK' ? 'ok' : (item.result === 'NG' ? 'ng' : '')}">${item.result || '-'}</span>
                        </div>
                        <div class="checksheet-field" style="grid-column: 1 / -1;">
                            <label>Remark FIRST</label>
                            <textarea class="prototype-note-input" rows="2" readonly>${item.remark || '-'}</textarea>
                        </div>
                    </div>
                `;
            }).join('');

            return `
                <div class="checksheet-empty" style="text-align:left; color:#0f5fb8; font-weight:850;">
                    <i class="fas fa-history mr-1"></i> Checksheet Scan FIRST dari part yang sama
                    <div style="margin-top:6px; color:#1a2340; font-weight:900;">
                        PIC FIRST: ${firstPic}
                    </div>
                </div>
                ${rows}
                <div class="checksheet-empty" style="text-align:left; color:#0f5fb8; font-weight:850;">
                    <i class="fas fa-clipboard-check mr-1"></i> Input Checksheet Scan LAST
                </div>
            `;
        }

        function renderPrototypeChecksheet(checkItems, firstItems) {
            const items = Array.isArray(checkItems) ? checkItems : [];

            if (!items.length) {
                $('#prototype-checksheet').hide();
                $('#prototype-checksheet-body').html('<div class="checksheet-empty">Master item check untuk part ini belum terdaftar.</div>');
                $('#save-inspection-btn').prop('disabled', true);
                return;
            }

            const rows = items.map(function(item, index) {
                const result = judgePrototypeResult(item.actual || '', item.std_lower || '', item.std_upper || '');

                return `
                    <div class="checksheet-card">
                        <div class="checksheet-field">
                            <label>Item</label>
                            <div class="checksheet-item-title item-cell">${item.item || '-'}</div>
                        </div>
                        <div class="checksheet-field">
                            <label>Standard</label>
                            <div class="checksheet-standard-grid">
                                <div class="checksheet-standard-value">
                                    <span>Std Upper</span>
                                    ${item.std_upper || '-'}
                                </div>
                                <div class="checksheet-standard-value">
                                    <span>Std Lower</span>
                                    ${item.std_lower || '-'}
                                </div>
                            </div>
                        </div>
                        <div class="checksheet-field">
                            <label>Actual Input</label>
                            <input
                                type="text"
                                class="prototype-value-input"
                                name="prototype_actual_${index}"
                                data-std-upper="${item.std_upper || ''}"
                                data-std-lower="${item.std_lower || ''}"
                                value="${item.actual || ''}">
                            <div class="prototype-value-warning">
                                <i class="fas fa-exclamation-circle mr-1"></i> Actual input harus diisi.
                            </div>
                        </div>
                        <div class="checksheet-field">
                            <label>Result Otomatis</label>
                            <span
                                class="prototype-result-badge ${result === 'OK' ? 'ok' : (result === 'NG' ? 'ng' : '')}"
                                data-result="${result}"
                                data-name="prototype_result_${index}">${result}</span>
                        </div>
                        <div class="checksheet-field" style="grid-column: 1 / -1;">
                            <label>Remark</label>
                            <textarea
                                class="prototype-note-input"
                                name="prototype_remark_${index}"
                                rows="2"
                                placeholder="Catatan pengecekan...">${item.remark || ''}</textarea>
                        </div>
                    </div>
                `;
            }).join('');

            $('#prototype-checksheet-body').html(renderFirstChecksheet(firstItems) + rows);
            $('#prototype-checksheet').show();
            $('#save-inspection-btn').prop('disabled', false);
        }

        $(document).on('input', '.prototype-value-input', function() {
            updatePrototypeAutoJudge(this);
        });

        $('#full-ok-all-btn').on('click', function() {
            markAllFullChecksheetOk();
        });

        $(document).on('click', '.quality-scan.full-mode .item-cell', function() {
            markAllFullChecksheetOk();
        });

        $('#inspection-pic').on('input', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid');
                $('#inspection-pic-warning').removeClass('show');
            }
        });

        function buildInspectionDetails() {
            return $('#prototype-checksheet-body .checksheet-card').not('[data-source="FIRST"]').map(function() {
                const $row = $(this);
                const item = $row.find('.item-cell').text().trim();
                const stdUpper = $row.find('.prototype-value-input').data('std-upper') || '';
                const stdLower = $row.find('.prototype-value-input').data('std-lower') || '';
                const actual = $row.find('.prototype-value-input').val().trim();
                const result = $row.find('.prototype-result-badge').attr('data-result') || 'PENDING';
                const remark = $row.find('.prototype-note-input').val().trim();

                if (!item) {
                    return null;
                }

                return {
                    item: item,
                    standard: `Std Upper: ${stdUpper} | Std Lower: ${stdLower}`,
                    result: result === 'PENDING' ? 'NA' : result,
                    remark: actual ? `Actual: ${actual}${remark ? ' | ' + remark : ''}` : remark
                };
            }).get().filter(Boolean);
        }

        $('#save-inspection-btn').on('click', function() {
            if (!currentScanData) {
                showAlert('danger', 'Silahkan scan kanban terlebih dahulu.');
                return;
            }

            const details = buildInspectionDetails();

            if (!details.length) {
                showAlert('danger', 'Detail checksheet belum tersedia.');
                return;
            }

            const hasPending = details.some(function(detail) {
                return detail.result === 'NA';
            });

            if (hasPending) {
                const $firstInvalid = showActualInputWarnings();
                showAlert('danger', 'Masih ada actual yang belum diisi. Lengkapi semua actual terlebih dahulu.');
                if ($firstInvalid) {
                    $firstInvalid.focus();
                }
                return;
            }

            const inspectionPic = $('#inspection-pic').val().trim();

            if (!inspectionPic) {
                $('#inspection-pic').addClass('is-invalid');
                $('#inspection-pic-warning').addClass('show');
                showAlert('danger', 'PIC pengecek wajib diisi.');
                $('#inspection-pic').focus();
                return;
            }

            $('#inspection-pic').removeClass('is-invalid');
            $('#inspection-pic-warning').removeClass('show');

            const $button = $(this).prop('disabled', true);

            $.ajax({
                url: '{{ route('quality.storeInspection') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    kanban_code: currentScanData.kanban_code || '',
                    inspection_mode: qualityCheckMode,
                    check_type: qualityCheckMode === 'FULL' ? '100% CHECK' : ($('input[name="sampling_type"]:checked').val() || 'FIRST'),
                    line: currentScanData.line || 'QC GATE R1',
                    pic: inspectionPic,
                    part_name: currentScanData.part_name || '',
                    part_number: currentScanData.part_number || '',
                    back_number: currentScanData.back_number || '',
                    qty_per_box: String(currentScanData.qty_per_box || ''),
                    total_box: qualityCheckMode === 'FULL' ? totalScan : '',
                    total_part: qualityCheckMode === 'FULL' ? ($('#summary_total_part').text() || '0') : '',
                    details: details
                },
                success: function(res) {
                    const inspectionId = res.data?.inspection_id;
                    const successMessage = res.message || 'Hasil inspection berhasil disimpan.';

                    $('#scan-status-title').text('Success');
                    $('#scan-status-message').text(successMessage);
                    showAlert('success', successMessage);
                    showSuccessToast(successMessage);

                    if (qualityCheckMode === 'FULL') {
                        currentScanData = null;
                        $('#prototype-checksheet').hide();
                        $('#prototype-checksheet-body').html('<div class="checksheet-empty">Scan kanban untuk menampilkan form input checksheet.</div>');
                        $('#save-inspection-btn').prop('disabled', true);
                        $('#inspection-pic').val('');
                        $('#kanban_code').val('').focus();
                        $('#scan-status-title').text('Lanjut Box Berikutnya');
                        $('#scan-status-message').text('Data 100% check tersimpan. Ambil box berikutnya, ambil kanban, lalu scan kembali.');
                        return;
                    }

                    if (inspectionId) {
                        setTimeout(function() {
                            window.location.href = '{{ route('quality.history') }}';
                        }, 3000);
                        return;
                    }

                    $button.prop('disabled', false);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal menyimpan hasil inspection.';
                    $('#scan-status-title').text('Error');
                    $('#scan-status-message').text(msg);
                    showAlert('danger', msg);
                    $button.prop('disabled', false);
                }
            });
        });

        $('#lookup-form').on('submit', function(e) {
            e.preventDefault();

            const kanbanCode = $('#kanban_code').val().trim();
            if (!kanbanCode) {
                showAlert('danger', 'Barcode kanban wajib diisi.');
                return;
            }

            $.ajax({
                url: '{{ route('quality.lookupKanban') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    kanban_code: kanbanCode,
                    check_type: qualityCheckMode === 'FULL' ? '100% CHECK' : ($('input[name="sampling_type"]:checked').val() || 'FIRST')
                },
                success: function(res) {
                    const data = res.data || {};

                    $('#part_name').text(data.part_name || '-');
                    $('#part_number').text(data.part_number || '-');
                    $('#back_number').text(data.back_number || '-');
                    $('#qty_per_box').text(data.qty_per_box || '-');
                    $('#customer').text(data.customer || '-');
                    currentScanData = data;
                    $('#line_info').text(data.line || 'QC GATE R1');
                    $('#checksheet-badge').text((data.part_number || 'Quality') + ' Checksheet');
                    renderPrototypeChecksheet(data.check_items || [], data.first_check_items || []);

                    if (!(data.check_items || []).length) {
                        showAlert('danger', 'Master item check untuk part ini belum terdaftar. Hubungi admin untuk input standard part ini.');
                    }

                    const samplingType = $('input[name="sampling_type"]:checked').val() || 'FIRST';

                    if (samplingType === 'LAST' && !(data.first_check_items || []).length) {
                        showAlert('danger', 'Scan First terlebih dahulu untuk part ini. Checksheet FIRST belum ditemukan.');
                    }

                    if (data.photo) {
                        $('#part-photo-img').attr('src', data.photo).show();
                        $('#photo-placeholder').hide();
                    } else {
                        $('#part-photo-img').hide();
                        $('#photo-placeholder').show().text('Foto tidak tersedia');
                    }

                    updateScanSummary(data.qty_per_box);

                    if (qualityCheckMode === 'FULL') {
                        $('#scan-status-title').text('Kanban 100% Check Terbaca');
                        $('#scan-status-message').text(`Box ke-${totalScan} siap dicek. Setelah part habis 1 box dan OK, klik OK Semua Item.`);
                        showAlert('success', `Kanban box ke-${totalScan} terbaca. Lakukan pengecekan sampai habis 1 box, lalu klik OK Semua Item jika OK.`);
                    } else {
                        $('#scan-status-title').text(`Sampling ${samplingType}`);
                        $('#scan-status-message').text('Detail part berhasil ditampilkan dari hasil scan kanban ' + samplingType + '.');
                        if (samplingType !== 'LAST' || (data.first_check_items || []).length) {
                            showAlert('success', res.message || 'Detail part berhasil ditampilkan.');
                        }
                    }
                    $('#kanban_code').val('').focus();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal membaca data kanban.';
                    renderPrototypeChecksheet([]);
                    $('#scan-status-title').text('Error');
                    $('#scan-status-message').text(msg);
                    showAlert('danger', msg);
                    $('#kanban_code').focus();
                }
            });
        });

        $(function() {
            if (qualityCheckMode === 'FULL') {
                $('#scan-help-text').text('Ambil kanban dari box, scan kanban, cek part sampai habis 1 box, lalu klik OK Semua Item jika OK.');
            }

            $('#kanban_code').focus();
        });
    </script>
@endsection