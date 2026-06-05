@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Geospatial Zones</h2>
    <p class="text-muted mb-0 page-header-subtitle">Manage service zones and delivery fees</p>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-plus-circle"></i> Add Zone</h5>
            </div>
            <form method="POST" action="{{ route('admin.zones.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label small text-muted">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Service Type</label>
                    <input type="text" name="service_type" class="form-control form-control-sm" placeholder="delivery, pickup">
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Base Fee (TZS)</label>
                    <input type="number" name="base_fee" step="0.01" min="0" class="form-control form-control-sm">
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Traveler Capacity</label>
                    <input type="number" name="traveler_capacity" min="0" class="form-control form-control-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Priority</label>
                    <input type="number" name="priority" min="0" value="0" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-check2-circle"></i> Create Zone
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-geo-alt"></i> Existing Zones</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Service</th>
                            <th>Base Fee</th>
                            <th>Capacity</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                            <tr>
                                <td>{{ $zone->name }}</td>
                                <td>{{ $zone->service_type ?? '-' }}</td>
                                <td>TZS {{ number_format((float)$zone->base_fee, 2) }}</td>
                                <td>{{ $zone->traveler_capacity ?? '-' }}</td>
                                <td>{{ $zone->priority }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No zones defined yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

