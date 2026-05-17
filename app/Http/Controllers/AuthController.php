<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $data = $request->validateWithBag('register', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_CHEF, User::ROLE_TRAVELER])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Chef-specific fields
            'cuisine_type' => ['nullable', 'string', 'max:255'],
            'years_experience' => ['nullable', 'string', 'max:255'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'heritage_story' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
        ]);

        $status = in_array($data['role'], [User::ROLE_CHEF, User::ROLE_TRAVELER], true)
            ? User::STATUS_PENDING
            : User::STATUS_APPROVED;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'status' => $status,
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

        if (in_array($data['role'], [User::ROLE_CHEF, User::ROLE_TRAVELER])) {
            return redirect()->route('verification.show');
        }

        return redirect()->route('dashboard');
    }

    public function showLogin(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
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
        if (!$user || !Hash::check($data['password'], $user->password)) {
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

        // Block login if admin account is locked / suspended
        if ($user->role === User::ROLE_ADMIN && $user->status === User::STATUS_SUSPENDED) {
            LoginActivity::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => true,
                'successful' => false,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'device_fingerprint' => $deviceFingerprint,
                'reason' => 'locked',
            ]);

            return back()
                ->withErrors(['email' => 'Your account has been locked due to multiple failed login attempts. Please contact a super admin.'])
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

            if (app()->environment('local')) {
                $request->session()->flash('two_factor_hint', $code);
            }

            return redirect()->route('login.2fa.show');
        }

        // Standard login flow (no 2FA)
        Auth::login($user, $remember);
        $request->session()->regenerate();

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
            'password' => 'required|confirmed|min:8',
        ]);

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
}

