@extends('layouts.root.dashboard')

@section('main')
    @php
        $machine = $machine ?? request()->query('machine', 'TMEJ003');
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
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-sky-100/80" for="machineInput">Mesin</label>
                        <input id="machineInput" type="text" value="{{ $machine }}"
                            class="mt-1 w-[140px] rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm text-white placeholder-white/50 shadow-inner backdrop-blur focus:outline-none focus:ring-2 focus:ring-white/70 focus:bg-white/15" />
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
            const $ = (id) => document.getElementById(id);
            const machineInput = $('machineInput');
            const intervalSelect = $('intervalSelect');
            let timer = null;
            let firstLoad = true;

            const META = {
                'REGISTER_GENERAL_DATA_LEAK': { label: 'General Data Leak', icon: 'fa-tint', unit: '' },
                'FLOW_MEASUREMENT_VALUE': { label: 'Flow Measurement', icon: 'fa-wind', unit: '' },
            };

            const prettyKey = (k) => k.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
            const prettyVal = (v) => (v === null || v === undefined || v === '') ? '-' : String(v);

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
                const m = META[key] || { label: prettyKey(key), icon: 'fa-microchip', unit: '' };
                return '<div class="leak-card bg-white border border-slate-200 rounded-2xl p-5 shadow-sm" data-key="' + esc(key) + '">'
                    + '<div class="flex items-center gap-3 mb-3">'
                    + '<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#294795] to-[#00a8e8] text-white flex items-center justify-center"><i class="fas ' + esc(m.icon) + '"></i></div>'
                    + '<div><p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0">' + esc(m.label) + '</p></div>'
                    + '</div>'
                    + '<p class="leak-value font-extrabold text-slate-900 mb-0" style="font-size:2rem;line-height:1">' + inner + '</p>'
                    + '</div>';
            }

            const SPINNER = '<i class="fas fa-circle-notch fa-spin text-[#294795]" style="font-size:1.75rem"></i>';

            function drawCards(data) {
                const grid = $('cardsGrid');
                const entries = Object.entries(data || {});
                if (!entries.length) {
                    grid.innerHTML = '<div class="md:col-span-2 bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">'
                        + '<div class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400"><i class="fas fa-inbox fa-lg"></i></div>'
                        + '<p class="font-bold text-slate-600 mb-1">Belum ada data</p>'
                        + '<button id="leakRetryBtn" type="button" class="rounded-xl bg-[#294795] px-4 py-2 text-sm font-bold text-white shadow hover:opacity-90 active:scale-[.98] transition"><i class="fas fa-redo"></i> Coba Lagi</button>'
                        + '</div>';
                    const rb = $('leakRetryBtn');
                    if (rb) rb.addEventListener('click', load);
                    return;
                }
                grid.innerHTML = entries.map(([key, val]) => cardHtml(key, esc(prettyVal(val)))).join('');
            }

            function drawLoadingCards() {
                const grid = $('cardsGrid');
                const existing = [...grid.querySelectorAll('.leak-card')].map((c) => c.dataset.key).filter(Boolean);
                const list = existing.length ? existing : Object.keys(META);
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
                const grid = $('cardsGrid');
                const sync = $('syncSpin');
                const btnIcon = document.querySelector('#leakRefreshBtn i');
                if (on) {
                    if (sync) sync.style.display = 'inline';
                    if (btnIcon) btnIcon.classList.add('fa-spin');
                    if (firstLoad || grid.querySelectorAll('.leak-card').length) {
                        drawLoadingCards();
                    }
                } else {
                    if (sync) sync.style.display = 'none';
                    if (btnIcon) btnIcon.classList.remove('fa-spin');
                }
            }

            async function load() {
                const machine = (machineInput.value || '').trim() || @json($machine);
                $('machineTitle').textContent = machine;
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
                    drawCards({});
                    drawError(e.message || 'Gagal mengambil data.');
                    $('lastUpdate').textContent = '-';
                } finally {
                    firstLoad = false;
                    setLoading(false);
                    $('leakRefreshBtn').disabled = false;
                }
            }

            function restartTimer() {
                if (timer) { clearInterval(timer); timer = null; }
                const ms = parseInt(intervalSelect.value, 10);
                if (ms > 0) timer = setInterval(load, ms);
            }

            $('leakRefreshBtn').addEventListener('click', load);
            machineInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') load(); });
            intervalSelect.addEventListener('change', restartTimer);
            restartTimer();
            load();
        })();
    </script>
@endsection
