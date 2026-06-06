@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">User Management</h2>
        @if(!empty($adminPermissions['users.create']) || !empty($adminPermissions['users.view']))
        <div class="page-header-actions">
            @if(!empty($adminPermissions['users.create']))
                <button type="button" class="btn btn-sm btn-outline-success page-header-action-btn" onclick="openCreateUserModal()">
                    <i class="bi bi-person-plus"></i> Create
                </button>
            @endif
            @if(!empty($adminPermissions['users.view']))
                <a class="btn btn-sm btn-success page-header-action-btn" href="{{ route('admin.users.index', ['filter' => 'pending_approvals']) }}#pending-approvals">
                    <i class="bi bi-person-check"></i> Pending
                    @if(($pendingApprovalsCount ?? 0) > 0)
                        <span class="badge bg-light text-success ms-1">{{ number_format($pendingApprovalsCount) }}</span>
                    @endif
                </a>
            @endif
        </div>
        @endif
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Review and manage all users on the platform</p>
</div>

@if($errors->has('bulk_action'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Action blocked:</strong> {{ $errors->first('bulk_action') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Filters & Search -->
<div class="dashboard-card mb-3 mb-md-4">
    <div class="card-header py-2">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form id="admin-users-filter-form" method="GET" action="{{ route('admin.users.index') }}" class="dashboard-filter-form dashboard-filter-form--inline dashboard-filter-form--wrap-sm">
        @if($filter !== '')
            <input type="hidden" name="filter" value="{{ $filter }}">
        @endif
        @if($perPage)
            <input type="hidden" name="per_page" value="{{ $perPage }}">
        @endif
        <div class="dashboard-filter-fields">
        <div class="dashboard-filter-field dashboard-filter-field--grow">
            <label class="form-label dashboard-filter-label" for="user-search">Search</label>
            <input type="text" id="user-search" name="search" value="{{ $search }}" class="form-control" placeholder="Name, email, phone, ID">
        </div>
        <div class="dashboard-filter-field">
            <label class="form-label dashboard-filter-label" for="user-role">Role</label>
            <select id="user-role" name="role" class="form-select">
                <option value="">All roles</option>
                <option value="customer" @selected($role === 'customer')>Customer</option>
                <option value="chef" @selected($role === 'chef')>Chef</option>
                <option value="traveler" @selected($role === 'traveler')>Traveler</option>
                <option value="admin" @selected($role === 'admin')>Admin</option>
            </select>
        </div>
        <div class="dashboard-filter-field">
            <label class="form-label dashboard-filter-label" for="user-status">Status</label>
            <select id="user-status" name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                <option value="suspended" @selected($status === 'suspended')>Suspended</option>
            </select>
        </div>
        </div>
        <div class="dashboard-filter-actions dashboard-filter-actions--end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div class="row g-3 g-md-4 mb-3 mb-md-4">
    <div class="col-6 col-md-3">
        <a class="stat-card stat-green d-block text-decoration-none" href="{{ route('admin.users.index', ['filter' => 'pending_approvals']) }}#pending-approvals">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value">{{ $pendingApprovalsCount }}</div>
            <div class="stat-label">Pending Approvals</div>
            <div class="stat-change small mt-2 text-warning">
                <i class="bi bi-arrow-right"></i> View pending approvals
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a class="stat-card stat-blue d-block text-decoration-none" href="{{ route('admin.users.index', ['filter' => 'pending_chefs']) }}#pending-chefs">
            <div class="stat-icon">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-value">{{ $pendingChefs->count() }}</div>
            <div class="stat-label">Pending Chefs</div>
            <div class="stat-change small mt-2 text-primary">
                <i class="bi bi-arrow-right"></i> Review chef requests
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a class="stat-card stat-green d-block text-decoration-none" href="{{ route('admin.users.index', ['filter' => 'pending_travelers']) }}#pending-travelers">
            <div class="stat-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-value">{{ $pendingTravelers->count() }}</div>
            <div class="stat-label">Pending Travelers</div>
            <div class="stat-change small mt-2 text-success">
                <i class="bi bi-arrow-right"></i> Review traveler requests
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a class="stat-card stat-blue d-block text-decoration-none" href="{{ route('admin.users.index', ['filter' => 'active_partners']) }}#all-users">
            <div class="stat-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="stat-value">{{ $activePartnersCount }}</div>
            <div class="stat-label">Active Partners</div>
            <div class="stat-change small mt-2 text-primary">
                <i class="bi bi-arrow-right"></i> View active partners
            </div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12" id="pending-approvals"></div>
</div>

<div class="row g-4">
    <!-- Pending Chefs -->
    <div class="col-md-6" id="pending-chefs">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-person-check"></i> Pending Chefs ({{ $pendingChefs->count() }})
                </h5>
            </div>
            <div class="table-responsive table-responsive-fit">
                <table class="table table-hover pending-users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingChefs as $chef)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $chef->name }}</div>
                                    @if($chef->phone)
                                        <small class="text-muted">{{ $chef->phone }}</small>
                                    @endif
                                </td>
                                <td class="text-break">{{ $chef->email }}</td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.users.show', $chef) }}">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No pending chefs</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Travelers -->
    <div class="col-md-6" id="pending-travelers">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-truck"></i> Pending Travelers ({{ $pendingTravelers->count() }})
                </h5>
            </div>
            <div class="table-responsive table-responsive-fit">
                <table class="table table-hover pending-users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingTravelers as $traveler)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $traveler->name }}</div>
                                    @if($traveler->phone)
                                        <small class="text-muted">{{ $traveler->phone }}</small>
                                    @endif
                                </td>
                                <td class="text-break">{{ $traveler->email }}</td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.users.show', $traveler) }}">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No pending travelers</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- All Users Table -->
@php
    $usersListQuery = array_filter([
        'filter' => $filter ?: null,
        'search' => $search ?: null,
        'role' => $role ?: null,
        'status' => $status ?: null,
        'per_page' => $perPage,
    ]);
    $usersListQueryNoPageFilter = array_filter([
        'search' => $search ?: null,
        'role' => $role ?: null,
        'status' => $status ?: null,
        'per_page' => $perPage,
    ]);
@endphp
<div class="dashboard-card mt-4" id="all-users">
    <div class="card-header all-users-card-header">
        <div class="all-users-header-row">
            <h5 class="card-title mb-0">
                <i class="bi bi-people"></i> {{ $filterLabel }}
            </h5>
            <div class="all-users-table-toolbar">
                <div class="all-users-toolbar-group all-users-pager-group">
                    <span class="all-users-toolbar-label">Per page</span>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ (int) $perPage }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach([10, 20, 50, 100] as $size)
                                <li>
                                    <a class="dropdown-item @if((int) $perPage === $size) active @endif"
                                       href="{{ route('admin.users.index', array_merge($usersListQuery, ['per_page' => $size])) }}#all-users">
                                        {{ $size }} per page
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @if($filter !== '')
                    <div class="all-users-toolbar-group">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.index', $usersListQueryNoPageFilter) }}#all-users">
                            <i class="bi bi-x-circle"></i> Clear filter
                        </a>
                    </div>
                @endif
                @if(!empty($adminPermissions['users.export']))
                    <div class="all-users-toolbar-group">
                        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.users.export', request()->query()) }}">
                            <i class="bi bi-download"></i> Export CSV
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if(!empty($adminPermissions['users.manage']))
    <form method="POST" action="{{ route('admin.users.bulk') }}">
        @csrf
    @endif
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        @if(!empty($adminPermissions['users.manage']))
                        <th style="width: 40px;">
                            <input type="checkbox" id="select_all_users" class="form-check-input">
                        </th>
                        @endif
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allUsers as $user)
                        <tr>
                            @if(!empty($adminPermissions['users.manage']))
                            <td>
                                @if($user->role !== 'admin')
                                    <input type="checkbox" name="selected_users[]" value="{{ $user->id }}" class="form-check-input user-checkbox">
                                @endif
                            </td>
                            @endif
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if($user->phone)
                                    <small class="text-muted">{{ $user->phone }}</small>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                                @if($user->role === 'admin')
                                    <div class="mt-1">@include('admin.partials.role-badge', ['user' => $user])</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->status === 'approved' ? 'success' : ($user->status === 'pending' ? 'warning' : ($user->status === 'suspended' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.show', $user) }}">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !empty($adminPermissions['users.manage']) ? 7 : 6 }}" class="text-center text-muted">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $allUsers->links() }}
            </div>
        </div>

        @if(!empty($adminPermissions['users.manage']))
        <div class="mt-3 d-flex flex-wrap gap-2 align-items-end">
            <div style="min-width: 220px;">
                <label class="form-label small text-muted">Bulk action</label>
                <select name="action" id="bulk_action_select" class="form-select form-select-sm" required>
                    <option value="">Select action</option>
                    <option value="approve">Approve (chefs/travelers)</option>
                    <option value="suspend">Suspend account</option>
                    <option value="block">Block account (suspend)</option>
                    <option value="activate">Activate account</option>
                    <option value="delete">Delete permanently (no linked records only)</option>
                </select>
                <small class="text-muted d-block mt-1">
                    Prefer <strong>Suspend/Block</strong> to keep orders, payments, and complaints. Delete only removes accounts with no linked data.
                </small>
            </div>
            <div class="flex-grow-1">
                <label class="form-label small text-muted">Reason (for audit trail)</label>
                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Optional reason for this action">
            </div>
            <div>
                <button type="submit" class="btn btn-sm btn-success" id="bulk_apply_btn">
                    <i class="bi bi-check2-circle"></i> Apply to selected
                </button>
            </div>
        </div>
    </form>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('select_all_users')?.addEventListener('change', function () {
    const checked = this.checked;
    document.querySelectorAll('.user-checkbox').forEach(function (cb) {
        cb.checked = checked;
    });
});

document.querySelector('form[action="{{ route('admin.users.bulk') }}"]')?.addEventListener('submit', function (e) {
    const action = document.getElementById('bulk_action_select')?.value;
    const checked = document.querySelectorAll('.user-checkbox:checked').length;

    if (!checked) {
        e.preventDefault();
        alert('Please select at least one user.');
        return;
    }

    if (action === 'delete') {
        const ok = confirm(
            'Permanent delete is only allowed for accounts with NO orders, deliveries, reviews, disputes, or meals in orders.\n\n'
            + 'Accounts with linked records will be skipped — use Suspend/Block instead.\n\n'
            + 'Continue?'
        );
        if (!ok) {
            e.preventDefault();
        }
    }

    if (action === 'suspend' || action === 'block') {
        const ok = confirm('Suspend/block the selected account(s)? They cannot sign in, but all records are kept.');
        if (!ok) {
            e.preventDefault();
        }
    }
});
</script>
@endpush

@if(!empty($adminPermissions['users.create']))
    @include('admin.users._create_modal')
@endif
@endsection
