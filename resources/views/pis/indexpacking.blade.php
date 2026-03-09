@extends('layouts.root.minimal')

@section('main')
    <div class="mb-3">
        <button onclick="history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="card border" style="border-radius: 12px;">
                                <div class="card-header py-2">
                                    <strong>Part Number</strong>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <input id="detail_no" class="form-control" name="detail_no" required>
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-3" style="border-radius: 12px;" id="table_hide">
                                <div class="card-header py-2">
                                    <strong>Counter</strong>
                                </div>
                                <div class="card-body" style="height:110px;">
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <div class="display-4 font-weight-bold" id="counter">TRIAL</div>
                                    </div>
                                </div>
                                <div class="card-footer py-2 text-muted small">{{ date('Y-m-d') }}</div>
                            </div>

                            <div class="card border mt-3" style="border-radius: 12px;" id="table_hide">
                                <div class="card-header py-2">
                                    <strong>Loading List</strong>
                                </div>
                                <div class="card-body p-2">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">No</th>
                                                <th>Part Name</th>
                                                <th style="width: 120px;">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody id="loading_list_body">
                                            <tr>
                                                <td id="loading_list_no">&nbsp;</td>
                                                <td id="loading_list_part">&nbsp;</td>
                                                <td id="loading_list_qty">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td id="loading_list_no">&nbsp;</td>
                                                <td id="loading_list_part">&nbsp;</td>
                                                <td id="loading_list_qty">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td id="loading_list_no">&nbsp;</td>
                                                <td id="loading_list_part">&nbsp;</td>
                                                <td id="loading_list_qty">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td id="loading_list_no">&nbsp;</td>
                                                <td id="loading_list_part">&nbsp;</td>
                                                <td id="loading_list_qty">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td id="loading_list_no">&nbsp;</td>
                                                <td id="loading_list_part">&nbsp;</td>
                                                <td id="loading_list_qty">&nbsp;</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="col-md-7">
                            <div id="alert"
                                class="alert alert-{{ (session('message') && isset(session('message')['type'])) ? session('message')['type'] : 'success' }}">
                                <div class="d-flex align-items-center">
                                    <div class="mr-2" id="alert-header"><i class="fas fa-check-circle"></i> Alert</div>
                                </div>
                                <div id="alert-body">
                                    {{ (session('message') && isset(session('message')['text'])) ? session('message')['text'] : 'Ready to Scan !!' }}
                                </div>
                            </div>

                            <div class="card border" style="border-radius: 12px;">
                                <div class="card-header py-2 d-flex align-items-center justify-content-between">
                                    <strong>Preview Image</strong>
                                    <span class="text-muted small" id="previewImageLabel">Tampil jika part ditemukan</span>
                                </div>
                                <div class="card-body p-2">
                                    <div id="imageDiv" class="text-center" style="min-height: 180px;">
                                        <img id="previewImg" src="" alt="Part image" class="img-fluid"
                                            style="max-height: 560px; display: none;" />
                                        <div id="previewPlaceholder" class="text-muted py-4">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <p class="mb-0 small">Gambar akan tampil setelah scan berhasil</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="card border" style="border-radius: 12px;">
                                <div class="card-header py-2">
                                    <strong>Type</strong>
                                </div>
                                <div class="card-body">
                                    <div id="delivery" class="form-group mb-0">
                                        <button id="btnOEM" value="OEM" type="button" class="btn btn-block btn-primary"
                                            onclick="func_change_delivery(this);">OEM</button>
                                        <button id="btnGNP" value="GNP" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_delivery(this);">GNP</button>
                                        <button id="btnDAN" value="DANDORY" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_delivery(this);">DANDORY</button>
                                        <input id="delivery_type" value="OEM" type="hidden"></input>
                                    </div>
                                </div>
                            </div>

                            <div class="card border mt-3" style="border-radius: 12px;">
                                <div class="card-header py-2">
                                    <strong>Dock</strong>
                                </div>
                                <div class="card-body">
                                    <div id="dock" class="form-group mb-0">
                                        <button id="btnOTHER" value="OTHER" type="button" class="btn btn-block btn-primary"
                                            onclick="func_change_dock(this);">OTHER</button>
                                        <button id="btn43" value="43" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">43</button>
                                        <button id="btn53" value="53" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">53</button>
                                        <button id="btn1L" value="1L" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">1L</button>
                                        <button id="btn1N" value="1N" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">1N</button>
                                        <button id="btn1S" value="1S" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">S1</button>
                                        <button id="btn6I" value="6I" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">6I</button>
                                        <button id="btnTAMTAM" value="TAMTAM" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">TAM-TAM</button>
                                        <button id="btnTAMADM" value="TAMADM" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">TAM-ADM</button>
                                        <button id="btnTAMHINO" value="TAMHINO" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">TAM-HINO</button>
                                        <button id="btnADMAS" value="ADMAS" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">ADM-AS</button>
                                        <button id="btnADMKP" value="ADMKP" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">ADM-KP</button>
                                        <button id="btnADMKP" value="YHA" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">YHA</button>
                                        <button id="btnADMAS" value="ADM" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">ADM</button>
                                        <button id="btnADMKP" value="TTI" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">TTI</button>
                                        <button id="btnADMKP" value="S1-TAM" type="button" class="btn btn-block btn-outline-primary"
                                            onclick="func_change_dock(this);">S1-TAM</button>
                                        <input id="dock_type" value="OTHER" type="hidden"></input>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
