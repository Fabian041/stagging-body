
(function() {
    if (window.$u) return;

    const normLabel = s => String(s || '').replace(/\s+/g, ' ').trim().toLowerCase();
    const normUpper = s => String(s || '').trim().toUpperCase();
    const int = v => {
        const n = parseInt(String(v ?? '').replace(/[^\d-]/g, ''), 10);
        return isNaN(n) ? 0 : n;
    };
    const getCellByLabel = (row, wanted) => {
        const wl = normLabel(wanted);
        const tds = Array.from(row?.children || []);
        for (const c of tds)
            if (normLabel(c.getAttribute('data-label')) === wl) return c;
        for (const c of tds) {
            const l = normLabel(c.getAttribute('data-label'));
            if (l && (l.includes(wl) || wl.includes(l))) return c;
        }
        return null;
    };

    // --- time & date helpers ---
    const timeToMinutes = txt => {
        if (!txt) return null;
        const m = String(txt).trim().match(/^(\d{1,2})\s*:\s*(\d{2})/);
        if (!m) {
            const h = parseInt(String(txt).trim().match(/^(\d{1,2})/)?.[1] ?? '', 10);
            return isNaN(h) ? null : h * 60;
        }
        const hh = parseInt(m[1], 10),
            mm = parseInt(m[2], 10);
        if (isNaN(hh) || isNaN(mm)) return null;
        return hh * 60 + mm;
    };
    const getCurrentISO = () =>
        (document.querySelector('input[name="date"]')?.value || new Date().toISOString()).slice(0, 10);
    const isoAddDays = (iso, days) => {
        const d = new Date(iso + 'T00:00:00');
        d.setDate(d.getDate() + (days | 0));
        return d.toISOString().slice(0, 10);
    };
    // Convert "MM/DD" (dari tabel) -> "YYYY-MM-DD" (ambil tahun dari current date)
    const mdToISO = (mdText, refISO = getCurrentISO()) => {
        if (!mdText) return null;
        const m = String(mdText).trim().match(/^(\d{1,2})\s*\/\s*(\d{1,2})/);
        if (!m) return null;
        const [, MMs, DDs] = m;
        const MM = parseInt(MMs, 10),
            DD = parseInt(DDs, 10);
        if (isNaN(MM) || isNaN(DD)) return null;
        const ref = new Date(refISO + 'T00:00:00');
        const y = ref.getFullYear();
        const dSame = new Date(y, MM - 1, DD);
        return dSame.toISOString().slice(0, 10);
    };
    // Klasifikasi shift sesuai aturan 09:40 + tanggal
    const toShiftByDateTime = (currentISO, deliveryISO, timeText) => {
        if (!currentISO || !deliveryISO || !timeText) return 'other';
        const mins = timeToMinutes(timeText);
        if (mins == null) return 'other';
        const THRESH = 9 * 60 + 40; // 09:40
        const nextISO = isoAddDays(currentISO, 1);
        if (deliveryISO === currentISO && mins >= THRESH) return 'morning';
        if (deliveryISO === nextISO && mins < THRESH) return 'night';
        return 'other';
    };

    // (dipakai di tempat lain – biarkan)
    const hourFromText = txt => {
        if (!txt) return null;
        const m = String(txt).trim().match(/^(\d{1,2})/);
        if (!m) return null;
        const h = parseInt(m[1], 10);
        return isNaN(h) ? null : h;
    };
    const canonicalBackNoSplit = s => String(s || '').toUpperCase()
        .replace(/\s*\(C\d(?:[–-])?\d\)\s*$/, '').trim();

    window.$u = {
        normLabel,
        normUpper,
        int,
        getCellByLabel,
        // baru:
        timeToMinutes,
        getCurrentISO,
        isoAddDays,
        mdToISO,
        toShiftByDateTime,
        // legacy:
        hourFromText,
        canonicalBackNoSplit
    };
})();


(function themeInit() {
    const key = 'pulling_theme';
    const el = document.documentElement;
    const apply = mode => {
        el.setAttribute('data-theme', mode);
        const btn = document.getElementById('themeToggle');
        if (!btn) return;
        const icon = btn.querySelector('i');
        const label = btn.querySelector('span');
        if (mode === 'dark') {
            if (icon) icon.className = 'far fa-sun me-1';
            if (label) label.textContent = 'Light';
        } else {
            if (icon) icon.className = 'far fa-moon me-1';
            if (label) label.textContent = 'Dark';
        }
    };
    const saved = localStorage.getItem(key);
    apply(saved || (window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ? 'dark' : 'light'));
    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const next = (el.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
        localStorage.setItem(key, next);
        apply(next);
    });
})();


const COL_ORDER = [
    'Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute',
    'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time', 'Delivery Date',
    'Balance Time'
];

class ProductionPlanSSEClient {
    constructor() {
        this.eventSource = null;
        this.statusElement = null;
        this.currentDate = this.getCurrentDate();
        this.highlightTimeouts = new Set();
        this.originalOrder = new Map();
        this.orderRestoreTimeouts = new Map();
        this.summaries = {};
        this.HIGHLIGHT_DURATION_MS = 40000;
        this.init();
    }

    static normLabel = $u.normLabel;
    static colOrder = COL_ORDER.map($u.normLabel);

    init() {
        this.createStatusIndicator();
        this.addFlipStyles();
        this.connect();
        this.setupDateChangeListener();
        this.setupErrorHandling();
        this.storeOriginalOrder();

        this.AS003 = document.querySelector('[data-toggle-table="AS003"]');
        this.AS004 = document.querySelector('[data-toggle-table="AS004"]');

        this.shelves = {};
        if (this.AS003) this.shelves.AS003 = new PinnedShelf(this.AS003);
        if (this.AS004) this.shelves.AS004 = new PinnedShelf(this.AS004);


        this.prefillRawAttrs(this.AS003);
        this.prefillRawAttrs(this.AS004);

        this.buildSummaries();

        if (window.bootstrap?.Tooltip) {
            document.querySelectorAll('[title]').forEach(el => {
                el.setAttribute('data-bs-toggle', 'tooltip');
                try {
                    new bootstrap.Tooltip(el);
                } catch {}
            });
        }
    }

    _cell(row, wanted) {
        return $u.getCellByLabel(row, wanted);
    }

    prefillRawAttrs(container) {
        if (!container) return;
        container.querySelectorAll('tbody tr').forEach(row => {
            const bnTd = this._cell(row, 'Back No');
            const bnEl = bnTd?.querySelector('.flip') || bnTd;
            if (bnEl && !bnEl.dataset.backnoRaw) bnEl.dataset.backnoRaw = (bnEl.textContent || '')
                .trim();

            const odTd = this._cell(row, 'Order');
            const odEl = odTd?.querySelector('.flip') || odTd;
            if (odEl && !odEl.dataset.orderRaw) odEl.dataset.orderRaw = String($u.int(odEl
                .textContent || '0'));
        });
    }

    _getBackNo(row) {
        const td = this._cell(row, 'Back No');
        const el = td?.querySelector('.flip') || td;
        let val = (el?.dataset?.backnoAlias || el?.dataset?.backnoRaw || el?.textContent || '').trim();
        if (val) return val.toUpperCase();
        const text = (row.textContent || '').toUpperCase();
        const m = text.match(/\b(?:D\d{2,4}|CI\d{2,4})\b/);
        return m ? m[0] : '';
    }

    _getOrder(row) {
        const td = this._cell(row, 'Order');
        const el = td?.querySelector('.flip') || td;
        if (!el) return 0;
        const raw = parseInt(el.dataset?.orderRaw || '', 10);
        return isNaN(raw) ? $u.int(el.textContent || '0') : raw;
    }

    _getDP(row) {
        return $u.int(row?.querySelector('[data-type="direct-pulling"]')?.textContent);
    }
    _getSC(row) {
        return $u.int(row?.querySelector('[data-type="stock-chute"]')?.textContent);
    }

    _getId(row) {
        const el = row?.querySelector('[data-type="direct-pulling"]') || row?.querySelector(
            '[data-type="stock-chute"]');
        return el ? el.getAttribute('data-item-id') : null;
    }

    _getCycle(row) {
        const td = this._cell(row, 'Cycle');
        const m = (td?.textContent || '').trim().match(/\d+/);
        const n = m ? parseInt(m[0], 10) : NaN;
        return isNaN(n) ? null : (((n - 1) % 8) + 1);
    }

    isGroupStart(row) {
        return row && row.style.display !== 'none' && !!row.querySelector('[rowspan]');
    }

    getGroupRowsFrom(startRow) {
        const rows = [startRow];
        let p = startRow;
        while (p?.nextElementSibling && !this.isGroupStart(p.nextElementSibling)) {
            p = p.nextElementSibling;
            rows.push(p);
        }
        return rows;
    }

    _purgePinnedSummaries(container) {
        const tbody = container?.querySelector('tbody');
        if (!tbody) return;
        tbody.querySelectorAll('tr[data-summary-row="1"]').forEach(n => n.remove());
    }

    _removeSummaryRowIfExists(tbody, label) {
        tbody.querySelectorAll(`tr[data-summary-row="1"][data-summary-label="${label}"]`).forEach(n => n
            .remove());
    }

    recalcRowspans(container) {
        const tbody = container?.querySelector('tbody');
        if (!tbody) return;
        Array.from(tbody.querySelectorAll('tr')).forEach(row => {
            if (!this.isGroupStart(row)) return;
            const groupRows = this.getGroupRowsFrom(row);
            const visible = Math.max(1, groupRows.filter(r => r.style.display !== 'none').length);
            row.querySelectorAll('[rowspan]').forEach(td => td.rowSpan = visible);
        });
    }

    _indexByLabel(td) {
        const lbl = ProductionPlanSSEClient.normLabel(td?.getAttribute('data-label'));
        const idx = ProductionPlanSSEClient.colOrder.indexOf(lbl);
        return idx >= 0 ? idx : (td?.cellIndex ?? 9999);
    }

    _moveRowspanCellsTo(startRow, hostRow) {
        if (!startRow || !hostRow || hostRow === startRow) return;
        hostRow.querySelectorAll('[data-cloned-header]').forEach(n => n.remove());
        const startCells = Array.from(startRow.children)
            .filter(td => td.hasAttribute('rowspan'))
            .sort((a, b) => this._indexByLabel(a) - this._indexByLabel(b));
        startCells.forEach(td => {
            const clone = td.cloneNode(true);
            clone.setAttribute('rowspan', 1);
            clone.setAttribute('data-cloned-header', '1');
            const wantIdx = this._indexByLabel(td);
            let ref = null;
            for (const ex of Array.from(hostRow.children)) {
                if (this._indexByLabel(ex) > wantIdx) {
                    ref = ex;
                    break;
                }
            }
            hostRow.insertBefore(clone, ref);
        });
        const tbody = startRow.parentElement;
        if (tbody && hostRow.previousElementSibling !== startRow) tbody.insertBefore(hostRow, startRow);
        Array.from(startRow.querySelectorAll('[rowspan]')).forEach(td => td.removeAttribute('rowspan'));
        startRow.style.display = 'none';
    }

    buildSummaries() {
        Object.values(this.summaries).flat().forEach(s => s?.row?.remove?.());
        this.summaries = {};

        if (this.AS003) {
            this._purgePinnedSummaries(this.AS003);
            this.summaries.AS003 = this._extractAndPinSummaryCI12Split({
                container: this.AS003,
                targets: ['D111', 'CI12'],
                baseLabel: 'CI12'
            });
        }
        if (this.AS004) {
            this._purgePinnedSummaries(this.AS004);
            const one = this._extractAndPinSummaryGeneral({
                container: this.AS004,
                targets: ['D500', 'CI19'],
                label: 'CI19'
            });
            this.summaries.AS004 = one ? [one] : [];
        }
    }

