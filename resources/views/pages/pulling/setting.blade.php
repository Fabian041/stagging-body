@extends('layouts.root.main')

@section('main')
    <style>
        /* ======================
                                                                                                                                                                                                                                                                                                         THEME TOKENS (fallback)
                                                                                                                                                                                                                                                                                                         ====================== */
        :root {
            --brand-primary: #0d6efd;
            --brand-accent: #20c997;
            --brand-warn: #ffc107;

            --ink: var(--ink, #273446);
            --muted: var(--muted, #6b7280);
            --surface: var(--surface, #ffffff);
            --surface-sub: var(--surface-subtle, #f7f9fc);
            --border: var(--border, #e5eaf0);
            --shadow: 0 6px 24px rgba(16, 24, 40, .06);

            /* header list (light) */
            --thead-bg: #EEF2F7;
            --thead-ink: #273446;
            --thead-border: #D6DEE9;

            /* highlight */
            --hi-yellow: #fff7d6;
            --hi-yellow-2: #ffefb6;
            --hi-green: #e8faf1;
            --chip-bg: #eef2ff;
            --chip-ink: #344767;
            --chip-bd: #dfe6ff;
            --radius: 10px;

            --col-seq: 100px;
            --col-back: 220px;
            --col-cust: 1fr;
            --col-qty: 160px;
            --col-time: 160px;
            --col-gap: 1rem;
            /* sama dengan gap-3 */
        }

        html[data-theme="dark"] {
            --ink: #eaf0f7;
            --muted: #aeb9c8;
            --surface: #1b1f26;
            --surface-sub: #12161b;
            --border: #2f3742;
            --shadow: 0 8px 28px rgba(0, 0, 0, .45);

            --thead-bg: #1f2530;
            --thead-ink: #f5f9ff;
            --thead-border: #3a4452;

            --hi-yellow: #3a2f10;
            --hi-yellow-2: #514015;
            --hi-green: #173c29;
            --chip-bg: #243041;
            --chip-ink: #eaf0ff;
            --chip-bd: #334158;
        }

        /* ==============
                                                                                                                                                                                                                                                                                                         BASE / WRAPPER
                                                                                                                                                                                                                                                                                                         ============== */
        .seq-board {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .seq-head {
            display: flex;
            gap: 16px;
            align-items: end;
            padding: 16px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(180deg, var(--surface), var(--surface-sub));
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
        }

        .seq-head .form-label {
            font-weight: 600;
            color: var(--muted);
            letter-spacing: .3px;
        }

        .seq-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            border-radius: 10px;
        }

        .btn-primary {
            box-shadow: 0 8px 20px rgba(13, 110, 253, .18);
        }

        .btn-success {
            box-shadow: 0 8px 20px rgba(16, 185, 129, .18);
        }

        /* =================
                                                                                                                                                                                                                                                                                                         INFO / SUB HEADER
                                                                                                                                                                                                                                                                                                         ================= */
        .info-panel {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--hi-green);
            color: var(--ink);
            border-left: 4px solid var(--brand-accent);
            padding: 10px 14px;
            border-radius: 10px;
            margin: 16px 16px 10px;
        }

        .list-head {
            position: sticky;
            top: 0;
            z-index: 1;
            display: flex;
            gap: 0;
            align-items: center;
            padding: 10px 16px;
            background: var(--thead-bg);
            color: var(--thead-ink);
            border-top: 1px solid var(--thead-border);
            border-bottom: 1px solid var(--thead-border);
        }

        .list-head .hcell {
            font-weight: 700;
        }

        /* =========
                                                                                                                                                                                                                                                                                                         ITEM ROWS
                                                                                                                                                                                                                                                                                                         ========= */
        .item-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            margin: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: 0 1px 0 rgba(16, 24, 40, .02);
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }

        .item-row:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--border) 70%, var(--brand-primary));
            box-shadow: 0 10px 24px rgba(2, 6, 23, .06);
        }

        .item-badge {
            background: var(--chip-bg);
            color: var(--chip-ink);
            border: 1px solid var(--chip-bd);
            border-radius: 999px;
            padding: .35rem .6rem;
            font-weight: 700;
        }

        /* sequence input */
        .sequence-input-container {
            width: 110px;
        }

        .industrial-sequence-input {
            width: 100%;
            height: 64px;
            text-align: center;
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: .5px;
            border: 2px solid var(--border);
            background: var(--surface-sub);
            color: var(--ink);
            border-radius: 12px;
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
            -moz-appearance: textfield;
        }

        .industrial-sequence-input::-webkit-outer-spin-button,
        .industrial-sequence-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .industrial-sequence-input:focus {
            border-color: var(--brand-primary);
            background: var(--surface);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-primary) 20%, transparent);
        }

        /* changed rows */
        .sequence-changed {
            position: relative;
            background: var(--hi-yellow) !important;
            border-color: var(--brand-warn) !important;
            box-shadow: 0 6px 18px rgba(255, 193, 7, .18);
            animation: pulseRow 2s infinite;
        }

        @keyframes pulseRow {
            0% {
                background: var(--hi-yellow);
            }

            50% {
                background: var(--hi-yellow-2);
            }

            100% {
                background: var(--hi-yellow);
            }
        }

        .sequence-changed::after {
            content: attr(data-swap-info);
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .8rem;
            font-weight: 700;
            color: #0f5132;
            background: #fff;
            border: 1px solid #198754;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .swap-info-badge {
            display: inline-block;
            margin-left: 6px;
            background: #17a2b8;
            color: #fff;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: .75rem;
        }

        /* Header grid (Bootstrap cols tetap dipakai, ini hanya memberi min-width agar rapi) */
        .h-seq {
            min-width: 110px;
        }

        .h-back {
            min-width: 220px;
        }

        .h-cust {
            min-width: 300px;
        }

        .h-qty {
            min-width: 160px;
            text-align: center;
        }

        .h-dead {
            min-width: 160px;
            text-align: center;
        }

        /* list scroll area */
        #itemsContainer {
            padding-top: 6px;
            padding-bottom: 4px;
        }

        :root {
            --page-side-pad: clamp(14px, 2vw, 32px);
        }

        .container {
            padding-left: var(--page-side-pad);
            padding-right: var(--page-side-pad);
        }

        html {
            font-size: clamp(16px, 1.1vw, 22px);
        }

        h4 {
            font-size: clamp(1.1rem, 1.35vw, 1.6rem);
        }

        @media (min-width: 1600px) {
            html {
                font-size: 20px;
            }

            .industrial-sequence-input {
                height: 70px;
                font-size: 1.5rem;
            }
        }

        @media (min-width: 1920px) {
            html {
                font-size: 24px;
            }

            .industrial-sequence-input {
                height: 76px;
                font-size: 1.6rem;
            }
        }

        /* ====== PATCH: compact header + info bar + sticky list header ====== */

        /* Panel utama lebih rapih, tanpa overflow shadow aneh */
        .seq-board {
            border-radius: 12px;
            overflow: hidden;
        }

        /* Header filter: rata tengah vertikal, tanpa gradient & shadow */
        .seq-head {
            align-items: center;
            gap: 24px;
            padding: 12px 16px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: none;
        }

        .seq-head .row.g-3 {
            margin: 0;
        }

        .seq-head .form-control,
        .seq-head .form-select {
            height: 44px;
        }

        .seq-actions .btn {
            box-shadow: none;
            padding: .65rem 1rem;
        }

        .btn {
            border-radius: 8px;
        }

        /* kurangi radius yang terlalu besar */

        /* Info panel: lebih datar, aksen bar di kiri pakai ::before (bukan border-left) */
        .info-panel {
            margin: 12px 16px 0;
            padding: 10px 12px 10px 40px;
            border-radius: 8px;
            background: color-mix(in srgb, var(--brand-accent) 8%, var(--surface));
            border: 1px solid color-mix(in srgb, var(--brand-accent) 25%, var(--border));
            position: relative;
        }

        .info-panel::before {
            content: "";
            position: absolute;
            left: 12px;
            top: 10px;
            bottom: 10px;
            width: 6px;
            border-radius: 3px;
            background: var(--brand-accent);
        }

        /* List header (SEQ#, BACK NUMBER, dst) – sticky rapi & konsisten */
        .list-head {
            margin: 10px 0 0;
            padding: 10px 16px;
            background: var(--thead-bg);
            color: var(--thead-ink);
            border-top: 0;
            border-bottom: 1px solid var(--thead-border);
            position: sticky;
            top: var(--sticky-offset, 0);
            /* diisi via JS di bawah jika ada navbar fixed */
            z-index: 1;
        }

        .hcell {
            font-weight: 700;
            padding-right: 12px;
        }

        /* Kolom minimum agar tidak “loncat” saat resize */
        .h-seq {
            min-width: 110px;
        }

        .h-back {
            min-width: 220px;
        }

        .h-cust {
            min-width: 300px;
        }

        .h-qty {
            min-width: 160px;
            text-align: center;
        }

        .h-dead {
            min-width: 160px;
            text-align: center;
        }

        /* Header & rows pakai grid yang identik */
        .list-head,
        #itemsContainer .item-row {
            display: grid !important;
            grid-template-columns: var(--col-seq) var(--col-back) var(--col-cust) var(--col-qty) var(--col-time);
            align-items: center;
            column-gap: var(--col-gap);
            box-sizing: border-box;
        }

        /* Padding seragam (summary & non-summary) */
        .list-head .hcell,
        #itemsContainer .item-row>div {
            padding: 12px 16px;
        }

        #itemsContainer .summary-list-row {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        /* Perataan kolom agar konsisten */
        .list-head .hcell:nth-child(4),
        #itemsContainer .item-row>div:nth-child(4) {
            /* Quantity */
            text-align: center;
            justify-self: center;
        }

        .list-head .hcell:nth-child(5),
        #itemsContainer .item-row>div:nth-child(5) {
            /* Delivery time */
            text-align: right;
            justify-self: end;
        }

        /* Netralisir warisan flex/min-width inline pada template lama */
        #itemsContainer .sequence-input-container {
            width: auto !important;
            flex: 0 0 auto !important;
        }

        #itemsContainer .item-row>.flex-grow-1,
        #itemsContainer .item-row>.text-center,
        #itemsContainer .item-row>.text-end {
            min-width: 0 !important;
        }
    </style>

    <div class="row mt-3">
        <div class="col-12">
            <div class="seq-board">
                {{-- FILTER / HEADER --}}
                <div class="seq-head mt-5">
                    <div class="flex-grow-1">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="reorderDate" class="form-label">Production Date</label>
                                <input type="date" id="reorderDate" name="date" class="form-control"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="reorderLine" class="form-label">Production Line</label>
                                <select id="reorderLine" name="line" class="form-control">
                                    <option value="AS003">LINE AS003</option>
                                    <option value="AS004">LINE AS004</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="seq-actions">
                        <button type="button" id="loadItemsBtn" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i> Load Production
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div id="reorderContainer" class="d-none">
                    <div class="info-panel mt-3">
                        <i class="fas fa-info-circle"></i>
                        <div>Edit the sequence numbers to change production order. <strong>Lower numbers = higher
                                priority.</strong></div>
                    </div>

                    {{-- sticky list header --}}
                    <div class="list-head">
                        <div class="hcell h-seq">SEQ #</div>
                        <div class="hcell h-back">BACK NUMBER</div>
                        <div class="hcell h-cust">CUSTOMER</div>
                        <div class="hcell h-qty">QUANTITY</div>
                        <div class="hcell h-dead">DELIVERY TIME</div>
                    </div>

                    <div id="itemsContainer" class="mb-3"></div>

                    <div class="d-flex justify-content-end gap-2 p-3">
                        <button id="saveOrderBtn" class="btn btn-success mr-2">
                            <i class="fas fa-save me-2"></i> Save Production Sequence
                        </button>
                        <button id="resetChangesBtn" class="btn btn-outline-danger" style="display:none;">Reset All
                            Changes</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /* =========================================
                                               BACK NO CANONICAL CONVERTER (global-safe)
                                               - toCanon() & expandTargets()
                                               - Disimpan di window.BackNoCanon untuk dipakai modul lain
                                            ========================================= */
        (function BackNoConverter() {
            const BACKNO_PAIRS = [
                ['D111', 'CI12'],
                ['D500', 'CI19'],
                ['D403', 'CI18'], // contoh tambahan
            ];
            const up = s => String(s || '').trim().toUpperCase();
            const BACKNO_CANON = new Map(); // any -> canonical
            const CANON_VARIANTS = new Map(); // canonical -> Set(variants)

            function rebuildMaps(pairs) {
                BACKNO_CANON.clear();
                CANON_VARIANTS.clear();
                pairs.forEach(([raw, canon]) => {
                    const R = up(raw),
                        C = up(canon);
                    BACKNO_CANON.set(R, C);
                    BACKNO_CANON.set(C, C);
                    if (!CANON_VARIANTS.has(C)) CANON_VARIANTS.set(C, new Set([C]));
                    CANON_VARIANTS.get(C).add(R);
                });
            }
            rebuildMaps(BACKNO_PAIRS);

            function toCanon(code) {
                const k = up(code);
                return BACKNO_CANON.get(k) || k; // default ke dirinya sendiri
            }

            function expandTargets(targets) {
                const set = new Set();
                (targets || []).forEach(t => {
                    const C = toCanon(t);
                    set.add(C);
                    const vars = CANON_VARIANTS.get(C);
                    if (vars) vars.forEach(v => set.add(v));
                });
                return set;
            }

            function addPairs(pairs) {
                const merged = [...BACKNO_PAIRS, ...pairs];
                rebuildMaps(merged);
            }

            window.BackNoCanon = {
                toCanon,
                expandTargets,
                addPairs
            };
        })();

        /* =========================================
           REORDER MODULE (IIFE) — rotate delivery_time
           - UI ikut urutan backend (FE tidak sort)
           - Input posisi = insert/shift (ROTATE jam, termasuk summary rows)
           - Save: POST {id, delivery_time}; waktu SUMMARY dipakai untuk override
                   SEMUA item dengan alias tsb pada hari & line terpilih
           - Summary CI12 hanya AS003, CI19 hanya AS004
           - Detail untuk alias yg disummary DISEMBUNYIKAN di list
        ========================================= */
        (function ReorderModule() {
            let changedRows = new Map(); // id -> note
            let originalSnapshot = []; // [{id, delivery_time}]
            let loadedItems = []; // cache semua item dari backend (agar hidden items tetap ikut Save & Sum)

            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('loadItemsBtn')?.addEventListener('click', loadProductionItems);
                document.getElementById('saveOrderBtn')?.addEventListener('click', saveProductionSequence);

                const saveBtn = document.getElementById('saveOrderBtn');
                if (saveBtn && !document.getElementById('resetHighlightsBtn')) {
                    const container = saveBtn.parentNode;
                    const btns = document.createElement('span');
                    btns.innerHTML = `
            <button id="resetChangesBtn" class="btn btn-outline-danger ms-2" style="display:none;">Reset All Changes</button>
          `;
                    container.appendChild(btns);
                }

                document.getElementById('resetHighlightsBtn')?.addEventListener('click', () => {
                    document.querySelectorAll('.sequence-changed').forEach(el => {
                        el.classList.remove('sequence-changed');
                        el.removeAttribute('data-swap-info');
                    });
                    changedRows.clear();
                });

                document.getElementById('resetChangesBtn')?.addEventListener('click', resetAllChanges);
            });

            /* ============ Utilities ============ */
            const t2m = t => {
                const [H, M] = String(t || '00:00').split(':').map(v => parseInt(v, 10) || 0);
                return H * 60 + M;
            };

            function renumberPositions() {
                const rows = Array.from(document.querySelectorAll('#itemsContainer .item-row'));
                rows.forEach((r, i) => {
                    r.setAttribute('data-pos', i + 1);
                    r.setAttribute('data-sequence', i + 1);
                    const input = r.querySelector('.industrial-sequence-input');
                    if (input) {
                        input.value = i + 1;
                        input.max = rows.length;
                        input.min = 1;
                    }
                });
            }

            function repaintDelivery(row, hhmm) {
                const v = (hhmm || '00:00').slice(0, 5);
                row.setAttribute('data-delivery', v);
                const cell = row.querySelector('[data-col="delivery"]');
                if (cell) cell.textContent = v;
            }

            function markChanged(row, text) {
                row.classList.add('sequence-changed');
                row.setAttribute('data-swap-info', text);
                const id = row.getAttribute('data-id');
                if (id) changedRows.set(id, text);
            }

            /* ============ Target summary dinamis per line ============ */
            function getActiveSumTargets() {
                const line = (document.getElementById('reorderLine')?.value || '').toUpperCase();
                if (line === 'AS003') return ['CI12'];
                if (line === 'AS004') return ['CI19'];
                return [];
            }

            /* ============ SUM & SUMMARY ROWS (REORDERABLE) ============ */
            function ensureSumBadgesBar() {
                let bar = document.getElementById('backnoSums');
                if (!bar) {
                    bar = document.createElement('div');
                    bar.id = 'backnoSums';
                    bar.className = 'd-flex flex-wrap gap-2 mb-3';
                    const items = document.getElementById('itemsContainer');
                    if (items && items.parentNode) {
                        items.parentNode.insertBefore(bar, items);
                    } else {
                        document.body.insertBefore(bar, document.body.firstChild);
                    }
                }
                return bar;
            }

            function renderSummaryListRows(sums, earliestTimes, targets) {
                const wrap = document.getElementById('itemsContainer');
                if (!wrap) return;
                // Hapus summary row sebelumnya
                wrap.querySelectorAll('.summary-list-row').forEach(n => n.remove());

                // Sisipkan baris summary (dengan input) di PALING ATAS list
                // hanya kalau ada total > 0 (alias memang ada di hari/line tsb)
                for (let i = targets.length - 1; i >= 0; i--) {
                    const key = targets[i];
                    if (!sums[key]) continue; // skip jika 0
                    const time = (earliestTimes[key] || '00:00');
                    const row = document.createElement('div');
                    row.className =
                        'item-row summary-list-row d-flex align-items-center gap-3 rounded-3 py-2 mb-2';
                    row.setAttribute('data-summary', '1');
                    row.setAttribute('data-group-alias', key);
                    row.setAttribute('data-delivery', time);

                    row.innerHTML = `
            <div class="sequence-input-container" style="width:100px;flex:0 0 100px">
              <input type="number" class="industrial-sequence-input"
                     value="1" min="1" max="1"
                     onchange="handleSequenceChange(this)">
            </div>
            <div class="flex-grow-1 fw-semibold" style="min-width:220px">${key}</div>
            <div class="flex-grow-1" style="min-width:300px">--</div>
            <div class="text-center" style="min-width:160px">
              <span class="item-badge">${(sums[key] || 0).toLocaleString('id-ID')} UNITS</span>
            </div>
            <div class="text-end fw-semibold" style="min-width:160px" data-col="delivery">${time}</div>
          `;
                    wrap.insertBefore(row, wrap.firstChild);
                }

                // Nomori ulang semua rows + set min/max input
                renumberPositions();
            }

            function updateBacknoSums() {
                const TARGETS = getActiveSumTargets();
                const bar = ensureSumBadgesBar();

                // Hitung sum & earliest delivery_time dari CACHE (bukan DOM),
                // supaya item yang DISSEMBUNYIKAN juga ikut dihitung.
                const sums = Object.fromEntries(TARGETS.map(k => [k, 0]));
                const earliest = Object.fromEntries(TARGETS.map(k => [k, null]));

                loadedItems.forEach(it => {
                    const alias = (window.BackNoCanon?.toCanon(String(it.back_no || '')) || '').toUpperCase();
                    if (!alias || !(alias in sums)) return;
                    const qty = parseInt(it.order_qty || '0', 10) || 0;
                    const time = (String(it.delivery_time || '00:00').slice(0, 5));
                    sums[alias] += qty;
                    if (!earliest[alias] || time < earliest[alias]) earliest[alias] = time;
                });

                // Tambahkan baris ringkasan (ikut di-reorder) sesuai targets aktif
                renderSummaryListRows(sums, earliest, TARGETS);
            }

            /* ============ Load (pakai urutan backend) ============ */
            function loadProductionItems() {
                document.querySelectorAll('.sequence-changed').forEach(el => {
                    el.classList.remove('sequence-changed');
                    el.removeAttribute('data-swap-info');
                });
                changedRows.clear();
                originalSnapshot = [];
                loadedItems = [];

                const date = document.getElementById('reorderDate')?.value;
                const line = document.getElementById('reorderLine')?.value;
                if (!date) {
                    alert('Please select a production date');
                    return;
                }

                const btn = document.getElementById('loadItemsBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Loading...';
                }

                fetch('/api/production-items?date=' + encodeURIComponent(date) + '&line=' + encodeURIComponent(line))
                    .then(r => r.json())
                    .then(data => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-search me-2"></i> Load Production';
                        }
                        if (!Array.isArray(data) || !data.length) {
                            alert('No production items found for selected criteria');
                            return;
                        }

                        // normalisasi jam & simpan CACHE
                        data.forEach(d => {
                            d.delivery_time = (d.delivery_time || '00:00').slice(0, 5);
                        });
                        loadedItems = data.slice();

                        originalSnapshot = data.map(d => ({
                            id: String(d.id),
                            delivery_time: d.delivery_time
                        }));

                        const wrap = document.getElementById('itemsContainer');
                        if (!wrap) return;
                        wrap.innerHTML = '';

                        // Render HANYA item yg BUKAN alias summary aktif
                        const activeAliases = new Set(getActiveSumTargets()); // canonical
                        let visibleIdx = 0;

                        data.forEach(item => {
                            const aliasBN = (window.BackNoCanon?.toCanon(String(item.back_no || '')
                                .trim()) || (item.back_no || '')).toUpperCase();
                            if (activeAliases.has(aliasBN))
                                return; // sembunyikan detail dari alias yg disummary

                            visibleIdx++;
                            const row = document.createElement('div');
                            row.className = 'item-row d-flex align-items-center gap-3';
                            row.setAttribute('data-id', item.id);
                            row.setAttribute('data-pos', visibleIdx);
                            row.setAttribute('data-delivery', (item.delivery_time || '00:00').slice(0, 5));

                            // simpan untuk referensi
                            row.dataset.backnoAlias = aliasBN;
                            row.dataset.orderQty = String(item.order_qty ?? 0);

                            const isChanged = changedRows.has(String(item.id));
                            row.innerHTML = `
                <div class="sequence-input-container" style="width:100px;flex:0 0 100px">
                  <input type="number" class="industrial-sequence-input"
                         value="${visibleIdx}" min="1" max="${data.length}"
                         onchange="handleSequenceChange(this)">
                </div>
                <div class="flex-grow-1 fw-semibold" style="min-width:220px">
                  ${aliasBN || '-'}
                  ${isChanged ? '<span class="swap-info-badge">Modified</span>' : ''}
                </div>
                <div class="flex-grow-1" style="min-width:300px">${item.customer || '-'}</div>
                <div class="text-center" style="min-width:160px"><span class="item-badge">${item.order_qty ?? 0} UNITS</span></div>
                <div class="text-end fw-semibold" style="min-width:160px" data-col="delivery">${(item.delivery_time || '00:00').slice(0,5)}</div>
              `;
                            wrap.appendChild(row);
                        });

                        document.getElementById('reorderContainer')?.classList.remove('d-none');
                        const resetBtn = document.getElementById('resetChangesBtn');
                        if (resetBtn) resetBtn.style.display = 'none';

                        // pastikan numbering benar, lalu render SUMMARY (badge + row)
                        renumberPositions();
                        updateBacknoSums();
                    })
                    .catch(err => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-search me-2"></i> Load Production';
                        }
                        alert('Error loading production data: ' + (err.message || 'Unknown error'));
                    });
            }

            /* ============ Insert/Shift by rotating delivery_time (rows + summary) ============ */
            function handleSequenceChange(input) {
                const container = document.getElementById('itemsContainer');
                if (!container) return;

                // Ambil SEMUA row termasuk summary
                const rows = Array.from(container.querySelectorAll('.item-row'));

                // Init sequence & delivery if missing
                rows.forEach((r, i) => {
                    if (!r.hasAttribute('data-sequence')) {
                        r.setAttribute('data-sequence', i + 1);
                        const inp = r.querySelector('.industrial-sequence-input');
                        if (inp) inp.value = i + 1;
                    }
                    if (!r.hasAttribute('data-delivery')) {
                        const txt = r.querySelector('[data-col="delivery"]')?.textContent?.trim() || '00:00';
                        r.setAttribute('data-delivery', txt.slice(0, 5));
                    }
                });

                const movedRow = input.closest('.item-row');
                const total = rows.length;

                let newPos = parseInt(input.value, 10);
                const oldPos = parseInt(movedRow.getAttribute('data-sequence'), 10);

                if (isNaN(newPos)) {
                    input.value = oldPos;
                    return;
                }
                newPos = Math.max(1, Math.min(total, newPos));
                if (newPos === oldPos) {
                    input.value = oldPos;
                    return;
                }

                const resetBtn = document.getElementById('resetChangesBtn');
                if (resetBtn) resetBtn.style.display = 'inline-block';

                const seq = r => parseInt(r.getAttribute('data-sequence'), 10);
                const setSeq = (r, s) => {
                    r.setAttribute('data-sequence', s);
                    const inp = r.querySelector('.industrial-sequence-input');
                    if (inp) {
                        inp.value = s;
                        inp.max = rows.length;
                    }
                };
                const getTime = r => (r.getAttribute('data-delivery') || r.querySelector('[data-col="delivery"]')
                    ?.textContent || '00:00').slice(0, 5);
                const setTime = (r, t) => {
                    const v = (t || '00:00').slice(0, 5);
                    r.setAttribute('data-delivery', v);
                    const cell = r.querySelector('[data-col="delivery"]');
                    if (cell) cell.textContent = v;
                };
                const mark = (r, text) => {
                    r.classList.add('sequence-changed');
                    r.setAttribute('data-swap-info', text);
                    const id = r.getAttribute('data-id');
                    if (id) changedRows.set(id, text);
                };

                const ordered = rows.slice().sort((a, b) => seq(a) - seq(b));
                const timesOld = ordered.map(getTime);
                const oldIdx = oldPos - 1,
                    newIdx = newPos - 1;

                const newOrder = ordered.slice();
                const [moved] = newOrder.splice(oldIdx, 1);
                newOrder.splice(newIdx, 0, moved);

                const indexOfNew = new Map(newOrder.map((r, i) => [r, i]));
                ordered.forEach((r, iOld) => {
                    const iNew = indexOfNew.get(r);
                    const delta = iNew - iOld;
                    if (r === movedRow) mark(r, `Moved ${oldPos} → ${newPos}`);
                    else if (delta === 1) mark(r, '↓1');
                    else if (delta === -1) mark(r, '↑1');
                });

                // ROTATE delivery_time per slot baru (summary rows ikut)
                newOrder.forEach((r, iNew) => setTime(r, timesOld[iNew]));

                newOrder.forEach((r, i) => setSeq(r, i + 1));
                newOrder.forEach(r => container.appendChild(r));
            }

            /* ============ Reset ============ */
            function resetAllChanges() {
                if (!originalSnapshot.length) return;
                // Kembalikan jam item nyata ke snapshot
                const map = new Map(originalSnapshot.map(x => [String(x.id), (x.delivery_time || '00:00').slice(0,
                    5)]));
                const itemRows = Array.from(document.querySelectorAll('#itemsContainer .item-row[data-id]'));
                itemRows.forEach(row => {
                    const id = String(row.getAttribute('data-id'));
                    if (map.has(id)) repaintDelivery(row, map.get(id));
                    row.classList.remove('sequence-changed');
                    row.removeAttribute('data-swap-info');
                });
                changedRows.clear();
                // Rebuild summary rows dari CACHE (line-aware)
                updateBacknoSums();

                const resetBtn = document.getElementById('resetChangesBtn');
                if (resetBtn) resetBtn.style.display = 'none';
            }

            /* ============ Save ============ */
            function saveProductionSequence() {
                const date = document.getElementById('reorderDate')?.value;
                const line = document.getElementById('reorderLine')?.value;

                if (!date || !line) {
                    alert('Please select both date and production line');
                    return;
                }

                // Ambil waktu summary rows → aliasTimeMap (akan override semua item dgn alias tsb)
                const aliasTimeMap = {};
                document.querySelectorAll('#itemsContainer .summary-list-row').forEach(sr => {
                    const alias = (sr.getAttribute('data-group-alias') || '').toUpperCase();
                    const time = (sr.getAttribute('data-delivery') || sr.querySelector('[data-col="delivery"]')
                        ?.textContent || '00:00').slice(0, 5);
                    if (alias && time) aliasTimeMap[alias] = time;
                });

                // Build payload:
                // A) Semua item yang nampak di list (bukan alias summary) -> ambil time dari DOM
                // B) Semua item tersembunyi (alias summary aktif) -> ambil dari loadedItems & set time = aliasTimeMap[alias]
                const payloadMap = new Map();

                // A) visible
                document.querySelectorAll('#itemsContainer .item-row[data-id]').forEach(r => {
                    const id = r.getAttribute('data-id');
                    const time = (r.getAttribute('data-delivery') || '00:00').slice(0, 5);
                    payloadMap.set(id, time);
                });

                // B) hidden (alias summary)
                const activeAliases = new Set(getActiveSumTargets());
                loadedItems.forEach(it => {
                    const alias = (window.BackNoCanon?.toCanon(String(it.back_no || '')) || '').toUpperCase();
                    if (activeAliases.has(alias) && aliasTimeMap[alias]) {
                        payloadMap.set(String(it.id), aliasTimeMap[alias]);
                    }
                });

                if (!payloadMap.size) {
                    alert('No production items to sequence');
                    return;
                }

                const newOrder = Array.from(payloadMap.entries()).map(([id, delivery_time]) => ({
                    id,
                    delivery_time
                }));

                const btn = document.getElementById('saveOrderBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';
                }

                fetch('/pulling/settings/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            date,
                            line,
                            new_order: newOrder
                        })
                    })
                    .then(async r => {
                        const ct = r.headers.get('content-type') || '';
                        if (!ct.includes('application/json')) {
                            throw new Error(await r.text() || 'Non-JSON response');
                        }
                        return r.json();
                    })
                    .then(data => {
                        if (!data.success) throw new Error(data.message || 'Server error');
                        if (Array.isArray(data.data)) {
                            renderItemsFromServer(data.data);
                        }
                        // snapshot baru (dari DOM hasil render terbaru)
                        const rowsNow = Array.from(document.querySelectorAll('#itemsContainer .item-row[data-id]'));
                        originalSnapshot = rowsNow.map(r => ({
                            id: String(r.getAttribute('data-id')),
                            delivery_time: (r.getAttribute('data-delivery') || '00:00').slice(0, 5)
                        }));
                        document.getElementById('resetChangesBtn')?.style && (document.getElementById(
                            'resetChangesBtn').style.display = 'none');
                        alert('Delivery times updated & schedule recomputed.');
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error updating: ' + (err.message || 'Check console'));
                    })
                    .finally(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save me-2"></i> Save Production Sequence';
                        }
                    });
            }

            function renderItemsFromServer(items) {
                // normalisasi jam (TANPA sort—biarkan urutan backend)
                items.forEach(d => d.delivery_time = (d.delivery_time || '00:00').slice(0, 5));
                loadedItems = items.slice(); // refresh cache

                const wrap = document.getElementById('itemsContainer');
                if (!wrap) return;
                wrap.innerHTML = '';

                // Render HANYA item yg BUKAN alias summary aktif
                const activeAliases = new Set(getActiveSumTargets());
                let visibleIdx = 0;

                items.forEach(item => {
                    const aliasBN = window.BackNoCanon?.toCanon(String(item.back_no || '').trim()) || (item
                        .back_no || '');
                    const aliasUp = aliasBN.toUpperCase();
                    if (activeAliases.has(aliasUp)) return; // sembunyikan pecahan alias summary

                    visibleIdx++;
                    const row = document.createElement('div');
                    row.className = 'item-row d-flex align-items-center gap-3';
                    row.setAttribute('data-id', item.id);
                    row.setAttribute('data-pos', visibleIdx);
                    row.setAttribute('data-sequence', visibleIdx);
                    row.setAttribute('data-delivery', item.delivery_time);

                    row.dataset.backnoAlias = aliasUp;
                    row.dataset.orderQty = String(item.order_qty ?? 0);

                    row.innerHTML = `
            <div class="sequence-input-container" style="width:100px;flex:0 0 100px">
              <input type="number" class="industrial-sequence-input"
                     value="${visibleIdx}" min="1" max="${items.length}"
                     onchange="handleSequenceChange(this)">
            </div>
            <div class="flex-grow-1 fw-semibold" style="min-width:220px">${aliasUp || '-'}</div>
            <div class="flex-grow-1" style="min-width:300px">${item.customer || '-'}</div>
            <div class="text-center" style="min-width:160px"><span class="item-badge">${item.order_qty ?? 0} UNITS</span></div>
            <div class="text-end fw-semibold" style="min-width:160px" data-col="delivery">${item.delivery_time}</div>
          `;
                    wrap.appendChild(row);
                });

                // Rebuild SUMMARY (badge + row) & renumber
                renumberPositions();
                updateBacknoSums();
            }

            // Expose minimal APIs yang dibutuhkan inline/luar
            window.handleSequenceChange = handleSequenceChange;
            window.loadProductionItems = loadProductionItems;
            window.saveProductionSequence = saveProductionSequence;
        })();
    </script>
@endpush
