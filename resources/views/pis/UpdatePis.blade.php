@extends('layouts.root.main')

@section('main')
    <div class="section-header">
        <h1><i class="fas fa-edit mr-2"></i>Update PIS Data</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('pis.master') }}"><i class="fas fa-arrow-left mr-1"></i>Back to Master Data</a></div>
            <div class="breadcrumb-item active">Update PIS</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            @if(session('message'))
            <div class="alert alert-{{ session('message')['type'] ?? 'info' }} alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-{{ session('message')['type'] == 'success' ? 'check-circle' : 'info-circle' }} mr-1"></i></strong>
                {{ session('message')['text'] ?? 'Action completed.' }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if(isset($part_piss) && (is_array($part_piss) || $part_piss instanceof \Illuminate\Support\Collection) && count($part_piss) > 0)
            @foreach($part_piss as $part_pis)
            <div class="card shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-database mr-2"></i>Edit PIS Information
                    </h4>
                </div>
                <div class="card-body">
                    <form id="updatePisForm" role="form" action="{{ route('pis.updatepis') }}" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="img_path" value="{{ $part_pis->img_path ?? '' }}">
                        <input type="hidden" name="id" value="{{ $part_pis->id ?? '' }}">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="part_number_customer">
                                        <i class="fas fa-barcode mr-1"></i>Part Number Customer
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="part_number_customer" 
                                           name="part_number_customer" 
                                           placeholder="Enter Part Number Customer" 
                                           value="{{ $part_pis->part_number_customer ?? '' }}"
                                           onkeyup="this.value = this.value.toUpperCase()"
                                           autocomplete="off"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="back_number">
                                        <i class="fas fa-hashtag mr-1"></i>Back No
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="back_number" 
                                           name="back_number" 
                                           placeholder="Enter Back No" 
                                           value="{{ $part_pis->back_number ?? '' }}"
                                           onkeyup="this.value = this.value.toUpperCase()"
                                           autocomplete="off"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="qty_kanban">
                                        <i class="fas fa-sort-numeric-up mr-1"></i>Quantity
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="qty_kanban" 
                                           name="qty_kanban" 
                                           placeholder="Enter Quantity" 
                                           value="{{ $part_pis->qty_kanban ?? '' }}"
                                           min="1"
                                           autocomplete="off"
                                           required>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="part_kind">
                                        <i class="fas fa-tag mr-1"></i>Type
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="part_kind" name="part_kind" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="OEM" {{ (isset($part_pis->part_kind) && $part_pis->part_kind == 'OEM') ? 'selected' : '' }}>OEM</option>
                                        <option value="GNP" {{ (isset($part_pis->part_kind) && $part_pis->part_kind == 'GNP') ? 'selected' : '' }}>GNP</option>
                                        <option value="DANDORY" {{ (isset($part_pis->part_kind) && $part_pis->part_kind == 'DANDORY') ? 'selected' : '' }}>DANDORY</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="part_dock">
                                        <i class="fas fa-map-marker-alt mr-1"></i>Destination
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="part_dock" name="part_dock" required>
                                        <option value="">-- Select Destination --</option>
                                        <option value="43" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == '43') ? 'selected' : '' }}>43</option>
                                        <option value="53" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == '53') ? 'selected' : '' }}>53</option>
                                        <option value="1L" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == '1L') ? 'selected' : '' }}>1L</option>
                                        <option value="1N" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == '1N') ? 'selected' : '' }}>1N</option>
                                        <option value="1S" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == '1S') ? 'selected' : '' }}>1S</option>
                                        <option value="6I" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == '6I') ? 'selected' : '' }}>6I</option>
                                        <option value="TAMTAM" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == 'TAMTAM') ? 'selected' : '' }}>TAMTAM</option>
                                        <option value="TAMADM" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == 'TAMADM') ? 'selected' : '' }}>TAMADM</option>
                                        <option value="TAMHINO" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == 'TAMHINO') ? 'selected' : '' }}>TAMHINO</option>
                                        <option value="OTHER" {{ (isset($part_pis->part_dock) && $part_pis->part_dock == 'OTHER') ? 'selected' : '' }}>OTHER</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="part_picture">
                                        <i class="fas fa-image mr-1"></i>Update Picture
                                        <small class="text-muted">(JPG, JPEG, PNG)</small>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               class="custom-file-input" 
                                               id="part_picture" 
                                               name="part_picture"
                                               accept=".jpg,.jpeg,.png"
                                               onchange="previewImage(event)">
                                        <label class="custom-file-label" for="part_picture">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Leave empty to keep current image
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Current Image Preview -->
                        @if(isset($part_pis->img_path) && $part_pis->img_path)
                        <div class="row mt-3">
                            <div class="col-12">
                                <label><i class="fas fa-eye mr-1"></i>Current Image:</label>
                                <div class="border rounded p-2 bg-light text-center" style="max-width: 400px;">
                                    <img src="{{ asset('storage/pis/' . $part_pis->img_path) }}" 
                                         alt="Current PIS Image" 
                                         class="img-fluid rounded"
                                         style="max-height: 200px;"
                                         onerror="this.src='{{ asset('storage/pis/default.JPG') }}'">
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- New Image Preview -->
                        <div class="row mt-3" id="newImagePreview" style="display: none;">
                            <div class="col-12">
                                <label><i class="fas fa-image mr-1"></i>New Image Preview:</label>
                                <div class="border rounded p-2 bg-light text-center" style="max-width: 400px;">
                                    <img id="previewImg" 
                                         src="" 
                                         alt="New Image Preview" 
                                         class="img-fluid rounded"
                                         style="max-height: 200px;">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('pis.master') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i>Cancel
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-warning mr-2" onclick="resetPreview()">
                                            <i class="fas fa-undo mr-1"></i>Reset
                                        </button>
                                        <button type="submit" class="btn btn-success" name="submit">
                                            <i class="fas fa-save mr-1"></i>Update PIS Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
            @else
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 48px;"></i>
                    <h4 class="mt-3">No Data Found</h4>
                    <p class="text-muted">The PIS data you're trying to edit could not be found.</p>
                    <a href="{{ route('pis.master') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left mr-1"></i>Return to Master Data
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@section('custom-script')
    <link rel="stylesheet" type="text/css" href="{{url('/css/select2.min.css')}}">
    <script type="text/javascript" src="{{url('/plugins/select2.js')}}"></script>

    <script>
        // Initialize Select2 for better dropdown UX (optional)
        $(document).ready(function() {
            $('#part_kind, #part_dock').select2({
                placeholder: "Select an option",
                allowClear: false,
                width: '100%'
            });

            // Custom file input label update
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
            });

            // Form validation feedback
            $('#updatePisForm').on('submit', function(e) {
                var isValid = true;
                
                // Check required fields
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
                    alert('⚠️ Please fill in all required fields marked with *');
                    return false;
                }
            });

            // Remove invalid class on input
            $('input, select').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });

        // Image preview function
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

        // Reset preview
        function resetPreview() {
            $('#newImagePreview').hide();
            $('#previewImg').attr('src', '');
            $('.custom-file-label').removeClass('selected').html('Choose file');
        }
    </script>

    <style>
        /* Additional styling for better UX */
        .form-control:focus,
        .custom-file-input:focus ~ .custom-file-label {
            border-color: #6777ef;
            box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.25);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .custom-file-label.selected {
            color: #495057;
        }

        .card {
            transition: all 0.3s ease;
        }

        .btn {
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .form-group label {
            font-weight: 600;
            color: #34395e;
        }

        .text-danger {
            font-weight: bold;
        }
    </style>
@endsection