    _extractAndPinSummaryGeneral({
        container,
        targets,
        label
    }) {
        const tgtSet = new Set(targets.map(t => String(t).toUpperCase()));
        const tbody = container?.querySelector('tbody');
        if (!tbody) return null;

        const summary = {
            row: null,
            totals: {
                order: 0,
                dp: 0,
                sc: 0
            },
            ids: new Map()
        };

        const allRows = Array.from(tbody.querySelectorAll('tr'));

        // Kelompokkan per group (rowspan)
        let i = 0,
            groups = [];
        while (i < allRows.length) {
            const start = allRows[i];
            const rs = this.isGroupStart(start) ?
                parseInt(start.querySelector('[rowspan]')?.getAttribute('rowspan') || '1', 10) :
                1;
            const g = [start];
            for (let k = 1; k < rs && (i + k) < allRows.length; k++) g.push(allRows[i + k]);
            groups.push(g);
            i += Math.max(1, rs);
        }

        const customerBag = [];
        groups.forEach(groupRows => {
            const startRow = groupRows[0];
            const matches = groupRows.filter(r => {
                const back = this._getBackNo(r);
                const dock = this._normalize(this._cellText(r, 'Dock')); // sudah ada helpernya
                return tgtSet.has(back) && dock !== 'STR';
            });
            if (!matches.length) return;

            const custTd = this._cell(startRow, 'Customer');
            const custText = (custTd?.querySelector('.flip')?.textContent || custTd?.textContent || '')
                .trim();
            if (custText) customerBag.push(custText);

            matches.forEach(r => {
                const id = this._getId(r),
                    dp = this._getDP(r),
                    sc = this._getSC(r),
                    od = this._getOrder(r);
                summary.totals.dp += dp;
                summary.totals.sc += sc;
                summary.totals.order += od;
                if (id) summary.ids.set(id, {
                    dp,
                    sc,
                    order: od
                });
            });

            const keepRows = groupRows.filter(r => !matches.includes(r));
            if (!keepRows.length) {
                groupRows.forEach(r => r.remove());
            } else {
                if (matches.includes(startRow)) this._moveRowspanCellsTo(startRow, keepRows[0]);
                matches.forEach(r => {
                    if (r !== startRow) r.remove();
                });
            }
        });

        let customerText = '--';
        if (customerBag.length) {
            const freq = customerBag.reduce((m, s) => (m[s] = (m[s] || 0) + 1, m), {});
            customerText = Object.entries(freq).sort((a, b) => b[1] - a[1])[0][0];
        }

        if (summary.totals.order + summary.totals.dp + summary.totals.sc > 0) {
            this._removeSummaryRowIfExists(tbody, label);
            summary.row = this._createSummaryRow({
                label,
                totals: summary.totals,
                customerText
            });

            // === PIN DI PALING BAWAH ===
            tbody.appendChild(summary.row);
        }

        this.recalcRowspans(container);
        return summary;
    }


    _normalize(s) {
        return String(s || '').replace(/\s+/g, ' ').trim().toUpperCase();
    }
    _cellText(row, label) {
        const td = this._cell(row, label);
        const el = td?.querySelector('.flip') || td;
        return (el?.textContent || '').trim();
    }
    _findAnchorRow(container, {
        customer,
        dock,
        cycle
    }) {
        const tbody = container?.querySelector('tbody');
        if (!tbody) return null;
        const wantCust = this._normalize(customer),
            wantDock = this._normalize(dock),
            wantCycle = Number(cycle);
        for (const tr of Array.from(tbody.querySelectorAll('tr'))) {
            if (!this.isGroupStart(tr)) continue;
            const custTxt = this._normalize(this._cellText(tr, 'Customer'));
            const dockTxt = this._normalize(this._cellText(tr, 'Dock'));
            const cyc = this._getCycle(tr);
            if (custTxt.includes(wantCust) && dockTxt.includes(wantDock) && cyc === wantCycle) return tr;
        }
        return null;
    }

    _extractAndPinSummaryCI12Split({
        container,
        targets,
        baseLabel
    }) {
        const tgtSet = new Set(targets.map(t => String(t).toUpperCase()));
        const tbody = container?.querySelector('tbody');
        const result = [];
        if (!tbody) return result;

        const S47 = {
            row: null,
            totals: {
                order: 0,
                dp: 0,
                sc: 0
            },
            ids: new Map(),
            label: `${baseLabel} (C4–7)`,
            customers: []
        };
        const S83 = {
            row: null,
            totals: {
                order: 0,
                dp: 0,
                sc: 0
            },
            ids: new Map(),
            label: `${baseLabel} (C8–3)`,
            customers: []
        };

        const allRows = Array.from(tbody.querySelectorAll('tr'));
        let i = 0,
            groups = [];
        while (i < allRows.length) {
            const start = allRows[i];
            const rs = this.isGroupStart(start) ? parseInt(start.querySelector('[rowspan]')?.getAttribute(
                'rowspan') || '1', 10) : 1;
            const g = [start];
            for (let k = 1; k < rs && (i + k) < allRows.length; k++) g.push(allRows[i + k]);
            groups.push(g);
            i += Math.max(1, rs);
        }

        groups.forEach(groupRows => {
            const startRow = groupRows[0];
            const matchesAll = groupRows.filter(r => {
                const back = this._getBackNo(r);
                const dock = this._normalize(this._cellText(r, 'Dock'));
                return tgtSet.has(back) && dock !== 'STR';
            });
            if (!matchesAll.length) return;

            const custTd = this._cell(startRow, 'Customer');
            const custText = (custTd?.querySelector('.flip')?.textContent || custTd?.textContent || '')
                .trim();
            if (custText) {
                S47.customers.push(custText);
                S83.customers.push(custText);
            }

            const in47 = [],
                in83 = [];
            matchesAll.forEach(r => {
                const cyc = this._getCycle(r);
                if (cyc == null) in83.push(r);
                else if (cyc >= 4 && cyc <= 7) in47.push(r);
                else in83.push(r);
            });

            const collect = (bucket, rows) => {
                rows.forEach(r => {
                    const id = this._getId(r),
                        dp = this._getDP(r),
                        sc = this._getSC(r),
                        od = this._getOrder(r);
                    bucket.totals.dp += dp;
                    bucket.totals.sc += sc;
                    bucket.totals.order += od;
                    if (id) bucket.ids.set(id, {
                        dp,
                        sc,
                        order: od
                    });
                });
            };
            collect(S47, in47);
            collect(S83, in83);

            const keepRows = groupRows.filter(r => !matchesAll.includes(r));
            if (!keepRows.length) groupRows.forEach(r => r.remove());
            else {
                if (matchesAll.includes(startRow)) this._moveRowspanCellsTo(startRow, keepRows[0]);
                matchesAll.forEach(r => {
                    if (r !== startRow) r.remove();
                });
            }
        });

        const modeOf = arr => {
            if (!arr.length) return '--';
            const freq = arr.reduce((m, s) => (m[s] = (m[s] || 0) + 1, m), {});
            return Object.entries(freq).sort((a, b) => b[1] - a[1])[0][0];
        };
        const cust47 = modeOf(S47.customers);
        const cust83 = modeOf(S83.customers);

        const anchor47 = this._findAnchorRow(container, {
            customer: 'TMMIN KARAWANG PLANT 3',
            dock: '6I',
            cycle: 7
        });
        const anchor83 = this._findAnchorRow(container, {
            customer: 'ADM ENGINE PLANT',
            dock: 'EXP',
            cycle: 1
        });

        if (S47.totals.order + S47.totals.dp + S47.totals.sc > 0) {
            this._removeSummaryRowIfExists(tbody, S47.label);
            S47.row = this._createSummaryRow({
                label: S47.label,
                totals: S47.totals,
                customerText: cust47
            });
            tbody.insertBefore(S47.row, anchor47 || tbody.firstChild || null);
            result.push(S47);
        }
        if (S83.totals.order + S83.totals.dp + S83.totals.sc > 0) {
            this._removeSummaryRowIfExists(tbody, S83.label);
            S83.row = this._createSummaryRow({
                label: S83.label,
                totals: S83.totals,
                customerText: cust83
            });
            if (anchor83) tbody.insertBefore(S83.row, anchor83);
            else tbody.appendChild(S83.row);
            result.push(S83);
        }

        this.recalcRowspans(container);
        return result;
    }

    _createSummaryRow({
        label,
        totals,
        customerText = '--'
    }) {
        const tr = document.createElement('tr');
        tr.className = 'fw-bold';
        tr.setAttribute('data-summary-row', '1');
        tr.setAttribute('data-summary-label', label);

        const pct = Math.min(100, Math.round(((totals.dp + totals.sc) / Math.max(1, totals.order)) * 100));
        const td = (text, attrs = {}) => {
            const el = document.createElement('td');
            if (text != null) el.innerHTML = `<span class="flip">${text}</span>`;
            for (const k in attrs) el.setAttribute(k, attrs[k]);
            return el;
        };

        tr.appendChild(td(customerText, {
            'data-label': 'Customer',
            rowspan: '1'
        }));
        tr.appendChild(td('--', {
            'data-label': 'Dock',
            rowspan: '1'
        }));
        tr.appendChild(td('--', {
            'data-label': 'Cycle',
            rowspan: '1'
        }));
        tr.appendChild(td(label, {
            'data-label': 'Back No'
        }));
        tr.appendChild(td(totals.order.toLocaleString('id-ID'), {
            'data-label': 'Order'
        }));

        const dpCell = document.createElement('td');
        dpCell.setAttribute('data-label', 'Direct Pulling');
        dpCell.innerHTML = `
<div class="qty-progress" title="DP ${totals.dp} / ${totals.order}">
<div class="bar"><i style="width:${Math.min(100, Math.round((totals.dp/Math.max(1,totals.order))*100))}%"></i></div>
<span class="val"><span class="flip" data-summary-dp>${totals.dp}</span></span>
</div>`;
        tr.appendChild(dpCell);

        const scCell = document.createElement('td');
        scCell.setAttribute('data-label', 'Stock Chute');
        scCell.innerHTML = `
<div class="qty-progress" title="SC ${totals.sc} / ${totals.order}">
<div class="bar"><i style="width:${Math.min(100, Math.round((totals.sc/Math.max(1,totals.order))*100))}%"></i></div>
<span class="val"><span class="flip" data-summary-sc>${totals.sc}</span></span>
</div>`;
        tr.appendChild(scCell);

        tr.appendChild(td('--', {
            'data-label': 'Cycle Time'
        }));
        tr.appendChild(td('--', {
            'data-label': 'Planning Start'
        }));
        tr.appendChild(td('--', {
            'data-label': 'Actual Start'
        }));
        tr.appendChild(td('<span class="text-warning">--</span>', {
            'data-label': 'Duration'
        }));

        const prog = document.createElement('td');
        prog.className = 'total-progress';
        prog.setAttribute('data-label', 'Progress');
        prog.innerHTML = `
<div class="qty-progress" title="DP+SC ${totals.dp + totals.sc} / ${totals.order} (${pct}%)">
<div class="bar"><i data-summary-totalbar style="width:${pct}%"></i></div>
<span class="val" data-summary-totalpct>${pct}%</span>
</div>`;
        tr.appendChild(prog);

        tr.appendChild(td('--', {
            'data-label': 'Delivery Time'
        }));
        tr.appendChild(td('--', {
            'data-label': 'Delivery Date'
        }));
        tr.appendChild(td('--', {
            'data-label': 'Balance Time'
        }));

        return tr;
    }

    _refreshSummaryRow(summary) {
        if (!summary?.row) return;
        const {
            order,
            dp,
            sc
        } = summary.totals;
        const pct = Math.min(100, Math.round(((dp + sc) / Math.max(1, order)) * 100));
        const dpSpan = summary.row.querySelector('[data-summary-dp]');
        const scSpan = summary.row.querySelector('[data-summary-sc]');
        const totalBar = summary.row.querySelector('[data-summary-totalbar]');
        const totalPct = summary.row.querySelector('[data-summary-totalpct]');
        const orderFlip = summary.row.querySelector('[data-label="Order"] .flip');
        if (dpSpan) dpSpan.textContent = dp;
        if (scSpan) scSpan.textContent = sc;
        if (orderFlip) orderFlip.textContent = order.toLocaleString('id-ID');
        if (totalBar) totalBar.style.width = pct + '%';
        if (totalPct) totalPct.textContent = pct + '%';
    }

    refreshSummaries() {
        Object.values(this.summaries).flat().forEach(s => this._refreshSummaryRow(s));
    }

    handleSSEPayload(data) {
        try {
            if (!data) return;
            if (data.date && data.date !== this.currentDate) return;

            const finish = (processed = 0) => {
            // cukup refresh yg tersentuh (lihat patch di handleUpdates)
            window.dispatchEvent(new CustomEvent('pulling:update', {
                detail: { date: this.currentDate, processed }
            }));
            this.updateConnectionStatus('connected');
            };

            // helper: proses array besar per-chunk agar UI tetap responsif
            const runBatched = (arr, size = 150) => {
            let i = 0, done = 0;
            const step = () => {
                const end = Math.min(i + size, arr.length);
                const slice = arr.slice(i, end);
                this.handleUpdates(slice);      // <- patch di bawah bikin ini efisien
                done += slice.length;
                i = end;
                if (i < arr.length) requestAnimationFrame(step);
                else finish(done);
            };
            requestAnimationFrame(step);
            };

            if (Array.isArray(data.batches) && data.batches.length) {
            // flaten batches dulu lalu proses batched
            const flat = data.batches.flatMap(ch => Array.isArray(ch) ? ch : Object.values(ch));
            runBatched(flat, 150);
            return;
            }

            if (Array.isArray(data.updates) && data.updates.length) {
            runBatched(data.updates, 150);
            }
        } catch {
            this.updateConnectionStatus('error', 'payload');
        }
    }

