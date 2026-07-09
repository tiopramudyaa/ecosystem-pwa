<a href="{{ route('notifications.index') }}" class="relative text-gray-600 hover:text-gray-900" title="Notifikasi">
    <i class="fas fa-bell text-lg"></i>
    <span id="notif-badge" class="hidden absolute -top-1.5 -right-2 bg-red-600 text-white text-[10px] font-bold leading-none rounded-full px-1.5 py-0.5"></span>
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
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(function () {});
        }

        refreshUnreadCount();
        setInterval(refreshUnreadCount, 30000);
    })();
</script>
