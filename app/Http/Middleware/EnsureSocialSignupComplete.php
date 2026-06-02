<?php

namespace App\Http\Middleware;

use App\Services\SocialSignupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSocialSignupComplete
{
    public function __construct(
        private readonly SocialSignupService $socialSignup
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $this->socialSignup->needsCompletion($user)) {
            if (! $request->routeIs('social.signup.*', 'logout', 'login')) {
                $request->session()->put('social_signup_user_id', $user->id);
                $request->session()->put('open_social_signup_modal', true);
                Auth::logout();

                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
