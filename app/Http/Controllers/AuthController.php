<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'This account is not active.'])
                ->onlyInput('email');
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $this->logCompanyAuthEvent($user, 'company.auth.login', $user->name.' signed in.', $request);

        if ($user->must_reset_password) {
            return redirect()->route('password.force.show');
        }

        $dashboardRoute = $user->isCompanyUser()
            ? route('company.dashboard')
            : route('active8.page', 'dashboard');

        return redirect()->intended($dashboardRoute);
    }

    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $this->logCompanyAuthEvent($user, 'company.auth.logout', $user->name.' signed out.', $request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function showForcedPasswordReset(): View
    {
        return view('auth.force-reset-password');
    }

    public function completeForcedPasswordReset(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_reset_password' => false,
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        $dashboardRoute = $user->isCompanyUser()
            ? route('company.dashboard')
            : route('active8.page', 'dashboard');

        return redirect()->intended($dashboardRoute)
            ->with('status', 'Password reset successfully.');
    }

    public function sendResetOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = Str::lower($validated['email']);
        $userExists = User::query()->where('email', $email)->exists();

        if (! $userExists) {
            return back()
                ->withErrors(['email' => 'No Active8 account was found for that email address.'])
                ->onlyInput('email');
        }

        $recentReset = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($recentReset?->created_at && Carbon::parse($recentReset->created_at)->gt(now()->subMinute())) {
            return redirect()
                ->route('password.reset', ['email' => $email])
                ->withErrors(['email' => 'Please wait 1 minute before requesting another reset code.']);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        $request->session()->forget('password_reset_verified_email');

        Mail::raw(
            "Your Active8 password reset code is {$otp}. This code expires in 5 minutes.",
            fn ($message) => $message
                ->to($email)
                ->subject('Your Active8 password reset code')
        );

        $redirect = redirect()
            ->route('password.reset', ['email' => $email])
            ->with('status', 'We sent a 6-digit reset code to your email.');

        if (app()->environment(['local', 'development', 'testing'])) {
            $redirect->with('debug_reset_otp', $otp);
        }

        return $redirect;
    }

    public function showResetPassword(Request $request): View
    {
        $email = Str::lower($request->string('email')->toString());
        $reset = $email !== ''
            ? DB::table('password_reset_tokens')->where('email', $email)->first()
            : null;
        $resendAvailableIn = 0;

        if ($reset?->created_at) {
            $resendAvailableAt = Carbon::parse($reset->created_at)->addMinute();
            $resendAvailableIn = $resendAvailableAt->isFuture()
                ? $resendAvailableAt->getTimestamp() - now()->getTimestamp()
                : 0;
        }

        return view('auth.reset-password', [
            'email' => $email,
            'otpVerified' => $request->session()->get('password_reset_verified_email') === $email,
            'resendAvailableIn' => $resendAvailableIn,
        ]);
    }

    public function verifyResetOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Str::lower($request->string('email')->toString());
        $reset = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $reset || ! Hash::check($request->string('otp')->toString(), $reset->token)) {
            return back()
                ->withErrors(['otp' => 'The reset code is invalid.'])
                ->onlyInput('email');
        }

        if (! $reset->created_at || Carbon::parse($reset->created_at)->lt(now()->subMinutes(5))) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'The reset code has expired. Please request a new one.'])
                ->onlyInput('email');
        }

        $request->session()->put('password_reset_verified_email', $email);

        return redirect()
            ->route('password.reset', ['email' => $email])
            ->with('status', 'Code verified. Create your new password.');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $email = Str::lower($request->string('email')->toString());

        if ($request->session()->get('password_reset_verified_email') !== $email) {
            return redirect()
                ->route('password.reset', ['email' => $email])
                ->withErrors(['otp' => 'Please verify the reset code first.']);
        }

        $reset = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $reset || ! $reset->created_at || Carbon::parse($reset->created_at)->lt(now()->subMinutes(5))) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $request->session()->forget('password_reset_verified_email');

            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'The reset code has expired. Please request a new one.']);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $request->session()->forget('password_reset_verified_email');

            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'We could not reset that account. Please request a new code.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->string('password')->toString()),
            'remember_token' => Str::random(60),
            'status' => 'active',
            'must_reset_password' => false,
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget('password_reset_verified_email');

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
    }

    private function logCompanyAuthEvent(User $user, string $event, string $description, Request $request): void
    {
        if (! $user->isCompanyUser() || ! Schema::hasTable('activity_logs')) {
            return;
        }

        ActivityLog::query()->create([
            'user_id' => $user->id,
            'event' => $event,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => $description,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);
    }
}
