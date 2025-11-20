@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <div class="mx-5 my-5">
            <div class="row">
                <div class="col-lg-2 col-sm-12">
                    <div class="card card-warning py-5 shadow" style="padding: 1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark">Scan Part Number</label>
                        <input id="code" type="text" class="form-control" name="code" tabindex="1"
                            placeholder="scan part..." required autofocus autocomplete="off">
                    </div>
                    <div class="shadow pt-4 card card-secondary model-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px;">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Model Running</h5>
                            <div class="bg-secondary m-auto shadow model-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="model">-</h1>
                            </div>
                        </div>
                    </div>
                    <div class="shadow pt-4 card card-secondary total-scan-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Scan Progress</h5>
                            <div class="bg-secondary m-auto shadow total-scan-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="total-scan">0 / 0</h1>
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
                    <button class="btn btn-danger py-3 px-5 shadow mb-4"
                        style="padding: 1rem; border-radius:8px; width:100% !important" id="pause">
                        <h3 class="text-center text-white">Pause</h3>
                    </button>
                    <div class="shadow pt-4 card card-secondary status-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Status</h5>
                            <div class="bg-secondary m-auto shadow status-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="status">-</h1>
                            </div>
                        </div>
                    </div>
                    <div class="shadow pt-4 card card-secondary total-part-card-header"
                        style="margin-bottom:130px;height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Total Part</h5>
                            <div class="bg-secondary m-auto shadow total-part-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="total-part">0</h1>
                            </div>
                        </div>
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

    {{-- confirmation modal --}}
    <div class="modal fade" id="modalConfirmation" aria-hidden="true" aria-labelledby="modalToggleLabel2" tabindex="-1"
        data-backdrop="static">
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
    {{-- end of confirmation modal --}}

    <audio id="not-match-sound">
        <source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg" preload="auto">
    </audio>

    <audio id="already-scan-sound">
        <source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg" preload="auto">
    </audio>

    <audio id="forget-sound">
        <source src={{ asset('assets/sounds/forget.mp3') }} type="audio/mpeg" preload="auto">
    </audio>

    <audio id="match-sound">
        <source src={{ asset('assets/sounds/match.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="fullfilled-sound">
        <source src={{ asset('assets/sounds/fullfilled.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="error-connection">
        <source src={{ asset('assets/sounds/errConnection.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="dandori-ng-sound">
        <source src={{ asset('assets/sounds/dandori_error.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="master-dandori-ng-sound">
        <source src={{ asset('assets/sounds/master_dandori_error.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
    <audio id="wrong-kanban-sound">
        <source src={{ asset('assets/sounds/wrongKanban.mp3') }} type="audio/mpeg" preload="auto">
    </audio>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    let line = '';
    var timerId;
    var timerActive = false;
    var endTime;
    var barcode = ""; // buffer global untuk #code
    var isConfirmationShown = false; // kontrol modal konfirmasi

    function notMatchSound() {
        document.getElementById("not-match-sound").play();
    }

    function errConnection() {
        document.getElementById("error-connection").play();
    }

    function alreadyScanSound() {
        document.getElementById("already-scan-sound").play();
    }

    function forgetSound() {
        document.getElementById("forget-sound").play();
    }

    function matchSound() {
        document.getElementById("match-sound").play();
    }

    function fullfilledSound() {
        document.getElementById("fullfilled-sound").play();
    }

    function okSound() {
        document.getElementById("ok-sound").play();
    }

    function dandoriSound() {
        document.getElementById("dandori-ng-sound").play();
    }

    function masterDandoriSound() {
        document.getElementById("master-dandori-ng-sound").play();
    }

    function wrongKanbanSound() {
        document.getElementById("wrong-kanban-sound").play();
    }

    // Modal konfirmasi: kasih jeda setelah notif dulu baru muncul modal + fokus
    function showModalConfirmation() {
        if (isConfirmationShown) return; // jangan buka berulang-ulang

        isConfirmationShown = true;

        // jeda 3.5 detik (notif 3 detik, lalu modal muncul)
        setTimeout(function() {
            $('#modalConfirmation')
                .one('shown.bs.modal', function() {
                    $('#input-confirmation').focus();
                })
                .modal('show');
        }, 3500);
    }

    // ====== LOOP: selalu jalan tiap 2 detik, tapi modalnya diatur oleh showModalConfirmation() ======
    function loopNotMatchSound() {
        if (localStorage.getItem('error') === 'true') {
            wrongKanbanSound();
            showModalConfirmation();
        }
        setTimeout(loopNotMatchSound, 2000);
    }

    function loopDandoriSound() {
        if (localStorage.getItem('dandori_error') === 'true') {
            dandoriSound();
            showModalConfirmation();
        }
        setTimeout(loopDandoriSound, 2000);
    }

    function loopMasterDandoriSound() {
        if (localStorage.getItem('master_dandori_error') === 'true') {
            masterDandoriSound();
            showModalConfirmation();
        }
        setTimeout(loopMasterDandoriSound, 2000);
    }

    function loopAlreadyScanSound() {
        if (localStorage.getItem('kanban_exist_error') === 'true') {
            alreadyScanSound();
            showModalConfirmation();
        }
        setTimeout(loopAlreadyScanSound, 2000);
    }

    let hasNotified = false;

    function initApp() {
        let model = localStorage.getItem('model');
        let backNumber = localStorage.getItem('back_number');
        let totalScan = localStorage.getItem('scan_counter');
        let totalPart = localStorage.getItem('part_counter');
        let photo = localStorage.getItem('photo');

        if (model || photo) {
            $('.model-card-header').removeClass('card-secondary').addClass('card-info');
            $('.model-card').removeClass('bg-secondary').addClass('bg-info');

            $('#model').text(backNumber);
            $('#pis').html(
                `<img src="{{ asset('assets/img/pis/${photo}') }}" alt="PIS" class="rounded" height="600">`
            );
        }

        // === UPDATE: tampilkan total scan dengan target yang tersimpan ===
        if (totalScan !== null || totalPart !== null) {
            const tgt = (typeof getTarget === 'function') ? getTarget() : 0;

            $('.total-scan-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-scan-card').removeClass('bg-secondary').addClass('bg-success');

            $('.total-part-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-part-card').removeClass('bg-secondary').addClass('bg-success');

            $('#total-scan').text(`${totalScan || 0} / ${tgt}`);
            $('#total-part').text(totalPart || 0);
        }

        loopNotMatchSound();
        loopDandoriSound();
        loopMasterDandoriSound();
        loopAlreadyScanSound();

        $('#code').focus();
    }

    function notif(color, text) {
        let textNotif = $('#notif');
        if (color === "error") {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#FF2A00");
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
        }
        $('#notifModal').modal('show');
        setTimeout(() => {
            $('#notifModal').modal('hide');
            // fokus sementara ke code, nanti saat modalConfirmation muncul, fokus pindah ke input-confirmation
            $('#code').focus();
        }, 3000);
    }

    function extractMasterSample(key) {
        const prefix = "counter_";
        return key.substring(prefix.length);
    }

    function getMasterSample() {
        let masterSample = false;
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("counter_")) {
                masterSample = extractMasterSample(key);
            }
        }
        return masterSample;
    }

    function deleteMasterSampleCounter() {
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("counter_")) {
                localStorage.removeItem(key);
            }
        }
    }

    function startTimer() {
        if (timerActive) return;

        var currentTime = new Date().getTime();
        var storedEndTime = localStorage.getItem('timerEndTime');

        if (storedEndTime) {
            endTime = parseInt(storedEndTime, 10);
        } else {
            endTime = currentTime + 70000;
            localStorage.setItem('timerEndTime', endTime);
        }

        timerActive = true;

        timerId = setInterval(function() {
            var timeLeft = endTime - new Date().getTime();

            if (timeLeft <= 0) {
                clearInterval(timerId);
                timerActive = false;
                localStorage.removeItem('timerEndTime');
                localStorage.setItem('error', 'true');
                notif('error', 'Jangan lupa scan kanban!');
                forgetSound();

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        }, 1000);
    }

    function pauseTimer() {
        clearInterval(timerId);
        timerActive = false;
        localStorage.removeItem('timerEndTime');
    }

    function resetAndStartTimer() {
        pauseTimer();
        localStorage.removeItem('timerEndTime');
        startTimer();
    }

    function sendErrorLog(message = null, expected = null, scanned = null) {
        $.ajax({
            url: "{{ route('error.store') }}",
            type: "GET",
            data: {
                message: message,
                expected: expected,
                scanned: scanned
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                console.log("Error log sent successfully");
            },
            error: function(xhr, status, error) {
                console.error("Error while sending error log:", error);
            }
        });
    }

    $(document).ready(function() {
        initApp();

        document.getElementById('fullscreenBtn').addEventListener('click', function() {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        });

        $(document).on('click', function() {
            if (!$('#modalConfirmation').hasClass('show')) {
                $('#code').focus();
            }
        });

        // reset flag + fokus balik saat modal konfirmasi ditutup
        $('#modalConfirmation').on('hidden.bs.modal', function() {
            isConfirmationShown = false;
            $('#code').focus();
        });

        // MODAL CONFIRMATION (pakai keydown untuk Enter)
        $('#input-confirmation').on('keydown', function(e) {
            const key = (typeof e.key !== 'undefined' && e.key !== null) ?
                e.key :
                String.fromCharCode(e.which || e.keyCode || 0);

            const isEnter =
                key === 'Enter' ||
                e.which === 13 ||
                e.keyCode === 13;

            if (!isEnter) {
                return;
            }

            e.preventDefault();

            const barcodecomplete = $(this).val().trim();

            if (barcodecomplete.length === 6) {
                if (
                    barcodecomplete === '000448' ||
                    barcodecomplete === '002484' ||
                    barcodecomplete === '000040' ||
                    barcodecomplete === '000504'
                ) {
                    localStorage.removeItem('error');
                    localStorage.removeItem('dandori_error');
                    localStorage.removeItem('master_dandori_error');
                    localStorage.removeItem('kanban_exist_error');

                    $('#modalConfirmation').modal('hide');
                    notif('success', 'Selamat melanjutkan!');

                    $(this).val('');
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
            } else {
                $('#modalConfirmation').modal('hide');
                notif('error', 'Scan barcode NPK 6 digit');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        });

        $('#release').on('click', function() {
            $('#code').focus();
            localStorage.clear();
            window.location.reload();
        });

        $('#pause').on('click', function() {
            pauseTimer();
            notif('success', 'Timer telah berhenti!');
        });
    });
</script>

<script>
    $(document).ready(function() {
        (function() {
            const CSRF = "{{ csrf_token() }}";
            const CMD = {
                regular: "{{ url('/production') }}",
                logout: "{{ url('/logout') }}"
            };

            const $statusHdr = $('.status-card-header');
            const $status = $('.status-card');
            const $txtStatus = $('#status');
            const $modelTxt = $('#model');
            const $totScan = $('#total-scan');
            const $totPart = $('#total-part');
            const $pis = $('#pis');

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
                    setTimeout(() => setStatus('idle'), state === 'ok' ? 700 : 1000);
                }
            }

            const LS = {
                get: k => localStorage.getItem(k),
                set: (k, v) => localStorage.setItem(k, String(v)),
                mset: obj => Object.keys(obj).forEach(k => localStorage.setItem(k, String(obj[k]))),
            };

            // === UPDATE: getTarget aman (tidak NaN) ===
            function getTarget() {
                var raw = LS.get('target');
                var t = parseInt(raw, 10);
                return isNaN(t) ? 0 : t;
            }

            function api(url, method, data) {
                return $.ajax({
                    url,
                    method,
                    dataType: 'json',
                    data
                });
            }

            function parsePart26(s) {
                return {
                    program: s.slice(0, 2),
                    line: s.slice(2, 4),
                    hhmm: s.slice(4, 8),
                    operator: s.slice(8, 12),
                    dateDD: s.slice(12, 14),
                    monthMM: s.slice(14, 16),
                    yearYY: s.slice(16, 18),
                    shift: s.slice(18, 19),
                    shoot: s.slice(19, 22),
                    back4: s.slice(23, 27)
                };
            }

            const KANBAN_FORMATS = [{
                    len: 230,
                    internal: [41, 19],
                    seri: [123, 4],
                    back: [100, 4],
                    pcs: [196, 1]
                },
                {
                    len: 220,
                    internal: [35, 12],
                    seri: [130, 4],
                    back: [100, 4],
                    pcs: [196, 1]
                },
                {
                    len: 241,
                    internal: [35, 12],
                    seri: [127, 4],
                    back: [100, 4],
                    pcs: [196, 1]
                },
                {
                    len: 219,
                    internal: [42, 19],
                    back: [100, 5],
                    seri: [123, 4],
                    pcs: [196, 1]
                },
            ];

            function parseKanban(s) {
                const f = KANBAN_FORMATS.find(x => x.len === s.length);
                if (!f) return null;
                const sub = (a) => s.substr(a[0], a[1]).trim();
                return {
                    internal: sub(f.internal),
                    seri: sub(f.seri),
                    backNum: sub(f.back),
                    pcs: sub(f.pcs)
                };
            }

            function updateTotals(actual, target, partCounter) {
                $totScan.text(`${actual} / ${target}`);
                $totPart.text(partCounter);
            }

            // ==== SCAN HANDLER pakai keypress ====
            $('#code').keypress(function(e) {
                e.preventDefault();
                var code = (e.keyCode ? e.keyCode : e.which);

                if (code === 13) { // ENTER
                    const barcodecomplete = barcode.trim();
                    barcode = "";

                    if (!barcodecomplete) return;

                    // QUICK COMMANDS
                    if (barcodecomplete === 'regular') {
                        window.location.replace(CMD.regular);
                        return;
                    }
                    if (barcodecomplete === 'logout' || barcodecomplete.length === 13) {
                        window.location.replace(CMD.logout);
                        return;
                    }

                    // 1) DANDORI BOARD
                    if (barcodecomplete.endsWith('dandori')) {
                        LS.set('dandori_board', barcodecomplete.replace(/-dandori$/, ""));
                        LS.set('production_start_time', new Date().toLocaleString('sv-SE'));

                        notif("success", 'Berhasil scan dandori board!');
                        setStatus('ok');
                        return;
                    }

                    // WAJIB SUDAH PUNYA DANDORI
                    if (!LS.get('dandori_board')) {
                        dandoriSound();
                        notif("error", 'Scan dandori board terlebih dahulu!');
                        LS.set('dandori_error', 'true');
                        setStatus('ng');
                        sendErrorLog("Belum scan dandori board");
                        return;
                    }

                    // 2) MASTER / MODEL
                    if (barcodecomplete.endsWith('model')) {
                        const model = barcodecomplete.replace(/-model$/, "");
                        if (model !== LS.get('dandori_board')) {
                            masterDandoriSound();
                            notif("error", 'Master sample tidak sesuai dengan dandori board!');
                            LS.set('master_dandori_error', 'true');
                            setStatus('ng');
                            sendErrorLog('Master != Dandori', LS.get('dandori_board'), model);
                            return;
                        }

                        api(`{{ url('pulling/internal-check') }}/${model}`, 'GET', {
                                _token: CSRF
                            })
                            .done(dp => {
                                if (dp.status !== 'success') {
                                    notif('error', dp.message || 'Internal check gagal');
                                    return;
                                }

                                console.log(dp);

                                // === UPDATE: ambil target dengan fallback aman ===
                                var rawTarget = dp.target;
                                if (rawTarget === undefined || rawTarget === null) {
                                    if (dp.Target !== undefined && dp.Target !== null) {
                                        rawTarget = dp.Target;
                                    } else if (dp.target_qty !== undefined && dp.target_qty !==
                                        null) {
                                        rawTarget = dp.target_qty;
                                    } else if (dp.data && dp.data.target !== undefined && dp
                                        .data.target !== null) {
                                        rawTarget = dp.data.target;
                                    } else if (dp.data && dp.data.target_qty !== undefined && dp
                                        .data.target_qty !== null) {
                                        rawTarget = dp.data.target_qty;
                                    } else {
                                        rawTarget = 0;
                                    }
                                }

                                var tgt = parseInt(rawTarget, 10);
                                if (isNaN(tgt)) tgt = 0;

                                var partNumber = dp.partNumber || (dp.data && dp.data
                                    .partNumber) || '';
                                var backNumber = dp.backNumber || dp.back_no || dp.backNum || dp
                                    .back || (dp.data && dp.data.backNumber) || '';
                                var photo = dp.photo || (dp.data && dp.data.photo) || '';
                                var lineVal = dp.line || (dp.data && dp.data.line) || '';

                                LS.mset({
                                    target: tgt,
                                    model: partNumber,
                                    back_number: backNumber,
                                    photo: photo,
                                    line: lineVal,
                                    actual_scan: 0,
                                    scan_counter: 0,
                                    part_counter: 0
                                });

                                $('.model-card-header').removeClass('card-secondary').addClass(
                                    'card-info');
                                $('.model-card').removeClass('bg-secondary').addClass(
                                    'bg-info');
                                $('.total-scan-card-header, .total-part-card-header')
                                    .removeClass('card-secondary').addClass('card-success');
                                $('.total-scan-card, .total-part-card').removeClass(
                                    'bg-secondary').addClass('bg-success');

                                $modelTxt.text(backNumber || '-');
                                updateTotals(0, tgt, 0);
                                $pis.html(
                                    `<img src="{{ asset('assets/img/pis/${dp.photo}') }}" alt="PIS" class="rounded" height="700">`
                                );

                                // initApp() DIHILANGKAN supaya tidak override total-scan lagi
                                setStatus('ok');
                            })
                            .fail(xhr => {
                                if (xhr.status === 0) {
                                    notif('error', 'Connection Error');
                                    errConnection();
                                    return;
                                }
                                notif('error', xhr.responseJSON?.errors ||
                                    'Internal Server Error');
                            });

                        return;
                    }

                    // 3) PART 26 CHAR (alfanumerik)
                    if (barcodecomplete.length === 27) {
                        const model = LS.get('model');
                        const dandori = LS.get('dandori_board');
                        const backNo = (LS.get('back_number') || '').toUpperCase();
                        const line = LS.get('line');

                        if (!model || !dandori) {
                            wrongKanbanSound();
                            notif('error', 'Scan dandori & master/model dulu.');
                            return;
                        }
                        if (!line) {
                            notif('error', 'Line belum di-set');
                            return;
                        }
                        if (!backNo) {
                            wrongKanbanSound();
                            notif('error', 'Back number belum tersedia. Scan master/model ulang.');
                            setStatus('ng');
                            return;
                        }

                        const last4 = parsePart26(barcodecomplete).back4.toUpperCase();
                        const prefixBackNo = backNo.slice(0, 2); // 2 huruf awal SP, KP, dst.

                        // RULE BARU
                        // KMOU => SP
                        // KMOT => KP
                        const RULES = {
                            KMOU: 'SP',
                            KMOT: 'KP'
                        };

                        const expectedPrefix = RULES[last4];
                        const isValid = expectedPrefix && expectedPrefix === prefixBackNo;

                        // Jika tidak valid → error
                        if (!isValid) {
                            wrongKanbanSound();
                            notif('error', 'Barcode part tidak sesuai dengan BACK NUMBER!');
                            setStatus('ng');
                            return;
                        }

                        // Lolos validasi → lanjut simpan
                        api(`/production/part-scan`, 'POST', {
                                _token: CSRF,
                                line,
                                model,
                                dandori,
                                barcode: barcodecomplete
                            })
                            .done(res => {
                                if (res.status === 'duplicate') {
                                    alreadyScanSound();
                                    notif('error', 'Barcode part sudah pernah discan!');
                                    return;
                                }
                                if (res.status !== 'ok') {
                                    wrongKanbanSound();
                                    notif('error', res.message || 'Gagal simpan part');
                                    return;
                                }

                                const actual = parseInt(res.actual || 0, 10);
                                const tgt = getTarget();
                                const partCounter = parseInt(LS.get('part_counter') || '0',
                                    10) + 1;

                                LS.mset({
                                    actual_scan: actual,
                                    scan_counter: actual,
                                    part_counter: partCounter
                                });

                                updateTotals(actual, tgt, partCounter);
                                setStatus('ok');

                                if (actual >= tgt) {
                                    notif('success',
                                        'Target part tercapai. Silakan scan KANBAN untuk close batch.'
                                    );
                                }
                            })
                            .fail(xhr => {
                                if (xhr.status === 0) {
                                    notif('error', 'Connection Error');
                                    errConnection();
                                    return;
                                }
                                wrongKanbanSound();
                                notif('error', xhr.responseJSON?.message ||
                                    'Internal Server Error');
                            });

                        return;
                    }


                    // 4) KANBAN
                    const k = parseKanban(barcodecomplete);
                    if (!k) {
                        wrongKanbanSound();
                        notif('error', 'Format barcode tidak dikenali');
                        setStatus('ng');
                        return;
                    }

                    notif('success', LS.get('dandori_board').trim);
                    return;

                    if (LS.get('model') && LS.get('dandori_board')) {
                        if (LS.get('model') === k.internal && LS.get('dandori_board').trim === k
                            .internal) {
                            const line = LS.get('line');
                            const target = getTarget();
                            const actual = parseInt(LS.get('actual_scan') || '0', 10);

                            if (!line) {
                                notif('error', 'Line belum di-set');
                                return;
                            }

                            if (actual < target) {
                                notif('error',
                                    `Belum mencapai target (${actual} / ${target})`);
                                setStatus('ng');
                                return;
                            }

                            api(`{{ url('production/store/') }}`, 'GET', {
                                    _token: CSRF,
                                    partNumber: k.internal,
                                    seri: k.seri
                                })
                                .done(data => {
                                    if (data.status === 'success') {

                                        api(`/production/part-scan/assign-kanban`,
                                                'POST', {
                                                    _token: CSRF,
                                                    line,
                                                    model: LS.get('model'),
                                                    internal: k.internal,
                                                    seri: k.seri,
                                                    limit: target
                                                })
                                            .done(res => {
                                                if (res.status !== 'ok') {
                                                    wrongKanbanSound();
                                                    notif('error', res.message ||
                                                        'Gagal assign kanban');
                                                    return;
                                                }

                                                LS.mset({
                                                    actual_scan: 0,
                                                    scan_counter: 0,
                                                    part_counter: 0
                                                });

                                                notif('success',
                                                    `KANBAN tersimpan & ${res.assigned} part di-link ke Kanban #${res.kanban_id}. Counter di-reset.`
                                                );
                                                setStatus('ok');
                                                updateTotals(0, getTarget(), 0);
                                            })
                                            .fail(xhr => {
                                                if (xhr.status === 0) {
                                                    notif('error',
                                                        'Connection Error');
                                                    errConnection();
                                                    return;
                                                }
                                                wrongKanbanSound();
                                                notif('error', xhr.responseJSON
                                                    ?.message ||
                                                    'Assign kanban gagal');
                                            });

                                    } else if (data.status === 'kanbanExist') {
                                        alreadyScanSound();
                                        notif('error', data.message);
                                        LS.set('kanban_exist_error', 'true');
                                        sendErrorLog('Seri Kanban sudah discan!', LS
                                            .get(
                                                'dandori_board'), k.internal);
                                    } else {
                                        wrongKanbanSound();
                                        notif('error', data.message ||
                                            'Gagal simpan KANBAN');
                                        LS.set('error', 'true');
                                    }
                                })
                                .fail(xhr => {
                                    if (xhr.status === 0) {
                                        notif('error', 'Connection Error');
                                        errConnection();
                                        return;
                                    }
                                    notif('error', 'Internal Server Error');
                                });
                        } else {
                            wrongKanbanSound();
                            notif('error', 'Kanban tidak sesuai!');
                            LS.set('error', 'true');
                            setStatus('ng');
                            sendErrorLog('Kanban tidak sesuai!', LS.get('dandori_board'), k
                                .internal);
                        }
                    }

                } else {
                    // kumpulin karakter scanner
                    barcode = barcode + String.fromCharCode(code);
                }
            });
        })();
    });
</script>
