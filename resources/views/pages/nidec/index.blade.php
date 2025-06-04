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
                            <h5 class="text-center text-dark">Model Outer</h5>
                            <div class="bg-secondary m-auto shadow model-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="model">-</h1>
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
                    <button id="fullscreenBtn" class="btn btn-info mb-2 text-end p-4"
                        style="border-radius:4px; width:100% !important">Full Screen</button>
                    <button id="fullscreenBtn" class="btn btn-warning mb-4 text-end p-4"
                        style="border-radius:4px; width:100% !important">Refresh</button>
                    <div class="shadow pt-4 card card-secondary status-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Model Inner 1</h5>
                            <div class="bg-secondary m-auto shadow status-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="outer1">-</h1>
                            </div>
                        </div>
                    </div>
                    <div class="shadow pt-4 card card-secondary status-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Model Inner 2</h5>
                            <div class="bg-secondary m-auto shadow status-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="outer2">-</h1>
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
        let photo = localStorage.getItem('photo');
        let outer1 = localStorage.getItem('outer_1');
        let outer2 = localStorage.getItem('outer_2');

        if (outer1) {
            $('#outer1').text(outer1);
        }
        if (outer2) {
            $('#outer2').text(outer2);
        }
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
        resetDailyInnerScanIfNeeded();

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

    $(document).ready(function() {
        initApp();

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
            localStorage.clear();
            window.location.reload();
        });

        $('#pause').on('click', function() {
            pauseTimer();
            notif('success', 'Timer telah berhenti!');
        });

        var partNumber
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

                // get each information inside kanban code
                if (barcodecomplete.length !== 0) {
                    if (barcodecomplete.length == 241) {
                        partNumber = barcodecomplete.match(/\d{6}-\d{5}/)
                        partNumber = partNumber[0];
                        internal = 1;
                    } else if (barcodecomplete.length >= 100 && barcodecomplete.length <= 200) {
                        partNumber = barcodecomplete.match(/(?:IC|OS)\s+([^\s|\/]*-C)/i)
                        partNumber = partNumber[1].replace(/-c$/i, '')
                        internal = 0;
                    }
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('pulling/internal-check') }}" + '/' + partNumber + '/' + internal,
                        _token: "{{ csrf_token() }}",
                        dataType: 'json',
                        success: function(dataPart) {
                            // store part number information in local storage
                            if (dataPart.status == 'success') {
                                if (!localStorage.getItem('inner')) {
                                    // 🔄 Reset harian jika ganti hari
                                    const today = new Date().toISOString().split('T')[0]; // "YYYY-MM-DD"
                                    const lastScanDate = localStorage.getItem('scan_date');

                                    if (lastScanDate !== today) {
                                        // Reset daftar inner harian
                                        localStorage.removeItem('scanned_inners');
                                        localStorage.setItem('scan_date', today);
                                    }

                                    // Ambil daftar inner yang sudah discan sebelumnya
                                    let scannedInners = JSON.parse(localStorage.getItem('scanned_inners')) || [];

                                    // ❌ Validasi duplikat inner
                                    if (scannedInners.includes(partNumber)) {
                                        notif('error', 'Inner ini sudah pernah digunakan sebelumnya!');
                                        alreadyScanSound();
                                        return;
                                    }

                                    // ✅ Simpan inner ke localStorage
                                    localStorage.setItem('inner', partNumber); // inner aktif saat ini
                                    scannedInners.push(partNumber); // masukkan ke daftar
                                    localStorage.setItem('scanned_inners', JSON.stringify(scannedInners));
                                    localStorage.setItem('scan_date', today); // pastikan tanggalnya diperbarui juga

                                    // ✅ Lanjutkan proses normal inner:
                                    localStorage.setItem('back_number', dataPart.backNumber);
                                    localStorage.setItem('photo', dataPart.photo);

                                    $('#pis').html(
                                        `<img src="{{ asset('assets/img/pis/${dataPart.photo}') }}" alt="PIS" class="rounded" height="700">`
                                    );

                                    $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                                    $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                                    $('#model').text(dataPart.backNumber);

                                    // Reset counter outer match jika kamu pakai logika outer 2x
                                    localStorage.removeItem('outer_match_count');
                                    localStorage.removeItem('outer_1');
                                    localStorage.removeItem('outer_2');
                                } else {
                                    if (partNumber == localStorage.getItem('inner')) {
                                        // ✅ MATCH dengan inner
                                    let count = parseInt(localStorage.getItem('outer_match_count')) || 0;
                                    count += 1;
                                    localStorage.setItem('outer_match_count', count);

                                    if (count === 1) {
                                        $('#outer1').text(dataPart.backNumber);
                                        localStorage.setItem('outer_1', dataPart.backNumber);
                                    } else if (count === 2) {
                                        $('#outer2').text(dataPart.backNumber);
                                        localStorage.setItem('outer_2', dataPart.backNumber);

                                        // ✅ Di sinilah Langkah ke-4 ditempatkan:
                                        notif('success', '2x Inner match! Silakan scan kanban outer berikutnya.');

                                        localStorage.removeItem('inner');
                                        localStorage.removeItem('photo');
                                        localStorage.removeItem('outer_match_count');
                                        localStorage.removeItem('outer_1');
                                        localStorage.removeItem('outer_2');

                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    }
                                    } else {
                                        // ❌ TIDAK COCOK
                                        notif('error', 'Kanban tidak sesuai!');
                                        wrongKanbanSound();

                                        $('.status-card-header').removeClass('card-secondary card-success').addClass('card-danger');
                                        $('.status-card').removeClass('bg-secondary bg-success').addClass('bg-danger');
                                        $('#status').text('NG');
                                    }

                                }
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
                    });
                } else {
                    notif("error", "Kanban tidak dikenali !");

                    // notification sound
                    unknownSound();

                    let interval = setInterval(function() {
                        $('#notifModal').modal('hide');
                        clearInterval(interval);
                        $('#code').focus();
                    }, 1500);
                }
            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }

        });

        function resetDailyInnerScanIfNeeded() {
            const today = new Date().toISOString().split('T')[0]; // Format: "YYYY-MM-DD"
            const lastScanDate = localStorage.getItem('scan_date');

            if (lastScanDate !== today) {
                localStorage.removeItem('scanned_inners');
                localStorage.setItem('scan_date', today);
                console.log('Reset inner harian karena ganti hari.');
            }
        }
    });
</script>
