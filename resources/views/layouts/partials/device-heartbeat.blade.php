@auth
<script>
    (function() {
        var key = 'bella_device_id';
        var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var heartbeatUrl = @json(route('settings.devices.heartbeat'));
        var resetAppliedUrl = @json(route('settings.devices.reset-applied'));
        var errorUrl = @json(route('settings.devices.error'));
        var resetInProgress = false;
        var lastErrorKey = '';
        var isPullingPage = window.location.pathname.indexOf('/pulling') === 0;
        var heartbeatIntervalMs = isPullingPage ? 60000 : 30000;
        var initialHeartbeatDelayMs = isPullingPage ? 5000 : 0;

        // Device monitoring KHUSUS halaman scanner pulling (route /pulling). Di luar itu, tidak melacak apa pun.
        if (window.location.pathname !== '/pulling') return;

        function readCookie(name) {
            return document.cookie.split('; ').reduce(function(value, part) {
                var pair = part.split('=');
                return pair[0] === name ? decodeURIComponent(pair.slice(1).join('=')) : value;
            }, '');
        }

        function writeDeviceCookie(id) {
            document.cookie = key + '=' + encodeURIComponent(id) + '; path=/; max-age=31536000; SameSite=Lax';
        }

        function newDeviceId() {
            if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
            return 'dev-' + Date.now() + '-' + Math.random().toString(16).slice(2);
        }

        function deviceId() {
            var cookieId = readCookie(key);

            try {
                var id = localStorage.getItem(key);
                if (!id) {
                    id = cookieId || newDeviceId();
                    localStorage.setItem(key, id);
                }
                if (!cookieId) writeDeviceCookie(id);
                return id;
            } catch (e) {
                if (cookieId) return cookieId;
                var fallbackId = newDeviceId();
                writeDeviceCookie(fallbackId);
                return fallbackId;
            }
        }

        window.getBellaDeviceId = deviceId;

        function post(url, data) {
            return fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(data)
            });
        }

        function clearIndexedDB() {
            if (!window.indexedDB || !indexedDB.databases) return Promise.resolve();

            return indexedDB.databases().then(function(databases) {
                return Promise.all((databases || []).map(function(database) {
                    if (!database.name) return Promise.resolve();

                    return new Promise(function(resolve) {
                        var request = indexedDB.deleteDatabase(database.name);
                        request.onsuccess = resolve;
                        request.onerror = resolve;
                        request.onblocked = resolve;
                    });
                }));
            }).catch(function() {});
        }

        function applyReset() {
            if (resetInProgress) return;
            resetInProgress = true;

            var id = deviceId();
            try {
                localStorage.clear();
                localStorage.setItem(key, id);
                writeDeviceCookie(id);
            } catch (e) {}

            post(resetAppliedUrl, { device_id: id })
                .catch(function() {})
                .then(function() {
                    return Promise.race([
                        clearIndexedDB(),
                        new Promise(function(resolve) { setTimeout(resolve, 2500); })
                    ]);
                })
                .finally(function() {
                    window.location.reload();
                });
        }

        function heartbeat() {
            var id = deviceId();
            if (!id || !csrf || resetInProgress || document.hidden) return;

            post(heartbeatUrl, {
                    device_id: id,
                    current_page: (window.location.pathname + window.location.search).slice(0, 255),
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || '',
                    screen_size: screen.width + 'x' + screen.height + '@' + (window.devicePixelRatio || 1),
                    language: navigator.language || '',
                    online: navigator.onLine ? 1 : 0
                })
                .then(function(response) {
                    return response.ok ? response.json() : null;
                })
                .then(function(data) {
                    if (data && data.reset_required) applyReset();
                })
                .catch(function() {});
        }

        function reportClientError(message, source, line, column) {
            var id = deviceId();
            message = String(message || 'Unknown client error').slice(0, 500);
            source = String(source || window.location.pathname).slice(0, 255);

            var errorKey = message + '|' + source + '|' + (line || 0);
            if (!id || !csrf || errorKey === lastErrorKey) return;
            lastErrorKey = errorKey;

            post(errorUrl, {
                device_id: id,
                message: message,
                source: source,
                line: line || null,
                column: column || null
            }).catch(function() {});
        }

        setTimeout(heartbeat, initialHeartbeatDelayMs);
        setInterval(heartbeat, heartbeatIntervalMs);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) heartbeat();
        });
        window.addEventListener('error', function(event) {
            reportClientError(event.message, event.filename, event.lineno, event.colno);
        });
        window.addEventListener('unhandledrejection', function(event) {
            var reason = event.reason;
            reportClientError(reason && reason.message ? reason.message : reason, 'unhandledrejection');
        });
    })();
</script>
@endauth
