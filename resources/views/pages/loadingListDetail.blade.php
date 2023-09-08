@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-12 m-auto">
            <div class="card card-info shadow" style="border-radius:20px">
                <div class="card-body">
                    <h4 class="card-title mt-3 mb-4 text-dark text-center">LOADING LIST DETAIL</h4>

                    <div class="row mt-5 mb-4 m-auto">
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-dark" style="font-weight: 700">Loading
                                    List No. <p class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->number }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">PDS Number <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->pds_number }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Customer <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->name }}</p>
                                </li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-dark" style="font-weight: 700">Delivery Date <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->delivery_date }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Shipping Date <p
                                        class="text-right" style="display: inline;">
                                        : {{ $loadingListDetail->shipping_date }}</p>
                                </li>
                                <li class="list-group-item text-dark" style="font-weight: 700">Cycle <p class="text-right"
                                        style="display: inline;">
                                        : {{ $loadingListDetail->cycle }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="card card-danger mt-3 shadow" style="border-radius:10px">
        <div class="card-body">
            <h5 class="card-title mt-3 text-dark text-center">DETAILS</h5>
            <table class="table table-responsive-lg" id="loadingList">
                <thead>
                    <tr>
                        {{-- <th class="text-left">Part Name</th> --}}
                        <th class="text-center">Customer Part No.</th>
                        <th class="text-center">Internal Part No.</th>
                        <th class="text-center">Customer Back No.</th>
                        <th class="text-center">Internal Back No.</th>
                        <th class="text-center">Kanban Qty</th>
                        <th class="text-center">Total Scan</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($details as $detail)
                        <tr>
                            {{-- <td class="text-left">{{ $detail->part_name }}</td> --}}
                            <td class="text-center">{{ $detail->pn_customer }}</td>
                            <td class="text-center">{{ $detail->pn_internal }}</td>
                            <td class="text-center">{{ $detail->bn_customer }}</td>
                            <td class="text-center">{{ $detail->bn_internal }}</td>
                            <td class="text-center">{{ $detail->kanban_qty }}</td>
                            <td class="text-center">
                                <span id="actual">{{ $detail->actual_kanban_qty }}</span>
                                <input id="editActual" class="form-control" type="number"
                                    value="{{ $detail->actual_kanban_qty }}" data-width="100"
                                    style="border-radius:6px; display:none">
                            </td>
                            <td class="text-center">
                                <button class="btn btn-icon btn-primary edit"><i class="far fa-edit"></i></button>
                                <button class="btn btn-icon btn-success save" style="display: none"><i
                                        class="fas fa-check"></i></button>
                                <button class="btn btn-icon btn-danger cancel" style="display: none"><i
                                        class="fas fa-times"></i></button>
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
            lengthMenu: [
                [5, 10, 'All'],
                [5, 10, 'All']
            ],
            columnDefs: [{
                targets: [6],
                orderable: false
            }]
        });

        $('.edit').on('click', function() {
            // hide span
            $(this).closest('tr').find('#actual').hide();

            // show input
            $(this).closest('tr').find('#editActual').show();

            // show save button
            $(this).closest('tr').find('.save').css({
                display: 'inline'
            });

            // show cancel button
            $(this).closest('tr').find('.cancel').show({
                display: 'inline'
            });

            // hide edit button
            $(this).closest('tr').find('.edit').hide();
        });
    });
</script>
