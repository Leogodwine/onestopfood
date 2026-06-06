<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountUsable
{
    /** @var list<string> */
    private array $allowedWhenSelfDeactivated = [
        'account.settings',
        'account.location.update',
        'account.deactivate',
        'account.reactivate',
        'account.deletion.request',
        'account.deletion.cancel',
        'profile.show',
        'profile.edit',
        'profile.update',
        'profile.password.update',
        'profile.avatar',
        'logout',
        'login',
        'login.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->role === User::ROLE_ADMIN) {
            return $next($request);
        }

        if ($user->status === User::STATUS_SUSPENDED && $user->suspended_by === User::SUSPENDED_BY_ADMIN) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => __('account.admin_suspended_desc')]);
        }

        if ($user->isSelfDeactivated()) {
            $routeName = $request->route()?->getName();
            if ($routeName && ! in_array($routeName, $this->allowedWhenSelfDeactivated, true)) {
                return redirect()->route('account.settings')
                    ->with('status', __('account.reactivate_desc'));
            }
        }

        return $next($request);
    }
}
