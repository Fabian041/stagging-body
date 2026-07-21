<script>
    let line = '';
    let partDetail = {};
    let part = 'part';
    let partNumber;
    let loadingListItem = [];
    let loadinglistDetail = [];
    var loadingListNumber = [];
    const inFlightLL = new Set();
    // flag selesai: tandai LL yang SUDAH beres proses agar tidak bisa discan lagi
    const doneKey = (ll) => `llDone_${ll}`;
    const legacyDoneKey = (ll) => `ll_done_${ll}`;
    const isLLDone = (ll) => (
        localStorage.getItem(doneKey(ll)) === '1' ||
        localStorage.getItem(legacyDoneKey(ll)) === '1'
    );
    const markLLDone = (ll) => {
        try {
            localStorage.setItem(doneKey(ll), '1');
            localStorage.removeItem(legacyDoneKey(ll));
        } catch (e) {}
    };

    function normalizeLoadingListNumber(number) {
        return String(number || '').trim();
    }

    function loadingListDomId(number) {
        return `loadingListContainer-${normalizeLoadingListNumber(number).replace(/[^A-Za-z0-9_-]/g, '')}`;
    }

    function hasLoadingListChip(number) {
        const normalized = normalizeLoadingListNumber(number);
        if (!normalized) return false;

        return $('#list .pulling-ll-chip span').filter(function() {
            return normalizeLoadingListNumber($(this).text()) === normalized;
        }).length > 0;
    }

    function pullingLlMarkup(number, liId) {
        const normalized = normalizeLoadingListNumber(number);
        const id = liId || loadingListDomId(normalized);
        return `<li class="pulling-ll-item" id="${id}">
            <div class="pulling-ll-chip" id="loadingList">
                <span id="loadingList-display">${normalized}</span>
            </div>
        </li>`;
    }

    function appendLoadingListChip(number) {
        const normalized = normalizeLoadingListNumber(number);
        if (!normalized || hasLoadingListChip(normalized)) return false;

        $('#loadingListContainerSample').remove();
        $('#list').append(pullingLlMarkup(normalized));

        return true;
    }

    function replaceTileWithLoadingListChip(tileId, number) {
        const normalized = normalizeLoadingListNumber(number);
        const $tile = $(`#${tileId}`);
        if (!normalized) {
            $tile.remove();
            return false;
        }

        if (hasLoadingListChip(normalized)) {
            $tile.remove();
            return false;
        }

        $('#loadingListContainerSample').remove();
        if ($tile.length) {
            $tile.replaceWith(pullingLlMarkup(normalized));
        } else {
            $('#list').append(pullingLlMarkup(normalized));
        }

        return true;
    }

    function pullingSkidMarkup(skid) {
        return `<div class="row mt-2 px-1">
            <div class="col-12">
                <div class="pulling-skid-bar">
                    <div class="pulling-skid-info">
                        <span class="pulling-skid-label">Skid</span>
                        <span class="pulling-skid-value" id="skid-display">${skid}</span>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" id="close-skid">Close Skid ${skid}</button>
                </div>
            </div>
        </div>`;
    }

    function okSound() {
        var sound = document.getElementById("ok-sound");
        sound.play();
    }

    function kanbanNotExistSound() {
        var sound = document.getElementById("kanban-not-exist-sound");
        sound.play();
    }

    function partNotExistSound() {
        var sound = document.getElementById("part-not-exist-sound");
        sound.play();
    }

    function notScannedSound() {
        var sound = document.getElementById("not-scanned-sound");
        sound.play();
    }

    function notExist() {
        var sound = document.getElementById("not-exist-sound");
        sound.play();
    }

    function notMatchSound() {
        var sound = document.getElementById("not-match-sound");
        sound.play();
    }

    function notMatchLlSound() {
        var sound = document.getElementById("not-match-ll-sound");
        sound.play();
    }

    function unknownSound() {
        var sound = document.getElementById("unknown-sound");
        sound.play();
    }

    function unknownLlSound() {
        var sound = document.getElementById("unknown-ll-sound");
        sound.play();
    }

    function alreadyScanSound() {
        var sound = document.getElementById("already-scan-sound");
        sound.play();
    }

    function alreadyPulledSound() {
        var sound = document.getElementById("already-pulled-sound");
        sound.play();
    }

    function alreadyScanLlSound() {
        var sound = document.getElementById("already-scan-ll-sound");
        sound.play();
    }

    function uncompleteLlSound() {
        var sound = document.getElementById("uncomplete-ll-sound");
        sound.play();
    }

    function fullfilledSound() {
        var sound = document.getElementById("fullfilled-sound");
        sound.play();
    }

    function scanCustomerFirstSound() {
        var sound = document.getElementById("scan-customer-first-sound");
        sound.play();
    }

    function finishPullingSound() {
        var sound = document.getElementById("finish-pulling-sound");
        sound.play();
    }

    // extract the loading list number from the key
    function extractLoadingListNumber(key) {
        const prefix = "ll_";
        return key.substring(prefix.length);
    }

    function extractManifest(key) {
        const prefix = "skid_";
        return key.substring(prefix.length);
    }

    // retrieve the loading list number from localStorage
    function getLoadingListNumber() {
        loadingListNumber = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("ll_") && !key.startsWith("ll_done_")) {
                const number = extractLoadingListNumber(key);
                if (number && !loadingListNumber.includes(number)) {
                    loadingListNumber.push(number);
                }
            }
        }
        // Return a default value if no loading list number is found
        return loadingListNumber;
    }

    // get manifest from skid prefix
    function getManifest() {
        let manifest = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("skid_")) {
                manifest.push(extractManifest(key));
            }
        }
        // Return a default value if no manifest is found
        return manifest;
    }

    const KEY_DIRECT_MODE = 'directMode'; // 'on' | 'off'

    function setDirectMode(on) {
        try {
            localStorage.setItem(KEY_DIRECT_MODE, on ? 'on' : 'off');
        } catch (e) {}
    }

    function isDirectOn() {
        try {
            return (localStorage.getItem(KEY_DIRECT_MODE) || 'off') === 'on';
        } catch (e) {
            return false;
        }
    }

    // cache hasil alamat chute (master data statis) → unit ke-2 dst dari part yang sama tidak nembak server lagi
    var chuteCache = {};

    function applyChute(c) {
        $('#chute-display').text(c.alamat_chute || '-');
        if (c.back_no) {
            $('#backno-display').text(c.back_no);
        }
    }

    function fetchAlamatChute(loadingList, customerPart, internalPart) {
        if (!loadingList || !customerPart) {
            $('#chute-display').text('-');
            return;
        }

        var key = loadingList + '|' + customerPart + '|' + (internalPart || '');
        if (chuteCache[key]) {
            applyChute(chuteCache[key]);
            return;
        }

        $('#chute-display').text('Loading...');
        $.ajax({
            type: 'GET',
            url: "{{ route('pulling.alamat-chute') }}",
            data: {
                loadingList: loadingList,
                customerPart: customerPart,
                internalPart: internalPart || ''
            },
            dataType: 'json',
            success: function(resp) {
                // cache hanya saat sukses (jangan cache error jaringan)
                chuteCache[key] = {
                    alamat_chute: resp?.alamat_chute || null,
                    back_no: resp?.back_no || null
                };
                applyChute(chuteCache[key]);
            },
            error: function() {
                $('#chute-display').text('-');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('directToggle');
        if (!toggle) return;
        toggle.checked = isDirectOn();
        toggle.addEventListener('change', () => {
            setDirectMode(toggle.checked);
            // feedback kecil
            if (typeof notif === 'function') notif(toggle.checked ? 'success' : 'info',
                `Direct ${toggle.checked?'ON':'OFF'}`);
            setTimeout(() => $('#code').focus(), 200);
        });
    });

    const STORE_NAME = 'loadingList';
    const SERIAL_STORE_NAME = 'scanSerials';
    const SUMMARY_STORE_NAME = 'scanSummary';
    const SUMMARY_KEY = 'current';
    const SUMMARY_VERSION = 3;
    const REQUIRED_IDB_STORES = [STORE_NAME, SERIAL_STORE_NAME, SUMMARY_STORE_NAME];

    function ensurePullingStores(database) {
        REQUIRED_IDB_STORES.forEach((storeName) => {
            if (!database.objectStoreNames.contains(storeName)) {
                const store = database.createObjectStore(storeName);
                if (storeName === STORE_NAME) store.createIndex('loadingListDetail', 'seri');
            }
        });
    }

    function openPullingDB(dbName) {
        return new Promise((resolve, reject) => {
            if (!dbName) {
                reject(new Error('Nama DB pulling kosong'));
                return;
            }

            const req = indexedDB.open(dbName);
            req.onupgradeneeded = (e) => ensurePullingStores(e.target.result);
            req.onerror = () => reject(new Error(`Gagal buka DB "${dbName}"`));
            req.onblocked = () => reject(new Error(`DB "${dbName}" tertahan tab lain`));
            req.onsuccess = (ev) => {
                const db = ev.target.result;
                const missingStore = REQUIRED_IDB_STORES.some((storeName) => !db.objectStoreNames.contains(
                    storeName));
                if (!missingStore) return resolve(db);

                const nextVersion = (db.version || 1) + 1;
                db.close();
                const req2 = indexedDB.open(dbName, nextVersion);
                req2.onupgradeneeded = (e) => ensurePullingStores(e.target.result);
                req2.onsuccess = (e) => resolve(e.target.result);
                req2.onerror = () => reject(new Error('Upgrade DB pulling gagal'));
                req2.onblocked = () => reject(new Error('Upgrade DB pulling tertahan tab lain'));
            };
        });
    }

    function openDBEnsureStore(dbName) {
        return openPullingDB(dbName);
    }

    function scanSerialKey(customerPart, serial, scanEventId = '') {
        const baseKey = `${String(customerPart || '').trim()}|${String(serial || '').trim()}`;
        const eventId = String(scanEventId || '').trim();
        return eventId ? `${baseKey}|${eventId}` : baseKey;
    }

    function comparableCustomerPart(value) {
        return String(value || '').trim().replace(/[-\s]/g, '').toUpperCase();
    }

    function recordQuantityIdentityKey(record, loadingListNumber = '') {
        const loadingList = String(loadingListNumber || record?.loading_list_number || '').trim();
        const customer = comparableCustomerPart(record?.customer || recordCustomerIdentity(record));
        const internal = String(recordInternalIdentity(record) || record?.internal || '').trim();
        return `${loadingList}|${customer}|${internal}`;
    }

    function loadingListPartKey(customerPart, internalPart = '') {
        const customer = String(customerPart || '').trim();
        const internal = String(internalPart || '').trim();
        return internal ? `${customer}||${internal}` : customer;
    }

    function isCompositePartKey(key) {
        return String(key || '').includes('||');
    }

    function recordStorageKey(record) {
        const stored = String(record?.key || '').trim();
        if (stored) return stored;
        return loadingListPartKey(record?.customer, record?.internal);
    }

    function recordCustomerIdentity(record) {
        const keyCustomer = String(recordStorageKey(record)).split('||')[0] || '';
        return String(record?.customer || keyCustomer).trim();
    }

    function recordInternalIdentity(record) {
        const key = String(recordStorageKey(record));
        const keyInternal = isCompositePartKey(key) ? key.split('||').slice(1).join('||') : '';
        return String(record?.internal || keyInternal).trim();
    }

    function recordPartIdentity(record) {
        const internal = recordInternalIdentity(record);
        if (internal) return `internal:${internal}`;

        const customer = recordCustomerIdentity(record);
        return customer ? `customer:${customer}` : '';
    }

    function recordScannedCount(record) {
        return pullingInt(record?.scanned_count ?? record?.seri?.length ?? record?.actual_qty);
    }

    function cappedPullingCount(actual, target) {
        const normalizedActual = pullingInt(actual);
        const normalizedTarget = pullingInt(target);
        return normalizedTarget > 0 ? Math.min(normalizedActual, normalizedTarget) : normalizedActual;
    }

    function recordLoadingListIdentityKeys(record) {
        const entries = loadingListBucketEntries(record);
        const sources = entries.length ? entries.map((entry) => entry.number) : loadingListSources(record);
        const fallback = String(record?.loading_list_number || '').trim();
        const listNumbers = (sources.length ? sources : [fallback])
            .map((source) => String(source || '').trim())
            .filter(Boolean);
        const partIdentity = recordPartIdentity(record);

        return partIdentity ? listNumbers.map((source) => `${source}|${partIdentity}`) : [];
    }

    function recordDedupeScore(record) {
        return (cappedPullingCount(recordScannedCount(record), record?.total_qty) * 100000) +
            (isCompositePartKey(recordStorageKey(record)) ? 10000 : 0) +
            (recordInternalIdentity(record) ? 1000 : 0) +
            pullingInt(record?.total_qty);
    }

    function activeLoadingListRecords(records) {
        const bestByIdentity = new Map();
        const fallbackRecords = new Set();

        records.forEach((record) => {
            const identityKeys = recordLoadingListIdentityKeys(record);
            if (!identityKeys.length) {
                fallbackRecords.add(record);
                return;
            }

            const score = recordDedupeScore(record);
            identityKeys.forEach((key) => {
                const current = bestByIdentity.get(key);
                if (!current || score > current.score) {
                    bestByIdentity.set(key, {
                        record,
                        score
                    });
                }
            });
        });

        const active = new Set(fallbackRecords);
        bestByIdentity.forEach((item) => active.add(item.record));

        return records.filter((record) => {
            return active.has(record);
        });
    }

    function selectPreferredPartMatch(matches) {
        const compositeMatches = matches.filter((match) => isCompositePartKey(match.key || match.record?.key));
        const pool = compositeMatches.length ? compositeMatches : matches;

        return pool
            .slice()
            .sort((a, b) => recordDedupeScore(b.record) - recordDedupeScore(a.record))[0] || null;
    }

    function findPartRecordInStore(objectStore, customerPart, internalPart = '') {
        return new Promise((resolve, reject) => {
            const customer = String(customerPart || '').trim();
            const internal = String(internalPart || '').trim();
            const matches = [];
            const request = objectStore.openCursor();

            request.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) {
                    resolve(selectPreferredPartMatch(matches));
                    return;
                }

                const record = cursor.value || {};
                const key = String(cursor.key || recordStorageKey(record));
                if ((!record.key || isCompositePartKey(key)) && key) {
                    record.key = key;
                }

                const sameCustomer = comparableCustomerPart(record.customer) === comparableCustomerPart(
                    customer);
                const sameInternal = !internal || String(record.internal || '').trim() === internal;
                if (sameCustomer && sameInternal) {
                    matches.push({
                        key,
                        record
                    });
                }

                cursor.continue();
            };

            request.onerror = () => reject(request.error || new Error('Gagal membaca data part lokal'));
        });
    }

    function getAllLoadingListRecords(database) {
        return new Promise((resolve, reject) => {
            const records = [];
            const transaction = database.transaction([STORE_NAME], 'readonly');
            const request = transaction.objectStore(STORE_NAME).openCursor();

            request.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) {
                    resolve(activeLoadingListRecords(records));
                    return;
                }

                records.push(cursor.value);
                cursor.continue();
            };
            request.onerror = () => reject(request.error || new Error('Gagal membaca loading list lokal'));
        });
    }

    function fallbackScanRowsForRecord(record, seenSerials = new Set()) {
        const rows = [];
        const fallbackSerials = Array.isArray(record?.seri) ? record.seri : [];
        const buckets = loadingListBucketEntries(record);
        let bucketIndex = 0;
        let bucketUsed = 0;

        fallbackSerials.forEach((serial) => {
            const value = String(serial || '').trim();
            if (!value || seenSerials.has(value)) return;

            while (buckets[bucketIndex] && bucketUsed >= buckets[bucketIndex].target) {
                bucketIndex += 1;
                bucketUsed = 0;
            }

            const bucket = buckets[bucketIndex] || buckets[0];
            rows.push({
                serial: value,
                loading_list_number: bucket?.number || record?.loading_list_number,
                scanned_at: null
            });
            seenSerials.add(value);
            bucketUsed += 1;
        });

        return rows;
    }

    function legacyRecordMatches(record, legacyRecord) {
        if (!legacyRecord) return false;
        const customer = String(record?.customer || '').trim();
        const internal = String(record?.internal || '').trim();
        const legacyCustomer = String(legacyRecord?.customer || '').trim();
        const legacyInternal = String(legacyRecord?.internal || '').trim();
        return legacyCustomer === customer && (!legacyInternal || legacyInternal === internal);
    }

    function getLegacyPartRecord(database, record) {
        return new Promise((resolve) => {
            const key = String(record?.customer || '').trim();
            if (!key || key === recordStorageKey(record)) {
                resolve(null);
                return;
            }

            const transaction = database.transaction([STORE_NAME], 'readonly');
            const request = transaction.objectStore(STORE_NAME).get(key);
            request.onsuccess = () => {
                const legacyRecord = request.result;
                resolve(legacyRecordMatches(record, legacyRecord) ? legacyRecord : null);
            };
            request.onerror = () => resolve(null);
        });
    }

    function getScanRowsByKey(database, record, key, seenEvents = new Set(), seenSerials = new Set(),
        matchInternal = false) {
        return new Promise((resolve, reject) => {
            const rows = [];
            const internal = String(record?.internal || '').trim();
            const prefix = `${key}|`;
            const range = IDBKeyRange.bound(prefix, `${prefix}\uffff`);
            const transaction = database.transaction([SERIAL_STORE_NAME], 'readonly');
            const request = transaction.objectStore(SERIAL_STORE_NAME).openCursor(range);

            request.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) {
                    resolve(rows);
                    return;
                }

                const value = String(cursor.value?.serial || '').trim();
                const rowInternal = String(cursor.value?.internal || '').trim();
                if (matchInternal && rowInternal && rowInternal !== internal) {
                    cursor.continue();
                    return;
                }

                const eventId = String(cursor.value?.scan_event_id || cursor.value?.mutation_id || '').trim();
                const identity = eventId ? `event:${eventId}` : `legacy:${key}|${value}`;
                if (value && !seenEvents.has(identity)) {
                    seenEvents.add(identity);
                    seenSerials.add(value);
                    rows.push({
                        serial: value,
                        scan_event_id: eventId,
                        loading_list_number: cursor.value?.loading_list_number || record
                            ?.loading_list_number,
                        scanned_at: cursor.value?.scanned_at || null,
                        synced_from_mutation: cursor.value?.synced_from_mutation === true,
                        verified_from_mutation: cursor.value?.verified_from_mutation === true
                    });
                }
                cursor.continue();
            };
            request.onerror = () => reject(request.error || new Error('Gagal membaca serial scan'));
        });
    }

    async function getScanRowsForPart(database, record) {
        const seenEvents = new Set();
        const seenSerials = new Set();
        const currentKey = recordStorageKey(record);
        const rows = await getScanRowsByKey(database, record, currentKey, seenEvents, seenSerials);
        const legacyKey = String(record?.customer || '').trim();

        if (legacyKey && legacyKey !== currentKey) {
            rows.push(...await getScanRowsByKey(database, record, legacyKey, seenEvents, seenSerials, true));
            const legacyRecord = await getLegacyPartRecord(database, record);
            if (legacyRecord) rows.push(...fallbackScanRowsForRecord(legacyRecord, seenSerials));
        }

        rows.sort((a, b) => serialScanTimestamp(a.scanned_at) - serialScanTimestamp(b.scanned_at));
        return rows.concat(fallbackScanRowsForRecord(record, seenSerials));
    }

    function getPartRecordFromIDB(customerPart) {
        return new Promise(async (resolve, reject) => {
            try {
                const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
                if (!dbName) return reject(new Error('DB belum siap, scan Loading List dulu.'));
                const db = await openDBEnsureStore(dbName, STORE_NAME);
                const tx = db.transaction([STORE_NAME], 'readonly');
                const st = tx.objectStore(STORE_NAME);
                findPartRecordInStore(st, customerPart).then((match) => {
                    db.close();
                    match ? resolve(match.record) : reject(new Error(
                        `Part "${customerPart}" tidak ada`));
                }).catch((e) => {
                    db.close();
                    reject(e?.target?.error || new Error('Gagal ambil record'));
                });
            } catch (err) {
                reject(err);
            }
        });
    }

    async function directSingleScan(customerPart) {
        try {
            // safety: hanya jalan jika Direct ON
            if ((localStorage.getItem('directMode') || 'off') !== 'on') return;

            const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
            if (!dbName) throw new Error('DB belum siap. Scan Loading List dulu.');

            // 1) Ambil record dari IDB -> butuh internal & loading_list_number
            const rec = await getPartRecordFromIDB(customerPart);

            const internalPart = String(rec.internal || '').trim();
            const partKey = recordStorageKey(rec);
            const loadingList = nextLoadingListForScan(rec);
            if (!internalPart || !loadingList) throw new Error('Mapping internal/LL tidak ditemukan');

            // 2) Panggil backend untuk increment actual_kanban_qty (single scan)
            const res = await $.ajax({
                type: 'GET',
                dataType: 'json',
                url: "{{ route('kanban.scanned') }}",
                data: {
                    loadingList,
                    internalPart,
                    customerPart
                }
            });
            if (!res || res.status !== 'success') throw new Error(res?.message || 'Store gagal');

            // 3) Update IDB lokal: simpan serial ringan + seri legacy untuk UI lama
            let directSerial = '';
            await new Promise((resolve, reject) => {
                openPullingDB(dbName).then((db) => {
                    const tx = db.transaction([STORE_NAME, SERIAL_STORE_NAME, SUMMARY_STORE_NAME],
                        'readwrite');
                    const st = tx.objectStore(STORE_NAME);
                    const serialStore = tx.objectStore(SERIAL_STORE_NAME);
                    const summaryStore = tx.objectStore(SUMMARY_STORE_NAME);
                    const g = st.get(partKey);

                    tx.oncomplete = () => {
                        db.close();
                        resolve();
                    };
                    tx.onerror = (event) => {
                        db.close();
                        reject(event?.target?.error || new Error('Gagal simpan IDB'));
                    };

                    g.onsuccess = () => {
                        const data = g.result;
                        if (!data) {
                            return reject(new Error('Record hilang di IDB'));
                        }
                        if (!Array.isArray(data.seri)) data.seri = [];
                        const currentCount = parseInt(data.scanned_count ?? data.seri.length,
                            10) || 0;
                        if (currentCount >= (data.total_qty || 0)) {
                            return reject(new Error('Part sudah complete'));
                        }
                        const serial = `AUTO-DIRECT-${Date.now()}`;
                        directSerial = serial;
                        data.scanned_count = currentCount + 1;
                        data.actual_qty = data.scanned_count;
                        incrementLoadingListBucket(data, loadingList);
                        serialStore.put({
                            customer_part: customerPart,
                            part_key: partKey,
                            internal: internalPart,
                            serial,
                            loading_list_number: loadingList,
                            scanned_at: Date.now(),
                            synced_from_mutation: false,
                            verified_from_mutation: false
                        }, scanSerialKey(partKey, serial));

                        const summaryRequest = summaryStore.get(SUMMARY_KEY);
                        summaryRequest.onsuccess = () => {
                            const actual = parseInt(localStorage.getItem(
                                'pulling_total_actual') || '0', 10) || 0;
                            const target = parseInt(localStorage.getItem(
                                    'pulling_total_target') || data.total_qty || '0',
                                10) || 0;
                            const summary = summaryRequest.result || {
                                totalActual: actual,
                                totalTarget: target
                            };
                            summary.version = SUMMARY_VERSION;
                            summary.totalActual = (parseInt(summary.totalActual || 0, 10) ||
                                0) + 1;
                            summary.totalTarget = parseInt(summary.totalTarget || target,
                                10) || target;
                            summary.updatedAt = Date.now();
                            summaryStore.put(summary, SUMMARY_KEY);
                        };
                        st.put(data, partKey);
                    };
                    g.onerror = (e) => {
                        reject(e?.target?.error || new Error('Gagal ambil record'));
                    };
                }).catch(reject);
            });

            if (directSerial && typeof pushScanPairHistory === 'function') {
                pushScanPairHistory(customerPart, internalPart, directSerial);
            }

            // 4) Update UI minimal (qty naik, indikator hijau, bersihkan panel customer)
            try {
                // Ambil ulang rec untuk hitung panjang terbaru (opsional, boleh perkiraan +1)
                openPullingDB(dbName).then((db2) => {
                    const tx2 = db2.transaction([STORE_NAME], 'readonly');
                    const st2 = tx2.objectStore(STORE_NAME);
                    const g2 = st2.get(partKey);
                    g2.onsuccess = () => {
                        const latest = g2.result || rec;
                        const nowLen = parseInt(latest?.scanned_count ?? latest?.seri?.length ?? ((rec
                                ?.seri
                                ?.length || 0) + 1),
                            10) || 0;
                        $('#qty-display').text(`${nowLen}/${latest?.total_qty ?? rec.total_qty}`);
                        if (typeof successIndicator === 'function') successIndicator();
                        if (typeof tmminSuccessIndicator === 'function') tmminSuccessIndicator();
                        if (typeof okSound === 'function') okSound();
                        $('#cust-display').text('-');
                        $('#chute-display').text('-');
                        $('#backno-display').text('-');
                        localStorage.removeItem('customerPart');
                        if (typeof incrementPullingQuantity === 'function') incrementPullingQuantity(1);
                        db2.close();
                    };
                }).catch(() => {});
            } catch (_) {}
        } catch (err) {
            if (typeof notif === 'function') notif('error', err?.message || String(err));
            else alert(err?.message || err);
            if (typeof errorIndicator === 'function') errorIndicator();
            setTimeout(() => $('#code').focus(), 400);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('directToggle');

        // helper: fokuskan #code dan taruh caret di akhir
        const focusCode = () => {
            const el = document.getElementById('code');
            if (!el) return;
            el.focus();
            try {
                const len = el.value ? el.value.length : 0;
                el.setSelectionRange(len, len);
            } catch (_) {}
        };

        if (toggle) {
            // sinkronisasi awal (kalau kamu pakai localStorage)
            if (typeof setDirectMode === 'function') setDirectMode(toggle.checked);

            // saat toggle berubah → simpan mode + fokus ke #code
            toggle.addEventListener('change', () => {
                if (typeof setDirectMode === 'function') setDirectMode(toggle.checked);
                // beri sedikit jeda agar animasi selesai, lalu fokus
                setTimeout(focusCode, 150);
            });

            // bila label diklik cepat, tetap re-fokus setelah mouseup
            const track = document.querySelector('label.direct-toggle__track[for="directToggle"]');
            if (track) {
                track.addEventListener('mouseup', () => setTimeout(focusCode, 0));
                track.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') setTimeout(focusCode, 0);
                });
            }
        }
    });


    function initApp() {
        // check solve status
        if (localStorage.getItem('status')) {
            $('#modalConfirmation').on('shown.bs.modal', function() {
                $('#input-confirmation').focus();
            })
            $('#modalConfirmation').modal('show');

            $(document).on('click', function() {
                $('#input-confirmation').focus();
            })
        }

        let customer = localStorage.getItem('customer');
        let cycle = localStorage.getItem('cycle');
        let loadingList = getLoadingListNumber();
        checkLoadingList();
        // iterate local storage
        for (key in loadingList) {
            // loading list display
            appendLoadingListChip(loadingList[key]);

            pullingQuantity();
            $('#customer-display').text(customer);
            $('#cycle-display').text(cycle);
        }

        // set skid
        let skid = localStorage.getItem('skid');
        if (!skid) {
            localStorage.setItem('skid', 1);
        }

        // display skid if customer is TMMIN
        if (customer == 'TMMIN KRW 1') {
            $('.skid-display').append(pullingSkidMarkup(skid));
        }

        if (getLoadingListNumber().length == 0) {
            $('#modalLoadingListScan').on('shown.bs.modal', function() {
                $('#input-loadingList').focus();
            })
            $('#modalLoadingListScan').modal('show');

            // empty text
            $('#customer-display').text('customer');
            $('#cycle-display').text('cycle');
        }
        $('#code').focus();
    }

    function successIndicator() {
        $('#indicator')
            .removeClass('bg-warning');
        $('#indicator')
            .removeClass('bg-danger');
        $('#indicator')
            .addClass('bg-success');
    }

    function tmminSuccessIndicator() {
        $('#tmmin-indicator')
            .removeClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-danger');
        $('#tmmin-indicator')
            .addClass('bg-success');
    }

    function resetIndicator() {
        $('#tmmin-indicator')
            .addClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-danger');
        $('#tmmin-indicator')
            .removeClass('bg-success');
    }

    function errorIndicator() {
        $('#indicator')
            .removeClass('bg-warning');
        $('#indicator')
            .removeClass('bg-success');
        $('#indicator')
            .addClass('bg-danger');
    }

    function tmminErrorIndicator() {
        $('#tmmin-indicator')
            .removeClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-success');
        $('#tmmin-indicator')
            .addClass('bg-danger');
    }

    const SCAN_HISTORY_KEY = 'pulling_scan_pair_history';
    const SCAN_HISTORY_LIMIT = 500;
    const SCAN_HISTORY_REMOTE_URL = "{{ route('pulling.scan-history') }}";

    function scanHistoryStorageKey() {
        const pds = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber') || 'global';
        return `${SCAN_HISTORY_KEY}_${pds}`;
    }

    function loadScanHistory() {
        try {
            const raw = localStorage.getItem(scanHistoryStorageKey()) || localStorage.getItem(SCAN_HISTORY_KEY) || '[]';
            const history = JSON.parse(raw);
            return Array.isArray(history) ? history.slice(0, SCAN_HISTORY_LIMIT) : [];
        } catch (_) {
            return [];
        }
    }

    const scanHistoryState = loadScanHistory();
    let scanHistoryTotalCount = scanHistoryState.length;

    function normalizeScanHistoryItems(items) {
        const seen = new Set();

        return (Array.isArray(items) ? items : [])
            .map((item) => ({
                customer: String(item?.customer || item?.customer_part || '-').trim() || '-',
                internal: String(item?.internal || '-').trim() || '-',
                serial: String(item?.serial || '-').trim() || '-',
                loading_list: String(item?.loading_list || item?.loading_list_number || '').trim(),
                scanned_at: parseInt(item?.scanned_at || 0, 10) || 0,
                scan_event_id: String(item?.scan_event_id || item?.mutation_id || '').trim()
            }))
            .filter((item) => item.serial !== '-')
            .filter((item) => {
                const key = item.scan_event_id ?
                    `event:${item.scan_event_id}` :
                    `${item.customer}|${item.internal}|${item.serial}|${item.scanned_at || 'legacy'}`;
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            })
            .sort((a, b) => b.scanned_at - a.scanned_at)
            .slice(0, SCAN_HISTORY_LIMIT);
    }

    function saveScanHistory() {
        localStorage.setItem(scanHistoryStorageKey(), JSON.stringify(scanHistoryState));
    }

    function scanHistoryTotalFromSync(syncRows, fallback = 0) {
        const total = (Array.isArray(syncRows) ? syncRows : []).reduce((sum, row) => {
            const scanned = pullingInt(row?.scanned_count);
            if (scanned > 0) return sum + scanned;
            return sum + (Array.isArray(row?.serials) ? row.serials.length : 0);
        }, 0);

        return total || fallback;
    }

    function setScanHistory(items, totalCount = null) {
        const history = normalizeScanHistoryItems(items);
        const parsedTotal = parseInt(totalCount, 10);
        scanHistoryTotalCount = Number.isNaN(parsedTotal) ? history.length : Math.max(0, parsedTotal);

        scanHistoryState.splice(0, scanHistoryState.length, ...history);
        saveScanHistory();
        renderScanHistory();

        return history.length > 0;
    }

    function syncIndexedDBFromMutation(syncRows, allowedLoadingLists = []) {
        const rows = (Array.isArray(syncRows) ? syncRows : [])
            .filter((row) => String(row?.customer || '').trim() && String(row?.internal || '').trim());
        const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
        const allowed = new Set((Array.isArray(allowedLoadingLists) ? allowedLoadingLists : [allowedLoadingLists])
            .map((number) => String(number || '').trim())
            .filter(Boolean));

        if (!dbName || !rows.length) return Promise.resolve(false);

        const grouped = rows.reduce((carry, row) => {
            const customer = String(row.customer || '').trim();
            const internal = String(row.internal || '').trim();
            const partKey = loadingListPartKey(customer, internal);

            if (!carry[partKey]) carry[partKey] = {
                key: partKey,
                customer,
                internal,
                rows: []
            };
            carry[partKey].rows.push(row);
            return carry;
        }, {});

        return openPullingDB(dbName).then((database) => {
            return new Promise((resolve, reject) => {
                const transaction = database.transaction([STORE_NAME, SERIAL_STORE_NAME], 'readwrite');
                const objectStore = transaction.objectStore(STORE_NAME);
                const serialStore = transaction.objectStore(SERIAL_STORE_NAME);
                const fail = (error) => {
                    database.close();
                    reject(error || new Error('Gagal sync mutation ke IndexedDB'));
                };

                Object.values(grouped).forEach((group) => {
                    const request = objectStore.get(group.key);

                    request.onsuccess = function() {
                        const firstRow = group.rows[0] || {};
                        const firstLoadingList = String(firstRow.loading_list || '').trim();
                        const firstQtyPerKbn = Number(firstRow.qty_per_kbn || 0) || 0;
                        const record = request.result || {
                            key: group.key,
                            loading_list_number: firstLoadingList,
                            internal: group.internal,
                            customer: group.customer,
                            qty_per_kbn: firstQtyPerKbn,
                            actual_qty: 0,
                            scanned_count: 0,
                            total_qty: 0,
                            source_loading_lists: firstLoadingList ? [
                                firstLoadingList
                            ] : [],
                            loading_list_buckets: {},
                            seri: []
                        };

                        record.key = group.key;
                        record.internal = group.internal;
                        record.customer = group.customer;

                        const buckets = ensureLoadingListBuckets(record);
                        const sources = new Set(loadingListSources(record)
                            .filter((source) => !allowed.size || allowed.has(String(
                                source || '').trim())));

                        if (allowed.size) {
                            Object.keys(buckets).forEach((source) => {
                                if (!allowed.has(String(source || '').trim()))
                                    delete buckets[source];
                            });
                        }

                        group.rows.forEach((row) => {
                            const loadingList = String(row.loading_list || '')
                                .trim();
                            const target = pullingInt(row.target);
                            const scanned = pullingInt(row.scanned_count);

                            if (allowed.size && loadingList && !allowed.has(
                                    loadingList)) return;

                            if (loadingList) {
                                sources.add(loadingList);
                                buckets[loadingList] = buckets[loadingList] || {
                                    target,
                                    scanned: 0
                                };
                                buckets[loadingList].target = target || pullingInt(
                                    buckets[loadingList].target);
                                buckets[loadingList].scanned = scanned;
                            }

                            (Array.isArray(row.serials) ? row.serials : []).forEach(
                                (item, index) => {
                                    const serial = String(item?.serial || '')
                                        .trim();
                                    if (!serial) return;

                                    const scanEventId = String(item?.mutation_id || item
                                        ?.scan_event_id || item?.scanned_at || index).trim();
                                    const serialKey = scanSerialKey(group.key,
                                        serial, scanEventId);
                                    serialStore.delete(scanSerialKey(group.key, serial));
                                    serialStore.put({
                                        customer_part: group.customer,
                                        part_key: group.key,
                                        internal: group.internal,
                                        serial,
                                        scan_event_id: scanEventId,
                                        mutation_id: item?.mutation_id || null,
                                        loading_list_number: loadingList || record
                                            .loading_list_number,
                                        scanned_at: serialScanTimestamp(item?.scanned_at),
                                        synced_from_mutation: true,
                                        verified_from_mutation: item?.verified === true
                                    }, serialKey);
                                });
                        });

                        record.source_loading_lists = Array.from(sources);
                        record.loading_list_buckets = buckets;
                        record.loading_list_number = record.loading_list_number ||
                            firstLoadingList;
                        record.qty_per_kbn = Number(record.qty_per_kbn || firstQtyPerKbn ||
                            0) || 0;
                        record.scanned_count = Object.values(buckets).reduce((sum,
                                bucket) => sum +
                            pullingInt(bucket?.scanned), 0);
                        record.actual_qty = record.scanned_count;
                        record.total_qty = Object.values(buckets).reduce((sum, bucket) =>
                            sum + pullingInt(
                                bucket?.target), 0) || pullingInt(record.total_qty);
                        record.seri = Array.isArray(record.seri) ? record.seri : [];
                        record.synced_from_mutation_at = Date.now();

                        objectStore.put(record, group.key);
                    };

                    request.onerror = function() {
                        try {
                            transaction.abort();
                        } catch (_) {}
                    };
                });

                transaction.oncomplete = function() {
                    refreshPullingSummaryFromRecords(database)
                        .then(() => {
                            database.close();
                            resolve(true);
                        })
                        .catch(fail);
                };
                transaction.onerror = () => fail(transaction.error);
                transaction.onabort = () => fail(transaction.error);
            });
        });
    }

    function renderScanHistory() {
        const list = document.getElementById('scanHistoryList');
        const count = document.getElementById('scanHistoryCount');
        if (!list || !count) return;

        count.textContent = scanHistoryTotalCount;
        list.innerHTML = '';

        if (!scanHistoryState.length) {
            list.innerHTML =
                '<div class="pulling-history-item"><span class="pulling-history-icon"><i class="fas fa-link"></i></span><span class="pulling-history-copy"><span class="pulling-history-main">Belum ada pasangan kanban</span><span class="pulling-history-meta">Scan customer lalu internal kanban</span></span></div>';
            return;
        }

        scanHistoryState.forEach(function(item) {
            const row = document.createElement('div');
            row.className = 'pulling-history-item is-success';
            row.innerHTML =
                '<span class="pulling-history-icon"><i class="fas fa-link"></i></span><span class="pulling-history-copy"><span class="pulling-history-main"></span><span class="pulling-history-meta"></span></span>';
            row.querySelector('.pulling-history-main').textContent = 'Kanban Customer: ' + item.customer;
            row.querySelector('.pulling-history-meta').textContent = 'Kanban Internal: ' + item.internal +
                ' · Serial: ' + item.serial;
            list.appendChild(row);
        });
    }

    function pushScanPairHistory(customer, internal, serial, scanEventId = '') {
        setScanHistory([{
            customer: String(customer || '-').trim() || '-',
            internal: String(internal || '-').trim() || '-',
            serial: String(serial || '-').trim() || '-',
            scanned_at: Date.now(),
            scan_event_id: String(scanEventId || '').trim()
        }].concat(scanHistoryState), scanHistoryTotalCount + 1);
    }

    renderScanHistory();

    function hydrateScanHistoryFromIndexedDB() {
        const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
        if (!dbName) return Promise.resolve(false);

        return openPullingDB(dbName).then((database) => {
            return new Promise((resolve) => {
                const rows = [];
                const transaction = database.transaction([SERIAL_STORE_NAME], 'readonly');
                const request = transaction.objectStore(SERIAL_STORE_NAME).openCursor();

                request.onsuccess = function(event) {
                    const cursor = event.target.result;
                    if (!cursor) return;

                    const item = cursor.value || {};
                    if (item.serial) rows.push(item);
                    cursor.continue();
                };

                transaction.oncomplete = function() {
                    database.close();
                    const history = rows
                        .sort((a, b) => (parseInt(b.scanned_at || 0, 10) || 0) - (parseInt(a
                            .scanned_at || 0,
                            10) || 0))
                        .slice(0, SCAN_HISTORY_LIMIT)
                        .map((item) => ({
                            customer: String(item.customer_part || '-').trim() || '-',
                            internal: String(item.internal || '-').trim() || '-',
                            serial: String(item.serial || '-').trim() || '-',
                            scanned_at: parseInt(item.scanned_at || 0, 10) || 0,
                            scan_event_id: String(item.scan_event_id || item.mutation_id || '').trim()
                        }));

                    resolve(setScanHistory(history, rows.length));
                };

                transaction.onerror = function() {
                    database.close();
                    resolve(false);
                };
            });
        }).catch(() => false);
    }

    hydrateScanHistoryFromIndexedDB();

    function hydrateScanHistoryFromMutations(extraLoadingLists = []) {
        const storedLoadingLists = typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
        const loadingLists = Array.from(new Set(storedLoadingLists
            .concat(Array.isArray(extraLoadingLists) ? extraLoadingLists : [extraLoadingLists])
            .map((number) => String(number || '').trim())
            .filter(Boolean)));
        const pds = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber') || '';

        if (!loadingLists.length) return Promise.resolve(false);

        return new Promise((resolve) => {
            $.ajax({
                type: 'GET',
                url: SCAN_HISTORY_REMOTE_URL,
                dataType: 'json',
                data: {
                    pds,
                    loading_lists: loadingLists,
                    limit: SCAN_HISTORY_LIMIT
                },
                success: function(data) {
                    const hasRemoteHistory = data?.status === 'success' && Array.isArray(data
                        .history);
                    if (hasRemoteHistory) {
                        setScanHistory(data.history, scanHistoryTotalFromSync(data?.sync, data
                            .history.length));
                    }

                    syncIndexedDBFromMutation(data?.sync, loadingLists)
                        .then((synced) => {
                            if (!synced) {
                                resolve(hasRemoteHistory);
                                return;
                            }

                            hydrateScanHistoryFromIndexedDB()
                                .then(() => resolve(true))
                                .catch(() => resolve(true));
                        })
                        .catch(() => resolve(hasRemoteHistory));
                },
                error: function() {
                    resolve(false);
                }
            });
        });
    }

    let scanHistoryBackgroundSyncTimer = null;
    let scanHistoryBackgroundSyncPromise = null;
    let scanHistoryBackgroundSyncedAt = 0;
    const SCAN_HISTORY_BACKGROUND_SYNC_INTERVAL_MS = 15000;

    function refreshActiveCustomerQuantityFromIndexedDB() {
        const customerPart = localStorage.getItem('customerPart');
        if (!customerPart) return;

        getPartRecordFromIDB(customerPart)
            .then((record) => {
                if (comparableCustomerPart(localStorage.getItem('customerPart')) !== comparableCustomerPart(record
                        ?.customer)) {
                    return;
                }

                updatePartQuantityDisplay(record);
                refreshPartQuantityDisplayFromIndexedDB(record);
            })
            .catch(() => {});
    }

    function scheduleScanHistoryBackgroundSync(delay = 250) {
        if (scanHistoryBackgroundSyncTimer || scanHistoryBackgroundSyncPromise) return;

        scanHistoryBackgroundSyncTimer = setTimeout(() => {
            scanHistoryBackgroundSyncTimer = null;

            if (Date.now() - scanHistoryBackgroundSyncedAt < SCAN_HISTORY_BACKGROUND_SYNC_INTERVAL_MS) {
                return;
            }

            scanHistoryBackgroundSyncPromise = hydrateScanHistoryFromMutations()
                .then((synced) => {
                    scanHistoryBackgroundSyncedAt = Date.now();
                    if (synced) refreshActiveCustomerQuantityFromIndexedDB();
                })
                .catch(() => {})
                .finally(() => {
                    scanHistoryBackgroundSyncPromise = null;
                });
        }, delay);
    }

    hydrateScanHistoryFromMutations()
        .then(() => {
            scanHistoryBackgroundSyncedAt = Date.now();
        })
        .catch(() => {});

    $(document).on('click', '#scanHistoryToggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const wrap = document.getElementById('scanHistoryAccordion');
        const body = document.getElementById('scanHistoryBody');
        if (!wrap || !body) return;

        const open = body.hidden;
        body.hidden = !open;
        wrap.classList.toggle('is-open', open);
        this.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    let notifTimer = null;

    function notif(color, text) {
        const type = String(color || 'info').toLowerCase().trim();
        const message = String(text == null ? '' : text);
        const variant = type.indexOf('error') === 0 || type === 'danger' ?
            'error' :
            (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info'));
        const meta = {
            success: ['Berhasil', 'fas fa-check'],
            error: ['Gagal', 'fas fa-exclamation'],
            warning: ['Perhatian', 'fas fa-exclamation-triangle'],
            info: ['Info', 'fas fa-info']
        } [variant];
        const modal = document.getElementById('notifModal');
        const card = document.getElementById('divNotif');
        const icon = document.getElementById('notifIcon');
        const title = document.getElementById('notifTitle');
        const messageEl = document.getElementById('notif');

        if (!modal || !card || !icon || !title || !messageEl) {
            console[type.indexOf('error') === 0 ? 'error' : 'log'](message);
            return;
        }

        clearTimeout(notifTimer);
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        card.className = 'pulling-scan-notif-card is-' + variant;
        icon.innerHTML = '<i class="' + meta[1] + '"></i>';
        title.textContent = meta[0];
        messageEl.textContent = message;

        requestAnimationFrame(function() {
            modal.classList.add('is-visible');
        });

        notifTimer = setTimeout(function() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            setTimeout(function() {
                if (!modal.classList.contains('is-visible')) modal.hidden = true;
            }, 180);
        }, variant === 'success' ? 1900 : 2800);
    }

    function loadingListModal() {
        let loadingList = localStorage.getItem('loadingList');
        $('#input-loadingList').val('');
        setTimeout(() => {
            if (!loadingList) {
                $('#modalLoadingListScan').on('shown.bs.modal',
                    function() {
                        $('#input-loadingList').focus();
                    })
                $('#modalLoadingListScan').modal('show');
            }
        }, 1500);
    }

    function confirmationModal() {
        let status = localStorage.getItem('status');
        $('#input-confirmation').val('');
        setTimeout(() => {
            if (status) {
                $('#modalConfirmation').on('shown.bs.modal',
                    function() {
                        $('#input-confirmation').focus();
                    })
                $('#modalConfirmation').modal('show');
            }
        }, 1500);
    }

    function loadingListModal2() {
        $('#input-loadingList').val('');
        setTimeout(() => {
            $('#modalLoadingListScan').on('shown.bs.modal',
                function() {
                    $('#input-loadingList').focus();
                })
            $('#modalLoadingListScan').modal('show');
        }, 1500);
    }

    const customerMetaRequests = {};
    const CUSTOMER_META_CACHE_PREFIX = 'pulling_customer_meta:';

    function customerMetaCacheKey(customer, pds = null) {
        return `${CUSTOMER_META_CACHE_PREFIX}${String(customer || '').trim()}|${String(pds || '').trim()}`;
    }

    function getCachedCustomerMeta(customer, pds = null) {
        try {
            const cached = localStorage.getItem(customerMetaCacheKey(customer, pds));
            return cached ? JSON.parse(cached) : null;
        } catch (e) {
            return null;
        }
    }

    function setCachedCustomerMeta(customer, pds = null, data) {
        try {
            localStorage.setItem(customerMetaCacheKey(customer, pds), JSON.stringify(data));
        } catch (e) {}
    }

    function applyCustomerMeta(data, updateDisplay = true) {
        if (updateDisplay) {
            $('#customer-display').text(data.customer);
            localStorage.setItem('customer', data.customer);
        }

        localStorage.setItem('char_first', data.first);
        localStorage.setItem('char_length', data.length);
        localStorage.setItem('char_total', data.total);
    }

    function fetchCustomerMeta(customer, pds = null) {
        const cacheKey = customerMetaCacheKey(customer, pds);
        const cached = getCachedCustomerMeta(customer, pds);

        if (cached?.status === 'success') {
            return Promise.resolve(cached);
        }

        if (customerMetaRequests[cacheKey]) {
            return customerMetaRequests[cacheKey];
        }

        let url = "{{ url('pulling/customer-check') }}/" + customer;
        if (pds) {
            url += '/' + encodeURIComponent(pds);
        }

        customerMetaRequests[cacheKey] = new Promise(function(resolve, reject) {
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        setCachedCustomerMeta(customer, pds, data);
                        resolve(data);
                    } else {
                        reject(data.message || 'Unknown error');
                    }
                },
                error: function(xhr) {
                    reject(new Error(xhr.statusText || 'Gagal membaca data customer'));
                }
            });
        });

        customerMetaRequests[cacheKey].then(
            () => delete customerMetaRequests[cacheKey],
            () => delete customerMetaRequests[cacheKey]
        );

        return customerMetaRequests[cacheKey];
    }

    function customerCheck(customer, pds = null) {
        return fetchCustomerMeta(customer, pds).then(function(data) {
            applyCustomerMeta(data, true);
        });
    }


    function checkLoadingList() {
        const pds = localStorage.getItem('pds_local');
        if (!pds) {
            $('#total-ll').text(0);
            return;
        }

        const ll = [];

        openPullingDB(pds).then((database) => {
            const transaction = database.transaction([STORE_NAME], 'readonly');
            const objectStore = transaction.objectStore(STORE_NAME);
            const cursorRequest = objectStore.openCursor();

            cursorRequest.onsuccess = function(event) {
                let cursor = event.target.result;
                if (cursor) {
                    // check each loading list
                    if (!ll.includes(cursor.value.loading_list_number)) {
                        ll.push(cursor.value.loading_list_number);
                    }

                    cursor.continue();
                } else {
                    $('#total-ll').text(ll.length);
                }
            }

            transaction.oncomplete = function() {
                database.close();
            };

            cursorRequest.onerror = function(event) {
                notif('error', event?.target?.error?.message || 'Gagal membaca loading list');
            };
        }).catch(() => {
            $('#total-ll').text(0);
        });
    }

    // Function to get the Skid data (either Skid 1 or Skid 2)
    function getSkidData(skidId) {
        return new Promise(function(resolve, reject) {
            const pds = localStorage.getItem('pds_local');
            if (!pds) {
                resolve([]);
                return;
            }

            openPullingDB(pds).then((database) => {
                const transaction = database.transaction([STORE_NAME], 'readonly');
                const objectStore = transaction.objectStore(STORE_NAME);
                let allData = [];

                objectStore.openCursor().onsuccess = function(event) {
                    let cursor = event.target.result;
                    if (cursor) {
                        allData.push(cursor.value);
                        cursor.continue();
                    } else {
                        // Group data by skidNo
                        let skidGroups = {};
                        allData.forEach(item => {
                            if (!skidGroups[item.skidNo]) {
                                skidGroups[item.skidNo] = [];
                            }
                            skidGroups[item.skidNo].push(item);
                        });

                        // Extract skids and sort them
                        let skids = Object.keys(skidGroups).sort();

                        if (skidId === 1) {
                            let skid1Data = skidGroups[skids[0]] || [];
                            localStorage.setItem("skid_1_data", JSON.stringify(skid1Data));
                            resolve(skid1Data);
                        } else {
                            let skid1Data = JSON.parse(localStorage.getItem("skid_1_data") || "[]");
                            let skid1SkidNo = skid1Data.length > 0 ? skid1Data[0].skidNo : null;

                            let skid2Data = allData.filter(d => d.skidNo !== skid1SkidNo);
                            resolve(skid2Data);
                        }
                    }
                };

                transaction.onerror = function(event) {
                    reject("Error reading IndexedDB: " + event.target.error);
                };
                transaction.oncomplete = function() {
                    database.close();
                };
            }).catch(reject);
        });
    }

    function customerCharStore(customer, pds = null) {
        return fetchCustomerMeta(customer, pds)
            .then(function(data) {
                applyCustomerMeta(data, false);
            })
            .catch(function(error) {
                notif('error', error?.message || error);
                loadingListModal();
            });
    }

    function errorStore(message = null, expected = null, scanned = null) {
        $.ajax({
            type: 'GET',
            url: "{{ route('error.store') }}",
            _token: "{{ csrf_token() }}",
            dataType: 'json',
            data: {
                message: message,
                expected: expected,
                scanned: scanned
            },
            success: function(data) {
                console.log("Error recorded");
            },
            error: function(xhr) {
                console.log(xhr);
            }
        });
    }

    // Function to update the button based on the status of Skid 1 and Skid 2
    function updateButtonStatus() {
        var button = $("#close-skid");

        if (!skid1Sent) {
            // button.text("Close Skid 1");
            button.off('click').on('click', function() {
                sendSkidData(1);
            });
        } else if (!skid2Sent) {
            button.text("Close Skid 2");
            button.off('click').on('click', function() {
                sendSkidData(2);
            });
        } else {
            button.text("Semua Data Terkirim").prop("disabled",
                true);
        }
    }

    function setPullingQuantityDisplay(totalActual, totalTarget) {
        const safeTarget = pullingInt(totalTarget);
        const safeActual = cappedPullingCount(totalActual, safeTarget);
        const statusContainer = $('#pullingStatusContainer');

        localStorage.setItem('pulling_total_actual', String(safeActual));
        localStorage.setItem('pulling_total_target', String(safeTarget));
        $('#pullingQty').text(`${safeActual}/${safeTarget}`);
        statusContainer.removeClass('bg-danger bg-warning bg-success');

        if (safeActual == 0) {
            statusContainer.addClass('bg-danger');
            $('#pullingStatus').text(' - Ayo Pulling!')
        } else if (safeActual > 0 && safeActual < safeTarget) {
            statusContainer.addClass('bg-warning');
            $('#pullingStatus').text(' - Belum Lengkap!')
        } else {
            statusContainer.addClass('bg-success');
            $('#pullingStatus').text(' - Pulling Selesai!')
        }
    }

    function pullingInt(value) {
        return parseInt(value ?? 0, 10) || 0;
    }

    function serialScanTimestamp(value) {
        if (value === null || value === undefined || value === '') return 0;

        const numericValue = Number(value);
        if (Number.isFinite(numericValue) && numericValue > 0) {
            if (numericValue >= 1000000000000) return numericValue;
            if (numericValue >= 1000000000) return numericValue * 1000;
        }

        const parsedValue = Date.parse(String(value));
        return Number.isNaN(parsedValue) ? 0 : parsedValue;
    }

    function loadingListSources(record) {
        if (!record) return [];

        const sources = Array.isArray(record.source_loading_lists) ?
            record.source_loading_lists.map(String) : [];

        if (!sources.length && record.loading_list_number) {
            sources.push(String(record.loading_list_number));
        }

        return sources;
    }

    function seedLoadingListBuckets(record) {
        const sources = loadingListSources(record);
        const buckets = {};
        const total = pullingInt(record?.total_qty);
        let remainingScanned = pullingInt(record?.scanned_count ?? record?.seri?.length ?? record?.actual_qty);

        sources.forEach((source, index) => {
            const target = Math.floor(total / sources.length) + (index < (total % sources.length) ? 1 : 0);
            const scanned = Math.min(target, remainingScanned);
            remainingScanned -= scanned;
            buckets[source] = {
                target,
                scanned
            };
        });

        return buckets;
    }

    function ensureLoadingListBuckets(record) {
        if (!record) return {};

        if (!record.loading_list_buckets || Array.isArray(record.loading_list_buckets)) {
            record.loading_list_buckets = seedLoadingListBuckets(record);
        }

        return record.loading_list_buckets;
    }

    function loadingListBucketEntries(record) {
        const buckets = ensureLoadingListBuckets(record);
        return loadingListSources(record)
            .filter((source) => buckets[source])
            .map((source) => ({
                number: source,
                target: pullingInt(buckets[source].target),
                scanned: pullingInt(buckets[source].scanned)
            }))
            .filter((entry) => entry.target > 0);
    }

    function partQuantityFromRecord(record) {
        const entries = loadingListBucketEntries(record);
        const bucketTarget = entries.reduce((sum, entry) => sum + pullingInt(entry.target), 0);
        const bucketActual = entries.reduce((sum, entry) => sum + pullingInt(entry.scanned), 0);
        const rawActual = Math.max(
            pullingInt(record?.scanned_count),
            pullingInt(record?.actual_qty),
            Array.isArray(record?.seri) ? record.seri.length : 0
        );
        const target = bucketTarget || pullingInt(record?.total_qty);
        const actual = Math.max(bucketActual, rawActual);

        return {
            actual: cappedPullingCount(actual, target),
            target
        };
    }

    function updatePartQuantityDisplay(record) {
        const qty = partQuantityFromRecord(record);
        $('#qty-display').text(`${qty.actual}/${qty.target}`);
        return qty;
    }

    function refreshPartQuantityDisplayFromIndexedDB(record) {
        const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
        if (!dbName || !record) return;

        const customer = String(record.customer || '').trim();
        const customerComparable = comparableCustomerPart(customer);
        const internal = String(record.internal || '').trim();
        const keys = new Set([
            recordStorageKey(record),
            loadingListPartKey(customer, internal),
            customer
        ].map((key) => String(key || '').trim()).filter(Boolean));
        const baseQty = partQuantityFromRecord(record);
        let serialCount = 0;
        const quantityMap = new Map();
        const addQuantity = (key, actual, target) => {
            const previous = quantityMap.get(key) || {
                actual: 0,
                target: 0
            };
            quantityMap.set(key, {
                actual: Math.max(previous.actual, cappedPullingCount(actual, target)),
                target: Math.max(previous.target, target)
            });
        };

        openPullingDB(dbName).then((database) => {
            const transaction = database.transaction([STORE_NAME, SERIAL_STORE_NAME], 'readonly');
            const recordRequest = transaction.objectStore(STORE_NAME).openCursor();
            const serialRequest = transaction.objectStore(SERIAL_STORE_NAME).openCursor();

            recordRequest.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) return;

                const item = cursor.value || {};
                const sameCustomer = comparableCustomerPart(item.customer) === customerComparable;
                const sameInternal = !internal || String(item.internal || '').trim() === internal;

                if (sameCustomer && sameInternal) {
                    const entries = loadingListBucketEntries(item);
                    if (entries.length) {
                        entries.forEach((entry) => {
                            addQuantity(recordQuantityIdentityKey(item, entry.number), entry
                                .scanned, entry.target);
                        });
                    } else {
                        addQuantity(recordQuantityIdentityKey(item), recordScannedCount(item), item
                            .total_qty);
                    }
                }

                cursor.continue();
            };

            serialRequest.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) return;

                const item = cursor.value || {};
                const itemPartKey = String(item.part_key || '').trim();
                const samePartKey = itemPartKey && keys.has(itemPartKey);
                const samePartFields = comparableCustomerPart(item.customer_part) === customerComparable &&
                    (!internal || String(item.internal || '').trim() === internal);

                if (samePartKey || samePartFields) serialCount += 1;
                cursor.continue();
            };

            transaction.oncomplete = function() {
                database.close();
                if (comparableCustomerPart(localStorage.getItem('customerPart')) !== customerComparable)
                    return;

                const aggregateQty = Array.from(quantityMap.values()).reduce((carry, row) => ({
                    actual: carry.actual + pullingInt(row.actual),
                    target: carry.target + pullingInt(row.target)
                }), {
                    actual: 0,
                    target: 0
                });
                const target = aggregateQty.target || baseQty.target;
                const actual = cappedPullingCount(Math.max(baseQty.actual, aggregateQty.actual,
                        serialCount),
                    target);
                $('#qty-display').text(`${actual}/${target}`);
            };

            transaction.onerror = function() {
                database.close();
            };
        }).catch(() => {});
    }

    function nextLoadingListForScan(record) {
        const entries = loadingListBucketEntries(record);
        const next = entries.find((entry) => entry.scanned < entry.target) || entries[0];
        return next?.number || record?.loading_list_number;
    }

    function incrementLoadingListBucket(record, loadingListNumber) {
        const key = String(loadingListNumber || '');
        const buckets = ensureLoadingListBuckets(record);

        if (key && buckets[key]) {
            buckets[key].scanned = pullingInt(buckets[key].scanned) + 1;
        }
    }

    function addRecordQuantity(quantityMap, record) {
        const fallbackPart = String(record?.internal || record?.customer || '').trim();
        const partKey = recordPartIdentity(record) || recordStorageKey(record) || fallbackPart;
        const entries = loadingListBucketEntries(record);

        if (entries.length) {
            entries.forEach((entry) => {
                const key = `${entry.number}|${partKey || entry.number}`;
                const target = pullingInt(entry.target);
                const previous = quantityMap.get(key) || {
                    actual: 0,
                    target: 0
                };
                quantityMap.set(key, {
                    actual: Math.max(previous.actual, cappedPullingCount(entry.scanned, target)),
                    target: Math.max(previous.target, target)
                });
            });
            return;
        }

        const fallbackKey = `${String(record?.loading_list_number || '').trim()}|${partKey}`;
        const fallbackTarget = pullingInt(record?.total_qty);
        const previous = quantityMap.get(fallbackKey) || {
            actual: 0,
            target: 0
        };
        quantityMap.set(fallbackKey, {
            actual: Math.max(previous.actual, cappedPullingCount(record?.scanned_count ?? record?.seri?.length,
                fallbackTarget)),
            target: Math.max(previous.target, fallbackTarget)
        });
    }

    function quantityTotals(records) {
        const quantityMap = new Map();
        activeLoadingListRecords(records).forEach((record) => addRecordQuantity(quantityMap, record));

        return Array.from(quantityMap.values()).reduce((carry, row) => ({
            actual: carry.actual + pullingInt(row.actual),
            target: carry.target + pullingInt(row.target)
        }), {
            actual: 0,
            target: 0
        });
    }

    function mergeLoadingListItem(existing, item, loadingListNumber) {
        const source = String(loadingListNumber);
        const sources = loadingListSources(existing);

        if (sources.includes(source)) {
            return {
                record: existing,
                shouldPut: false
            };
        }

        const targetQty = pullingInt(item.total_kanban_qty);
        const actualQty = pullingInt(item.actual_kanban_qty);

        if (existing) {
            const scannedCount = pullingInt(existing.scanned_count ?? existing.seri?.length ?? existing.actual_qty);
            const buckets = ensureLoadingListBuckets(existing);

            existing.key = loadingListPartKey(item.part_number_cust, item.part_number_int);
            existing.internal = item.part_number_int;
            existing.customer = item.part_number_cust;
            existing.total_qty = pullingInt(existing.total_qty) + targetQty;
            existing.scanned_count = scannedCount + actualQty;
            existing.actual_qty = pullingInt(existing.actual_qty ?? scannedCount) + actualQty;
            existing.source_loading_lists = sources.concat(source);
            buckets[source] = {
                target: targetQty,
                scanned: actualQty
            };
            existing.seri = Array.isArray(existing.seri) ? existing.seri : [];

            return {
                record: existing,
                shouldPut: true
            };
        }

        return {
            record: {
                key: loadingListPartKey(item.part_number_cust, item.part_number_int),
                loading_list_number: loadingListNumber,
                internal: item.part_number_int,
                customer: item.part_number_cust,
                qty_per_kbn: targetQty ? item.total_qty / targetQty : 0,
                actual_qty: actualQty,
                scanned_count: actualQty,
                total_qty: targetQty,
                source_loading_lists: [source],
                loading_list_buckets: {
                    [source]: {
                        target: targetQty,
                        scanned: actualQty
                    }
                },
                seri: []
            },
            shouldPut: true
        };
    }

    function refreshPullingSummaryFromRecords(database) {
        return new Promise((resolve, reject) => {
            const records = [];
            const readTransaction = database.transaction([STORE_NAME], 'readonly');
            const objectStore = readTransaction.objectStore(STORE_NAME);
            const cursorRequest = objectStore.openCursor();

            cursorRequest.onsuccess = function(event) {
                const cursor = event.target.result;

                if (cursor) {
                    records.push(cursor.value);
                    cursor.continue();
                }
            };

            cursorRequest.onerror = () => reject(cursorRequest.error);

            readTransaction.oncomplete = () => {
                const totals = quantityTotals(records);

                const writeTransaction = database.transaction([SUMMARY_STORE_NAME], 'readwrite');
                const summaryStore = writeTransaction.objectStore(SUMMARY_STORE_NAME);

                summaryStore.put({
                    version: SUMMARY_VERSION,
                    totalActual: totals.actual,
                    totalTarget: totals.target,
                    updatedAt: Date.now()
                }, SUMMARY_KEY);

                writeTransaction.oncomplete = () => {
                    setPullingQuantityDisplay(totals.actual, totals.target);
                    resolve();
                };
                writeTransaction.onerror = () => reject(writeTransaction.error);
                writeTransaction.onabort = () => reject(writeTransaction.error);
            };

            readTransaction.onerror = () => reject(readTransaction.error);
            readTransaction.onabort = () => reject(readTransaction.error);
        });
    }

    function incrementPullingQuantity(deltaActual) {
        const actual = parseInt(localStorage.getItem('pulling_total_actual') || '', 10);
        const target = parseInt(localStorage.getItem('pulling_total_target') || '', 10);

        if (Number.isNaN(actual) || Number.isNaN(target)) {
            pullingQuantity();
            return;
        }

        setPullingQuantityDisplay(actual + deltaActual, target);
    }

    function recalculatePullingQuantity(database) {
        const records = [];
        const transaction = database.transaction([STORE_NAME], 'readonly');
        const objectStore = transaction.objectStore(STORE_NAME);

        const cursorRequest = objectStore.openCursor();
        cursorRequest.onsuccess = function(event) {
            let cursor = event.target.result;
            if (cursor) {
                records.push(cursor.value);
                cursor.continue();
                return;
            }

            const totals = quantityTotals(records);
            setPullingQuantityDisplay(totals.actual, totals.target);
        };

        cursorRequest.onerror = function(event) {
            notif('error', event.message);
        };

        transaction.oncomplete = function() {
            try {
                const totals = quantityTotals(records);
                const writeTransaction = database.transaction([SUMMARY_STORE_NAME], 'readwrite');
                writeTransaction.objectStore(SUMMARY_STORE_NAME).put({
                    version: SUMMARY_VERSION,
                    totalActual: totals.actual,
                    totalTarget: totals.target,
                    updatedAt: Date.now()
                }, SUMMARY_KEY);
                writeTransaction.oncomplete = () => database.close();
                writeTransaction.onerror = () => database.close();
            } catch (error) {
                database.close();
            }
        };
    }

    function pullingQuantity() {
        const pds = localStorage.getItem('pds_local');
        if (!pds) return;

        openPullingDB(pds).then((database) => {
            const transaction = database.transaction([SUMMARY_STORE_NAME], 'readonly');
            const summaryStore = transaction.objectStore(SUMMARY_STORE_NAME);
            const summaryRequest = summaryStore.get(SUMMARY_KEY);

            summaryRequest.onsuccess = function() {
                const summary = summaryRequest.result;
                const totalActual = parseInt(summary?.totalActual, 10);
                const totalTarget = parseInt(summary?.totalTarget, 10);

                if (summary?.version === SUMMARY_VERSION && !Number.isNaN(totalActual) && !Number.isNaN(
                        totalTarget)) {
                    setPullingQuantityDisplay(totalActual, totalTarget);
                    database.close();
                    return;
                }

                recalculatePullingQuantity(database);
            };

            summaryRequest.onerror = function() {
                recalculatePullingQuantity(database);
            };
        }).catch(() => {
            notif('error', 'Failed to connect to database!');
        });
    }

    function refreshToken() {
        $.ajax({
            url: "/refresh-token",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    notif('success', 'Successfully refreshed token');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    notif("error", response.message);
                }
            },
            error: function(xhr) {
                notif("error", xhr.responseJSON.message);
            }
        });
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || "{{ csrf_token() }}";
    }

    function storeLoadingListDetailsBulk(ll, items) {
        return $.ajax({
            type: 'POST',
            url: "{{ route('loadingList.storeDetails') }}",
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                loadingList: ll,
                items
            })
        });
    }

    function formatLoadingListDetailFailureMessage(failures) {
        const list = Array.isArray(failures) ? failures : [];
        const first = list[0] || {};
        const extra = list.length > 1 ? ` (+${list.length - 1} lainnya)` : '';
        const cust = first.cust || '-';
        const intPart = first.int || '-';
        const reason = first.reason || 'master part tidak ditemukan';

        return `MASTER PART TIDAK LENGKAP. ${list.length} detail gagal disimpan${extra}. Cust: ${cust} | Int: ${intPart} | ${reason}`;
    }

    function createLoadingListDetailFailureError(failures) {
        const error = new Error(formatLoadingListDetailFailureMessage(failures));
        error.loadingListDetailFailed = true;
        error.pullingAlreadyNotified = true;
        error.failures = failures;
        return error;
    }

    $(document).ready(function() {
        initApp();
        $('#code').focus();

        $("#refreshTokenBtn").click(function() {
            refreshToken();
        });

        $('#loadingList').on('click', function() {
            loadingListModal2();
        });

        if (localStorage.getItem('status')) {
            $(document).on('click', function() {
                $('#input-confirmation').focus();
            });
        }

        var token = "{{ session()->get('token') }}";

        class ModalLoadingListScanner {
            constructor() {
                this.token = "{{ session()->get('token') }}" || '';
                this.modalSel = '#modalLoadingListScan';
                this.$modal = $(this.modalSel);
            }

            init() {
                this.hideLoading(); // pastikan hidden on start
                $('#input-loadingList').on('keypress', (e) => this.handleKeyPress(e));
            }

            handleKeyPress(e) {
                const keyCode = e.keyCode || e.which;
                if (keyCode === 13) { // Enter key
                    const inputValue = $(e.target).val();
                    this.processLoadingList(inputValue);
                }
            }

            async processLoadingList(inputValue) {
                const loadingListNumber = inputValue.substr(0, 11) + ' A';
                let ownsLoadingListNumber = false;

                try {
                    const loadingList = this.getLoadingListNumber();

                    // kalau sudah “done”, jangan lanjut
                    if (isLLDone(loadingListNumber)) {
                        this.showError('Loading list ini sudah diproses.');
                        return;
                    }

                    // jika sedang diproses paralel → tolak
                    if (inFlightLL.has(loadingListNumber)) {
                        this.showError('Loading list ini sedang diproses...');
                        return;
                    }
                    inFlightLL.add(loadingListNumber);
                    ownsLoadingListNumber = true;

                    // kalau sudah ada di localStorage (header sudah tersimpan) → tolak
                    if (this.getLoadingListNumber().includes(loadingListNumber)) {
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        inFlightLL.delete(loadingListNumber);
                        return;
                    }

                    // early return tanpa spinner
                    if (loadingList.includes(loadingListNumber)) {
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }

                    // ⬇️ mulai tampilkan loading
                    this.showLoading('Mengambil data loading list...');

                    const response = await this.fetchLoadingList(loadingListNumber);
                    if (response.status !== 'success') {
                        this.showError(response.message);
                        this.showModal();
                        return;
                    }

                    // proses lanjut (spinner tetap on, nanti di-update pesannya di method berikut)
                    await this.processLoadingListResponse(response.data, loadingListNumber);

                    // kalau sukses dan mau tutup modal
                    this.hideModal();

                } catch (error) {
                    console.error('Loading list error:', error);
                    this.handleAjaxError(error);
                } finally {
                    // pastikan spinner dimatikan & input difokuskan
                    if (ownsLoadingListNumber) inFlightLL.delete(loadingListNumber);
                    this.hideLoading();
                    this?.focusCodeInput?.();
                }
            }


            async fetchLoadingList(loadingListNumber) {
                return await $.ajax({
                    type: 'GET',
                    url: `https://dea-dev.aiia.co.id/api/v1/loading-lists/${loadingListNumber}`,
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    },
                    dataType: 'json'
                });
            }

            async processLoadingListResponse(data, requestedNumber = '') {
                // validasi dulu
                const validationResult = this.validateLoadingList(data);
                if (!validationResult.isValid) {
                    if (validationResult.hydrateFromMutation && data?.number) {
                        if (data?.pds_number) this.storeLocalData(data);
                        this.updateLoadingListUI(data);
                        await hydrateScanHistoryFromMutations([data.number]);
                        this.pullingQuantity();
                    }

                    this.showError(validationResult.message, validationResult.soundFn);
                    if (validationResult.showModal) this.showModal();
                    return;
                }

                const {
                    pds_number,
                    number: ll,
                    cycle,
                    customer_code,
                    delivery_date,
                    shipping_date
                } = data;

                let ownsCanonicalNumber = false;
                if ((ll !== requestedNumber && inFlightLL.has(ll)) || this.getLoadingListNumber().includes(
                        ll) ||
                    hasLoadingListChip(ll) || isLLDone(ll)) {
                    this.showError('Loading list sudah discan!', () => {
                        if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                    });
                    return;
                }
                inFlightLL.add(ll);
                ownsCanonicalNumber = true;

                try {
                    this.setLoadingMessage('Menyimpan header...');
                    await this.storeLoadingList(ll, pds_number, cycle, customer_code, delivery_date,
                        shipping_date);

                    this.setLoadingMessage('Menyimpan detail item...');
                    await this.storeLoadingListDetails(ll, data.items);

                    this.setLoadingMessage('Menyimpan data lokal...');
                    this.storeLocalData(data);

                    this.setLoadingMessage('Memperbarui tampilan...');
                    this.updateLoadingListUI(data);

                    this.setLoadingMessage('Menyiapkan IndexedDB...');
                    await this.initializeDatabase(data);

                    await hydrateScanHistoryFromMutations([ll]);

                    this.setLoadingMessage('Finalisasi...');
                    await this.performAdditionalProcessing(data);

                    markLLDone(ll);
                    if (ownsCanonicalNumber) inFlightLL.delete(ll);

                } catch (error) {
                    console.error('Error processing loading list:', error);
                    if (!error?.pullingAlreadyNotified) {
                        this.showError(error?.message || 'Gagal memproses loading list');
                    }
                } finally {
                    // fokus input akan dipanggil di finally processLoadingList (luar)
                    if (ownsCanonicalNumber) inFlightLL.delete(ll);
                    this.hideLoading();
                    this?.focusCodeInput?.();
                }
            }

            validateLoadingList(data) {
                // Check if already pulled (missing total_kanban_qty property)
                if (!data.items[0]?.hasOwnProperty('total_kanban_qty')) {
                    return {
                        isValid: false,
                        message: 'Loading list sudah pernah dipulling',
                        hydrateFromMutation: true,
                        soundFn: () => {
                            if (typeof alreadyPulledSound === 'function') alreadyPulledSound();
                        },
                        showModal: true
                    };
                }

                // Check totals (commented out in original but keeping logic)
                const totals = this.calculateTotals(data.items);
                // if (totals.actual >= totals.kanban) {
                //     return {
                //         isValid: false,
                //         message: 'Loading list sudah pernah dipulling',
                //         soundFn: () => { if (typeof alreadyPulledSound === 'function') alreadyPulledSound(); },
                //         showModal: true
                //     };
                // }

                // Check PDS consistency
                const existingPDS = localStorage.getItem('pdsNumber');
                if (existingPDS && data.pds_number !== existingPDS) {
                    return {
                        isValid: false,
                        message: 'Loading list tidak sesuai!',
                        soundFn: () => {
                            if (typeof notMatchLlSound === 'function') notMatchLlSound();
                        },
                        showModal: false
                    };
                }

                return {
                    isValid: true
                };
            }

            calculateTotals(items) {
                return items.reduce((acc, item) => {
                    acc.actual += item.actual_kanban_qty || 0;
                    acc.kanban += item.total_kanban_qty || 0;
                    return acc;
                }, {
                    actual: 0,
                    kanban: 0
                });
            }

            async storeLoadingList(ll, pds, cycle, customerCode, deliveryDate, shippingDate) {
                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `/loading-list/store/${ll}/${pds}/${cycle}/${customerCode}/${deliveryDate || ''}/${shippingDate || ''}`,
                        dataType: 'json'
                    });

                    // console.log('Modal loading list stored:', response.message);
                    return response;

                } catch (xhr) {
                    console.error('Error storing loading list:', xhr);

                    if (xhr.status === 0) {
                        this.showError('Connection Error');
                        throw new Error('Network error');
                    } else {
                        const errorMsg = xhr.responseJSON?.errors || 'Gagal menyimpan loading list';
                        this.showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                }
            }

            storeLocalData(data) {
                localStorage.setItem('pds_local', data.pds_number);
                localStorage.setItem(`ll_${data.number}`, data.number);
                localStorage.setItem('pdsNumber', data.pds_number);
            }

            updateLoadingListUI(data) {
                appendLoadingListChip(data.number);
            }

            async initializeDatabase(data) {
                const database = await openPullingDB(data.pds_number);
                try {
                    await this.populateDatabase(database, data);
                } finally {
                    database.close();
                }
            }

            async populateDatabase(database, data) {
                const transaction = database.transaction([STORE_NAME], 'readwrite');
                const objectStore = transaction.objectStore(STORE_NAME);
                const transactionDone = new Promise((resolve, reject) => {
                    transaction.oncomplete = resolve;
                    transaction.onerror = () => reject(transaction.error);
                    transaction.onabort = () => reject(transaction.error);
                });

                const promises = data.items.map(item => {
                    return new Promise((resolve, reject) => {
                        const key = loadingListPartKey(item.part_number_cust, item
                            .part_number_int);
                        const getRequest = objectStore.get(key);

                        getRequest.onsuccess = (event) => {
                            try {
                                const merged = mergeLoadingListItem(event.target.result,
                                    item, data.number);

                                if (merged.shouldPut) {
                                    objectStore.put(merged.record, key);
                                }

                                resolve();
                            } catch (error) {
                                reject(error);
                            }
                        };

                        getRequest.onerror = () => reject(getRequest.error);
                    });
                });

                await Promise.all(promises);
                await transactionDone;
                await refreshPullingSummaryFromRecords(database);
            }

            async storeLoadingListDetails(ll, items) {
                try {
                    const response = await storeLoadingListDetailsBulk(ll, items);
                    const failures = response?.failed || [];

                    if (failures.length > 0) {
                        console.warn(`${failures.length} detail items failed to store:`, failures);
                        const error = createLoadingListDetailFailureError(failures);
                        this.showError(error.message);
                        throw error;
                    }

                    return response;
                } catch (xhr) {
                    if (xhr?.pullingAlreadyNotified) {
                        throw xhr;
                    }

                    console.error('Critical error storing details:', xhr);

                    if (xhr.status === 0) {
                        throw new Error('Network error storing detail');
                    }

                    this.showError('Gagal menyimpan detail loading list. Koneksi bermasalah.');
                    throw new Error(xhr.responseJSON?.message || xhr.statusText ||
                        'Gagal menyimpan detail loading list');
                }
            }

            async performAdditionalProcessing(data) {
                try {
                    // Customer check
                    await this.customerCheck(data.customer_code, data.pds_number);

                    // Update cycle display
                    $('#cycle-display').text(data.cycle);
                    localStorage.setItem('cycle', data.cycle);

                    // Calculate quantities
                    this.pullingQuantity();

                    // Handle TMMIN skid display
                    this.handleTmminSkidDisplay(data.customer_code);

                    // Additional checks
                    this.checkLoadingList();
                    this.customerCharStore(data.customer_code, data.pds_number);

                } catch (error) {
                    console.error('Additional processing error:', error);
                    this.showError(error.message || 'Error in additional processing');
                }
            }

            handleTmminSkidDisplay(customerCode) {
                if (customerCode === '7A00001') { // TMMIN customer code
                    let skid = localStorage.getItem('skid') || '1';
                    if (!localStorage.getItem('skid')) {
                        localStorage.setItem('skid', '1');
                    }

                    $('.skid-display').append(pullingSkidMarkup(skid));
                }
            }

            showLoading(message = 'Memproses...') {
                this.setLoadingMessage(message);
                this.$modal.find('.loading-overlay').addClass('is-active');
                this.$modal.find('input,button,select,textarea').prop('disabled', true);
            }

            hideLoading() {
                this.$modal.find('.loading-overlay').removeClass('is-active');
                this.$modal.find('input,button,select,textarea').prop('disabled', false);
            }

            setLoadingMessage(message) {
                this.$modal.find('#loading-text').text(message);
            }

            handleAjaxError(xhr) {
                if (xhr.status === 0) {
                    this.showError('Connection Error');
                } else if (xhr.status === 401) {
                    this.showError(`${xhr.statusText} Please re-login`);
                } else {
                    this.showError(xhr.statusText || 'Request failed');
                }
                this.showModal();
            }

            showError(message, soundFunction = null) {
                if (typeof notif === 'function') {
                    notif('error', message);
                }
                if (soundFunction) soundFunction();
            }

            showModal() {
                if (typeof loadingListModal === 'function') {
                    loadingListModal();
                }
            }

            hideModal() {
                $('#modalLoadingListScan').modal('hide');
            }

            // di dalam class:
            focusCodeInput = () => {
                const $el = $('#code:visible:not([disabled])');
                if (!$el.length) return;

                // kalau ada modal bootstrap, fokuskan setelah modal bener² tertutup
                if ($('.modal.show').length) {
                    $('.modal.show').one('hidden.bs.modal', () => this.focusCodeInput());
                    return;
                }

                // tunda 1 frame supaya DOM/UI selesai update
                requestAnimationFrame(() => setTimeout(() => {
                    $el.trigger('focus');
                    // opsional: taruh kursor di akhir input
                    const el = $el.get(0);
                    if (el && el.setSelectionRange) {
                        const len = el.value?.length ?? 0;
                        el.setSelectionRange(len, len);
                    }
                }, 0));
            };


            getLoadingListNumber() {
                return typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
            }

            async customerCheck(customerCode, pdsNumber) {
                return new Promise((resolve, reject) => {
                    if (typeof customerCheck === 'function') {
                        customerCheck(customerCode, pdsNumber)
                            .then(resolve)
                            .catch(reject);
                    } else {
                        resolve();
                    }
                });
            }

            pullingQuantity() {
                if (typeof pullingQuantity === 'function') {
                    pullingQuantity();
                }
            }

            checkLoadingList() {
                if (typeof checkLoadingList === 'function') {
                    checkLoadingList();
                }
            }

            customerCharStore(customerCode, pdsNumber) {
                if (typeof customerCharStore === 'function') {
                    customerCharStore(customerCode, pdsNumber);
                }
            }
        }

        // Initialize the modal loading list scanner
        $(document).ready(() => {
            const modalScanner = new ModalLoadingListScanner();
            modalScanner.init();
        });

        $('#input-confirmation').keypress(function(e) {
            e.preventDefault();
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                barcodecomplete = barcode;
                barcode = "";
                if (barcodecomplete.length === 6) {
                    if (barcodecomplete == '000453' || barcodecomplete == '002484') {
                        localStorage.removeItem('status');
                        $('#modalConfirmation').modal('hide');
                        notif('success', 'Selamat melanjutkan pulling!');

                        // remove original customer part in local storage
                        localStorage.removeItem('originalCustomerPart');

                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                    } else {
                        $('#modalConfirmation').modal('hide');
                        notif('error', `NPK ${barcodecomplete} tidak memiliki hak akses`);
                        confirmationModal();
                    }
                } else {
                    $('#modalConfirmation').modal('hide');
                    notif('error', 'Scan barcode NPK');
                    confirmationModal();
                }
            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }
        });

        // Function to clear local storage
        function clearLocalStorage() {
            localStorage.clear();
        }

        // Function to clear IndexedDB using localforage
        function deleteAllIndexedDB() {
            // Retrieve a list of all databases
            indexedDB.databases().then(function(databaseList) {
                // Loop through each database and delete it
                databaseList.forEach(function(database) {
                    var deleteRequest = indexedDB.deleteDatabase(database.name);

                    deleteRequest.onsuccess = function() {
                        console.log('IndexedDB database deleted successfully:', database
                            .name);
                    };

                    deleteRequest.onerror = function(event) {
                        console.error('Error deleting IndexedDB database:', event.target
                            .error, database.name);
                    };
                });
            }).catch(function(error) {
                console.error('Error retrieving IndexedDB database names:', error);
            });
        }

        const PULLING_PENDING_DB_DELETE_KEY = 'pulling_pending_db_delete';

        function deleteIndexedDBInBackground(databaseName) {
            if (!databaseName) return;

            try {
                const deleteRequest = indexedDB.deleteDatabase(databaseName);

                deleteRequest.onsuccess = function() {
                    try {
                        if (sessionStorage.getItem(PULLING_PENDING_DB_DELETE_KEY) === databaseName) {
                            sessionStorage.removeItem(PULLING_PENDING_DB_DELETE_KEY);
                        }
                    } catch (error) {}
                };

                deleteRequest.onerror = function(event) {
                    console.warn('Gagal cleanup IndexedDB background:', databaseName, event?.target?.error);
                };

                deleteRequest.onblocked = function() {
                    console.warn('Cleanup IndexedDB tertunda karena database masih dipakai:', databaseName);
                };
            } catch (error) {
                console.warn('Gagal memulai cleanup IndexedDB:', databaseName, error);
            }
        }

        function queuePullingDatabaseCleanup(databaseName) {
            if (!databaseName) return;

            try {
                sessionStorage.setItem(PULLING_PENDING_DB_DELETE_KEY, databaseName);
            } catch (error) {}

            deleteIndexedDBInBackground(databaseName);
        }

        function cleanupQueuedPullingDatabase() {
            let databaseName = '';

            try {
                databaseName = sessionStorage.getItem(PULLING_PENDING_DB_DELETE_KEY) || '';
            } catch (error) {}

            if (databaseName) {
                deleteIndexedDBInBackground(databaseName);
            }
        }

        cleanupQueuedPullingDatabase();

        let pullingConfirmAction = null;

        function showPullingConfirm(options) {
            const config = Object.assign({
                title: 'Konfirmasi',
                message: 'Lanjutkan aksi ini?',
                confirmText: 'Lanjutkan',
                confirmClass: 'btn-danger',
                icon: 'fas fa-exclamation-triangle',
                onConfirm: null
            }, options || {});

            pullingConfirmAction = config.onConfirm;
            $('#pullingConfirmTitle').text(config.title);
            $('#pullingConfirmMessage').text(config.message);
            $('#pullingConfirmIcon').html('<i class="' + config.icon + '"></i>');
            $('#pullingConfirmOk')
                .attr('class', 'btn ' + config.confirmClass)
                .text(config.confirmText);
            $('#pullingConfirmModal').modal('show');
        }

        $('#pullingConfirmOk').on('click', function() {
            const action = pullingConfirmAction;
            pullingConfirmAction = null;
            $('#pullingConfirmModal').modal('hide');
            if (typeof action === 'function') action();
        });

        $('#pullingConfirmModal').on('hidden.bs.modal', function() {
            pullingConfirmAction = null;
            $('#code').focus();
        });

        $(document).on('submit', '#pullingLogoutForm', function(event) {
            const form = this;
            if (form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            showPullingConfirm({
                title: 'Logout dari BELLA?',
                message: 'Sesi pulling akan ditutup dan kamu perlu login ulang untuk lanjut bekerja.',
                confirmText: 'Logout',
                confirmClass: 'btn-danger',
                icon: 'fas fa-sign-out-alt',
                onConfirm: function() {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });

        $('#delay').on('click', function() {
            $(this).prop('disabled', true).css({
                color: '#B91C1C',
                backgroundColor: '#F9CFCF',
                borderColor: '#F2A5A5'
            });
            localStorage.clear();
            window.location.reload();
        });

        $(document).on('click', '#close-skid', function() {
            const button = this;
            showPullingConfirm({
                title: 'Ganti Skid?',
                message: 'Nomor skid akan diganti untuk proses pulling berikutnya.',
                confirmText: 'Ganti Skid',
                confirmClass: 'btn-warning',
                onConfirm: function() {
                    let skid = parseInt(localStorage.getItem('skid')) || 1;
                    let skidDisplay = $('#skid-display');

                    if (skid === 1) {
                        skid++;
                        localStorage.setItem('skid', skid);
                    } else if (skid === 2) {
                        skid--;
                        localStorage.setItem('skid', skid);
                    }

                    skidDisplay.text(skid);
                    $(button).text(`Close Skid ${skid}`);
                    setInterval(() => {
                        $('#code').focus();
                    }, 1000);
                }
            });
        });

        $('#hardReset').on('click', function() {
            showPullingConfirm({
                title: 'Reset Semua Data?',
                message: 'Semua data scan lokal dan IndexedDB akan dihapus. Aksi ini tidak bisa dibatalkan.',
                confirmText: 'Reset',
                confirmClass: 'btn-danger',
                onConfirm: function() {
                    clearLocalStorage();
                    deleteAllIndexedDB();
                    location.reload();
                }
            });
        });

        function setPullingDoneLoading(isLoading, message = 'Memproses pulling...') {
            const $done = $('#done');
            if (!$done.data('idle-html')) {
                $done.data('idle-html', $done.html());
            }

            if (isLoading) {
                $done
                    .prop('disabled', true)
                    .addClass('is-loading')
                    .attr('aria-busy', 'true')
                    .html(`
                        <span class="spinner-border pulling-done-spinner" role="status" aria-hidden="true"></span>
                        <span class="pulling-done-label">${message}</span>
                    `);
                $('#delay, #hardReset, #refreshTokenBtn, #code').prop('disabled', true);
                return;
            }

            $done
                .prop('disabled', false)
                .removeClass('is-loading')
                .removeAttr('aria-busy')
                .html($done.data('idle-html'));
            $('#delay, #hardReset, #refreshTokenBtn, #code').prop('disabled', false);
        }

        function pullingAjaxJson(options) {
            return new Promise((resolve, reject) => {
                $.ajax(Object.assign({}, options, {
                    success: resolve,
                    error: reject
                }));
            });
        }

        function pullingDelay(ms) {
            return new Promise((resolve) => setTimeout(resolve, ms));
        }

        const PULLING_DONE_STAGE_ORDER = ['sync', 'local', 'validate', 'dea', 'store'];
        const PULLING_DONE_STAGE_LABELS = {
            sync: 'Sync scan',
            local: 'Baca lokal',
            validate: 'Validasi qty',
            dea: 'Kirim DEA',
            store: 'Simpan status'
        };
        let pullingDoneProgressStartedAt = 0;
        let pullingDoneProgressStage = null;
        let pullingDoneProgressStageStartedAt = 0;
        let pullingDoneProgressTimer = null;

        function pullingDoneNow() {
            return window.performance && typeof window.performance.now === 'function' ?
                window.performance.now() :
                Date.now();
        }

        function pullingDoneDuration(ms) {
            const value = Math.max(0, Number(ms) || 0);
            if (value < 1000) return `${Math.round(value)}ms`;
            return `${(value / 1000).toFixed(value < 10000 ? 1 : 0)}s`;
        }

        function pullingDoneStageElement(stage) {
            return $(`.pulling-finish-step[data-finish-stage="${stage}"]`);
        }

        function setPullingDoneProgressFill(stage) {
            const index = PULLING_DONE_STAGE_ORDER.indexOf(stage);
            const max = PULLING_DONE_STAGE_ORDER.length - 1;
            const percent = index < 0 || max <= 0 ? 0 : Math.round((index / max) * 100);
            $('#finishProgress').css('--finish-progress', `${percent * .8}%`);
        }

        function updatePullingDoneElapsed() {
            if (!pullingDoneProgressStartedAt) return;

            const now = pullingDoneNow();
            $('#finishElapsed').text(pullingDoneDuration(now - pullingDoneProgressStartedAt));

            if (pullingDoneProgressStage) {
                pullingDoneStageElement(pullingDoneProgressStage)
                    .find('strong')
                    .text(pullingDoneDuration(now - pullingDoneProgressStageStartedAt));
            }
        }

        function resetPullingDoneProgress(title = 'Mengecek data...') {
            pullingDoneProgressStartedAt = pullingDoneNow();
            pullingDoneProgressStage = null;
            pullingDoneProgressStageStartedAt = 0;

            $('#finishProgress')
                .prop('hidden', false)
                .addClass('is-running')
                .removeClass('is-failed')
                .css('--finish-progress', '0%');
            $('#finishProgressClose').prop('hidden', true);
            $('#finishProgressModal').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
            $('#finishProgressTitle').text(title);
            $('.pulling-finish-step')
                .removeClass('is-active is-done is-skipped is-error')
                .find('strong')
                .text('Menunggu');

            clearInterval(pullingDoneProgressTimer);
            pullingDoneProgressTimer = setInterval(updatePullingDoneElapsed, 500);
            updatePullingDoneElapsed();
        }

        function setPullingDoneProgressStage(stage, message) {
            if (!pullingDoneProgressStartedAt) resetPullingDoneProgress(message);
            if (pullingDoneProgressStage && pullingDoneProgressStage !== stage) {
                completePullingDoneProgressStage(pullingDoneProgressStage);
            }

            pullingDoneProgressStage = stage;
            pullingDoneProgressStageStartedAt = pullingDoneNow();
            $('#finishProgressTitle').text(message || PULLING_DONE_STAGE_LABELS[stage] || 'Memproses...');
            setPullingDoneProgressFill(stage);
            pullingDoneStageElement(stage)
                .removeClass('is-done is-skipped is-error')
                .addClass('is-active')
                .find('strong')
                .text('0ms');
            updatePullingDoneElapsed();
        }

        function completePullingDoneProgressStage(stage = pullingDoneProgressStage, state = 'done', text =
            null) {
            if (!stage) return;

            const isActiveStage = pullingDoneProgressStage === stage;
            const duration = isActiveStage && pullingDoneProgressStageStartedAt ?
                pullingDoneDuration(pullingDoneNow() - pullingDoneProgressStageStartedAt) :
                null;

            pullingDoneStageElement(stage)
                .removeClass('is-active is-done is-skipped is-error')
                .addClass(`is-${state}`)
                .find('strong')
                .text(text || duration || 'Selesai');
            setPullingDoneProgressFill(stage);

            if (isActiveStage) {
                pullingDoneProgressStage = null;
                pullingDoneProgressStageStartedAt = 0;
            }
        }

        function skipPullingDoneProgressStage(stage, text = 'Lewat') {
            completePullingDoneProgressStage(stage, 'skipped', text);
        }

        function failPullingDoneProgress(message = 'Gagal memproses') {
            if (pullingDoneProgressStage) {
                completePullingDoneProgressStage(pullingDoneProgressStage, 'error');
            }
            $('#finishProgress').addClass('is-failed');
            $('#finishProgressTitle').text(message);
        }

        function stopPullingDoneProgress(showClose = true) {
            clearInterval(pullingDoneProgressTimer);
            pullingDoneProgressTimer = null;
            $('#finishProgress').removeClass('is-running');
            $('#finishProgressClose').prop('hidden', !showClose);
            updatePullingDoneElapsed();
        }

        function setPullingDoneStage(stage, message) {
            setPullingDoneLoading(true, message);
            setPullingDoneProgressStage(stage, message);
        }

        $('#finishProgressModal').on('hidden.bs.modal', function() {
            setTimeout(() => $('#code').focus(), 150);
        });

        $('#done').on('click', async function() {
            if ($(this).hasClass('is-loading')) return;

            const pds = localStorage.getItem('pds_local');
            if (!pds) {
                notif('error', 'Database lokal belum siap!');
                return;
            }

            let database = null;
            let finishSucceeded = false;
            resetPullingDoneProgress('Mengecek data...');
            setPullingDoneLoading(true, 'Mengecek data...');

            try {
                setPullingDoneStage('sync', 'Sinkronisasi scan...');
                await hydrateScanHistoryFromMutations();
                completePullingDoneProgressStage('sync');

                setPullingDoneStage('local', 'Membaca scan lokal...');
                database = await openPullingDB(pds);
                const records = await getAllLoadingListRecords(database);
                const loadingListPayload = {};
                const finishRows = [];
                const seenLoadingLists = new Set();
                let localSubmitRows = 0;
                let complete = records.length > 0;
                const addFinishRow = (loadingListNumber) => {
                    if (seenLoadingLists.has(loadingListNumber)) return;

                    seenLoadingLists.add(loadingListNumber);
                    finishRows.push({
                        customer: localStorage.getItem('customer'),
                        loadingList: loadingListNumber,
                        pdsNumber: localStorage.getItem('pdsNumber'),
                        cycle: localStorage.getItem('cycle'),
                    });
                };

                completePullingDoneProgressStage('local');
                setPullingDoneStage('validate', 'Validasi qty...');

                for (const record of records) {
                    const scanRows = await getScanRowsForPart(database, record);
                    const buckets = loadingListBucketEntries(record);
                    const fallbackTarget = parseInt(record.total_qty || 0, 10) || 0;
                    const totalTarget = buckets.reduce((sum, bucket) => sum + bucket.target, 0) ||
                        fallbackTarget;

                    console.log('Scan rows:', scanRows.length, 'for loading list:', record
                        .loading_list_number, 'with target:', totalTarget);
                    if (scanRows.length < totalTarget) {
                        complete = false;
                        break;
                    }

                    scanRows.forEach((row) => {
                        const loadingListNumber = row.loading_list_number || record
                            .loading_list_number;
                        if (!loadingListNumber) return;

                        addFinishRow(loadingListNumber);
                        if (row.synced_from_mutation) return;
                        if (!row.synced_from_mutation) localSubmitRows += 1;

                        const item = {
                            part_number_internal: record.internal,
                            part_number_customer: record.customer,
                            serial_number: row.serial
                        };

                        if (loadingListPayload.hasOwnProperty(loadingListNumber)) {
                            loadingListPayload[loadingListNumber].push(item);
                        } else {
                            loadingListPayload[loadingListNumber] = [item];
                        }
                    });
                }

                database.close();
                database = null;
                if (!complete) {
                    completePullingDoneProgressStage('validate', 'error', 'Belum lengkap');
                    $('#finishProgressTitle').text('Loading list belum lengkap');
                    notif('error', 'loading list belum lengkap!');
                    uncompleteLlSound();
                    setTimeout(() => $('#code').focus(), 1000);
                    return;
                }
                completePullingDoneProgressStage('validate');

                const submitPayload = localSubmitRows > 0 && Object.keys(loadingListPayload)
                    .length > 0;
                const finishLocalPulling = function() {
                    return new Promise((resolve, reject) => {
                        if (finishRows.length === 0) {
                            resolve();
                            return;
                        }

                        let storedRows = 0;
                        finishRows.forEach((row) => {
                            $.ajax({
                                type: 'GET',
                                url: "{{ route('pulling.store') }}",
                                _token: "{{ csrf_token() }}",
                                data: row,
                                dataType: 'json',
                                success: function() {
                                    storedRows += 1;
                                    if (storedRows === finishRows
                                        .length) {
                                        resolve();
                                    }
                                },
                                error: reject
                            });
                        });
                    });
                };

                const finishWithSuccess = async function(message) {
                    finishSucceeded = true;
                    stopPullingDoneProgress(false);
                    queuePullingDatabaseCleanup(pds);
                    await pullingDelay(700);
                    $('#finishProgressModal').modal('hide');
                    await pullingDelay(250);
                    notif('success', message);
                    finishPullingSound();
                    await pullingDelay(900);
                    localStorage.clear();
                    window.location.reload();
                };

                if (!submitPayload) {
                    skipPullingDoneProgressStage('dea', 'Tidak perlu');
                    setPullingDoneStage('store', 'Menyelesaikan pulling...');
                    await finishLocalPulling();
                    completePullingDoneProgressStage('store');
                    await finishWithSuccess('Pulling sudah complete dari scanner lain.');
                    return;
                }

                setPullingDoneStage('dea', 'Mengirim ke DEA...');
                await pullingAjaxJson({
                    type: 'GET',
                    url: "{{ route('pulling.post') }}",
                    _token: "{{ csrf_token() }}",
                    data: {
                        loadingList: loadingListPayload,
                        token: token
                    },
                    dataType: 'json'
                });
                completePullingDoneProgressStage('dea');

                setPullingDoneStage('store', 'Menyimpan status...');
                await finishLocalPulling();
                completePullingDoneProgressStage('store');
                await finishWithSuccess('Pulling berhasil!');
            } catch (error) {
                const message = error?.responseJSON?.message ||
                    error?.target?.error?.message ||
                    error?.message ||
                    error?.statusText ||
                    'Gagal membaca data pulling lokal';

                failPullingDoneProgress('Gagal memproses pulling');
                notif('error', message);
            } finally {
                if (database) database.close();
                stopPullingDoneProgress(!finishSucceeded);
                setPullingDoneLoading(false);
                setTimeout(() => $('#code').focus(), 250);
            }
        });

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        function checkInternalAndCustomer(database, cursor, internal, primaryKey, seri) {
            let loadingList = nextLoadingListForScan(cursor);
            let customer = cursor['customer'];
            let qty_per_kbn = cursor['qty_per_kbn'];
            let arraySeri = cursor['seri'];
            let totalQty = cursor['total_qty'];
            let isSameObject = false;
            let skid = localStorage.getItem('skid');
            let originalBarcode = localStorage.getItem('originalCustomerPart');
            let barcodecomplete = localStorage.getItem('customerPart');
            let manifest, itemNo, seqNo;

            if (localStorage.getItem('char_total') == 39) {
                manifest = originalBarcode.substr(3, 10);
                itemNo = originalBarcode.substr(31, 4);
                seqNo = originalBarcode.substr(35, 4);
            }

            // Helper: ambil pesan error dari xhr
            const xhrMessage = (xhr) => {
                if (xhr.status === 0) return 'Connection Error';
                return (xhr.responseJSON?.message) ||
                    (xhr.responseJSON?.errors ? JSON.stringify(xhr.responseJSON.errors) : null) ||
                    xhr.responseText ||
                    `HTTP ${xhr.status}`;
            };

            // Helper: uniform error handling + pencatatan
            // opsi: { expected, scanned, playSound }
            const handleError = (message, opts = {}) => {
                const {
                    expected = null, scanned = null, playSound = true
                } = opts;

                $('#indicator').removeClass('bg-success bg-warning').addClass('bg-danger');
                notif('error', message);

                // catat detail error via API
                errorStore(message, expected, scanned);

                if (playSound) notMatchSound();

                // fokus input kembali (sekali, bukan interval)
                setTimeout(() => {
                    $('#code').focus();
                }, 1000);
            };

            if (!Array.isArray(arraySeri)) {
                arraySeri = [];
                cursor.seri = arraySeri;
            }
            let scannedCount = parseInt(cursor.scanned_count ?? arraySeri.length, 10) || 0;

            // === Validasi: internal & customer harus dalam objek yang sama
            isSameObject = comparableCustomerPart(cursor.customer) === comparableCustomerPart(localStorage.getItem(
                    'customerPart')) &&
                String(cursor.internal || '').trim() === String(internal || '').trim();

            if (!isSameObject) {
                handleError('Kanban tidak sesuai!', {
                    expected: `Internal & Customer satu objek (internal: ${internal.trimEnd()}, customer: ${localStorage.getItem('customerPart')})`,
                    scanned: `Record lokal (internal: ${String(cursor.internal || '').trim()}, customer: ${String(cursor.customer || '').trim()})`
                });
                localStorage.setItem('status', 'true');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                return;
            }

            let updateIndexedDB = () => {};
            const continueAfterQuantityCheck = () => {
                // === Validasi: quantity sudah penuh
                if (scannedCount >= totalQty) {
                    handleError('Part number sudah complete!', {
                        expected: `Qty <= ${totalQty}`,
                        scanned: `Attempt push, current=${scannedCount}`,
                        playSound: false
                    });
                    fullfilledSound();
                    return false;
                }

                // === Update IndexedDB setelah validasi backend OK
                updateIndexedDB = (scanEventId = '') => {
                    scannedCount += 1;
                    cursor.scanned_count = scannedCount;
                    cursor.actual_qty = scannedCount;
                    incrementLoadingListBucket(cursor, loadingList);
                    let countApplied = true;
                    const rollbackLocalCount = () => {
                        if (!countApplied) return;
                        scannedCount -= 1;
                        cursor.scanned_count = scannedCount;
                        cursor.actual_qty = scannedCount;
                        const buckets = ensureLoadingListBuckets(cursor);
                        if (buckets[loadingList]) {
                            buckets[loadingList].scanned = Math.max(0, pullingInt(buckets[
                                loadingList].scanned) - 1);
                        }
                        countApplied = false;
                    };
                    const transaction = database.transaction([STORE_NAME, SERIAL_STORE_NAME,
                        SUMMARY_STORE_NAME
                    ], 'readwrite');
                    const objectStore = transaction.objectStore(STORE_NAME);
                    const serialStore = transaction.objectStore(SERIAL_STORE_NAME);
                    const summaryStore = transaction.objectStore(SUMMARY_STORE_NAME);
                    transaction.oncomplete = () => database.close();
                    performUpdate(transaction, objectStore, serialStore, summaryStore, scanEventId);
                };

                function performUpdate(transaction, objectStore, serialStore, summaryStore, scanEventId) {
                    const eventId = String(scanEventId || `${Date.now()}-${Math.random().toString(36).slice(2)}`);
                    serialStore.put({
                        customer_part: customer,
                        part_key: primaryKey,
                        internal: String(internal || '').trim(),
                        serial: String(seri || '').trim(),
                        scan_event_id: eventId,
                        mutation_id: scanEventId || null,
                        loading_list_number: loadingList,
                        scanned_at: Date.now(),
                        synced_from_mutation: false,
                        verified_from_mutation: false
                    }, scanSerialKey(primaryKey, seri, eventId));

                    const summaryRequest = summaryStore.get(SUMMARY_KEY);
                    summaryRequest.onsuccess = function() {
                        const actual = parseInt(localStorage.getItem('pulling_total_actual') || '0',
                            10) || 0;
                        const target = parseInt(localStorage.getItem('pulling_total_target') ||
                            totalQty || '0', 10) || 0;
                        const summary = summaryRequest.result || {
                            totalActual: actual,
                            totalTarget: target
                        };
                        summary.version = SUMMARY_VERSION;
                        summary.totalActual = (parseInt(summary.totalActual || 0, 10) || 0) + 1;
                        summary.totalTarget = parseInt(summary.totalTarget || target, 10) || target;
                        summary.updatedAt = Date.now();
                        summaryStore.put(summary, SUMMARY_KEY);
                    };

                    const putRequest = objectStore.put(cursor, primaryKey);

                    putRequest.onsuccess = function() {
                        $('#qty-display').text(`${scannedCount}/${totalQty}`);
                        $('#int-display').text(internal);
                        $('#cust-display').text('-');
                        $('#chute-display').text('-');
                        $('#backno-display').text('-');
                        $('#indicator').removeClass('bg-danger bg-warning').addClass('bg-success');
                        resetIndicator();
                        incrementPullingQuantity(1);
                        okSound();
                        pushScanPairHistory(customer, internal.trimEnd(), seri, eventId);
                        localStorage.removeItem('customerPart');
                    };

                    putRequest.onerror = function(event) {
                        const err = event?.target?.error?.message || 'Put request failed';
                        handleError('Gagal menyimpan data ke database lokal!', {
                            expected: `IDB put ke store "${STORE_NAME}" key=${primaryKey}`,
                            scanned: err
                        });
                        rollbackLocalCount();
                    };

                    transaction.onerror = function(event) {
                        const err = event?.target?.error?.message || 'Transaction failed';
                        handleError('Transaction failed!', {
                            expected: `IDB transaksi write ke store "${STORE_NAME}"`,
                            scanned: err
                        });
                        rollbackLocalCount();
                        database.close();
                    };
                }

                return true;
            };

            const requestMutation = () => {
                // === Request ke backend terlebih dahulu
                try {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('pulling.mutation') }}",
                        _token: "{{ csrf_token() }}",
                        data: {
                            loadingList: loadingList,
                            internalPart: internal.trimEnd(),
                            customerPart: localStorage.getItem('customerPart'),
                            serialNumber: seri,
                            qty_per_kbn: qty_per_kbn
                        },
                        contentType: 'application/json',
                        success: function(data) {
                            if (data.status == 'success') {
                                // eDCL opsional
                                if (localStorage.getItem('char_total') == 10000) {
                                    $.ajax({
                                        type: 'GET',
                                        url: "{{ url('/edcl/store') }}/" +
                                            skid + '/' + manifest + '/' + itemNo + '/' +
                                            seqNo + '/' + barcodecomplete + '/' +
                                            originalBarcode + '/' + loadingList + '/' +
                                            localStorage.getItem('customer'),
                                        _token: "{{ csrf_token() }}",
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status == 'success') {
                                                updateIndexedDB(data.scan_event_id);
                                                tmminSuccessIndicator();
                                            } else {
                                                handleError(response.message ||
                                                    'eDCL gagal', {
                                                        expected: 'eDCL success',
                                                        scanned: JSON.stringify(
                                                            response),
                                                        playSound: false
                                                    });
                                                tmminErrorIndicator();
                                            }
                                        },
                                        error: function(xhr) {
                                            const msg = xhrMessage(xhr);
                                            handleError(msg, {
                                                expected: 'HTTP 200 OK dari eDCL',
                                                scanned: `HTTP ${xhr.status}`
                                            });
                                        }
                                    });
                                } else {
                                    // tanpa eDCL
                                    updateIndexedDB(data.scan_event_id);
                                }
                            } else {
                                handleError(data.message || 'Validasi backend gagal', {
                                    expected: 'Response success dari mutation',
                                    scanned: JSON.stringify(data),
                                    playSound: !['notExists', 'alreadyPulled'].includes(data
                                        .status)
                                });
                                if (data.status == 'notExists') notExist();
                                if (data.status == 'alreadyPulled') alreadyPulledSound();
                                database.close();
                            }
                        },
                        error: function(xhr) {
                            const msg = xhrMessage(xhr);
                            handleError(msg, {
                                expected: 'HTTP 200 OK dari mutation',
                                scanned: `HTTP ${xhr.status}`
                            });
                            database.close();
                        }
                    });
                } catch (error) {
                    handleError('An error occurred. Please try again.', {
                        expected: 'AJAX berjalan sukses',
                        scanned: error?.message || String(error)
                    });
                    database.close();
                }
            };

            if (continueAfterQuantityCheck()) requestMutation();
        }

        class ScannerInput {
            constructor(selector, onScan, {
                gapMs = 40,
                acceptTab = true
            } = {}) {
                this.$el = $(selector);
                this.onScan = onScan;
                this.buffer = '';
                this.timer = null;
                this.gapMs = gapMs;
                this.acceptTab = acceptTab;

                // Jangan biarkan nilai menumpuk di input (biar selalu “kosong” secara visual)
                this.$el.val('');

                // 1) Kumpulkan karakter via 'input' (IME / Zebra / OS manapun)
                this.$el.on('input', (e) => {
                    const v = e.target.value || '';
                    if (!v) return;
                    this.buffer += v;
                    // kosongkan field supaya tidak bertambah panjang
                    e.target.value = '';
                    this._restartTimer();
                });

                // 2) Enter/TAB via keydown (DataWedge bisa diset suffix ENTER/TAB)
                this.$el.on('keydown', (e) => {
                    // ENTER → proses langsung
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this._flush();
                    }
                    // TAB (opsional) → proses langsung
                    else if (this.acceptTab && e.key === 'Tab') {
                        e.preventDefault();
                        this._flush();
                    } else {
                        // untuk zebra kadang kirim keyCode 229 (IME) → abaikan, biarkan 'input' yang bekerja
                    }
                });

                // 3) Paste fallback (kalau user tempel string)
                this.$el.on('paste', (e) => {
                    const text = (e.originalEvent.clipboardData || window.clipboardData)?.getData(
                        'text') || '';
                    if (text) {
                        this.buffer += text;
                        this._flushSoon();
                    }
                });

                // Aksesibilitas: fokus kembali jika blur
                this.$el.on('blur', () => {
                    // setTimeout(() => this.focus(), 0);
                });
            }

            _restartTimer() {
                clearTimeout(this.timer);
                // Jika scanner tidak mengirim suffix (ENTER/TAB),
                // kita finalize setelah jeda antarkarakter (inter-char gap).
                this.timer = setTimeout(() => this._flush(), this.gapMs);
            }

            _flushSoon() {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this._flush(), 0);
            }

            _flush() {
                clearTimeout(this.timer);
                if (!this.buffer) return;
                const code = this.buffer;
                this.buffer = '';
                try {
                    this.onScan(code);
                } catch (e) {
                    console.error(e);
                }
                this.focus();
            }

            focus() {
                const el = this.$el.get(0);
                if (!el) return;
                el.focus();
                try {
                    // taruh caret di akhir (meski field kita kosong)
                    el.setSelectionRange(el.value.length, el.value.length);
                } catch (_) {}
            }
        }

        class BarcodeScanner {
            constructor() {
                this.barcode = "";
                this.token = "{{ session()->get('token') }}" || '';
                this.progressTilePrefix = 'll-progress-';
                this.tileByKey = new Map();
                // Don't cache PDS value in constructor - get it dynamically when needed

                // Barcode patterns and handlers
                this.patterns = [{
                        test: (code) => code.startsWith('C') && code.length < 22,
                        handler: 'handleLoadingList'
                    },
                    {
                        test: (code) => code === "DONE",
                        handler: 'handleLogout'
                    },
                    {
                        test: () => this.isMmkiFamilyCustomer() && !localStorage.getItem(
                            'customerPart'),
                        handler: 'handleMMKIKanban'
                    },
                    {
                        test: (code) => this.isKanbanBarcode(code),
                        handler: 'handleKanbanBarcode'
                    },
                    {
                        test: (code) => code.length === parseInt(localStorage.getItem('char_total') ||
                            '0'),
                        handler: 'handleCustomerKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'TB INA',
                        handler: 'handleTBINAKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'TTI INDONESIA',
                        handler: 'handleTTIKanban'
                    }
                ];
            }

            sanitizeKey(str) {
                return String(str).replace(/[^A-Za-z0-9_-]/g, '');
            }
            getTileIdByKey(key) {
                return `${this.progressTilePrefix}${this.sanitizeKey(key)}`;
            }

            // ===== progress tile helpers =====
            createProgressTile(message = 'Mengambil data loading list...') {
                const id = `${this.progressTilePrefix}${Date.now()}`;
                $('#list').append(`
                    <li class="pulling-ll-item" id="${id}">
                    <div id="${id}-card" class="glass-tile glass-tile--loading glass-in">
                        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                        <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                    </div>
                    </li>
                `);
                return id;
            }

            ensureProgressTile(key, message = 'Mengambil data loading list...') {
                const id = this.getTileIdByKey(key);
                const $li = $(`#${id}`);
                if ($li.length) {
                    // reset ke state loading
                    const $card = $li.find(`#${id}-card`);
                    $card.attr('class', 'glass-tile glass-tile--loading glass-in')
                        .html(`
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                            `);
                    return id;
                }
                $('#list').append(`
                        <li class="pulling-ll-item" id="${id}">
                        <div id="${id}-card" class="glass-tile glass-tile--loading glass-in">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                        </div>
                        </li>
                    `);
                this.tileByKey.set(key, id);
                return id;
            }

            setProgressTile(id, msg) {
                $(`#${id}-text`).text(msg);
            }

            setProgressTileError(id, msg = 'Gagal memproses', {
                autoRemove = true,
                delay = 1500
            } = {}) {
                const $c = $(`#${id}-card`);
                $c.removeClass('glass-tile--loading').addClass('glass-tile--error');
                $c.find('.spinner-border').remove();
                $(`#${id}-text`).text(msg);
                if (autoRemove) setTimeout(() => this.removeTileById(id), delay);
            }

            removeTileById(id) {
                const $li = $(`#${id}`);
                if ($li.length) $li.remove();
                // bersihkan mapping key->id
                for (const [k, v] of this.tileByKey.entries())
                    if (v === id) this.tileByKey.delete(k);
            }

            // final: ganti tile progress jadi markup LAMA-mu (biru)
            replaceTileWithFinalOriginal(key, number) {
                const id = this.getTileIdByKey(key);
                replaceTileWithLoadingListChip(id, number);
                this.tileByKey.delete(key);
            }


            // Helper method to get current PDS dynamically
            getCurrentPDS() {
                return localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber') || '';
            }

            init() {
                this.scannerInput = new ScannerInput('#code', (scanned) => {
                    this.processBarcode(scanned); // panggil fungsi lama milikmu
                }, {
                    gapMs: 40, // jeda finalize jika tidak ada suffix
                    acceptTab: true // jika DataWedge pakai TAB sebagai suffix
                });

                // fokuskan saat mulai
                // this.scannerInput.focus();
            }

            handleKeyPress(e) {
                e.preventDefault();
                const keyCode = e.keyCode || e.which;
                if (keyCode === 13) { // Enter key
                    this.processBarcode(this.barcode);
                    this.barcode = "";
                } else {
                    this.barcode += String.fromCharCode(e.which);
                }
            }

            processBarcode(code) {
                console.log(`Processing barcode: ${code} (length: ${code.length})`);

                const pattern = this.patterns.find(p => p.test(code));
                if (pattern) {
                    this[pattern.handler](code);
                } else {
                    this.showError("Kanban tidak dikenali!", () => {
                        if (typeof unknownSound === 'function') unknownSound();
                    });
                }
            }

            isKanbanBarcode(code) {
                const kanbanLengths = [218, 220, 230, 241, 242];
                return kanbanLengths.includes(code.length);
            }

            isMmkiFamilyCustomer() {
                const customer = (localStorage.getItem('customer') || '').toUpperCase();
                return ['MMKI', 'SENOPATI', 'FUJITRANS'].some((name) => customer.includes(name));
            }

            async handleLoadingList(code) {
                const formattedCode = code.substr(0, 11) + ' A';
                let canonicalNumber = formattedCode;
                let ownsFormattedCode = false;
                let ownsCanonicalNumber = false;
                const formattedInFlight = inFlightLL.has(formattedCode);
                const tileId = formattedInFlight ? this.getTileIdByKey(formattedCode) : this
                    .ensureProgressTile(
                        formattedCode, 'Mengambil data loading list...');

                try {
                    if (formattedInFlight) {
                        this.showError('Loading list ini sedang diproses...');
                        return;
                    }
                    inFlightLL.add(formattedCode);
                    ownsFormattedCode = true;

                    const loadingList = this.getLoadingListNumber();
                    if (loadingList.includes(formattedCode) || hasLoadingListChip(formattedCode)) {
                        this.setProgressTileError(tileId, 'Sudah discan', {
                            autoRemove: true,
                            delay: 1200
                        });
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }

                    const response = await this.fetchLoadingList(formattedCode);
                    if (response.status !== 'success') {
                        this.setProgressTileError(tileId, response.message || 'Gagal ambil data', {
                            autoRemove: true
                        });
                        this.showError(response.message || 'Gagal mengambil data');
                        return;
                    }

                    canonicalNumber = response?.data?.number || formattedCode;
                    if ((canonicalNumber !== formattedCode && inFlightLL.has(canonicalNumber)) ||
                        this.getLoadingListNumber().includes(canonicalNumber) || hasLoadingListChip(
                            canonicalNumber) || isLLDone(canonicalNumber)) {
                        this.setProgressTileError(tileId, 'Sudah discan', {
                            autoRemove: true,
                            delay: 1200
                        });
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }
                    inFlightLL.add(canonicalNumber);
                    ownsCanonicalNumber = true;

                    const processed = await this.processLoadingListData(
                        response.data,
                        (msg) => this.setProgressTile(tileId, msg),
                        /* tileId not needed here now */
                    );
                    if (!processed) {
                        this.setProgressTileError(tileId, $(`#${tileId}-text`).text() ||
                            'Gagal memproses', {
                                autoRemove: true,
                                delay: 1500
                            });
                        return;
                    }

                    // sukses → replace dengan markup lama (biru)
                    this.replaceTileWithFinalOriginal(formattedCode, response.data.number);

                } catch (e) {
                    this.setProgressTileError(tileId, 'Koneksi bermasalah', {
                        autoRemove: true
                    });
                    this.showError('Connection Error');
                } finally {
                    if (ownsFormattedCode) inFlightLL.delete(formattedCode);
                    if (ownsCanonicalNumber) inFlightLL.delete(canonicalNumber);
                }
            }

            async fetchLoadingList(code) {
                return await $.ajax({
                    type: 'GET',
                    url: `https://dea-dev.aiia.co.id/api/v1/loading-lists/${code}`,
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    },
                    dataType: 'json'
                });
            }

            async processLoadingListData(data, progressCb = null, tileId = null) {
                const {
                    pds_number,
                    number: ll,
                    cycle,
                    customer_code,
                    delivery_date,
                    shipping_date
                } = data;

                const existingPDS = localStorage.getItem('pdsNumber');
                if (existingPDS && data.pds_number !== existingPDS) {
                    progressCb?.('LL tidak sesuai');
                    this.showError('Loading list tidak sesuai!', () => {
                        if (typeof notMatchLlSound === 'function') notMatchLlSound();
                    });
                    return false;
                }

                try {
                    progressCb?.('Menyimpan header...');
                    await this.storeLoadingList(ll, pds_number, cycle, customer_code, delivery_date,
                        shipping_date);

                    progressCb?.('Menyimpan detail item...');
                    await this.storeLoadingListDetails(ll, data.items);

                    progressCb?.('Menyimpan data lokal...');
                    this.storeLoadingListData(data);

                    progressCb?.('Memperbarui tampilan...');

                    if (!tileId) this.updateLoadingListUI(data);
                    $('#cycle-display').text(data.cycle);

                    progressCb?.('Menyiapkan IndexedDB...');
                    await this.initializeDatabase(data);

                    await hydrateScanHistoryFromMutations([ll]);

                    progressCb?.('Finalisasi...');
                    await this.customerCheck(customer_code, pds_number);
                    this.pullingQuantity();
                    this.checkLoadingList();
                    this.customerCharStore(customer_code, pds_number);

                    this.focusInput();
                    return true;

                } catch (error) {
                    progressCb?.('Gagal memproses');
                    if (!error?.pullingAlreadyNotified) {
                        this.showError(error?.message || 'Gagal memproses loading list');
                    }
                    return false;
                }
            }

            async storeLoadingList(ll, pds, cycle, customerCode, deliveryDate, shippingDate) {
                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `/loading-list/store/${ll}/${pds}/${cycle}/${customerCode}/${deliveryDate || ''}/${shippingDate || ''}`,
                        dataType: 'json'
                    });

                    // console.log('Loading list stored:', response.status);
                    return response;

                } catch (xhr) {
                    console.error('Error storing loading list:', xhr);

                    if (xhr.status === 0) {
                        this.showError('Connection Error');
                        throw new Error('Network error storing loading list');
                    } else {
                        const errorMsg = xhr.responseJSON?.errors || 'Gagal menyimpan loading list';
                        this.showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                }
            }

            async storeLoadingListDetails(ll, items) {
                try {
                    const response = await storeLoadingListDetailsBulk(ll, items);
                    const failures = response?.failed || [];

                    if (failures.length > 0) {
                        console.warn(`${failures.length} items failed to store:`, failures);
                        const error = createLoadingListDetailFailureError(failures);
                        this.showError(error.message);
                        throw error;
                    }

                    return response;
                } catch (xhr) {
                    if (xhr?.pullingAlreadyNotified) {
                        throw xhr;
                    }

                    console.error('Critical error storing loading list details:', xhr);

                    if (xhr.status === 0) {
                        throw new Error('Network error storing detail');
                    }

                    this.showError('Gagal menyimpan detail loading list. Koneksi bermasalah.');
                    throw new Error(xhr.responseJSON?.message || xhr.statusText ||
                        'Gagal menyimpan detail loading list');
                }
            }

            storeLoadingListData(data) {
                localStorage.setItem('pds_local', data.pds_number);
                localStorage.setItem(`ll_${data.number}`, data.number);
                localStorage.setItem('pdsNumber', data.pds_number);
                localStorage.setItem('cycle', data.cycle);
            }

            updateLoadingListUI(data, tileId = null) {
                if (tileId) this.replaceTileWithFinal(tileId, data.number);
                // update elemen lain:
                $('#cycle-display').text(data.cycle);
            }

            async initializeDatabase(data) {
                const database = await openPullingDB(data.pds_number);
                try {
                    await this.populateDatabase(database, data);
                } finally {
                    database.close();
                }
            }

            async populateDatabase(database, data) {
                const transaction = database.transaction([STORE_NAME], 'readwrite');
                const objectStore = transaction.objectStore(STORE_NAME);
                const transactionDone = new Promise((resolve, reject) => {
                    transaction.oncomplete = resolve;
                    transaction.onerror = () => reject(transaction.error);
                    transaction.onabort = () => reject(transaction.error);
                });

                const promises = data.items.map(item => {
                    return new Promise((resolve, reject) => {
                        const key = loadingListPartKey(item.part_number_cust, item
                            .part_number_int);
                        const getRequest = objectStore.get(key);

                        getRequest.onsuccess = (event) => {
                            try {
                                const merged = mergeLoadingListItem(event.target.result,
                                    item, data.number);

                                if (merged.shouldPut) {
                                    objectStore.put(merged.record, key);
                                }

                                resolve();
                            } catch (error) {
                                reject(error);
                            }
                        };

                        getRequest.onerror = () => reject(getRequest.error);
                    });
                });

                await Promise.all(promises);
                await transactionDone;
                await refreshPullingSummaryFromRecords(database);
            }

            handleCustomerKanban(code) {
                const processedCode = this.processCustomerCode(code);
                this.findKanbanInDatabase(processedCode, code);
            }

            processCustomerCode(code) {
                let processed = code;
                const charLength = parseInt(localStorage.getItem('char_length') || '0');
                const charFirst = parseInt(localStorage.getItem('char_first') || '0');

                if (charLength > 0) {
                    processed = code.substr(charFirst, charLength);
                }

                processed = processed.trim().replace(/-/g, '').toUpperCase();

                // Handle Suzuki special cases — KHUSUS customer Suzuki.
                // (Customer lain dengan char_length sama jangan ikut dipotong "000"-nya.)
                const isSuzuki = (localStorage.getItem('customer') || '').toUpperCase().includes('SUZUKI');
                if (isSuzuki) {
                    if (charLength === 17 && processed.substr(10, 3) === '000') {
                        processed = processed.slice(0, -3);
                    } else if (charLength === 15 && processed.slice(-3) === '000') {
                        processed = processed.slice(0, -3);
                    }
                }

                return processed;
            }

            async findKanbanInDatabase(processedCode, originalCode) {
                // Get current PDS dynamically
                const databaseName = this.getCurrentPDS();

                // console.log('PDS value for database:', databaseName);
                // console.log('Available PDS values:', {
                //     'getCurrentPDS()': databaseName,
                //     'pds_local': localStorage.getItem('pds_local'),
                //     'pdsNumber': localStorage.getItem('pdsNumber')
                // });

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                openPullingDB(databaseName).then((database) => {
                    try {
                        const transaction = database.transaction([STORE_NAME], 'readonly');
                        const objectStore = transaction.objectStore(STORE_NAME);
                        findPartRecordInStore(objectStore, processedCode).then((match) => {
                            const record = match?.record;
                            if (!record) {
                                this.showError('Kanban tidak dikenali / sesuai!', () => {
                                    if (typeof unknownSound === 'function')
                                        unknownSound();
                                });
                                return;
                            }

                            this.updateKanbanDisplay(record, processedCode, originalCode);
                            scheduleScanHistoryBackgroundSync(isDirectOn() ? 2000 : 250);

                            const qty = partQuantityFromRecord(record);
                            if (qty.target > 0 && qty.actual >= qty.target) {
                                this.showError('Part number sudah complete!', () => {
                                    if (typeof fullfilledSound === 'function')
                                        fullfilledSound();
                                });
                                this.setIndicator('danger');
                                return;
                            }
                        }).catch((event) => {
                            console.error('Get request error:', event?.target?.error ||
                                event);
                            this.showError('Error membaca database');
                        });


                        transaction.onerror = (event) => {
                            console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                        transaction.oncomplete = () => {
                            database.close();
                        };

                    } catch (error) {
                        console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                }).catch((error) => {
                    console.error('Database open error:', error);
                    this.showError('Error membuka database');
                });
            }

            handleKanbanBarcode(code) {
                if (!localStorage.getItem('customerPart')) {
                    this.showError('Scan kanban customer dulu!', () => {
                        if (typeof scanCustomerFirstSound === 'function') scanCustomerFirstSound();
                    });
                    return;
                }

                const kanbanData = this.extractKanbanData(code.toUpperCase());
                if (kanbanData) {
                    this.processInternalKanban(kanbanData);
                }
            }

            extractKanbanData(code) {
                const patterns = {
                    230: {
                        internal: [41, 19],
                        seri: [123, 4]
                    },
                    220: {
                        internal: [35, 16],
                        seri: [130, 4]
                    },
                    241: {
                        internal: [35, 12],
                        seri: [127, 4]
                    },
                    218: {
                        internal: [41, 16],
                        seri: [123, 4]
                    },
                    242: {
                        internal: [35, 12],
                        seri: [127, 4]
                    }
                };

                const pattern = patterns[code.length];
                if (!pattern) return null;

                return {
                    internal: code.substr(pattern.internal[0], pattern.internal[1]),
                    seri: code.substr(pattern.seri[0], pattern.seri[1])
                };
            }

            processInternalKanban({
                internal,
                seri
            }) {
                const databaseName = this.getCurrentPDS();

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                openPullingDB(databaseName).then((database) => {
                    try {
                        const transaction = database.transaction([STORE_NAME], 'readonly');
                        const objectStore = transaction.objectStore(STORE_NAME);
                        const customerPart = localStorage.getItem('customerPart');
                        findPartRecordInStore(objectStore, customerPart, internal).then((match) => {
                            const record = match?.record;
                            if (record) {
                                this.checkInternalAndCustomer(database, record, internal,
                                    match.key || recordStorageKey(record), seri);
                            } else {
                                this.showError('Kanban tidak ditemukan!', () => {
                                    if (typeof unknownSound === 'function')
                                        unknownSound();
                                });
                                database.close();
                            }
                        }).catch(() => {
                            this.showError('Error membaca data kanban');
                            database.close();
                        });


                        transaction.onerror = (event) => {
                            // console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                    } catch (error) {
                        // console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                }).catch((event) => {
                    // console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                });
            }

            handleMMKIKanban(code) {
                this.processCustomerSpecificKanban(code.trimEnd(), 'MMKI');
            }

            handleTBINAKanban(code) {
                let processed = code;
                if (code.length === 36) {
                    processed = code.substr(parseInt(localStorage.getItem('char_first') || '0'),
                        parseInt(localStorage.getItem('char_length') || '0'));
                } else if (code.length === 14) {
                    processed = code.substr(0, 11);
                }

                processed = processed.trim().replace(/-/g, '').toUpperCase();
                console.log('Processed TB INA kanban code:', processed);
                this.processCustomerSpecificKanban(processed, 'TB INA');
            }

            handleTTIKanban(code) {
                const processed = code.substr(7, 10);
                this.processCustomerSpecificKanban(processed.trimEnd(), 'TTI INDONESIA');
            }

            async processCustomerSpecificKanban(code, customerType) {
                console.log(`Processing ${customerType} kanban:`, code);
                const databaseName = this.getCurrentPDS();

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                openPullingDB(databaseName).then((database) => {
                    try {
                        const transaction = database.transaction([STORE_NAME], 'readonly');
                        const objectStore = transaction.objectStore(STORE_NAME);

                        findPartRecordInStore(objectStore, code).then((match) => {
                            const record = match?.record;
                            if (!record) {
                                this.showError('Kanban tidak sesuai!', () => {
                                    if (typeof notMatchSound === 'function')
                                        notMatchSound();
                                });
                                return;
                            }

                            this.updateKanbanDisplay(record, record.customer || code, code);
                            scheduleScanHistoryBackgroundSync(isDirectOn() ? 2000 : 250);

                            const qty = partQuantityFromRecord(record);
                            if (qty.target > 0 && qty.actual >= qty.target) {
                                this.showError('Part number sudah complete!', () => {
                                    if (typeof fullfilledSound === 'function')
                                        fullfilledSound();
                                });
                                this.setIndicator('danger');
                                return;
                            }
                        }).catch((event) => {
                            console.error('Get request error:', event?.target?.error ||
                                event);
                            this.showError('Error membaca database');
                        });

                        transaction.onerror = (event) => {
                            // console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                        transaction.oncomplete = () => {
                            database.close();
                        };

                    } catch (error) {
                        // console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                }).catch((error) => {
                    // console.error('Database open error:', error);
                    this.showError('Error membuka database');
                });
            }

            handleLogout() {
                const existingForm = document.getElementById('pullingLogoutForm');
                if (existingForm) {
                    if (typeof existingForm.requestSubmit === 'function') {
                        existingForm.requestSubmit();
                    } else {
                        existingForm.submit();
                    }
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('logout.auth'));

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }

            updateKanbanDisplay(record, customerCode, originalCode = null) {
                $('#cust-display').text(record.customer);
                $('#backno-display').text('-');
                fetchAlamatChute(record.loading_list_number, record.customer, record.internal);
                $('#int-display').text('-');
                updatePartQuantityDisplay(record);
                refreshPartQuantityDisplayFromIndexedDB(record);

                this.setIndicator('warning');
                this.resetIndicator();

                localStorage.setItem('customerPart', record.customer);
                if (originalCode) {
                    localStorage.setItem('originalCustomerPart', originalCode);
                }
            }

            setIndicator(type) {
                const indicator = $('#indicator');
                indicator.removeClass('bg-success bg-warning bg-danger');
                indicator.addClass(`bg-${type}`);
            }

            showError(message, soundFunction = null) {
                if (typeof notif === 'function') {
                    notif('error', message);
                }
                if (soundFunction) soundFunction();
                this.delayedFocus();
            }

            delayedFocus() {
                setTimeout(() => this.focusInput(), 1000);
            }

            focusInput() {
                $('#code').focus();
            }

            getLoadingListNumber() {
                return typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
            }

            checkInternalAndCustomer(database, record, internal, customerPart, seri) {
                if (typeof checkInternalAndCustomer === 'function') {
                    checkInternalAndCustomer(database, record, internal, customerPart, seri);
                }
            }

            resetIndicator() {
                if (typeof resetIndicator === 'function') {
                    resetIndicator();
                }
            }

            async customerCheck(customerCode, pdsNumber = null) {
                return new Promise((resolve, reject) => {
                    if (typeof customerCheck === 'function') {
                        customerCheck(customerCode, pdsNumber).then(resolve).catch(reject);
                    } else {
                        resolve();
                    }
                });
            }

            pullingQuantity() {
                if (typeof pullingQuantity === 'function') {
                    pullingQuantity();
                }
            }

            checkLoadingList() {
                if (typeof checkLoadingList === 'function') {
                    checkLoadingList();
                }
            }

            customerCharStore(customerCode, pdsNumber) {
                if (typeof customerCharStore === 'function') {
                    customerCharStore(customerCode, pdsNumber);
                }
            }
        }

        (function() {
            if (!window.BarcodeScanner) return;
            const proto = BarcodeScanner.prototype;

            if (typeof proto.updateKanbanDisplay === 'function') {
                const _orig = proto.updateKanbanDisplay;
                proto.updateKanbanDisplay = function(record, customerCode, originalCode = null) {
                    const ret = _orig.call(this, record, customerCode, originalCode);

                    if (isDirectOn()) {
                        const customerPart = String(record?.customer || '').trim();
                        if (customerPart) {
                            getPartRecordFromIDB(customerPart)
                                .then(rec => {
                                    console.log('[DIRECT] IDB OK:', {
                                        loading_list_number: rec.loading_list_number,
                                        internal: rec.internal,
                                        total_qty: rec.total_qty,
                                        seri_len: rec.seri?.length
                                    });
                                })
                                .catch(err => {
                                    console.warn('[DIRECT] IDB error:', err?.message || err);
                                });
                        }
                    }
                    return ret;
                };
            }
        })();


        // Initialize the barcode scanner
        $(document).ready(() => {
            const scanner = new BarcodeScanner();
            // HOOK INSTANCE: panggil single-scan saat Direct ON
            const _origUpdate = scanner.updateKanbanDisplay.bind(scanner);
            scanner.updateKanbanDisplay = function(record, customerCode, originalCode = null) {
                // TRACE (biar tetap kelihatan di console)
                console.log(
                    '[TRACE] updateKanbanDisplay CALLED (Direct will try single-scan if ON)', {
                        directOn: (localStorage.getItem('directMode') || 'off'),
                        customer: record?.customer,
                        len_seri: record?.seri?.length,
                        total: record?.total_qty
                    });

                // jalankan logika asli (update UI customer panel, set localStorage.customerPart, dll.)
                const ret = _origUpdate(record, customerCode, originalCode);

                // kalau Direct ON → jalankan single-scan
                if ((localStorage.getItem('directMode') || 'off') === 'on') {
                    const customerPart = String(record?.customer || '').trim();
                    if (customerPart) directSingleScan(customerPart);
                } else {
                    console.log('[TRACE] Direct OFF – tidak single-scan.');
                }

                return ret;
            };

            (function() {
                const proto = BarcodeScanner.prototype;
                if (typeof proto.handleKanbanBarcode === 'function') {
                    const _orig = proto.handleKanbanBarcode;
                    BarcodeScanner.prototype.handleKanbanBarcode = function(code) {
                        if ((localStorage.getItem('directMode') || 'off') === 'on') {
                            if (typeof notif === 'function') notif('error',
                                'Direct ON: scan internal diabaikan.');
                            setTimeout(() => $('#code').focus(), 200);
                            return; // stop flow internal
                        }
                        return _orig.call(this, code);
                    };
                }
            })();


            scanner.init();
        });
    });
</script>
