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
    let request;

    function initApp() {
        let loadingList = localStorage.getItem('loadingList');
        let customer = localStorage.getItem('customer');
        let cycle = localStorage.getItem('cycle');

        if (!loadingList) {
            $('#modalLoadingListScan').on('shown.bs.modal', function() {
                $('#input-loadingList').focus();
            })
            $('#modalLoadingListScan').modal('show');

            // empty text
            $('#customer-display').text('customer');
            $('#cycle-display').text('cycle');
        } else {
            $('#loadingList-display').text(loadingList);
            $('#customer-display').text(customer);
            $('#cycle-display').text(cycle);
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
                        localStorage.setItem('customer', data.customer);
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
        initApp();
        var token = "{{ session()->get('token') }}";

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
                            // create database indexed db
                            request = window.indexedDB.open("sanTenShogo");

                            // loading list display
                            $('#loadingList-display').text(data.data.number);
                            localStorage.setItem('loadingList', data.data.number);

                            // create database schema
                            request.onupgradeneeded = function(event) {
                                const database = event.target.result;
                                const objectStore = database.createObjectStore(
                                    'loadingList');
                                var index = objectStore.createIndex('loadingListDetail',
                                    'seri');
                            }

                            // transaction
                            request.onsuccess = function(event) {
                                const database = event.target.result;
                                const transaction = database.transaction([
                                        'loadingList'
                                    ],
                                    'readwrite');
                                const objectStore = transaction.objectStore(
                                    'loadingList');
                                var index = objectStore.index('loadingListDetail');

                                data.data.items.map((item, index) => {
                                    const key = item.part_number_int;
                                    // insert into
                                    objectStore.put({
                                        key: key,
                                        internal: item.part_number_int,
                                        customer: item.part_number_cust,
                                        actual_qty: item
                                            .actual_kanban_qty,
                                        total_qty: item
                                            .total_kanban_qty,
                                        seri: []
                                    }, key);
                                });

                                // check customer if exist 
                                customerCheck(data.data.customer_code)
                                    .then(function() {
                                        // cycle display
                                        $('#cycle-display').text(data.data.cycle);
                                        localStorage.setItem('cycle', data
                                            .data.cycle);

                                        // scan kanban
                                        $('#code').focus();

                                    })
                                    .catch(function(err) {
                                        notif('error', data.message);
                                    })

                                // Close the db when the transaction is done
                                transaction.oncomplete = function() {
                                    database.close();
                                };
                                $('#code').focus();
                            }
                            // create handler
                            request.onerror = function(event) {
                                console.log("error: " + event.message);
                            }
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
            localStorage.removeItem("customer");
            localStorage.removeItem("internal");
            localStorage.removeItem("cycle");
            localStorage.removeItem("seri");
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

                if (barcodecomplete.length == 218 || barcodecomplete.length == 230) {
                    let internal = barcodecomplete.substr(41, 12);
                    let seri = barcodecomplete.substr(123, 4);

                    // initiate database
                    request = window.indexedDB.open("sanTenShogo");

                    // transaction
                    request.onsuccess = function(event) {
                        const database = event.target.result;
                        const transaction = database.transaction([
                                'loadingList'
                            ],
                            'readonly');
                        const objectStore = transaction.objectStore(
                            'loadingList');

                        objectStore.openCursor().onsuccess = function(event) {
                            const cursor = event.target.result;
                            if (cursor) {
                                const record = cursor.value;

                                // check if kanban internal exist in loading list record
                                if (internal == record.internal) {
                                    // display internal
                                    $('#int-display').text(record.internal);

                                    // display current qty
                                    $('#qty-display').text(`
                                        ${record.seri.length}/${record.total_qty}
                                    `);
                                    // set local storage for internal kanban and serial number
                                    localStorage.setItem('internal', record.internal);
                                    localStorage.setItem('seri', seri);
                                }
                                cursor.continue();
                            } else {
                                console.log('iteration complete');
                            }
                        }

                        // when complete
                        request.oncomplete = function(event) {
                            database.close();
                        }
                    }
                    // Event handler for a failed database connection
                    request.onerror = function(event) {
                        console.log('Failed to open database');
                    };

                } else if (barcodecomplete.length == 12) {

                    // check if already scan internal kanban
                    if (localStorage.getItem('internal')) {
                        // initialize databae connection
                        request = window.indexedDB.open("sanTenShogo");

                        // display customer
                        $('#cust-display').text(barcodecomplete);

                        request.onsuccess = function(event) {
                            const database = event.target.result;
                            const transaction = database.transaction(['loadingList'], 'readwrite');
                            const objectStore = transaction.objectStore('loadingList');

                            objectStore.openCursor().onsuccess = function(event) {
                                const cursor = event.target.result;
                                if (cursor) {
                                    // get spesific primary key
                                    const primaryKey = cursor.primaryKey
                                    if (primaryKey == localStorage.getItem('internal')) {
                                        // check pair only in spesific key
                                        objectStore.get(primaryKey).onsuccess = function(
                                            event) {
                                            const cursor = event.target.result;
                                            if (cursor) {
                                                // check if kanban internal and customer in the same object or record
                                                if (cursor['internal'] == localStorage
                                                    .getItem(
                                                        'internal') && cursor['customer'] ==
                                                    barcodecomplete) {
                                                    // check actual qty of spesific part number
                                                    if (cursor['seri'].length < cursor[
                                                            'total_qty']) {
                                                        // check if serial number is not scanned before
                                                        if (!cursor['seri'].includes(
                                                                localStorage
                                                                .getItem('seri'))) {
                                                            // push kanban serial number to array seri
                                                            cursor['seri'].push(localStorage
                                                                .getItem(
                                                                    'seri'));
                                                            // update the object
                                                            objectStore.put(cursor,
                                                                    primaryKey)
                                                                .onsuccess = function(
                                                                    event) {
                                                                    // udpate the qty display
                                                                    $('#qty-display').text(`
                                                                            ${cursor.seri.length}/${cursor['total_qty']}
                                                                        `);
                                                                    // reset internal and customer display
                                                                    localStorage.removeItem(
                                                                        'internal');
                                                                    localStorage.removeItem(
                                                                        'seri');
                                                                    $('#int-display').text(
                                                                        '-');
                                                                    $('#cust-display').text(
                                                                        '-');
                                                                }
                                                            // error handling
                                                            objectStore.put(cursor,
                                                                    primaryKey)
                                                                .onerror = function(event) {
                                                                    notif('error',
                                                                        'Kanban tidak sesuai!'
                                                                    );
                                                                }
                                                        } else {
                                                            notif('error',
                                                                'Seri kanban sudah discan!'
                                                            );
                                                        }
                                                    }
                                                } else {
                                                    notif('error',
                                                        'Kanban tidak sesuai!');
                                                }
                                            }
                                        }
                                        // error handling
                                        objectStore.get(primaryKey).onerror = function(event) {
                                            notif('error',
                                                'Kanban tidak sesuai!');
                                        }
                                    }
                                    cursor.continue();
                                } else {
                                    console.log('iteration complete');
                                }
                            }
                        }
                    } else {
                        notif('error',
                            'Scan kanban internal dulu!');
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
