<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOBE Republic Admin Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #111827; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login { background: #fff; border-radius: 12px; padding: 40px; width: 100%; max-width: 400px; }
        .login h1 { font-size: 22px; margin-bottom: 6px; }
        .login h1 span { color: #f59e0b; }
        .login p { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 11px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 6px; background: #f59e0b; color: #fff; font-size: 15px; cursor: pointer; }
        .error { background: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 6px; font-size: 14px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="login">
        <h1>GOBE <span>Republic</span></h1>
        <p>Sign in to the administration dashboard</p>
        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
    </div>
</body>
</html>
