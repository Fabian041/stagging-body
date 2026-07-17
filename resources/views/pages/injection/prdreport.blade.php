@extends('layouts.root.auth')

@section('main')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .modern-shell {
        background: var(--bg, #F0F4F9);
        min-height: 100vh;
        padding: 1.25rem;
    }

    .modern-card {
        border: 1px solid var(--border, #DDE3EF);
        border-radius: 14px;
        background: var(--card, #FFFFFF);
        box-shadow: var(--shadow-md, 0 4px 20px rgba(41, 71, 149, .12));
    }

    .scan-input {
        border-radius: 10px;
        border: 1px solid var(--border, #DDE3EF);
        background: #ffffff;
        color: var(--text, #1A2340);
        box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        font-size: 1rem;
        padding: 0.9rem 1rem;
    }

    .scan-input::placeholder {
        color: var(--text-muted, #6B7A99);
    }

    .scan-input:focus {
        border-color: var(--primary, #0070B7);
        box-shadow: 0 0 0 0.2rem rgba(0, 112, 183, 0.12);
        outline: none;
    }

    .metric-card {
        border-radius: 10px;
        background: linear-gradient(135deg, var(--navy, #294795), var(--blue, #0070B7));
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 4.5rem;
        box-shadow: 0 8px 20px rgba(0, 112, 183, 0.18);
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .action-btn {
        width: 100%;
        border-radius: 10px;
        font-weight: 800;
        padding: 1rem;
        min-height: 3.6rem;
        border: 0;
        box-shadow: 0 8px 20px rgba(0, 112, 183, 0.18);
        text-transform: none;
        letter-spacing: 0.01em;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.03);
    }

    .status-card {
        min-height: 5rem;
        border-radius: 10px;
    }

    .content-panel {
        border-radius: 14px;
        min-height: 100%;
        background: var(--card, #FFFFFF);
        color: var(--text, #1A2340);
        box-shadow: var(--shadow-md, 0 4px 20px rgba(41, 71, 149, .12));
    }

    .text-dark,
    .modal-title,
    .modal-body h5,
    .modal-body p,
    #pis h2 {
        color: var(--text, #1A2340) !important;
        text-shadow: none;
    }

    .table {
        color: var(--text, #1A2340);
    }

    .table thead th {
        background: #f8fbff;
        color: var(--text-muted, #6B7A99);
        border-color: var(--border, #DDE3EF);
    }

    .table tbody td {
        background: #ffffff;
        border-color: var(--border, #DDE3EF);
    }
</style>

<div class="modern-shell">
    <div class="mx-3 mx-lg-5 my-4">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-2 col-sm-12">
                <div class="modern-card p-3 mb-3">
                    <label class="fw-bold text-center text-dark d-block mb-2">Scan Tag Mold / Kanban</label>
                    <input id="code" type="text" class="form-control scan-input" name="code" tabindex="1"
                        placeholder="scan tag mold / kanban..." required autofocus autocomplete="off">
                </div>

                <div class="modern-card p-3 mb-3 line-card-header">
                    <h5 class="text-center text-dark mb-2">Line</h5>
                    <div class="metric-card line-card" id="display-line-name">-</div>
                </div>

                <div class="modern-card p-3 mb-3 mold-card-header">
                    <h5 class="text-center text-dark mb-2">Mold Running</h5>
                    <div class="metric-card mold-card" id="mold-running">-</div>
                </div>

                <div class="modern-card p-3 mb-3 model-card-header">
                    <h5 class="text-center text-dark mb-2">Last Part</h5>
                    <div class="metric-card model-card" id="model">-</div>
                </div>

                <div class="modern-card p-3 mb-3 total-scan-card-header">
                    <h5 class="text-center text-dark mb-2">Total Scan</h5>
                    <div class="metric-card total-scan-card" id="total-scan">0</div>
                </div>

                <div class="modern-card p-3 mb-3 time-card-header">
                    <h5 class="text-center text-dark mb-2" id="texttime">Time / Box</h5>
                    <div class="metric-card time-card" id="time-per-box">00:00</div>
                </div>
            </div>

            <div class="col-lg-8 col-sm-12">
                <div class="modern-card content-panel p-4" id="pis">
                    <h2 class="text-center text-dark">Ready to scan tag mold !!</h2>
                </div>
            </div>

            <div class="col-lg-2 col-sm-12">
                <button id="fullscreenBtn" class="btn btn-info mb-2 action-btn">Full Screen</button>

                <button class="btn btn-warning action-btn mb-2" id="release">Stop</button>
                <button class="btn btn-danger action-btn mb-2" id="pause">Problem</button>
                <button class="btn btn-success action-btn mb-2" id="pauseSetup">Setup</button>
                <button class="btn btn-success action-btn mb-4" id="pauseQualityCheck">QC Cek</button>

                <div class="modern-card p-3 mb-3 status-card-header">
                    <h5 class="text-center text-dark mb-2">Status</h5>
                    <div class="metric-card status-card" id="status">-</div>
                </div>

                <div class="modern-card p-3 total-part-card-header">
                    <h5 class="text-center text-dark mb-2">Total Part</h5>
                    <div class="metric-card total-part-card" id="total-part">0</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- modal notif --}}
<div class="modal fade gfont" id="notifModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="divNotif" style="border-radius: 12px !important;">
            <div class="modal-body text-center">
                <span style="color: white; font-size: 30pt" id="notif">Error!</span>
            </div>
        </div>
    </div>
</div>

{{-- confirmation modal --}}
<div class="modal fade" id="modalConfirmation" aria-hidden="true" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-body">
                <h5 class="text-center"><b>JP or Leader Confirmation</b></h5>
                <p class="text-center" style="color: red">*hubungi JP atau Leader</p><br>
                <input type="text" class="form-control" id="input-confirmation" placeholder="scan barcode..."
                    autocomplete="off" autofocus>
                <br>
            </div>
        </div>
    </div>
</div>

{{-- problem modal --}}
<div class="modal fade" id="pauseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">Pilih Jenis Masalah</h5>
            </div>
            <div class="modal-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <button id="pauseMachine" class="btn btn-danger btn-lg w-100 py-4 btn-stop-category"
                            style="font-size: 1.5rem;" data-category="mesin">Mesin</button>
                    </div>
                    <div class="col-md-3">
                        <button id="pauseQuality" class="btn btn-warning btn-lg w-100 py-4 btn-stop-category"
                            style="font-size: 1.5rem;" data-category="quality">Quality</button>
                    </div>
                    <div class="col-md-3">
                        <button id="pauseSupply" class="btn btn-success btn-lg w-100 py-4 btn-stop-category"
                            style="font-size: 1.5rem;" data-category="supply">Supply</button>
                    </div>
                    <div class="col-md-3">
                        <button id="openOtherProblem" class="btn btn-secondary btn-lg w-100 py-4 btn-stop-category"
                            style="font-size: 1.5rem;" data-category="others">Others</button>
                    </div>
                </div>

                <div class="mt-4" id="otherProblemSection" style="display:none;">
                    <select id="stopReason" class="form-control mb-3">
                        <option value="">Loading...</option>
                    </select>
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-secondary btn-lg w-100" data-dismiss="modal">Cancel</button>
                        </div>
                        <div class="col-md-6">
                            <button id="pauseOther" class="btn btn-primary btn-lg w-100">Submit Problem</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- line modal --}}
<div class="modal fade" id="lineModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">Input Nama Line</h5>
            </div>
            <div class="modal-body">
                <input type="text" id="input-line-name" class="form-control text-center"
                    placeholder="Contoh: INJ001 / INJ002" autocomplete="off">
                <br>
                <button id="save-line-name" class="btn btn-primary w-100">Simpan Line</button>
            </div>
        </div>
    </div>
</div>

<audio id="not-match-sound"><source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg"></audio>
<audio id="already-scan-sound"><source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg"></audio>
<audio id="forget-sound"><source src={{ asset('assets/sounds/forget.mp3') }} type="audio/mpeg"></audio>
<audio id="match-sound"><source src={{ asset('assets/sounds/match.mp3') }} type="audio/mpeg"></audio>
<audio id="ok-sound"><source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg"></audio>
<audio id="error-connection"><source src={{ asset('assets/sounds/errConnection.mp3') }} type="audio/mpeg"></audio>
<audio id="dandori-ng-sound"><source src={{ asset('assets/sounds/dandori_error.mp3') }} type="audio/mpeg"></audio>
<audio id="master-dandori-ng-sound"><source src={{ asset('assets/sounds/master_dandori_error.mp3') }} type="audio/mpeg"></audio>
<audio id="wrong-kanban-sound"><source src={{ asset('assets/sounds/wrongKanban.mp3') }} type="audio/mpeg"></audio>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/injection-prdreport-helpers.js') }}"></script>
<script>
    let barcode = '';
    let timerId;
    let timerActive = false;
    let endTime;
    let timePerBoxInterval;
    let isProcessingScan = false;

    const { STORAGE_KEYS, ROUTES, csrfToken, playAudio, normalizePartNumber, getLineName, getMoldParts, getCycleScannedParts, getCycleNumber, isPartAlreadyScannedInCurrentCycle, markPartScannedInCurrentCycle, getUniqueMoldPartNumbers, getRemainingPartsInCurrentCycle, isCurrentCycleComplete, findMoldPart, isPartMemberOfMold, resetCurrentCycle } = window.InjectionScanHelpers;

    function notMatchSound() { playAudio('not-match-sound'); }
    function errConnection() { playAudio('error-connection'); }
    function alreadyScanSound() { playAudio('already-scan-sound'); }
    function forgetSound() { playAudio('forget-sound'); }
    function matchSound() { playAudio('match-sound'); }
    function okSound() { playAudio('ok-sound'); }
    function dandoriSound() { playAudio('dandori-ng-sound'); }
    function masterDandoriSound() { playAudio('master-dandori-ng-sound'); }
    function wrongKanbanSound() { playAudio('wrong-kanban-sound'); }

    function notif(color, text) {
        $('#notif').text(text);
        $('#divNotif').css('background-color', color === 'error' ? '#FF2A00' : '#32a852');
        $('#notifModal').modal('show');
        setTimeout(() => {
            $('#notifModal').modal('hide');
            focusScanInput();
        }, 2500);
    }

    function setStatus(type, text = null, autoReset = true) {
        $('.status-card-header').removeClass('card-secondary card-success card-danger');
        $('.status-card').removeClass('bg-secondary bg-success bg-danger');

        if (type === 'success') {
            $('.status-card-header').addClass('card-success');
            $('.status-card').addClass('bg-success');
            $('#status').text(text || 'OK');
        } else if (type === 'danger') {
            $('.status-card-header').addClass('card-danger');
            $('.status-card').addClass('bg-danger');
            $('#status').text(text || 'NG');
        } else {
            $('.status-card-header').addClass('card-secondary');
            $('.status-card').addClass('bg-secondary');
            $('#status').text(text || '-');
        }

        if (autoReset && type !== 'idle') {
            setTimeout(() => setStatus('idle'), type === 'success' ? 2000 : 3000);
        }
    }

    function focusScanInput() {
        setTimeout(() => $('#code').focus(), 50);
    }

    function setScanBusy(isBusy) {
        isProcessingScan = isBusy;
        $('#code').prop('disabled', isBusy);
        $('#code').attr('placeholder', isBusy ? 'Processing scan...' : 'scan tag mold / kanban...');

        if (isBusy) {
            $('#code').addClass('is-loading');
        } else {
            $('#code').removeClass('is-loading');
        }
    }

    function checkLineName() {
        const lineName = getLineName();
        if (!lineName) {
            $('#lineModal').modal('show');
            setTimeout(() => $('#input-line-name').focus(), 500);
        }
    }

    function updateDisplayLineName() {
        const lineName = getLineName();
        $('#display-line-name').text(lineName || '-');

        $('.line-card-header').removeClass('card-secondary').addClass(lineName ? 'card-info' : 'card-secondary');
        $('.line-card').removeClass('bg-secondary').addClass(lineName ? 'bg-info' : 'bg-secondary');
    }


    function startTimeCounter(startTimestamp = null) {
        clearInterval(timePerBoxInterval);

        const startTime = startTimestamp ? new Date(Number(startTimestamp)) : new Date();

        timePerBoxInterval = setInterval(() => {
            const now = new Date();
            const diffMs = now - startTime;
            const totalSeconds = Math.floor(diffMs / 1000);
            const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
            const seconds = (totalSeconds % 60).toString().padStart(2, '0');

            $('#time-per-box').text(`${minutes}:${seconds}`);
        }, 1000);
    }

    function renderMoldRunning() {
        const moldName = localStorage.getItem(STORAGE_KEYS.moldName);
        const moldCode = localStorage.getItem(STORAGE_KEYS.moldCode);
        const display = moldName || moldCode || '-';

        if (moldCode) {
            $('.mold-card-header').removeClass('card-secondary').addClass('card-info');
            $('.mold-card').removeClass('bg-secondary').addClass('bg-info');
        }

        $('#mold-running').text(display);
    }

    function renderLastPart() {
        const lastBackNumber = localStorage.getItem(STORAGE_KEYS.lastBackNumber);
        const lastPartNumber = localStorage.getItem(STORAGE_KEYS.lastPartNumber);
        const photo = localStorage.getItem(STORAGE_KEYS.lastPhoto);

        if (lastBackNumber || lastPartNumber) {
            $('.model-card-header').removeClass('card-secondary').addClass('card-info');
            $('.model-card').removeClass('bg-secondary').addClass('bg-info');
            $('#model').text(lastBackNumber || lastPartNumber);
        }

        if (photo) {
            $('#pis').html(`<img src="{{ asset('assets/img/pis/${photo}') }}" alt="PIS" class="rounded" height="600">`);
        }
    }

    function renderCounters() {
        const scanCounter = localStorage.getItem(STORAGE_KEYS.scanCounter) || 0;
        const partCounter = localStorage.getItem(STORAGE_KEYS.partCounter) || 0;

        if (Number(scanCounter) > 0 || Number(partCounter) > 0) {
            $('.total-scan-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-scan-card').removeClass('bg-secondary').addClass('bg-success');

            $('.total-part-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-part-card').removeClass('bg-secondary').addClass('bg-success');
        }

        $('#total-scan').text(scanCounter);
        $('#total-part').text(partCounter);
    }

    function renderMoldPartsList() {
        const parts = getMoldParts();
        if (!parts.length) {
            $('#pis').html('<h2 class="text-center text-dark">Ready to scan tag mold !!</h2>');
            return;
        }

        const rows = parts.map((item, index) => {
            const partNumber = typeof item === 'string' ? item : (item.part_number || item.internal_part_number || item.internal || '-');
            const backNumber = typeof item === 'string' ? '-' : (item.back_number || item.backNumber || '-');
            const partName = typeof item === 'string' ? '-' : (item.part_name || item.name || '-');

            return `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><b>${partNumber}</b></td>
                    <td>${backNumber}</td>
                    <td>${partName}</td>
                </tr>
            `;
        }).join('');

        $('#pis').html(`
            <div class="px-4">
                <h2 class="text-center text-dark mb-4">Part Anggota Mold</h2>
                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table table-bordered table-striped bg-white">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width: 70px">No</th>
                                <th>Part Number</th>
                                <th>Back Number</th>
                                <th>Part Name</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        `);
    }

    function initApp() {
        updateDisplayLineName();
        renderMoldRunning();
        renderLastPart();
        renderCounters();

        const lastScanTime = localStorage.getItem(STORAGE_KEYS.lastKanbanTime);
        if (lastScanTime) {
            startTimeCounter(parseInt(lastScanTime));
        }

        if (localStorage.getItem(STORAGE_KEYS.moldCode)) {
            renderMoldPartsList();
        }

        loopNotMatchSound();
        loopMoldSound();
        focusScanInput();
    }

    function showModalConfirmation() {
        $('#modalConfirmation').on('shown.bs.modal', function() {
            $('#input-confirmation').focus();
        });
        $('#modalConfirmation').modal('show');

        $(document).on('click', function() {
            $('#input-confirmation').focus();
        });
    }

    function loopNotMatchSound() {
        if (localStorage.getItem(STORAGE_KEYS.error) === 'true') {
            wrongKanbanSound();
            showModalConfirmation();
            setTimeout(loopNotMatchSound, 2000);
        }
    }

    function loopMoldSound() {
        if (localStorage.getItem(STORAGE_KEYS.moldError) === 'true') {
            masterDandoriSound();
            showModalConfirmation();
            setTimeout(loopMoldSound, 2000);
        }
    }

    function sendErrorLog(message = null, expected = null, scanned = null) {
        $.ajax({
            url: ROUTES.errorStore,
            method: 'POST',
            data: { message, expected, scanned },
            headers: { 'X-CSRF-TOKEN': csrfToken() }
        });
    }

    function parseKanban(barcodecomplete) {
        let internal = null;
        let seri = null;
        let backNum = null;
        let pcs = null;

        if (barcodecomplete.length == 230) {
            internal = barcodecomplete.substr(41, 19);
            seri = barcodecomplete.substr(123, 4);
            backNum = barcodecomplete.substr(100, 4);
            pcs = barcodecomplete.substr(196, 1);
        } else if (barcodecomplete.length == 220) {
            internal = barcodecomplete.substr(35, 12);
            seri = barcodecomplete.substr(130, 4);
            backNum = barcodecomplete.substr(100, 4);
            pcs = barcodecomplete.substr(196, 1);
        } else if (barcodecomplete.length == 241) {
            internal = barcodecomplete.substr(35, 12);
            seri = barcodecomplete.substr(127, 4);
            backNum = barcodecomplete.substr(100, 4);
            pcs = barcodecomplete.substr(196, 1);
        } else if (barcodecomplete.length == 218) {
            internal = barcodecomplete.substr(41, 16);
            seri = barcodecomplete.substr(123, 4);
            backNum = barcodecomplete.substr(100, 4);
            pcs = barcodecomplete.substr(196, 1);
        }

        return {
            internal: internal ? internal.trim() : null,
            seri: seri || 'noseri',
            backNum,
            pcs
        };
    }

    async function getInternalFromPartNumber(partNumber) {
        try {
            const data = await $.ajax({
                url: `${ROUTES.getInternal}/${partNumber}`,
                method: 'GET',
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': csrfToken() }
            });

            return {
                internal: data?.data?.internal_part?.part_number || null,
                seri: 'noseri',
                backNum: data?.data?.internal_part?.back_number || null,
                pcs: data?.data?.qty_per_kanban || 0
            };
        } catch (err) {
            notif('error', 'Gagal memeriksa kode part.');
            return null;
        }
    }

    async function parseFlexibleKanban(barcodecomplete) {
        let parsed = parseKanban(barcodecomplete);

        if (parsed.internal) {
            return parsed;
        }

        if (barcodecomplete.length >= 100 && barcodecomplete.length <= 180) {
            const parts = barcodecomplete.trim().split(';');
            const partNumber = parts[4]?.split('-').slice(0, 2).join('-') || null;
            if (!partNumber) return parsed;
            return await getInternalFromPartNumber(partNumber);
        }

        if (barcodecomplete.length == 80) {
            const parts = barcodecomplete.trim().split('|');
            const partNumber = parts[3] || null;
            if (!partNumber) return parsed;
            return await getInternalFromPartNumber(partNumber);
        }

        const plainPartMatch = barcodecomplete.trim().match(/\d{5,6}-\d{4,5}/);
        if (plainPartMatch) {
            return await getInternalFromPartNumber(plainPartMatch[0]);
        }

        return parsed;
    }

    function isMoldTag(barcodecomplete) {
        return barcodecomplete.endsWith('-mold') || barcodecomplete.endsWith('-MOLD');
    }

    function extractMoldCode(barcodecomplete) {
        return barcodecomplete.replace(/-mold$/i, '').trim();
    }

    async function handleMoldScan(barcodecomplete) {
        const moldCode = extractMoldCode(barcodecomplete);
        const now = Date.now();

        if (!getLineName()) {
            notif('error', 'Input nama line terlebih dahulu!');
            checkLineName();
            return;
        }

        try {
            const response = await $.ajax({
                type: 'GET',
                url: `${ROUTES.checkMold}/${moldCode}`,
                data: { line: getLineName() },
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': csrfToken() }
            });

            if (response.status !== 'success') {
                masterDandoriSound();
                notif('error', response.message || 'Tag mold tidak valid.');
                setStatus('danger');
                localStorage.setItem(STORAGE_KEYS.moldError, 'true');
                sendErrorLog('Tag mold tidak valid', 'MOLD MASTER', moldCode);
                setTimeout(() => window.location.reload(), 1800);
                return;
            }

            const mold = response.data || response.mold || response;
            const parts = mold.parts || response.parts || [];

            if (!parts.length) {
                masterDandoriSound();
                notif('error', 'Mold tidak memiliki part anggota.');
                setStatus('danger');
                sendErrorLog('Mold tidak memiliki part anggota', moldCode, '-');
                return;
            }

            localStorage.setItem(STORAGE_KEYS.moldId, mold.id || mold.mold_id || moldCode);
            localStorage.setItem(STORAGE_KEYS.moldCode, mold.code || mold.mold_code || moldCode);
            localStorage.setItem(STORAGE_KEYS.moldName, mold.name || mold.mold_name || moldCode);
            localStorage.setItem(STORAGE_KEYS.moldParts, JSON.stringify(parts));
            localStorage.setItem(STORAGE_KEYS.cycleNumber, 1);
            localStorage.setItem(
                STORAGE_KEYS.cycleScannedParts,
                JSON.stringify([])
            );
            localStorage.setItem(STORAGE_KEYS.productionStartTime, new Date().toLocaleString('sv-SE'));
            localStorage.setItem(STORAGE_KEYS.scanCounter, 0);
            localStorage.setItem(STORAGE_KEYS.partCounter, 0);
            localStorage.setItem('scan_timer_start', now);
            localStorage.removeItem(STORAGE_KEYS.lastKanbanTime);
            localStorage.removeItem(STORAGE_KEYS.lastPartNumber);
            localStorage.removeItem(STORAGE_KEYS.lastBackNumber);
            localStorage.removeItem(STORAGE_KEYS.lastPhoto);
            localStorage.removeItem(STORAGE_KEYS.error);
            localStorage.removeItem(STORAGE_KEYS.moldError);

            renderMoldRunning();
            renderCounters();
            renderMoldPartsList();
            startTimeCounter(now);

            okSound();
            notif('success', 'Tag mold OK. Lanjut scan part anggota mold.');
            setStatus('success');
        } catch (xhr) {
            if (xhr.status == 0) {
                notif('error', 'Connection Error');
                errConnection();
                return;
            }

            masterDandoriSound();
            notif('error', xhr.responseJSON?.message || xhr.responseJSON?.errors || 'Gagal cek tag mold.');
            setStatus('danger');
        }
    }

    async function handlePartScan(barcodecomplete) {
        if (!localStorage.getItem(STORAGE_KEYS.moldCode)) {
            masterDandoriSound();
            notif('error', 'Scan tag mold terlebih dahulu!');
            setStatus('danger');
            localStorage.setItem(STORAGE_KEYS.moldError, 'true');
            sendErrorLog('Belum scan tag mold', 'TAG MOLD', barcodecomplete);
            setTimeout(() => window.location.reload(), 1800);
            return;
        }

        const parsed = await parseFlexibleKanban(barcodecomplete);
        if (!parsed || !parsed.internal) {
            wrongKanbanSound();
            notif('error', 'Format barcode part / kanban tidak valid.');
            setStatus('danger');
            sendErrorLog('Format barcode tidak valid', localStorage.getItem(STORAGE_KEYS.moldCode), barcodecomplete);
            return;
        }

        const internal = normalizePartNumber(parsed.internal);

        if (!isPartMemberOfMold(internal)) {
            wrongKanbanSound();
            notif('error', 'Part bukan anggota mold!');
            setStatus('danger');
            localStorage.setItem(STORAGE_KEYS.error, 'true');
            sendErrorLog('Part bukan anggota mold', localStorage.getItem(STORAGE_KEYS.moldCode), internal);
            setTimeout(() => window.location.reload(), 1800);
            return;
        }

        if (isPartAlreadyScannedInCurrentCycle(internal)) {
            const cycleNumber = getCycleNumber();
            const remainingParts = getRemainingPartsInCurrentCycle();

            wrongKanbanSound();

            notif(
                'error',
                `Part ${internal} sudah discan pada cycle ${cycleNumber}. ` +
                `Selesaikan part lain: ${remainingParts.join(', ')}`
            );

            setStatus('danger');

            sendErrorLog(
                `Part sudah discan pada cycle ${cycleNumber}`,
                remainingParts.join(', '),
                internal
            );

            return;
        }

        const matchedPart = findMoldPart(internal);
        const now = Date.now();
        const startTime = localStorage.getItem(STORAGE_KEYS.productionStartTime);

        try {
            const data = await $.ajax({
                method: 'POST',
                url: ROUTES.storeScan,
                data: {
                    line: getLineName(),
                    mold_id: localStorage.getItem(STORAGE_KEYS.moldId),
                    mold_code: localStorage.getItem(STORAGE_KEYS.moldCode),
                    partNumber: internal,
                    seri: parsed.seri || 'noseri',
                    start_time: startTime,
                    end_time: new Date().toLocaleString('sv-SE')
                },
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': csrfToken() }
            });

            if (data.status !== 'success') {
                alreadyScanSound();
                notif('error', data.message || 'Gagal menyimpan scan.');
                setStatus('danger');
                localStorage.setItem(STORAGE_KEYS.error, 'true');
                setTimeout(() => window.location.reload(), 1500);
                return;
            }

            okSound();

            const currentCycleNumber = getCycleNumber();
            const scannedParts = markPartScannedInCurrentCycle(internal);
            const remainingParts = getRemainingPartsInCurrentCycle();

            let scanCounter = parseInt(localStorage.getItem(STORAGE_KEYS.scanCounter) || 0);
            let partCounter = parseInt(localStorage.getItem(STORAGE_KEYS.partCounter) || 0);
            const qty = parseInt(data.qty || matchedPart?.qty_per_kanban || matchedPart?.qty || parsed.pcs || 1);

            scanCounter++;
            partCounter += qty;

            localStorage.setItem(STORAGE_KEYS.scanCounter, scanCounter);
            localStorage.setItem(STORAGE_KEYS.partCounter, partCounter);
            localStorage.setItem(STORAGE_KEYS.lastKanbanTime, now);
            localStorage.setItem(STORAGE_KEYS.lastPartNumber, internal);
            localStorage.setItem(STORAGE_KEYS.lastBackNumber, data.back_number || matchedPart?.back_number || matchedPart?.backNumber || internal);

            if (data.photo || matchedPart?.photo) {
                localStorage.setItem(STORAGE_KEYS.lastPhoto, data.photo || matchedPart.photo);
            }

            $('.model-card-header').removeClass('card-secondary').addClass('card-info');
            $('.model-card').removeClass('bg-secondary').addClass('bg-info');
            $('#model').text(localStorage.getItem(STORAGE_KEYS.lastBackNumber));

            renderCounters();
            setStatus('success');
            startTimeCounter(now);

            if (isCurrentCycleComplete()) {
                notif(
                    'success',
                    `Cycle ${currentCycleNumber} lengkap. Lanjut cycle ${currentCycleNumber + 1}.`
                );

                resetCurrentCycle(true);
            } else {
                notif(
                    'success',
                    `Part ${internal} OK. Sisa cycle ${currentCycleNumber}: ${remainingParts.join(', ')}`
                );
            }
        } catch (xhr) {
            if (xhr.status == 0) {
                notif('error', 'Connection Error');
                errConnection();
                return;
            }

            notif('error', xhr.responseJSON?.message || xhr.responseJSON?.errors || 'Internal Server Error');
            setStatus('danger');
        }
    }

    function resetLocalInjectionState() {
        const keepLine = localStorage.getItem(STORAGE_KEYS.line);
        localStorage.clear();
        if (keepLine) localStorage.setItem(STORAGE_KEYS.line, keepLine);
    }

    function handleSpecialCommand(barcodecomplete) {
        if (barcodecomplete === 'injection') {
            window.location.replace(ROUTES.index);
            return true;
        }

        if (barcodecomplete === 'prdreport') {
            window.location.replace('/production');
            return true;
        }

        if (barcodecomplete === 'logout' || barcodecomplete.length === 13) {
            window.location.replace(ROUTES.logout);
            return true;
        }

        return false;
    }

    $(document).ready(function() {
        initApp();
        checkLineName();
        updateDisplayLineName();

        let pauseStartTime = null;
        let selectedSrnaId = null;
        let pauseTimerInterval = null;
        let selectedCategory = null;

        $('#save-line-name').on('click', function() {
            const lineName = $('#input-line-name').val().trim();
            if (!lineName) {
                notif('error', 'Nama line wajib diisi!');
                return;
            }

            localStorage.setItem(STORAGE_KEYS.line, lineName);
            updateDisplayLineName();
            $('#lineModal').modal('hide');
            focusScanInput();
            notif('success', 'Line berhasil disimpan: ' + lineName);
        });

        $('#input-line-name').on('keypress', function(e) {
            if (e.which === 13) $('#save-line-name').click();
        });

        $('.btn-stop-category').on('click', function() {
            selectedCategory = $(this).data('category');
            pauseStartTime = new Date();

            $('#otherProblemSection').slideDown();
            fetchStopReasons(selectedCategory);
        });

        function startPauseTimer() {
            clearInterval(pauseTimerInterval);
            clearInterval(timePerBoxInterval);

            const startTime = pauseStartTime || new Date();
            pauseTimerInterval = setInterval(() => {
                const now = new Date();
                const diffMs = now - startTime;
                const totalSeconds = Math.floor(diffMs / 1000);
                const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
                const seconds = (totalSeconds % 60).toString().padStart(2, '0');
                $('#time-per-box').text(`${minutes}:${seconds}`);
            }, 1000);
        }

        function stopAllTimers() {
            clearInterval(pauseTimerInterval);
            clearInterval(timePerBoxInterval);
        }

        $('#pause').on('click', function() {
            if ($('#pause').text().trim() === 'Mulai') {
                const now = new Date();
                const pad = n => n.toString().padStart(2, '0');
                const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const fmtFull = d => `${fmt(d)} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

                const payload = {
                    data: [{
                        line_id: getLineName() || 'UNKNOWN',
                        prd_dt: fmt(now),
                        str_dt: fmtFull(pauseStartTime),
                        end_dt: fmtFull(now),
                        mold_id: localStorage.getItem(STORAGE_KEYS.moldId) || 'UNKNOWN',
                        mold_code: localStorage.getItem(STORAGE_KEYS.moldCode) || 'UNKNOWN',
                        matnr: localStorage.getItem(STORAGE_KEYS.lastPartNumber) || 'UNKNOWN',
                        srna_id: selectedSrnaId,
                        crtby: "{{ auth()->user()->npk }}"
                    }]
                };

                $.ajax({
                    url: ROUTES.insertStop,
                    method: 'POST',
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': csrfToken() },
                    data: JSON.stringify(payload),
                    success: function() {
                        notif('success', 'Stop reason berhasil dikirim');
                        $('#pause').text('Problem').removeClass('btn-success').addClass('btn-danger');
                        $('#texttime').text('Time / Box');
                        setStatus('idle');
                        pauseStartTime = null;
                        selectedSrnaId = null;
                        stopAllTimers();
                        focusScanInput();
                        startTimeCounter(Date.now());
                    },
                    error: function() {
                        notif('error', 'Gagal kirim stop reason');
                    }
                });
            } else {
                stopAllTimers();
                $('#otherProblemSection').hide();
                $('#pauseModal').modal('show');
            }
        });

        $('#pauseSetup').on('click', function() {
            pauseStartTime = new Date();
            selectedSrnaId = 'SETUP';
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');
            setStatus('danger', 'Stop', false);
            startPauseTimer();
        });

        $('#pauseQualityCheck').on('click', function() {
            pauseStartTime = new Date();
            selectedSrnaId = 'QCCEK';
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');
            setStatus('danger', 'Stop', false);
            startPauseTimer();
        });

        $('#openOtherProblem').on('click', function(e) {
            e.stopPropagation();
            $('#otherProblemSection').slideDown();
        });

        $('#stopReason').on('click', function(e) {
            e.stopPropagation();
        });

        $('#pauseOther').on('click', function() {
            const selected = $('#stopReason').val();
            if (!selected) {
                alert('Pilih alasan terlebih dahulu!');
                return;
            }

            selectedSrnaId = selected;
            pauseStartTime = new Date();
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');
            setStatus('danger', 'Stop', false);
            startPauseTimer();
        });

        function fetchStopReasons(category) {
            const line = getLineName() || 'DEFAULT';
            $.ajax({
                url: `${ROUTES.listStop}/${line}/${category}`,
                method: 'GET',
                success: function(response) {
                    if (response.status) {
                        let options = '<option value="">-- Pilih Masalah --</option>';
                        response.data.forEach(function(item) {
                            options += `<option value="${item.srna_id}">${item.name1} (${item.type2_text})</option>`;
                        });
                        $('#stopReason').html(options);
                    } else {
                        $('#stopReason').html('<option value="">Tidak ada data</option>');
                    }
                },
                error: function() {
                    $('#stopReason').html('<option value="">Gagal Load</option>');
                }
            });
        }

        $('#fullscreenBtn').on('click', function() {
            if (!document.fullscreenElement) {
                const docEl = document.documentElement;
                if (docEl.requestFullscreen) docEl.requestFullscreen();
                else if (docEl.mozRequestFullScreen) docEl.mozRequestFullScreen();
                else if (docEl.webkitRequestFullscreen) docEl.webkitRequestFullscreen();
                else if (docEl.msRequestFullscreen) docEl.msRequestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                else if (document.msExitFullscreen) document.msExitFullscreen();
            }
        });

        $(document).on('click', function() {
            if (!$('#lineModal').hasClass('show') && !$('#modalConfirmation').hasClass('show')) {
                focusScanInput();
            }
        });

        $('#input-confirmation').keypress(function(e) {
            e.preventDefault();
            const keyCode = e.keyCode ? e.keyCode : e.which;

            if (keyCode == 13) {
                const barcodecomplete = barcode;
                barcode = '';

                if (handleSpecialCommand(barcodecomplete)) return;

                if (barcodecomplete.length === 6) {
                    if (barcodecomplete == '000448' || barcodecomplete == '002484' || barcodecomplete == '000040' || barcodecomplete == '000504') {
                        localStorage.removeItem(STORAGE_KEYS.error);
                        localStorage.removeItem(STORAGE_KEYS.moldError);
                        $('#modalConfirmation').modal('hide');
                        notif('success', 'Selamat melanjutkan!');
                        focusScanInput();
                    } else {
                        $('#modalConfirmation').modal('hide');
                        notif('error', `NPK ${barcodecomplete} tidak memiliki hak akses`);
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } else {
                    $('#modalConfirmation').modal('hide');
                    notif('error', 'Scan barcode NPK');
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
                barcode += String.fromCharCode(e.which);
            }
        });

        $('#release').on('click', function() {
            focusScanInput();

            const payload = {
                line: getLineName(),
                mold_id: localStorage.getItem(STORAGE_KEYS.moldId),
                mold_code: localStorage.getItem(STORAGE_KEYS.moldCode),
                end_time: new Date().toLocaleString('sv-SE')
            };

            $.ajax({
                url: ROUTES.stopProduction,
                method: 'POST',
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': csrfToken() },
                data: JSON.stringify(payload),
                success: function() {
                    notif('success', 'Stop injection berhasil dikirim');
                    resetLocalInjectionState();
                    setTimeout(() => window.location.reload(), 1500);
                },
                error: function() {
                    notif('error', 'Gagal kirim stop injection');
                }
            });
        });

        $('#code').on('keypress', async function(e) {
            e.preventDefault();
            const keyCode = e.keyCode ? e.keyCode : e.which;

            if (keyCode !== 13) {
                barcode += String.fromCharCode(e.which);
                return;
            }

            if (isProcessingScan) {
                return;
            }

            const barcodecomplete = barcode.trim();
            barcode = '';

            if (!barcodecomplete) return;

            console.log('Scanned Code:', barcodecomplete, barcodecomplete.length);

            if (handleSpecialCommand(barcodecomplete)) return;

            if (!getLineName()) {
                notif('error', 'Input nama line terlebih dahulu!');
                checkLineName();
                return;
            }

            setScanBusy(true);

            try {
                if (isMoldTag(barcodecomplete)) {
                    await handleMoldScan(barcodecomplete);
                    return;
                }

                await handlePartScan(barcodecomplete);
            } finally {
                setScanBusy(false);
                focusScanInput();
            }
        });
    });
</script>
