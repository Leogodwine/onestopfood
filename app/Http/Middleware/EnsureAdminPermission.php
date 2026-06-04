<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AdminAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function __construct(private AdminAccessService $adminAccess)
    {
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_ADMIN) {
            abort(403);
        }

        foreach ($permissions as $permission) {
            if ($this->adminAccess->can($user, $permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