    storeOriginalOrder() {
        document.querySelectorAll('.tab-pane table tbody').forEach(tbody => {
            this.originalOrder.set(tbody, Array.from(tbody.querySelectorAll('tr')));
        });
    }

    getCurrentDate() {
        const inp = document.querySelector('input[name="date"]');
        return inp ? inp.value : new Date().toISOString().split('T')[0];
    }

    createStatusIndicator() {
        const el = document.createElement('div');
        el.id = 'sse-connection-status';
        el.textContent = '● Connecting to updates...';
        document.body.appendChild(el);
        this.statusElement = el;
    }

    addFlipStyles() {
        const st = document.createElement('style');
        st.textContent = `
    .flip{display:inline-block;transition:all .3s ease;transform-style:preserve-3d;transform-origin:bottom center;}
    .animate-flip{animation:flipAnimation .6s ease;}
    @keyframes flipAnimation{0%{transform:rotateX(0);opacity:1;}50%{transform:rotateX(90deg);opacity:0;}51%{transform:rotateX(-90deg);}100%{transform:rotateX(0);opacity:1;}}
    @keyframes continuousBlink{0%,100%{background-color:var(--highlight-color);}50%{background-color:var(--base-bg);}}
    .highlight-beep-direct{--highlight-color:var(--highlight-direct);--base-bg:var(--highlight-base);animation:continuousBlink 1s ease-in-out infinite;}
    .highlight-beep-stock{--highlight-color:var(--highlight-stock);--base-bg:var(--highlight-base);animation:continuousBlink 1s ease-in-out infinite;}
    .highlight-beep-direct td,.highlight-beep-stock td{background-color:inherit!important;}`;
        document.head.appendChild(st);
    }

    _restorePinnedRow(row) {
        const tbody = row?.parentElement;
        if (!tbody) return;
        Array.from(row.querySelectorAll('[data-cloned-header]')).forEach(n => n.remove());
        const ph = row._pinPlaceholder;
        if (ph && ph.parentNode === tbody) {
            tbody.insertBefore(row, ph);
            ph.remove();
            row._pinPlaceholder = null;
        } else {
            this._restoreOriginalOrder?.(tbody);
        }
        row.classList.remove('is-pinned');
        const container = tbody.closest('[data-toggle-table]') || tbody.closest('table')?.parentElement;
        this.recalcRowspans(container);
    }

    // REPLACE triggerHighlight di ProductionPlanSSEClient
    triggerHighlight(row, type = 'direct-pulling') {
        if (!row) return;
        const cls = (type === 'stock-chute') ? 'highlight-beep-stock' : 'highlight-beep-direct';
        row.classList.remove('highlight-beep-direct', 'highlight-beep-stock');
        void row.offsetWidth; // restart anim
        row.classList.add(cls);
        clearTimeout(row._blinkTimer);
        row._blinkTimer = setTimeout(() => row.classList.remove(cls), this.HIGHLIGHT_DURATION_MS);

        // kirim clone ke shelf sesuai line
        const lineKey = row.closest('[data-toggle-table]')?.getAttribute('data-toggle-table');
        if (lineKey && this.shelves?.[lineKey]) {
            this.shelves[lineKey].upsertFromRow(row);
        }
    }

    connect() {
        try {
            if (this.eventSource) this.eventSource.close();

            const url = `/stream/direct-pulling-updates?date=${this.currentDate}`;
            this.eventSource = new EventSource(url);
            this.updateConnectionStatus('connecting');

            // Open
            this.eventSource.onopen = () => this.updateConnectionStatus('connected');

            // Server says "hello"
            this.eventSource.addEventListener('connected', (e) => {
                // e.data: {message, clientId, date, timestamp}
                this.updateConnectionStatus('connected');
            });

            // Server mulai refetch dari external
            this.eventSource.addEventListener('refetching', (e) => {
                this.updateConnectionStatus('refetching');
            });

            // Selesai refetch (sukses / tidak)
            this.eventSource.addEventListener('refetched', (e) => {
                // balik ke normal; detail status bisa kamu pakai kalau mau
                this.updateConnectionStatus('connected');
            });

            // Update data utama
            this.eventSource.addEventListener('directPullingUpdate', (e) => {
                try {
                    const data = JSON.parse(e.data || '{}');
                    this.handleSSEPayload(data);
                    this.updateConnectionStatus('connected');
                } catch {
                    this.updateConnectionStatus('error', 'parse');
                }
            });

            // Server menutup koneksi (mis. rotasi 30 menit)
            this.eventSource.addEventListener('close', () => {
                this.updateConnectionStatus('disconnected');
                this.reconnect();
            });

            // Error jaringan / stream
            this.eventSource.onerror = () => {
                this.updateConnectionStatus('disconnected');
                this.reconnect();
            };
        } catch {
            this.updateConnectionStatus('error', 'EventSource');
        }
    }

    setupDateChangeListener() {
        const inp = document.querySelector('input[name="date"]');
        if (inp) inp.addEventListener('change', () => {
            this.currentDate = this.getCurrentDate();
            this.reconnect();
        });
    }

    updateConnectionStatus(status, msg = '') {
        const m = ({
            connecting:   { text: '● Connecting to updates...',       class: 'text-primary border bg-white' },
            connected:    { text: '● Live Updates Active',            class: 'text-success border bg-white' },
            refetching:   { text: '● Refetching data...',             class: 'text-info border bg-white' },
            disconnected: { text: '● Connection Lost',                class: 'text-danger border bg-white' },
            error:        { text: '● Update Error ' + msg,            class: 'text-warning border bg-white' }
        }[status]) || { text: '● Update Error', class: 'text-warning border bg-white' };

        if (this.statusElement) {
            this.statusElement.className = m.class;
            this.statusElement.textContent = m.text;
        }
    }

    reconnect() {
        if (this.eventSource) this.eventSource.close();
        setTimeout(() => this.connect(), 1500);
    }
    setupErrorHandling() {
        window.addEventListener('beforeunload', () => {
            try {
                this.eventSource?.close();
            } catch {}
        });
    }

    handleUpdates(updates) {
        const touched = new Set(); // kumpulkan summary yang berubah

        updates.forEach(item => {
            // update angka di summary (tanpa refresh tiap item)
            Object.values(this.summaries).flat().forEach(s => {
            if (!s?.ids) return;
            if (s.ids.has(String(item.id))) {
                const prev = s.ids.get(String(item.id)) || { dp: 0, sc: 0, order: 0 };
                const newDP = (item.direct_pulling_qty ?? prev.dp) | 0;
                const newSC = (item.stock_chute_qty ?? prev.sc) | 0;
                const newOD = (item.order_qty ?? prev.order) | 0;

                s.totals.dp    += (newDP - prev.dp);
                s.totals.sc    += (newSC - prev.sc);
                s.totals.order += (newOD - prev.order);

                s.ids.set(String(item.id), { dp: newDP, sc: newSC, order: newOD });
                touched.add(s);
            }
            });

            // update row DOM kalau ada
            const idSel = id => `[data-item-id="${id}"]`;
            if (
            document.querySelector(`${idSel(item.id)}[data-type="direct-pulling"]`) ||
            document.querySelector(`${idSel(item.id)}[data-type="stock-chute"]`)
            ) {
            this.updateQuantity(`${idSel(item.id)}[data-type="direct-pulling"]`, item.direct_pulling_qty, 'direct-pulling', item.order_qty);
            this.updateQuantity(`${idSel(item.id)}[data-type="stock-chute"]`, item.stock_chute_qty, 'stock-chute', item.order_qty);
            this.updateQuantity(`${idSel(item.id)}[data-type="actual_start"]`, item.actual_start, 'time');
            this.updateQuantity(`${idSel(item.id)}[data-type="end"]`, item.end, 'time');
            this.updateQuantity(`${idSel(item.id)}[data-type="balance"]`, item.balance, 'time');
            }
        });

        // refresh hanya summary yang berubah
        touched.forEach(s => this._refreshSummaryRow(s));
    }


    updateQuantity(selector, newValue, type, targetQty = null) {
        const els = document.querySelectorAll(selector);
        if (!els.length) return;

        els.forEach(el => {
            const cur = (el.textContent || '').trim();
            if (cur === String(newValue)) return; // no-op

            el.textContent = newValue ?? '';
            const td  = el.closest('td');
            const row = td?.parentElement;

            if (!isNaN(parseFloat(newValue))) this.updateCellStyle(td, parseFloat(newValue), type, targetQty);
            else this.updateCellStyle(td, null, type);

            const bar = td?.querySelector('.qty-progress .bar > i');
            if (bar && (type === 'direct-pulling' || type === 'stock-chute')) {
            const order = this._getOrder(row);
            const dp = $u.int(row.querySelector('[data-type="direct-pulling"]')?.textContent);
            const sc = $u.int(row.querySelector('[data-type="stock-chute"]')?.textContent);
            const val = (type === 'direct-pulling') ? dp : sc;
            const pct = Math.min(100, Math.round((val / Math.max(1, order)) * 100));
            bar.style.width = pct + '%';

            const totCell = row.querySelector('.total-progress');
            if (totCell) {
                const tBar = totCell.querySelector('.bar > i');
                const tPct = totCell.querySelector('.val');
                const totalVal = dp + sc;
                const totalPct = Math.min(100, Math.round((totalVal / Math.max(1, order)) * 100));
                if (tBar) tBar.style.width = totalPct + '%';
                if (tPct) tPct.textContent = totalPct + '%';
            }
            }

            if (row && (type === 'direct-pulling' || type === 'stock-chute')) this.triggerHighlight(row, type);

            const f = td?.querySelector('.flip');
            if (f) {
            f.classList.add('animate-flip');
            setTimeout(() => f.classList.remove('animate-flip'), 600);
            }
        });
    }

    updateCellStyle(cell, val, type, targetQty = null) {
        if (!cell || type === 'time') return;
        if (val === null) {
            cell.className = '';
            return;
        }
        let cls = 'fw-bold ';
        if (type === 'direct-pulling' || type === 'stock-chute') {
            if (targetQty !== null && !isNaN(targetQty)) cls += (val >= targetQty) ?
                'bg-success bg-opacity-75 text-white' : 'bg-warning bg-opacity-75';
            else cls += (val > 0) ? 'bg-success bg-opacity-25' : 'bg-warning bg-opacity-25';
        }
        cell.className = cls.trim();
    }

    _findGroupStartRow(row) {
        let p = row;
        while (p && !this.isGroupStart(p)) p = p.previousElementSibling;
        return (p && this.isGroupStart(p)) ? p : null;
    }

    _cloneRowspanCellsToRow(startRow, hostRow) {
        const hasByLabel = (r, lbl) => Array.from(r.children).some(td => ProductionPlanSSEClient.normLabel(td
            .getAttribute('data-label')) === ProductionPlanSSEClient.normLabel(lbl));
        const startCells = Array.from(startRow.children).filter(td => td.hasAttribute('rowspan'));
        startCells.forEach(src => {
            const lbl = src.getAttribute('data-label') || '';
            if (hasByLabel(hostRow, lbl)) return;
            const clone = src.cloneNode(true);
            clone.setAttribute('rowspan', 1);
            clone.setAttribute('data-cloned-header', '1');
            const wantIdx = this._indexByLabel(src);
            let ref = null;
            for (const ex of Array.from(hostRow.children)) {
                if (this._indexByLabel(ex) > wantIdx) {
                    ref = ex;
                    break;
                }
            }
            hostRow.insertBefore(clone, ref);
        });
    }

    _pinRowToTop(row) {
        const tbody = row?.parentElement;
        if (!tbody) return;
        if (!row._pinPlaceholder) {
            row._pinPlaceholder = document.createComment('pin-anchor');
            tbody.insertBefore(row._pinPlaceholder, row);
        }
        const startRow = this._findGroupStartRow(row);
        if (startRow) this._cloneRowspanCellsToRow(startRow, row);

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const firstNonSummary = rows.find(tr => tr.getAttribute('data-summary-row') !== '1' && tr !== row);
        if (firstNonSummary) tbody.insertBefore(row, firstNonSummary);
        else tbody.insertBefore(row, tbody.firstChild);
        row.classList.add('is-pinned');

        const container = tbody.closest('[data-toggle-table]') || tbody.closest('table')?.parentElement;
        this.recalcRowspans(container);
    }

