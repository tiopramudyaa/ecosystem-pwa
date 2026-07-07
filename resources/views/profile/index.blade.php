<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - EcoSystem Lite</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 40px auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 14px; }
        th { background: #f5f5f5; width: 220px; }
        form.password input { display: block; margin-bottom: 8px; width: 100%; padding: 6px; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>Profile</h1>
        <div style="display:flex; gap:12px; align-items:center;">
            @include('partials.notifications-bell')
            <a href="{{ route('dashboard') }}">&larr; Dashboard</a>
        </div>
    </div>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <table>
        <tr><th>ECI</th><td>{{ $profile['eci'] ?? '-' }}</td></tr>
        <tr><th>Name</th><td>{{ $profile['title'] ?? '' }} {{ $profile['first_name'] ?? '' }} {{ $profile['last_name'] ?? '' }}</td></tr>
        <tr><th>Nick Name</th><td>{{ $profile['nick_name'] ?? '-' }}</td></tr>
        <tr><th>Position</th><td>{{ $profile['position'] ?? '-' }}</td></tr>
        <tr><th>Division</th><td>{{ $profile['division'] ?? '-' }}</td></tr>
        <tr><th>Department</th><td>{{ $profile['department'] ?? '-' }}</td></tr>
        <tr><th>Employee Type</th><td>{{ $profile['employee_type'] ?? '-' }}</td></tr>
        <tr><th>Email (Work)</th><td>{{ $profile['email_work'] ?? '-' }}</td></tr>
        <tr><th>Email (Personal)</th><td>{{ $profile['email_personal'] ?? '-' }}</td></tr>
        <tr><th>Cell Phone</th><td>{{ $profile['cell_phone'] ?? '-' }}</td></tr>
        <tr><th>City</th><td>{{ $profile['city'] ?? '-' }}</td></tr>
        <tr><th>Roles</th>
            <td>
                @forelse ($profile['roles'] ?? [] as $role)
                    {{ $role['name'] ?? '-' }}@if (!$loop->last), @endif
                @empty
                    -
                @endforelse
            </td>
        </tr>
    </table>

    <h2>Ubah Password</h2>
    <form class="password" method="POST" action="{{ route('profile.change-password') }}">
        @csrf
        @method('PATCH')
        <input type="password" name="password" placeholder="Password baru (min 8 karakter)" required>
        <input type="password" name="password_confirmation" placeholder="Konfirmasi password baru" required>
        <button type="submit">Ubah Password</button>
    </form>
</body>
</html>
