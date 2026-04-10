@extends('layouts.root.main')

@section('main')
    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card card-danger">
                <div class="card-header justify-content-center">
                    <h3 class="p-4">Check Part Status</h3>
                </div>
                <div class="card-body">

                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Display Custom Error Message --}}
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {!! session('error') !!}
                        </div>
                    @elseif (isset($error))
                        <div class="alert alert-danger">
                            {!! $error !!}
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-danger">
                                <div class="card-header text-center">
                                    <h3 class="p-4">Scan</h3>
                                </div>
                                <div class="card-body">
                                    <div id="notif-area"></div>

                                    <div class="form-group">
                                        <label for="code">Scan Barcode</label>
                                        <input type="text" id="code" class="form-control"
                                            placeholder="Scan barcode di sini" autocomplete="off" autofocus>
                                    </div>

                                    <div class="mt-4" id="result-area" style="display: none;">
                                        <h5>Information Details</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th width="200">ID</th>
                                                        <td id="result-id"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Line</th>
                                                        <td id="result-line"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Model</th>
                                                        <td id="result-model"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Dandori Board</th>
                                                        <td id="result-dandori-board"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Barcode</th>
                                                        <td id="result-barcode"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Last 4</th>
                                                        <td id="result-last4"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Scan Date</th>
                                                        <td id="result-scan-date"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Scanned At</th>
                                                        <td id="result-scanned-at"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Kanban ID</th>
                                                        <td id="result-kanban-id"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Created At</th>
                                                        <td id="result-created-at"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Updated At</th>
                                                        <td id="result-updated-at"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#code').focus();

            function showNotif(message, type = 'danger') {
                $('#notif-area').html(`
                <div class="alert alert-${type}">
                    ${message}
                </div>
            `);
            }

            function clearNotif() {
                $('#notif-area').html('');
            }

            function clearResult() {
                $('#result-area').hide();

                $('#result-id').text('');
                $('#result-line').text('');
                $('#result-model').text('');
                $('#result-dandori-board').text('');
                $('#result-barcode').text('');
                $('#result-last4').text('');
                $('#result-scan-date').text('');
                $('#result-scanned-at').text('');
                $('#result-kanban-id').text('');
                $('#result-created-at').text('');
                $('#result-updated-at').text('');
            }

            function fillResult(data) {
                $('#result-id').text(data.id ?? '-');
                $('#result-line').text(data.line ?? '-');
                $('#result-model').text(data.model ?? '-');
                $('#result-dandori-board').text(data.dandori_board ?? '-');
                $('#result-barcode').text(data.barcode ?? '-');
                $('#result-last4').text(data.last4 ?? '-');
                $('#result-scan-date').text(data.scan_date ?? '-');
                $('#result-scanned-at').text(data.scanned_at ?? '-');
                $('#result-kanban-id').text(data.kanban_id ?? '-');
                $('#result-created-at').text(data.created_at ?? '-');
                $('#result-updated_at').text(data.updated_at ?? '-');

                // perbaikan karena id html pakai result-updated-at
                $('#result-updated-at').text(data.updated_at ?? '-');

                $('#result-area').show();
            }

            function submitBarcode() {
                let barcode = $('#code').val().trim();

                clearNotif();
                clearResult();

                if (barcode === '') {
                    showNotif('Barcode tidak boleh kosong.');
                    $('#code').val('').focus();
                    return;
                }

                $.ajax({
                    url: "{{ route('dashboard.partCheckSubmit') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode
                    },
                    success: function(response) {
                        if (response.status) {
                            showNotif('Data part ditemukan.', 'success');
                            fillResult(response.data);
                        } else {
                            showNotif(response.message ?? 'Data tidak ditemukan.');
                        }

                        $('#code').val('').focus();
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        showNotif(message, 'danger');
                        $('#code').val('').focus();
                    }
                });
            }

            $('#code').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    submitBarcode();
                }
            });
        });
    </script>
@endsection
