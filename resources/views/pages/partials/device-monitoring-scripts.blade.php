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
        var heartbeatCounts = {};
        var pollCount = 0;
        var refreshFullAfterRequest = false;

        try {
            var restoreScroll = sessionStorage.getItem(reloadKey);
            if (restoreScroll) {
                sessionStorage.removeItem(reloadKey);
                window.scrollTo(0, parseInt(restoreScroll, 10) || 0);
            }
        } catch (e) {}

        function shouldSkipFullRefresh() {
            return document.querySelector('#settingsDeviceResetModal.open, #settingsDeviceDeleteModal.open')
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

        function captureHeartbeatState(section) {
            var latestCounts = {};

            section.querySelectorAll('[data-device-heartbeat]').forEach(function(heartbeat) {
                var deviceId = heartbeat.getAttribute('data-device-heartbeat');
                latestCounts[deviceId] = parseInt(heartbeat.getAttribute('data-heartbeat-count') || '0', 10) || 0;
            });

            heartbeatCounts = latestCounts;
        }

        function applyDeviceSummary(section, devices, animateChanges) {
            var liveDevices = {};
            var latestCounts = {};
            var structureChanged = false;

            (devices || []).forEach(function(device) {
                liveDevices[device.device_id] = device;
            });

            var cards = section.querySelectorAll('[data-device-card]');
            if (cards.length !== Object.keys(liveDevices).length) structureChanged = true;

            cards.forEach(function(card) {
                var deviceId = card.getAttribute('data-device-card');
                var device = liveDevices[deviceId];
                if (!device) {
                    structureChanged = true;
                    return;
                }

                var latestCount = parseInt(device.heartbeat_count || 0, 10) || 0;
                latestCounts[deviceId] = latestCount;

                var heartbeat = card.querySelector('[data-device-heartbeat]');
                if (heartbeat) {
                    heartbeat.setAttribute('data-heartbeat-count', String(latestCount));
                    heartbeat.setAttribute('aria-label', 'Heartbeat ' + latestCount.toLocaleString('id-ID'));
                    heartbeat.classList.toggle('is-online', !!device.is_online);

                    var count = heartbeat.querySelector('.settings-heartbeat-count');
                    if (count) count.textContent = 'HB ' + latestCount.toLocaleString('id-ID');

                    if (animateChanges
                        && device.is_online
                        && Object.prototype.hasOwnProperty.call(heartbeatCounts, deviceId)
                        && latestCount > heartbeatCounts[deviceId]) {
                        heartbeat.classList.remove('has-beat');
                        void heartbeat.offsetWidth;
                        heartbeat.classList.add('has-beat');
                    }
                }

                var status = card.querySelector('[data-device-online]');
                if (status) {
                    status.classList.toggle('is-online', !!device.is_online);
                    status.innerHTML = '<i class="fas fa-circle"></i> ' + (device.is_online ? 'Online' : 'Offline');
                }

                var lastSeen = card.querySelector('[data-device-last-seen]');
                if (lastSeen) {
                    lastSeen.textContent = device.last_seen_label || '-';
                    lastSeen.title = device.last_seen_at_label || '-';
                }

                if (card.getAttribute('data-device-reset-state') !== (device.reset_pending ? '1' : '0')) {
                    structureChanged = true;
                }
            });

            heartbeatCounts = latestCounts;
            return structureChanged;
        }

        function refreshDeviceMonitoring(fullRefresh) {
            var current = document.querySelector('[data-device-monitor-section]');
            if (!current || document.hidden || inFlight) return;
            if (fullRefresh && shouldSkipFullRefresh()) fullRefresh = false;

            inFlight = true;
            var openDeviceModal = document.querySelector('.settings-modal.open[id^="deviceModal"]');
            var openDeviceModalId = openDeviceModal ? openDeviceModal.id : '';
            var url = new URL(current.getAttribute('data-device-monitor-url'), window.location.origin);
            url.searchParams.set('_device_monitor', Date.now());
            url.searchParams.set('summary', fullRefresh ? '0' : '1');

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
                    if (!data || !data.devices) return;
                    current = document.querySelector('[data-device-monitor-section]');
                    if (!current) return;

                    if (fullRefresh && data.html) {
                        current.outerHTML = data.html;
                        current = document.querySelector('[data-device-monitor-section]');
                    }

                    refreshFullAfterRequest = applyDeviceSummary(current, data.devices, true);
                    lastSuccessAt = Date.now();
                    if (fullRefresh && openDeviceModalId) {
                        var freshModal = document.getElementById(openDeviceModalId);
                        if (freshModal) {
                            freshModal.classList.add('open');
                            freshModal.setAttribute('aria-hidden', 'false');
                        }
                    }
                })
                .catch(function() { reloadForSync(); })
                .finally(function() {
                    inFlight = false;
                    if (refreshFullAfterRequest) {
                        refreshFullAfterRequest = false;
                        refreshDeviceMonitoring(true);
                    }
                });
        }

        var initialSection = document.querySelector('[data-device-monitor-section]');
        if (initialSection) captureHeartbeatState(initialSection);
        refreshDeviceMonitoring(false);
        setInterval(function() {
            pollCount += 1;
            refreshDeviceMonitoring(pollCount % 6 === 0);
            if (hasPendingReset() && Date.now() - lastSuccessAt > 15000) reloadForSync();
        }, 5000);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) refreshDeviceMonitoring(true);
        });
    })();
</script>
