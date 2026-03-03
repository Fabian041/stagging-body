@extends('layouts.root.main')

@section('main')
        <!-- /.box -->

            <!-- <div class="box box-primary"> -->
              <!-- <div class="box-header with-border">
                <h3 class="box-title">Master PIS</h3>

                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
                </div> 
              </div> -->
              <!-- <form role="form" method="post" action="{{url('/pis/search')}}">
                <input type="hidden" value="{{csrf_token()}}" name="_token">
              <div class="box-body">
                
                <div class="row">
                  <div class="col-xs-4">
                    <label for="exampleInputEmail1">Type</label>
                    <br>
                      <label>
                        <input type="checkbox" class="minimal" name = "oem" value = "OEM" id="">
                        OEM
                      </label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      <label>
                        <input type="checkbox" class="minimal" name = "gnp" value = "GNP" id="">
                        GNP
                      </label>
                  </div>
                  <div class="col-xs-4">
                    <label for="exampleInputPassword1">Destination</label>
                    <br>
                    <label>
                        <input type="checkbox" class="minimal" name = "dock_4N" value = "4N" id="">
                        Dock 4N
                      </label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      <label>
                        <input type="checkbox" class="minimal" name = "dock_4L" value = "4L" id="">
                        Dock 4L
                      </label>
                  </div>
                  
                  
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name ="search">
                      <span class="glyphicon glyphicon-search"></span> Search</button>
                </div>
              </div>
              </form> -->
              <!-- /.box-body
            </div> -->

        <div class="section-header">
            <h1>Master Data PIS</h1>
            <div class="section-header-breadcrumb">
                <span class="text-muted">Kelola master part PIS dengan database.</span>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-primary shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                            <div>
                                <h4 class="mb-1"><i class="fas fa-database mr-2"></i>Data Master PIS</h4>
                                <div class="text-muted">Kelola master part PIS.</div>
                            </div>
                            <!-- FIX: use button (no href="") so modal can open -->
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModal">
                                <i class="fas fa-plus mr-1"></i> Add New PIS
                            </button>
                        </div>

                        <div class="mt-3">
              @if(Session::has('flash_message'))
                      <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span><em> {!! session('flash_message') !!}</em></div>
              @endif

                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Part No</th>
                    <th>Back No</th>
                    <th>Qty</th>
                    <th>Type</th>
                    <th>Destination</th>
                    <th>Picture</th>
                    <th>Status Picture</th>
                    <th>Edit</th>
                    
                  </tr>
                  </thead>
                  <tbody>
                  @if(isset($part_piss) && (is_array($part_piss) || $part_piss instanceof \Illuminate\Support\Collection) && count($part_piss) > 0)
                  @foreach($part_piss as $part_pis)
                  <tr>
                    <td>{{$part_pis->part_number_customer}}</td>
                    <td>{{$part_pis->back_number}}</td>
                    <td>{{$part_pis->qty_kanban}}</td>
                    <td>{{$part_pis->part_kind}}</td>
                    <td>{{$part_pis->part_dock}}</td>
                   
                   
                    <td>
                       
                      <a href="{{ url('pis/preview/'.$part_pis->img_path) }}" target="_blank" onclick="window.open('{{ url('pis/preview/'.$part_pis->img_path) }}', 'popup', 'height=540, width=650, top = 120, left= 350 '); return false;">{{$part_pis->img_path}}</a>
                    </td>
                    <td bgcolor="{{ $part_pis->validasi == 'Ada' ? 'green' : 'red' }}" align = 'center'><strong><font color="white"> {{$part_pis->validasi}}</font></strong></td>
                    <td>
                      <a class = "btn btn-primary" href="{{url('/pis/edit/'.$part_pis->id)}}"><i class="fas fa-edit"></i> </a>
                     <!--  <a class = "btn btn-danger" href="{{url('/pis/delete/'.$part_pis->id)}}" ><span class="glyphicon glyphicon-trash"></span> </a> -->
                    </td>
                  </tr>

                  @endforeach
                  @endif
                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
             <!-- /.card -->


      <!-- /.box-body-add-part -->
      <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><b>CREATE PART</b></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                      <div class="modal-body">
                      <form id="pisForm" role="form" enctype="multipart/form-data">
                          <input type="hidden" name="_token" value="{{ csrf_token() }}">
                          <input type="hidden" name="img_path" value="">
                          <input type="hidden" name="id" value="">
                          <div class="box-body">
                            <div class="form-group" id="part_number_show">
                              <label for="exampleInputEmail1">Part Number AIIA</label>
                                <select id="part_number" class="form-control select2" style="width: 100%;" name="part_number" tabindex="0">
                                <option value="" selected="" disabled="" required >Choose Part Number</option>
                                </select>
                            </div>
                            <div class="form-group">
                              <label class="checkbox-inline" for="manual">
                              <input type="checkbox" id="manual" name="manual" value="option1"> Manual Input
                              </label>
                            </div>
                            <div class="form-group" id="div_hidden_part_no_aiia" for="hidden_part_no_aiia" style="display: none;">
                              <label for="hidden_part_no_aiia">Part Number AIIA</label>
                              <input type="text" class="form-control" id="hidden_part_no_aiia" name="hidden_part_no_aiia" placeholder="Part Number AIIA" onkeyup="this.value = this.value.toUpperCase()" autocomplete="off">
                            </div>
                            <div class="form-group" id="div_hidden_part_name" for="hidden_part_name" style="display: none;">
                              <label for="hidden_part_name">Part Name</label>
                              <input type="text" class="form-control" id="hidden_part_name" name="hidden_part_name" placeholder="Part Name" autocomplete="off">
                            </div>
                            <div class="form-group">
                              <label for="exampleInputEmail1">Part Number Customer</label>
                              <input type="text" class="form-control" id="part_number_customer" name="part_number_customer" placeholder="Part Number Customer" onkeyup="this.value = this.value.toUpperCase()" autocomplete="off">
                            </div>
                            <div class="form-group">
                              <label for="exampleInput1">Back No</label>
                              <input type="text" class="form-control" id="back_number" name="back_number" placeholder="Back No" onkeyup="this.value = this.value.toUpperCase()" autocomplete="off">
                            </div>
                            <div class="form-group" id="div_hidden_min" for="hidden_min" style="display: none;">
                              <label for="exampleInput1">Min Stock</label>
                              <input type="number" class="form-control" id="min_stock" name="min_stock" placeholder="Min Stock" autocomplete="off">
                            </div>
                            <div class="form-group" id="div_hidden_max" for="hidden_max" style="display: none;">
                              <label for="exampleInput1" id="hidden_max" for="hidden_max">Max Stock</label>
                              <input type="number" class="form-control" id="max_stock" name="max_stock" placeholder="Max Stock" autocomplete="off">
                            </div>
                            <div class="form-group">
                              <label for="exampleInput1">Qty</label>
                              <input type="number" class="form-control" id="qty_kanban" name="qty_kanban" placeholder="Qty" autocomplete="off">
                            </div>
                            <div class="form-group">
                              <label for="exampleInput1">Type</label>
                              <select class="form-control" id="part_kind" name="part_kind">
                                <option value="">-- Select Type --</option>
                                <option value="OEM">OEM</option>
                                <option value="DANDORY">DANDORY</option>
                              </select>
                            </div>
                            <div class="form-group" id="part_dock_show">
                              <label for="exampleInput1">Destination</label>
                              <select class="form-control" id="part_dock" name="part_dock">
                                <option value="">-- Select Destination --</option>
                                <option value="43">43</option>
                                <option value="53">53</option>
                                <option value="1L">1L</option>
                                <option value="1N">1N</option>
                                <option value="1S">1S</option>
                                <option value="6I">6I</option>
                                <option value="TAMTAM">TAMTAM</option>
                                <option value="TAMADM">TAMADM</option>
                                <option value="TAMHINO">TAMHINO</option>
                                <option value="OTHER">OTHER</option>
                              </select>
                            </div>

                            <div class="form-group">
                              <label for="exampleInputFile">Picture (.JPG)</label>
                              <input type="file" id="pis_picture" name="pis_picture" accept=".jpg,.jpeg,.png">
                            </div> 
                            <div class="col-md-8">
                            <button type="button" class="btn btn-sm btn-primary" onclick="cekData()" >
                              <span class='glyphicon glyphicon-floppy-saved'></span>&nbsp;
                              <font face='calibri'><b>SAVE</b></font>
                            </button>&nbsp;&nbsp;
                            <button type="reset" class="btn btn-sm btn-danger">
                              <span class='glyphicon glyphicon-repeat'></span>&nbsp;
                              <font face='calibri'><b>RESET</b></font>
                            </button>
                            </div>
                          </div>
                      </form>
                </div>
          </div>
    </div>
    <!-- /.end-box-body-add-part -->  
