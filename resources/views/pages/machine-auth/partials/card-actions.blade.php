<div class="ma-table-actions">
    <a class="ma-action-btn" href="{{ route('machine-auth.index', ['tab' => 'cards', 'edit_card' => $card->id]) }}">
        <i class="fas fa-edit"></i> Edit
    </a>
    <form method="POST" action="{{ route('machine-auth.cards.toggle', $card) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="ma-action-btn {{ $card->is_active ? 'is-warning' : 'is-success' }}">
            <i class="fas fa-power-off"></i> {{ $card->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>
</div>
