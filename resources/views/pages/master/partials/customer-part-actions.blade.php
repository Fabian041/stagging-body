<div class="d-flex justify-content-end" style="gap: 6px;">
    <a href="{{ route('dashboard.master.customer-parts.index', ['edit' => $customerPart->id]) }}"
        class="master-btn"><i class="fas fa-pen"></i> Edit</a>
    <form method="POST" action="{{ route('dashboard.master.customer-parts.destroy', $customerPart) }}"
        onsubmit="return confirm('Hapus mapping ini?')">
        @csrf
        @method('DELETE')
        <button class="master-btn danger"><i class="fas fa-trash"></i> Delete</button>
    </form>
</div>
