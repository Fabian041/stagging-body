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
                                        <input id="detail_no" class="form-control" name="detail_no" required tabindex="-1" placeholder="Scan di sini" aria-describedby="pis-pending-banner">
                                    </div>
                                    <div id="pis-pending-banner" class="alert alert-warning py-1 px-2 small mb-0 mt-1" style="display:none;" role="status">
                                        <i class="fas fa-lock"></i> Wajib tekan <strong>Confirm Packing</strong> sebelum scan label berikutnya, kanban baru, atau loading list lain.
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-1" style="border-radius: 12px;">
                                <div class="p-2" style="padding-left: 10px;">
                                    <strong>Counter (pcs)</strong>
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
                                                <th style="width: 58px;" class="text-right">Cur (pcs)</th>
                                                <th style="width: 58px;" class="text-right">Tar (pcs)</th>
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
                                            <button type="button" class="btn btn-lg btn-outline-danger" id="pis-btn-delay" tabindex="-1" style="border-radius: 40px; width: 100%; min-height: 50px; font-size: 1.15rem;">
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
                                    <div id="pis-confirm-packing-card" class="mt-1">
                                        <button type="button" class="btn btn-lg btn-success" id="pis-btn-confirm-packing" disabled style="border-radius: 40px; width: 100%; min-height: 54px; font-size: 1.07rem;">
                                            <i class="fas fa-check-double"></i> Confirm Packing
                                        </button>
                                        <div class="text-center small text-muted mt-1 mb-0">
                                            Menunggu konfirmasi: <strong id="pis-pending-count">0</strong> label
                                        </div>
                                    </div>
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

                                    <span id="pis-step3-block">
                                        <span class="mx-2">→</span>

                                        <span>STEP 3</span>
                                        <i class="fas fa-tag mx-1"></i>
                                        <span>Label Part</span>
                                    </span>

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
                                            <p id="pis-preview-hint" class="mb-0">Gambar akan muncul otomatis jika label cocok</p>
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
                                        <button value="MMKI-Fujitrans" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">MMKI-Fujitrans</button>
                                        <button value="MMKI-Senopati" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">MMKI-Senopati</button>
                                        <button value="MMKI-MMO" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">MMKI-MMO</button>
                                        <button value="6I" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">6I</button>
                                        <button value="TAM-ADM" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TAM-ADM</button>
                                        <button value="TAM-HINO" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TAM-HINO</button>
                                        <button value="ADM-AS" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">ADM-AS</button>
                                        <button value="ADM-KP" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">ADM-KP</button>
                                        <button value="YHA" type="button" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">YHA</button>
                                        <button value="ADM" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">ADM</button>
                                        <button value="TTI" type="button" data-dandory="1" class="btn btn-block btn-default pis-dock-btn" onclick="func_change_dock(this);">TTI</button>
                                        <input id="dock_type" value="TMMIN SPD" type="hidden">
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

        /* SweetAlert2 default z-index ~1060; harus di atas modal interlock */
        body.pis-interlock-open .swal2-container {
            z-index: 10050 !important;
        }

        /* Pratinjau: tinggi adaptif mengikuti monitor (terutama desktop/16:9). */
        #imageDiv.pis-preview-area {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 760px;
            height: clamp(760px, 94vh, 1750px);
            max-height: 1750px;
            overflow: hidden;
            box-sizing: border-box;
            padding: 2px;
        }

        /* Di monitor desktop, naikkan tinggi supaya area preview tidak tampak kecil. */
        @media (min-width: 1200px) {
            #imageDiv.pis-preview-area {
                height: clamp(920px, 95vh, 1900px);
                max-height: 1900px;
            }
        }

        /* Di monitor tinggi (mis. 1080p ke atas), gunakan porsi viewport lebih besar. */
        @media (min-height: 1000px) {
            #imageDiv.pis-preview-area {
                height: clamp(1000px, 97vh, 2100px);
                max-height: 2100px;
            }
        }

        #previewImg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            box-sizing: border-box;
        }

        #previewImg.pis-fit-contain {
            object-fit: contain;
        }

        #previewImg.pis-fit-cover {
            object-fit: cover;
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

        /* Interlock: ada label yang menunggu Confirm Packing */
        body.pis-pending-confirm-pack #detail_no {
            background-color: #fff3cd;
            border-color: #ffc107;
        }

        /* Delay: default putih; hindari state focus/active tertahan di touchscreen (tampak merah penuh) */
        #pis-btn-delay {
            background-color: #fff !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #pis-btn-delay:hover {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        #pis-btn-delay:focus,
        #pis-btn-delay:active,
        #pis-btn-delay.active,
        #pis-btn-delay.focus {
            background-color: #fff !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
            box-shadow: none !important;
        }

        /* Confirm Packing: di bawah Fullscreen, tetap terlihat saat scroll kolom kiri */
        #pis-confirm-packing-card {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background: #fff;
            padding-top: 0.15rem;
        }

        #pis-btn-confirm-packing:disabled {
            opacity: 0.72;
            cursor: not-allowed;
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

    {{-- Modal: semua loading list dalam sesi selesai --}}
    <div class="modal fade" id="modalPisLoadingListComplete" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> Loading list selesai</h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">Semua item dalam daftar telah terpenuhi.</p>
                    <p class="mb-0 font-weight-bold">LL: <span id="pis-ll-complete-summary">-</span></p>
                </div>
                <div class="modal-footer justify-content-center py-2">
                    <button type="button" class="btn btn-success px-4" id="pis-btn-ll-complete-ok">OK</button>
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
    <audio id="pis-lbl-tidak-valid-sound">
        <source src="{{ asset('assets/sounds/lblTidakValid.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="pis-ll-selesai-sound">
        <source src="{{ asset('assets/sounds/LLSelesai.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="pis-pds-berbeda-sound">
        <source src="{{ asset('assets/sounds/pdsBerbeda.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="pis-pindah-kanban-sound">
        <source src="{{ asset('assets/sounds/pindahKanban.mp3') }}" type="audio/mpeg">
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

        function pisLabelInvalidSound() {
            playPisSound('pis-lbl-tidak-valid-sound');
        }

        function pisLlCompleteSound() {
            playPisSound('pis-ll-selesai-sound');
        }

        function pisPdsBerbedaSound() {
            playPisSound('pis-pds-berbeda-sound');
        }

        function pisPindahKanbanSound() {
            playPisSound('pis-pindah-kanban-sound');
        }

        // Gambar PIS via route Laravel (baca Storage langsung) — aman untuk nama file ber-spasi tanpa mengandalkan symlink/static URL.
        var pisImageFileEndpoint = "{{ url('/pis/storage-image') }}";
        var pisImageDefault = pisImageFileEndpoint + "?f={{ rawurlencode('default.JPG') }}";

        /** Variasi segmen dock pada nama file (legacy / beda konvensi penamaan). */
        function pisDockFilenameVariants(dockRaw) {
            var d = (dockRaw || '').toString().trim().toUpperCase();
            if (!d) d = 'OTHER';
            var out = [d];
            if (d.indexOf(' ') !== -1) {
                out.push(d.replace(/ /g, '_'));
                out.push(d.replace(/ /g, '-'));
                out.push(d.replace(/ /g, ''));
            }
            return out.filter(function (x, i, arr) { return arr.indexOf(x) === i; });
        }

        // Lookup gambar dari part_number_cust + type + dock (sama pola backend PisController).
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
            var docks = pisDockFilenameVariants(pisGetDockType());

            var candidates = [];
            var add = function (fileName) {
                if (fileName) {
                    candidates.push(pisImageFileEndpoint + '?f=' + encodeURIComponent(fileName));
                }
            };

            var addBase = function (base) {
                if (!base) return;
                add(base + '.webp');
                add(base + '.JPG');
            };

            for (var di = 0; di < docks.length; di++) {
                var dock = docks[di];
                // Pola utama: [PART_CUST]-[TYPE]-[DOCK] — utamakan .webp, fallback .JPG
                if (custSanitized) {
                    addBase(custSanitized + '-' + type + '-' + dock);
                }
                if (custNoDash && custNoDash !== custSanitized) {
                    addBase(custNoDash + '-' + type + '-' + dock);
                }
            }

            // Fallback tambahan: hanya [PART_CUST]
            if (custSanitized) {
                addBase(custSanitized);
            }
            if (custNoDash && custNoDash !== custSanitized) {
                addBase(custNoDash);
            }

            var idx = 0;

            function setPreviewFitModeBySrc() {
                if (!img || !img.src) return;
                var srcUpper = img.src.toUpperCase();
                // DANDORY board cenderung berisi detail sampai sisi tepi, jadi tampilkan utuh (atas-bawah fit).
                var useContain = srcUpper.indexOf('-DANDORY-') !== -1;
                img.classList.remove('pis-fit-contain', 'pis-fit-cover');
                img.classList.add(useContain ? 'pis-fit-contain' : 'pis-fit-cover');
            }

            function tryNext() {
                if (idx >= candidates.length) {
                    // Jika semua kombinasi gagal, pakai gambar default sebagai fallback
                    img.onerror = null;
                    img.src = pisImageDefault;
                    setPreviewFitModeBySrc();
                    img.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    if (typeof _savedScrollTop === 'number') window.scrollTo(0, _savedScrollTop);
                    return;
                }

                img.src = candidates[idx++];
                setPreviewFitModeBySrc();
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
            pisRefreshDockSpecificUi();
            // Refresh gambar hanya jika sudah ada part yang tervalidasi (scan label = Part Number (Cust))
            if (currentPreviewItem) {
                setPreviewImage(currentPreviewItem.part_number_int || '', currentPreviewItem.part_number_cust || '');
            }
        }

        /** Dock aktif: tombol primary di UI (sumber kebenaran), fallback ke hidden #dock_type. */
        function pisGetDockType() {
            var $primary = $('#dock').find('button.pis-dock-btn.btn-primary:visible').first();
            if (!$primary.length) {
                $primary = $('#dock').find('button.pis-dock-btn.btn-primary').first();
            }
            if ($primary.length) {
                var fromBtn = ($primary.attr('value') || '').toString().trim();
                if (fromBtn) {
                    $('#dock_type').val(fromBtn);
                    return fromBtn;
                }
            }
            return ($('#dock_type').val() || 'TMMIN SPD').toString().trim();
        }

        function pisUpdateScanContextOnServer(meta) {
            if (!meta || !meta.loading_list_number) return;
            $.ajax({
                url: '{{ url("pis/save-scan") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    delivery_type: ($('#delivery_type').val() || 'OEM'),
                    dock_type: pisGetDockType(),
                    loading_list_number: meta.loading_list_number,
                    pds_number: meta.pds_number || '',
                    cycle: meta.cycle || '',
                    delivery_date: meta.delivery_date || '',
                    shipping_date: meta.shipping_date || '',
                    customer_id: meta.customer_id || null,
                    customer_code: meta.customer_code || null,
                    customer_name: meta.customer_name || null,
                    items: meta.items || []
                },
                success: function (response) {
                    console.log('PIS scan context saved:', response);
                },
                error: function (xhr) {
                    console.error('Failed to save PIS scan context:', xhr);
                }
            });
        }

        var barcode = "";
        var token = ""; // Variabel untuk menyimpan token yang diperoleh setelah login
        var stage = 1; // 1 = scan loading list, 2 = scan kanban, 3 = scan label (decrement quantity)
        var loadingListItems = []; // Flattened items (semua LL); setiap item punya loading_list_number
        var pisLoadingListGroups = []; // { loadingListNumber, displayName, pds_number, items: [] } — terpisah per LL di UI
        var pisSessionPdsNumber = ''; // PDS sesi: LL tambahan wajib sama
        var currentLoadingListNumber = ''; // LL terakhir di-scan (log/API); match part memakai semua LL sesi secara setara
        var lastScannedKanban = ''; // String kanban penuh terakhir — validasi label (part harus ada di kanban)
        var ttiScannedUniques = []; // TTI: unique kanban (mis. G9C3TP) yang sudah di-scan dalam sesi LL
        // Counter harian untuk scan label part (bukan scan loading list)
        var loadingListScanCount = 0;
        // Item terakhir untuk refresh preview saat ganti dock/type (gambar dari part_number_cust di loading list).
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
        var PIS_INTERLOCK_STORAGE_KEY = 'pis_jp_interlock_active';
        // Part yang sudah "dimulai" (scan kanban/label) dalam sesi loading list saat ini saja.
        // Di-reset setiap kali loading list di-scan; interlock "part belum terpenuhi" hanya memakai ini.
        var partsStartedInCurrentSession = [];
        // Label part yang sudah divalidasi tetapi belum ditulis ke qty/counter/DB — commit saat "Confirm Packing"
        var pendingLabelPacks = [];
        var pisConfirmPackingInProgress = false;

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

        /** Nomor LL (11 char) yang sudah dimuat di sesi PIS ini. */
        function pisGetSessionLoadingListNumbers() {
            var nums = [];
            for (var gi = 0; gi < pisLoadingListGroups.length; gi++) {
                var n = (pisLoadingListGroups[gi].loadingListNumber || '').toString().trim().toUpperCase();
                if (n && nums.indexOf(n) === -1) nums.push(n);
            }
            if (!nums.length && currentLoadingListNumber) {
                nums.push(currentLoadingListNumber.toString().trim().toUpperCase());
            }
            return nums;
        }

        /** Kanban wajib memuat minimal satu nomor Loading List sesi (mis. C365A494520). */
        function pisKanbanContainsSessionLoadingList(kanbanStr) {
            var upper = (kanbanStr || '').toString().toUpperCase();
            if (!upper) return false;
            var sessionLls = pisGetSessionLoadingListNumbers();
            if (!sessionLls.length) return true;
            for (var i = 0; i < sessionLls.length; i++) {
                if (sessionLls[i] && upper.indexOf(sessionLls[i]) !== -1) return true;
            }
            return false;
        }

        /**
         * Scan pendek yang hanya berisi part cust (label) — bukan kanban panjang / berisi LL.
         * Dipakai di stage 2 agar label tidak lolos sebagai kanban.
         */
        function pisIsLikelyLabelOnlyBarcode(scanStr, extractedPart) {
            var s = (scanStr || '').toString().trim();
            if (!s) return false;
            if (s.length > 100) return false;
            if (pisKanbanContainsSessionLoadingList(s)) return false;
            var tokens = s.split(/\s+/).filter(function (t) { return t && t.length > 0; });
            if (tokens.length >= 4) return false;
            var part = (extractedPart || '').toString().trim().toUpperCase();
            var upper = s.toUpperCase();
            if (part && upper === part) return true;
            if (s.length <= 32 && tokens.length <= 1) return true;
            return false;
        }

        /** Interlock: satu label valid boleh antre; scan berikutnya baru setelah Confirm Packing. */
        function pisHasPendingLabelPack() {
            return pendingLabelPacks.length > 0;
        }

        function setPersistentInterlockState(isActive) {
            try {
                if (isActive) {
                    localStorage.setItem(PIS_INTERLOCK_STORAGE_KEY, '1');
                } else {
                    localStorage.removeItem(PIS_INTERLOCK_STORAGE_KEY);
                }
            } catch (e) {
                // Abaikan jika storage tidak tersedia; interlock tetap jalan selama sesi aktif.
            }
        }

        function hasPersistentInterlockState() {
            try {
                return localStorage.getItem(PIS_INTERLOCK_STORAGE_KEY) === '1';
            } catch (e) {
                return false;
            }
        }

        var pisLastConfirmInterlockSwalAt = 0;
        function showPisConfirmPackingInterlock(reason) {
            var now = Date.now();
            if (now - pisLastConfirmInterlockSwalAt < 2000) return;
            pisLastConfirmInterlockSwalAt = now;
            var text = reason === 'loading_list'
                ? 'Selesaikan Confirm Packing untuk label yang sudah di-scan sebelum menambah atau mengganti loading list.'
                : (reason === 'kanban'
                    ? 'Selesaikan Confirm Packing sebelum scan kanban atau label lain.'
                    : 'Tekan tombol Confirm Packing untuk menulis qty ke sistem. Scan label, kanban, maupun loading list lain diblok sampai konfirmasi selesai.');
            Swal.fire({
                icon: 'warning',
                title: 'Menunggu Confirm Packing',
                text: text,
                confirmButtonText: 'OK'
            });
        }

        var pisLoadingListCompleteCallback = null;

        function showPisLoadingListCompleteModal(callback) {
            var llSummary = pisLoadingListGroups.map(function (g) {
                return g.displayName || g.loadingListNumber;
            }).join(', ');
            $('#pis-ll-complete-summary').text(llSummary || '-');
            pisLoadingListCompleteCallback = callback;
            var $modal = $('#modalPisLoadingListComplete');
            if ($modal.length && !$modal.parent().is('body')) {
                $modal.appendTo('body');
            }
            pisLlCompleteSound();
            $modal.modal('show');
        }

        function updatePendingPackingUI() {
            var n = pendingLabelPacks.length;
            $('#pis-pending-count').text(String(n));
            $('#pis-btn-confirm-packing').prop('disabled', n === 0 || pisConfirmPackingInProgress);
            if (n > 0) {
                $('#pis-pending-banner').show();
                $('body').addClass('pis-pending-confirm-pack');
            } else {
                $('#pis-pending-banner').hide();
                $('body').removeClass('pis-pending-confirm-pack');
            }
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

        function getPisQtyPerKanban(item) {
            return Math.max(1, toQtyNumber(item && item.qty_per_kanban));
        }

        function getPisRemainingKanban(item) {
            if (!item) return 0;
            if (item.remaining_kanban_qty != null) {
                return Math.max(0, toQtyNumber(item.remaining_kanban_qty));
            }

            var targetKanban = toQtyNumber(item.total_kanban_qty);
            return Math.max(0, targetKanban - toQtyNumber(item.actual_kanban_qty));
        }

        function getPisConfirmedQty(item) {
            var remainingPcs = Math.max(0, toQtyNumber(item && item.remaining));
            return Math.min(remainingPcs, getPisQtyPerKanban(item));
        }

        /**
         * Simpan satu Confirm Packing ke server. Qty UI baru diubah setelah server sukses
         * agar counter dan database tidak berbeda ketika request gagal.
         */
        function applyCommittedLabelPack(matched, raw) {
            if (!matched || toQtyNumber(matched.remaining) <= 0 || getPisRemainingKanban(matched) <= 0) {
                return null;
            }

            var llForUpdate = (matched.loading_list_number || currentLoadingListNumber || '').toString().trim();
            if (!llForUpdate) return null;

            return $.ajax({
                url: '{{ url("pis/update-scan-detail") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    loading_list_number: llForUpdate,
                    part_number_int: matched.part_number_int || '',
                    part_number_cust: matched.part_number_cust || '',
                    // simpan ke tabel log agar tiap scan bisa ditelusuri
                    label: raw || ''
                }
            });
        }

        /**
         * Setelah scan label valid: hanya antre di frontend sampai user menekan Confirm Packing.
         */
        function confirmPacking() {
            if (!pendingLabelPacks.length || pisConfirmPackingInProgress) return;

            var pack = pendingLabelPacks[0];
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

            if (!matchedItem) {
                Swal.fire({
                    icon: 'error',
                    title: 'Confirm Packing',
                    text: 'Data part pada Loading List tidak ditemukan. Refresh halaman lalu scan ulang.'
                });
                return;
            }

            var request = applyCommittedLabelPack(matchedItem, pack.rawLabel);
            if (!request) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Confirm Packing',
                    text: 'Qty part sudah terpenuhi atau data Loading List tidak valid.'
                });
                return;
            }

            pisConfirmPackingInProgress = true;
            updatePendingPackingUI();

            request.done(function (response) {
                matchedItem.total_qty = toQtyNumber(response.order_qty);
                matchedItem.qty_per_kanban = Math.max(1, toQtyNumber(response.qty_per_kanban));
                matchedItem.total_kanban_qty = toQtyNumber(response.target_kanban_qty);
                matchedItem.actual_kanban_qty = toQtyNumber(response.scanned_qty);
                matchedItem.actual_qty = toQtyNumber(response.actual_qty);
                matchedItem.remaining = Math.max(0, toQtyNumber(response.remaining_order_qty));
                matchedItem.remaining_kanban_qty = Math.max(0, toQtyNumber(response.remaining_qty));

                pendingLabelPacks.shift();

                loadingListScanCount += 1;
                saveDailyCounter();

                if (pack.ttiUnique && ttiScannedUniques.indexOf(pack.ttiUnique) === -1) {
                    ttiScannedUniques.push(pack.ttiUnique);
                }

                lastScannedLabel = pack.rawLabel || lastScannedLabel;

                if (pisIsDockTti()) {
                    stage = 2;
                    lastScannedKanban = '';
                    $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                    if (matchedItem.remaining > 0) {
                        $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK');
                        $('#alert-body').text((matchedItem.part_number_cust || matchedItem.part_number_int) + ' berhasil dikonfirmasi. Sisa: ' + matchedItem.remaining + ' pcs.');
                    } else {
                        $('#alert-header').html('<i class="fas fa-check-double"></i> Item Selesai');
                        $('#alert-body').text('Qty order untuk part ini sudah terpenuhi. Silahkan scan KANBAN selanjutnya.');
                        pisPindahKanbanSound();
                    }
                } else {
                    if (matchedItem.remaining > 0) {
                        stage = 3;
                        $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                        $('#alert-header').html('<i class="fas fa-check-circle"></i> Part OK');
                        $('#alert-body').text((matchedItem.part_number_cust || matchedItem.part_number_int) + ' berhasil dikonfirmasi. Sisa: ' + matchedItem.remaining + ' pcs.');
                    } else {
                        stage = 2;
                        lastScannedKanban = '';
                        $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                        $('#alert-header').html('<i class="fas fa-check-double"></i> Item Selesai');
                        $('#alert-body').text('Qty order untuk part ini sudah terpenuhi. Silahkan scan KANBAN selanjutnya.');
                        pisPindahKanbanSound();
                    }
                }

                updateStepIndicator();
                renderLoadingList();
                updateCounter();

                $('#detail_no').val(pack.ttiUnique || matchedItem.part_number_int || '');
                if ((matchedItem.part_number_cust || '').toString().trim()) {
                    currentPreviewItem = matchedItem;
                    setPreviewImage(matchedItem.part_number_int || '', matchedItem.part_number_cust || '');
                } else {
                    currentPreviewItem = null;
                    clearPreviewImage();
                }

                if (isLoadingListComplete()) {
                    showPisLoadingListCompleteModal(function () {
                        resetPisState();
                        $(window).scrollTop(_savedScrollTop);
                    });
                    return;
                }

                $(window).scrollTop(_savedScrollTop);
            }).fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Gagal menyimpan Confirm Packing ke server. Qty belum ditambahkan dan dapat dicoba kembali.';
                Swal.fire({ icon: 'error', title: 'Confirm Packing', text: msg });
                pisErrorSound();
            }).always(function () {
                pisConfirmPackingInProgress = false;
                updatePendingPackingUI();
            });
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
            return pisGetDockType().toUpperCase() === 'MMKI-SPD';
        }

        /**
         * TTI kanban pipe-delimited, contoh:
         * <WDT>G9C3TP|18|IDAII|85820-X7A00|85820X7A00|20260602|1|...
         * unique = G9C3TP, part = 85820-X7A00 (field ke-4).
         */
        function pisIsTtiKanbanFormat(kanbanStr) {
            var s = (kanbanStr || '').toString().trim();
            if (!s || s.indexOf('|') === -1) return false;
            var parts = s.split('|');
            if (parts.length < 4) return false;
            var first = (parts[0] || '').toUpperCase();
            return first.indexOf('WDT') !== -1 || /^[A-Z0-9]{4,12}$/.test(first.replace(/^<WDT>/i, ''));
        }

        /** Dock TTI: hanya scan Loading List + Kanban (tanpa label part). */
        function pisIsDockTti() {
            return pisGetDockType().toUpperCase() === 'TTI';
        }

        function pisRefreshDockSpecificUi() {
            var isTti = pisIsDockTti();
            $('#pis-step3-block').toggle(!isTti);
            $('#pis-confirm-packing-card').show();
            $('#pis-preview-hint').text(isTti
                ? 'Gambar akan muncul otomatis jika kanban cocok dengan loading list'
                : 'Gambar akan muncul otomatis jika label cocok');
            if (isTti && stage === 3) {
                stage = 2;
                updateStepIndicator();
            }
            if (pisHasPendingLabelPack()) {
                clearPendingLabelPacks();
            }
        }

        /**
         * Parse kanban TTI berformat pipe, contoh:
         * <WDT>G9C3TP|18|IDAII|85820-X7A00|85820X7A00|20260602|1|...
         * unique = G9C3TP, part number = 85820-X7A00 (field ke-4).
         */
        function pisParseTtiKanban(kanbanStr) {
            var raw = (kanbanStr || '').toString().trim();
            if (!raw || raw.indexOf('|') === -1) return null;
            var fields = raw.split('|').map(function (p) { return (p || '').trim(); });
            if (fields.length < 4) return null;

            var uniqueRaw = (fields[0] || '').toUpperCase();
            var unique = uniqueRaw.replace(/^<[^>]+>/, '').trim();
            if (!unique) unique = uniqueRaw;

            var partNumber = (fields[3] || '').toUpperCase();
            var partNumberNoDash = (fields[4] || partNumber.replace(/-/g, '')).toUpperCase();
            if (!unique || !partNumber) return null;

            return {
                unique: unique,
                partNumber: partNumber,
                partNumberNoDash: partNumberNoDash,
                raw: raw
            };
        }

        function pisNormalizePartToken(s) {
            return (s || '').toString().trim().toUpperCase().replace(/-/g, '');
        }

        function pisTtiLlItemMatches(item, parsed) {
            if (!item || !parsed) return false;
            var pint = (item.part_number_int || '').toString().trim().toUpperCase();
            var pcust = (item.part_number_cust || '').toString().trim().toUpperCase();
            var parsedPart = (parsed.partNumber || '').toString().trim().toUpperCase();
            var parsedPartNorm = pisNormalizePartToken(parsed.partNumber);
            var parsedPartNoDash = (parsed.partNumberNoDash || parsedPartNorm).toString().trim().toUpperCase();

            if (!parsedPart) return false;

            if (pcust && (pcust === parsedPart || pisNormalizePartToken(pcust) === parsedPartNorm)) {
                return true;
            }
            if (pint && (pint === parsedPart || pisNormalizePartToken(pint) === parsedPartNorm || pint === parsedPartNoDash)) {
                return true;
            }
            return false;
        }

        function pisFindTtiLlItem(parsed) {
            if (!parsed) return null;
            var sortedItems = getSortedLoadingListItemsForMatch();
            for (var i = 0; i < sortedItems.length; i++) {
                var item = sortedItems[i];
                if (toQtyNumber(item.remaining) <= 0) continue;
                if (pisTtiLlItemMatches(item, parsed)) return item;
            }
            return null;
        }

        /** Hanya untuk MMKI-SPD: buang suffix revisi/lot berupa - + angka di akhir (contoh 5716A714HC-001 → 5716A714HC). */
        function pisMmkiSpdStripRevisionSuffix(s) {
            if (!s) return '';
            return ('' + s).toUpperCase().trim().replace(/-\d+$/, '');
        }

        /**
         * Validasi tambahan: scan label harus cocok dengan part di Loading List.
         * Mencegah false positive contains (69210VT010A14 vs LL 69210VT010A1) tetapi tetap
         * mengizinkan artefak scanner: digit ekstra di akhir, atau label lebih pendek dari LL.
         */
        function pisIsLabelStrictMatchWithLlPart(scanStr, partStr) {
            var scan = (scanStr || '').toString().trim().toUpperCase();
            var part = (partStr || '').toString().trim().toUpperCase();
            if (!scan || !part) return false;
            if (scan === part) return true;
            if (pisIsDockMmkiSpd() && pisMmkiSpdStripRevisionSuffix(scan) === part) return true;
            // Beberapa label fisik mengandung 1 prefix huruf di depan (atau scanner melewatkannya):
            // contoh LL D16100B9490001 vs scan 16100B9490001.
            if (part.length > 1 && /^[A-Z]/.test(part.charAt(0)) && part.substring(1) === scan) return true;
            if (scan.length > 1 && /^[A-Z]/.test(scan.charAt(0)) && scan.substring(1) === part) return true;

            // Scan lebih panjang dari LL: sufiks numerik di akhir label (mis. 5716A707YD0001 vs LL 5716A707YD),
            // 1 digit artefak scanner (mis. ...A14 vs ...A1), atau 1–2 huruf dari scanner (mis. 692060K020E1H vs LL 692060K020E1).
            if (scan.length > part.length && scan.indexOf(part) === 0) {
                var scanExtra = scan.substring(part.length);
                if (/^[0-9]{1,6}$/.test(scanExtra)) return true;
                if (/^[A-Z]{1,2}$/.test(scanExtra)) return true;
            }

            // Scan lebih pendek dari LL: label tidak membawa suffix penuh (mis. 15100BZ060 vs 15100BZ06000)
            if (part.length > scan.length && part.indexOf(scan) === 0) {
                var partTail = part.substring(scan.length);
                if (/^[0-9]{1,6}$/.test(partTail)) return true;
            }

            return false;
        }

        /** Tambahan: label (dengan suffix scanner) cocok part LL yang juga ada di string kanban. */
        function pisLabelMatchesKanban(scanUpper, kanbanUpper) {
            if (!scanUpper || !kanbanUpper) return false;
            var items = loadingListItems || [];
            for (var i = 0; i < items.length; i++) {
                var candidates = [
                    (items[i].part_number_cust || '').toString().trim().toUpperCase(),
                    (items[i].part_number_int || '').toString().trim().toUpperCase()
                ];
                for (var c = 0; c < candidates.length; c++) {
                    var part = candidates[c];
                    if (!part || kanbanUpper.indexOf(part) === -1) continue;
                    if (pisIsLabelStrictMatchWithLlPart(scanUpper, part)) return true;
                }
            }
            return false;
        }

        /** Apakah scan (raw atau bersih) exact match ke part_number_cust / part_number_int item LL. */
        function pisIsLabelStrictMatchWithLlItem(cleanLabel, raw, item) {
            if (!item) return false;
            var pcust = (item.part_number_cust || '').toString().trim();
            var pint = (item.part_number_int || '').toString().trim();
            var labels = [(cleanLabel || '').toString(), (raw || '').toString()];
            for (var li = 0; li < labels.length; li++) {
                var lb = labels[li];
                if (!lb) continue;
                if (pisIsLabelStrictMatchWithLlPart(lb, pcust) || pisIsLabelStrictMatchWithLlPart(lb, pint)) {
                    return true;
                }
            }
            return false;
        }

        /** Cari baris LL yang cocok persis dengan label (tanpa substring / indexOf). */
        function pisFindLlItemByStrictLabelMatch(cleanLabel, raw) {
            var sortedItems = getSortedLoadingListItemsForMatch();
            for (var i = 0; i < sortedItems.length; i++) {
                var item = sortedItems[i];
                if (toQtyNumber(item.remaining) <= 0) continue;
                if (pisIsLabelStrictMatchWithLlItem(cleanLabel, raw, item)) {
                    return item;
                }
            }
            return null;
        }

        function pisDescribeLlPartForMismatch(item) {
            if (!item) return '-';
            var c = (item.part_number_cust || '').toString().trim();
            var p = (item.part_number_int || '').toString().trim();
            if (c && p && c !== p) return c + ' / ' + p;
            return c || p || '-';
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
        // Prioritas: part yang LL-nya juga ada di string kanban (membedakan kanban vs label part saja).
        function extractCustomerPartFromKanban(kanbanStr) {
            if (!kanbanStr) return '';
            if (pisIsDockTti()) {
                var ttiParsed = pisParseTtiKanban(kanbanStr);
                if (ttiParsed && ttiParsed.partNumber) return ttiParsed.partNumber;
            }
            var upper = kanbanStr.toUpperCase();
            var kanbanHasLl = pisKanbanContainsSessionLoadingList(kanbanStr);

            var tokens = upper.split(/\s+/).filter(function(t) { return t && t.trim().length > 0; });
            if (!tokens.length) return '';

            var bestMatch = '';
            var bestLen = -1;
            var bestWithLl = '';
            var bestWithLlLen = -1;

            try {
                (loadingListItems || []).forEach(function(item) {
                    var cust = (item.part_number_cust || '').toString().toUpperCase().trim();
                    if (!cust) return;
                    var itemLl = (item.loading_list_number || '').toString().trim().toUpperCase();
                    var llInKanban = itemLl && upper.indexOf(itemLl) !== -1;
                    for (var i = 0; i < tokens.length; i++) {
                        var tok = tokens[i];
                        var exact = tok === cust;
                        var mmkiAlias = pisIsDockMmkiSpd() && pisMmkiSpdStripRevisionSuffix(tok) === cust;
                        if (!exact && !mmkiAlias) continue;
                        if (kanbanHasLl && llInKanban && cust.length > bestWithLlLen) {
                            bestWithLlLen = cust.length;
                            bestWithLl = cust;
                        }
                        if (cust.length > bestLen) {
                            bestLen = cust.length;
                            bestMatch = cust;
                        }
                    }
                });
            } catch (e) {
                // Abaikan error
            }

            return bestWithLl || bestMatch || '';
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
                var actualPcs = item.actual_qty != null
                    ? item.actual_qty
                    : Math.min(toQtyNumber(t), toQtyNumber(item.actual_kanban_qty) * getPisQtyPerKanban(item));
                scanned += toQtyNumber(actualPcs);
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
                    var remaining = Math.max(0, toQtyNumber(item.remaining != null ? item.remaining : target));
                    var current = item.actual_qty != null
                        ? Math.max(0, toQtyNumber(item.actual_qty))
                        : Math.max(0, toQtyNumber(target) - remaining);
                    var row = $('<tr></tr>');
                    if (remaining <= 0) row.addClass('table-success');
                    row.append($('<td></td>').text(item.part_number_int || '—'));
                    row.append($('<td></td>').text(item.part_number_cust || '—'));
                    row.append($('<td class="text-right"></td>').text(current));
                    row.append($('<td class="text-right"></td>').text(target));
                    tbody.append(row);
                });
            }
        }

        $(document).keydown(function(e) {
            if (hasPersistentInterlockState()) {
                openInterlockModal();
                return;
            }
            if ($('#modalPisJpConfirmation').hasClass('show')) return;
            if ($('#modalPisLoadingListComplete').hasClass('show')) return;
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
                        if (pisHasPendingLabelPack()) {
                            $('#part_number_loading').hide();
                            $('#status-container').removeClass('alert-success').addClass('alert-warning');
                            $('#alert-header').html('<i class="fas fa-lock"></i> Menunggu Confirm Packing');
                            $('#alert-body').text('Selesaikan Confirm Packing untuk label yang sudah di-scan sebelum scan loading list lain.');
                            pisErrorSound();
                            showPisConfirmPackingInterlock('loading_list');
                            barcode = '';
                            return;
                        }
                        $('#detail_no').val(displayBarcode);
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
                        if (pisIsDockTti()) {
                            processKanbanScan(processBarcode);
                            $('#part_number_loading').hide();
                            $(window).scrollTop(_savedScrollTop);
                        } else if (isLikelyPisLoadingListBarcode(processBarcode)) {
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
            ttiScannedUniques = [];
            currentLoadingListNumber = '';
            lastScannedKanban = '';
            currentPreviewItem = null;
            lastScannedLabel = '';
            lastScannedLabelTime = 0;
            lastLabelCooldownStartTime = 0;
            clearSameLabelCountdown();
            barcode = '';
            pendingJpAction = null;
            pendingScanBarcode = null;
            pendingScanDisplayBarcode = null;
            jpConfirmBarcode = '';
            setPersistentInterlockState(false);
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

            setPersistentInterlockState(true);
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

        /**
         * TTI: scan kanban langsung cocokkan unique + part number ke Loading List (tanpa label).
         * Format: <WDT>G9C3TP|18|IDAII|85820-X7A00|85820X7A00|...
         */
        function processTtiKanbanScan(barcode) {
            var fullKanbanRaw = cleanBarcode(barcode || '');
            if (isLikelyPisLoadingListBarcode(fullKanbanRaw)) {
                scanLoadingList(fullKanbanRaw, stripLastTwoChars(fullKanbanRaw));
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            var parsed = pisParseTtiKanban(fullKanbanRaw);
            if (!parsed) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Format Kanban TTI Salah');
                $('#alert-body').text('Kanban TTI harus berformat pipe (|) dengan unique dan part number. Contoh: <WDT>G9C3TP|...|85820-X7A00|...');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            // Check if unique is already pending or scanned
            var isAlreadyPending = pendingLabelPacks.some(function(p) {
                return p.ttiUnique === parsed.unique;
            });
            if (isAlreadyPending || (parsed.unique && ttiScannedUniques.indexOf(parsed.unique) !== -1)) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-ban"></i> Kanban Sudah Di-scan');
                $('#alert-body').text('Unique kanban "' + parsed.unique + '" sudah tercatat dalam sesi ini atau sedang menunggu konfirmasi. Scan kanban lain.');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            var matched = pisFindTtiLlItem(parsed);
            if (!matched) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-times-circle"></i> Kanban Tidak Cocok LL');
                $('#alert-body').text(
                    'Unique "' + parsed.unique + '" / part "' + parsed.partNumber + '" tidak ditemukan di Loading List aktif atau qty sudah terpenuhi.'
                );
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            var rem = (matched.remaining != null ? matched.remaining : matched.total_qty || 0);
            var remNum = toQtyNumber(rem);
            if (remNum <= 0) {
                $('#status-container').removeClass('alert-success alert-primary').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-ban"></i> Kanban Sudah Terpenuhi');
                $('#alert-body').text('Part "' + (matched.part_number_cust || matched.part_number_int) + '" sudah mencapai target (Sisa: 0).');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            var partKey = getPartSessionKey(matched);
            if (partKey && partsStartedInCurrentSession.indexOf(partKey) === -1) {
                partsStartedInCurrentSession.push(partKey);
            }

            pendingLabelPacks.push({
                part_number_int: matched.part_number_int || '',
                part_number_cust: matched.part_number_cust || '',
                loading_list_number: (matched.loading_list_number || '').toString().trim(),
                rawLabel: fullKanbanRaw,
                ttiUnique: parsed.unique || ''
            });
            updatePendingPackingUI();

            $('#detail_no').val(parsed.unique || matched.part_number_cust || matched.part_number_int || '');

            if ((matched.part_number_cust || '').toString().trim()) {
                currentPreviewItem = matched;
                setPreviewImage(matched.part_number_int || '', matched.part_number_cust || '');
            } else {
                currentPreviewItem = null;
                clearPreviewImage();
            }

            var effAfterConfirm = Math.max(0, remNum - getPisConfirmedQty(matched));
            stage = 2; // TTI stays in stage 2 (kanban scan)
            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
            if (effAfterConfirm > 0) {
                $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK');
                $('#alert-body').text((matched.part_number_cust || matched.part_number_int) + ' di-scan. Setelah Confirm Packing, sisa: ' + effAfterConfirm + ' pcs.');
                pisOkSound();
            } else {
                $('#alert-header').html('<i class="fas fa-lock"></i> Kanban terakhir — wajib Confirm Packing');
                $('#alert-body').text('Ini kanban terakhir untuk part ini. Tekan Confirm Packing untuk menutup qty; scan kanban/loading list lain diblok sampai konfirmasi.');
                pisOkSound();
            }

            updateStepIndicator();
            $(window).scrollTop(_savedScrollTop);
        }

        // Stage 2: Scan kanban — simpan data untuk validasi label (label harus terkandung di data kanban)
        function processKanbanScan(barcode) {
            if (pisIsDockTti()) {
                processTtiKanbanScan(barcode);
                return;
            }
            if (pisHasPendingLabelPack()) {
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-lock"></i> Menunggu Confirm Packing');
                $('#alert-body').text('Ada label yang belum dikonfirmasi. Tekan Confirm Packing terlebih dahulu, lalu scan kanban baru.');
                pisErrorSound();
                showPisConfirmPackingInterlock('kanban');
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
            var fullKanbanUpper = fullKanbanRaw.toUpperCase();
            var extractedPart = extractCustomerPartFromKanban(fullKanbanRaw);
            var kanbanPart = extractedPart || '';

            if (!fullKanbanRaw) {
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Kanban Kosong');
                $('#alert-body').text('Data kanban tidak valid.');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            if (pisIsLikelyLabelOnlyBarcode(fullKanbanRaw, extractedPart)) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-tag"></i> Ini Label Part');
                $('#alert-body').text(
                    'Scan "' + (kanbanPart || fullKanbanRaw) + '" adalah label part, bukan kanban. '
                    + 'Scan kanban fisik (baris panjang yang memuat nomor Loading List).'
                );
                pisLabelInvalidSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            if (!pisKanbanContainsSessionLoadingList(fullKanbanRaw)) {
                var llHint = pisGetSessionLoadingListNumbers().join(', ') || '(belum ada)';
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-times-circle"></i> Kanban Tidak Cocok LL');
                $('#alert-body').text(
                    'Kanban harus memuat nomor Loading List sesi: ' + llHint + '. '
                    + 'Periksa apakah yang di-scan bukan label part saja.'
                );
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            if (!kanbanPart) {
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-times-circle"></i> Part Tidak di LL');
                $('#alert-body').text('Part di kanban tidak ditemukan di Loading List aktif.');
                pisErrorSound();
                $(window).scrollTop(_savedScrollTop);
                return;
            }

            var kanbanContent = fullKanbanUpper;

            // --- LOGIKA VALIDASI: Cek apakah Part di dalam Kanban ini sudah terpenuhi (QTY 0) ---
            var isAlreadyFinished = false;
            var matchedPartName = "";

            for (var i = 0; i < loadingListItems.length; i++) {
                var item = loadingListItems[i];
                var pInt = (item.part_number_int || '').toString().toUpperCase();
                var pCust = (item.part_number_cust || '').toString().toUpperCase();

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
                        scanned: kanbanPart
                    }
                });

                $(window).scrollTop(_savedScrollTop);
                var kanbanFullToApply = fullKanbanRaw;
                showJpConfirmationThen(function() {
                    // Interlock dibuka → anggap kanban ini sebagai konteks aktif BARU.
                    // Jangan biarkan state part sebelumnya memaksa user menyelesaikan part lama.
                    resetActiveKanbanContext();
                    lastScannedKanban = kanbanFullToApply;
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
            lastScannedKanban = fullKanbanRaw;
            stage = 3;
            updateStepIndicator();

            $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-warning');
            $('#alert-header').html('<i class="fas fa-check-circle"></i> Kanban OK');
            $('#alert-body').text(
                'Kanban diterima (LL + part ' + kanbanPart + '). Silahkan scan LABEL PART.'
            );
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

            if (pisHasPendingLabelPack()) {
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-lock"></i> Menunggu Confirm Packing');
                $('#alert-body').text('Tekan Confirm Packing sebelum scan label part berikutnya.');
                pisErrorSound();
                showPisConfirmPackingInterlock('label');
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

            var existsInKanban = (kanbanUpper.indexOf(rawUpper) !== -1 || kanbanUpper.indexOf(cleanUpper) !== -1)
                || pisLabelMatchesKanban(rawUpper, kanbanUpper)
                || pisLabelMatchesKanban(cleanUpper, kanbanUpper);
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
                pisLabelInvalidSound();

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

            // --- Cek tambahan: label harus exact match part di LL (setelah logika match lama tetap jalan) ---
            if (matched && !pisIsLabelStrictMatchWithLlItem(cleanLabel, raw, matched)) {
                var strictOnly = pisFindLlItemByStrictLabelMatch(cleanLabel, raw);
                if (strictOnly) {
                    matched = strictOnly;
                }
            }
            if (matched && !pisIsLabelStrictMatchWithLlItem(cleanLabel, raw, matched)) {
                var scannedShow = cleanLabel || raw;
                var llPartShow = pisDescribeLlPartForMismatch(matched);
                $('#status-container').removeClass('alert-success').addClass('alert-danger');
                $('#alert-header').html('<i class="fas fa-times-circle"></i> Label Tidak Cocok LL');
                $('#alert-body').text(
                    'Scan "' + scannedShow + '" tidak sama persis dengan part di Loading List ("' + llPartShow + '"). '
                    + 'Periksa karakter tambahan di akhir barcode (contoh: ...A14 vs ...A1, atau ...E1H vs ...E1).'
                );
                pisLabelInvalidSound();
                $.ajax({
                    url: '{{ url("error/store") }}',
                    type: 'GET',
                    data: {
                        source: 'pis',
                        message: 'Label scan tidak exact match dengan part Loading List',
                        expected: llPartShow,
                        scanned: scannedShow
                    }
                });
                $(window).scrollTop(_savedScrollTop);
                $('#part_number_loading').hide();
                return;
            }

            // 4. JIKA ITEM DITEMUKAN (MATCHED) — simpan ke antrean frontend (maks. 1 label sampai Confirm Packing)
            if (matched) {
                var rem = (matched.remaining != null ? matched.remaining : matched.total_qty || 0);
                var remNum = toQtyNumber(rem);
                if (remNum < 1) {
                    $('#status-container').removeClass('alert-success').addClass('alert-warning');
                    $('#alert-header').html('<i class="fas fa-exclamation-triangle"></i> Qty penuh');
                    $('#alert-body').text('Sisa qty untuk part ini sudah nol. Konfirmasi packing sebelumnya atau refresh data.');
                    pisLabelInvalidSound();
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

                if ((matched.part_number_cust || '').toString().trim()) {
                    currentPreviewItem = matched;
                    setPreviewImage(matched.part_number_int || '', matched.part_number_cust || '');
                } else {
                    currentPreviewItem = null;
                    clearPreviewImage();
                }

                // Sisa ditampilkan dalam pcs setelah satu kanban dikonfirmasi.
                var effAfterConfirm = Math.max(0, remNum - getPisConfirmedQty(matched));
                stage = 3;
                $('#status-container').removeClass('alert-danger alert-warning').addClass('alert-success');
                if (effAfterConfirm > 0) {
                    $('#alert-header').html('<i class="fas fa-check-circle"></i> Part OK');
                    $('#alert-body').text((matched.part_number_cust || matched.part_number_int) + ' di-scan. Setelah Confirm Packing, sisa: ' + effAfterConfirm + ' pcs.');
                    pisOkSound();
                } else {
                    $('#alert-header').html('<i class="fas fa-lock"></i> Label terakhir — wajib Confirm Packing');
                    $('#alert-body').text('Ini kanban terakhir untuk part ini. Tekan Confirm Packing untuk menutup qty; scan label/kanban/loading list lain diblok sampai konfirmasi.');
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
                pisLabelInvalidSound();
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
            if (pisHasPendingLabelPack()) {
                $('#part_number_loading').hide();
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-lock"></i> Menunggu Confirm Packing');
                $('#alert-body').text('Tekan Confirm Packing sebelum menambah atau mengganti loading list.');
                pisErrorSound();
                showPisConfirmPackingInterlock('loading_list');
                $(window).scrollTop(_savedScrollTop);
                return;
            }
            var rawScanned = cleanBarcode((barcode || '').toString());
            var loadingListNumberForDB = rawScanned.replace(/[\x00-\x1F\x7F]/g, '').trim().substr(0, 11);
            var loadingListNumberForApi = loadingListNumberForDB + ' A';

            function mapPisItemsFromDbOrApi(items) {
                return (items || []).map(function (it) {
                    var totalPcs = toQtyNumber(it.total_qty != null ? it.total_qty : (it.order_qty != null ? it.order_qty : 0));
                    var totalKanban = Math.max(0, toQtyNumber(it.total_kanban_qty));
                    var qtyPerKanban = Math.max(0, toQtyNumber(it.qty_per_kanban));
                    if (qtyPerKanban <= 0 && totalPcs > 0 && totalKanban > 0) {
                        qtyPerKanban = Math.ceil(totalPcs / totalKanban);
                    }
                    qtyPerKanban = Math.max(1, qtyPerKanban);
                    if (totalKanban <= 0 && totalPcs > 0) {
                        totalKanban = Math.ceil(totalPcs / qtyPerKanban);
                    }
                    var actualKanban = Math.max(0, toQtyNumber(it.actual_kanban_qty));
                    var actualPcs = Math.max(0, toQtyNumber(it.actual_qty != null
                        ? it.actual_qty
                        : Math.min(totalPcs, actualKanban * qtyPerKanban)));
                    var remainingPcs = Math.max(0, toQtyNumber(it.remaining != null
                        ? it.remaining
                        : totalPcs - actualPcs));
                    var remainingKanban = Math.max(0, toQtyNumber(it.remaining_kanban_qty != null
                        ? it.remaining_kanban_qty
                        : totalKanban - actualKanban));
                    return {
                        part_number_int: it.part_number_int || '',
                        part_number_cust: it.part_number_cust || '',
                        total_qty: totalPcs,
                        qty_per_kanban: qtyPerKanban,
                        total_kanban_qty: totalKanban,
                        actual_kanban_qty: actualKanban,
                        actual_qty: actualPcs,
                        remaining: remainingPcs,
                        remaining_kanban_qty: remainingKanban
                    };
                });
            }

            function mapPisItemsFromDeaApi(items) {
                return (items || []).map(function (it) {
                    var totalPcs = toQtyNumber(it.total_qty != null
                        ? it.total_qty
                        : (it.order_qty != null ? it.order_qty : (it.quantity != null ? it.quantity : 0)));
                    var totalKanban = Math.max(0, toQtyNumber(it.total_kanban_qty != null
                        ? it.total_kanban_qty
                        : (it.kanban_qty != null ? it.kanban_qty : 0)));
                    var qtyPerKanban = Math.max(0, toQtyNumber(
                        it.qty_per_kanban != null
                            ? it.qty_per_kanban
                            : (it.quantity_per_kanban != null
                                ? it.quantity_per_kanban
                                : (it.qty_per_pack != null ? it.qty_per_pack : 0))
                    ));
                    if (qtyPerKanban <= 0 && totalPcs > 0 && totalKanban > 0) {
                        qtyPerKanban = Math.ceil(totalPcs / totalKanban);
                    }
                    qtyPerKanban = Math.max(1, qtyPerKanban);
                    if (totalKanban <= 0 && totalPcs > 0) {
                        totalKanban = Math.ceil(totalPcs / qtyPerKanban);
                    }
                    var doneKanban = Math.max(0, toQtyNumber(it.actual_kanban_qty != null ? it.actual_kanban_qty : 0));
                    var donePcs = Math.max(0, toQtyNumber(it.actual_qty != null
                        ? it.actual_qty
                        : Math.min(totalPcs, doneKanban * qtyPerKanban)));
                    return {
                        part_number_int: it.part_number_int || '',
                        part_number_cust: it.part_number_cust || '',
                        total_qty: totalPcs,
                        qty_per_kanban: qtyPerKanban,
                        total_kanban_qty: totalKanban,
                        actual_kanban_qty: doneKanban,
                        actual_qty: donePcs,
                        remaining: Math.max(0, totalPcs - donePcs),
                        remaining_kanban_qty: Math.max(0, totalKanban - doneKanban)
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
                    ttiScannedUniques = [];
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
                        pisPdsBerbedaSound();
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
                    pisIsDockTti()
                        ? 'LL: ' + name + ' — total ' + nLl + ' LL. Scan kanban TTI (unique + part number); tekan Confirm Packing untuk mengupdate qty.'
                        : (fromExisting
                            ? 'LL (existing): ' + name + ' — total ' + nLl + ' LL. Tiap LL dipisah di tabel; scan kanban & label; Confirm Packing per label.'
                            : 'LL: ' + name + ' — total ' + nLl + ' LL. Scan kanban, lalu label; Confirm Packing untuk qty & counter.')
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

                        pisUpdateScanContextOnServer({
                            loading_list_number: res.loading_list_number,
                            pds_number: res.pds_number,
                            cycle: res.cycle,
                            delivery_date: res.delivery_date,
                            shipping_date: res.shipping_date,
                            customer_id: res.customer_id,
                            items: mapPisItemsFromDbOrApi(res.items)
                        });

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

                                pisUpdateScanContextOnServer({
                                    loading_list_number: loadingListNumberForDB,
                                    pds_number: payload.pds_number || payload.pds || '',
                                    cycle: payload.cycle || '',
                                    delivery_date: payload.delivery_date || '',
                                    shipping_date: payload.shipping_date || '',
                                    customer_id: payload.customer_id || null,
                                    customer_code: payload.customer_code || (payload.customer && payload.customer.code) || null,
                                    customer_name: payload.customer_name || (payload.customer && payload.customer.name) || null,
                                    items: mapped
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

        function pisGetFullscreenElement() {
            return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement || null;
        }

        function pisUpdateFullscreenButton() {
            var isFullscreen = !!pisGetFullscreenElement();
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

            if (pisGetFullscreenElement()) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
                return;
            }

            var root = document.documentElement;
            if (root.requestFullscreen) {
                root.requestFullscreen().catch(function () { });
            } else if (root.webkitRequestFullscreen) {
                root.webkitRequestFullscreen();
            } else if (root.msRequestFullscreen) {
                root.msRequestFullscreen();
            } else if (root.webkitRequestFullScreen) {
                root.webkitRequestFullScreen();
            }
        }

        var pisDelayBtnBusy = false;

        function releasePisDelayButtonVisual() {
            var $btn = $('#pis-btn-delay');
            $btn.blur();
            $btn.removeClass('active focus');
        }

        function onPisDelayButtonActivate() {
            if (pisDelayBtnBusy) return;
            pisDelayBtnBusy = true;
            setTimeout(function() { pisDelayBtnBusy = false; }, 500);
            resetPisState();
            releasePisDelayButtonVisual();
        }

        $('#pis-btn-delay').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            onPisDelayButtonActivate();
        });

        // Touchscreen: lepaskan state "pressed" agar tidak tampak terus ditekan / terbaca ganda
        $('#pis-btn-delay').on('mouseup touchend pointerup mouseleave', function() {
            releasePisDelayButtonVisual();
        });

        // Scanner Enter: cegah aktivasi ulang tombol, lepas fokus, tetap teruskan ke handler scan document
        $('#pis-btn-delay').on('keydown', function(e) {
            var code = e.keyCode || e.which;
            if (code === 13) {
                e.preventDefault();
                releasePisDelayButtonVisual();
            } else if (code === 32) {
                e.preventDefault();
            }
        });

        $('#pis-btn-fullscreen').on('click', function() {
            $(this).blur();
            pisToggleFullscreen();
        });

        // Beberapa browser touchscreen/webview kurang konsisten pada event click.
        $('#pis-btn-fullscreen').on('touchend pointerup', function(e) {
            e.preventDefault();
            $(this).blur();
            pisToggleFullscreen();
        });

        document.addEventListener('fullscreenchange', function() {
            pisFullscreenTransitionLock = false;
            pisUpdateFullscreenButton();
        });
        document.addEventListener('webkitfullscreenchange', function() {
            pisFullscreenTransitionLock = false;
            pisUpdateFullscreenButton();
        });
        document.addEventListener('MSFullscreenChange', function() {
            pisFullscreenTransitionLock = false;
            pisUpdateFullscreenButton();
        });

        $('#pis-btn-confirm-packing').on('click', function () {
            if (!pendingLabelPacks.length) return;
            confirmPacking();
        });

        $('#pis-btn-ll-complete-ok').on('click', function () {
            $('#modalPisLoadingListComplete').modal('hide');
        });

        $('#modalPisLoadingListComplete').on('hidden.bs.modal', function () {
            if (typeof pisLoadingListCompleteCallback === 'function') {
                var cb = pisLoadingListCompleteCallback;
                pisLoadingListCompleteCallback = null;
                cb();
            }
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
                    setPersistentInterlockState(false);
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
            pisRefreshDockSpecificUi();
            pisUpdateFullscreenButton();

            var $defaultDock = $('#dock').find('button.pis-dock-btn.btn-primary').first();
            if ($defaultDock.length) {
                func_change_dock($defaultDock[0]);
            }
            
            // Nonaktifkan niceScroll yang mungkin menyebabkan auto-scroll
            if($.fn.niceScroll) {
                $('html, body').getNiceScroll().remove();
            }
            
            // Prevent auto-scroll to input fields
            $('input, select, textarea').off('focus').on('focus', function(e) {
                // Tidak melakukan scroll otomatis saat focus
                e.preventDefault();
            });

            if (hasPersistentInterlockState()) {
                pendingJpAction = null;
                $('#status-container').removeClass('alert-success').addClass('alert-warning');
                $('#alert-header').html('<i class="fas fa-lock"></i> Interlock Aktif');
                $('#alert-body').text('Interlock masih aktif. Silahkan scan barcode JP/Leader untuk melanjutkan.');
                openInterlockModal();
            }
        });
    </script>
@endsection
