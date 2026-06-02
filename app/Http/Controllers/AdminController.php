<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserVerificationDocument;
use App\Models\AdminAction;
use App\Services\UserAccountGuardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $filter = (string) $request->query('filter', '');
        $search = (string) $request->query('search', '');
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $pendingChefs = User::query()
            ->where('role', User::ROLE_CHEF)
            ->where('status', User::STATUS_PENDING)
            ->with('chefProfile')
            ->latest()
            ->get();

        $pendingTravelers = User::query()
            ->where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_PENDING)
            ->with('travelerProfile')
            ->latest()
            ->get();

        $activePartnersCount = User::query()
            ->whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER])
            ->where('status', User::STATUS_APPROVED)
            ->count();

        $allUsersQuery = User::query()
            ->with(['chefProfile', 'travelerProfile'])
            ->latest();

        $filterLabel = 'All Users';
        if ($filter === 'pending_approvals') {
            $filterLabel = 'Pending Approvals';
            $allUsersQuery
                ->where('status', User::STATUS_PENDING)
                ->whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER]);
        } elseif ($filter === 'pending_chefs') {
            $filterLabel = 'Pending Chefs';
            $allUsersQuery
                ->where('status', User::STATUS_PENDING)
                ->where('role', User::ROLE_CHEF);
        } elseif ($filter === 'pending_travelers') {
            $filterLabel = 'Pending Travelers';
            $allUsersQuery
                ->where('status', User::STATUS_PENDING)
                ->where('role', User::ROLE_TRAVELER);
        } elseif ($filter === 'active_partners') {
            $filterLabel = 'Active Partners';
            $allUsersQuery
                ->where('status', User::STATUS_APPROVED)
                ->whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER]);
        }

        if ($search !== '') {
            $allUsersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if ($role !== '') {
            $allUsersQuery->where('role', $role);
        }

        if ($status !== '') {
            $allUsersQuery->where('status', $status);
        }

        $allUsers = $allUsersQuery->paginate($perPage)->withQueryString();

        return view('admin.index', [
            'pendingChefs' => $pendingChefs,
            'pendingTravelers' => $pendingTravelers,
            'activePartnersCount' => $activePartnersCount,
            'pendingApprovalsCount' => $pendingChefs->count() + $pendingTravelers->count(),
            'allUsers' => $allUsers,
            'filter' => $filter,
            'filterLabel' => $filterLabel,
            'perPage' => $perPage,
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ]);
    }

    public function showUser(User $user)
    {
        $user->load(['chefProfile', 'travelerProfile', 'locations', 'socialAccounts']);
        $documents = UserVerificationDocument::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $guard = app(UserAccountGuardService::class);

        return view('admin.user-detail', [
            'user' => $user,
            'documents' => $documents,
            'accountDependencies' => $guard->dependencies($user),
            'canHardDelete' => $guard->canHardDelete($user),
            'dependencyMessage' => $guard->dependencyMessage($user),
        ]);
    }

    public function approve(User $user)
    {
        if (!in_array($user->role, [User::ROLE_CHEF, User::ROLE_TRAVELER], true)) {
            return back()->withErrors(['error' => 'Only chefs and travelers require approval']);
        }

        $user->update([
            'status' => User::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
        $this->logAdminAction('approve_user', $user);

        return back()->with('status', 'User approved successfully');
    }

    public function reject(User $user, Request $request)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!in_array($user->role, [User::ROLE_CHEF, User::ROLE_TRAVELER], true)) {
            return back()->withErrors(['error' => 'Only chefs and travelers require approval']);
        }

        $user->update([
            'status' => User::STATUS_REJECTED,
        ]);
        $this->logAdminAction('reject_user', $user, $request->input('reason'));

        return back()->with('status', 'User rejected');
    }

    public function suspend(User $user)
    {
        $user->update([
            'status' => User::STATUS_SUSPENDED,
        ]);
        $this->logAdminAction('suspend_user', $user);

        return back()->with('status', 'User suspended');
    }

    public function unsuspend(User $user)
    {
        $user->update([
            'status' => User::STATUS_APPROVED,
        ]);
        $this->logAdminAction('unsuspend_user', $user);

        return back()->with('status', 'User unsuspended');
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'selected_users' => ['required', 'array'],
            'selected_users.*' => ['integer', 'exists:users,id'],
            'action' => ['required', 'string', 'in:approve,suspend,block,activate,delete'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = $request->user();
        $guard = app(UserAccountGuardService::class);

        $users = User::whereIn('id', $data['selected_users'])->get();
        $affected = 0;
        $blockedDeletes = [];

        foreach ($users as $user) {
            if ($admin->id === $user->id) {
                continue; // never modify self in bulk
            }

            $action = $data['action'] === 'block' ? 'suspend' : $data['action'];

            switch ($action) {
                case 'approve':
                    if (in_array($user->role, [User::ROLE_CHEF, User::ROLE_TRAVELER], true)) {
                        $user->update([
                            'status' => User::STATUS_APPROVED,
                            'approved_at' => now(),
                        ]);
                        $affected++;
                        $this->logAdminAction('bulk_approve_user', $user, $data['reason'] ?? null);
                    }
                    break;
                case 'suspend':
                    $guard->suspend($user);
                    $affected++;
                    $this->logAdminAction(
                        $data['action'] === 'block' ? 'bulk_block_user' : 'bulk_suspend_user',
                        $user,
                        $data['reason'] ?? null
                    );
                    break;
                case 'activate':
                    $user->update([
                        'status' => User::STATUS_APPROVED,
                    ]);
                    $affected++;
                    $this->logAdminAction('bulk_activate_user', $user, $data['reason'] ?? null);
                    break;
                case 'delete':
                    if ($user->role === User::ROLE_ADMIN) {
                        break;
                    }

                    if (! $guard->canHardDelete($user)) {
                        $blockedDeletes[] = "{$user->email} ({$guard->dependencyMessage($user)})";
                        break;
                    }

                    try {
                        $this->logAdminAction('bulk_delete_user', $user, $data['reason'] ?? null, ['email' => $user->email]);
                        $guard->deleteIfAllowed($user);
                        $affected++;
                    } catch (\Throwable $e) {
                        $blockedDeletes[] = $user->email;
                        report($e);
                    }
                    break;
            }
        }

        $message = "Bulk action completed. {$affected} user(s) updated.";

        if ($blockedDeletes !== []) {
            return back()
                ->with('status', $message)
                ->withErrors([
                    'bulk_action' => 'These accounts were not deleted because they have linked records. Use Suspend/Block instead: '
                        . implode('; ', $blockedDeletes),
                ]);
        }

        return back()->with('status', $message);
    }

    public function export(Request $request)
    {
        $filter = (string) $request->query('filter', '');
        $search = (string) $request->query('search', '');
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        $query = User::query()->with(['chefProfile', 'travelerProfile'])->latest();

        if ($filter === 'pending_approvals') {
            $query
                ->where('status', User::STATUS_PENDING)
                ->whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER]);
        } elseif ($filter === 'pending_chefs') {
            $query
                ->where('status', User::STATUS_PENDING)
                ->where('role', User::ROLE_CHEF);
        } elseif ($filter === 'pending_travelers') {
            $query
                ->where('status', User::STATUS_PENDING)
                ->where('role', User::ROLE_TRAVELER);
        } elseif ($filter === 'active_partners') {
            $query
                ->where('status', User::STATUS_APPROVED)
                ->whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER]);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if ($role !== '') {
            $query->where('role', $role);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $fileName = 'users_export_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Registered At']);

            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $user) {
                    $registeredAt = $user->created_at
                        ? $user->created_at->format('Y-m-d H:i:s')
                        : '';

                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->phone,
                        $user->role,
                        $user->status,
                        $registeredAt !== '' ? "\t{$registeredAt}" : '',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function impersonate(Request $request, User $user)
    {
        $admin = $request->user();

        if ($admin->role !== User::ROLE_ADMIN || !$admin->is_super_admin) {
            abort(403);
        }

        if ($user->id === $admin->id) {
            return back()->withErrors(['error' => 'You cannot impersonate yourself.']);
        }

        $request->session()->put('impersonator_id', $admin->id);
        $request->session()->put('impersonated_user_id', $user->id);

        $this->logAdminAction('impersonate_start', $user, null, [
            'impersonator_id' => $admin->id,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'You are now impersonating ' . $user->name . '.');
    }

    public function stopImpersonating(Request $request)
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        $impersonatedId = $request->session()->pull('impersonated_user_id');

        if (!$impersonatorId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($impersonatorId);

        if (!$admin) {
            return redirect()->route('dashboard');
        }

        $this->logAdminAction('impersonate_stop', $impersonatedId ? User::find($impersonatedId) : null, null, [
            'impersonator_id' => $admin->id,
        ]);

        Auth::login($admin);

        return redirect()->route('admin.users.index')->with('status', 'You have stopped impersonating users.');
    }

    private function logAdminAction(string $action, ?User $targetUser = null, ?string $reason = null, array $meta = []): void
    {
        $admin = Auth::user();

        if (!$admin || $admin->role !== User::ROLE_ADMIN) {
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
        } catch (\Throwable $e) {
            // Audit logging should not break primary flow
        }
    }
}
