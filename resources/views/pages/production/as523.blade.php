@extends('layouts.root.auth')

@section('main')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="main-section">
        <div class="mx-5 my-5">
            <div class="row">
                <div class="col-lg-2 col-sm-12">
                    <div class="card card-warning py-3 shadow" style="padding: 1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark">Scan Part Number</label>
                        <input id="code" type="text" class="form-control" name="code" tabindex="1"
                            placeholder="scan part..." required autofocus autocomplete="off">
                    </div>
                    <div class="shadow pt-4 card card-secondary model-card-header"
                        style="margin-bottom:80px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px;">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Model Running</h5>
                            <div class="bg-secondary m-auto shadow model-card"
                                style="height: 7rem; width: 85%; border-radius: 6px; padding: 30px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="model">-</h1>
                            </div>
                        </div>
                    </div>
                    <div class="shadow pt-4 card card-secondary total-scan-card-header"
                        style="margin-bottom:80px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Total Scan</h5>
                            <div class="bg-secondary m-auto shadow total-scan-card"
                                style="height: 7rem; width: 85%; border-radius: 6px; padding: 30px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="total-scan">0</h1>
                            </div>
                        </div>
                    </div>
                    <div class="shadow pt-4 card card-secondary model-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px;">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark" id="texttime">Time / Box</h5>
                            <div class="bg-secondary m-auto shadow model-card"
                                style="height: 5rem; width: 85%; border-radius: 6px; padding: 15px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="time-per-box">00:00</h1>
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
                        <h3 class="text-center text-white">Stop</h3>
                    </button>
                    <button class="btn btn-danger py-3 px-5 shadow mb-2"
                        style="padding: 1rem; border-radius:8px; width:100% !important; font-size:2rem"
                        id="pause">Problem
                    </button>
                    <button class="btn btn-success py-3 px-5 shadow mb-2"
                        style="padding: 1rem; border-radius:8px; width:100% !important; font-size:2rem"
                        id="pauseSetup">Setup
                    </button>
                    <button class="btn btn-success py-3 px-5 shadow mb-4"
                        style="padding: 1rem; border-radius:8px; width:100% !important; font-size:2rem"
                        id="pauseQualityCheck">QC Cek
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
                        style="margin-bottom:130px;height: 5rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Total Part</h5>
                            <div class="bg-secondary m-auto shadow total-part-card"
                                style="height: 5rem; width: 85%; border-radius: 6px; padding: 15px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="total-part">0</h1>
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
            <div class="modal-content" id="divNotif" style="border-radius: 12px !important;">
                <div class="modal-body text-center">
                    <span style="color: white; font-size: 30pt" id="notif"> Error!</span>
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
                <div class="modal-header">
                </div>
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
    {{-- end of modal --}}

    {{-- Pause Modal (Problem Category) --}}
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
                                style="font-size: 1.5rem;" data-category="mesin">
                                Mesin
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button id="pauseQuality" class="btn btn-warning btn-lg w-100 py-4 btn-stop-category"
                                style="font-size: 1.5rem;" data-category="quality">
                                Quality
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button id="pauseSupply" class="btn btn-success btn-lg w-100 py-4 btn-stop-category"
                                style="font-size: 1.5rem;" data-category="supply">
                                Supply
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button id="openOtherProblem" class="btn btn-secodary btn-lg w-100 py-4 btn-stop-category"
                                style="font-size: 1.5rem;" data-category="others">
                                Others
                            </button>
                        </div>
                    </div>

                    <div class="mt-4" id="otherProblemSection">
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

    <audio id="not-match-sound">
        <source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg">
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

    {{-- tambahan dari code 2 (untuk target tercapai) --}}
    <audio id="fullfilled-sound">
        <source src={{ asset('assets/sounds/fullfilled.mp3') }} type="audio/mpeg">
    </audio>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    let line = '';
    var timerId;
    var timerActive = false;
    var endTime;
    let timePerBoxInterval;

    // ===== helper LS & target/actual (tambahan dari flow code 2) =====
    const LS = {
        get: (k) => localStorage.getItem(k),
        set: (k, v) => localStorage.setItem(k, String(v)),
        mset: (obj) => Object.keys(obj).forEach(k => localStorage.setItem(k, String(obj[k]))),
        del: (k) => localStorage.removeItem(k),
    };

    function getTarget() {
        const raw = LS.get('target');
        const t = parseInt(raw || '0', 10);
        return isNaN(t) ? 0 : t;
    }

    function getActual() {
        const raw = LS.get('actual_scan');
        const a = parseInt(raw || '0', 10);
        return isNaN(a) ? 0 : a;
    }

    function setScanProgressUI() {
        // total-scan di code1 tetap dipakai, tapi sekarang tampil "actual / target"
        const actual = getActual();
        const target = getTarget();
        $('#total-scan').text(`${actual} / ${target}`);
    }

    function startTimeCounter(startTimestamp = null) {
        clearInterval(timePerBoxInterval);

        const startTime = startTimestamp ? new Date(startTimestamp) : new Date();

        timePerBoxInterval = setInterval(() => {
            const now = new Date();
            const diffMs = now - startTime;

            const totalSeconds = Math.floor(diffMs / 1000);
            const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
            const seconds = (totalSeconds % 60).toString().padStart(2, '0');

            $('#time-per-box').text(`${minutes}:${seconds}`);
        }, 1000);
    }

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

    function fullfilledSound() {
        document.getElementById("fullfilled-sound").play();
    }

    function showModalConfirmation() {
        $('#modalConfirmation').on('shown.bs.modal', function() {
            $('#input-confirmation').focus();
        })
        $('#modalConfirmation').modal('show');

        $(document).on('click', function() {
            $('#input-confirmation').focus();
        })
    }

    function loopNotMatchSound() {
        if (localStorage.getItem('error') === 'true') {
            wrongKanbanSound();
            showModalConfirmation();
            setTimeout(loopNotMatchSound, 2000);
        }
    }

    function loopDandoriSound() {
        if (localStorage.getItem('dandori_error') === 'true') {
            dandoriSound();
            showModalConfirmation();
            setTimeout(loopDandoriSound, 2000);
        }
    }

    function loopMasterDandoriSound() {
        if (localStorage.getItem('master_dandori_error') === 'true') {
            masterDandoriSound();
            showModalConfirmation();
            setTimeout(loopMasterDandoriSound, 2000);
        }
    }

    function initApp() {
        let model = localStorage.getItem('model');
        let backNumber = localStorage.getItem('back_number');
        let totalPart = localStorage.getItem('part_counter');
        let photo = localStorage.getItem('photo');

        if (model || photo) {
            $('.model-card-header').removeClass('card-secondary').addClass('card-info');
            $('.model-card').removeClass('bg-secondary').addClass('bg-info');
            $('#model').text(backNumber)
            $('#pis').html(
                `<img src="{{ asset('assets/img/pis/${photo}') }}" alt="PIS" class="rounded" height="600">`);
        }

        // total scan sekarang progress actual/target (flow code2)
        $('.total-scan-card-header').removeClass('card-secondary').addClass('card-success');
        $('.total-scan-card').removeClass('bg-secondary').addClass('bg-success');
        setScanProgressUI();

        if (totalPart !== null) {
            $('.total-part-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-part-card').removeClass('bg-secondary').addClass('bg-success');
            $('#total-part').text(totalPart || 0)
        }

        loopNotMatchSound();
        loopDandoriSound();
        loopMasterDandoriSound();

        let lastScanTime = localStorage.getItem('last_kanban_time');
        if (lastScanTime) {
            startTimeCounter(parseInt(lastScanTime));
        }

        $('#code').focus();
    }

    function notif(color, text) {
        let textNotif = $('#notif');
        if (color == "error") {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#FF2A00");
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
        }
        $('#notifModal').modal('show');
        setTimeout(() => {
            $('#notifModal').modal('hide');
            $('#code').focus();
        }, 3000);
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
                message,
                expected,
                scanned
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        });
    }

    // ========= tambahan parsing PART (27 char) seperti code2 =========
    function parsePart27(s) {
        // mengikuti rule code2: ambil 4 char pada index 23..26
        return {
            last4: s.slice(23, 27)
        };
    }

    // ========= parsing kanban seperti code1 (tetap) =========
    function parseKanbanFromCode1(barcodecomplete) {
        let internal = null,
            seri = null,
            backNum = null,
            pcs = null;

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

        } else if (barcodecomplete.length == 219) {
            internal = barcodecomplete.substr(42, 17);
            seri = barcodecomplete.substr(123, 4);
            backNum = barcodecomplete.substr(100, 4);
            pcs = barcodecomplete.substr(196, 1);
        }

        if (!internal) return null;
        return {
            internal: (internal || '').trim(),
            seri: (seri || '').trim(),
            backNum: (backNum || '').trim(),
            pcs: (pcs || '').trim(),
        };
    }

    $(document).ready(function() {
        initApp();

        // =========================
        // UPDATE SCRIPT START (Problem/Setup/QC) - tetap dari code1
        // =========================
        let pauseStartTime = null;
        let selectedSrnaId = null;
        let pauseTimerInterval = null;
        let selectedCategory = null;

        $('.btn-stop-category').on('click', function() {
            selectedCategory = $(this).data('category');
            pauseStartTime = new Date();

            if (selectedCategory) {
                $('#otherProblemSection').slideDown();
                fetchStopReasons(selectedCategory);
            } else {
                $('#pauseModal').modal('hide');
                selectedSrnaId = selectedCategory;
                $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
                $('#texttime').text('Stop Time');

                $('.status-card').removeClass('bg-secondary').addClass('bg-danger');
                $('#status').text('Stop');

                startPauseTimer();
            }
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
            if ($('#pause').text() === 'Mulai') {
                const now = new Date();
                const pad = (n) => n.toString().padStart(2, '0');
                const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const fmtFull = (d) =>
                    `${fmt(d)} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

                const payload = {
                    data: [{
                        line_id: localStorage.getItem('line_prd') || "UNKNOWN",
                        prd_dt: fmt(now),
                        str_dt: fmtFull(pauseStartTime),
                        end_dt: fmtFull(now),
                        matnr: localStorage.getItem('model') || "UNKNOWN",
                        srna_id: selectedSrnaId,
                        crtby: "{{ auth()->user()->npk }}"
                    }]
                };

                $.ajax({
                    url: '/production/api-insert-stop',
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify(payload),
                    success: function() {
                        notif('success', 'Stop reason berhasil dikirim');
                        $('#pause').text('Pause').removeClass('btn-success').addClass(
                            'btn-danger');
                        $('#texttime').text('Time / Box');
                        $('.status-card').removeClass('bg-danger bg-success').addClass(
                            'bg-secondary');
                        $('#status').text('-');

                        pauseStartTime = null;
                        selectedSrnaId = null;
                        stopAllTimers();
                        $('#code').focus();
                        startTimeCounter(now);
                    },
                    error: function() {
                        notif('error', 'Gagal kirim stop reason');
                    }
                });
            } else {
                stopAllTimers();
                $('#pauseModal').modal('show');
            }
        });

        $('#pauseSetup').on('click', function() {
            pauseStartTime = new Date();
            selectedSrnaId = 'SETUP';
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');

            $('.status-card').removeClass('bg-secondary bg-success').addClass('bg-danger');
            $('#status').text('Stop');

            startPauseTimer();
        });

        $('#pauseQualityCheck').on('click', function() {
            pauseStartTime = new Date();
            selectedSrnaId = 'QCCEK';
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');

            $('.status-card').removeClass('bg-secondary bg-success').addClass('bg-danger');
            $('#status').text('Stop');

            startPauseTimer();
        });

        $('#openOtherProblem').on('click', function(e) {
            e.stopPropagation();
            $('#otherProblemSection').slideDown();
            $('#pauseModal').off('click.dismiss.bs.modal');
        });

        $('#stopReason').on('click', function(e) {
            e.stopPropagation();
        });

        $('#pauseOther').on('click', function() {
            let selected = $('#stopReason').val();
            if (!selected) return alert("Pilih alasan terlebih dahulu!");

            selectedSrnaId = selected;
            pauseStartTime = new Date();
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');

            $('.status-card').removeClass('bg-secondary bg-success').addClass('bg-danger');
            $('#status').text('Stop');
            startPauseTimer();
        });

        function fetchStopReasons(category) {
            let line = localStorage.getItem('line_prd') || "DEFAULT";
            $.ajax({
                url: `/production/api-list-stop/${line}/${category}`,
                method: 'GET',
                success: function(response) {
                    if (response.status) {
                        let options = '<option value="">-- Pilih Masalah --</option>';
                        response.data.forEach(function(item) {
                            options +=
                                `<option value="${item.srna_id}">${item.name1} (${item.type2_text})</option>`;
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
        // =========================
        // UPDATE SCRIPT END
        // =========================

        document.getElementById('fullscreenBtn').addEventListener('click', function() {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) document.documentElement
                    .requestFullscreen();
                else if (document.documentElement.mozRequestFullScreen) document.documentElement
                    .mozRequestFullScreen();
                else if (document.documentElement.webkitRequestFullscreen) document.documentElement
                    .webkitRequestFullscreen();
                else if (document.documentElement.msRequestFullscreen) document.documentElement
                    .msRequestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                else if (document.msExitFullscreen) document.msExitFullscreen();
            }
        });

        $(document).on('click', function() {
            $('#code').focus();
        });

        // ===== confirmation modal scan (tetap code1) =====
        var barcodeConfirm = "";
        $('#input-confirmation').keypress(function(e) {
            e.preventDefault();
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                const barcodecomplete = barcodeConfirm;
                barcodeConfirm = "";

                if (barcodecomplete == "prdreport") {
                    window.location.replace("{{ url('/production') }}");
                    return;
                }
                if (barcodecomplete == "logout") {
                    window.location.replace("{{ url('/logout') }}");
                    return;
                }
                if (barcodecomplete.length == 13) {
                    window.location.replace("{{ url('/logout') }}");
                    return;
                }

                if (barcodecomplete.length === 6) {
                    if (barcodecomplete == '000448' || barcodecomplete == '002484' || barcodecomplete ==
                        '000040' || barcodecomplete == '000504') {
                        localStorage.removeItem('error');
                        localStorage.removeItem('dandori_error');
                        localStorage.removeItem('master_dandori_error');
                        localStorage.removeItem('kanban_exist_error');
                        $('#modalConfirmation').modal('hide');
                        notif('success', 'Selamat melanjutkan!');
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                    } else {
                        $('#modalConfirmation').modal('hide');
                        notif('error', `NPK ${barcodecomplete} tidak memiliki hak akses`);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    $('#modalConfirmation').modal('hide');
                    notif('error', 'Scan barcode NPK');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                barcodeConfirm = barcodeConfirm + String.fromCharCode(e.which);
            }
        });

        // ===== stop/release (tetap code1) =====
        $('#release').on('click', function() {
            $('#code').focus();

            let part = localStorage.getItem('dandori_board');

            $.ajax({
                url: '/production/api-stop',
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify(part),
                success: function() {
                    notif('success', 'Stop reason berhasil dikirim saat Release');
                    localStorage.removeItem('production_start_time');
                    localStorage.clear();
                    setInterval(() => {
                        window.location.reload();
                    }, 2000);
                },
                error: function() {
                    notif('error', 'Gagal kirim stop reason saat Release');
                }
            });
        });

        // =========================
        // SCAN UTAMA (mengikuti flow code2, tapi tetap pertahankan logic code1)
        // =========================
        var barcode = "";

        $('#code').on('keypress', async function(e) {
            e.preventDefault();
            var key = (e.keyCode ? e.keyCode : e.which);

            if (key == 13) {
                const barcodecomplete = barcode.trim();
                barcode = "";

                if (!barcodecomplete) return;

                // quick command tetap
                if (barcodecomplete == "AS523") {
                    window.location.replace("{{ url('/production/as523') }}");
                    return;
                }
                if (barcodecomplete == "prdreport") {
                    window.location.replace("{{ url('/production') }}");
                    return;
                }
                if (barcodecomplete.length == 13 || barcodecomplete == "logout") {
                    window.location.replace("{{ url('/logout') }}");
                    return;
                }

                // 1) DANDORI
                if (barcodecomplete.endsWith('dandori')) {
                    const cleaned = barcodecomplete
                        .replace(/-dandori$/i, '')
                        .replace(/[\u00A0\u200B-\u200D\uFEFF]/g, '') // buang NBSP & zero-width
                        .replace(/^[\s\u00A0\u200B-\u200D\uFEFF]+/,
                            '') // buang whitespace di depan (super)
                        .replace(/[\s\u00A0\u200B-\u200D\uFEFF]+$/,
                            '') // buang whitespace di belakang (super)
                        .trim();

                    localStorage.setItem('dandori_board', cleaned);
                    // lock start time produksi hanya saat dandori (sesuai kebutuhan kamu sebelumnya)
                    localStorage.setItem('production_start_time', new Date().toLocaleString(
                        'sv-SE'));

                    notif("success", 'Berhasil scan dandori board!');
                    $('.status-card-header').removeClass('card-secondary card-danger').addClass(
                        'card-success');
                    $('.status-card').removeClass('bg-secondary bg-danger').addClass('bg-success');
                    $('#status').text('OK');

                    setTimeout(() => {
                        $('.status-card').removeClass('bg-success').addClass(
                            'bg-secondary');
                        $('#status').text('-');
                    }, 2000);

                    return;
                }

                // wajib dandori dulu
                if (!localStorage.getItem('dandori_board')) {
                    dandoriSound();
                    notif("error", 'Scan dandori board terlebih dahulu!');

                    $('.status-card-header').removeClass('card-secondary card-success').addClass(
                        'card-danger');
                    $('.status-card').removeClass('bg-secondary bg-success').addClass('bg-danger');
                    $('#status').text('NG');

                    localStorage.setItem('dandori_error', 'true');
                    sendErrorLog("Belum scan dandori board");

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return;
                }

                // 2) MASTER / MODEL
                if (localStorage.getItem('dandori_board') && barcodecomplete.endsWith('model')) {
                    const model = barcodecomplete.replace(/-model$/, "");
                    let now = Date.now();

                    if (model == localStorage.getItem('dandori_board')) {
                        // reset scan progress (flow code2)
                        LS.mset({
                            actual_scan: 0,
                            scan_counter: 0,
                        });
                        setScanProgressUI();
                        localStorage.removeItem('last_kanban_time');

                        $.ajax({
                            type: 'GET',
                            url: "{{ url('pulling/internal-check') }}" + '/' + model,
                            _token: "{{ csrf_token() }}",
                            dataType: 'json',
                            success: function(dataPart) {
                                if (dataPart.status == 'success') {

                                    // ambil target (bisa beda struktur, jadi kita amankan beberapa kemungkinan)
                                    const rawTarget = dataPart.target ?? dataPart
                                        .Target ?? dataPart.target_qty ??
                                        (dataPart.data && (dataPart.data.target ??
                                            dataPart.data.target_qty)) ?? 0;
                                    const tgt = parseInt(rawTarget || 0, 10) || 0;

                                    // simpan model info
                                    localStorage.setItem('model', dataPart.partNumber);
                                    localStorage.setItem('back_number', dataPart
                                        .backNumber);
                                    localStorage.setItem('photo', dataPart.photo);
                                    localStorage.setItem('line_prd', dataPart.line);

                                    // simpan target untuk flow scan part
                                    LS.set('target', tgt);

                                    // total part tetap dihitung total historis
                                    if (!LS.get('part_counter')) LS.set('part_counter',
                                        0);

                                    // display model running
                                    $('.model-card-header').removeClass(
                                        'card-secondary').addClass('card-info');
                                    $('.model-card').removeClass('bg-secondary')
                                        .addClass('bg-info');

                                    // cards scan/part
                                    $('.total-scan-card-header').removeClass(
                                        'card-secondary').addClass('card-success');
                                    $('.total-scan-card').removeClass('bg-secondary')
                                        .addClass('bg-success');

                                    $('.total-part-card-header').removeClass(
                                        'card-secondary').addClass('card-success');
                                    $('.total-part-card').removeClass('bg-secondary')
                                        .addClass('bg-success');

                                    $('#model').text(dataPart.backNumber);
                                    setScanProgressUI();
                                    $('#total-part').text(LS.get('part_counter') || 0);

                                    startTimeCounter(now);

                                    $('#pis').html(
                                        `<img src="{{ asset('assets/img/pis/${dataPart.photo}') }}" alt="PIS" class="rounded" height="700">`
                                    );

                                    notif('success', `Master OK. Target: ${tgt}`);
                                } else {
                                    notif('error', dataPart.message);
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status == 0) {
                                    notif("error", 'Connection Error');
                                    errConnection();
                                    return;
                                }
                                notif("error", xhr.responseJSON?.errors ||
                                    'Internal Server Error');
                            }
                        });
                    } else {
                        masterDandoriSound();
                        notif("error", 'Master sample tidak sesuai dengan dandori board!');

                        $('.status-card-header').removeClass('card-secondary card-success')
                            .addClass('card-danger');
                        $('.status-card').removeClass('bg-secondary bg-success').addClass(
                            'bg-danger');
                        $('#status').text('NG');

                        localStorage.setItem('master_dandori_error', 'true');
                        sendErrorLog('Master sample tidak sesuai dengan dandori board!',
                            localStorage.getItem('dandori_board'), model);

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return;
                    }

                    return;
                }

                // wajib sudah scan master dulu (model harus ada)
                if (!localStorage.getItem('model')) {
                    wrongKanbanSound();
                    notif('error', 'Scan master sample (model) terlebih dahulu!');
                    return;
                }

                // 3) PART SCAN (27 char) - fitur baru (dari code2)
                if (barcodecomplete.length === 27) {
                    const model = LS.get('model');
                    const dandori = LS.get('dandori_board');
                    const backNo = (LS.get('back_number') || '').toUpperCase();
                    const linePrd = LS.get('line_prd');

                    if (!model || !dandori) {
                        wrongKanbanSound();
                        notif('error', 'Scan dandori & master/model dulu.');
                        return;
                    }
                    if (!linePrd) {
                        notif('error', 'Line belum di-set. Scan master/model ulang.');
                        return;
                    }
                    if (!backNo) {
                        wrongKanbanSound();
                        notif('error', 'Back number belum tersedia. Scan master/model ulang.');
                        return;
                    }

                    const tgt = getTarget();
                    const actualNow = getActual();

                    // stop kalau target sudah tercapai (harus scan KANBAN untuk close batch)
                    if (tgt > 0 && actualNow >= tgt) {
                        fullfilledSound();
                        notif('error',
                            `Target sudah tercapai (${actualNow} / ${tgt}). Scan KANBAN untuk close batch.`
                        );
                        return;
                    }

                    // rule validasi part seperti code2 (mapping prefix)
                    const last4 = parsePart27(barcodecomplete).last4.toUpperCase();
                    const prefixBackNo = backNo.slice(0, 2);

                    const RULES = {
                        KMOU: 'SP',
                        KMOT: 'KP'
                    };
                    const expectedPrefix = RULES[last4];
                    const isValid = expectedPrefix && expectedPrefix === prefixBackNo;

                    if (!isValid) {
                        wrongKanbanSound();
                        notif('error', 'Barcode part tidak sesuai dengan BACK NUMBER!');
                        $('.status-card-header').removeClass('card-secondary card-success')
                            .addClass('card-danger');
                        $('.status-card').removeClass('bg-secondary bg-success').addClass(
                            'bg-danger');
                        $('#status').text('NG');
                        setTimeout(() => {
                            $('.status-card').removeClass('bg-danger').addClass(
                                'bg-secondary');
                            $('#status').text('-');
                        }, 2000);
                        return;
                    }

                    // simpan part scan ke server
                    $.ajax({
                        url: '/production/part-scan',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            line: linePrd,
                            model: model,
                            dandori: dandori,
                            barcode: barcodecomplete
                        },
                        success: function(res) {
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

                            okSound();

                            const actual = parseInt(res.actual || (getActual() + 1),
                                10) || (getActual() + 1);
                            const target = getTarget();

                            // update local
                            const partCounter = (parseInt(LS.get('part_counter') || '0',
                                10) || 0) + 1;
                            LS.mset({
                                actual_scan: actual,
                                scan_counter: actual,
                                part_counter: partCounter
                            });

                            setScanProgressUI();
                            $('#total-part').text(partCounter);

                            $('.status-card-header').removeClass(
                                'card-secondary card-danger').addClass(
                                'card-success');
                            $('.status-card').removeClass('bg-secondary bg-danger')
                                .addClass('bg-success');
                            $('#status').text('OK');
                            setTimeout(() => {
                                $('.status-card').removeClass('bg-success')
                                    .addClass('bg-secondary');
                                $('#status').text('-');
                            }, 1200);

                            if (target > 0 && actual >= target) {
                                fullfilledSound();
                                notif('success',
                                    `Target tercapai (${actual} / ${target}). Silakan scan KANBAN untuk close batch.`
                                );
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status == 0) {
                                notif('error', 'Connection Error');
                                errConnection();
                                return;
                            }
                            wrongKanbanSound();
                            notif('error', xhr.responseJSON?.message ||
                                'Internal Server Error');
                        }
                    });

                    return;
                }

                // 4) KANBAN SCAN (hanya boleh kalau target tercapai)
                const k = parseKanbanFromCode1(barcodecomplete);
                // notif('info', barcodecomplete.length);
                // return;

                // (tetap support branch code1 yang panjangnya 100-180 atau 80 untuk lookup internal)
                // tapi itu sebetulnya kanban/QR lain - kita biarkan jalan seperti code1: set LS.internal via api-get-internal
                if (!k && ((barcodecomplete.length >= 100 && barcodecomplete.length <= 180) ||
                        barcodecomplete.length == 80)) {
                    // logic asli code1 (ambil partNumber dari QR lalu /api-get-internal)
                    const qr = barcodecomplete.trim();

                    let partNumber = 'UNKNOWN';
                    if (barcodecomplete.length == 80) {
                        const parts = qr.split('|');
                        partNumber = parts[3] || 'UNKNOWN';
                    } else {
                        const parts = qr.split(';');
                        partNumber = parts[4]?.split('-').slice(0, 2).join('-') || 'UNKNOWN';
                    }

                    try {
                        await $.ajax({
                            url: '/production/api-get-internal/' + partNumber,
                            method: 'GET',
                            contentType: 'application/json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                localStorage.setItem('internal', data.data.internal_part
                                    .part_number);
                                notif('success', 'Internal OK, lanjut scan KANBAN');
                            }
                        });
                    } catch (err) {
                        notif('error', 'Gagal memeriksa kode.');
                    }
                    return;
                }

                if (!k) {
                    wrongKanbanSound();
                    notif('error', 'Format barcode tidak dikenali');
                    localStorage.setItem('error', 'true');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                    return;
                }

                // pastikan kanban sesuai model
                const modelLS = (LS.get('model') || '').trim();
                const dandoriLS = (LS.get('dandori_board') || '').trim();

                // (code1 fallback internal dari LS.internal)
                let internal = k.internal;
                if (!internal) internal = (LS.get('internal') || '').trim();

                if (modelLS !== internal.trim() || dandoriLS !== internal.trim()) {
                    // notif('error', dandoriLS);
                    // return;
                    notif('error', 'Kanban tidak sesuai!');
                    wrongKanbanSound();

                    $('.status-card-header').removeClass('card-secondary card-success').addClass(
                        'card-danger');
                    $('.status-card').removeClass('bg-secondary bg-success').addClass('bg-danger');
                    $('#status').text('NG');

                    localStorage.setItem('error', 'true');
                    sendErrorLog('Kanban tidak sesuai!', LS.get('dandori_board'), internal.trim());

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return;
                }

                // gate: harus target tercapai dulu
                const tgt = getTarget();
                const actual = getActual();
                if (tgt > 0 && actual < tgt) {
                    notif('error', `Belum mencapai target (${actual} / ${tgt}). Scan PART dulu.`);
                    wrongKanbanSound();
                    return;
                }

                // kalau target 0 (tidak ada target), tetap boleh close batch (optional)
                let now = Date.now();
                let startTime = localStorage.getItem('production_start_time');

                // simpan last kanban time untuk timer Time/Box
                localStorage.setItem('last_kanban_time', now);

                // 4a) simpan produksi (gunakan store2 dari code1 supaya tetap jalan logic existing)
                $.ajax({
                    type: 'get',
                    url: "{{ url('production/store2/') }}",
                    _token: "{{ csrf_token() }}",
                    data: {
                        partNumber: internal.trim(),
                        seri: k.seri,
                        start_time: startTime,
                        end_time: new Date().toLocaleString('sv-SE')
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.status == 'success') {

                            // 4b) assign-kanban untuk mengikat part scan ke kanban (flow code2)
                            $.ajax({
                                url: `/production/part-scan/assign-kanban`,
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr(
                                        'content'),
                                    line: LS.get('line_prd'),
                                    model: LS.get('model'),
                                    internal: internal.trim(),
                                    seri: k.seri,
                                    limit: tgt
                                },
                                success: function(res) {
                                    if (res.status !== 'ok') {
                                        wrongKanbanSound();
                                        notif('error', res.message ||
                                            'Gagal assign kanban');
                                        return;
                                    }

                                    okSound();

                                    // reset progress scan part (actual/scan_counter), total part tetap
                                    LS.mset({
                                        actual_scan: 0,
                                        scan_counter: 0
                                    });
                                    setScanProgressUI();

                                    localStorage.removeItem('error');
                                    localStorage.removeItem(
                                        'kanban_exist_error');
                                    localStorage.removeItem(
                                        'dandori_error');
                                    localStorage.removeItem(
                                        'master_dandori_error');
                                    localStorage.removeItem('internal');

                                    // status OK
                                    $('.status-card-header').removeClass(
                                            'card-secondary card-danger')
                                        .addClass('card-success');
                                    $('.status-card').removeClass(
                                            'bg-secondary bg-danger')
                                        .addClass('bg-success');
                                    $('#status').text('OK');

                                    startTimeCounter(now);

                                    setTimeout(() => {
                                        $('.status-card')
                                            .removeClass(
                                                'bg-success')
                                            .addClass(
                                                'bg-secondary');
                                        $('#status').text('-');
                                    }, 2000);

                                    notif('success',
                                        `OK - Batch closed (${res.assigned || tgt || 0} pcs)`
                                    );
                                },
                                error: function(xhr) {
                                    if (xhr.status == 0) {
                                        notif('error', 'Connection Error');
                                        errConnection();
                                        return;
                                    }
                                    wrongKanbanSound();
                                    notif('error', xhr.responseJSON
                                        ?.message ||
                                        'Assign kanban gagal');
                                }
                            });

                        } else if (data.status === 'kanbanExist') {
                            alreadyScanSound();
                            notif('error', data.message ||
                                'Kanban sudah pernah discan!');
                            LS.set('kanban_exist_error', 'true');
                            sendErrorLog('Seri Kanban sudah discan!', LS.get(
                                'dandori_board'), internal.trim());
                        } else {
                            notif("error", data.message || 'Gagal simpan KANBAN');
                            wrongKanbanSound();
                            localStorage.setItem('error', 'true');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status == 0) {
                            notif("error", 'Connection Error');
                            errConnection();
                            return;
                        }
                        notif("error", 'Internal Server Error');
                    }
                });

                return;

            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }
        });

    });
</script>
