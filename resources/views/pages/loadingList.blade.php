@extends('layouts.root.main')

@section('main')
    <div class="card card-info mt-3 shadow" style="border-radius:10px">
        <div class="card-body">
            <h4 class="card-title mt-3 text-dark text-center">DELIVERY MONITORING</h4>
            <table class="table table-responsive-lg" id="loadingList">
                <thead>
                    <tr>
                        <th class="text-center">Loading List Number</th>
                        <th class="text-center">PDS Number</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Cycle</th>
                        <th class="text-center">Delivery Date</th>
                        <th class="text-center">Status</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($loadingLists as $loadingList)
                        <tr>
                            <td class="text-center">{{ $loadingList->number }}</td>
                            <td class="text-center">{{ $loadingList->pds_number }}</td>
                            <td class="text-center">{{ $loadingList->customer_id }}</td>
                            <td class="text-center">{{ $loadingList->cycle }}</td>
                            <td class="text-center">{{ $loadingList->delivery_date }}</td>

                            @if ($loadingList->detail[0]->actual_kanban_qty >= $loadingList->detail[0]->kanban_qty)
                                <td class="text-center"><span class="badge badge-success">COMPLETE</span></td>
                            @elseif ($loadingList->detail[0]->actual_kanban_qty == 0)
                                <td class="text-center"><span class="badge badge-danger">NOT STARTED</span></td>
                            @elseif ($loadingList->detail[0]->actual_kanban_qty < $loadingList->detail[0]->kanban_qty)
                                <td class="text-center"><span class="badge badge-warning">ON PROGRESS</span></td>
                            @endif

                            <td class="text-center">
                                <a href="/loading-list/{{ $loadingList->id }}" class="btn btn-info text-white">DETAIL</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

{{-- mqtt --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"
    integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
    $(document).ready(function() {
        $('#loadingList').DataTable({
            columnDefs: [{
                targets: [6],
                orderable: false
            }]
        });
    });
</script>
