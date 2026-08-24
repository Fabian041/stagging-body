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
        paintingCount: 'scan_count_painting'
    };

    const sessionKeys = Object.values(keys);
    const automaticKanbanLengths = [218, 220, 230, 241];
    let isProcessing = false;
    let notificationTimer = null;

    function readNumber(key, fallback = 0) {
        const value = Number.parseInt(localStorage.getItem(key) || '', 10);
        return Number.isFinite(value) ? value : fallback;
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

    function normalizePartNumber(value) {
        return String(value || '').trim().toUpperCase();
    }

    function extractPartNumber(barcode) {
        const match = String(barcode || '').toUpperCase().match(/(\d{6,7})-(\d{5})-([A-Z0-9]{3})/);
        if (!match) return null;

        let firstSection = match[1];
        if (firstSection.length === 7 && firstSection.startsWith('0')) {
            firstSection = firstSection.slice(1);
        }

        return `${firstSection}-${match[2]}-${match[3]}`;
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
            assemblyPieces,
            paintingPieces,
            targetPainting: paintingQty > 0 ? Math.ceil(assemblyPieces / paintingQty) : 0,
            isComplete: assemblyCount > 0 && assemblyPieces === paintingPieces
        };
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
            `${session.assemblyModel} · ${session.assemblyCount} kanban · ${session.assemblyPieces} pcs`
        );
        $('#model_painting').text(
            `${session.paintingModel} · ${session.paintingCount}/${session.targetPainting} kanban · ` +
            `${session.paintingPieces}/${session.assemblyPieces} pcs`
        );
        $('#assembly-help').text(`${session.assemblyQty} pieces per kanban`);
        $('#painting-help').text(`${session.paintingQty} pieces per kanban`);
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
            const remainingPieces = Math.max(0, session.assemblyPieces - session.paintingPieces);
            $('#pairing-status').text(`Belum lengkap · Kurang ${remainingPieces} pcs painting`);
            setCardState('.model-card-header', '.model-card', 'info');
            setCardState(
                '.total-scan-card-header',
                '.total-scan-card',
                session.paintingCount > 0 ? 'info' : 'secondary'
            );
        }
    }

    function clearSession() {
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
        if (!scannedPart) {
            notify('error', 'Barcode kanban tidak valid atau part number tidak terbaca.');
            playSound('not-match-sound');
            return;
        }

        if (scannedPart === normalizePartNumber(session.assemblyPart)) {
            const nextCount = session.assemblyCount + 1;
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

            const nextPaintingPieces = (session.paintingCount + 1) * session.paintingQty;
            if (nextPaintingPieces > session.assemblyPieces) {
                notify('error', 'Kanban painting melebihi total pieces assembly. Scan assembly terlebih dahulu.');
                playSound('full-filled');
                return;
            }

            const nextCount = session.paintingCount + 1;
            localStorage.setItem(keys.paintingCount, String(nextCount));
            updateDisplay();

            const updatedSession = getSession();
            notify(
                'success',
                updatedSession.isComplete
                    ? 'Pairing lengkap. Tekan Finish atau scan assembly untuk menambah batch.'
                    : `Kanban painting ke-${nextCount} berhasil discan.`
            );
            playSound(updatedSession.isComplete ? 'match-sound' : 'ok-sound');
            return;
        }

        notify('error', 'Kanban tidak sesuai dengan pasangan Dandori Board.');
        playSound('wrong-kanban-sound');
    }

    async function processBarcode(barcode) {
        const value = String(barcode || '').trim();
        if (!value || isProcessing) return;

        isProcessing = true;
        $('#code').prop('disabled', true);

        try {
            if (isDandoriBarcode(value)) {
                await scanDandori(value);
            } else {
                scanKanban(value);
            }
        } finally {
            $('#code').val('').prop('disabled', false).focus();
            isProcessing = false;
        }
    }

    function finishPairing() {
        const session = getSession();
        if (!session.isComplete) {
            notify('error', 'Pairing belum lengkap. Samakan total pieces assembly dan painting.');
            playSound('not-match-sound');
            return;
        }

        clearSession();
        notify('success', 'Pairing selesai. Silakan scan Dandori Board berikutnya.');
        playSound('ok-sound');
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

    return { processBarcode, finishPairing, clearSession, getSession };
})();
