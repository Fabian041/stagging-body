@extends('layouts.root.dashboard')

@section('main')
    @php
        $customer = strtoupper($customer ?? request()->query('customer', 'TMMIN'));
        if (!in_array($customer, ['TMMIN', 'MMKI'], true)) {
            $customer = 'TMMIN';
        }
        $isTmmin = $customer === 'TMMIN';
        $customerTitle = $isTmmin ? 'TMMIN' : 'MMKI';
        $customerSub = $isTmmin
            ? 'PT. Toyota Motor Manufacturing Indonesia'
            : 'PT. Mitsubishi Motors Krama Yudha Indonesia';
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

        {{-- NAV customer (statis: TMMIN & MMKI). Form sama, header beda. --}}
        <ul class="nav nav-pills mb-3" id="customerNav">
            <li class="nav-item">
                <a class="nav-link {{ $isTmmin ? 'active' : '' }}"
                    href="{{ route('dashboard.kanbanUpload', ['customer' => 'TMMIN']) }}">
                    <i class="fas fa-industry"></i> TMMIN

                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ !$isTmmin ? 'active' : '' }}"
                    href="{{ route('dashboard.kanbanUpload', ['customer' => 'MMKI']) }}">
                    <i class="fas fa-industry"></i> MMKI

                </a>
            </li>
        </ul>



        <div class="card">
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
@endsection
