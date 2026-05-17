<?php

namespace App\Http\Middleware;

use Closure;
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
            abort(403);
        }

        if (!in_array($user->role, $roles, true) || $user->status !== 'approved') {
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

