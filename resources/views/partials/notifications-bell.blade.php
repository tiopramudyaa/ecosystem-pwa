<a href="{{ route('notifications.index') }}" id="notif-bell-link" class="relative text-gray-600 hover:text-gray-900" title="Notifications">
    <i class="fas fa-bell text-lg"></i>
    <span id="notif-badge" class="hidden absolute -top-1.5 -right-2 bg-red-600 text-white text-[10px] font-bold leading-none rounded-full px-1.5 py-0.5"></span>
</a>
<script>
    (function () {
        var badge = document.getElementById('notif-badge');
        var bellLink = document.getElementById('notif-bell-link');
        var listUrl = '{{ route('notifications.index') }}';
        var seenKey = 'notif_seen_ids';

        function getSeenIds() {
            try {
                return JSON.parse(localStorage.getItem(seenKey) || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveSeenIds(ids) {
            try {
                localStorage.setItem(seenKey, JSON.stringify(ids.slice(-200)));
            } catch (e) {}
        }

        function showSystemNotification(notification) {
            if (!('serviceWorker' in navigator) || Notification.permission !== 'granted') return;

            var title = notification.ticket_number ? notification.ticket_number : 'New Notification';
            var url = notification.ticket_id
                ? '{{ url('/tickets') }}/' + notification.ticket_id + (notification.message_id ? ('?highlight_message_id=' + notification.message_id) : '')
                : listUrl;

            navigator.serviceWorker.ready.then(function (reg) {
                reg.showNotification(title, {
                    body: notification.preview || '',
                    icon: '/images/icons/icon-192.png',
                    badge: '/images/icons/icon-192.png',
                    tag: 'notif-' + notification.id,
                    data: { url: url },
                });
            });
        }

        function refreshNotifications() {
            fetch(listUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data || !data.success) return;

                    var unreadCount = data.unread_count || 0;
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }

                    var seenIds = getSeenIds();
                    var isFirstRun = seenIds.length === 0 && !localStorage.getItem(seenKey + '_init');
                    var newSeenIds = seenIds.slice();

                    (data.data || []).forEach(function (notification) {
                        if (seenIds.indexOf(notification.id) === -1) {
                            newSeenIds.push(notification.id);
                            if (!isFirstRun && !notification.is_read) {
                                showSystemNotification(notification);
                            }
                        }
                    });

                    localStorage.setItem(seenKey + '_init', '1');
                    saveSeenIds(newSeenIds);
                })
                .catch(function () {});
        }

        function requestPermissionOnce(event) {
            if (!('Notification' in window) || Notification.permission !== 'default') return;

            event.preventDefault();
            Notification.requestPermission().then(function () {
                return window.ecosystemSubscribeToPush ? window.ecosystemSubscribeToPush() : null;
            }).finally(function () {
                window.location.href = bellLink.href;
            });
        }

        if (bellLink) {
            bellLink.addEventListener('click', requestPermissionOnce);
        }

        refreshNotifications();
        setInterval(refreshNotifications, 30000);
    })();
</script>