    _restoreOriginalOrder(tbody) {
        const orig = this.originalOrder.get(tbody);
        if (!orig) return;
        Array.from(tbody.querySelectorAll('tr [data-cloned-header]')).forEach(n => n.remove());
        orig.forEach(r => {
            if (r && r.parentElement === tbody && r.getAttribute('data-summary-row') !== '1') tbody
                .appendChild(r);
        });
        const container = tbody.closest('[data-toggle-table]') || tbody.closest('table')?.parentElement;
        this.recalcRowspans(container);
    }

    updateAllInlineSums() {
        this.refreshSummaries();
    }
}

/* Boot */
document.addEventListener('DOMContentLoaded', () => {
    window.prodPlanSSE = new ProductionPlanSSEClient();
});

/* Navigasi tanggal */
function navigateDate(days) {
    const inp = document.querySelector('input[name="date"]');
    if (!inp) return;
    const d = new Date(inp.value);
    d.setDate(d.getDate() + days);
    inp.value = d.toISOString().split('T')[0];
    document.querySelector('form')?.submit();
}

function gotoToday() {
    const inp = document.querySelector('input[name="date"]');
    if (!inp) return;
    inp.value = new Date().toISOString().split('T')[0];
    document.querySelector('form')?.submit();
}

/* Bridge eksternal */
(function Bridge() {
    window.updateAllInlineSums = () => {
        window.prodPlanSSE?.updateAllInlineSums?.();
    };
})();


(function SafeColumnHideV5() {
    const STORAGE_PREFIX = 'hiddenCols_';
    const tableStates = new Map();
    let isProcessing = false;

    const CANON = [
        'Customer', 'Dock', 'Cycle', 'Back No', 'Order', 'Direct Pulling', 'Stock Chute',
        'Cycle Time', 'Planning Start', 'Actual Start', 'Duration', 'Progress', 'Delivery Time',
        'Delivery Date', 'Balance Time'
    ].map(s => s.toLowerCase());

    const GROUP_MAP = {
        'running qty': ['Direct Pulling', 'Stock Chute'],
        'working time': ['Planning Start', 'Actual Start', 'Duration', 'Progress']
    };

    if (!document.getElementById('colhide_label_style')) {
        const st = document.createElement('style');
        st.id = 'colhide_label_style';
        st.textContent = '.col-hidden{display:none!important}';
        document.head.appendChild(st);
    }

    const norm = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();
    const canonicalize = label => {
        const n = norm(label);
        const idx = CANON.indexOf(n);
        return idx >= 0 ? CANON[idx].replace(/\b\w/g, c => c.toUpperCase()) : (label || '').trim();
    };

    const readHiddenKeys = tableKey => {
        try {
            return new Set(JSON.parse(localStorage.getItem(STORAGE_PREFIX + tableKey) || '[]').map(
                canonicalize));
        } catch {
            return new Set();
        }
    };
    const saveHiddenKeys = (tableKey, set) => {
        try {
            localStorage.setItem(STORAGE_PREFIX + tableKey, JSON.stringify([...set]));
        } catch {}
    };

    function annotateHeader(container) {
        const thead = container.querySelector('thead');
        if (!thead) return {
            leafKeys: [],
            groupHeads: []
        };
        const rows = Array.from(thead.rows);
        const r0 = rows[0] || null;
        const r1 = rows[1] || null;
        const leafKeys = [];
        const groupHeads = [];
        if (!r0) return {
            leafKeys,
            groupHeads
        };

        let childIdx = 0;
        const r1cells = r1 ? Array.from(r1.cells) : [];
        Array.from(r0.cells).forEach(th => {
            const text = canonicalize(th.textContent);
            const ntext = norm(text);
            if ((th.rowSpan || 1) > 1 && (th.colSpan || 1) === 1) {
                const key = text;
                th.setAttribute('data-col-key', key);
                leafKeys.push(key);
            } else if ((th.colSpan || 1) > 1) {
                const span = th.colSpan,
                    kids = [];
                for (let i = 0; i < span; i++) {
                    const c = r1cells[childIdx++];
                    if (!c) continue;
                    const k = canonicalize(c.textContent);
                    c.setAttribute('data-col-key', k);
                    leafKeys.push(k);
                    kids.push(k);
                }
                const gChildren = (GROUP_MAP[ntext] || kids);
                th.setAttribute('data-col-group', gChildren.join('||'));
                groupHeads.push({
                    el: th,
                    children: gChildren
                });
            } else {
                const key = text;
                th.setAttribute('data-col-key', key);
                leafKeys.push(key);
            }
        });
        if (r1 && groupHeads.length === 0) r1cells.forEach(c => {
            const k = canonicalize(c.textContent);
            c.setAttribute('data-col-key', k);
            leafKeys.push(k);
        });
        return {
            leafKeys,
            groupHeads
        };
    }

    function applyHiding(container, hiddenKeys, headerInfo) {
        const {
            groupHeads
        } = headerInfo;
        container.querySelectorAll('tbody td[data-label]').forEach(td => {
            const key = canonicalize(td.getAttribute('data-label'));
            td.classList.toggle('col-hidden', hiddenKeys.has(key));
        });
        container.querySelectorAll('thead th[data-col-key]').forEach(th => {
            const key = th.getAttribute('data-col-key');
            th.classList.toggle('col-hidden', hiddenKeys.has(key));
        });
        groupHeads.forEach(g => {
            const visibleCount = g.children.reduce((n, k) => n + (hiddenKeys.has(canonicalize(k)) ? 0 :
                1), 0);
            if (visibleCount === 0) {
                g.el.classList.add('col-hidden');
                g.el.colSpan = 1;
            } else {
                g.el.classList.remove('col-hidden');
                g.el.colSpan = visibleCount;
            }
        });
    }

    document.querySelectorAll('[data-colpicker]').forEach(menu => {
        const tableKey = menu.getAttribute('data-colpicker');
        const container = document.querySelector(`[data-toggle-table="${tableKey}"]`);
        const table = container?.querySelector('table');
        if (!table) return;

        const headerInfo = annotateHeader(container);
        const hiddenKeys = readHiddenKeys(tableKey);
        tableStates.set(tableKey, {
            container,
            menu,
            headerInfo,
            hiddenKeys
        });

        menu.querySelectorAll('.column-check').forEach(cb => {
            let key = cb.dataset.key ? canonicalize(cb.dataset.key) : null;
            if (!key && cb.dataset.col != null) {
                const idx = parseInt(cb.dataset.col, 10);
                const k2 = headerInfo.leafKeys[idx];
                if (k2) key = canonicalize(k2);
            }
            if (!key) return;
            cb.checked = !hiddenKeys.has(key);
            cb.addEventListener('change', e => {
                e.stopPropagation();
                if (cb.checked) hiddenKeys.delete(key);
                else hiddenKeys.add(key);
                saveHiddenKeys(tableKey, hiddenKeys);
                applyHiding(container, hiddenKeys, headerInfo);
                window.prodPlanSSE?.recalcRowspans?.(container);
            });
        });
        applyHiding(container, hiddenKeys, headerInfo);
    });

    window.__colHideApplyAll = function() {
        if (isProcessing) return;
        isProcessing = true;
        try {
            tableStates.forEach((state, tableKey) => {
                const hiddenKeys = readHiddenKeys(tableKey);
                state.hiddenKeys = hiddenKeys;
                state.menu.querySelectorAll('.column-check').forEach(cb => {
                    let key = cb.dataset.key ? canonicalize(cb.dataset.key) : null;
                    if (!key && cb.dataset.col != null) {
                        const idx = parseInt(cb.dataset.col, 10);
                        const k2 = state.headerInfo.leafKeys[idx];
                        if (k2) key = canonicalize(k2);
                    }
                    if (!key) return;
                    cb.checked = !hiddenKeys.has(key);
                });
                applyHiding(state.container, hiddenKeys, state.headerInfo);
                window.prodPlanSSE?.recalcRowspans?.(state.container);
            });
        } finally {
            isProcessing = false;
        }
    };

    try {
        const observer = new MutationObserver(() => {
            if (!isProcessing) window.__colHideApplyAll();
        });
        document.querySelectorAll('[data-toggle-table]').forEach(container => {
            const tbody = container.querySelector('table tbody');
            if (tbody) observer.observe(tbody, {
                childList: true,
                subtree: false
            });
        });
    } catch {}
})();


(function BackNoRenamer() {
    const LS_KEY = 'backnoRenameMap';
    const loadMap = () => {
        try {
            return JSON.parse(localStorage.getItem(LS_KEY) || '{}');
        } catch {
            return {};
        }
    };
    const saveMap = map => {
        try {
            localStorage.setItem(LS_KEY, JSON.stringify(map));
        } catch {}
    };

    function applyMapToContainer(container, map) {
        if (!container) return;
        container.querySelectorAll('tbody tr').forEach(row => {
            const td = $u.getCellByLabel(row, 'Back No');
            if (!td) return;
            const el = td.querySelector('.flip') || td;
            const original = $u.normUpper(el.dataset.backnoRaw || el.textContent);
            const alias = map[original];
            if (alias) {
                el.dataset.backnoAlias = $u.normUpper(alias);
                (td.querySelector('.flip') || el).textContent = alias;
            } else if (el.dataset.backnoAlias) {
                (td.querySelector('.flip') || el).textContent = el.dataset.backnoRaw || el.textContent;
                delete el.dataset.backnoAlias;
            }
        });
    }

    function applyAll(map) {
        document.querySelectorAll('[data-toggle-table]').forEach(container => applyMapToContainer(container,
            map));
        document.querySelectorAll('[data-toggle-table]').forEach(c => window.prodPlanSSE?.recalcRowspans?.(c));
    }

    window.setBackNoRenameMap = function(map, {
        persist = true,
        applyNow = true
    } = {}) {
        const clean = {};
        Object.entries(map || {}).forEach(([k, v]) => {
            if (k && v) clean[$u.normUpper(k)] = $u.normUpper(v);
        });
        if (persist) saveMap(clean);
        if (applyNow) applyAll(clean);
        return clean;
    };

    window.renameBackNo = function(from, to, {
        persist = true,
        applyNow = true
    } = {}) {
        const map = loadMap();
        if (from && to) {
            map[$u.normUpper(from)] = $u.normUpper(to);
            if (persist) saveMap(map);
        }
        if (applyNow) applyAll(map);
        return map;
    };

    window.clearBackNoRenameMap = function({
        applyNow = true
    } = {}) {
        saveMap({});
        if (applyNow) applyAll({});
    };

    document.addEventListener('DOMContentLoaded', () => {
        const map = loadMap();
        if (Object.keys(map).length) applyAll(map);
    });
})();

/* default alias set */
setBackNoRenameMap({
    'D403': 'CI18',
    'D111': 'CI12',
    'D500': 'CI19'
});


