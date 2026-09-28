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
        .mark { width: 116px; display: block; }
        .mark img { display: block; width: 100%; height: auto; }
        .eyebrow { margin-top: 24px; color: #2563eb; font-size: 12px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 8px 0; font-size: 32px; letter-spacing: 0; }
        p { margin: 0 0 22px; color: #64748b; line-height: 1.65; }
        label { display: block; margin: 16px 0 7px; color: #334155; font-size: 13px; font-weight: 800; }
        input { width: 100%; height: 48px; padding: 0 14px; border: 1px solid #dbe3ef; border-radius: 14px; background: #f8fafc; font: inherit; outline: none; }
        .otp-input { text-align: center; font-size: 24px; font-weight: 900; letter-spacing: .42em; }
        input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 4px rgba(37,99,235,.12); }
        button { width: 100%; height: 50px; margin-top: 18px; border: 0; border-radius: 14px; background: #0f172a; color: white; font-weight: 900; cursor: pointer; }
        a { color: #2563eb; text-decoration: none; font-weight: 800; }
        .reset-actions { display: grid; gap: 10px; margin-top: 14px; }
        .resend-form { display: flex; justify-content: center; }
        .hint { width: auto; height: auto; margin: 0; padding: 0; background: transparent; color: #2563eb; font-size: 13px; font-weight: 800; }
        .hint:disabled { color: #94a3b8; cursor: not-allowed; }
        .back-link { width: 100%; min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid #dbe3ef; border-radius: 14px; background: #fff; color: #334155; font-size: 14px; font-weight: 900; }
        .back-link:hover { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
        .back-link svg { width: 16px; height: 16px; }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 14px; font-size: 13px; font-weight: 700; }
        .alert.error { background: #fef2f2; color: #b42318; }
        .alert.success { background: #ecfdf3; color: #067647; }
        .debug-code { margin-bottom: 16px; padding: 14px; border: 1px dashed #93c5fd; border-radius: 14px; background: #eff6ff; color: #1e3a8a; font-size: 13px; font-weight: 800; }
        .debug-code strong { display: block; margin-top: 4px; color: #0f172a; font-size: 26px; letter-spacing: .28em; }
    </style>
</head>
<body>
    <main class="card">
        <div class="mark"><img src="{{ asset('images/active8-logo.png') }}" alt="Active8"></div>
        <div class="eyebrow">Secure Reset</div>
        <h1>{{ $otpVerified ? 'Create a new password' : 'Verify your code' }}</h1>
        <p>{{ $otpVerified ? 'Choose a strong password for your Active8 dashboard account.' : 'Enter the 6-digit code sent to your email.' }}</p>

        @if (session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        @if (session('debug_reset_otp'))
            <div class="debug-code">Development reset code<strong>{{ session('debug_reset_otp') }}</strong></div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        @if ($otpVerified)
            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="email" value="{{ old('email', $email) }}">

                <label>New password</label>
                <input type="password" name="password" autocomplete="new-password" required autofocus>

                <label>Confirm password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password" required>

                <button type="submit">Update password</button>
            </form>
        @else
            <form method="POST" action="{{ route('password.otp.verify') }}">
                @csrf

                <input type="hidden" name="email" value="{{ old('email', $email) }}">

                <label>Reset code</label>
                <input class="otp-input" type="text" name="otp" value="{{ old('otp') }}" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>

                <button type="submit">Verify code</button>
            </form>
        @endif

        <div class="reset-actions">
            <form class="resend-form" method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', $email) }}">
                <button class="hint" type="submit" data-resend-link data-remaining="{{ $resendAvailableIn }}" data-ready-text="Request another code" @disabled($resendAvailableIn > 0)>{{ $resendAvailableIn > 0 ? 'Request another code in '.$resendAvailableIn.'s' : 'Request another code' }}</button>
            </form>
            <a class="back-link" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Back to sign in
            </a>
        </div>
    </main>
    <script>
        const resendLink = document.querySelector('[data-resend-link]');
        if (resendLink) {
            let remaining = Number.parseInt(resendLink.dataset.remaining || '0', 10);
            const readyText = resendLink.dataset.readyText || 'Request another code';
            if (remaining <= 0) {
                resendLink.disabled = false;
                resendLink.textContent = readyText;
            }
            const tick = () => {
                remaining -= 1;
                if (remaining <= 0) {
                    resendLink.disabled = false;
                    resendLink.textContent = readyText;
                    clearInterval(timer);
                    return;
                }

                resendLink.textContent = `Request another code in ${remaining}s`;
            };
            const timer = remaining > 0 ? setInterval(tick, 1000) : null;
        }
    </script>
</body>
</html>
