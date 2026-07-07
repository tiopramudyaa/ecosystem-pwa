<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - EcoSystem Lite</title>
</head>
<body style="font-family: sans-serif; max-width: 360px; margin: 60px auto;">
    <h1>Login</h1>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div>
            <label>Email / ECI / Phone</label><br>
            <input type="text" name="email" value="{{ old('email') }}" required>
        </div>
        <div style="margin-top: 10px;">
            <label>Password</label><br>
            <input type="password" name="password" required>
        </div>
        <button type="submit" style="margin-top: 15px;">Login</button>
    </form>
</body>
</html>
