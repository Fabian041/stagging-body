@extends('layouts.root.main')

@section('main')
    <div class="card card-info mt-3 shadow" style="border-radius:10px">
        <div class="card-body">
            <h4 class="card-title mt-3 mb-3 text-dark text-center">DELIVERY MONITORING</h4>
            <table class="table table-responsive-lg" id="loadingList">
                <thead>
                    <tr>
                        <th class="text-center">Loading List Number</th>
                        <th class="text-center">PDS Number</th>
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
                            <td class="text-center">{{ $loadingList->cycle }}</td>
                            <td class="text-center">{{ $loadingList->delivery_date }}</td>
                            @if ($actualKanban >= $totalKanban)
                                <td class="text-center">
                                    <div class="text-small float-right font-weight-bold text-muted ml-3">
                                        {{ $actualKanban }}/{{ $totalKanban }}</div>
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
                                    DETAIL</a>
                                {{-- @if ($actualKanban >= $totalKanban)
                                    <button class="btn btn-success text-white">
                                        <i class="fas fa-solid fa-check mr-2"></i>
                                        FINISH</button>
                                @else
                                    <button class="btn btn-success text-white"
                                        style="background-color: white !important; box-shadow: none !important; border-color:white !important">
                                        <i class="fas fa-solid fa-check mr-2"></i>
                                        FINISH</button>
                                @endif --}}
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
                targets: [6],
                orderable: false
            }]
        });
    });
</script>
