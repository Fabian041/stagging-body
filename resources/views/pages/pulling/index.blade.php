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
                            <div class="row mt-1" id="list">
                                <h6 id="loadingList" style="padding-left: 1rem">Loading List</h6>
                                <li class="col-12" style="padding-left: 1rem; padding-right: 0px; list-style-type: none;"
                                    id="loadingListContainerSample">
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="loadingList-display"></h5>
                                    </div>
                                </li>
                            </div>
                            <div class="row mt-2">
                                <div class="col-9" style="padding-left: 1rem; padding-right: 0px">
                                    <h6>Customer</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h6 class="text-center " style="padding-top: .9rem; color: white;"
                                            id="customer-display">Customer</h6>
                                    </div>
                                </div>
                                <div class="col-3" style="padding-right: 0px">
                                    <h6>Cycle</h6>
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="cycle-display">Cycle</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12" style="padding-right: 0px">
                                    <div
                                        style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px; padding:10.5px; padding-left:12px">
                                        <small class="badge badge-dark"
                                            style="color:#ffffff; display:inline;">Quantity</small>
                                        <h5 style="color: #ffffff; display:inline; padding-left:4.5rem">
                                            <span id="qty-display">-</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
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
                            <div class="row">
                                <div class="col-md-12" style="padding: 15px;">
                                    <input
                                        style="height: 2.4rem; width: 100%; background-color: white; border-radius: 20px;"
                                        height=60 id="code" class="form-control" name="code" required
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="padding: 15px; padding-top:0">
                                    <div style="height: 2.4rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-success"
                                            style="border-radius: 3rem; height: 3rem; width: 100%; font-size: 1.5rem;"
                                            id="done">Selesai</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col">
                                    <span class="badge badge-pill badge-danger">0/20 <span> - Belum Lengkap</span></span>
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

    function loadingListModal2() {
        $('#input-loadingList').val('');
        setTimeout(() => {
            $('#modalLoadingListScan').on('shown.bs.modal',
                function() {
                    $('#input-loadingList').focus();
                })
            $('#modalLoadingListScan').modal('show');
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

        $('#loadingList').on('click', function() {
            loadingListModal2();
        });

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

                            // check if loading list have same manifest code (pds number)
                            if (localStorage.getItem('pdsNumber')) {
                                if (data.data.pds_number != localStorage.getItem(
                                        'pdsNumber')) {
                                    notif('error', 'Loading list tidak sesuai!');
                                    return;
                                }
                            }

                            // remove example display
                            $('#loadingListContainerSample').remove();

                            // loading list display
                            $('#list').append(
                                `<li class="col-12 mt-2"
                                    style="padding-left: 1rem; padding-right: 0px; list-style-type: none;"
                                    id="loadingListContainer">
                                    <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 20px;"
                                        id="loadingList">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="loadingList-display">${data.data.number}</h5>
                                    </div>
                                </li>`
                            );

                            localStorage.setItem(data.data.number, data.data.number);
                            localStorage.setItem('pdsNumber', data.data.pds_number);

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

            request = window.indexedDB.open("sanTenShogo");

            // transaction
            request.onsuccess = function(event) {
                const database = event.target.result;
                const transaction = database.transaction(['loadingList'], 'readonly');
                const objectStore = transaction.objectStore('loadingList');
                let flag = true;

                objectStore.openCursor().onsuccess = function(event) {
                    let cursor = event.target.result;
                    if (cursor) {
                        const record = cursor.value;
                        // check if the loading list is fullfilled by check each array seri
                        if (record.seri.length < record.total_qty) {
                            flag = false;
                            return;
                        }
                        cursor.continue();
                    }
                }

                // when transaction complete
                transaction.oncomplete = function() {
                    if (flag) {
                        // save to database 
                        $.ajax({
                            type: 'GET',
                            url: "{{ url('pulling/store/') }}",
                            _token: "{{ csrf_token() }}",
                            data: {
                                customer: localStorage.getItem('customer'),
                                loadingList: localStorage.getItem('loadingList'),
                                pdsNumber: localStorage.getItem('pdsNumber'),
                                cycle: localStorage.getItem('cycle'),
                            },
                            dataType: 'json',
                            success: function(data) {
                                console.log(data);
                                localStorage.removeItem("loadingList");
                                localStorage.removeItem("customer");
                                localStorage.removeItem("internal");
                                localStorage.removeItem("cycle");
                                localStorage.removeItem("seri");
                                window.location.reload();

                                notif('success', 'Pulling berhasil!');
                            },
                            error: function(xhr) {
                                notif('eror', xhr.message);
                            }
                        });
                    } else {
                        notif('error', 'loading list belum lengkap!');
                    }
                }
            }
        });

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        function checkInternalAndCustomer(objectStore, cursor, barcodecomplete, primaryKey) {
            let internal = cursor['internal'];
            let customer = cursor['customer'];
            let arraySeri = cursor['seri'];
            let totalQty = cursor['total_qty'];
            let currentSeri = localStorage.getItem('seri');
            let isSameObject = false;

            for (const key in cursor) {
                if (cursor[key] === localStorage.getItem('internal')) {
                    // Value1 found, check if Value2 is also in the object
                    if (Object.values(cursor).includes(barcodecomplete)) {
                        isSameObject = true;
                        break;
                    }
                }
            }

            // check if kanban internal and customer in the same object
            if (!isSameObject) {
                notif('error', 'Kanban tidak sesuai!');
                return;
            }

            // check actual qty of spesific part number by compare the current length seri and total_qty
            if (arraySeri.length >= totalQty) {
                notif('error', 'Part number sudah complete!');
                return;
            }

            // push kanban serial number to array seri
            arraySeri.push(currentSeri);
            // update the object
            objectStore.put(cursor, primaryKey).onsuccess = function(event) {
                // udpate the qty display
                $('#qty-display').text(`${arraySeri.length}/${totalQty}`);

                // display customer
                $('#cust-display').text(barcodecomplete);

                // reset internal and customer display
                localStorage.removeItem('internal');
                localStorage.removeItem('seri');
                $('#int-display').text('-');
                $('#cust-display').text('-');
            }
            // error handling
            objectStore.put(cursor, primaryKey).onerror = function(event) {
                notif('error', 'Kanban tidak sesuai!');
            };
        }

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

                    console.log(seri);

                    // initiate database
                    request = window.indexedDB.open("sanTenShogo");

                    // transaction
                    request.onsuccess = function(event) {
                        const database = event.target.result;
                        const transaction = database.transaction(['loadingList'], 'readonly');
                        const objectStore = transaction.objectStore('loadingList');
                        let isAvailable = false;

                        objectStore.openCursor().onsuccess = function(event) {
                            const cursor = event.target.result;
                            if (cursor) {
                                const record = cursor.value;

                                // check if kanban internal exist in loading list record
                                if (internal == record.internal) {
                                    // check quantity in spesific part number
                                    if (record.seri.length >= record.total_qty) {
                                        notif('error', 'Part number sudah complete!');
                                        return;
                                    }

                                    // check if serial number kanban exist in spesific part number
                                    if (record.seri.includes(seri)) {
                                        notif('error', 'Seri kanban sudah discan!');
                                        return;
                                    }

                                    // set flag
                                    isAvailable = true;

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

                                // check if the kanban internal is available
                                if (!isAvailable) {
                                    notif('error', 'Kanban tidak sesuai!')
                                }
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

                    console.log(barcodecomplete);
                    // check if already scan internal kanban
                    if (!localStorage.getItem('internal')) {
                        notif('error', 'Scan kanban internal dulu!');
                        return;
                    }

                    // initialize databae connection
                    request = window.indexedDB.open("sanTenShogo");

                    request.onsuccess = function(event) {
                        const database = event.target.result;
                        const transaction = database.transaction(['loadingList'], 'readwrite');
                        const objectStore = transaction.objectStore('loadingList');
                        let isAvailable = false;

                        objectStore.openCursor().onsuccess = function(event) {
                            const cursor = event.target.result;
                            if (cursor) {
                                // get spesific primary key
                                let primaryKey = cursor.primaryKey
                                if (primaryKey == localStorage.getItem('internal')) {
                                    // set flag
                                    isAvailable = true;

                                    // check pair only in spesific key
                                    objectStore.get(primaryKey).onsuccess = function(event) {
                                        const cursor = event.target.result;
                                        if (cursor) {
                                            checkInternalAndCustomer(objectStore, cursor,
                                                barcodecomplete, primaryKey);
                                            return;
                                        } else {
                                            console.log('Iteration complete');
                                        }
                                    }
                                    // error handling
                                    objectStore.get(primaryKey).onerror = function(event) {
                                        notif('error',
                                            'Kanban tidak sesuai: ' + event.target.error
                                        );
                                    }
                                }
                                cursor.continue();
                            } else {
                                console.log('iteration complete');

                                if (!isAvailable) {
                                    notif('error', 'Kanban tidak ditemukan!')
                                }
                            }
                        }

                        // error handling
                        objectStore.openCursor().onerror = function(event) {
                            notif('error', event.target.error);
                        }
                    }

                    // error handling
                    request.onerror = function(event) {
                        notif('error', event.target.error);
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
