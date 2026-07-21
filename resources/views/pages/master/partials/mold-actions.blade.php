<div class="d-flex justify-content-end flex-wrap" style="gap: 6px;">
    <button type="button" class="master-btn info view-mold-qr"
        data-toggle="modal" data-target="#moldQrModal"
        data-tag="{{ $mold->tag }}" data-name="{{ $mold->name }}"
        title="View QR Code">
        <i class="fas fa-qrcode mr-1"></i> QR Code
    </button>
    <a href="{{ route('dashboard.master.molds.index', ['edit' => $mold->id]) }}"
        class="master-btn"><i class="fas fa-pen"></i> Edit</a>
    <form method="POST" action="{{ route('dashboard.master.molds.destroy', $mold) }}"
        onsubmit="return confirm('Hapus mold ini?')">
        @csrf
        @method('DELETE')
        <button class="master-btn danger"><i class="fas fa-trash"></i> Delete</button>
    </form>
</div>