@endsection

@section('custom-script')
    <script type="text/javascript">
        function func_change_delivery(obj) {
            $('#delivery').find('button').removeClass('btn-primary');
            $('#delivery').find('button').addClass('btn-default');
            $(obj).addClass('btn-primary');
            $('#delivery_type').val(obj.value);
        }

        function func_change_dock(obj) {
            $('#dock').find('button').removeClass('btn-primary');
            $('#dock').find('button').addClass('btn-outline-primary');
            $(obj).removeClass('btn-outline-primary').addClass('btn-primary');
            $('#dock_type').val(obj.value);
        }

        var barcode = "";
        var rep2 = "";
        var table = "";
        var old_html = $("#imageDiv").html();
        var detail_no = $('#detail_no');
        var labelValue = "";
        var stage = 1;

        // Helper function to clean barcode from control characters
        function cleanBarcode(barcode) {
            if (!barcode) return '';
            // Remove control characters: \r (carriage return), \n (line feed), \t (tab), and other control chars
            // Also remove leading/trailing whitespace
            return barcode.replace(/[\r\n\t]/g, '').replace(/[\x00-\x1F\x7F]/g, '').trim();
        }

        $(document).keypress(function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) { // Enter key hit
                e.preventDefault(); // Prevent default Enter behavior
                $('#detail_no').val('');

                // Clean barcode from any control characters that might have been added
                barcode = cleanBarcode(barcode);

                // Cek apakah pilihan adalah DANDORY dan S1
                let isDandoryS1 = ($('#delivery_type').val() === 'DANDORY' && $('#dock_type').val() === '1S');

                if (isDandoryS1) {
                    // Logika jika DANDORY dan S1, mencakup dua tahap scan (label dan kanban)
                    if (stage === 1) {
                        labelValue = barcode.slice(0, 12); // Ambil 10 digit dari label
                        console.log(labelValue);

                        $('#alert').removeClass('alert-danger alert-success').addClass('alert-warning');
                        $('#alert-header').html('<i class="icon fa fa-info-circle"></i> Peringatan');
                        $('#alert-body').text('Silakan scan kanban');

                        stage = 2;
                        barcode = "";
                    } else if (stage === 2) {
                        let scannedKanban = barcode.slice(158, 170);
                        console.log(scannedKanban);

                        if (barcode.length < 220) {
                            $('#alert').removeClass('alert-danger alert-success').addClass('alert-success');
                            $('#alert-header').html('<i class="icon fa fa-info-circle"></i> Peringatan');
                            $('#alert-body').text('Anda sudah mengscan label. Mohon scan kanban dengan benar.');
                            barcode = "";
                        } else {
                            if (labelValue === scannedKanban) {
                                ajaxScan(barcode);
                            } else {
                                $('#alert').removeClass('alert-success alert-danger').addClass('alert-warning');
                                $('#alert-header').html('<i class="icon fa fa-info-circle"></i> Peringatan');
                                $('#alert-body').text('Label dan kanban tidak cocok. Silakan scan ulang kanban.');
                                barcode = "";
                            }
                        }
                    }
                } else {
                    // Logika untuk pilihan selain DANDORY dan S1, hanya perlu scan kanban
                    ajaxScan(barcode);
                }

                barcode = ""; // Reset barcode setelah scan
            } else {
                // Only add printable characters (exclude control characters)
                // Key codes 32-126 are printable ASCII characters
                if (code >= 32 && code <= 126) {
                    barcode += String.fromCharCode(e.which); // Kumpulkan karakter barcode
                }
            }
        });

        // Fungsi AJAX untuk scan
        function ajaxScan(barcode) {
            // Clean barcode one more time before sending
            barcode = cleanBarcode(barcode);
            
            // Properly encode the barcode and other parameters for URL
            var encodedBarcode = encodeURIComponent(barcode);
            var encodedType = encodeURIComponent($('#delivery_type').val());
            var encodedDock = encodeURIComponent($('#dock_type').val());
            
            $.ajax({
                type: 'get',
                url: "{{ url('pis/getAjaxImage') }}" + '/' + encodedBarcode + '/' + encodedType + '/' + encodedDock,
                _token: "{{ csrf_token() }}",
                dataType: 'json',
                success: function(data) {
                    rep2 = data.part_number_customer;
                    if (rep2 == "") {
                        $('#detail_no').prop('readonly', false).val(barcode);
                        $('#alert').removeClass('alert-success').addClass('alert-danger');
                        $('#alert-header').html('<i class="icon fa fa-warning"></i> Error');
                        $('#alert-body').text('Part Not Found');
                        $('#detail_no').prop('readonly', true);
                        barcode = "";
                        rep2 = "";
                        // Clear preview image
                        $('#previewImg').attr('src', '').hide();
                        $('#previewPlaceholder').show();
                    } else {
                        $('#alert').removeClass('alert-danger alert-warning').addClass('alert-success');
                        $('#alert-header').html('<i class="icon fa fa-check"></i> Success');
                        $('#alert-body').text(rep2 + ' Part Found');
                        $('#detail_no').prop('readonly', false).val(rep2);
                        // Render preview image (responsive)
                        $('#previewImg').attr('src', data.img_path || '').show();
                        $('#previewPlaceholder').hide();
                        $('#detail_no').prop('readonly', true);
                        $('#counter').text(data.counter);
                        // Clear loading list table
                        $('[id^=loading_list_no]').html('&nbsp;');
                        $('[id^=loading_list_part]').html('&nbsp;');
                        $('[id^=loading_list_qty]').html('&nbsp;');
                        // Populate loading list
                        if (data.loading_list && data.loading_list.length > 0) {
                            data.loading_list.forEach((item, i) => {
                                $('[id^=loading_list_no]').eq(i).text(i + 1);
                                $('[id^=loading_list_part]').eq(i).text(item.part_name || '-');
                                $('[id^=loading_list_qty]').eq(i).text(item.quantity || 0);
                            });
                        }
                        barcode = "";
                        rep2 = "";
                        stage = 1;
                    }
                },
                error: function(xhr) {
                    $('#alert').removeClass('alert-success').addClass('alert-danger');
                    $('#alert-header').html('<i class="icon fa fa-warning"></i> Scan Error: ' + xhr
                        .status + " - " + xhr.statusText);
                    $('#alert-body').text('Part Not Found');
                    barcode = "";
                    rep2 = "";
                    // Clear preview image
                    $('#previewImg').attr('src', '').hide();
                    $('#previewPlaceholder').show();
                }
            });
        }


        $(document).ready(function() {
            $('#detail_no').prop('readonly', true);
            // If image fails to load, show placeholder again
            $('#previewImg').on('error', function() {
                $(this).attr('src', '').hide();
                $('#previewPlaceholder').show();
            });
        });
    </script>
@endsection
