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
                        <label style="font-weight:800" class="text-center text-dark">Scan Part (Kanan)</label>
                        <input id="part-right" type="text" class="form-control" name="part-right" tabindex="2"
                            placeholder="scan part kanan..." autocomplete="off" disabled>
                        <small class="text-muted">Last: <span id="last-right">-</span></small>
                    </div>

                    <div class="card card-warning py-4 shadow mb-2" style="padding: 1rem; border-radius:8px">
                        <label style="font-weight:800" class="text-center text-dark">Scan Part (Kiri)</label>
                        <input id="part-left" type="text" class="form-control" name="part-left" tabindex="3"
                            placeholder="scan part kiri..." autocomplete="off" disabled>
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

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>

<script>
    (function() {
        const CSRF = "{{ csrf_token() }}";

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

        function initFromStorage() {
            const back = LS.get('back_number');
            const photo = LS.get('photo');
            const lastR = LS.get('pc2b_last_right') || '-';
            const lastL = LS.get('pc2b_last_left') || '-';

            if (back || photo) {
                $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                $modelTxt.text(back || '-');
                showPis(photo);
                enablePartInputs(true);
            } else {
                enablePartInputs(false);
            }

            $lastR.text(lastR);
            $lastL.text(lastL);

            focusExpected();
        }

        function parseMasterInput(s) {
            const t = (s || '').trim();
            if (!t) return null;
            if (t.endsWith('model')) {
                return t.replace(/-model$/, '');
            }
            return t;
        }

        function handleMasterScan(raw) {
            const modelCode = parseMasterInput(raw);
            if (!modelCode) return;

            api(`{{ url('pulling/internal-check') }}/${modelCode}`, 'GET', {
                    _token: CSRF
                })
                .done(dp => {
                    if (dp.status !== 'success') {
                        wrongSound();
                        notif('error', dp.message || 'Master sample tidak valid');
                        setStatus('ng');
                        $master.val('');
                        $master.focus();
                        return;
                    }

                    const partNumber = dp.partNumber || (dp.data && dp.data.partNumber) || '';
                    const backNumber = dp.backNumber || dp.back_no || dp.backNum || dp.back || (dp.data && dp.data.backNumber) ||
                        '';
                    const photo = dp.photo || (dp.data && dp.data.photo) || '';
                    const lineVal = dp.line || (dp.data && dp.data.line) || '';

                    if (!partNumber || !lineVal) {
                        wrongSound();
                        notif('error', 'Data master sample belum lengkap (model/line).');
                        setStatus('ng');
                        return;
                    }

                    LS.mset({
                        model: partNumber,
                        back_number: backNumber,
                        photo: photo,
                        line: lineVal,
                        pc2b_expected_side: 'R',
                    });

                    // reset per master
                    LS.del('pc2b_last_right');
                    LS.del('pc2b_last_left');
                    $lastR.text('-');
                    $lastL.text('-');

                    $('.model-card-header').removeClass('card-secondary').addClass('card-info');
                    $('.model-card').removeClass('bg-secondary').addClass('bg-info');
                    $modelTxt.text(backNumber || '-');
                    showPis(photo);

                    enablePartInputs(true);
                    okSound();
                    notif('success', 'Master sample OK. Lanjut scan part KANAN.');
                    setStatus('ok');

                    $master.val('');
                    $partR.val('');
                    $partL.val('');
                    $partR.focus();
                })
                .fail(xhr => {
                    if (xhr.status === 0) {
                        notif('error', 'Connection Error');
                        errConnection();
                        return;
                    }
                    notif('error', xhr.responseJSON?.errors || 'Internal Server Error');
                    wrongSound();
                });
        }

        function handlePartScan(side, rawBarcode) {
            const barcode = (rawBarcode || '').trim();
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
                notif('error', `Urutan salah. Sekarang harus scan part ${exp === 'R' ? 'KANAN' : 'KIRI'}.`);
                setStatus('ng');
                focusExpected();
                return;
            }

            if (barcode.length !== 27) {
                wrongSound();
                notif('error', 'Barcode part harus 27 karakter.');
                setStatus('ng');
                focusExpected();
                return;
            }

            const line = LS.get('line');
            const model = LS.get('model');
            const dandori = LS.get('dandori_board') || '';

            api(`/production/part-scan`, 'POST', {
                    _token: CSRF,
                    line,
                    model,
                    dandori,
                    barcode
                })
                .done(res => {
                    if (res.status && res.status !== 'ok') {
                        wrongSound();
                        notif('error', res.message || 'Gagal simpan scan part');
                        setStatus('ng');
                        focusExpected();
                        return;
                    }

                    if (side === 'R') {
                        LS.set('pc2b_last_right', barcode);
                        $lastR.text(barcode);
                        setExpectedSide('L');
                        $partR.val('');
                        okSound();
                        notif('success', 'Scan part kanan OK. Lanjut part kiri.');
                        setStatus('ok');
                        $partL.focus();
                    } else {
                        LS.set('pc2b_last_left', barcode);
                        $lastL.text(barcode);
                        setExpectedSide('R');
                        $partL.val('');
                        okSound();
                        notif('success', 'Scan part kiri OK. Lanjut part kanan.');
                        setStatus('ok');
                        $partR.focus();
                    }
                })
                .fail(xhr => {
                    if (xhr.status === 0) {
                        notif('error', 'Connection Error');
                        errConnection();
                        return;
                    }
                    wrongSound();
                    notif('error', xhr.responseJSON?.errors || 'Internal Server Error');
                    setStatus('ng');
                    focusExpected();
                });
        }

        $(document).ready(function() {
            initFromStorage();

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
                    } else if (document.documentElement.mozCancelFullScreen) {
                        document.documentElement.mozCancelFullScreen();
                    } else if (document.documentElement.webkitExitFullscreen) {
                        document.documentElement.webkitExitFullscreen();
                    } else if (document.documentElement.msExitFullscreen) {
                        document.documentElement.msExitFullscreen();
                    }
                }
            });

            $(document).on('click', function() {
                focusExpected();
            });

            $('#release').on('click', function() {
                localStorage.clear();
                window.location.reload();
            });

            $('#stop').on('click', function() {
                const model = LS.get('model');
                if (!model) {
                    wrongSound();
                    notif('error', 'Belum ada master sample / model running.');
                    setStatus('ng');
                    $master.focus();
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
                        notif('error', xhr.responseJSON?.message || xhr.responseJSON?.errors || 'Internal Server Error');
                        setStatus('ng');
                        focusExpected();
                    });
            });

            $master.on('keydown', function(e) {
                const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                if (!isEnter) return;
                e.preventDefault();
                handleMasterScan($master.val());
            });

            $partR.on('keydown', function(e) {
                const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                if (!isEnter) return;
                e.preventDefault();
                handlePartScan('R', $partR.val());
            });

            $partL.on('keydown', function(e) {
                const isEnter = e.key === 'Enter' || e.which === 13 || e.keyCode === 13;
                if (!isEnter) return;
                e.preventDefault();
                handlePartScan('L', $partL.val());
            });
        });
    })();
</script>
