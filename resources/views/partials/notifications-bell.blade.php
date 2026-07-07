<a href="{{ route('notifications.index') }}" style="position:relative; text-decoration:none;">
    &#128276; Notifikasi
    <span id="notif-badge" style="display:none; background:#e02424; color:#fff; border-radius:10px; padding:0 6px; font-size:11px; position:relative; top:-8px;"></span>
</a>
<script>
    (function () {
        var badge = document.getElementById('notif-badge');

        function refreshUnreadCount() {
            fetch('{{ route('notifications.unread-count') }}', { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data || !data.success) return;
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(function () {});
        }

        refreshUnreadCount();
        setInterval(refreshUnreadCount, 30000);
    })();
</script>
