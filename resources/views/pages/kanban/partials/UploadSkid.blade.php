{{-- ===== FORM AREA: UPLOAD SKID ===== --}}
<div id="formAreaUploadSkid" class="upload-form-pane" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-boxes"></i>
                Form Upload Skid
            </h4>
            <small class="text-muted">Form upload skid customer order (PDF langsung tanpa preview atau crop)</small>
        </div>

        <div class="card-body" id="skidCardBody">
            <div class="form-group">
                <label>Select Skid PDF</label>
                <div class="d-flex align-items-center" style="gap:10px;">
                    <input type="file" id="skidFiles" multiple accept="application/pdf,.pdf" class="form-control">
                </div>
                <small class="text-muted">
                    Only PDF file allowed. Bisa pilih berkali-kali — file akan menumpuk di daftar. Maks 20MB per file.
                </small>
            </div>

            <div id="skidFileList" class="mb-3">
                <small class="text-muted">Belum ada file dipilih.</small>
            </div>

            <div class="progress mb-3 d-none" id="skidUploadProgressWrap" style="height:22px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="skidUploadProgressBar"
                    role="progressbar" style="width:0%">0%</div>
            </div>

            <div class="action-row">
                <div class="action-left">
                    <button id="skidUploadBtn" class="btn btn-primary btn-equal" type="button">
                        <i class="fas fa-upload"></i> Process
                    </button>
                    <button id="skidResetBtn" class="btn btn-outline-secondary btn-equal" type="button">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <div id="skidStepStatus" class="mt-3">
                <div class="alert alert-secondary mb-0" id="skidStatusBox">
                    <strong>Status:</strong> <span id="skidStatusText">Siap. Pilih PDF lalu klik Process.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #skidFileList .file-row {
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

    #skidFileList button:disabled {
        cursor: not-allowed !important;
        opacity: .5 !important;
    }

    #skidCardBody.is-locked #skidFileList {
        opacity: .65;
    }

    #skidCardBody.is-locked .file-row {
        background: #F1F5F9;
    }
</style>

