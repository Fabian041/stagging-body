<style>
    .master-page {
        padding-top: 18px !important;
        padding-bottom: 30px !important;
    }

    .master-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .master-page-title {
        margin: 0;
        color: var(--navy, #294795);
        font-size: 23px;
        font-weight: 800;
        line-height: 1.25;
    }

    .master-page-subtitle {
        margin-top: 4px;
        color: var(--text-muted, #64748b);
        font-size: 12.5px;
        line-height: 1.5;
    }

    .master-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .master-btn {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 7px 12px;
        color: var(--text, #0f172a);
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.2;
        background: var(--card, #fff);
        border: 1px solid var(--border, #dce4ef);
        border-radius: 5px;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .master-btn:hover {
        color: var(--primary, #0070b7);
        text-decoration: none;
        background: #f3f8fc;
        border-color: #b9d8ec;
    }

    .master-btn.primary {
        color: #fff;
        background: var(--primary, #0070b7);
        border-color: var(--primary, #0070b7);
    }

    .master-btn.primary:hover {
        color: #fff;
        background: #005f9c;
        border-color: #005f9c;
    }

    .master-btn.success {
        color: #15713b;
        background: #effbf4;
        border-color: #bde9ce;
    }

    .master-btn.danger {
        color: #c62828;
        background: #fff5f5;
        border-color: #f2caca;
    }

    .master-btn.info {
        color: #08749e;
        background: #f0f9fc;
        border-color: #bee2ee;
    }

    .master-panel {
        height: 100%;
        overflow: hidden;
        background: var(--card, #fff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 8px;
        box-shadow: var(--shadow, 0 8px 24px rgba(15, 23, 42, .06));
    }

    .master-panel-header {
        min-height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 18px;
        background: var(--card, #fff);
        border-bottom: 1px solid var(--border, #e2e8f0);
    }

    .master-panel-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: var(--navy, #294795);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .master-panel-title i {
        color: var(--primary, #0070b7);
    }

    .master-panel-body {
        padding: 18px;
    }

    .master-record-count {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        padding: 4px 9px;
        color: #52617a;
        font-size: 10.5px;
        font-weight: 800;
        background: #f1f5f9;
        border-radius: 5px;
        white-space: nowrap;
    }

    .master-form .form-label,
    .master-form .form-group > label {
        margin-bottom: 6px;
        color: #53627d;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .master-form .form-control {
        min-height: 38px;
        color: var(--text, #17203a);
        font-size: 13px;
        background: #fff;
        border: 1px solid #d8e1ee;
        border-radius: 6px;
        box-shadow: none;
    }

    .master-form .form-control:focus {
        border-color: var(--primary, #0070b7);
        box-shadow: 0 0 0 3px rgba(0, 112, 183, .1);
    }

    .master-submit-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        margin-top: 6px;
    }

    .master-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .master-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 10px 16px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border, #e2e8f0);
    }

    .master-segmented {
        display: inline-flex;
        gap: 3px;
        padding: 3px;
        background: #eaf0f5;
        border-radius: 6px;
    }

    .master-segmented button {
        min-height: 29px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        color: #66758e;
        font-size: 11px;
        font-weight: 700;
        background: transparent;
        border: 0;
        border-radius: 4px;
    }

    .master-segmented button.active {
        color: var(--primary, #0070b7);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .1);
    }

    .master-table {
        width: 100% !important;
        margin: 0 !important;
        color: var(--text, #17203a);
        font-size: 12.5px;
        border-collapse: collapse !important;
    }

    .master-table thead th {
        padding: 10px 12px !important;
        color: var(--text-muted, #64748b) !important;
        font-size: 10.5px !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        background: var(--bg, #f8fafc) !important;
        border-top: 0 !important;
        border-bottom: 1px solid var(--border, #e2e8f0) !important;
        white-space: nowrap;
    }

    .master-table tbody td {
        padding: 10px 12px !important;
        vertical-align: middle !important;
        background: #fff;
        border-top: 0 !important;
        border-bottom: 1px solid #edf1f6 !important;
    }

    .master-table tbody tr:hover td {
        background: #f8fbfd;
    }

    .master-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .master-panel .dataTables_wrapper > .row:first-child,
    .master-panel .dataTables_wrapper > .row:last-child {
        margin: 0;
        padding: 12px 16px;
    }

    .master-panel .dataTables_wrapper > .row:first-child {
        border-bottom: 1px solid var(--border, #e2e8f0);
    }

    .master-panel .dataTables_wrapper > .row:last-child {
        border-top: 1px solid var(--border, #e2e8f0);
    }

    .master-panel .dataTables_filter input,
    .master-panel .dataTables_length select {
        min-height: 32px;
        color: var(--text, #17203a);
        font-size: 12px;
        background: #fff;
        border: 1px solid #d8e1ee;
        border-radius: 5px;
        box-shadow: none;
    }

    .master-panel .dataTables_filter label,
    .master-panel .dataTables_length label,
    .master-panel .dataTables_info {
        color: var(--text-muted, #64748b);
        font-size: 11.5px;
    }

    .master-panel .page-link {
        color: #53627d;
        font-size: 11.5px;
        border-color: #d8e1ee;
    }

    .master-panel .page-item.active .page-link {
        color: #fff;
        background: var(--primary, #0070b7);
        border-color: var(--primary, #0070b7);
    }

    .master-alert {
        padding: 11px 14px;
        font-size: 12.5px;
        border: 0;
        border-radius: 6px;
    }

    .master-photo-thumb,
    .master-photo-empty {
        width: 46px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #eef4f8;
        border: 1px solid #d8e5ee;
        border-radius: 5px;
    }

    .master-photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .master-photo-empty {
        color: #8a99ae;
    }

    .part-photo-field {
        display: grid;
        grid-template-columns: 76px 1fr;
        align-items: center;
        gap: 12px;
        padding: 10px;
        background: #f8fafc;
        border: 1px solid #dfe7f0;
        border-radius: 6px;
    }

    .part-photo-preview {
        width: 76px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #8492a8;
        font-size: 20px;
        background: #fff;
        border: 1px solid #d8e1ee;
        border-radius: 5px;
    }

    .part-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .part-photo-copy {
        min-width: 0;
    }

    .part-photo-name {
        max-width: 100%;
        margin-top: 5px;
        overflow: hidden;
        color: #6b7a90;
        font-size: 10.5px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .master-import-modal .modal-content {
        overflow: hidden;
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 8px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
    }

    .master-import-modal .modal-header,
    .master-import-modal .modal-footer {
        padding: 13px 18px;
        border-color: var(--border, #e2e8f0);
    }

    .master-import-modal .modal-body {
        padding: 18px;
        background: #f8fafc;
    }

    .master-import-dropzone {
        padding: 26px 18px;
        text-align: center;
        cursor: pointer;
        background: #fff;
        border: 1px dashed #abc9dc;
        border-radius: 7px;
        transition: background .15s ease, border-color .15s ease;
    }

    .master-import-dropzone:hover,
    .master-import-dropzone.is-active {
        background: #f0f8fd;
        border-color: var(--primary, #0070b7);
    }

    .master-import-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 9px;
        color: var(--primary, #0070b7);
        font-size: 17px;
        background: #e8f4fb;
        border-radius: 6px;
    }

    .master-import-dropzone strong {
        display: block;
        color: #26334d;
        font-size: 12.5px;
    }

    .master-import-dropzone span {
        display: block;
        margin-top: 4px;
        color: #7a879b;
        font-size: 10.5px;
    }

    .master-import-file {
        display: none;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #d8e4ec;
        border-radius: 6px;
    }

    .master-import-file.is-visible {
        display: flex;
    }

    .master-import-file-meta {
        min-width: 0;
        flex: 1;
    }

    .master-import-file-name {
        overflow: hidden;
        color: #26334d;
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .master-import-file-size {
        color: #7a879b;
        font-size: 10.5px;
    }

    @media (max-width: 767.98px) {
        .master-page-header {
            flex-direction: column;
        }

        .master-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .master-panel-header {
            align-items: flex-start;
        }

        .master-panel .dataTables_filter {
            margin-top: 8px;
            text-align: left !important;
        }
    }
</style>
