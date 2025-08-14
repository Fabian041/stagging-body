@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <div class="mx-5 my-2">
            <div class="row">

                <div class="col-lg-12 col-sm-12">


                    <button class="btn btn-danger" onclick="resetScanState()">Reset Scan</button>

                        <input id="code" type="text" class="form-control" name="code" tabindex="1"
                            placeholder="scan part..." required autofocus autocomplete="off" style="opacity: 0; width: 1px; height: 1px;">
                    <div class="shadow pt-4 card card-secondary model-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px;">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Kanban Assembly</h5>
                            <div class="bg-secondary m-auto shadow model-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 30px 0">
                                <h3 class="text-center" style="color:#ffffff; font-size:3rem" id="model_assy">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="shadow pt-4 card card-secondary total-scan-card-header"
                        style="margin-bottom:130px; height: 7rem; width: 100%; background-color: #ffffff; border-radius: 6px">
                        <div class="hero-inner">
                            <h5 class="text-center text-dark">Kanban Painting</h5>
                            <div class="bg-secondary m-auto shadow total-scan-card"
                                style="height: 10rem; width: 85%; border-radius: 6px; padding: 30px 0">
                                <h3 class="text-center" style="color:#ffffff; font-size:3rem" id="model_painting">-</h3>
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
        }, 2000);
    }


    $(document).ready(function() {
        $(document).on('click', function() {
            $('#code').focus();
        })

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        let scanTimeout;

        $('#code').on('input', function () {
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

        // Cek apakah scan pertama (assy)
        if (!localStorage.getItem('assy_part_number')) {
            const assyPart = scannedPart;
            const assyModel = scannedModel;

            try {
                const res = await fetch(`/validation/kanban/pairing?part=${assyPart}`);
                const data = await res.json();

                if (data.success) {
                    // Hitung rasio berdasarkan KPK
                    const qtyAssy = parseInt(data.qty_assy);
                    const qtyPainting = parseInt(data.qty_painting);

                    function gcd(a, b) {
                        return b === 0 ? a : gcd(b, a % b);
                    }
                    function lcm(a, b) {
                        return (a * b) / gcd(a, b);
                    }

                    let ratioAssy = 1;
                    let ratioPainting = 1;

                    if (qtyAssy > qtyPainting) {
                        ratioAssy = qtyAssy / qtyPainting;
                        ratioPainting = 1;
                    } else {
                        ratioPainting = qtyPainting / qtyAssy;
                        ratioAssy = 1;
                    }

                    // Simpan ke localStorage
                    localStorage.setItem('assy_part_number', assyPart);
                    localStorage.setItem('expected_painting', data.painting);
                    localStorage.setItem('model_painting', data.model_painting);
                    localStorage.setItem('model_assy', data.model_assy);
                    localStorage.setItem('qty_assy', qtyAssy);
                    localStorage.setItem('qty_painting', qtyPainting);
                    localStorage.setItem('ratio_assy', ratioAssy);
                    localStorage.setItem('ratio_painting', ratioPainting);
                    localStorage.setItem('scan_count_assy', 1); // ini scan pertama
                    localStorage.setItem('scan_count_painting', 0);

                    $('#total-scan').text(0);
                    updateScanProgress();
                    notif('success', 'Scan assy pertama berhasil');
                } else {
                    notif('error', 'Tidak ditemukan pasangan painting');
                }
            } catch (error) {
                notif('error', 'Gagal mengambil data pasangan');
            }

            return; // keluar dari fungsi karena scan pertama
        }

        // Scan berikutnya
        const expectedPainting = localStorage.getItem('expected_painting');
        const expectedAssy = localStorage.getItem('assy_part_number');

        let countAssy = parseInt(localStorage.getItem('scan_count_assy') || 0);
        let countPainting = parseInt(localStorage.getItem('scan_count_painting') || 0);
        const ratioAssy = parseInt(localStorage.getItem('ratio_assy'));
        const ratioPainting = parseInt(localStorage.getItem('ratio_painting'));

        // Logika scan assy/painting
        if (scannedPart === expectedAssy) {
            if (countAssy >= ratioAssy) {
                notif('error', 'Jumlah assy sudah cukup');
                return;
            }

            countAssy++;
            localStorage.setItem('scan_count_assy', countAssy);
            updateScanProgress();
            notif('success', `Assy ke-${countAssy} berhasil`);
        } else if (scannedPart === expectedPainting) {
            if (countAssy < ratioAssy) {
                notif('error', 'Scan assy dulu sampai cukup');
                return;
            }
            if (countPainting >= ratioPainting) {
                notif('error', 'Jumlah painting sudah cukup');
                return;
            }

            countPainting++;
            localStorage.setItem('scan_count_painting', countPainting);
            $('#total-scan').text(countPainting);
            notif('success', `Painting ke-${countPainting} berhasil`);
            updateScanProgress();
        } else {
            notif('error', 'Part tidak sesuai pasangan');
            return;
        }

        // ✅ Cek pairing selesai
        if (countAssy === ratioAssy && countPainting === ratioPainting) {
            notif('success', '✅ Pairing selesai!');
            localStorage.clear();
            $('#model_assy').text('-');
            $('#model_painting').text('-');
            resetScanState();
        }
    }


    function extractPartNumber(barcode) {
        const regex = /\b\d{7}-\d{5}-[A-Z0-9]{3}\b/;
        const match = barcode.match(regex);
        console.log("Extracted part number:", match[0].slice(1));
        return match ? match[0].slice(1) : null;
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
        const countAssy = parseInt(localStorage.getItem('scan_count_assy') || 0);
        const countPainting = parseInt(localStorage.getItem('scan_count_painting') || 0);
        const ratioAssy = parseInt(localStorage.getItem('ratio_assy') || 0);
        const ratioPainting = parseInt(localStorage.getItem('ratio_painting') || 0);
        const modelAssy = localStorage.getItem('model_assy') || '-';
        const modelPainting = localStorage.getItem('model_painting') || '-';

        const progressTextAssy = `${modelAssy} (${countAssy}/${ratioAssy})`;
        const progressTextPainting = `${modelPainting} (${countPainting}/${ratioPainting})`;
        $('#model_assy').text(progressTextAssy);
        $('#model_painting').text(progressTextPainting);

        if (countAssy >= ratioAssy) {
            $('.model-card-header').removeClass('card-secondary').addClass('card-success');
            $('.model-card').removeClass('bg-secondary').addClass('bg-success');
        }

        if (countPainting >= ratioPainting) {
            $('.total-scan-card-header').removeClass('card-secondary').addClass('card-success');
            $('.total-scan-card').removeClass('bg-secondary').addClass('bg-success');
        }
    }

</script>
