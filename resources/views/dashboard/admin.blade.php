@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Admin Dashboard</h2>
            <p class="text-muted mb-0">Overview of your platform statistics and management</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-success" href="{{ route('admin.users.index') }}">
                <i class="bi bi-person-check"></i> Review Pending Approvals
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards - Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-green d-block text-decoration-none" href="{{ route('admin.users.index') }}#all-users">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">Total Users</div>
            <div class="stat-change text-success small mt-2">
                <i class="bi bi-arrow-up"></i> All registered users
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-blue d-block text-decoration-none" href="{{ route('admin.users.index', ['role' => 'chef', 'status' => 'approved']) }}#all-users">
            <div class="stat-icon">
                <i class="bi bi-egg-fried"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_chefs']) }}</div>
            <div class="stat-label">Total Chefs</div>
            <div class="stat-change text-primary small mt-2">
                <i class="bi bi-check-circle"></i> Active chefs
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-green d-block text-decoration-none" href="{{ route('admin.orders.index') }}">
            <div class="stat-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-change text-success small mt-2">
                <i class="bi bi-graph-up"></i> All time orders
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-blue d-block text-decoration-none" href="{{ route('admin.meals.index', ['availability' => 'available']) }}">
            <div class="stat-icon">
                <i class="bi bi-utensils"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_meals']) }}</div>
            <div class="stat-label">Total Meals</div>
            <div class="stat-change text-primary small mt-2">
                <i class="bi bi-menu-button"></i> Available meals
            </div>
        </a>
    </div>
</div>

<!-- Statistics Cards - Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-green d-block text-decoration-none" href="{{ route('admin.users.index', ['role' => 'customer', 'status' => 'approved']) }}#all-users">
            <div class="stat-icon">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
            <div class="stat-label">Customers</div>
            <div class="stat-change text-success small mt-2">
                <i class="bi bi-people-fill"></i> Active customers
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-blue d-block text-decoration-none" href="{{ route('admin.users.index', ['role' => 'traveler', 'status' => 'approved']) }}#all-users">
            <div class="stat-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_travelers']) }}</div>
            <div class="stat-label">Travelers</div>
            <div class="stat-change text-primary small mt-2">
                <i class="bi bi-truck-flatbed"></i> Delivery partners
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-green d-block text-decoration-none" href="{{ route('admin.users.index', ['filter' => 'pending_approvals']) }}#pending-approvals">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['pending_approvals']) }}</div>
            <div class="stat-label">Pending Approvals</div>
            @if($stats['pending_approvals'] > 0)
                <div class="stat-change text-warning small mt-2">
                    <i class="bi bi-exclamation-triangle"></i> Requires attention
                </div>
            @else
                <div class="stat-change text-success small mt-2">
                    <i class="bi bi-check-circle"></i> All clear
                </div>
            @endif
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a class="stat-card stat-blue d-block text-decoration-none" href="{{ route('admin.users.index', ['filter' => 'active_partners']) }}#all-users">
            <div class="stat-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_chefs'] + $stats['total_travelers']) }}</div>
            <div class="stat-label">Active Partners</div>
            <div class="stat-change text-primary small mt-2">
                <i class="bi bi-handshake"></i> Chefs & travelers
            </div>
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title">
            <i class="bi bi-lightning-charge"></i> Quick Actions
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <a class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2" href="{{ route('admin.users.index') }}" style="height: 60px;">
                    <i class="bi bi-people fs-4"></i>
                    <div class="text-start">
                        <div class="fw-bold">Manage Users</div>
                        <small class="opacity-75">Review & approve accounts</small>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2" href="{{ route('chefs.index') }}" style="height: 60px;">
                    <i class="bi bi-egg-fried fs-4"></i>
                    <div class="text-start">
                        <div class="fw-bold">View All Chefs</div>
                        <small class="opacity-75">Browse chef profiles</small>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2" href="{{ route('meals.index') }}" style="height: 60px;">
                    <i class="bi bi-utensils fs-4"></i>
                    <div class="text-start">
                        <div class="fw-bold">View All Meals</div>
                        <small class="opacity-75">Browse meal catalog</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="dashboard-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-clock-history"></i> Recent Orders
        </h5>
        <a href="{{ route('meals.index') }}" class="btn btn-sm btn-outline-primary">
            View All Orders
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 100px;">Order ID</th>
                    <th>Customer</th>
                    <th>Chef</th>
                    <th class="text-end" style="width: 120px;">Total</th>
                    <th style="width: 140px;">Status</th>
                    <th style="width: 140px;">Date</th>
                    <th style="width: 100px;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['recent_orders'] as $order)
                <tr>
                    <td>
                        <span class="fw-bold text-primary">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <span class="text-white small fw-bold">{{ substr($order->customer->name ?? 'N', 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $order->customer->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $order->customer->email ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <span class="text-white small fw-bold">{{ substr($order->chef->name ?? 'N', 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $order->chef->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $order->chef->chefProfile?->cuisine_type ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="fw-bold text-success">TZS {{ number_format((float)$order->total, 2) }}</div>
                        @if($order->delivery_fee > 0)
                            <small class="text-muted">+ TZS {{ number_format((float)$order->delivery_fee, 2) }} delivery</small>
                        @endif
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'accepted' => 'info',
                                'preparing' => 'primary',
                                'ready' => 'info',
                                'out_for_delivery' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $statusColor = $statusColors[$order->status] ?? 'secondary';
                            $statusIcons = [
                                'pending' => 'clock',
                                'accepted' => 'check-circle',
                                'preparing' => 'egg-fried',
                                'ready' => 'check2-circle',
                                'out_for_delivery' => 'truck',
                                'delivered' => 'check-circle-fill',
                                'cancelled' => 'x-circle',
                            ];
                            $statusIcon = $statusIcons[$order->status] ?? 'circle';
                        @endphp
                        <span class="badge badge-{{ $statusColor }} d-flex align-items-center gap-1" style="width: fit-content;">
                            <i class="bi bi-{{ $statusIcon }}"></i>
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="small">
                            <div class="fw-semibold">{{ $order->created_at->format('M d, Y') }}</div>
                            <div class="text-muted">{{ $order->created_at->format('h:i A') }}</div>
                        </div>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary" title="View Order">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <div class="mt-3 text-muted">No orders yet</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stats['recent_orders']->count() > 0)
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing {{ $stats['recent_orders']->count() }} most recent orders</small>
                <a href="{{ route('meals.index') }}" class="btn btn-sm btn-outline-primary">
                    View All Orders <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
