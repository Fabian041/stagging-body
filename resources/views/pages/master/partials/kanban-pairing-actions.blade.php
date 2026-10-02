<div class="d-flex justify-content-end" style="gap: 6px;">
    <a href="{{ route('dashboard.master.kanban-pairings.index', ['edit' => $kanbanPairing->id]) }}"
        class="master-btn"><i class="fas fa-pen"></i> Edit</a>
    <form method="POST" action="{{ route('dashboard.master.kanban-pairings.destroy', $kanbanPairing) }}"
        onsubmit="return confirm('Hapus master pairing ini?')">
        @csrf
        @method('DELETE')
        <button class="master-btn danger"><i class="fas fa-trash"></i> Delete</button>
    </form>
</div>
