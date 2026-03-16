@extends('layouts.root.minimal')

@section('main')
    <div class="mb-2">
        <button onclick="history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm w-100" style="border-radius:12px;">
                <div class="card-body p-4">
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
                                <div class="card-body" style="height:180px;">
                                    <!-- Tambahkan flex-column agar angka dan tanggal bertumpuk -->
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <div class="display-4 font-weight-bold" id="counter" style="line-height: 1;">0</div>
                                        <br>
                                        <br>
                                        <br>
                                        <!-- Elemen Tanggal Baru -->
                                        <div class="text-muted small mt-1" id="sysdate" style="font-weight: 500;"></div>
                                    </div>
                                </div>
                                 <div class="row mt-2 no-gutters">
                                    <div class="col">
                                        <button type="button" class="btn btn-lg btn-outline-danger" id="pis-btn-delay" style="border-radius: 50px; width: 100%; height: 70px; font-size: 1.5rem;">
                                            <i class="fas fa-pause-circle"></i> Delay
                                        </button>
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
                                    <div id="dock" class="form-group mb-0" style="height: 410px; overflow-y: auto; padding: 8px;">
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

    {{-- Modal konfirmasi JP/Leader (interlock: label tidak sesuai / pindah part sebelum selesai) --}}
    <div class="modal fade" id="modalPisJpConfirmation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-lock"></i> Verifikasi JP / Leader (Interlock)</h5>
                </div>
                <div class="modal-body">
                    <p class="text-center text-danger mb-2"><strong>Proses dihentikan. Hanya JP/Leader yang terdaftar dapat membuka interlock.</strong></p>
                    <p class="small text-muted mb-2">Silahkan Hubungi JP/Leader untuk verifikasi dan melanjutkan. Silahkan Lakukan SCW.</p>
                    <input type="text" class="form-control" id="pis-input-jp-confirm" placeholder="Scan barcode..." autocomplete="off">
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
            var custRaw = (partNumberCust || '').toString().trim();
            var img = document.getElementById('previewImg');
            var placeholder = document.getElementById('previewPlaceholder');

            if (!custRaw) {
                img.src = '';
                img.style.display = 'none';
                if (placeholder) placeholder.style.display = 'block';
                return;
            }

            // Normalisasi nama file:
            // - trim
            // - uppercase
            // - hanya mengizinkan A–Z, 0–9, dan tanda hubung (-)
            var custUpper = custRaw.toUpperCase();
            var custSanitized = custUpper.replace(/[^A-Z0-9-]/g, '');
            var custNoDash = custSanitized.replace(/-/g, '');

            var type = ($('#delivery_type').val() || 'OEM').toUpperCase();
            var dock = ($('#dock_type').val() || 'OTHER').toUpperCase();

            var candidates = [];
            var add = function (fileName) {
                if (fileName) {
                    candidates.push(pisImageBase + '/' + encodeURIComponent(fileName));
                }
            };

            // Pola utama: [PART_CUST]-[TYPE]-[DOCK].JPG
            if (custSanitized) {
                add(custSanitized + '-' + type + '-' + dock + '.JPG');
            }
            if (custNoDash && custNoDash !== custSanitized) {
                add(custNoDash + '-' + type + '-' + dock + '.JPG');
            }

            // Fallback tambahan: hanya [PART_CUST].JPG
            if (custSanitized) {
                add(custSanitized + '.JPG');
            }
            if (custNoDash && custNoDash !== custSanitized) {
                add(custNoDash + '.JPG');
            }

            var idx = 0;

            function tryNext() {
                if (idx >= candidates.length) {
                    // Jika semua kombinasi gagal, pakai gambar default sebagai fallback
                    img.onerror = null;
                    img.src = pisImageDefault;
                    img.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    if (typeof _savedScrollTop === 'number') window.scrollTo(0, _savedScrollTop);
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
        // Countdown UI untuk delay label yang sama (agar waktunya "bergerak")
        var sameLabelCountdownTimer = null;

        function clearSameLabelCountdown() {
            if (sameLabelCountdownTimer) {
                clearInterval(sameLabelCountdownTimer);
                sameLabelCountdownTimer = null;
            }
        }

        function startSameLabelCountdown() {
            clearSameLabelCountdown();

            // Update setiap 1 detik sampai cooldown habis
            sameLabelCountdownTimer = setInterval(function () {
                var now = Date.now();
                var remainingMs = LABEL_SAME_DELAY_MS - (now - lastScannedLabelTime);
                var remainingSec = Math.ceil(remainingMs / 1000);
                if (remainingSec <= 0 || !lastScannedLabelTime) {
                    clearSameLabelCountdown();
                    // Opsional: ubah pesan saat sudah boleh scan lagi
                    if ($('#status-container').hasClass('alert-warning') && $('#alert-header').text().indexOf('Label sama') !== -1) {
                        $('#alert-body').text('Silakan scan lagi.');
                    }
                    return;
                }
                $('#same-label-remaining').text(String(remainingSec));
            }, 1000);
        }
        // Simpan posisi scroll sebelum aksi scan agar tampilan tidak loncat ke bawah setelah scan
        var _savedScrollTop = 0;
        // Interlock JP/Leader: NPK yang diizinkan (sumber: config/pis.php)
        var PIS_JP_LEADER_NPKS = @json(config('pis.jp_leader_npks', ['000453', '002484']));
        var pendingJpAction = null;   // callback setelah konfirmasi JP berhasil
        var pendingScanBarcode = null;
        var pendingScanDisplayBarcode = null;
        var jpConfirmBarcode = '';
        // Part yang sudah "dimulai" (scan kanban/label) dalam sesi loading list saat ini saja.
        // Di-reset setiap kali loading list di-scan; interlock "part belum terpenuhi" hanya memakai ini.
        var partsStartedInCurrentSession = [];

        // Saat interlock dibuka dan user pindah kanban/part, kita perlu memastikan
        // state part sebelumnya tidak menghalangi proses berikutnya.
        function resetActiveKanbanContext() {
            lastScannedKanban = '';
            lastScannedLabel = '';
            lastScannedLabelTime = 0;
            clearSameLabelCountdown();
            currentPreviewItem = null;
            clearPreviewImage();
            $('#detail_no').val('');
        }

        function getPartKeyForSession(it) {
            return (it && (it.part_number_cust || it.part_number_int) || '').toString().trim();
        }

        function setSessionStartedPartsFromKanban(partsInThisKanban) {
            partsStartedInCurrentSession = [];
            (partsInThisKanban || []).forEach(function(p) {
                var k = getPartKeyForSession(p);
                if (k && partsStartedInCurrentSession.indexOf(k) === -1) partsStartedInCurrentSession.push(k);
            });
        }

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
            
            updateCounter();

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
            var str = ('' + s)
                .replace(/[\r\n\t]/g, '')          // buang CR/LF/TAB
                .replace(/[\x00-\x1F\x7F]/g, '')   // buang karakter kontrol lain
                .trim();
            // Beberapa scanner kadang mengirim karakter tambahan di awalan
            // (misalnya simbol/non-alfanumerik) yang membuat nomor bergeser.
            str = str.replace(/^[^A-Za-z0-9]+/, '');
            // Beberapa scanner mengirim prefix 'z'/'Z' di awal (mis. dari Code 128 / config scanner)
            // yang menyebabkan lookup gagal. Buang awalan z/Z yang diikuti angka (bukan part number).
            // Contoh: z12345678901 → 12345678901 (loading list), z8281074820 → 8281074820
            if (/^[zZ]\d/.test(str)) str = str.replace(/^[zZ]+/, '');
            return str;
        }

        // Hapus dua karakter terakhir dari hasil scan (sampah yang ikut terbaca)
        function stripLastTwoChars(s) {
            if (!s) return '';
            var str = ('' + s).trim();
            if (str.length <= 1) return str;
            return str.substring(0, str.length - 1);
        }

        // Ekstrak Part No Customer dari string kanban berdasarkan loading list aktif.
        // Hanya mengembalikan nilai yang benar-benar sama dengan part_number_cust di loading list.
        function extractCustomerPartFromKanban(kanbanStr) {
            if (!kanbanStr) return '';
            var upper = kanbanStr.toUpperCase();

            // Pecah string kanban menjadi token berdasarkan spasi/tab
            var tokens = upper.split(/\s+/).filter(function(t) { return t && t.trim().length > 0; });
            if (!tokens.length) return '';

            var bestMatch = '';
            var bestLen = -1;

            try {
                (loadingListItems || []).forEach(function(item) {
                    var cust = (item.part_number_cust || '').toString().toUpperCase().trim();
                    if (!cust) return;
                    for (var i = 0; i < tokens.length; i++) {
                        // Token harus sama persis dengan part_number_cust (ignore case)
                        if (tokens[i] === cust && cust.length > bestLen) {
                            bestLen = cust.length;
                            bestMatch = cust;
                        }
                    }
                });
            } catch (e) {
                // Abaikan error
            }

            // Jika tidak ada yang match persis dengan part_number_cust di loading list, anggap tidak valid
            return bestMatch || '';
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
            var scanned = 0;
            var target = 0;
            loadingListItems.forEach(function(item) {
                scanned += item.actual_kanban_qty || 0;
                target += item.total_qty || 0;
            });
            if (target === 0) {
                $('#counter').text('0/0');
            } else {
                $('#counter').text(scanned + '/' + target);
            }
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
            if ($('#modalPisJpConfirmation').hasClass('show')) return;
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                var rawScanned = cleanBarcode(barcode || '');
                var displayBarcode = stripLastTwoChars(rawScanned);
                var processBarcode = rawScanned;
                
                // Simpan posisi scroll saat ini TEPAT sebelum proses dimulai
                _savedScrollTop = $(window).scrollTop();

                // Tampilkan loading
                $('#part_number_loading').show();

                function doStageAction() {
                    // Tampilkan hanya hasil scan loading list di input Part Number.
                    // Untuk scan kanban (stage 2) dan label (stage 3), nilai input akan
                    // di-set spesifik oleh fungsi proses masing-masing (bukan seluruh string scan).
                    if (stage === 1) {
                        $('#detail_no').val(displayBarcode);
                    }
                    if (stage === 1) {
                        // Interlock: jika ada loading list yang belum selesai, tanya dulu (Tunda / Lanjutkan + konfirmasi JP)
                        if (loadingListItems.length > 0 && !isLoadingListComplete()) {
                            $('#part_number_loading').hide();
                            Swal.fire({
                                title: 'Loading list belum selesai',
                                html: 'Loading list <strong>' + (currentLoadingListNumber || '-') + '</strong> masih ada item yang belum terpenuhi.<br>Lanjutkan ke loading list baru?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Lanjutkan',
                                cancelButtonText: 'Tunda',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d'
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    // Log interlock: mulai loading list baru saat sebelumnya belum selesai
                                    $.ajax({
                                        url: '{{ url("error/store") }}',
                                        type: 'GET',
                                        data: {
                                            source: 'pis',
                                            message: 'Interlock: Loading list baru sebelum selesai',
                                            expected: currentLoadingListNumber || '',
                                            scanned: displayBarcode
                                        }
                                    });

                                    pendingScanBarcode = processBarcode;
                                    pendingScanDisplayBarcode = displayBarcode;
                                    pendingJpAction = function() {
                                        var b = pendingScanBarcode;
                                        var d = pendingScanDisplayBarcode;
                                        pendingJpAction = null;
                                        pendingScanBarcode = null;
                                        pendingScanDisplayBarcode = null;
                                        $('#part_number_loading').show();
                                        scanLoadingList(b, d);
                                    };
                                    $('#pis-input-jp-confirm').val('');
                                    $('#modalPisJpConfirmation').modal('show');
                                    setTimeout(function() { $('#pis-input-jp-confirm').focus(); }, 300);
                                }
                            });
                            barcode = '';
                            return;
                        }
                        scanLoadingList(processBarcode, displayBarcode);
                    } else if (stage === 2 && loadingListItems.length > 0) {
                        processKanbanScan(processBarcode);
                        // Matikan loading karena kanban scan sifatnya lokal (cepat)
                        $('#part_number_loading').hide();
                        // Restore scroll untuk Kanban
                        $(window).scrollTop(_savedScrollTop);
                    } else if (stage === 3 && loadingListItems.length > 0) {
                        processPartScan(processBarcode, displayBarcode);
                        // Matikan loading
                        $('#part_number_loading').hide();
                        // Restore scroll ditangani di dalam processPartScan (jangan duplikasi di sini)
                    } else {
                        scanLoadingList(processBarcode, displayBarcode);
                    }
                    
                    // HAPUS setTimeout scroll di sini yang sebelumnya ada
                    // Biarkan masing-masing fungsi yang mengatur kapan scroll dikembalikan
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

        function resetPisState() {
            stage = 1;
            loadingListItems = [];
            partsStartedInCurrentSession = [];
            currentLoadingListNumber = '';
            lastScannedKanban = '';
            currentPreviewItem = null;
            lastScannedLabel = '';
            lastScannedLabelTime = 0;
            barcode = '';
            pendingJpAction = null;
            pendingScanBarcode = null;
            pendingScanDisplayBarcode = null;
            jpConfirmBarcode = '';
            $('#modalPisJpConfirmation').modal('hide');
            $('#part_number_loading').hide();
            updateStepIndicator();
            renderLoadingList();
            updateCounter();
            $('#detail_no').val('');
            clearPreviewImage();
            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
            $('#alert-header').html('<i class="fas fa-check-circle"></i> Ready');
            $('#alert-body').text('Silahkan Scan Loading List untuk memulai');
        }

        function showJpConfirmationThen(callback) {
            pendingJpAction = callback;
            jpConfirmBarcode = '';
            $('#pis-input-jp-confirm').val('');
            $('#modalPisJpConfirmation').modal('show');
            setTimeout(function() { $('#pis-input-jp-confirm').focus(); }, 300);
        }

        // Stage 2: Scan kanban — simpan data untuk validasi label (label harus terkandung di data kanban)
        function processKanbanScan(barcode) {
            // Simpan string asli dari scanner (biasanya sangat panjang)
            var fullKanbanRaw = cleanBarcode(barcode || '');
            // Hanya ambil bagian Part No Customer yang relevan dengan loading list aktif
            var extractedPart = extractCustomerPartFromKanban(fullKanbanRaw);
            // raw = nilai yang akan dipakai untuk semua proses selanjutnya (hanya part no customer)
            var raw = extractedPart || fullKanbanRaw;

            if (!raw) {
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Kanban Kosong');
                $('#alert-body').text('Data kanban tidak valid.');
                $(window).scrollTop(_savedScrollTop);
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
                $('#status-container').removeClass('alert-success alert-primary').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-ban"></i> Kanban Sudah Terpenuhi');
                $('#alert-body').text('Part "' + matchedPartName + '" sudah mencapai target (Sisa: 0). Silahkan scan Kanban untuk part lain yang belum selesai.');
                
                // JANGAN update input detail_no
                // JANGAN pindah stage (tetap di stage 2)
                $(window).scrollTop(_savedScrollTop);
                return; // Berhenti di sini, tidak melanjutkan proses
            }

            // INTERLOCK (2): Dalam satu loading list masih ada part yang belum selesai, user scan kanban part lain — wajib verifikasi JP/Leader
            var partsInThisKanban = [];
            for (var k = 0; k < loadingListItems.length; k++) {
                var it = loadingListItems[k];
                var pIntK = (it.part_number_int || '').toString().toUpperCase();
                var pCustK = (it.part_number_cust || '').toString().toUpperCase();
                if ((pIntK && kanbanContent.indexOf(pIntK) !== -1) || (pCustK && kanbanContent.indexOf(pCustK) !== -1)) {
                    partsInThisKanban.push(it);
                }
            }
            // Cari part lain yang SUDAH MULAI di-scan dalam SESI LOADING LIST SAAT INI saja (bukan dari loading list sebelumnya).
            // Part dianggap "dimulai" hanya jika sudah ada scan kanban/label untuk part itu SETELAH loading list terakhir di-scan.
            // Ini mencegah interlock muncul ketika user scan loading list dulu lalu scan kanban (konteks baru).
            function getPartKey(it) { return (it.part_number_cust || it.part_number_int || '').toString().trim(); }
            function isPartStartedInSession(item) {
                var key = getPartKey(item);
                return key && partsStartedInCurrentSession.indexOf(key) !== -1;
            }
            var otherPartUnfinished = [];
            for (var u = 0; u < loadingListItems.length; u++) {
                var itemU = loadingListItems[u];
                var alreadyStarted = isPartStartedInSession(itemU);
                var stillRemaining = (itemU.remaining || 0) > 0;
                if (!alreadyStarted || !stillRemaining) continue;

                var isInKanban = false;
                for (var v = 0; v < partsInThisKanban.length; v++) {
                    if (partsInThisKanban[v] === itemU) { isInKanban = true; break; }
                }
                if (!isInKanban) otherPartUnfinished.push(itemU);
            }
            if (otherPartUnfinished.length > 0) {
                var partNames = otherPartUnfinished.map(function(i) { return i.part_number_cust || i.part_number_int; }).join(', ');
                $('#status-container').removeClass('alert-success alert-primary').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-lock"></i> Interlock: Masih Ada Part Belum Selesai');
                $('#alert-body').text('Part lain belum selesai packing: ' + partNames + '. Silahkan hubungi JP/Leader untuk pindah part.');

                // Log interlock: pindah part lain saat masih ada part yang belum selesai
                $.ajax({
                    url: '{{ url("error/store") }}',
                    type: 'GET',
                    data: {
                        source: 'pis',
                        message: 'Interlock: Pindah part sebelum selesai',
                        expected: partNames,
                        // simpan hanya Part No Customer hasil ekstraksi, bukan seluruh isi string kanban
                        scanned: extractedPart || raw
                    }
                });

                $(window).scrollTop(_savedScrollTop);
                var kanbanRawToApply = raw;
                showJpConfirmationThen(function() {
                    // Interlock dibuka → anggap kanban ini sebagai konteks aktif BARU.
                    // Jangan biarkan state part sebelumnya memaksa user menyelesaikan part lama.
                    resetActiveKanbanContext();
                    lastScannedKanban = kanbanRawToApply;
                    setSessionStartedPartsFromKanban(partsInThisKanban);
                    stage = 3;
                    updateStepIndicator();
                    $('#status-container').removeClass('alert-danger').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK (setelah verifikasi)');
                    $('#alert-body').text('Kanban diterima. Silahkan scan LABEL PART.');
                    $(window).scrollTop(_savedScrollTop);
                });
                return;
            }
            // --- END LOGIKA VALIDASI ---

            // JIKA LOLOS VALIDASI (Sisa Qty masih > 0): tandai part dalam kanban ini sebagai "dimulai" dalam sesi ini
            partsInThisKanban.forEach(function(p) {
                var k = getPartKey(p);
                if (k && partsStartedInCurrentSession.indexOf(k) === -1) partsStartedInCurrentSession.push(k);
            });
            // Simpan ke state interlock hanya bagian Part No Customer yang sudah diproses
            lastScannedKanban = raw;
            stage = 3;
            updateStepIndicator();

            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
            $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK');
            $('#alert-body').text('Kanban diterima. Silahkan scan LABEL PART.');
            
            $(window).scrollTop(_savedScrollTop);
        }

        function processPartScan(barcode, displayBarcode) {
            var raw = cleanBarcode(barcode || '');
            // Bersihkan countdown lama bila ada (mencegah interval numpuk saat proses scan berjalan)
            clearSameLabelCountdown();

            // --- LOGIKA PEMBERSIHAN BARCODE ---
            var cleanLabel = raw.split(/\s{2,}/)[0].trim();
            var matched = null;

            // Helper: ambil part number customer (kombinasi angka + huruf, mis. 8281074820WBY) dari string kanban
            function extractPartFromKanban(kanbanStr) {
                if (!kanbanStr) return '';
                var upper = kanbanStr.toUpperCase();
                var matches = upper.match(/[A-Z0-9]{8,16}/g);
                if (!matches || !matches.length) return '';
                // Filter hanya token yang mengandung huruf (bukan murni angka, supaya tidak ambil tanggal / qty)
                var withLetters = matches.filter(function (t) {
                    return /[A-Z]/.test(t) && /\d/.test(t);
                });
                if (withLetters.length) {
                    return withLetters[withLetters.length - 1]; // ambil kandidat terakhir yang ada hurufnya
                }
                // Fallback: pakai token terakhir apa adanya
                return matches[matches.length - 1];
            }

            // 1. Validasi: Apakah part ada di dalam Kanban?
            var kanbanUpper = (lastScannedKanban || '').toUpperCase();
            var rawUpper = raw.toUpperCase();
            var cleanUpper = cleanLabel.toUpperCase();

            var existsInKanban = (kanbanUpper.indexOf(rawUpper) !== -1 || kanbanUpper.indexOf(cleanUpper) !== -1);

            // INTERLOCK (1): Scan kanban Part A lalu scan label Part B yang tidak sesuai — wajib verifikasi JP/Leader
            if (!lastScannedKanban || !existsInKanban) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-lock"></i> Interlock: Label Tidak Sesuai Kanban');
                $('#alert-body').text('Label "' + (cleanLabel || raw) + '" tidak sesuai kanban. Proses dihentikan. Hubungi JP/Leader untuk verifikasi.');

                // Log interlock: label tidak sesuai kanban
                $.ajax({
                    url: '{{ url("error/store") }}',
                    type: 'GET',
                    data: {
                        source: 'pis',
                        message: 'Interlock: Label tidak sesuai Kanban',
                        // Simpan hanya part number customer dari kanban, bukan seluruh string panjang
                        expected: extractPartFromKanban(lastScannedKanban) || '',
                        scanned: cleanLabel || raw
                    }
                });

                $(window).scrollTop(_savedScrollTop);
                $('#part_number_loading').hide();
                showJpConfirmationThen(function() {
                    stage = 2;
                    // Interlock dibuka → reset konteks agar scan kanban berikutnya dianggap kanban aktif baru,
                    // tanpa dipengaruhi state part/kanban sebelumnya.
                    resetActiveKanbanContext();
                    partsStartedInCurrentSession = [];
                    updateStepIndicator();
                    $('#status-container').removeClass('alert-danger').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Interlock dibuka');
                    $('#alert-body').text('Verifikasi berhasil. Silakan scan kanban kembali.');
                });
                return;
            }

            // 2. Delay untuk label yang sama (Mencegah double scan tidak sengaja)
            var now = Date.now();
            if (raw && raw === lastScannedLabel && (now - lastScannedLabelTime) < LABEL_SAME_DELAY_MS) {
                var sisaWaktu = Math.ceil((LABEL_SAME_DELAY_MS - (now - lastScannedLabelTime)) / 1000);
                $('#status-container').removeClass('alert-success alert-danger').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-clock"></i> Label sama');
                $('#alert-body').html('Label ini baru saja di-scan. Tunggu <b id="same-label-remaining">' + sisaWaktu + '</b> detik lagi.');
                startSameLabelCountdown();
                $(window).scrollTop(_savedScrollTop);
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

                // Tandai part ini sebagai "dimulai" dalam sesi loading list saat ini (untuk evaluasi interlock)
                var partKey = (matched.part_number_cust || matched.part_number_int || '').toString().trim();
                if (partKey && partsStartedInCurrentSession.indexOf(partKey) === -1) partsStartedInCurrentSession.push(partKey);

                // Decrement Quantity
                matched.remaining = (matched.remaining != null ? matched.remaining : matched.total_qty || 0) - 1;
                if (matched.remaining < 0) matched.remaining = 0;
                matched.actual_kanban_qty = (matched.actual_kanban_qty || 0) + 1;

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
                    $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Part OK');
                    $('#alert-body').text((matched.part_number_cust || matched.part_number_int) + ' Berhasil di-scan. Sisa: ' + matched.remaining + ' box.');
                } else {
                    // SUDAH HABIS: Kembali ke Stage 2 (Harus scan kanban baru untuk part lain)
                    stage = 2;
                    lastScannedKanban = ''; // Reset kanban agar user wajib scan kanban baru
                    $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-double"></i> Item Selesai');
                    $('#alert-body').text('Quantity untuk part ini sudah terpenuhi. Silahkan scan KANBAN selanjutnya.');
                }

                updateStepIndicator();

                // Cek jika seluruh Loading List sudah selesai semua
                if (isLoadingListComplete()) {
                    Swal.fire({
                        title: 'Loading List Complete!',
                        text: 'Semua item dalam daftar telah terpenuhi.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        // Setelah user acknowledge, reset state agar scan berikutnya tidak dianggap
                        // "scan loading list yang sama" / balik ke awal secara tidak sengaja.
                        resetPisState();
                    });
                    return;
                }
            } else {
                // JIKA TIDAK ADA YANG COCOK DI LOADING LIST
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Tidak Cocok');
                $('#alert-body').text('Part "' + cleanLabel + '" tidak ada dalam daftar loading list atau qty sudah terpenuhi.');
                // Restore scroll position untuk kasus ini
                $(window).scrollTop(_savedScrollTop);
            }
            // Scroll position sudah dipulihkan di dalam masing-masing kondisi di atas
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
                        $('#status-container').removeClass('alert-success').addClass('alert-danger');
                        $('#alert-header').html('<i class="icon fa fa-warning"></i> Error Login');
                        $('#alert-body').text('Gagal login ke DEA: token tidak ditemukan pada respons.');
                        return;
                    }

                    // Panggil callback setelah login sukses
                    callback();
                },
                error: function(xhr, status, error) {
                    $('#part_number_loading').hide();
                    $('#status-container').removeClass('alert-success').addClass('alert-danger');
                    $('#alert-header').html('<i class="icon fa fa-warning"></i> Error Login');
                    $('#alert-body').text('Gagal login: ' + xhr.statusText);
                }
            });
        }

        // Fungsi untuk memindai loading list menggunakan barcode yang diberikan.
        // 1) Cek dulu ke backend: jika data sudah ada di pis_scan → pakai data existing (no API, no overwrite).
        // 2) Jika belum ada → panggil API DEA, simpan ke DB, lalu pakai response.
        // Part Number (detail_no) diisi dengan hasil scan barcode.
        function scanLoadingList(barcode, displayBarcode) {
            var rawScanned = (barcode || '').toString().trim();
            var loadingListNumberForDB = rawScanned.replace(/[\x00-\x1F\x7F]/g, '').trim().substr(0, 11);
            var loadingListNumberForApi = loadingListNumberForDB + ' A';

            function applyLoadingListState(name, items, fromExisting) {
                // Scan loading list = konteks baru: reset part yang "dimulai" untuk evaluasi interlock
                partsStartedInCurrentSession = [];
                loadingListItems = items.map(function(it) {
                    var total = (it.total_qty != null ? it.total_qty : (it.total_kanban_qty != null ? it.total_kanban_qty : 0));
                    var remaining = (it.remaining != null ? it.remaining : Math.max(0, total - (it.actual_kanban_qty || 0)));
                    return {
                        part_number_int: it.part_number_int || '',
                        part_number_cust: it.part_number_cust || '',
                        total_qty: total,
                        total_kanban_qty: it.total_kanban_qty != null ? it.total_kanban_qty : total,
                        actual_kanban_qty: it.actual_kanban_qty != null ? it.actual_kanban_qty : (total - remaining),
                        remaining: remaining
                    };
                });
                currentLoadingListNumber = loadingListNumberForDB;
                stage = 2;
                lastScannedKanban = '';
                updateStepIndicator();
                renderLoadingList();
                updateCounter();
                currentPreviewItem = null;
                clearPreviewImage();
                $('#detail_no').val(displayBarcode);
                $(window).scrollTop(_savedScrollTop);
                $('#part_number_loading').hide();
                $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                $('#alert-header').html('<i class="icon fa fa-check"></i> Loading List Ditemukan');
                $('#alert-body').text(
                    fromExisting
                        ? 'Loading list (data existing): ' + name + ' — lanjutkan scan kanban & label.'
                        : 'Loading list: ' + name + ' — scan kanban, lalu scan label untuk decrement quantity.'
                );
            }

            function failLoadingList(message) {
                loadingListItems = [];
                partsStartedInCurrentSession = [];
                stage = 1;
                lastScannedKanban = '';
                updateStepIndicator();
                renderLoadingList();
                updateCounter();
                $('#detail_no').val('');
                $(window).scrollTop(_savedScrollTop);
                currentPreviewItem = null;
                clearPreviewImage();
                $('#part_number_loading').hide();
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="icon fa fa-warning"></i> ' + (message ? 'Loading List Tidak Ditemukan' : 'Error Pemindaian'));
                $('#alert-body').text(message || 'Gagal memindai loading list.');
            }

            // Step 1: Cek apakah data sudah ada di database (jangan panggil API atau overwrite)
            $.ajax({
                url: '{{ url("pis/get-loading-list-data") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    barcode: rawScanned
                },
                dataType: 'json',
                success: function(res) {
                    if (res.exists && res.items && res.items.length >= 0) {
                        console.log('Loading list dari database (existing):', res);
                        applyLoadingListState(res.name || res.loading_list_number || loadingListNumberForDB, res.items, true);
                        return;
                    }

                    // Step 2: Data belum ada → panggil API DEA (hanya pertama kali)
                    $.ajax({
                        type: 'GET',
                        url: 'https://dea-dev.aiia.co.id/api/v1/loading-lists/' + encodeURIComponent(loadingListNumberForApi),
                        headers: { "Authorization": "Bearer " + token },
                        dataType: 'json',
                        success: function(data) {
                            var ok = (data && data.status === 'success' && data.data) || (data && data.loading_list);
                            var payload = (data && data.data) || (data && data.loading_list) || {};
                            var name = payload.name || payload.number || loadingListNumberForApi;
                            var items = (payload.items || []);

                            if (ok) {
                                console.log('Loading list dari API (pertama kali):', payload);
                                var mapped = items.map(function(it) {
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
                                currentLoadingListNumber = loadingListNumberForDB;
                                loadingListItems = mapped;

                                $.ajax({
                                    url: '{{ url("pis/save-scan") }}',
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        delivery_type: ($('#delivery_type').val() || 'OEM'),
                                        dock_type: ($('#dock_type').val() || 'OTHER'),
                                        loading_list_number: loadingListNumberForDB,
                                        pds_number: payload.pds_number || payload.pds || '',
                                        cycle: payload.cycle || '',
                                        delivery_date: payload.delivery_date || '',
                                        shipping_date: payload.shipping_date || '',
                                        customer_id: payload.customer_id || null,
                                        customer_code: payload.customer_code || (payload.customer && payload.customer.code) || null,
                                        customer_name: payload.customer_name || (payload.customer && payload.customer.name) || null,
                                        items: loadingListItems
                                    },
                                    success: function(response) {
                                        console.log('PIS scan saved (first time):', response);
                                    },
                                    error: function(xhr) {
                                        console.error('Failed to save PIS scan:', xhr);
                                    }
                                });

                                applyLoadingListState(name, loadingListItems, false);
                            } else {
                                failLoadingList('Barcode tidak sesuai dengan loading list.');
                            }
                        },
                        error: function(xhr) {
                            failLoadingList('Gagal memindai loading list: ' + (xhr.statusText || 'network error'));
                        }
                    });
                },
                error: function(xhr) {
                    failLoadingList('Gagal cek data loading list: ' + (xhr.statusText || 'network error'));
                }
            });
        }

        $('#pis-btn-delay').on('click', function() {
            resetPisState();
        });

        $('#modalPisJpConfirmation').on('shown.bs.modal', function() {
            $('#pis-input-jp-confirm').focus();
        });

        // Saat interlock, pastikan cursor tetap di field scan interlock (refocus jika kehilangan fokus)
        $('#pis-input-jp-confirm').on('focusout', function() {
            if ($('#modalPisJpConfirmation').hasClass('show')) {
                var el = this;
                setTimeout(function() { $(el).focus(); }, 50);
            }
        });

        $('#pis-input-jp-confirm').on('keypress', function(e) {
            var code = e.keyCode ? e.keyCode : e.which;
            if (code === 13) {
                e.preventDefault();
                var npk = $(this).val().trim().replace(/\D/g, '');
                if (npk.length >= 6) {
                    npk = npk.slice(0, 6);
                }
                if (npk.length === 6 && PIS_JP_LEADER_NPKS.indexOf(npk) !== -1) {
                    $('#modalPisJpConfirmation').modal('hide');
                    if (typeof pendingJpAction === 'function') {
                        pendingJpAction();
                    }
                    pendingJpAction = null;
                } else {
                    var $input = $(this);
                    $input.val('');
                    Swal.fire({
                        title: 'NPK tidak memiliki akses',
                        text: npk.length === 6 ? 'NPK ' + npk + ' bukan JP/Leader.' : 'Scan barcode NPK (6 digit).',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        if ($('#modalPisJpConfirmation').hasClass('show')) {
                            $input.focus();
                        }
                    });
                }
            }
        });

        $(document).ready(function() {
            $('#detail_no').prop('readonly', true).blur();
            loadDailyCounter();
            updateStepIndicator();
            
            // Nonaktifkan niceScroll yang mungkin menyebabkan auto-scroll
            if($.fn.niceScroll) {
                $('html, body').getNiceScroll().remove();
            }
            
            // Prevent auto-scroll to input fields
            $('input, select, textarea').off('focus').on('focus', function(e) {
                // Tidak melakukan scroll otomatis saat focus
                e.preventDefault();
            });
        });
    </script>
@endsection
