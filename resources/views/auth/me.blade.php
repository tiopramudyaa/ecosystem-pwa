<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Me - EcoSystem Lite</title>
</head>
<body style="font-family: sans-serif; max-width: 480px; margin: 60px auto;">
    <h1>Welcome, {{ $user['name'] }}</h1>
    <pre>{{ json_encode($user, JSON_PRETTY_PRINT) }}</pre>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
