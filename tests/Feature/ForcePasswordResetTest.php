<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_platform_user_is_marked_for_password_reset(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
            'must_reset_password' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('users.store'), [
                'name' => 'New Operator',
                'email' => 'operator@example.com',
                'password' => 'temporary-pass',
                'role' => 'operator',
                'status' => 'active',
            ])
            ->assertRedirect(route('active8.page', 'accounts'));

        $user = User::query()->where('email', 'operator@example.com')->firstOrFail();

        $this->assertTrue($user->must_reset_password);
    }

    public function test_new_company_user_is_marked_for_password_reset(): void
    {
        $company = Company::query()->create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'status' => 'active',
        ]);

        $owner = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'company_owner',
            'status' => 'active',
            'must_reset_password' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('company.users.store'), [
                'name' => 'Company Staff',
                'email' => 'staff@example.com',
                'role' => 'company_staff',
                'status' => 'active',
                'password' => 'temporary-pass',
            ])
            ->assertRedirect(route('company.users'));

        $user = User::query()->where('email', 'staff@example.com')->firstOrFail();

        $this->assertTrue($user->must_reset_password);
    }

    public function test_flagged_user_is_redirected_to_forced_password_reset(): void
    {
        $user = User::factory()->create([
            'role' => 'operator',
            'status' => 'active',
            'must_reset_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('active8.page', 'dashboard'))
            ->assertRedirect(route('password.force.show'));
    }

    public function test_flagged_user_goes_to_forced_password_reset_after_login(): void
    {
        User::factory()->create([
            'email' => 'operator@example.com',
            'password' => Hash::make('temporary-pass'),
            'role' => 'operator',
            'status' => 'active',
            'must_reset_password' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => 'operator@example.com',
            'password' => 'temporary-pass',
        ])->assertRedirect(route('password.force.show'));
    }

    public function test_forced_password_reset_clears_flag_and_continues(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temporary-pass'),
            'role' => 'operator',
            'status' => 'active',
            'must_reset_password' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.force.update'), [
                'password' => 'new-secure-pass',
                'password_confirmation' => 'new-secure-pass',
            ])
            ->assertRedirect(route('active8.page', 'dashboard'));

        $user->refresh();

        $this->assertFalse($user->must_reset_password);
        $this->assertTrue(Hash::check('new-secure-pass', $user->password));
    }
}
