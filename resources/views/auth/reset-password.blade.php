<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create new password | Active8</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #0f172a; background: radial-gradient(circle at 10% 10%, rgba(37,99,235,.45), transparent 28rem), linear-gradient(135deg, #020617, #1e293b); }
        .card { width: min(100%, 480px); padding: 34px; border: 1px solid rgba(255,255,255,.16); border-radius: 24px; background: rgba(255,255,255,.96); box-shadow: 0 30px 90px rgba(0,0,0,.34); }
        .mark { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 16px; background: #eff6ff; color: #2563eb; font-weight: 900; }
        .eyebrow { margin-top: 24px; color: #2563eb; font-size: 12px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 8px 0; font-size: 32px; letter-spacing: 0; }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.65; }
        label { display: block; margin: 16px 0 7px; color: #334155; font-size: 13px; font-weight: 800; }
        input { width: 100%; height: 48px; padding: 0 14px; border: 1px solid #dbe3ef; border-radius: 14px; background: #f8fafc; font: inherit; outline: none; }
        input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 4px rgba(37,99,235,.12); }
        button { width: 100%; height: 50px; margin-top: 18px; border: 0; border-radius: 14px; background: #0f172a; color: white; font-weight: 900; cursor: pointer; }
        a { display: inline-block; margin-top: 18px; color: #2563eb; text-decoration: none; font-weight: 800; }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 14px; background: #fef2f2; color: #b42318; font-size: 13px; font-weight: 700; }
    </style>
</head>
<body>
    <main class="card">
        <div class="mark">A8</div>
        <div class="eyebrow">Secure Reset</div>
        <h1>Create a new password</h1>
        <p>Choose a strong password for your Active8 dashboard account.</p>

        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label>Email address</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>

            <label>New password</label>
            <input type="password" name="password" autocomplete="new-password" required>

            <label>Confirm password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password" required>

            <button type="submit">Update password</button>
        </form>

        <a href="{{ route('login') }}">Back to sign in</a>
    </main>
</body>
</html>
