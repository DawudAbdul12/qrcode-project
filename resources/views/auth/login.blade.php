<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | Active8</title>
    <style>
        :root {
            --ink: #0b1220;
            --muted: #64748b;
            --line: rgba(148, 163, 184, .24);
            --blue: #2563eb;
            --blue-deep: #1d4ed8;
            --soft: #f8fafc;
            --white: #ffffff;
            --green: #067647;
            --shadow: 0 30px 90px rgba(15, 23, 42, .22);
        }
        * { box-sizing: border-box; }
        html { min-height: 100%; background: #f8fafc; }
        body {
            margin: 0;
            overflow: hidden;
            min-height: 100vh;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f8fafc;
        }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .page {
            height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(430px, 560px);
            align-items: stretch;
            padding: 0;
        }
        .visual-panel {
            position: relative;
            height: 100vh;
            overflow: hidden;
            background: #e8eef3;
        }
        .visual-panel img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
        }
        .visual-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(15, 23, 42, .06), rgba(15, 23, 42, .18));
        }
        .brand-logo-img { display: block; width: 96px; height: auto; }
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100vh;
            padding: 48px 56px;
            overflow: auto;
            background: var(--white);
            box-shadow: -22px 0 60px rgba(15, 23, 42, .10);
        }
        .card {
            width: 100%;
            max-width: 420px;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            backdrop-filter: none;
        }
        .card-top { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 24px; }
        .mini-brand { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 900; }
        .mini-brand .brand-logo-img { width: 96px; }
        .secure-badge { padding: 7px 9px; border: 1px solid #bbf7d0; border-radius: 999px; background: #f0fdf4; color: var(--green); font-size: 11px; font-weight: 900; }
        .eyebrow { color: var(--blue); font-size: 12px; font-weight: 900; letter-spacing: .13em; text-transform: uppercase; }
        h2 { margin: 8px 0 8px; font-size: 32px; line-height: 1.05; letter-spacing: -.045em; }
        .sub { margin: 0 0 24px; color: var(--muted); font-size: 14px; line-height: 1.65; }
        .alert {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 750;
            line-height: 1.45;
        }
        .alert.error { border: 1px solid #fecaca; background: #fef2f2; color: #b42318; }
        .alert.success { border: 1px solid #bbf7d0; background: #ecfdf3; color: var(--green); }
        label { display: block; margin: 15px 0 7px; color: #334155; font-size: 12px; font-weight: 900; }
        .field {
            position: relative;
        }
        .field input {
            width: 100%;
            height: 50px;
            padding: 0 14px 0 43px;
            border: 1px solid #dbe3ef;
            border-radius: 15px;
            background: var(--soft);
            color: var(--ink);
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .field input:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 4px rgba(37,99,235,.12);
        }
        .field svg {
            position: absolute;
            left: 15px;
            top: 50%;
            width: 16px;
            height: 16px;
            color: #94a3b8;
            transform: translateY(-50%);
        }
        .row { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin: 16px 0 22px; }
        .check { display: inline-flex; align-items: center; gap: 9px; margin: 0; color: #475569; font-size: 13px; font-weight: 800; cursor: pointer; }
        .check input { width: 16px; height: 16px; accent-color: var(--blue); }
        .link { color: var(--blue); font-size: 13px; font-weight: 900; }
        .submit {
            width: 100%;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--blue), var(--blue-deep));
            color: white;
            box-shadow: 0 16px 34px rgba(37, 99, 235, .32);
            font-weight: 950;
            cursor: pointer;
        }
        .submit:hover { filter: brightness(.98); transform: translateY(-1px); }
        .access-note {
            margin-top: 18px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 17px;
            background: linear-gradient(180deg, #f8fafc, #fff);
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }
        .access-note strong { display: block; margin-bottom: 4px; color: #0f172a; }
        @media (max-width: 960px) {
            body { overflow: auto; }
            .form-panel { min-height: 100vh; height: auto; padding: 32px 22px; overflow: visible; box-shadow: none; }
            .page { grid-template-columns: 1fr; padding: 0; }
            .visual-panel { display: none; }
            .card { max-width: none; }
        }
        @media (max-width: 560px) {
            .card-top, .row { align-items: flex-start; flex-direction: column; }
            .form-panel { align-items: flex-start; padding: 24px 18px; }
        }
    </style>
</head>
<body>
    <svg width="0" height="0" style="position:absolute">
        <symbol id="i-mail" viewBox="0 0 24 24"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z M22 6l-10 7L2 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="i-lock" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    </svg>

    <main class="page">
        <section class="visual-panel" aria-label="QR verification preview">
            <img src="{{ asset('images/login-verification-hero.png') }}" alt="Product package QR verification scan">
        </section>

        <section class="form-panel" aria-label="Sign in form">
            <div class="card">
                <div class="card-top">
                    <div class="mini-brand"><img class="brand-logo-img" src="{{ asset('images/active8-logo.png') }}" alt="Active8"></div>
                    <span class="secure-badge">Protected</span>
                </div>

                <div class="eyebrow">Dashboard Access</div>
                <h2>Welcome back</h2>
                <p class="sub">Sign in to manage verification profiles, products, batches, scan logs, and reports.</p>

                @if (session('status'))
                    <div class="alert success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <label for="email">Email address</label>
                    <div class="field">
                        <svg><use href="#i-mail"></use></svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="admin@company.com" autocomplete="email" required autofocus>
                    </div>

                    <label for="password">Password</label>
                    <div class="field">
                        <svg><use href="#i-lock"></use></svg>
                        <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    </div>

                    <div class="row">
                        <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                        <a class="link" href="{{ route('password.request') }}">Forgot password?</a>
                    </div>

                    <button class="submit" type="submit">
                        Sign in
                        <svg width="16" height="16"><use href="#i-arrow"></use></svg>
                    </button>
                </form>

                <div class="access-note">
                    <strong>Authorized access only</strong>
                    This portal controls product verification, QR lifecycle, and scan intelligence. Use an approved admin account.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