(function FixShiftCardsV3() {
    if (window.__fixShiftCardsV3Installed) return;
    window.__fixShiftCardsV3Installed = true;

    const LINES = ['AS003', 'AS004'];
    const IDLOCALE = 'id-ID';
    const norm = $u.normUpper;
    const isSummaryRow = tr => tr?.getAttribute('data-summary-row') === '1';
    const getLineKeyOfRow = tr => tr?.closest('[data-toggle-table]')?.getAttribute('data-toggle-table') || '';

    function cellByLabel(row, wanted) {
        return $u.getCellByLabel(row, wanted);
    }

    function readSummaryLabel(tr) {
        return norm(tr?.getAttribute('data-summary-label') || tr?.getAttribute('data-summary-key') || tr?.textContent || '');
    }

    function readOrder(tr) {
        if (isSummaryRow(tr)) {
            const ds = tr.querySelector('[data-summary-order]');
            if (ds) return $u.int(ds.textContent);
            const td = cellByLabel(tr, 'Order');
            const el = td?.querySelector('.flip') || td;
            return $u.int(el?.textContent);
        }
        const td = cellByLabel(tr, 'Order');
        const el = td?.querySelector('.flip') || td;
        const raw = el?.dataset?.orderRaw;
        return raw != null && raw !== '' ? $u.int(raw) : $u.int(el?.textContent);
    }

    const readDP = tr =>
        isSummaryRow(tr)
            ? $u.int(tr.querySelector('[data-summary-dp]')?.textContent)
            : $u.int((tr.querySelector('[data-type="direct-pulling"]') || cellByLabel(tr, 'Direct Pulling')?.querySelector('.flip'))?.textContent);

    const readSC = tr =>
        isSummaryRow(tr)
            ? $u.int(tr.querySelector('[data-summary-sc]')?.textContent)
            : $u.int((tr.querySelector('[data-type="stock-chute"]') || cellByLabel(tr, 'Stock Chute')?.querySelector('.flip'))?.textContent);

    function readDeliveryHourForRow(row) {
        let r = row;
        while (r) {
            const td = cellByLabel(r, 'Delivery Time');
            if (td) return $u.hourFromText(td.textContent);
            const prev = r.previousElementSibling;
            if (!prev) break;
            r = prev;
        }
        return null;
    }

    function readHourFromRowTimes(row) {
        const asEl = row.querySelector('[data-type="actual_start"]');
        const psEl = row.querySelector('[data-type="start"]');
        let h = $u.hourFromText(asEl?.textContent || '') || $u.hourFromText(cellByLabel(row, 'Actual Start')?.textContent);
        if (h == null) {
            h = $u.hourFromText(psEl?.textContent || '') || $u.hourFromText(cellByLabel(row, 'Planning Start')?.textContent);
        }
        return h;
    }

    function specialSummaryShift(lineKey, row) {
        if (!isSummaryRow(row)) return null;
        const label = readSummaryLabel(row);
        if (lineKey === 'AS003') {
            if (/^CI12\b/i.test(label)) {
                if (/\bC\s*4\s*[–-]\s*7\b/i.test(label)) return 'morning';
                if (/\bC\s*8\s*[–-]\s*3\b/i.test(label)) return 'night';
            }
        }
        if (lineKey === 'AS004') {
            if (/\bCI19\b/i.test(label)) return 'morning';
        }
        return null;
    }

    function readDeliveryTimeTextForRow(row) {
        let r = row;
        while (r) {
            const td = cellByLabel(r, 'Delivery Time');
            if (td) {
                const t = (td.textContent || '').trim();
                return t || null; // "HH:mm"
            }
            const prev = r.previousElementSibling;
            if (!prev) break;
            r = prev;
        }
        return null;
    }

    function readDeliveryDateMDForRow(row) {
        let r = row;
        while (r) {
            const td = cellByLabel(r, 'Delivery Date');
            if (td) {
                const t = (td.textContent || '').trim(); // "M/D" atau "MM/DD"
                return t || null;
            }
            const prev = r.previousElementSibling;
            if (!prev) break;
            r = prev;
        }
        return null;
    }

    // 1) Klasifikasi shift: Delivery Date+Time (09:40 rule), fallback jam Delivery Time
    function classifyShift(row, lineKey) {
        // summary khusus tetap berlaku
        const ov = specialSummaryShift(lineKey, row);
        if (ov) return ov;

        const tm = readDeliveryTimeTextForRow(row); // "HH:mm"
        const md = readDeliveryDateMDForRow(row);   // "MM/DD"
        if (tm) {
            if (md) {
                const curISO = window.prodPlanSSE?.getCurrentDate?.() || $u.getCurrentISO();
                const dlvISO = $u.mdToISO(md, curISO);
                const byDT = $u.toShiftByDateTime(curISO, dlvISO, tm); // 09:40 logic
                if (byDT === 'morning' || byDT === 'night') return byDT;
            }
            // fallback kalau "Delivery Date" kosong: nilai jamnya saja
            const mins = $u.timeToMinutes(tm);
            if (mins != null) return mins >= (9 * 60 + 40) ? 'morning' : 'night';
        }

        // jangan pakai Planning/Actual Start lagi (sesuai: pakai Delivery Time saja)
        return 'other';
    }

    const rowCountable = tr => tr && tr.style.display !== 'none';

    // 2) Hitung kartu: actual = DP + SC, hapus sisa +/- diff
    function computeLine(lineKey) {
        const wrap = document.querySelector(`[data-toggle-table="${lineKey}"]`);
        const sums = {
            morning: { order: 0, actual: 0 },
            night:   { order: 0, actual: 0 },
            total:   { order: 0, actual: 0 }
        };
        if (!wrap) return sums;

        wrap.querySelectorAll('tbody tr').forEach(tr => {
            if (tr.style.display === 'none') return;

            const order = readOrder(tr);
            const dp = readDP(tr);
            const sc = readSC(tr);
            const shift = classifyShift(tr, lineKey);

            if (tr.getAttribute('data-shift') !== shift) tr.setAttribute('data-shift', shift);

            if (shift === 'morning' || shift === 'night') {
                sums[shift].order  += order;
                sums[shift].actual += (dp + sc); // <= actual = DP + SC
            }
        });

        // TOTAL = Morning + Night (baris "other" nggak ikut)
        sums.total.order  = sums.morning.order + sums.night.order;
        sums.total.actual = sums.morning.actual + sums.night.actual;

        return sums;
    }

    const pct = (a, o) => o > 0 ? Math.min(100, Math.round((a / o) * 100)) : 0;

    function renderBlock(lineKey, shift, data) {
        const root = document.querySelector(
            `[data-shift-card="${lineKey}"] .strip-stat[data-line="${lineKey}"][data-shift="${shift}"]`
        );
        if (!root) return;
        const O = data.order, A = data.actual, P = pct(A, O);
        const q = sel => root.querySelector(sel);
        const elO = q('[data-role="shift-order"]');
        const elA = q('[data-role="shift-actual"]');
        const elP = q('[data-role="shift-pct"]');
        const elB = q('[data-role="shift-bar"]');
        if (elO) elO.textContent = O.toLocaleString(IDLOCALE);
        if (elA) elA.textContent = A.toLocaleString(IDLOCALE);
        if (elP) elP.textContent = P + '%';
        if (elB) elB.style.width = P + '%';
    }

    function recompute(lineKey) {
        const sums = computeLine(lineKey);
        renderBlock(lineKey, 'morning', sums.morning);
        renderBlock(lineKey, 'night',   sums.night);
        renderBlock(lineKey, 'total',   sums.total);
    }

    function recomputeAll() {
        LINES.forEach(recompute);
    }
    window.recomputeAllShiftCards = recomputeAll;

    function updateBarsForRow(row) {
        const order = readOrder(row), dp = readDP(row), sc = readSC(row);
        const dpBar = row.querySelector('[data-label][data-label*="Direct Pulling" i] .qty-progress .bar > i');
        if (dpBar) dpBar.style.width = (order > 0 ? Math.min(100, Math.round((dp / order) * 100)) : 0) + '%';
        const scBar = row.querySelector('[data-label][data-label*="Stock Chute" i] .qty-progress .bar > i');
        if (scBar) scBar.style.width = (order > 0 ? Math.min(100, Math.round((sc / order) * 100)) : 0) + '%';
        const totCell = row.querySelector('.total-progress');
        const tBar = totCell?.querySelector('.bar > i');
        const thePct = (order > 0 ? Math.min(100, Math.round(((dp + sc) / order) * 100)) : 0);
        if (tBar) tBar.style.width = thePct + '%';
        const tPct = totCell?.querySelector('.val');
        if (tPct) tPct.textContent = thePct + '%';
    }

    function applyUpdateItem(it) {
        const id = String(it.id);
        const el = document.querySelector(`[data-item-id="${id}"][data-type="direct-pulling"]`)
            || document.querySelector(`[data-item-id="${id}"][data-type="stock-chute"]`)
            || document.querySelector(`[data-item-id="${id}"][data-type="actual_start"]`)
            || document.querySelector(`[data-item-id="${id}"][data-type="start"]`)
            || document.querySelector(`[data-item-id="${id}"][data-type="balance"]`);
        if (!el) return;
        const row = el.closest('tr');
        if (!row) return;

        if (typeof it.direct_pulling_qty === 'number') {
            const dpEl = row.querySelector(`[data-item-id="${id}"][data-type="direct-pulling"]`);
            if (dpEl && dpEl.textContent.trim() !== String(it.direct_pulling_qty)) dpEl.textContent = it.direct_pulling_qty;
        }
        if (typeof it.stock_chute_qty === 'number') {
            const scEl = row.querySelector(`[data-item-id="${id}"][data-type="stock-chute"]`);
            if (scEl && scEl.textContent.trim() !== String(it.stock_chute_qty)) scEl.textContent = it.stock_chute_qty;
        }
        if (typeof it.order_qty === 'number') {
            const tdOrder = cellByLabel(row, 'Order');
            const flip = tdOrder?.querySelector('.flip') || tdOrder;
            if (flip) {
                flip.dataset.orderRaw = String(it.order_qty);
                flip.textContent = Number(it.order_qty).toLocaleString(IDLOCALE);
            }
        }
        if (typeof it.actual_start === 'string') {
            const asEl = row.querySelector(`[data-item-id="${id}"][data-type="actual_start"]`);
            if (asEl) asEl.textContent = it.actual_start || '--';
        }
        if (typeof it.end === 'string') {
            const endEl = row.querySelector(`[data-item-id="${id}"][data-type="end"]`);
            if (endEl) endEl.textContent = it.end || '--';
        }
        if (typeof it.balance === 'string') {
            const balEl = row.querySelector(`[data-item-id="${id}"][data-type="balance"]`);
            if (balEl) balEl.textContent = it.balance || '--';
        }

        const lineKey = getLineKeyOfRow(row);
        const newShift = classifyShift(row, lineKey);
        if (newShift !== (row.getAttribute('data-shift') || 'other')) row.setAttribute('data-shift', newShift);
        updateBarsForRow(row);
    }

    // Initial compute saat load
    document.addEventListener('DOMContentLoaded', recomputeAll);

    // >>> UPDATED: dengarkan event kustom dari ProductionPlanSSEClient, tanpa bikin EventSource sendiri
    document.addEventListener('DOMContentLoaded', () => {
        // ProductionPlanSSEClient akan update DOM (rows, qty, bar) lebih dulu,
        // lalu mem-broadcast 'pulling:update' -> kita tinggal recompute kartu.
        window.addEventListener('pulling:update', (ev) => {
            recomputeAll();
        });
    });

    // Recompute saat ada perubahan baris (insert/remove) dari tabel
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-toggle-table] tbody').forEach(tbody => {
            try {
                const mo = new MutationObserver(() => recomputeAll());
                mo.observe(tbody, { childList: true, subtree: false });
            } catch {}
        });
    });
})();

