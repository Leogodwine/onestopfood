@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Customer Dashboard</h2>
    <p class="text-muted mb-0 page-header-subtitle">Browse meals, place orders, and track your deliveries</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="stat-value">{{ $stats['total_orders'] }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value">{{ $stats['pending_orders'] }}</div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['completed_orders'] }}</div>
            <div class="stat-label">Completed Orders</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title">Quick Actions</h5>
    </div>
    <div class="d-flex flex-wrap gap-3">
        <a class="btn btn-primary" href="{{ route('meals.index') }}">
            <i class="bi bi-utensils"></i> Browse Meals
        </a>
        <a class="btn btn-outline-primary" href="{{ route('customer.orders') }}">
            <i class="bi bi-cart-check"></i> My Orders
        </a>
        <a class="btn btn-outline-primary" href="{{ route('locations.index') }}">
            <i class="bi bi-geo-alt"></i> My Addresses
        </a>
        <a class="btn btn-outline-primary" href="{{ route('cart.index') }}">
            <i class="bi bi-cart"></i> View Cart
        </a>
    </div>
</div>

<!-- Partner applications (for social-login customers) -->
<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Become a Partner</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Signed in with Google or Facebook? You can apply to join as a chef or delivery partner without creating a new account.</p>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('partner.apply') }}" class="d-inline">
                @csrf
                <input type="hidden" name="role" value="chef">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-egg-fried"></i> Become a Chef
                </button>
            </form>
            <form method="POST" action="{{ route('partner.apply') }}" class="d-inline">
                @csrf
                <input type="hidden" name="role" value="traveler">
                <button type="submit" class="btn btn-outline-success">
                    <i class="bi bi-truck"></i> Become a Traveler / Dropper
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Recent Orders – Statement view -->
<div class="dashboard-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recent Orders</h5>
        <a href="{{ route('customer.orders') }}" class="btn btn-sm btn-outline-primary">All orders</a>
    </div>
    <div class="card-body">
        @forelse($stats['recent_orders'] as $order)
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">Order #{{ $order->id }}</h6>
                <span class="badge bg-{{ match($order->status) {
                    'pending' => 'warning',
                    'accepted' => 'info',
                    'preparing' => 'primary',
                    'ready' => 'success',
                    'out_for_delivery' => 'info',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    default => 'secondary'
                } }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            </div>

            <div class="mb-2">
                <strong>Items</strong>
                <ul class="list-unstyled mb-0 small">
                    @foreach($order->items as $item)
                    <li class="d-flex justify-content-between">
                        <span>{{ $item->meal?->name ?? 'Meal' }} x {{ $item->quantity }}</span>
                        <span>{{ money($item->line_total) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="small mb-2">
                Subtotal: {{ money($order->subtotal) }}<br>
                Delivery fee: {{ money($order->delivery_fee) }}<br>
                <strong>Total: {{ money($order->total) }}</strong>
            </div>

            <div class="row small g-2 mb-2">
                <div class="col-12">Chef: {{ $order->chef?->name ?? 'N/A' }}</div>
                @if($order->payment)
                <div class="col-12">Payment: {{ $order->payment->method }} – {{ $order->payment->status }}</div>
                @endif
                @if($order->delivery)
                <div class="col-12">Delivery: {{ $order->delivery->status }} · Traveler: {{ $order->delivery->traveler?->name ?? 'Not assigned' }}</div>
                @endif
            </div>

            <div class="invoice-action-btns mt-2">
                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View full
                </a>
                @if($order->invoice)
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.print', $order->invoice) }}" target="_blank">
                        <i class="bi bi-printer"></i> Print
                    </a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('invoices.download', $order->invoice) }}">
                        <i class="bi bi-download"></i> Download
                    </a>
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted mb-0 page-header-subtitle">No orders yet. <a href="{{ route('meals.index') }}">Start ordering!</a></p>
        @endforelse
    </div>
</div>
@endsection

