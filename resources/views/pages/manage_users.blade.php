@extends('layouts.root.main')

@section('main')
<style>
    .mu-page {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 16px;
        min-width: 0;
    }

    .mu-head,
    .mu-panel {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--r);
        box-shadow: var(--shadow);
    }

    .mu-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 20px;
    }

    .mu-title {
        margin: 0;
        color: var(--text);
        font-size: 24px;
        font-weight: 850;
    }

    .mu-subtitle {
        margin-top: 5px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
    }

    .mu-actions,
    .mu-filter,
    .mu-form-grid,
    .mu-upload-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .mu-panel {
        padding: 18px;
    }

    .mu-panel-title {
        margin: 0 0 12px;
        color: var(--text);
        font-size: 15px;
        font-weight: 850;
    }

    .mu-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
        gap: 16px;
    }

    .mu-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mu-field {
        min-width: 0;
    }

    .mu-field.is-full {
        grid-column: 1 / -1;
    }

    .mu-label {
        display: block;
        margin-bottom: 6px;
        color: var(--text);
        font-size: 12px;
        font-weight: 850;
    }

    .mu-input,
    .mu-select {
        width: 100%;
        height: 40px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: #fff;
        color: var(--text);
        padding: 0 11px;
        font-size: 13px;
        font-weight: 700;
        outline: none;
    }

    .mu-input:focus,
    .mu-select:focus {
        border-color: rgba(0, 112, 183, .45);
        box-shadow: 0 0 0 4px rgba(0, 112, 183, .09);
    }

    .mu-btn {
        min-height: 38px;
        border: 0;
        border-radius: 7px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        background: var(--primary);
        color: #fff;
        font-size: 12.5px;
        font-weight: 850;
        cursor: pointer;
        text-decoration: none !important;
    }

    .mu-btn.is-muted {
        background: #EEF2F7;
        color: #51617a;
    }

    .mu-btn.is-danger {
        background: var(--danger-light);
        color: var(--danger);
    }

    .mu-table-wrap {
        overflow: auto;
        border: 1px solid var(--border);
        border-radius: 10px;
    }

    .mu-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .mu-table th,
    .mu-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--border);
        text-align: left;
        vertical-align: middle;
        white-space: nowrap;
    }

    .mu-table th {
        background: #F8FAFC;
        color: var(--text-muted);
        font-size: 10.5px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .mu-table tr:last-child td {
        border-bottom: 0;
    }

    .mu-user-main {
        color: var(--text);
        font-weight: 850;
    }

    .mu-user-sub {
        margin-top: 2px;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 650;
    }

    .mu-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 8px;
        background: var(--primary-light);
        color: var(--primary);
        font-size: 10.5px;
        font-weight: 850;
        text-transform: uppercase;
    }

    .mu-badge.is-active {
        background: var(--success-light);
        color: #15803d;
    }

    .mu-inline-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .mu-alert {
        display: flex;
        gap: 9px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 750;
    }

    .mu-alert.ok {
        color: #166534;
        background: var(--success-light);
        border: 1px solid rgba(22, 163, 74, .2);
    }

    .mu-alert.bad {
        color: #991b1b;
        background: var(--danger-light);
        border: 1px solid rgba(220, 38, 38, .2);
    }

    .mu-error {
        margin-top: 5px;
        color: var(--danger);
        font-size: 11px;
        font-weight: 750;
    }

    .mu-import-errors {
        max-height: 180px;
        overflow: auto;
        margin: 10px 0 0;
        padding: 10px 12px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid rgba(220, 38, 38, .18);
        color: #991b1b;
        font-size: 12px;
        font-weight: 700;
    }

    .mu-empty {
        padding: 28px 12px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .mu-delete-modal {
        position: fixed;
        inset: 0;
        z-index: 7200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .mu-delete-modal.open {
        display: flex;
    }

    .mu-delete-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .32);
        backdrop-filter: blur(10px);
    }

    .mu-delete-card {
        position: relative;
        width: min(410px, 100%);
        padding: 18px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .94);
        border: 1px solid rgba(255, 255, 255, .72);
        box-shadow: 0 28px 80px rgba(15, 23, 42, .24);
    }

    .mu-delete-title {
        margin: 0 0 6px;
        color: var(--text);
        font-size: 17px;
        font-weight: 850;
    }

    .mu-delete-copy {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
        line-height: 1.5;
    }

    .mu-delete-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 16px;
    }

    @media (max-width: 992px) {
        .mu-grid,
        .mu-form-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 640px) {
        .mu-head {
            flex-direction: column;
            align-items: stretch;
            padding: 16px;
        }

        .mu-title {
            font-size: 20px;
        }

        .mu-head .mu-actions .mu-btn {
            width: 100%;
        }

        .mu-panel {
            padding: 14px;
        }

        .mu-filter,
        .mu-upload-row {
            flex-direction: column;
            align-items: stretch;
        }

        .mu-filter .mu-input,
        .mu-filter .mu-select,
        .mu-upload-row .mu-input {
            max-width: none !important;
            width: 100%;
        }

        .mu-filter .mu-btn,
        .mu-upload-row .mu-btn {
            width: 100%;
        }
    }