(function() {
    const IDLOCALE = 'id-ID';

    const isSummaryRow = tr => tr?.getAttribute('data-summary-row') === '1';

    function readBackNo(tr) {
        // Untuk baris summary, pakai label apa adanya (biar C4–7 & C8–3 jadi entri terpisah)
        if (isSummaryRow(tr)) {
            const td = $u.getCellByLabel(tr, 'Back No');
            const el = td?.querySelector('.flip') || td;
            return String(el?.textContent || '').trim(); // contoh: "CI12 (C4–7)"
        }
        // Untuk baris normal, pakai canonical (hapus suffix siklus kalau ada)
        const td = $u.getCellByLabel(tr, 'Back No');
        const el = td?.querySelector('.flip') || td;
        const raw = (el?.dataset?.backnoAlias || el?.dataset?.backnoRaw || el?.textContent || '').trim();
        if (!raw || raw === '--') return '';
        return $u.canonicalBackNoSplit(raw); // contoh: "CI12"
    }

    function readOrder(tr, isSummary) {
        if (isSummary) {
            const txt = $u.getCellByLabel(tr, 'Order')?.querySelector('.flip')?.textContent;
            return $u.int(txt);
        }
        const el = $u.getCellByLabel(tr, 'Order')?.querySelector('.flip') || $u.getCellByLabel(tr, 'Order');
        const ds = el?.dataset?.orderRaw;
        return ds != null && ds !== '' ? $u.int(ds) : $u.int(el?.textContent);
    }

    const readDP = (tr, isSummary) =>
        isSummary ? $u.int(tr.querySelector('[data-summary-dp]')?.textContent) :
        $u.int(tr.querySelector('[data-type="direct-pulling"]')?.textContent);

    const readSC = (tr, isSummary) =>
        isSummary ? $u.int(tr.querySelector('[data-summary-sc]')?.textContent) :
        $u.int(tr.querySelector('[data-type="stock-chute"]')?.textContent);

    function readCustomer(tr) {
        const td = $u.getCellByLabel(tr, 'Customer');
        const el = td?.querySelector('.flip') || td;
        return (el?.textContent || '').trim() || '--';
    }

    function collect(lineCode) {
        const wrap = document.querySelector(`[data-toggle-table="${lineCode}"]`);
        const tbody = wrap?.querySelector('tbody');
        const map = new Map();
        if (!tbody) return [];

        Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
            // Lewati baris yang disembunyikan (placeholder hasil move/clone)
            if (tr.style?.display === 'none') return;

            const summary = isSummaryRow(tr);
            const bn = readBackNo(tr);
            if (!bn) return;

            const ord = readOrder(tr, summary);
            const dp = readDP(tr, summary);
            const sc = readSC(tr, summary);
            const cust = readCustomer(tr);

            // Kunci pakai nama apa adanya:
            //  - Summary: "CI12 (C4–7)" atau "CI12 (C8–3)" -> TERPISAH
            //  - Normal : "CI12" (canonical)
            const key = bn;
            const rec = map.get(key) || {
                backNo: key,
                customer: cust,
                orderQty: 0,
                dp: 0,
                sc: 0
            };
            rec.orderQty += ord;
            rec.dp += dp;
            rec.sc += sc;
            if (!rec.customer || rec.customer === '--') rec.customer = cust;
            map.set(key, rec);
        });

        // Urutkan by OrderQty desc, lalu alfabet backNo
        return Array.from(map.values()).sort((a, b) => (b.orderQty - a.orderQty) || a.backNo.localeCompare(b
            .backNo));
    }

    let __lastSummary = {
        line: '',
        rows: []
    };

    function renderModal(lineCode) {
        const rows = collect(lineCode);
        __lastSummary = {
            line: lineCode,
            rows
        };

        // Statistik atas modal – dibuat dari data yang sama dengan card
        const totalBack = rows.length;
        const totalOrders = rows.reduce((s, r) => s + r.orderQty, 0);
        const completed = rows.reduce((s, r) => s + r.dp + r.sc, 0);
        const avg = totalBack > 0 ? Math.round(totalOrders / totalBack) : 0;

        document.getElementById('modalLineTitle').textContent = lineCode;
        document.getElementById('totalBackNumbers').textContent = totalBack.toLocaleString(IDLOCALE);
        document.getElementById('totalOrders').textContent = totalOrders.toLocaleString(IDLOCALE);
        document.getElementById('avgOrderPerBack').textContent = avg.toLocaleString(IDLOCALE);
        document.getElementById('completedOrders').textContent = completed.toLocaleString(IDLOCALE);

        const list = document.getElementById('backNumberList');
        list.innerHTML = '';
        if (!rows.length) {
            list.innerHTML = '<div class="text-center text-muted py-4">No data available</div>';
        } else {
            rows.forEach(r => {
                const done = r.dp + r.sc;
                const pct = r.orderQty > 0 ? Math.round((done / r.orderQty) * 100) : 0;
                const status = done >= r.orderQty ? 'Complete' : 'In Progress';
                const color = status === 'Complete' ? 'success' : 'warning';
                const div = document.createElement('div');
                div.className = 'back-number-item';
                div.innerHTML = `
    <div class="d-flex flex-column">
    <div class="back-no">${r.backNo}</div>
    <div class="small number">${r.customer || '--'}</div>
    </div>
    <div class="d-flex align-items-center gap-3">
    <div class="text-end">
        <div class="order-qty">${r.orderQty.toLocaleString(IDLOCALE)}</div>
        <div class="small number">Order Qty</div>
    </div>
    <div class="text-end">
        <div class="fw-bold text-${color}">${done.toLocaleString(IDLOCALE)}</div>
        <div class="small number">Completed</div>
    </div>
    <div class="text-end">
        <div class="fw-bold">${pct}%</div>
        <div class="small number">Progress</div>
    </div>
    </div>`;
                list.appendChild(div);
            });
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('summaryModal')).show();
    }

    window.showSummary = renderModal;

    window.exportSummary = function() {
        const {
            line,
            rows
        } = __lastSummary;
        if (!rows.length) return;
        const header = ['Back Number', 'Customer', 'Order Qty', 'Direct Pulling', 'Stock Chute',
            'Completed', 'Progress %', 'Status'
        ];
        const csv = [
            header.join(','),
            ...rows.map(r => {
                const done = r.dp + r.sc;
                const pct = r.orderQty > 0 ? Math.round((done / r.orderQty) * 100) : 0;
                const status = done >= r.orderQty ? 'Complete' : 'In Progress';
                return [
                    `"${r.backNo.replace(/"/g,'""')}"`,
                    `"${(r.customer||'--').replace(/"/g,'""')}"`,
                    r.orderQty, r.dp, r.sc, done, pct + '%', status
                ].join(',');
            })
        ].join('\n');

        const blob = new Blob([csv], {
            type: 'text/csv;charset=utf-8;'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `summary_${line}_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    };
})();


(function CardTotalsFromDOM() {
    /* disabled: handled by FixShiftCardsV3 */
})();

class PinnedShelf {
    constructor(container, opts = {}) {
        this.container = container;
        this.max = opts.max ?? 4;          // max chip current
        this.nextMax = opts.nextMax ?? 1;  // max chip next
        this.ttl = opts.ttl ?? (window.prodPlanSSE?.HIGHLIGHT_DURATION_MS || 40000);
        this.map = new Map(); // current: id -> {el, timer, ts}
        this._ensureShelf();
        this._hookObservers();
    }

    _ensureShelf() {
        if (this.deck) return;

        // ---------- 2 CARD SIDE-BY-SIDE ----------
        this.deck = document.createElement('div');
        this.deck.className = 'row g-3 shelf-deck mb-3';

        this.deck.innerHTML = `
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm pinned-card">
            <div class="card-header d-flex align-items-center gap-2">
              <i class="fas fa-cogs text-primary"></i>
              <strong class="curr">Current Production / Pulling</strong>
            </div>
            <div class="card-body py-3">
              <div data-shelf-list class="d-flex flex-column gap-2"></div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card shadow-sm next-card">
            <div class="card-header d-flex align-items-center gap-2">
              <i class="fas fa-forward text-primary"></i>
              <strong class="next">Next Production</strong>
            </div>
            <div class="card-body py-3">
              <div data-next-list class="d-flex flex-column gap-2"></div>
            </div>
          </div>
        </div>`;

        // taruh di atas kartu tabel (tepat setelah toolbar bila ada)
        const toolbar = this.container.querySelector('.d-flex.justify-content-end') || this.container.firstElementChild;
        (toolbar?.parentElement || this.container)
          .insertBefore(this.deck, toolbar?.nextSibling || this.container.firstChild);

        this.list = this.deck.querySelector('[data-shelf-list]');   // current
        this.nextList = this.deck.querySelector('[data-next-list]'); // next
    }

    _hookObservers() {
        // Refresh Next setiap ada SSE update dari ProductionPlanSSEClient
        window.addEventListener('pulling:update', () => this.refreshNextQueue());

        // Refresh Next kalau ada insert/remove baris
        try {
            const tbody = this.container.querySelector('table tbody');
            if (tbody) {
                this._mo = new MutationObserver(() => this.refreshNextQueue());
                this._mo.observe(tbody, { childList: true, subtree: false });
            }
        } catch {}

        // First paint
        this.refreshNextQueue();
    }

    _extract(row) {
        const get = (lbl) => {
            const td = $u.getCellByLabel(row, lbl);
            const el = td?.querySelector('.flip') || td;
            return (el?.textContent || '').trim();
        };
        const idEl = row.querySelector('[data-item-id]');
        const id = idEl?.getAttribute('data-item-id') || '';

        const orderTd = $u.getCellByLabel(row, 'Order');
        const orderEl = orderTd?.querySelector('.flip') || orderTd;
        const orderRaw = parseInt(
            String(orderEl?.dataset?.orderRaw ?? orderEl?.textContent ?? '0').replace(/[^\d-]/g, ''),
            10
        ) || 0;

        const dp = $u.int(row.querySelector('[data-type="direct-pulling"]')?.textContent);
        const sc = $u.int(row.querySelector('[data-type="stock-chute"]')?.textContent);
        const done = dp + sc;
        const pct = orderRaw > 0 ? Math.min(100, Math.round(done / orderRaw * 100)) : 0;

        return {
            row,
            id,
            backNo: get('Back No'),
            customer: get('Customer') || '--',
            dock: get('Dock') || '--',
            order: orderRaw,
            dp,
            sc,
            done,
            pct,
            deliveryTime: get('Delivery Time') || '--',
            deliveryDate: get('Delivery Date') || '--'
        };
    }

    _renderChipCurrent(d) {
        const done = (d.dp || 0) + (d.sc || 0);
        const div = document.createElement('div');
        div.className = 'pinned-chip';
        div.innerHTML = `
            <div class="info">
            <div class="backno fw-bold">${d.backNo}</div>
            <div class="dim">${d.customer || '--'}</div>
            </div>
            <div class="stats">
            <div class="stat-stack">
                <div class="stat-label">Order</div>
                <div class="stat-number primary" data-x="order">${d.order.toLocaleString('id-ID')}</div>
            </div>
            <div class="stat-stack">
                <div class="stat-label">Completed</div>
                <div class="stat-number" data-x="done">${done.toLocaleString('id-ID')}</div>
            </div>
            </div>
            <div class="meta">
            <span class="tag">Dock</span><span data-x="dock">${d.dock}</span>
            <span>•</span><span data-x="dtime">${d.deliveryTime}</span>
            <span>·</span><span data-x="ddate">${d.deliveryDate}</span>
            </div>`;
        return div;
    }

    _renderChipNext(d) {
        const div = document.createElement('div');
        div.className = 'pinned-chip';
        div.innerHTML = `
            <div class="info">
            <div class="backno fw-bold">${d.backNo}</div>
            <div class="dim">${d.customer || '--'}</div>
            </div>
            <div class="stats">
            <div class="stat-stack">
                <div class="stat-label">Order</div>
                <div class="stat-number primary">${d.order.toLocaleString('id-ID')}</div>
            </div>
            </div>
            <div class="meta">
            <span class="tag">Dock</span><span>${d.dock}</span>
            <span>•</span><span>${d.deliveryTime}</span>
            <span>·</span><span>${d.deliveryDate}</span>
            </div>`;
        return div;
    }

    _patchChip(el, d) {
        const set = (k, v) => {
            const n = el.querySelector(`[data-x="${k}"]`);
            if (n) n.textContent = v;
        };
        set('order', d.order.toLocaleString('id-ID'));
        set('done', d.done.toLocaleString('id-ID'));
        set('totpct', `${d.pct}%`);
        set('dock', d.dock);
        set('dtime', d.deliveryTime);
        set('ddate', d.deliveryDate);

        const totbar = el.querySelector('[data-x="totbar"]');
        if (totbar) totbar.style.width = `${d.pct}%`;
    }

    // -------- CURRENT (kiri) --------
    upsertFromRow(row) {
        const d = this._extract(row);
        if (!d.id) return;

        const rec = this.map.get(d.id);
        if (!rec) {
            const chip = this._renderChipCurrent(d);
            this.list.prepend(chip);
            const timer = setTimeout(() => this.remove(d.id), this.ttl);
            this.map.set(d.id, { el: chip, timer, ts: Date.now() });
            this._trim();
        } else {
            this._patchChip(rec.el, d);
            clearTimeout(rec.timer);
            rec.timer = setTimeout(() => this.remove(d.id), this.ttl);
            rec.ts = Date.now();
        }

        // setiap current update -> refresh Next berdasarkan posisi baris ini
        this.refreshNextQueue(row);
    }

    remove(id) {
        const rec = this.map.get(id);
        if (!rec) return;
        clearTimeout(rec.timer);
        rec.el.remove();
        this.map.delete(id);
        // current berkurang -> next bisa berubah
        this.refreshNextQueue();
    }

    _trim() {
        if (this.map.size <= this.max) return;
        const arr = [...this.map.entries()].sort((a, b) => a[1].ts - b[1].ts);
        const over = this.map.size - this.max;
        for (let i = 0; i < over; i++) {
            const [id, rec] = arr[i];
            clearTimeout(rec.timer);
            rec.el.remove();
            this.map.delete(id);
        }
    }

    // -------- NEXT (kanan) --------
    _visibleDataRows() {
        const tbody = this.container.querySelector('tbody');
        if (!tbody) return [];
        return Array.from(tbody.querySelectorAll('tr')).filter(tr =>
            tr.style.display !== 'none' && tr.getAttribute('data-summary-row') !== '1'
        );
    }

    _computeNext(anchorRow = null) {
        const currentIds = new Set(this.map.keys());
        const rows = this._visibleDataRows();

        // Mulai dari baris di bawah "anchor" kalau tersedia,
        // kalau tidak ada anchor, mulai dari baris paling atas (global queue).
        let startIndex = 0;
        if (anchorRow) {
            const idx = rows.indexOf(anchorRow);
            if (idx >= 0) startIndex = idx + 1;
        }

        const out = [];
        const pushIfCandidate = (r) => {
            const d = this._extract(r);
            if (!d.id) return;
            if (currentIds.has(d.id)) return;            // skip yang sedang current
            if (d.order > 0 && d.done >= d.order) return; // skip yang sudah complete
            out.push(d);
        };

        for (let i = startIndex; i < rows.length && out.length < this.nextMax; i++) {
            pushIfCandidate(rows[i]);
        }
        // wrap ke atas kalau belum penuh
        if (out.length < this.nextMax && startIndex > 0) {
            for (let i = 0; i < startIndex && out.length < this.nextMax; i++) {
                pushIfCandidate(rows[i]);
            }
        }
        return out;
    }

    refreshNextQueue(anchorRow = null) {
        if (!this.nextList) return;
        const items = this._computeNext(anchorRow);
        this.nextList.innerHTML = '';
        if (!items.length) {
            this.nextList.innerHTML = '<div class="text-muted small">No upcoming rows</div>';
            return;
        }
        items.forEach(d => {
            const chip = this._renderChipNext(d);
            this.nextList.appendChild(chip);
        });
    }
}


(function ShiftCardControls() {
    const LS_KEY = 'shiftCardState'; // { mini: {AS003:true}, hidden:{AS004:true} }
    const readState = () => {
        try {
            return JSON.parse(localStorage.getItem(LS_KEY) || '{"mini":{},"hidden":{}}');
        } catch {
            return {
                mini: {},
                hidden: {}
            };
        }
    };
    const writeState = (s) => {
        try {
            localStorage.setItem(LS_KEY, JSON.stringify(s));
        } catch {}
    };

    const state = readState();

    // tray untuk restore kartu tersembunyi
    function ensureTray() {
        let tray = document.getElementById('hidden-cards-tray');
        if (!tray) {
            tray = document.createElement('div');
            tray.id = 'hidden-cards-tray';
            tray.className = 'alert alert-secondary py-2 px-3 d-flex align-items-center gap-2 d-none';
            tray.innerHTML = `
                <i class="fas fa-eye-slash"></i>
                <strong class="me-1">Hidden cards:</strong>
                <span data-tray-list class="d-flex flex-wrap align-items-center"></span>
            `;
            // taruh di atas tab-content (aman, ga nutup table)
            const container = document.querySelector('.container') || document.body;
            const tabs = document.getElementById('lineTabs') || container.firstElementChild;
            container.insertBefore(tray, (tabs?.nextElementSibling) || container.firstChild);
        }
        return tray;
    }

    function refreshTray() {
        const tray = ensureTray();
        const list = tray.querySelector('[data-tray-list]');
        list.innerHTML = '';
        const hiddenKeys = Object.keys(state.hidden || {}).filter(k => state.hidden[k]);
        if (!hiddenKeys.length) {
            tray.classList.add('d-none');
            return;
        }
        hiddenKeys.forEach(key => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'badge-btn btn btn-sm btn-outline-secondary';
            b.textContent = key + ' (show)';
            b.addEventListener('click', () => {
                const card = document.querySelector(`[data-shift-card="${key}"]`);
                if (card) {
                    card.classList.remove('d-none');
                    state.hidden[key] = false;
                    writeState(state);
                    refreshTray();
                }
            });
            list.appendChild(b);
        });
        tray.classList.remove('d-none');
    }

    function buildHeader(card, key, collapseId) {
        // Header
        const head = document.createElement('div');
        head.className = 'card-header d-flex justify-content-between align-items-center shiftcard-head';
        head.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-industry text-primary"></i>
            <span class="title">${key} – Shift Summary</span>
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" data-action="minimize" aria-expanded="true" title="Minimize">
            <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        `;
        card.insertBefore(head, card.firstChild);

        // Wiring tombol
        const btnMin = head.querySelector('[data-action="minimize"]');
        // const btnHide = head.querySelector('[data-action="hide"]');
        const target = document.getElementById(collapseId);
        const bsCollapse = new bootstrap.Collapse(target, {
            toggle: false
        });

        function setMinimized(min) {
            if (min) {
                bsCollapse.hide();
                btnMin.setAttribute('aria-expanded', 'false');
                btnMin.innerHTML = '<i class="fas fa-chevron-down"></i>';
            } else {
                bsCollapse.show();
                btnMin.setAttribute('aria-expanded', 'true');
                btnMin.innerHTML = '<i class="fas fa-chevron-up"></i>';
            }
        }

        btnMin.addEventListener('click', () => {
            const current = btnMin.getAttribute('aria-expanded') === 'true';
            setMinimized(current); // toggle
            state.mini[key] = current; // simpan: kalau tadinya expanded=true, sekarang jadi minimized
            writeState(state);
        });

        // btnHide.addEventListener('click', () => {
        //     card.classList.add('d-none');
        //     state.hidden[key] = true;
        //     writeState(state);
        //     refreshTray();
        // });

        // apply state awal
        setMinimized(!!state.mini[key]);
    }

    // Upgrade semua card
    document.querySelectorAll('[data-shift-card]').forEach(card => {
        const key = card.getAttribute('data-shift-card') || 'CARD';
        // Bungkus body ke collapse
        let body = card.querySelector('.card-body');
        if (!body) return;

        // kalau sudah pernah di-wrap, skip
        let wrap = body.closest('.collapse');
        if (!wrap) {
            const id = `shiftcard-${key}`;
            wrap = document.createElement('div');
            wrap.className = 'collapse show';
            wrap.id = id;
            body.parentElement.insertBefore(wrap, body);
            wrap.appendChild(body);

            // buat header + tombol
            buildHeader(card, key, id);
        }

        // apply hidden state
        if (state.hidden[key]) card.classList.add('d-none');
    });

    refreshTray();
})();

