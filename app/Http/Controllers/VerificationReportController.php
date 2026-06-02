<?php

namespace App\Http\Controllers;

use App\Models\ScanLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationReportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status', 'all');
        $company = $request->query('company', 'all');
        $batch = $request->query('batch', 'all');

        $query = ScanLog::query()
            ->with('qrCode.batch.product.company')
            ->latest('scanned_at');

        if ($status !== 'all') {
            $query->where('status_result', $status);
        }

        if ($company !== 'all' && $company !== null) {
            $query->whereHas('qrCode.batch.product', fn ($builder) => $builder->where('company_id', $company));
        }

        if ($batch !== 'all' && $batch !== null) {
            $query->whereHas('qrCode.batch', fn ($builder) => $builder->whereKey($batch));
        }

        $filename = 'active8-verification-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Scanned At',
                'Status',
                'Company',
                'Product',
                'Batch',
                'Token',
                'Device',
                'Browser',
                'IP Address',
                'Location',
                'Risk Flags',
                'Fingerprint',
            ]);

            $query->chunk(250, function ($scans) use ($handle): void {
                foreach ($scans as $scan) {
                    fputcsv($handle, [
                        $scan->scanned_at?->toDateTimeString(),
                        $scan->status_result,
                        $scan->qrCode?->batch?->product?->company?->name,
                        $scan->qrCode?->batch?->product?->name,
                        $scan->qrCode?->batch?->batch_number,
                        $scan->token_scanned,
                        $scan->device_type,
                        $scan->browser,
                        $scan->ip_address,
                        collect([$scan->city, $scan->country])->filter()->implode(', '),
                        implode('|', $scan->metadata['risk_flags'] ?? []),
                        $scan->metadata['fingerprint'] ?? null,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
