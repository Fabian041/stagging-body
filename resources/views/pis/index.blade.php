@extends('layouts.root.minimal')

@section('main')

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm w-100 pis-compact" style="border-radius:12px;">
                <div class="card-body p-2">
                    <div class="row">

                        <div class="col-md-2">
                            <div class="card border" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>Part Number</strong>
                                </div>
                                <div class="card-body py-1 px-2">
                                    <div id="part_number_loading" class="small text-muted mb-1" style="min-height: 1.25rem; display: none;">
                                        <i class="fas fa-spinner fa-spin"></i> Scanning...
                                    </div>
                                    <div class="form-group mb-0">
                                        <input id="detail_no" class="form-control" name="detail_no" required tabindex="-1" placeholder="Scan di sini">
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>Counter</strong>
                                </div>
                                <div class="card-body p-0 d-flex flex-column align-items-center" style="height:60px;">
                                    <div class="display-4 font-weight-bold" id="counter" style="font-size: 40px; line-height:1;">
                                        0
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>Loading List</strong>
                                </div>
                                <div class="card-body p-2 pis-loading-list-scroll">
                                    <table class="table table-sm table-bordered mb-0 small pis-loading-list-table" style="font-size: 0.8rem;">
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
                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>Action Delay</strong>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row no-gutters align-items-stretch">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-lg btn-outline-danger" id="pis-btn-delay" style="border-radius: 40px; width: 100%; min-height: 50px; font-size: 1.15rem;">
                                                <i class="fas fa-pause-circle"></i> Delay
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>View</strong>
                                </div>
                                <div class="card-body p-2">
                                    <button type="button" class="btn btn-lg btn-outline-primary" id="pis-btn-fullscreen" tabindex="-1" style="border-radius: 40px; width: 100%; min-height: 50px; font-size: 1.05rem;">
                                        <i class="fas fa-expand"></i> Fullscreen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div id="pis-step-flow" class="alert alert-primary mb-1 shadow-sm py-1" style="border-radius:6px;">
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
                                <div class="card-body p-2">
                                    <!-- PINDAHAN ALERT BODY ADA DI SINI -->
                                    <div id="status-container" class="alert alert-success text-center mb-2 py-2" style="font-size: 0.96rem;">
                                        <h5 class="alert-heading mb-1" id="alert-header" style="font-size:1.1rem"><i class="fas fa-check-circle"></i> Ready</h5>
                                        <p class="mb-0 font-weight-bold" id="alert-body" style="font-size:1rem">Silahkan Scan Loading List untuk memulai</p>
                                    </div>

                                    <div id="imageDiv" class="pis-preview-area text-center bg-white border" style="border-radius: 8px;">
                                        <img id="previewImg" src="" alt="Part image" style="background: #f9f9f9; border-radius: 8px; display: none;" />
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
                                <div class="p-2" style="padding-left: 20px;"><strong>Type</strong></div>
                                <div class="card-body">
                                    <div id="delivery" class="form-group mb-0">
                                        <button id="btnOEM" value="OEM" type="button" class="btn btn-block btn-primary" onclick="func_change_delivery(this);">OEM</button>
                                        <button id="btnDANDORY" value="DANDORY" type="button" class="btn btn-block btn-default" onclick="func_change_delivery(this);">DANDORY</button>
                                        <input id="delivery_type" value="OEM" type="hidden">
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;"><strong>Dock</strong></div>
                                <div class="card-body p-0">
                                    <div id="dock" class="form-group mb-0" style="height: 350px; overflow-y: auto; padding: 8px;">
                                        <!-- Dock dengan data-dandory="1" = tampil jika Type DANDORY -->
                                        <button value="TMMIN SPD" type="button" data-dandory="1" class="btn btn-block btn-primary pis-dock-btn" onclick="func_change_dock(this);">TMMIN SPD</button>
                                        <button value="TMMIN SPD-ADM" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TMMIN SPD-ADM</button>
                                        <button value="TMMIN-PBOD" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TMMIN-PBOD</button>
                                        <button value="43" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">43</button>
                                        <button value="53" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">53</button>
                                        <button value="1L" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">1L</button>
                                        <button value="1N" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">1N</button>
                                        <button value="HINO-SPD" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">HINO-SPD</button>
                                        <button value="SIM-SPD" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">SIM-SPD</button>
                                        <button value="TAM-SPD" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TAM-SPD</button>
                                        <button value="MMKI" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">MMKI</button>
                                        <button value="MMKI-SPD" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">MMKI-SPD</button>
                                        <button value="6I" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">6I</button>
                                        <button value="TAM-TAM" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TAM-TAM</button>
                                        <button value="TAM-ADM" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TAM-ADM</button>
                                        <button value="TAM-HINO" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TAM-HINO</button>
                                        <button value="ADM-AS" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">ADM-AS</button>
                                        <button value="ADM-KP" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">ADM-KP</button>
                                        <button value="YHA" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">YHA</button>
                                        <button value="ADM" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">ADM</button>
                                        <button value="TTI" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TTI</button>
                                        <input id="dock_type" value="OTHER" type="hidden">
                                    </div>
                                </div>
                            </div>
                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>Action Confirm Packing</strong>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row no-gutters align-items-stretch">
                                        <div class="col-12 mb-1">
                                            <button type="button" class="btn btn-lg btn-success" id="pis-btn-confirm-packing" disabled style="border-radius: 40px; width: 100%; min-height: 54px; font-size: 1.07rem;">
                                                <i class="fas fa-check-double"></i> Confirm Packing
                                        </button>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <div class="text-center small text-muted">
                                                Menunggu konfirmasi: <strong id="pis-pending-count">0</strong> label
                                            </div>
                                        </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <style>
        /* PIS: compact spacing label-field (scoped) */
        .pis-compact .form-group {
            margin-bottom: 0.2rem;
        }

        .pis-compact label {
            margin-bottom: 0.1rem;
        }

        .pis-compact .card-header {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
        }

        /* Only affects card-body without Bootstrap padding utilities (p-*) */
        .pis-compact .card-body:not(.p-0) {
            padding: 0.35rem 0.55rem !important;
        }

        .pis-compact .form-control {
            padding: 0.25rem 0.5rem !important;
            min-height: 32px;
            height: auto;
            font-size: 0.95rem;
        }

        /* Rapatkan antar panel yang memakai .card.border.mt-* */
        .pis-compact .card.border.mt-1 {
            margin-top: 0.1rem !important;
        }

        .pis-compact .card.border.mt-2 {
            margin-top: 0.1rem !important;
        }

        /* Lock screen interlock: overlay di bawah modal, modal selalu paling atas */
        body.pis-interlock-open #modalPisJpConfirmation {
            z-index: 9999 !important;
            position: fixed;
            inset: 0;
        }

        body.pis-interlock-open .modal-backdrop.show {
            z-index: 9998 !important;
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.55) !important;
            opacity: 1 !important;
        }

        body.pis-interlock-open #modalPisJpConfirmation .modal-dialog,
        body.pis-interlock-open #modalPisJpConfirmation .modal-content {
            position: relative;
            z-index: 10000 !important;
        }

        /* Pratinjau: tinggi adaptif mengikuti monitor (terutama desktop/16:9). */
        #imageDiv.pis-preview-area {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 600px;
            height: clamp(600px, 90vh, 1450px);
            max-height: 1450px;
            overflow: hidden;
            box-sizing: border-box;
            padding: 2px;
        }

        /* Di monitor desktop, naikkan tinggi supaya area preview tidak tampak kecil. */
        @media (min-width: 1200px) {
            #imageDiv.pis-preview-area {
                height: clamp(760px, 93vh, 1650px);
                max-height: 1650px;
            }
        }

        /* Di monitor tinggi (mis. 1080p ke atas), gunakan porsi viewport lebih besar. */
        @media (min-height: 1000px) {
            #imageDiv.pis-preview-area {
                height: clamp(820px, 95vh, 1800px);
                max-height: 1800px;
            }
        }

        #previewImg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            box-sizing: border-box;
        }

        #previewPlaceholder {
            position: relative;
            z-index: 0;
            pointer-events: none;
        }

        /* Loading List: batasi tinggi, aktifkan scroll jika item banyak */
        .pis-loading-list-scroll {
            max-height: 336px; /* ~7 baris data + header */
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        @media (max-width: 768px) {
            .pis-loading-list-scroll {
                max-height: 170px; /* ~3 baris data + header (mobile) */
            }
        }

        .pis-loading-list-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff;
        }
    </style>

    {{-- Modal konfirmasi JP/Leader (interlock: label tidak sesuai / pindah part sebelum selesai) --}}
    <div class="modal fade" id="modalPisJpConfirmation" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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

    <audio id="pis-ok-sound">
        <source src="{{ asset('assets/sounds/ok.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="pis-not-match-sound">
        <source src="{{ asset('assets/sounds/notMatch.mp3') }}" type="audio/mpeg">
    </audio>
