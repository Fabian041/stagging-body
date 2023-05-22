@extends('layouts.root.auth')

@section('main')    
<div class="main-section">
    <section class="section">
        <div class="row" >
            <div class="col-12 col-sm-12 col-md-12 p-0" style="height: 100%;">
                <div class="shadow hero bg-white text-dark" style="padding: 3rem; height: 100%;">
                    <div class="hero-inner">
                        <div class="row">
                            <div class="col-md-12 mt-3">
                                <span style="font-size: 1.5rem;">Siap Pulling, {{ auth()->user()->name    }}</span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm-9" style="padding: 15px;">
                                <h4>Loading List</h4>
                                <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                    <h3 class="text-center " style="padding-top: 1rem; color: white;" id="qty-display" >Loading List</h3>
                                </div>
                            </div>

                            <div class="col-sm-3" style="padding: 15px;">
                                <h4>Qty</h4>
                                <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                    <h3 class="text-center " style="padding-top: 1rem; color: white;" id="qty-display" >1/28</h3>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-sm-6" style="padding: 15px;">
                                <h4>Customer</h4>
                                <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                    <h3 class="text-center " style="padding-top: 1rem; color: white;" id="qty-display" >Customer</h3>
                                </div>
                            </div>

                            <div class="col-sm-6" style="padding: 15px;">
                                <h4>Cycle</h4>
                                <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                    <h3 class="text-center " style="padding-top: 1rem; color: white;" id="qty-display" >Cycle</h3>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-sm-6" style="padding: 15px;">
                                <h4>Kanban Internal</h4>
                                <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                    <h3 class="text-center " style="padding-top: 1rem; color: white;" id="int-display" >-</h3>
                                </div>
                            </div>

                            <div class="col-sm-6" style="padding: 15px;">
                                <h4>Kanban Customer</h4>
                                <div style="height: 4rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                    <h3 class="text-center " style="padding-top: 1rem; color: white;" id="int-cust" >-</h3>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-md-12" style="padding: 15px;">
                                <h4>Scan Qr Code</h4>
                                
                                <input style="height: 4rem; width: 100%; background-color: white; border-radius: 20px;" height=60 id="detail_no" class="form-control" name="detail_no" required>
                            </div>
                        

                        </div>
                        <div class="row" style="margin-top: 3rem;">
                            <div class="col-md-12" style="padding: 15px;">
                                
                                <div style="height: 4rem; width: 100%; border-radius: 20px;">
                                    <button type="button" class="btn btn-xl btn-success" style="border-radius: 3rem; height: 4rem; width: 100%; font-size: 1.5rem;" id="done">Selesai</button>
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
  <div class="modal-dialog modal-dialog-centered" >
    <div class="modal-content" >
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

