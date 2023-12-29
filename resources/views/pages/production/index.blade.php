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
                            <h5 class="text-center text-dark">Total Scan</h5>
                            <div class="bg-secondary m-auto shadow total-scan-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 60px 0">
                                <h1 class="text-center" style="color:#ffffff; font-size:3rem" id="total-scan">0</h1>
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
                    <button class="btn btn-warning py-3 px-5 shadow mb-2"
                        style="padding: 1rem; border-radius:8px; width:100% !important" id="release">
                        <h3 class="text-center text-white">Release</h3>
                    </button>
                    <button class="btn btn-danger py-3 px-5 shadow mb-5"
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
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    let line = '';

    function initApp() {
        let model = localStorage.getItem('model');
        let totalScan = localStorage.getItem('scan_counter');
        let totalPart = localStorage.getItem('part_counter');
        let photo = localStorage.getItem('photo');
        if (model || photo) {
            // display model  running
            $('.model-card-header').removeClass('card-secondary');
            $('.model-card-header').addClass('card-info');

            $('.model-card').removeClass('bg-secondary');
            $('.model-card').addClass('bg-info');

            $('#model').text(model)
            // display PIS
            $('#pis').html(
                `<img src="{{ asset('assets/img/pis/${photo}') }}" alt="PIS" class="rounded" height="700">`);
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
            }, 1000);
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
            $('#notifModal').modal('show');
            setTimeout(() => {
                $('#notifModal').modal('hide');
                $('#code').focus();
            }, 1000);
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

    $(document).ready(function() {
        initApp();

        const countdownTimeInSeconds = 60 // 1 minutes
        $(document).on('click', function() {
            $('#code').focus();
        })

        $('#release').on('click', function() {
            $('#code').focus();
            localStorage.clear();
            window.location.reload();
        });

        function updateTimer() {
            const currentTime = Math.floor(Date.now() / 1000);
            const startTime = parseInt(localStorage.getItem('startTime'));

            if (!startTime || isNaN(startTime)) {
                localStorage.setItem('startTime', currentTime);
            }

            const elapsedTimeInSeconds = currentTime - startTime;
            const remainingTimeInSeconds = Math.max(countdownTimeInSeconds - elapsedTimeInSeconds, 0);

            if (remainingTimeInSeconds === 0) {
                // Countdown has ended, take action here
                localStorage.removeItem('startTime');

                clearInterval(interval);
            } else {
                // Update the timer display
                displayTimeRemaining(remainingTimeInSeconds);
            }
            displayTimeRemaining(remainingTimeInSeconds);

            return remainingTimeInSeconds;
        }

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        $('#code').keypress(function(e) {
            e.preventDefault();
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) // Enter key hit 
            {
                barcodecomplete = barcode;
                barcode = "";

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
                internal = internal.trimEnd();

                // check if model is set in local storage
                if (localStorage.getItem('model')) {

                    // compare scanned kanban with model in local storage
                    if (localStorage.getItem('model') === backNum) {
                        // get current counter value
                        scanCounter = localStorage.getItem('scan_counter');
                        scanCounter = parseInt(scanCounter);
                        scanCounter++;
                        localStorage.setItem('scan_counter', scanCounter);

                        partCounter = localStorage.getItem('part_counter');
                        partCounter = parseInt(partCounter);
                        partCounter += parseInt(pcs);
                        localStorage.setItem('part_counter', partCounter);

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
                        $('#status').text('OK')

                        return;
                    } else {
                        notif('error', 'Kanban tidak sesuai!');

                        // display status
                        $('.status-card-header').removeClass('card-secondary');
                        $('.status-card-header').removeClass('card-success');
                        $('.status-card-header').addClass('card-danger');

                        $('.status-card').removeClass('bg-secondary');
                        $('.status-card').removeClass('bg-success');
                        $('.status-card').addClass('bg-danger');

                        $('#status').text('NG')

                        return;
                    }
                }

                // check if part number exist in database
                $.ajax({
                    type: 'GET',
                    url: "{{ url('pulling/internal-check') }}" + '/' + internal,
                    _token: "{{ csrf_token() }}",
                    dataType: 'json',
                    success: function(data) {
                        // store to database
                        $.ajax({
                            type: 'get',
                            url: "{{ url('production/store/') }}",
                            _token: "{{ csrf_token() }}",
                            data: {
                                partNumber: internal,
                                seri: seri
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.status == 'success') {
                                    console.log(data);
                                } else {
                                    notif("error", data.message);
                                    let interval = setInterval(function() {
                                        $('#notifModal').modal('hide');
                                        clearInterval(interval);
                                        $('#code').focus();
                                    }, 1500);
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status == 0) {
                                    notif("error", 'Connection Error');
                                    return;
                                }
                                notif("error", 'Internal Server Error');
                            }
                        });

                        // store part number information in local storage
                        if (data.status == 'success') {
                            localStorage.setItem('model', data.backNumber);
                            localStorage.setItem('scan_counter', 0);
                            localStorage.setItem('part_counter', 0);
                            localStorage.setItem('photo', data.photo);

                            // display model  running
                            $('.model-card-header').removeClass('card-secondary');
                            $('.model-card-header').addClass('card-info');

                            $('.model-card').removeClass('bg-secondary');
                            $('.model-card').addClass('bg-info');

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

                            $('#model').text(data.backNumber)
                            $('#total-scan').text(scanCounter)
                            $('#total-part').text(partCounter)

                            // display PIS
                            $('#pis').html(`
                                <img src="{{ asset('assets/img/pis/${data.photo}') }}" alt="PIS" class="rounded" height="700">
                            `);
                        } else {
                            notif('error', data.message);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr)
                        if (xhr.status == 0) {
                            notif("error", 'Connection Error');
                            return;
                        }
                        notif("error", xhr.responseJSON.errors);
                    }
                })

            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }
        });
    });
</script>
