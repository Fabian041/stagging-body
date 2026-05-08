@extends('layouts.root.auth')

@section('main')
    <style>
        /* ===== PAIRING SCAN - MATCH MASTER PIS STYLE ===== */
        :root {
            --primary: #294795;
            --navy: #294795;
            --sky: #0097D8;
            --blue: #0070B7;
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e5e7eb;
            --text: #0f172a;
            --text-muted: #64748b;
            --shadow: 0 10px 28px rgba(15, 23, 42, .08);
            --shadow-md: 0 18px 45px rgba(15, 23, 42, .14);
            --r: 8px;
            --danger-light: #fee2e2;
            --danger: #dc2626;
        }

        .pairing-page {
            min-height: calc(100vh - 24px);
            padding: 18px;
            background:
                radial-gradient(circle at top left, rgba(0, 151, 216, .10), transparent 28%),
                radial-gradient(circle at bottom right, rgba(41, 71, 149, .10), transparent 26%),
                var(--bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
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

        .scan-action-right {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .act-btn {
            border: 1px solid transparent;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1;
            transition: .15s;
            text-decoration: none !important;
            cursor: pointer;
            height: 34px;
            padding: 0 14px;
            font-size: 12px;
            letter-spacing: .04em;
        }

        .act-btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff !important;
        }

        .act-btn.danger {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff !important;
        }

        .act-btn.secondary {
            background: var(--card);
            border-color: var(--border);
            color: var(--text-muted) !important;
        }

        .act-btn:hover {
            filter: brightness(.97);
            transform: translateY(-1px);
        }

        .pairing-body {
            padding: 18px 20px 20px;
            background: var(--bg);
        }

        .scan-info-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .scan-mini-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 12px 14px;
            min-height: 72px;
        }

        .scan-mini-card label {
            display: block;
            margin-bottom: 6px;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .scan-mini-card span {
            font-size: 14px;
            font-weight: 800;
            color: var(--navy);
        }

        .scan-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .kanban-status-card {
            border: 1px solid var(--border) !important;
            border-radius: 10px !important;
            box-shadow: var(--shadow) !important;
            background: var(--card) !important;
            overflow: hidden;
            min-height: 250px;
            margin: 0 !important;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }

        .kanban-status-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md) !important;
        }

        .kanban-status-card.card-secondary {
            border-color: var(--border) !important;
        }

        .kanban-status-card.card-info {
            border-color: rgba(0, 151, 216, .35) !important;
        }

        .kanban-status-card.card-success {
            border-color: rgba(22, 163, 74, .35) !important;
        }

        .kanban-status-card .hero-inner {
            padding: 16px;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .scan-card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .scan-card-title h5 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .scan-card-title small {
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
        }

        .scan-card-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(41, 71, 149, .08);
            color: var(--primary);
        }

        .scan-display-card {
            flex: 1;
            min-height: 150px;
            width: 100% !important;
            border-radius: 10px !important;
            padding: 22px 14px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, #94a3b8, #64748b) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25), var(--shadow) !important;
        }

        .scan-display-card.bg-secondary {
            background: linear-gradient(135deg, #94a3b8, #64748b) !important;
        }

        .scan-display-card.bg-info {
            background: linear-gradient(135deg, var(--sky), var(--blue)) !important;
        }

        .scan-display-card.bg-success {
            background: linear-gradient(135deg, #22c55e, #15803d) !important;
        }

        .scan-display-card h3 {
            margin: 0;
            color: #fff !important;
            font-size: clamp(2.3rem, 5vw, 4.6rem) !important;
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: .02em;
            word-break: break-word;
            text-shadow: 0 2px 12px rgba(0, 0, 0, .18);
        }

        .hidden-scanner-input {
            position: fixed;
            top: 0;
            left: 0;
            opacity: 0;
            width: 1px;
            height: 1px;
            border: 0;
            pointer-events: none;
        }

        /* ===== MODAL STYLE MATCH VIEW MASTER PIS ===== */
        .modal-content {
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-md) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            overflow: hidden !important;
        }

        .modal-header {
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
            padding: 14px 20px !important;
        }

        .modal-title {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: var(--navy) !important;
        }

        .modal-body {
            padding: 16px 20px !important;
            background: var(--bg) !important;
        }

        .modal-body .form-control {
            height: 34px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--card) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            box-shadow: none !important;
        }

        .modal-body .form-control:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
        }

        #notifModal .modal-content {
            border: none !important;
            color: #fff;
        }

        #notifModal .modal-body {
            background: transparent !important;
            padding: 24px !important;
        }

        #notif {
            color: #fff;
            font-size: 22px !important;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .confirmation-note {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 99px;
            background: #fee2e2;
            color: #dc2626;
            font-size: 11px;
            font-weight: 700;
        }

        @media (max-width: 992px) {

            .scan-grid,
            .scan-info-strip {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .pairing-page {
                padding: 12px;
            }

            .bella-table-card-header,
            .scan-action-right {
                flex-direction: column;
                align-items: stretch;
            }

            .act-btn {
                width: 100%;
            }

            .pairing-body {
                padding: 14px;
            }
        }
    </style>

    <div class="pairing-page">
        <div class="bella-table-card">
            <div class="bella-table-card-header">
                <div>
                    <span class="bella-table-card-title"><i class="fas fa-barcode mr-2"></i>Kanban Pairing Scan</span>
                    <div class="bella-table-card-subtitle">Scan kanban assembly dan painting sesuai master pairing.</div>
                </div>
                <div class="scan-action-right">
                    <button type="button" class="act-btn danger" onclick="resetScanState()">
                        <i class="fas fa-redo-alt"></i> Reset Scan
                    </button>
                    <form id="logout-form" action="{{ route('logout.auth') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" class="act-btn secondary"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="pairing-body">
                <input id="code" type="text" class="form-control hidden-scanner-input" name="code" tabindex="1"
                    placeholder="scan part..." required autofocus autocomplete="off">

                <div class="scan-info-strip">
                    <div class="scan-mini-card">
                        <label>Mode</label>
                        <span>Batch Pairing</span>
                    </div>
                    <div class="scan-mini-card">
                        <label>Total Assembly</label>
                        <span id="total-part">0</span>
                    </div>
                    <div class="scan-mini-card">
                        <label>Total Painting</label>
                        <span id="total-scan">0</span>
                    </div>
                </div>

                <div class="scan-grid">
                    <div class="card card-secondary kanban-status-card model-card-header">
                        <div class="hero-inner">
                            <div class="scan-card-title">
                                <div>
                                    <h5>Kanban Assembly</h5>
                                    <small>Scan kanban assembly terlebih dahulu</small>
                                </div>
                                <span class="scan-card-icon"><i class="fas fa-cubes"></i></span>
                            </div>
                            <div class="bg-secondary m-auto shadow model-card scan-display-card">
                                <h3 id="model_assy">-</h3>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary kanban-status-card total-scan-card-header">
                        <div class="hero-inner">
                            <div class="scan-card-title">
                                <div>
                                    <h5>Kanban Painting</h5>
                                    <small>Progress painting mengikuti rasio master</small>
                                </div>
                                <span class="scan-card-icon"><i class="fas fa-spray-can"></i></span>
                            </div>
                            <div class="bg-secondary m-auto shadow total-scan-card scan-display-card">
                                <h3 id="model_painting">-</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal --}}
    <div class="modal fade gfont" id="notifModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="divNotif">
                <div class="modal-body text-center">
                    <span id="notif">Error!</span>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal --}}

    {{-- confirmation modal --}}
    <div class="modal fade" id="modalConfirmation" aria-hidden="true" aria-labelledby="modalToggleLabel2" tabindex="-1"
        data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h5 class="modal-title"><b>JP or Leader Confirmation</b></h5>
                </div>
                <div class="modal-body text-center">
                    <div class="confirmation-note mb-3">
                        <i class="fas fa-exclamation-triangle"></i> Hubungi JP atau Leader
                    </div>
                    <input type="text" class="form-control text-center" id="input-confirmation"
                        placeholder="scan barcode..." autocomplete="off" autofocus>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal --}}

    <audio id="not-match-sound">
        <source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg">
    </audio>

    <audio id="full-filled">
        <source src={{ asset('assets/sounds/fullfilled.mp3') }} type="audio/mpeg">
    </audio>

    <audio id="already-scan-sound">
        <source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg">
    </audio>

    <audio id="forget-sound">
        <source src={{ asset('assets/sounds/forget.mp3') }} type="audio/mpeg">
    </audio>

    <audio id="match-sound">
        <source src={{ asset('assets/sounds/match.mp3') }} type="audio/mpeg">
    </audio>

    <audio id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg">
    </audio>
    <audio id="error-connection">
        <source src={{ asset('assets/sounds/errConnection.mp3') }} type="audio/mpeg">
    </audio>
    <audio id="dandori-ng-sound">
        <source src={{ asset('assets/sounds/dandori_error.mp3') }} type="audio/mpeg">
    </audio>
    <audio id="master-dandori-ng-sound">
        <source src={{ asset('assets/sounds/master_dandori_error.mp3') }} type="audio/mpeg">
    </audio>
    <audio id="wrong-kanban-sound">
        <source src={{ asset('assets/sounds/wrongKanban.mp3') }} type="audio/mpeg">
    </audio>
