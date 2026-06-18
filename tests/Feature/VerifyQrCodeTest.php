<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\ScanLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyQrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_genuine_qr_code_stays_genuine_after_many_scans(): void
    {
        $company = Company::query()->create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'status' => 'active',
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'status' => 'active',
        ]);

        $batch = Batch::query()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-001',
            'expiry_date' => now()->addYear()->toDateString(),
            'qa_status' => 'approved',
            'status' => 'distributed',
        ]);

        $qrCode = QrCode::query()->create([
            'batch_id' => $batch->id,
            'token' => 'valid-token',
            'verification_url' => url('/verify/valid-token'),
            'status' => 'active',
            'generated_at' => now(),
            'activated_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        for ($scan = 1; $scan <= 10; $scan++) {
            ScanLog::query()->create([
                'qr_code_id' => $qrCode->id,
                'token_scanned' => $qrCode->token,
                'status_result' => 'genuine',
                'scanned_at' => now()->subMinutes(20 - $scan),
            ]);
        }

        $this->get('/verify/valid-token')
            ->assertOk()
            ->assertSee('Verified Genuine')
            ->assertDontSee('Review Needed');

        $this->assertDatabaseHas('scan_logs', [
            'qr_code_id' => $qrCode->id,
            'token_scanned' => 'valid-token',
            'status_result' => 'genuine',
        ]);
    }
}
