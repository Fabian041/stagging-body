window.ValidationPairingFlow = (() => {
    const keys = {
        dandori: 'validation_dandori_part',
        assemblyPart: 'assy_part_number',
        paintingPart: 'expected_painting',
        assemblyModel: 'model_assy',
        paintingModel: 'model_painting',
        assemblyQty: 'qty_assy',
        paintingQty: 'qty_painting',
        assemblyCount: 'scan_count_assy',
        paintingCount: 'scan_count_painting',
        assemblySerials: 'validation_assembly_serials',
        paintingSerials: 'validation_painting_serials'
    };

    const sessionKeys = Object.values(keys);
    const automaticKanbanLengths = [218, 219, 220, 230, 241];
    let isProcessing = false;
    let isAutoResetting = false;
    let autoResetTimer = null;
    let notificationTimer = null;

    function readNumber(key, fallback = 0) {
        const value = Number.parseInt(localStorage.getItem(key) || '', 10);
        return Number.isFinite(value) ? value : fallback;
    }

    function readList(key) {
        try {
            const value = JSON.parse(localStorage.getItem(key) || '[]');
            return Array.isArray(value) ? value : [];
        } catch (error) {
            return [];
        }
    }

    function playSound(id) {
        const sound = document.getElementById(id);
        if (sound) {
            sound.currentTime = 0;
            sound.play().catch(() => {});
        }
    }

    function notify(type, message, duration = 1800) {
        if (notificationTimer) {
            clearTimeout(notificationTimer);
        }

        $('#notif').text(message);
        $('#divNotif').css('background-color', type === 'error' ? '#dc2626' : '#16a34a');
        $('#notifModal').modal('show');

        notificationTimer = setTimeout(() => {
            $('#notifModal').modal('hide');
            $('#code').focus();
            notificationTimer = null;
        }, duration);
    }

    function completePairingAutomatically() {
        if (notificationTimer) {
            clearTimeout(notificationTimer);
            notificationTimer = null;
        }
        if (autoResetTimer) clearTimeout(autoResetTimer);

        isAutoResetting = true;
        $('#code').val('').prop('disabled', true);
        $('#finishButton').prop('disabled', true);
        $('#pairing-status').text('Complete Pairing... reset otomatis');
        $('#notif').html('<i class="fas fa-spinner fa-spin mr-2"></i>Complete Pairing...');
        $('#divNotif').css('background-color', '#16a34a');
        $('#notifModal').modal('show');
        playSound('match-sound');

        autoResetTimer = setTimeout(() => {
            autoResetTimer = null;
            isAutoResetting = false;
            clearSession();
            $('#notifModal').modal('hide');
            $('#code').prop('disabled', false).focus();
        }, 1500);
    }

    function normalizePartNumber(value) {
        return String(value || '').trim().toUpperCase();
    }

    function extractPartNumber(barcode) {
        const match = String(barcode || '')
            .toUpperCase()
            .match(/(\d{6,7})-(\d{5})-(?:([A-Z0-9]{2})-([A-Z0-9]{3})|([A-Z0-9]{3}))/);
        if (!match) return null;

        let firstSection = match[1];
        if (firstSection.length === 7 && firstSection.startsWith('0')) {
            firstSection = firstSection.slice(1);
        }

        const partSuffix = match[3]
            ? [match[3], match[4]]
            : [match[5]];

        return [firstSection, match[2], ...partSuffix]
            .filter(Boolean)
            .join('-');
    }

    function extractSerialNumber(barcode) {
        const value = String(barcode || '').trim();
        const serialPositionByLength = {
            218: 123,
            219: 124,
            220: 130,
            230: 123,
            241: 127
        };
        const startPosition = serialPositionByLength[value.length];

        if (startPosition === undefined) return null;

        const serialNumber = value.substr(startPosition, 4).trim().toUpperCase();
        return serialNumber.length === 4 ? serialNumber : null;
    }

    function isDandoriBarcode(barcode) {
        return /-dandori$/i.test(String(barcode || '').trim());
    }

    function getSession() {
        const assemblyCount = readNumber(keys.assemblyCount);
        const paintingCount = readNumber(keys.paintingCount);
        const assemblyQty = readNumber(keys.assemblyQty);
        const paintingQty = readNumber(keys.paintingQty);
        const assemblyPieces = assemblyCount * assemblyQty;
        const paintingPieces = paintingCount * paintingQty;
        const targetPainting = paintingQty > 0 ? Math.floor(assemblyPieces / paintingQty) : 0;
        const piecesNeededForNextPainting = Math.max(
            0,
            (paintingPieces + paintingQty) - assemblyPieces
        );
        const additionalAssemblyNeeded = assemblyQty > 0 && piecesNeededForNextPainting > 0
            ? Math.ceil(piecesNeededForNextPainting / assemblyQty)
            : 0;

        return {
            dandori: localStorage.getItem(keys.dandori) || '',
            assemblyPart: localStorage.getItem(keys.assemblyPart) || '',
            paintingPart: localStorage.getItem(keys.paintingPart) || '',
            assemblyModel: localStorage.getItem(keys.assemblyModel) || '-',
            paintingModel: localStorage.getItem(keys.paintingModel) || '-',
            assemblyCount,
            paintingCount,
            assemblyQty,
            paintingQty,
            assemblySerials: readList(keys.assemblySerials),
            paintingSerials: readList(keys.paintingSerials),
            assemblyPieces,
            paintingPieces,
            targetPainting,
            additionalAssemblyNeeded,
            isComplete: assemblyCount > 0 && assemblyPieces === paintingPieces
        };
    }

    function greatestCommonDivisor(firstValue, secondValue) {
        let first = Math.abs(firstValue);
        let second = Math.abs(secondValue);

        while (second) {
            [first, second] = [second, first % second];
        }

        return first || 1;
    }

    function setCardState(headerSelector, cardSelector, state) {
        $(headerSelector)
            .removeClass('card-secondary card-info card-success')
            .addClass(`card-${state}`);
        $(cardSelector)
            .removeClass('bg-secondary bg-info bg-success')
            .addClass(`bg-${state}`);
    }

    function updateDisplay() {
        const session = getSession();
        const hasMaster = Boolean(session.dandori && session.assemblyPart && session.paintingPart);

        $('#dandori-part').text(session.dandori || '-');
        $('#total-part').text(session.assemblyCount);
        $('#total-scan').text(session.paintingCount);

        if (!hasMaster) {
            $('#pairing-status').text('Scan Dandori Board');
            $('#assembly-help, #painting-help').text('Menunggu Dandori Board');
            $('#model_assy, #model_painting').text('-');
            $('#finishButton').prop('disabled', true);
            setCardState('.model-card-header', '.model-card', 'secondary');
            setCardState('.total-scan-card-header', '.total-scan-card', 'secondary');
            return;
        }

        $('#model_assy').text(
            `${session.assemblyModel} · ${session.assemblyCount} kanban`
        );
        $('#model_painting').text(
            `${session.paintingModel} · ${session.paintingCount}/${session.targetPainting} kanban`
        );
        const ratioDivisor = greatestCommonDivisor(session.assemblyQty, session.paintingQty);
        const assemblyKanbanRatio = session.paintingQty / ratioDivisor;
        const paintingKanbanRatio = session.assemblyQty / ratioDivisor;

        $('#assembly-help').text(
            `Rasio ${assemblyKanbanRatio} assembly : ${paintingKanbanRatio} painting`
        );
        $('#painting-help').text('Target mengikuti jumlah kanban assembly');
        $('#finishButton').prop('disabled', !session.isComplete);

        if (session.isComplete) {
            $('#pairing-status').text('Lengkap · Tekan Finish');
            setCardState('.model-card-header', '.model-card', 'success');
            setCardState('.total-scan-card-header', '.total-scan-card', 'success');
        } else if (session.assemblyCount === 0) {
            $('#pairing-status').text('Scan Kanban Assembly');
            setCardState('.model-card-header', '.model-card', 'info');
            setCardState('.total-scan-card-header', '.total-scan-card', 'secondary');
        } else {
            const remainingKanban = Math.max(0, session.targetPainting - session.paintingCount);
            if (remainingKanban > 0) {
                $('#pairing-status').text(`Belum lengkap · Kurang ${remainingKanban} kanban painting`);
            } else {
                $('#pairing-status').text(
                    `Belum lengkap · Scan ${session.additionalAssemblyNeeded} kanban assembly lagi`
                );
            }
            setCardState('.model-card-header', '.model-card', 'info');
            setCardState(
                '.total-scan-card-header',
                '.total-scan-card',
                session.paintingCount > 0 ? 'info' : 'secondary'
            );
        }
    }

    function clearSession() {
        if (autoResetTimer) {
            clearTimeout(autoResetTimer);
            autoResetTimer = null;
        }
        isAutoResetting = false;
        sessionKeys.forEach(key => localStorage.removeItem(key));
        localStorage.removeItem('error');
        localStorage.removeItem('dandori_error');
        localStorage.removeItem('master_dandori_error');
        updateDisplay();
    }

    async function scanDandori(barcode) {
        const activeSession = getSession();
        if (activeSession.assemblyPart) {
            notify('error', 'Sesi pairing masih aktif. Selesaikan pairing lalu tekan Finish.');
            playSound('wrong-kanban-sound');
            return;
        }

        const dandoriPart = extractPartNumber(barcode.replace(/-dandori$/i, ''));
        if (!dandoriPart) {
            notify('error', 'Format Dandori Board tidak valid.');
            playSound('dandori-ng-sound');
            return;
        }

        try {
            const response = await fetch(`/validation/kanban/pairing?part=${encodeURIComponent(dandoriPart)}`);
            const data = await response.json();

            if (!response.ok || !data.success) {
                notify('error', data.message || 'Master pairing Dandori tidak ditemukan.');
                playSound('master-dandori-ng-sound');
                return;
            }

            const assemblyQty = Number.parseInt(data.qty_assy, 10);
            const paintingQty = Number.parseInt(data.qty_painting, 10);
            if (assemblyQty <= 0 || paintingQty <= 0) {
                notify('error', 'Quantity per kanban pada master pairing belum valid.');
                playSound('master-dandori-ng-sound');
                return;
            }

            localStorage.setItem(keys.dandori, dandoriPart);
            localStorage.setItem(keys.assemblyPart, normalizePartNumber(data.assembly));
            localStorage.setItem(keys.paintingPart, normalizePartNumber(data.painting));
            localStorage.setItem(keys.assemblyModel, data.model_assy || '-');
            localStorage.setItem(keys.paintingModel, data.model_painting || '-');
            localStorage.setItem(keys.assemblyQty, String(assemblyQty));
            localStorage.setItem(keys.paintingQty, String(paintingQty));
            localStorage.setItem(keys.assemblyCount, '0');
            localStorage.setItem(keys.paintingCount, '0');
            localStorage.setItem(keys.assemblySerials, '[]');
            localStorage.setItem(keys.paintingSerials, '[]');

            updateDisplay();
            notify('success', 'Dandori Board sesuai master. Silakan scan kanban assembly.');
            playSound('ok-sound');
        } catch (error) {
            notify('error', 'Gagal mengambil master pairing. Periksa koneksi.');
            playSound('error-connection');
        }
    }

    function scanKanban(barcode) {
        const session = getSession();
        if (!session.dandori || !session.assemblyPart) {
            notify('error', 'Scan Dandori Board terlebih dahulu.');
            playSound('dandori-ng-sound');
            return;
        }

        const scannedPart = extractPartNumber(barcode);
        const scannedSerial = extractSerialNumber(barcode);
        if (!scannedPart) {
            notify('error', 'Barcode kanban tidak valid atau part number tidak terbaca.');
            playSound('not-match-sound');
            return;
        }

        if (!scannedSerial) {
            notify('error', 'Serial kanban tidak dapat dibaca.');
            playSound('not-match-sound');
            return;
        }

        if (scannedPart === normalizePartNumber(session.assemblyPart)) {
            if (session.assemblySerials.includes(scannedSerial)) {
                notify('error', `Serial assembly ${scannedSerial} sudah pernah discan.`);
                playSound('already-scan-sound');
                return;
            }

            const nextCount = session.assemblyCount + 1;
            localStorage.setItem(
                keys.assemblySerials,
                JSON.stringify([...session.assemblySerials, scannedSerial])
            );
            localStorage.setItem(keys.assemblyCount, String(nextCount));
            updateDisplay();
            notify('success', `Kanban assembly ke-${nextCount} berhasil discan.`);
            playSound('ok-sound');
            return;
        }

        if (scannedPart === normalizePartNumber(session.paintingPart)) {
            if (session.assemblyCount === 0) {
                notify('error', 'Scan minimal satu kanban assembly sebelum painting.');
                playSound('not-match-sound');
                return;
            }

            if (session.paintingSerials.includes(scannedSerial)) {
                notify('error', `Serial painting ${scannedSerial} sudah pernah discan.`);
                playSound('already-scan-sound');
                return;
            }

            const nextPaintingPieces = (session.paintingCount + 1) * session.paintingQty;
            if (nextPaintingPieces > session.assemblyPieces) {
                notify(
                    'error',
                    `Kanban assembly belum cukup. Scan ${session.additionalAssemblyNeeded} kanban assembly lagi.`
                );
                playSound('full-filled');
                return;
            }

            const nextCount = session.paintingCount + 1;
            localStorage.setItem(
                keys.paintingSerials,
                JSON.stringify([...session.paintingSerials, scannedSerial])
            );
            localStorage.setItem(keys.paintingCount, String(nextCount));
            updateDisplay();

            const updatedSession = getSession();
            if (updatedSession.isComplete) {
                completePairingAutomatically();
                return;
            }

            notify('success', `Kanban painting ke-${nextCount} berhasil discan.`);
            playSound('ok-sound');
            return;
        }

        notify('error', 'Kanban tidak sesuai dengan pasangan Dandori Board.');
        playSound('wrong-kanban-sound');
    }

    async function processBarcode(barcode) {
        const value = String(barcode || '').trim();
        if (!value || isProcessing) return;
        if (isAutoResetting) {
            $('#code').val('');
            return;
        }

        isProcessing = true;
        $('#code').prop('disabled', true);

        try {
            if (isDandoriBarcode(value)) {
                await scanDandori(value);
            } else {
                scanKanban(value);
            }
        } finally {
            $('#code').val('').prop('disabled', isAutoResetting).focus();
            isProcessing = false;
        }
    }

    function finishPairing() {
        const session = getSession();
        if (!session.isComplete) {
            notify('error', 'Pairing belum lengkap. Lengkapi jumlah kanban painting sesuai kebutuhan.');
            playSound('not-match-sound');
            return;
        }

        clearSession();
        notify('success', 'Pairing selesai. Silakan scan Dandori Board berikutnya.');
        playSound('ok-sound');
    }

    function resetTransaction() {
        const session = getSession();
        const hasTransaction = Boolean(
            session.dandori ||
            session.assemblyCount > 0 ||
            session.paintingCount > 0
        );

        if (hasTransaction && !window.confirm(
            'Reset transaksi ini? Semua progress scan assembly dan painting akan dihapus.'
        )) {
            $('#code').focus();
            return;
        }

        clearSession();
        notify('success', 'Transaksi berhasil di-reset. Silakan scan Dandori Board.');
    }

    function bindScanner() {
        $('#code').on('keydown.validationPairing', function(event) {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            processBarcode($(this).val());
        });

        $('#code').on('input.validationPairing', function() {
            const value = $(this).val().trim();
            if (isDandoriBarcode(value) || automaticKanbanLengths.includes(value.length)) {
                processBarcode(value);
            }
        });

        $(document).on('click.validationPairing', function(event) {
            if (!$(event.target).is('#finishButton, #input-confirmation')) {
                $('#code').focus();
            }
        });

        $('#finishButton').on('click.validationPairing', finishPairing);
        $('#resetTransactionButton').on('click.validationPairing', resetTransaction);
    }

    $(document).ready(function() {
        const restoredSession = getSession();
        if (restoredSession.assemblyPart && !restoredSession.dandori) {
            clearSession();
        }

        updateDisplay();
        bindScanner();
        $('#code').focus();
    });

    return { processBarcode, finishPairing, resetTransaction, clearSession, getSession };
})();
