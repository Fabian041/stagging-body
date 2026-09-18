@extends('layouts.root.main')

@section('main')
    <style>
        .ma-page { min-width: 0; }
        .ma-head, .ma-panel, .ma-stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--shadow);
        }
        .ma-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .ma-title { margin: 0; color: var(--text); font-size: 24px; font-weight: 850; }
        .ma-subtitle { margin-top: 5px; color: var(--text-muted); font-size: 13px; font-weight: 650; }
        .ma-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .ma-stat { min-width: 100px; padding: 12px 14px; }
        .ma-stat-label { color: var(--text-muted); font-size: 10px; font-weight: 850; text-transform: uppercase; }
        .ma-stat-value { margin-top: 3px; color: var(--text); font-size: 20px; font-weight: 900; }
        .ma-tabs {
            display: flex;
            gap: 7px;
            overflow-x: auto;
            padding: 7px;
            margin-bottom: 14px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 11px;
            box-shadow: var(--shadow);
        }
        .ma-tabs .nav-link {
            white-space: nowrap;
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 850;
        }
        .ma-tabs .nav-link.active { background: var(--primary); color: #fff; }
        .ma-grid { display: grid; grid-template-columns: minmax(300px, .36fr) minmax(0, .64fr); gap: 15px; }
        .ma-panel { min-width: 0; overflow: hidden; }
        .ma-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 15px 17px;
            border-bottom: 1px solid var(--border);
        }
        .ma-panel-title { margin: 0; color: var(--text); font-size: 15px; font-weight: 850; }
        .ma-panel-body { padding: 17px; }
        .ma-field { margin-bottom: 13px; }
        .ma-label { display: block; margin-bottom: 6px; color: var(--text); font-size: 12px; font-weight: 850; }
        .ma-input, .ma-select, .ma-textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
            color: var(--text);
            padding: 9px 11px;
            font-size: 13px;
            font-weight: 700;
            outline: none;
        }
        .ma-input, .ma-select { min-height: 40px; }
        .ma-textarea { min-height: 75px; resize: vertical; }
        .ma-input:focus, .ma-select:focus, .ma-textarea:focus {
            border-color: rgba(0, 112, 183, .45);
            box-shadow: 0 0 0 4px rgba(0, 112, 183, .09);
        }
        .ma-check { display: flex; align-items: center; gap: 8px; color: var(--text); font-size: 12px; font-weight: 800; }
        .ma-btn, .ma-action-btn {
            border: 0;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none !important;
            cursor: pointer;
            font-weight: 850;
        }
        .ma-btn { min-height: 40px; padding: 0 14px; background: var(--primary); color: #fff; font-size: 12.5px; }
        .ma-btn.is-muted { background: #eef2f7; color: #51617a; }
        .ma-btn.is-danger { background: var(--danger-light); color: var(--danger); }
        .ma-form-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .ma-form-actions .ma-btn { flex: 1 1 120px; }
        .ma-table-wrap { overflow: auto; }
        .ma-table { width: 100% !important; margin: 0 !important; font-size: 12px; }
        .ma-table th { background: #f8fafc; color: var(--text-muted); font-size: 10px; text-transform: uppercase; }
        .ma-table th, .ma-table td { padding: 10px 11px !important; vertical-align: middle !important; white-space: nowrap; }
        .ma-table-actions { display: inline-flex; align-items: center; justify-content: flex-end; gap: 5px; }
        .ma-table-actions form { margin: 0; }
        .ma-action-btn { min-height: 30px; padding: 0 9px; background: #eaf4ff; color: #0b6db3; font-size: 10.5px; }
        .ma-action-btn.is-warning { background: #fff5d8; color: #9a6700; }
        .ma-action-btn.is-success { background: #e7f8ed; color: #15803d; }
        .ma-action-btn.is-danger { background: #feecec; color: #c62828; }
        .ma-alert { margin-bottom: 14px; padding: 13px 15px; border-radius: 9px; font-size: 12.5px; font-weight: 750; }
        .ma-alert.ok { color: #166534; background: #eaf8ef; border: 1px solid #bde7ca; }
        .ma-alert.bad { color: #991b1b; background: #fff0f0; border: 1px solid #f5c7c7; }
        .ma-token-box { color: #174a74; background: #edf7ff; border: 1px solid #b9dcf5; }
        .ma-token-value { display: flex; gap: 8px; margin-top: 9px; }
        .ma-token-value textarea { min-height: 72px; font-family: monospace; font-size: 11px; word-break: break-all; }
        .ma-token-section { margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border); }
        .ma-token-list { margin: 10px 0 0; padding: 0; list-style: none; }
        .ma-token-list li { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 9px 0; border-bottom: 1px solid var(--border); }
        .ma-token-meta { min-width: 0; color: var(--text-muted); font-size: 10.5px; }
        .ma-token-name { color: var(--text); font-size: 12px; font-weight: 850; }
        .ma-filter { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)) auto; gap: 8px; padding: 13px 15px; border-bottom: 1px solid var(--border); }
        .ma-machine-filter {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 10px 15px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        .ma-machine-filter label { margin: 0; color: var(--text-muted); font-size: 11px; font-weight: 850; }
        .ma-machine-filter .ma-select { width: auto; min-width: 190px; }
        .ma-help { margin-top: 5px; color: var(--text-muted); font-size: 10.5px; line-height: 1.45; }
        .select2-container--default .select2-selection--single { min-height: 40px; border-color: var(--border); border-radius: 7px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; font-weight: 700; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
        @media (max-width: 1100px) {
            .ma-head { align-items: flex-start; flex-direction: column; }
            .ma-grid { grid-template-columns: minmax(0, 1fr); }
            .ma-stats { width: 100%; }
        }
        @media (max-width: 640px) {
            .ma-head { padding: 15px; }
            .ma-title { font-size: 20px; }
            .ma-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ma-stat { min-width: 0; padding: 10px; }
            .ma-panel-body { padding: 14px; }
            .ma-filter { grid-template-columns: minmax(0, 1fr); }
            .ma-machine-filter { align-items: stretch; flex-direction: column; }
            .ma-machine-filter .ma-select { width: 100%; min-width: 0; }
            .ma-token-value { flex-direction: column; }
            .ma-token-value .ma-btn { width: 100%; }
        }
    </style>

    <div class="ma-page">
        <div class="ma-head">
            <div>
                <h1 class="ma-title">Machine Authorization</h1>
                <div class="ma-subtitle">Kelola RFID operator, mesin, permission, token Raspberry Pi, dan audit akses.</div>
            </div>
            <div class="ma-stats">
                <div class="ma-stat"><div class="ma-stat-label">RFID</div><div class="ma-stat-value">{{ $counts['cards'] }}</div></div>
                <div class="ma-stat"><div class="ma-stat-label">Mesin</div><div class="ma-stat-value">{{ $counts['machines'] }}</div></div>
                <div class="ma-stat"><div class="ma-stat-label">Permission</div><div class="ma-stat-value">{{ $counts['permissions'] }}</div></div>
                <div class="ma-stat"><div class="ma-stat-label">Log Akses</div><div class="ma-stat-value">{{ $counts['logs'] }}</div></div>
            </div>
        </div>

        @if (session('success'))
            <div class="ma-alert ok"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="ma-alert bad">
                <strong>Data belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if (session('machine_plain_text_token'))
            <div class="ma-alert ma-token-box">
                <strong>Token {{ session('token_machine_code') }} — hanya ditampilkan sekali.</strong>
                <div class="ma-help">Salin token ini ke konfigurasi Raspberry Pi. BELLA hanya menyimpan hash token.</div>
                <div class="ma-token-value">
                    <textarea id="machinePlainToken" class="ma-textarea" readonly>{{ session('machine_plain_text_token') }}</textarea>
                    <button type="button" class="ma-btn" id="copyMachineToken"><i class="fas fa-copy"></i> Salin Token</button>
                </div>
            </div>
        @endif

        <nav class="ma-tabs nav" id="machineAuthTabs" role="tablist">
            <a class="nav-link {{ $activeTab === 'cards' ? 'active' : '' }}" data-toggle="tab" href="#ma-cards" role="tab"><i class="fas fa-id-card mr-1"></i> RFID Card</a>
            <a class="nav-link {{ $activeTab === 'machines' ? 'active' : '' }}" data-toggle="tab" href="#ma-machines" role="tab"><i class="fas fa-industry mr-1"></i> Mesin & Token</a>
            <a class="nav-link {{ $activeTab === 'permissions' ? 'active' : '' }}" data-toggle="tab" href="#ma-permissions" role="tab"><i class="fas fa-user-shield mr-1"></i> Permission</a>
            <a class="nav-link {{ $activeTab === 'logs' ? 'active' : '' }}" data-toggle="tab" href="#ma-logs" role="tab"><i class="fas fa-history mr-1"></i> Access Log</a>
        </nav>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'cards' ? 'show active' : '' }}" id="ma-cards" role="tabpanel">
                <div class="ma-grid">
                    <div class="ma-panel">
                        <div class="ma-panel-head"><h2 class="ma-panel-title">{{ $editingCard ? 'Edit RFID Card' : 'Assign RFID Card' }}</h2></div>
                        <div class="ma-panel-body">
                            <form method="POST" action="{{ $editingCard ? route('machine-auth.cards.update', $editingCard) : route('machine-auth.cards.store') }}">
                                @csrf
                                @if ($editingCard) @method('PUT') @endif
                                <div class="ma-field">
                                    <label class="ma-label">User / NPK</label>
                                    <select name="user_id" class="ma-select ma-select2" required>
                                        <option value="">-- pilih user --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ (string) old('user_id', $editingCard?->user_id) === (string) $user->id ? 'selected' : '' }}>
                                                {{ $user->npk }} — {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ma-field">
                                    <label class="ma-label">Card UID</label>
                                    <input class="ma-input" name="card_uid" maxlength="64" placeholder="1667815478" value="{{ old('card_uid', $editingCard?->card_uid) }}" required>
                                    <div class="ma-help">UID disimpan sebagai teks; leading zero tidak akan hilang.</div>
                                </div>
                                <div class="ma-field ma-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $editingCard?->is_active ?? true) ? 'checked' : '' }}>
                                    RFID aktif
                                </div>
                                <div class="ma-form-actions">
                                    <button class="ma-btn" type="submit"><i class="fas fa-save"></i> {{ $editingCard ? 'Update RFID' : 'Simpan RFID' }}</button>
                                    @if ($editingCard)<a class="ma-btn is-muted" href="{{ route('machine-auth.index', ['tab' => 'cards']) }}">Batal</a>@endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="ma-panel">
                        <div class="ma-panel-head"><h2 class="ma-panel-title">Daftar RFID Card</h2></div>
                        <div class="ma-table-wrap">
                            <table id="maCardsTable" class="table ma-table align-middle">
                                <thead><tr><th>UID</th><th>NPK</th><th>Nama</th><th>Status</th><th>Aksi</th></tr></thead><tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'machines' ? 'show active' : '' }}" id="ma-machines" role="tabpanel">
                <div class="ma-grid">
                    <div class="ma-panel">
                        <div class="ma-panel-head"><h2 class="ma-panel-title">{{ $editingMachine ? 'Edit Mesin & Token' : 'Tambah Mesin' }}</h2></div>
                        <div class="ma-panel-body">
                            <form method="POST" action="{{ $editingMachine ? route('machine-auth.machines.update', $editingMachine) : route('machine-auth.machines.store') }}">
                                @csrf
                                @if ($editingMachine) @method('PUT') @endif
                                <div class="ma-field"><label class="ma-label">Machine Code</label><input class="ma-input" name="machine_code" maxlength="64" placeholder="MC-001" value="{{ old('machine_code', $editingMachine?->machine_code) }}" required></div>
                                <div class="ma-field"><label class="ma-label">Nama Mesin</label><input class="ma-input" name="machine_name" maxlength="150" placeholder="Injection Machine 001" value="{{ old('machine_name', $editingMachine?->machine_name) }}" required></div>
                                <div class="ma-field">
                                    <label class="ma-label">Line</label>
                                    <select name="line_id" class="ma-select ma-select2" required>
                                        <option value="">-- pilih line --</option>
                                        @foreach ($lines as $line)
                                            @php
                                                $isCurrentMachineLine = (string) $editingMachine?->line_id === (string) $line->id;
                                            @endphp
                                            <option value="{{ $line->id }}"
                                                {{ (string) old('line_id', $editingMachine?->line_id) === (string) $line->id ? 'selected' : '' }}
                                                {{ ! $line->is_active && ! $isCurrentMachineLine ? 'disabled' : '' }}>
                                                {{ $line->name }}{{ ! $line->is_active ? ' (Nonaktif)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="ma-help">Satu mesin hanya terhubung ke satu line.</div>
                                </div>
                                <div class="ma-field"><label class="ma-label">Lokasi</label><input class="ma-input" name="location" maxlength="150" placeholder="Line Injection" value="{{ old('location', $editingMachine?->location) }}"></div>
                                <div class="ma-field"><label class="ma-label">Deskripsi</label><textarea class="ma-textarea" name="description" maxlength="2000">{{ old('description', $editingMachine?->description) }}</textarea></div>
                                <div class="ma-field ma-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $editingMachine?->is_active ?? true) ? 'checked' : '' }}>
                                    Mesin aktif
                                </div>
                                <div class="ma-form-actions">
                                    <button class="ma-btn" type="submit"><i class="fas fa-save"></i> {{ $editingMachine ? 'Update Mesin' : 'Simpan Mesin' }}</button>
                                    @if ($editingMachine)<a class="ma-btn is-muted" href="{{ route('machine-auth.index', ['tab' => 'machines']) }}">Batal</a>@endif
                                </div>
                            </form>

                            @if ($editingMachine)
                                <div class="ma-token-section">
                                    <h3 class="ma-panel-title mb-3">Provisioning Token Raspberry Pi</h3>
                                    <form method="POST" action="{{ route('machine-auth.machines.tokens.store', $editingMachine) }}">
                                        @csrf
                                        <div class="ma-field"><label class="ma-label">Nama Token</label><input class="ma-input" name="name" value="raspberry-pi" required></div>
                                        <div class="ma-field"><label class="ma-label">Kedaluwarsa (opsional)</label><input class="ma-input" type="datetime-local" name="expires_at"></div>
                                        <div class="ma-field ma-check">
                                            <input type="hidden" name="rotate_existing" value="0">
                                            <input type="checkbox" name="rotate_existing" value="1">
                                            Cabut semua token lama sebelum membuat token baru
                                        </div>
                                        <button type="submit" class="ma-btn"><i class="fas fa-key"></i> Buat Token</button>
                                    </form>
                                    <ul class="ma-token-list">
                                        @forelse ($machineTokens as $token)
                                            <li>
                                                <div class="ma-token-meta">
                                                    <div class="ma-token-name">{{ $token->name }}</div>
                                                    Dibuat {{ $token->created_at?->format('Y-m-d H:i') }} · Terakhir dipakai {{ $token->last_used_at?->format('Y-m-d H:i') ?? 'belum pernah' }}
                                                </div>
                                                <form method="POST" action="{{ route('machine-auth.machines.tokens.destroy', [$editingMachine, $token->id]) }}" onsubmit="return confirm('Cabut token ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="ma-action-btn is-danger" type="submit"><i class="fas fa-ban"></i> Cabut</button>
                                                </form>
                                            </li>
                                        @empty
                                            <li><span class="ma-token-meta">Belum ada token untuk mesin ini.</span></li>
                                        @endforelse
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="ma-panel">
                        <div class="ma-panel-head"><h2 class="ma-panel-title">Daftar Mesin</h2></div>
                        <div class="ma-machine-filter">
                            <label for="maMachineLine"><i class="fas fa-industry"></i> Filter Line</label>
                            <select id="maMachineLine" class="ma-select">
                                <option value="">Semua Line</option>
                                @foreach ($lines as $line)
                                    <option value="{{ $line->id }}">{{ $line->name }}{{ ! $line->is_active ? ' (Nonaktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ma-table-wrap">
                            <table id="maMachinesTable" class="table ma-table align-middle">
                                <thead><tr><th>Kode</th><th>Nama</th><th>Line</th><th>Lokasi</th><th>Permission</th><th>Token</th><th>Status</th><th>Aksi</th></tr></thead><tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'permissions' ? 'show active' : '' }}" id="ma-permissions" role="tabpanel">
                <div class="ma-grid">
                    <div class="ma-panel">
                        <div class="ma-panel-head"><h2 class="ma-panel-title">{{ $editingPermission ? 'Edit Permission' : 'Tambah Permission' }}</h2></div>
                        <div class="ma-panel-body">
                            <form method="POST" action="{{ $editingPermission ? route('machine-auth.permissions.update', $editingPermission) : route('machine-auth.permissions.store') }}">
                                @csrf
                                @if ($editingPermission) @method('PUT') @endif
                                <div class="ma-field">
                                    <label class="ma-label">User / NPK</label>
                                    <select name="user_id" class="ma-select ma-select2" required>
                                        <option value="">-- pilih user --</option>
                                        @foreach ($users as $user)<option value="{{ $user->id }}" {{ (string) old('user_id', $editingPermission?->user_id) === (string) $user->id ? 'selected' : '' }}>{{ $user->npk }} — {{ $user->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="ma-field">
                                    <label class="ma-label">Mesin</label>
                                    <select name="machine_id" class="ma-select ma-select2" required>
                                        <option value="">-- pilih mesin --</option>
                                        @foreach ($machines as $machine)<option value="{{ $machine->id }}" {{ (string) old('machine_id', $editingPermission?->machine_id) === (string) $machine->id ? 'selected' : '' }}>{{ $machine->machine_code }} — {{ $machine->machine_name }}{{ $machine->line ? ' · ' . $machine->line->name : '' }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="ma-field ma-check">
                                    <input type="hidden" name="is_allowed" value="0">
                                    <input type="checkbox" name="is_allowed" value="1" {{ old('is_allowed', $editingPermission?->is_allowed ?? true) ? 'checked' : '' }}>
                                    Diizinkan menggunakan mesin
                                </div>
                                <div class="ma-field"><label class="ma-label">Berlaku Mulai (opsional)</label><input class="ma-input" type="datetime-local" name="valid_from" value="{{ old('valid_from', $editingPermission?->valid_from?->format('Y-m-d\TH:i')) }}"></div>
                                <div class="ma-field"><label class="ma-label">Berlaku Sampai (opsional)</label><input class="ma-input" type="datetime-local" name="valid_until" value="{{ old('valid_until', $editingPermission?->valid_until?->format('Y-m-d\TH:i')) }}"></div>
                                <div class="ma-form-actions">
                                    <button class="ma-btn" type="submit"><i class="fas fa-save"></i> {{ $editingPermission ? 'Update Permission' : 'Simpan Permission' }}</button>
                                    @if ($editingPermission)<a class="ma-btn is-muted" href="{{ route('machine-auth.index', ['tab' => 'permissions']) }}">Batal</a>@endif
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="ma-panel">
                        <div class="ma-panel-head"><h2 class="ma-panel-title">Daftar Permission</h2></div>
                        <div class="ma-table-wrap">
                            <table id="maPermissionsTable" class="table ma-table align-middle">
                                <thead><tr><th>NPK</th><th>Nama</th><th>Mesin</th><th>Status</th><th>Mulai</th><th>Sampai</th><th>Aksi</th></tr></thead><tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'logs' ? 'show active' : '' }}" id="ma-logs" role="tabpanel">
                <div class="ma-panel">
                    <div class="ma-panel-head"><h2 class="ma-panel-title">Audit Machine Access</h2></div>
                    <div class="ma-filter">
                        <select id="maLogResult" class="ma-select"><option value="">Semua hasil</option><option value="GRANTED">GRANTED</option><option value="DENIED">DENIED</option></select>
                        <select id="maLogMachine" class="ma-select"><option value="">Semua mesin</option>@foreach ($machines as $machine)<option value="{{ $machine->id }}">{{ $machine->machine_code }}</option>@endforeach</select>
                        <input id="maLogFrom" class="ma-input" type="date" title="Tanggal mulai">
                        <input id="maLogUntil" class="ma-input" type="date" title="Tanggal akhir">
                        <button id="maApplyLogFilter" type="button" class="ma-btn"><i class="fas fa-filter"></i> Terapkan</button>
                    </div>
                    <div class="ma-table-wrap">
                        <table id="maLogsTable" class="table ma-table align-middle">
                            <thead><tr><th>Waktu</th><th>Transaction</th><th>Mesin</th><th>Card UID</th><th>NPK</th><th>Nama</th><th>Hasil</th><th>Reason</th></tr></thead><tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            if ($.fn.select2) {
                $('.ma-select2').select2({ width: '100%' });
            }

            const language = {
                processing: 'Memuat data...', search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data', infoEmpty: 'Belum ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
            };

            const base = { processing: true, serverSide: true, pageLength: 10, autoWidth: false, language: language };
            const tables = [];

            if ($.fn.DataTable) {
                tables.push($('#maCardsTable').DataTable($.extend(true, {}, base, {
                    ajax: '{{ route('machine-auth.cards.data') }}',
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'card_uid', name: 'rfid_cards.card_uid' },
                        { data: 'npk', name: 'users.npk' },
                        { data: 'user_name', name: 'users.name' },
                        { data: 'is_active', name: 'rfid_cards.is_active' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                })));

                tables.push($('#maMachinesTable').DataTable($.extend(true, {}, base, {
                    ajax: {
                        url: '{{ route('machine-auth.machines.data') }}',
                        data: function (data) { data.line_id = $('#maMachineLine').val(); }
                    },
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'machine_code', name: 'machines.machine_code' },
                        { data: 'machine_name', name: 'machines.machine_name' },
                        { data: 'line_name', name: 'lines.name', defaultContent: '-' },
                        { data: 'location', name: 'machines.location', defaultContent: '-' },
                        { data: 'permissions_count', searchable: false },
                        { data: 'tokens_count', searchable: false },
                        { data: 'is_active', name: 'machines.is_active' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                })));

                $('#maMachineLine').on('change', function () {
                    $('#maMachinesTable').DataTable().ajax.reload();
                });

                tables.push($('#maPermissionsTable').DataTable($.extend(true, {}, base, {
                    ajax: '{{ route('machine-auth.permissions.data') }}',
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'npk', name: 'users.npk' },
                        { data: 'user_name', name: 'users.name' },
                        { data: 'machine_code', name: 'machines.machine_code' },
                        { data: 'permission_status', orderable: false, searchable: false },
                        { data: 'valid_from', name: 'machine_permissions.valid_from' },
                        { data: 'valid_until', name: 'machine_permissions.valid_until' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                })));

                const logsTable = $('#maLogsTable').DataTable($.extend(true, {}, base, {
                    ajax: {
                        url: '{{ route('machine-auth.logs.data') }}',
                        data: function (data) {
                            data.result = $('#maLogResult').val();
                            data.machine_id = $('#maLogMachine').val();
                            data.date_from = $('#maLogFrom').val();
                            data.date_until = $('#maLogUntil').val();
                        }
                    },
                    order: [[0, 'desc']],
                    columns: [
                        { data: 'occurred_at', name: 'machine_access_logs.occurred_at' },
                        { data: 'transaction_id', name: 'machine_access_logs.transaction_id' },
                        { data: 'machine_code', name: 'machine_access_logs.machine_code' },
                        { data: 'card_uid', name: 'machine_access_logs.card_uid' },
                        { data: 'npk', name: 'users.npk' },
                        { data: 'user_name', name: 'users.name' },
                        { data: 'result', name: 'machine_access_logs.result' },
                        { data: 'reason', name: 'machine_access_logs.reason' }
                    ]
                }));
                tables.push(logsTable);
                $('#maApplyLogFilter').on('click', function () { logsTable.ajax.reload(); });
            }

            $('#machineAuthTabs a[data-toggle="tab"]').on('shown.bs.tab', function () {
                tables.forEach(function (table) { table.columns.adjust(); });
            });

            $('#copyMachineToken').on('click', function () {
                const input = document.getElementById('machinePlainToken');
                if (!input) return;
                const copied = function () { $('#copyMachineToken').html('<i class="fas fa-check"></i> Tersalin'); };
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(input.value).then(copied);
                } else {
                    input.select();
                    document.execCommand('copy');
                    copied();
                }
            });
        });
    </script>
@endpush
