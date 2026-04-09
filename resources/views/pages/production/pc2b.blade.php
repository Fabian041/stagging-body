@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <div class="mx-5 my-5">
            <div class="row">
                <div class="col-lg-2 col-sm-12">
                    <div class="card card-warning py-4 shadow" style="padding: 1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark">Scan Master Sample</label>
                        <input id="master" type="text" class="form-control" name="master" tabindex="1"
                            placeholder="scan master sample..." required autofocus autocomplete="off">
                    </div>

                    <div class="shadow pt-4 card card-secondary model-card-header"
                        style="margin-bottom:110px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px;">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Model Running</h5>
                            <div class="bg-secondary m-auto shadow model-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="model">-</h1>
                            </div>
                        </div>
                    </div>

                    <div class="shadow pt-4 card card-secondary status-card-header"
                        style="margin-bottom:110px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Status</h5>
                            <div class="bg-secondary m-auto shadow status-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="status">-</h1>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-sm-12">
                    <div class="card card-warning py-5 shadow" style="padding: 1rem; border-radius:8px" id="pis">
                        <h2 class="text-center text-dark">Ready to scan !!</h2>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-12">
                    <button id="fullscreenBtn" class="btn btn-info mb-2 text-end"
                        style="border-radius:4px; width:100% !important">Full Screen</button>

                    <button class="btn btn-warning py-3 px-5 shadow mb-2"
                        style="padding: 1rem; border-radius:8px; width:100% !important" id="release">
                        <h3 class="text-center text-white">Release</h3>
                    </button>

                    <button class="btn btn-danger py-3 px-5 shadow mb-2"
                        style="padding: 1rem; border-radius:8px; width:100% !important" id="stop">
                        <h3 class="text-center text-white">Stop</h3>
                    </button>

                    <div class="card card-warning py-4 shadow mb-2" style="padding: 1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark">Scan Kanban (Kanan)</label>
                        <input id="part-right" type="text" class="form-control" name="part-right" tabindex="2"
                            placeholder="scan kanban kanan..." autocomplete="off" disabled>
                        <small class="text-muted">Last: <span id="last-right">-</span></small>
                    </div>

                    <div class="card card-warning py-4 shadow mb-2" style="padding: 1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark">Scan Kanban (Kiri)</label>
                        <input id="part-left" type="text" class="form-control" name="part-left" tabindex="3"
                            placeholder="scan kanban kiri..." autocomplete="off" disabled>
                        <small class="text-muted">Last: <span id="last-left">-</span></small>
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

                // Dua kode master tetap: trigger awal tanpa API/DB. Part aktual: 423125-10360 (RH), 423126-10360 (LH).
                const MASTER_RH = '423125-10360-MASTER';
                const MASTER_LH = '423126-10360-MASTER';
                const VALID_MASTERS = [MASTER_RH, MASTER_LH];
                const PART_IMAGE_URL = "{{ asset('storage/pis/pc2b_part.jpeg') }}";
                const EXPECTED_PART_RIGHT = '423125-10550';
                const EXPECTED_PART_LEFT = '423126-10330';
                const PC2B_SCAN_KANBAN_URL = "{{ route('production.pc2b.scan-kanban') }}";

                var $ = window.jQuery;
                if (!$) {
                    console.error('jQuery still not available on pc2b after DOMContentLoaded');
                    return;
                }

                const $master = $('#master');
                const $partR = $('#part-right');
                const $partL = $('#part-left');

                const $pis = $('#pis');
                const $modelTxt = $('#model');

                const $statusHdr = $('.status-card-header');
                const $status = $('.status-card');
                const $txtStatus = $('#status');

                const $lastR = $('#last-right');
                const $lastL = $('#last-left');

                const LS = {
                    get: k => localStorage.getItem(k),
                    set: (k, v) => localStorage.setItem(k, String(v)),
                    mset: obj => Object.keys(obj).forEach(k => localStorage.setItem(k, String(obj[k]))),
                    del: k => localStorage.removeItem(k),
                };

                function okSound() {
                    document.getElementById("ok-sound").play();
                }

                function wrongSound() {
                    document.getElementById("wrong-kanban-sound").play();
                }

                function errConnection() {
                    document.getElementById("error-connection").play();
                }

                function notif(color, text) {
                    $('#notif').text(text);
                    $('#divNotif').css("background-color", color === "error" ? "#FF2A00" : "#32a852");
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
                    $statusHdr.removeClass('card-secondary card-success card-danger').addClass(hdrCls);
                    $status.removeClass('bg-secondary bg-success bg-danger').addClass(bgCls);
                    $txtStatus.text(txt);
                    if (state !== 'idle') {
                        setTimeout(() => setStatus('idle'), state === 'ok' ? 700 : 1200);
                    }
                }

                function api(url, method, data) {
                    return $.ajax({
                        url,
                        method,
                        dataType: 'json',
                        data
                    });
                }

                function enablePartInputs(enabled) {
                    $partR.prop('disabled', !enabled);
                    $partL.prop('disabled', !enabled);
                }

                function expectedSide() {
                    return LS.get('pc2b_expected_side') || 'R';
                }

                function setExpectedSide(side) {
                    LS.set('pc2b_expected_side', side);
                }

                function focusExpected() {
                    if (!LS.get('model')) {
                        $master.focus();
                        return;
                    }
                    if (expectedSide() === 'R') $partR.focus();
                    else $partL.focus();
                }

                function showPis(photo) {
                    if (!photo) {
                        $pis.html(`<h2 class="text-center text-dark">Ready to scan !!</h2>`);
                        return;
                    }
                    $pis.html(
                        `<img src="{{ asset('assets/img/pis/${photo}') }}" alt="PIS" class="rounded" height="700">`
                    );
                }

                /** Gambar part dari storage (symlink) — tanpa API/DB */
                function showPisFromStorage() {
                    const url = PART_IMAGE_URL;
                    $pis.html(
                        `<img src="${url}" alt="Part PC2B" class="rounded" height="700" onerror="this.parentElement.innerHTML='<h2 class=\\"text-center text-dark\\">Ready to scan !!</h2>';">`
                    );
                }

                function initFromStorage() {
                    const back = LS.get('back_number');
                    const photo = LS.get('photo');
                    const masterScanned = LS.get('pc2b_master_scanned');
                    const lastR = LS.get('pc2b_last_right') || '-';
                    const lastL = LS.get('pc2b_last_left') || '-';

                    if (back || photo) {
                        $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                        $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                        $modelTxt.text(back || '-');
                        showPis(photo);
                        enablePartInputs(true);
                    } else if (masterScanned) {
                        $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                        $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                        $modelTxt.text('PC2B');
                        showPisFromStorage();
                        enablePartInputs(true);
                    } else {
                        enablePartInputs(false);
                    }

                    $lastR.text(lastR);
                    $lastL.text(lastL);

                    focusExpected();
                }

                /**
                 * Parse barcode kanban full (format: ... 423125-10550 ... MM0P 0000050000000000000030 ...).
                 * Part number: 423125-10550.
                 * Serial number: diambil dari blok panjang setelah MM0P/MM0Q (0000050000000000000030),
                 *   yaitu 4 karakter pada posisi index 2–5 -> "0005".
                 */
                function parseKanbanBarcode(raw) {
                    const s = (raw || '').trim();
                    if (!s) return null;
                    const partMatch = s.match(/\d{5,6}-\d{4,5}/);
                    if (!partMatch) return null;
                    const part_number = partMatch[0];
                    const mm0Block = s.match(/MM0[PQ]\s+(\d+)/);
                    let serial_number = '0000';
                    if (mm0Block && mm0Block[1].length >= 6) {
                        const block = mm0Block[1];
                        serial_number = block.substring(2, 6);
                    }
                    return {
                        part_number,
                        serial_number
                    };
                }

                /** Cek apakah input adalah salah satu dari dua kode master tetap (trigger saja, tanpa API/DB). */
                function isLocalMaster(code) {
                    const t = (code || '').trim();
                    return t === MASTER_RH || t === MASTER_LH;
                }

                function handleMasterScan(raw) {
                    const v = (raw || '').trim();
                    console.log('PC2B handleMasterScan raw:', v);

                    if (!v) return;

                    if (isLocalMaster(v)) {
                        // Hanya trigger lokal: tidak ada API, tidak ada simpan ke DB
                        LS.mset({
                            pc2b_master_scanned: '1',
                            model: 'PC2B',
                            line: 'PC2B',
                            pc2b_expected_side: 'R',
                        });
                        LS.del('back_number');
                        LS.del('photo');
                        LS.del('pc2b_last_right');
                        LS.del('pc2b_last_left');
                        $lastR.text('-');
                        $lastL.text('-');

                        $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                        $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                        $modelTxt.text('PC2B');
                        showPisFromStorage();
                        enablePartInputs(true);

                        okSound();
                        notif('success', 'Master OK. Lanjut scan kanban KANAN (' + EXPECTED_PART_RIGHT + ').');
                        setStatus('ok');

                        $master.val('');
                        $partR.val('');
                        $partL.val('');
                        setTimeout(function() {
                            $partR[0].focus();
                        }, 1850);
                        return;
                    }

                    wrongSound();
                    notif('error', 'Gunakan 423125-10360-MASTER atau 423126-10360-MASTER.');
                    setStatus('ng');
                    $master.val('');
                    $master.focus();
                }

                function handlePartScan(side, rawBarcode) {
                    const exp = expectedSide();

                    if (!LS.get('model') || !LS.get('line')) {
                        wrongSound();
                        notif('error', 'Scan master sample dulu.');
                        setStatus('ng');
                        enablePartInputs(false);
                        $master.focus();
                        return;
                    }

                    if (side !== exp) {
                        wrongSound();
                        notif('error',
                            `Urutan salah. Sekarang harus scan kanban ${exp === 'R' ? 'KANAN' : 'KIRI'}.`);
                        setStatus('ng');
                        focusExpected();
                        return;
                    }

                    const parsed = parseKanbanBarcode(rawBarcode);
                    if (!parsed) {
                        wrongSound();
                        notif('error',
                            'Format barcode kanban tidak valid. Harus ada part number (contoh: 423125-10550) dan serial.'
                        );
                        setStatus('ng');
                        focusExpected();
                        return;
                    }

                    const expectedPart = side === 'R' ? EXPECTED_PART_RIGHT : EXPECTED_PART_LEFT;
                    if (parsed.part_number !== expectedPart) {
                        wrongSound();
                        notif('error',
                            `Part number salah. Harus ${expectedPart}, dapat: ${parsed.part_number}`);
                        setStatus('ng');
                        focusExpected();
                        return;
                    }

                    $.ajax({
                            url: PC2B_SCAN_KANBAN_URL,
                            method: 'POST',
                            contentType: 'application/json',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': CSRF
                            },
                            data: JSON.stringify({
                                part_number: parsed.part_number,
                                serial_number: parsed.serial_number,
                                side: side
                            })
                        })
                        .done(function(res) {
                            if (res.status !== 'success') {
                                wrongSound();
                                notif('error', res.message || 'Gagal menyimpan scan.');
                                setStatus('ng');
                                focusExpected();
                                return;
                            }
                            const displayText = parsed.part_number + ' / ' + parsed.serial_number;
                            if (side === 'R') {
                                LS.set('pc2b_last_right', displayText);
                                $lastR.text(displayText);
                                setExpectedSide('L');
                                $partR.val('');
                                okSound();
                                notif('success', 'Scan kanban kanan OK. Lanjut kanban kiri.');
                                setStatus('ok');
                                setTimeout(function() {
                                    $partL[0].focus();
                                }, 1850);
                            } else {
                                LS.set('pc2b_last_left', displayText);
                                $lastL.text(displayText);
                                setExpectedSide('R');
                                $partL.val('');
                                okSound();
                                notif('success', 'Scan kanban kiri OK. Lanjut kanban kanan.');
                                setStatus('ok');
                                setTimeout(function() {
                                    $partR[0].focus();
                                }, 1850);
                            }
                        })
                        .fail(function(xhr) {
                            if (xhr.status === 0) {
                                notif('error', 'Connection Error');
                                errConnection();
                            } else {
                                const msg = xhr.responseJSON && (xhr.responseJSON.message || xhr
                                    .responseJSON.errors);
                                wrongSound();
                                notif('error', msg || 'Gagal menyimpan mutation.');
                            }
                            setStatus('ng');
                            focusExpected();
                        });
                }

                console.log('PC2B script initialized, $master length =', $master.length);
                initFromStorage();

                // Fullscreen button
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

                // Fokuskan ke field yang diharapkan pada setiap klik
                $(document).on('click', function() {
                    focusExpected();
                });

                // Tombol release
                $('#release').on('click', function() {
                    localStorage.clear();
                    window.location.reload();
                });

                // Tombol stop
                $('#stop').on('click', function() {
                    const model = LS.get('model');
                    const localOnly = LS.get('pc2b_master_scanned');
                    if (!model && !localOnly) {
                        wrongSound();
                        notif('error', 'Belum ada master sample / model running.');
                        setStatus('ng');
                        $master.focus();
                        return;
                    }
                    // Mode master lokal (tanpa API/DB): cukup clear state dan reload
                    if (localOnly || model === 'PC2B') {
                        localStorage.removeItem('pc2b_master_scanned');
                        localStorage.removeItem('model');
                        localStorage.removeItem('line');
                        localStorage.removeItem('pc2b_expected_side');
                        localStorage.removeItem('pc2b_last_right');
                        localStorage.removeItem('pc2b_last_left');
                        okSound();
                        notif('success', 'Stop. State lokal direset.');
                        setStatus('ok');
                        setTimeout(function() {
                            window.location.reload();
                        }, 800);
                        return;
                    }
                    $.ajax({
                            url: `/production/api-stop`,
                            method: 'POST',
                            contentType: 'application/json',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': CSRF
                            },
                            data: JSON.stringify([model])
                        })
                        .done(res => {
                            if (res.status !== 'success') {
                                wrongSound();
                                notif('error', res.message || 'Gagal stop inbound');
                                setStatus('ng');
                                return;
                            }
                            okSound();
                            notif('success', 'Stop berhasil dikirim.');
                            setStatus('ok');
                            focusExpected();
                        })
                        .fail(xhr => {
                            if (xhr.status === 0) {
                                notif('error', 'Connection Error');
                                errConnection();
                                return;
                            }
                            wrongSound();
                            notif('error', xhr.responseJSON?.message || xhr.responseJSON?.errors ||
                                'Internal Server Error');
                            setStatus('ng');
                            focusExpected();
                        });
                });

                // EVENT SCAN MASTER SAMPLE – langsung di field #master
                $master.on('keydown', function(e) {
                    const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                    if (!isEnter) return;
                    e.preventDefault();
                    const v = $(this).val();
                    console.log('PC2B master keydown Enter, value:', v);
                    handleMasterScan(v);
                });

                $master.on('change', function() {
                    const v = $(this).val();
                    if (!v) return;
                    console.log('PC2B master change, value:', v);
                    handleMasterScan(v);
                });

                // Event untuk part kanan/kiri
                $partR.on('keydown', function(e) {
                    const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                    if (!isEnter) return;
                    e.preventDefault();
                    handlePartScan('R', $(this).val());
                });

                $partL.on('keydown', function(e) {
                    const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                    if (!isEnter) return;
                    e.preventDefault();
                    handlePartScan('L', $(this).val());
                });
            });
        })();
    </script>
