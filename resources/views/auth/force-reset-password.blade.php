<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password | Active8</title>
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
        input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 4px rgba(37,99,235,.12); }
        .password-field { position: relative; }
        .password-field input { padding-right: 48px; }
        .toggle-password { position: absolute; right: 6px; top: 50%; width: 38px; height: 36px; display: inline-flex; align-items: center; justify-content: center; margin: 0; border-radius: 10px; background: transparent; color: #64748b; transform: translateY(-50%); }
        .toggle-password:hover { background: #e0ecff; color: #1d4ed8; }
        .toggle-password svg { width: 18px; height: 18px; }
        .toggle-password .eye-off { display: none; }
        .toggle-password.is-visible .eye { display: none; }
        .toggle-password.is-visible .eye-off { display: block; }
        .hint { margin: 8px 0 0; color: #64748b; font-size: 12px; line-height: 1.45; }
        button { width: 100%; height: 50px; margin-top: 18px; border: 0; border-radius: 14px; background: #0f172a; color: white; font-weight: 900; cursor: pointer; }
        .logout { width: 100%; height: 48px; margin-top: 12px; border: 1px solid #dbe3ef; border-radius: 14px; background: #fff; color: #334155; font-weight: 900; }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 14px; background: #fef2f2; color: #b42318; font-size: 13px; font-weight: 700; }
    </style>
</head>
<body>
    <main class="card">
        <div class="mark"><img src="{{ asset('images/active8-logo.png') }}" alt="Active8"></div>
        <div class="eyebrow">Password Required</div>
        <h1>Create your password</h1>
        <p>Your account was created with a temporary password. Set a new password before continuing.</p>

        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.force.update') }}">
            @csrf

            <label>New password</label>
            <div class="password-field">
                <input type="password" name="password" placeholder="Create a new password" autocomplete="new-password" required autofocus data-password-input>
                <button class="toggle-password" type="button" aria-label="Show password" aria-pressed="false" data-password-toggle>
                    <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.3 17.3 0 0 1-3.2 4.3"/><path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.6-1.2"/><path d="M9.9 9.9A3 3 0 0 0 14.1 14.1"/></svg>
                </button>
            </div>
            <p class="hint">Use at least 8 characters. A mix of letters, numbers, and symbols is recommended.</p>

            <label>Confirm password</label>
            <div class="password-field">
                <input type="password" name="password_confirmation" placeholder="Re-enter the new password" autocomplete="new-password" required data-password-input>
                <button class="toggle-password" type="button" aria-label="Show password" aria-pressed="false" data-password-toggle>
                    <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.3 17.3 0 0 1-3.2 4.3"/><path d="M6.6 6.6C3.6 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.6-1.2"/><path d="M9.9 9.9A3 3 0 0 0 14.1 14.1"/></svg>
                </button>
            </div>

            <button type="submit">Continue</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout" type="submit">Sign out</button>
        </form>
    </main>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            const field = button.closest('.password-field');
            const input = field?.querySelector('[data-password-input]');
            if (! input) return;

            button.addEventListener('click', () => {
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                button.classList.toggle('is-visible', ! visible);
                button.setAttribute('aria-pressed', String(! visible));
                button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            });
        });
    </script>
</body>
</html>
