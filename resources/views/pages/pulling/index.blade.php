@extends('layouts.root.auth')

@section('main')
    <div class="main-section">
        <section class="section">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 p-0" style="height: 100%;">
                    <div class="shadow hero bg-white text-dark" style="padding: 1.5rem; height: 100%;">
                        <div class="hero-inner">
                            <div class="row">
                                <div class="col-md-12">
                                    <span style="font-size: 1rem;">Siap Pulling, {{ auth()->user()->name }}</span>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-9" style="padding-left: 1rem; padding-right: 0px">
                                    <h6>Loading List</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="loadingList-display">Loading List</h5>
                                    </div>
                                </div>
                                <div class="col-3" style="padding-right: 0px">
                                    <h6>Qty</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"><span
                                                id="qty-display">-</span></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6" style="padding-left: 1rem; padding-right: 0px">
                                    <h6>Customer</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="customer-display">Customer</h5>
                                    </div>
                                </div>
                                <div class="col-6" style="padding-right: 0px">
                                    <h6>Cycle</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="cycle-display">Cycle</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6" style="padding-left: 1rem; padding-right: 0px">
                                    <h6>Internal</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;" id="int-display">
                                            -
                                        </h5>
                                    </div>
                                </div>

                                <div class="col-6" style="padding-right: 0px">
                                    <h6>Customer</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="cust-display">-
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-12" style="padding: 15px;">
                                    <h6>Scan Qr Code</h6>

                                    <input style="height: 3rem; width: 100%; background-color: white; border-radius: 20px;"
                                        height=60 id="code" class="form-control" name="code" required
                                        autocomplete="off">
                                </div>


                            </div>
                            <div class="row" style="margin-top: 1rem;">
                                <div class="col-md-12" style="padding: 15px;">

                                    <div style="height: 3rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-success"
                                            style="border-radius: 3rem; height: 3rem; width: 100%; font-size: 1.5rem;"
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

    <div class="modal fade" id="modalLoadingListScan" aria-hidden="true" aria-labelledby="modalToggleLabel2" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                </div>
                <div class="modal-body">

                    <h5 class="text-center"><b>LOADING LIST</b></h5><br>
                    <input type="text" class="form-control" id="input-loadingList" autocomplete="off">
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
    let partDetail = {};
    let part = 'part';
    let partNumber;
    let loadingListItem = [];
    let loadinglistDetail = [];

    function initApp() {
        let loadingList = localStorage.getItem('loadingList');
        if (!loadingList) {
            $('#modalLoadingListScan').on('shown.bs.modal', function() {
                $('#input-loadingList').focus();
            })
            $('#modalLoadingListScan').modal('show');

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

    function loadingListModal() {
        let loadingList = localStorage.getItem('loadingList');
        $('#input-loadingList').val('');
        setTimeout(() => {
            if (!loadingList) {
                $('#modalLoadingListScan').on('shown.bs.modal',
                    function() {
                        $('#input-loadingList').focus();
                    })
                $('#modalLoadingListScan').modal('show');
            }
        }, 1500);
    }

    function customerCheck(customer) {
        return new Promise(function(resolve, reject) {
            $.ajax({
                type: 'GET',
                url: "{{ url('pulling/customer-check/') }}" + '/' + customer,
                _token: "{{ csrf_token() }}",
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.status == 'success') {
                        // display customer
                        $('#customer-display').text(data.customer);
                        resolve();
                    } else {
                        reject();
                    }
                },
                error: function(xhr) {
                    reject(new Error(xhr.statusText));
                }
            });
        });
    }

    $(document).ready(function() {
        var token = "{{ session()->get('token') }}";
        initApp();
        $(document).on('click', function() {
            $('#code').focus();
        });

        $('#input-loadingList').keypress(function(e) {
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {

                //Check Line
                $.ajax({
                    type: 'GET',
                    url: 'http://api-dea-dev/api/v1/loading-lists/' + $(
                        this).val(),
                    _token: "{{ csrf_token() }}",
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        if (data.status == 'success') {

                            // set loading list state
                            localStorage.setItem('loadingList', 'true');

                            // loading list display
                            $('#loadingList-display').text(data.data.number);

                            // insert loading list to an array
                            data.data.items.map((item) => {
                                loadingListItem.push([
                                    item.part_number_int,
                                    item.part_number_cust,
                                    item.actual_kanban_qty,
                                    item.total_kanban_qty
                                ])

                                // make an empty array based on part number
                                partDetail[part + partNumber] = [];
                            });

                            console.log(loadingListItem);

                            // check customer if exist 
                            customerCheck(data.data.customer_code)
                                .then(function() {
                                    // cycle display
                                    $('#cycle-display').text(data.data.cycle);

                                    // scan kanban
                                    $('#code').focus();

                                })
                                .catch(function(err) {
                                    notif('error', data.message);
                                })

                            initApp();
                        } else {
                            notif('error', data.message);
                            loadingListModal();
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr)
                        if (xhr.status == 0) {
                            notif("error", 'Connection Error');
                            loadingListModal();
                            return;
                        }
                        notif("error", xhr.responseJSON.errors);
                        loadingListModal();
                    }
                });

                $('#modalLoadingListScan').modal('hide');
            }
        });

        $('#done').on('click', function() {
            localStorage.removeItem("loadingList");
            window.location.reload();
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

                console.log(barcodecomplete.length);

                if (barcodecomplete.length == 218 || barcodecomplete.length == 230) {
                    let internal = barcodecomplete.substr(41, 12);
                    let seri = barcodecomplete.substr(123, 4);

                    localStorage.setItem('seri', seri);

                    // check if kanban internal exist in loading list array
                    loadingListItem.map(function(item, index) {
                        if (internal == item[0]) {
                            // display qty
                            $('#int-display').text(item[[0]]);

                            $('#qty-display').text(`
                                ${loadinglistDetail.length}/${item[3]}
                            `);
                            // set target
                            localStorage.setItem('target', item[3]);
                            localStorage.setItem('internal', item[0]);
                        }
                    })

                } else if (barcodecomplete.length == 12) {
                    // check if kanban customer exist in the same array with kanban internal
                    let checkPair = loadingListItem.some(function(item) {
                        return (
                            item.includes(localStorage.getItem('internal')) &&
                            item.includes(barcodecomplete)
                        );
                    });

                    if (checkPair) {
                        if (loadinglistDetail.length == 0 || loadinglistDetail[
                                loadinglistDetail.length - 1].length == localStorage.getItem(
                                'target')) {

                            // push new empty array
                            loadinglistDetail.push([]);
                        }
                        // insert serial number to it
                        loadinglistDetail[loadinglistDetail.length - 1].push(localStorage.getItem(
                            'seri'));

                        // display qty
                        loadingListItem.map(function(item, index) {
                            if (localStorage.getItem('internal') == item[0]) {
                                $('#qty-display').text(`
                                        ${loadinglistDetail[index].length}/${localStorage.getItem('target')}
                                    `);
                            }
                        });
                        // display qty
                        $('#cust-display').text(barcodecomplete);
                    } else {
                        notif('error', 'Kanban tidak sesuai!');
                    }
                } else {
                    notif("error", "Kanban tidak dikenali !");
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
