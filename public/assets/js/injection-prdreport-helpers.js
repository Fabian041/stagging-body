window.InjectionScanHelpers = (() => {
    const STORAGE_KEYS = {
        line: 'line_prd',
        moldId: 'injection_mold_id',
        moldCode: 'injection_mold_code',
        moldName: 'injection_mold_name',
        moldImageUrl: 'injection_mold_image_url',
        moldParts: 'injection_mold_parts',
        cycleScannedParts: 'injection_cycle_scanned_parts',
        cycleNumber: 'injection_cycle_number',
        lastPartNumber: 'injection_last_part_number',
        lastBackNumber: 'injection_last_back_number',
        lastPhoto: 'injection_last_photo',
        scanCounter: 'scan_counter',
        partCounter: 'part_counter',
        productionStartTime: 'production_start_time',
        lastKanbanTime: 'last_kanban_time',
        internal: 'internal',
        error: 'error',
        moldError: 'mold_error'
    };

    const ROUTES = {
        checkMold: '/injection/mold-check',
        getInternal: '/injection/api-get-internal',
        storeScan: '/injection/store',
        stopProduction: '/injection/api-stop',
        insertStop: '/injection/api-insert-stop',
        listStop: '/injection/api-list-stop',
        errorStore: '/error/store',
        index: '/injection',
        logout: '/logout'
    };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function playAudio(id) {
        const audio = document.getElementById(id);
        if (audio) audio.play().catch(() => {});
    }

    function normalizePartNumber(value) {
        return String(value || '').trim();
    }

    function getLineName() {
        return localStorage.getItem(STORAGE_KEYS.line) || '';
    }

    function getMoldParts() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEYS.moldParts) || '[]');
        } catch (e) {
            return [];
        }
    }

    function getCycleScannedParts() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEYS.cycleScannedParts) || '[]');
        } catch (e) {
            return [];
        }
    }

    function getCycleNumber() {
        return parseInt(localStorage.getItem(STORAGE_KEYS.cycleNumber) || '1', 10);
    }

    function isPartAlreadyScannedInCurrentCycle(partNumber) {
        const target = normalizePartNumber(partNumber);
        const scannedParts = getCycleScannedParts();

        return scannedParts.some(item => normalizePartNumber(item) === target);
    }

    function markPartScannedInCurrentCycle(partNumber) {
        const target = normalizePartNumber(partNumber);
        const scannedParts = getCycleScannedParts();

        if (!scannedParts.includes(target)) {
            scannedParts.push(target);
        }

        localStorage.setItem(STORAGE_KEYS.cycleScannedParts, JSON.stringify(scannedParts));
        return scannedParts;
    }

    function getUniqueMoldPartNumbers() {
        const parts = getMoldParts();

        return [...new Set(parts
            .map(item => {
                if (typeof item === 'string') {
                    return normalizePartNumber(item);
                }

                return normalizePartNumber(
                    item.part_number ||
                    item.internal_part_number ||
                    item.internal
                );
            })
            .filter(Boolean))];
    }

    function getRemainingPartsInCurrentCycle() {
        const moldParts = getUniqueMoldPartNumbers();
        const scannedParts = getCycleScannedParts();

        return moldParts.filter(partNumber => !scannedParts.includes(partNumber));
    }

    function isCurrentCycleComplete() {
        const moldParts = getUniqueMoldPartNumbers();
        const scannedParts = getCycleScannedParts();

        return moldParts.length > 0 && moldParts.every(partNumber => scannedParts.includes(partNumber));
    }

    function findMoldPart(partNumber) {
        const target = normalizePartNumber(partNumber);
        const parts = getMoldParts();

        return parts.find(item => {
            if (typeof item === 'string') {
                return normalizePartNumber(item) === target;
            }

            return normalizePartNumber(item.part_number) === target ||
                normalizePartNumber(item.internal_part_number) === target ||
                normalizePartNumber(item.internal) === target;
        }) || null;
    }

    function isPartMemberOfMold(partNumber) {
        const target = normalizePartNumber(partNumber);
        const parts = getMoldParts();

        return parts.some(item => {
            if (typeof item === 'string') {
                return normalizePartNumber(item) === target;
            }

            return normalizePartNumber(item.part_number) === target ||
                normalizePartNumber(item.internal_part_number) === target ||
                normalizePartNumber(item.internal) === target;
        });
    }

    function resetCurrentCycle(incrementCycle = false) {
        let cycleNumber = getCycleNumber();

        if (incrementCycle) {
            cycleNumber++;
        }

        localStorage.setItem(STORAGE_KEYS.cycleNumber, cycleNumber);
        localStorage.setItem(STORAGE_KEYS.cycleScannedParts, JSON.stringify([]));
    }

    return {
        STORAGE_KEYS,
        ROUTES,
        csrfToken,
        playAudio,
        normalizePartNumber,
        getLineName,
        getMoldParts,
        getCycleScannedParts,
        getCycleNumber,
        isPartAlreadyScannedInCurrentCycle,
        markPartScannedInCurrentCycle,
        getUniqueMoldPartNumbers,
        getRemainingPartsInCurrentCycle,
        isCurrentCycleComplete,
        findMoldPart,
        isPartMemberOfMold,
        resetCurrentCycle
    };
})();
