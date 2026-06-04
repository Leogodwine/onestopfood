<?php

namespace App\Http\Controllers;

use App\Models\AdminAction;
use App\Models\LoginActivity;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminSecurityController extends Controller
{
    public function index(Request $request)
    {
        $loginFilter = (string) $request->query('login_filter', '');
        $loginQuery = LoginActivity::with('user')->latest('logged_at');

        if ($loginFilter === 'failed') {
            $loginQuery->where('successful', false);
        } elseif ($loginFilter === 'success') {
            $loginQuery->where('successful', true);
        } elseif ($loginFilter === 'admin') {
            $loginQuery->where('is_admin', true);
        }

        $failedThreshold = max(1, (int) SystemSetting::getValue('max_failed_login_attempts', 5));

        $suspiciousUsers = User::query()
            ->where('failed_login_attempts', '>=', max(3, $failedThreshold - 2))
            ->orWhere(function ($query) {
                $query->where('status', User::STATUS_SUSPENDED)
                    ->where('failed_login_attempts', '>', 0);
            })
            ->orderByDesc('failed_login_attempts')
            ->limit(20)
            ->get();

        $securitySettings = SystemSetting::query()
            ->where('group', 'Security')
            ->orderBy('key')
            ->get();

        return view('admin.security.index', [
            'loginActivities' => $loginQuery->paginate(25, ['*'], 'login_page')->withQueryString(),
            'adminActions' => AdminAction::with(['admin', 'targetUser'])
                ->latest()
                ->paginate(25, ['*'], 'audit_page')
                ->withQueryString(),
            'suspiciousUsers' => $suspiciousUsers,
            'failedThreshold' => $failedThreshold,
            'loginFilter' => $loginFilter,
            'securitySettings' => $securitySettings,
        ]);
    }

    public function blockUser(Request $request, User $user)
    {
        if ($user->role === User::ROLE_ADMIN) {
            return back()->withErrors(['security' => 'Admin accounts cannot be blocked from this panel.']);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->status = User::STATUS_SUSPENDED;
        $user->save();

        $this->logAdminAction('security_block_user', $user, $data['reason'] ?? 'Blocked from security panel');

        return back()->with('status', 'Account blocked: '.$user->email);
    }

    public function resetLoginAttempts(User $user)
    {
        $user->failed_login_attempts = 0;

        if ($user->status === User::STATUS_SUSPENDED && $user->role !== User::ROLE_ADMIN) {
            $user->status = User::STATUS_APPROVED;
        }

        $user->save();

        $this->logAdminAction('security_reset_login_attempts', $user);

        return back()->with('status', 'Login attempts reset for '.$user->email);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'settings' => ['array'],
        ]);

        foreach ($data['settings'] ?? [] as $key => $value) {
            $setting = SystemSetting::query()->where('key', $key)->first();

            if (! $setting || $setting->group !== 'Security') {
                continue;
            }

            $setting->value = $value;
            $setting->save();
        }

        Cache::forget('settings.site_name');

        $this->logAdminAction('security_settings_update');

        return back()->with('status', 'Security settings updated.');
    }

    private function logAdminAction(string $action, ?User $targetUser = null, ?string $reason = null, array $meta = []): void
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== User::ROLE_ADMIN) {
            return;
        }

        try {
            AdminAction::create([
                'admin_id' => $admin->id,
                'target_user_id' => $targetUser?->id,
                'action' => $action,
                'reason' => $reason,
                'meta' => $meta ?: null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable) {
            //
        }
    }
}