@endsection

@section('custom-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        function playPisSound(id) {
            var sound = document.getElementById(id);
            if (!sound) return;
            try {
                sound.currentTime = 0;
                var playPromise = sound.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () { });
                }
            } catch (e) { }
        }

        function pisOkSound() {
            playPisSound('pis-ok-sound');
        }

        function pisErrorSound() {
            playPisSound('pis-not-match-sound');
        }

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

        // Tampilkan dock sesuai Type: DANDORY hanya dock ber-data-dandory="1"
        function pis_refresh_dock_for_delivery_type() {
            var isDandory = ($('#delivery_type').val() || '').toUpperCase() === 'DANDORY';
            var $btns = $('#dock').find('button.pis-dock-btn');
            $btns.each(function () {
                var $b = $(this);
                var show = !isDandory || $b.attr('data-dandory') === '1';
                $b.toggle(show);
            });
            if (!isDandory) {
                return;
            }
            var currentVal = $('#dock_type').val();
            var $visible = $btns.filter(':visible');
            var match = $visible.filter(function () {
                return $(this).attr('value') === currentVal;
            });
            if (!match.length && $visible.length) {
                func_change_dock($visible[0]);
            }
        }

        // Fungsi untuk menangani perubahan pada delivery type
        function func_change_delivery(obj) {
            $('#delivery').find('button').removeClass('btn-primary');
            $('#delivery').find('button').addClass('btn-default');
            $(obj).addClass('btn-primary');
            $('#delivery_type').val(obj.value);
            pis_refresh_dock_for_delivery_type();
            // Refresh gambar hanya jika sudah ada part yang tervalidasi (scan label = Part Number (Cust))
            if (currentPreviewItem) {
                setPreviewImage(currentPreviewItem.part_number_int || '', currentPreviewItem.part_number_cust || '');
            }
        }

        // Fungsi untuk menangani perubahan pada dock type
        function func_change_dock(obj) {
            $('#dock').find('button.pis-dock-btn').removeClass('btn-primary');
            $('#dock').find('button.pis-dock-btn').addClass('btn-default');
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
        var loadingListItems = []; // Flattened items (semua LL); setiap item punya loading_list_number
        var pisLoadingListGroups = []; // { loadingListNumber, displayName, pds_number, items: [] } — terpisah per LL di UI
        var pisSessionPdsNumber = ''; // PDS sesi: LL tambahan wajib sama
        var currentLoadingListNumber = ''; // LL terakhir di-scan (log/API); match part memakai semua LL sesi secara setara
        var lastScannedKanban = ''; // Data kanban terakhir untuk validasi label (label harus ada di kanban)
        // Counter harian untuk scan label part (bukan scan loading list)
        var loadingListScanCount = 0;
        // Item yang terakhir tervalidasi untuk preview (hanya jika scan label = part_number_cust)
        var currentPreviewItem = null;
        // Cooldown scan label: mulai setiap label berhasil di-scan
        var lastScannedLabel = '';
        var lastScannedLabelTime = 0;
        var lastLabelCooldownStartTime = 0;
        var LABEL_SAME_DELAY_MS = 15 * 1000; // 15 detik
        // Countdown UI untuk delay label yang sama (agar waktunya "bergerak")
        var sameLabelCountdownTimer = null;

        function clearSameLabelCountdown() {
            if (sameLabelCountdownTimer) {
                clearInterval(sameLabelCountdownTimer);
                sameLabelCountdownTimer = null;
            }
        }

        function hidePisSameLabelCooldownUi() {
            // Elemen countdown di Action Confirm Packing sudah dihapus
            // Biarkan tetap aman jika kode lama memanggil hide().
            $('#pis-same-label-countdown-wrap').hide();
        }

        /** Selesai cooldown: tampilkan pesan scan lagi (tanpa angka countdown). */
        function finishLabelCooldownUi() {
            clearSameLabelCountdown();
            hidePisSameLabelCooldownUi();
            lastLabelCooldownStartTime = 0;
            $('.pis-scan-cooldown-inline').remove();
            var $container = $('#status-container');
            var headerPlain = $('#alert-header').text();
            if ($container.hasClass('alert-warning') && headerPlain.indexOf('Cooldown') !== -1) {
                $('#alert-body').text('Silahkan scan kembali.');
            } else if ($container.hasClass('alert-success')) {
                $('.pis-scan-cooldown-done').remove();
                $('#alert-body').append(' <span class="pis-scan-cooldown-done font-weight-bold">Silahkan scan kembali.</span>');
            } else {
                $('#alert-body').text('Silahkan scan kembali.');
            }
        }

        function tickSameLabelCountdown() {
            if (!lastLabelCooldownStartTime) return;
            var now = Date.now();
            var remainingMs = LABEL_SAME_DELAY_MS - (now - lastLabelCooldownStartTime);
            if (remainingMs <= 0) {
                finishLabelCooldownUi();
            }
        }

        function startSameLabelCountdown() {
            clearSameLabelCountdown();
            tickSameLabelCountdown();
            if (lastLabelCooldownStartTime) {
                sameLabelCountdownTimer = setInterval(tickSameLabelCountdown, 1000);
            }
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
        // Label part yang sudah divalidasi tetapi belum ditulis ke qty/counter/DB — commit saat "Confirm Packing"
        var pendingLabelPacks = [];

        // Saat interlock dibuka dan user pindah kanban/part, kita perlu memastikan
        // state part sebelumnya tidak menghalangi proses berikutnya.
        function resetActiveKanbanContext() {
            lastScannedKanban = '';
            lastScannedLabel = '';
            lastScannedLabelTime = 0;
            lastLabelCooldownStartTime = 0;
            clearSameLabelCountdown();
            $('#pis-step-flow').show();
            hidePisSameLabelCooldownUi();
            clearPendingLabelPacks();
            currentPreviewItem = null;
            clearPreviewImage();
            $('#detail_no').val('');
        }

        function getPartKeyForSession(it) {
            return (it && (it.part_number_cust || it.part_number_int) || '').toString().trim();
        }

        function getPartSessionKey(it) {
            var pk = getPartKeyForSession(it);
            if (!pk) return '';
            var ll = (it && it.loading_list_number != null) ? String(it.loading_list_number).trim() : '';
            return ll ? (ll + '|' + pk) : pk;
        }

        function pisLlNumberExists(ll) {
            var n = (ll || '').toString().trim();
            for (var i = 0; i < pisLoadingListGroups.length; i++) {
                if (pisLoadingListGroups[i].loadingListNumber === n) return true;
            }
            return false;
        }

        function rebuildFlattenedLoadingListItems() {
            loadingListItems = [];
            for (var g = 0; g < pisLoadingListGroups.length; g++) {
                var grp = pisLoadingListGroups[g];
                var ll = grp.loadingListNumber;
                for (var i = 0; i < grp.items.length; i++) {
                    var it = grp.items[i];
                    it.loading_list_number = ll;
                    loadingListItems.push(it);
                }
            }
        }

        function getSortedLoadingListItemsForMatch() {
            // Urutan = urutan LL di tabel (rebuildFlattenedLoadingListItems); semua LL sesi ikut tanpa klik "aktif".
            return loadingListItems.slice();
        }

        /** Barcode pendek / pola LL — beda dari kanban panjang (pulling-style). */
        function isLikelyPisLoadingListBarcode(raw) {
            var s = cleanBarcode(raw || '');
            if (!s) return false;
            // Perketat: hanya anggap Loading List jika 11 karakter dan diawali "C"
            // (menghindari label/kanban pendek terdeteksi sebagai LL dan memicu API 404).
            var core = s.substr(0, 11);
            if (core.length !== 11) return false;
            if (!/^C/i.test(core)) return false;
            // Hanya alfanumerik untuk mencegah artefak scanner
            if (!/^[A-Za-z0-9]{11}$/.test(core)) return false;
            return true;
        }

        function getPendingPackCountForPart(partInt, partCust, loadingListNumber) {
            var a = (partInt || '').toString().trim();
            var b = (partCust || '').toString().trim();
            var ll = (loadingListNumber || '').toString().trim();
            return pendingLabelPacks.filter(function (p) {
                return (p.part_number_int || '').toString().trim() === a
                    && (p.part_number_cust || '').toString().trim() === b
                    && (p.loading_list_number || '').toString().trim() === ll;
            }).length;
        }

        function updatePendingPackingUI() {
            var n = pendingLabelPacks.length;
            $('#pis-pending-count').text(String(n));
            $('#pis-btn-confirm-packing').prop('disabled', n === 0);
        }

        function clearPendingLabelPacks() {
            pendingLabelPacks = [];
            updatePendingPackingUI();
        }

        /** Qty dari API/JSON sering string; wajib parse agar += tidak jadi konkatenasi (mis. 0+"4"+"3" → "043"). */
        function toQtyNumber(v) {
            if (v == null || v === '') return 0;
            var n = parseInt(v, 10);
            return isNaN(n) ? 0 : n;
        }

        /**
         * Terapkan satu label yang sudah divalidasi ke qty, counter harian, dan API (satu langkah scan seperti sebelumnya).
         * Mengembalikan object { matched } atau null jika gagal.
         */
        function applyCommittedLabelPack(matched, raw) {
            if (!matched || toQtyNumber(matched.remaining) <= 0) return null;

            var remBase = matched.remaining != null ? matched.remaining : matched.total_qty || 0;
            matched.remaining = toQtyNumber(remBase) - 1;
            if (matched.remaining < 0) matched.remaining = 0;
            matched.actual_kanban_qty = toQtyNumber(matched.actual_kanban_qty) + 1;

            loadingListScanCount += 1;
            saveDailyCounter();

            var llForUpdate = (matched.loading_list_number || currentLoadingListNumber || '').toString().trim();
            if (llForUpdate) {
                $.ajax({
                    url: '{{ url("pis/update-scan-detail") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        loading_list_number: llForUpdate,
                        part_number_int: matched.part_number_int || '',
                        part_number_cust: matched.part_number_cust || ''
                    }
                });
            }

            // Jangan memicu cooldown di sini (ini proses "Confirm Packing", bukan scan label)
            lastScannedLabel = raw || lastScannedLabel;

            return { matched: matched };
        }

        /**
         * Setelah scan label valid: hanya antre di frontend sampai user menekan Confirm Packing.
         */
        function confirmPacking() {
            if (!pendingLabelPacks.length) return;

            var toApply = pendingLabelPacks.slice();
            clearPendingLabelPacks();

            var lastResult = null;
            var lastAppliedPack = null;

            for (var i = 0; i < toApply.length; i++) {
                var pack = toApply[i];
                var matchedItem = null;
                var packLl = (pack.loading_list_number || '').toString().trim();
                for (var j = 0; j < loadingListItems.length; j++) {
                    var it = loadingListItems[j];
                    if ((it.part_number_int || '').toString().trim() === (pack.part_number_int || '').toString().trim()
                        && (it.part_number_cust || '').toString().trim() === (pack.part_number_cust || '').toString().trim()
                        && (it.loading_list_number || '').toString().trim() === packLl) {
                        matchedItem = it;
                        break;
                    }
                }
                if (!matchedItem) continue;

                var res = applyCommittedLabelPack(matchedItem, pack.rawLabel);
                if (!res) continue;
                lastResult = res;
                lastAppliedPack = pack;

                if (res.matched.remaining > 0) {
                    stage = 3;
                    $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Part OK');
                    $('#alert-body').text((res.matched.part_number_cust || res.matched.part_number_int) + ' Berhasil dikonfirmasi. Sisa: ' + res.matched.remaining + ' box.');
                } else {
                    stage = 2;
                    lastScannedKanban = '';
                    $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-double"></i> Item Selesai');
                    $('#alert-body').text('Quantity untuk part ini sudah terpenuhi. Silahkan scan KANBAN selanjutnya.');
                }
                updateStepIndicator();

                if (isLoadingListComplete()) {
                    renderLoadingList();
                    updateCounter();
                    Swal.fire({
                        title: 'Loading List Complete!',
                        text: 'Semua item dalam daftar telah terpenuhi.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        resetPisState();
                    });
                    $(window).scrollTop(_savedScrollTop);
                    return;
                }
            }

            renderLoadingList();
            updateCounter();

            if (lastResult && lastResult.matched && lastAppliedPack) {
                var m = lastResult.matched;
                $('#detail_no').val(m.part_number_int || '');
                var normCust = (m.part_number_cust || '').toString().trim().toUpperCase();
                var cl = (lastAppliedPack.cleanUpper || '').toString();
                if (normCust && cl && (cl === normCust || cl.indexOf(normCust) !== -1 || normCust.indexOf(cl) !== -1)) {
                    currentPreviewItem = m;
                    setPreviewImage(m.part_number_int || '', m.part_number_cust || '');
                } else {
                    currentPreviewItem = null;
                    clearPreviewImage();
                }
            }

            if (lastResult) {
                $(window).scrollTop(_savedScrollTop);
            }
        }

        function setSessionStartedPartsFromKanban(partsInThisKanban) {
            partsStartedInCurrentSession = [];
            (partsInThisKanban || []).forEach(function(p) {
                var k = getPartSessionKey(p);
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

        /** Dock MMKI-SPD: kasus khusus label fisik berbentuk BASE-001 sementara API/LL/kanban hanya BASE. */
        function pisIsDockMmkiSpd() {
            return ($('#dock_type').val() || '').toString().trim().toUpperCase() === 'MMKI-SPD';
        }

        /** Hanya untuk MMKI-SPD: buang suffix revisi/lot berupa - + angka di akhir (contoh 5716A714HC-001 → 5716A714HC). */
        function pisMmkiSpdStripRevisionSuffix(s) {
            if (!s) return '';
            return ('' + s).toUpperCase().trim().replace(/-\d+$/, '');
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
            // Prefix z/Z sebelum C — loading list 11 karakter diawali C (bukan z+digit).
            // Tanpa ini: zC1234567890 → substr(0,11) jadi zC123456789 → data tidak ditemukan.
            while (/^[zZ]+C/i.test(str)) str = str.replace(/^[zZ]+/i, '');
            // Prefix z/Z di awal lalu angka (artefak scanner / Code 128)
            if (/^[zZ]\d/.test(str)) str = str.replace(/^[zZ]+/, '');
            // Suffix z/Z setelah 11 karakter LL valid (mis. C1234567890z dari scanner)
            if (/^C[A-Za-z0-9]{10}[zZ]+$/i.test(str)) str = str.substring(0, 11);
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
                        var tok = tokens[i];
                        // Token harus sama persis dengan part_number_cust (ignore case)
                        var exact = tok === cust;
                        // MMKI-SPD: token di kanban bisa BASE-001 sedangkan LL/API = BASE
                        var mmkiAlias = pisIsDockMmkiSpd() && pisMmkiSpdStripRevisionSuffix(tok) === cust;
                        if ((exact || mmkiAlias) && cust.length > bestLen) {
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
                var t = item.total_qty != null ? item.total_qty : (item.total_kanban_qty != null ? item.total_kanban_qty : 0);
                scanned += toQtyNumber(item.actual_kanban_qty);
                target += toQtyNumber(t);
            });
            if (target === 0) {
                $('#counter').text('0/0');
            } else {
                $('#counter').text(scanned + '/' + target);
            }
        }

        function isGroupComplete(grp) {
            if (!grp || !grp.items || !grp.items.length) return false;
            for (var i = 0; i < grp.items.length; i++) {
                var it = grp.items[i];
                var target = it.total_qty != null ? it.total_qty : (it.total_kanban_qty != null ? it.total_kanban_qty : 0);
                var remaining = it.remaining != null ? it.remaining : target;
                if (toQtyNumber(remaining) > 0) return false;
            }
            return true;
        }

        function renderLoadingList() {
            var tbody = $('#loading_list_body');
            tbody.empty();
            if (!pisLoadingListGroups.length) {
                tbody.append('<tr><td colspan="4" class="text-muted text-center">&nbsp;</td></tr>');
                return;
            }
            for (var g = 0; g < pisLoadingListGroups.length; g++) {
                var grp = pisLoadingListGroups[g];
                var done = isGroupComplete(grp);
                var headRow = $('<tr class="pis-ll-header"></tr>').attr('data-ll', grp.loadingListNumber);
                headRow.addClass(done ? 'table-success' : 'table-secondary');
                var $headCell = $('<td colspan="4" class="font-weight-bold small py-1"></td>');
                $headCell.text('LL: ' + (grp.displayName || grp.loadingListNumber) + ' ');
                if (done) {
                    $headCell.append($('<span class="badge badge-success">Selesai</span>'));
                } else {
                    $headCell.append($('<span class="badge badge-primary">Siap scan</span>'));
                }
                headRow.append($headCell);
                tbody.append(headRow);
                grp.items.forEach(function (item) {
                    var target = item.total_qty != null ? item.total_qty : (item.total_kanban_qty != null ? item.total_kanban_qty : 0);
                    var remaining = item.remaining != null ? item.remaining : target;
                    var current = Math.max(0, toQtyNumber(target) - toQtyNumber(remaining));
                    var row = $('<tr></tr>');
                    if (toQtyNumber(remaining) <= 0) row.addClass('table-success');
                    row.append($('<td></td>').text(item.part_number_int || '—'));
                    row.append($('<td></td>').text(item.part_number_cust || '—'));
                    row.append($('<td class="text-right"></td>').text(current));
                    row.append($('<td class="text-right"></td>').text(target));
                    tbody.append(row);
                });
            }
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
                            var llSummary = pisLoadingListGroups.map(function (g) {
                                return g.loadingListNumber;
                            }).join(', ');
                            Swal.fire({
                                title: 'Loading list belum selesai',
                                html: 'Masih ada item belum terpenuhi di LL: <strong>' + (llSummary || '-') + '</strong>.<br>Tambah loading list baru? (PDS harus sama; tabel menampilkan tiap LL terpisah.)',
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
                                    openInterlockModal();
                                    setTimeout(function() { $('#pis-input-jp-confirm').focus(); }, 300);
                                }
                            });
                            barcode = '';
                            return;
                        }
                        scanLoadingList(processBarcode, displayBarcode);
                    } else if (stage === 2 && loadingListItems.length > 0) {
                        if (isLikelyPisLoadingListBarcode(processBarcode)) {
                            scanLoadingList(processBarcode, displayBarcode);
                        } else {
                            processKanbanScan(processBarcode);
                            $('#part_number_loading').hide();
                            $(window).scrollTop(_savedScrollTop);
                        }
                    } else if (stage === 3 && loadingListItems.length > 0) {
                        if (isLikelyPisLoadingListBarcode(processBarcode)) {
                            scanLoadingList(processBarcode, displayBarcode);
                        } else {
                            processPartScan(processBarcode, displayBarcode);
                            $('#part_number_loading').hide();
                        }
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
            if (!pisLoadingListGroups.length) return false;
            for (var g = 0; g < pisLoadingListGroups.length; g++) {
                var items = pisLoadingListGroups[g].items || [];
                for (var i = 0; i < items.length; i++) {
                    if (toQtyNumber(items[i].remaining) > 0) return false;
                }
            }
            return true;
        }

        function resetPisState() {
            stage = 1;
            pisLoadingListGroups = [];
            pisSessionPdsNumber = '';
            loadingListItems = [];
            partsStartedInCurrentSession = [];
            clearPendingLabelPacks();
            currentLoadingListNumber = '';
            lastScannedKanban = '';
            currentPreviewItem = null;
            lastScannedLabel = '';
            lastScannedLabelTime = 0;
            lastLabelCooldownStartTime = 0;
            barcode = '';
            pendingJpAction = null;
            pendingScanBarcode = null;
            pendingScanDisplayBarcode = null;
            jpConfirmBarcode = '';
            $('#modalPisJpConfirmation').modal('hide');
            $('#part_number_loading').hide();
            $('#pis-step-flow').show();
            hidePisSameLabelCooldownUi();
            updateStepIndicator();
            renderLoadingList();
            updateCounter();
            $('#detail_no').val('');
            clearPreviewImage();
            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
            $('#alert-header').html('<i class="fas fa-check-circle"></i> Ready');
            $('#alert-body').text('Silahkan Scan Loading List untuk memulai');
        }

        function openInterlockModal() {
            var $modal = $('#modalPisJpConfirmation');
            if (!$modal.length) return;

            // Pastikan modal tidak berada di dalam elemen lain/backdrop.
            if (!$modal.parent().is('body')) {
                $modal.appendTo('body');
            }

            $modal.modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
        }

        function showJpConfirmationThen(callback) {
            pendingJpAction = callback;
            jpConfirmBarcode = '';
            $('#pis-input-jp-confirm').val('');
            openInterlockModal();
            setTimeout(function() { $('#pis-input-jp-confirm').focus(); }, 300);
        }

        // Stage 2: Scan kanban — simpan data untuk validasi label (label harus terkandung di data kanban)
        function processKanbanScan(barcode) {
            if (pendingLabelPacks.length > 0) {
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Konfirmasi diperlukan');
                $('#alert-body').text('Ada label yang belum dikonfirmasi. Tekan Confirm Packing terlebih dahulu, lalu scan kanban baru.');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }
            // Simpan string asli dari scanner (biasanya sangat panjang)
            var fullKanbanRaw = cleanBarcode(barcode || '');
            if (isLikelyPisLoadingListBarcode(fullKanbanRaw)) {
                scanLoadingList(fullKanbanRaw, stripLastTwoChars(fullKanbanRaw));
                $(window).scrollTop(_savedScrollTop);
                return;
            }
            // Hanya ambil bagian Part No Customer yang relevan dengan loading list aktif
            var extractedPart = extractCustomerPartFromKanban(fullKanbanRaw);
            // raw = nilai yang akan dipakai untuk semua proses selanjutnya (hanya part no customer)
            var raw = extractedPart || fullKanbanRaw;

            if (!raw) {
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Kanban Kosong');
                $('#alert-body').text('Data kanban tidak valid.');
                pisErrorSound();
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
                pisErrorSound();
                
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
            function isPartStartedInSession(item) {
                var key = getPartSessionKey(item);
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
                pisErrorSound();

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
                    $('#status-container').removeClass('alert-danger').addClass('alert-warning');
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK (setelah verifikasi)');
                    $('#alert-body').text('Kanban diterima. Silahkan scan LABEL PART.');
                    $(window).scrollTop(_savedScrollTop);
                });
                return;
            }
            // --- END LOGIKA VALIDASI ---

            // JIKA LOLOS VALIDASI (Sisa Qty masih > 0): tandai part dalam kanban ini sebagai "dimulai" dalam sesi ini
            partsInThisKanban.forEach(function(p) {
                var k = getPartSessionKey(p);
                if (k && partsStartedInCurrentSession.indexOf(k) === -1) partsStartedInCurrentSession.push(k);
            });
            // Simpan ke state interlock hanya bagian Part No Customer yang sudah diproses
            lastScannedKanban = raw;
            stage = 3;
            updateStepIndicator();

            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-warning');
            $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK');
            $('#alert-body').text('Kanban diterima. Silahkan scan LABEL PART.');
            pisOkSound();
            
            $(window).scrollTop(_savedScrollTop);
        }

       	function processPartScan(barcode, displayBarcode) {
            var raw = cleanBarcode(barcode || '');
            // Bersihkan countdown lama bila ada (mencegah interval numpuk saat proses scan berjalan)
            clearSameLabelCountdown();

            if (isLikelyPisLoadingListBarcode(raw)) {
                $('#pis-step-flow').show();
                hidePisSameLabelCooldownUi();
                scanLoadingList(raw, stripLastTwoChars(raw));
                $(window).scrollTop(_savedScrollTop);
                $('#part_number_loading').hide();
                return;
            }

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
            if (pisIsDockMmkiSpd()) {
                var mmkiRawBase = pisMmkiSpdStripRevisionSuffix(rawUpper);
                var mmkiCleanBase = pisMmkiSpdStripRevisionSuffix(cleanUpper);
                existsInKanban = existsInKanban
                    || (mmkiRawBase && kanbanUpper.indexOf(mmkiRawBase) !== -1)
                    || (mmkiCleanBase && kanbanUpper.indexOf(mmkiCleanBase) !== -1);
            }

            // INTERLOCK (1): Scan kanban Part A lalu scan label Part B yang tidak sesuai — wajib verifikasi JP/Leader
            if (!lastScannedKanban || !existsInKanban) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-lock"></i> Interlock: Label Tidak Sesuai Kanban');
                $('#alert-body').text('Label "' + (cleanLabel || raw) + '" tidak sesuai kanban. Proses dihentikan. Hubungi JP/Leader untuk verifikasi.');
                pisErrorSound();

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

            // 2. Cooldown 15 detik setelah scan label (mencegah scan beruntun terlalu cepat)
            var now = Date.now();
            if (lastLabelCooldownStartTime && (now - lastLabelCooldownStartTime) < LABEL_SAME_DELAY_MS) {
                $('#status-container').removeClass('alert-success alert-danger').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-hourglass-half"></i> Cooldown');
                $('#alert-body').text('Delay aktif. Mohon tunggu sebentar.');
                pisErrorSound();
                startSameLabelCountdown();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            hidePisSameLabelCooldownUi();

            // 3. PENCARIAN ITEM DI LOADING LIST (urutan LL di tabel; part sama di beberapa LL tanpa klik prioritas)
            var sortedItems = getSortedLoadingListItemsForMatch();
            // Tahap A: Match Exact
            var mmkiScanBase = pisIsDockMmkiSpd() ? pisMmkiSpdStripRevisionSuffix(cleanUpper) : '';
            for (var i = 0; i < sortedItems.length; i++) {
                var item = sortedItems[i];
                if ((item.remaining || 0) <= 0) continue;

                var pcust = (item.part_number_cust || '').toString().trim();
                var pint = (item.part_number_int || '').toString().trim();

                if (pcust === raw || pcust === cleanLabel || pint === raw || pint === cleanLabel) {
                    matched = item;
                    break;
                }
                // MMKI-SPD: scan 5716A714HC-001 vs API/LL 5716A714HC
                if (pisIsDockMmkiSpd() && mmkiScanBase) {
                    if (pcust.toUpperCase() === mmkiScanBase || pint.toUpperCase() === mmkiScanBase) {
                        matched = item;
                        break;
                    }
                }
            }

            // Tahap B: Match Contains (Jika exact match tidak ditemukan)
            if (!matched) {
                for (var j = 0; j < sortedItems.length; j++) {
                    var it = sortedItems[j];
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
                    if (pisIsDockMmkiSpd() && mmkiScanBase) {
                        if (pcust2 && (mmkiScanBase === pcust2 || mmkiScanBase.indexOf(pcust2) !== -1 || pcust2.indexOf(mmkiScanBase) !== -1)) {
                            matched = it;
                            break;
                        }
                        if (pint2 && (mmkiScanBase === pint2 || mmkiScanBase.indexOf(pint2) !== -1 || pint2.indexOf(mmkiScanBase) !== -1)) {
                            matched = it;
                            break;
                        }
                    }
                }
            }

            // 4. JIKA ITEM DITEMUKAN (MATCHED) — simpan ke antrean frontend; qty & counter setelah Confirm Packing
            if (matched) {
                var pendingForPart = getPendingPackCountForPart(matched.part_number_int, matched.part_number_cust, matched.loading_list_number);
                var rem = (matched.remaining != null ? matched.remaining : matched.total_qty || 0);
                if (pendingForPart + 1 > rem) {
                    $('#status-container').removeClass('alert-success').addClass('alert-warning');
                    $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Melebihi sisa');
                    $('#alert-body').text('Jumlah label (termasuk yang menunggu konfirmasi) melebihi sisa untuk part ini.');
                    pisErrorSound();
                    $(window).scrollTop(_savedScrollTop);
                    return;
                }

                lastScannedLabelTime = Date.now();
                lastLabelCooldownStartTime = lastScannedLabelTime;

                var partKey = getPartSessionKey(matched);
                if (partKey && partsStartedInCurrentSession.indexOf(partKey) === -1) partsStartedInCurrentSession.push(partKey);

                pendingLabelPacks.push({
                    part_number_int: matched.part_number_int || '',
                    part_number_cust: matched.part_number_cust || '',
                    loading_list_number: (matched.loading_list_number || '').toString().trim(),
                    rawLabel: raw,
                    cleanLabel: cleanLabel,
                    cleanUpper: cleanUpper
                });
                updatePendingPackingUI();

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
                    pisOkSound();
                } else {
                    // SUDAH HABIS: Kembali ke Stage 2 (Harus scan kanban baru untuk part lain)
                    stage = 2;
                    lastScannedKanban = ''; // Reset kanban agar user wajib scan kanban baru
                    $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                    $('#alert-header').html('<i class="fas fa-check-double"></i> Item Selesai');
                    $('#alert-body').text('Quantity untuk part ini sudah terpenuhi. Silahkan scan KANBAN selanjutnya.');
                    pisOkSound();
                }

                // Mulai countdown di background (tanpa menampilkan detik)
                startSameLabelCountdown();

                updateStepIndicator();

                $(window).scrollTop(_savedScrollTop);
            } else {
                // JIKA TIDAK ADA YANG COCOK DI LOADING LIST
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Tidak Cocok');
                $('#alert-body').text('Part "' + cleanLabel + '" tidak ada dalam daftar loading list atau qty sudah terpenuhi.');
                pisErrorSound();
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
        // 2) Jika belum ada → panggil API DEA, simpan ke DB, lalu tambahkan grup LL (boleh banyak LL per sesi; tabel per LL).
        // Part Number (detail_no) diisi dengan hasil scan barcode.
        function scanLoadingList(barcode, displayBarcode) {
            if (pendingLabelPacks.length > 0) {
                $('#part_number_loading').hide();
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Konfirmasi diperlukan');
                $('#alert-body').text('Tekan Confirm Packing terlebih dahulu sebelum menambah loading list.');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }
            var rawScanned = cleanBarcode((barcode || '').toString());
            var loadingListNumberForDB = rawScanned.replace(/[\x00-\x1F\x7F]/g, '').trim().substr(0, 11);
            var loadingListNumberForApi = loadingListNumberForDB + ' A';

            function mapPisItemsFromDbOrApi(items) {
                return (items || []).map(function (it) {
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
            }

            function mapPisItemsFromDeaApi(items) {
                return (items || []).map(function (it) {
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
            }

            function finishApplyNewGroup(name, itemsMapped, fromExisting, pdsFromRes) {
                if (!loadingListNumberForDB) {
                    $('#part_number_loading').hide();
                    failLoadingList('Nomor loading list tidak valid.');
                    return;
                }
                if (pisLlNumberExists(loadingListNumberForDB)) {
                    $('#part_number_loading').hide();
                    $('#status-container').removeClass('alert-success').addClass('alert-warning');
                    $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Loading list sudah ada');
                    $('#alert-body').text('Nomor ' + loadingListNumberForDB + ' sudah ada di sesi. Scan LL lain jika perlu.');
                    pisErrorSound();
                    $(window).scrollTop(_savedScrollTop);
                    return;
                }
                var pds = (pdsFromRes || '').toString().trim();
                if (pisLoadingListGroups.length === 0) {
                    pisSessionPdsNumber = pds;
                    partsStartedInCurrentSession = [];
                    clearPendingLabelPacks();
                } else {
                    // Interlock: semua LL dalam satu sesi wajib PDS sama (termasuk sama-sama kosong vs salah satu ada nilai).
                    var sessionPdsNorm = (pisSessionPdsNumber || '').toString().trim().toUpperCase();
                    var newPdsNorm = pds.toUpperCase();
                    if (sessionPdsNorm !== newPdsNorm) {
                        $('#part_number_loading').hide();
                        $('#status-container').removeClass('alert-success').addClass('alert-danger');
                        $('#alert-header').html('<i class="icon fa fa-warning"></i> PDS tidak sesuai');
                        $('#alert-body').text(
                            'Tidak boleh mencampur loading list dengan PDS berbeda dalam satu sesi. ' +
                            'PDS sesi: ' + (pisSessionPdsNumber || '(kosong)') + ' — PDS LL ini: ' + (pds || '(kosong)') + '.'
                        );
                        pisErrorSound();
                        $(window).scrollTop(_savedScrollTop);

                        // Wajib konfirmasi JP/Leader untuk override PDS berbeda
                        $.ajax({
                            url: '{{ url("error/store") }}',
                            type: 'GET',
                            data: {
                                source: 'pis',
                                message: 'Interlock: PDS loading list berbeda',
                                expected: pisSessionPdsNumber || '',
                                scanned: pds || ''
                            }
                        });

                        // Wajib JP/Leader hanya sebagai acknowledgement, TIDAK boleh memasukkan LL PDS beda
                        showJpConfirmationThen(function () {
                            Swal.fire({
                                title: 'PDS berbeda',
                                text: 'Konfirmasi JP/Leader tercatat. Loading list dengan PDS berbeda tetap tidak dimasukkan.',
                                icon: 'info',
                                confirmButtonText: 'OK'
                            }).then(function() {
                                $('#part_number_loading').hide();
                            });
                        });

                        return;
                    }
                }

                pisLoadingListGroups.push({
                    loadingListNumber: loadingListNumberForDB,
                    displayName: name,
                    pds_number: pds,
                    items: itemsMapped
                });
                currentLoadingListNumber = loadingListNumberForDB;
                rebuildFlattenedLoadingListItems();
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
                var nLl = pisLoadingListGroups.length;
                $('#alert-body').text(
                    fromExisting
                        ? 'LL (existing): ' + name + ' — total ' + nLl + ' LL. Tiap LL dipisah di tabel; scan kanban & label; Confirm Packing per label.'
                        : 'LL: ' + name + ' — total ' + nLl + ' LL. Scan kanban, lalu label; Confirm Packing untuk qty & counter.'
                );
                pisOkSound();
            }

            function failLoadingList(message) {
                $('#part_number_loading').hide();
                if (!pisLoadingListGroups.length) {
                    stage = 1;
                    pisSessionPdsNumber = '';
                    currentLoadingListNumber = '';
                    loadingListItems = [];
                    partsStartedInCurrentSession = [];
                    clearPendingLabelPacks();
                    lastScannedKanban = '';
                    updateStepIndicator();
                    renderLoadingList();
                    updateCounter();
                    $('#detail_no').val('');
                    currentPreviewItem = null;
                    clearPreviewImage();
                }
                $(window).scrollTop(_savedScrollTop);
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="icon fa fa-warning"></i> ' + (message ? 'Loading List Tidak Ditemukan' : 'Error Pemindaian'));
                $('#alert-body').text(message || 'Gagal memindai loading list.');
                pisErrorSound();
            }

            $.ajax({
                url: '{{ url("pis/get-loading-list-data") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    barcode: rawScanned
                },
                dataType: 'json',
                success: function (res) {
                    if (res.exists && res.items && res.items.length >= 0) {
                        console.log('Loading list dari database (existing):', res);

                        // Jika backend mengirim kumpulan LL dengan PDS yang sama, muat semua sekaligus.
                        if (Array.isArray(res.related_loading_lists) && res.related_loading_lists.length > 0) {
                            pisLoadingListGroups = [];
                            partsStartedInCurrentSession = [];
                            clearPendingLabelPacks();

                            var sessionPds = (res.pds_number || '').toString().trim();
                            pisSessionPdsNumber = sessionPds;

                            for (var idx = 0; idx < res.related_loading_lists.length; idx++) {
                                var rel = res.related_loading_lists[idx] || {};
                                var relLl = (rel.loading_list_number || '').toString().trim();
                                if (!relLl) continue;
                                var relItemsMapped = mapPisItemsFromDbOrApi(rel.items || []);
                                pisLoadingListGroups.push({
                                    loadingListNumber: relLl,
                                    displayName: rel.name || relLl,
                                    pds_number: (rel.pds_number || sessionPds || '').toString().trim(),
                                    items: relItemsMapped
                                });
                            }

                            if (!pisLoadingListGroups.length) {
                                // Fallback ke perilaku lama jika data related kosong/tidak valid.
                                var mappedDbFallback = mapPisItemsFromDbOrApi(res.items);
                                finishApplyNewGroup(
                                    res.name || res.loading_list_number || loadingListNumberForDB,
                                    mappedDbFallback,
                                    true,
                                    sessionPds
                                );
                                return;
                            }

                            currentLoadingListNumber = loadingListNumberForDB;
                            rebuildFlattenedLoadingListItems();
                            stage = 2;
                            lastScannedKanban = '';
                            currentPreviewItem = null;
                            updateStepIndicator();
                            renderLoadingList();
                            updateCounter();
                            clearPreviewImage();
                            $('#detail_no').val(displayBarcode);
                            $(window).scrollTop(_savedScrollTop);
                            $('#part_number_loading').hide();
                            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                            $('#alert-header').html('<i class="icon fa fa-check"></i> Group PDS Dimuat');
                            $('#alert-body').text(
                                'LL ' + loadingListNumberForDB + ' ditemukan. ' +
                                pisLoadingListGroups.length + ' loading list dengan PDS yang sama otomatis dimuat.'
                            );
                            pisOkSound();
                            return;
                        }

                        var mappedDb = mapPisItemsFromDbOrApi(res.items);
                        finishApplyNewGroup(
                            res.name || res.loading_list_number || loadingListNumberForDB,
                            mappedDb,
                            true,
                            res.pds_number || ''
                        );
                        return;
                    }

                    $.ajax({
                        type: 'GET',
                        url: 'https://dea-dev.aiia.co.id/api/v1/loading-lists/' + encodeURIComponent(loadingListNumberForApi),
                        headers: { "Authorization": "Bearer " + token },
                        dataType: 'json',
                        success: function (data) {
                            var ok = (data && data.status === 'success' && data.data) || (data && data.loading_list);
                            var payload = (data && data.data) || (data && data.loading_list) || {};
                            var name = payload.name || payload.number || loadingListNumberForApi;
                            var items = (payload.items || []);

                            if (ok) {
                                console.log('Loading list dari API (pertama kali):', payload);
                                var mapped = mapPisItemsFromDeaApi(items);

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
                                        items: mapped
                                    },
                                    success: function (response) {
                                        console.log('PIS scan saved (first time):', response);
                                    },
                                    error: function (xhr) {
                                        console.error('Failed to save PIS scan:', xhr);
                                    }
                                });

                                finishApplyNewGroup(
                                    name,
                                    mapped,
                                    false,
                                    payload.pds_number || payload.pds || ''
                                );
                            } else {
                                failLoadingList('Barcode tidak sesuai dengan loading list.');
                            }
                        },
                        error: function (xhr) {
                            failLoadingList('Gagal memindai loading list: ' + (xhr.statusText || 'network error'));
                        }
                    });
                },
                error: function (xhr) {
                    failLoadingList('Gagal cek data loading list: ' + (xhr.statusText || 'network error'));
                }
            });
        }

        function pisUpdateFullscreenButton() {
            var isFullscreen = !!document.fullscreenElement;
            var $btn = $('#pis-btn-fullscreen');
            if (!$btn.length) return;
            if (isFullscreen) {
                $btn.html('<i class="fas fa-compress"></i> Exit Fullscreen');
            } else {
                $btn.html('<i class="fas fa-expand"></i> Fullscreen');
            }
        }

        var pisFullscreenTransitionLock = false;
        var pisLastFullscreenToggleAt = 0;

        function pisToggleFullscreen() {
            if (pisFullscreenTransitionLock) return;

            var now = Date.now();
            // Scanner bisa mengirim Enter beruntun; cegah toggle fullscreen bolak-balik.
            if (now - pisLastFullscreenToggleAt < 700) return;
            pisLastFullscreenToggleAt = now;
            pisFullscreenTransitionLock = true;

            if (document.fullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                return;
            }
            var root = document.documentElement;
            if (root.requestFullscreen) {
                root.requestFullscreen().catch(function () { });
            }
        }

        $('#pis-btn-delay').on('click', function() {
            resetPisState();
        });

        $('#pis-btn-fullscreen').on('click', function() {
            $(this).blur();
            pisToggleFullscreen();
        });

        document.addEventListener('fullscreenchange', function() {
            pisFullscreenTransitionLock = false;
            pisUpdateFullscreenButton();
        });

        $('#pis-btn-confirm-packing').on('click', function () {
            if (!pendingLabelPacks.length) return;
            confirmPacking();
        });

        $('#modalPisJpConfirmation').on('shown.bs.modal', function() {
            $('body').addClass('pis-interlock-open');
            $('#pis-input-jp-confirm').focus();
        });

        $('#modalPisJpConfirmation').on('hidden.bs.modal', function() {
            $('body').removeClass('pis-interlock-open');
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
            pisUpdateFullscreenButton();
            
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