/* ============================================================
Shift Capacity Reclassifier (S1/NS/LS1 & S3/LS3 + spillover)
Berlaku untuk AS003 & AS004. Tidak mengubah SSE/DOM lain.
------------------------------------------------------------
Aturan:
- Morning (S1–LS1):
    0..720      -> S1
    721..850    -> NS
    851..1050   -> LS1
    >1050       -> kelebihan (qty-1050) DIALIHKAN ke Night
- Night (S3–LS3):
    0..720      -> S3
    721..1050   -> LS3
    >1050       -> kelebihan dipindahkan ke Morning
                    HANYA jika Morning < 1050 (pindah sebatas sisa kapasitas)
- Total tetap sama; yang berubah hanya pembagian “order” per shift pada kartu.
============================================================ */
/* ============================================================
   Shift Capacity Reclassifier (v2) — with summary-row support
   ============================================================ */
(function ShiftCapacityReclassifierV2(){
    const LINES = ['AS003','AS004'];
    const IDLOCALE = 'id-ID';
    const MAX = 1050;
    const normU = s => String(s||'').toUpperCase().trim();
    const isSummary = tr => tr?.getAttribute('data-summary-row') === '1';

    const q = (root, sel) => root ? root.querySelector(sel) : null;

    function cellByLabel(row, label){ return $u.getCellByLabel(row, label); }

    function readOrder(row){
        const td = cellByLabel(row,'Order');
        const el = td?.querySelector('.flip') || td;
        // summary tidak punya dataset orderRaw, jadi pakai text saja
        const raw = el?.dataset?.orderRaw;
        return raw != null && raw !== '' ? $u.int(raw) : $u.int(el?.textContent);
    }
    function readDP(row){
        if (isSummary(row)) return $u.int(row.querySelector('[data-summary-dp]')?.textContent);
        return $u.int(row.querySelector('[data-type="direct-pulling"]')?.textContent);
    }
    function readSC(row){
        if (isSummary(row)) return $u.int(row.querySelector('[data-summary-sc]')?.textContent);
        return $u.int(row.querySelector('[data-type="stock-chute"]')?.textContent);
    }
    function readSummaryLabel(row){
        const attr = row?.getAttribute('data-summary-label');
        if (attr) return normU(attr);
        const td = cellByLabel(row, 'Back No');
        return normU((td?.textContent || '').trim());
    }

    // --- ambil Delivery Time + Date dari header group terdekat
    function readDeliveryTimeText(row){
        let r = row;
        while(r){
        const td = cellByLabel(r,'Delivery Time');
        if (td) return (td.textContent||'').trim() || null;
        r = r.previousElementSibling;
        }
        return null;
    }
    function readDeliveryDateMD(row){
        let r = row;
        while(r){
        const td = cellByLabel(r,'Delivery Date');
        if (td) return (td.textContent||'').trim() || null;
        r = r.previousElementSibling;
        }
        return null;
    }

    // --- mapping shift (summary-aware)
    function classifyShift(row, lineKey){
        if (isSummary(row)){
        const label = readSummaryLabel(row);
        if (lineKey === 'AS003'){
            if (/^CI12\b.*C\s*4\s*[–-]\s*7\b/.test(label)) return 'morning';
            if (/^CI12\b.*C\s*8\s*[–-]\s*3\b/.test(label)) return 'night';
        }
        if (lineKey === 'AS004'){
            if (/\bCI19\b/.test(label)) return 'morning';
        }
        }
        // fallback: Delivery Date + Time (09:40)
        const tm = readDeliveryTimeText(row);       // "HH:mm"
        const md = readDeliveryDateMD(row);         // "MM/DD"
        if (!tm) return 'other';
        if (md){
        const curISO = $u.getCurrentISO();
        const dlvISO = $u.mdToISO(md, curISO);
        const byDT = $u.toShiftByDateTime(curISO, dlvISO, tm);
        if (byDT === 'morning' || byDT === 'night') return byDT;
        }
        const mins = $u.timeToMinutes(tm);
        if (mins == null) return 'other';
        return mins >= (9*60+40) ? 'morning' : 'night';
    }

    // --- spillover 1050
    function applyTransfer(morningOrder, nightOrder){
        let m = Math.max(0, morningOrder|0);
        let n = Math.max(0, nightOrder|0);
        let movedMtoN = 0, movedNtoM = 0;

        if (m > MAX){ movedMtoN = m - MAX; m = MAX; n += movedMtoN; }
        if (n > MAX && m < MAX){
        const cap = MAX - m;
        const move = Math.min(n - MAX, cap);
        if (move > 0){ movedNtoM = move; n -= move; m += move; }
        }
        return { morning: m, night: n, movedMtoN, movedNtoM };
    }

    function statusMorning(q){ return q <= 720 ? 'S1' : (q <= 850 ? 'NS' : 'LS1'); }
    function statusNight(q){   return q <= 720 ? 'S3' : 'LS3'; }

    function hideLegacyAdvanceChip(root){
        // sembunyikan chip lama "Advance to ..."
        root.querySelectorAll('.chip').forEach(ch=>{
        const t = (ch.textContent||'').toLowerCase();
        if (!ch.hasAttribute('data-role') && t.startsWith('advance to')) ch.classList.add('d-none');
        });
    }

    function setStatusChip(lineKey, shift, label, note=''){
        const root = document.querySelector(
        `[data-shift-card="${lineKey}"] .strip-stat[data-line="${lineKey}"][data-shift="${shift}"]`
        );
        if (!root) return;
        hideLegacyAdvanceChip(root);

        let chip = root.querySelector('[data-role="shift-status"]');
        if (!chip){
        chip = document.createElement('span');
        chip.setAttribute('data-role','shift-status');
        chip.className = 'chip border fw-bolder ms-1';
        const valWrap = root.querySelector('.d-flex.align-items-baseline') || root;
        valWrap.appendChild(chip);
        }
        chip.textContent = label;
        chip.classList.remove('bg-success-subtle','bg-warning-subtle','bg-danger-subtle','text-dark');
        if (label === 'S1' || label === 'S3') chip.classList.add('bg-success-subtle','text-dark');
        else if (label === 'NS')               chip.classList.add('bg-warning-subtle','text-dark');
        else                                   chip.classList.add('bg-danger-subtle','text-dark');
        chip.classList.remove('d-none');

        let noteEl = root.querySelector('[data-role="shift-note"]');
        if (!noteEl){
        noteEl = document.createElement('small');
        noteEl.setAttribute('data-role','shift-note');
        noteEl.className = 'ms-2 text-muted';
        chip.after(noteEl);
        }
        noteEl.textContent = note;
    }

    function renderOne(lineKey, which, effOrder, actual){
        const card = document.querySelector(
        `[data-shift-card="${lineKey}"] .strip-stat[data-line="${lineKey}"][data-shift="${which}"]`
        );
        if (!card) return;
        const pct = effOrder > 0 ? Math.min(100, Math.round((actual/effOrder)*100)) : 0;
        const setText = (sel, val) => { const el = q(card, sel); if (el) el.textContent = val; };

        setText('[data-role="shift-order"]',  Number(effOrder||0).toLocaleString(IDLOCALE));
        setText('[data-role="shift-actual"]', Number(actual||0).toLocaleString(IDLOCALE));
        setText('[data-role="shift-pct"]',    pct + '%');
        const bar = q(card,'[data-role="shift-bar"]'); if (bar) bar.style.width = pct + '%';
    }

    function recomputeLine(lineKey){
        const wrap = document.querySelector(`[data-toggle-table="${lineKey}"]`);
        const tbody = wrap?.querySelector('tbody');
        if (!tbody) return;

        let mOrder=0, mActual=0, nOrder=0, nActual=0;

        Array.from(tbody.querySelectorAll('tr')).forEach(tr=>{
        if (tr.style.display==='none') return;
        const sh = classifyShift(tr, lineKey);
        if (sh !== 'morning' && sh !== 'night') return;

        const order = readOrder(tr);
        const actual = readDP(tr) + readSC(tr);

        if (sh === 'morning'){ mOrder += order; mActual += actual; }
        else { nOrder += order; nActual += actual; }
        });

        // spill-over 1050
        const t = applyTransfer(mOrder, nOrder);

        // render
        renderOne(lineKey, 'morning', t.morning, mActual);
        renderOne(lineKey, 'night',   t.night,   nActual);

        // status chip & notes
        setStatusChip(lineKey, 'morning', statusMorning(t.morning));
        setStatusChip(lineKey, 'night',   statusNight(t.night));

        // total (efektif)
        const totalOrder = t.morning + t.night;
        const totalActual = mActual + nActual;
        const totalCard = document.querySelector(
        `[data-shift-card="${lineKey}"] .strip-stat[data-line="${lineKey}"][data-shift="total"]`
        );
        if (totalCard){
        const pct = totalOrder>0 ? Math.min(100, Math.round((totalActual/totalOrder)*100)) : 0;
        const setText = (sel, val) => { const el = q(totalCard, sel); if (el) el.textContent = val; };
        setText('[data-role="shift-order"]',  totalOrder.toLocaleString(IDLOCALE));
        setText('[data-role="shift-actual"]', totalActual.toLocaleString(IDLOCALE));
        const bar = q(totalCard,'[data-role="shift-bar"]'); if (bar) bar.style.width = pct + '%';
        setText('[data-role="shift-pct"]', pct + '%');
        }
    }

    function recomputeAll(){ LINES.forEach(recomputeLine); }

    // initial + sse + mutasi
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ()=>setTimeout(recomputeAll,0));
    else setTimeout(recomputeAll,0);

    window.addEventListener('pulling:update', ()=>setTimeout(recomputeAll,0));
    document.querySelectorAll('[data-toggle-table] tbody').forEach(tb=>{
        try{ new MutationObserver(()=>setTimeout(recomputeAll,0)).observe(tb,{childList:true,subtree:false}); }catch{}
    });
})();

 /* Smooth Auto-Scroll (GPU) + Global & Per-Pane Toggle */
