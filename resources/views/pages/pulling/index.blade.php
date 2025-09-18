@extends('layouts.root.auth')

<style>
    .bg-default {
        background-color: #03b1fc;
    }

    #modalLoadingListScan .loading-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        /* selalu flex */
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .35);
        backdrop-filter: blur(1px);
        z-index: 10;

        /* hidden by default */
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .15s ease;
    }

    #modalLoadingListScan .loading-overlay.is-active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    #modalLoadingListScan .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    /* Base glass tile */
    /* Glass tile utk progress saja */
    .glass-tile {
        position: relative;
        border-radius: 14px;
        padding: .5rem .75rem;
        min-height: 2.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: #fff;
        background: rgba(17, 25, 40, .45);
        border: 1px solid rgba(255, 255, 255, .18);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .18), inset 0 1px 0 rgba(255, 255, 255, .08);
        overflow: hidden;
        transform: translateZ(0);
    }

    @supports ((-webkit-backdrop-filter:none) or (backdrop-filter:none)) {
        .glass-tile {
            backdrop-filter: blur(10px) saturate(140%);
            -webkit-backdrop-filter: blur(10px) saturate(140%);
            background: rgba(17, 25, 40, .30);
        }
    }

    .glass-tile--loading {
        border-color: rgba(0, 173, 255, .35);
        box-shadow: 0 6px 20px rgba(0, 173, 255, .18), inset 0 1px 0 rgba(255, 255, 255, .12);
    }

    .glass-tile--error {
        border-color: rgba(255, 99, 132, .45);
        background: rgba(40, 17, 22, .50);
    }

    .glass-tile--loading::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 0%, rgba(255, 255, 255, .14) 25%, transparent 50%);
        transform: translateX(-100%);
        animation: glass-shimmer 2.2s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes glass-shimmer {
        to {
            transform: translateX(100%);
        }
    }

    .glass-in {
        animation: glass-in .18s ease-out both;
    }

    @keyframes glass-in {
        from {
            transform: scale(.98);
            opacity: 0
        }

        to {
            transform: scale(1);
            opacity: 1
        }
    }

    .glass-tile .spinner-border {
        color: rgba(255, 255, 255, .92);
    }
</style>

