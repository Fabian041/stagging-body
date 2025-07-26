@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-info shadow" style="padding: 40px;padding-top:60px; border-radius:16px">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="custom-select" id="area">
                                    <option selected disabled>-- Select Area --</option>
                                    <option value="admin">Admin</option>
                                    <option value="mh">Material Handling</option>
                                    <option value="prod">Production</option>
                                    <option value="ppic">PPIC</option>
                                </select>
                                <div class="input-group-append" id="reset">
                                    <button class="btn btn-lg btn-danger" type="button">Filter</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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


        $('#area').on('change', function() {
            // get all filter values
            let area = $('#area').val();

            if (area) {
                table.column(0).search(area);
            } else {
                table.column(0).search('');
            }

            table.draw();
        })
    });
</script>