@endsection

@section('custom-script')
  <!-- FIX: Ensure modal backdrop and modal have correct z-index stacking -->
  <style>
    /* Fix Bootstrap modal backdrop z-index issue */
    /* Backdrop should be at 1040, modal at 1050 */
    .modal-backdrop {
      z-index: 1040 !important;
    }
    
    #myModal.modal {
      z-index: 1050 !important;
      /* Ensure modal is above backdrop */
    }
    
    #myModal .modal-dialog {
      z-index: 1050;
      position: relative;
      pointer-events: auto !important;
    }
    
    #myModal .modal-content {
      pointer-events: auto !important;
      position: relative;
      z-index: 1;
    }
    
    #myModal .modal-body {
      pointer-events: auto !important;
    }
    
    /* Ensure all form elements are interactive */
    #myModal .modal-body input,
    #myModal .modal-body select,
    #myModal .modal-body button,
    #myModal .modal-body textarea,
    #myModal .modal-body label {
      pointer-events: auto !important;
    }
    
    #myModal .modal-body input,
    #myModal .modal-body textarea {
      cursor: text !important;
    }
    
    #myModal .modal-body button,
    #myModal .modal-body select {
      cursor: pointer !important;
    }
    
    #myModal .modal-body .checkbox input[type="checkbox"] {
      cursor: pointer !important;
    }
    
    /* Ensure Select2 dropdown appears above everything */
    .select2-container {
      z-index: 9999 !important;
    }
    
    .select2-dropdown {
      z-index: 9999 !important;
    }
    
    /* Ensure Select2 search input is enabled and visible */
    .select2-search__field {
      pointer-events: auto !important;
      cursor: text !important;
      width: 100% !important;
      background: white !important;
    }
    
    .select2-container--open .select2-search__field {
      pointer-events: auto !important;
      cursor: text !important;
    }
    
    /* Remove any disabled state styling */
    #myModal input:disabled,
    #myModal select:disabled {
      opacity: 1 !important;
      cursor: not-allowed !important;
    }
    
    /* Prevent main-wrapper from creating stacking context issues */
    .main-wrapper,
    .main-content {
      position: relative;
      z-index: auto !important;
    }
  </style>

  <script>
    $(document).ready(function(){
        $('input[type="search"]').removeClass('form-control').removeClass('input-sm');
        $('.dataTables_filter').addClass('pull-right');
        $('.pagination').addClass('pull-right');

        // FIX: Move modal to body root to fix z-index stacking issue
        // The modal is rendered inside .main-content which creates stacking context issues
        $('#myModal').appendTo('body');
    });

    $('table').dataTable({
        "searching": true,
        //hotfix-2.0.4, by Yudo Maryanto, Mengubah paging menjadi 100
        "iDisplayLength": 10
    });
  </script>


      <script>
  $(function () {
    $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>
<script type="text/javascript">
  var pa="";
  $(document).ready(function() {
    // Initialize Select2 with modal-specific configuration
    function initSelect2() {
      $("#part_number").select2({
        dropdownParent: $('#myModal'), // FIX: Ensure dropdown appears within modal
        placeholder: 'Type at least 2 characters to search...',
        allowClear: true,
        width: '100%',
        ajax:{
          url     : "{{url('/getajaxpartPis')}}",
          dataType  : 'json',
          delay   : 250,
          data    : function(params){
            return {
              q     : params.term,
              page  : params.page
            };
          },
          processResults: function (data) {
            return {
              results: $.map(data, function (item) {
                return {
                  text: item.part_number,
                  id: item.part_number
                }
              })
            };
          }
        },
        minimumResultsForSearch: Infinity, // Always show search box
        cache : true,
        minimumInputLength  : 2,
      });
      
      // FIX: Ensure selected value is properly stored when selection is made
      $("#part_number").on('select2:select', function (e) {
        var data = e.params.data;
        // Ensure the option exists in the select element
        if ($("#part_number option[value='" + data.id + "']").length === 0) {
          // Add the option if it doesn't exist
          var newOption = new Option(data.text, data.id, true, true);
          $("#part_number").append(newOption).trigger('change');
        }
        // Ensure the value is set
        $("#part_number").val(data.id).trigger('change');
      });
    }
    
    // Initialize Select2 on page load
    initSelect2();
    
    // Add click handler to ensure dropdown opens and is typeable
    $(document).on('click', '#part_number', function() {
      // Open Select2 dropdown when clicked
      if (!$('#part_number').data('select2')) {
        initSelect2();
      }
      setTimeout(function() {
        $('#part_number').select2('open');
        // Ensure search field is enabled
        setTimeout(function() {
          var searchField = $('.select2-search__field');
          if (searchField.length > 0) {
            searchField.prop('disabled', false)
                      .prop('readonly', false)
                      .removeAttr('disabled')
                      .removeAttr('readonly')
                      .focus();
          }
        }, 100);
      }, 50);
    });

    // FIX: Ensure modal is above backdrop and properly configured
    $('#myModal').on('show.bs.modal', function (e) {
      // Ensure modal is in body (already moved on page load)
      // Fix z-index at runtime in case CSS is overridden
      $(this).css('z-index', 1050);
      
      // Ensure backdrop is below modal
      setTimeout(function() {
        $('.modal-backdrop').css('z-index', 1040);
      }, 0);
    });

    // FIX: Ensure modal inputs are not readonly/disabled on open
    $('#myModal').on('shown.bs.modal', function () {
      // Remove any disabled/readonly attributes that might be preventing interaction
      $('#myModal input').prop('readonly', false).prop('disabled', false);
      $('#myModal select').prop('disabled', false);
      $('#myModal textarea').prop('readonly', false).prop('disabled', false);
      
      // Ensure modal dialog has pointer events
      $('#myModal .modal-dialog, #myModal .modal-content').css('pointer-events', 'auto');
      
      // Destroy and reinitialize Select2 to ensure it works properly in modal
      if ($("#part_number").hasClass("select2-hidden-accessible")) {
        $("#part_number").select2('destroy');
      }
      
      // Ensure select element is enabled before reinitializing
      $('#part_number').prop('disabled', false).removeAttr('disabled');
      
      // Reinitialize Select2
      initSelect2();
      
      // Focus on Select2 search box after initialization
      setTimeout(function() {
        // Open the dropdown
        $('#part_number').select2('open');
        
        // Ensure the search input is enabled and focusable
        setTimeout(function() {
          var searchField = $('.select2-search__field');
          if (searchField.length > 0) {
            searchField.prop('disabled', false)
                      .prop('readonly', false)
                      .removeAttr('disabled')
                      .removeAttr('readonly')
                      .attr('tabindex', '0')
                      .focus()
                      .click();
          }
        }, 150);
      }, 300);
    });
  });
  
      // alert('test');
    </script>
<script>
$(function(){

var manual = $("#manual");
var hidden_a = $("#div_hidden_part_no_aiia");
var hidden_b = $("#div_hidden_part_name");
var hidden_c = $("#div_hidden_max");
var hidden_d = $("#div_hidden_min");
var hidden_e = $("#part_number_show");

// Ensure manual input fields are hidden initially
hidden_a.hide();
hidden_b.hide();
hidden_c.hide();
hidden_d.hide();

// Manual input checkbox handler
manual.on('change', function() {
    if ($(this).is(":checked")) {
        // Show manual input fields
        hidden_a.show();
        hidden_b.show();
        hidden_c.show();
        hidden_d.show();
        // Hide select2 dropdown
        hidden_e.hide();
        // Clear and disable select2
        $('#part_number').val(null).trigger('change');
        // Focus on manual input field
        setTimeout(function() {
            $('#hidden_part_no_aiia').focus();
        }, 100);
    } else {
        // Hide manual input fields
        hidden_a.hide();
        hidden_b.hide();
        hidden_c.hide();
        hidden_d.hide();
        // Show select2 dropdown
        hidden_e.show();
        // Clear manual input fields
        $('#hidden_part_no_aiia').val('');
        $('#hidden_part_name').val('');
        $('#min_stock').val('');
        $('#max_stock').val('');
    }
});

// Handle form reset properly
$('#pisForm').on('reset', function(e) {
    setTimeout(function() {
        // Clear Select2
        $('#part_number').val(null).trigger('change');
        
        // Uncheck manual input and hide fields
        if (manual.is(':checked')) {
            manual.prop('checked', false).trigger('change');
        }
    }, 10);
});

// Reset form when modal is hidden
$('#myModal').on('hidden.bs.modal', function () {
    $('#pisForm')[0].reset();
    $('#part_number').val(null).trigger('change');
    manual.prop('checked', false).trigger('change');
});

// This handler is now merged with the one above - removed to avoid conflicts

});
</script>

<script>
  function cekData(){
    var part_number_aiia     = $('#hidden_part_no_aiia').val();
    // FIX: Get part_number value - try multiple methods to ensure we get the value
    var part_number = $('#part_number').val();
    // If val() returns empty, try getting from Select2 data
    if (!part_number && $('#part_number').data('select2')) {
      var select2Data = $('#part_number').select2('data');
      if (select2Data && select2Data.length > 0) {
        part_number = select2Data[0].id;
      }
    }
    var part_number_customer = $('#part_number_customer').val();
    var part_kind            = $('#part_kind').val();
    var part_dock            = $('#part_dock').val();
    var part_name            = $('#hidden_part_name').val();
    var back_number          = $('#back_number').val();
    var min_stock            = $('#min_stock').val();
    var max_stock            = $('#max_stock').val();
    var qty_kanban           = $('#qty_kanban').val();
    var pis_picture          = $('#pis_picture')[0].files[0];

    // Check if manual input is enabled
    var isManual = $('#manual').is(':checked');

    // Basic validation
    if (isManual) {
        // Manual mode: requires part_number_aiia, part_name, min/max stock
        if (!part_number_aiia || !part_name) {
            alert('⚠️ Manual Input Mode: Please fill Part Number AIIA and Part Name');
            return;
        }
        if (!min_stock || !max_stock) {
            alert('⚠️ Manual Input Mode: Please fill Min Stock and Max Stock');
            return;
        }
    } else {
        // Non-manual: requires part_number from select2
        if (!part_number) {
            alert('⚠️ Please select Part Number AIIA from dropdown (type at least 2 characters)');
            return;
        }
    }

    // Common required fields
    if (!part_number_customer || !back_number || !qty_kanban || !pis_picture) {
        alert('⚠️ Please fill all required fields:\n- Part Number Customer\n- Back No\n- Qty\n- Picture');
        return;
    }

    if (!part_kind || !part_dock) {
        alert('⚠️ Please select Type and Destination');
        return;
    }

    // Show loading indicator
    var $saveBtn = $('button[onclick="cekData()"]');
    var originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    // First, validate with server to determine which endpoint to use
    $.ajax({
        url: "{{url('/pis/validasi')}}",
        type: "GET",
        data: {
            part_number_aiia: part_number_aiia,
            part_number: part_number,
            part_number_customer: part_number_customer,
            part_kind: part_kind,
            part_dock: part_dock,
            back_number: back_number,
            qty_kanban: qty_kanban,
            part_name: part_name,
            min_stock: min_stock,
            max_stock: max_stock
        },
        success: function(data){
            var path = "";
            var formData = new FormData();
            
            // Determine which endpoint to use
            if(data == "save"){
                // Part exists in avi_parts - use addpis
                path = "{{ url('/pis/addpis') }}";
                // FIX: Ensure part_number is not empty before appending
                if (!part_number) {
                    alert('⚠️ Error: Part Number AIIA is required but was not captured. Please select a part number again.');
                    $saveBtn.prop('disabled', false).html(originalText);
                    return;
                }
                formData.append('part_number', part_number);
            } else if(data == "save1"){
                // Part doesn't exist - use addpart (manual input)
                path = "{{ url('/pis/addpart') }}";
                formData.append('hidden_part_no_aiia', part_number_aiia);
                formData.append('hidden_part_name', part_name);
                formData.append('min_stock', min_stock);
                formData.append('max_stock', max_stock);
            } else {
                alert('⚠️ Validation error: ' + data);
                $saveBtn.prop('disabled', false).html(originalText);
                return;
            }

            // Add common fields
            formData.append('part_number_customer', part_number_customer);
            formData.append('back_number', back_number);
            formData.append('part_kind', part_kind);
            formData.append('part_dock', part_dock);
            formData.append('qty_kanban', qty_kanban);
            formData.append('pis_picture', pis_picture);
            formData.append('_token', '{{ csrf_token() }}');

            // Submit form with file
            $.ajax({
                url: path,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response){
                    // Close modal
                    $('#myModal').modal('hide');
                    // Reload page to show updated data
                    window.location.reload();
                },
                error: function(xhr){
                    var errorMsg = 'Error saving data';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // Try to extract error from HTML response
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(xhr.responseText, 'text/html');
                        var errorElement = doc.querySelector('.alert-danger, .error');
                        if (errorElement) {
                            errorMsg = errorElement.textContent.trim();
                        }
                    }
                    alert('❌ ' + errorMsg);
                    $saveBtn.prop('disabled', false).html(originalText);
                }
            });
        },
        error: function(xhr){
            alert('❌ Error validating data. Please try again.');
            $saveBtn.prop('disabled', false).html(originalText);
        }
    });
  }
</script>
@endsection
