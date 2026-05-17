@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>Delivery & Logistics</h2>
    <p class="text-muted mb-0">Monitor active deliveries across the platform</p>
</div>

<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form method="GET" action="{{ route('admin.logistics.index') }}" class="row g-3 p-2">
        <div class="col-md-4">
            <label class="form-label small text-muted">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(['unassigned','assigned','picked_up','delivered','failed'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.logistics.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-map"></i> Active Deliveries</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Traveler</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $delivery)
                    <tr>
                        <td>#{{ str_pad($delivery->order_id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $delivery->order->customer->name ?? 'N/A' }}</td>
                        <td>{{ $delivery->traveler->name ?? 'Unassigned' }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$delivery->status)) }}</td>
                        <td>{{ $delivery->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No deliveries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        {{ $deliveries->links() }}
    </div>
</div>
@endsection

