<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Company;
use App\Models\LabelCode;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\ScanLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UssdVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ussd_callback_verifies_unused_label_code_and_logs_attempt(): void
    {
        $labelCode = $this->labelCode();

        $this->post(route('ussd.callback'), [
            'text' => '*XXX*'.$labelCode->code.'#',
            'phoneNumber' => '0240000000',
            'sessionId' => 'session-1',
        ])
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSeeText('END GENUINE');

        $this->assertDatabaseHas('label_codes', [
            'id' => $labelCode->id,
            'status' => 'used',
            'verification_count' => 1,
        ]);

        $this->assertDatabaseHas('scan_logs', [
            'label_code_id' => $labelCode->id,
            'token_scanned' => $labelCode->code,
            'status_result' => 'genuine',
            'device_type' => 'ussd',
        ]);
    }

    public function test_ussd_callback_logs_invalid_code_attempts(): void
    {
        $this->post(route('ussd.callback'), [
            'text' => '*XXX*NOPE999#',
            'phoneNumber' => '0240000000',
        ])
            ->assertOk()
            ->assertSeeText('END INVALID CODE');

        $this->assertDatabaseHas('scan_logs', [
            'label_code_id' => null,
            'token_scanned' => 'NOPE999',
            'status_result' => 'invalid',
            'device_type' => 'ussd',
        ]);
    }

    public function test_ussd_callback_reports_already_used_before_suspicious_threshold(): void
    {
        $labelCode = $this->labelCode([
            'status' => 'used',
            'verification_count' => 1,
            'first_verified_at' => now()->subDay(),
            'last_verified_at' => now()->subDay(),
        ]);

        $this->post(route('ussd.callback'), ['code' => $labelCode->code])
            ->assertOk()
            ->assertSeeText('END USED CODE');

        $this->assertDatabaseHas('label_codes', [
            'id' => $labelCode->id,
            'status' => 'used',
            'verification_count' => 2,
        ]);

        $this->assertDatabaseHas('scan_logs', [
            'label_code_id' => $labelCode->id,
            'status_result' => 'already_used',
        ]);
    }

    public function test_ussd_callback_flags_code_as_suspicious_after_too_many_checks(): void
    {
        $labelCode = $this->labelCode([
            'status' => 'used',
            'verification_count' => 3,
            'first_verified_at' => now()->subDay(),
            'last_verified_at' => now()->subHour(),
        ]);

        $this->post(route('ussd.callback'), ['code' => $labelCode->code])
            ->assertOk()
            ->assertSeeText('END SUSPICIOUS CODE');

        $this->assertDatabaseHas('label_codes', [
            'id' => $labelCode->id,
            'status' => 'suspicious',
            'verification_count' => 4,
        ]);

        $this->assertDatabaseHas('scan_logs', [
            'label_code_id' => $labelCode->id,
            'status_result' => 'suspicious',
        ]);
    }

    public function test_ussd_callback_supports_revoked_expired_and_recalled_codes(): void
    {
        foreach (['revoked', 'expired', 'recalled'] as $status) {
            $labelCode = $this->labelCode(['code' => 'CODE'.strtoupper($status), 'status' => $status]);

            $this->post(route('ussd.callback'), ['code' => $labelCode->code])
                ->assertOk()
                ->assertSeeText('END '.strtoupper($status));

            $this->assertDatabaseHas('scan_logs', [
                'label_code_id' => $labelCode->id,
                'status_result' => $status,
            ]);
        }
    }

    public function test_local_ussd_simulator_page_is_available(): void
    {
        $this->get(route('ussd.test'))
            ->assertOk()
            ->assertSee('USSD Verification Test');
    }

    private function labelCode(array $overrides = []): LabelCode
    {
        $company = Company::query()->create([
            'name' => 'Akoma Naturals',
            'slug' => 'akoma-'.strtolower($overrides['code'] ?? 'default'),
            'status' => 'active',
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Shea Butter',
            'slug' => 'shea-butter',
            'status' => 'active',
        ]);

        $batch = Batch::query()->create([
            'product_id' => $product->id,
            'batch_number' => 'AK-SG-'.str_pad((string) $company->id, 3, '0', STR_PAD_LEFT),
            'expiry_date' => now()->addYear()->toDateString(),
            'qa_status' => 'approved',
            'status' => 'distributed',
        ]);

        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 1,
            'status' => 'printed',
        ]);

        return LabelCode::query()->create(array_merge([
            'label_order_id' => $order->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'ABC12345678',
            'status' => 'unused',
            'verification_count' => 0,
        ], $overrides));
    }
}
