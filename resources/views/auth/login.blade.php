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
        html { min-height: 100%; background: #020617; }
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 18% 18%, rgba(37, 99, 235, .38), transparent 28rem),
                radial-gradient(circle at 82% 72%, rgba(20, 184, 166, .18), transparent 24rem),
                linear-gradient(135deg, #020617 0%, #0f172a 48%, #111827 100%);
        }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(420px, 500px);
            padding: 22px;
        }
        .brand-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: calc(100vh - 44px);
            padding: 38px;
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 30px;
            background:
                linear-gradient(145deg, rgba(15, 23, 42, .86), rgba(15, 23, 42, .54)),
                radial-gradient(circle at 80% 20%, rgba(37, 99, 235, .35), transparent 20rem);
            color: white;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        }
        .brand-panel::before {
            content: "";
            position: absolute;
            inset: auto -12% 10% auto;
            width: 420px;
            height: 420px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 999px;
            background: radial-gradient(circle, rgba(37,99,235,.22), transparent 58%);
        }
        .brand-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.055) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: linear-gradient(120deg, rgba(0,0,0,.78), transparent 72%);
        }
        .brand-top, .hero-copy, .brand-bottom { position: relative; z-index: 1; }
        .brand-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand-mark { display: inline-flex; align-items: center; gap: 12px; font-size: 18px; font-weight: 900; letter-spacing: -.03em; }
        .mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 15px;
            background: rgba(255,255,255,.1);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
        }
        .security-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.82);
            font-size: 12px;
            font-weight: 800;
        }
        .security-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 5px rgba(34,197,94,.13); }
        h1 {
            max-width: 720px;
            margin: 0 0 18px;
            font-size: clamp(42px, 7vw, 86px);
            line-height: .92;
            letter-spacing: -.08em;
        }
        .brand-copy {
            max-width: 600px;
            margin: 0;
            color: rgba(255,255,255,.72);
            font-size: 18px;
            line-height: 1.75;
        }
        .signal-card {
            width: min(100%, 560px);
            margin-top: 32px;
            padding: 16px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 22px;
            background: rgba(15,23,42,.62);
            backdrop-filter: blur(18px);
        }
        .signal-head { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 14px; color: rgba(255,255,255,.68); font-size: 12px; font-weight: 800; }
        .signal-line { height: 96px; display: flex; align-items: end; gap: 8px; padding: 10px 0; }
        .bar { flex: 1; min-width: 10px; border-radius: 999px 999px 4px 4px; background: linear-gradient(180deg, #60a5fa, #2563eb); opacity: .86; }
        .bar:nth-child(2) { height: 46%; }
        .bar:nth-child(3) { height: 72%; }
        .bar:nth-child(4) { height: 58%; }
        .bar:nth-child(5) { height: 86%; }
        .bar:nth-child(6) { height: 64%; }
        .bar:nth-child(7) { height: 92%; }
        .bar:nth-child(8) { height: 76%; }
        .bar:nth-child(9) { height: 88%; }
        .bar:nth-child(10) { height: 62%; }
        .brand-bottom { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .metric {
            padding: 15px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 18px;
            background: rgba(255,255,255,.08);
        }
        .metric strong { display: block; font-size: 22px; letter-spacing: -.04em; }
        .metric span { display: block; margin-top: 5px; color: rgba(255,255,255,.66); font-size: 12px; line-height: 1.45; }
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 14px 28px 34px;
        }
        .card {
            width: 100%;
            max-width: 430px;
            padding: 30px;
            border: 1px solid rgba(255,255,255,.72);
            border-radius: 28px;
            background: rgba(255,255,255,.94);
            box-shadow: var(--shadow);
            backdrop-filter: blur(26px);
        }
        .card-top { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 24px; }
        .mini-brand { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 900; }
        .mini-mark { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 11px; background: #0f172a; color: white; font-size: 12px; font-weight: 900; }
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
            .page { grid-template-columns: 1fr; padding: 14px; }
            .brand-panel { min-height: auto; padding: 26px; }
            .hero-copy { margin: 70px 0; }
            .brand-bottom { grid-template-columns: 1fr; }
            .form-panel { padding: 16px 0 0; }
            .card { max-width: none; }
        }
        @media (max-width: 560px) {
            .brand-top, .card-top, .row { align-items: flex-start; flex-direction: column; }
            .security-pill { display: none; }
            h1 { font-size: 42px; }
            .brand-copy { font-size: 15px; }
            .card { padding: 22px; border-radius: 22px; }
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
        <section class="brand-panel" aria-label="Active8 platform summary">
            <div class="brand-top">
                <div class="brand-mark"><span class="mark">A8</span> Active8</div>
                <div class="security-pill"><span class="security-dot"></span> Secure admin portal</div>
            </div>

            <div class="hero-copy">
                <h1>Product trust starts here.</h1>
                <p class="brand-copy">Control company profiles, batch QR codes, scan intelligence, audit trails, and anti-counterfeit verification from one polished command center.</p>

                <div class="signal-card">
                    <div class="signal-head">
                        <span>Verification signal</span>
                        <span>Live operations</span>
                    </div>
                    <div class="signal-line" aria-hidden="true">
                        <span class="bar" style="height:38%"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </div>
                </div>
            </div>

            <div class="brand-bottom">
                <div class="metric"><strong>QR</strong><span>Lifecycle controls for batches and labels.</span></div>
                <div class="metric"><strong>Risk</strong><span>Repeat scan and anomaly intelligence.</span></div>
                <div class="metric"><strong>Audit</strong><span>Traceable admin activity and reports.</span></div>
            </div>
        </section>

        <section class="form-panel" aria-label="Sign in form">
            <div class="card">
                <div class="card-top">
                    <div class="mini-brand"><span class="mini-mark">A8</span> Active8</div>
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
