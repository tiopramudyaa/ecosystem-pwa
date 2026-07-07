<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $ticket['ticket_number'] ?? 'Ticket' }} - EcoSystem Lite</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 14px; vertical-align: top; }
        th { background: #f5f5f5; width: 220px; }
        .message { border: 1px solid #ddd; border-radius: 6px; padding: 10px; margin-bottom: 10px; transition: box-shadow 0.3s ease, border-color 0.3s ease; }
        .message.internal_note { background: #fff8e1; }
        .message-meta { font-size: 12px; color: #666; margin-bottom: 4px; }
        .message.highlight-flash { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.4); }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>{{ $ticket['ticket_number'] ?? '-' }}</h1>
        <div style="display:flex; gap:12px; align-items:center;">
            @include('partials.notifications-bell')
            <a href="{{ route('tickets.index') }}">&larr; Daftar Tiket</a>
        </div>
    </div>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <table>
        <tr><th>Description</th><td>{{ $ticket['description'] ?? '-' }}</td></tr>
        <tr><th>Status</th><td>{{ $ticket['status'] ?? '-' }}</td></tr>
        <tr><th>Priority</th><td>{{ $ticket['ticket_priority'] ?? '-' }}</td></tr>
        <tr><th>Type</th><td>{{ $ticket['ticket_type'] ?? '-' }}</td></tr>
        <tr><th>Scale</th><td>{{ $ticket['scale'] ?? '-' }}</td></tr>
        <tr><th>Channel</th><td>{{ $ticket['channel'] ?? '-' }}</td></tr>
        <tr><th>Progress</th><td>{{ $ticket['progress_percentage'] ?? '-' }}%</td></tr>
        <tr><th>Start Date</th><td>{{ $ticket['start_date'] ?? '-' }}</td></tr>
        <tr><th>End Date</th><td>{{ $ticket['end_date'] ?? '-' }}</td></tr>
        <tr><th>Customer</th><td>{{ $ticket['customer']['customer_name'] ?? '-' }}</td></tr>
        <tr><th>PIC</th><td>{{ $ticket['pic']['employee_name'] ?? '-' }}</td></tr>
        <tr><th>Members</th>
            <td>
                @forelse ($ticket['members'] ?? [] as $member)
                    {{ $member['employee_name'] ?? '-' }}@if (!$loop->last), @endif
                @empty
                    -
                @endforelse
            </td>
        </tr>
        <tr><th>SLA Resolution</th><td>{{ $ticket['sla']['resolution_status'] ?? '-' }} (due: {{ $ticket['sla']['resolution_due_at'] ?? '-' }})</td></tr>
        <tr><th>SLA Response</th><td>{{ $ticket['sla']['response_status'] ?? '-' }}</td></tr>
        <tr><th>Created At</th><td>{{ $ticket['created_at'] ?? '-' }}</td></tr>
        <tr><th>Updated At</th><td>{{ $ticket['updated_at'] ?? '-' }}</td></tr>
    </table>

    <h2>Ubah Status</h2>
    <form method="POST" action="{{ route('tickets.status', $ticket['ticket_id']) }}">
        @csrf
        @method('PATCH')
        <select name="status">
            @foreach (['open','inprocess','waiting_on_customer','waiting_on_3rd_party','waiting_to_confirmation','hold','cancelled','closed'] as $status)
                <option value="{{ $status }}" @selected(($ticket['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button type="submit">Update Status</button>
    </form>

    <h2>Pesan</h2>
    <div id="messages">
        @forelse ($messages as $message)
            <div id="msg-{{ $message['id'] ?? '' }}" class="message {{ $message['message_type'] ?? 'reply' }}" @if($message['is_highlighted'] ?? false) data-highlighted="true" @endif>
                <div class="message-meta">
                    {{ $message['sender_name'] ?? '-' }} &middot; {{ $message['created_at'] ?? '-' }}
                    @if (($message['message_type'] ?? 'reply') === 'internal_note')
                        &middot; <strong>Internal Note</strong>
                    @endif
                </div>
                <div style="white-space: pre-wrap;">{{ $message['message_body'] ?? '' }}</div>
            </div>
        @empty
            <p>Belum ada pesan.</p>
        @endforelse
    </div>

    <script>
        (function () {
            var highlighted = document.querySelector('#messages [data-highlighted="true"]');

            if (highlighted) {
                highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
                highlighted.classList.add('highlight-flash');
                setTimeout(function () {
                    highlighted.classList.remove('highlight-flash');
                }, 2000);
            } else if ({{ $requestedHighlightMessageId ? 'true' : 'false' }}) {
                var lastMessage = document.querySelector('#messages .message:last-child');
                if (lastMessage) {
                    lastMessage.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            }
        })();
    </script>

    <h2>Kirim Pesan</h2>
    <form method="POST" action="{{ route('tickets.messages.store', $ticket['ticket_id']) }}">
        @csrf
        <div>
            <textarea name="message" rows="4" style="width:100%;" required placeholder="Tulis pesan..."></textarea>
        </div>
        <div style="margin-top:8px;">
            <label><input type="radio" name="message_type" value="reply" checked> Reply</label>
            <label><input type="radio" name="message_type" value="internal_note"> Internal Note</label>
        </div>
        <button type="submit" style="margin-top:8px;">Kirim</button>
    </form>
</body>
</html>
