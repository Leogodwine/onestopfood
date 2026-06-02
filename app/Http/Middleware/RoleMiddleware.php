<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            // Let the auth middleware redirect guests to login as usual
            return redirect()->route('login', ['session_expired' => 1]);
        }

        $hasRequiredRole = in_array($user->role, $roles, true);

        // Some installs may have legacy admin rows with NULL/empty status.
        // Treat admin as approved unless explicitly suspended/rejected/pending.
        $isApproved = ($user->status === User::STATUS_APPROVED)
            || ($user->role === User::ROLE_ADMIN && ($user->status === null || $user->status === ''));

        if (!$hasRequiredRole || !$isApproved) {
            // Log unauthorized access attempts, especially for admin-only areas
            try {
                \App\Models\LoginActivity::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'is_admin' => $user->role === \App\Models\User::ROLE_ADMIN,
                    'successful' => false,
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'device_fingerprint' => $request->input('device_fingerprint'),
                    'reason' => 'unauthorized_access',
                ]);
            } catch (\Throwable $e) {
                // Avoid breaking requests if logging fails
            }

            abort(403);
        }

        return $next($request);
    }
}

