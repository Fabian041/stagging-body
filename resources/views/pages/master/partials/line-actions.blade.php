<div class="master-actions flex-nowrap">
    <a href="{{ route('dashboard.master.lines.index', ['edit' => $line->id]) }}" class="master-btn info">
        <i class="fas fa-edit"></i> Edit
    </a>
    <form method="POST" action="{{ route('dashboard.master.lines.toggle', $line) }}" class="m-0"
        onsubmit="return confirm('{{ $line->is_active ? 'Nonaktifkan line ini? Part dan mesin yang sudah terhubung tetap tersimpan.' : 'Aktifkan kembali line ini?' }}')">
        @csrf
        @method('PATCH')
        <button type="submit" class="master-btn {{ $line->is_active ? 'danger' : 'success' }}">
            <i class="fas fa-power-off"></i> {{ $line->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>
</div>
