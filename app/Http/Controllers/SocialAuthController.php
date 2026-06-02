<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PartnerApplicationService;
use App\Services\SocialAuthService;
use App\Services\SocialSignupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $socialAuth,
        private readonly PartnerApplicationService $partnerApplication,
        private readonly SocialSignupService $socialSignup
    ) {}

    public function redirectGoogle(Request $request)
    {
        return $this->redirect($request, 'google');
    }

    public function callbackGoogle(Request $request)
    {
        return $this->callback($request, 'google');
    }

    public function redirectFacebook(Request $request)
    {
        return $this->redirect($request, 'facebook');
    }

    public function callbackFacebook(Request $request)
    {
        return $this->callback($request, 'facebook');
    }

    public function redirect(Request $request, string $provider)
    {
        if (! $this->socialAuth->isSupportedProvider($provider)) {
            abort(404);
        }

        if (! $this->socialAuth->isConfigured($provider)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => ucfirst($provider).' sign-in is not configured yet. Add '.strtoupper($provider).'_CLIENT_ID and '.strtoupper($provider).'_CLIENT_SECRET to your .env file.']);
        }

        $intent = $request->query('intent');
        if ($intent && $this->partnerApplication->isPartnerRole($intent)) {
            $request->session()->put('oauth_intended_role', $intent);
        } else {
            $request->session()->forget('oauth_intended_role');
        }

        $driver = Socialite::driver($provider)
            ->redirectUrl($this->socialAuth->redirectUrl($request, $provider));

        if ($provider === 'google') {
            $driver->scopes(['openid', 'profile', 'email']);
        }

        if ($provider === 'facebook') {
            $driver->scopes(['email', 'public_profile']);
        }

        return $driver->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        if (! $this->socialAuth->isSupportedProvider($provider)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl($this->socialAuth->redirectUrl($request, $provider))
                ->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('login')
                ->withErrors(['email' => ucfirst($provider).' sign-in failed. Please try again.']);
        }

        try {
            $user = $this->socialAuth->authenticate($provider, $socialUser, $request);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => $e->getMessage()]);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Admin accounts must sign in with email and password.']);
        }

        if ($user->status === User::STATUS_SUSPENDED) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your account is suspended. Contact support for assistance.']);
        }

        $intent = $request->session()->pull('oauth_intended_role');
        if ($intent && $this->partnerApplication->isPartnerRole($intent)) {
            if ($this->socialSignup->needsCompletion($user)) {
                $request->session()->put('social_signup_intended_role', $intent);
            } else {
                $user = $this->partnerApplication->applyIfEligible($user, $intent);
            }
        }

        if ($this->socialSignup->needsCompletion($user)) {
            $request->session()->put('social_signup_user_id', $user->id);
            $request->session()->put('open_social_signup_modal', true);
            $request->session()->forget(['social_signup_otp_sent', 'social_signup_otp_hint']);

            return redirect()->route('login');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectAfterSocialLogin($user);
    }

    private function redirectAfterSocialLogin(User $user)
    {
        if (
            in_array($user->role, PartnerApplicationService::PARTNER_ROLES, true)
            && in_array($user->status, [User::STATUS_PENDING, User::STATUS_REJECTED], true)
        ) {
            $label = $user->role === User::ROLE_CHEF ? 'chef' : 'traveler';

            return redirect()
                ->route('verification.show')
                ->with('success', 'Welcome! Complete your '.$label.' verification profile to submit for admin approval.');
        }

        return redirect()->intended(route('dashboard'));
    }
}