<div class="modal fade gfont" id="notifModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document" > 
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
    let Database_Name = 'SanTenShogo';  
    let Version = 1.0;  
    let Text_Description = 'Temporary local database ';  
    let Database_Size = 2 * 1024 * 1024;  
    let dbObj = openDatabase(Database_Name, Version, Text_Description, Database_Size);  
    dbObj.transaction(function (tx) {  

        tx.executeSql('CREATE TABLE IF NOT EXISTS Employee_Table (id unique, Name, Location,did)');  
        tx.executeSql('CREATE TABLE IF NOT EXISTS dept_Table (did unique, dName,estd)');  

        var today = new Date();  
        var dd = today.getDate();  
        var mm = today.getMonth() + 1; //January is 0!  

        var yyyy = today.getFullYear();  
        if (dd < 10) {  
            dd = '0' + dd  
        }  
        if (mm < 10) {  
            mm = '0' + mm  
        }  
        var today = dd + '/' + mm + '/' + yyyy;  

        tx.executeSql('insert into dept_Table(did, dName, estd) values(1,"IT","' + today + '")');  
        tx.executeSql('insert into dept_Table(did, dName, estd) values(2,"Accountant","' + today + '")');  
        tx.executeSql('insert into dept_Table(did, dName, estd) values(3,"Claerk","' + today + '")');  
        tx.executeSql('insert into dept_Table(did, dName, estd) values(4,"Management","' + today + '")');  
    });

    function initApp() {
        let line_number = localStorage.getItem('line');
        let sample = localStorage.getItem('avi_sample');
        if (line_number == null || line_number == undefined) {
            $('#modalLineScan').on('shown.bs.modal', function () {
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
        if (color == "error" ) {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#961a2c");
            $('#notifModal').modal('show');
        } else {
            textNotif.text(text);
            $('#divNotif').css("background-color", "#32a852");
            $('#notifModal').modal('show');

        }
    }

    $(document).ready(function() {
        // initApp();
        $(document).on('click', function() {
            $('#detail_no').focus();
        });
        $('#input-line').keypress(function(e) {
            let code = (e.keyCode ? e.keyCode : e.which);
            if(code==13) {
                //Check Line
                $.ajax({
                    type: 'get',
                    url: "{{ url('production/line-check/') }}"+'/'+ $(this).val(),
                    _token: "{{ csrf_token() }}",
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                    },
                    error: function (xhr) {
                    }
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
            if(code==13) {
                localStorage.setItem('avi_sample', $(this).val());
                initApp();
                $('#modalSampleScan').modal('hide');
                $('#detail_no').focus();
            }
        });

        var barcode   ="";
        var rep2      = "";
        var detail_no = $('#detail_no');
        let total = 0 ;

        $('#detail_no').keypress(function(e) {
            e.preventDefault();
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code==13)// Enter key hit 
            {

            // {
                barcodecomplete = barcode;

                let a = barcodecomplete.substr(41, 12);
                console.log(a);
                barcode = "";

                if (a == localStorage.getItem('avi_sample')) {

                    notif("success", "Part Sesuai Dengan Sample");
                    let interval = setInterval( function(){
                        $('#notifModal').modal('hide');
                        clearInterval(interval);
                        $('#detail_no').focus();
                        total = total+1;
                        console.log(total);
                        $('#qty-display').text(total);

                    }, 1500);
                } else {
                    notif("error", "Part Tidak Sesuai Dengan Sample !");
                    let interval = setInterval( function(){
                        $('#notifModal').modal('hide');
                        clearInterval(interval);
                        $('#detail_no').focus();
                    }, 1500);
                }
            //     $('#detail_no').val('');
            //     if (barcodecomplete.length == 15) {
            //         $.ajax({
            //                 type: 'get',           // {{-- POST Request --}}
            //                 url: "{{ url('/trace/scan/assembling/getAjax') }}"+'/'+barcodecomplete+'/'+line,
            //                 _token: "{{ csrf_token() }}",
            //                 dataType: 'json',       // {{-- Data Type of the Transmit --}}
            //                 success: function (data) {
            //                     code = data.code;
            //                     if(code == "" ){
            //                         $('#detail_no').prop('readonly', false);
            //                         $('#detail_no').val(barcode);
            //                         $('#alert').removeClass('alert-success');
            //                         $('#alert').addClass('alert-danger');
            //                         $('#alert-header').html('<i class="icon fa fa-warning"></i>'+'GAGAL !!');
            //                         $('#alert-body').text('Data sudah ada');
            //                         $('#detail_no').prop('readonly', true);
            //                         $('#detail_no').focus();
            //                     }
            //                     else{
            //                         table.ajax.url("{{ url ('trace/assembling/update')}}").load();
            //                         $('#alert').removeClass('alert-danger');
            //                         $('#alert').addClass('alert-success');
            //                         $('#alert-header').html('<i class="icon fa fa-check"></i>'+'BERHASIL !!');
            //                         $('#alert-body').text(barcodecomplete);
            //                         $('#detail_no').val("");
            //                         $('#detail_no').prop('readonly', true);
            //                         // {{-- dev-1.0, 20170913, Ferry, Fungsi informasi display --}}
            //                         $('#counter').text(data.counter);
            //                         $('#detail_no').focus();
            //                     }
            //                 },
            //                 error: function (xhr) {
            //                     if (xhr.status) {
            //                         location.reload();
            //                     }

            //                     $('#alert').removeClass('alert-success');
            //                     $('#alert').addClass('alert-danger');
            //                     $('#alert-header').html('<i class="icon fa fa-warning"></i>'+'@lang("avicenna/pis.error_scan")'+xhr.status+" - "+xhr.statusText);

            //                     if (xhr.status == 0) {
            //                         $('#alert-body').text('@lang("avicenna/pis.connection_error")');
            //                         return;
            //                     }

            //                     $('#alert-body').text('@lang("avicenna/pis.fatal_error")');
            //                 }
            //             });
            //     }
            //     else if (barcodecomplete == "RELOAD")
            //     {
            //             location.reload();
            //     }
            //     else{
            //         $('#alert').removeClass('alert-success');
            //         $('#alert').addClass('alert-danger');
            //         $('#alert-header').html('<i class="icon fa fa-warning"></i>'+'GAGAL !!');
            //         $('#alert-body').text('Mohon Scan Ulang');
            //         $('#detail_no').prop('readonly', true);

            //     }
            } else {
                barcode=barcode+String.fromCharCode(e.which);
            }
        });
    } );

</script>