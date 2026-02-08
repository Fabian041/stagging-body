@extends('layouts.root.main')

@section('main')
    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header justify-content-center mt-3">
                    <h3>Production Result</h3>
                </div>
                <div class="card-body">
                    {{-- Nav Tabs --}}
                    <ul class="nav nav-pills justify-content-center mb-4" id="myTab3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#all" role="tab">All</a>
                        </li>
                        @foreach ($lines as $line)
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#{{ $line->line }}" role="tab">
                                    {{ $line->line }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <button class="btn btn-lg btn-success" data-toggle="modal" data-target="#exportMutationModal">
                        Export Production Result
                    </button>

                    {{-- Filter Tanggal --}}
                    <form method="GET" action="{{ route('dashboard.prodResult') }}" class="mb-3 mt-5">
                        <div class="form-row">
                            <div class="col-md-3">
                                <input type="date" id="date" name="date" class="form-control"
                                    value="{{ $selectedDate ?? \Carbon\Carbon::now()->toDateString() }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div class="tab-content" id="myTabContent2">

                        {{-- Tab ALL --}}
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-md">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Line</th>
                                            <th>Back Number</th>
                                            <th>Last Scan Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $rowIndex = 1; @endphp
                                        @foreach ($lines as $line)
                                            @foreach ($line->items as $item)
                                                <tr>
                                                    <td class="text-center">{{ $rowIndex++ }}</td>
                                                    <td>{{ $line->line }}</td>
                                                    <td>
                                                        <h3><code>{{ $item['back_number'] }}</code></h3>
                                                    </td>
                                                    <td>{{ $item['details'][0]['date'] ?? '-' }}</td>
                                                    <td class="text-center">
                                                        <button class="btn btn-info" data-toggle="collapse"
                                                            data-target="#collapse-all-{{ $line->line }}-{{ $loop->iteration }}">
                                                            Detail
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="p-0">
                                                        <div id="collapse-all-{{ $line->line }}-{{ $loop->iteration }}"
                                                            class="collapse">
                                                            <div class="p-3">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered small">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Serial Number</th>
                                                                                <th>Qty</th>
                                                                                <th>Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($item['details'] as $detail)
                                                                                <tr>
                                                                                    <td>{{ $detail['serial_number'] }}</td>
                                                                                    <td>{{ $detail['qty'] }}</td>
                                                                                    <td>{{ $detail['date'] }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $lines->links() }}
                            </div>
                        </div>

                        {{-- Tab per Line --}}
                        @foreach ($lines as $line)
                            <div class="tab-pane fade" id="{{ $line->line }}" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-md">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Back Number</th>
                                                <th>Last Scan Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($line->items as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <h3><code>{{ $item['back_number'] }}</code></h3>
                                                    </td>
                                                    <td>{{ $item['details'][0]['date'] ?? '-' }}</td>
                                                    <td class="text-center">
                                                        <button class="btn btn-info" data-toggle="collapse"
                                                            data-target="#collapse-{{ $line->line }}-line-{{ $loop->iteration }}">
                                                            Detail
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="p-0">
                                                        <div id="collapse-{{ $line->line }}-line-{{ $loop->iteration }}"
                                                            class="collapse">
                                                            <div class="p-3">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered small">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Serial Number</th>
                                                                                <th>Qty</th>
                                                                                <th>Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($item['details'] as $detail)
                                                                                <tr>
                                                                                    <td>{{ $detail['serial_number'] }}</td>
                                                                                    <td>{{ $detail['qty'] }}</td>
                                                                                    <td>{{ $detail['date'] }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>

    
@endsection
<div class="modal fade" tabindex="-1" role="dialog" id="exportMutationModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.mutation.export') }}" method="GET">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Mutasi (Filter Tanggal)</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body mt-3">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" class="form-control" name="from"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" class="form-control" name="to"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Download Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{{-- mqtt --}}
<script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('assets/js/apexcharts.js') }}"></script>
<script src={{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.js') }}></script>
<script>
    var errorMessege = "{!! session('error') !!}";
    var successMessege = "{!! session('success') !!}";

    $(document).ready(function() {
        if (errorMessege) {
            iziToast.error({
                title: 'Error! ' + errorMessege,
                position: 'bottomRight'
            });
        } else if (successMessege) {
            iziToast.success({
                title: 'Success! ' + successMessege,
                position: 'bottomRight'
            });
        }

        $('.date-filter').on('change', function() {
            const selectedDate = $(this).val();
            const targetSelector = $(this).data('target');
            const tables = $(targetSelector);

            if (!selectedDate) {
                tables.find('tbody tr').show();
                return;
            }

            tables.each(function() {
                $(this).find('tbody tr').each(function() {
                    const rowDate = $(this).data('date');
                    if (rowDate === selectedDate) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    });
</script>
