@extends('layouts.root.minimal')

@section('main')
    <style>
        .bella-table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 14px;
        }

        .bella-table-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
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

        .bella-table-card-body {
            padding: 18px 20px 20px;
        }

        .absen-steps {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .absen-step {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            background: var(--bg);
        }

        .absen-step-num {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: var(--primary-light);
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .absen-step label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .absen-step .form-control,
        .absen-step select {
            height: 42px;
            border: 1px solid var(--border) !important;
            border-radius: 8px !important;
            background: var(--card) !important;
            color: var(--text) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 13px !important;
            font-weight: 600;
            box-shadow: none !important;
        }

        .absen-step .form-control:focus,
        .absen-step select:focus {
            border-color: var(--sky) !important;
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10) !important;
        }

        .absen-hint {
            margin-top: 7px;
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .absen-customer-chip {
            display: none;
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #bbf7d0;
            background: #dcfce7;
            color: #15803d;
            font-size: 12.5px;
            font-weight: 700;
        }

        .absen-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .bella-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .bella-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .bella-badge-gray {
            background: #f1f5f9;
            color: #64748b;
        }

        .bella-badge-amber {
            background: #fef3c7;
            color: #b45309;
        }

        #absenAlert .alert {
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        #driverAbsenTable {
            width: 100% !important;
        }

        #driverAbsenTable thead th {
            text-align: center !important;
            padding: 9px 12px !important;
            color: var(--text-muted) !important;
            font-size: 10.5px !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            font-weight: 700 !important;
            background: var(--bg) !important;
            border-bottom: 1px solid var(--border) !important;
        }

        #driverAbsenTable tbody td {
            text-align: center !important;
            padding: 10px 12px !important;
            border-bottom: 1px solid var(--border) !important;
            vertical-align: middle !important;
            font-size: 12.5px !important;
        }

        @media (max-width: 768px) {
            .absen-steps {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="bella-table-card">
        <div class="bella-table-card-header">
            <div>
                <div class="bella-table-card-title"><i class="fas fa-truck" style="margin-right:6px;"></i> Absen Driver</div>
                <div class="bella-table-card-subtitle">Scan label customer, lalu scan barcode cycle atau pilih master cycle customer.</div>
            </div>
        </div>

        <div class="bella-table-card-body">
            <div id="absenAlert"></div>

            <div class="absen-steps">
                <div class="absen-step">
                    <span class="absen-step-num">1</span>
                    <label for="scanCustomer">Scan Label Customer</label>
                    <input type="text" id="scanCustomer" class="form-control" placeholder="Scan label customer di sini"
                        autocomplete="off" autofocus>
                    <div class="absen-hint">Scanner akan mengirim Enter setelah scan. Customer dicocokkan dari kode, nama, atau posisi karakter label.</div>
                    <div class="absen-customer-chip" id="selectedCustomerChip"></div>
                </div>

                <div class="absen-step">
                    <span class="absen-step-num">2</span>
                    <label for="scanCycle">Scan Barcode Cycle</label>
                    <input type="text" id="scanCycle" class="form-control" placeholder="Scan barcode cycle (opsional)"
                        autocomplete="off" disabled>
                    <div class="absen-hint">Scan nama cycle master atau ID master cycle. Bisa juga pilih manual dari dropdown master cycle.</div>

                    <label for="cycleSelect" style="margin-top:14px;">Master Cycle</label>
                    <select id="cycleSelect" class="form-control" disabled>
                        <option value="">-- Scan customer terlebih dahulu --</option>
                    </select>
                    <div class="absen-hint">Data cycle diambil dari Master Cycle (Preparation Monitoring), termasuk ETA truck.</div>
                </div>
            </div>

            <div class="absen-actions">
                <button type="button" class="bella-btn bella-btn-primary" id="btnSubmitAbsen" disabled>
                    <i class="fas fa-check-circle"></i> Konfirmasi Absen
                </button>
                <button type="button" class="bella-btn bella-btn-secondary" id="btnResetAbsen">
                    <i class="fas fa-redo-alt"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <div class="bella-table-card">
        <div class="bella-table-card-header">
            <div>
                <div class="bella-table-card-title">Daftar Loading List Hari Ini</div>
                <div class="bella-table-card-subtitle">Status truk: belum diambil / sudah diambil</div>
            </div>
            <input type="date" id="filterDate" class="form-control" style="max-width:180px;height:36px;" value="{{ date('Y-m-d') }}">
        </div>
        <div class="table-responsive">
            <table class="table" id="driverAbsenTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Loading List</th>
                        <th>Customer</th>
                        <th>Cycle</th>
                        <th>Delivery Date</th>
                        <th>Status Truk</th>
                        <th>Waktu Diambil</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}"></script>
<script>
    $(document).ready(function() {
        let selectedCustomer = null;
        let customerBuffer = '';
        let cycleBuffer = '';

        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        function showAlert(type, message) {
            const cls = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
            $('#absenAlert').html('<div class="alert ' + cls + '">' + message + '</div>');
        }

        function clearAlert() {
            $('#absenAlert').empty();
        }

        function updateSubmitState() {
            const cycleVal = $('#cycleSelect').val() || '';
            $('#btnSubmitAbsen').prop('disabled', !(selectedCustomer && cycleVal));
        }

        function resetForm() {
            selectedCustomer = null;
            customerBuffer = '';
            cycleBuffer = '';
            $('#scanCustomer').val('').prop('disabled', false).focus();
            $('#scanCycle').val('').prop('disabled', true);
            $('#cycleSelect').html('<option value="">-- Scan customer terlebih dahulu --</option>').prop('disabled', true);
            $('#selectedCustomerChip').hide().text('');
            $('#btnSubmitAbsen').prop('disabled', true);
            clearAlert();
        }

        function populateCycles(cycles) {
            const $select = $('#cycleSelect');
            $select.empty().append('<option value="">-- Pilih master cycle --</option>');

            if (!cycles || !cycles.length) {
                $select.append('<option value="" disabled>Belum ada master cycle untuk customer ini</option>');
                $select.prop('disabled', true);
                $('#scanCycle').prop('disabled', true);
                updateSubmitState();
                return;
            }

            (cycles || []).forEach(function(item) {
                const cycleName = item.cycle_name || item.cycle || '';
                const statusText = item.truck_status === 'picked' ? ' · Sudah diambil' : '';
                const truckTime = item.truck_time ? ' · ETA ' + item.truck_time : '';
                const llInfo = item.has_loading_list
                    ? ' · ' + (item.loading_list_count || 0) + ' LL'
                    : ' · Tanpa LL';

                $select.append(
                    $('<option>', {
                        value: cycleName,
                        text: cycleName + truckTime + llInfo + statusText
                    })
                );
            });

            $select.prop('disabled', false);
            $('#scanCycle').prop('disabled', false);
            updateSubmitState();
        }

        function scanCustomer(barcode) {
            $.ajax({
                url: "{{ route('loadingList.driverAbsen.scanCustomer') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: csrfToken,
                    barcode: barcode,
                    delivery_date: $('#filterDate').val()
                },
                success: function(res) {
                    if (res.status !== 'success') {
                        showAlert('error', res.message || 'Customer tidak ditemukan.');
                        $('#scanCustomer').val('').focus();
                        return;
                    }

                    selectedCustomer = res.customer;
                    $('#scanCustomer').val(res.customer.name).prop('disabled', true);
                    $('#selectedCustomerChip').html(
                        '<i class="fas fa-building"></i> ' + res.customer.name + ' (' + (res.customer.code || '-') + ')'
                    ).show();
                    populateCycles(res.cycles || []);
                    clearAlert();
                    $('#scanCycle').focus();
                    absenTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal memproses scan customer.';
                    showAlert('error', msg);
                    $('#scanCustomer').val('').focus();
                }
            });
        }

        $('#scanCustomer').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const code = customerBuffer.trim() || $(this).val().trim();
                customerBuffer = '';
                if (!code) return;
                scanCustomer(code);
            } else {
                customerBuffer += String.fromCharCode(e.which);
            }
        });

        $('#scanCycle').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const code = cycleBuffer.trim() || $(this).val().trim();
                cycleBuffer = '';
                if (!code || !selectedCustomer) return;

                $.ajax({
                    url: "{{ route('loadingList.driverAbsen.submit') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: csrfToken,
                        customer_id: selectedCustomer.id,
                        cycle: $('#cycleSelect').val() || code,
                        barcode: code,
                        delivery_date: $('#filterDate').val()
                    },
                    success: handleSubmitSuccess,
                    error: function(xhr) {
                        showAlert('error', xhr.responseJSON?.message || 'Gagal absen cycle.');
                        $('#scanCycle').val('').focus();
                    }
                });
            } else {
                cycleBuffer += String.fromCharCode(e.which);
            }
        });

        $('#cycleSelect').on('change', updateSubmitState);

        $('#btnSubmitAbsen').on('click', function() {
            if (!selectedCustomer) return;
            const cycle = $('#cycleSelect').val();
            if (!cycle) {
                showAlert('error', 'Pilih cycle terlebih dahulu.');
                return;
            }

            $.ajax({
                url: "{{ route('loadingList.driverAbsen.submit') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: csrfToken,
                    customer_id: selectedCustomer.id,
                    cycle: cycle,
                    barcode: $('#scanCycle').val().trim() || null,
                    delivery_date: $('#filterDate').val()
                },
                success: handleSubmitSuccess,
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'Gagal menyimpan absen.');
                }
            });
        });

        function handleSubmitSuccess(res) {
            const type = res.status === 'success' ? 'success' : (res.status === 'warning' ? 'warning' : 'error');
            showAlert(type, res.message || 'Selesai.');
            absenTable.ajax.reload(null, false);

            if (res.status === 'success' && selectedCustomer) {
                $.get("{{ route('loadingList.driverAbsen.cycles') }}", {
                    customer_id: selectedCustomer.id,
                    delivery_date: $('#filterDate').val()
                }, function(data) {
                    populateCycles(data.cycles || []);
                });
            }

            $('#scanCycle').val('');
            if (res.status === 'success') {
                setTimeout(resetForm, 1500);
            }
        }

        $('#btnResetAbsen').on('click', resetForm);

        const absenTable = $('#driverAbsenTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('loadingList.driverAbsen.data') }}",
                data: function(d) {
                    d.delivery_date = $('#filterDate').val();
                    if (selectedCustomer) {
                        d.customer_id = selectedCustomer.id;
                    }
                }
            },
            columns: [
                { data: 'number' },
                { data: 'customer_name' },
                { data: 'cycle' },
                { data: 'delivery_date_fmt' },
                { data: 'truck_status_badge', orderable: false, searchable: false },
                { data: 'picked_at_fmt' }
            ],
            order: [[3, 'desc']],
            pageLength: 25
        });

        $('#filterDate').on('change', function() {
            absenTable.ajax.reload();
        });
    });
</script>
