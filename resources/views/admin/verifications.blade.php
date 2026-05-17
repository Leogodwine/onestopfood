@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>Chef & Traveler Verification</h2>
    <p class="text-muted mb-0">Review and approve verification documents for partners</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-label">Pending Documents</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div class="stat-value">{{ $expiringSoonCount }}</div>
            <div class="stat-label">Expiring Within 30 Days</div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-filter"></i> Filters
        </h5>
    </div>
    <form method="GET" action="{{ route('admin.verifications.index') }}" class="row g-3 p-2">
        <div class="col-md-3">
            <label class="form-label small text-muted">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted">Role</label>
            <select name="role" class="form-select">
                <option value="">All</option>
                <option value="chef" @selected($role === 'chef')>Chef</option>
                <option value="traveler" @selected($role === 'traveler')>Traveler</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted">Type</label>
            <input type="text" name="type" value="{{ $type }}" class="form-control" placeholder="e.g. nida, license">
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.verifications.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-file-earmark-check"></i> Verification Queue
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Type</th>
                    <th>Document No</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $doc->user->name }}</div>
                            <small class="text-muted">{{ $doc->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge badge-primary text-capitalize">{{ $doc->user->role }}</span>
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</td>
                        <td>{{ $doc->document_no ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $doc->status === 'approved' ? 'success' : ($doc->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($doc->status) }}
                            </span>
                        </td>
                        <td>
                            @if($doc->expires_at)
                                @php $daysLeft = now()->diffInDays($doc->expires_at, false); @endphp
                                <span class="small {{ $daysLeft <= 30 ? 'text-danger' : 'text-muted' }}">
                                    {{ $doc->expires_at->format('M d, Y') }}
                                    @if($daysLeft <= 30)
                                        <br><strong>Expiring soon</strong>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td>{{ $doc->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <form method="POST" action="{{ route('admin.verifications.approve', $doc) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.verifications.reject', $doc) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger w-100">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No verification documents found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        {{ $documents->links() }}
    </div>
</div>
@endsection

