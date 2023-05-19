@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <section class="section">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 p-0" style="height: 100%;">
                    <div class="shadow hero bg-white text-dark" style="padding: 3rem; height: 100%;">
                        <div class="hero-inner">
                            <div class="row">
                                <div class="col-md-12 mt-3">
                                    <span style="font-size: 1.5rem;">Welcome, {{ auth()->user()->name }}</span>
                                    <h1 class="text-dark" id="line-display">Line</h1>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4> Master Sample </h4>
                                </div>
                                <div class="col-md-12">
                                    <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h3 class="text-center " style="padding-top: 1rem; color: white;"
                                            id="sample-display">Master Sample</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12" style="padding: 15px;">
                                    <h4>Qty Scan</h4>
                                    <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h3 class="text-center " style="padding-top: 1rem; color: white;" id="qty-display">0
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12" style="padding: 15px;">
                                    <h4>Scan Qr Code</h4>

                                    <input style="height: 4rem; width: 100%; background-color: white; border-radius: 20px;"
                                        height=60 id="detail_no" class="form-control" name="detail_no" required>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 3rem;">
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
                    <input type="text" class="form-control" id="input-line">
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
                    <input type="text" class="form-control" id="input-sample">
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
        let line_number = localStorage.getItem('line');
        let sample = localStorage.getItem('avi_sample');
        if (line_number == null || line_number == undefined) {
            $('#modalLineScan').on('shown.bs.modal', function() {
                $('#input-line').focus();
            })
            $('#modalLineScan').modal('show');

        } else {
            $('#line-display').text(line_number);
            if (sample == null || sample == undefined) {
                $('#modalSampleScan').modal('show');
                setInterval(() => {
                    $('#input-sample').focus();
                }, 10);
            } else {
                $('#sample-display').text(sample);
                setInterval(() => {
                    $('#detail_no').focus();
                }, 1000);
            }

        }

        $('#detail_no').focus();
    }

    function notif(color, text) {
        let modal = $('#notifModal');
        let textNotif = $('#notif');
        if (color == "error") {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#FF2A00");
            $('#notifModal').modal('show');
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
            $('#notifModal').modal('show');

        }
    }

    $(document).ready(function() {
        initApp();
        $(document).on('click', function() {
            $('#detail_no').focus();
        });
        $('#input-line').keypress(function(e) {
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
                    },
                    error: function(xhr) {}
                });


                localStorage.setItem('line', $(this).val());
                initApp();
                $('#modalLineScan').modal('hide');
            }
        });

        $('#done').on('click', function() {
            $('#detail_no').focus();
            localStorage.removeItem("line");
            localStorage.removeItem("avi_sample");
            window.location.reload();
        });

        $('#input-sample').keypress(function(e) {
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                localStorage.setItem('avi_sample', $(this).val());
                initApp();
                $('#modalSampleScan').modal('hide');
                $('#detail_no').focus();
            }
        });

        var barcode = "";
        var rep2 = "";
        var detail_no = $('#detail_no');
        let total = 0;

        $('#detail_no').keypress(function(e) {
            e.preventDefault();
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) // Enter key hit 
            {
                barcodecomplete = barcode;

                let a = barcodecomplete.substr(41, 12);
                console.log(a);
                barcode = "";

                if (a == localStorage.getItem('avi_sample')) {

                    notif("success", "Part Sesuai Dengan Sample");
                    let interval = setInterval(function() {
                        $('#notifModal').modal('hide');
                        clearInterval(interval);
                        $('#detail_no').focus();
                        total = total + 1;
                        console.log(total);
                        $('#qty-display').text(total);

                    }, 1500);
                } else {
                    notif("error", "Part Tidak Sesuai Dengan Sample !");
                    let interval = setInterval(function() {
                        $('#notifModal').modal('hide');
                        clearInterval(interval);
                        $('#detail_no').focus();
                    }, 1500);
                }
            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }
        });
    });
</script>
