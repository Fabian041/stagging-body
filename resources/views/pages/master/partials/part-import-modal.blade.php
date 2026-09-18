<div class="modal fade master-import-modal" id="partImportModal" tabindex="-1" role="dialog"
    aria-labelledby="partImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.part.import') }}" method="POST" enctype="multipart/form-data"
                id="partImportForm">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="partImportModalLabel">Import Master Part</h5>
                        <div class="text-muted small">Tambah internal part dan mapping customer melalui spreadsheet.</div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="file" id="partImportFile" name="file" accept=".xlsx,.xls,.csv" hidden required>

                    <div class="master-import-dropzone" id="partImportDropzone" role="button" tabindex="0">
                        <span class="master-import-icon"><i class="fas fa-file-excel"></i></span>
                        <strong>Pilih file Master Part</strong>
                        <span>.xlsx, .xls, atau .csv dengan ukuran maksimal 10 MB</span>
                    </div>

                    <div class="master-import-file" id="partImportFileCard">
                        <span class="master-import-icon mb-0"><i class="fas fa-file-excel"></i></span>
                        <div class="master-import-file-meta">
                            <div class="master-import-file-name" id="partImportFileName">-</div>
                            <div class="master-import-file-size" id="partImportFileSize">-</div>
                        </div>
                        <button type="button" class="master-btn" id="partImportRemove" title="Hapus file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="alert alert-danger master-alert mt-3 mb-0 d-none" id="partImportError"></div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('dashboard.part.template') }}" class="master-btn mr-auto">
                        <i class="fas fa-download"></i> Template Excel
                    </a>
                    <button type="button" class="master-btn" data-dismiss="modal">Batal</button>
                    <button type="submit" class="master-btn primary" id="partImportSubmit" disabled>
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
