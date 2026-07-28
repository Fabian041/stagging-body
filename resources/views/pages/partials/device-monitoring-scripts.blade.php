<script>
    (function() {
        function toggleModal(el, open) {
            if (!el) return;
            el.classList.toggle('open', open);
            el.setAttribute('aria-hidden', open ? 'false' : 'true');
        }

        document.addEventListener('click', function(e) {
            var openBtn = e.target.closest('[data-device-modal-open]');
            if (openBtn) {
                toggleModal(document.getElementById(openBtn.getAttribute('data-device-modal-open')), true);
                return;
            }

            if (e.target.closest('[data-device-modal-close]')) {
                toggleModal(e.target.closest('.settings-modal'), false);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.settings-modal.open').forEach(function(modal) {
                if (modal.id && modal.id.indexOf('deviceModal') === 0) toggleModal(modal, false);
            });
        });
    })();

    (function() {
        function bindDeviceConfirm(formSelector, modalId, closeSelector, confirmId) {
            var modal = document.getElementById(modalId);
            var confirm = document.getElementById(confirmId);
            var pendingForm = null;
            var confirmedForm = null;

            function toggleModal(open) {
                if (!modal) return;
                modal.classList.toggle('open', open);
                modal.setAttribute('aria-hidden', open ? 'false' : 'true');
            }

            function closeModal() {
                pendingForm = null;
                toggleModal(false);
            }

            document.addEventListener('submit', function(e) {
                if (!e.target.matches(formSelector)) return;
                if (e.target === confirmedForm) {
                    confirmedForm = null;
                    return;
                }

                e.preventDefault();
                e.stopImmediatePropagation();
                pendingForm = e.target;
                if (confirm) confirm.disabled = false;
                toggleModal(true);
            }, true);

            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target.closest(closeSelector)) closeModal();
                });
            }

            if (confirm) {
                confirm.addEventListener('click', function() {
                    if (!pendingForm) return;
                    confirmedForm = pendingForm;
                    confirm.disabled = true;
                    if (pendingForm.requestSubmit) {
                        pendingForm.requestSubmit();
                    } else {
                        pendingForm.submit();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal();
            });
        }

        bindDeviceConfirm('[data-device-reset-form]', 'settingsDeviceResetModal', '[data-device-reset-close]', 'settingsDeviceResetConfirm');
        bindDeviceConfirm('[data-device-delete-form]', 'settingsDeviceDeleteModal', '[data-device-delete-close]', 'settingsDeviceDeleteConfirm');
    })();

    (function() {
        var inFlight = false;
        var lastSuccessAt = Date.now();
        var reloadKey = 'settings_device_monitor_scroll';
        var heartbeatInitialized = false;

        try {
            var restoreScroll = sessionStorage.getItem(reloadKey);
            if (restoreScroll) {
                sessionStorage.removeItem(reloadKey);
                window.scrollTo(0, parseInt(restoreScroll, 10) || 0);
            }
        } catch (e) {}

        function shouldSkip() {
            return document.hidden
                || inFlight
                || document.querySelector('#settingsDeviceResetModal.open, #settingsDeviceDeleteModal.open')
                || (document.activeElement && document.activeElement.closest('[data-device-monitor-section] input, [data-device-monitor-section] textarea, [data-device-monitor-section] select'));
        }

        function hasPendingReset() {
            return !!document.querySelector('[data-device-reset-pending]');
        }

        function reloadForSync() {
            if (!hasPendingReset()) return;
            try { sessionStorage.setItem(reloadKey, String(window.scrollY || 0)); } catch (e) {}
            window.location.reload();
        }

        function refreshDeviceMonitoring(force) {
            var current = document.querySelector('[data-device-monitor-section]');
            if (!current || (!force && shouldSkip())) return;

            var previousHeartbeats = {};
            current.querySelectorAll('[data-device-heartbeat]').forEach(function(heartbeat) {
                previousHeartbeats[heartbeat.getAttribute('data-device-heartbeat')] = parseInt(
                    heartbeat.getAttribute('data-heartbeat-count') || '0', 10
                ) || 0;
            });

            inFlight = true;
            var openDeviceModal = document.querySelector('.settings-modal.open[id^="deviceModal"]');
            var openDeviceModalId = openDeviceModal ? openDeviceModal.id : '';
            var url = new URL(current.getAttribute('data-device-monitor-url'), window.location.origin);
            url.searchParams.set('_device_monitor', Date.now());

            fetch(url.toString(), {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function(response) { return response.ok ? response.json() : null; })
                .then(function(data) {
                    if (!data || !data.html || (!force && shouldSkip())) return;
                    current = document.querySelector('[data-device-monitor-section]');
                    if (current) {
                        current.outerHTML = data.html;
                        current = document.querySelector('[data-device-monitor-section]');
                        current.querySelectorAll('[data-device-heartbeat].is-online').forEach(function(heartbeat) {
                            var deviceId = heartbeat.getAttribute('data-device-heartbeat');
                            var latestCount = parseInt(heartbeat.getAttribute('data-heartbeat-count') || '0', 10) || 0;
                            var countIncreased = Object.prototype.hasOwnProperty.call(previousHeartbeats, deviceId)
                                && latestCount > previousHeartbeats[deviceId];
                            if (countIncreased || (!heartbeatInitialized && latestCount > 0)) {
                                heartbeat.classList.add('has-beat');
                            }
                        });
                        heartbeatInitialized = true;
                        lastSuccessAt = Date.now();
                        if (openDeviceModalId) {
                            var freshModal = document.getElementById(openDeviceModalId);
                            if (freshModal) {
                                freshModal.classList.add('open');
                                freshModal.setAttribute('aria-hidden', 'false');
                            }
                        }
                    }
                })
                .catch(function() { reloadForSync(); })
                .finally(function() { inFlight = false; });
        }

        refreshDeviceMonitoring(true);
        setInterval(function() {
            refreshDeviceMonitoring(false);
            if (hasPendingReset() && Date.now() - lastSuccessAt > 15000) reloadForSync();
        }, 5000);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) refreshDeviceMonitoring(true);
        });
    })();
</script>
