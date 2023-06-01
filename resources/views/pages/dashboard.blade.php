@extends('layouts.root.main')

@section('main')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow" style="border-radius:8px">
                <div class="row">
                    <div class="col">
                        <div class="card-header">
                            <h4>Delivery Monitoring</h4>
                        </div>
                    </div>
                    <div class="col mt-3 text-right">
                        <div class="col-md-12">
                            {{-- <button class="btn btn-lg btn-danger" data-toggle="modal" data-target="#partModal">Upload
                                Part</button> --}}
                            <button class="btn btn-lg btn-danger" data-toggle="modal" data-target="#manifestModal">Upload
                                Manifest</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="table-1">
                            <thead>
                                <tr>
                                    <th>PDS Number</th>
                                    <th>Customer</th>
                                    <th>Cycle</th>
                                    <th>Delivery Data</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="data">
                                    <td colspan="6" id="nullData">
                                        <h5 class="text-center mt-4" id="splash">Upload Manifest First</h5>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- modal --}}
<div class="modal fade" tabindex="-1" role="dialog" id="partModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.part.import') }}" method="POST" enctype="multipart/form-data">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Part</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="customFile" name="file">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}

{{-- modal --}}
<div class="modal fade" tabindex="-1" role="dialog" id="manifestModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('dashboard.manifest.import') }}" method="POST" enctype="multipart/form-data">
                @method('POST')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Manifest</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body mt-3">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="customFile" name="file">
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- end of modal --}}
