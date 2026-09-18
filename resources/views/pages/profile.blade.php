@extends('layouts.root.main')

@section('main')
@php
    $name = trim($user->name ?? 'User');
    $parts = array_values(array_filter(explode(' ', $name)));
    $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
    $role = strtoupper($user->role ?? 'USER');
@endphp

<style>
    .profile-page {
        display: grid;
        gap: 18px;
    }

    .profile-hero {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 22px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--r);
        box-shadow: var(--shadow);
    }

    .profile-avatar {
        width: 76px;
        height: 76px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--navy), var(--primary) 62%, var(--sky));
        color: #fff;
        font-size: 24px;
        font-weight: 850;
        box-shadow: 0 14px 28px rgba(0, 112, 183, .18);
        flex-shrink: 0;
    }

    .profile-title {
        margin: 0;
        color: var(--text);
        font-size: 24px;
        font-weight: 850;
        line-height: 1.2;
    }

    .profile-subtitle {
        margin-top: 5px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
    }

    .profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .profile-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 30px;
        padding: 6px 10px;
        border-radius: 7px;
        background: var(--primary-light);
        color: var(--navy);
        font-weight: 750;
        font-size: 12px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
        gap: 18px;
    }

    .profile-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--r);
        box-shadow: var(--shadow);
        padding: 20px;
    }

    .profile-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #eef2f7;
    }

    .profile-card-title {
        margin: 0;
        color: var(--text);
        font-size: 16px;
        font-weight: 850;
    }

    .profile-card-caption {
        margin-top: 4px;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .profile-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .profile-field.is-full {
        grid-column: 1 / -1;
    }

    .profile-label {
        display: block;
        margin-bottom: 7px;
        color: #51617a;
        font-size: 12px;
        font-weight: 800;
    }

    .profile-input {
        width: 100%;
        height: 42px;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 0 12px;
        color: var(--text);
        font-size: 13px;
        font-weight: 650;
        outline: none;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .profile-input:focus {
        border-color: rgba(0, 112, 183, .42);
        box-shadow: 0 0 0 4px rgba(0, 151, 216, .10);
    }

    .profile-input.is-invalid {
        border-color: rgba(220, 38, 38, .55);
    }

    .profile-error {
        margin-top: 6px;
        color: var(--danger);
        font-size: 11.5px;
        font-weight: 700;
    }

    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .profile-btn {
        min-height: 40px;
        border: 0;
        border-radius: 7px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: background .16s ease, transform .16s ease;
    }

    .profile-btn:hover {
        background: #005f9d;
        color: #fff;
        transform: translateY(-1px);
    }

    .profile-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .profile-alert.success {
        color: #166534;
        background: var(--success-light);
        border-color: rgba(22, 163, 74, .2);
    }

    .profile-alert.danger {
        color: #991b1b;
        background: var(--danger-light);
        border-color: rgba(220, 38, 38, .18);
    }

    @media (max-width: 992px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .profile-hero {
            align-items: flex-start;
            padding: 18px;
        }

        .profile-avatar {
            width: 58px;
            height: 58px;
            border-radius: 10px;
            font-size: 19px;
        }

        .profile-title {
            font-size: 20px;
        }

        .profile-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-page">
    @if (session('profile_success'))
        <div class="profile-alert success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('profile_success') }}</span>
        </div>
    @endif

    @if (session('password_success'))
        <div class="profile-alert success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('password_success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="profile-alert danger">
            <i class="fas fa-exclamation-circle"></i>
            <span>Periksa kembali data yang diisi. Ada field yang belum sesuai.</span>
        </div>
    @endif

    <section class="profile-hero">
        <div class="profile-avatar">{{ $initials }}</div>
        <div>
            <h1 class="profile-title">{{ $name }}</h1>
            <div class="profile-subtitle">{{ $user->email }} - NPK {{ $user->npk }}</div>
            <div class="profile-meta">
                <span class="profile-chip"><i class="fas fa-user-shield"></i>{{ $role }}</span>
                @if (!empty($user->department))
                    <span class="profile-chip"><i class="fas fa-building"></i>{{ ucwords(str_replace('-', ' ', $user->department)) }}</span>
                @endif
            </div>
        </div>
    </section>

    <div class="profile-grid">
        <section class="profile-card">
            <div class="profile-card-head">
                <div>
                    <h2 class="profile-card-title">Informasi Profile</h2>
                    <div class="profile-card-caption">Data dasar untuk akun yang sedang login.</div>
                </div>
                <i class="far fa-user"></i>
            </div>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="profile-form-grid">
                    <div class="profile-field is-full">
                        <label class="profile-label" for="name">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                            class="profile-input @error('name') is-invalid @enderror" autocomplete="name">
                        @error('name')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label class="profile-label" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                            class="profile-input @error('email') is-invalid @enderror" autocomplete="email">
                        @error('email')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label class="profile-label" for="npk">Username / NPK</label>
                        <input id="npk" name="npk" type="text" value="{{ old('npk', $user->npk) }}"
                            class="profile-input @error('npk') is-invalid @enderror" inputmode="numeric" maxlength="6"
                            autocomplete="username">
                        @error('npk')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="profile-actions">
                    <button type="submit" class="profile-btn">
                        <i class="fas fa-save"></i>
                        Simpan Profile
                    </button>
                </div>
            </form>
        </section>

        <section class="profile-card">
            <div class="profile-card-head">
                <div>
                    <h2 class="profile-card-title">Ubah Password</h2>
                    <div class="profile-card-caption">Gunakan password lama untuk konfirmasi.</div>
                </div>
                <i class="fas fa-lock"></i>
            </div>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="profile-form-grid">
                    <div class="profile-field is-full">
                        <label class="profile-label" for="current_password">Password Lama</label>
                        <input id="current_password" name="current_password" type="password"
                            class="profile-input @error('current_password') is-invalid @enderror"
                            autocomplete="current-password">
                        @error('current_password')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="profile-field is-full">
                        <label class="profile-label" for="password">Password Baru</label>
                        <input id="password" name="password" type="password"
                            class="profile-input @error('password') is-invalid @enderror"
                            autocomplete="new-password">
                        @error('password')
                            <div class="profile-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="profile-field is-full">
                        <label class="profile-label" for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="profile-input" autocomplete="new-password">
                    </div>
                </div>

                <div class="profile-actions">
                    <button type="submit" class="profile-btn">
                        <i class="fas fa-key"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
