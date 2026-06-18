<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Services\ScanLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyController extends Controller
{
    public function show(Request $request, string $token, ScanLogger $scanLogger): View
    {
        $qrCode = QrCode::query()
            ->with(['batch.product.company.profile', 'batch.product.suitabilityItems'])
            ->where('token', $token)
            ->orWhere('custom_path', $token)
            ->first();

        $status = $this->resolveStatus($qrCode);
        $trackedToken = $qrCode?->token ?? $token;
        $existingScans = $scanLogger->scanCount($trackedToken, $qrCode);
        $shouldLogScan = $request->isMethod('GET');
        $scanCount = $existingScans + ($shouldLogScan ? 1 : 0);

        if ($shouldLogScan) {
            $scanLogger->log($request, $trackedToken, $qrCode, $status);
        }

        return view('verify.show', [
            'token' => $trackedToken,
            'qrCode' => $qrCode,
            'batch' => $qrCode?->batch,
            'product' => $qrCode?->batch?->product,
            'company' => $qrCode?->batch?->product?->company,
            'profile' => $qrCode?->batch?->product?->company?->profile,
            'status' => $status,
            'scanCount' => $scanCount,
        ]);
    }

    private function resolveStatus(?QrCode $qrCode): string
    {
        if (! $qrCode) {
            return 'invalid';
        }

        if ($qrCode->isRevoked()) {
            return 'revoked';
        }

        if ($qrCode->isExpired()) {
            return 'expired';
        }

        $batch = $qrCode->batch;
        $product = $batch?->product;

        if ($batch?->status === 'recalled' || $product?->status === 'recalled') {
            return 'recalled';
        }

        if ($batch?->expiry_date && $batch->expiry_date->isPast()) {
            return 'expired';
        }

        if ($qrCode->status !== 'active' || $batch?->qa_status !== 'approved') {
            return 'suspicious';
        }

        return 'genuine';
    }
}
