<?php

namespace App\Services;

use App\Models\QrCode;
use App\Models\ScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScanLogger
{
    public function __construct(private readonly ScanGeoResolver $geoResolver)
    {
    }

    public function log(Request $request, string $token, ?QrCode $qrCode, string $status): ScanLog
    {
        $scanCount = $this->scanCount($token, $qrCode) + 1;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $geo = $this->geoResolver->resolve($request);
        $country = $geo['country'];
        $city = $geo['city'];
        $previousScan = $this->queryForToken($token, $qrCode)->latest('scanned_at')->first();
        $sameIpScanCount = $ipAddress
            ? (clone $this->queryForToken($token, $qrCode))->where('ip_address', $ipAddress)->count()
            : 0;
        $fingerprint = $this->fingerprint($request, $ipAddress, $userAgent);
        $sameFingerprintScanCount = (clone $this->queryForToken($token, $qrCode))
            ->where('metadata->fingerprint', $fingerprint)
            ->count();
        $repeatScan = $scanCount > 1;
        $rapidRepeat = $previousScan?->scanned_at?->greaterThanOrEqualTo(now()->subMinutes(10)) ?? false;
        $locationChanged = $previousScan
            && ($previousScan->country || $previousScan->city)
            && ($country || $city)
            && (
                ($previousScan->country && $country && $previousScan->country !== $country)
                || ($previousScan->city && $city && $previousScan->city !== $city)
            );
        $riskFlags = collect([
            'repeat_scan' => $repeatScan,
            'rapid_repeat_scan' => $rapidRepeat,
            'same_ip_repeat' => $sameIpScanCount > 0,
            'same_device_repeat' => $sameFingerprintScanCount > 0,
            'location_anomaly' => $locationChanged,
        ])->filter()->keys()->values()->all();

        return ScanLog::query()->create([
            'qr_code_id' => $qrCode?->id,
            'token_scanned' => $token,
            'status_result' => $status,
            'scanned_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'country' => $country,
            'city' => $city,
            'device_type' => $this->detectDevice($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'metadata' => [
                'scan_count' => $scanCount,
                'previous_scan_at' => $previousScan?->scanned_at?->toIso8601String(),
                'same_ip_scan_count' => $sameIpScanCount,
                'same_fingerprint_scan_count' => $sameFingerprintScanCount,
                'fingerprint' => $fingerprint,
                'geo_source' => $geo['source'],
                'risk_flags' => $riskFlags,
                'referer' => $request->headers->get('referer'),
                'accept_language' => $request->headers->get('accept-language'),
                'request_path' => $request->path(),
                'is_repeat_scan' => $repeatScan,
                'is_rapid_repeat_scan' => $rapidRepeat,
            ],
        ]);
    }

    public function scanCount(string $token, ?QrCode $qrCode): int
    {
        if ($qrCode) {
            return ScanLog::query()->where('qr_code_id', $qrCode->id)->count();
        }

        return ScanLog::query()->where('token_scanned', $token)->count();
    }

    private function queryForToken(string $token, ?QrCode $qrCode)
    {
        if ($qrCode) {
            return ScanLog::query()->where('qr_code_id', $qrCode->id);
        }

        return ScanLog::query()->where('token_scanned', $token);
    }

    private function fingerprint(Request $request, ?string $ipAddress, ?string $userAgent): string
    {
        return hash('sha256', implode('|', [
            $ipAddress,
            Str::limit((string) $userAgent, 500, ''),
            $request->headers->get('accept-language'),
        ]));
    }

    private function detectDevice(?string $userAgent): string
    {
        $userAgent = strtolower($userAgent ?? '');

        if (str_contains($userAgent, 'ipad') || str_contains($userAgent, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'iphone') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(?string $userAgent): string
    {
        $userAgent = strtolower($userAgent ?? '');

        return match (true) {
            str_contains($userAgent, 'edg/') => 'Edge',
            str_contains($userAgent, 'chrome/') => 'Chrome',
            str_contains($userAgent, 'safari/') => 'Safari',
            str_contains($userAgent, 'firefox/') => 'Firefox',
            default => 'Unknown',
        };
    }
}
