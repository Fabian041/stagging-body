<div class="ma-table-actions">
    <a class="ma-action-btn" href="{{ route('machine-auth.index', ['tab' => 'permissions', 'edit_permission' => $permission->id]) }}">
        <i class="fas fa-edit"></i> Edit
    </a>
    <form method="POST" action="{{ route('machine-auth.permissions.destroy', $permission) }}"
        onsubmit="return confirm('Hapus permission ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="ma-action-btn is-danger">
            <i class="fas fa-trash"></i> Hapus
        </button>
    </form>
</div>
