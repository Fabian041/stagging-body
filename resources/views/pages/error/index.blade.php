@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-info shadow" style="padding: 40px;padding-top:60px; border-radius:16px">
                <form>
                    <div class="form-row align-items-end">
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold">Area</label>
                                <div class="input-group">
                                    <select class="custom-select" id="area">
                                        <option value="">-- Select Area --</option>
                                        <option value="admin">Admin</option>
                                        <option value="mh">Material Handling</option>
                                        <option value="prod">Production</option>
                                        <option value="ppic">PPIC</option>
                                        <option value="Packing">Packing</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold">Start Date</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold">End Date</label>
                                <input type="date" id="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12">
                            <div class="form-group mb-2">
                                <label class="d-block invisible">Export</label>
                                <button id="export-error-log" class="btn btn-success btn-lg btn-block" type="button" style="height:38px;">
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card card-danger mt-2 shadow" style="border-radius:10px">
        <div class="card-body">
            <h4 class="card-title mt-3 mb-3 text-dark text-center">Application Error Logs</h4>
            <div class="table-responsive-lg">
                <table class="table" id="loadingList" style="width: 100%">
                    <thead>
                        <tr>
                            <th class="text-center">Area</th>
                            <th class="text-center">Messege</th>
                            <th class="text-center">Expected</th>
                            <th class="text-center">Scanned</th>
                            <th class="text-center">Date</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    $(document).ready(function() {
        let table = $('#loadingList').DataTable({
            scrollX: false,
            processing: false,
            serverSide: true,
            ajax: {
                url: `{{ url('error/getErrorLogs') }}`,
                dataType: 'json',
                data: function(d) {
                    d.area = $('#area').val() || '';
                    d.start_date = $('#start_date').val() || '';
                    d.end_date = $('#end_date').val() || '';
                }
            },
            columns: [{
                    data: 'area',
                },
                {
                    data: 'message',
                },
                {
                    data: 'expected',
                },
                {
                    data: 'scanned'
                },
                {
                    data: 'date'
                },
            ]
        });


        $('#area, #start_date, #end_date').on('change', function() {
            table.draw();
        });

        $('#export-error-log').on('click', function() {
            const area = $('#area').val() || '';
            const params = new URLSearchParams();
            if (area) {
                params.append('area', area);
            }
            const startDate = $('#start_date').val() || '';
            const endDate = $('#end_date').val() || '';
            if (startDate) {
                params.append('start_date', startDate);
            }
            if (endDate) {
                params.append('end_date', endDate);
            }

            const baseUrl = `{{ route('error.export') }}`;
            const url = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

            window.location.href = url;
        });
    });
</script>
