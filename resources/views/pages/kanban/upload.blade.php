@extends('layouts.root.main')

@section('main')
    @php
        $customer = strtoupper($customer ?? request()->query('customer', 'TMMIN'));
        if (!in_array($customer, ['TMMIN', 'MMKI', 'ALL'], true)) {
            $customer = 'TMMIN';
        }
        $isTmmin = $customer === 'TMMIN';
        $isMmki = $customer === 'MMKI';
        $isAll = $customer === 'ALL';
        $customerTitle = $customer;
        $customerSub = $isTmmin
            ? 'PT. Toyota Motor Manufacturing Indonesia'
            : ($isMmki
                ? 'PT. Mitsubishi Motors Krama Yudha Indonesia'
                : 'Mode manual — upload PDF, atur garis merah, crop per garis');
    @endphp

    <style>
        .main-content>.section {
            margin-top: -94px !important;
            position: relative;
            z-index: 2;
        }

        #customerNav {
            margin-top: 10px !important;
        }
    </style>

    <div class="container-fluid">

        {{-- NAV customer (statis: TMMIN, MMKI & MANUAL). Tab MANUAL = customer ALL, khusus manual crop. --}}
        <!-- <ul class="nav nav-pills mb-3" id="customerNav">
            <li class="nav-item">
                <a class="nav-link {{ $isTmmin ? 'active' : '' }}"
                    href="{{ route('dashboard.kanbanUpload', ['customer' => 'TMMIN']) }}">
                    <i class="fas fa-industry"></i> TMMIN

                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $isMmki ? 'active' : '' }}"
                    href="{{ route('dashboard.kanbanUpload', ['customer' => 'MMKI']) }}">
                    <i class="fas fa-industry"></i> MMKI

                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $isAll ? 'active' : '' }}"
                    href="{{ route('dashboard.kanbanUpload', ['customer' => 'ALL']) }}">
                    <i class="fas fa-cut"></i> MANUAL

                </a>
            </li>
        </ul> -->



        {{-- Form auto (TMMIN/MMKI). Disembunyikan saat MANUAL karena MANUAL khusus manual crop. DOM tetap ada (hidden) agar JS lama tidak error. --}}
        <div class="card" @if($isAll) style="display:none;" @endif>
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-file-pdf"></i>
                    Form Upload — {{ $customer }}
                </h4>
                <small class="text-muted">{{ $customerSub }}</small>
            </div>

            <div class="card-body" id="kanbanCardBody">

                {{-- STEPPER --}}
                <div class="kanban-steps mb-4" id="kanbanSteps">
                    <div class="kstep" data-step="1">
                        <div class="kdot"><span>1</span></div>
                        <div class="klabel">Uploading<small>Kirim PDF ke server</small></div>
                    </div>
                    <div class="kline"></div>
                    <div class="kstep" data-step="2">
                        <div class="kdot"><span>2</span></div>
                        <div class="klabel">Checking<small>Validasi PDF</small></div>
                    </div>
                    <div class="kline"></div>
                    <div class="kstep" data-step="3">
                        <div class="kdot"><span>3</span></div>
                        <div class="klabel">Generating<small>Split &amp; generate</small></div>
                    </div>
                    <div class="kline"></div>
                    <div class="kstep" data-step="4">
                        <div class="kdot"><span>4</span></div>
                        <div class="klabel">Download<small>Ambil hasil</small></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Select Kanban PDF ({{ $customer }})</label>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <input type="file" id="files" multiple accept="application/pdf,.pdf" class="form-control">
                        <!-- <button id="addFilesBtn" type="button" class="btn btn-outline-primary text-nowrap">
                            <i class="fas fa-plus"></i> Add File
                        </button> -->
                    </div>
                    <small class="text-muted">
                        Only PDF file allowed. Bisa pilih berkali-kali — file akan menumpuk di daftar. Maks 20MB per file.
                    </small>
                </div>

                <div id="fileList" class="mb-3"></div>

                <div id="fileLoading" class="d-none align-items-center mb-3" style="gap:8px;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">Memuat...</span>
                    </div>
                    <small class="text-muted">Memuat file yang dipilih...</small>
                </div>

                <div class="progress mb-3 d-none" id="uploadProgressWrap" style="height:22px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="uploadProgressBar"
                        role="progressbar" style="width:0%">0%</div>
                </div>

                <div class="action-row">
                    <div class="action-left">
                        <button id="upload" class="btn btn-primary btn-equal">
                            <i class="fas fa-upload"></i> Process
                        </button>
                        <button id="cancelBtn" class="btn btn-outline-danger btn-equal" type="button" disabled>
                            <i class="fas fa-ban"></i> Cancel
                        </button>
                        <button id="resetBtn" class="btn btn-outline-secondary btn-equal" type="button">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    <div class="action-right">
                        <span id="download"></span>
                        <button id="doneBtn" class="btn btn-outline-success btn-equal d-none" type="button">
                            <i class="fas fa-check"></i> Done
                        </button>
                    </div>
                </div>

                <div id="stepStatus" class="mt-3">
                    <div class="alert alert-secondary mb-0" id="statusBox">
                        <strong>Status:</strong> <span id="statusText">Siap. Pilih PDF lalu klik Process.</span>
                        <span id="elapsed" class="float-right text-muted"></span>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===== MANUAL CROP (khusus tab MANUAL, customer=ALL) ===== --}}
        @if($isAll)
        <div class="card" id="allManualCard">
            
            <div class="card-body">
                {{-- Daftar button master (preset tersimpan) — di atas form upload, klik untuk load garis. --}}
                <div class="allm-preset-wrap mb-3">
                    <div id="allManualPresetBar" class="d-flex flex-wrap" style="gap:8px;">
                        <small class="text-muted">Memuat master...</small>
                    </div>
                </div>

                {{-- Stepper manual: Setup → Process → Download --}}
                <div class="kanban-steps mb-4" id="allManualSteps">
                    <div class="kstep" data-step="1">
                        <div class="kdot"><span>1</span></div>
                        <div class="klabel">Setup<small>Pilih PDF & garis</small></div>
                    </div>
                    <div class="kline"></div>
                    <div class="kstep" data-step="2">
                        <div class="kdot"><span>2</span></div>
                        <div class="klabel">Process<small>Upload & crop</small></div>
                    </div>
                    <div class="kline"></div>
                    <div class="kstep" data-step="3">
                        <div class="kdot"><span>3</span></div>
                        <div class="klabel">Download<small>Ambil hasil</small></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Pilih PDF Kanban</label>
                    <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                        <input type="file" id="allManualFilesInput" multiple accept="application/pdf,.pdf" class="form-control" style="max-width:420px;">
                        <button id="allManualResetPreviewBtn" class="btn btn-outline-secondary btn-sm" type="button">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    <small class="text-muted">Hanya PDF, maks 20MB per file. Preview hanya 5 halaman pertama agar ringan — garis page 1 berlaku ke semua halaman.</small>
                </div>
                {{-- Daftar file + proses/download satuan per file (proses gabungan tetap lewat tombol Process Manual). --}}
                <div id="allManualFileRows" class="mb-3"></div>
                {{-- Checkbox disembunyikan: copy garis, auto-lanjut, dan buang blank kini permanent logic (selalu ON, hanya backend yang tahu). --}}
                <input type="checkbox" id="allManualSyncChk" checked style="display:none;">
                <input type="checkbox" id="allManualAutoChk" checked style="display:none;">
                <input type="checkbox" id="allManualBlankChk" checked style="display:none;">
                {{-- Tombol di atas agar mudah dijangkau --}}
                <div class="action-row">
                    <div class="action-left">
                        <button id="allManualProcessBtn" class="btn btn-primary btn-equal" type="button">
                            <i class="fas fa-cut"></i> Process
                        </button>
                        <button id="allManualCancelBtn" class="btn btn-outline-primary btn-equal" type="button" disabled>
                            <i class="fas fa-ban"></i> Cancel
                        </button>
                    </div>
                    <div class="action-right">
                        <span id="allManualDownload"></span>
                    </div>
                </div>
                <div class="progress my-3 d-none" id="allManualProgressWrap" style="height:22px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="allManualProgressBar" role="progressbar" style="width:0%">0%</div>
                </div>
                <div class="mb-3 pt-4">
                    <div class="alert alert-secondary mb-0" id="allManualStatusBox">
                        <strong>Status manual:</strong> <span id="allManualStatusText">Siap. Pilih PDF lalu klik Process Manual.</span>
                    </div>
                </div>
                {{-- Preview paling terakhir --}}
                <hr>
                <h5 class="mb-2"><i class="fas fa-eye"></i> Preview & Atur Garis</h5>
                <div id="allManualEmpty"><small class="text-muted">Belum ada preview. Pilih PDF dulu.</small></div>
                <div id="allManualList"></div>
                {{-- Save master — setelah preview, simpan garis saat ini sebagai master baru. --}}
                <div class="allm-preset-wrap mt-3">
                    <label class="mb-1" style="font-size:13px;font-weight:800;">Simpan Master <small class="text-muted" style="font-weight:600;">— atur garis di preview lalu Save untuk simpan preset</small></label>
                    <div class="d-flex flex-wrap mt-2" style="gap:8px;">
                        <input type="text" id="allManualPresetName" class="form-control" placeholder="ex: AISIN" style="max-width:220px;text-transform:uppercase;" maxlength="50">
                        <button id="allManualPresetSaveBtn" class="btn btn-outline-primary btn-sm" type="button">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <style>
        #customerNav {
            margin-top: 5px;
        }

        #customerNav .nav-link {
            border-radius: 14px;
            font-weight: 800;
            color: #294795;
            background: #fff;
            border: 1px solid #DDE3EF;
            margin-right: 8px;
            min-width: 170px;
        }

        #customerNav .nav-link.active {
            background: linear-gradient(135deg, #294795, #0070B7);
            color: #fff;
            border-color: transparent;
        }

        #customerNav .nav-link small {
            opacity: .75;
        }

        .kanban-steps {
            display: flex;
            align-items: flex-start;
            gap: 0;
        }

        .kstep {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 110px;
            flex: 0 0 auto;
            text-align: center;
        }

        .kdot {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 850;
            background: #EEF2F7;
            color: #8A97AD;
            border: 2px solid #DDE3EF;
            transition: all .2s ease;
        }

        .kdot span {
            line-height: 1;
        }

        .klabel {
            margin-top: 8px;
            font-weight: 800;
            font-size: 13px;
            color: #6B7A99;
        }

        .klabel small {
            display: block;
            font-weight: 600;
            font-size: 11px;
            color: #9AA7BC;
        }

        .kline {
            flex: 1 1 auto;
            height: 2px;
            background: #DDE3EF;
            margin: 21px 8px 0;
            border-radius: 2px;
        }

        .kstep.active .kdot {
            background: linear-gradient(135deg, #294795, #0097D8);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 10px 24px rgba(0, 112, 183, .28);
            animation: kpulse 1.2s infinite;
        }

        .kstep.active .klabel {
            color: #1A2340;
        }

        .kstep.done .kdot {
            background: #28a745;
            border-color: #28a745;
            color: #fff;
        }

        .kstep.done .kdot span::after {
            content: '';
        }

        .kstep.done .klabel {
            color: #1A2340;
        }

        .kstep.failed .kdot {
            background: #DC2626;
            border-color: #DC2626;
            color: #fff;
        }

        .kstep.failed .klabel {
            color: #B91C1C;
        }

        @keyframes kpulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.07);
            }

            100% {
                transform: scale(1);
            }
        }

        #fileList .file-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            border: 1px solid #DDE3EF;
            border-radius: 10px;
            margin-bottom: 6px;
            background: #F8FAFF;
            font-size: 13px;
        }

        .action-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-right {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
            flex-wrap: wrap;
        }

        .btn-equal {
            min-width: 170px;
        }

        .btn-equal:disabled,
        #addFilesBtn:disabled,
        #fileList button:disabled {
            cursor: not-allowed !important;
            opacity: .5 !important;
        }

        #files:disabled {
            cursor: not-allowed;
            background: #F1F5F9;
        }

        #kanbanCardBody.is-locked #fileList {
            opacity: .65;
        }

        #kanbanCardBody.is-locked .file-row {
            background: #F1F5F9;
        }

        @media (max-width: 640px) {
            .kstep {
                min-width: 70px;
            }

            .klabel {
                font-size: 11px;
            }
        }
    </style>

    <script>
        (function() {
            const CUSTOMER = @json($customer);
            const filesInput = document.getElementById('files');
            const addFilesBtn = document.getElementById('addFilesBtn');
            const fileList = document.getElementById('fileList');
            const uploadBtn = document.getElementById('upload');
            const cancelBtn = document.getElementById('cancelBtn');
            const resetBtn = document.getElementById('resetBtn');
            const doneBtn = document.getElementById('doneBtn');
            const statusText = document.getElementById('statusText');
            const statusBox = document.getElementById('statusBox');
            const elapsedEl = document.getElementById('elapsed');
            const progressWrap = document.getElementById('uploadProgressWrap');
            const progressBar = document.getElementById('uploadProgressBar');
            const downloadBox = document.getElementById('download');
            const cardBody = document.getElementById('kanbanCardBody');
            const lockNote = document.getElementById('lockNote');
            const steps = Array.from(document.querySelectorAll('#kanbanSteps .kstep'));

            let pollTimer = null;
            let elapsedTimer = null;
            let startTs = null;
            let selectedFiles = [];
            let isBusy = false;
            let currentXhr = null;
            let currentJobId = null;
            let cancelRequested = false;
            let navGuard = false;

            function setBusy(busy) {
                isBusy = busy;
                if (uploadBtn) uploadBtn.disabled = busy;
                if (resetBtn) resetBtn.disabled = busy;
                if (addFilesBtn) addFilesBtn.disabled = busy;
                if (filesInput) filesInput.disabled = busy;
                if (cancelBtn) cancelBtn.disabled = !busy;
                if (fileList) fileList.querySelectorAll('button').forEach(function(b) {
                    b.disabled = busy;
                    b.setAttribute('aria-disabled', busy ? 'true' : 'false');
                });
                if (cardBody) cardBody.classList.toggle('is-locked', busy);
                if (lockNote) lockNote.classList.toggle('d-none', !busy);
            }

            function showDone(show) {
                if (doneBtn) doneBtn.classList.toggle('d-none', !show);
            }

            // Tidak ada lagi yang bisa dibatalkan (DONE/timeout/gagal): kunci tombol Cancel.
            function lockCancel() {
                if (cancelBtn) cancelBtn.disabled = true;
            }

            function fullReset(msg, kind) {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                const pendingXhr = currentXhr;
                currentXhr = null;
                if (pendingXhr) {
                    try {
                        pendingXhr.abort();
                    } catch (e) {}
                }
                currentJobId = null;
                cancelRequested = false;
                navGuard = false;
                stopElapsed();
                elapsedEl.textContent = '';
                filesInput.value = '';
                selectedFiles = [];
                renderFiles();
                progressWrap.classList.add('d-none');
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                downloadBox.innerHTML = '';
                showDone(false);
                setBusy(false);
                setStep({
                    active: 0,
                    done: [],
                    failed: null
                });
                setStatus(msg || 'Siap. Pilih PDF lalu klik Process.', kind || 'secondary');
            }

            function setStep(state) {
                // state: {active: n, done: [..], failed: n|null}
                steps.forEach(function(el) {
                    const n = parseInt(el.getAttribute('data-step'), 10);
                    el.classList.remove('active', 'done', 'failed');
                    const dot = el.querySelector('.kdot span');
                    dot.innerHTML = String(n);
                    if (state.failed === n) {
                        el.classList.add('failed');
                        dot.innerHTML = '<i class="fas fa-times"></i>';
                    } else if (state.done.includes(n)) {
                        el.classList.add('done');
                        dot.innerHTML = '<i class="fas fa-check"></i>';
                    } else if (state.active === n) {
                        el.classList.add('active');
                    }
                });
            }

            function setStatus(msg, kind) {
                statusText.innerHTML = msg;
                statusBox.className = 'alert mb-0 alert-' + (kind || 'secondary');
            }

            function setProgress(pct) {
                const v = Math.max(0, Math.min(100, Math.round(pct)));
                progressBar.style.width = v + '%';
                progressBar.textContent = v + '%';
            }

            function startElapsed() {
                stopElapsed();
                startTs = Date.now();
                elapsedTimer = setInterval(function() {
                    const s = Math.floor((Date.now() - startTs) / 1000);
                    elapsedEl.textContent = s + 's';
                }, 500);
            }

            function stopElapsed() {
                if (elapsedTimer) {
                    clearInterval(elapsedTimer);
                    elapsedTimer = null;
                }
            }

            function renderFiles() {
                fileList.innerHTML = '';
                if (selectedFiles.length === 0) {
                    fileList.innerHTML = '<small class="text-muted">Belum ada file dipilih.</small>';
                    return;
                }
                selectedFiles.forEach(function(f, idx) {
                    const row = document.createElement('div');
                    row.className = 'file-row';
                    const ok = /\.pdf$/i.test(f.name) && f.type !== '' ? true : /\.pdf$/i.test(f.name);
                    row.innerHTML = '<span><i class="fas fa-file-pdf text-danger"></i> ' +
                        '<strong></strong> <span class="text-muted"></span></span>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger">Hapus</button>';
                    row.querySelector('strong').textContent = f.name;
                    row.querySelector('.text-muted').textContent = ' (' + (f.size / 1024 / 1024).toFixed(2) +
                        ' MB' + (ok ? '' : ' · bukan PDF?') + ')';
                    row.querySelector('button').addEventListener('click', function() {
                        if (isBusy) return;
                        selectedFiles.splice(idx, 1);
                        renderFiles();
                    });
                    if (isBusy) row.querySelector('button').disabled = true;
                    fileList.appendChild(row);
                });
            }

            if (filesInput) filesInput.addEventListener('change', function() {
                if (isBusy) {
                    filesInput.value = '';
                    return;
                }
                const incoming = Array.from(filesInput.files || []);
                filesInput.value = '';
                if (incoming.length === 0) return;

                // Tampilkan loading dulu agar spinner sempat ter-render sebelum proses file.
                const fileLoading = document.getElementById('fileLoading');
                if (fileLoading) {
                    fileLoading.classList.remove('d-none');
                    fileLoading.classList.add('d-flex');
                }
                setStatus('Memuat ' + incoming.length + ' file...', 'info');

                setTimeout(function() {
                    // Hanya PDF yang diterima — file lain dilewati.
                    const pdfs = [];
                    const rejected = [];
                    incoming.forEach(function(f) {
                        if (/\.pdf$/i.test(f.name)) pdfs.push(f);
                        else rejected.push(f.name);
                    });
                    // Tambah (append), bukan ganti — jadi bisa upload 1 lalu tambah 1 lagi.
                    pdfs.forEach(function(f) {
                        const dup = selectedFiles.some(function(e) {
                            return e.name === f.name && e.size === f.size &&
                                e.lastModified === f.lastModified;
                        });
                        if (!dup) selectedFiles.push(f);
                    });
                    renderFiles();
                    if (fileLoading) {
                        fileLoading.classList.add('d-none');
                        fileLoading.classList.remove('d-flex');
                    }
                    setStep({
                        active: 0,
                        done: [],
                        failed: null
                    });
                    setStatus('Siap. ' + selectedFiles.length + ' file dipilih (' + CUSTOMER + '). Klik Process.',
                        'secondary');
                    if (rejected.length > 0) {
                        alert('Hanya file PDF yang diterima. Dilewati:\n- ' + rejected.join('\n- '));
                    }
                    downloadBox.innerHTML = '';
                }, 200);
            });

            if (addFilesBtn) addFilesBtn.addEventListener('click', function() {
                if (isBusy) return;
                filesInput.click();
            });

            if (resetBtn) resetBtn.addEventListener('click', function() {
                if (isBusy) return;
                fullReset('Direset. Pilih PDF lalu klik Process.');
            });

            if (doneBtn) doneBtn.addEventListener('click', function() {
                fullReset('Selesai. Silakan upload lagi dari awal.');
            });

            function showCancelled() {
                // Cancel = stop semua + reset otomatis (tanpa perlu klik Done).
                fullReset(
                    'Dibatalkan total (' + CUSTOMER +
                    '). File sisa & antrean dibersihkan. Silakan mulai lagi dari awal.',
                    'warning'
                );
            }

            if (cancelBtn) cancelBtn.addEventListener('click', async function() {
                if (!isBusy) return;
                if (!confirm('Batalkan total proses ini? File sisa & antrean akan dibersihkan.')) return;
                cancelRequested = true;

                // 1) Setop sisi browser: upload yang jalan + polling status.
                if (currentXhr) {
                    try {
                        currentXhr.abort();
                    } catch (e) {}
                    currentXhr = null;
                }
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }

                // 2) Setop sisi server: tandai job + bersihkan file & antrean.
                if (currentJobId) {
                    try {
                        await fetch('/dashboard/kanban/cancel/' + currentJobId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error(e);
                    }
                    currentJobId = null;
                }

                showCancelled();
            });

            function pollStatus(jobId) {
                let attempts = 0;
                const maxAttempts = 240; // ~10 menit @2.5s
                // Bobot progres: upload 0-30%, checking ~35%, generating merangkak 35% -> 95%.
                // 100% hanya saat DONE agar bar jujur mengikuti proses server.
                const creepGenerating = function() {
                    const cur = parseFloat(progressBar.style.width) || 35;
                    if (cur < 95) {
                        setProgress(cur + (95 - cur) * 0.06);
                    }
                };
                pollTimer = setInterval(async function() {
                    attempts++;
                    try {
                        const res = await fetch('/dashboard/kanban/status/' + jobId);
                        const st = await res.json();
                        if (st.status === 'CANCELLED') {
                            showCancelled();
                        } else if (st.status === 'DONE') {
                            clearInterval(pollTimer);
                            pollTimer = null;
                            stopElapsed();
                            setStep({
                                active: 0,
                                done: [1, 2, 3, 4],
                                failed: null
                            });
                            setStatus('Selesai (' + CUSTOMER + '). File siap diunduh.', 'success');
                            navGuard = false;
                            progressBar.style.width = '100%';
                            progressBar.textContent = '100%';
                            downloadBox.innerHTML = '<a href="' + st.download + '"' +
                                ' download class="btn btn-success">' +
                                '<i class="fas fa-download"></i> Download (' + CUSTOMER + ')</a>';
                            lockCancel();
                            showDone(true);
                        } else {
                            creepGenerating();
                            setStatus('Generating (' + CUSTOMER + ')... job <code>' + jobId +
                                '</code> · cek ke-' + attempts, 'info');
                            if (attempts >= maxAttempts) {
                                clearInterval(pollTimer);
                                pollTimer = null;
                                stopElapsed();
                                setStep({
                                    active: 0,
                                    done: [1, 2],
                                    failed: 3
                                });
                                navGuard = false;
                                setStatus(
                                    'Timeout: proses lebih dari ~10 menit. Klik Done untuk reset lalu coba lagi.',
                                    'danger');
                                lockCancel();
                                showDone(true);
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        if (attempts >= 5) {
                            clearInterval(pollTimer);
                            pollTimer = null;
                            stopElapsed();
                            setStep({
                                active: 0,
                                done: [1],
                                failed: 3
                            });
                            navGuard = false;
                            setStatus('Gagal memantau status job. Klik Done untuk reset lalu coba lagi.',
                                'danger');
                            lockCancel();
                            showDone(true);
                        }
                    }
                }, 2500);
            }

            if (uploadBtn) uploadBtn.addEventListener('click', function() {
                if (isBusy) return;
                if (selectedFiles.length === 0) {
                    alert('Please select PDF file');
                    return;
                }
                for (const f of selectedFiles) {
                    if (!/\.pdf$/i.test(f.name)) {
                        alert('File harus PDF: ' + f.name);
                        return;
                    }
                    if (f.size > 20 * 1024 * 1024) {
                        alert('File melebihi 20MB: ' + f.name);
                        return;
                    }
                }

                const formData = new FormData();
                selectedFiles.forEach(function(f) {
                    formData.append('files[]', f);
                });
                formData.append('customer', CUSTOMER);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                uploadBtn.disabled = true;
                downloadBox.innerHTML = '';
                showDone(false);
                currentJobId = null;
                cancelRequested = false;
                setBusy(true);
                navGuard = true;
                progressWrap.classList.remove('d-none');
                startElapsed();

                // STEP 1: uploading
                setStep({
                    active: 1,
                    done: [],
                    failed: null
                });
                setStatus('Step 1/4 — Uploading (' + CUSTOMER + ')...', 'info');

                const xhr = new XMLHttpRequest();
                currentXhr = xhr;
                xhr.open('POST', "{{ route('dashboard.kanbanUploadProcess') }}", true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'));
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        // Upload hanya berbobot 0-30% dari total proses.
                        const pct = Math.round(e.loaded / e.total * 30);
                        setProgress(pct);
                    }
                });

                xhr.addEventListener('abort', function() {
                    currentXhr = null;
                    if (cancelRequested) return; // sudah ditangani tombol Cancel
                    if (!isBusy) return;
                    fullReset('Upload dibatalkan browser. Silakan mulai lagi dari awal.', 'warning');
                });

                xhr.onreadystatechange = function() {
                    if (xhr.readyState !== 4) return;
                    currentXhr = null;
                    if (cancelRequested) return; // sudah ditangani tombol Cancel
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let result;
                        try {
                            result = JSON.parse(xhr.responseText);
                        } catch (e) {
                            result = null;
                        }
                        if (result && result.status) {
                            // STEP 2: checking (validasi lolos di server)
                            setStep({
                                active: 2,
                                done: [1],
                                failed: null
                            });
                            setStatus('Step 2/4 — Checking: validasi PDF lolos (' + (result.customer ||
                                CUSTOMER) + '). Lanjut generating...', 'info');
                            setProgress(35);
                            // STEP 3: generating (polling)
                            setTimeout(function() {
                                if (cancelRequested) return;
                                currentJobId = result.job_id;
                                setStep({
                                    active: 3,
                                    done: [1, 2],
                                    failed: null
                                });
                                setStatus('Step 3/4 — Generating (' + CUSTOMER + ')...', 'info');
                                pollStatus(result.job_id);
                            }, 600);
                        } else {
                            stopElapsed();
                            navGuard = false;
                            setStep({
                                active: 0,
                                done: [],
                                failed: 2
                            });
                            setStatus('Checking gagal: ' + (result && result.message ? result.message :
                                'response tidak valid.'), 'danger');
                            showDone(true);
                        }
                    } else {
                        let msg = 'Upload gagal (HTTP ' + xhr.status + ').';
                        try {
                            const j = JSON.parse(xhr.responseText);
                            if (j.message) msg += ' ' + j.message;
                        } catch (e) {}
                        stopElapsed();
                        navGuard = false;
                        setStep({
                            active: 0,
                            done: [],
                            failed: 1
                        });
                        setStatus(msg, 'danger');
                        showDone(true);
                    }
                };

                xhr.onerror = function() {
                    stopElapsed();
                    navGuard = false;
                    setStep({
                        active: 0,
                        done: [],
                        failed: 1
                    });
                    setStatus('System error saat uploading. Klik Done untuk reset.', 'danger');
                    showDone(true);
                };

                xhr.send(formData);
            });

            renderFiles();
            setStep({
                active: 0,
                done: [],
                failed: null
            });

            // Cegah pindah tab / reload tidak sengaja HANYA saat proses berjalan.
            // Setelah DONE/gagal/timeout/batal, navigasi bebas (termasuk klik Download).
            window.addEventListener('beforeunload', function(e) {
                if (navGuard) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        })();
    </script>

    {{-- ===== ALL MANUAL CROP assets (TERISOLASI, hanya di tab ALL) ===== --}}
    @if($isAll)
    <style>
        #allManualList { display: flex; flex-direction: column; gap: 16px; }
        .allm-page { border: 1px solid #BFDBFE; border-radius: 14px; overflow: hidden; background: #EFF6FF; }
        .allm-page-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 10px 12px; background: #DBEAFE; border-bottom: 1px solid #BFDBFE; font-size: 13px; font-weight: 800; color: #1E3A8A; }
        .allm-page-head .btn { min-width: 0; }
        .allm-canvas-wrap { position: relative; margin: 12px auto; width: fit-content; max-width: 100%; box-shadow: 0 10px 28px rgba(37,99,235,.14); background: #fff; }
        .allm-canvas-wrap canvas { display: block; max-width: 100%; height: auto; }
        .allm-overlay { position: absolute; inset: 0; cursor: crosshair; touch-action: none; }
        .allm-line { position: absolute; left: 0; right: 0; height: 0; border-top: 3px solid #2563EB; cursor: ns-resize; touch-action: none; z-index: 5; filter: drop-shadow(0 2px 4px rgba(37,99,235,.5)); }
        .allm-line::before { content: ''; position: absolute; left: 0; right: 0; top: -14px; height: 28px; }
        .allm-line-tag { position: absolute; right: 6px; top: -15px; display: flex; align-items: center; gap: 6px; background: #2563EB; color: #fff; font-size: 11px; font-weight: 800; border-radius: 999px; padding: 3px 6px 3px 10px; white-space: nowrap; }
        .allm-line-tag button { border: 0; border-radius: 999px; background: #fff; color: #2563EB; font-weight: 900; width: 26px; height: 26px; line-height: 1; font-size: 15px; cursor: pointer; touch-action: manipulation; }
        .allm-line, .allm-line-tag { user-select: none; -webkit-user-select: none; }
        .allm-line.auto { border-top: 3px dashed #F59E0B; cursor: default; filter: drop-shadow(0 2px 4px rgba(245,158,11,.5)); }
        .allm-line-tag.auto { background: #F59E0B; }
        .allm-page.is-master { border-color: #2563EB; box-shadow: 0 0 0 2px rgba(37,99,235,.25); }
        .allm-page.is-locked .allm-overlay { cursor: not-allowed; }
        .allm-master-badge { background: #2563EB; color: #fff; font-size: 10px; font-weight: 900; border-radius: 999px; padding: 2px 10px; }
        .allm-lock-badge { background: #6B7A99; color: #fff; font-size: 10px; font-weight: 900; border-radius: 999px; padding: 2px 10px; }
        .allm-drop { position: absolute; left: 0; right: 0; z-index: 4; pointer-events: none;
            background: repeating-linear-gradient(45deg, rgba(37,99,235,.22) 0 8px, rgba(37,99,235,.07) 8px 16px);
            border-top: 1px dashed rgba(37,99,235,.65); border-bottom: 1px dashed rgba(37,99,235,.65);
            display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .allm-drop span { background: rgba(37,99,235,.92); color: #fff; font-size: 10px; font-weight: 800; border-radius: 999px; padding: 1px 8px; white-space: nowrap; }
        .allm-vline { position: absolute; top: 0; bottom: 0; width: 0; border-left: 3px solid #16A34A; cursor: ew-resize; touch-action: none; z-index: 5; filter: drop-shadow(2px 0 4px rgba(22,163,74,.5)); }
        .allm-vline::before { content: ''; position: absolute; top: 0; bottom: 0; left: -14px; width: 28px; }
        .allm-vline-tag { position: absolute; top: 6px; left: 6px; display: flex; align-items: center; gap: 6px; background: #16A34A; color: #fff; font-size: 11px; font-weight: 800; border-radius: 999px; padding: 3px 6px 3px 10px; white-space: nowrap; }
        .allm-vline-tag button { border: 0; border-radius: 999px; background: #fff; color: #16A34A; font-weight: 900; width: 26px; height: 26px; line-height: 1; font-size: 15px; cursor: pointer; touch-action: manipulation; }
        .allm-vline, .allm-vline-tag { user-select: none; -webkit-user-select: none; }
        .allm-vdrop { position: absolute; top: 0; bottom: 0; z-index: 4; pointer-events: none;
            background: repeating-linear-gradient(-45deg, rgba(22,163,74,.22) 0 8px, rgba(22,163,74,.07) 8px 16px);
            border-left: 1px dashed rgba(22,163,74,.65); border-right: 1px dashed rgba(22,163,74,.65);
            display: flex; align-items: flex-start; justify-content: center; overflow: hidden; }
        .allm-vdrop span { background: rgba(22,163,74,.92); color: #fff; font-size: 10px; font-weight: 800; border-radius: 999px; padding: 1px 8px; white-space: nowrap; margin-top: 6px; }
        .allm-preset-wrap { border: 1px dashed #BFDBFE; border-radius: 12px; padding: 10px 12px; background: #EFF6FF; }
        .allm-preset-btn { border: 1px solid #DDE3EF; background: #fff; color: #1E3A8A; font-weight: 800; font-size: 12px; border-radius: 999px; padding: 5px 10px 5px 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .allm-preset-btn:hover { border-color: #2563EB; }
        .allm-preset-btn.active { background: #2563EB; border-color: #2563EB; color: #fff; }
        .allm-preset-btn .allm-preset-x { border: 0; border-radius: 999px; background: rgba(37,99,235,.12); color: #2563EB; font-weight: 900; width: 20px; height: 20px; line-height: 1; font-size: 13px; cursor: pointer; }
        .allm-preset-btn.active .allm-preset-x { background: rgba(255,255,255,.25); color: #fff; }
        #allManualFileRows { display: flex; flex-direction: column; gap: 8px; }
        #allManualFileRows:empty { display: none; }
        .allm-file-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 8px 12px; border: 1px solid #BFDBFE; border-radius: 10px; background: #F8FAFF; font-size: 13px; }
        .allm-file-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-wrap: wrap; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script>
        (function() {
            if (!document.getElementById('allManualCard')) return;
            if (window.pdfjsLib) {
                try { window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'; } catch (e) {}
            }
            const filesInput = document.getElementById('files');
            const listEl = document.getElementById('allManualList');
            const emptyEl = document.getElementById('allManualEmpty');
            const processBtn = document.getElementById('allManualProcessBtn');
            const cancelBtn = document.getElementById('allManualCancelBtn');
            const statusText = document.getElementById('allManualStatusText');
            const statusBox = document.getElementById('allManualStatusBox');
            const progressWrap = document.getElementById('allManualProgressWrap');
            const progressBar = document.getElementById('allManualProgressBar');
            const downloadBox = document.getElementById('allManualDownload');
            const PROCESS_URL = "{{ route('dashboard.kanbanAllManualProcess') }}";
            const CHUNK_URL = "{{ route('dashboard.kanbanAllChunkUpload') }}";
            const FINALIZE_URL = "{{ route('dashboard.kanbanAllChunksFinalize') }}";
            const CHUNK_THRESHOLD = 6 * 1024 * 1024; // di atas ini pakai upload per bagian
            const CHUNK_TARGET = 5 * 1024 * 1024; // target ukuran tiap bagian (aman di bawah post_max_size 8MB)
            let chunking = false, chunkCancelled = false, chunkAbort = null;

            let allFiles = []; // File[]
            let pages = [];    // {key,fileIdx,fileName,pageNum,cuts:[],wrap,overlay}
            let busy = false, pollTimer = null, currentJobId = null, currentXhr = null;
            let finished = false; // true setelah DONE: hanya Reset + Download yang available
            function editingLocked() { return busy || finished; }
            const MAX_PREVIEW_PAGES = 5; // preview ringan: hanya 5 halaman pertama per file
            const MIN_SEG_MM = 30.0; // SAMA dengan MIN_SEGMENT_HEIGHT_MM backend: segmen lebih pendek diarsir = dibuang
            let filePageCounts = {}; // fileIdx -> total halaman PDF (untuk perluasan garis ke semua halaman)

            function setStatus(msg, kind) {
                statusText.innerHTML = msg;
                statusBox.className = 'alert mb-0 alert-' + (kind || 'secondary');
            }
            function setProgress(p) {
                const v = Math.max(0, Math.min(100, Math.round(p)));
                progressBar.style.width = v + '%';
                progressBar.textContent = v + '%';
            }
            const manualSteps = Array.from(document.querySelectorAll('#allManualSteps .kstep'));
            function setManualStep(state) {
                // state: {active: n, done: [..], failed: n|null}
                manualSteps.forEach(function(el) {
                    const n = parseInt(el.getAttribute('data-step'), 10);
                    el.classList.remove('active', 'done', 'failed');
                    const dot = el.querySelector('.kdot span');
                    dot.innerHTML = String(n);
                    if (state.failed === n) {
                        el.classList.add('failed');
                        dot.innerHTML = '<i class="fas fa-times"></i>';
                    } else if (state.done.includes(n)) {
                        el.classList.add('done');
                        dot.innerHTML = '<i class="fas fa-check"></i>';
                    } else if (state.active === n) {
                        el.classList.add('active');
                    }
                });
            }
            function refreshCount() { /* dihapus: penghitung garis tidak dipakai agar ringan */ }
            function fileKey(f) { return f.name + '|' + f.size + '|' + f.lastModified; }
            // Permanent logic: selalu ON (checkbox disembunyikan dari frontend, hanya backend yang tahu).
            function isSyncOn() { return true; }
            function isAutoOn() { return true; }
            function isMaster(pg) { return pages.length > 0 && pages[0] === pg; }

            // Normalisasi base: filter tepi, urut, dedup rapat.
            function normBase(raw) {
                const arr = (raw || []).map(Number).filter(function(v) { return v > 0.02 && v < 0.98; });
                arr.sort(function(a, b) { return a - b; });
                const out = [];
                arr.forEach(function(v) {
                    if (!out.length || Math.abs(v - out[out.length - 1]) >= 0.03) out.push(v);
                });
                return out;
            }

            // Hitung garis final: base + auto-tiling dari jarak base[0] -> base[1].
            function computeFinal(base) {
                const b = normBase(base);
                const final = b.slice();
                const auto = [];
                if (isAutoOn() && b.length >= 2) {
                    const d = b[1] - b[0];
                    if (d >= 0.03) {
                        // Auto hanya lanjut SETELAH garis merah terakhir — tidak boleh
                        // di dalam rentang / menumpuk garis merah.
                        let next = b[b.length - 1] + d;
                        let guard = 0;
                        while (next < 0.98 && guard < 30) {
                            const clash = b.some(function(v) { return Math.abs(v - next) < 0.03; }) ||
                                          auto.some(function(v) { return Math.abs(v - next) < 0.025; });
                            if (!clash) { final.push(next); auto.push(next); }
                            next += d;
                            guard++;
                        }
                    }
                }
                final.sort(function(a, c) { return a - c; });
                return { final: final, auto: auto, base: b };
            }

            function syncFromMaster() {
                if (!isSyncOn() || pages.length < 2) return;
                const masterBase = (pages[0].base || []).slice();
                const masterVBase = (pages[0].vbase || []).slice();
                for (let i = 1; i < pages.length; i++) {
                    pages[i].base = masterBase.slice();
                    pages[i].vbase = masterVBase.slice();
                    renderPageLines(pages[i], true);
                }
                refreshCount();
            }

            function renderPageLines(pg, skipSync) {
                pg.base = normBase(pg.base);
                pg.vbase = normBase(pg.vbase);
                const comp = computeFinal(pg.base);
                pg.base = comp.base;
                pg.cuts = comp.final;
                pg.autoCuts = comp.auto;
                const autoSet = {};
                comp.auto.forEach(function(v) { autoSet[v.toFixed(4)] = true; });
                pg.overlay.querySelectorAll('.allm-line').forEach(function(n) { n.remove(); });
                pg.overlay.querySelectorAll('.allm-drop').forEach(function(n) { n.remove(); });
                pg.overlay.querySelectorAll('.allm-vline').forEach(function(n) { n.remove(); });
                pg.overlay.querySelectorAll('.allm-vdrop').forEach(function(n) { n.remove(); });
                // tandai master / locked
                if (pg.card) {
                    pg.card.classList.toggle('is-master', isMaster(pg));
                    pg.card.classList.toggle('is-locked', isSyncOn() && !isMaster(pg));
                    const badge = pg.card.querySelector('.allm-sync-badge');
                    if (badge) badge.innerHTML = isMaster(pg) ? '<span class="allm-master-badge">MASTER</span>' : (isSyncOn() ? '<span class="allm-lock-badge">ikut page 1</span>' : '');
                }
                pg.cuts.forEach(function(frac) {
                    const isAuto = !!autoSet[frac.toFixed(4)];
                    const baseIdx = comp.base.findIndex(function(v) { return Math.abs(v - frac) < 0.0005; });
                    const line = document.createElement('div');
                    line.className = 'allm-line' + (isAuto ? ' auto' : '');
                    line.style.top = (frac * 100) + '%';
                    if (isAuto) {
                        line.innerHTML = '<span class="allm-line-tag auto">auto — ' + Math.round(frac * 100) + '%</span>';
                        pg.overlay.appendChild(line);
                        return;
                    }
                    const label = '✥ garis ' + (baseIdx + 1) + ' — ' + Math.round(frac * 100) + '% ';
                    line.innerHTML = '<span class="allm-line-tag">' + label + '<button type="button" title="Hapus garis">×</button></span>';
                    line.querySelector('button').addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                        if (isSyncOn() && !isMaster(pg)) { setStatus('Matikan checkbox copy agar bisa edit page ini satuan.', 'warning'); return; }
                        pg.base.splice(baseIdx, 1);
                        renderPageLines(pg); refreshCount();
                        if (isSyncOn() && isMaster(pg)) syncFromMaster();
                    });
                    // drag hanya untuk garis manual (abaikan tekan pada tombol × agar klik hapus sampai)
                    line.addEventListener('pointerdown', function(ev) {
                        if (ev.target && ev.target.closest && ev.target.closest('button')) return;
                        if (editingLocked()) return;
                        if (isSyncOn() && !isMaster(pg)) return;
                        ev.preventDefault();
                        try { line.setPointerCapture(ev.pointerId); } catch (e) {}
                        const myIdx = baseIdx;
                        const move = function(e2) {
                            const r = pg.overlay.getBoundingClientRect();
                            let y = (e2.clientY - r.top) / r.height;
                            y = Math.max(0.02, Math.min(0.98, y));
                            pg.base[myIdx] = y;
                            line.style.top = (y * 100) + '%';
                            const tag = line.querySelector('.allm-line-tag');
                            if (tag) tag.childNodes[0].textContent = '✥ garis ' + (myIdx + 1) + ' — ' + Math.round(y * 100) + '% ';
                        };
                        const up = function() {
                            line.removeEventListener('pointermove', move);
                            line.removeEventListener('pointerup', up);
                            line.removeEventListener('pointercancel', up);
                            renderPageLines(pg); refreshCount();
                            if (isSyncOn() && isMaster(pg)) syncFromMaster();
                        };
                        line.addEventListener('pointermove', move);
                        line.addEventListener('pointerup', up);
                        line.addEventListener('pointercancel', up);
                    });
                    pg.overlay.appendChild(line);
                });
                // Garis BERDIRI (hijau, drag kiri-kanan, tanpa auto).
                (pg.vbase || []).forEach(function(frac, baseIdx) {
                    const vline = document.createElement('div');
                    vline.className = 'allm-vline';
                    vline.style.left = (frac * 100) + '%';
                    const vlabel = '❖ garis berdiri ' + (baseIdx + 1) + ' — ' + Math.round(frac * 100) + '% ';
                    vline.innerHTML = '<span class="allm-vline-tag">' + vlabel + '<button type="button" title="Hapus garis">×</button></span>';
                    vline.querySelector('button').addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                        if (isSyncOn() && !isMaster(pg)) { setStatus('Garis ikut page 1 (MASTER).', 'warning'); return; }
                        pg.vbase.splice(baseIdx, 1);
                        renderPageLines(pg); refreshCount();
                        if (isSyncOn() && isMaster(pg)) syncFromMaster();
                    });
                    vline.addEventListener('pointerdown', function(ev) {
                        if (ev.target && ev.target.closest && ev.target.closest('button')) return;
                        if (editingLocked()) return;
                        if (isSyncOn() && !isMaster(pg)) return;
                        ev.preventDefault();
                        try { vline.setPointerCapture(ev.pointerId); } catch (e) {}
                        const myIdx = baseIdx;
                        const move = function(e2) {
                            const r = pg.overlay.getBoundingClientRect();
                            let x = (e2.clientX - r.left) / r.width;
                            x = Math.max(0.02, Math.min(0.98, x));
                            pg.vbase[myIdx] = x;
                            vline.style.left = (x * 100) + '%';
                            const tag = vline.querySelector('.allm-vline-tag');
                            if (tag) tag.childNodes[0].textContent = '❖ garis berdiri ' + (myIdx + 1) + ' — ' + Math.round(x * 100) + '% ';
                        };
                        const up = function() {
                            vline.removeEventListener('pointermove', move);
                            vline.removeEventListener('pointerup', up);
                            vline.removeEventListener('pointercancel', up);
                            renderPageLines(pg); refreshCount();
                            if (isSyncOn() && isMaster(pg)) syncFromMaster();
                        };
                        vline.addEventListener('pointermove', move);
                        vline.addEventListener('pointerup', up);
                        vline.addEventListener('pointercancel', up);
                    });
                    pg.overlay.appendChild(vline);
                });
                // Arsiran segmen pendek (< MIN_SEG_MM): akan dibuang backend sebagai strip putih.
                try {
                    const mmH = parseFloat(pg.pageHmm || 0);
                    if (mmH > 0 && pg.cuts && pg.cuts.length) {
                        const bounds = [0].concat(pg.cuts.slice(), [1]);
                        for (let s = 0; s < bounds.length - 1; s++) {
                            const t = bounds[s], b = bounds[s + 1];
                            const hfrac = b - t;
                            if (hfrac > 0.0001 && hfrac * mmH < MIN_SEG_MM) {
                                const dz = document.createElement('div');
                                dz.className = 'allm-drop';
                                dz.style.top = (t * 100) + '%';
                                dz.style.height = (hfrac * 100) + '%';
                                const lbl = document.createElement('span');
                                lbl.textContent = 'dibuang (' + Math.round(hfrac * mmH) + 'mm < ' + MIN_SEG_MM + 'mm)';
                                dz.appendChild(lbl);
                                pg.overlay.appendChild(dz);
                            }
                        }
                    }
                } catch (e) {}
                // Arsiran kolom sempit (< MIN_SEG_MM): akan dibuang backend.
                try {
                    const mmW = parseFloat(pg.pageWmm || 0);
                    const vb = normBase(pg.vbase || []);
                    if (mmW > 0 && vb.length) {
                        const vbounds = [0].concat(vb.slice(), [1]);
                        for (let s = 0; s < vbounds.length - 1; s++) {
                            const l = vbounds[s], r = vbounds[s + 1];
                            const wfrac = r - l;
                            if (wfrac > 0.0001 && wfrac * mmW < MIN_SEG_MM) {
                                const dz = document.createElement('div');
                                dz.className = 'allm-vdrop';
                                dz.style.left = (l * 100) + '%';
                                dz.style.width = (wfrac * 100) + '%';
                                const lbl = document.createElement('span');
                                lbl.textContent = 'dibuang (' + Math.round(wfrac * mmW) + 'mm < ' + MIN_SEG_MM + 'mm)';
                                dz.appendChild(lbl);
                                pg.overlay.appendChild(dz);
                            }
                        }
                    }
                } catch (e) {}
            }

            function addPageDom(fileIdx, fileName, pageNum, canvas, pageHmm, pageWmm) {
                const key = fileIdx + ':' + pageNum;
                const card = document.createElement('div');
                card.className = 'allm-page';
                card.innerHTML = '<div class="allm-page-head"><span></span><span class="allm-sync-badge"></span><span style="display:flex;gap:8px;flex-wrap:wrap;">' +
                    '<button type="button" class="btn btn-sm btn-primary allm-add">+ Garis tidur</button>' +
                    '<button type="button" class="btn btn-sm btn-success allm-add-v">+ Garis berdiri</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-primary allm-clear">Clear</button></span></div>';
                card.querySelector('span').textContent = fileName + ' — hal ' + pageNum;
                const wrap = document.createElement('div');
                wrap.className = 'allm-canvas-wrap';
                wrap.appendChild(canvas);
                const overlay = document.createElement('div');
                overlay.className = 'allm-overlay';
                wrap.appendChild(overlay);
                card.appendChild(wrap);
                listEl.appendChild(card);
                // Default 2 garis tidur; garis berdiri default kosong. Page 2+ copy page 1; preset aktif ikut bila ada.
                let initBase = [1 / 3, 2 / 3];
                if (typeof pendingPresetLines !== 'undefined' && pendingPresetLines && pendingPresetLines.length) initBase = pendingPresetLines.slice();
                else if (isSyncOn() && pages.length > 0) initBase = (pages[0].base || [1 / 3, 2 / 3]).slice();
                let initVBase = [];
                if (typeof pendingPresetVLines !== 'undefined' && pendingPresetVLines && pendingPresetVLines.length) initVBase = pendingPresetVLines.slice();
                else if (isSyncOn() && pages.length > 0) initVBase = (pages[0].vbase || []).slice();
                const pg = { key: key, fileIdx: fileIdx, fileName: fileName, pageNum: pageNum, base: initBase, vbase: initVBase, cuts: [], autoCuts: [], card: card, wrap: wrap, overlay: overlay, pageHmm: parseFloat(pageHmm || 0), pageWmm: parseFloat(pageWmm || 0) };
                pages.push(pg);
                card.querySelector('.allm-add').addEventListener('click', function() {
                    if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                    if (isSyncOn() && !isMaster(pg)) { setStatus('Garis ikut page 1 (MASTER).', 'warning'); return; }
                    pg.base.push(0.5);
                    renderPageLines(pg); refreshCount();
                    if (isSyncOn() && isMaster(pg)) syncFromMaster();
                });
                card.querySelector('.allm-add-v').addEventListener('click', function() {
                    if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                    if (isSyncOn() && !isMaster(pg)) { setStatus('Garis ikut page 1 (MASTER).', 'warning'); return; }
                    pg.vbase.push(0.5);
                    renderPageLines(pg); refreshCount();
                    if (isSyncOn() && isMaster(pg)) syncFromMaster();
                });
                card.querySelector('.allm-clear').addEventListener('click', function() {
                    if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                    if (isSyncOn() && !isMaster(pg)) { setStatus('Garis ikut page 1 (MASTER).', 'warning'); return; }
                    pg.base = []; pg.vbase = []; renderPageLines(pg); refreshCount();
                    if (isSyncOn() && isMaster(pg)) syncFromMaster();
                });
                overlay.addEventListener('dblclick', function(ev) {
                    if (editingLocked()) return;
                    if (ev.target && ev.target.closest && ev.target.closest('.allm-line, .allm-vline')) return;
                    if (isSyncOn() && !isMaster(pg)) return;
                    const r = overlay.getBoundingClientRect();
                    if (ev.shiftKey) {
                        // Shift + double-click = garis berdiri.
                        let x = (ev.clientX - r.left) / r.width;
                        x = Math.max(0.02, Math.min(0.98, x));
                        pg.vbase.push(x);
                    } else {
                        let y = (ev.clientY - r.top) / r.height;
                        y = Math.max(0.02, Math.min(0.98, y));
                        pg.base.push(y);
                    }
                    renderPageLines(pg); refreshCount();
                    if (isSyncOn() && isMaster(pg)) syncFromMaster();
                });
                renderPageLines(pg);
                if (emptyEl) emptyEl.style.display = 'none';
                refreshCount();
                return pg;
            }

            async function previewFile(file, fileIdx) {
                if (!window.pdfjsLib) { setStatus('pdf.js gagal dimuat (CDN). Cek internet lalu pilih ulang file.', 'danger'); return; }
                try {
                    const buf = await file.arrayBuffer();
                    const pdf = await window.pdfjsLib.getDocument({ data: buf }).promise;
                    filePageCounts[fileIdx] = pdf.numPages;
                    renderFileRows();
                    const maxN = Math.min(pdf.numPages, MAX_PREVIEW_PAGES);
                    for (let n = 1; n <= maxN; n++) {
                        try {
                            const page = await pdf.getPage(n);
                            const viewport = page.getViewport({ scale: 1.3 });
                            const canvas = document.createElement('canvas');
                            canvas.width = Math.floor(viewport.width);
                            canvas.height = Math.floor(viewport.height);
                            const ctx = canvas.getContext('2d');
                            await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                            // Tinggi & lebar halaman dalam mm (viewport / skala = point; 1pt = 25.4/72mm)
                            // agar arsiran segmen pendek sama dengan rule backend.
                            const mmH = (viewport.height / 1.3) * 25.4 / 72;
                            const mmW = (viewport.width / 1.3) * 25.4 / 72;
                            addPageDom(fileIdx, file.name, n, canvas, mmH, mmW);
                        } catch (e) { console.error(e); }
                    }
                    const more = pdf.numPages > maxN ? ' (preview ' + maxN + ' pertama dari ' + pdf.numPages + ' hal, garis page 1 berlaku ke semua)' : '';
                    setStatus(allFiles.length + ' file, preview ' + pages.length + ' halaman' + more + '. Atur garis lalu klik Process Manual.', 'info');
                    renderFileRows();
                } catch (e) {
                    console.error(e);
                    setStatus('Gagal preview ' + file.name + ': ' + (e.message || e), 'danger');
                }
            }

            function addIncomingFiles(incoming) {
                if (finished) { setStatus('Sudah finish. Klik Reset dulu untuk job baru.', 'warning'); return; }
                if (busy) return;
                (incoming || []).forEach(function(f) {
                    if (!/\.pdf$/i.test(f.name)) return;
                    const k = fileKey(f);
                    const dup = allFiles.some(function(e) { return fileKey(e) === k; });
                    if (!dup) {
                        allFiles.push(f);
                        const idx = allFiles.length - 1;
                        previewFile(f, idx);
                    }
                });
                renderFileRows();
            }

            // Input khusus manual (utama, terisolasi dari flow lama).
            const manualInput = document.getElementById('allManualFilesInput');
            if (manualInput) manualInput.addEventListener('change', function() {
                addIncomingFiles(Array.from(manualInput.files || []));
                manualInput.value = '';
            });

            // Fallback: tangkap file dari input atas SEBELUM script lama mengosongkannya.
            // Dipasang di document capture-phase agar jalan duluan.
            document.addEventListener('change', function(e) {
                if (e && e.target && e.target.id === 'files') {
                    try { addIncomingFiles(Array.from(e.target.files || [])); } catch (err) {}
                }
            }, true);

            const resetPreviewBtn = document.getElementById('allManualResetPreviewBtn');
            if (resetPreviewBtn) resetPreviewBtn.addEventListener('click', function() {
                if (busy || anyRowRunning()) return;
                allFiles = []; pages = [];
                filePageCounts = {};
                fileRows.forEach(clearRowJob);
                fileRows = [];
                renderFileRows();
                finished = false;
                listEl.innerHTML = '';
                if (emptyEl) emptyEl.style.display = '';
                downloadBox.innerHTML = '';
                progressWrap.classList.add('d-none');
                setProgress(0);
                // Kembali ke awal: process available lagi.
                processBtn.disabled = false;
                cancelBtn.disabled = true;
                const mi = document.getElementById('allManualFilesInput');
                if (mi) mi.disabled = false;
                const sc = document.getElementById('allManualSyncChk');
                if (sc) sc.disabled = false;
                const ac = document.getElementById('allManualAutoChk');
                if (ac) ac.disabled = false;
                refreshCount();
                setStatus('Preview direset. Pilih PDF lagi.', 'secondary');
                setManualStep({ active: 1, done: [], failed: null });
            });

            const syncChk = document.getElementById('allManualSyncChk');
            if (syncChk) syncChk.addEventListener('change', function() {
                pages.forEach(function(pg) { renderPageLines(pg, true); });
                if (syncChk.checked) syncFromMaster();
                else pages.forEach(function(pg) { renderPageLines(pg, true); });
                refreshCount();
            });
            const autoChk = document.getElementById('allManualAutoChk');
            if (autoChk) autoChk.addEventListener('change', function() {
                pages.forEach(function(pg) { renderPageLines(pg, true); });
                if (isSyncOn()) syncFromMaster();
                refreshCount();
            });

            // ===== MASTER PRESET garis (shared DB): save base page-1, klik button load ke semua preview =====
            const PRESET_LIST_URL = "{{ route('dashboard.kanbanManualPresets') }}";
            const PRESET_STORE_URL = "{{ route('dashboard.kanbanManualPresetStore') }}";
            const presetBar = document.getElementById('allManualPresetBar');
            const presetNameInput = document.getElementById('allManualPresetName');
            const presetSaveBtn = document.getElementById('allManualPresetSaveBtn');
            let presetsCache = [];
            let canManagePreset = false;
            let activePresetName = '';
            let pendingPresetLines = null; // dipakai bila preset diklik sebelum ada preview
            let pendingPresetVLines = null;
            function csrfToken() { const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; }
            function currentBaseLines() {
                if (pages.length > 0) return normBase(pages[0].base || []);
                return normBase(pendingPresetLines || []);
            }
            function currentVLines() {
                if (pages.length > 0) return normBase(pages[0].vbase || []);
                return normBase(pendingPresetVLines || []);
            }
            function applyPresetLines(lines, vlines, name) {
                // Kompatibel preset lama (dipanggil dengan 2 argumen: lines, name).
                if (typeof name === 'undefined' && typeof vlines === 'string') { name = vlines; vlines = []; }
                const norm = normBase(lines || []);
                const normV = normBase(vlines || []);
                if (!norm.length && !normV.length) { setStatus('Preset ' + (name || '') + ' kosong.', 'warning'); return; }
                activePresetName = (name || '').toUpperCase();
                pendingPresetLines = norm.slice();
                pendingPresetVLines = normV.slice();
                if (pages.length === 0) {
                    setStatus('Preset <strong>' + activePresetName + '</strong> dimuat — pilih PDF, garis otomatis ikut preset.', 'info');
                    renderPresetBar();
                    return;
                }
                pages.forEach(function(pg) { pg.base = norm.slice(); pg.vbase = normV.slice(); });
                pages.forEach(function(pg) { renderPageLines(pg, true); });
                if (isSyncOn()) syncFromMaster();
                refreshCount();
                renderPresetBar();
                setStatus('Preset <strong>' + activePresetName + '</strong> dimuat (' + norm.length + ' garis). Klik Process bila sudah oke.', 'success');
            }
            function renderPresetBar() {
                if (!presetBar) return;
                presetBar.innerHTML = '';
                if (!presetsCache.length) {
                    const s = document.createElement('small');
                    s.className = 'text-muted';
                    s.textContent = 'Belum ada master. Atur garis lalu Save, mis. AISIN.';
                    presetBar.appendChild(s);
                    return;
                }
                presetsCache.forEach(function(p) {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'allm-preset-btn' + (activePresetName === String(p.name).toUpperCase() ? ' active' : '');
                    const label = document.createElement('span');
                    label.textContent = p.name;
                    b.appendChild(label);
                    b.title = 'Load garis ' + p.name;
                    b.addEventListener('click', function() { applyPresetLines(p.lines, p.vlines, p.name); });
                    if (canManagePreset) {
                        const x = document.createElement('button');
                        x.type = 'button';
                        x.className = 'allm-preset-x';
                        x.textContent = '×';
                        x.title = 'Hapus ' + p.name;
                        x.addEventListener('click', function(ev) {
                            ev.stopPropagation();
                            deletePreset(p);
                        });
                        b.appendChild(x);
                    }
                    presetBar.appendChild(b);
                });
            }
            async function loadPresets() {
                if (!presetBar) return;
                try {
                    const res = await fetch(PRESET_LIST_URL, { headers: { 'Accept': 'application/json' } });
                    const j = await res.json();
                    presetsCache = (j && j.data) || [];
                    canManagePreset = !!(j && j.can_manage);
                    // Save terbuka untuk semua user login; hanya tombol hapus (×) yang khusus manage-master.
                    if (presetSaveBtn) { presetSaveBtn.disabled = false; presetSaveBtn.title = ''; }
                    if (presetNameInput) presetNameInput.disabled = false;
                    renderPresetBar();
                } catch (e) {
                    if (presetBar) presetBar.innerHTML = '<small class="text-danger">Gagal memuat master.</small>';
                }
            }
            async function deletePreset(p) {
                if (!confirm('Yakin ingin menghapus master ' + p.name + '?\nTindakan ini tidak bisa dibatalkan.')) return;
                try {
                    const res = await fetch(PRESET_STORE_URL + '/' + p.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
                    });
                    const j = await res.json();
                    if (!res.ok || !j.status) throw new Error((j && j.message) || ('HTTP ' + res.status));
                    if (activePresetName === String(p.name).toUpperCase()) activePresetName = '';
                    await loadPresets();
                    setStatus('Master <strong>' + p.name + '</strong> dihapus.', 'warning');
                } catch (e) { setStatus('Gagal hapus: ' + (e.message || e), 'danger'); }
            }
            if (presetSaveBtn) presetSaveBtn.addEventListener('click', async function() {
                if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                const base = currentBaseLines();
                const vbase = currentVLines();
                if (!base.length && !vbase.length) { alert('Atur minimal 1 garis dulu (tidur/berdiri).'); return; }
                const rawName = presetNameInput ? presetNameInput.value : '';
                const name = String(rawName || '').trim().toUpperCase();
                if (!name) { alert('Isi nama dulu, mis. AISIN.'); if (presetNameInput) presetNameInput.focus(); return; }
                if (!/^[A-Z0-9 _\-]{1,50}$/.test(name)) { alert('Nama hanya boleh huruf, angka, spasi, - dan _.'); return; }
                presetSaveBtn.disabled = true;
                try {
                    const fd = new FormData();
                    fd.append('name', name);
                    base.forEach(function(v) { fd.append('lines[]', String(v)); });
                    vbase.forEach(function(v) { fd.append('vlines[]', String(v)); });
                    fd.append('_token', csrfToken());
                    const res = await fetch(PRESET_STORE_URL, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
                    const j = await res.json();
                    if (!res.ok || !j.status) throw new Error((j && j.message) || ('HTTP ' + res.status));
                    activePresetName = name;
                    pendingPresetLines = base.slice();
                    pendingPresetVLines = vbase.slice();
                    if (presetNameInput) presetNameInput.value = '';
                    await loadPresets();
                    setStatus('Master <strong>' + name + '</strong> tersimpan (' + base.length + ' tidur, ' + vbase.length + ' berdiri). Klik button-nya kapan saja untuk load.', 'success');
                } catch (e) {
                    console.error(e);
                    setStatus('Gagal save: ' + (e.message || e), 'danger');
                } finally {
                    presetSaveBtn.disabled = false;
                }
            });
            loadPresets();

            // ===== Proses & download SATUAN per file (aliran gabungan tidak diubah) =====
            const fileRowsEl = document.getElementById('allManualFileRows');
            let fileRows = []; // {fi, status, note, download, volumes, jobId, pollTimer, xhr, chunkAbort, chunkCancelled}
            function fmtMB(b) { return ((b || 0) / 1024 / 1024).toFixed(2) + ' MB'; }
            function anyRowRunning() { return fileRows.some(function(r) { return r && r.status === 'running'; }); }
            function renderFileRows() {
                if (!fileRowsEl) return;
                while (fileRows.length < allFiles.length) fileRows.push({ status: 'ready', note: '', download: null, volumes: null, jobId: null, pollTimer: null, xhr: null, chunkAbort: null, chunkCancelled: false });
                if (fileRows.length > allFiles.length) {
                    fileRows.slice(allFiles.length).forEach(clearRowJob);
                    fileRows.length = allFiles.length;
                }
                fileRowsEl.innerHTML = '';
                if (!allFiles.length) return;
                allFiles.forEach(function(f, fi) {
                    const row = fileRows[fi];
                    const total = parseInt(filePageCounts[fi], 10) || 0;
                    const div = document.createElement('div');
                    div.className = 'allm-file-row';
                    const meta = document.createElement('div');
                    const title = document.createElement('div');
                    const strong = document.createElement('strong');
                    strong.textContent = f.name;
                    title.appendChild(strong);
                    const sub = document.createElement('small');
                    sub.className = 'text-muted';
                    sub.textContent = ' ' + fmtMB(f.size) + (total ? ' · ' + total + ' hal' : ' · ...');
                    title.appendChild(sub);
                    meta.appendChild(title);
                    if (row.note) {
                        const nw = document.createElement('div');
                        const ns = document.createElement('small');
                        ns.className = 'text-muted';
                        ns.textContent = row.note;
                        nw.appendChild(ns);
                        meta.appendChild(nw);
                    }
                    const acts = document.createElement('div');
                    acts.className = 'allm-file-actions';
                    if (row.status === 'running') {
                        const c = document.createElement('button');
                        c.type = 'button'; c.className = 'btn btn-sm btn-outline-primary'; c.textContent = 'Cancel';
                        c.addEventListener('click', function() { cancelRowJob(fi); });
                        acts.appendChild(c);
                    } else {
                        const p = document.createElement('button');
                        p.type = 'button'; p.className = 'btn btn-sm btn-primary'; p.textContent = 'Process satuan';
                        if (!total) { p.disabled = true; p.title = 'Menunggu info halaman...'; }
                        else if (row.status === 'done') { p.disabled = true; p.title = 'Sudah diproses. Klik Reset untuk job baru.'; }
                        p.addEventListener('click', function() { processSingleFile(fi); });
                        acts.appendChild(p);
                    }
                    const addDl = function(href, txt) {
                        const a = document.createElement('a');
                        a.href = href; a.setAttribute('download', ''); a.className = 'btn btn-success btn-sm'; a.textContent = txt;
                        acts.appendChild(a);
                    };
                    const addPreview = function(href, txt) {
                        const a = document.createElement('a');
                        a.href = href; a.target = '_blank'; a.rel = 'noopener'; a.className = 'btn btn-info btn-sm'; a.textContent = txt;
                        acts.appendChild(a);
                    };
                    if (row.download) { addDl(row.download, 'Download'); if (row.preview) addPreview(row.preview, 'Preview / Print'); }
                    else if (row.volumes && row.volumes.length) row.volumes.forEach(function(v, idx) { addDl(v.download, 'Download ' + (idx + 1)); if (v.preview) addPreview(v.preview, 'Preview / Print ' + (idx + 1)); });
                    div.appendChild(meta);
                    div.appendChild(acts);
                    fileRowsEl.appendChild(div);
                });
            }
            function clearRowJob(row) {
                if (!row) return;
                if (row.pollTimer) { clearInterval(row.pollTimer); row.pollTimer = null; }
                if (row.xhr) { try { row.xhr.abort(); } catch (e) {} row.xhr = null; }
                if (row.chunkAbort) { try { row.chunkAbort.abort(); } catch (e) {} row.chunkAbort = null; }
                row.chunkCancelled = true;
            }
            function stopRowCancel(fi) {
                const row = fileRows[fi]; if (!row) return;
                clearRowJob(row);
                row.status = 'ready'; row.jobId = null; row.note = 'Dibatalkan.';
                renderFileRows();
            }
            function stopRowFail(fi, msg) {
                const row = fileRows[fi]; if (!row) return;
                clearRowJob(row);
                row.status = 'fail'; row.jobId = null; row.note = 'Gagal: ' + msg;
                renderFileRows();
            }
            async function cancelRowJob(fi) {
                const row = fileRows[fi];
                if (!row || row.status !== 'running') return;
                if (!confirm('Batalkan proses file ini?')) return;
                row.chunkCancelled = true;
                if (row.xhr) { try { row.xhr.abort(); } catch (e) {} row.xhr = null; }
                if (row.chunkAbort) { try { row.chunkAbort.abort(); } catch (e) {} row.chunkAbort = null; }
                if (row.pollTimer) { clearInterval(row.pollTimer); row.pollTimer = null; }
                if (row.jobId) {
                    try {
                        await fetch('/dashboard/kanban/cancel/' + row.jobId, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
                        });
                    } catch (e) {}
                    row.jobId = null;
                }
                row.status = 'ready'; row.note = 'Dibatalkan.';
                renderFileRows();
            }
            function processSingleFile(fi) {
                const row = fileRows[fi];
                const f = allFiles[fi];
                if (!row || !f || row.status === 'running') return;
                if (busy) { setStatus('Tunggu proses gabungan selesai dulu.', 'warning'); return; }
                const comp = computeFinal(currentBaseLines());
                const vlines = normBase(currentVLines());
                if (!comp.final.length && !vlines.length) { alert('Atur minimal 1 garis dulu (tidur/berdiri).'); return; }
                const total = parseInt(filePageCounts[fi], 10) || 0;
                if (!total) { alert('Info halaman belum siap, coba sesaat lagi.'); return; }
                const cuts = { 0: {} };
                const vcuts = { 0: {} };
                for (let p = 1; p <= total; p++) {
                    if (comp.final.length) cuts[0][p] = comp.final.slice();
                    if (vlines.length) vcuts[0][p] = vlines.slice();
                }
                row.status = 'running'; row.note = 'Memproses...'; row.download = null; row.volumes = null; row.jobId = null;
                renderFileRows();
                if ((f.size || 0) > CHUNK_THRESHOLD && window.PDFLib) chunkedSingleFile(fi, cuts, vcuts);
                else simpleSingleFile(fi, cuts, vcuts);
            }
            function simpleSingleFile(fi, cuts, vcuts) {
                const row = fileRows[fi];
                const f = allFiles[fi];
                const fd = new FormData();
                fd.append('files[]', f);
                fd.append('cuts', JSON.stringify(cuts));
                fd.append('vcuts', JSON.stringify(vcuts || {}));
                fd.append('skip_blank', '1');
                fd.append('reverse', '1');
                fd.append('_token', csrfToken());
                const xhr = new XMLHttpRequest();
                row.xhr = xhr;
                xhr.open('POST', PROCESS_URL, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState !== 4) return;
                    row.xhr = null;
                    if (row.status !== 'running') return;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let r = null;
                        try { r = JSON.parse(xhr.responseText); } catch (e) { r = null; }
                        if (r && r.job_id) {
                            row.jobId = r.job_id; row.note = 'Generating...';
                            renderFileRows();
                            pollRowJob(fi, r.job_id);
                        } else {
                            stopRowFail(fi, 'Response tidak valid (HTTP ' + xhr.status + ').');
                        }
                    } else {
                        let msg = 'Upload gagal (HTTP ' + xhr.status + ').';
                        try {
                            const j = JSON.parse(xhr.responseText);
                            if (j.message) msg += ' ' + j.message;
                        } catch (e) {}
                        stopRowFail(fi, msg);
                    }
                };
                xhr.onerror = function() { row.xhr = null; if (row.status === 'running') stopRowFail(fi, 'System error saat upload.'); };
                xhr.send(fd);
            }
            async function chunkedSingleFile(fi, cuts, vcuts) {
                const row = fileRows[fi];
                const f = allFiles[fi];
                row.chunkCancelled = false;
                try {
                    const csrf = csrfToken();
                    const uploadId = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('up-' + Date.now() + '-' + Math.random().toString(16).slice(2));
                    const bytes = await f.arrayBuffer();
                    const src = await window.PDFLib.PDFDocument.load(bytes, { ignoreEncryption: true });
                    const np = src.getPageCount();
                    if (!np) throw new Error('File tidak ada halaman.');
                    const perPage = f.size / np;
                    const perChunk = Math.max(1, Math.min(50, Math.floor(CHUNK_TARGET / Math.max(1, perPage))));
                    const ranges = [];
                    for (let s = 0; s < np; s += perChunk) ranges.push([s, Math.min(s + perChunk, np)]);
                    for (let ci = 0; ci < ranges.length; ci++) {
                        if (row.chunkCancelled || row.status !== 'running') throw { cancelled: true };
                        const sub = await window.PDFLib.PDFDocument.create();
                        const idxs = [];
                        for (let p = ranges[ci][0]; p < ranges[ci][1]; p++) idxs.push(p);
                        const copied = await sub.copyPages(src, idxs);
                        copied.forEach(function(pg) { sub.addPage(pg); });
                        const chunkBytes = await sub.save({ useObjectStreams: false });
                        const fd = new FormData();
                        fd.append('uploadId', uploadId);
                        fd.append('fileIndex', '0');
                        fd.append('chunkIndex', String(ci));
                        fd.append('totalChunks', String(ranges.length));
                        fd.append('fileName', f.name);
                        fd.append('chunk', new Blob([chunkBytes], { type: 'application/pdf' }), 'chunk.pdf');
                        fd.append('_token', csrf);
                        row.chunkAbort = new AbortController();
                        let res;
                        try {
                            res = await fetch(CHUNK_URL, {
                                method: 'POST', body: fd,
                                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                signal: row.chunkAbort.signal
                            });
                        } catch (e) {
                            if (row.chunkCancelled || (e && e.name === 'AbortError')) throw { cancelled: true };
                            throw e;
                        } finally { row.chunkAbort = null; }
                        if (!res.ok) {
                            let t = '';
                            try { t = await res.text(); } catch (e2) {}
                            throw new Error('Bagian ' + (ci + 1) + '/' + ranges.length + ' gagal (HTTP ' + res.status + '). ' + t.substring(0, 120));
                        }
                        if (row.status !== 'running') throw { cancelled: true };
                        row.note = 'Upload bagian ' + (ci + 1) + '/' + ranges.length + '...';
                        renderFileRows();
                    }
                    if (row.status !== 'running') throw { cancelled: true };
                    const fd2 = new FormData();
                    fd2.append('uploadId', uploadId);
                    fd2.append('files', JSON.stringify([{ fileIndex: 0, fileName: f.name, chunks: ranges.length, pages: np, chunkPages: ranges.map(function(r) { return r[1] - r[0]; }) }]));
                    fd2.append('cuts', JSON.stringify(cuts));
                    fd2.append('vcuts', JSON.stringify(vcuts || {}));
                    fd2.append('skip_blank', '1');
                    fd2.append('reverse', '1');
                    fd2.append('_token', csrf);
                    const res2 = await fetch(FINALIZE_URL, {
                        method: 'POST', body: fd2,
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    });
                    let j2 = null;
                    try { j2 = await res2.json(); } catch (e) { j2 = null; }
                    if (!res2.ok || !j2 || !j2.job_id) throw new Error('Finalisasi gagal: ' + ((j2 && j2.message) || ('HTTP ' + res2.status)));
                    row.jobId = j2.job_id; row.note = 'Generating...';
                    renderFileRows();
                    pollRowJob(fi, j2.job_id);
                } catch (e) {
                    if (e && e.cancelled) stopRowCancel(fi);
                    else { console.error(e); stopRowFail(fi, (e && e.message) || String(e)); }
                }
            }
            function pollRowJob(fi, jobId) {
                const row = fileRows[fi];
                let attempts = 0;
                let errCount = 0;
                row.pollTimer = setInterval(async function() {
                    attempts++;
                    try {
                        const res = await fetch('/dashboard/kanban/status/' + jobId);
                        const st = await res.json();
                        if (!fileRows[fi] || fileRows[fi].status !== 'running') { clearInterval(row.pollTimer); row.pollTimer = null; return; }
                        if (st.status === 'CANCELLED') stopRowCancel(fi);
                        else if (st.status === 'FAILED') stopRowFail(fi, st.message || 'Proses gagal.');
                        else if (st.status === 'DONE') {
                            clearInterval(row.pollTimer); row.pollTimer = null;
                            row.status = 'done'; row.jobId = null;
                            if (st.volumes && st.volumes.length) { row.volumes = st.volumes; row.download = null; row.preview = null; }
                            else { row.download = st.download; row.preview = st.preview || null; row.volumes = null; }
                            let msg = 'Selesai';
                            if (st.pages !== undefined) msg += ' (' + st.pages + ' page' + (st.skipped_blank ? ', ' + st.skipped_blank + ' blank dibuang' : '') + ')';
                            row.note = msg + '. Klik Download.';
                            renderFileRows();
                        } else {
                            if (st.parts_done !== undefined && st.parts_total) row.note = 'Generating... bagian ' + st.parts_done + '/' + st.parts_total;
                            else row.note = 'Generating... (cek ke-' + attempts + ')';
                            renderFileRows();
                            if (attempts >= 240) stopRowFail(fi, 'Timeout ~10 menit.');
                        }
                    } catch (e) {
                        console.error(e);
                        errCount++;
                        if (errCount >= 5) stopRowFail(fi, 'Gagal memantau status job.');
                    }
                }, 2500);
            }

            function buildCuts() {
                const out = {};
                // Mode copy (default): garis page 1 berlaku ke SEMUA halaman semua file,
                // termasuk halaman yang tidak di-preview (PDF 1000+ hal tetap kepotong semua).
                if (isSyncOn() && pages.length > 0) {
                    const master = pages[0].cuts.slice().sort(function(a, b) { return a - b; });
                    if (master.length === 0) return out; // tanpa garis = semua disalin apa adanya
                    Object.keys(filePageCounts).forEach(function(fi) {
                        const total = parseInt(filePageCounts[fi], 10) || 0;
                        for (let p = 1; p <= total; p++) {
                            if (!out[fi]) out[fi] = {};
                            out[fi][p] = master.slice();
                        }
                    });
                    return out;
                }
                pages.forEach(function(pg) {
                    if (!pg.cuts.length) return;
                    if (!out[pg.fileIdx]) out[pg.fileIdx] = {};
                    out[pg.fileIdx][pg.pageNum] = pg.cuts.slice().sort(function(a,b){return a-b;});
                });
                return out;
            }

            // Garis BERDIRI per halaman — bentuk sama seperti cuts (tanpa auto).
            function buildVCuts() {
                const out = {};
                if (isSyncOn() && pages.length > 0) {
                    const master = normBase(pages[0].vbase || []);
                    if (master.length === 0) return out;
                    Object.keys(filePageCounts).forEach(function(fi) {
                        const total = parseInt(filePageCounts[fi], 10) || 0;
                        for (let p = 1; p <= total; p++) {
                            if (!out[fi]) out[fi] = {};
                            out[fi][p] = master.slice();
                        }
                    });
                    return out;
                }
                pages.forEach(function(pg) {
                    const v = normBase(pg.vbase || []);
                    if (!v.length) return;
                    if (!out[pg.fileIdx]) out[pg.fileIdx] = {};
                    out[pg.fileIdx][pg.pageNum] = v.slice();
                });
                return out;
            }

            // Upload per bagian (chunk) untuk file raksasa: pecah PDF di browser via pdf-lib,
            // kirim tiap bagian kecil, lalu finalize agar server gabung + crop.
            async function startChunkedUpload(cuts, vcuts) {
                chunking = true; chunkCancelled = false;
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const uploadId = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('up-' + Date.now() + '-' + Math.random().toString(16).slice(2));
                const plans = [];
                let totalChunks = 0;
                for (let fi = 0; fi < allFiles.length; fi++) {
                    const f = allFiles[fi];
                    const bytes = await f.arrayBuffer();
                    const src = await window.PDFLib.PDFDocument.load(bytes, { ignoreEncryption: true });
                    const np = src.getPageCount();
                    if (!np) throw new Error('File ' + f.name + ' tidak ada halaman.');
                    const perPage = f.size / np;
                    const perChunk = Math.max(1, Math.min(50, Math.floor(CHUNK_TARGET / Math.max(1, perPage))));
                    const ranges = [];
                    for (let s = 0; s < np; s += perChunk) ranges.push([s, Math.min(s + perChunk, np)]);
                    plans.push({ file: f, src: src, np: np, ranges: ranges });
                    totalChunks += ranges.length;
                }
                let doneChunks = 0;
                const specs = [];
                for (let fi = 0; fi < plans.length; fi++) {
                    const pl = plans[fi];
                    for (let ci = 0; ci < pl.ranges.length; ci++) {
                        if (chunkCancelled) throw { cancelled: true };
                        const start = pl.ranges[ci][0], end = pl.ranges[ci][1];
                        const sub = await window.PDFLib.PDFDocument.create();
                        const idxs = [];
                        for (let p = start; p < end; p++) idxs.push(p);
                        const copied = await sub.copyPages(pl.src, idxs);
                        copied.forEach(function(pg) { sub.addPage(pg); });
                        // WAJIB tanpa object-stream: FPDI server tidak bisa baca PDF terkompresi modern.
                        const chunkBytes = await sub.save({ useObjectStreams: false });
                        const fd = new FormData();
                        fd.append('uploadId', uploadId);
                        fd.append('fileIndex', String(fi));
                        fd.append('chunkIndex', String(ci));
                        fd.append('totalChunks', String(pl.ranges.length));
                        fd.append('fileName', pl.file.name);
                        fd.append('chunk', new Blob([chunkBytes], { type: 'application/pdf' }), 'chunk.pdf');
                        fd.append('_token', csrf);
                        chunkAbort = new AbortController();
                        let res;
                        try {
                            res = await fetch(CHUNK_URL, {
                                method: 'POST', body: fd,
                                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                signal: chunkAbort.signal
                            });
                        } catch (e) {
                            if (chunkCancelled || (e && e.name === 'AbortError')) throw { cancelled: true };
                            throw e;
                        }
                        if (!res.ok) {
                            let t = '';
                            try { t = await res.text(); } catch (e2) {}
                            throw new Error('Bagian ' + (ci + 1) + '/' + pl.ranges.length + ' gagal (HTTP ' + res.status + '). ' + t.substring(0, 120));
                        }
                        doneChunks++;
                        setProgress(Math.round(doneChunks / totalChunks * 30));
                        setStatus('Uploading bagian ' + doneChunks + '/' + totalChunks + '...', 'info');
                    }
                    specs.push({ fileIndex: fi, fileName: pl.file.name, chunks: pl.ranges.length, pages: pl.np, chunkPages: pl.ranges.map(function(r) { return r[1] - r[0]; }) });
                }
                if (chunkCancelled) throw { cancelled: true };
                setStatus('Semua bagian terkirim. Server menggabung + crop...', 'info');
                const fd2 = new FormData();
                fd2.append('uploadId', uploadId);
                fd2.append('files', JSON.stringify(specs));
                fd2.append('cuts', JSON.stringify(cuts));
                fd2.append('vcuts', JSON.stringify(vcuts || {}));
                fd2.append('skip_blank', '1'); // permanent: selalu buang halaman blank
                fd2.append('reverse', '1'); // permanent: hasil split selalu dibalik (nama file tetap)
                fd2.append('_token', csrf);
                const res2 = await fetch(FINALIZE_URL, {
                    method: 'POST', body: fd2,
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                let j2 = null;
                try { j2 = await res2.json(); } catch (e) { j2 = null; }
                if (!res2.ok || !j2 || !j2.job_id) {
                    const em = (j2 && j2.message) || ('HTTP ' + res2.status);
                    throw new Error('Finalisasi gagal: ' + em);
                }
                chunking = false;
                currentJobId = j2.job_id;
                setProgress(35);
                setStatus('Step 2/2 — Generating manual...', 'info');
                pollStatus(j2.job_id);
            }

            function pollStatus(jobId) {
                let attempts = 0;
                pollTimer = setInterval(async function() {
                    attempts++;
                    try {
                        const res = await fetch('/dashboard/kanban/status/' + jobId);
                        const st = await res.json();
                        if (st.status === 'CANCELLED') {
                            clearInterval(pollTimer); pollTimer = null; busy = false;
                            processBtn.disabled = false; cancelBtn.disabled = true;
                            const mi = document.getElementById('allManualFilesInput');
                            if (mi) mi.disabled = false;
                            const sc = document.getElementById('allManualSyncChk');
                            if (sc) sc.disabled = false;
                            const ac = document.getElementById('allManualAutoChk');
                            if (ac) ac.disabled = false;
                            setStatus('Dibatalkan.', 'warning');
                            setManualStep({ active: 1, done: [], failed: null });
                        } else if (st.status === 'FAILED') {
                            clearInterval(pollTimer); pollTimer = null; busy = false;
                            processBtn.disabled = false; cancelBtn.disabled = true;
                            const miF = document.getElementById('allManualFilesInput');
                            if (miF) miF.disabled = false;
                            const scF = document.getElementById('allManualSyncChk');
                            if (scF) scF.disabled = false;
                            const acF = document.getElementById('allManualAutoChk');
                            if (acF) acF.disabled = false;
                            const tmpF = document.createElement('div');
                            tmpF.textContent = (st.message || 'Proses gagal tanpa pesan.');
                            setStatus('Gagal: <code>' + tmpF.innerHTML.substring(0, 300) + '</code> — kirim pesan ini agar diperbaiki.', 'danger');
                            setManualStep({ active: 0, done: [1], failed: 2 });
                        } else if (st.status === 'DONE') {
                            clearInterval(pollTimer); pollTimer = null; busy = false;
                            finished = true;
                            // Finish: process TETAP disable, cancel disable. Hanya Reset + Download yang available.
                            processBtn.disabled = true; cancelBtn.disabled = true;
                            setProgress(100);
                            let doneMsg = 'Selesai (ALL manual). File siap diunduh.';
                            if (st && (st.pages !== undefined || st.skipped_blank !== undefined)) {
                                const pgCount = (st.pages !== undefined) ? st.pages : '?';
                                const skCount = (st.skipped_blank !== undefined) ? st.skipped_blank : 0;
                                doneMsg = 'Selesai (ALL manual). Hasil <strong>' + pgCount + ' page</strong>' +
                                    (skCount > 0 ? ' (<strong>' + skCount + ' blank dibuang</strong>)' : ' (tidak ada blank)');
                                if (st.volumes && st.volumes.length > 1) {
                                    doneMsg += ' dalam <strong>' + st.volumes.length + ' file</strong>';
                                }
                                doneMsg += '. Klik Download lalu Reset untuk job baru.';
                            } else {
                                doneMsg = 'Selesai (ALL manual). Blank otomatis dibuang. Klik Download lalu Reset untuk job baru.';
                            }
                            setStatus(doneMsg, 'success');
                            setManualStep({ active: 0, done: [1, 2, 3], failed: null });
                            if (st.volumes && st.volumes.length) {
                                let html = '';
                                st.volumes.forEach(function(v, idx) {
                                    html += '<a href="' + v.download + '" download class="btn btn-success btn-sm m-1"><i class="fas fa-download"></i> Download ' + (idx + 1) + '</a>';
                                });
                                downloadBox.innerHTML = html;
                            } else {
                                downloadBox.innerHTML = '<a href="' + st.download + '" download class="btn btn-success"><i class="fas fa-download"></i> Download (ALL manual)</a>';
                            }
                        } else {
                            const cur = parseFloat(progressBar.style.width) || 35;
                            if (cur < 95) setProgress(cur + (95 - cur) * 0.06);
                            if (st.parts_done !== undefined && st.parts_total) {
                                setStatus('Generating manual... bagian <strong>' + st.parts_done + '/' + st.parts_total + '</strong> selesai · job <code>' + jobId + '</code>', 'info');
                            } else {
                                setStatus('Generating manual... job <code>' + jobId + '</code> · cek ke-' + attempts, 'info');
                            }
                            if (attempts >= 240) {
                                clearInterval(pollTimer); pollTimer = null; busy = false;
                                processBtn.disabled = false; cancelBtn.disabled = true;
                                const mi2 = document.getElementById('allManualFilesInput');
                                if (mi2) mi2.disabled = false;
                                const sc2 = document.getElementById('allManualSyncChk');
                                if (sc2) sc2.disabled = false;
                                const ac2 = document.getElementById('allManualAutoChk');
                                if (ac2) ac2.disabled = false;
                                setStatus('Timeout ~10 menit.', 'danger');
                                setManualStep({ active: 0, done: [1], failed: 2 });
                            }
                        }
                    } catch (e) { console.error(e); }
                }, 2500);
            }

            if (processBtn) processBtn.addEventListener('click', function() {
                if (busy) return;
                if (anyRowRunning()) { setStatus('Tunggu proses satuan selesai dulu.', 'warning'); return; }
                if (allFiles.length === 0) { alert('Pilih PDF dulu di form atas.'); return; }
                const cuts = buildCuts();
                const vcuts = buildVCuts();
                // File besar (>6MB total): pecah per bagian di browser agar tiap POST di bawah post_max_size.
                const totalBytes = allFiles.reduce(function(s, f) { return s + (f.size || 0); }, 0);
                if (totalBytes > CHUNK_THRESHOLD) {
                    if (!window.PDFLib) { alert('File besar butuh pdf-lib (CDN). Cek internet lalu coba lagi.'); return; }
                    busy = true; processBtn.disabled = true; cancelBtn.disabled = false;
                    finished = false;
                    const miC0 = document.getElementById('allManualFilesInput');
                    if (miC0) miC0.disabled = true;
                    const scC0 = document.getElementById('allManualSyncChk');
                    if (scC0) scC0.disabled = true;
                    const acC0 = document.getElementById('allManualAutoChk');
                    if (acC0) acC0.disabled = true;
                    downloadBox.innerHTML = '';
                    progressWrap.classList.remove('d-none');
                    setProgress(2);
                    setManualStep({ active: 2, done: [1], failed: null });
                    setStatus('File besar terdeteksi — upload per bagian...', 'info');
                    startChunkedUpload(cuts, vcuts).catch(function(e) {
                        chunking = false;
                        console.error(e);
                        const msg = (e && e.cancelled) ? 'Dibatalkan.' :
                            ('Gagal upload per bagian: ' + ((e && e.message) || e));
                        busy = false; processBtn.disabled = false; cancelBtn.disabled = true;
                        const miCE = document.getElementById('allManualFilesInput');
                        if (miCE) miCE.disabled = false;
                        const scCE = document.getElementById('allManualSyncChk');
                        if (scCE) scCE.disabled = false;
                        const acCE = document.getElementById('allManualAutoChk');
                        if (acCE) acCE.disabled = false;
                        setStatus(msg, (e && e.cancelled) ? 'warning' : 'danger');
                        setManualStep((e && e.cancelled) ? { active: 1, done: [], failed: null } : { active: 0, done: [1], failed: 2 });
                    });
                    return;
                }
                const fd = new FormData();
                allFiles.forEach(function(f) { fd.append('files[]', f); });
                fd.append('cuts', JSON.stringify(cuts));
                fd.append('vcuts', JSON.stringify(vcuts));
                fd.append('skip_blank', '1'); // permanent: selalu buang halaman blank
                fd.append('reverse', '1'); // permanent: hasil split selalu dibalik (nama file tetap)
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                busy = true; processBtn.disabled = true; cancelBtn.disabled = false;
                finished = false;
                const mi0 = document.getElementById('allManualFilesInput');
                if (mi0) mi0.disabled = true;
                const sc0 = document.getElementById('allManualSyncChk');
                if (sc0) sc0.disabled = true;
                const ac0 = document.getElementById('allManualAutoChk');
                if (ac0) ac0.disabled = true;
                downloadBox.innerHTML = '';
                progressWrap.classList.remove('d-none');
                setProgress(5);
                setManualStep({ active: 2, done: [1], failed: null });
                setStatus('Step 1/2 — Uploading manual (ALL)...', 'info');
                const xhr = new XMLHttpRequest();
                currentXhr = xhr;
                xhr.open('POST', PROCESS_URL, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) setProgress(Math.round(e.loaded / e.total * 30));
                });
                xhr.onreadystatechange = function() {
                    if (xhr.readyState !== 4) return;
                    currentXhr = null;
                    const reenable = function() {
                        busy = false; processBtn.disabled = false; cancelBtn.disabled = true;
                        const miR = document.getElementById('allManualFilesInput');
                        if (miR) miR.disabled = false;
                        const scR = document.getElementById('allManualSyncChk');
                        if (scR) scR.disabled = false;
                        const acR = document.getElementById('allManualAutoChk');
                        if (acR) acR.disabled = false;
                    };
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let r = null;
                        try { r = JSON.parse(xhr.responseText); } catch (e) { r = null; }
                        if (r && r.job_id) {
                            currentJobId = r.job_id;
                            setProgress(35);
                            setStatus('Step 2/2 — Generating manual...', 'info');
                            pollStatus(r.job_id);
                        } else {
                            reenable();
                            const raw = (xhr.responseText || '').trim();
                            console.error('Manual upload bad response:', xhr.status, raw.substring(0, 500));
                            if (/^\s*</.test(raw) || /<\s*html/i.test(raw)) {
                                setStatus('Server mengembalikan halaman HTML, bukan JSON — kemungkinan <strong>sesi habis / belum login</strong>. Refresh halaman (Ctrl+F5), login ulang, lalu coba lagi.', 'danger');
                            } else if (!raw) {
                                setStatus('Server balas kosong (HTTP ' + xhr.status + '). Coba lagi; bila tetap, cek koneksi / restart queue worker.', 'danger');
                            } else {
                                const safe = raw.replace(/<[^>]*>/g, '').substring(0, 200);
                                const tmp = document.createElement('div');
                                tmp.textContent = safe;
                                setStatus('Response tidak valid (HTTP ' + xhr.status + '). Isi: <code>' + tmp.innerHTML + '</code>', 'danger');
                            }
                            setManualStep({ active: 0, done: [1], failed: 2 });
                        }
                    } else {
                        let msg = 'Upload gagal (HTTP ' + xhr.status + ').';
                        try {
                            const j = JSON.parse(xhr.responseText);
                            if (j.errors) {
                                const firstKey = Object.keys(j.errors)[0];
                                if (firstKey && j.errors[firstKey] && j.errors[firstKey][0]) msg += ' ' + j.errors[firstKey][0];
                                else if (j.message) msg += ' ' + j.message;
                            } else if (j.message) msg += ' ' + j.message;
                        } catch (e) {}
                        if (xhr.status === 419) msg = 'Sesi kedaluwarsa (CSRF). Refresh halaman (Ctrl+F5) lalu coba lagi.';
                        else if (xhr.status === 401) msg = 'Belum login / sesi habis. Refresh halaman dan login ulang.';
                        else if (xhr.status === 413) msg = 'File terlalu besar untuk server. Kecilkan / pecah PDF lalu coba lagi.';
                        reenable();
                        setStatus(msg, 'danger');
                        setManualStep({ active: 0, done: [1], failed: 2 });
                    }
                };
                xhr.onerror = function() {
                    busy = false; processBtn.disabled = false; cancelBtn.disabled = true;
                    const miG = document.getElementById('allManualFilesInput');
                    if (miG) miG.disabled = false;
                    const scG = document.getElementById('allManualSyncChk');
                    if (scG) scG.disabled = false;
                    const acG = document.getElementById('allManualAutoChk');
                    if (acG) acG.disabled = false;
                    setStatus('System error saat uploading manual.', 'danger');
                    setManualStep({ active: 0, done: [1], failed: 2 });
                };
                xhr.send(fd);
            });

            if (cancelBtn) cancelBtn.addEventListener('click', async function() {
                if (!busy) return;
                if (!confirm('Batalkan proses manual ini?')) return;
                if (chunking) { chunkCancelled = true; if (chunkAbort) { try { chunkAbort.abort(); } catch (e) {} chunkAbort = null; } }
                if (currentXhr) { try { currentXhr.abort(); } catch (e) {} currentXhr = null; }
                if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
                if (currentJobId) {
                    try {
                        await fetch('/dashboard/kanban/cancel/' + currentJobId, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
                        });
                    } catch (e) {}
                    currentJobId = null;
                }
                busy = false; processBtn.disabled = false; cancelBtn.disabled = true;
                const miC = document.getElementById('allManualFilesInput');
                if (miC) miC.disabled = false;
                const scC = document.getElementById('allManualSyncChk');
                if (scC) scC.disabled = false;
                const acC = document.getElementById('allManualAutoChk');
                if (acC) acC.disabled = false;
                setStatus('Dibatalkan.', 'warning');
                setManualStep({ active: 1, done: [], failed: null });
            });
            // Download gabungan diklik semua → reset otomatis agar siap job baru.
            if (downloadBox) downloadBox.addEventListener('click', function(e) {
                const a = e.target && e.target.closest ? e.target.closest('a') : null;
                if (!a || !resetPreviewBtn) return;
                a.dataset.dl = '1';
                const links = downloadBox.querySelectorAll('a');
                const all = links.length > 0 && Array.from(links).every(function(l) { return l.dataset.dl === '1'; });
                if (all) setTimeout(function() { resetPreviewBtn.click(); }, 1500);
            });
            // Init stepper manual: mulai di Setup.
            setManualStep({ active: 1, done: [], failed: null });
        })();
    </script>
    @endif
@endsection
