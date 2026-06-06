<?php

namespace App\Http\Controllers;

use App\Models\ChefProfile;
use App\Models\TravelerProfile;
use App\Models\User;
use App\Models\UserVerificationDocument;
use App\Models\AdminAction;
use App\Services\AdminAccessService;
use App\Services\UserAccountGuardService;
use App\Support\PasswordRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'canCreateAdmin' => $request->user()->adminCan('users.create_admin'),
        ]);
    }

    public function createUser(Request $request): RedirectResponse
    {
        if (! $request->user()->adminCan('users.create')) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['bulk_action' => 'You do not have permission to create users.'], 'default');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('open_create_user_modal', true);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $access = app(AdminAccessService::class);
        $allowedRoles = [User::ROLE_CUSTOMER, User::ROLE_CHEF, User::ROLE_TRAVELER];

        if ($admin->adminCan('users.create_admin')) {
            $allowedRoles[] = User::ROLE_ADMIN;
        }

        \App\Support\PhoneNumber::mergeIntoRequest($request);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_country_code' => ['required', 'string', Rule::in(array_keys(\App\Support\PhoneNumber::countries()))],
            'phone_number' => \App\Support\PhoneNumber::nationalNumberRules('phone_country_code'),
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'role' => ['required', Rule::in($allowedRoles)],
            'status' => ['nullable', Rule::in([User::STATUS_PENDING, User::STATUS_APPROVED])],
            'password' => PasswordRules::forRegistration(),
        ];

        if ($admin->adminCan('users.create_admin')) {
            $rules['admin_title'] = ['nullable', Rule::in(array_keys($access->titles()))];
        }

        $data = $request->validateWithBag('create_user', $rules, array_merge(
            PasswordRules::validationMessages(),
            \App\Support\PhoneNumber::validationMessages()
        ));

        if ($data['role'] === User::ROLE_ADMIN && ! $admin->adminCan('users.create_admin')) {
            abort(403, 'Only system administrators can create admin accounts.');
        }

        $status = in_array($data['role'], [User::ROLE_CHEF, User::ROLE_TRAVELER], true)
            ? ($data['status'] ?? User::STATUS_APPROVED)
            : User::STATUS_APPROVED;

        $adminTitle = null;
        $isSuperAdmin = false;

        if ($data['role'] === User::ROLE_ADMIN) {
            $adminTitle = $data['admin_title'] ?? User::ADMIN_TITLE_MANAGER;
            $isSuperAdmin = $adminTitle === User::ADMIN_TITLE_SYSTEM_ADMINISTRATOR;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'status' => $status,
            'locale' => in_array(session('locale'), ['en', 'sw'], true) ? session('locale') : 'en',
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'approved_at' => $status === User::STATUS_APPROVED ? now() : null,
            'admin_title' => $adminTitle,
            'is_super_admin' => $isSuperAdmin,
        ]);

        if ($data['role'] === User::ROLE_CHEF) {
            ChefProfile::create(['user_id' => $user->id]);
        }

        if ($data['role'] === User::ROLE_TRAVELER) {
            TravelerProfile::create(['user_id' => $user->id]);
        }

        $this->logAdminAction('create_user', $user, null, [
            'role' => $user->role,
            'status' => $user->status,
            'admin_title' => $user->admin_title,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'User account created successfully.');
    }

    public function showUser(User $user)
    {
        $user->load(['chefProfile', 'travelerProfile', 'locations', 'socialAccounts', 'accountActionRequests']);
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
            'pendingDeletionRequest' => $user->accountActionRequests
                ->where('action', \App\Models\AccountActionRequest::ACTION_DELETION)
                ->where('status', \App\Models\AccountActionRequest::STATUS_PENDING)
                ->first(),
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
        app(\App\Services\AccountLifecycleNotifier::class)->accountApproved($user->fresh());

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
        app(\App\Services\AccountLifecycleNotifier::class)->accountRejected($user->fresh(), $request->input('reason'));

        return back()->with('status', 'User rejected');
    }

    public function suspend(User $user)
    {
        app(UserAccountGuardService::class)->suspend($user, null, User::SUSPENDED_BY_ADMIN);
        $this->logAdminAction('suspend_user', $user);

        return back()->with('status', 'User suspended');
    }

    public function unsuspend(User $user)
    {
        $user->update([
            'status' => User::STATUS_APPROVED,
            'suspended_by' => null,
            'deactivated_at' => null,
        ]);
        $this->logAdminAction('unsuspend_user', $user);

        return back()->with('status', 'User unsuspended');
    }

    public function approveDeletionRequest(Request $request, \App\Models\AccountActionRequest $accountActionRequest)
    {
        if (! $request->user()->adminCan('users.manage')) {
            abort(403);
        }

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(UserAccountGuardService::class)->approveDeletionRequest(
                $accountActionRequest,
                $request->user(),
                $data['admin_notes'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['deletion' => $e->getMessage()]);
        }

        return back()->with('status', 'Account permanently deleted.');
    }

    public function rejectDeletionRequest(Request $request, \App\Models\AccountActionRequest $accountActionRequest)
    {
        if (! $request->user()->adminCan('users.manage')) {
            abort(403);
        }

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        app(UserAccountGuardService::class)->rejectDeletionRequest(
            $accountActionRequest,
            $request->user(),
            $data['admin_notes'] ?? null,
        );

        return back()->with('status', 'Deletion request rejected.');
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
                        app(\App\Services\AccountLifecycleNotifier::class)->accountApproved($user->fresh());
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

        if ($admin->role !== User::ROLE_ADMIN || ! $admin->adminCan('users.impersonate')) {
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
