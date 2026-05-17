@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>Dispute Center</h2>
    <p class="text-muted mb-0">Review and resolve customer, chef, and traveler disputes</p>
</div>

<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form method="GET" action="{{ route('admin.disputes.index') }}" class="row g-3 p-2">
        <div class="col-md-4">
            <label class="form-label small text-muted">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(['open','in_review','resolved','escalated'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-exclamation-octagon"></i> Disputes</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Order</th>
                    <th>Complainant</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($disputes as $dispute)
                    <tr>
                        <td>#{{ $dispute->id }}</td>
                        <td>@if($dispute->order_id)#{{ str_pad($dispute->order_id, 6, '0', STR_PAD_LEFT) }}@else N/A @endif</td>
                        <td>{{ $dispute->createdBy->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$dispute->category ?? 'other')) }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$dispute->status)) }}</td>
                        <td>{{ $dispute->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No disputes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        {{ $disputes->links() }}
    </div>
</div>
@endsection