@section('main')
    <div class="main-section">
        <section class="section">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 p-0" style="height: 100%;">
                    <div class="shadow hero bg-white text-dark" style="padding: 1.5rem; height: 100%;">
                        <div class="hero-inner">
                            <div class="row">
                                <div class="col-6">
                                    <span style="font-size: 1rem;">Siap Pulling, {{ auth()->user()->name }}</span>
                                </div>
                                <div class="col-2 ml-4">
                                    <div style="height: 2.4rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-warning"
                                            id="refreshTokenBtn">Refresh</button>
                                    </div>
                                </div>
                                <div class="col-2 ml-3">
                                    <div style="height: 2.4rem; width: 100%; border-radius: 20px;">
                                        <button type="button" class="btn btn-xl btn-danger" id="hardReset">Reset</button>
                                    </div>
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
                            <div class="skid-display"></div>
                            <div class="row mt-2">
                                <div class="col-6" style="padding-right: 0px">
                                    <div class="bg-default" style="height: 5rem; width: 100%;  border-radius: 4px;">
                                        <h6 class="p-2" style="color: #ffffff; font-size:12px; font-weight:lighter">
                                            Kanban Customer</h6>
                                        <h6 class="text-center " style="padding-top: 0rem;  color: white;"
                                            id="cust-display">
                                            -
                                        </h6>
                                        {{-- <div class="bg-warning"
                                            style="display:inline-block; margin-left:143px; margin-top:-55px; border-radius:100%; width: 10px; height:10px"
                                            id="tmmin-indicator">
                                        </div> --}}
                                    </div>
                                </div>
                                <div class="col-6" style="padding-left: .5rem; padding-right: 0px">
                                    <div style="height: 5rem; width: 100%; background-color: #03b1fc; border-radius: 4px;"
                                        id="tmmin-indicator">
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
    <div class="modal fade" id="modalLoadingListScan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true" aria-labelledby="modalToggleLabel2">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative">
                <!-- overlay TANPA d-none/d-flex -->
                <div class="loading-overlay" aria-live="polite" aria-busy="true">
                    <div class="text-center">
                        <div class="spinner-border" role="status" aria-hidden="true"></div>
                        <div class="mt-3 fw-semibold" id="loading-text">Memproses...</div>
                    </div>
                </div>

                <div class="modal-header"></div>
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
                    <span style="color: white; font-size: 30pt" id="notif">Error</span>
                </div>
            </div>
        </div>
    </div>

    {{-- confirmation modal --}}
    <div class="modal fade" id="modalConfirmation" aria-hidden="true" aria-labelledby="modalToggleLabel2"
        tabindex="-1" data-backdrop="static">
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

    <audio id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
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
    <audio id="not-exist-sound">
        <source src={{ asset('assets/sounds/notExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="kanban-not-exist-sound">
        <source src={{ asset('assets/sounds/kanbanNotExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="part-not-exist-sound">
        <source src={{ asset('assets/sounds/partNotExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="not-scanned-sound">
        <source src={{ asset('assets/sounds/notScanned.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
@endsection
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    let line = '';
    let partDetail = {};
    let part = 'part';
    let partNumber;
    let loadingListItem = [];
    let loadinglistDetail = [];

    function okSound() {
        var sound = document.getElementById("ok-sound");
        sound.play();
    }

    function kanbanNotExistSound() {
        var sound = document.getElementById("kanban-not-exist-sound");
        sound.play();
    }

    function partNotExistSound() {
        var sound = document.getElementById("part-not-exist-sound");
        sound.play();
    }

    function notScannedSound() {
        var sound = document.getElementById("not-scanned-sound");
        sound.play();
    }

    function notExist() {
        var sound = document.getElementById("not-exist-sound");
        sound.play();
    }

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

    // extract the loading list number from the key
    function extractLoadingListNumber(key) {
        const prefix = "ll_";
        return key.substring(prefix.length);
    }

    function extractManifest(key) {
        const prefix = "skid_";
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

    // get manifest from skid prefix
    function getManifest() {
        let manifest = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("skid_")) {
                manifest.push(extractManifest(key));
            }
        }
        // Return a default value if no manifest is found
        return manifest;
    }

    function initApp() {
        // check solve status
        if (localStorage.getItem('status')) {
            $('#modalConfirmation').on('shown.bs.modal', function() {
                $('#input-confirmation').focus();
            })
            $('#modalConfirmation').modal('show');

            $(document).on('click', function() {
                $('#input-confirmation').focus();
            })
        }

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

        // set skid
        let skid = localStorage.getItem('skid');
        if (!skid) {
            localStorage.setItem('skid', 1);
        }

        // display skid if customer is TMMIN
        if (customer == 'TMMIN KRW 1') {
            $('.skid-display').append(`<div class="row mt-2">
                <div class="col-12" style="padding-right: 0px">
                    <div
                        style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 4px; padding:10.5px; padding-left:12px">
                        <small class="badge badge-dark"
                            style="color:#ffffff; display:inline; border-radius:4px !important;">Skid</small>
                        <h5 style="color: #ffffff; display:inline; padding-left:5rem">
                            <span id="skid-display">${skid}</span>
                        </h5>
                        <div class="btn btn-danger"
                            style="display:inline-block; margin-left:220px; margin-top:-27px;"
                            id="close-skid">
                            Close Skid ${skid}
                        </div>
                    </div>
                </div>
            </div>`)
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

    function successIndicator() {
        $('#indicator')
            .removeClass('bg-warning');
        $('#indicator')
            .removeClass('bg-danger');
        $('#indicator')
            .addClass('bg-success');
    }

    function tmminSuccessIndicator() {
        $('#tmmin-indicator')
            .removeClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-danger');
        $('#tmmin-indicator')
            .addClass('bg-success');
    }

    function resetIndicator() {
        $('#tmmin-indicator')
            .addClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-danger');
        $('#tmmin-indicator')
            .removeClass('bg-success');
    }

    function errorIndicator() {
        $('#indicator')
            .removeClass('bg-warning');
        $('#indicator')
            .removeClass('bg-success');
        $('#indicator')
            .addClass('bg-danger');
    }

    function tmminErrorIndicator() {
        $('#tmmin-indicator')
            .removeClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-success');
        $('#tmmin-indicator')
            .addClass('bg-danger');
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

    function confirmationModal() {
        let status = localStorage.getItem('status');
        $('#input-confirmation').val('');
        setTimeout(() => {
            if (status) {
                $('#modalConfirmation').on('shown.bs.modal',
                    function() {
                        $('#input-confirmation').focus();
                    })
                $('#modalConfirmation').modal('show');
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

    function customerCheck(customer, pds = null) {
        return new Promise(function(resolve, reject) {
            let url = "{{ url('pulling/customer-check') }}/" + customer;

            if (pds) {
                url += '/' + encodeURIComponent(pds); // tanpa tanda tanya
            }

            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.status === 'success') {
                        $('#customer-display').text(data.customer);
                        localStorage.setItem('customer', data.customer);
                        resolve();
                    } else {
                        reject(data.message || 'Unknown error');
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

    // Function to get the Skid data (either Skid 1 or Skid 2)
    function getSkidData(skidId) {
        return new Promise(function(resolve, reject) {
            let pds = localStorage.getItem('pds_local');
            let request = window.indexedDB.open(pds);

            request.onsuccess = function(event) {
                const database = event.target.result;
                const transaction = database.transaction(["loadingList"], 'readonly');
                const objectStore = transaction.objectStore("loadingList");

                let allData = [];
                objectStore.openCursor().onsuccess = function(event) {
                    let cursor = event.target.result;
                    if (cursor) {
                        allData.push(cursor.value);
                        cursor.continue();
                    } else {
                        // Group data by skidNo
                        let skidGroups = {};
                        allData.forEach(item => {
                            if (!skidGroups[item.skidNo]) {
                                skidGroups[item.skidNo] = [];
                            }
                            skidGroups[item.skidNo].push(item);
                        });

                        // Extract skids and sort them
                        let skids = Object.keys(skidGroups).sort();

                        if (skidId === 1) {
                            let skid1Data = skidGroups[skids[0]] || [];
                            localStorage.setItem("skid_1_data", JSON.stringify(skid1Data));
                            resolve(skid1Data);
                        } else {
                            let skid1Data = JSON.parse(localStorage.getItem("skid_1_data") || "[]");
                            let skid1SkidNo = skid1Data.length > 0 ? skid1Data[0].skidNo : null;

                            let skid2Data = allData.filter(d => d.skidNo !== skid1SkidNo);
                            resolve(skid2Data);
                        }
                    }
                };

                transaction.onerror = function(event) {
                    reject("Error reading IndexedDB: " + event.target.error);
                };
            };

            request.onerror = function(event) {
                reject("Failed to open IndexedDB: " + event.target.error);
            };
        });
    }

    function customerCharStore(customer, pds = null) {
        // Buat URL dengan path segment, tanpa tanda tanya
        let url = "{{ url('pulling/customer-check') }}/" + customer;
        if (pds) {
            url += '/' + encodeURIComponent(pds);
        }

        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.status === 'success') {
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
                notif('error', xhr.statusText); // karena di sini ga pakai promise
                loadingListModal();
            }
        });
    }

    function errorStore(message = null, expected = null, scanned = null) {
        $.ajax({
            type: 'GET',
            url: "{{ route('error.store') }}",
            _token: "{{ csrf_token() }}",
            dataType: 'json',
            data: {
                message: message,
                expected: expected,
                scanned: scanned
            },
            success: function(data) {
                console.log("Error recorded");
            },
            error: function(xhr) {
                console.log(xhr);
            }
        });
    }

    // Function to update the button based on the status of Skid 1 and Skid 2
    function updateButtonStatus() {
        var button = $("#close-skid");

        if (!skid1Sent) {
            // button.text("Close Skid 1");
            button.off('click').on('click', function() {
                sendSkidData(1);
            });
        } else if (!skid2Sent) {
            button.text("Close Skid 2");
            button.off('click').on('click', function() {
                sendSkidData(2);
            });
        } else {
            button.text("Semua Data Terkirim").prop("disabled",
                true);
        }
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

    function refreshToken() {
        $.ajax({
            url: "/refresh-token",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    notif('success', 'Successfully refreshed token');
                    setInterval(() => {
                        $('#code').focus();
                    }, 1000);
                } else {
                    notif("error", response.message);
                }
            },
            error: function(xhr) {
                notif("error", xhr.responseJSON.message);
            }
        });
    }

    $(document).ready(function() {
        initApp();

        $('#code').focus();

        $("#refreshTokenBtn").click(function() {
            refreshToken();
        });

        $('#loadingList').on('click', function() {
            loadingListModal2();
        });

        if (localStorage.getItem('status')) {
            $(document).on('click', function() {
                $('#input-confirmation').focus();
            });
        }

        var token = "{{ session()->get('token') }}";

        class ModalLoadingListScanner {
            constructor() {
                this.token = "{{ session()->get('token') }}" || '';
                this.modalSel = '#modalLoadingListScan';
                this.$modal = $(this.modalSel);
            }

            init() {
                this.hideLoading(); // pastikan hidden on start
                $('#input-loadingList').on('keypress', (e) => this.handleKeyPress(e));
            }

            handleKeyPress(e) {
                const keyCode = e.keyCode || e.which;
                if (keyCode === 13) { // Enter key
                    const inputValue = $(e.target).val();
                    this.processLoadingList(inputValue);
                }
            }

            async processLoadingList(inputValue) {
                try {
                    const loadingList = this.getLoadingListNumber();
                    const loadingListNumber = inputValue.substr(0, 11) + ' A';

                    // early return tanpa spinner
                    if (loadingList.includes(loadingListNumber)) {
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }

                    // ⬇️ mulai tampilkan loading
                    this.showLoading('Mengambil data loading list...');

                    const response = await this.fetchLoadingList(loadingListNumber);
                    if (response.status !== 'success') {
                        this.showError(response.message);
                        this.showModal();
                        return;
                    }

                    // proses lanjut (spinner tetap on, nanti di-update pesannya di method berikut)
                    await this.processLoadingListResponse(response.data);

                    // kalau sukses dan mau tutup modal
                    this.hideModal();

                } catch (error) {
                    console.error('Loading list error:', error);
                    this.handleAjaxError(error);
                } finally {
                    // pastikan spinner dimatikan & input difokuskan
                    this.hideLoading();
                    this?.focusCodeInput?.();
                }
            }


            async fetchLoadingList(loadingListNumber) {
                return await $.ajax({
                    type: 'GET',
                    url: `https://dea-dev.aiia.co.id/api/v1/loading-lists/${loadingListNumber}`,
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    },
                    dataType: 'json'
                });
            }

            async processLoadingListResponse(data) {
                // validasi dulu
                const validationResult = this.validateLoadingList(data);
                if (!validationResult.isValid) {
                    this.showError(validationResult.message, validationResult.soundFn);
                    if (validationResult.showModal) this.showModal();
                    return;
                }

                const {
                    pds_number,
                    number: ll,
                    cycle,
                    customer_code,
                    delivery_date,
                    shipping_date
                } = data;

                try {
                    this.setLoadingMessage('Menyimpan header...');
                    await this.storeLoadingList(ll, pds_number, cycle, customer_code, delivery_date,
                        shipping_date);

                    this.setLoadingMessage('Menyimpan data lokal...');
                    this.storeLocalData(data);

                    this.setLoadingMessage('Memperbarui tampilan...');
                    this.updateLoadingListUI(data);

                    this.setLoadingMessage('Menyiapkan IndexedDB...');
                    await this.initializeDatabase(data);

                    this.setLoadingMessage('Menyimpan detail item...');
                    await this.storeLoadingListDetails(ll, data.items);

                    this.setLoadingMessage('Finalisasi...');
                    await this.performAdditionalProcessing(data);

                } catch (error) {
                    console.error('Error processing loading list:', error);
                    this.showError('Gagal memproses loading list');
                } finally {
                    // fokus input akan dipanggil di finally processLoadingList (luar)
                }
            }

            validateLoadingList(data) {
                // Check if already pulled (missing total_kanban_qty property)
                if (!data.items[0]?.hasOwnProperty('total_kanban_qty')) {
                    return {
                        isValid: false,
                        message: 'Loading list sudah pernah dipulling',
                        soundFn: () => {
                            if (typeof alreadyPulledSound === 'function') alreadyPulledSound();
                        },
                        showModal: true
                    };
                }

                // Check totals (commented out in original but keeping logic)
                const totals = this.calculateTotals(data.items);
                // if (totals.actual >= totals.kanban) {
                //     return {
                //         isValid: false,
                //         message: 'Loading list sudah pernah dipulling',
                //         soundFn: () => { if (typeof alreadyPulledSound === 'function') alreadyPulledSound(); },
                //         showModal: true
                //     };
                // }

                // Check PDS consistency
                const existingPDS = localStorage.getItem('pdsNumber');
                if (existingPDS && data.pds_number !== existingPDS) {
                    return {
                        isValid: false,
                        message: 'Loading list tidak sesuai!',
                        soundFn: () => {
                            if (typeof notMatchLlSound === 'function') notMatchLlSound();
                        },
                        showModal: false
                    };
                }

                return {
                    isValid: true
                };
            }

            calculateTotals(items) {
                return items.reduce((acc, item) => {
                    acc.actual += item.actual_kanban_qty || 0;
                    acc.kanban += item.total_kanban_qty || 0;
                    return acc;
                }, {
                    actual: 0,
                    kanban: 0
                });
            }

            async storeLoadingList(ll, pds, cycle, customerCode, deliveryDate, shippingDate) {
                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `/loading-list/store/${ll}/${pds}/${cycle}/${customerCode}/${deliveryDate || ''}/${shippingDate || ''}`,
                        dataType: 'json'
                    });

                    // console.log('Modal loading list stored:', response.message);
                    return response;

                } catch (xhr) {
                    console.error('Error storing loading list:', xhr);

                    if (xhr.status === 0) {
                        this.showError('Connection Error');
                        throw new Error('Network error');
                    } else {
                        const errorMsg = xhr.responseJSON?.errors || 'Gagal menyimpan loading list';
                        this.showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                }
            }

            storeLocalData(data) {
                localStorage.setItem('pds_local', data.pds_number);
                localStorage.setItem(`ll_${data.number}`, data.number);
                localStorage.setItem('pdsNumber', data.pds_number);
            }

            updateLoadingListUI(data) {
                $('#loadingListContainerSample').remove();
                $('#list').append(`
            <li class="col-12 mt-2" style="padding-left: 1rem; padding-right: 0px; list-style-type: none;" id="loadingListContainer">
                <div style="height: 2rem; width: 100%; background-color: #03b1fc; border-radius: 4px;" id="loadingList">
                    <h6 class="text-center" style="padding-top: .5rem; color: white;" id="loadingList-display">${data.number}</h6>
                </div>
            </li>
        `);
            }

            async initializeDatabase(data) {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(data.pds_number);

                    request.onupgradeneeded = (event) => {
                        const database = event.target.result;
                        if (!database.objectStoreNames.contains('loadingList')) {
                            const objectStore = database.createObjectStore('loadingList');
                            objectStore.createIndex('loadingListDetail', 'seri');
                        }
                    };

                    request.onsuccess = async (event) => {
                        try {
                            const database = event.target.result;
                            await this.populateDatabase(database, data);
                            database.close();
                            resolve();
                        } catch (error) {
                            reject(error);
                        }
                    };

                    request.onerror = () => {
                        console.log("Database error:", request.error);
                        reject(request.error);
                    };
                });
            }

            async populateDatabase(database, data) {
                const transaction = database.transaction(['loadingList'], 'readwrite');
                const objectStore = transaction.objectStore('loadingList');

                const promises = data.items.map(item => {
                    return new Promise((resolve, reject) => {
                        const key = item.part_number_cust;
                        const getRequest = objectStore.get(key);

                        getRequest.onsuccess = (event) => {
                            try {
                                if (!event.target.result) {
                                    objectStore.put({
                                        key,
                                        loading_list_number: data.number,
                                        internal: item.part_number_int,
                                        customer: item.part_number_cust,
                                        qty_per_kbn: item.total_qty / item
                                            .total_kanban_qty,
                                        actual_qty: item.actual_kanban_qty,
                                        total_qty: item.total_kanban_qty,
                                        seri: []
                                    }, key);
                                }
                                resolve();
                            } catch (error) {
                                reject(error);
                            }
                        };

                        getRequest.onerror = () => reject(getRequest.error);
                    });
                });

                await Promise.all(promises);
            }

            async storeLoadingListDetails(ll, items) {
                const promises = items.map((item, index) => {
                    const qtyPerKbn = item.total_qty / item.total_kanban_qty;

                    return new Promise((resolve, reject) => {
                        setTimeout(() => {
                            $.ajax({
                                type: 'GET',
                                url: `/loading-list/storeDetail/${ll}/${item.part_number_cust}/${item.part_number_int}/${item.total_kanban_qty}/${qtyPerKbn}/${item.total_qty}/${item.actual_kanban_qty}`,
                                dataType: 'json',
                                success: (response) => {
                                    console.log('Detail stored:',
                                        response.status, response
                                        .data);
                                    resolve(response);
                                },
                                error: (xhr) => {
                                    console.error('Detail store error:',
                                        xhr);

                                    if (xhr.status === 0) {
                                        reject(new Error(
                                            'Network error storing detail'
                                        ));
                                    } else {
                                        // Log error but don't stop the process
                                        console.warn(
                                            `Failed to store detail for ${item.part_number_cust}, continuing...`
                                        );
                                        this.showError(
                                            'Scan ulang loading list'
                                        );
                                        resolve({
                                            status: 'error',
                                            item: item
                                                .part_number_cust
                                        });
                                    }
                                }
                            });
                        }, 200); // Original timeout
                    });
                });

                try {
                    const results = await Promise.all(promises);
                    const failures = results.filter(result => result?.status === 'error');

                    if (failures.length > 0) {
                        console.warn(`${failures.length} detail items failed to store`);
                    }

                    return results;
                } catch (error) {
                    console.error('Critical error storing details:', error);
                    throw error;
                }
            }

            async performAdditionalProcessing(data) {
                try {
                    // Customer check
                    await this.customerCheck(data.customer_code, data.pds_number);

                    // Update cycle display
                    $('#cycle-display').text(data.cycle);
                    localStorage.setItem('cycle', data.cycle);

                    // Calculate quantities
                    this.pullingQuantity();

                    // Handle TMMIN skid display
                    this.handleTmminSkidDisplay(data.customer_code);

                    // Additional checks
                    this.checkLoadingList();
                    this.customerCharStore(data.customer_code, data.pds_number);

                } catch (error) {
                    console.error('Additional processing error:', error);
                    this.showError(error.message || 'Error in additional processing');
                }
            }

            handleTmminSkidDisplay(customerCode) {
                if (customerCode === '7A00001') { // TMMIN customer code
                    let skid = localStorage.getItem('skid') || '1';
                    if (!localStorage.getItem('skid')) {
                        localStorage.setItem('skid', '1');
                    }

                    $('.skid-display').append(`
                <div class="row mt-2">
                    <div class="col-12" style="padding-right: 0px">
                        <div style="height: 3rem; width: 100%; background-color: #03b1fc; border-radius: 4px; padding:10.5px; padding-left:12px">
                            <small class="badge badge-dark" style="color:#ffffff; display:inline; border-radius:4px !important;">Skid</small>
                            <h5 style="color: #ffffff; display:inline; padding-left:5rem">
                                <span id="skid-display">${skid}</span>
                            </h5>
                            <div class="btn btn-danger" style="display:inline-block; margin-left:220px; margin-top:-27px;" id="close-skid">
                                Close Skid ${skid}
                            </div>
                        </div>
                    </div>
                </div>
            `);
                }
            }

            showLoading(message = 'Memproses...') {
                this.setLoadingMessage(message);
                this.$modal.find('.loading-overlay').addClass('is-active');
                this.$modal.find('input,button,select,textarea').prop('disabled', true);
            }

            hideLoading() {
                this.$modal.find('.loading-overlay').removeClass('is-active');
                this.$modal.find('input,button,select,textarea').prop('disabled', false);
            }

            setLoadingMessage(message) {
                this.$modal.find('#loading-text').text(message);
            }

            handleAjaxError(xhr) {
                if (xhr.status === 0) {
                    this.showError('Connection Error');
                } else if (xhr.status === 401) {
                    this.showError(`${xhr.statusText} Please re-login`);
                } else {
                    this.showError(xhr.statusText || 'Request failed');
                }
                this.showModal();
            }

            showError(message, soundFunction = null) {
                if (typeof notif === 'function') {
                    notif('error', message);
                }
                if (soundFunction) soundFunction();
            }

            showModal() {
                if (typeof loadingListModal === 'function') {
                    loadingListModal();
                }
            }

            hideModal() {
                $('#modalLoadingListScan').modal('hide');
            }

            // di dalam class:
            focusCodeInput = () => {
                const $el = $('#code:visible:not([disabled])');
                if (!$el.length) return;

                // kalau ada modal bootstrap, fokuskan setelah modal bener² tertutup
                if ($('.modal.show').length) {
                    $('.modal.show').one('hidden.bs.modal', () => this.focusCodeInput());
                    return;
                }

                // tunda 1 frame supaya DOM/UI selesai update
                requestAnimationFrame(() => setTimeout(() => {
                    $el.trigger('focus');
                    // opsional: taruh kursor di akhir input
                    const el = $el.get(0);
                    if (el && el.setSelectionRange) {
                        const len = el.value?.length ?? 0;
                        el.setSelectionRange(len, len);
                    }
                }, 0));
            };


            getLoadingListNumber() {
                return typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
            }

            async customerCheck(customerCode, pdsNumber) {
                return new Promise((resolve, reject) => {
                    if (typeof customerCheck === 'function') {
                        customerCheck(customerCode, pdsNumber)
                            .then(resolve)
                            .catch(reject);
                    } else {
                        resolve();
                    }
                });
            }

            pullingQuantity() {
                if (typeof pullingQuantity === 'function') {
                    pullingQuantity();
                }
            }

            checkLoadingList() {
                if (typeof checkLoadingList === 'function') {
                    checkLoadingList();
                }
            }

            customerCharStore(customerCode, pdsNumber) {
                if (typeof customerCharStore === 'function') {
                    customerCharStore(customerCode, pdsNumber);
                }
            }
        }

        // Initialize the modal loading list scanner
        $(document).ready(() => {
            const modalScanner = new ModalLoadingListScanner();
            modalScanner.init();
        });

        $('#input-confirmation').keypress(function(e) {
            e.preventDefault();
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                barcodecomplete = barcode;
                barcode = "";
                if (barcodecomplete.length === 6) {
                    if (barcodecomplete == '000453' || barcodecomplete == '002484') {
                        localStorage.removeItem('status');
                        $('#modalConfirmation').modal('hide');
                        notif('success', 'Selamat melanjutkan pulling!');

                        // remove original customer part in local storage
                        localStorage.removeItem('originalCustomerPart');

                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                    } else {
                        $('#modalConfirmation').modal('hide');
                        notif('error', `NPK ${barcodecomplete} tidak memiliki hak akses`);
                        confirmationModal();
                    }
                } else {
                    $('#modalConfirmation').modal('hide');
                    notif('error', 'Scan barcode NPK');
                    confirmationModal();
                }
            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }
        });

        // Function to clear local storage
        function clearLocalStorage() {
            localStorage.clear();
        }

        // Function to clear IndexedDB using localforage
        function deleteAllIndexedDB() {
            // Retrieve a list of all databases
            indexedDB.databases().then(function(databaseList) {
                // Loop through each database and delete it
                databaseList.forEach(function(database) {
                    var deleteRequest = indexedDB.deleteDatabase(database.name);

                    deleteRequest.onsuccess = function() {
                        console.log('IndexedDB database deleted successfully:', database
                            .name);
                    };

                    deleteRequest.onerror = function(event) {
                        console.error('Error deleting IndexedDB database:', event.target
                            .error, database.name);
                    };
                });
            }).catch(function(error) {
                console.error('Error retrieving IndexedDB database names:', error);
            });
        }

        $('#delay').on('click', function() {
            localStorage.clear();
            window.location.reload();
        });

        $('#close-skid').on('click', function() {
            if (!confirm("Are you sure you want to change the skid?")) {
                return; // Stop execution if user cancels
            }

            let skid = parseInt(localStorage.getItem('skid')) || 1; // Default to 1 if not set
            let skidDisplay = $('#skid-display');

            if (skid === 1) {
                skid++; // Increment first
                localStorage.setItem('skid', skid); // Store updated value
            } else if (skid === 2) {
                skid--; // Decrement first
                localStorage.setItem('skid', skid); // Store updated value
            }

            skidDisplay.text(skid);
            $(this).text(`Close Skid ${skid}`);
            setInterval(() => {
                $('#code').focus();
            }, 1000);
        });

        $('#hardReset').on('click', function() {
            // Display a confirmation dialog
            var confirmReset = confirm('Yakin akan reset? semua data akan hilang');

            // If the user confirms, proceed with clearing storage and reload the page
            if (confirmReset) {
                clearLocalStorage();
                deleteAllIndexedDB();
                location.reload();
            }
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
                                notif('error', xhr.statusText);
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
                                            url: "{{ route('pulling.store') }}",
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
                                                localStorage.clear();
                                                window.location.reload();
                                            },
                                            error: function(xhr) {
                                                notif('error', xhr
                                                    .statusText);
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

        function checkInternalAndCustomer(database, cursor, internal, primaryKey, seri) {
            let loadingList = cursor['loading_list_number'];
            let customer = cursor['customer'];
            let qty_per_kbn = cursor['qty_per_kbn'];
            let arraySeri = cursor['seri'];
            let totalQty = cursor['total_qty'];
            let isSameObject = false;
            let skid = localStorage.getItem('skid');
            let originalBarcode = localStorage.getItem('originalCustomerPart');
            let barcodecomplete = localStorage.getItem('customerPart');
            let manifest, itemNo, seqNo;

            if (localStorage.getItem('char_total') == 39) {
                manifest = originalBarcode.substr(3, 10);
                itemNo = originalBarcode.substr(31, 4);
                seqNo = originalBarcode.substr(35, 4);
            }

            // Helper: ambil pesan error dari xhr
            const xhrMessage = (xhr) => {
                if (xhr.status === 0) return 'Connection Error';
                return (xhr.responseJSON?.message) ||
                    (xhr.responseJSON?.errors ? JSON.stringify(xhr.responseJSON.errors) : null) ||
                    xhr.responseText ||
                    `HTTP ${xhr.status}`;
            };

            // Helper: uniform error handling + pencatatan
            // opsi: { expected, scanned, playSound }
            const handleError = (message, opts = {}) => {
                const {
                    expected = null, scanned = null, playSound = true
                } = opts;

                $('#indicator').removeClass('bg-success bg-warning').addClass('bg-danger');
                notif('error', message);

                // catat detail error via API
                errorStore(message, expected, scanned);

                if (playSound) notMatchSound();

                // fokus input kembali (sekali, bukan interval)
                setTimeout(() => {
                    $('#code').focus();
                }, 1000);
            };

            // === Validasi: internal & customer harus dalam objek yang sama
            for (const key in cursor) {
                if (cursor[key] === localStorage.getItem('customerPart')) {
                    if (Object.values(cursor).includes(internal.trimEnd())) {
                        isSameObject = true;
                        break;
                    }
                }
            }

            if (!isSameObject) {
                handleError('Kanban tidak sesuai!', {
                    expected: `Internal & Customer satu objek (internal: ${internal.trimEnd()}, customer: ${localStorage.getItem('customerPart')})`,
                    scanned: `Cursor keys match? ${Object.keys(cursor).length} keys`
                });
                localStorage.setItem('status', 'true');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                return;
            }

            // === Validasi: seri duplikat
            if (arraySeri.includes(seri)) {
                handleError('Seri kanban sudah discan!', {
                    expected: 'Seri unik (belum pernah discan)',
                    scanned: `Seri=${seri}`,
                    playSound: false
                });
                alreadyScanSound();
                return;
            }

            // === Validasi: quantity sudah penuh
            if (arraySeri.length >= totalQty) {
                handleError('Part number sudah complete!', {
                    expected: `Qty <= ${totalQty}`,
                    scanned: `Attempt push, current=${arraySeri.length}`,
                    playSound: false
                });
                fullfilledSound();
                return;
            }

            // === Update IndexedDB setelah validasi backend OK
            const updateIndexedDB = () => {
                // tambah seri sementara
                arraySeri.push(seri);

                if (!database.objectStoreNames.contains('loadingList')) {
                    const availableStores = Array.from(database.objectStoreNames);

                    if (availableStores.length > 0) {
                        const storeName = availableStores[0];
                        const transaction = database.transaction([storeName], 'readwrite');
                        const objectStore = transaction.objectStore(storeName);
                        performUpdate(transaction, objectStore, storeName);
                    } else {
                        handleError('No object stores found in database!', {
                            expected: 'Tersedia store "loadingList"',
                            scanned: 'Tidak ada store sama sekali'
                        });
                        arraySeri.pop();
                        return;
                    }
                } else {
                    const transaction = database.transaction(['loadingList'], 'readwrite');
                    const objectStore = transaction.objectStore('loadingList');
                    performUpdate(transaction, objectStore, 'loadingList');
                }

                function performUpdate(transaction, objectStore, storeName) {
                    const putRequest = objectStore.put(cursor, primaryKey);

                    putRequest.onsuccess = function() {
                        $('#qty-display').text(`${arraySeri.length}/${totalQty}`);
                        $('#int-display').text(internal);
                        $('#cust-display').text('-');
                        $('#indicator').removeClass('bg-danger bg-warning').addClass('bg-success');
                        resetIndicator();
                        pullingQuantity();
                        okSound();
                        localStorage.removeItem('customerPart');
                    };

                    putRequest.onerror = function(event) {
                        const err = event?.target?.error?.message || 'Put request failed';
                        handleError('Gagal menyimpan data ke database lokal!', {
                            expected: `IDB put ke store "${storeName}" key=${primaryKey}`,
                            scanned: err
                        });
                        arraySeri.pop();
                    };

                    transaction.onerror = function(event) {
                        const err = event?.target?.error?.message || 'Transaction failed';
                        handleError('Transaction failed!', {
                            expected: `IDB transaksi write ke store "${storeName}"`,
                            scanned: err
                        });
                        arraySeri.pop();
                    };
                }
            };

            // === Request ke backend terlebih dahulu
            try {
                $.ajax({
                    type: 'GET',
                    url: "{{ route('pulling.mutation') }}",
                    _token: "{{ csrf_token() }}",
                    data: {
                        loadingList: loadingList,
                        internalPart: internal.trimEnd(),
                        customerPart: localStorage.getItem('customerPart'),
                        serialNumber: seri,
                        qty_per_kbn: qty_per_kbn
                    },
                    contentType: 'application/json',
                    success: function(data) {
                        if (data.status == 'success') {
                            // eDCL opsional
                            if (localStorage.getItem('char_total') == 10000) {
                                $.ajax({
                                    type: 'GET',
                                    url: "{{ url('/edcl/store') }}/" +
                                        skid + '/' + manifest + '/' + itemNo + '/' +
                                        seqNo + '/' + barcodecomplete + '/' +
                                        originalBarcode + '/' + loadingList + '/' +
                                        localStorage.getItem('customer'),
                                    _token: "{{ csrf_token() }}",
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.status == 'success') {
                                            updateIndexedDB();
                                            tmminSuccessIndicator();
                                        } else {
                                            handleError(response.message ||
                                                'eDCL gagal', {
                                                    expected: 'eDCL success',
                                                    scanned: JSON.stringify(
                                                        response),
                                                    playSound: false
                                                });
                                            tmminErrorIndicator();
                                        }
                                    },
                                    error: function(xhr) {
                                        const msg = xhrMessage(xhr);
                                        handleError(msg, {
                                            expected: 'HTTP 200 OK dari eDCL',
                                            scanned: `HTTP ${xhr.status}`
                                        });
                                    }
                                });
                            } else {
                                // tanpa eDCL
                                updateIndexedDB();
                            }
                        } else {
                            handleError(data.message || 'Validasi backend gagal', {
                                expected: 'Response success dari mutation',
                                scanned: JSON.stringify(data),
                                playSound: (data.status == 'notExists')
                            });
                            if (data.status == 'notExists') notExist();
                        }
                    },
                    error: function(xhr) {
                        const msg = xhrMessage(xhr);
                        handleError(msg, {
                            expected: 'HTTP 200 OK dari mutation',
                            scanned: `HTTP ${xhr.status}`
                        });
                    }
                });
            } catch (error) {
                handleError('An error occurred. Please try again.', {
                    expected: 'AJAX berjalan sukses',
                    scanned: error?.message || String(error)
                });
            }
        }

        function checkKanban(seri, internal) {
            $.ajax({
                type: 'GET',
                url: '/kanban/check',
                _token: "{{ csrf_token() }}",
                data: {
                    seri: seri,
                    internal: internal.trimEnd()
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status == 'success') {
                        console.log(data.status);
                    } else if (data.status == 'partNotExist') {
                        notif('error', data.message)
                        partNotExistSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return false;
                    } else if (data.status == 'kanbanNotExist') {
                        notif('error', data.message)
                        kanbanNotExistSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return false;
                    } else if (data.status == 'notScanned') {
                        notif('error', data.message)
                        notScannedSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return false;
                    }
                },
                error: function(xhr) {
                    console.log(xhr.getResponse);
                }
            });
        }

        class BarcodeScanner {
            constructor() {
                this.barcode = "";
                this.token = "{{ session()->get('token') }}" || '';
                this.progressTilePrefix = 'll-progress-';
                this.tileByKey = new Map();
                // Don't cache PDS value in constructor - get it dynamically when needed

                // Barcode patterns and handlers
                this.patterns = [{
                        test: (code) => code.startsWith('C') && code.length < 22,
                        handler: 'handleLoadingList'
                    },
                    {
                        test: (code) => code === "DONE",
                        handler: 'handleLogout'
                    },
                    {
                        test: (code) => this.isKanbanBarcode(code),
                        handler: 'handleKanbanBarcode'
                    },
                    {
                        test: (code) => code.length === parseInt(localStorage.getItem('char_total') ||
                            '0'),
                        handler: 'handleCustomerKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'MMKI',
                        handler: 'handleMMKIKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'TB INA',
                        handler: 'handleTBINAKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'TTI INDONESIA',
                        handler: 'handleTTIKanban'
                    }
                ];
            }

            sanitizeKey(str) {
                return String(str).replace(/[^A-Za-z0-9_-]/g, '');
            }
            getTileIdByKey(key) {
                return `${this.progressTilePrefix}${this.sanitizeKey(key)}`;
            }

            // ===== progress tile helpers =====
            createProgressTile(message = 'Mengambil data loading list...') {
                const id = `${this.progressTilePrefix}${Date.now()}`;
                $('#list').append(`
                    <li class="col-12 mt-2" style="padding-left:1rem;padding-right:0;list-style-type:none;" id="${id}">
                    <div id="${id}-card" class="glass-tile glass-tile--loading glass-in">
                        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                        <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                    </div>
                    </li>
                `);
                return id;
            }

            ensureProgressTile(key, message = 'Mengambil data loading list...') {
                const id = this.getTileIdByKey(key);
                const $li = $(`#${id}`);
                if ($li.length) {
                    // reset ke state loading
                    const $card = $li.find(`#${id}-card`);
                    $card.attr('class', 'glass-tile glass-tile--loading glass-in')
                        .html(`
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                            `);
                    return id;
                }
                $('#list').append(`
                        <li class="col-12 mt-2" style="padding-left:1rem;padding-right:0;list-style-type:none;" id="${id}">
                        <div id="${id}-card" class="glass-tile glass-tile--loading glass-in">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                        </div>
                        </li>
                    `);
                this.tileByKey.set(key, id);
                return id;
            }

            setProgressTile(id, msg) {
                $(`#${id}-text`).text(msg);
            }

            setProgressTileError(id, msg = 'Gagal memproses', {
                autoRemove = true,
                delay = 1500
            } = {}) {
                const $c = $(`#${id}-card`);
                $c.removeClass('glass-tile--loading').addClass('glass-tile--error');
                $c.find('.spinner-border').remove();
                $(`#${id}-text`).text(msg);
                if (autoRemove) setTimeout(() => this.removeTileById(id), delay);
            }

            removeTileById(id) {
                const $li = $(`#${id}`);
                if ($li.length) $li.remove();
                // bersihkan mapping key->id
                for (const [k, v] of this.tileByKey.entries())
                    if (v === id) this.tileByKey.delete(k);
            }

            // final: ganti tile progress jadi markup LAMA-mu (biru)
            replaceTileWithFinalOriginal(key, number) {
                const id = this.getTileIdByKey(key);
                const html = `
                    <li class="col-12 mt-2" style="padding-left: 1rem; padding-right: 0px; list-style-type: none;" id="loadingListContainer">
                    <div style="height: 2rem; width: 100%; background-color: #03b1fc; border-radius: 4px;" id="loadingList">
                        <h6 class="text-center " style="padding-top: .5rem; color: white;" id="loadingList-display">${number}</h6>
                    </div>
                    </li>`;
                $(`#${id}`).replaceWith(html);
                this.tileByKey.delete(key);
            }


            // Helper method to get current PDS dynamically
            getCurrentPDS() {
                return localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber') || '';
            }

            init() {
                $('#code').on('keypress', (e) => this.handleKeyPress(e));
            }

            handleKeyPress(e) {
                e.preventDefault();
                const keyCode = e.keyCode || e.which;

                if (keyCode === 13) { // Enter key
                    this.processBarcode(this.barcode);
                    this.barcode = "";
                } else {
                    this.barcode += String.fromCharCode(e.which);
                }
            }

            processBarcode(code) {
                // console.log(`Processing barcode: ${code} (length: ${code.length})`);

                const pattern = this.patterns.find(p => p.test(code));
                if (pattern) {
                    this[pattern.handler](code);
                } else {
                    this.showError("Kanban tidak dikenali!", () => {
                        if (typeof unknownSound === 'function') unknownSound();
                    });
                }
            }

            isKanbanBarcode(code) {
                const kanbanLengths = [218, 220, 230, 241, 242];
                return kanbanLengths.includes(code.length);
            }

            async handleLoadingList(code) {
                const formattedCode = code.substr(0, 11) + ' A';
                const tileId = this.ensureProgressTile(formattedCode, 'Mengambil data loading list...');

                try {
                    const loadingList = this.getLoadingListNumber();
                    if (loadingList.includes(formattedCode)) {
                        this.setProgressTileError(tileId, 'Sudah discan', {
                            autoRemove: true,
                            delay: 1200
                        });
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }

                    const response = await this.fetchLoadingList(formattedCode);
                    if (response.status !== 'success') {
                        this.setProgressTileError(tileId, response.message || 'Gagal ambil data', {
                            autoRemove: true
                        });
                        this.showError(response.message || 'Gagal mengambil data');
                        return;
                    }

                    await this.processLoadingListData(
                        response.data,
                        (msg) => this.setProgressTile(tileId, msg),
                        /* tileId not needed here now */
                    );

                    // sukses → replace dengan markup lama (biru)
                    this.replaceTileWithFinalOriginal(formattedCode, response.data.number);

                } catch (e) {
                    this.setProgressTileError(tileId, 'Koneksi bermasalah', {
                        autoRemove: true
                    });
                    this.showError('Connection Error');
                }
            }

            async fetchLoadingList(code) {
                return await $.ajax({
                    type: 'GET',
                    url: `https://dea-dev.aiia.co.id/api/v1/loading-lists/${code}`,
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    },
                    dataType: 'json'
                });
            }

            async processLoadingListData(data, progressCb = null, tileId = null) {
                const {
                    pds_number,
                    number: ll,
                    cycle,
                    customer_code,
                    delivery_date,
                    shipping_date
                } = data;

                const existingPDS = localStorage.getItem('pdsNumber');
                if (existingPDS && data.pds_number !== existingPDS) {
                    progressCb?.('LL tidak sesuai');
                    this.showError('Loading list tidak sesuai!', () => {
                        if (typeof notMatchLlSound === 'function') notMatchLlSound();
                    });
                    return;
                }

                try {
                    progressCb?.('Menyimpan header...');
                    await this.storeLoadingList(ll, pds_number, cycle, customer_code, delivery_date,
                        shipping_date);

                    progressCb?.('Menyimpan data lokal...');
                    this.storeLoadingListData(data);

                    progressCb?.('Memperbarui tampilan...');

                    if (!tileId) this.updateLoadingListUI(data);
                    $('#cycle-display').text(data.cycle);

                    progressCb?.('Menyiapkan IndexedDB...');
                    await this.initializeDatabase(data);

                    progressCb?.('Menyimpan detail item...');
                    await this.storeLoadingListDetails(ll, data.items);

                    progressCb?.('Finalisasi...');
                    await this.customerCheck(customer_code);
                    this.pullingQuantity();
                    this.checkLoadingList();
                    this.customerCharStore(customer_code, pds_number);

                    this.focusInput();

                } catch (error) {
                    progressCb?.('Gagal memproses');
                    this.showError('Gagal memproses loading list');
                }
            }

            async storeLoadingList(ll, pds, cycle, customerCode, deliveryDate, shippingDate) {
                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `/loading-list/store/${ll}/${pds}/${cycle}/${customerCode}/${deliveryDate || ''}/${shippingDate || ''}`,
                        dataType: 'json'
                    });

                    // console.log('Loading list stored:', response.status);
                    return response;

                } catch (xhr) {
                    console.error('Error storing loading list:', xhr);

                    if (xhr.status === 0) {
                        this.showError('Connection Error');
                        throw new Error('Network error storing loading list');
                    } else {
                        const errorMsg = xhr.responseJSON?.errors || 'Gagal menyimpan loading list';
                        this.showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                }
            }

            async storeLoadingListDetails(ll, items) {
                const promises = items.map((item, index) => {
                    const qtyPerKbn = item.total_qty / item.total_kanban_qty;

                    return new Promise((resolve, reject) => {
                        setTimeout(() => {
                            $.ajax({
                                type: 'GET',
                                url: `/loading-list/storeDetail/${ll}/${item.part_number_cust}/${item.part_number_int}/${item.total_kanban_qty}/${qtyPerKbn}/${item.total_qty}/${item.actual_kanban_qty}`,
                                dataType: 'json',
                                success: (response) => {
                                    // console.log(
                                    //     `Stored detail for ${item.part_number_cust}: ${response.status}`
                                    // );
                                    resolve(response);
                                },
                                error: (xhr, status, error) => {
                                    // console.error(
                                    //     `Failed to store detail for ${item.part_number_cust}:`,
                                    //     xhr);

                                    if (xhr.status === 0) {
                                        reject(new Error(
                                            `Network error storing ${item.part_number_cust}`
                                        ));
                                    } else {
                                        console.warn(
                                            `Server error ${xhr.status} for ${item.part_number_cust}, continuing...`
                                        );
                                        resolve({
                                            status: 'error',
                                            item: item
                                                .part_number_cust,
                                            error: xhr
                                                .responseJSON
                                                ?.errors ||
                                                error
                                        });
                                    }
                                }
                            });
                        }, index * 200); // Using 200ms delay like original
                    });
                });

                try {
                    const results = await Promise.all(promises);
                    const failures = results.filter(result => result && result.status === 'error');

                    if (failures.length > 0) {
                        console.warn(`${failures.length} items failed to store:`, failures);
                    }

                    return results;
                } catch (error) {
                    // console.error('Critical error storing loading list details:', error);
                    this.showError('Gagal menyimpan detail loading list. Koneksi bermasalah.');
                    throw error;
                }
            }

            storeLoadingListData(data) {
                localStorage.setItem('pds_local', data.pds_number);
                localStorage.setItem(`ll_${data.number}`, data.number);
                localStorage.setItem('pdsNumber', data.pds_number);
                localStorage.setItem('cycle', data.cycle);
            }

            updateLoadingListUI(data, tileId = null) {
                if (tileId) this.replaceTileWithFinal(tileId, data.number);
                // update elemen lain:
                $('#cycle-display').text(data.cycle);
            }

            async initializeDatabase(data) {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(data.pds_number);

                    request.onupgradeneeded = (event) => {
                        const database = event.target.result;
                        if (!database.objectStoreNames.contains('loadingList')) {
                            const objectStore = database.createObjectStore('loadingList');
                            objectStore.createIndex('loadingListDetail', 'seri');
                        }
                    };

                    request.onsuccess = async (event) => {
                        const database = event.target.result;
                        await this.populateDatabase(database, data);
                        database.close();
                        resolve();
                    };

                    request.onerror = () => reject(request.error);
                });
            }

            async populateDatabase(database, data) {
                const transaction = database.transaction(['loadingList'], 'readwrite');
                const objectStore = transaction.objectStore('loadingList');

                const promises = data.items.map(item => {
                    return new Promise((resolve) => {
                        const key = item.part_number_cust;
                        const getRequest = objectStore.get(key);

                        getRequest.onsuccess = (event) => {
                            if (!event.target.result) {
                                objectStore.put({
                                    key,
                                    loading_list_number: data.number,
                                    internal: item.part_number_int,
                                    customer: item.part_number_cust,
                                    qty_per_kbn: item.total_qty / item
                                        .total_kanban_qty,
                                    actual_qty: item.actual_kanban_qty,
                                    total_qty: item.total_kanban_qty,
                                    seri: []
                                }, key);
                            }
                            resolve();
                        };
                    });
                });

                await Promise.all(promises);
            }

            handleCustomerKanban(code) {
                const processedCode = this.processCustomerCode(code);
                this.findKanbanInDatabase(processedCode, code);
            }

            processCustomerCode(code) {
                let processed = code;
                const charLength = parseInt(localStorage.getItem('char_length') || '0');
                const charFirst = parseInt(localStorage.getItem('char_first') || '0');

                if (charLength > 0) {
                    processed = code.substr(charFirst, charLength);
                }

                processed = processed.trim().replace(/-/g, '').toUpperCase();

                // Handle Suzuki special cases
                if (charLength === 17 && processed.substr(10, 3) === '000') {
                    processed = processed.slice(0, -3);
                } else if (charLength === 15 && processed.slice(-3) === '000') {
                    processed = processed.slice(0, -3);
                }

                return processed;
            }

            async findKanbanInDatabase(processedCode, originalCode) {
                // Get current PDS dynamically
                const databaseName = this.getCurrentPDS();

                // console.log('PDS value for database:', databaseName);
                // console.log('Available PDS values:', {
                //     'getCurrentPDS()': databaseName,
                //     'pds_local': localStorage.getItem('pds_local'),
                //     'pdsNumber': localStorage.getItem('pdsNumber')
                // });

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                const request = indexedDB.open(databaseName);

                request.onsuccess = (event) => {
                    const database = event.target.result;

                    // Debug: Check database info
                    // console.log('Database opened:', database.name);
                    // console.log('Available object stores:', Array.from(database.objectStoreNames));

                    // Check if objectStore exists before creating transaction
                    if (!database.objectStoreNames.contains('loadingList')) {
                        console.error('Object store "loadingList" not found in database');
                        // console.log('Database version:', database.version);
                        this.showError('Database belum di-initialize. Scan loading list dulu!');
                        database.close();
                        return;
                    }

                    try {
                        const transaction = database.transaction(['loadingList'], 'readonly');
                        const objectStore = transaction.objectStore('loadingList');
                        let found = false;

                        objectStore.openCursor().onsuccess = (event) => {
                            const cursor = event.target.result;
                            if (cursor) {
                                const record = cursor.value;
                                // console.log('Checking record:', record.customer, 'against',
                                //     processedCode);
                                if (processedCode === record.customer) {
                                    found = true;
                                    if (record.seri.length >= record.total_qty) {
                                        this.showError('Part number sudah complete!', () => {
                                            if (typeof fullfilledSound === 'function')
                                                fullfilledSound();
                                        });
                                        this.setIndicator('danger');
                                    } else {
                                        this.updateKanbanDisplay(record, processedCode,
                                            originalCode);
                                    }
                                }
                                cursor.continue();
                            } else if (!found) {
                                this.showError('Kanban tidak dikenali / sesuai!', () => {
                                    if (typeof unknownSound === 'function') unknownSound();
                                });
                            }
                        };

                        objectStore.openCursor().onerror = (event) => {
                            console.error('Cursor error:', event.target.error);
                            this.showError('Error membaca database');
                        };

                        transaction.onerror = (event) => {
                            console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                        transaction.oncomplete = () => {
                            database.close();
                        };

                    } catch (error) {
                        console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                };

                request.onerror = (event) => {
                    console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                };
            }

            handleKanbanBarcode(code) {
                if (!localStorage.getItem('customerPart')) {
                    this.showError('Scan kanban customer dulu!', () => {
                        if (typeof scanCustomerFirstSound === 'function') scanCustomerFirstSound();
                    });
                    return;
                }

                const kanbanData = this.extractKanbanData(code.toUpperCase());
                if (kanbanData) {
                    this.processInternalKanban(kanbanData);
                }
            }

            extractKanbanData(code) {
                const patterns = {
                    230: {
                        internal: [41, 19],
                        seri: [123, 4]
                    },
                    220: {
                        internal: [35, 16],
                        seri: [130, 4]
                    },
                    241: {
                        internal: [35, 12],
                        seri: [127, 4]
                    },
                    218: {
                        internal: [41, 16],
                        seri: [123, 4]
                    },
                    242: {
                        internal: [35, 12],
                        seri: [127, 4]
                    }
                };

                const pattern = patterns[code.length];
                if (!pattern) return null;

                return {
                    internal: code.substr(pattern.internal[0], pattern.internal[1]),
                    seri: code.substr(pattern.seri[0], pattern.seri[1])
                };
            }

            processInternalKanban({
                internal,
                seri
            }) {
                const databaseName = this.getCurrentPDS();

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                const request = indexedDB.open(databaseName);

                request.onsuccess = (event) => {
                    const database = event.target.result;

                    // Check if objectStore exists
                    if (!database.objectStoreNames.contains('loadingList')) {
                        // console.error('Object store "loadingList" not found');
                        this.showError('Database belum di-initialize. Scan loading list dulu!');
                        database.close();
                        return;
                    }

                    try {
                        const transaction = database.transaction(['loadingList'], 'readwrite');
                        const objectStore = transaction.objectStore('loadingList');
                        const customerPart = localStorage.getItem('customerPart');

                        objectStore.get(customerPart).onsuccess = (event) => {
                            const record = event.target.result;
                            if (record) {
                                this.checkInternalAndCustomer(database, record, internal,
                                    customerPart, seri);
                            } else {
                                this.showError('Kanban tidak ditemukan!', () => {
                                    if (typeof unknownSound === 'function') unknownSound();
                                });
                                database.close();
                            }
                        };

                        objectStore.get(customerPart).onerror = (event) => {
                            // console.error('Get request error:', event.target.error);
                            this.showError('Error membaca data kanban');
                            database.close();
                        };

                        transaction.onerror = (event) => {
                            // console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                    } catch (error) {
                        // console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                };

                request.onerror = (event) => {
                    // console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                };
            }

            handleMMKIKanban(code) {
                this.processCustomerSpecificKanban(code.trimEnd(), 'MMKI');
            }

            handleTBINAKanban(code) {
                let processed = code;
                if (code.length === 36) {
                    processed = code.substr(parseInt(localStorage.getItem('char_first') || '0'),
                        parseInt(localStorage.getItem('char_length') || '0'));
                } else if (code.length === 14) {
                    processed = code.substr(0, 11);
                }
                processed = processed.trim().replace(/-/g, '').toUpperCase();
                this.processCustomerSpecificKanban(processed, 'TB INA');
            }

            handleTTIKanban(code) {
                const processed = code.substr(7, 10);
                this.processCustomerSpecificKanban(processed.trimEnd(), 'TTI INDONESIA');
            }

            processCustomerSpecificKanban(code, customerType) {
                const databaseName = this.getCurrentPDS();

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                const request = indexedDB.open(databaseName);

                request.onsuccess = (event) => {
                    const database = event.target.result;

                    // Check if objectStore exists
                    if (!database.objectStoreNames.contains('loadingList')) {
                        // console.error('Object store "loadingList" not found');
                        this.showError('Database belum di-initialize. Scan loading list dulu!');
                        database.close();
                        return;
                    }

                    try {
                        const transaction = database.transaction(['loadingList'], 'readonly');
                        const objectStore = transaction.objectStore('loadingList');
                        let found = false;

                        objectStore.openCursor().onsuccess = (event) => {
                            const cursor = event.target.result;
                            if (cursor) {
                                const record = cursor.value;
                                if (code === record.customer) {
                                    found = true;
                                    if (record.seri.length >= record.total_qty) {
                                        this.showError('Part number sudah complete!', () => {
                                            if (typeof fullfilledSound === 'function')
                                                fullfilledSound();
                                        });
                                        this.setIndicator('danger');
                                    } else {
                                        this.updateKanbanDisplay(record, code);
                                    }
                                }
                                cursor.continue();
                            } else if (!found) {
                                this.showError('Kanban tidak sesuai!', () => {
                                    if (typeof notMatchSound === 'function')
                                        notMatchSound();
                                });
                            }
                        };

                        objectStore.openCursor().onerror = (event) => {
                            // console.error('Cursor error:', event.target.error);
                            this.showError('Error membaca database');
                        };

                        transaction.onerror = (event) => {
                            // console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                        transaction.oncomplete = () => {
                            database.close();
                        };

                    } catch (error) {
                        // console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                };

                request.onerror = (event) => {
                    // console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                };
            }

            handleLogout() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }

            updateKanbanDisplay(record, customerCode, originalCode = null) {
                $('#cust-display').text(record.customer);
                $('#int-display').text('-');
                $('#qty-display').text(`${record.seri.length}/${record.total_qty}`);

                this.setIndicator('warning');
                this.resetIndicator();

                localStorage.setItem('customerPart', record.customer);
                if (originalCode) {
                    localStorage.setItem('originalCustomerPart', originalCode);
                }
            }

            setIndicator(type) {
                const indicator = $('#indicator');
                indicator.removeClass('bg-success bg-warning bg-danger');
                indicator.addClass(`bg-${type}`);
            }

            showError(message, soundFunction = null) {
                if (typeof notif === 'function') {
                    notif('error', message);
                }
                if (soundFunction) soundFunction();
                this.delayedFocus();
            }

            delayedFocus() {
                setTimeout(() => this.focusInput(), 1000);
            }

            focusInput() {
                $('#code').focus();
            }

            getLoadingListNumber() {
                return typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
            }

            checkInternalAndCustomer(database, record, internal, customerPart, seri) {
                if (typeof checkInternalAndCustomer === 'function') {
                    checkInternalAndCustomer(database, record, internal, customerPart, seri);
                }
            }

            resetIndicator() {
                if (typeof resetIndicator === 'function') {
                    resetIndicator();
                }
            }

            async customerCheck(customerCode) {
                return new Promise((resolve, reject) => {
                    if (typeof customerCheck === 'function') {
                        customerCheck(customerCode).then(resolve).catch(reject);
                    } else {
                        resolve();
                    }
                });
            }

            pullingQuantity() {
                if (typeof pullingQuantity === 'function') {
                    pullingQuantity();
                }
            }

            checkLoadingList() {
                if (typeof checkLoadingList === 'function') {
                    checkLoadingList();
                }
            }

            customerCharStore(customerCode, pdsNumber) {
                if (typeof customerCharStore === 'function') {
                    customerCharStore(customerCode, pdsNumber);
                }
            }
        }

        // Initialize the barcode scanner
        $(document).ready(() => {
            const scanner = new BarcodeScanner();
            scanner.init();
        });
    });
</script>
