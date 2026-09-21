@extends('layouts.root.dashboard')

@section('main')
    @php
        // Generic: tidak ada default hardcode. Kosong = user harus search/pilih mesin.
        $machine = $machine ?? request()->query('machine', '');
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        .main-content > .section { margin-top: -70px !important; position: relative; z-index: 2; }
        .leak-card { position: relative; }
        .leak-card.is-loading .leak-value { opacity: 0.35; }
        .leak-card-spin {
            position: absolute; top: 12px; right: 12px;
            width: 28px; height: 28px; border-radius: 9999px;
            background: rgba(41,71,149,.08);
            display: flex; align-items: center; justify-content: center;
        }
    </style>

    <div class="max-w-7xl mx-auto">

        {{-- ===== Hero ===== --}}
        <div class="relative overflow-hidden rounded-2xl px-4 py-2 mb-6 text-white shadow-lg bg-gradient-to-br from-[#0b1e42] via-[#294795] to-[#00a8e8]">
            <div class="pointer-events-none absolute -top-20 -right-16 w-72 h-72 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-24 right-40 w-56 h-56 rounded-full bg-sky-300/20"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.15]"
                style="background-image: radial-gradient(rgba(255,255,255,.7) 1px, transparent 1px); background-size: 22px 22px;"></div>

            <div class="relative flex flex-wrap items-center gap-x-4 gap-y-2 min-h-[112px]">
                <div class="flex items-center gap-3 my-auto">
                    <div class="w-11 h-11 rounded-2xl bg-white/15 border border-white/25 flex items-center justify-center text-lg backdrop-blur">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="min-w-[200px]">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-sky-200 mb-0.5">Monitoring</p>
                        <h4 class="m-0 font-extrabold text-xl leading-tight">
                            Leak · <span id="machineTitle">{{ $machine }}</span>
                            <span id="statusBadge" class="ml-2 align-middle text-[11px] font-bold px-2 py-1 rounded-full bg-white/20">-</span>
                        </h4>
                        <p class="text-xs text-white/75 mt-1 mb-0">
                            Update terakhir: <b id="lastUpdate" class="text-white font-bold">-</b>
                            <span id="syncSpin" style="display:none" class="ml-1 text-white/90"><i class="fas fa-circle-notch fa-spin"></i> memuat…</span>
                        </p>
                    </div>
                </div>

                <div class="ml-auto flex flex-wrap justify-end items-end gap-2 self-end">
                    <div class="relative">
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-sky-100/80" for="machineInput">Mesin</label>
                        <input id="machineInput" type="text" value="{{ $machine }}" placeholder="Cari mesin…"
                            autocomplete="off" role="combobox" aria-expanded="false" aria-controls="machineList"
                            class="mt-1 w-[200px] rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm text-white placeholder-white/50 shadow-inner backdrop-blur focus:outline-none focus:ring-2 focus:ring-white/70 focus:bg-white/15" />
                        <div id="machineList" class="absolute z-20 mt-1 w-[200px] overflow-hidden rounded-xl bg-white text-slate-800 shadow-lg" style="display:none"></div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-sky-100/80" for="intervalSelect">Refresh</label>
                        <select id="intervalSelect"
                            class="mt-1 rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm text-white shadow-inner backdrop-blur focus:outline-none focus:ring-2 focus:ring-white/70">
                            <option value="5000" class="text-slate-800">5 dtk</option>
                            <option value="10000" class="text-slate-800" selected>10 dtk</option>
                            <option value="30000" class="text-slate-800">30 dtk</option>
                            <option value="0" class="text-slate-800">Manual</option>
                        </select>
                    </div>
                    <button id="leakRefreshBtn"
                        class="rounded-xl bg-white px-4 py-2 text-sm font-extrabold text-[#1d3573] shadow hover:bg-sky-50 active:scale-[.98] transition">
                        <i class="fas fa-sync-alt"></i> Muat Ulang
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== Error ===== --}}
        <div id="leakWarning" class="mb-3" style="display:none"></div>

        {{-- ===== Kartu data (generic, mengikuti isi "data" dari API) ===== --}}
        <div id="cardsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"></div>
    </div>

    <script>
        (function () {
            const baseDataUrl = @json(route('dashboard.leak.data'));
            const machinesUrl = @json(route('dashboard.leak.machines'));
            const $ = (id) => document.getElementById(id);
            const machineInput = $('machineInput');
            const machineList = $('machineList');
            const intervalSelect = $('intervalSelect');
            let timer = null;
            let searchTimer = null;

            const prettyKey = (k) => k.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
            const prettyVal = (v) => (v === null || v === undefined || v === '') ? '-' : String(v);

            // Generic: tentukan ikon dari nama key, bukan daftar key hardcode per mesin.
            function iconFor(key) {
                const k = String(key || '').toLowerCase();
                if (k.includes('flow')) return 'fa-wind';
                if (k.includes('leak')) return 'fa-tint';
                if (k.includes('temp')) return 'fa-thermometer-half';
                if (k.includes('press')) return 'fa-gauge-high';
                return 'fa-microchip';
            }

            // Escape semua string dari API sebelum dimasukkan ke innerHTML (anti-XSS).
            function esc(s) {
                return String(s ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function setBadge(ok) {
                const el = $('statusBadge');
                el.textContent = ok ? 'ONLINE' : 'OFFLINE';
                el.className = 'ml-2 align-middle text-[11px] font-bold px-2 py-1 rounded-full ' + (ok ? 'bg-emerald-400/90' : 'bg-red-400/90');
            }

            function cardHtml(key, inner) {
                const label = prettyKey(key);
                const icon = iconFor(key);
                return '<div class="leak-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm" data-key="' + esc(key) + '">'
                    + '<div class="flex items-center gap-3 mb-3">'
                    + '<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#294795] to-[#00a8e8] text-white flex items-center justify-center"><i class="fas ' + esc(icon) + '"></i></div>'
                    + '<div><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0">' + esc(label) + '</p></div>'
                    + '</div>'
                    + '<p class="leak-value font-extrabold text-slate-900 mb-0" style="font-size:2rem;line-height:1">' + inner + '</p>'
                    + '</div>';
            }

            const SPINNER = '<i class="fas fa-circle-notch fa-spin text-[#294795]" style="font-size:1.75rem"></i>';

            function drawEmpty(text) {
                const grid = $('cardsGrid');
                grid.innerHTML = '<div class="md:col-span-2 bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">'
                    + '<div class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400"><i class="fas fa-inbox fa-lg"></i></div>'
                    + '<p class="font-bold text-slate-600 mb-1">' + esc(text) + '</p>'
                    + '<button id="leakRetryBtn" type="button" class="rounded-xl bg-[#294795] px-4 py-2 text-sm font-bold text-white shadow hover:opacity-90 active:scale-[.98] transition"><i class="fas fa-redo"></i> Coba Lagi</button>'
                    + '</div>';
                const rb = $('leakRetryBtn');
                if (rb) rb.addEventListener('click', load);
            }

            function drawCards(data) {
                const entries = Object.entries(data || {});
                if (!entries.length) {
                    drawEmpty('Belum ada data');
                    return;
                }
                $('cardsGrid').innerHTML = entries.map(([key, val]) => cardHtml(key, esc(prettyVal(val)))).join('');
            }

            function drawLoadingCards() {
                const grid = $('cardsGrid');
                const existing = [...grid.querySelectorAll('.leak-card')].map((c) => c.dataset.key).filter(Boolean);
                // Generic skeleton: pakai key yang sudah ada, atau 4 placeholder netral.
                const list = existing.length ? existing : ['loading-1', 'loading-2', 'loading-3', 'loading-4'];
                grid.innerHTML = list.map((key) => cardHtml(key, SPINNER)).join('');
            }

            function drawError(msg) {
                const box = $('leakWarning');
                box.style.display = 'block';
                box.innerHTML = '<div class="alert alert-danger mb-0" style="border-radius:12px">'
                    + '<b><i class="fas fa-exclamation-triangle"></i> Gagal ambil data:</b> ' + esc(msg) + '</div>';
            }

            function hideError() {
                const box = $('leakWarning');
                box.style.display = 'none';
                box.innerHTML = '';
            }

            function setLoading(on) {
                const sync = $('syncSpin');
                const btnIcon = document.querySelector('#leakRefreshBtn i');
                if (on) {
                    if (sync) sync.style.display = 'inline';
                    if (btnIcon) btnIcon.classList.add('fa-spin');
                    drawLoadingCards();
                } else {
                    if (sync) sync.style.display = 'none';
                    if (btnIcon) btnIcon.classList.remove('fa-spin');
                }
            }

            function currentMachine() {
                return ((machineInput.value || '').trim() || '').toUpperCase();
            }

            async function load() {
                const machine = currentMachine();
                if (!machine) {
                    setBadge(false);
                    $('machineTitle').textContent = '-';
                    $('lastUpdate').textContent = '-';
                    hideError();
                    drawEmpty('Pilih mesin dulu via kolom search');
                    return;
                }
                $('machineTitle').textContent = machine;
                const url = new URL(window.location.href);
                url.searchParams.set('machine', machine);
                window.history.replaceState(null, '', url);
                $('leakRefreshBtn').disabled = true;
                setLoading(true);
                try {
                    const res = await fetch(baseDataUrl + '?machine=' + encodeURIComponent(machine), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (!json.status) throw new Error(json.message || 'Gagal mengambil data.');
                    hideError();
                    setBadge(true);
                    drawCards(json.data || {});
                    $('lastUpdate').textContent = new Date().toLocaleString();
                } catch (e) {
                    setBadge(false);
                    drawEmpty('Belum ada data');
                    drawError(e.message || 'Gagal mengambil data.');
                    $('lastUpdate').textContent = '-';
                } finally {
                    setLoading(false);
                    $('leakRefreshBtn').disabled = false;
                }
            }

            function hideMachineList() {
                machineList.style.display = 'none';
                machineInput.setAttribute('aria-expanded', 'false');
            }

            function showMachineList(items) {
                if (!items.length) { hideMachineList(); return; }
                machineList.innerHTML = items.map((m) =>
                    '<button type="button" data-code="' + esc(m.code) + '" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-100">'
                    + '<span class="font-bold">' + esc(m.code) + '</span>'
                    + (m.name && m.name !== m.code ? ' <span class="text-slate-500">· ' + esc(m.name) + '</span>' : '')
                    + '</button>'
                ).join('');
                machineList.style.display = 'block';
                machineInput.setAttribute('aria-expanded', 'true');
                machineList.querySelectorAll('button[data-code]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        machineInput.value = btn.dataset.code;
                        hideMachineList();
                        load();
                    });
                });
            }

            async function searchMachines(term) {
                try {
                    const res = await fetch(machinesUrl + '?q=' + encodeURIComponent(term), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    showMachineList(json.machines || []);
                } catch (e) {
                    hideMachineList();
                }
            }

            function restartTimer() {
                if (timer) { clearInterval(timer); timer = null; }
                const ms = parseInt(intervalSelect.value, 10);
                if (ms > 0) timer = setInterval(load, ms);
            }

            $('leakRefreshBtn').addEventListener('click', load);
            machineInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { hideMachineList(); load(); } });
            machineInput.addEventListener('input', () => {
                const term = machineInput.value.trim();
                if (searchTimer) clearTimeout(searchTimer);
                if (term.length < 1) { hideMachineList(); return; }
                searchTimer = setTimeout(() => searchMachines(term), 250);
            });
            document.addEventListener('click', (e) => {
                if (!machineList.contains(e.target) && e.target !== machineInput) hideMachineList();
            });
            intervalSelect.addEventListener('change', restartTimer);
            restartTimer();
            load();
        })();
    </script>
@endsection
