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
                            <div id="pisRowsContainer">
                              <div class="pis-row border rounded p-3 mb-3" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                  <h6 class="mb-0"><b>Master PIS #<span class="row-number">1</span></b></h6>
                                  <button type="button" class="btn btn-sm btn-outline-danger remove-row" style="display:none;">
                                    <i class="fas fa-times"></i> Hapus
                                  </button>
                                </div>

                                <div class="form-group part_number_aiia_group">
                                  <label>Part Number AIIA</label>
                                  <input type="text"
                                         class="form-control part_number_aiia"
                                         name="part_number_aiia[]"
                                         placeholder="Part Number AIIA"
                                         onkeyup="this.value = this.value.toUpperCase()"
                                         autocomplete="off">
                                </div>

                                <div class="form-group part_number_customer_group">
                                  <label>Part Number Customer</label>
                                  <input type="text" class="form-control part_number_customer" name="part_number_customer[]" placeholder="Part Number Customer" onkeyup="this.value = this.value.toUpperCase()" autocomplete="off">
                                </div>

                                <div class="form-group back_number_group">
                                  <label>Back No</label>
                                  <input type="text" class="form-control back_number" name="back_number[]" placeholder="Back No" onkeyup="this.value = this.value.toUpperCase()" autocomplete="off">
                                </div>



                                <div class="form-group qty_group">
                                  <label>Qty</label>
                                  <input type="number" class="form-control qty_kanban" name="qty_kanban[]" placeholder="Qty" autocomplete="off">
                                </div>

                                <div class="form-group part_kind_group">
                                  <label>Type</label>
                                  <select class="form-control part_kind" name="part_kind[]">
                                    <option value="">-- Select Type --</option>
                                    <option value="OEM">OEM</option>
                                    <option value="DANDORY">DANDORY</option>
                                  </select>
                                </div>

                                <div class="form-group part_dock_show">
                                  <label>Destination</label>
                                  <select class="form-control part_dock" name="part_dock[]">
                                    <option value="">-- Select Destination --</option>
                                    <option value="TMMIN SPD">TMMIN SPD</option>
                                    <option value="TMMIN SPD-ADM">TMMIN SPD-ADM</option>
                                    <option value="43">43</option>
                                    <option value="53">53</option>
                                    <option value="1L">1L</option>
                                    <option value="1N">1N</option>
                                    <option value="HINO-SPD">HINO-SPD</option>
                                    <option value="SIM-SPD">SIM-SPD</option>
                                    <option value="MMKI">MMKI</option>
                                    <option value="MMKI-SPD">MMKI-SPD</option>
                                    <option value="6I">6I</option>
                                    <option value="TAM-TAM">TAM-TAM</option>
                                    <option value="TAM-ADM">TAM-ADM</option>
                                    <option value="TAM-HINO">TAM-HINO</option>
                                    <option value="ADM-AS">ADM-AS</option>
                                    <option value="ADM-KP">ADM-KP</option>
                                    <option value="YHA">YHA</option>
                                    <option value="ADM">ADM</option>
                                    <option value="TTI">TTI</option>
                                  </select>
                                </div>

                                <div class="form-group pis_picture_group">
                                  <label>Picture (.JPG)</label>
                                  <input type="file" class="pis_picture" name="pis_picture[]" accept=".jpg,.jpeg,.png">
                                </div>
                              </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                              <button type="button" id="addPisRow" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Tambah Baris
                              </button>
                              <div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="cekData()">
                                  <span class='glyphicon glyphicon-floppy-saved'></span>&nbsp;
                                  <font face='calibri'><b>SAVE ALL</b></font>
                                </button>&nbsp;&nbsp;
                                <button type="reset" class="btn btn-sm btn-danger">
                                  <span class='glyphicon glyphicon-repeat'></span>&nbsp;
                                  <font face='calibri'><b>RESET</b></font>
                                </button>
                              </div>
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
    #myModal .modal-body label,
    #myModal .modal-body .select2-container,
    #myModal .modal-body .select2-selection {
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
    
    /* Select2 dropdown di dalam modal */
    #myModal .select2-container {
      z-index: 1060 !important;
    }
    
    #myModal .select2-dropdown {
      z-index: 1060 !important;
    }
    
    /* Prevent modal from clipping Select2 dropdown */
    #myModal .modal-body {
      overflow-x: visible;
      overflow-y: auto;
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
    
    /* ========== GRID LAYOUT UNTUK FORM PIS ========== */
    .pis-row {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
      align-items: start;
    }
    
    /* Urutan elemen dalam .pis-row:
       1. .d-flex (header) - FULL WIDTH
       2. .form-group.part_number_show - FULL WIDTH
       3. .form-group (manual toggle) - FULL WIDTH
       4. .form-group.div_hidden_part_no_aiia - FULL WIDTH
       5. .form-group.div_hidden_part_name - FULL WIDTH
       6. .form-group (part_number_customer) - COL 1
       7. .form-group (back_number) - COL 2
       8. .form-group.div_hidden_min - COL 1
       9. .form-group.div_hidden_max - COL 2
       10. .form-group (qty_kanban) - COL 1
       11. .form-group (part_kind) - COL 2
       12. .form-group.part_dock_show - FULL WIDTH
       13. .form-group (pis_picture) - FULL WIDTH
    */
    
    /* Header - full width */
    .pis-row > .d-flex {
      grid-column: 1 / -1;
    }
    
    /* Part Number AIIA dropdown - full width (child 2) */
    .pis-row > .form-group:nth-child(2) {
      grid-column: 1 / -1;
    }
    
    /* Manual Input checkbox - full width (child 3) */
    .pis-row > .form-group:nth-child(3) {
      grid-column: 1 / -1;
    }
    
    /* Hidden Part No AIIA - full width (child 4) */
    .pis-row > .form-group:nth-child(4) {
      grid-column: 1 / -1;
    }
    
    /* Hidden Part Name - full width (child 5) */
    .pis-row > .form-group:nth-child(5) {
      grid-column: 1 / -1;
    }
    
    /* Part Number Customer - kolom 1 (child 6) */
    .pis-row > .form-group:nth-child(6) {
      grid-column: 1;
    }
    
    /* Back Number - kolom 2 (child 7) */
    .pis-row > .form-group:nth-child(7) {
      grid-column: 2;
    }
    
    /* Min Stock - kolom 1 (child 8) */
    .pis-row > .form-group:nth-child(8) {
      grid-column: 1;
    }
    
    /* Max Stock - kolom 2 (child 9) */
    .pis-row > .form-group:nth-child(9) {
      grid-column: 2;
    }
    
    /* Qty - kolom 1 (child 10) */
    .pis-row > .form-group:nth-child(10) {
      grid-column: 1;
    }
    
    /* Type - kolom 2 (child 11) */
    .pis-row > .form-group:nth-child(11) {
      grid-column: 2;
    }
    
    /* Destination - full width (child 12) */
    .pis-row > .form-group:nth-child(12) {
      grid-column: 1 / -1;
    }
    
    /* Picture - full width (child 13) */
    .pis-row > .form-group:nth-child(13) {
      grid-column: 1 / -1;
    }

    /* Override grid placement for updated CREATE PIS fields (manual AIIA) */
    .pis-row .part_number_aiia_group {
      grid-column: 1 / -1 !important;
    }
    .pis-row .part_number_customer_group {
      grid-column: 1 !important;
    }
    .pis-row .back_number_group {
      grid-column: 2 !important;
    }
    .pis-row .qty_group {
      grid-column: 1 !important;
    }
    .pis-row .part_kind_group {
      grid-column: 2 !important;
    }
    .pis-row .part_dock_show {
      grid-column: 1 / -1 !important;
    }
    .pis-row .pis_picture_group {
      grid-column: 1 / -1 !important;
    }
    
    /* Form group margin adjustment untuk grid */
    .pis-row .form-group {
      margin-bottom: 0;
    }
    
    .pis-row .form-group label {
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
      display: block;
    }
    
    .pis-row .form-group input,
    .pis-row .form-group select,
    .pis-row .form-group .select2-container {
      font-size: 0.875rem;
    }
    
    .pis-row .form-group input,
    .pis-row .form-group select {
      width: 100%;
    }
    
    /* Manual Input Checkbox - align checkbox dengan text label */
    .manual-checkbox {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 0;
      font-weight: 500;
      font-size: 0.875rem;
    }
    
    .manual-checkbox input[type="checkbox"] {
      margin: 0;
      width: auto;
      height: auto;
      cursor: pointer;
      flex-shrink: 0;
    }
    
    .manual-checkbox span {
      margin: 0;
    }
    
    /* Responsive: di mobile, ubah menjadi 1 kolom */
    @media (max-width: 768px) {
      .pis-row {
        grid-template-columns: 1fr;
      }
      
      .pis-row > .form-group {
        grid-column: 1 !important;
      }
    }
    
    /* Ensure Select2 width is 100% dalam grid */
    .pis-row .select2-container--bootstrap {
      width: 100% !important;
    }
    
    .pis-row .select2-container {
      width: 100% !important;
    }
  </style>

  <script>
    $(document).ready(function(){
        $('input[type="search"]').removeClass('form-control').removeClass('input-sm');
        $('.dataTables_filter').addClass('pull-right');
        $('.pagination').addClass('pull-right');

        // Move modal to body root to fix z-index stacking issue
        $('#myModal').appendTo('body');
    });

    $('table').dataTable({
        "searching": true,
        "iDisplayLength": 10
    });
  </script>


  <script>
  $(function () {
    $('#example1').DataTable();
    if ($('#example2').length) {
      $('#example2').DataTable({
        'paging'      : true,
        'lengthChange': false,
        'searching'   : false,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false
      });
    }
  });
  </script>

  <script type="text/javascript">
  $(document).ready(function() {
    // Tambah baris baru (tanpa Select2 / dropdown pencarian)
    $('#addPisRow').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      var $container = $('#pisRowsContainer');
      var $lastRow   = $container.find('.pis-row').last();
      var newIndex   = $container.find('.pis-row').length;

      // Clone row terakhir tanpa event/data
      var $newRow = $lastRow.clone(false);

      $newRow.attr('data-index', newIndex);
      $newRow.find('.row-number').text(newIndex + 1);

      // Reset nilai input di row baru
      $newRow.find('input[type="text"], input[type="number"], input[type="file"]').val('');
      $newRow.find('.part_kind, .part_dock').val('');

      $container.append($newRow);

      // Tampilkan tombol hapus jika baris > 1
      if ($container.find('.pis-row').length > 1) {
        $container.find('.pis-row .remove-row').show();
      }

      // Scroll ke baris baru
      var $modalBody = $('#myModal .modal-body');
      $modalBody.animate({ scrollTop: $modalBody[0].scrollHeight }, 300);
    });

    // Hapus baris
    $('#pisRowsContainer').on('click', '.remove-row', function() {
      var $container = $('#pisRowsContainer');
      $(this).closest('.pis-row').remove();

      // Renumber dan sembunyikan tombol hapus jika hanya 1 baris
      $container.find('.pis-row').each(function(idx) {
        $(this).attr('data-index', idx);
        $(this).find('.row-number').text(idx + 1);
      });

      if ($container.find('.pis-row').length === 1) {
        $container.find('.pis-row .remove-row').hide();
      }
    });

    // Reset form
    $('#pisForm').on('reset', function() {
      setTimeout(function() {
        var $container = $('#pisRowsContainer');
        $container.find('.pis-row').not(':first').remove();
        var $first = $container.find('.pis-row').first();
        $first.attr('data-index', 0);
        $first.find('.row-number').text(1);
        $first.find('input[type="text"], input[type="number"], input[type="file"]').val('');
        $first.find('.part_kind, .part_dock').val('');
        $container.find('.remove-row').hide();
      }, 10);
    });

    // Reset form saat modal ditutup
    $('#myModal').on('hidden.bs.modal', function () {
      $('#pisForm')[0].reset();
    });

    // Modal z-index fixes
    $('#myModal').on('show.bs.modal', function () {
      $(this).css('z-index', 1050);
      setTimeout(function() {
        $('.modal-backdrop').css('z-index', 1040);
      }, 0);
    });

    $('#myModal').on('shown.bs.modal', function () {
      $('#myModal input').prop('readonly', false).prop('disabled', false);
      $('#myModal select').prop('disabled', false);
      $('#myModal textarea').prop('readonly', false).prop('disabled', false);
      $('#myModal .modal-dialog, #myModal .modal-content').css('pointer-events', 'auto');
    });
  });
  </script>

  <script>
  function cekData(){
    var $rows = $('#pisRowsContainer .pis-row');
    if ($rows.length === 0) {
      alert('Tidak ada data yang akan disimpan.');
      return;
    }

    var $saveBtn = $('button[onclick="cekData()"]');
    var originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    var currentIndex = 0;

    function processNextRow() {
      if (currentIndex >= $rows.length) {
        $('#myModal').modal('hide');
        window.location.reload();
        return;
      }

      var $row = $($rows[currentIndex]);
      var part_number_aiia    = $row.find('.part_number_aiia').val();
      var part_number_customer = $row.find('.part_number_customer').val();
      var part_kind            = $row.find('.part_kind').val();
      var part_dock            = $row.find('.part_dock').val();
      var back_number          = $row.find('.back_number').val();
      var qty_kanban           = $row.find('.qty_kanban').val();
      var pis_pictureInput     = $row.find('.pis_picture')[0];
      var pis_picture          = pis_pictureInput && pis_pictureInput.files[0] ? pis_pictureInput.files[0] : null;

      // backend `addpis` butuh `part_number` (AIIA) - dari input manual.
      var part_number          = part_number_aiia;

      if (!part_number_aiia || !part_number_customer || !back_number || !qty_kanban || !pis_picture) {
        alert('Baris #' + (currentIndex + 1) + ': lengkapi Part Number AIIA, Part Number Customer, Back No, Qty dan Picture.');
        $saveBtn.prop('disabled', false).html(originalText);
        return;
      }

      if (!part_kind || !part_dock) {
        alert('Baris #' + (currentIndex + 1) + ': pilih Type dan Destination.');
        $saveBtn.prop('disabled', false).html(originalText);
        return;
      }

      var formData = new FormData();
      var path = "{{ url('/pis/addpis') }}";

      formData.append('part_number', part_number);
      formData.append('part_number_customer', part_number_customer);
      formData.append('back_number', back_number);
      formData.append('part_kind', part_kind);
      formData.append('part_dock', part_dock);
      formData.append('qty_kanban', qty_kanban);
      formData.append('pis_picture', pis_picture);
      formData.append('_token', '{{ csrf_token() }}');

      $.ajax({
        url: path,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(){
          currentIndex++;
          processNextRow();
        },
        error: function(xhr){
          var errorMsg = 'Error saving data';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          } else if (xhr.responseText) {
            try {
              var parser = new DOMParser();
              var doc = parser.parseFromString(xhr.responseText, 'text/html');
              var errorElement = doc.querySelector('.alert-danger, .error');
              if (errorElement) {
                errorMsg = errorElement.textContent.trim();
              }
            } catch (e) {}
          }
          alert('Baris #' + (currentIndex + 1) + ' gagal disimpan: ' + errorMsg);
          $saveBtn.prop('disabled', false).html(originalText);
        }
      });
    }

    processNextRow();
  }
  </script>
@endsection
