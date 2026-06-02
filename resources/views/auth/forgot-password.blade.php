<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset access | Active8</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #0f172a; background: linear-gradient(135deg, #0f172a, #1d4ed8); }
        .card { width: min(100%, 460px); padding: 34px; border-radius: 24px; background: rgba(255,255,255,.96); box-shadow: 0 30px 90px rgba(0,0,0,.28); }
        .mark { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 16px; background: #eff6ff; color: #2563eb; font-weight: 900; }
        .eyebrow { margin-top: 24px; color: #2563eb; font-size: 12px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 8px 0; font-size: 32px; letter-spacing: 0; }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.65; }
        label { display: block; margin: 16px 0 7px; color: #334155; font-size: 13px; font-weight: 800; }
        input { width: 100%; height: 48px; padding: 0 14px; border: 1px solid #dbe3ef; border-radius: 14px; background: #f8fafc; font: inherit; outline: none; }
        input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 4px rgba(37,99,235,.12); }
        button { width: 100%; height: 50px; margin-top: 18px; border: 0; border-radius: 14px; background: #0f172a; color: white; font-weight: 900; cursor: pointer; }
        a { display: inline-block; margin-top: 18px; color: #2563eb; text-decoration: none; font-weight: 800; }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 14px; font-size: 13px; font-weight: 700; }
        .alert.error { background: #fef2f2; color: #b42318; }
        .alert.success { background: #ecfdf3; color: #067647; }
    </style>
</head>
<body>
    <main class="card">
        <div class="mark">A8</div>
        <div class="eyebrow">Password Recovery</div>
        <h1>Reset your access</h1>
        <p>Enter your dashboard email. We will send a secure reset link to restore access.</p>

        @if (session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label>Email address</label>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            <button type="submit">Send reset link</button>
        </form>

        <a href="{{ route('login') }}">Back to sign in</a>
    </main>
</body>
</html>
