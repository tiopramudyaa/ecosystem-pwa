<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - EcoSystem Lite</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 900px;
            margin: 40px auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 10px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f5f5f5;
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 16px;
            min-width: 100px;
        }

        .stat-box strong {
            display: block;
            font-size: 20px;
        }

        h2 {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Dashboard</h1>
        <div style="display:flex; gap:12px; align-items:center;">
            <a href="{{ route('tickets.index') }}">Tickets</a>
            @include('partials.notifications-bell')
            <a href="{{ route('profile.index') }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>

    <p>Login sebagai <strong>{{ $user['name'] ?? '-' }}</strong> ({{ $user['role']['name'] ?? '-' }})</p>

    @if ($errors->any())
    <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    @isset($data['ticket_stats'])
    <h2>Ticket Stats</h2>
    <div class="stats">
        @foreach ($data['ticket_stats'] as $label => $value)
        <div class="stat-box">
            <strong>{{ $value }}</strong>
            {{ ucwords(str_replace('_', ' ', $label)) }}
        </div>
        @endforeach
    </div>
    @endisset

    @isset($data['sla_summary'])
    <h2>SLA Summary</h2>
    <div class="stats">
        @foreach ($data['sla_summary'] as $label => $value)
        <div class="stat-box">
            <strong>{{ $value }}</strong>
            {{ ucwords(str_replace('_', ' ', $label)) }}
        </div>
        @endforeach
    </div>
    @endisset

    @isset($data['staging_pending'])
    <p><strong>Staging Pending:</strong> {{ $data['staging_pending'] }}</p>
    @endisset

    @isset($data['unassigned_count'])
    <p><strong>Unassigned:</strong> {{ $data['unassigned_count'] }}</p>
    @endisset

    @isset($data['very_high_count'])
    <p><strong>Very High Priority:</strong> {{ $data['very_high_count'] }}</p>
    @endisset

    @isset($data['as_pic_count'])
    <p><strong>As PIC:</strong> {{ $data['as_pic_count'] }}</p>
    @endisset

    @isset($data['active_count'])
    <p><strong>Active:</strong> {{ $data['active_count'] }}</p>
    @endisset

    @isset($data['timesheet_pending'])
    <p><strong>Timesheet Pending:</strong> {{ $data['timesheet_pending'] }}</p>
    @endisset

    @isset($data['priority_breakdown'])
    <h2>Priority Breakdown</h2>
    <table>
        <tr>
            @foreach ($data['priority_breakdown'] as $label => $value)
            <th>{{ $label }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($data['priority_breakdown'] as $value)
            <td>{{ $value }}</td>
            @endforeach
        </tr>
    </table>
    @endisset

    @isset($data['team_load'])
    <h2>Team Load</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Open Count</th>
        </tr>
        @foreach ($data['team_load'] as $member)
        <tr>
            <td>{{ $member['name'] ?? '-' }}</td>
            <td>{{ $member['open_count'] ?? '-' }}</td>
        </tr>
        @endforeach
    </table>
    @endisset

    @isset($data['recent_tickets'])
    <h2>Recent Tickets</h2>
    <table>
        <tr>
            <th>Number</th>
            <th>Description</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Customer</th>
            <th>PIC</th>
        </tr>
        @foreach ($data['recent_tickets'] as $ticket)
        <tr>
            <td>{{ $ticket['ticket_number'] ?? '-' }}</td>
            <td>{{ $ticket['description'] ?? '-' }}</td>
            <td>{{ $ticket['status'] ?? '-' }}</td>
            <td>{{ $ticket['ticket_priority'] ?? '-' }}</td>
            <td>{{ $ticket['customer_name'] ?? '-' }}</td>
            <td>{{ $ticket['pic_name'] ?? '-' }}</td>
        </tr>
        @endforeach
    </table>
    @endisset

    @isset($data['urgent_tickets'])
    <h2>Urgent Tickets</h2>
    <pre>{{ json_encode($data['urgent_tickets'], JSON_PRETTY_PRINT) }}</pre>
    @endisset
</body>

</html>