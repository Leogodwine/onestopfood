@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">My Orders</h2>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-primary page-header-action-btn" href="{{ route('meals.index') }}">
                <i class="bi bi-shop"></i> Browse
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">View and track your order history</p>
</div>

@if($orders->isEmpty())
    <div class="dashboard-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 mb-2">You haven't placed any orders yet</h5>
            <p class="text-muted mb-4">Discover delicious meals from our expert chefs and place your first order.</p>
            <a class="btn btn-success btn-lg" href="{{ route('meals.index') }}">
                <i class="bi bi-shop"></i> Browse Meals
            </a>
        </div>
    </div>
@else
    <div class="dashboard-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Chef</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong>#{{ $order->id }}</strong>
                                    @if($order->checkout_batch_id)
                                        <span class="badge bg-info text-dark ms-1" title="Part of multi-chef checkout">Multi-chef</span>
                                    @endif
                                </td>
                                <td>{{ $order->chef?->name ?? '—' }}</td>
                                <td>{{ $order->items->count() }} item(s)</td>
                                <td>{{ money($order->total) }}</td>
                                <td>
                                    <span class="badge bg-{{ match($order->status) {
                                        'pending' => 'warning',
                                        'accepted' => 'info',
                                        'preparing' => 'primary',
                                        'ready' => 'success',
                                        'out_for_delivery' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    } }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @php $orderPayment = $order->effectivePayment(); @endphp
                                    @if($orderPayment)
                                        <span class="badge bg-{{ $orderPayment->statusBadgeClass() }}">
                                            {{ $orderPayment->statusLabel() }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="invoice-table-actions">
                                        @if($order->invoice)
                                            <a class="btn btn-sm btn-outline-success" href="{{ route('invoices.show', $order->invoice) }}" title="Invoice">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.print', $order->invoice) }}" target="_blank" title="Print">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('invoices.download', $order->invoice) }}" title="Download PDF">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        @endif
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('orders.show', $order) }}">
                                            <i class="bi bi-box-seam"></i> Order
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endif
@endsection
