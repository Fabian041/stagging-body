@extends('layouts.root.main')

@section('main')
    <style>
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
        .seq-head .form-control {
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

        /* kolom aksi */
        :root {
            --col-act: 80px;
        }

        /* tambahkan kolom aksi ke grid */
        .list-head,
        #itemsContainer .item-row {
            grid-template-columns:
                var(--col-seq) var(--col-back) var(--col-cust) var(--col-qty) var(--col-time) var(--col-act) !important;
        }

        /* header/actions align */
        .list-head .hcell:nth-child(6),
        #itemsContainer .item-row>div:nth-child(6) {
            text-align: right;
            justify-self: end;
        }

        /* tombol delete */
        .row-actions .btn {
            padding: .35rem .6rem;
            border-radius: 8px;
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
                        <button type="button" id="openAddModal" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i> Add Item
                        </button>

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
                        <div class="hcell h-act text-end">ACTIONS</div> <!-- NEW -->
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
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Item Produksi (Multi DN, Multi Back No)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addItemForm" class="modal-body">
                <!-- HEADER (shared untuk seluruh DN) -->
                <div class="row g-3 pb-2 border-bottom">
                    <div class="col-md-6">
                        <label class="form-label">Customer</label>
                        <select name="customer" class="form-control" required>
                            <option value="">— pilih customer —</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dock</label>
                        <select name="dock" class="form-control" required>
                            <option value="">— pilih dock —</option>
                        </select>
                    </div>

                    <!-- hidden dari header utama halaman -->
                    <input type="hidden" name="plan_date">
                    <input type="hidden" name="line">
                </div>

                <!-- DN GROUPS -->
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <h6 class="m-0">Delivery Notes (DN)</h6>
                    <button class="btn btn-sm btn-outline-primary" type="button" id="btnAddDnGroup">
                        <i class="fas fa-plus me-1"></i> Add DN
                    </button>
                </div>

                <div id="dnGroups" class="mt-2">
                    <!-- DN groups dibuat via JS -->
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="submitAddItem" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i> Tambah
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ============== BACK NO CANON + REORDER ============== */
        (function BackNoConverter() {
            const BACKNO_PAIRS = [
                ['D111', 'CI12'],
                ['D500', 'CI19'],
                ['D403', 'CI18'],
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
                return BACKNO_CANON.get(k) || k;
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
           REORDER MODULE — rotate delivery_time
           - UI ikut urutan backend (FE tidak sort)
           - Input posisi = insert/shift (ROTATE jam, termasuk summary rows)
           - Save: POST {id, delivery_time}; waktu SUMMARY override semua alias(ci12/ci19)
           - Summary CI12 hanya AS003, CI19 hanya AS004
           - Detail alias yg disummary DISEMBUNYIKAN di list
        ========================================= */
        (function ReorderModule() {
            let changedRows = new Map(); // id -> note
            let originalSnapshot = []; // [{id, delivery_time}]
            let loadedItems = []; // cache semua item dari backend

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

                document.getElementById('resetChangesBtn')?.addEventListener('click', resetAllChanges);

                // Delegasi delete
                document.getElementById('itemsContainer')?.addEventListener('click', async (e) => {
                    const btn = e.target.closest('.del-item');
                    if (!btn) return;

                    const row = btn.closest('.item-row[data-id]');
                    const id = row?.getAttribute('data-id');
                    if (!id) return;

                    const back = row?.querySelector(':scope > div:nth-child(2)')?.textContent
                        ?.trim() || '';
                    const qty = row?.querySelector('.item-badge')?.textContent?.trim() || '';
                    if (!confirm(`Hapus item ini?\nBack No: ${back}\n${qty}`)) return;

                    const planDate = document.getElementById('reorderDate')?.value || '';
                    const line = document.getElementById('reorderLine')?.value || '';

                    try {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        const res = await fetch(
                            `/pulling/settings/reorder/${encodeURIComponent(id)}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.content
                                },
                                body: JSON.stringify({
                                    plan_date: planDate,
                                    line
                                })
                            });

                        const json = await res.json().catch(() => ({}));
                        if (!res.ok || json.success === false) {
                            throw new Error(json.message || 'Gagal menghapus item');
                        }

                        if (Array.isArray(json.data) && typeof window.renderItemsFromServer ===
                            'function') {
                            window.renderItemsFromServer(json.data);
                        } else if (typeof window.loadProductionItems === 'function') {
                            window.loadProductionItems();
                        }
                    } catch (err) {
                        alert(err.message || 'Delete gagal');
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i>';
                    }
                });
            });

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

            function getActiveSumTargets() {
                const line = (document.getElementById('reorderLine')?.value || '').toUpperCase();
                if (line === 'AS003') return ['CI12'];
                if (line === 'AS004') return ['CI19'];
                return [];
            }

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
                wrap.querySelectorAll('.summary-list-row').forEach(n => n.remove());

                for (let i = targets.length - 1; i >= 0; i--) {
                    const key = targets[i];
                    if (!sums[key]) continue;
                    const time = (earliestTimes[key] || '00:00');
                    const row = document.createElement('div');
                    row.className = 'item-row summary-list-row d-flex align-items-center gap-3 rounded-3 py-2 mb-2';
                    row.setAttribute('data-summary', '1');
                    row.setAttribute('data-group-alias', key);
                    row.setAttribute('data-delivery', time);

                    row.innerHTML = `
        <div class="sequence-input-container" style="width:100px;flex:0 0 100px">
          <input type="number" class="industrial-sequence-input" value="1" min="1" max="1" onchange="handleSequenceChange(this)">
        </div>
        <div class="flex-grow-1 fw-semibold" style="min-width:220px">${key}</div>
        <div class="flex-grow-1" style="min-width:300px">--</div>
        <div class="text-center" style="min-width:160px">
          <span class="item-badge">${(sums[key] || 0).toLocaleString('id-ID')} UNITS</span>
        </div>
        <div class="text-end fw-semibold" style="min-width:160px" data-col="delivery">${time}</div>
        <div class="row-actions text-end" style="min-width:var(--col-act)"></div>
      `;
                    wrap.insertBefore(row, wrap.firstChild);
                }
                renumberPositions();
            }

            function updateBacknoSums() {
                const TARGETS = getActiveSumTargets();
                ensureSumBadgesBar();

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

                renderSummaryListRows(sums, earliest, TARGETS);
            }

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

                        data.forEach(d => d.delivery_time = (d.delivery_time || '00:00').slice(0, 5));
                        loadedItems = data.slice();

                        originalSnapshot = data.map(d => ({
                            id: String(d.id),
                            delivery_time: d.delivery_time
                        }));

                        const wrap = document.getElementById('itemsContainer');
                        if (!wrap) return;
                        wrap.innerHTML = '';

                        const activeAliases = new Set(getActiveSumTargets());
                        let visibleIdx = 0;

                        data.forEach(item => {
                            const aliasBN = (window.BackNoCanon?.toCanon(String(item.back_no || '')
                                .trim()) || (item.back_no || '')).toUpperCase();
                            if (activeAliases.has(aliasBN)) return;

                            visibleIdx++;
                            const row = document.createElement('div');
                            row.className = 'item-row d-flex align-items-center gap-3';
                            row.setAttribute('data-id', item.id);
                            row.setAttribute('data-pos', visibleIdx);
                            row.setAttribute('data-delivery', (item.delivery_time || '00:00').slice(0, 5));

                            row.dataset.backnoAlias = aliasBN;
                            row.dataset.orderQty = String(item.order_qty ?? 0);

                            row.innerHTML = `
            <div class="sequence-input-container" style="width:100px;flex:0 0 100px">
              <input type="number" class="industrial-sequence-input" value="${visibleIdx}" min="1" max="${data?.length ?? visibleIdx}" onchange="handleSequenceChange(this)">
            </div>
            <div class="flex-grow-1 fw-semibold" style="min-width:220px">${aliasBN || '-'}</div>
            <div class="flex-grow-1" style="min-width:300px">${item.customer || '-'}</div>
            <div class="text-center" style="min-width:160px"><span class="item-badge">${item.order_qty ?? 0} UNITS</span></div>
            <div class="text-end fw-semibold" style="min-width:160px" data-col="delivery">${(item.delivery_time || '00:00').slice(0,5)}</div>
            <div class="row-actions text-end" style="min-width:var(--col-act)">
              <button type="button" class="btn btn-outline-danger btn-sm del-item" data-id="${item.id}" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          `;
                            wrap.appendChild(row);
                        });

                        document.getElementById('reorderContainer')?.classList.remove('d-none');
                        document.getElementById('resetChangesBtn') && (document.getElementById('resetChangesBtn')
                            .style.display = 'none');

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

            function handleSequenceChange(input) {
                const container = document.getElementById('itemsContainer');
                if (!container) return;

                const rows = Array.from(container.querySelectorAll('.item-row'));

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
                    if (r === movedRow) {
                        r.classList.add('sequence-changed');
                        r.setAttribute('data-swap-info', `Moved ${oldPos} → ${newPos}`);
                    } else if (delta === 1) {
                        r.classList.add('sequence-changed');
                        r.setAttribute('data-swap-info', '↓1');
                    } else if (delta === -1) {
                        r.classList.add('sequence-changed');
                        r.setAttribute('data-swap-info', '↑1');
                    }
                });

                newOrder.forEach((r, iNew) => setTime(r, timesOld[iNew]));
                newOrder.forEach((r, i) => setSeq(r, i + 1));
                newOrder.forEach(r => container.appendChild(r));
            }

            function resetAllChanges() {
                if (!originalSnapshot.length) return;
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
                updateBacknoSums();
                const resetBtn = document.getElementById('resetChangesBtn');
                if (resetBtn) resetBtn.style.display = 'none';
            }

            function saveProductionSequence() {
                const date = document.getElementById('reorderDate')?.value;
                const line = document.getElementById('reorderLine')?.value;
                if (!date || !line) {
                    alert('Please select both date and production line');
                    return;
                }

                const aliasTimeMap = {};
                document.querySelectorAll('#itemsContainer .summary-list-row').forEach(sr => {
                    const alias = (sr.getAttribute('data-group-alias') || '').toUpperCase();
                    const time = (sr.getAttribute('data-delivery') || sr.querySelector('[data-col="delivery"]')
                        ?.textContent || '00:00').slice(0, 5);
                    if (alias && time) aliasTimeMap[alias] = time;
                });

                const payloadMap = new Map();

                document.querySelectorAll('#itemsContainer .item-row[data-id]').forEach(r => {
                    const id = r.getAttribute('data-id');
                    const time = (r.getAttribute('data-delivery') || '00:00').slice(0, 5);
                    payloadMap.set(id, time);
                });

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
                        if (!ct.includes('application/json')) throw new Error(await r.text() ||
                            'Non-JSON response');
                        return r.json();
                    })
                    .then(data => {
                        if (!data.success) throw new Error(data.message || 'Server error');
                        if (Array.isArray(data.data)) renderItemsFromServer(data.data);

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
                items.forEach(d => d.delivery_time = (d.delivery_time || '00:00').slice(0, 5));
                loadedItems = items.slice();

                const wrap = document.getElementById('itemsContainer');
                if (!wrap) return;
                wrap.innerHTML = '';

                const activeAliases = new Set(getActiveSumTargets());
                let visibleIdx = 0;

                items.forEach(item => {
                    const aliasBN = window.BackNoCanon?.toCanon(String(item.back_no || '').trim()) || (item
                        .back_no || '');
                    const aliasUp = aliasBN.toUpperCase();
                    if (activeAliases.has(aliasUp)) return;

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
          <input type="number" class="industrial-sequence-input" value="${visibleIdx}" min="1" max="${items.length}" onchange="handleSequenceChange(this)">
        </div>
        <div class="flex-grow-1 fw-semibold" style="min-width:220px">${aliasUp || '-'}</div>
        <div class="flex-grow-1" style="min-width:300px">${item.customer || '-'}</div>
        <div class="text-center" style="min-width:160px"><span class="item-badge">${item.order_qty ?? 0} UNITS</span></div>
        <div class="text-end fw-semibold" style="min-width:160px" data-col="delivery">${(item.delivery_time || '00:00').slice(0,5)}</div>
        <div class="row-actions text-end" style="min-width:var(--col-act)">
          <button type="button" class="btn btn-outline-danger btn-sm del-item" data-id="${item.id}" title="Delete">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      `;
                    wrap.appendChild(row);
                });

                renumberPositions();
                updateBacknoSums();
            }

            // Expose
            window.handleSequenceChange = handleSequenceChange;
            window.loadProductionItems = loadProductionItems;
            window.saveProductionSequence = saveProductionSequence;
            window.renderItemsFromServer = renderItemsFromServer;
        })();
    </script>

    <script>
        (() => {
            /* ====== UTIL ====== */
            function toYMD(d) {
                const y = d.getFullYear(),
                    m = String(d.getMonth() + 1).padStart(2, '0'),
                    day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }

            function todayYMD(baseDate = new Date()) {
                return toYMD(baseDate);
            }

            function addDaysYMD(ymd, days) {
                const d = new Date(ymd + 'T00:00:00');
                d.setDate(d.getDate() + days);
                return toYMD(d);
            }

            function plusOneMinute(hhmm) {
                try {
                    const [H, M] = String(hhmm || '06:00').split(':').map(n => parseInt(n, 10) || 0);
                    const t = Math.min(1439, Math.max(0, H * 60 + M + 1));
                    return String(Math.floor(t / 60)).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0');
                } catch {
                    return '06:01';
                }
            }

            function enforceDeliveryDateTodayBesok(input) {
                const t = todayYMD();
                const besok = addDaysYMD(t, 1);
                input.min = t;
                input.max = besok;
                input.addEventListener('change', () => {
                    if (input.value < t || input.value > besok) input.value = t;
                });
            }

            /* ====== FETCH OPSI (customers, docks, back_nos) ====== */
            async function fetchAddOptions(line, planDate) {
                const url = new URL('/pulling/settings/reorder/options', window.location.origin);
                if (line) url.searchParams.set('line', line);
                if (planDate) url.searchParams.set('plan_date', planDate);
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Gagal load opsi');
                return res.json(); // { customers:[], docks:[], back_nos:[] }
            }

            function fillSelect(sel, options, placeholder) {
                sel.innerHTML = '';
                const opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = placeholder || '— pilih —';
                sel.appendChild(opt0);
                (options || []).forEach(v => {
                    if (!v) return;
                    const opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = v;
                    sel.appendChild(opt);
                });
            }

            /* ====== PROD TIME MAP (fallback) ====== */
            const PTMAP = (window.PROD_TIME_MAP) || {
                CI11: '00:34',
                CI12: '00:34',
                CI13: '00:40',
                CI14: '00:34',
                CI15: '00:39',
                CI16: '00:40',
                CI17: '00:40',
                CI18: '00:40',
                CI19: '00:37',
                D403: '00:40',
                D111: '00:34',
                D500: '00:37',
            };

            /* ====== ITEM ROW (per Back No) ====== */
            function createItemRow(backNoOptions) {
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-end item-line border rounded p-2 mb-2';
                row.innerHTML = `
      <div class="col-md-3">
        <label class="form-label">Back No</label>
        <select class="form-control back-no" required>
          <option value="">— pilih back no —</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Order Qty</label>
        <input type="number" class="form-control qty" min="1" step="1" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Cycle Time (mm:ss)</label>
        <input type="text" class="form-control prod-time" placeholder="00:34" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Cycle</label>
        <input type="number" class="form-control cycle" min="0" step="1" value="0">
      </div>
      <div class="col-md-2">
        <label class="form-label">Working Start</label>
        <input type="time" class="form-control working-start" placeholder="auto">
      </div>
      <div class="col-md-1 text-end">
        <button type="button" class="btn btn-outline-danger btn-sm btnDelRow" title="Hapus baris">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    `;
                // options
                const sel = row.querySelector('.back-no');
                fillSelect(sel, backNoOptions || [], '— pilih back no —');
                // auto prod_time dari mapping
                const pt = row.querySelector('.prod-time');
                sel.addEventListener('change', e => {
                    const key = String(e.target.value || '').trim().toUpperCase();
                    if (PTMAP[key] && !pt.value) pt.value = PTMAP[key];
                });
                // hapus baris
                row.querySelector('.btnDelRow').addEventListener('click', () => row.remove());
                return row;
            }

            /* ====== DN GROUP (berisi DN header + repeater back_no) ====== */
            let dnIndex = 0;

            function createDnGroup(backNoOptions, {
                defaultDate,
                defaultTime
            }) {
                dnIndex++;
                const group = document.createElement('div');
                group.className = 'dn-group card mb-3';
                group.innerHTML = `
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">DN Number</label>
            <input type="text" class="form-control dn-number" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Delivery Date</label>
            <input type="date" class="form-control delivery-date" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Delivery Time</label>
            <input type="time" class="form-control delivery-time" required>
          </div>
          <div class="col-md-1 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm btnDelGroup" title="Hapus DN">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-3">
          <h6 class="m-0">Model / Back No</h6>
          <button class="btn btn-sm btn-outline-primary btnAddRow" type="button">
            <i class="fas fa-plus me-1"></i> Add Model
          </button>
        </div>
        <div class="itemsRepeater mt-2"></div>
      </div>
    `;

                // set default date/time + enforce hanya hari ini/besok
                const dInput = group.querySelector('.delivery-date');
                const tInput = group.querySelector('.delivery-time');
                enforceDeliveryDateTodayBesok(dInput);
                dInput.value = defaultDate;
                tInput.value = defaultTime;

                // row pertama
                const repeater = group.querySelector('.itemsRepeater');
                repeater.appendChild(createItemRow(backNoOptions));

                // tambah row di group ini
                group.querySelector('.btnAddRow').addEventListener('click', () => {
                    repeater.appendChild(createItemRow(backNoOptions));
                });

                // hapus group
                group.querySelector('.btnDelGroup').addEventListener('click', () => {
                    const groups = document.querySelectorAll('#dnGroups .dn-group');
                    if (groups.length <= 1) {
                        alert('Minimal 1 DN.');
                        return;
                    }
                    group.remove();
                });

                return group;
            }

            /* ====== OPEN MODAL ====== */
            let addModal;
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('openAddModal')?.addEventListener('click', async () => {
                    const form = document.getElementById('addItemForm');
                    form.reset();

                    // hidden dari header utama
                    const line = document.getElementById('reorderLine')?.value || '';
                    const planDate = document.getElementById('reorderDate')?.value || todayYMD();
                    form.elements['plan_date'].value = planDate;
                    form.elements['line'].value = line;

                    // default time = +1 menit dari jam terakhir visible list
                    const lastVisibleTime = (() => {
                        const rows = document.querySelectorAll(
                            '#itemsContainer .item-row[data-id]');
                        if (!rows.length) return '06:00';
                        const last = rows[rows.length - 1];
                        return (last.getAttribute('data-delivery') || last.querySelector(
                            '[data-col="delivery"]')?.textContent || '06:00').slice(0,
                            5);
                    })();
                    const defaultTime = plusOneMinute(lastVisibleTime);

                    // isi selects customer & dock, siapkan options back_no
                    const custSel = form.elements['customer'];
                    const dockSel = form.elements['dock'];
                    const dnWrap = document.getElementById('dnGroups');
                    dnWrap.innerHTML = '';

                    fillSelect(custSel, [], 'Memuat...');
                    fillSelect(dockSel, [], 'Memuat...');
                    let backNoOptions = [];

                    try {
                        const json = await fetchAddOptions(line, planDate);
                        fillSelect(custSel, json.customers || [], '— pilih customer —');
                        fillSelect(dockSel, json.docks || ['STR', 'EXP', '6I', 'OTHERS'],
                            '— pilih dock —');
                        backNoOptions = json.back_nos || [];
                    } catch (e) {
                        fillSelect(custSel, [], '— pilih customer —');
                        fillSelect(dockSel, ['STR', 'EXP', '6I', 'OTHERS'], '— pilih dock —');
                        backNoOptions = (line === 'AS003') ? ['CI11', 'CI12', 'CI13', 'CI14',
                            'CI17', 'CI18', 'D403', 'D111'
                        ] : ['CI15', 'CI16', 'CI19', 'D500'];
                        console.warn(e);
                    }

                    // default date untuk group pertama: pakai planDate kalau hari ini/besok, selain itu fallback ke hari ini
                    const tdy = todayYMD();
                    const besok = addDaysYMD(tdy, 1);
                    const defaultDate = ([tdy, besok].includes(planDate) ? planDate : tdy);

                    // buat 1 DN group awal
                    dnWrap.appendChild(createDnGroup(backNoOptions, {
                        defaultDate,
                        defaultTime
                    }));

                    // tombol tambah DN
                    document.getElementById('btnAddDnGroup').onclick = () => {
                        // tiap DN baru default time tetap +1 menit dari lastVisibleTime (biar user atur sendiri)
                        dnWrap.appendChild(createDnGroup(backNoOptions, {
                            defaultDate,
                            defaultTime
                        }));
                    };

                    addModal ??= new bootstrap.Modal(document.getElementById('addItemModal'));
                    addModal.show();
                });

                /* ====== SUBMIT (serial per ROW across all DN) ====== */
                document.getElementById('submitAddItem')?.addEventListener('click', async () => {
                    const form = document.getElementById('addItemForm');
                    const btn = document.getElementById('submitAddItem');

                    // validasi header
                    const headerReq = ['customer', 'dock', 'plan_date', 'line'];
                    for (const n of headerReq) {
                        const el = form.elements[n];
                        if (!el || !el.value) {
                            alert(`Field "${n.replace('_',' ').toUpperCase()}" wajib diisi`);
                            return;
                        }
                    }

                    const dnGroups = [...document.querySelectorAll('#dnGroups .dn-group')];
                    if (!dnGroups.length) {
                        alert('Tambah minimal 1 DN.');
                        return;
                    }

                    const base = {
                        line: form.elements['line'].value,
                        plan_date: form.elements['plan_date'].value,
                        customer: form.elements['customer'].value,
                        dock: form.elements['dock'].value,
                    };

                    // bangun semua payload per model (row) di setiap DN
                    const payloads = [];
                    for (const g of dnGroups) {
                        const dn_number = g.querySelector('.dn-number')?.value?.trim();
                        const delivery_date = g.querySelector('.delivery-date')?.value;
                        const delivery_time = g.querySelector('.delivery-time')?.value;
                        if (!dn_number || !delivery_date || !delivery_time) {
                            alert(
                                'DN Number, Delivery Date, dan Delivery Time wajib diisi pada setiap DN.'
                            );
                            return;
                        }

                        const rows = [...g.querySelectorAll('.itemsRepeater .item-line')];
                        if (!rows.length) {
                            alert(`DN ${dn_number}: minimal 1 model/back no.`);
                            return;
                        }

                        for (const r of rows) {
                            const back_no = r.querySelector('.back-no')?.value || '';
                            const order_qty = parseInt(r.querySelector('.qty')?.value || '0', 10);
                            const prod_time = (r.querySelector('.prod-time')?.value || '').trim();
                            const cycle = parseInt(r.querySelector('.cycle')?.value || '0', 10);
                            const working_start = r.querySelector('.working-start')?.value || '';

                            if (!back_no || !order_qty || !prod_time) {
                                alert(
                                    `DN ${dn_number}: Pastikan setiap baris memiliki Back No, Order Qty, dan Cycle Time.`
                                );
                                return;
                            }
                            if (!/^\d{1,2}:[0-5]\d$/.test(prod_time)) {
                                alert(
                                    `DN ${dn_number}: Format Cycle Time salah pada back no ${back_no} (contoh 00:34)`
                                );
                                return;
                            }

                            payloads.push({
                                ...base,
                                dn_number,
                                delivery_date,
                                delivery_time,
                                back_no,
                                order_qty,
                                prod_time,
                                cycle,
                                working_start
                            });
                        }
                    }

                    // kirim serial → /pulling/settings/reorder/add
                    try {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

                        let lastResponseData = null;
                        for (let i = 0; i < payloads.length; i++) {
                            const res = await fetch('/pulling/settings/reorder/add', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.content
                                },
                                body: JSON.stringify(payloads[i])
                            });
                            const json = await res.json().catch(() => ({
                                success: false,
                                message: 'Non-JSON response'
                            }));
                            if (!res.ok || json.success === false) {
                                throw new Error(
                                    `Gagal simpan item ${i+1} (DN ${payloads[i].dn_number}, Back ${payloads[i].back_no}): ${json.message || res.statusText}`
                                );
                            }
                            lastResponseData = json.data || lastResponseData;
                        }

                        // refresh list
                        if (Array.isArray(lastResponseData) && typeof window
                            .renderItemsFromServer === 'function') {
                            window.renderItemsFromServer(lastResponseData);
                        } else if (typeof window.loadProductionItems === 'function') {
                            window.loadProductionItems();
                        }

                        addModal?.hide();
                        alert(
                            `Berhasil menambahkan ${payloads.length} item dari ${dnGroups.length} DN.`
                        );
                    } catch (e) {
                        console.error(e);
                        alert(e.message || 'Gagal menambah item.');
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-plus me-2"></i> Tambah';
                    }
                });
            });
        })();
    </script>
@endpush
