@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>Logistics & Travelers</h2>
            <p class="text-muted mb-0 page-header-subtitle">Travelers who picked up your orders and commission per delivery</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value">{{ $totalTravelers }}</div>
            <div class="stat-label">Travelers</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-value">{{ $totalOrders }}</div>
            <div class="stat-label">Orders Picked Up</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-percent"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float) $totalCommission, 0) }}</div>
            <div class="stat-label">Total Commission ({{ $commissionRate }}%)</div>
        </div>
    </div>
</div>

@if($travelerSummaries->isNotEmpty())
    <div class="dashboard-card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-person-badge"></i> Travelers</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Traveler</th>
                        <th>Phone</th>
                        <th class="text-end">Orders</th>
                        <th class="text-end">Order Value</th>
                        <th class="text-end">Commission</th>
                        <th class="text-end">Your Net</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($travelerSummaries as $summary)
                        <tr>
                            <td class="fw-semibold">{{ $summary->traveler->name }}</td>
                            <td>{{ $summary->traveler->phone ?? '—' }}</td>
                            <td class="text-end">{{ $summary->orders_count }}</td>
                            <td class="text-end">TZS {{ number_format((float) $summary->revenue, 2) }}</td>
                            <td class="text-end text-danger">TZS {{ number_format((float) $summary->commission, 2) }}</td>
                            <td class="text-end fw-bold">TZS {{ number_format((float) $summary->net, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('chef.logistics.index', ['traveler_id' => $summary->traveler->id]) }}" class="btn btn-sm btn-outline-primary">
                                    View orders
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="dashboard-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-list-check"></i> Orders by Traveler</h5>
        @if($travelers->isNotEmpty())
            <form method="GET" action="{{ route('chef.logistics.index') }}" class="d-flex gap-2 align-items-center">
                <select name="traveler_id" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                    <option value="">All travelers</option>
                    @foreach($travelers as $traveler)
                        <option value="{{ $traveler->id }}" @selected((string) $selectedTravelerId === (string) $traveler->id)>
                            {{ $traveler->name }}
                        </option>
                    @endforeach
                </select>
                @if($selectedTravelerId)
                    <a href="{{ route('chef.logistics.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Traveler</th>
                    <th>Delivery Status</th>
                    <th class="text-end">Order Value</th>
                    <th class="text-end">Commission</th>
                    <th class="text-end">Your Net</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $row)
                    <tr>
                        <td><strong>#{{ $row->order->id }}</strong></td>
                        <td>{{ $row->order->customer->name }}</td>
                        <td>
                            <span class="fw-semibold">{{ $row->traveler?->name ?? '—' }}</span>
                            @if($row->traveler?->phone)
                                <small class="text-muted d-block">{{ $row->traveler->phone }}</small>
                            @endif
                        </td>
                        <td>
                            @php $dStatus = $row->delivery?->status ?? '—'; @endphp
                            <span class="badge badge-{{ match($dStatus) {
                                'assigned' => 'primary',
                                'picked_up' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            } }}">
                                {{ ucfirst(str_replace('_', ' ', $dStatus)) }}
                            </span>
                        </td>
                        <td class="text-end">TZS {{ number_format((float) $row->subtotal, 2) }}</td>
                        <td class="text-end text-danger">TZS {{ number_format((float) $row->commission, 2) }}</td>
                        <td class="text-end fw-bold">TZS {{ number_format((float) $row->net, 2) }}</td>
                        <td>{{ $row->order->created_at->format('M d, Y') }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('chef.orders.show', $row->order) }}">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            No orders have been picked up by a traveler yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