<script>
    (function() {
        var filesInput = document.getElementById('skidFiles');
        var fileList = document.getElementById('skidFileList');
        var uploadBtn = document.getElementById('skidUploadBtn');
        var resetBtn = document.getElementById('skidResetBtn');
        var statusBox = document.getElementById('skidStatusBox');
        var statusText = document.getElementById('skidStatusText');
        var progressWrap = document.getElementById('skidUploadProgressWrap');
        var progressBar = document.getElementById('skidUploadProgressBar');
        var cardBody = document.getElementById('skidCardBody');

        var selectedFiles = [];
        var isBusy = false;
        var currentXhr = null;

        function setStatus(text, kind) {
            kind = kind || 'secondary';
            if (statusBox) {
                statusBox.className = 'alert alert-' + kind + ' mb-0';
            }
            if (statusText) {
                statusText.textContent = text;
            }
        }

        function setBusy(busy) {
            isBusy = busy;
            if (uploadBtn) uploadBtn.disabled = busy;
            if (filesInput) filesInput.disabled = busy;
            if (cardBody) cardBody.classList.toggle('is-locked', busy);
            if (fileList) {
                fileList.querySelectorAll('button').forEach(function(b) {
                    b.disabled = busy;
                });
            }
        }

        function setProgress(pct) {
            if (!progressWrap || !progressBar) return;
            pct = Math.min(100, Math.max(0, Math.round(pct)));
            progressWrap.classList.remove('d-none');
            progressBar.style.width = pct + '%';
            progressBar.textContent = pct + '%';
        }

        function hideProgress() {
            if (!progressWrap || !progressBar) return;
            progressWrap.classList.add('d-none');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
        }

        function renderFiles() {
            if (!fileList) return;
            fileList.innerHTML = '';
            if (selectedFiles.length === 0) {
                fileList.innerHTML = '<small class="text-muted">Belum ada file dipilih.</small>';
                return;
            }

            selectedFiles.forEach(function(f, idx) {
                var row = document.createElement('div');
                row.className = 'file-row';
                var ok = /\.pdf$/i.test(f.name);
                row.innerHTML = '<span><i class="fas fa-file-pdf text-danger"></i> ' +
                    '<strong></strong> <span class="text-muted"></span></span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger">Hapus</button>';
                row.querySelector('strong').textContent = f.name;
                row.querySelector('.text-muted').textContent = ' (' + (f.size / 1024 / 1024).toFixed(2) +
                    ' MB' + (ok ? '' : ' · bukan PDF?') + ')';

                var delBtn = row.querySelector('button');
                delBtn.addEventListener('click', function() {
                    if (isBusy) return;
                    selectedFiles.splice(idx, 1);
                    renderFiles();
                    if (selectedFiles.length === 0) {
                        setStatus('Siap. Pilih PDF lalu klik Process.', 'secondary');
                    }
                });
                if (isBusy) delBtn.disabled = true;

                fileList.appendChild(row);
            });
        }

        if (filesInput) {
            filesInput.addEventListener('change', function() {
                var incoming = Array.from(filesInput.files || []);
                if (typeof window.normalizeFile === 'function') {
                    incoming = incoming.map(window.normalizeFile);
                }

                var invalidFiles = [];
                var oversizedFiles = [];

                incoming.forEach(function(f) {
                    if (!f || !f.name) return;
                    if (!/\.pdf$/i.test(f.name)) {
                        invalidFiles.push(f.name);
                        return;
                    }
                    if (f.size > 20 * 1024 * 1024) {
                        oversizedFiles.push(f.name);
                        return;
                    }
                    if (selectedFiles.some(function(existing) { return existing.name === f.name; })) {
                        return;
                    }
                    selectedFiles.push(f);
                });

                // Clear input value so re-selecting triggers change
                filesInput.value = '';
                renderFiles();

                if (invalidFiles.length > 0) {
                    setStatus('File diabaikan (harus PDF): ' + invalidFiles.join(', '), 'warning');
                } else if (oversizedFiles.length > 0) {
                    setStatus('File diabaikan (melebihi 20MB): ' + oversizedFiles.join(', '), 'warning');
                } else if (selectedFiles.length > 0) {
                    setStatus(selectedFiles.length + ' file dipilih. Klik Process untuk mengunggah.', 'info');
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (currentXhr) {
                    try { currentXhr.abort(); } catch (e) {}
                    currentXhr = null;
                }
                selectedFiles = [];
                if (filesInput) filesInput.value = '';
                renderFiles();
                hideProgress();
                setBusy(false);
                setStatus('Siap. Pilih PDF lalu klik Process.', 'secondary');
            });
        }

        if (uploadBtn) {
            uploadBtn.addEventListener('click', function() {
                if (isBusy) return;
                if (selectedFiles.length === 0) {
                    setStatus('Pilih minimal 1 file PDF sebelum memproses.', 'warning');
                    return;
                }

                setBusy(true);
                setProgress(0);
                setStatus('Mengunggah ' + selectedFiles.length + ' file skid...', 'info');

                var fd = new FormData();
                selectedFiles.forEach(function(file) {
                    fd.append('files[]', file, file.name);
                });
                fd.append('_token', '{{ csrf_token() }}');

                var xhr = new XMLHttpRequest();
                currentXhr = xhr;

                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        var pct = Math.round((e.loaded / e.total) * 100);
                        setProgress(pct);
                    }
                };

                xhr.onload = function() {
                    currentXhr = null;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res && res.status) {
                                setProgress(100);
                                setStatus(res.message || 'Berhasil mengunggah ' + selectedFiles.length + ' file skid.', 'success');
                                selectedFiles = [];
                                renderFiles();
                                setTimeout(function() {
                                    hideProgress();
                                }, 1500);
                            } else {
                                setStatus((res && res.message) ? res.message : 'Gagal memproses file skid.', 'danger');
                            }
                        } catch (e) {
                            setStatus('Gagal membaca respons dari server.', 'danger');
                        }
                    } else {
                        var msg = 'Upload gagal (HTTP ' + xhr.status + ').';
                        try {
                            var j = JSON.parse(xhr.responseText);
                            if (j.errors && typeof j.errors === 'object') {
                                var firstKey = Object.keys(j.errors)[0];
                                if (firstKey && j.errors[firstKey] && j.errors[firstKey][0]) {
                                    msg = j.errors[firstKey][0];
                                } else if (j.message) {
                                    msg = j.message;
                                }
                            } else if (j.message) {
                                msg = j.message;
                            }
                        } catch (e) {}
                        setStatus(msg, 'danger');
                    }
                    setBusy(false);
                };

                xhr.onerror = function() {
                    currentXhr = null;
                    setStatus('Terjadi kesalahan jaringan saat mengunggah file.', 'danger');
                    setBusy(false);
                };

                xhr.open('POST', '{{ route('dashboard.skidUploadProcess') }}', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.send(fd);
            });
        }
    })();
</script>
