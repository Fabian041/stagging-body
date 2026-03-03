@extends('layouts.root.minimal')

@section('main')
    <div class="mb-2">
        <button onclick="history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius:12px; margin-left:-8px;">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="card border" style="border-radius: 12px;">
                                <div class="card-header py-1">
                                    <strong>Part Number</strong>
                                </div>
                                <div class="card-body">
                                    <div id="part_number_loading" class="small text-muted mb-1" style="min-height: 1.25rem; display: none;">
                                        <i class="fas fa-spinner fa-spin"></i> Scanning...
                                    </div>
                                    <div class="form-group mb-0">
                                        <input id="detail_no" class="form-control" name="detail_no" required tabindex="-1" placeholder="Hasil scan akan tampil di sini">
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-2" style="border-radius: 12px;">
                                <div class="card-header py-1">
                                    <strong>Loading List</strong>
                                </div>
                                <div class="card-body p-2">
                                    <table class="table table-sm table-bordered mb-0 small" style="font-size: 0.8rem;">
                                        <thead>
                                            <tr>
                                                <th>PN (Int)</th>
                                                <th>PN (Cust)</th>
                                                <th style="width: 50px;" class="text-right">Cur</th>
                                                <th style="width: 50px;" class="text-right">Tar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="loading_list_body">
                                            <tr><td colspan="4" class="text-muted text-center">&nbsp;</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Cari bagian ini di file Anda -->
                            <div class="card border mt-2" style="border-radius: 12px;">
                                <div class="card-header py-1">
                                    <strong>Counter</strong>
                                </div>
                                <div class="card-body" style="height:110px;">
                                    <!-- Tambahkan flex-column agar angka dan tanggal bertumpuk -->
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <div class="display-4 font-weight-bold" id="counter" style="line-height: 1;">0</div>
                                        <!-- Elemen Tanggal Baru -->
                                        <div class="text-muted small mt-1" id="sysdate" style="font-weight: 500;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div id="alert" class="alert alert-primary mb-1 shadow-sm py-1" style="border-radius:6px;">
                                <div class="d-flex justify-content-center align-items-center font-weight-bold"
                                    style="font-size:10px; white-space:nowrap;">

                                    <span>STEP 1</span>
                                    <i class="fas fa-file-invoice mx-1"></i>
                                    <span>Loading List</span>

                                    <span class="mx-2">→</span>

                                    <span>STEP 2</span>
                                    <i class="fas fa-barcode mx-1"></i>
                                    <span>Kanban</span>

                                    <span class="mx-2">→</span>

                                    <span>STEP 3</span>
                                    <i class="fas fa-tag mx-1"></i>
                                    <span>Label Part</span>

                                </div>
                            </div>
                            <div class="card border shadow-sm" style="border-radius: 12px;">
                                <div class="card-header py-2 d-flex align-items-center justify-content-between bg-light">
                                    <strong><i class="fas fa-image"></i> Preview & Status</strong>
                                    <span class="badge badge-info" id="previewImageLabel">Information Display</span>
                                </div>
                                <div class="card-body p-3">
                                    <!-- PINDAHAN ALERT BODY ADA DI SINI -->
                                    <div id="status-container" class="alert alert-success text-center mb-3">
                                        <h4 class="alert-heading mb-1" id="alert-header"><i class="fas fa-check-circle"></i> Ready</h4>
                                        <p class="mb-0 font-weight-bold" id="alert-body">Silahkan Scan Loading List untuk memulai</p>
                                    </div>

                                    <div id="imageDiv" class="text-center bg-white border" style="min-height: 440px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <img id="previewImg" src="" alt="Part image" class="img-fluid" style="max-height: 500px; display: none;" />
                                        <div id="previewPlaceholder" class="text-muted py-5">
                                            <i class="fas fa-image fa-5x mb-3" style="opacity: 0.2;"></i>
                                            <p class="mb-0">Gambar akan muncul otomatis jika label cocok</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card border" style="border-radius: 12px;">
                                <div class="card-header py-1"><strong>Type</strong></div>
                                <div class="card-body">
                                    <div id="delivery" class="form-group mb-0">
                                        <button id="btnOEM" value="OEM" type="button" class="btn btn-block btn-primary" onclick="func_change_delivery(this);">OEM</button>
                                        <button id="btnDANDORY" value="DANDORY" type="button" class="btn btn-block btn-default" onclick="func_change_delivery(this);">DANDORY</button>
                                        <input id="delivery_type" value="OEM" type="hidden">
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-2" style="border-radius: 12px;">
                                <div class="card-header py-1"><strong>Dock</strong></div>
                                <div class="card-body p-0">
                                    <div id="dock" class="form-group mb-0" style="height: 380px; overflow-y: auto; padding: 8px;">
                                        <!-- List Dock buttons stay same -->
                                        <button value="OTHER" type="button" class="btn btn-block btn-primary" onclick="func_change_dock(this);">OTHER</button>
                                        <button value="43" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">43</button>
                                        <button value="53" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">53</button>
                                        <button value="1L" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">1L</button>
                                        <button value="1N" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">1N</button>
                                        <button value="S1" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">S1</button>
                                        <button value="6I" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">6I</button>
                                        <button value="TAM-TAM" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">TAM-TAM</button>
                                        <button value="TAM-ADM" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">TAM-ADM</button>
                                        <button value="TAM-HINO" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">TAM-HINO</button>
                                        <button value="ADM-AS" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">ADM-AS</button>
                                        <button value="ADM-KP" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">ADM-KP</button>
                                        <button value="YHA" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">YHA</button>
                                        <button value="ADM" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">ADM</button>
                                        <button value="TTI" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">TTI</button>
                                        <button value="S1-TAM" type="button" class="btn btn-block btn-default" onclick="func_change_dock(this);">S1-TAM</button>
                                        <input id="dock_type" value="OTHER" type="hidden">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
