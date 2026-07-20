<div class="d-flex justify-content-end flex-wrap" style="gap: 6px;">
    <button type="button" class="btn btn-sm btn-outline-info view-mold-qr"
        data-toggle="modal" data-target="#moldQrModal"
        data-tag="{{ $mold->tag }}" data-name="{{ $mold->name }}"
        title="View QR Code">
        <i class="fas fa-qrcode mr-1"></i> QR Code
    </button>
    <a href="{{ route('dashboard.master.molds.index', ['edit' => $mold->id]) }}"
        class="btn btn-sm btn-outline-primary">Edit</a>
    <form method="POST" action="{{ route('dashboard.master.molds.destroy', $mold) }}"
        onsubmit="return confirm('Hapus mold ini?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger">Delete</button>
    </form>
</div>
