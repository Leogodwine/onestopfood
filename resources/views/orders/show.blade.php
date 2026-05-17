@extends('layouts.dashboard')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2>Order #{{ $order->id }}</h2>
        <p class="text-muted mb-0">Order details and status</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($order->invoice)
            <a class="btn btn-success" href="{{ route('invoices.show', $order->invoice) }}">
                <i class="bi bi-receipt"></i> View invoice
            </a>
        @elseif($order->payment)
            <a class="btn btn-success" href="{{ route('orders.invoice', $order) }}">
                <i class="bi bi-receipt"></i> View invoice
            </a>
        @endif
        <a class="btn btn-outline-primary" href="{{ route('customer.orders') }}">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->meal?->name }}</div>
                                    </td>
                                    <td class="text-end">x {{ $item->quantity }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format((float)$item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-semibold">Subtotal:</td>
                                <td class="text-end fw-bold">TZS {{ number_format((float)$order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end fw-semibold">Delivery fee:</td>
                                <td class="text-end fw-bold">TZS {{ number_format((float)$order->delivery_fee, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong class="h5 mb-0">TZS {{ number_format((float)$order->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="dashboard-card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-primary fs-6">{{ ucfirst($order->status) }}</span>
                </div>
                @if($order->chef)
                    <div class="text-muted small">
                        <i class="bi bi-person-check"></i> Chef: <strong>{{ $order->chef->name }}</strong>
                    </div>
                @elseif($order->orderChefs && $order->orderChefs->isNotEmpty())
                    <div class="text-muted small">
                        <i class="bi bi-people"></i> Chefs: <strong>{{ $order->orderChefs->map(fn($oc) => $oc->chef->name)->join(', ') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="dashboard-card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted">Method:</span>
                    <strong class="ms-2">{{ ucfirst($order->payment?->method ?? 'N/A') }}</strong>
                </div>
                <div>
                    <span class="text-muted">Status:</span>
                    <span class="badge bg-{{ $order->payment?->status === 'paid' ? 'success' : ($order->payment?->status === 'pending' ? 'warning' : ($order->payment?->status === 'failed' ? 'danger' : 'secondary')) }} ms-2">
                        {{ ucfirst($order->payment?->status ?? 'N/A') }}
                    </span>
                </div>
                @include('orders.partials.mobile-money-pay')
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Delivery</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted">Status:</span>
                    <span class="badge bg-{{ $order->delivery?->status === 'delivered' ? 'success' : ($order->delivery?->status === 'assigned' ? 'info' : ($order->delivery?->status === 'in_transit' ? 'primary' : 'secondary')) }} ms-2">
                        {{ ucfirst($order->delivery?->status ?? 'Not assigned') }}
                    </span>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-truck"></i> Traveler:
                    <strong>{{ $order->delivery?->traveler?->name ?? 'Not assigned yet' }}</strong>
                </div>
            </div>
        </div>

        @if($order->status === 'delivered' && auth()->id() === $order->customer_id)
            @php
                $hasReview = \App\Models\Review::where('order_id', $order->id)->exists();
            @endphp
            @if(!$hasReview)
                <div class="card shadow-sm mt-3 border-success">
                    <div class="card-body text-center">
                        <h5 class="mb-2">Order Delivered!</h5>
                        <a class="btn btn-success" href="{{ route('reviews.create', $order) }}">Rate & Review</a>
                    </div>
                </div>
            @else
                <div class="card shadow-sm mt-3">
                    <div class="card-body text-center">
                        <a class="btn btn-outline-primary" href="{{ route('reviews.edit', \App\Models\Review::where('order_id', $order->id)->first()) }}">Edit Review</a>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

