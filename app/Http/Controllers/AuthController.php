<?php

namespace App\Http\Controllers;

use App\Mail\AdminTwoFactorMail;
use App\Support\PasswordRules;
use App\Models\User;
use App\Models\LoginActivity;
use App\Services\PartnerApplicationService;
use App\Services\SocialSignupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $existingUser = User::query()
            ->where('email', $request->input('email'))
            ->first();

        if ($existingUser && $existingUser->socialAccounts()->exists()) {
            $role = $request->input('role', User::ROLE_CUSTOMER);
            $intentHint = in_array($role, [User::ROLE_CHEF, User::ROLE_TRAVELER], true)
                ? ' Use Google or Facebook sign-in with the same email — choose “Become a Chef” or “Become a Traveler” first.'
                : ' Use Google or Facebook sign-in with the same email instead.';

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'This email is already linked to a social account.'.$intentHint], 'register');
        }

        \App\Support\PhoneNumber::mergeIntoRequest($request);

        $data = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_country_code' => ['required', 'string', Rule::in(array_keys(\App\Support\PhoneNumber::countries()))],
            'phone_number' => \App\Support\PhoneNumber::nationalNumberRules('phone_country_code'),
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_CHEF, User::ROLE_TRAVELER])],
            'password' => PasswordRules::forRegistration(),
            // Chef-specific fields
            'cuisine_type' => ['nullable', 'string', 'max:255'],
            'years_experience' => ['nullable', 'string', 'max:255'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'heritage_story' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
        ], array_merge(PasswordRules::registerMessages(), \App\Support\PhoneNumber::validationMessages()));

        $status = in_array($data['role'], [User::ROLE_CHEF, User::ROLE_TRAVELER], true)
            ? User::STATUS_PENDING
            : User::STATUS_APPROVED;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'status' => $status,
            'locale' => in_array(session('locale'), ['en', 'sw'], true) ? session('locale') : 'en',
            'password' => Hash::make($data['password']),
        ]);

        // Create chef profile if role is chef
        if ($data['role'] === User::ROLE_CHEF) {
            \App\Models\ChefProfile::create([
                'user_id' => $user->id,
                'cuisine_type' => $data['cuisine_type'] ?? null,
                'years_experience' => $data['years_experience'] ?? null,
                'specialties' => $data['specialties'] ?? null,
                'heritage_story' => $data['heritage_story'] ?? null,
                'bio' => $data['bio'] ?? null,
            ]);
        }

        // Create traveler profile if role is traveler
        if ($data['role'] === User::ROLE_TRAVELER) {
            \App\Models\TravelerProfile::create([
                'user_id' => $user->id,
            ]);
        }

        Auth::login($user);
        $this->syncUserLocale($user);

        app(\App\Services\AccountLifecycleNotifier::class)->accountCreated($user->fresh());

        if (in_array($data['role'], [User::ROLE_CHEF, User::ROLE_TRAVELER])) {
            return redirect()->route('verification.show');
        }

        return redirect()->route('dashboard');
    }

    public function showLogin(Request $request, SocialSignupService $socialSignup)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        if ($request->boolean('restart_social_signup')) {
            $request->session()->forget([
                'social_signup_otp_sent',
                'social_signup_otp_hint',
                'social_signup_intended_role',
                'social_signup_otp_sent_at',
            ]);
        }

        $socialSignupState = null;
        $userId = $request->session()->get('social_signup_user_id');

        if ($userId) {
            $user = User::query()->find($userId);

            if ($user && $socialSignup->needsCompletion($user)) {
                $defaultRole = $request->session()->get('social_signup_intended_role', User::ROLE_CUSTOMER);
                if (! in_array($defaultRole, PartnerApplicationService::SIGNUP_ROLES, true)) {
                    $defaultRole = User::ROLE_CUSTOMER;
                }

                $selectedRole = old('role', $defaultRole);

                $socialSignupState = [
                    'user' => $user,
                    'otpSent' => (bool) $request->session()->get('social_signup_otp_sent'),
                    'otpHint' => $socialSignup->otpHintForRequest($request, $user),
                    'showModal' => (bool) $request->session()->get('open_social_signup_modal', true),
                    'selectedRole' => $selectedRole,
                    'resendCooldown' => $socialSignup->resendCooldownRemaining(
                        $request->session()->get('social_signup_otp_sent_at')
                    ),
                ];
            }
        }

        $sessionErrors = $request->session()->get('errors');
        if (
            $socialSignupState
            && $sessionErrors instanceof \Illuminate\Support\ViewErrorBag
            && $sessionErrors->getBag('social_signup')->isNotEmpty()
        ) {
            $socialSignupState['showModal'] = true;
        }

        return view('auth.login', compact('socialSignupState'));
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_fingerprint' => ['nullable', 'string', 'max:255'],
        ]);

        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();
        $deviceFingerprint = $data['device_fingerprint'] ?? null;
        $remember = (bool) $request->boolean('remember');

        /** @var \App\Models\User|null $user */
        $user = User::where('email', $data['email'])->first();

        // Handle invalid user or password (and track failed attempts)
        if (! $user || empty($user->password) || ! Hash::check($data['password'], $user->password)) {
            if ($user && empty($user->password)) {
                $providers = $user->socialAccounts()
                    ->pluck('provider')
                    ->map(fn (string $p) => ucfirst($p))
                    ->unique()
                    ->implode(' or ');

                return back()
                    ->withErrors(['email' => 'This account uses '.($providers ?: 'social').' sign-in. Please use the button above.'])
                    ->onlyInput('email');
            }

            if ($user) {
                $user->failed_login_attempts = ($user->failed_login_attempts ?? 0) + 1;

                // Lock admin accounts after 5 consecutive failures
                if (
                    $user->role === User::ROLE_ADMIN &&
                    $user->failed_login_attempts >= 5 &&
                    $user->status !== User::STATUS_SUSPENDED
                ) {
                    $user->status = User::STATUS_SUSPENDED;
                }

                $user->save();
            }

            LoginActivity::create([
                'user_id' => $user?->id,
                'email' => $data['email'],
                'is_admin' => $user?->role === User::ROLE_ADMIN,
                'successful' => false,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_fingerprint' => $deviceFingerprint,
                'reason' => $user && $user->status === User::STATUS_SUSPENDED
                    ? 'locked'
                    : 'invalid_credentials',
            ]);

            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->onlyInput('email');
        }

        // Block login if account is admin-suspended (self-deactivated may sign in to reactivate)
        if ($user->status === User::STATUS_SUSPENDED && $user->suspended_by !== User::SUSPENDED_BY_SELF) {
            LoginActivity::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->role === User::ROLE_ADMIN,
                'successful' => false,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_fingerprint' => $deviceFingerprint,
                'reason' => 'locked',
            ]);

            $message = $user->role === User::ROLE_ADMIN
                ? 'Your account has been locked due to multiple failed login attempts. Please contact a super admin.'
                : __('account.admin_suspended_desc');

            return back()
                ->withErrors(['email' => $message])
                ->onlyInput('email');
        }

        // Reset failed attempts and update last login metadata
        $user->failed_login_attempts = 0;
        $user->last_login_at = now();
        $user->last_login_ip = $ip;
        $user->last_login_user_agent = $userAgent;
        $user->last_login_device_fingerprint = $deviceFingerprint;
        $user->save();

        // If admin has 2FA enabled, start 2FA flow (no session yet)
        if ($user->role === User::ROLE_ADMIN && $user->two_factor_enabled) {
            $code = (string) random_int(100000, 999999);

            $user->two_factor_code = $code;
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            LoginActivity::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => true,
                'successful' => true,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_fingerprint' => $deviceFingerprint,
                'reason' => 'password_verified_2fa_pending',
            ]);

            // Store pending admin ID and remember flag for 2FA step
            $request->session()->put('two_factor_admin_id', $user->id);
            $request->session()->put('two_factor_remember', $remember);

            try {
                Mail::to($user->email)->send(new AdminTwoFactorMail($user, $code));
            } catch (\Throwable $e) {
                report($e);
                logger()->warning('Admin 2FA email failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if (config('app.show_developer_hints')) {
                $request->session()->flash('two_factor_hint', $code);
            }

            return redirect()->route('login.2fa.show');
        }

        // Standard login flow (no 2FA)
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $this->syncUserLocale($user);

        LoginActivity::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'is_admin' => $user->role === User::ROLE_ADMIN,
            'successful' => true,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_fingerprint' => $deviceFingerprint,
            'reason' => 'login',
        ]);

        // Redirect to intended URL (set by auth middleware) or dashboard
        if ($user->isSelfDeactivated()) {
            return redirect()->route('account.settings')
                ->with('status', __('account.reactivate_desc'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showTwoFactorForm(Request $request)
    {
        if (!$request->session()->has('two_factor_admin_id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa');
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $adminId = $request->session()->get('two_factor_admin_id');
        $remember = (bool) $request->session()->get('two_factor_remember', false);

        if (!$adminId) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User|null $user */
        $user = User::find($adminId);

        if (
            !$user ||
            $user->role !== User::ROLE_ADMIN ||
            !$user->two_factor_enabled ||
            !$user->two_factor_code ||
            !$user->two_factor_expires_at ||
            now()->greaterThan($user->two_factor_expires_at) ||
            $request->input('code') !== $user->two_factor_code
        ) {
            return back()->withErrors(['code' => 'Invalid or expired 2FA code.']);
        }

        // Clear 2FA state
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $request->session()->forget(['two_factor_admin_id', 'two_factor_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $this->syncUserLocale($user);

        // Redirect to intended URL (set by auth middleware) or dashboard
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->isSocialOnlyUser()) {
            return back()->withErrors([
                'email' => 'This account uses Google or Facebook sign-in and does not have a password. Please use the social sign-in buttons on the login page.',
            ]);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => PasswordRules::forReset(),
        ], PasswordRules::validationMessages());

        $existing = User::where('email', $request->email)->first();
        if ($existing && $existing->isSocialOnlyUser()) {
            return back()->withErrors([
                'email' => 'This account uses Google or Facebook sign-in and cannot set a password here.',
            ]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    private function syncUserLocale(User $user): void
    {
        $sessionLocale = session('locale');

        if (in_array($sessionLocale, ['en', 'sw'], true) && $user->locale !== $sessionLocale) {
            $user->forceFill(['locale' => $sessionLocale])->save();

            return;
        }

        if (in_array($user->locale, ['en', 'sw'], true)) {
            session(['locale' => $user->locale]);
        }
    }
}

