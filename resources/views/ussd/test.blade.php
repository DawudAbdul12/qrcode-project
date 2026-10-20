<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USSD Test | Active8</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            color: #101828;
            background: #eef4f8;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main {
            width: min(100%, 560px);
            padding: 28px;
            border: 1px solid rgba(16, 24, 40, .12);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 18px 60px rgba(16, 24, 40, .12);
        }
        h1 { margin: 0 0 8px; font-size: 28px; letter-spacing: 0; }
        p { margin: 0 0 22px; color: #667085; line-height: 1.6; }
        label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 800; color: #344054; }
        input {
            width: 100%;
            min-height: 48px;
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font: inherit;
        }
        button {
            min-height: 46px;
            padding: 0 18px;
            border: 0;
            border-radius: 6px;
            background: #155eef;
            color: #fff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }
        .phone {
            margin-top: 24px;
            padding: 18px;
            border-radius: 8px;
            background: #101828;
            color: #d0d5dd;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            white-space: pre-wrap;
        }
        .error { margin: -4px 0 14px; color: #b42318; font-size: 13px; }
    </style>
</head>
<body>
    <main>
        <h1>USSD Verification Test</h1>
        <p>Simulate the NALO <code>USERDATA</code> value by entering a label code or a full dial string such as *XXX*ABC12345678#.</p>

        <form method="POST" action="{{ route('ussd.test.submit') }}">
            @csrf
            <label for="dial_string">Dial string or label code</label>
            <input id="dial_string" name="dial_string" value="{{ old('dial_string', $dialString) }}" placeholder="*XXX*ABC12345678#" autocomplete="off" required>
            @error('dial_string')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="phone_number">Phone number</label>
            <input id="phone_number" name="phone_number" value="{{ old('phone_number', '0240000000') }}" placeholder="0240000000">

            <button type="submit">Send USSD Request</button>
        </form>

        @if ($responseMessage)
            <div class="phone">{{ $responseMessage }}</div>
        @endif
    </main>
</body>
</html>
