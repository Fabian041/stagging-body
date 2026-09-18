@extends('layouts.root.dashboard')

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
            margin-top: -104px !important;
            position: relative;
            z-index: 2;
        }

        #customerNav {
            margin-top: 10px !important;
        }
    </style>

    <div class="container-fluid">

        {{-- NAV customer (statis: TMMIN, MMKI & MANUAL). Tab MANUAL = customer ALL, khusus manual crop. --}}
        <ul class="nav nav-pills mb-3" id="customerNav">
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
            <!-- <li class="nav-item">
                <a class="nav-link {{ $isAll ? 'active' : '' }}"
                    href="{{ route('dashboard.kanbanUpload', ['customer' => 'ALL']) }}">
                    <i class="fas fa-cut"></i> MANUAL

                </a>
            </li> -->
        </ul>



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
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
                <div>
                    <h4 class="mb-0"><i class="fas fa-cut text-danger"></i> Form Manual</h4>
                </div>
                <span class="badge badge-danger" id="allManualCount">0 garis</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Pilih PDF Kanban</label>
                    <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                        <input type="file" id="allManualFilesInput" multiple accept="application/pdf,.pdf" class="form-control" style="max-width:420px;">
                        <button id="allManualResetPreviewBtn" class="btn btn-outline-secondary btn-sm" type="button">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    <small class="text-muted">Hanya PDF, maks 20MB per file.</small>
                </div>
                <div class="d-flex flex-wrap mb-3" style="gap:16px;">
                    <label class="mb-0" style="font-size:13px;font-weight:700;">
                        <input type="checkbox" id="allManualSyncChk" checked> Copy garis page 1 ke semua page
                    </label>
                    <label class="mb-0" style="font-size:13px;font-weight:700;">
                        <input type="checkbox" id="allManualAutoChk" checked> Auto-lanjutkan garis ke bawah
                    </label>
                </div>
                {{-- Tombol di atas agar mudah dijangkau --}}
                <div class="action-row">
                    <div class="action-left">
                        <button id="allManualProcessBtn" class="btn btn-danger btn-equal" type="button">
                            <i class="fas fa-cut"></i> Process Manual
                        </button>
                        <button id="allManualCancelBtn" class="btn btn-outline-danger btn-equal" type="button" disabled>
                            <i class="fas fa-ban"></i> Cancel Manual
                        </button>
                    </div>
                    <div class="action-right">
                        <span id="allManualDownload"></span>
                    </div>
                </div>
                <div class="progress my-3 d-none" id="allManualProgressWrap" style="height:22px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" id="allManualProgressBar" role="progressbar" style="width:0%">0%</div>
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
        .allm-page { border: 1px solid #F3B4B4; border-radius: 14px; overflow: hidden; background: #FFF7F7; }
        .allm-page-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 10px 12px; background: #FEE2E2; border-bottom: 1px solid #F3B4B4; font-size: 13px; font-weight: 800; color: #7F1D1D; }
        .allm-page-head .btn { min-width: 0; }
        .allm-canvas-wrap { position: relative; margin: 12px auto; width: fit-content; max-width: 100%; box-shadow: 0 10px 28px rgba(220,38,38,.14); background: #fff; }
        .allm-canvas-wrap canvas { display: block; max-width: 100%; height: auto; }
        .allm-overlay { position: absolute; inset: 0; cursor: crosshair; touch-action: none; }
        .allm-line { position: absolute; left: 0; right: 0; height: 0; border-top: 3px solid #DC2626; cursor: ns-resize; touch-action: none; z-index: 5; filter: drop-shadow(0 2px 4px rgba(220,38,38,.5)); }
        .allm-line::before { content: ''; position: absolute; left: 0; right: 0; top: -14px; height: 28px; }
        .allm-line-tag { position: absolute; right: 6px; top: -15px; display: flex; align-items: center; gap: 6px; background: #DC2626; color: #fff; font-size: 11px; font-weight: 800; border-radius: 999px; padding: 3px 6px 3px 10px; white-space: nowrap; }
        .allm-line-tag button { border: 0; border-radius: 999px; background: #fff; color: #DC2626; font-weight: 900; width: 26px; height: 26px; line-height: 1; font-size: 15px; cursor: pointer; touch-action: manipulation; }
        .allm-line, .allm-line-tag { user-select: none; -webkit-user-select: none; }
        .allm-line.auto { border-top: 3px dashed #F59E0B; cursor: default; filter: drop-shadow(0 2px 4px rgba(245,158,11,.5)); }
        .allm-line-tag.auto { background: #F59E0B; }
        .allm-page.is-master { border-color: #DC2626; box-shadow: 0 0 0 2px rgba(220,38,38,.25); }
        .allm-page.is-locked .allm-overlay { cursor: not-allowed; }
        .allm-master-badge { background: #DC2626; color: #fff; font-size: 10px; font-weight: 900; border-radius: 999px; padding: 2px 10px; }
        .allm-lock-badge { background: #6B7A99; color: #fff; font-size: 10px; font-weight: 900; border-radius: 999px; padding: 2px 10px; }
        .allm-seg-hint { position: absolute; left: 6px; background: rgba(26,35,64,.72); color: #fff; font-size: 10px; font-weight: 800; border-radius: 8px; padding: 2px 8px; pointer-events: none; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function() {
            if (!document.getElementById('allManualCard')) return;
            if (window.pdfjsLib) {
                try { window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'; } catch (e) {}
            }
            const filesInput = document.getElementById('files');
            const listEl = document.getElementById('allManualList');
            const emptyEl = document.getElementById('allManualEmpty');
            const countEl = document.getElementById('allManualCount');
            const processBtn = document.getElementById('allManualProcessBtn');
            const cancelBtn = document.getElementById('allManualCancelBtn');
            const statusText = document.getElementById('allManualStatusText');
            const statusBox = document.getElementById('allManualStatusBox');
            const progressWrap = document.getElementById('allManualProgressWrap');
            const progressBar = document.getElementById('allManualProgressBar');
            const downloadBox = document.getElementById('allManualDownload');
            const PROCESS_URL = "{{ route('dashboard.kanbanAllManualProcess') }}";

            let allFiles = []; // File[]
            let pages = [];    // {key,fileIdx,fileName,pageNum,cuts:[],wrap,overlay}
            let busy = false, pollTimer = null, currentJobId = null, currentXhr = null;
            let finished = false; // true setelah DONE: hanya Reset + Download yang available
            function editingLocked() { return busy || finished; }

            function setStatus(msg, kind) {
                statusText.innerHTML = msg;
                statusBox.className = 'alert mb-0 alert-' + (kind || 'secondary');
            }
            function setProgress(p) {
                const v = Math.max(0, Math.min(100, Math.round(p)));
                progressBar.style.width = v + '%';
                progressBar.textContent = v + '%';
            }
            function refreshCount() {
                let garis = 0, hasil = 0;
                pages.forEach(function(p) { garis += p.cuts.length; hasil += (p.cuts.length + 1); });
                if (countEl) countEl.textContent = garis + ' garis → ' + hasil + ' page hasil';
            }
            function fileKey(f) { return f.name + '|' + f.size + '|' + f.lastModified; }
            function isSyncOn() { const c = document.getElementById('allManualSyncChk'); return c ? c.checked : true; }
            function isAutoOn() { const c = document.getElementById('allManualAutoChk'); return c ? c.checked : true; }
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
                        let next = b[1] + d;
                        let guard = 0;
                        while (next < 0.98 && guard < 30) {
                            const last = final[final.length - 1];
                            if (Math.abs(next - last) >= 0.025) { final.push(next); auto.push(next); }
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
                for (let i = 1; i < pages.length; i++) {
                    pages[i].base = masterBase.slice();
                    renderPageLines(pages[i], true);
                }
                refreshCount();
            }

            function renderPageLines(pg, skipSync) {
                pg.base = normBase(pg.base);
                const comp = computeFinal(pg.base);
                pg.base = comp.base;
                pg.cuts = comp.final;
                pg.autoCuts = comp.auto;
                const autoSet = {};
                comp.auto.forEach(function(v) { autoSet[v.toFixed(4)] = true; });
                pg.overlay.querySelectorAll('.allm-line,.allm-seg-hint').forEach(function(n) { n.remove(); });
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
                // hint segmen hasil
                const bounds = [0].concat(pg.cuts).concat([1]);
                for (let i = 0; i < bounds.length - 1; i++) {
                    const mid = (bounds[i] + bounds[i + 1]) / 2;
                    const hint = document.createElement('div');
                    hint.className = 'allm-seg-hint';
                    hint.style.top = 'calc(' + (mid * 100) + '% - 10px)';
                    hint.textContent = 'hasil ' + (i + 1);
                    pg.overlay.appendChild(hint);
                }
            }

            function addPageDom(fileIdx, fileName, pageNum, canvas) {
                const key = fileIdx + ':' + pageNum;
                const card = document.createElement('div');
                card.className = 'allm-page';
                card.innerHTML = '<div class="allm-page-head"><span></span><span class="allm-sync-badge"></span><span style="display:flex;gap:8px;flex-wrap:wrap;">' +
                    '<button type="button" class="btn btn-sm btn-danger allm-add">+ Garis tengah</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger allm-clear">Clear</button></span></div>';
                card.querySelector('span').textContent = fileName + ' — hal ' + pageNum;
                const wrap = document.createElement('div');
                wrap.className = 'allm-canvas-wrap';
                wrap.appendChild(canvas);
                const overlay = document.createElement('div');
                overlay.className = 'allm-overlay';
                wrap.appendChild(overlay);
                card.appendChild(wrap);
                listEl.appendChild(card);
                // Default 2 garis; page 2+ copy posisi page 1.
                let initBase = [1 / 3, 2 / 3];
                if (isSyncOn() && pages.length > 0) initBase = (pages[0].base || [1 / 3, 2 / 3]).slice();
                const pg = { key: key, fileIdx: fileIdx, fileName: fileName, pageNum: pageNum, base: initBase, cuts: [], autoCuts: [], card: card, wrap: wrap, overlay: overlay };
                pages.push(pg);
                card.querySelector('.allm-add').addEventListener('click', function() {
                    if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                    if (isSyncOn() && !isMaster(pg)) { setStatus('Matikan checkbox copy agar bisa edit page ini satuan.', 'warning'); return; }
                    pg.base.push(0.5);
                    renderPageLines(pg); refreshCount();
                    if (isSyncOn() && isMaster(pg)) syncFromMaster();
                });
                card.querySelector('.allm-clear').addEventListener('click', function() {
                    if (editingLocked()) { setStatus(finished ? 'Sudah finish. Klik Reset untuk job baru.' : 'Tunggu proses selesai dulu.', 'warning'); return; }
                    if (isSyncOn() && !isMaster(pg)) { setStatus('Matikan checkbox copy agar bisa edit page ini satuan.', 'warning'); return; }
                    pg.base = []; renderPageLines(pg); refreshCount();
                    if (isSyncOn() && isMaster(pg)) syncFromMaster();
                });
                overlay.addEventListener('dblclick', function(ev) {
                    if (editingLocked()) return;
                    if (ev.target && ev.target.closest && ev.target.closest('.allm-line')) return;
                    if (isSyncOn() && !isMaster(pg)) return;
                    const r = overlay.getBoundingClientRect();
                    let y = (ev.clientY - r.top) / r.height;
                    y = Math.max(0.02, Math.min(0.98, y));
                    pg.base.push(y);
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
                    for (let n = 1; n <= pdf.numPages; n++) {
                        try {
                            const page = await pdf.getPage(n);
                            const viewport = page.getViewport({ scale: 1.3 });
                            const canvas = document.createElement('canvas');
                            canvas.width = Math.floor(viewport.width);
                            canvas.height = Math.floor(viewport.height);
                            const ctx = canvas.getContext('2d');
                            await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                            addPageDom(fileIdx, file.name, n, canvas);
                        } catch (e) { console.error(e); }
                    }
                    setStatus(allFiles.length + ' file, ' + pages.length + ' halaman di-preview. Atur garis merah lalu klik Process Manual.', 'info');
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
                if (busy) return;
                allFiles = []; pages = [];
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

            function buildCuts() {
                const out = {};
                pages.forEach(function(pg) {
                    if (!pg.cuts.length) return;
                    if (!out[pg.fileIdx]) out[pg.fileIdx] = {};
                    out[pg.fileIdx][pg.pageNum] = pg.cuts.slice().sort(function(a,b){return a-b;});
                });
                return out;
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
                                    (skCount > 0 ? ' (<strong>' + skCount + ' blank dibuang</strong>)' : ' (tidak ada blank)') +
                                    '. Klik Download lalu Reset untuk job baru.';
                            } else {
                                doneMsg = 'Selesai (ALL manual). Blank otomatis dibuang. Klik Download lalu Reset untuk job baru.';
                            }
                            setStatus(doneMsg, 'success');
                            downloadBox.innerHTML = '<a href="' + st.download + '" download class="btn btn-success"><i class="fas fa-download"></i> Download (ALL manual)</a>';
                        } else {
                            const cur = parseFloat(progressBar.style.width) || 35;
                            if (cur < 95) setProgress(cur + (95 - cur) * 0.06);
                            setStatus('Generating manual... job <code>' + jobId + '</code> · cek ke-' + attempts, 'info');
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
                            }
                        }
                    } catch (e) { console.error(e); }
                }, 2500);
            }

            if (processBtn) processBtn.addEventListener('click', function() {
                if (busy) return;
                if (allFiles.length === 0) { alert('Pilih PDF dulu di form atas.'); return; }
                const cuts = buildCuts();
                const fd = new FormData();
                allFiles.forEach(function(f) { fd.append('files[]', f); });
                fd.append('cuts', JSON.stringify(cuts));
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
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let r = null;
                        try { r = JSON.parse(xhr.responseText); } catch (e) {}
                        if (r && r.job_id) {
                            currentJobId = r.job_id;
                            setProgress(35);
                            setStatus('Step 2/2 — Generating manual...', 'info');
                            pollStatus(r.job_id);
                        } else {
                            busy = false; processBtn.disabled = false; cancelBtn.disabled = true;
                            const miE = document.getElementById('allManualFilesInput');
                            if (miE) miE.disabled = false;
                            const scE = document.getElementById('allManualSyncChk');
                            if (scE) scE.disabled = false;
                            const acE = document.getElementById('allManualAutoChk');
                            if (acE) acE.disabled = false;
                            setStatus('Response tidak valid.', 'danger');
                        }
                    } else {
                        let msg = 'Upload gagal (HTTP ' + xhr.status + ').';
                        try { const j = JSON.parse(xhr.responseText); if (j.message) msg += ' ' + j.message; } catch (e) {}
                        busy = false; processBtn.disabled = false; cancelBtn.disabled = true;
                        const miF = document.getElementById('allManualFilesInput');
                        if (miF) miF.disabled = false;
                        const scF = document.getElementById('allManualSyncChk');
                        if (scF) scF.disabled = false;
                        const acF = document.getElementById('allManualAutoChk');
                        if (acF) acF.disabled = false;
                        setStatus(msg, 'danger');
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
                };
                xhr.send(fd);
            });

            if (cancelBtn) cancelBtn.addEventListener('click', async function() {
                if (!busy) return;
                if (!confirm('Batalkan proses manual ini?')) return;
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
            });
        })();
    </script>
    @endif
@endsection
