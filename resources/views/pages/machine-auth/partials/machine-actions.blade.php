<div class="ma-table-actions">
    <a class="ma-action-btn" href="{{ route('machine-auth.index', ['tab' => 'machines', 'edit_machine' => $machine->id]) }}">
        <i class="fas fa-edit"></i> Edit / Token
    </a>
    <form method="POST" action="{{ route('machine-auth.machines.toggle', $machine) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="ma-action-btn {{ $machine->is_active ? 'is-warning' : 'is-success' }}">
            <i class="fas fa-power-off"></i> {{ $machine->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>
</div>
