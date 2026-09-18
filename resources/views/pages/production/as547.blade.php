@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <div class="mx-4 my-4">
            <div class="row">
                <div class="col-lg-2 col-sm-12">
                    <div class="card side-card-header card-secondary shadow mb-3" style="border-radius:8px">
                        <div class="card-body py-3">
                            <h6 class="text-center text-dark" style="font-weight:800">Sedang Produksi</h6>
                            <div class="side-card bg-secondary shadow m-auto d-flex align-items-center justify-content-center"
                                style="height:6rem; width:100%; border-radius:6px">
                                <h1 class="text-center mb-0" style="color:#ffffff; font-size:1.9rem" id="side">STANDBY</h1>
                            </div>
                        </div>
                    </div>

                    <div class="card model-card-header card-secondary shadow mb-3" style="border-radius:8px">
                        <div class="card-body py-3">
                            <h6 class="text-center text-dark" style="font-weight:800">Model Running</h6>
                            <div class="model-card bg-secondary shadow m-auto d-flex align-items-center justify-content-center"
                                style="height:6rem; width:100%; border-radius:6px">
                                <h1 class="text-center mb-0" style="color:#ffffff; font-size:2.6rem" id="model">-</h1>
                            </div>
                        </div>
                    </div>

                    <div class="card status-card-header card-secondary shadow mb-3" style="border-radius:8px">
                        <div class="card-body py-3">
                            <h6 class="text-center text-dark" style="font-weight:800">Status</h6>
                            <div class="status-card bg-secondary shadow m-auto d-flex align-items-center justify-content-center"
                                style="height:6rem; width:100%; border-radius:6px">
                                <h1 class="text-center mb-0" style="color:#ffffff; font-size:2.6rem" id="status">-</h1>
                            </div>
                        </div>
                    </div>

                    <div class="card card-secondary shadow mb-3" style="border-radius:8px">
                        <div class="card-body py-3">
                            <h6 class="text-center text-dark mb-2" style="font-weight:800">Total Box Shift Ini</h6>

                            <div class="d-flex align-items-center justify-content-between px-1 mb-2">
                                <span class="text-dark" style="font-weight:700">RH <small class="text-muted"
                                        id="label-r">-</small></span>
                                <span class="badge badge-primary" style="font-size:1.4rem" id="count-r">0</span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between px-1">
                                <span class="text-dark" style="font-weight:700">LH <small class="text-muted"
                                        id="label-l">-</small></span>
                                <span class="badge badge-info" style="font-size:1.4rem" id="count-l">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-sm-12">
                    <div class="card card-warning py-4 shadow text-center" style="padding:1rem; border-radius:8px">
                        <div id="pis">
                            <h2 class="text-center text-dark">Scan kanban untuk memulai produksi</h2>
                        </div>
                        <p class="mt-3 mb-0 text-muted" id="pis-caption"></p>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-12">
                    <button id="fullscreenBtn" class="btn btn-info mb-2"
                        style="border-radius:4px; width:100% !important">Full Screen</button>

                    <button class="btn btn-danger py-3 shadow mb-3" style="border-radius:8px; width:100% !important"
                        id="finish">
                        <h3 class="text-center text-white mb-0">FINISH</h3>
                    </button>

                    <div class="card card-warning py-3 shadow mb-3" style="padding:1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark mb-1">Scan Kanban</label>
                        <small class="text-muted text-center mb-2">kanban internal / customer,<br>kanan &amp; kiri
                            dibaca otomatis</small>
                        <input id="scan" type="text" class="form-control form-control-lg" name="scan" tabindex="1"
                            placeholder="scan kanban..." autocomplete="off" autofocus>
                    </div>

                    <div class="card shadow mb-3" style="padding:.7rem; border-radius:8px">
                        <small class="text-muted" style="font-weight:700">Box Terakhir</small>
                        <small class="text-muted">RH: <span id="last-right">-</span></small>
                        <small class="text-muted">LH: <span id="last-left">-</span></small>
                    </div>

                    <div class="card shadow" style="padding:.7rem; border-radius:8px">
                        <small class="text-muted" style="font-weight:700">Hasil Baca Barcode</small>
                        <small class="text-muted" style="word-break:break-all" id="scan-debug">-</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- modal notif --}}
    <div class="modal fade gfont" id="notifModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="divNotif" style="border-radius: 12px !important;">
                <div class="modal-body text-center">
                    <span style="color: white; font-size: 30pt" id="notif"> Error!</span>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal notif --}}

    {{-- modal konfirmasi scan beruntun --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px">
                <div class="modal-header">
                    <h5 class="modal-title">Box Baru?</h5>
                </div>
                <div class="modal-body">
                    <p class="mb-2" id="confirm-message">-</p>
                    <p class="mb-0 text-muted">
                        Pilih <b>Ya</b> kalau ini memang box berikutnya, <b>Batal</b> kalau tidak sengaja
                        discan dua kali.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="confirm-cancel">Batal</button>
                    <button type="button" class="btn btn-success" id="confirm-yes">Ya, Box Baru</button>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal konfirmasi --}}

    {{-- modal ringkasan finish --}}
    <div class="modal fade" id="finishModal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px">
                <div class="modal-header">
                    <h5 class="modal-title">Ringkasan Produksi AS547</h5>
                </div>
                <div class="modal-body">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th style="width:45%">Mulai</th>
                                <td id="sum-start">-</td>
                            </tr>
                            <tr>
                                <th>Selesai</th>
                                <td id="sum-end">-</td>
                            </tr>
                            <tr>
                                <th>Durasi</th>
                                <td id="sum-duration">-</td>
                            </tr>
                            <tr>
                                <th>Box RH <small class="text-muted" id="sum-label-r"></small></th>
                                <td id="sum-count-r">0</td>
                            </tr>
                            <tr>
                                <th>Box LH <small class="text-muted" id="sum-label-l"></small></th>
                                <td id="sum-count-l">0</td>
                            </tr>
                            <tr>
                                <th>Total Box</th>
                                <td id="sum-total">0</td>
                            </tr>
                            <tr>
                                <th>Estimasi Pcs</th>
                                <td id="sum-pcs">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="finish-cancel">Batal</button>
                    <button type="button" class="btn btn-danger" id="finish-confirm">Selesai &amp; Reset</button>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal ringkasan --}}

    <audio id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="wrong-kanban-sound">
        <source src={{ asset('assets/sounds/wrongKanban.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="error-connection">
        <source src={{ asset('assets/sounds/errConnection.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
@endsection

@section('custom-script')
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const CSRF = "{{ csrf_token() }}";
                const PIS_BASE_URL = "{{ asset('assets/img/pis') }}";
                const PAIR_URL = "{{ route('production.as547.pair') }}";
                const SCAN_URL = "{{ route('production.as547.scan-kanban') }}";

                /** Jeda sebelum gambar PIS berganti ke sisi lawan setelah scan OK. */
                const FLIP_DELAY_MS = 2000;

                var $ = window.jQuery;
                if (!$) {
                    console.error('jQuery still not available on as547 after DOMContentLoaded');
                    return;
                }

                const $scan = $('#scan');
                const $pis = $('#pis');
                const $pisCaption = $('#pis-caption');
                const $modelTxt = $('#model');
                const $sideTxt = $('#side');
                const $countR = $('#count-r');
                const $countL = $('#count-l');
                const $labelR = $('#label-r');
                const $labelL = $('#label-l');
                const $lastR = $('#last-right');
                const $lastL = $('#last-left');
                const $txtStatus = $('#status');

                const K = {
                    active: 'as547_active',
                    pair: 'as547_pair',
                    side: 'as547_side',
                    countR: 'as547_count_r',
                    countL: 'as547_count_l',
                    lastR: 'as547_last_right',
                    lastL: 'as547_last_left',
                    started: 'as547_started_at',
                };

                const LS = {
                    get: k => localStorage.getItem(k),
                    set: (k, v) => localStorage.setItem(k, String(v)),
                    del: k => localStorage.removeItem(k),
                };

                /** True selama transisi 2 detik / request berjalan: semua scan diabaikan. */
                let locked = false;
                /** Scan yang sedang menunggu konfirmasi "box baru?" dari MP. */
                let pendingScan = null;

                function okSound() {
                    document.getElementById('ok-sound').play();
                }

                function wrongSound() {
                    document.getElementById('wrong-kanban-sound').play();
                }

                function errConnection() {
                    document.getElementById('error-connection').play();
                }

                function notif(color, text) {
                    $('#notif').text(text);
                    $('#divNotif').css('background-color', color === 'error' ? '#FF2A00' : '#32a852');
                    $('#notifModal').modal('show');
                    setTimeout(() => {
                        $('#notifModal').modal('hide');
                    }, 1800);
                }

                function setStatus(state) {
                    const map = {
                        ok: ['card-success', 'bg-success', 'OK'],
                        ng: ['card-danger', 'bg-danger', 'NG'],
                        idle: ['card-secondary', 'bg-secondary', '-']
                    };
                    const [hdrCls, bgCls, txt] = map[state] || map.idle;
                    $('.status-card-header').removeClass('card-secondary card-success card-danger').addClass(hdrCls);
                    $('.status-card').removeClass('bg-secondary bg-success bg-danger').addClass(bgCls);
                    $txtStatus.text(txt);
                    if (state !== 'idle') {
                        setTimeout(() => setStatus('idle'), state === 'ok' ? 1200 : 1500);
                    }
                }

                function fail(message) {
                    wrongSound();
                    notif('error', message);
                    setStatus('ng');
                }

                // ---------- state ----------

                function isActive() {
                    return LS.get(K.active) === '1';
                }

                function pair() {
                    try {
                        return JSON.parse(LS.get(K.pair) || 'null');
                    } catch (e) {
                        return null;
                    }
                }

                function side() {
                    return LS.get(K.side) === 'L' ? 'L' : 'R';
                }

                function sideLabel(s) {
                    return s === 'R' ? 'KANAN (RH)' : 'KIRI (LH)';
                }

                function count(s) {
                    return parseInt(LS.get(s === 'R' ? K.countR : K.countL) || '0', 10) || 0;
                }

                function setCount(s, value) {
                    LS.set(s === 'R' ? K.countR : K.countL, value);
                }

                function resetState() {
                    Object.keys(K).forEach(name => LS.del(K[name]));
                }

                // ---------- render ----------

                function lock() {
                    locked = true;
                    $scan.prop('readonly', true);
                }

                function unlock() {
                    locked = false;
                    $scan.prop('readonly', false);
                }

                function modalOpen() {
                    return $('#finishModal, #confirmModal').hasClass('show');
                }

                function focusScan() {
                    if (modalOpen()) return;
                    if (document.activeElement === $scan[0]) return;
                    $scan.trigger('focus');
                }

                function clearAndRefocus() {
                    $scan.val('');
                    setTimeout(focusScan, 50);
                }

                function showPis(part) {
                    const photo = part && part.photo ? String(part.photo) : '';

                    if (!photo) {
                        $pis.html('<h2 class="text-center text-dark">Gambar PIS belum di-set untuk part ini</h2>');
                    } else {
                        const $img = $('<img>')
                            .attr('src', PIS_BASE_URL + '/' + encodeURIComponent(photo))
                            .attr('alt', 'PIS')
                            .attr('height', 700)
                            .addClass('rounded')
                            .on('error', function() {
                                $pis.html('<h2 class="text-center text-dark">Gambar PIS tidak ditemukan: ' +
                                    photo + '</h2>');
                            });
                        $pis.empty().append($img);
                    }

                    if (!part) {
                        $pisCaption.text('');
                        return;
                    }

                    const cust = (part.customer_part_numbers || []).join(' / ');
                    $pisCaption.text(
                        (part.part_number || '') + ' — ' + (part.part_name || '') +
                        (cust ? '  ·  kanban customer ' + cust : '')
                    );
                }

                function render() {
                    const p = pair();

                    if (!isActive() || !p) {
                        $('.side-card-header').removeClass('card-primary card-info').addClass('card-secondary');
                        $('.side-card').removeClass('bg-primary bg-info').addClass('bg-secondary');
                        $sideTxt.text('STANDBY');

                        $('.model-card-header').removeClass('card-info').addClass('card-secondary');
                        $('.model-card').removeClass('bg-info').addClass('bg-secondary');
                        $modelTxt.text('-');

                        $pis.html('<h2 class="text-center text-dark">Scan kanban untuk memulai produksi</h2>');
                        $pisCaption.text('');

                        $labelR.text('-');
                        $labelL.text('-');
                        $countR.text('0');
                        $countL.text('0');
                        $lastR.text('-');
                        $lastL.text('-');

                        $scan.prop('readonly', false);
                        focusScan();
                        return;
                    }

                    const s = side();
                    const part = p[s] || {};

                    $('.side-card-header').removeClass('card-secondary card-primary card-info')
                        .addClass(s === 'R' ? 'card-primary' : 'card-info');
                    $('.side-card').removeClass('bg-secondary bg-primary bg-info')
                        .addClass(s === 'R' ? 'bg-primary' : 'bg-info');
                    $sideTxt.text(sideLabel(s));

                    $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                    $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                    $modelTxt.text(part.back_number || part.part_number || '-');

                    showPis(part);

                    $labelR.text(p.R && p.R.back_number ? p.R.back_number : '-');
                    $labelL.text(p.L && p.L.back_number ? p.L.back_number : '-');
                    $countR.text(count('R'));
                    $countL.text(count('L'));
                    $lastR.text(LS.get(K.lastR) || '-');
                    $lastL.text(LS.get(K.lastL) || '-');

                    $scan.prop('readonly', false);
                    focusScan();
                }

                // ---------- barcode ----------

                function normalizeCode(value) {
                    return String(value || '').trim();
                }

                /**
                 * Jenis kanban ditentukan server dari barcode-nya sendiri: kanban internal
                 * cetakan plant panjangnya 218-241 karakter, kanban customer 8-15 karakter.
                 * Halaman tidak ikut menebak supaya aturannya cuma ada di satu tempat.
                 */
                function showDebug(code, res) {
                    const norm = normalizeCode(code);
                    const head = 'len ' + norm.length + ' · ' +
                        (norm.length > 24 ? norm.slice(0, 24) + '…' : (norm || '-'));

                    if (!res) {
                        $('#scan-debug').text(head);
                        return;
                    }

                    $('#scan-debug').text(
                        head +
                        ' · ' + (res.source === 'internal' ? 'kanban internal' : 'kanban customer') +
                        (res.side ? ' · sisi ' + res.side : '') +
                        (res.serial ? ' · ' + res.serial : '')
                    );
                }

                // ---------- alur scan ----------

                function handleAjaxFail(xhr) {
                    if (xhr.status === 0) {
                        errConnection();
                        notif('error', 'Connection Error');
                        setStatus('ng');
                        return;
                    }

                    const body = xhr.responseJSON || {};
                    let message = body.message;

                    if (!message && body.errors) {
                        message = Object.keys(body.errors).map(k => body.errors[k]).join(' ');
                    }

                    fail(message || 'Internal Server Error');
                }

                /** Scan pertama: server menentukan sisi + pasangan RH/LH. Tidak menyimpan mutation. */
                function startProduction(code) {
                    lock();

                    $.ajax({
                            url: PAIR_URL,
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                code: normalizeCode(code)
                            }
                        })
                        .done(function(res) {
                            unlock();

                            if (!res || res.status !== 'success') {
                                fail((res && res.message) || 'Kanban customer tidak dikenali.');
                                clearAndRefocus();
                                return;
                            }

                            LS.set(K.pair, JSON.stringify(res.data));
                            LS.set(K.side, res.side);
                            LS.set(K.active, '1');
                            LS.set(K.started, Date.now());
                            setCount('R', 0);
                            setCount('L', 0);
                            LS.del(K.lastR);
                            LS.del(K.lastL);

                            showDebug(code, {
                                source: res.source,
                                side: res.side
                            });

                            $scan.val('');
                            render();

                            okSound();
                            setStatus('ok');
                            notif('success', 'Produksi dimulai — ' + sideLabel(res.side) +
                                '. Scan kanban lagi setelah 1 box selesai.');
                        })
                        .fail(function(xhr) {
                            unlock();
                            handleAjaxFail(xhr);
                            clearAndRefocus();
                        });
                }

                /** Scan berikutnya: 1 scan = 1 box selesai, lalu gambar pindah ke sisi lawan. */
                function submitScan(code, confirmed) {
                    const p = pair();
                    const expected = side();
                    const expectedPart = (p && p[expected] && p[expected].part_number) || '';

                    if (!expectedPart) {
                        fail('Data model hilang. Tekan FINISH lalu scan ulang kanban pembuka.');
                        clearAndRefocus();
                        return;
                    }

                    lock();

                    $.ajax({
                            url: SCAN_URL,
                            method: 'POST',
                            contentType: 'application/json',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': CSRF
                            },
                            data: JSON.stringify({
                                code: normalizeCode(code),
                                expected_side: expected,
                                expected_part: expectedPart,
                                confirm: confirmed === true
                            })
                        })
                        .done(function(res) {
                            // Scan mencurigakan (kartu sama / terlalu rapat): tanya dulu,
                            // jangan ditolak dan jangan dihitung diam-diam.
                            if (res && res.status === 'confirm') {
                                unlock();
                                pendingScan = normalizeCode(code);
                                $('#confirm-message').text(res.message);
                                $('#confirmModal').modal('show');
                                return;
                            }

                            if (!res || res.status !== 'success') {
                                unlock();
                                fail((res && res.message) || 'Gagal menyimpan scan.');
                                clearAndRefocus();
                                return;
                            }

                            showDebug(code, res);

                            const s = res.side || expected;
                            setCount(s, count(s) + 1);

                            const display = (res.back_number || '') + ' · ' + (res.serial || '');
                            LS.set(s === 'R' ? K.lastR : K.lastL, display);
                            (s === 'R' ? $lastR : $lastL).text(display);
                            (s === 'R' ? $countR : $countL).text(count(s));

                            $scan.val('');

                            okSound();
                            setStatus('ok');
                            notif('success', res.message + ' Ganti ke ' + sideLabel(res.next_side) +
                                ' dalam 2 detik.');

                            setTimeout(function() {
                                LS.set(K.side, res.next_side);
                                unlock();
                                render();
                            }, FLIP_DELAY_MS);
                        })
                        .fail(function(xhr) {
                            unlock();
                            handleAjaxFail(xhr);
                            clearAndRefocus();
                        });
                }

                function handleScan(raw) {
                    if (locked) return;

                    const code = normalizeCode(raw);

                    if (!code) {
                        clearAndRefocus();
                        return;
                    }

                    showDebug(code, null);

                    if (!isActive()) {
                        startProduction(code);
                        return;
                    }

                    // Sisi, jenis kanban, dan kecocokan model semuanya divalidasi server.
                    submitScan(code, false);
                }

                // ---------- finish ----------

                function jam(epochMs) {
                    const ms = parseInt(epochMs, 10);
                    if (!ms) return '-';
                    const d = new Date(ms);
                    return isNaN(d.getTime()) ? '-' : d.toLocaleString('id-ID', {
                        hour12: false
                    });
                }

                function formatDuration(startMs, to) {
                    const ms = parseInt(startMs, 10);
                    if (!ms) return '-';

                    let minutes = Math.max(0, Math.round((to - ms) / 60000));
                    const hours = Math.floor(minutes / 60);
                    minutes = minutes % 60;

                    return hours + ' jam ' + minutes + ' menit';
                }

                function showSummary() {
                    const p = pair() || {};
                    const now = new Date();
                    const r = count('R');
                    const l = count('L');

                    const qtyR = p.R && p.R.qty_per_kanban ? p.R.qty_per_kanban : null;
                    const qtyL = p.L && p.L.qty_per_kanban ? p.L.qty_per_kanban : null;

                    $('#sum-start').text(jam(LS.get(K.started)));
                    $('#sum-end').text(now.toLocaleString('id-ID', {
                        hour12: false
                    }));
                    $('#sum-duration').text(formatDuration(LS.get(K.started), now.getTime()));
                    $('#sum-label-r').text(p.R && p.R.back_number ? '(' + p.R.back_number + ')' : '');
                    $('#sum-label-l').text(p.L && p.L.back_number ? '(' + p.L.back_number + ')' : '');
                    $('#sum-count-r').text(r);
                    $('#sum-count-l').text(l);
                    $('#sum-total').text(r + l);
                    $('#sum-pcs').text(
                        qtyR || qtyL ?
                        (r * (qtyR || 0) + l * (qtyL || 0)) + ' pcs' :
                        'qty per kanban belum di-set'
                    );

                    $('#finishModal').modal('show');
                }

                // ---------- init & events ----------

                // Modal notif tidak boleh merebut fokus: tanpa ini Bootstrap memasang focus trap
                // (_enforceFocus) yang menarik fokus keluar dari input setiap notif muncul.
                $('#notifModal').modal({
                    show: false,
                    focus: false,
                    keyboard: false
                });

                $('#notifModal').on('shown.bs.modal hidden.bs.modal', function() {
                    $(document).off('focusin.bs.modal');
                    focusScan();
                });

                // Jaring pengaman: apa pun yang merebut fokus (modal, backdrop, klik layar),
                // fokus dikembalikan ke input scan supaya MP tidak perlu menyentuh layar.
                setInterval(focusScan, 400);

                render();

                $('#fullscreenBtn').on('click', function() {
                    const docEl = document.documentElement;
                    if (!document.fullscreenElement) {
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

                $('#confirm-yes').on('click', function() {
                    const code = pendingScan;
                    pendingScan = null;
                    $('#confirmModal').modal('hide');

                    if (code) {
                        submitScan(code, true);
                    }
                });

                $('#confirm-cancel').on('click', function() {
                    pendingScan = null;
                    $('#confirmModal').modal('hide');
                    clearAndRefocus();
                });

                $('#confirmModal').on('hidden.bs.modal', function() {
                    focusScan();
                });

                $('#finish').on('click', function() {
                    if (!isActive()) {
                        fail('Belum ada produksi berjalan.');
                        return;
                    }
                    showSummary();
                });

                $('#finish-confirm').on('click', function() {
                    resetState();
                    $('#finishModal').modal('hide');
                    setTimeout(function() {
                        window.location.reload();
                    }, 300);
                });

                $('#finishModal').on('hidden.bs.modal', function() {
                    focusScan();
                });

                // Kembalikan fokus ke field scan, tapi jangan ganggu tombol / modal.
                $(document).on('click', function(e) {
                    if ($(e.target).closest('button, a, input, .modal').length) return;
                    focusScan();
                });

                $scan.on('keydown', function(e) {
                    const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                    if (!isEnter) return;

                    e.preventDefault();
                    handleScan($(this).val());
                });
            });
        })();
    </script>
@endsection
