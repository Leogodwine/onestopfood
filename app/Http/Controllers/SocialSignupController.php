<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PartnerApplicationService;
use App\Services\SocialSignupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SocialSignupController extends Controller
{
    public function __construct(
        private readonly SocialSignupService $socialSignup,
        private readonly PartnerApplicationService $partnerApplication
    ) {}

    public function showComplete(Request $request)
    {
        if ($request->boolean('restart')) {
            $request->session()->forget([
                'social_signup_otp_sent',
                'social_signup_otp_hint',
                'social_signup_intended_role',
                'social_signup_otp_sent_at',
            ]);
        }

        $user = $this->resolveSignupUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $this->socialSignup->needsCompletion($user)) {
            return $this->finishLogin($request, $user);
        }

        $request->session()->put('open_social_signup_modal', true);

        return redirect()->route('login');
    }

    public function sendOtp(Request $request)
    {
        $user = $this->resolveSignupUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validateWithBag('social_signup', [
            'role' => ['required', Rule::in(PartnerApplicationService::SIGNUP_ROLES)],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ]);

        try {
            $user = $this->partnerApplication->setSignupRole($user, $data['role']);
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return redirect()
                ->route('login')
                ->withInput()
                ->withErrors(['role' => $e->getMessage()], 'social_signup');
        }

        $request->session()->put('social_signup_intended_role', $data['role']);

        try {
            $this->socialSignup->sendOtp($user, $data['phone']);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('login')
                ->withInput()
                ->withErrors(['phone' => $e->getMessage()], 'social_signup');
        }

        $user = $user->fresh();
        $request->session()->put('social_signup_otp_sent', true);
        $request->session()->put('social_signup_otp_sent_at', now()->timestamp);
        $request->session()->put('open_social_signup_modal', true);

        if (config('app.show_developer_hints')) {
            $request->session()->flash('social_signup_otp_hint', $user->signup_otp_code);
        }

        return redirect()->route('login');
    }

    public function verifyOtp(Request $request)
    {
        $user = $this->resolveSignupUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validateWithBag('social_signup', [
            'code' => ['required', 'digits:6'],
        ]);

        if (! $this->socialSignup->verifyOtp($user->fresh(), $request->input('code'))) {
            $request->session()->put('open_social_signup_modal', true);

            return redirect()
                ->route('login')
                ->withErrors(['code' => 'Invalid or expired verification code. Please try again or request a new code.'], 'social_signup');
        }

        $request->session()->forget(['social_signup_otp_sent', 'social_signup_otp_hint', 'open_social_signup_modal', 'social_signup_intended_role']);

        return $this->finishLogin($request, $user->fresh());
    }

    public function resendOtp(Request $request)
    {
        $user = $this->resolveSignupUser($request);

        if (! $user || empty($user->phone)) {
            $request->session()->put('open_social_signup_modal', true);

            return redirect()
                ->route('login')
                ->withErrors(['code' => 'Please enter your phone number first.'], 'social_signup');
        }

        $sentAt = (int) $request->session()->get('social_signup_otp_sent_at', 0);
        $remaining = $this->socialSignup->resendCooldownRemaining($sentAt > 0 ? $sentAt : null);

        if ($remaining > 0) {
            $request->session()->put('open_social_signup_modal', true);

            return redirect()
                ->route('login')
                ->withErrors(['code' => 'Please wait '.$remaining.' seconds before requesting a new code.'], 'social_signup');
        }

        try {
            $this->socialSignup->sendOtp($user, $user->phone);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('login')
                ->withErrors(['phone' => $e->getMessage()], 'social_signup');
        }

        $user = $user->fresh();
        $request->session()->put('social_signup_otp_sent', true);
        $request->session()->put('social_signup_otp_sent_at', now()->timestamp);
        $request->session()->put('open_social_signup_modal', true);

        if (config('app.show_developer_hints')) {
            $request->session()->flash('social_signup_otp_hint', $user->signup_otp_code);
        }

        return redirect()->route('login');
    }

    private function resolveSignupUser(Request $request): ?User
    {
        if ($request->session()->has('social_signup_user_id')) {
            return User::query()->find($request->session()->get('social_signup_user_id'));
        }

        if (Auth::check() && Auth::user()->isSocialOnlyUser()) {
            return Auth::user();
        }

        return null;
    }

    private function finishLogin(Request $request, User $user)
    {
        $request->session()->forget('social_signup_user_id');

        Auth::login($user, true);
        $request->session()->regenerate();

        if (
            in_array($user->role, PartnerApplicationService::PARTNER_ROLES, true)
            && in_array($user->status, [User::STATUS_PENDING, User::STATUS_REJECTED], true)
        ) {
            $label = $user->role === User::ROLE_CHEF ? 'chef' : 'traveler';

            return redirect()
                ->route('verification.show')
                ->with('success', 'Account verified! Complete your '.$label.' verification profile to submit for admin approval.');
        }

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'Your account has been verified. Welcome!');
    }
}
