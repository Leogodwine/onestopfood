@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">My Orders</h2>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-outline-primary page-header-back-btn" href="{{ route('dashboard') }}">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Manage incoming orders from customers</p>
</div>

<div class="dashboard-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-cart-check"></i>
            @if($statusFilter === 'pending')
                Pending Orders
            @else
                All Orders
            @endif
        </h5>
        <div class="nav nav-pills nav-pills-sm gap-1">
            <a class="nav-link {{ !$statusFilter || $statusFilter === 'all' ? 'active' : '' }}" href="{{ route('chef.orders.index', ['status' => 'all']) }}">
                All
            </a>
            <a class="nav-link {{ $statusFilter === 'pending' ? 'active' : '' }}" href="{{ route('chef.orders.index', ['status' => 'pending']) }}">
                Pending
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Traveler</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $portion = $order->orderChefs->firstWhere('chef_id', auth()->id());
                        $myItems = $portion
                            ? $order->items->filter(fn ($i) => $i->meal && (int) $i->meal->chef_id === (int) auth()->id())
                            : $order->items;
                        $rowSubtotal = $portion ? (float) $portion->subtotal : (float) $order->subtotal;
                        $rowStatus = $portion ? $portion->status : $order->status;
                        $orderPayment = $order->effectivePayment();
                    @endphp
                    <tr>
                        <td>
                            <strong>#{{ $order->id }}</strong>
                            @if($order->checkout_batch_id)
                                <span class="badge bg-light text-dark border ms-1" title="Separate order from multi-chef checkout">Split</span>
                            @endif
                        </td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ $myItems->count() }} item(s) @if($portion)<span class="text-muted small">(your portion)</span>@endif</td>
                        <td class="text-end">TZS {{ number_format($rowSubtotal, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ match($rowStatus) {
                                'pending' => 'warning',
                                'accepted' => 'primary',
                                'preparing' => 'primary',
                                'ready' => 'success',
                                'out_for_delivery' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                'rejected' => 'danger',
                                default => 'primary'
                            } }}">
                                {{ ucfirst(str_replace('_', ' ', $rowStatus)) }}
                            </span>
                        </td>
                        <td>
                            @if($orderPayment)
                                <span class="badge bg-{{ $orderPayment->statusBadgeClass() }}" title="{{ $orderPayment->methodLabel() }}">
                                    {{ $orderPayment->statusLabel() }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($order->delivery?->traveler)
                                <span class="small">{{ $order->delivery->traveler->name }}</span>
                            @elseif(!in_array($order->status, ['cancelled', 'delivered'], true))
                                <a class="btn btn-sm btn-outline-success" href="{{ route('chef.orders.show', ['order' => $order, 'assign' => 1]) }}#assign-traveler">
                                    <i class="bi bi-truck"></i> Assign
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('chef.orders.show', $order) }}">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No orders yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $orders->links() }}
    </div>
</div>
@endsection
