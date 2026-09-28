<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_creates_otp_reset_record(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.reset', ['email' => $user->email]));

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        $reset = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        $this->assertNotNull($reset);
        $this->assertFalse((bool) preg_match('/^\d{6}$/', $reset->token));
    }

    public function test_development_environment_flashes_reset_code(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.reset', ['email' => $user->email]))
            ->assertSessionHas('debug_reset_otp');

        $otp = session('debug_reset_otp');

        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    public function test_forgot_password_rejects_unknown_email_without_creating_otp(): void
    {
        Mail::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'missing@example.com',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'missing@example.com',
        ]);
    }

    public function test_forgot_password_blocks_another_code_request_for_one_minute(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.reset', ['email' => $user->email]))
            ->assertSessionHasErrors('email');

        $reset = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        $this->assertTrue(Hash::check('123456', $reset->token));
    }

    public function test_reset_page_uses_actual_remaining_resend_cooldown_after_refresh(): void
    {
        Carbon::setTestNow('2026-09-27 12:00:00');

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subSeconds(35),
        ]);

        $this->get(route('password.reset', ['email' => $user->email]))
            ->assertOk()
            ->assertSee('data-remaining="25"', false)
            ->assertSee('disabled', false)
            ->assertSee('Request another code in 25s');

        Carbon::setTestNow();
    }

    public function test_reset_page_enables_resend_link_after_cooldown_passes(): void
    {
        Carbon::setTestNow('2026-09-27 12:00:00');

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subSeconds(61),
        ]);

        $this->get(route('password.reset', ['email' => $user->email]))
            ->assertOk()
            ->assertSee('data-remaining="0"', false)
            ->assertSee('>Request another code</button>', false);

        Carbon::setTestNow();
    }

    public function test_request_another_code_creates_new_otp_after_cooldown(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-09-27 12:00:00');

        $user = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subSeconds(61),
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.reset', ['email' => $user->email]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'We sent a 6-digit reset code to your email.');

        $reset = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        $this->assertNotNull($reset);
        $this->assertSame(now()->toDateTimeString(), Carbon::parse($reset->created_at)->toDateTimeString());
        $this->assertFalse(Hash::check('123456', $reset->token));

        Carbon::setTestNow();
    }

    public function test_otp_verification_shows_password_step(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('old-password'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->post(route('password.otp.verify'), [
            'email' => $user->email,
            'otp' => '123456',
        ])->assertRedirect(route('password.reset', ['email' => $user->email]));

        $this->withSession(['password_reset_verified_email' => $user->email])
            ->get(route('password.reset', ['email' => $user->email]))
            ->assertOk()
            ->assertSee('Create a new password')
            ->assertSee('Update password')
            ->assertDontSee('Verify code');
    }

    public function test_password_can_be_reset_after_valid_otp_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('old-password'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->withSession(['password_reset_verified_email' => $user->email])
            ->post(route('password.update'), [
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_password_is_not_reset_before_otp_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('old-password'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->post(route('password.update'), [
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('password.reset', ['email' => $user->email]));

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
    }

    public function test_password_is_not_reset_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('old-password'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $this->from(route('password.reset', ['email' => $user->email]))
            ->post(route('password.otp.verify'), [
                'email' => $user->email,
                'otp' => '654321',
            ])->assertRedirect(route('password.reset', ['email' => $user->email]));

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_otp_expires_after_five_minutes(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('old-password'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(6),
        ]);

        $this->post(route('password.otp.verify'), [
            'email' => $user->email,
            'otp' => '123456',
        ])->assertRedirect(route('password.request'));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }
}
