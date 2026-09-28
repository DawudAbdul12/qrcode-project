<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Company;
use App\Models\LabelCode;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_owner_can_view_company_dashboard(): void
    {
        $company = $this->company();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee($company->name)
            ->assertSee('Total Products')
            ->assertSee('Active Batches')
            ->assertSee('Total QR Scans')
            ->assertSee('Total USSD Checks')
            ->assertSee('Suspicious Activity')
            ->assertSee('Most Checked Products');
    }

    public function test_platform_user_cannot_view_company_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertForbidden();
    }

    public function test_suspended_company_user_cannot_view_company_dashboard(): void
    {
        $company = $this->company();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'suspended',
        ]);

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertForbidden();
    }

    public function test_user_without_company_cannot_view_company_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('company.dashboard'))
            ->assertForbidden();
    }

    public function test_company_owner_is_redirected_away_from_admin_dashboard(): void
    {
        $company = $this->company();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('active8.page', 'dashboard'))
            ->assertRedirect(route('company.dashboard'));
    }

    public function test_company_owner_login_redirects_to_company_dashboard(): void
    {
        $company = $this->company();
        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->post(route('login.store'), [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertRedirect(route('company.dashboard'));
    }

    public function test_company_account_log_shows_login_and_logout_events(): void
    {
        $company = $this->company();
        User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Company Owner',
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->post(route('login.store'), [
            'email' => 'owner@example.com',
            'password' => 'password',
        ])->assertRedirect(route('company.dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'company.auth.login',
            'description' => 'Company Owner signed in.',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'company.auth.logout',
            'description' => 'Company Owner signed out.',
        ]);

        $user = User::query()->where('email', 'owner@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get(route('company.account', ['tab' => 'log']))
            ->assertOk()
            ->assertSee('Company Owner signed in.')
            ->assertSee('Company Owner signed out.');
    }

    public function test_company_lists_only_show_the_authenticated_company_data(): void
    {
        $owned = $this->company('Owned Company', 'owned-company');
        $other = $this->company('Other Company', 'other-company');
        $user = User::factory()->create([
            'company_id' => $owned->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);
        $ownedData = $this->companyData($owned, 'Owned Product', 'OWN-BATCH', 'OWNEDCODE01');
        $otherData = $this->companyData($other, 'Other Product', 'OTHER-BATCH', 'OTHERCODE01');

        $this->actingAs($user)
            ->get(route('company.products'))
            ->assertOk()
            ->assertSee($ownedData['product']->name)
            ->assertDontSee($otherData['product']->name);

        $this->actingAs($user)
            ->get(route('company.batches'))
            ->assertOk()
            ->assertSee($ownedData['batch']->batch_number)
            ->assertDontSee($otherData['batch']->batch_number);

        $this->actingAs($user)
            ->get(route('company.label-orders'))
            ->assertOk()
            ->assertSee($ownedData['product']->name)
            ->assertDontSee($otherData['product']->name);

        $this->actingAs($user)
            ->get(route('company.ussd-codes'))
            ->assertOk()
            ->assertSee($ownedData['code']->formattedCode())
            ->assertDontSee($otherData['code']->formattedCode());

        $this->actingAs($user)
            ->get(route('company.verifications'))
            ->assertOk()
            ->assertSee($ownedData['scan']->token_scanned)
            ->assertDontSee($otherData['scan']->token_scanned);
    }

    public function test_company_owner_cannot_access_another_company_route_models(): void
    {
        $owned = $this->company('Owned Company', 'owned-company');
        $other = $this->company('Other Company', 'other-company');
        $user = User::factory()->create([
            'company_id' => $owned->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);
        $otherData = $this->companyData($other, 'Other Product', 'OTHER-BATCH', 'OTHERCODE01');

        $this->actingAs($user)->get(route('company.products.show', $otherData['product']))->assertForbidden();
        $this->actingAs($user)->get(route('company.batches.show', $otherData['batch']))->assertForbidden();
        $this->actingAs($user)->get(route('company.label-orders.show', $otherData['order']))->assertForbidden();
        $this->actingAs($user)->get(route('company.ussd-codes.show', $otherData['code']))->assertForbidden();
        $this->actingAs($user)->get(route('company.verifications.show', $otherData['scan']))->assertForbidden();
    }

    public function test_company_account_profile_shows_edit_modal(): void
    {
        $company = $this->company();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('company.account', ['tab' => 'profile']))
            ->assertOk()
            ->assertSee('Edit profile')
            ->assertSee(route('company.account.profile.update'), false)
            ->assertSee('data-account-profile-modal', false);
    }

    public function test_company_owner_can_update_allowed_account_profile_fields(): void
    {
        $company = $this->company();
        $company->update([
            'registration_number' => 'REG-ORIGINAL',
            'industry' => 'Cosmetics',
            'country' => 'Ghana',
            'region' => 'Ashanti',
            'city' => 'Kumasi',
        ]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->put(route('company.account.profile.update'), [
                'company' => [
                    'name' => 'Updated Naturals',
                    'registration_number' => 'REG-88',
                    'industry' => 'Skincare',
                    'contact_person' => 'Ama Mensah',
                    'email' => 'support@example.com',
                    'phone' => '+233200000000',
                    'country' => 'Ghana',
                    'region' => 'Greater Accra',
                    'city' => 'Accra',
                    'website' => 'https://example.com',
                ],
                'profile' => [
                    'verification_headline' => 'Verify before use',
                    'support_email' => 'verify@example.com',
                    'support_phone' => '+233244000000',
                    'website_url' => 'https://verify.example.com',
                    'company_description' => 'Original product maker.',
                    'manufacturer_name' => 'Updated Naturals Factory',
                    'factory_address' => 'North Industrial Area',
                    'certification_number' => 'CERT-99',
                ],
            ])
            ->assertRedirect(route('company.account', ['tab' => 'profile']));

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Akoma Naturals',
            'registration_number' => 'REG-ORIGINAL',
            'industry' => 'Cosmetics',
            'country' => 'Ghana',
            'region' => 'Ashanti',
            'city' => 'Kumasi',
            'contact_person' => 'Ama Mensah',
            'email' => 'support@example.com',
            'phone' => '+233200000000',
            'website' => 'https://example.com',
        ]);
        $this->assertDatabaseHas('company_profiles', [
            'company_id' => $company->id,
            'verification_headline' => 'Verify before use',
            'support_email' => 'verify@example.com',
        ]);
    }

    public function test_company_owner_can_add_company_user(): void
    {
        $company = $this->company();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('company.users'))
            ->assertOk()
            ->assertSee('Add user');

        $this->actingAs($owner)
            ->post(route('company.users.store'), [
                'name' => 'Kofi Staff',
                'email' => 'kofi.staff@example.com',
                'role' => 'company_staff',
                'status' => 'active',
                'password' => 'temporary-pass',
            ])
            ->assertRedirect(route('company.users'));

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'name' => 'Kofi Staff',
            'email' => 'kofi.staff@example.com',
            'role' => 'company_staff',
            'status' => 'active',
        ]);
    }

    public function test_company_owner_can_add_company_user_from_account_users_tab(): void
    {
        $company = $this->company();
        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('company.account', ['tab' => 'users']))
            ->assertOk()
            ->assertSee('Add user')
            ->assertSee('data-account-user-drawer', false);

        $this->actingAs($owner)
            ->post(route('company.users.store'), [
                'return_to' => 'account-users',
                'name' => 'Efua Staff',
                'email' => 'efua.staff@example.com',
                'role' => 'company_staff',
                'status' => 'active',
                'password' => 'temporary-pass',
            ])
            ->assertRedirect(route('company.account', ['tab' => 'users']));

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'name' => 'Efua Staff',
            'email' => 'efua.staff@example.com',
            'role' => 'company_staff',
            'status' => 'active',
        ]);
    }

    public function test_company_staff_cannot_add_company_user(): void
    {
        $company = $this->company();
        $staff = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_staff',
            'status' => 'active',
        ]);

        $this->actingAs($staff)
            ->get(route('company.users'))
            ->assertOk()
            ->assertDontSee('Add user');

        $this->actingAs($staff)
            ->post(route('company.users.store'), [
                'name' => 'Blocked User',
                'email' => 'blocked@example.com',
                'role' => 'company_staff',
                'status' => 'active',
                'password' => 'temporary-pass',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'blocked@example.com',
        ]);
    }

    private function company(string $name = 'Akoma Naturals', string $slug = 'akoma-naturals'): Company
    {
        return Company::query()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
        ]);
    }

    /**
     * @return array{product: Product, batch: Batch, order: LabelOrder, code: LabelCode, scan: ScanLog}
     */
    private function companyData(Company $company, string $productName, string $batchNumber, string $code): array
    {
        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => $productName,
            'slug' => str($productName)->slug(),
            'status' => 'active',
        ]);
        $batch = Batch::query()->create([
            'product_id' => $product->id,
            'batch_number' => $batchNumber,
            'quantity' => 100,
            'qa_status' => 'passed',
            'status' => 'active',
        ]);
        $qrCode = QrCode::query()->create([
            'batch_id' => $batch->id,
            'token' => $batchNumber.'-TOKEN',
            'verification_url' => 'https://example.test/verify/'.$batchNumber,
            'status' => 'active',
        ]);
        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 100,
            'status' => 'printed',
        ]);
        $labelCode = LabelCode::query()->create([
            'label_order_id' => $order->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => $code,
            'status' => 'unused',
        ]);
        $scanLog = ScanLog::query()->create([
            'qr_code_id' => $qrCode->id,
            'label_code_id' => $labelCode->id,
            'token_scanned' => $code,
            'status_result' => 'genuine',
            'scanned_at' => now(),
            'device_type' => 'ussd',
        ]);

        return [
            'product' => $product,
            'batch' => $batch,
            'order' => $order,
            'code' => $labelCode,
            'scan' => $scanLog,
        ];
    }
}