@endsection

@section('custom-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        // Base URL gambar lokal PIS (storage/app/public/pis) — data tetap dari API
        var pisImageBase = "{{ asset('storage/pis') }}";
        var pisImageDefault = "{{ asset('storage/pis/default.JPG') }}";

        // Gambar hanya tampil jika lookup berdasarkan Part Number (Cust) — label yang di-scan = Part Number (Cust).
        // Hanya gunakan part_number_cust untuk path gambar; Part Number (Int) tidak dipakai.
        function setPreviewImage(partNumberInt, partNumberCust) {
            var cust = (partNumberCust || '').toString().trim();
            var img = document.getElementById('previewImg');
            var placeholder = document.getElementById('previewPlaceholder');
            if (!cust) {
                img.src = '';
                img.style.display = 'none';
                if (placeholder) placeholder.style.display = 'block';
                return;
            }
            var type = ($('#delivery_type').val() || 'OEM').toUpperCase();
            var dock = ($('#dock_type').val() || 'OTHER').toUpperCase();
            var candidates = [];
            var add = function (baseName, ext) {
                if (baseName) candidates.push(pisImageBase + '/' + encodeURIComponent(baseName) + (ext || ''));
            };
            // Pola lengkap: PARTCUST-TYPE-DOCK dengan berbagai ekstensi
            add(cust + '-' + type + '-' + dock + '.JPG');
            add(cust + '-' + type + '-' + dock + '.jpg');
            add(cust + '-' + type + '-' + dock + '.PNG');
            add(cust + '-' + type + '-' + dock + '.png');
            // Fallback: hanya pakai PARTCUST.ext
            ['png', 'jpg', 'jpeg'].forEach(function (ext) { add(cust, '.' + ext); });
            var idx = 0;
            function tryNext() {
                if (idx >= candidates.length) {
                    img.src = '';
                    img.style.display = 'none';
                    if (placeholder) placeholder.style.display = 'block';
                    return;
                }
                img.src = candidates[idx++];
                img.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            }
            img.onerror = tryNext;
            img.onload = function () {
                img.onerror = null;
                img.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
                if (typeof _savedScrollTop === 'number') window.scrollTo(0, _savedScrollTop);
            };
            tryNext();
        }

        function clearPreviewImage() {
            var img = document.getElementById('previewImg');
            var placeholder = document.getElementById('previewPlaceholder');
            if (img) { img.src = ''; img.style.display = 'none'; }
            if (placeholder) placeholder.style.display = 'block';
        }

        // Fungsi untuk menangani perubahan pada delivery type
        function func_change_delivery(obj) {
            $('#delivery').find('button').removeClass('btn-primary');
            $('#delivery').find('button').addClass('btn-default');
            $(obj).addClass('btn-primary');
            $('#delivery_type').val(obj.value);
            // Refresh gambar hanya jika sudah ada part yang tervalidasi (scan label = Part Number (Cust))
            if (currentPreviewItem) {
                setPreviewImage(currentPreviewItem.part_number_int || '', currentPreviewItem.part_number_cust || '');
            }
        }

        // Fungsi untuk menangani perubahan pada dock type
        function func_change_dock(obj) {
            $('#dock').find('button').removeClass('btn-primary');
            $('#dock').find('button').addClass('btn-default');
            $(obj).addClass('btn-primary');
            $('#dock_type').val(obj.value);
            // Refresh gambar hanya jika sudah ada part yang tervalidasi (scan label = Part Number (Cust))
            if (currentPreviewItem) {
                setPreviewImage(currentPreviewItem.part_number_int || '', currentPreviewItem.part_number_cust || '');
            }
        }

        var barcode = "";
        var token = ""; // Variabel untuk menyimpan token yang diperoleh setelah login
        var stage = 1; // 1 = scan loading list, 2 = scan kanban, 3 = scan label (decrement quantity)
        var loadingListItems = []; // Items from API with remaining qty, updated on each part scan
        var currentLoadingListNumber = ''; // Store current loading list number for saving scan details
        var lastScannedKanban = ''; // Data kanban terakhir untuk validasi label (label harus ada di kanban)
        // Counter harian untuk scan label part (bukan scan loading list)
        var loadingListScanCount = 0;
        // Item yang terakhir tervalidasi untuk preview (hanya jika scan label = part_number_cust)
        var currentPreviewItem = null;
        // Delay 30 detik untuk label yang sama: simpan label terakhir + waktu scan
        var lastScannedLabel = '';
        var lastScannedLabelTime = 0;
        var LABEL_SAME_DELAY_MS = 30 * 1000; // 30 detik
        // Simpan posisi scroll sebelum aksi scan agar tampilan tidak loncat ke bawah setelah scan
        var _savedScrollTop = 0;

        function getDailyCounterKey() {
            var d = new Date();
            var m = (d.getMonth() + 1); var day = d.getDate();
            return 'loadingListScanCount_' + d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
        }
        function loadDailyCounter() {
            try {
                var v = localStorage.getItem(getDailyCounterKey());
                loadingListScanCount = (v != null && v !== '') ? parseInt(v, 10) : 0;
                if (isNaN(loadingListScanCount)) loadingListScanCount = 0;
            } catch (e) { loadingListScanCount = 0; }
            
            $('#counter').text(loadingListScanCount);

            // --- TAMBAHKAN LOGIKA TANGGAL DI SINI ---
            var d = new Date();
            var day = String(d.getDate()).padStart(2, '0');
            var month = String(d.getMonth() + 1).padStart(2, '0');
            var year = d.getFullYear();
            var formattedDate = day + '-' + month + '-' + year;
            $('#sysdate').text(formattedDate);
            // ----------------------------------------
        }
        function saveDailyCounter() {
            try { localStorage.setItem(getDailyCounterKey(), String(loadingListScanCount)); } catch (e) {}
        }

        function cleanBarcode(s) {
            if (!s) return '';
            return ('' + s).replace(/[\r\n\t]/g, '').replace(/[\x00-\x1F\x7F]/g, '').trim();
        }

        // Hapus dua karakter terakhir dari hasil scan (sampah yang ikut terbaca)
        function stripLastTwoChars(s) {
            if (!s) return '';
            var str = ('' + s).trim();
            if (str.length <= 1) return str;
            return str.substring(0, str.length - 1);
        }

        function updateStepIndicator() {
            $('#step1 span').removeClass('badge-primary badge-success').addClass('badge-secondary');
            $('#step2 span').removeClass('badge-primary badge-success').addClass('badge-secondary');
            $('#step3 span').removeClass('badge-primary badge-success').addClass('badge-secondary');
            if (stage === 1) {
                $('#step1 span').removeClass('badge-secondary').addClass('badge-primary');
            } else if (stage === 2) {
                $('#step1 span').removeClass('badge-secondary').addClass('badge-success');
                $('#step2 span').removeClass('badge-secondary').addClass('badge-primary');
            } else if (stage === 3) {
                $('#step1 span, #step2 span').removeClass('badge-secondary').addClass('badge-success');
                $('#step3 span').removeClass('badge-secondary').addClass('badge-primary');
            }
        }

        function updateCounter() {
            // Counter menampilkan jumlah scan label part (harian)
            $('#counter').text(loadingListScanCount);
        }

        function renderLoadingList() {
            var tbody = $('#loading_list_body');
            tbody.empty();
            if (!loadingListItems.length) {
                tbody.append('<tr><td colspan="4" class="text-muted text-center">&nbsp;</td></tr>');
                return;
            }
            loadingListItems.forEach(function (item) {
                var target = item.total_qty != null ? item.total_qty : (item.total_kanban_qty != null ? item.total_kanban_qty : 0);
                var remaining = item.remaining != null ? item.remaining : target;
                var current = Math.max(0, target - remaining);
                var row = $('<tr></tr>');
                if (remaining <= 0) row.addClass('table-success');
                row.append($('<td></td>').text(item.part_number_int || '—'));
                row.append($('<td></td>').text(item.part_number_cust || '—'));
                row.append($('<td class="text-right"></td>').text(current));
                row.append($('<td class="text-right"></td>').text(target));
                tbody.append(row);
            });
        }

        $(document).keydown(function(e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault(); // Prevent default Enter key behavior (e.g., form submission or scrolling)
                var rawScanned = cleanBarcode(barcode || '');
                // For display, strip last two chars
                var displayBarcode = stripLastTwoChars(rawScanned);
                // For processing, use full barcode
                var processBarcode = rawScanned;
                var detail_no = $('#detail_no').val();
                _savedScrollTop = $(window).scrollTop();

                // Tampilkan loading
                $('#part_number_loading').show();

                function doStageAction() {
                    // Display scanned barcode only for loading list and label scans, not kanban
                    if (stage === 1 || stage === 3) {
                        $('#detail_no').val(displayBarcode);
                    }
                    if (stage === 1) {
                        scanLoadingList(processBarcode, displayBarcode);
                    } else if (stage === 2 && loadingListItems.length > 0) {
                        processKanbanScan(processBarcode);
                    } else if (stage === 3 && loadingListItems.length > 0) {
                        processPartScan(processBarcode, displayBarcode);
                    } else {
                        scanLoadingList(processBarcode, displayBarcode);
                    }
                    // Sembunyikan loading setelah proses
                    $('#part_number_loading').hide();
                    // Kembalikan posisi scroll setelah DOM selesai di-update (cegah scroll ke bawah)
                    setTimeout(function() { $(window).scrollTop(_savedScrollTop); }, 0);
                }

                if (!token) {
                    loginApi(function() { doStageAction(); });
                } else {
                    doStageAction();
                }

                barcode = "";
            } else if (code >= 32 && code <= 126) {
                barcode += String.fromCharCode(code);
            }
        });

        function isLoadingListComplete() {
            if (!loadingListItems.length) return false;
            for (var i = 0; i < loadingListItems.length; i++) {
                if ((loadingListItems[i].remaining || 0) > 0) return false;
            }
            return true;
        }

        // Stage 2: Scan kanban — simpan data untuk validasi label (label harus terkandung di data kanban)
        function processKanbanScan(barcode) {
            var currentScroll = $(window).scrollTop();
            var raw = cleanBarcode(barcode || '');
            
            if (!raw) {
                $('#alert').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Kanban Kosong');
                $('#alert-body').text('Data kanban tidak valid.');
                $(window).scrollTop(currentScroll);
                return;
            }

            // --- LOGIKA VALIDASI: Cek apakah Part di dalam Kanban ini sudah terpenuhi (QTY 0) ---
            var isAlreadyFinished = false;
            var matchedPartName = "";

            for (var i = 0; i < loadingListItems.length; i++) {
                var item = loadingListItems[i];
                var pInt = (item.part_number_int || '').toString().toUpperCase();
                var pCust = (item.part_number_cust || '').toString().toUpperCase();
                var kanbanContent = raw.toUpperCase();

                // Cek apakah Part Number (Int atau Cust) ada di dalam string Barcode Kanban
                if ((pInt && kanbanContent.indexOf(pInt) !== -1) || (pCust && kanbanContent.indexOf(pCust) !== -1)) {
                    // Jika Part ditemukan di dalam barcode, cek sisa qty-nya
                    if (item.remaining <= 0) {
                        isAlreadyFinished = true;
                        matchedPartName = pCust || pInt;
                        break;
                    }
                }
            }

            if (isAlreadyFinished) {
                // Tampilkan Alert Error
                $('#alert').removeClass('alert-success alert-primary').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-ban"></i> Kanban Sudah Terpenuhi');
                $('#alert-body').text('Part "' + matchedPartName + '" sudah mencapai target (Sisa: 0). Silahkan scan Kanban untuk part lain yang belum selesai.');
                
                // JANGAN update input detail_no
                // JANGAN pindah stage (tetap di stage 2)
                $(window).scrollTop(currentScroll);
                return; // Berhenti di sini, tidak melanjutkan proses
            }
            // --- END LOGIKA VALIDASI ---

            // JIKA LOLOS VALIDASI (Sisa Qty masih > 0):
            lastScannedKanban = raw;
            stage = 3;
            updateStepIndicator();

            $('#alert').removeClass('alert-danger alert-warning').addClass('alert-success');
            $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK');
            $('#alert-body').text('Kanban diterima. Silahkan scan LABEL PART.');
            
            $(window).scrollTop(currentScroll);
        }

        function processPartScan(barcode, displayBarcode) {
            var currentScroll = $(window).scrollTop();
            var raw = cleanBarcode(barcode || '');

            // --- LOGIKA PEMBERSIHAN BARCODE ---
            var cleanLabel = raw.split(/\s{2,}/)[0].trim();
            var matched = null;

            // 1. Validasi: Apakah part ada di dalam Kanban?
            var kanbanUpper = (lastScannedKanban || '').toUpperCase();
            var rawUpper = raw.toUpperCase();
            var cleanUpper = cleanLabel.toUpperCase();

            var existsInKanban = (kanbanUpper.indexOf(rawUpper) !== -1 || kanbanUpper.indexOf(cleanUpper) !== -1);

            if (!lastScannedKanban || !existsInKanban) {
                $('#alert').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-times-circle"></i> Label Tidak Sesuai Kanban');
                $('#alert-body').text('Label "' + (cleanLabel || raw) + '" tidak ditemukan dalam data kanban. Pastikan scan kanban yang benar.');
                $(window).scrollTop(currentScroll);
                return;
            }

            // 2. Delay untuk label yang sama (Mencegah double scan tidak sengaja)
            var now = Date.now();
            if (raw && raw === lastScannedLabel && (now - lastScannedLabelTime) < LABEL_SAME_DELAY_MS) {
                var sisaWaktu = Math.ceil((LABEL_SAME_DELAY_MS - (now - lastScannedLabelTime)) / 1000);
                $('#alert').removeClass('alert-success alert-danger').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-clock"></i> Label sama');
                $('#alert-body').text('Label ini baru saja di-scan. Tunggu ' + sisaWaktu + ' detik lagi.');
                $(window).scrollTop(currentScroll);
                return;
            }

            // 3. PENCARIAN ITEM DI LOADING LIST
            // Tahap A: Match Exact
            for (var i = 0; i < loadingListItems.length; i++) {
                var item = loadingListItems[i];
                if ((item.remaining || 0) <= 0) continue;

                var pcust = (item.part_number_cust || '').toString().trim();
                var pint = (item.part_number_int || '').toString().trim();

                if (pcust === raw || pcust === cleanLabel || pint === raw || pint === cleanLabel) {
                    matched = item;
                    break;
                }
            }

            // Tahap B: Match Contains (Jika exact match tidak ditemukan)
            if (!matched) {
                for (var j = 0; j < loadingListItems.length; j++) {
                    var it = loadingListItems[j];
                    if ((it.remaining || 0) <= 0) continue;

                    var pcust2 = (it.part_number_cust || '').toString().toUpperCase();
                    var pint2 = (it.part_number_int || '').toString().toUpperCase();

                    if (pcust2 && (cleanUpper.indexOf(pcust2) !== -1 || pcust2.indexOf(cleanUpper) !== -1)) {
                        matched = it;
                        break;
                    }
                    if (pint2 && (cleanUpper.indexOf(pint2) !== -1 || pint2.indexOf(cleanUpper) !== -1)) {
                        matched = it;
                        break;
                    }
                }
            }

            // 4. JIKA ITEM DITEMUKAN (MATCHED)
            if (matched) {
                lastScannedLabel = raw;
                lastScannedLabelTime = Date.now();

                // Decrement Quantity
                matched.remaining = (matched.remaining != null ? matched.remaining : matched.total_qty || 0) - 1;
                if (matched.remaining < 0) matched.remaining = 0;

                loadingListScanCount += 1;
                renderLoadingList();
                updateCounter();
                saveDailyCounter();

                // Simpan ke Database
                if (currentLoadingListNumber) {
                    $.ajax({
                        url: '{{ url("pis/update-scan-detail") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            loading_list_number: currentLoadingListNumber,
                            part_number_int: matched.part_number_int || '',
                            part_number_cust: matched.part_number_cust || ''
                        }
                    });
                }

                // Update input field Part Number & Preview Gambar
                $('#detail_no').val(matched.part_number_int || '');
                
                var normCust = (matched.part_number_cust || '').toString().trim().toUpperCase();
                if (normCust && (cleanUpper === normCust || cleanUpper.indexOf(normCust) !== -1 || normCust.indexOf(cleanUpper) !== -1)) {
                    currentPreviewItem = matched;
                    setPreviewImage(matched.part_number_int || '', matched.part_number_cust || '');
                } else {
                    currentPreviewItem = null;
                    clearPreviewImage();
                }

                // --- LOGIKA PERPINDAHAN STAGE BERDASARKAN SISA QUANTITY ---
                if (matched.remaining > 0) {
                    // MASIH ADA SISA: Tetap di Stage 3 (Tunggu scan box part selanjutnya)
                    stage = 3; 
                    $('#alert').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Part OK');
                    $('#alert-body').text((matched.part_number_cust || matched.part_number_int) + ' Berhasil di-scan. Sisa: ' + matched.remaining + ' box.');
                } else {
                    // SUDAH HABIS: Kembali ke Stage 2 (Harus scan kanban baru untuk part lain)
                    stage = 2;
                    lastScannedKanban = ''; // Reset kanban agar user wajib scan kanban baru
                    $('#alert').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-double"></i> Item Selesai');
                    $('#alert-body').text('Quantity untuk part ini sudah terpenuhi. Silahkan scan KANBAN selanjutnya.');
                }

                updateStepIndicator();

                // Cek jika seluruh Loading List sudah selesai semua
                if (isLoadingListComplete()) {
                    stage = 1;
                    currentLoadingListNumber = '';
                    lastScannedKanban = '';
                    updateStepIndicator();
                    Swal.fire({
                        title: 'Loading List Complete!',
                        text: 'Semua item dalam daftar telah terpenuhi.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            } else {
                // JIKA TIDAK ADA YANG COCOK DI LOADING LIST
                $('#alert').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Tidak Cocok');
                $('#alert-body').text('Part "' + cleanLabel + '" tidak ada dalam daftar loading list atau qty sudah terpenuhi.');
            }
            
            $(window).scrollTop(currentScroll);
        }

        // Fungsi untuk login ke API dan mendapatkan token
        function loginApi(callback) {
            $.ajax({
                url: 'https://dea-dev.aiia.co.id/api/v1/auth/login',
                type: 'POST',
                data: {
                    npk: "{{ Auth::user()->npk }}", // NPK pengguna yang sudah login
                    password: '123456' // Password yang digunakan (misalnya hardcoded di sini)
                },
                success: function(response) {
                    // Coba ambil token dari beberapa kemungkinan struktur respons
                    token = response.token
                        || response.access_token
                        || (response.data && (response.data.token || response.data.access_token))
                        || '';

                    console.log('Login berhasil, respons:', response);
                    console.log('Token yang dipakai: ' + token);

                    // Jika setelah parsing token tetap tidak ada, jangan lanjut ke scan
                    if (!token) {
                        $('#part_number_loading').hide();
                        $('#alert').removeClass('alert-success').addClass('alert-danger');
                        $('#alert-header').html('<i class="icon fa fa-warning"></i> Error Login');
                        $('#alert-body').text('Gagal login ke DEA: token tidak ditemukan pada respons.');
                        return;
                    }

                    // Panggil callback setelah login sukses
                    callback();
                },
                error: function(xhr, status, error) {
                    $('#part_number_loading').hide();
                    $('#alert').removeClass('alert-success').addClass('alert-danger');
                    $('#alert-header').html('<i class="icon fa fa-warning"></i> Error Login');
                    $('#alert-body').text('Gagal login: ' + xhr.statusText);
                }
            });
        }

        // Fungsi untuk memindai loading list menggunakan barcode yang diberikan
        // Format pemanggilan API: 11 karakter pertama + ' A' (sesuai spesifikasi DEA)
        // Part Number (detail_no) diisi dengan hasil scan barcode, bukan part number
        function scanLoadingList(barcode, displayBarcode) {
            var rawScanned = (barcode || '').toString();
            // detail_no diisi dari response API (part_number_int), bukan rawScanned
            // Tapi untuk kebutuhan API DEA tetap pakai format: 11 karakter pertama + ' A'
            var loadingListNumber = rawScanned.substr(0, 11) + ' A';
            var loadingListNumberForDB = rawScanned.substr(0, 11); // Store without ' A' for database consistency
            $.ajax({
                type: 'GET',
                url: 'https://dea-dev.aiia.co.id/api/v1/loading-lists/' + encodeURIComponent(loadingListNumber),
                headers: {
                    "Authorization": "Bearer " + token // Gunakan token untuk otorisasi
                },
                dataType: 'json',
                success: function(data) {
                    var ok = (data && data.status === 'success' && data.data) || (data && data.loading_list);
                    var payload = (data && data.data) || (data && data.loading_list) || {};
                    var name = payload.name || payload.number || loadingListNumber;
                    var items = (payload.items || []);

                    if (ok) {
                        console.log('Loading list ditemukan: ', payload);
                        loadingListItems = items.map(function(it) {
                            var total = (it.total_qty != null ? it.total_qty : (it.quantity != null ? it.quantity : 0));
                            var done = (it.actual_kanban_qty != null ? it.actual_kanban_qty : 0);
                            return {
                                part_number_int: it.part_number_int || '',
                                part_number_cust: it.part_number_cust || '',
                                total_qty: total,
                                total_kanban_qty: it.total_kanban_qty != null ? it.total_kanban_qty : total,
                                actual_kanban_qty: done,
                                remaining: Math.max(0, total - done)
                            };
                        });
                        
                        // Save scan results to database
                        // Store loading list number for later use (without ' A' for database consistency)
                        currentLoadingListNumber = loadingListNumberForDB;
                        
                        $.ajax({
                            url: '{{ url("pis/save-scan") }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                loading_list_number: loadingListNumberForDB,
                                pds_number: payload.pds_number || payload.pds || '',
                                cycle: payload.cycle || '',
                                delivery_date: payload.delivery_date || '',
                                shipping_date: payload.shipping_date || '',
                                customer_id: payload.customer_id || null,
                                customer_code: payload.customer_code || payload.customer?.code || null,
                                customer_name: payload.customer_name || payload.customer?.name || null,
                                items: loadingListItems
                            },
                            success: function(response) {
                                console.log('PIS scan saved:', response);
                            },
                            error: function(xhr) {
                                console.error('Failed to save PIS scan:', xhr);
                            }
                        });
                        
                        stage = 2;
                        lastScannedKanban = '';
                        updateStepIndicator();
                        renderLoadingList();
                        updateCounter();
                        // Setelah scan Loading List: hanya tampilkan data list (tanpa gambar)
                        currentPreviewItem = null;
                        clearPreviewImage();
                        // Part Number diisi dengan hasil scan barcode
                        var detailNoInput = $('#detail_no');
                        var currentScroll = $(window).scrollTop();
                        detailNoInput.val(displayBarcode);
                        $(window).scrollTop(currentScroll);
                        $('#alert').removeClass('alert-danger alert-warning').addClass('alert-success');
                        $('#alert-header').html('<i class="icon fa fa-check"></i> Loading List Ditemukan');
                        $('#alert-body').text('Loading list: ' + name + ' — scan kanban, lalu scan label untuk decrement quantity.');
                    } else {
                        loadingListItems = [];
                        stage = 1;
                        lastScannedKanban = '';
                        updateStepIndicator();
                        renderLoadingList();
                        updateCounter();
                        var detailNoInput = $('#detail_no');
                        var currentScroll = $(window).scrollTop();
                        detailNoInput.val('');
                        $(window).scrollTop(currentScroll);
                        currentPreviewItem = null;
                        clearPreviewImage();
                        $('#alert').removeClass('alert-success').addClass('alert-danger');
                        $('#alert-header').html(
                            '<i class="icon fa fa-warning"></i> Loading List Tidak Ditemukan');
                        $('#alert-body').text('Barcode tidak sesuai dengan loading list.');
                    }
                },
                error: function(xhr, status, error) {
                    loadingListItems = [];
                    stage = 1;
                    lastScannedKanban = '';
                    updateStepIndicator();
                    renderLoadingList();
                    updateCounter();
                    var detailNoInput = $('#detail_no');
                    var currentScroll = $(window).scrollTop();
                    detailNoInput.val('');
                    $(window).scrollTop(currentScroll);
                    currentPreviewItem = null;
                    clearPreviewImage();
                    $('#alert').removeClass('alert-success').addClass('alert-danger');
                    $('#alert-header').html('<i class="icon fa fa-warning"></i> Error Pemindaian');
                    $('#alert-body').text('Gagal memindai loading list, silahkan scan loading list terlebih dahulu : ' + xhr.statusText);
                }
            });
        }

        $(document).ready(function() {
            $('#detail_no').prop('readonly', true).blur();
            loadDailyCounter();
            updateStepIndicator();
        });
    </script>
@endsection
