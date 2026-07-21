<div class="d-flex justify-content-end" style="gap: 6px;">
    <a href="{{ route('dashboard.master.parts.index', ['edit' => $part->id]) }}"
        class="btn btn-sm btn-outline-primary">Edit</a>
    <form method="POST" action="{{ route('dashboard.master.parts.destroy', $part) }}"
        onsubmit="return confirm('Hapus part ini?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger">Delete</button>
    </form>
</div>
