@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <section class="section">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 p-0" style="height: 100%;">
                    <div class="shadow hero bg-white text-dark" style="padding: 3rem; height: 100%;">
                        <div class="hero-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <span style="font-size: 1.5rem;">Welcome, {{ auth()->user()->name }}</span>
                                    <h1 class="text-dark" id="line-display">Line</h1>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4> Master Sample </h4>
                                </div>
                                <div class="col-md-12">
                                    <div
                                        style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px; padding:10px; padding-left:12px">
                                        <h4 class="text-center " style="padding-top: .02rem; color: white;"
                                            id="sample-display">Master Sample</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12" style="padding: 15px;">
                                    <h4>Qty Scan</h4>
                                    <div
                                        style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px; padding:10px; padding-left:12px">
                                        <h4 class="text-center " style="padding-top: .02rem; color: white;"
                                            id="qty-display">0
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12" style="padding: 15px;">
                                    <input style="height: 3rem; width: 100%; background-color: white; border-radius: 20px;"
                                        height=60 id="code" class="form-control" name="code" required
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="padding: 15px;">
                                    <div style="height: 4rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-success"
                                            style="border-radius: 3rem; height: 4rem; width: 100%; font-size: 1.5rem;"
                                            id="done">Selesai</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalLineScan" aria-hidden="true" aria-labelledby="modalToggleLabel2" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                </div>
                <div class="modal-body">

                    <h3 class="text-center"><b>LINE</b></h3><br>
                    <input type="text" class="form-control" id="input-line" autocomplete="off">
                    <br>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSampleScan" aria-hidden="true" aria-labelledby="modalToggleLabel2" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                </div>
                <div class="modal-body">

                    <h3 class="text-center"><b>MASTER SAMPLE</b></h3><br>
                    <input type="text" class="form-control" id="input-sample" autocomplete="off">
                    <br>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade gfont" id="notifModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="divNotif" style="border-radius: 15px !important;">
                <div class="modal-body text-center">
                    <span style="color: white; font-size: 30pt" id="notif"> Scan Part</span>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    let line = '';

    function initApp() {
        let line = localStorage.getItem('line');
        let sample = localStorage.getItem('sample');
        if (!line) {
            $('#modalLineScan').on('shown.bs.modal', function() {
                $('#input-line').focus();
            })
            $('#modalLineScan').modal('show');

        } else {
            $('#line-display').text(line);
            if (!sample) {
                $('#modalSampleScan').modal('show');
                setInterval(() => {
                    $('#input-sample').focus();
                }, 10);
            } else {
                $('#sample-display').text(sample);
                setInterval(() => {
                    $('#code').focus();
                }, 1000);
            }

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
            }, 1000);
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
            $('#notifModal').modal('show');
            setTimeout(() => {
                $('#notifModal').modal('hide');
            }, 1000);

        }
    }

    function sampleModal() {
        let sample = localStorage.getItem('sample');
        $('#input-sample').val('');
        setTimeout(() => {
            if (!sample) {
                $('#modalSampleScan').on('shown.bs.modal',
                    function() {
                        $('#input-sample').focus();
                    })
                $('#modalSampleScan').modal('show');
            }
        }, 1500);
    }

    function lineModal() {
        $('#input-line').val('');
        setTimeout(() => {
            if (!line) {
                $('#modalLineScan').on('shown.bs.modal',
                    function() {
                        $('#input-line').focus();
                    })
                $('#modalLineScan').modal('show');
            }
        }, 1500);
    }

    $(document).ready(function() {
        initApp();
        $(document).on('click', function() {
            $('#code').focus();
        });
        $('#input-line').keypress(function(e) {
            let line = localStorage.getItem('line');
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {

                //Check Line
                $.ajax({
                    type: 'get',
                    url: "{{ url('production/line-check/') }}" + '/' + $(this).val(),
                    _token: "{{ csrf_token() }}",
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        if (data.status == 'success') {
                            localStorage.setItem('line', data.line);
                            initApp();
                        } else {
                            notif('error', data.message);
                            lineModal();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status == 0) {
                            notif("error", 'Connection Error');
                            lineModal();
                            return;
                        }
                        notif("error", 'Internal Server Error');
                        lineModal();
                    }
                });

                $('#modalLineScan').modal('hide');
            }
        });

        $('#done').on('click', function() {
            $('#code').focus();
            localStorage.removeItem("line");
            localStorage.removeItem("sample");
            window.location.reload();
        });

        $('#sample-display').on('click', function() {
            sampleModal();
        });

        $('#input-sample').keypress(function(e) {
            let line = localStorage.getItem('line');
            let sample = localStorage.getItem('sample');
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {

                //Check sample 
                $.ajax({
                    type: 'get',
                    url: "{{ url('production/sample-check/') }}" + '/' + line + '/' +
                        $(this).val(),
                    _token: "{{ csrf_token() }}",
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        if (data.status == 'success') {
                            localStorage.setItem('sample', data.sample);
                            initApp();
                        } else {
                            notif('error', data.message);
                            sampleModal();
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        if (xhr.status == 0) {
                            notif("error", 'Connection Error');
                            sampleModal();
                            return;
                        }
                        notif("error", 'Internal Server Error');
                        sampleModal();
                    }
                });

                initApp();
                $('#modalSampleScan').modal('hide');
                $('#code').focus();
            }
        });

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

                let partNumber = barcodecomplete.substr(41, 12);
                console.log(partNumber);

                if (partNumber == localStorage.getItem('sample')) {

                    //insert to mutation 
                    $.ajax({
                        type: 'get',
                        url: "{{ url('production/store/') }}",
                        _token: "{{ csrf_token() }}",
                        data: {
                            partNumber: partNumber
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log(data);
                            if (data.status == 'success') {
                                notif("success", data.message);
                                let interval = setInterval(function() {
                                    $('#notifModal').modal('hide');
                                    clearInterval(interval);
                                    $('#code').focus();
                                    total = total + 1;
                                    console.log(total);
                                    $('#qty-display').text(total);

                                }, 1500);
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

                } else {
                    notif("error", "Part Tidak Sesuai Dengan Sample !");
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
    });
</script>