</style>

<div class="mu-page">
    @if (session('manage_user_success'))
        <div class="mu-alert ok">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('manage_user_success') }}</span>
        </div>
    @endif

    @if (session('manage_user_error'))
        <div class="mu-alert bad">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('manage_user_error') }}</span>
        </div>
    @endif

    @if ($result = session('user_import_result'))
        @if (!empty($result['errors']))
            <div class="mu-alert bad">
                <i class="fas fa-file-excel"></i>
                <div>
                    <div>Detail error bulk upload</div>
                    <div class="mu-import-errors">
                        @foreach ($result['errors'] as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    <section class="mu-head">
        <div>
            <h1 class="mu-title">Manage User</h1>
            <div class="mu-subtitle">Kelola akun login dan role akses operator BELLA.</div>
        </div>
        <div class="mu-actions">
            <a href="{{ route('manage-users.template') }}" class="mu-btn is-muted">
                <i class="fas fa-download"></i>
                Template
            </a>
        </div>
    </section>

    <div class="mu-grid">
        <section class="mu-panel">
            <h2 class="mu-panel-title">{{ $editUser ? 'Edit User' : 'Create User' }}</h2>
            <form action="{{ $editUser ? route('manage-users.update', $editUser) : route('manage-users.store') }}" method="POST">
                @csrf
                @if ($editUser)
                    @method('PUT')
                @endif

                <div class="mu-form-grid">
                    <div class="mu-field">
                        <label class="mu-label" for="name">Nama</label>
                        <input class="mu-input" id="name" name="name" value="{{ old('name', $editUser->name ?? '') }}" required>
                        @error('name') <div class="mu-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="mu-field">
                        <label class="mu-label" for="npk">Username / NPK</label>
                        <input class="mu-input" id="npk" name="npk" value="{{ old('npk', $editUser->npk ?? '') }}" maxlength="6" required>
                        @error('npk') <div class="mu-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="mu-field">
                        <label class="mu-label" for="email">Email</label>
                        <input class="mu-input" id="email" name="email" type="email" value="{{ old('email', $editUser->email ?? '') }}" required>
                        @error('email') <div class="mu-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="mu-field">
                        <label class="mu-label" for="role">Role</label>
                        <select class="mu-select" id="role" name="role" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $editUser->role ?? 'prod') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('role') <div class="mu-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="mu-field">
                        <label class="mu-label" for="password">Password{{ $editUser ? ' baru' : '' }}</label>
                        <input class="mu-input" id="password" name="password" type="password" {{ $editUser ? '' : 'required' }}>
                        @error('password') <div class="mu-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="mu-field">
                        <label class="mu-label" for="password_confirmation">Konfirmasi Password</label>
                        <input class="mu-input" id="password_confirmation" name="password_confirmation" type="password" {{ $editUser ? '' : 'required' }}>
                    </div>
                </div>

                <div class="mu-actions" style="margin-top:14px;">
                    @if ($editUser)
                        <a href="{{ route('manage-users.index') }}" class="mu-btn is-muted">Batal</a>
                    @endif
                    <button class="mu-btn" type="submit">
                        <i class="fas fa-save"></i>
                        {{ $editUser ? 'Simpan Perubahan' : 'Buat User' }}
                    </button>
                </div>
            </form>
        </section>

        <section class="mu-panel">
            <h2 class="mu-panel-title">Bulk Upload</h2>
            <form action="{{ route('manage-users.bulk-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mu-upload-row">
                    <input class="mu-input" type="file" name="file" accept=".xlsx,.xls,.csv" required>
                    <button class="mu-btn" type="submit">
                        <i class="fas fa-upload"></i>
                        Upload
                    </button>
                </div>
                @error('file') <div class="mu-error">{{ $message }}</div> @enderror
            </form>
        </section>
    </div>

    <section class="mu-panel">
        <form class="mu-filter" action="{{ route('manage-users.index') }}" method="GET">
            <input class="mu-input" style="max-width:260px;" name="q" value="{{ request('q') }}" placeholder="Cari nama / email / username">
            <select class="mu-select" style="max-width:180px;" name="role">
                <option value="">Semua role</option>
                @foreach ($roleFilterOptions as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
            <button class="mu-btn is-muted" type="submit">
                <i class="fas fa-search"></i>
                Filter
            </button>
        </form>

        <div class="mu-table-wrap" style="margin-top:14px;">
            <table class="mu-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email / Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="mu-user-main">{{ $user->name }}</div>
                                <div class="mu-user-sub">ID #{{ $user->id }}</div>
                            </td>
                            <td>
                                <div class="mu-user-main">{{ $user->email }}</div>
                                <div class="mu-user-sub">{{ $user->npk }}</div>
                            </td>
                            <td><span class="mu-badge">{{ $user->role }}</span></td>
                            <td><span class="mu-badge is-active">Aktif</span></td>
                            <td>{{ optional($user->created_at)->format('d M Y') }}</td>
                            <td>
                                @if ($user->isSuperAdmin())
                                    <span class="mu-user-sub">Protected</span>
                                @else
                                    <div class="mu-inline-actions">
                                        <a class="mu-btn is-muted" href="{{ route('manage-users.index', array_merge(request()->query(), ['edit' => $user->id])) }}">
                                            <i class="fas fa-pen"></i>
                                            Edit
                                        </a>
                                        <button class="mu-btn is-danger" type="button" data-delete-user="{{ $user->name }}" data-delete-form="deleteUser{{ $user->id }}">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                        <form id="deleteUser{{ $user->id }}" action="{{ route('manage-users.destroy', $user) }}" method="POST" hidden>
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="mu-empty">Belum ada user yang cocok.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">
            {{ $users->links() }}
        </div>
    </section>
</div>

<div class="mu-delete-modal" id="muDeleteModal" aria-hidden="true">
    <div class="mu-delete-backdrop" data-delete-close></div>
    <div class="mu-delete-card" role="dialog" aria-modal="true" aria-labelledby="muDeleteTitle">
        <h2 class="mu-delete-title" id="muDeleteTitle">Hapus user?</h2>
        <div class="mu-delete-copy">User <strong id="muDeleteName">ini</strong> akan dihapus dari sistem.</div>
        <div class="mu-delete-actions">
            <button type="button" class="mu-btn is-muted" data-delete-close>Batal</button>
            <button type="button" class="mu-btn is-danger" id="muDeleteSubmit">Hapus</button>
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('muDeleteModal');
        var submit = document.getElementById('muDeleteSubmit');
        var name = document.getElementById('muDeleteName');
        var form = null;
        if (!modal || !submit) return;

        document.querySelectorAll('[data-delete-form]').forEach(function(button) {
            button.addEventListener('click', function() {
                form = document.getElementById(button.getAttribute('data-delete-form'));
                if (name) name.textContent = button.getAttribute('data-delete-user') || 'ini';
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        modal.addEventListener('click', function(e) {
            if (!e.target.closest('[data-delete-close]')) return;
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            form = null;
        });

        submit.addEventListener('click', function() {
            if (!form) return;
            submit.disabled = true;
            form.submit();
        });
    })();
</script>
@endsection
