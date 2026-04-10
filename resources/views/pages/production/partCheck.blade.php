@extends('layouts.root.main')

@section('main')
    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card card-danger">
                <div class="card-header justify-content-center">
                    <h3 class="p-4">Check Kanban Status</h3>
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

                    <form method="POST" action="{{ route('dashboard.kbnCheckSubmit') }}">
                        @csrf
                        <div class="form-row align-items-center">
                            <div class="col-md-5 mb-2">
                                <input type="text" name="back_number" class="form-control" placeholder="Back Number"
                                    value="{{ old('back_number', $back_number ?? '') }}" required>
                            </div>
                            <div class="col-md-5 mb-2">
                                <input type="text" name="serial_number" class="form-control"
                                    placeholder="Serial Number (4 digit)"
                                    value="{{ old('serial_number', $serial_number ?? '') }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary btn-block">Check</button>
                            </div>
                        </div>
                    </form>

                    {{-- Display Kanban Result --}}
                    @if (isset($internalPart) && isset($kanban))
                        <hr>
                        <h5>Kanban Detail</h5>
                        <table class="table table-bordered mt-3">
                            <tr>
                                <th>Back Number</th>
                                <td>{{ $internalPart->back_number }}</td>
                            </tr>
                            <tr>
                                <th>Part Name</th>
                                <td>{{ $internalPart->part_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Line</th>
                                <td>{{ $internalPart->line->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Last Scan</th>
                                <td>{{ $kanban->updated_at ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @switch($kanban->status)
                                        @case(0)
                                            <span class="badge badge-secondary">Belum Pernah di-Scan</span>
                                        @break

                                        @case(1)
                                            <span class="badge badge-warning">Sudah Scan Produksi / Belum Scan PPIC</span>
                                        @break

                                        @case(2)
                                            <span class="badge badge-success">Sudah Scan PPIC</span>
                                        @break

                                        @default
                                            <span class="badge badge-dark">Tidak Diketahui</span>
                                    @endswitch
                                </td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
