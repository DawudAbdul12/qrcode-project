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

        $this->post(route('ussd.callback'), $this->naloPayload($labelCode->code))
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonPath('USERID', 'ACTIVE8')
            ->assertJsonPath('MSISDN', '233240000000')
            ->assertJsonPath('MSGTYPE', false)
            ->assertJsonPath('MSG', 'GENUINE: Shea Butter by Akoma Naturals. Keep this SMS.');

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

        $metadata = ScanLog::query()->firstOrFail()->metadata;
        $this->assertSame('nalo', $metadata['provider']);
        $this->assertSame('session-1', $metadata['session_id']);
    }

    public function test_ussd_callback_accepts_the_hyphenated_code_printed_on_the_label(): void
    {
        $labelCode = $this->labelCode();

        $this->post(route('ussd.callback'), $this->naloPayload($labelCode->formattedCode()))
            ->assertOk()
            ->assertJsonPath('MSGTYPE', false)
            ->assertJsonPath('MSG', 'GENUINE: Shea Butter by Akoma Naturals. Keep this SMS.');

        $this->assertDatabaseHas('label_codes', [
            'id' => $labelCode->id,
            'status' => 'used',
            'verification_count' => 1,
        ]);
    }

    public function test_ussd_callback_prompts_for_a_code_when_nalo_starts_a_session(): void
    {
        $this->post(route('ussd.callback'), $this->naloPayload('9', true))
            ->assertOk()
            ->assertJson([
                'USERID' => 'ACTIVE8',
                'MSISDN' => '233240000000',
                'MSG' => 'Enter the code printed on the product label:',
                'MSGTYPE' => true,
            ]);

        $this->assertDatabaseCount('scan_logs', 0);
    }

    public function test_ussd_callback_accepts_string_one_as_nalos_initial_request_flag(): void
    {
        $payload = $this->naloPayload('9');
        $payload['MSGTYPE'] = '1';

        $this->post(route('ussd.callback'), $payload)
            ->assertOk()
            ->assertJson([
                'USERID' => 'ACTIVE8',
                'MSISDN' => '233240000000',
                'MSG' => 'Enter the code printed on the product label:',
                'MSGTYPE' => true,
            ]);

        $this->assertDatabaseCount('scan_logs', 0);
    }

    public function test_ussd_callback_logs_invalid_code_attempts(): void
    {
        $this->post(route('ussd.callback'), $this->naloPayload('NOPE999'))
            ->assertOk()
            ->assertJsonPath('MSGTYPE', false)
            ->assertJsonPath('MSG', 'INVALID CODE. Product may not be genuine.');

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

        $this->post(route('ussd.callback'), $this->naloPayload($labelCode->code))
            ->assertOk()
            ->assertJsonPath('MSG', 'USED CODE. This label was already checked. Call the seller if unsure.');

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

        $this->post(route('ussd.callback'), $this->naloPayload($labelCode->code))
            ->assertOk()
            ->assertJsonPath('MSG', 'SUSPICIOUS CODE. Too many checks. Contact the manufacturer.');

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

            $this->post(route('ussd.callback'), $this->naloPayload($labelCode->code))
                ->assertOk()
                ->assertJsonPath('MSGTYPE', false)
                ->assertJsonPath('MSG', fn (string $message): bool => str_starts_with($message, strtoupper($status)));

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

    public function test_ussd_callback_rejects_a_different_nalo_user_id_when_configured(): void
    {
        config()->set('services.nalo.ussd_user_id', 'EXPECTED-ID');

        $this->post(route('ussd.callback'), $this->naloPayload('ABC12345678'))
            ->assertForbidden()
            ->assertJsonPath('MSGTYPE', false)
            ->assertJsonPath('MSG', 'Unable to process this request.');

        $this->assertDatabaseCount('scan_logs', 0);
    }

    /**
     * @return array{USERID: string, MSISDN: string, USERDATA: string, MSGTYPE: bool, NETWORK: string, SESSIONID: string}
     */
    private function naloPayload(string $userData, bool $messageType = false): array
    {
        return [
            'USERID' => 'ACTIVE8',
            'MSISDN' => '233240000000',
            'USERDATA' => $userData,
            'MSGTYPE' => $messageType,
            'NETWORK' => 'MTN',
            'SESSIONID' => 'session-1',
        ];
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
