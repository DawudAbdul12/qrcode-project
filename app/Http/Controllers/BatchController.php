<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ActivityLog;
use App\Models\QrCode;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class BatchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $this->normalizeQrRequest($request);
            $qrSettings = $this->validatedQrSettings($request);
            $batch = Batch::query()->create($this->validatedData($request));

            $this->generateQrCodeFor($batch, $qrSettings);
            $this->logActivity('batch.created', $batch, 'Batch '.$batch->batch_number.' created and QR code generated.');
        });

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'Batch created successfully and QR code generated.');
    }

    public function update(Request $request, Batch $batch): RedirectResponse
    {
        DB::transaction(function () use ($request, $batch): void {
            $batch->loadMissing('qrCode');
            $this->normalizeQrRequest($request);
            $qrSettings = $this->validatedQrSettings($request, $batch->qrCode);

            $batch->update($this->validatedData($request, $batch));

            if ($batch->qrCode) {
                $this->updateQrCodeFromSettings($request, $batch, $qrSettings);
            } else {
                $this->generateQrCodeFor($batch, $qrSettings);
            }

            $this->logActivity('batch.updated', $batch, 'Batch '.$batch->batch_number.' updated.');
        });

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        $batch->delete();
        $this->logActivity('batch.archived', $batch, 'Batch '.$batch->batch_number.' archived.');

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'Batch archived successfully.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'print', 'distribute', 'recall', 'delete'])],
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['integer', 'exists:batches,id'],
        ]);

        $batches = Batch::query()->whereIn('id', $data['batch_ids']);

        match ($data['action']) {
            'approve' => $batches->update(['status' => 'approved', 'qa_status' => 'approved']),
            'print' => $batches->update(['status' => 'printed']),
            'distribute' => $batches->update(['status' => 'distributed']),
            'recall' => $batches->update(['status' => 'recalled']),
            'delete' => $batches->delete(),
        };

        if (Schema::hasTable('activity_logs')) {
            ActivityLog::query()->create([
                'user_id' => auth()->id(),
                'event' => 'batch.bulk_'.$data['action'],
                'subject_type' => Batch::class,
                'subject_id' => null,
                'description' => 'Bulk batch action applied: '.$data['action'].'.',
                'metadata' => ['batch_ids' => $data['batch_ids']],
            ]);
        }

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'Batch bulk action applied successfully.');
    }

    public function generateQrCode(Batch $batch): RedirectResponse
    {
        $batch->loadMissing('qrCode');

        if ($batch->qrCode) {
            return redirect()
                ->route('active8.page', 'batches')
                ->with('status', 'This batch already has a QR code.');
        }

        $this->generateQrCodeFor($batch);
        $this->logActivity('qr.generated', $batch, 'QR code generated for batch '.$batch->batch_number.'.');

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'QR code generated for batch '.$batch->batch_number.'.');
    }

    public function activateQrCode(Batch $batch): RedirectResponse
    {
        $batch->loadMissing('qrCode');

        abort_unless($batch->qrCode, 404);

        $payload = [
            'status' => 'active',
            'activated_at' => now(),
            'revoked_at' => null,
        ];

        if (Schema::hasColumn('qr_codes', 'revocation_reason')) {
            $payload['revocation_reason'] = null;
        }

        $batch->qrCode->update($payload);
        $this->logActivity('qr.activated', $batch, 'QR code activated for batch '.$batch->batch_number.'.');

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'QR code activated for batch '.$batch->batch_number.'.');
    }

    public function revokeQrCode(Request $request, Batch $batch): RedirectResponse
    {
        $batch->loadMissing('qrCode');

        abort_unless($batch->qrCode, 404);

        if ($request->filled('custom_path')) {
            $request->merge(['custom_path' => Str::slug((string) $request->input('custom_path'))]);
        }

        $data = $request->validate([
            'revocation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = [
            'status' => 'revoked',
            'revoked_at' => now(),
        ];

        if (Schema::hasColumn('qr_codes', 'revocation_reason')) {
            $payload['revocation_reason'] = $data['revocation_reason'] ?? 'Manual admin revocation';
        }

        $batch->qrCode->update($payload);
        $this->logActivity('qr.revoked', $batch, 'QR code revoked for batch '.$batch->batch_number.'.');

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'QR code revoked for batch '.$batch->batch_number.'.');
    }

    public function updateQrCodeControls(Request $request, Batch $batch): RedirectResponse
    {
        $batch->loadMissing('qrCode');

        abort_unless($batch->qrCode, 404);

        $this->normalizeQrRequest($request);
        $data = $this->validatedQrSettings($request, $batch->qrCode);
        $this->updateQrCodeFromSettings($request, $batch, $data);

        $this->logActivity('qr.controls_updated', $batch, 'QR lifecycle controls updated for batch '.$batch->batch_number.'.');

        return redirect()
            ->route('active8.page', 'batches')
            ->with('status', 'QR controls updated for batch '.$batch->batch_number.'.');
    }

    public function downloadQrCode(Batch $batch): Response
    {
        $batch->loadMissing('qrCode');

        abort_unless($batch->qrCode, 404);

        $filename = Str::slug($batch->batch_number ?: 'batch-qr').'-qr.svg';

        return response($batch->qrCode->svg(512), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Batch $batch = null): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'batch_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('batches')
                    ->where(fn (Builder $query) => $query->where('product_id', $request->input('product_id')))
                    ->ignore($batch?->id),
            ],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:manufacture_date'],
            'quantity' => ['required', 'integer', 'min:0'],
            'production_location' => ['nullable', 'string', 'max:255'],
            'qa_status' => ['required', Rule::in(['pending', 'approved', 'failed', 'review'])],
            'certificate_path' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'approved', 'printed', 'distributed', 'recalled'])],
        ]);
    }

    private function normalizeQrRequest(Request $request): void
    {
        if ($request->filled('custom_path')) {
            $request->merge(['custom_path' => Str::slug((string) $request->input('custom_path'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedQrSettings(Request $request, ?QrCode $qrCode = null): array
    {
        return $request->validate([
            'qr_expires_at' => ['nullable', 'date'],
            'custom_path' => [
                'nullable',
                'string',
                'max:72',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                Rule::unique('qr_codes', 'custom_path')->ignore($qrCode?->id),
            ],
            'foreground_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'label_text' => ['nullable', 'string', 'max:72'],
            'show_label' => ['nullable', 'boolean'],
            'show_logo' => ['nullable', 'boolean'],
            'external_link_label' => ['nullable', 'string', 'max:80'],
            'external_link_url' => ['nullable', 'url:http,https', 'max:2048'],
            'show_external_link' => ['nullable', 'boolean'],
        ]);
    }

    private function updateQrCodeFromSettings(Request $request, Batch $batch, array $settings): void
    {
        $batch->loadMissing('qrCode');

        if (! $batch->qrCode) {
            return;
        }

        $payload = [];

        if (Schema::hasColumn('qr_codes', 'expires_at')) {
            $payload['expires_at'] = filled($settings['qr_expires_at'] ?? null)
                ? Carbon::parse($settings['qr_expires_at'])->endOfDay()
                : null;
            $payload['status'] = $batch->qrCode->status === 'expired' ? 'active' : $batch->qrCode->status;
        }

        if (Schema::hasColumn('qr_codes', 'custom_path')) {
            $customPath = filled($settings['custom_path'] ?? null)
                ? Str::slug((string) $settings['custom_path'])
                : null;

            $payload['custom_path'] = $customPath;
            $payload['verification_url'] = url('/verify/'.($customPath ?: $batch->qrCode->token));
        }

        if (Schema::hasColumn('qr_codes', 'foreground_color')) {
            $payload['foreground_color'] = $settings['foreground_color'] ?? '#111827';
            $payload['background_color'] = $settings['background_color'] ?? '#ffffff';
            $payload['label_text'] = $settings['label_text'] ?? null;
            $payload['show_label'] = $request->boolean('show_label');
            $payload['show_logo'] = $request->boolean('show_logo');
        }

        if (Schema::hasColumn('qr_codes', 'external_link_url')) {
            $payload['external_link_label'] = $settings['external_link_label'] ?? null;
            $payload['external_link_url'] = $settings['external_link_url'] ?? null;
            $payload['show_external_link'] = $request->boolean('show_external_link')
                && filled($settings['external_link_url'] ?? null);
        }

        if ($payload !== []) {
            $batch->qrCode->update($payload);
        }
    }

    private function generateQrCodeFor(Batch $batch, array $settings = []): QrCode
    {
        return retry(3, function () use ($batch, $settings): QrCode {
            $token = 'a8_'.Str::lower(Str::random(32));
            $customPath = $settings['custom_path'] ?? null;

            $payload = [
                'batch_id' => $batch->id,
                'token' => $token,
                'verification_url' => url('/verify/'.($customPath ?: $token)),
                'status' => 'active',
                'generated_at' => now(),
                'activated_at' => now(),
            ];

            if (Schema::hasColumn('qr_codes', 'expires_at') && $batch->expiry_date) {
                $payload['expires_at'] = $batch->expiry_date->copy()->endOfDay();
            }

            if (Schema::hasColumn('qr_codes', 'expires_at') && array_key_exists('qr_expires_at', $settings)) {
                $payload['expires_at'] = filled($settings['qr_expires_at'] ?? null)
                    ? Carbon::parse($settings['qr_expires_at'])->endOfDay()
                    : null;
            }

            if (Schema::hasColumn('qr_codes', 'custom_path')) {
                $payload['custom_path'] = $customPath;
            }

            if (Schema::hasColumn('qr_codes', 'label_text')) {
                $payload['foreground_color'] = $settings['foreground_color'] ?? '#111827';
                $payload['background_color'] = $settings['background_color'] ?? '#ffffff';
                $payload['label_text'] = $settings['label_text'] ?? $batch->batch_number;
                $payload['show_label'] = (bool) ($settings['show_label'] ?? false);
                $payload['show_logo'] = (bool) ($settings['show_logo'] ?? false);
            }

            if (Schema::hasColumn('qr_codes', 'external_link_url')) {
                $payload['external_link_label'] = $settings['external_link_label'] ?? null;
                $payload['external_link_url'] = $settings['external_link_url'] ?? null;
                $payload['show_external_link'] = (bool) ($settings['show_external_link'] ?? false)
                    && filled($settings['external_link_url'] ?? null);
            }

            return QrCode::query()->create($payload);
        }, 50, function (Throwable $exception): bool {
            return $exception instanceof QueryException;
        });
    }

    private function logActivity(string $event, Batch $batch, string $description): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        ActivityLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => Batch::class,
            'subject_id' => $batch->id,
            'description' => $description,
            'metadata' => [
                'batch_number' => $batch->batch_number,
                'product_id' => $batch->product_id,
                'status' => $batch->status,
                'qa_status' => $batch->qa_status,
                'qr_status' => $batch->qrCode?->status,
                'qr_expires_at' => $batch->qrCode?->expires_at?->toIso8601String(),
            ],
        ]);
    }
}
