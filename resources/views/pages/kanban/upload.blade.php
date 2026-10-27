@extends('layouts.root.main')

@section('main')
    @php
        $customer = strtoupper($customer ?? request()->query('customer', 'ALL'));
        if (!in_array($customer, ['TMMIN', 'MMKI', 'ALL'], true)) {
            $customer = 'ALL';
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

        /* Upload Type Tab Button Styles */
        .upload-type-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            font-size: 13.5px;
            font-weight: 700;
            border-radius: 8px;
            border: 1px solid var(--border, #DDE3EF);
            background: #ffffff;
            color: var(--text-muted, #6B7A99);
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 1px 3px rgba(41, 71, 149, 0.05);
        }

        .upload-type-btn:hover {
            color: var(--navy, #294795);
            background: #F4F7FC;
            border-color: #B9C7DF;
            transform: translateY(-1px);
        }

        .upload-type-btn.active {
            background: linear-gradient(135deg, #294795, #0070B7) !important;
            border-color: #294795 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(41, 71, 149, 0.28) !important;
        }

        .upload-type-btn.active i {
            color: #ffffff !important;
        }
    </style>

    <div class="container-fluid">

        {{-- Card Selector Upload Type (Kanban, Manifest, Skid) --}}
        <div class="card mb-3" id="uploadTabCard">
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap" style="gap: 10px;" role="tablist" aria-label="Upload Type Selector">
                    <button type="button" 
                            class="btn upload-type-btn active" 
                            id="btnUploadKanban" 
                            data-target="#formAreaUploadKanban"
                            role="tab"
                            aria-selected="true"
                            aria-controls="formAreaUploadKanban">
                        <i class="fas fa-layer-group"></i> Upload Kanban
                    </button>
                    <button type="button" 
                            class="btn upload-type-btn" 
                            id="btnUploadManifest" 
                            data-target="#formAreaUploadManifest"
                            role="tab"
                            aria-selected="false"
                            aria-controls="formAreaUploadManifest">
                        <i class="fas fa-file-alt"></i> Upload Manifest
                    </button>
                    <button type="button" 
                            class="btn upload-type-btn" 
                            id="btnUploadSkid" 
                            data-target="#formAreaUploadSkid"
                            role="tab"
                            aria-selected="false"
                            aria-controls="formAreaUploadSkid">
                        <i class="fas fa-boxes"></i> Upload Skid
                    </button>
                </div>
            </div>
        </div>

        {{-- Dedicated Form Components --}}
        @include('pages.kanban.partials.UploadKanban')
        @include('pages.kanban.partials.UploadManifest')
        @include('pages.kanban.partials.UploadSkid')

    </div>

    {{-- Form Switcher Script (Kanban, Manifest, Skid) --}}
    <script>
        window.normalizeFileName = function (filename) {
            if (typeof filename !== 'string') return filename;
            var lastDot = filename.lastIndexOf('.');
            var name = lastDot !== -1 ? filename.slice(0, lastDot) : filename;
            var ext = lastDot !== -1 ? filename.slice(lastDot) : '';
            var hyphenIdx = name.indexOf('-');
            if (hyphenIdx !== -1) {
                name = name.slice(0, hyphenIdx);
            }
            return name + ext;
        };

        window.normalizeFile = function (f) {
            if (!f || !f.name) return f;
            var normName = window.normalizeFileName(f.name);
            if (normName === f.name) return f;
            try {
                return new File([f], normName, { type: f.type, lastModified: f.lastModified });
            } catch (e) {
                return f;
            }
        };

        (function () {
            function setupUploadTypeTabs() {
                var buttons = document.querySelectorAll('.upload-type-btn');
                var formPanes = document.querySelectorAll('.upload-form-pane');

                if (!buttons.length || !formPanes.length) return;

                buttons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var targetSelector = this.getAttribute('data-target');
                        if (!targetSelector) return;

                        // Update active styling on selector buttons
                        buttons.forEach(function (b) {
                            b.classList.remove('active');
                            b.setAttribute('aria-selected', 'false');
                        });
                        this.classList.add('active');
                        this.setAttribute('aria-selected', 'true');

                        // Hide all form panes
                        formPanes.forEach(function (pane) {
                            pane.style.display = 'none';
                        });

                        // Show only the selected form pane
                        var activePane = document.querySelector(targetSelector);
                        if (activePane) {
                            activePane.style.display = 'block';
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', setupUploadTypeTabs);
            } else {
                setupUploadTypeTabs();
            }
        })();
    </script>
@endsection
