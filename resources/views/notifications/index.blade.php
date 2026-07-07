<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifikasi - EcoSystem Lite</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; }
        .notif { border: 1px solid #ddd; border-radius: 6px; padding: 10px 14px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .notif.unread { background: #eef4ff; border-color: #b6d0ff; }
        .notif-body { flex: 1; }
        .notif-body.notif-clickable { cursor: pointer; }
        .notif-meta { font-size: 12px; color: #666; margin-bottom: 4px; }
        .notif-actions { display: flex; gap: 8px; white-space: nowrap; }
        .notif-actions form { margin: 0; }
        .notif-actions button { font-size: 12px; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Notifikasi</h1>
        <a href="{{ route('dashboard') }}">&larr; Dashboard</a>
    </div>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <div style="display:flex; gap:8px; margin-bottom:16px;">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            @method('PUT')
            <button type="submit">Tandai Semua Dibaca</button>
        </form>
        <form method="POST" action="{{ route('notifications.bulk-delete') }}">
            @csrf
            @method('DELETE')
            <button type="submit">Hapus yang Sudah Dibaca</button>
        </form>
    </div>

    @forelse ($notifications as $notification)
        @php
            $ticketUrl = !empty($notification['ticket_id'])
                ? route('tickets.show', $notification['ticket_id']) . (!empty($notification['message_id']) ? ('?highlight_message_id=' . $notification['message_id']) : '')
                : null;
        @endphp
        <div class="notif {{ ($notification['is_read'] ?? false) ? '' : 'unread' }}">
            <div
                class="notif-body {{ $ticketUrl ? 'notif-clickable' : '' }}"
                @if ($ticketUrl)
                    data-ticket-url="{{ $ticketUrl }}"
                    data-read-url="{{ route('notifications.read', $notification['id']) }}"
                    data-is-read="{{ ($notification['is_read'] ?? false) ? '1' : '0' }}"
                    onclick="openNotification(this)"
                @endif
            >
                <div class="notif-meta">{{ $notification['from_name'] ?? '-' }} &middot; {{ $notification['created_at'] ?? '-' }}</div>
                <div>
                    @if (!empty($notification['ticket_number']))
                        <strong>{{ $notification['ticket_number'] }}</strong> &mdash;
                    @endif
                    {{ $notification['preview'] ?? '' }}
                </div>
            </div>
            <div class="notif-actions">
                @if (!($notification['is_read'] ?? false))
                    <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                        @csrf
                        @method('PUT')
                        <button type="submit">Tandai Dibaca</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('notifications.destroy', $notification['id']) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <p>Belum ada notifikasi.</p>
    @endforelse

    <script>
        function openNotification(el) {
            var url = el.getAttribute('data-ticket-url');
            var isRead = el.getAttribute('data-is-read') === '1';

            if (isRead) {
                window.location.href = url;
                return;
            }

            var readUrl = el.getAttribute('data-read-url');
            var token = document.querySelector('meta[name="csrf-token"]').content;

            fetch(readUrl, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            }).catch(function () {}).finally(function () {
                window.location.href = url;
            });
        }
    </script>
</body>
</html>
