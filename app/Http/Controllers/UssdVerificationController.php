<?php

namespace App\Http\Controllers;

use App\Models\LabelCode;
use App\Models\ScanLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UssdVerificationController extends Controller
{
    private const SUSPICIOUS_AFTER_ATTEMPTS = 3;

    public function callback(Request $request): Response
    {
        try {
            $code = $this->validatedCode($request);
        } catch (ValidationException) {
            $message = 'END INVALID CODE. Enter the code printed on the label.';
            $this->logAttempt($request, null, $request->input('code') ?? $request->input('text'), 'invalid', $message);

            return $this->plain($message);
        }

        $result = DB::transaction(fn () => $this->verifyCode($request, $code));

        return $this->plain($result['message']);
    }

    public function simulator(): View
    {
        return view('ussd.test', [
            'dialString' => old('dial_string', ''),
            'responseMessage' => session('ussd_response'),
        ]);
    }

    public function simulate(Request $request)
    {
        $data = $request->validate([
            'dial_string' => ['required', 'string', 'max:80'],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        $callbackRequest = Request::create('/ussd/callback', 'POST', [
            'text' => $data['dial_string'],
            'phoneNumber' => $data['phone_number'] ?? '0240000000',
            'sessionId' => 'local-'.Str::uuid(),
            'serviceCode' => '*XXX#',
        ]);
        $callbackRequest->headers->set('user-agent', 'Local USSD Simulator');

        $response = $this->callback($callbackRequest);

        return back()
            ->withInput()
            ->with('ussd_response', $response->getContent());
    }

    /**
     * @return array{status: string, message: string}
     */
    private function verifyCode(Request $request, string $code): array
    {
        $labelCode = LabelCode::query()
            ->with(['batch.product.company'])
            ->where('code', $code)
            ->lockForUpdate()
            ->first();

        if (! $labelCode) {
            $message = 'END INVALID CODE. Product may not be genuine.';
            $this->logAttempt($request, null, $code, 'invalid', $message);

            return ['status' => 'invalid', 'message' => $message];
        }

        $attemptNumber = $labelCode->verification_count + 1;
        $status = $this->resolveStatus($labelCode, $attemptNumber);
        $message = $this->messageFor($status, $labelCode);
        $updates = [
            'verification_count' => $attemptNumber,
            'first_verified_at' => $labelCode->first_verified_at ?? now(),
            'last_verified_at' => now(),
        ];

        if ($status === 'genuine') {
            $updates['status'] = 'used';
        }

        if ($status === 'suspicious') {
            $updates['status'] = 'suspicious';
        }

        $labelCode->update($updates);
        $this->logAttempt($request, $labelCode, $code, $status, $message, $attemptNumber);

        return ['status' => $status, 'message' => $message];
    }

    private function resolveStatus(LabelCode $labelCode, int $attemptNumber): string
    {
        if ($labelCode->status === 'revoked') {
            return 'revoked';
        }

        if ($labelCode->status === 'expired' || $labelCode->batch?->expiry_date?->isPast()) {
            return 'expired';
        }

        if ($labelCode->status === 'recalled' || $labelCode->batch?->status === 'recalled' || $labelCode->batch?->product?->status === 'recalled') {
            return 'recalled';
        }

        if ($attemptNumber > self::SUSPICIOUS_AFTER_ATTEMPTS || $labelCode->status === 'suspicious') {
            return 'suspicious';
        }

        if ($labelCode->status === 'used') {
            return 'already_used';
        }

        if ($labelCode->status !== 'unused') {
            return 'suspicious';
        }

        return 'genuine';
    }

    private function messageFor(string $status, LabelCode $labelCode): string
    {
        $product = $labelCode->batch?->product?->name ?? 'product';
        $company = $labelCode->company?->name ?? $labelCode->batch?->product?->company?->name ?? 'manufacturer';

        return match ($status) {
            'genuine' => 'END GENUINE: '.$product.' by '.$company.'. Keep this SMS.',
            'already_used' => 'END USED CODE. This label was already checked. Call the seller if unsure.',
            'suspicious' => 'END SUSPICIOUS CODE. Too many checks. Contact the manufacturer.',
            'revoked' => 'END REVOKED CODE. Do not use this product. Contact the manufacturer.',
            'expired' => 'END EXPIRED PRODUCT. Do not use. Contact the seller.',
            'recalled' => 'END RECALLED PRODUCT. Stop use and contact the manufacturer.',
            default => 'END INVALID CODE. Product may not be genuine.',
        };
    }

    private function logAttempt(Request $request, ?LabelCode $labelCode, ?string $code, string $status, string $message, ?int $attemptNumber = null): void
    {
        ScanLog::query()->create([
            'label_code_id' => $labelCode?->id,
            'token_scanned' => $code ? Str::upper($code) : null,
            'status_result' => $status,
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => 'ussd',
            'browser' => 'USSD',
            'metadata' => [
                'channel' => 'ussd',
                'session_id' => $request->input('sessionId') ?? $request->input('session_id'),
                'service_code' => $request->input('serviceCode') ?? $request->input('service_code'),
                'phone_number' => $request->input('phoneNumber') ?? $request->input('phone_number') ?? $request->input('msisdn'),
                'raw_text' => $request->input('text'),
                'response_message' => $message,
                'attempt_number' => $attemptNumber,
            ],
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function validatedCode(Request $request): string
    {
        $payload = $request->validate([
            'text' => ['nullable', 'string', 'max:80'],
            'code' => ['nullable', 'string', 'max:40'],
            'phoneNumber' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'sessionId' => ['nullable', 'string', 'max:80'],
            'session_id' => ['nullable', 'string', 'max:80'],
            'serviceCode' => ['nullable', 'string', 'max:30'],
            'service_code' => ['nullable', 'string', 'max:30'],
        ]);

        $raw = $payload['code'] ?? $payload['text'] ?? '';
        $segments = collect(explode('*', trim($raw)))
            ->map(fn (string $segment) => trim($segment, " \t\n\r\0\x0B#"))
            ->filter()
            ->values();
        $code = Str::upper((string) ($segments->last() ?? ''));

        if (! preg_match('/^[A-Z0-9]{6,20}$/', $code)) {
            throw ValidationException::withMessages(['code' => 'Enter a valid label code.']);
        }

        return $code;
    }

    private function plain(string $message): Response
    {
        return response($message, 200)->header('Content-Type', 'text/plain');
    }
}
