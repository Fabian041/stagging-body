<div class="modal-header">
    <h5 class="modal-title">Detail Pick List: {{ $pickList }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>No</th>
                    <th>Part Number</th>
                    <th>Back Number</th>
                    <th class="text-right">Qty Ordered</th>
                    <th class="text-right">Qty Confirmed</th>
                    <th>UOM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->part_number }}</td>
                        <td>{{ $row->back_number }}</td>
                        <td class="text-right">{{ number_format($row->qty_ordered) }}</td>
                        <td class="text-right">{{ number_format($row->qty_confirmed) }}</td>
                        <td>{{ $row->uom }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data part untuk pick list ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
</div>
