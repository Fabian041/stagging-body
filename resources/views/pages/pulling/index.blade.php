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
                                <small class="text-right badge badge-primary ml-2" style="color:#ffffff; display:inline;"
                                    id="total-ll">0</small>
                                <li class="col-12 mt-2"
                                    style="padding-left: 1rem; padding-right: 0px; list-style-type: none;"
                                    id="loadingListContainerSample">
                                    <div style="height: 2rem; width: 100%; background-color: #03b1fc; border-radius: 4px;">
                                        <h5 class="text-center " style="padding-top: .8rem; color: white;"
                                            id="loadingList-display"></h5>
                                    </div>
                                </li>
                            </div>
                            <div class="row mt-2">
                                <div class="col-9" style="padding-left: 1rem; padding-right: 0px">
                                    <div style="height: 5rem; width: 100%; background-color: #03b1fc; border-radius: 4px;">
                                        <h6 class="p-2" style="color: #ffffff; font-size:12px; font-weight:lighter">
                                            Customer</h6>
                                        <h6 class="text-center " style="padding-top: 0rem; color: white;"
                                            id="customer-display">Customer</h6>
                                    </div>
                                </div>
                                <div class="col-3" style="padding-left: .5rem; padding-right: 0px">
                                    <div style="height: 5rem; width: 100%; background-color: #03b1fc; border-radius: 4px;">
                                        <h6 class="p-2" style="color: #ffffff;font-size:12px; font-weight:lighter">Cycle
                                        </h6>
                                        <h6 class="text-center " style="padding-top: 0rem; color: white;"
                                            id="cycle-display">Cycle</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12" style="padding-right: 0px">
                                    <div
                                        style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 4px; padding:10.5px; padding-left:12px">
                                        <small class="badge badge-dark"
                                            style="color:#ffffff; display:inline; border-radius:4px !important;">Quantity</small>
                                        <h5 style="color: #ffffff; display:inline; padding-left:4.5rem">
                                            <span id="qty-display">-</span>
                                        </h5>
                                        <div class="bg-warning"
                                            style="display:inline-block; margin-left:260px; margin-top:-25px; border-radius:10%; width: 60px; height:30px"
                                            id="indicator">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6" style="padding-right: 0px">
                                    <div style="height: 5rem; width: 100%; background-color: #03b1fc; border-radius: 4px;">
                                        <h6 class="p-2" style="color: #ffffff; font-size:12px; font-weight:lighter">
                                            Kanban Customer</h6>
                                        <h6 class="text-center " style="padding-top: 0rem; color: white;" id="cust-display">
                                            -
                                        </h6>
                                    </div>
                                </div>
                                <div class="col-6" style="padding-left: .5rem; padding-right: 0px">
                                    <div style="height: 5rem; width: 100%; background-color: #03b1fc; border-radius: 4px;">
                                        <h6 class="p-2" style="color: #ffffff; font-size:12px; font-weight:lighter">
                                            Kanban Internal</h6>
                                        <h6 class="text-center " style="padding-top: 0rem; color: white;" id="int-display">
                                            -
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="padding: 15px; padding-right: 0px">
                                    <input style="height: 2.4rem; width: 100%; background-color: white; border-radius: 4px;"
                                        height=60 id="code" class="form-control" name="code" required
                                        autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5" style="padding: 15px; padding-top:0; padding-right: 0px">
                                    <div style="height: 2.4rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-outline-danger"
                                            style="border-radius: .2rem; height: 3rem; width: 100%; font-size: 1.2rem;"
                                            id="delay">Delay</button>
                                    </div>
                                </div>
                                <div class="col-7" style="padding: 15px; padding-top:0; padding-right: 0px">
                                    <div style="height: 2.4rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-success"
                                            style="border-radius: .2rem; height: 3rem; width: 100%; font-size: 1.2rem; box-shadow: rgba(0, 0, 0, 0.45) 0px 25px 20px -20px;"
                                            id="done">Selesai</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-2">
                                <div class="col">
                                    <span class="badge badge-pill" id="pullingStatusContainer"
                                        style="border-radius: .2rem;">
                                        <span id="pullingQty" style="color: #ffffff"></span> <span id="pullingStatus"
                                            style="color: #ffffff"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal fade" id="modalLoadingListScan" aria-hidden="true" aria-labelledby="modalToggleLabel2"
        tabindex="-1">
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
    <div class="modal fade gfont" id="notifModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" id="divNotif" style="border-radius: 15px !important;">
                <div class="modal-body text-center">
                    <span style="color: white; font-size: 30pt" id="notif"> Scan Part</span>
                </div>
            </div>
        </div>
    </div>
    <audio id="not-match-sound">
        <source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="not-match-ll-sound">
        <source src={{ asset('assets/sounds/notMatch-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="unknown-sound">
        <source src={{ asset('assets/sounds/unknown.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="unknown-ll-sound">
        <source src={{ asset('assets/sounds/unknown-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="already-scan-sound">
        <source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="already-scan-ll-sound">
        <source src={{ asset('assets/sounds/already-scan-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="uncomplete-ll-sound">
        <source src={{ asset('assets/sounds/uncomplete-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="fullfilled-sound">
        <source src={{ asset('assets/sounds/fullfilled.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="scan-customer-first-sound">
        <source src={{ asset('assets/sounds/scan-customer-first.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="finish-pulling-sound">
        <source src={{ asset('assets/sounds/finish-pulling.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="already-pulled-sound">
        <source src={{ asset('assets/sounds/already-pulled.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    let line = '';
    let partDetail = {};
    let part = 'part';
    let partNumber;
    let loadingListItem = [];
    let loadinglistDetail = [];


    function notMatchSound() {
        var sound = document.getElementById("not-match-sound");
        sound.play();
    }

    function notMatchLlSound() {
        var sound = document.getElementById("not-match-ll-sound");
        sound.play();
    }

    function unknownSound() {
        var sound = document.getElementById("unknown-sound");
        sound.play();
    }

    function unknownLlSound() {
        var sound = document.getElementById("unknown-ll-sound");
        sound.play();
    }

    function alreadyScanSound() {
        var sound = document.getElementById("already-scan-sound");
        sound.play();
    }

    function alreadyPulledSound() {
        var sound = document.getElementById("already-pulled-sound");
        sound.play();
    }

    function alreadyScanLlSound() {
        var sound = document.getElementById("already-scan-ll-sound");
        sound.play();
    }

    function uncompleteLlSound() {
        var sound = document.getElementById("uncomplete-ll-sound");
        sound.play();
    }

    function fullfilledSound() {
        var sound = document.getElementById("fullfilled-sound");
        sound.play();
    }

    function scanCustomerFirstSound() {
        var sound = document.getElementById("scan-customer-first-sound");
        sound.play();
    }

    function finishPullingSound() {
        var sound = document.getElementById("finish-pulling-sound");
        sound.play();
    }

    // sextract the loading list number from the key
    function extractLoadingListNumber(key) {
        const prefix = "ll_";
        return key.substring(prefix.length);
    }

    // retrieve the loading list number from localStorage
    function getLoadingListNumber() {
        let loadingListNumber = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("ll_")) {
                loadingListNumber.push(extractLoadingListNumber(key));
            }
        }
        // Return a default value if no loading list number is found
        return loadingListNumber;
    }

    function initApp() {

        let customer = localStorage.getItem('customer');
        let cycle = localStorage.getItem('cycle');
        let loadingList = getLoadingListNumber();
        checkLoadingList();
        // iterate local storage
        for (key in loadingList) {
            // remove example display
            $('#loadingListContainerSample').remove();

            // loading list display
            $('#list').append(
                `<li class="col-12 mt-2"
                    style="padding-left: 1rem; padding-right: 0px; list-style-type: none;"
                    id="loadingListContainer">
                        <div style="height: 2rem; width: 100%; background-color: #03b1fc; border-radius: 4px;"
                        id="loadingList">
                            <h6 class="text-center " style="padding-top: .5rem; color: white;"
                            id="loadingList-display">${loadingList[key]}</h6>
                        </div>
                    </li>`
            );

            pullingQuantity();
            $('#customer-display').text(customer);
            $('#cycle-display').text(cycle);
        }

        if (getLoadingListNumber().length == 0) {
            $('#modalLoadingListScan').on('shown.bs.modal', function() {
                $('#input-loadingList').focus();
            })
            $('#modalLoadingListScan').modal('show');

            // empty text
            $('#customer-display').text('customer');
            $('#cycle-display').text('cycle');
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

    function checkLoadingList() {
        let pds = localStorage.getItem('pds_local');
        let ll = [];
        // initialize database
        request = window.indexedDB.open(pds);

        request.onsuccess = function(event) {
            const database = event.target.result;
            const transaction = database.transaction(["loadingList"], 'readonly');
            const objectStore = transaction.objectStore("loadingList");

            objectStore.openCursor().onsuccess = function(event) {
                let cursor = event.target.result;
                if (cursor) {

                    // check each loading list
                    if (!ll.includes(cursor.value.loading_list_number)) {
                        ll.push(cursor.value.loading_list_number);
                    }

                    cursor.continue();
                } else {
                    $('#total-ll').text(ll.length);
                }
            }

            // Close the db when the transaction is done
            transaction.oncomplete = function() {
                database.close();
            };

            objectStore.openCursor().onerror = function(event) {
                notif('error', event.message);
                return;
            }
        }
    }

    function customerCharStore(customer) {
        $.ajax({
            type: 'GET',
            url: "{{ url('pulling/customer-check/') }}" + '/' + customer,
            _token: "{{ csrf_token() }}",
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.status == 'success') {

                    // save all data about customer in local storage
                    localStorage.setItem('char_first', data.first);
                    localStorage.setItem('char_length', data.length);
                    localStorage.setItem('char_total', data.total);

                } else {
                    notif('error', data.message);
                    loadingListModal();
                }
            },
            error: function(xhr) {
                reject(new Error(xhr.statusText));
            }
        });
    }

    function errorStore() {
        $.ajax({
            type: 'GET',
            url: "{{ route('error.store') }}",
            _token: "{{ csrf_token() }}",
            dataType: 'json',
            success: function(data) {
                console.log("Error recorded");
            },
            error: function(xhr) {
                console.log(xhr);
            }
        });
    }

    function pullingQuantity() {
        let pds = localStorage.getItem('pds_local');
        // initialize database
        request = window.indexedDB.open(pds);

        // transaction 
        let totalActual = 0;
        let totalTarget = 0;
        request.onsuccess = function(event) {
            const database = event.target.result;
            const transaction = database.transaction(["loadingList"], 'readonly');
            const objectStore = transaction.objectStore("loadingList");

            objectStore.openCursor().onsuccess = function(event) {
                let cursor = event.target.result;
                if (cursor) {
                    const record = cursor.value;

                    // get total seri scanned
                    totalActual += parseInt(record.seri.length);

                    // get total target
                    totalTarget += parseInt(record.total_qty);


                    cursor.continue();
                } else {
                    // display the total and target
                    $('#pullingQty').text(`${totalActual}/${totalTarget}`);

                    // check qty for pulling statuss
                    if (totalActual == 0) {
                        $('#pullingStatusContainer').addClass('bg-danger');
                        $('#pullingStatus').text(' - Ayo Pulling!')
                    } else if (totalActual > 0 && totalActual < totalTarget) {
                        $('#pullingStatusContainer').removeClass('bg-danger');
                        $('#pullingStatusContainer').addClass('bg-warning');
                        $('#pullingStatus').text(' - Belum Lengkap!')
                    } else {
                        $('#pullingStatusContainer').removeClass('bg-warning');
                        $('#pullingStatusContainer').addClass('bg-success');
                        $('#pullingStatus').text(' - Pulling Selesai!')
                    }
                }
            }

            objectStore.openCursor().onerror = function(event) {
                notif('error', event.message);
                return;
            }

            // Close the db when the transaction is done
            transaction.oncomplete = function() {
                database.close();
            };
        }

        request.onerror = function(event) {
            notif('error', 'Failed to connect to database!')
            return;
        }
    }

    $(document).ready(function() {
        initApp();
        $('#code').focus();

        $('#loadingList').on('click', function() {
            loadingListModal2();
        });

        var token = "{{ session()->get('token') }}";

        $('#input-loadingList').keypress(function(e) {
            let loadingList = getLoadingListNumber();
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
                        if (data.status == 'success') {
                            // objectStor name is based on pds_number
                            let pds = data.data.pds_number;
                            let ll = data.data.number;

                            // create database indexed db
                            request = window.indexedDB.open(pds);

                            // check if loading list already exists
                            if (loadingList.includes(data.data.number)) {
                                notif('error', 'Loading list sudah discan!');
                                alreadyScanLlSound();
                                return;
                            }

                            // check if already pulled
                            if (!data.data.items[0].hasOwnProperty(
                                    'total_kanban_qty')) {
                                notif('error',
                                    'Loading list sudah pernah dipulling'
                                );
                                loadingListModal();
                                alreadyPulledSound();
                                return;
                            }

                            // check if loading list have same manifest code (pds number)
                            if (localStorage.getItem('pdsNumber')) {
                                if (data.data.pds_number != localStorage.getItem(
                                        'pdsNumber')) {
                                    notif('error', 'Loading list tidak sesuai!');

                                    // unknown ll sound
                                    notMatchLlSound();

                                    return false;
                                }
                            }

                            let pdsLocal = localStorage.setItem('pds_local', pds);
                            localStorage.setItem('ll_' + data.data.number, data.data
                                .number);
                            localStorage.setItem('pdsNumber', data.data.pds_number);

                            // remove example display
                            $('#loadingListContainerSample').remove();

                            // loading list display
                            $('#list').append(
                                `<li class="col-12 mt-2"
                                        style="padding-left: 1rem; padding-right: 0px; list-style-type: none;"
                                        id="loadingListContainer">
                                        <div style="height: 2rem; width: 100%; background-color: #03b1fc; border-radius: 4px;"
                                            id="loadingList">
                                            <h6 class="text-center " style="padding-top: .5rem; color: white;"
                                                id="loadingList-display">${data.data.number}</h6>
                                        </div>
                                    </li>`
                            );

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
                                    const key = item.part_number_cust;

                                    const getRequest = objectStore.get(key);

                                    getRequest.onsuccess = function(event) {
                                        const existingData = event.target
                                            .result;

                                        if (!existingData) {
                                            objectStore.put({
                                                key: key,
                                                loading_list_number: ll,
                                                internal: item
                                                    .part_number_int,
                                                customer: item
                                                    .part_number_cust,
                                                qty_per_kbn: item
                                                    .total_qty /
                                                    item
                                                    .total_kanban_qty,
                                                actual_qty: item
                                                    .actual_kanban_qty,
                                                total_qty: item
                                                    .total_kanban_qty,
                                                seri: []
                                            }, key);
                                        }
                                    };

                                    getRequest.onerror = function(event) {
                                        notif(event.data.error);
                                    };
                                });

                                // check customer if exist 
                                customerCheck(data.data.customer_code)
                                    .then(function() {
                                        // cycle display
                                        $('#cycle-display').text(data.data.cycle);
                                        localStorage.setItem('cycle', data
                                            .data.cycle);

                                        // calculate total quantity of the orders
                                        pullingQuantity();

                                        // scan kanban
                                        $('#code').focus();

                                    })
                                    .catch(function(err) {
                                        notif('error', data.message);
                                    })

                                // loadingList qty
                                checkLoadingList();

                                // customer check char
                                customerCharStore(data.data.customer_code);

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

        $('#delay').on('click', function() {
            localStorage.clear();
            window.location.reload();
        });

        $('#done').on('click', function() {
            let loadingList = getLoadingListNumber();
            let pds = localStorage.getItem('pds_local');
            let formData = new FormData();
            request = window.indexedDB.open(pds);

            // transaction
            request.onsuccess = function(event) {
                const database = event.target.result;
                const transaction = database.transaction(["loadingList"], 'readonly');
                const objectStore = transaction.objectStore("loadingList");
                let loadingList = {};
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

                        let items = [];
                        for (let i = 0; i < record.seri.length; i++) {
                            let item = {
                                part_number_internal: record.internal,
                                part_number_customer: record.customer,
                                serial_number: record.seri[i]
                            };
                            items.push(item);
                        }

                        // store in loading list array
                        const loadingListNumber = record.loading_list_number;
                        if (loadingList.hasOwnProperty(loadingListNumber)) {
                            loadingList[loadingListNumber].push(...items);
                        } else {
                            loadingList[loadingListNumber] = items;
                        }

                        cursor.continue();
                    }
                }

                // when transaction complete
                transaction.oncomplete = function() {
                    if (flag) {

                        // send loading list data to backend
                        $.ajax({
                            type: 'GET',
                            url: "{{ route('pulling.post') }}",
                            _token: "{{ csrf_token() }}",
                            data: {
                                loadingList: loadingList,
                                token: token
                            },
                            dataType: 'json',
                            success: function(data) {
                                console.log(data);
                                const deleteRequest = indexedDB.deleteDatabase(pds);

                                deleteRequest.onsuccess = function() {
                                    notif('success', 'Pulling berhasil!');
                                    finishPullingSound();
                                };

                                deleteRequest.onerror = function(event) {
                                    notif('error: ', event);
                                };
                            },
                            error: function(xhr) {
                                notif('eror', xhr.message);
                            }
                        });

                        let ll = [];
                        let data = [];

                        // initialize database
                        request = window.indexedDB.open(pds);

                        request.onsuccess = function(event) {
                            const database = event.target.result;
                            const transaction = database.transaction(["loadingList"],
                                'readonly');
                            const objectStore = transaction.objectStore("loadingList");

                            objectStore.openCursor().onsuccess = function(event) {
                                let cursor = event.target.result;
                                if (cursor) {

                                    // check each loading list
                                    if (!ll.includes(cursor.value
                                            .loading_list_number)) {
                                        ll.push(cursor.value.loading_list_number);
                                    }

                                    cursor.continue();
                                } else {
                                    for (let index = 0; index < ll.length; index++) {
                                        item = {
                                            customer: localStorage.getItem(
                                                'customer'),
                                            loadingList: ll[index],
                                            pdsNumber: localStorage.getItem(
                                                'pdsNumber'),
                                            cycle: localStorage.getItem('cycle'),
                                        }
                                        data.push(item)
                                    }
                                    for (let index = 0; index < data.length; index++) {
                                        $.ajax({
                                            type: 'GET',
                                            url: "{{ url('pulling/store') }}",
                                            _token: "{{ csrf_token() }}",
                                            data: {
                                                customer: data[index].customer,
                                                loadingList: data[index]
                                                    .loadingList,
                                                pdsNumber: data[index]
                                                    .pdsNumber,
                                                cycle: data[index].cycle
                                            },
                                            dataType: 'json',
                                            success: function(data) {
                                                console.log(data);
                                                localStorage.clear();
                                                window.location.reload();
                                            },
                                            error: function(xhr) {
                                                notif('eror', xhr.message);
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    } else {
                        notif('error', 'loading list belum lengkap!');
                        uncompleteLlSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                    }
                }
            }
        });

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        function checkInternalAndCustomer(objectStore, cursor, internal, primaryKey, seri) {
            let customer = cursor['customer'];
            let qty_per_kbn = cursor['qty_per_kbn'];
            let arraySeri = cursor['seri'];
            let totalQty = cursor['total_qty'];
            let isSameObject = false;

            for (const key in cursor) {
                if (cursor[key] === localStorage.getItem('customerPart')) {
                    // Value1 found, check if Value2 is also in the object
                    if (Object.values(cursor).includes(internal.trimEnd())) {
                        isSameObject = true;
                        break;
                    }
                }
            }

            // check if kanban internal and customer in the same object
            if (!isSameObject) {
                // error indicator
                $('#indicator').removeClass('bg-success');
                $('#indicator').removeClass('bg-warning');
                $('#indicator').addClass('bg-danger');
                notif('error', 'Kanban tidak sesuai!');

                // error log
                errorStore();

                // notification sound
                notMatchSound();

                setInterval(() => {
                    $('#code').focus();
                }, 1000);
                return;
            }

            // check if serial number kanban exist in spesific part number
            if (arraySeri.includes(seri)) {
                // error indicator
                $('#indicator').removeClass('bg-success');
                $('#indicator').removeClass('bg-warning');
                $('#indicator').addClass('bg-danger');
                alreadyScanSound();
                notif('error', 'Seri kanban sudah discan!');
                setInterval(() => {
                    $('#code').focus();
                }, 1000);
                return;
            }

            // check actual qty of spesific part number by compare the current length seri and total_qty
            if (arraySeri.length >= totalQty) {
                // error indicator
                $('#indicator').removeClass('bg-success');
                $('#indicator').removeClass('bg-warning');
                $('#indicator').addClass('bg-danger');
                notif('error', 'Part number sudah complete!');
                fullfilledSound();
                setInterval(() => {
                    $('#code').focus();
                }, 1000);
                return;
            }

            // push kanban serial number to array seri
            arraySeri.push(seri);

            // update the object
            objectStore.put(cursor, primaryKey).onsuccess = function(event) {
                // hit API to create checkout transaction after pulling
                $.ajax({
                    type: 'GET',
                    url: "{{ route('pulling.mutation') }}",
                    _token: "{{ csrf_token() }}",
                    data: {
                        internalPart: internal.trimEnd(),
                        serialNumber: seri,
                        qty_per_kbn: qty_per_kbn,
                    },
                    contentType: 'application/json',
                    success: function(data) {
                        console.log(data.status);
                        if (data.status == 'success') {
                            // udpate the qty display
                            $('#qty-display').text(`${arraySeri.length}/${totalQty}`);

                            // display internal
                            $('#int-display').text(internal);
                            $('#cust-display').text('-');

                            // success indicator
                            $('#indicator').removeClass('bg-danger');
                            $('#indicator').removeClass('bg-warning');
                            $('#indicator').addClass('bg-success');

                            // display total quantity
                            pullingQuantity();

                            // reset customer local storage
                            localStorage.removeItem('customerPart');
                        } else if (data.status == 'error') {
                            notif('error', data.message);
                        } else if (data.status == 'notExists') {
                            notif('error', data.message);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        notif('error', xhr.getResponseHeader());
                    }
                })
            }

            // error handling
            objectStore.put(cursor, primaryKey).onerror = function(event) {
                // error indicator
                $('#indicator').removeClass('bg-success');
                $('#indicator').removeClass('bg-warning');
                $('#indicator').addClass('bg-danger');
                notif('error', 'Kanban tidak sesuai!');

                // notification sound
                notMatchSound();

                setInterval(() => {
                    $('#code').focus();
                }, 1000);
            };
        }

        $('#code').keypress(function(e) {
            let pds = localStorage.getItem('pds_local');
            e.preventDefault();
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) // Enter key hit 
            {
                barcodecomplete = barcode;
                barcode = "";
                console.log(barcodecomplete);
                console.log(barcodecomplete.length);

                // check for MMKI
                if (localStorage.getItem('customer') == 'MMKI') {
                    // initiate database
                    request = window.indexedDB.open(pds);

                    // transaction
                    request.onsuccess = function(event) {
                        const database = event.target.result;
                        const transaction = database.transaction(["loadingList"], 'readonly');
                        const objectStore = transaction.objectStore("loadingList");
                        let isAvailable = false;

                        objectStore.openCursor().onsuccess = function(event) {
                            const cursor = event.target.result;
                            if (cursor) {
                                const record = cursor.value;
                                // check if kanban customer exist in loading list record
                                if (barcodecomplete == record.customer) {
                                    // check quantity in spesific part number
                                    if (record.seri.length >= record.total_qty) {
                                        notif('error', 'Part number sudah complete!');
                                        fullfilledSound();
                                        $('#indicator').removeClass('bg-success');
                                        $('#indicator').removeClass('bg-warning');
                                        $('#indicator').addClass('bg-danger');
                                        setInterval(() => {
                                            $('#code').focus();
                                        }, 1000);
                                        return;
                                    }
                                    // set flag
                                    isAvailable = true;
                                    // display customer
                                    $('#cust-display').text(record.customer);
                                    $('#int-display').text('-');

                                    // set indicator
                                    $('#indicator').removeClass('bg-success');
                                    $('#indicator').removeClass('bg-danger');
                                    $('#indicator').addClass('bg-warning');

                                    // display current qty
                                    $('#qty-display').text(`
                                        ${record.seri.length}/${record.total_qty}
                                    `);
                                    // set local storage for customer kanban
                                    localStorage.setItem('customerPart', record.customer);
                                }
                                cursor.continue();
                            } else {
                                console.log('iteration complete');
                                // check if the kanban customer is available
                                if (!isAvailable) {
                                    notif('error', 'Kanban tidak sesuai!');

                                    // notification sound
                                    notMatchSound();

                                    setInterval(() => {
                                        $('#code').focus();
                                    }, 1000);
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
                }

                if (barcodecomplete.charAt(0) == 'C') {
                    let loadingList = getLoadingListNumber();
                    $.ajax({
                        type: 'GET',
                        url: 'http://api-dea-dev/api/v1/loading-lists/' + barcodecomplete,
                        _token: "{{ csrf_token() }}",
                        headers: {
                            "Authorization": "Bearer " + token
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status == 'success') {
                                // objectStor name is based on pds_number
                                let pds = data.data.pds_number;
                                let ll = data.data.number;

                                // create database indexed db
                                request = window.indexedDB.open(pds);

                                // check if loading list already exists
                                if (loadingList.includes(data.data.number)) {
                                    notif('error', 'Loading list sudah discan!');
                                    alreadyScanLlSound();
                                    setInterval(() => {
                                        $('#code').focus();
                                    }, 1000);
                                    return;
                                }

                                // check if loading list have same manifest code (pds number)
                                if (localStorage.getItem('pdsNumber')) {
                                    if (data.data.pds_number != localStorage.getItem(
                                            'pdsNumber')) {
                                        notif('error', 'Loading list tidak sesuai!');

                                        // unknown ll sound
                                        notMatchLlSound();

                                        return false;
                                    }
                                }
                                let pdsLocal = localStorage.setItem('pds_local', pds);
                                localStorage.setItem('ll_' + data.data.number, data.data
                                    .number);
                                localStorage.setItem('pdsNumber', data.data.pds_number);

                                // remove example display
                                $('#loadingListContainerSample').remove();

                                // loading list display
                                $('#list').append(
                                    `<li class="col-12 mt-2"
                                    style="padding-left: 1rem; padding-right: 0px; list-style-type: none;"
                                    id="loadingListContainer">
                                    <div style="height: 2rem; width: 100%; background-color: #03b1fc; border-radius: 4px;"
                                        id="loadingList">
                                        <h6 class="text-center " style="padding-top: .5rem; color: white;"
                                            id="loadingList-display">${data.data.number}</h6>
                                    </div>
                                </li>`
                                );

                                // create database schema
                                request.onupgradeneeded = function(event) {
                                    const database = event.target.result;
                                    const objectStore = database.createObjectStore(
                                        'loadingList');
                                    var index = objectStore.createIndex(
                                        'loadingListDetail',
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
                                        const key = item.part_number_cust;

                                        const getRequest = objectStore.get(key);

                                        getRequest.onsuccess = function(event) {
                                            const existingData = event
                                                .target
                                                .result;

                                            if (!existingData) {
                                                objectStore.put({
                                                    key: key,
                                                    loading_list_number: ll,
                                                    internal: item
                                                        .part_number_int,
                                                    customer: item
                                                        .part_number_cust,
                                                    qty_per_kbn: item
                                                        .total_qty /
                                                        item
                                                        .total_kanban_qty,
                                                    actual_qty: item
                                                        .actual_kanban_qty,
                                                    total_qty: item
                                                        .total_kanban_qty,
                                                    seri: []
                                                }, key);
                                            }
                                        };

                                        getRequest.onerror = function(event) {
                                            notif(event.data.error);
                                        };
                                    });

                                    // check customer if exist 
                                    customerCheck(data.data.customer_code)
                                        .then(function() {
                                            // cycle display
                                            $('#cycle-display').text(data.data
                                                .cycle);
                                            localStorage.setItem('cycle', data
                                                .data.cycle);

                                            // calculate total quantity of the orders
                                            pullingQuantity();

                                            // scan kanban
                                            $('#code').focus();

                                        })
                                        .catch(function(err) {
                                            notif('error', data.message);
                                        })

                                    // loadingList qty
                                    checkLoadingList();

                                    // customer check char
                                    customerCharStore(data.data.customer_code);

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
                        }
                    });
                } else if (barcodecomplete.length == localStorage.getItem('char_total')) {
                    if (localStorage.getItem('char_length') != 0) {
                        // substring
                        barcodecomplete = barcodecomplete.substr(localStorage.getItem('char_first'),
                            localStorage.getItem('char_length'))
                        barcodecomplete = barcodecomplete.trim();
                        barcodecomplete = barcodecomplete.replace(/-/g, '');

                        // for suzuki case
                        if (localStorage.getItem('char_length') == 17) {
                            if (barcodecomplete.substr(10, 3) == '000') {
                                // delete 3 lastest characters
                                barcodecomplete = barcodecomplete.slice(0, -3);
                            }
                        }
                    }

                    console.log(barcodecomplete);
                    // initiate database
                    request = window.indexedDB.open(pds);

                    // transaction
                    request.onsuccess = function(event) {
                        const database = event.target.result;
                        const transaction = database.transaction(["loadingList"], 'readonly');
                        const objectStore = transaction.objectStore("loadingList");
                        let isAvailable = false;

                        objectStore.openCursor().onsuccess = function(event) {
                            const cursor = event.target.result;
                            if (cursor) {
                                const record = cursor.value;
                                // check if kanban customer exist in loading list record
                                if (barcodecomplete == record.customer) {
                                    // check quantity in spesific part number
                                    if (record.seri.length >= record.total_qty) {
                                        notif('error', 'Part number sudah complete!');
                                        fullfilledSound();
                                        $('#indicator').removeClass('bg-success');
                                        $('#indicator').removeClass('bg-warning');
                                        $('#indicator').addClass('bg-danger');
                                        setInterval(() => {
                                            $('#code').focus();
                                        }, 1000);
                                        return;
                                    }
                                    // set flag
                                    isAvailable = true;
                                    // display customer
                                    $('#cust-display').text(record.customer);
                                    $('#int-display').text('-');

                                    // set indicator
                                    $('#indicator').removeClass('bg-success');
                                    $('#indicator').removeClass('bg-danger');
                                    $('#indicator').addClass('bg-warning');

                                    // display current qty
                                    $('#qty-display').text(`
                                        ${record.seri.length}/${record.total_qty}
                                    `);
                                    // set local storage for customer kanban
                                    localStorage.setItem('customerPart', record.customer);
                                }
                                cursor.continue();
                            } else {
                                console.log('iteration complete');
                                // check if the kanban customer is available
                                if (!isAvailable) {
                                    notif('error', 'Kanban tidak dikenali / sesuai!');

                                    // notification sound
                                    unknownSound();


                                    setInterval(() => {
                                        $('#code').focus();
                                    }, 1000);
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

                } else if (barcodecomplete.length == 218 || barcodecomplete.length == 230 ||
                    barcodecomplete.length == 220 || barcodecomplete.length == 241) {
                    let internal;
                    let seri;
                    // check if already scan customer kanban
                    if (!localStorage.getItem('customerPart')) {
                        notif('error', 'Scan kanban customer dulu!');
                        scanCustomerFirstSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return;
                    }

                    if (barcodecomplete.length == 230) {
                        // normal kanban proccess
                        internal = barcodecomplete.substr(41, 19);
                        seri = barcodecomplete.substr(123, 4);
                    } else if (barcodecomplete.length == 220) {
                        // kanban buffer
                        internal = barcodecomplete.substr(35, 12);
                        seri = barcodecomplete.substr(130, 4);
                    } else if (barcodecomplete.length == 241) {
                        // kanban passtrough
                        internal = barcodecomplete.substr(35, 12);
                        seri = barcodecomplete.substr(127, 4);
                    }

                    console.log(seri);

                    // initialize databae connection
                    request = window.indexedDB.open(pds);

                    request.onsuccess = function(event) {
                        const database = event.target.result;
                        const transaction = database.transaction(["loadingList"], 'readwrite');
                        const objectStore = transaction.objectStore("loadingList");
                        let isAvailable = false;

                        objectStore.openCursor().onsuccess = function(event) {
                            const cursor = event.target.result;
                            if (cursor) {
                                // get spesific primary key
                                let primaryKey = cursor.primaryKey
                                if (primaryKey == localStorage.getItem('customerPart')) {
                                    // set flag
                                    isAvailable = true;

                                    // check pair only in spesific key
                                    objectStore.get(primaryKey).onsuccess = function(event) {
                                        const cursor = event.target.result;
                                        if (cursor) {
                                            checkInternalAndCustomer(objectStore, cursor,
                                                internal, primaryKey, seri);
                                            return;
                                        } else {
                                            console.log('Iteration complete');
                                        }
                                    }

                                    // error handling
                                    objectStore.get(primaryKey).onerror = function(event) {
                                        // error indicator
                                        $('#indicator').removeClass('bg-success');
                                        $('#indicator').removeClass('bg-warning');
                                        $('#indicator').addClass('bg-danger');
                                        notif('error', 'Kanban tidak sesuai');

                                        // notification sound
                                        notMatchSound();

                                    }
                                }
                                cursor.continue();
                            } else {
                                console.log('iteration complete');

                                if (!isAvailable) {
                                    // error indicator
                                    $('#indicator').removeClass('bg-success');
                                    $('#indicator').removeClass('bg-warning');
                                    $('#indicator').addClass('bg-danger');
                                    notif('error', 'Kanban tidak ditemukan!');
                                    unknownSound();
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
                } else if (barcodecomplete == "DONE") {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ url('/logout') }}";

                    // Add a CSRF token field to the form
                    var csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Append the form to the body and submit it
                    document.body.appendChild(form);
                    form.submit();

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
    });
</script>