(function() {
    const SPEED = 6; // px/detik
    const EDGE_PAUSE = 1800; // jeda di bawah (ms)
    const USER_PAUSE = 3000; // jeda pasca interaksi user (ms)
    const REINIT_DEBOUNCE = 600;

    const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
    const stops = new Set(); // stopper untuk pane aktif saja

    // ===== GLOBAL TOGGLE (master switch) =====
    const KEY_GLOBAL = 'pp:autoScrollEnabled';
    let enabled = (localStorage.getItem(KEY_GLOBAL) ?? '1') === '1'; // default ON

    function updateGlobalToggleUI() {
        const btn = document.getElementById('autoScrollToggle');
        if (!btn) return;
        const stateEl = btn.querySelector('.state');
        if (stateEl) stateEl.textContent = enabled ? 'On' : 'Off';
        btn.classList.toggle('btn-outline-danger', enabled);
        btn.classList.toggle('btn-outline-secondary', !enabled);
        btn.setAttribute('aria-pressed', String(enabled));
    }

    function setEnabled(next) {
        enabled = !!next;
        localStorage.setItem(KEY_GLOBAL, enabled ? '1' : '0');
        updateGlobalToggleUI();
        if (!enabled) stopAll();
        else initActive();
        window.__autoScrollEnabled = enabled;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('autoScrollToggle');
        if (btn) {
            btn.addEventListener('click', () => setEnabled(!enabled));
            updateGlobalToggleUI();
        }
    });

    // ===== PER-PANE TOGGLE =====
    const KEY_PANE_PREFIX = 'pp:autoScrollPane:'; // contoh: pp:autoScrollPane:AS003

    const getPaneKey = (pane) => {
        if (!pane) return '';
        const wrap = pane.querySelector('[data-toggle-table]');
        return (wrap && wrap.getAttribute('data-toggle-table')) || pane.id || '';
    };
    const isPaneEnabled = (key) => {
        if (!key) return true;
        const v = localStorage.getItem(KEY_PANE_PREFIX + key);
        return (v ?? '1') === '1'; // default ON
    };
    const setPaneEnabled = (key, on) => {
        if (!key) return;
        localStorage.setItem(KEY_PANE_PREFIX + key, on ? '1' : '0');
        updatePaneToggleUI(key);
        // jika pane yang diubah adalah pane aktif, re-evaluasi
        const activePane = document.querySelector('.tab-pane.show.active, .tab-pane.active');
        const activeKey = getPaneKey(activePane);
        if (activeKey === key) {
            if (!on) stopAll();
            else if (enabled) startForPane(activePane);
        }
    };
    const updatePaneToggleUI = (key) => {
        const on = isPaneEnabled(key);
        document.querySelectorAll(`[data-pane-autoscroll="${key}"]`).forEach(btn => {
            const stateEl = btn.querySelector('.state');
            if (stateEl) stateEl.textContent = on ? 'On' : 'Off';
            btn.classList.toggle('btn-outline-success', on);
            btn.classList.toggle('btn-outline-secondary', !on);
            btn.setAttribute('aria-pressed', String(on));
        });
    };

    // delegasi klik tombol per-pane
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-pane-autoscroll]');
        if (!btn) return;
        const key = btn.getAttribute('data-pane-autoscroll');
        setPaneEnabled(key, !isPaneEnabled(key));
    });

    // inisialisasi label per-pane ketika DOM siap
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-pane-autoscroll]').forEach(btn => {
            const key = btn.getAttribute('data-pane-autoscroll');
            updatePaneToggleUI(key);
        });
    });

    // ===== SCROLLER UNTUK 1 CONTAINER =====
    function startScroller(container) {
        const table = container.querySelector('table');
        const thead = container.querySelector('thead');
        const tbody = container.querySelector('tbody');
        if (!table || !tbody) return () => {};

        container.style.overflow = 'hidden';

        const measure = () => {
            const headH = thead ? thead.offsetHeight : 0;
            const bodyH = tbody.scrollHeight;
            const total = headH + bodyH;
            const max = Math.max(0, total - container.clientHeight);
            return max;
        };

        let max = measure();
        let offset = 0;
        let last = performance.now();
        let paused = false;
        let raf = 0,
            ut = null;

        const apply = y => {
            tbody.style.transform = `translate3d(0, ${-y}px, 0)`;
        };

        const userKick = () => {
            paused = true;
            clearTimeout(ut);
            ut = setTimeout(() => {
                paused = false;
                last = performance.now();
            }, USER_PAUSE);
        };

        container.addEventListener('wheel', (e) => {
            e.preventDefault();
            userKick();
            max = measure();
            offset = clamp(offset + e.deltaY, 0, max);
            apply(offset);
        }, {
            passive: false
        });

        let tY = 0;
        container.addEventListener('touchstart', (e) => {
            tY = e.touches[0].clientY;
            userKick();
        }, {
            passive: true
        });
        container.addEventListener('touchmove', (e) => {
            e.preventDefault();
            userKick();
            const ny = e.touches[0].clientY,
                dy = tY - ny;
            tY = ny;
            max = measure();
            offset = clamp(offset + dy, 0, max);
            apply(offset);
        }, {
            passive: false
        });

        let dragging = false,
            pY = 0,
            pid = null;
        container.addEventListener('pointerdown', (e) => {
            dragging = true;
            pY = e.clientY;
            pid = e.pointerId;
            container.setPointerCapture(pid);
            userKick();
        });
        container.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            const dy = pY - e.clientY;
            pY = e.clientY;
            max = measure();
            offset = clamp(offset + dy, 0, max);
            apply(offset);
        });
        const endDrag = () => {
            dragging = false;
            if (pid != null) {
                try {
                    container.releasePointerCapture(pid);
                } catch {}
                pid = null;
            }
        };
        container.addEventListener('pointerup', endDrag);
        container.addEventListener('pointercancel', endDrag);
        container.addEventListener('mouseleave', () => dragging = false);

        function loop(ts) {
            if (!container.isConnected) return;
            const dt = ts - last;
            last = ts;
            if (!paused) {
                max = measure();
                if (max > 0) {
                    offset += (SPEED * dt / 1000);
                    if (offset >= max - 0.5) {
                        offset = max;
                        apply(offset);
                        paused = true;
                        setTimeout(() => {
                            if (!container.isConnected) return;
                            offset = 0;
                            apply(offset);
                            paused = false;
                            last = performance.now();
                        }, EDGE_PAUSE);
                    } else {
                        apply(offset);
                    }
                }
            }
            raf = requestAnimationFrame(loop);
        }

        apply(0);
        raf = requestAnimationFrame(loop);

        return () => {
            cancelAnimationFrame(raf);
            clearTimeout(ut);
        };
    }

    // ===== Lifecycle (aktifkan hanya untuk pane aktif) =====
    function stopAll() {
        for (const s of stops) {
            try {
                s();
            } catch {}
        }
        stops.clear();
        window.__autoScrollCount = 0;
    }

    function startForPane(pane) {
        stopAll();
        if (!pane || !enabled) return;

        const paneKey = getPaneKey(pane);
        if (!isPaneEnabled(paneKey)) return;

        // tunggu transisi fade
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                pane.querySelectorAll('.table-responsive.auto-scroll').forEach(el => {
                    const stop = startScroller(el);
                    stops.add(stop);
                });
                window.__autoScrollPane = pane.id || '(no id)';
                window.__autoScrollCount = stops.size;
            });
        });
    }

    function initActive() {
        if (!enabled) {
            stopAll();
            return;
        }
        const activePane =
            document.querySelector('.tab-pane.show.active') ||
            document.querySelector('.tab-pane.active') ||
            document.querySelector('.tab-pane');
        startForPane(activePane);
    }

    let rt;
    const reinit = () => {
        clearTimeout(rt);
        rt = setTimeout(() => {
            enabled ? initActive() : stopAll();
        }, REINIT_DEBOUNCE);
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initActive);
    else initActive();
    window.addEventListener('load', initActive);

    document.addEventListener('shown.bs.tab', (ev) => {
        const sel = ev.target.getAttribute('data-bs-target') || ev.target.getAttribute('href');
        const pane = sel ? document.querySelector(sel) : null;
        startForPane(pane);
    });

    const host = document.getElementById('lineTabsContent');
    if (host) new MutationObserver(reinit).observe(host, {
        childList: true,
        subtree: true
    });

    // expose helpers (optional)
    window.reinitAutoScroll = reinit;
    window.setAutoScrollEnabled = setEnabled;
    window.getAutoScrollEnabled = () => enabled;
    window.setPaneAutoScrollEnabled = setPaneEnabled;
    window.getPaneAutoScrollEnabled = isPaneEnabled;

    // sinkronkan label tombol global di awal
    updateGlobalToggleUI();
    // sinkronkan label tombol per-pane di awal
    document.querySelectorAll('[data-pane-autoscroll]').forEach(btn => {
        const key = btn.getAttribute('data-pane-autoscroll');
        updatePaneToggleUI(key);
    });
})();

