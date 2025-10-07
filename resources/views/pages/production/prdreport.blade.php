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
                        style="padding: 1rem; border-radius:8px; width:100% !important; font-size:2rem" id="pause">Problem
                    </button>
                    <button class="btn btn-success py-3 px-5 shadow mb-2"
                        style="padding: 1rem; border-radius:8px; width:100% !important; font-size:2rem" id="pauseSetup">Setup
                    </button>
                    <button class="btn btn-success py-3 px-5 shadow mb-4"
                        style="padding: 1rem; border-radius:8px; width:100% !important; font-size:2rem" id="pauseQualityCheck">QC Cek
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
    <div class="modal fade" id="pauseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 12px">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">Pilih Jenis Masalah</h5>
            </div>
            <div class="modal-body">
                <div class="row text-center">
                <div class="col-md-3">
                    <button id="pauseMachine" class="btn btn-danger btn-lg w-100 py-4 btn-stop-category" style="font-size: 1.5rem;" data-category="mesin">
                    Mesin
                    </button>
                </div>
                <div class="col-md-3">
                    <button id="pauseQuality" class="btn btn-warning btn-lg w-100 py-4 btn-stop-category" style="font-size: 1.5rem;" data-category="quality">
                    Quality
                    </button>
                </div>
                <div class="col-md-3">
                    <button id="pauseSupply" class="btn btn-success btn-lg w-100 py-4 btn-stop-category" style="font-size: 1.5rem;" data-category="supply">
                    Supply
                    </button>
                </div>
                <div class="col-md-3">
                    <button id="openOtherProblem" class="btn btn-secodary btn-lg w-100 py-4 btn-stop-category" style="font-size: 1.5rem;" data-category="others">
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
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>

    <audio id="already-scan-sound">
        <source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>

    <audio id="forget-sound">
        <source src={{ asset('assets/sounds/forget.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>

    <audio id="match-sound">
        <source src={{ asset('assets/sounds/match.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>

    <audio id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="error-connection">
        <source src={{ asset('assets/sounds/errConnection.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="dandori-ng-sound">
        <source src={{ asset('assets/sounds/dandori_error.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="master-dandori-ng-sound">
        <source src={{ asset('assets/sounds/master_dandori_error.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="wrong-kanban-sound">
        <source src={{ asset('assets/sounds/wrongKanban.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
@endsection
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    let line = '';
    var timerId;
    var timerActive = false;
    var endTime; // Time when the timer is supposed to end
    let timePerBoxInterval;

    function startTimeCounter(startTimestamp = null) {
        clearInterval(timePerBoxInterval); // stop existing interval

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

    function wrongKanbanSound() {
        var sound = document.getElementById("wrong-kanban-sound");
        sound.play();
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
            wrongKanbanSound(); // Putar suara
            showModalConfirmation();
            setTimeout(loopNotMatchSound, 2000); // Loop setiap 2 detik
        }
    }

    function loopDandoriSound() {
        if (localStorage.getItem('dandori_error') === 'true') {
            dandoriSound(); // Putar suara
            showModalConfirmation();
            setTimeout(loopDandoriSound, 2000); // Loop setiap 2 detik
        }
    }

    function loopMasterDandoriSound() {
        if (localStorage.getItem('master_dandori_error') === 'true') {
            masterDandoriSound(); // Putar suara
            showModalConfirmation();
            setTimeout(loopMasterDandoriSound, 2000); // Loop setiap 2 detik
        }
    }

    function initApp() {
        let model = localStorage.getItem('model');
        let backNumber = localStorage.getItem('back_number');
        let totalScan = localStorage.getItem('scan_counter');
        let totalPart = localStorage.getItem('part_counter');
        let startTime = localStorage.getItem('start_time');
        let photo = localStorage.getItem('photo');
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
        let lastScanTime = localStorage.getItem('last_kanban_time');
        if (lastScanTime) {
            startTimeCounter(parseInt(lastScanTime));
        }

        $('#code').focus();
    }

    function notif(color, text) {
        let modal = $('#notifModal');
        let textNotif = $('#notif');
        if (color == "error") {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#FF2A00");
            $('#notifModal').modal('show');
            setTimeout(() => {
                $('#notifModal').modal('hide');
                $('#code').focus();
            }, 3000);
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
            $('#notifModal').modal('show');
            setTimeout(() => {
                $('#notifModal').modal('hide');
                $('#code').focus();
            }, 3000);
        }
    }

    // extract the master sample from counter
    function extractMasterSample(key) {
        const prefix = "counter_";
        return key.substring(prefix.length);
    }

    // retrieve the loading list number from localStorage
    function getMasterSample() {
        let masterSample = false;
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("counter_")) {
                masterSample = extractMasterSample(key);
            }
        }
        // Return a default value if no loading list number is found
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
        if (timerActive) {
            return; // Exit if the timer is already running
        }

        var currentTime = new Date().getTime();
        var storedEndTime = localStorage.getItem('timerEndTime');

        if (storedEndTime) {
            endTime = parseInt(storedEndTime, 10);
        } else {
            // Set new end time (60 seconds from now)
            endTime = currentTime + 70000;
            localStorage.setItem('timerEndTime', endTime);
        }

        timerActive = true;

        timerId = setInterval(function() {
            var timeLeft = endTime - new Date().getTime();

            if (timeLeft <= 0) {
                clearInterval(timerId);
                timerActive = false;
                localStorage.removeItem('timerEndTime'); // Clear the stored end time
                localStorage.setItem('error', 'true');
                notif('error', 'Jangan lupa scan kanban!');

                // notification sound
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
        localStorage.removeItem('timerEndTime'); // Clear any existing end time
        startTimer(); // Start a new timer
    }

    function sendErrorLog(message = null, expected = null, scanned = null) {
        $.ajax({
            url: "{{ route('error.store') }}",
            type: "GET", // Ganti ke POST jika kamu ubah routenya
            data: {
                message: message,
                expected: expected,
                scanned: scanned
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log("Error log sent successfully");
            },
            error: function(xhr, status, error) {
                console.error("Error while sending error log:", error);
            }
        });
    }


    $(document).ready(function() {
        initApp();
        // UPDATE SCRIPT START

        let pauseStartTime = null;
        let selectedSrnaId = null;
        let pauseTimerInterval = null;
        let selectedCategory = null;

        $('.btn-stop-category').on('click', function () {
            selectedCategory = $(this).data('category');
            pauseStartTime = new Date();

            if (selectedCategory) {
                console.log(selectedCategory);
                $('#otherProblemSection').slideDown();
                fetchStopReasons(selectedCategory); // fetch alasan kategori OTHERS
            } else {
                $('#pauseModal').modal('hide');
                selectedSrnaId = selectedCategory; // misalnya langsung gunakan kategori sebagai ID jika tidak pakai dropdown
                $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
                $('#texttime').text('Stop Time');

                $('.status-card').removeClass('bg-secondary').addClass('bg-danger');
                $('#status').text('Stop');

                startPauseTimer();
            }
        });


        function startPauseTimer() {
            clearInterval(pauseTimerInterval);
            clearInterval(timePerBoxInterval); // Stop other timer if active

            const startTime = pauseStartTime || new Date(); // Gunakan waktu pauseStartTime yang sudah dicatat
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

        $('#pause').on('click', function () {
            if ($('#pause').text() === 'Mulai') {
                const now = new Date();
                const pad = (n) => n.toString().padStart(2, '0');
                const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const fmtFull = (d) => `${fmt(d)} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

                const payload = {
                    data: [{
                        line_id: localStorage.getItem('line_prd') || "UNKNOWN",
                        prd_dt: fmt(now),
                        str_dt: fmtFull(pauseStartTime),
                        // end_dt: '',
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
                    success: function () {
                        notif('success', 'Stop reason berhasil dikirim');
                        $('#pause').text('Pause').removeClass('btn-success').addClass('btn-danger');
                        $('#texttime').text('Time / Box');
                        $('.status-card').removeClass('bg-danger');
                        $('.status-card').removeClass('bg-success');
                        $('.status-card').addClass(
                            'bg-secondary');
                        $('#status').text('-');

                        pauseStartTime = null;
                        selectedSrnaId = null;
                        stopAllTimers();
                        $('#code').focus();
                        startTimeCounter(now);
                    },
                    error: function () {
                        notif('error', 'Gagal kirim stop reason');
                    }
                });
            } else {
                stopAllTimers();
                $('#pauseModal').modal('show');
                
            }
        });

        $('#pauseSetup').on('click', function () {
            pauseStartTime = new Date();
            selectedSrnaId = 'SETUP'; // Reset selected stop reason ID
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');

            $('.status-card').removeClass('bg-secondary');
            $('.status-card').removeClass('bg-success');
            $('.status-card').addClass(
                'bg-danger');
            $('#status').text('Stop');
            
            startPauseTimer();
        });

        $('#pauseQualityCheck').on('click', function () {
            pauseStartTime = new Date();
            selectedSrnaId = 'QCCEK'; // Reset selected stop reason ID
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');

            $('.status-card').removeClass('bg-secondary');
            $('.status-card').removeClass('bg-success');
            $('.status-card').addClass(
                'bg-danger');
            $('#status').text('Stop');
            
            startPauseTimer();
        });

        $('#openOtherProblem').on('click', function (e) {
            e.stopPropagation();
            $('#otherProblemSection').slideDown();
            $('#pauseModal').off('click.dismiss.bs.modal');
        });

        $('#stopReason').on('click', function (e) {
            e.stopPropagation();
        });

        $('#pauseOther').on('click', function () {
            let selected = $('#stopReason').val();
            if (!selected) return alert("Pilih alasan terlebih dahulu!");

            selectedSrnaId = selected;
            pauseStartTime = new Date();
            $('#pauseModal').modal('hide');
            $('#pause').text('Mulai').removeClass('btn-danger').addClass('btn-success');
            $('#texttime').text('Stop Time');

            $('.status-card').removeClass('bg-secondary');
            $('.status-card').removeClass('bg-success');
            $('.status-card').addClass(
                'bg-danger');
            $('#status').text('Stop');
            startPauseTimer();
        });

        function fetchStopReasons(category) {
            let line = localStorage.getItem('line_prd') || "DEFAULT";
            $.ajax({
                url: `/production/api-list-stop/${line}/${category}`,
                method: 'GET',
                success: function (response) {
                    if (response.status) {
                        let options = '<option value="">-- Pilih Masalah --</option>';
                        response.data.forEach(function (item) {
                            options += `<option value="${item.srna_id}">${item.name1} (${item.type2_text})</option>`;
                        });
                        $('#stopReason').html(options);
                    } else {
                        $('#stopReason').html('<option value="">Tidak ada data</option>');
                    }
                },
                error: function () {
                    $('#stopReason').html('<option value="">Gagal Load</option>');
                }
            });
        }

    // UPDATE SCRIPT END

        document.getElementById('fullscreenBtn').addEventListener('click', function() {
            if (!document.fullscreenElement) {
                // Request fullscreen
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    /* Firefox */
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    /* Chrome, Safari & Opera */
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    /* IE/Edge */
                    document.documentElement.msRequestFullscreen();
                }
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    /* Firefox */
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    /* Chrome, Safari and Opera */
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    /* IE/Edge */
                    document.msExitFullscreen();
                }
            }
        });

        $(document).on('click', function() {
            $('#code').focus();
        })

        $('#input-confirmation').keypress(function(e) {
            e.preventDefault();
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                barcodecomplete = barcode;
                barcode = "";

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
                barcode = barcode + String.fromCharCode(e.which);
            }
        });

        $('#release').on('click', function() {
            $('#code').focus();

            let part = localStorage.getItem('dandori_board')

            $.ajax({
                url: '/production/api-stop',
                method: 'POST',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify(part),
                success: function () {
                    notif('success', 'Stop reason berhasil dikirim saat Release');
                    localStorage.removeItem('production_start_time');
                    localStorage.clear();
                    setInterval(() => {
                        window.location.reload();
                    }, 2000);
                },
                error: function () {
                    notif('error', 'Gagal kirim stop reason saat Release');
                }
            });

        });

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        $('#code').keypress(function(e) {
            e.preventDefault();
            var code = (e.keyCode ? e.keyCode : e.which);
            let internal;
            let backNum;
            let seri;
            let pcs;
            let proceedWithAjax = true; // Flag to control AJAX execution
            if (code == 13) // Enter key hit 
            {
                barcodecomplete = barcode;
                barcode = "";


                if (barcodecomplete == "AS523") {
                    window.location.replace("{{ url('/production/as523') }}");
                    return;
                }

                if (barcodecomplete == "prdreport") {
                    window.location.replace("{{ url('/production') }}");
                    return;
                }

                if (barcodecomplete.length == 13) {
                    window.location.replace("{{ url('/logout') }}");
                    return;
                }

                if (barcodecomplete == "logout") {
                    window.location.replace("{{ url('/logout') }}");
                    return;
                }

                // get each information inside kanban code
                if (barcodecomplete.length == 230) {
                    // normal kanban proccess
                    internal = barcodecomplete.substr(41, 19);
                    seri = barcodecomplete.substr(123, 4);
                    backNum = barcodecomplete.substr(100, 4);
                    pcs = barcodecomplete.substr(196, 1);

                } else if (barcodecomplete.length == 220) {
                    // kanban buffer
                    internal = barcodecomplete.substr(35, 12);
                    seri = barcodecomplete.substr(130, 4);
                    backNum = barcodecomplete.substr(100, 4);
                    pcs = barcodecomplete.substr(196, 1);

                } else if (barcodecomplete.length == 241) {
                    // kanban passtrough
                    internal = barcodecomplete.substr(35, 12);
                    seri = barcodecomplete.substr(127, 4);
                    backNum = barcodecomplete.substr(100, 4);
                    pcs = barcodecomplete.substr(196, 1);

                } else if (barcodecomplete.length == 218) {
                    // kanban suzuki
                    internal = barcodecomplete.substr(41, 16);
                    seri = barcodecomplete.substr(123, 4);
                    backNum = barcodecomplete.substr(100, 4);
                    pcs = barcodecomplete.substr(196, 1);

                }

                let scanCounter;
                let partCounter;
                let model;

                // new rule
                if (barcodecomplete.endsWith('dandori')) {
                    console.log('scan dandori board');
                    // set item
                    localStorage.setItem('dandori_board', barcodecomplete.replace(/-dandori$/, ""));
                    localStorage.setItem('production_start_time', new Date().toLocaleString('sv-SE'));


                    notif("success", 'Berhasil scan dandori board!');
                    // display status
                    $('.status-card-header').removeClass('card-secondary');
                    $('.status-card-header').addClass('card-success');

                    $('.status-card').removeClass('bg-secondary');
                    $('.status-card').addClass('bg-success');

                    $('#status').text('OK');

                    setTimeout(() => {
                        $('.status-card').removeClass('bg-success');
                        $('.status-card').addClass(
                            'bg-secondary');
                        $('#status').text('-');
                    }, 5000);
                    return;
                }

                // check if dandori board is scanned
                if (!localStorage.getItem('dandori_board')) {
                    // compare scanned kanban with dandori board in local storage
                    dandoriSound(); // Putar suara
                    notif("error", 'Scan dandori board terlebih dahulu!');

                    // display status
                    $('.status-card-header').removeClass('card-secondary');
                    $('.status-card-header').removeClass('card-success');
                    $('.status-card-header').addClass('card-danger');

                    $('.status-card').removeClass('bg-secondary');
                    $('.status-card').removeClass('bg-success');
                    $('.status-card').addClass('bg-danger');

                    $('#status').text('NG');

                    localStorage.setItem('dandori_error', 'true');

                    sendErrorLog("Belum scan dandori board");

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    return;
                }

                if (localStorage.getItem('dandori_board') && barcodecomplete.endsWith('model')) {
                    model = barcodecomplete.replace(/-model$/, "");
                    if (model == localStorage.getItem('dandori_board')) {

                        localStorage.setItem('scan_timer_start', Date.now()); // MULAI timer dari master sample
                        localStorage.removeItem('last_kanban_time'); // Reset agar hitungan pertama benar
                        
                        $.ajax({
                            type: 'GET',
                            url: "{{ url('pulling/internal-check') }}" + '/' + model,
                            _token: "{{ csrf_token() }}",
                            dataType: 'json',
                            success: function(dataPart) {
                                // store part number information in local storage
                                if (dataPart.status == 'success') {
                                    // store to database
                                    localStorage.setItem('model', dataPart
                                        .partNumber);
                                    localStorage.setItem('back_number', dataPart
                                        .backNumber);
                                    localStorage.setItem('scan_counter', 0);
                                    localStorage.setItem('part_counter', 0);
                                    localStorage.setItem('photo', dataPart
                                        .photo);
                                    localStorage.setItem('  ', dataPart.line);

                                    // display model  running
                                    $('.model-card-header').removeClass(
                                        'card-secondary');
                                    $('.model-card-header').addClass(
                                        'card-info');

                                    $('.model-card').removeClass(
                                        'bg-secondary');
                                    $('.model-card').addClass('bg-info');

                                    // display total scan
                                    $('.total-scan-card-header')
                                        .removeClass('card-secondary');
                                    $('.total-scan-card-header').addClass(
                                        'card-success');

                                    $('.total-scan-card').removeClass(
                                        'bg-secondary');
                                    $('.total-scan-card').addClass(
                                        'bg-success');

                                    // display total part
                                    $('.total-part-card-header')
                                        .removeClass('card-secondary');
                                    $('.total-part-card-header').addClass(
                                        'card-success');

                                    $('.total-part-card').removeClass(
                                        'bg-secondary');
                                    $('.total-part-card').addClass(
                                        'bg-success');

                                    $('#model').text(dataPart.backNumber)
                                    $('#total-scan').text(scanCounter)
                                    $('#total-part').text(partCounter)
                                    startTimeCounter(now);
                                    // display PIS
                                    $('#pis').html(
                                        `<img src="{{ asset('assets/img/pis/${dataPart.photo}') }}" alt="PIS" class="rounded" height="700">`
                                    );

                                    // start new timer
                                    // resetAndStartTimer();

                                    

                                } else {
                                    notif('error', dataPart.message);
                                }
                            },
                            error: function(xhr) {
                                console.log(xhr)
                                if (xhr.status == 0) {
                                    notif("error", 'Connection Error');
                                    errConnection();
                                    return;
                                }
                                notif("error", xhr.responseJSON.errors);
                            }
                        })
                    } else {
                        // compare scanned kanban with dandori board in local storage
                        masterDandoriSound(); // Putar suara
                        notif("error", 'Master sample tidak sesuai dengan dandori board!');

                        // display status
                        $('.status-card-header').removeClass('card-secondary');
                        $('.status-card-header').removeClass('card-success');
                        $('.status-card-header').addClass('card-danger');

                        $('.status-card').removeClass('bg-secondary');
                        $('.status-card').removeClass('bg-success');
                        $('.status-card').addClass('bg-danger');

                        $('#status').text('NG');

                        localStorage.setItem('master_dandori_error', 'true');

                        sendErrorLog('Master sample tidak sesuai dengan dandori board!', localStorage
                            .getItem('dandori_board'), model);

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return;
                    }
                }


                // check if model is set in local storage
                if (localStorage.getItem('model') && localStorage.getItem('dandori_board')) {
                    // compare scanned kanban with model in local storage
                    if (localStorage.getItem('model') === internal.trim() && localStorage.getItem(
                            'dandori_board') === internal.trim()) {
                        let now = Date.now();
                        let startTime = localStorage.getItem('production_start_time');
                        let endTime = new Date(now);

                        // Simpan endTime ke localStorage untuk digunakan sebagai startTime berikutnya
                        localStorage.setItem('last_kanban_time', now);
                        // get current counter value
                        $.ajax({
                            type: 'get',
                            url: "{{ url('production/store2/') }}",
                            _token: "{{ csrf_token() }}",
                            data: {
                                partNumber: internal.trim(),
                                seri: seri,
                                start_time: startTime,
                                end_time: new Date().toLocaleString('sv-SE')
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.status == 'success') {

                                    // match sound
                                    okSound();

                                    scanCounter = localStorage.getItem('scan_counter');
                                    scanCounter = parseInt(scanCounter);
                                    scanCounter++;
                                    localStorage.setItem('scan_counter', scanCounter);

                                    partCounter = localStorage.getItem('part_counter');
                                    partCounter = parseInt(partCounter);
                                    partCounter += parseInt(data.qty);
                                    localStorage.setItem('part_counter', partCounter);

                                    // display total scan
                                    $('.total-scan-card-header').removeClass(
                                        'card-secondary');
                                    $('.total-scan-card-header').addClass('card-success');

                                    $('.total-scan-card').removeClass('bg-secondary');
                                    $('.total-scan-card').addClass('bg-success');

                                    // display total part
                                    $('.total-part-card-header').removeClass(
                                        'card-secondary');
                                    $('.total-part-card-header').addClass('card-success');

                                    $('.total-part-card').removeClass('bg-secondary');
                                    $('.total-part-card').addClass('bg-success');

                                    // display status
                                    $('.status-card-header').removeClass('card-secondary');
                                    $('.status-card-header').removeClass('card-danger');
                                    $('.status-card-header').addClass('card-success');

                                    $('.status-card').removeClass('bg-secondary');
                                    $('.status-card').removeClass('bg-danger');
                                    $('.status-card').addClass('bg-success');

                                    // set display
                                    $('#total-scan').text(scanCounter)
                                    $('#total-part').text(partCounter)
                                    $('#status').text('OK');

                                    if (data.line_prd) {
                                        localStorage.setItem('line_prd', data.line_prd);
                                    }

                                    localStorage.setItem('last_kanban_time', now);

                                    startTimeCounter(now);


                                    setTimeout(() => {
                                        $('.status-card').removeClass('bg-danger');
                                        $('.status-card').removeClass('bg-success');
                                        $('.status-card').addClass(
                                            'bg-secondary');
                                        $('#status').text('-');
                                    }, 2000);

                                    // start new timer
                                    // resetAndStartTimer();
                                } else {
                                    notif("error", data.message);

                                    // notification sound
                                    alreadyScanSound();

                                    let interval = setInterval(function() {
                                        $('#notifModal').modal(
                                            'hide');
                                        clearInterval(interval);
                                        $('#code').focus();
                                    }, 1500);

                                    localStorage.setItem('error', 'true');

                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                }
                                return;
                            },
                            error: function(xhr) {
                                if (xhr.status == 0) {
                                    notif("error", 'Connection Error');
                                    errConnection();
                                    return;
                                }
                                notif("error", 'Internal Server Error');
                                return;
                            }
                        });
                    } else {
                        notif('error', 'Kanban tidak sesuai!');

                        // notification sound
                        wrongKanbanSound();

                        // display status
                        $('.status-card-header').removeClass('card-secondary');
                        $('.status-card-header').removeClass('card-success');
                        $('.status-card-header').addClass('card-danger');

                        $('.status-card').removeClass('bg-secondary');
                        $('.status-card').removeClass('bg-success');
                        $('.status-card').addClass('bg-danger');

                        $('#status').text('NG');

                        localStorage.setItem('error', 'true');

                        sendErrorLog('Kanban tidak sesuai!', localStorage
                            .getItem('dandori_board'), internal.trim());

                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return;
                    }
                    return;
                }

            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }

        });
    });
</script>