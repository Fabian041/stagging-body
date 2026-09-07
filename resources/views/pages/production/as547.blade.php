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
                        <small class="text-muted text-center mb-2">kanan / kiri dibaca otomatis</small>
                        <input id="scan" type="text" class="form-control form-control-lg" name="scan" tabindex="1"
                            placeholder="scan kanban..." autocomplete="off" autofocus>
                    </div>

                    <div class="card shadow mb-3" style="padding:.7rem; border-radius:8px">
                        <small class="text-muted" style="font-weight:700">Kanban Terakhir</small>
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
                const PAIR_URL = "{{ url('production/as547/pair') }}";
                const SCAN_URL = "{{ route('production.as547.scan-kanban') }}";

                /** Jeda sebelum gambar PIS berganti ke sisi lawan setelah scan OK. */
                const FLIP_DELAY_MS = 2000;
                /** Batas jumlah kanban yang diingat untuk anti double scan di sisi client. */
                const MAX_SCANNED_KEPT = 3000;

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
                    scanned: 'as547_scanned',
                };

                const LS = {
                    get: k => localStorage.getItem(k),
                    set: (k, v) => localStorage.setItem(k, String(v)),
                    del: k => localStorage.removeItem(k),
                };

                /** True selama transisi 2 detik / request berjalan: semua scan diabaikan. */
                let locked = false;

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

                function scannedList() {
                    try {
                        const list = JSON.parse(LS.get(K.scanned) || '[]');
                        return Array.isArray(list) ? list : [];
                    } catch (e) {
                        return [];
                    }
                }

                function alreadyScanned(key) {
                    return scannedList().indexOf(key) !== -1;
                }

                function markScanned(key) {
                    const list = scannedList();
                    list.push(key);
                    if (list.length > MAX_SCANNED_KEPT) {
                        list.splice(0, list.length - MAX_SCANNED_KEPT);
                    }
                    LS.set(K.scanned, JSON.stringify(list));
                }

                function resetState() {
                    Object.keys(K).forEach(name => LS.del(K[name]));
                }

                // ---------- render ----------

                /**
                 * Kunci input pakai readonly, bukan disabled: elemen disabled kehilangan fokus
                 * dan scanner jadi mengetik ke halaman kosong. Readonly tetap bisa difokus,
                 * karakter dari scanner dibuang, dan handler Enter tetap ditahan flag locked.
                 */
                function lock() {
                    locked = true;
                    $scan.prop('readonly', true);
                }

                function unlock() {
                    locked = false;
                    $scan.prop('readonly', false);
                }

                /** Fokus selalu balik ke input, kecuali saat modal ringkasan FINISH sedang dibuka. */
                function focusScan() {
                    if ($('#finishModal').hasClass('show')) return;
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

                    $pisCaption.text(part ? (part.part_number || '') + ' — ' + (part.part_name || '') : '');
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

                /**
                 * Layout kanban berdasarkan panjang barcode — sama persis dengan parser yang
                 * sudah dipakai production/index.blade.php dan as523.blade.php.
                 * Format: [offset, panjang].
                 */
                const KANBAN_LAYOUTS = {
                    218: { internal: [41, 16], seri: [123, 4], back: [100, 4], pcs: [196, 1] }, // kanban suzuki
                    219: { internal: [42, 17], seri: [124, 4], back: [100, 4], pcs: [196, 1] },
                    220: { internal: [35, 12], seri: [130, 4], back: [100, 4], pcs: [196, 1] }, // kanban buffer
                    230: { internal: [41, 19], seri: [123, 4], back: [100, 4], pcs: [196, 1] }, // kanban normal
                    241: { internal: [35, 12], seri: [127, 4], back: [100, 4], pcs: [196, 1] }, // kanban passtrough
                };

                function cut(code, spec) {
                    return code.substr(spec[0], spec[1]).trim();
                }

                /**
                 * Parse barcode kanban.
                 * Utama: layout panjang tetap (218/219/220/230/241) seperti halaman produksi lain.
                 * Cadangan: heuristik regex untuk barcode di luar panjang standar.
                 * serial_number null = barcode tidak layak dipakai (anti double scan butuh serial asli).
                 */
                function parseKanbanBarcode(raw) {
                    const code = (raw || '').trim();
                    if (!code) return null;

                    const layout = KANBAN_LAYOUTS[code.length];
                    if (layout) {
                        const partNumber = cut(code, layout.internal);
                        const serial = cut(code, layout.seri);

                        if (partNumber) {
                            return {
                                part_number: partNumber,
                                serial_number: serial || null,
                                back_number: cut(code, layout.back) || null,
                                pcs: cut(code, layout.pcs) || null,
                                length: code.length,
                                source: 'layout-' + code.length,
                            };
                        }
                    }

                    const partMatch = code.match(/\d{5,6}-\d{4,5}/);
                    if (!partMatch) return null;

                    let block = null;
                    const mm0 = code.match(/MM0[PQ]\s*(\d{6,})/);
                    if (mm0) {
                        block = mm0[1];
                    } else {
                        const runs = code.match(/\d{10,}/g);
                        if (runs && runs.length) block = runs[runs.length - 1];
                    }

                    return {
                        part_number: partMatch[0],
                        serial_number: block ? block.substring(2, 6) : null,
                        back_number: null,
                        pcs: null,
                        length: code.length,
                        source: 'fallback',
                    };
                }

                /** Tampilkan hasil baca barcode supaya format kanban gampang diverifikasi di mesin. */
                function showParseDebug(raw, parsed, detectedSide) {
                    const len = (raw || '').trim().length;

                    if (!parsed) {
                        $('#scan-debug').text('len ' + len + ' · tidak terbaca');
                        return;
                    }

                    $('#scan-debug').text(
                        'len ' + parsed.length +
                        ' · ' + parsed.source +
                        ' · part ' + parsed.part_number +
                        ' · seri ' + (parsed.serial_number || '-') +
                        ' · back ' + (parsed.back_number || '-') +
                        ' · sisi ' + (detectedSide || '?')
                    );
                }

                /** Tentukan sisi kanban dari pasangan part yang sedang jalan. */
                function detectSide(parsed, p) {
                    if (!p || !parsed) return null;

                    if (p.R && parsed.part_number === p.R.part_number) return 'R';
                    if (p.L && parsed.part_number === p.L.part_number) return 'L';

                    if (parsed.back_number) {
                        const back = parsed.back_number.toUpperCase();
                        if (p.R && String(p.R.back_number).toUpperCase() === back) return 'R';
                        if (p.L && String(p.L.back_number).toUpperCase() === back) return 'L';
                    }

                    return null;
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

                /** Scan pertama: server yang menentukan sisi + pasangan RH/LH. Tidak menyimpan mutation. */
                function startProduction(raw, parsed) {
                    lock();

                    $.ajax({
                            url: PAIR_URL + '/' + encodeURIComponent(parsed.part_number),
                            method: 'GET',
                            dataType: 'json'
                        })
                        .done(function(res) {
                            unlock();

                            if (!res || res.status !== 'success') {
                                fail((res && res.message) || 'Part tidak dikenali.');
                                clearAndRefocus();
                                return;
                            }

                            LS.set(K.pair, JSON.stringify(res.data));
                            LS.set(K.side, res.side);
                            LS.set(K.active, '1');
                            LS.set(K.started, new Date().toISOString());
                            LS.set(K.scanned, '[]');
                            setCount('R', 0);
                            setCount('L', 0);
                            LS.del(K.lastR);
                            LS.del(K.lastL);

                            showParseDebug(raw, parsed, res.side);

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
                function submitScan(s, parsed, key) {
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
                                part_number: parsed.part_number,
                                serial_number: parsed.serial_number,
                                side: s
                            })
                        })
                        .done(function(res) {
                            if (!res || res.status !== 'success') {
                                unlock();
                                fail((res && res.message) || 'Gagal menyimpan scan.');
                                clearAndRefocus();
                                return;
                            }

                            markScanned(key);
                            setCount(s, count(s) + 1);

                            const display = parsed.part_number + ' / ' + parsed.serial_number;
                            LS.set(s === 'R' ? K.lastR : K.lastL, display);
                            (s === 'R' ? $lastR : $lastL).text(display);
                            (s === 'R' ? $countR : $countL).text(count(s));

                            $scan.val('');

                            okSound();
                            setStatus('ok');
                            notif('success', res.message + ' Ganti ke ' + sideLabel(s === 'R' ? 'L' : 'R') +
                                ' dalam 2 detik.');

                            setTimeout(function() {
                                LS.set(K.side, s === 'R' ? 'L' : 'R');
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

                    const parsed = parseKanbanBarcode(raw);

                    if (!parsed) {
                        showParseDebug(raw, null, null);
                        fail('Format barcode kanban tidak valid (part number tidak terbaca).');
                        clearAndRefocus();
                        return;
                    }

                    if (!parsed.serial_number) {
                        showParseDebug(raw, parsed, null);
                        fail('Serial kanban tidak terbaca dari barcode.');
                        clearAndRefocus();
                        return;
                    }

                    if (!isActive()) {
                        showParseDebug(raw, parsed, null);
                        startProduction(raw, parsed);
                        return;
                    }

                    const p = pair();
                    const scannedSide = detectSide(parsed, p);
                    showParseDebug(raw, parsed, scannedSide);

                    if (!scannedSide) {
                        const names = [
                            p && p.R ? p.R.back_number + ' / ' + p.R.part_number : null,
                            p && p.L ? p.L.back_number + ' / ' + p.L.part_number : null,
                        ].filter(Boolean).join(' dan ');

                        fail('Kanban ' + parsed.part_number + ' bukan bagian dari model yang sedang jalan (' +
                            names + ').');
                        clearAndRefocus();
                        return;
                    }

                    const expected = side();
                    if (scannedSide !== expected) {
                        const expectedPart = p[expected] || {};
                        fail('Sekarang harus scan ' + sideLabel(expected) + ' — ' +
                            (expectedPart.back_number || expectedPart.part_number) + '. Terbaca ' +
                            (parsed.back_number || parsed.part_number) + '.');
                        clearAndRefocus();
                        return;
                    }

                    const key = parsed.part_number + '|' + parsed.serial_number;
                    if (alreadyScanned(key)) {
                        fail('Kanban ' + parsed.part_number + ' / ' + parsed.serial_number +
                            ' sudah discan di shift ini.');
                        clearAndRefocus();
                        return;
                    }

                    submitScan(scannedSide, parsed, key);
                }

                // ---------- finish ----------

                function formatTime(iso) {
                    if (!iso) return '-';
                    const d = new Date(iso);
                    if (isNaN(d.getTime())) return '-';
                    return d.toLocaleString('id-ID', {
                        hour12: false
                    });
                }

                function formatDuration(fromIso, to) {
                    if (!fromIso) return '-';
                    const from = new Date(fromIso);
                    if (isNaN(from.getTime())) return '-';

                    let minutes = Math.max(0, Math.round((to - from) / 60000));
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

                    $('#sum-start').text(formatTime(LS.get(K.started)));
                    $('#sum-end').text(now.toLocaleString('id-ID', {
                        hour12: false
                    }));
                    $('#sum-duration').text(formatDuration(LS.get(K.started), now));
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
