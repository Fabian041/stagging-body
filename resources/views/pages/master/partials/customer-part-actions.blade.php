<div class="d-flex justify-content-end" style="gap: 6px;">
    <a href="{{ route('dashboard.master.customer-parts.index', ['edit' => $customerPart->id]) }}"
        class="btn btn-sm btn-outline-primary">Edit</a>
    <form method="POST" action="{{ route('dashboard.master.customer-parts.destroy', $customerPart) }}"
        onsubmit="return confirm('Hapus mapping ini?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger">Delete</button>
    </form>
</div>
