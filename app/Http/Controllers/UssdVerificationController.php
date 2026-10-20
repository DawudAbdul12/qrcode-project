<?php

namespace App\Http\Controllers;

use App\Models\LabelCode;
use App\Models\ScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UssdVerificationController extends Controller
{
    private const SUSPICIOUS_AFTER_ATTEMPTS = 3;

    public function callback(Request $request): JsonResponse
    {
        $userId = $this->stringInput($request, 'USERID');
        $msisdn = $this->stringInput($request, 'MSISDN');
        $configuredUserId = (string) config('services.nalo.ussd_user_id');

        if ($configuredUserId !== '' && ! hash_equals($configuredUserId, $userId)) {
            return $this->naloResponse($userId, $msisdn, 'Unable to process this request.', false, 403);
        }

        $userData = trim($this->stringInput($request, 'USERDATA'));

        if ($this->isInitialRequest($request)) {
            return $this->naloResponse($userId, $msisdn, 'Enter the code printed on the product label:', true);
        }

        try {
            $code = $this->validatedCode($request);
        } catch (ValidationException) {
            $message = 'INVALID CODE. Enter the code printed on the label.';
            $this->logAttempt($request, null, $userData, 'invalid', $message);

            return $this->naloResponse($userId, $msisdn, $message, false);
        }

        $result = DB::transaction(fn () => $this->verifyCode($request, $code));

        return $this->naloResponse($userId, $msisdn, $result['message'], false);
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
            'USERID' => config('services.nalo.ussd_user_id') ?: 'LOCAL-SIMULATOR',
            'MSISDN' => $data['phone_number'] ?? '0240000000',
            'USERDATA' => $data['dial_string'],
            'MSGTYPE' => true,
            'NETWORK' => 'LOCAL',
            'SESSIONID' => 'local-'.Str::uuid(),
        ]);
        $callbackRequest->headers->set('user-agent', 'Local USSD Simulator');

        $response = $this->callback($callbackRequest);
        $responsePayload = $response->getData(true);

        return back()
            ->withInput()
            ->with('ussd_response', $responsePayload['MSG'] ?? 'No response received.');
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
            $message = 'INVALID CODE. Product may not be genuine.';
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
            'genuine' => 'GENUINE: '.$product.' by '.$company.'. Keep this SMS.',
            'already_used' => 'USED CODE. This label was already checked. Call the seller if unsure.',
            'suspicious' => 'SUSPICIOUS CODE. Too many checks. Contact the manufacturer.',
            'revoked' => 'REVOKED CODE. Do not use this product. Contact the manufacturer.',
            'expired' => 'EXPIRED PRODUCT. Do not use. Contact the seller.',
            'recalled' => 'RECALLED PRODUCT. Stop use and contact the manufacturer.',
            default => 'INVALID CODE. Product may not be genuine.',
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
                'provider' => 'nalo',
                'session_id' => $this->stringInput($request, 'SESSIONID'),
                'user_id' => $this->stringInput($request, 'USERID'),
                'phone_number' => $this->stringInput($request, 'MSISDN'),
                'network' => $this->stringInput($request, 'NETWORK'),
                'request_msg_type' => $request->input('MSGTYPE'),
                'raw_text' => $this->stringInput($request, 'USERDATA'),
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
        $raw = $this->stringInput($request, 'USERDATA');

        if (mb_strlen($raw) > 80) {
            throw ValidationException::withMessages(['USERDATA' => 'Enter a valid label code.']);
        }

        $segments = collect(explode('*', trim($raw)))
            ->map(fn (string $segment) => trim($segment, " \t\n\r\0\x0B#"))
            ->filter()
            ->values();
        $submittedCode = (string) ($segments->last() ?? '');
        $code = Str::upper((string) preg_replace('/[\s\p{Pd}]+/u', '', $submittedCode));

        if (! preg_match('/^[A-Z0-9]{6,20}$/', $code)) {
            throw ValidationException::withMessages(['code' => 'Enter a valid label code.']);
        }

        return $code;
    }

    private function stringInput(Request $request, string $key): string
    {
        $value = $request->input($key, '');

        return is_scalar($value) ? (string) $value : '';
    }

    private function isInitialRequest(Request $request): bool
    {
        $messageType = $request->input('MSGTYPE');

        if (is_bool($messageType)) {
            return $messageType;
        }

        return in_array(Str::lower(trim((string) $messageType)), ['1', 'true'], true);
    }

    private function naloResponse(string $userId, string $msisdn, string $message, bool $continueSession, int $status = 200): JsonResponse
    {
        return response()->json([
            'USERID' => $userId,
            'MSISDN' => $msisdn,
            'MSG' => $message,
            'MSGTYPE' => $continueSession,
        ], $status);
    }
}
