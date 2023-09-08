@extends('layouts.root.main')

@section('main')
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-info shadow" style="padding: 40px;padding-top:60px; border-radius:16px">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="input-group">
                                @isset($manifests)
                                    <select class="custom-select" id="manifest">
                                        <option selected disabled>-- Select manifest --</option>
                                        @foreach ($manifests as $manifest)
                                            <option value="{{ $manifest->pds_number }}">{{ $manifest->pds_number }}</option>
                                        @endforeach
                                    </select>
                                @endisset()
                                <select class="custom-select" id="cycle">
                                    <option selected disabled>-- Select cycle --</option>
                                    <option value="1">cycle 1</option>
                                    <option value="2">cycle 2</option>
                                    <option value="3">cycle 3</option>
                                    <option value="4">cycle 4</option>
                                    <option value="5">cycle 5</option>
                                </select>
                                @isset($customers)
                                    <select class="custom-select" id="customer">
                                        <option selected disabled>-- Select customer --</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->name }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                @endisset()
                                <input id="date" type="date" class="form-control" placeholder="Delivery date">
                                <div class="input-group-append" id="reset">
                                    <button class="btn btn-lg btn-danger" type="button">RESET</button>
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
            <h4 class="card-title mt-3 mb-3 text-dark text-center">DELIVERY MONITORING</h4>
            <table class="table table-responsive-lg" id="loadingList">
                <thead>
                    <tr>
                        <th class="text-center">Loading List Number</th>
                        <th class="text-center">PDS Number</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Cycle</th>
                        <th class="text-center">Delivery Date</th>
                        <th class="text-center">Progress</th>
                        <th class="text-center"></th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($loadingLists as $loadingList)
                        @php
                            // sum kanban qty
                            $totalKanban = 0;
                            $actualKanban = 0;
                            for ($i = 0; $i < count($loadingList->detail); $i++) {
                                $totalKanban = $totalKanban + $loadingList->detail[$i]->kanban_qty;
                                $actualKanban = $actualKanban + $loadingList->detail[$i]->actual_kanban_qty;
                            
                                // percentage
                                $progressPercentage = ($actualKanban / $totalKanban) * 100;
                                $progressPercentage = round($progressPercentage);
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loadingList->number }}</td>
                            <td class="text-center">{{ $loadingList->pds_number }}</td>
                            <td class="text-center">{{ $loadingList->customer->name }}</td>
                            <td class="text-center">{{ $loadingList->cycle }}</td>
                            <td class="text-center">{{ $loadingList->delivery_date }}</td>
                            @if ($actualKanban >= $totalKanban)
                                <td class="text-center">
                                    <div class="text-small float-right font-weight-bold text-muted ml-3">
                                        {{ $actualKanban }}/{{ $totalKanban }}</div>
                                    <div class="font-weight-bold mb-1" style="color: white">-</div>
                                    <div class="progress" data-height="20" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar" data-width="100%" aria-valuenow="100"
                                            aria-valuemin="0" aria-valuemax="100"
                                            style="width: 100%; background-color: lightgreen !important">
                                        </div>
                                    </div>
                                </td>

                                {{-- <td class="text-center"><span class="badge badge-success">COMPLETE</span></td> --}}
                            @elseif ($actualKanban == 0)
                                <td class="text-center">
                                    <div class="text-small float-right font-weight-bold text-muted ml-3">
                                        {{ $actualKanban }}/{{ $totalKanban }}</div>
                                    <div class="font-weight-bold mb-1" style="color: white">-</div>
                                    <div class="progress" data-height="20" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar" data-width="{{ $progressPercentage }}"
                                            aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                            style="width: 100%; background-color: red !important">
                                        </div>
                                    </div>
                                </td>
                                {{-- <td class="text-center"><span class="badge badge-danger">NOT STARTED</span></td> --}}
                            @elseif ($actualKanban < $totalKanban)
                                @if ($progressPercentage <= 50)
                                    <td class="text-center">
                                        <div class="text-small float-right font-weight-bold text-muted ml-3">
                                            {{ $actualKanban }}/{{ $totalKanban }}</div>
                                        <div class="font-weight-bold mb-1" style="color: white">-</div>
                                        <div class="progress" data-height="20" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar"
                                                data-width="{{ $progressPercentage }}" aria-valuenow="100"
                                                aria-valuemin="0" aria-valuemax="100"
                                                style="width: 100%; background-color: red !important">
                                            </div>
                                        </div>
                                    </td>
                                @elseif($progressPercentage > 50)
                                    <td class="text-center">
                                        <div class="text-small text-small float-right font-weight-bold text-muted ml-3">
                                            {{ $actualKanban }}/{{ $totalKanban }}</div>
                                        <div class="font-weight-bold mb-1" style="color: white">-</div>
                                        <div class="progress" data-height="20" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar"
                                                data-width="{{ $progressPercentage }}" aria-valuenow="100"
                                                aria-valuemin="0" aria-valuemax="100"
                                                style="width: 100%; background-color: yellow !important">
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                {{-- <td class="text-center"><span class="badge badge-warning">ON PROGRESS</span></td> --}}
                            @endif
                            <td class="text-center">
                                <a href="/loading-list/{{ $loadingList->id }}" class="btn btn-info text-white">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    DETAIL
                                </a>
                                @if ($actualKanban >= $totalKanban)
                                    <button class="btn btn-success">
                                        <i class="fas fa-solid fa-check" style="padding-right: 1px"></i>
                                        COMPLETE
                                    </button>
                                @elseif ($actualKanban < $totalKanban && $actualKanban > 0)
                                    <button class="btn btn-outline-warning">
                                        INPROGRESS
                                    </button>
                                @elseif ($actualKanban == 0)
                                    <button class="btn btn-outline-danger">
                                        INCOMPLETE
                                    </button>
                                @endif
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
            paging: false,
            columnDefs: [{
                targets: [5],
                orderable: false
            }]
        });

        let table = $('#loadingList').DataTable();

        $('#customer').on('change', function() {
            // get all filter values
            let customer = $('#customer').val();

            if (customer) {
                table.column(2).search(customer);
            } else {
                table.column(2).search('');
            }

            table.draw();
        })

        $('#manifest').on('change', function() {
            // get all filter values
            let manifest = $('#manifest').val();

            if (manifest) {
                table.column(1).search(manifest);
            } else {
                table.column(1).search('');
            }

            table.draw();
        })

        $('#cycle').on('change', function() {
            // get all filter values
            let cycle = $('#cycle').val();

            if (cycle) {
                table.column(3).search(cycle);
            } else {
                table.column(3).search('');
            }

            table.draw();
        })

        $('#date').on('change', function() {
            // get all filter values
            let date = $('#date').val();

            if (date) {
                table.column(4).search(date);
            } else {
                table.column(4).search('');
            }

            table.draw();
        })

        $('#reset').on('click', function() {
            $('#cycle').val('-- Select cycle --').trigger(
                'change'); // Reset the filter and trigger change event
            $('#customer').val('-- Select customer --').trigger(
                'change'); // Reset the filter and trigger change event
            $('#manifest').val('-- Select manifest --').trigger(
                'change'); // Reset the filter and trigger change event
            $('#date').val('').trigger(
                'change'); // Reset the filter and trigger change event
        });
    });
</script>