@endsection
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    let line = '';
    var timerId;
    var timerActive = false;
    var endTime; // Time when the timer is supposed to end

    let pairing = null; // info dari API
    let scannedPaintings = [];
    let scannedAssembly = null;


    function notMatchSound() {
        var sound = document.getElementById("not-match-sound");
        sound.play();
    }

    function errConnection() {
        var sound = document.getElementById("error-connection");
        sound.play();
    }

    function alreadyScanSound() {
        var sound = document.getElementById("already-scan-sound");
        sound.play();
    }


    function forgetSound() {
        var sound = document.getElementById("forget-sound");
        sound.play();
    }

    function matchSound() {
        var sound = document.getElementById("match-sound");
        sound.play();
    }

    function okSound() {
        var sound = document.getElementById("ok-sound");
        sound.play();
    }

    function dandoriSound() {
        var sound = document.getElementById("dandori-ng-sound");
        sound.play();
    }

    function masterDandoriSound() {
        var sound = document.getElementById("master-dandori-ng-sound");
        sound.play();
    }

    function fullfilled() {
        var sound = document.getElementById("master-dandori-ng-sound");
        sound.play();
    }

    function wrongKanbanSound() {
        var sound = document.getElementById("wrong-kanban-sound");
        sound.play();
    }

    function showModalConfirmation() {
        $('#input-confirmation').text('');
        $('#modalConfirmation').on('shown.bs.modal', function() {
            $('#input-confirmation').focus();
        })
        setTimeout(() => {
            $('#modalConfirmation').modal('show');
        }, 1500);

        $(document).on('click', function() {
            $('#input-confirmation').focus();
        })
    }

    function loopNotMatchSound() {
        if (localStorage.getItem('error') === 'true') {
            wrongKanbanSound(); // Putar suara
            showModalConfirmation();
            setTimeout(loopNotMatchSound, 1000); // Loop setiap 2 detik
        }
    }

    function loopDandoriSound() {
        if (localStorage.getItem('dandori_error') === 'true') {
            dandoriSound(); // Putar suara
            showModalConfirmation();
            setTimeout(loopDandoriSound, 1000); // Loop setiap 2 detik
        }
    }

    function loopMasterDandoriSound() {
        if (localStorage.getItem('master_dandori_error') === 'true') {
            masterDandoriSound(); // Putar suara
            showModalConfirmation();
            setTimeout(loopMasterDandoriSound, 1000); // Loop setiap 2 detik
        }
    }

    function initApp() {
        let model = localStorage.getItem('model');
        let backNumber = localStorage.getItem('back_number');
        let totalScan = localStorage.getItem('scan_counter');
        let totalPart = localStorage.getItem('part_counter');
        let photo = localStorage.getItem('photo');

        updateScanProgress();
        if (model || photo) {
            // display model  running
            $('.model-card-header').removeClass('card-secondary');
            $('.model-card-header').addClass('card-info');

            $('.model-card').removeClass('bg-secondary');
            $('.model-card').addClass('bg-info');

            $('#model').text(backNumber)
            // display PIS
            $('#pis').html(
                `<img src="{{ asset('assets/img/pis/${photo}') }}" alt="PIS" class="rounded" height="600">`);
        }

        if (totalScan || totalPart) {
            // display total scan
            $('.total-scan-card-header').removeClass('card-secondary');
            $('.total-scan-card-header').addClass('card-success');

            $('.total-scan-card').removeClass('bg-secondary');
            $('.total-scan-card').addClass('bg-success');

            // display total part
            $('.total-part-card-header').removeClass('card-secondary');
            $('.total-part-card-header').addClass('card-success');

            $('.total-part-card').removeClass('bg-secondary');
            $('.total-part-card').addClass('bg-success');

            $('#total-scan').text(totalScan)
            $('#total-part').text(totalPart)
        }

        loopNotMatchSound(); // Mulai looping suara
        loopDandoriSound(); // Mulai looping suara
        loopMasterDandoriSound(); // Mulai looping suara

        $('#code').focus();
    }

    function notif(color, text) {
        let modal = $('#notifModal');
        let textNotif = $('#notif');
        textNotif.text(text);
        $('#divNotif').css("background-color", color === "error" ? "#FF2A00" : "#32a852");
        modal.modal('show');
        setTimeout(() => {
            modal.modal('hide');
            $('#code').focus();
        }, 1000);
    }


    $(document).ready(function() {
        initApp();
        if ($('#modalConfirmation').hasClass('show')) {
            $('#input-confirmation').text('');
            $('#input-confirmation').focus();
        }

        if (localStorage.getItem('error') == 'true') {
            showModalConfirmation();

        }

        $(document).on('click', function() {
            $('#code').focus();
        })

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        let scanTimeout;

        $('#code').on('input', function() {
            const val = $(this).val();
            if (val.length == 230) {
                handleScan(val.trim());
                $(this).val('');
            }
        });
    });

    async function handleScan(barcode) {
        const scannedPart = extractPartNumber(barcode);
        const scannedModel = extractModel(barcode);

        if (!scannedPart) {
            notif('error', 'Barcode tidak valid / part number tidak terbaca');
            notMatchSound();
            return;
        }

        const expectedAssy = localStorage.getItem('assy_part_number');
        const expectedPainting = localStorage.getItem('expected_painting');

        // =============== SCAN PERTAMA: ASSY ===============
        if (!expectedAssy) {
            const assyPart = scannedPart;

            try {
                const res = await fetch(`/validation/kanban/pairing?part=${assyPart}`);
                const data = await res.json();

                if (data.success) {
                    const qtyAssy = parseInt(data.qty_assy, 10); // master: berapa assy
                    const qtyPainting = parseInt(data.qty_painting, 10); // master: berapa painting

                    // Simpan master & info pairing
                    localStorage.setItem('assy_part_number', assyPart);
                    localStorage.setItem('expected_painting', data.painting);
                    localStorage.setItem('model_painting', data.model_painting);
                    localStorage.setItem('model_assy', data.model_assy);
                    localStorage.setItem('qty_assy', qtyAssy);
                    localStorage.setItem('qty_painting', qtyPainting);

                    // Batch dimulai: 1 assy pertama sudah discan
                    localStorage.setItem('scan_count_assy', '1');
                    localStorage.setItem('scan_count_painting', '0');

                    $('#total-scan').text(0);
                    updateScanProgress();
                    notif('success', 'Scan assy pertama berhasil (batch mode)');
                    okSound();
                } else {
                    notif('error', 'Tidak ditemukan pasangan painting');
                    localStorage.setItem('error', 'true');
                    loopNotMatchSound(); // mulai looping bunyi + modal konfirmasi
                    wrongKanbanSound();
                }
            } catch (error) {
                notif('error', 'Gagal mengambil data pasangan');
                errConnection();
            }

            return; // keluar dari fungsi karena scan pertama
        }

        // =============== SCAN BERIKUTNYA (BATCH MODE) ===============
        let countAssy = parseInt(localStorage.getItem('scan_count_assy') || '0', 10);
        let countPainting = parseInt(localStorage.getItem('scan_count_painting') || '0', 10);

        const qtyAssyMaster = parseInt(localStorage.getItem('qty_assy') || '1', 10);
        const qtyPaintingMaster = parseInt(localStorage.getItem('qty_painting') || '0', 10);

        // Hitung target painting berdasarkan batch assy saat ini:
        // contoh:
        // - master: 1 assy = 2 painting  → target = countAssy * 2 / 1
        // - master: 2 assy = 1 painting  → target = countAssy * 1 / 2
        let targetPainting = Math.floor(countAssy * qtyPaintingMaster / qtyAssyMaster);

        // ===== SCAN ASSY (TAMBAH BATCH) =====
        if (scannedPart === expectedAssy) {
            countAssy++;
            localStorage.setItem('scan_count_assy', String(countAssy));

            // update target painting
            targetPainting = Math.floor(countAssy * qtyPaintingMaster / qtyAssyMaster);

            updateScanProgress();
            notif('success', `Assy ke-${countAssy} berhasil discan (batch)`);
            okSound();
            return;
        }

        // ===== SCAN PAINTING =====
        if (scannedPart === expectedPainting) {
            if (countAssy === 0) {
                notif('error', 'Scan kanban assy dulu sebelum painting');
                notMatchSound();
                return;
            }

            // hitung ulang target (kalau assy batch sebelumnya sudah nambah)
            targetPainting = Math.floor(countAssy * qtyPaintingMaster / qtyAssyMaster);

            if (targetPainting === 0) {
                notif('error', 'Batch belum terbentuk, scan assy lagi');
                notMatchSound();
                return;
            }

            if (countPainting >= targetPainting) {
                notif('error', 'Jumlah kanban painting sudah cukup untuk batch ini');
                fullfilled();
                return;
            }

            countPainting++;
            localStorage.setItem('scan_count_painting', String(countPainting));
            $('#total-scan').text(countPainting);
            notif('success', `Painting ke-${countPainting} berhasil`);
            okSound();
            updateScanProgress();

            // ✅ Kalau batch sudah lengkap → pairing selesai, reset untuk batch berikutnya
            if (countPainting === targetPainting) {
                notif('success', '✅ Pairing batch selesai!');
                resetScanState();
            }

            return;
        }

        // ===== PART TIDAK SESUAI =====
        notif('error', 'Part tidak sesuai pasangan');
        localStorage.setItem('error', 'true');
        notMatchSound();
        showModalConfirmation();
    }


    // Pakai event input (lebih cocok untuk barcode scanner)
    $(document).on('input', '#input-confirmation', function() {
        var barcodecomplete = $(this).val().trim();

        // ⚠️ Jangan lakukan apa-apa sebelum 6 digit
        if (barcodecomplete.length < 6) {
            return;
        }

        // Kalau scanner ngirim lebih dari 6 char (misal ada ENTER), ambil 6 digit pertama saja
        if (barcodecomplete.length > 6) {
            barcodecomplete = barcodecomplete.substring(0, 6);
            $(this).val(barcodecomplete); // sync ke input
            console.log('Trimmed NPK:', barcodecomplete);
        }

        // ✅ Cek NPK
        if (
            barcodecomplete === '000448' ||
            barcodecomplete === '002484' ||
            barcodecomplete === '000040' ||
            barcodecomplete === '000504'
        ) {
            // Bersihkan status error
            localStorage.removeItem('error');
            localStorage.removeItem('dandori_error');
            localStorage.removeItem('kanban_exist_error');
            localStorage.removeItem('master_dandori_error');

            $('#modalConfirmation').modal('hide');
            notif('success', 'Selamat melanjutkan!!! ulangi proses scan dari awal');
            // resetScanState();

            setTimeout(() => {
                $('#input-confirmation').val('');
            }, 100);

            setTimeout(() => {
                $('#code').focus();
            }, 500);
        } else {
            $('#modalConfirmation').modal('hide');
            notif('error', `NPK ${barcodecomplete} tidak memiliki hak akses`);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    });


    function extractPartNumber(barcode) {
        // Tangkap 3 bagian: depan (6/7 digit), tengah (5 digit), belakang (3 char)
        const regex = /(\d{6,7})-(\d{5})-([A-Z0-9]{3})/;
        const match = barcode.match(regex);

        if (!match) {
            console.log("Part number not found in barcode:", barcode);
            return null;
        }

        let first = match[1]; // 6 atau 7 digit pertama
        const middle = match[2]; // 5 digit tengah
        const last = match[3]; // 3 char terakhir

        // Jika 7 digit dan diawali 0 → buang 0 depan
        if (first.length === 7 && first.startsWith('0')) {
            first = first.slice(1);
        }

        const partNumber = `${first}-${middle}-${last}`;
        console.log("Extracted part number:", partNumber);
        return partNumber;
    }

    function extractModel(barcode) {
        const parts = barcode.trim().replace(/\s+/g, ' ').split(' ');
        return parts.find(p => /^[A-Z]{3,4}\d$/.test(p)) || null;
    }

    function getPairingRatio(qtyAssy, qtyPainting) {
        // Fungsi KPK
        function lcm(a, b) {
            return (a * b) / gcd(a, b);
        }

        function gcd(a, b) {
            return b === 0 ? a : gcd(b, a % b);
        }

        const kpk = lcm(qtyAssy, qtyPainting);
        const ratioAssy = kpk / qtyAssy;
        const ratioPainting = kpk / qtyPainting;

        return {
            assy: ratioAssy,
            painting: ratioPainting
        };
    }

    function resetScanState() {
        localStorage.clear();
        $('#model_assy').text('-');
        $('#model_painting').text('-');
        $('.model-card-header').removeClass('card-success').addClass('card-secondary');
        $('.model-card').removeClass('bg-success').addClass('bg-secondary');
        $('.total-scan-card-header').removeClass('card-success').addClass('card-secondary');
        $('.total-scan-card').removeClass('bg-success').addClass('bg-secondary');
    }

    function updateScanProgress() {
        const countAssy = parseInt(localStorage.getItem('scan_count_assy') || '0', 10);
        const countPainting = parseInt(localStorage.getItem('scan_count_painting') || '0', 10);
        const qtyAssyMaster = parseInt(localStorage.getItem('qty_assy') || '1', 10);
        const qtyPaintingMaster = parseInt(localStorage.getItem('qty_painting') || '0', 10);
        const modelAssy = localStorage.getItem('model_assy') || '-';
        const modelPainting = localStorage.getItem('model_painting') || '-';

        const targetPainting = Math.floor(countAssy * qtyPaintingMaster / qtyAssyMaster);

        // Teks kartu Assy → tunjukkan jumlah assy yang sudah discan
        const progressTextAssy = `${modelAssy} (${countAssy})`;

        // Teks kartu Painting → tunjukkan progress batch
        let progressTextPainting = modelPainting;
        if (targetPainting > 0) {
            progressTextPainting += ` (${countPainting}/${targetPainting})`;
        } else {
            progressTextPainting += ` (waiting assy...)`;
        }

        $('#model_assy').text(progressTextAssy);
        $('#model_painting').text(progressTextPainting);

        // Warna indikasi kartu
        if (countAssy > 0) {
            $('.model-card-header').removeClass('card-secondary').addClass('card-info');
            $('.model-card').removeClass('bg-secondary').addClass('bg-info');
        } else {
            $('.model-card-header').removeClass('card-info card-success').addClass('card-secondary');
            $('.model-card').removeClass('bg-info bg-success').addClass('bg-secondary');
        }

        if (targetPainting > 0 && countPainting === targetPainting) {
            $('.total-scan-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-scan-card').removeClass('bg-secondary').addClass('bg-success');
        } else if (targetPainting > 0 && countPainting > 0) {
            $('.total-scan-card-header').removeClass('card-secondary').addClass('card-info');
            $('.total-scan-card').removeClass('bg-secondary').addClass('bg-info');
        } else {
            $('.total-scan-card-header').removeClass('card-info card-success').addClass('card-secondary');
            $('.total-scan-card').removeClass('bg-info bg-success').addClass('bg-secondary');
        }
    }
</script>
