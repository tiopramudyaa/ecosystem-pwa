<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets - EcoSystem Lite</title>
    <style>
        body { font-family: sans-serif; max-width: 1000px; margin: 40px auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 14px; }
        th { background: #f5f5f5; }
        .stats { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .stat-box { border: 1px solid #ddd; border-radius: 6px; padding: 8px 14px; min-width: 90px; }
        .stat-box strong { display: block; font-size: 18px; }
        form.filters { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .pagination a { margin-right: 8px; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Tickets</h1>
        <div style="display:flex; gap:12px; align-items:center;">
            @include('partials.notifications-bell')
            <a href="{{ route('dashboard') }}">&larr; Dashboard</a>
        </div>
    </div>

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    @if (!empty($stats))
        <div class="stats">
            @foreach ($stats as $label => $value)
                <div class="stat-box">
                    <strong>{{ $value }}</strong>
                    {{ ucwords(str_replace('_', ' ', $label)) }}
                </div>
            @endforeach
        </div>
    @endif

    <div style="display:flex; gap:8px; margin-bottom:12px;">
        <a href="{{ route('tickets.index', array_merge($filters, ['scope' => 'all'])) }}"
           style="{{ ($scope ?? 'all') === 'all' ? 'font-weight:bold;' : '' }}">Semua / Unassigned</a>
        <a href="{{ route('tickets.index', array_merge($filters, ['scope' => 'my'])) }}"
           style="{{ ($scope ?? 'all') === 'my' ? 'font-weight:bold;' : '' }}">Tiket Saya</a>
    </div>

    <form class="filters" method="GET" action="{{ route('tickets.index') }}">
        <input type="hidden" name="scope" value="{{ $scope ?? 'all' }}">
        <input type="text" name="search" placeholder="Cari nomor/deskripsi" value="{{ $filters['search'] ?? '' }}">
        <select name="status">
            <option value="">Semua Status</option>
            @foreach (['open','inprocess','waiting_on_customer','waiting_on_3rd_party','waiting_to_confirmation','hold','cancelled','closed'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <select name="priority">
            <option value="">Semua Prioritas</option>
            @foreach (['Very High','High','Medium','Low'] as $priority)
                <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ $priority }}</option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
    </form>

    <table>
        <tr>
            <th>Number</th>
            <th>Description</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Customer</th>
            <th>PIC</th>
            <th>SLA</th>
        </tr>
        @forelse ($tickets as $ticket)
            <tr>
                <td><a href="{{ route('tickets.show', $ticket['ticket_id']) }}">{{ $ticket['ticket_number'] ?? '-' }}</a></td>
                <td>{{ $ticket['description'] ?? '-' }}</td>
                <td>{{ $ticket['status'] ?? '-' }}</td>
                <td>{{ $ticket['ticket_priority'] ?? '-' }}</td>
                <td>{{ $ticket['customer']['customer_name'] ?? '-' }}</td>
                <td>{{ $ticket['pic']['employee_name'] ?? '-' }}</td>
                <td>{{ $ticket['sla']['resolution_status'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Tidak ada tiket.</td></tr>
        @endforelse
    </table>

    @if (!empty($meta))
        <div class="pagination">
            <span>Halaman {{ $meta['current_page'] ?? 1 }} dari {{ $meta['last_page'] ?? 1 }} (total {{ $meta['total'] ?? 0 }})</span>
            <br>
            @if (($meta['current_page'] ?? 1) > 1)
                <a href="{{ route('tickets.index', array_merge($filters, ['scope' => $scope ?? 'all', 'page' => ($meta['current_page'] - 1)])) }}">&laquo; Sebelumnya</a>
            @endif
            @if (($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                <a href="{{ route('tickets.index', array_merge($filters, ['scope' => $scope ?? 'all', 'page' => ($meta['current_page'] + 1)])) }}">Selanjutnya &raquo;</a>
            @endif
        </div>
    @endif
</body>
</html>
