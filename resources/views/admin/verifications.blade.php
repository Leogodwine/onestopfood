@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Chef & Traveler Verification</h2>
    <p class="text-muted mb-0 page-header-subtitle">Review documents, certificates, and images submitted during partner verification</p>
</div>

<div class="row g-3 g-md-4 mb-3 mb-md-4">
    <div class="col-6 col-md-6">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-label">Pending Documents</div>
        </div>
    </div>
    <div class="col-6 col-md-6">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div class="stat-value">{{ $expiringSoonCount }}</div>
            <div class="stat-label">Expiring Within 30 Days</div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3 mb-md-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-filter"></i> Filters
        </h5>
    </div>
    <form method="GET" action="{{ route('admin.verifications.index') }}" class="dashboard-filter-form dashboard-filter-form--inline dashboard-filter-form--wrap-sm">
        <div class="dashboard-filter-fields">
        <div class="dashboard-filter-field">
            <label class="form-label dashboard-filter-label" for="verification-status">Status</label>
            <select id="verification-status" name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
            </select>
        </div>
        <div class="dashboard-filter-field">
            <label class="form-label dashboard-filter-label" for="verification-role">Role</label>
            <select id="verification-role" name="role" class="form-select">
                <option value="">All</option>
                <option value="chef" @selected($role === 'chef')>Chef</option>
                <option value="traveler" @selected($role === 'traveler')>Traveler</option>
            </select>
        </div>
        <div class="dashboard-filter-field dashboard-filter-field--grow">
            <label class="form-label dashboard-filter-label" for="verification-type">Type</label>
            <input type="text" id="verification-type" name="type" value="{{ $type }}" class="form-control" placeholder="e.g. selfie, license">
        </div>
        </div>
        <div class="dashboard-filter-actions dashboard-filter-actions--end">
            <button type="submit" class="btn btn-primary">
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
                    <th>Document</th>
                    <th>Preview</th>
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
                            <a href="{{ route('admin.users.show', $doc->user) }}" class="fw-semibold text-decoration-none">
                                {{ $doc->user->name }}
                            </a>
                            <small class="text-muted d-block">{{ $doc->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge badge-primary text-capitalize">{{ $doc->user->role }}</span>
                        </td>
                        <td>{{ \App\Services\VerificationDocumentSync::typeLabel($doc->type) }}</td>
                        <td>
                            @if($doc->file_path)
                                @if($doc->isImage())
                                    <a href="{{ $doc->url() }}" target="_blank" rel="noopener">
                                        <img src="{{ $doc->url() }}" alt="{{ $doc->type }}" class="rounded border" style="width: 72px; height: 72px; object-fit: cover;">
                                    </a>
                                @else
                                    <a href="{{ $doc->url() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-file-earmark-arrow-down"></i> View file
                                    </a>
                                @endif
                            @else
                                <span class="text-muted small">No file</span>
                            @endif
                        </td>
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
                        <td>{{ $doc->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($doc->status === 'pending')
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
                            @else
                                <span class="text-muted small">Reviewed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No verification documents found. Documents appear here after chefs or travelers complete the verification form.
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
