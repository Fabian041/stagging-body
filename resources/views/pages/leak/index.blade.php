@extends('layouts.root.dashboard')

@section('main')
    @php
        $machine = $machine ?? request()->query('machine', 'TMEJ003');
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        .main-content > .section { margin-top: -70px !important; position: relative; z-index: 2; }
    </style>

    <div class="max-w-7xl mx-auto">

        {{-- ===== Hero ===== --}}
        <div class="relative overflow-hidden rounded-2xl px-4 py-2 mb-6 text-white shadow-lg bg-gradient-to-br from-[#0b1e42] via-[#294795] to-[#00a8e8]">
            {{-- dekorasi --}}
            <div class="pointer-events-none absolute -top-20 -right-16 w-72 h-72 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-24 right-40 w-56 h-56 rounded-full bg-sky-300/20"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.15]"
                style="background-image: radial-gradient(rgba(255,255,255,.7) 1px, transparent 1px); background-size: 22px 22px;"></div>

            <div class="relative flex flex-wrap items-center gap-x-4 gap-y-2 min-h-[112px]">
                {{-- kiri: judul, tengah secara vertikal --}}
                <div class="flex items-center gap-3 my-auto">
                    <div class="w-11 h-11 rounded-2xl bg-white/15 border border-white/25 flex items-center justify-center text-lg backdrop-blur">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="min-w-[200px]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-sky-200 mb-0.5">Monitoring</p>
                        <h4 class="m-0 font-extrabold text-xl leading-tight">
                            Leak · <span id="machineTitle">{{ $machine }}</span>
                        </h4>
                        <p class="text-xs text-white/75 mt-1 mb-0">
                            Update terakhir: <b id="lastUpdate" class="text-white font-bold">-</b>
                        </p>
                    </div>
                </div>

                {{-- kanan bawah: kontrol --}}
                <div class="ml-auto flex flex-wrap justify-end items-end gap-2 self-end">
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-sky-100/80" for="machineInput">Mesin</label>
                        <input id="machineInput" type="text" value="{{ $machine }}"
                            class="mt-1 w-[140px] rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm text-white placeholder-white/50 shadow-inner backdrop-blur focus:outline-none focus:ring-2 focus:ring-white/70 focus:bg-white/15" />
                    </div>
                    <div>
                        <div class="relative mt-1">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-white/60"></i>
                            <input id="searchInput" type="text" placeholder="Cari data..."
                                class="w-[190px] rounded-xl border border-white/25 bg-white/10 pl-8 pr-3 py-2 text-sm text-white placeholder-white/50 shadow-inner backdrop-blur focus:outline-none focus:ring-2 focus:ring-white/70 focus:bg-white/15" />
                        </div>
                    </div>
                    <button id="leakRefreshBtn"
                        class="rounded-xl bg-white px-4 py-2 text-sm font-extrabold text-[#1d3573] shadow hover:bg-sky-50 active:scale-[.98] transition">
                        <i class="fas fa-sync-alt"></i> Muat Ulang
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== Data ===== --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="font-bold text-slate-800 mb-0"><i class="fas fa-table"></i> Data</p>
                <span id="rowCount" class="text-xs text-slate-400 font-semibold"></span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-md mb-0">
                    <thead id="leakTableHead">
                        <tr><th>#</th><th>Memuat...</th></tr>
                    </thead>
                    <tbody id="leakTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const baseDataUrl = @json(route('dashboard.leak.data'));
            const $ = (id) => document.getElementById(id);
            const machineInput = $('machineInput');
            const searchInput = $('searchInput');
            let cache = [];

            const title = (k) => k.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
            const txt = (v) => (v === null || v === undefined || v === '')
                ? '-' : (typeof v === 'object' ? JSON.stringify(v) : String(v));

            // Bentuk respons API belum pasti: array langsung, {data:[...]}, atau satu object.
            function toList(payload) {
                if (Array.isArray(payload)) return payload;
                if (payload && typeof payload === 'object') {
                    for (const v of Object.values(payload)) {
                        if (Array.isArray(v)) return v;
                    }
                    return [payload];
                }
                return [];
            }

            function colsOf(items) {
                const keys = [...new Set(items.flatMap((o) => (o && typeof o === 'object') ? Object.keys(o) : []))];
                return keys.slice(0, 6);
            }

            function drawTable() {
                const q = (searchInput.value || '').toLowerCase().trim();
                const rows = cache.filter((o) => !q || JSON.stringify(o).toLowerCase().includes(q)).slice(0, 50);
                const thead = $('leakTableHead'), tbody = $('leakTableBody');
                $('rowCount').textContent = cache.length ? cache.length + ' data' : '';

                if (!rows.length) {
                    thead.innerHTML = '<tr><th>#</th><th>Data</th></tr>';
                    tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>';
                    return;
                }
                if (typeof rows[0] !== 'object' || !rows[0]) {
                    thead.innerHTML = '<tr><th>#</th><th>Data</th></tr>';
                    tbody.innerHTML = rows.map((v, i) => '<tr><td>' + (i + 1) + '</td><td>' + txt(v) + '</td></tr>').join('');
                    return;
                }
                const cols = colsOf(cache);
                thead.innerHTML = '<tr><th>#</th>' + cols.map((c) => '<th>' + title(c) + '</th>').join('') + '</tr>';
                tbody.innerHTML = rows.map((o, i) =>
                    '<tr><td>' + (i + 1) + '</td>' +
                    cols.map((c) => '<td>' + txt(o[c]).substring(0, 60) + '</td>').join('') + '</tr>'
                ).join('');
            }

            async function load() {
                const machine = (machineInput.value || '').trim() || 'TMEJ003';
                $('machineTitle').textContent = machine;
                $('leakRefreshBtn').disabled = true;
                try {
                    const res = await fetch(baseDataUrl + '?machine=' + encodeURIComponent(machine), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (!json.status) throw new Error('empty');
                    cache = toList(json.data);
                    drawTable();
                    $('lastUpdate').textContent = new Date().toLocaleString();
                } catch (e) {
                    cache = [];
                    drawTable();
                    $('lastUpdate').textContent = '-';
                } finally {
                    $('leakRefreshBtn').disabled = false;
                }
            }

            $('leakRefreshBtn').addEventListener('click', load);
            machineInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') load(); });
            searchInput.addEventListener('input', drawTable);
            setInterval(load, 10000);
            load();
        })();
    </script>
@endsection
