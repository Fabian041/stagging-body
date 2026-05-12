@extends('layouts.root.main')

@section('main')
    <style>
        /* ===== UPDATE PIS - MATCH VIEW MASTER PIS STYLE ===== */
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: relative;
        }

        .bella-table-card-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .bella-table-card-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .bella-update-body {
            padding: 16px 20px;
            background: var(--bg);
        }

        .pis-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            align-items: start;
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 16px;
            margin-bottom: 12px;
            background: var(--card);
        }

        .pis-row .form-group {
            margin-bottom: 0;
        }

        .pis-row .form-group label,
        .pis-preview-label {
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            display: block;
        }

        .pis-row .form-control,
        .pis-row .custom-file-label {
            height: 34px;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
            box-shadow: none !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .pis-row .custom-file-label {
            line-height: 22px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .pis-row .form-control:focus,
        .pis-row .custom-file-input:focus~.custom-file-label {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
            background: #fff !important;
        }

        .pis-row .form-control.is-invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .10) !important;
        }

        .part_number_customer_group,
        .back_number_group,
        .qty_group,
        .part_kind_group,
        .part_dock_group,
        .pis_picture_group {
            min-width: 0;
        }

        .part_dock_group,
        .pis_picture_group {
            grid-column: 1 / -1;
        }

        .pis-preview-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .pis-preview-box {
            border: 1px solid var(--border);
            border-radius: var(--r, 8px);
            padding: 12px;
            background: var(--card);
        }

        .pis-preview-image-wrap {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            padding: 10px;
            text-align: center;
        }

        .pis-preview-image-wrap img {
            max-height: 200px;
            max-width: 100%;
            border-radius: 6px;
        }

        .bella-empty-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 42px 18px;
            text-align: center;
        }

        .bella-empty-card i {
            font-size: 42px;
            color: #f59e0b;
        }

        .bella-empty-card h4 {
            margin-top: 14px;
            font-size: 16px;
            font-weight: 800;
            color: var(--navy);
        }

        .bella-empty-card p {
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .bella-alert {
            margin: 16px 20px 0;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 12.5px;
        }

        .form-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
        }

        .form-action-right {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .act-btn {
            border: 1px solid transparent;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1;
            transition: .15s;
            text-decoration: none !important;
            cursor: pointer;
        }

        .act-btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff !important;
        }

        .act-btn.success {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff !important;
        }

        .act-btn.danger {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff !important;
        }

        .act-btn.secondary {
            background: var(--card);
            border-color: var(--border);
            color: var(--text-muted) !important;
        }

        .act-btn.warning-outline {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c !important;
        }

        .act-btn:hover {
            filter: brightness(.97);
            transform: translateY(-1px);
        }

        .select2-container--default .select2-selection--single {
            height: 34px !important;
            border: 1px solid var(--border) !important;
            border-radius: 5px !important;
            background: var(--bg) !important;
            box-shadow: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12.5px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
        }

        .select2-container {
            z-index: 1060 !important;
            width: 100% !important;
        }

        .select2-dropdown {
            z-index: 1060 !important;
            border-color: var(--border) !important;
            font-size: 12.5px !important;
        }

        @media (max-width: 768px) {

            .bella-table-card-header,
            .form-action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .pis-row,
            .pis-preview-grid {
                grid-template-columns: 1fr;
            }

            .part_dock_group,
            .pis_picture_group {
                grid-column: 1;
            }

            .form-action-right,
            .form-action-right .act-btn,
            .form-action-bar>.act-btn {
                width: 100%;
            }
        }
    </style>

    @if (isset($part_piss) &&
            (is_array($part_piss) || $part_piss instanceof \Illuminate\Support\Collection) &&
            count($part_piss) > 0)
        @foreach ($part_piss as $part_pis)
            <div class="bella-table-card mt-3">
                <div class="bella-table-card-header">
                    <div>
                        <span class="bella-table-card-title"><i class="fas fa-edit mr-2"></i>Update PIS Data</span>
                        <div class="bella-table-card-subtitle">Edit master part PIS dan update picture bila diperlukan.</div>
                    </div>
                    <a href="{{ route('pis.master') }}" class="act-btn secondary"
                        style="height:34px; padding:0 14px; font-size:12px; letter-spacing:.04em;">
                        <i class="fas fa-arrow-left"></i> Back to Master Data
                    </a>
                </div>

                @if (session('message'))
                    <div class="alert alert-{{ session('message')['type'] ?? 'info' }} alert-dismissible fade show bella-alert"
                        role="alert">
                        <i
                            class="fas fa-{{ (session('message')['type'] ?? 'info') == 'success' ? 'check-circle' : 'info-circle' }} mr-1"></i>
                        {{ session('message')['text'] ?? 'Action completed.' }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="bella-update-body">
                    <form id="updatePisForm" role="form" action="{{ route('pis.updatepis') }}" method="post"
                        enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="img_path" value="{{ $part_pis->img_path ?? '' }}">
                        <input type="hidden" name="id" value="{{ $part_pis->id ?? '' }}">

                        <div class="pis-row">
                            <div class="form-group part_number_customer_group">
                                <label for="part_number_customer">Part Number Customer <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="part_number_customer"
                                    name="part_number_customer" placeholder="Part Number Customer"
                                    value="{{ $part_pis->part_number_customer ?? '' }}"
                                    onkeyup="this.value = this.value.toUpperCase()" autocomplete="off" required>
                            </div>

                            <div class="form-group back_number_group">
                                <label for="back_number">Back No <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="back_number" name="back_number"
                                    placeholder="Back No" value="{{ $part_pis->back_number ?? '' }}"
                                    onkeyup="this.value = this.value.toUpperCase()" autocomplete="off" required>
                            </div>

                            <div class="form-group qty_group">
                                <label for="qty_kanban">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="qty_kanban" name="qty_kanban"
                                    placeholder="Qty" value="{{ $part_pis->qty_kanban ?? '' }}" min="1"
                                    autocomplete="off" required>
                            </div>

                            <div class="form-group part_kind_group">
                                <label for="part_kind">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="part_kind" name="part_kind" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="OEM"
                                        {{ isset($part_pis->part_kind) && $part_pis->part_kind == 'OEM' ? 'selected' : '' }}>
                                        OEM</option>
                                    <option value="DANDORY"
                                        {{ isset($part_pis->part_kind) && $part_pis->part_kind == 'DANDORY' ? 'selected' : '' }}>
                                        DANDORY</option>
                                </select>
                            </div>

                            <div class="form-group part_dock_group">
                                <label for="part_dock">Destination <span class="text-danger">*</span></label>
                                <select class="form-control" id="part_dock" name="part_dock" required>
                                    <option value="">-- Select Destination --</option>
                                    <option value="TMMIN SPD"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'TMMIN SPD' ? 'selected' : '' }}>
                                        TMMIN SPD</option>
                                    <option value="TMMIN SPD-ADM"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'TMMIN SPD-ADM' ? 'selected' : '' }}>
                                        TMMIN SPD-ADM</option>
                                    <option value="43"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == '43' ? 'selected' : '' }}>
                                        43</option>
                                    <option value="53"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == '53' ? 'selected' : '' }}>
                                        53</option>
                                    <option value="1L"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == '1L' ? 'selected' : '' }}>
                                        1L</option>
                                    <option value="1N"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == '1N' ? 'selected' : '' }}>
                                        1N</option>
                                    <option value="HINO-SPD"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'HINO-SPD' ? 'selected' : '' }}>
                                        HINO-SPD</option>
                                    <option value="SIM-SPD"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'SIM-SPD' ? 'selected' : '' }}>
                                        SIM-SPD</option>
                                    <option value="MMKI"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'MMKI' ? 'selected' : '' }}>
                                        MMKI</option>
                                    <option value="MMKI-SPD"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'MMKI-SPD' ? 'selected' : '' }}>
                                        MMKI-SPD</option>
                                    <option value="6I"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == '6I' ? 'selected' : '' }}>
                                        6I</option>
                                    <option value="TAM-TAM"
                                        {{ isset($part_pis->part_dock) && in_array($part_pis->part_dock, ['TAM-SPD', 'TAMSPD']) ? 'selected' : '' }}>
                                        TAM-SPD</option>
                                    <option value="TAM-ADM"
                                        {{ isset($part_pis->part_dock) && in_array($part_pis->part_dock, ['TAM-ADM', 'TAMADM']) ? 'selected' : '' }}>
                                        TAM-ADM</option>
                                    <option value="TAM-HINO"
                                        {{ isset($part_pis->part_dock) && in_array($part_pis->part_dock, ['TAM-HINO', 'TAMHINO']) ? 'selected' : '' }}>
                                        TAM-HINO</option>
                                    <option value="ADM-AS"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'ADM-AS' ? 'selected' : '' }}>
                                        ADM-AS</option>
                                    <option value="ADM-KP"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'ADM-KP' ? 'selected' : '' }}>
                                        ADM-KP</option>
                                    <option value="YHA"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'YHA' ? 'selected' : '' }}>
                                        YHA</option>
                                    <option value="ADM"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'ADM' ? 'selected' : '' }}>
                                        ADM</option>
                                    <option value="TTI"
                                        {{ isset($part_pis->part_dock) && $part_pis->part_dock == 'TTI' ? 'selected' : '' }}>
                                        TTI</option>
                                </select>
                            </div>

                            <div class="form-group pis_picture_group">
                                <label for="part_picture">Picture (.JPG, .JPEG, .PNG)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="part_picture"
                                        name="part_picture" accept=".jpg,.jpeg,.png" onchange="previewImage(event)">
                                    <label class="custom-file-label" for="part_picture">Choose file</label>
                                </div>
                                <small class="form-text text-muted" style="font-size:11px;">Leave empty to keep current
                                    image.</small>
                            </div>
                        </div>

                        <div class="pis-preview-grid">
                            @if (isset($part_pis->img_path) && $part_pis->img_path)
                                <div class="pis-preview-box">
                                    <label class="pis-preview-label"><i class="fas fa-eye mr-1"></i>Current Image</label>
                                    <div class="pis-preview-image-wrap">
                                        <img src="{{ asset('storage/pis/' . $part_pis->img_path) }}"
                                            alt="Current PIS Image"
                                            onerror="this.src='{{ asset('storage/pis/default.JPG') }}'">
                                    </div>
                                </div>
                            @endif

                            <div class="pis-preview-box" id="newImagePreview" style="display:none;">
                                <label class="pis-preview-label"><i class="fas fa-image mr-1"></i>New Image
                                    Preview</label>
                                <div class="pis-preview-image-wrap">
                                    <img id="previewImg" src="" alt="New Image Preview">
                                </div>
                            </div>
                        </div>

                        <div class="form-action-bar">
                            <a href="{{ route('pis.master') }}" class="act-btn secondary"
                                style="height:32px; padding:0 14px; font-size:12px;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <div class="form-action-right">
                                <button type="reset" class="act-btn warning-outline" onclick="resetPreview()"
                                    style="height:32px; padding:0 14px; font-size:12px;">
                                    <i class="fas fa-redo-alt"></i> Reset
                                </button>
                                <button type="submit" class="act-btn success" name="submit"
                                    style="height:32px; padding:0 16px; font-size:12px;">
                                    <i class="fas fa-save"></i> Update PIS Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @else
        <div class="bella-empty-card mt-3">
            <i class="fas fa-exclamation-triangle"></i>
            <h4>No Data Found</h4>
            <p>The PIS data you're trying to edit could not be found.</p>
            <a href="{{ route('pis.master') }}" class="act-btn primary"
                style="height:34px; padding:0 14px; font-size:12px;">
                <i class="fas fa-arrow-left"></i> Return to Master Data
            </a>
        </div>
    @endif
@endsection

@section('custom-script')
    <link rel="stylesheet" type="text/css" href="{{ url('/css/select2.min.css') }}">
    <script type="text/javascript" src="{{ url('/plugins/select2.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#part_kind, #part_dock').select2({
                placeholder: "Select an option",
                allowClear: false,
                width: '100%'
            });

            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'Choose file');
            });

            $('#updatePisForm').on('submit', function(e) {
                var isValid = true;

                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields marked with *');
                    return false;
                }
            });

            $('input, select').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#newImagePreview').show();
                }
                reader.readAsDataURL(file);
            }
        }

        function resetPreview() {
            $('#newImagePreview').hide();
            $('#previewImg').attr('src', '');
            $('.custom-file-label').removeClass('selected').html('Choose file');
        }
    </script>
@endsection
