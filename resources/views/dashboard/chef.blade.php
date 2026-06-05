@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Chef Dashboard</h2>
    <p>Manage your meals and handle incoming orders</p>
</div>

<!-- Statistics Cards (clickable, link to relevant pages) -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('chef.meals.index') }}" class="stat-card-link" title="View all meals">
            <div class="stat-card stat-green h-100">
                <div class="stat-icon">
                    <i class="bi bi-utensils"></i>
                </div>
                <div class="stat-value">{{ $stats['total_meals'] }}</div>
                <div class="stat-label">Total Meals</div>
                <span class="stat-card-link-hint">View meals <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('chef.meals.index', ['available' => 1]) }}" class="stat-card-link stat-card-link-blue" title="View available meals">
            <div class="stat-card stat-blue h-100">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">{{ $stats['available_meals'] }}</div>
                <div class="stat-label">Available Meals</div>
                <span class="stat-card-link-hint">View available <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('chef.orders.index') }}" class="stat-card-link" title="View all orders">
            <div class="stat-card stat-green h-100">
                <div class="stat-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
                <div class="stat-label">Total Orders</div>
                <span class="stat-card-link-hint">View orders <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('chef.orders.index', ['status' => 'pending']) }}" class="stat-card-link stat-card-link-blue" title="View pending orders">
            <div class="stat-card stat-blue h-100">
                <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-value">{{ $stats['pending_orders'] }}</div>
                <div class="stat-label">Pending Orders</div>
                <span class="stat-card-link-hint">View pending <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </div>
</div>

<!-- Earnings Card -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cash-coin"></i> Total Earnings</h5>
            </div>
            <div class="text-center py-4">
                <h2 class="mb-0" style="color: var(--primary-green); font-weight: 700;">
                    ${{ number_format($stats['total_earnings'], 2) }}
                </h2>
                <p class="text-muted mb-0 mt-2">From completed orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">Quick Actions</h5>
            </div>
            <div class="d-flex flex-column gap-2">
                <a class="btn btn-primary" href="{{ route('chef.meals.index') }}">
                    <i class="bi bi-utensils"></i> Manage Meals
                </a>
                <a class="btn btn-outline-primary" href="{{ route('chef.orders.index') }}">
                    <i class="bi bi-cart-check"></i> View All Orders
                </a>
                <a class="btn btn-outline-success" href="{{ route('chef.earnings') }}">
                    <i class="bi bi-cash-coin"></i> View Earnings Details
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title">Recent Orders</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['recent_orders'] as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer->name ?? 'N/A' }}</td>
                    <td>${{ number_format($order->total, 2) }}</td>
                    <td><span class="badge badge-primary">{{ ucfirst($order->status) }}</span></td>
                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('chef.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No orders yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

