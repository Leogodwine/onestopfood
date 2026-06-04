@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>Security Management</h2>
    <p class="text-muted mb-0">Manage permissions, monitor login activity, block suspicious accounts, and review security logs</p>
</div>

@if($errors->has('security'))
    <div class="alert alert-danger">{{ $errors->first('security') }}</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="dashboard-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-shield-lock"></i> Security Settings</h5>
            </div>
            <form method="POST" action="{{ route('admin.security.settings') }}">
                @csrf
                <div class="card-body">
                    @forelse($securitySettings as $setting)
                        <div class="mb-3">
                            <label class="form-label">{{ $setting->label }}</label>
                            @if($setting->type === 'boolean')
                                <select name="settings[{{ $setting->key }}]" class="form-select form-select-sm">
                                    <option value="true" @selected($setting->value === 'true' || $setting->value === '1')>Enabled</option>
                                    <option value="false" @selected($setting->value === 'false' || $setting->value === '0')>Disabled</option>
                                </select>
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control form-control-sm">
                            @endif
                            @if($setting->description)
                                <small class="text-muted">{{ $setting->description }}</small>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No security settings configured.</p>
                    @endforelse
                </div>
                @if($securitySettings->isNotEmpty())
                    <div class="card-footer bg-light text-end">
                        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="dashboard-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle"></i> Suspicious Accounts</h5>
                <span class="badge bg-warning text-dark">Threshold: {{ $failedThreshold }} failed attempts</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Failed Attempts</th>
                            <th>Last Login</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suspiciousUsers as $user)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td><span class="badge bg-{{ $user->status === 'suspended' ? 'danger' : 'secondary' }}">{{ ucfirst($user->status) }}</span></td>
                                <td>{{ $user->failed_login_attempts }}</td>
                                <td class="small">{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if($user->role !== 'admin')
                                            <form method="POST" action="{{ route('admin.security.block', $user) }}" onsubmit="return confirm('Block this account?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Block</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.security.reset-login', $user) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Reset</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No suspicious accounts detected.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-box-arrow-in-right"></i> Login Activity</h5>
        <form method="GET" class="d-flex gap-2">
            <select name="login_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All attempts</option>
                <option value="success" @selected($loginFilter === 'success')>Successful</option>
                <option value="failed" @selected($loginFilter === 'failed')>Failed</option>
                <option value="admin" @selected($loginFilter === 'admin')>Admin logins</option>
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Email</th>
                    <th>User</th>
                    <th>Result</th>
                    <th>Reason</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loginActivities as $activity)
                    <tr>
                        <td class="small">{{ $activity->logged_at?->format('M d, Y H:i:s') }}</td>
                        <td>{{ $activity->email }}</td>
                        <td>{{ $activity->user?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $activity->successful ? 'success' : 'danger' }}">
                                {{ $activity->successful ? 'Success' : 'Failed' }}
                            </span>
                            @if($activity->is_admin)
                                <span class="badge bg-dark">Admin</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $activity->reason ?? '—' }}</td>
                        <td class="small">{{ $activity->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No login activity recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($loginActivities->hasPages())
        <div class="card-footer">{{ $loginActivities->links() }}</div>
    @endif
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-journal-check"></i> Security &amp; Admin Audit Log</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Reason</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adminActions as $action)
                    <tr>
                        <td class="small">{{ $action->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $action->admin?->name ?? '—' }}</td>
                        <td><code>{{ $action->action }}</code></td>
                        <td>{{ $action->targetUser?->email ?? '—' }}</td>
                        <td class="small text-muted">{{ $action->reason ?? '—' }}</td>
                        <td class="small">{{ $action->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No admin actions logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($adminActions->hasPages())
        <div class="card-footer">{{ $adminActions->links() }}</div>
    @endif
</div>
@endsection
