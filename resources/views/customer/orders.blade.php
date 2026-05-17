@extends('layouts.dashboard')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2>My Orders</h2>
        <p class="text-muted mb-0">View and track your order history</p>
    </div>
    <a class="btn btn-primary" href="{{ route('meals.index') }}">
        <i class="bi bi-shop"></i> Browse Meals
    </a>
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
                                <td><strong>#{{ $order->id }}</strong></td>
                                <td>{{ $order->chef?->name ?? ($order->orderChefs?->map(fn($oc) => $oc->chef->name)->join(', ') ?? '—') }}</td>
                                <td>{{ $order->items->count() }} item(s)</td>
                                <td>TZS {{ number_format((float)$order->total, 2) }}</td>
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
                                    @php $payStatus = $order->payment?->status ?? '—'; @endphp
                                    <span class="badge bg-{{ $payStatus === 'paid' ? 'success' : ($payStatus === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($payStatus) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="text-nowrap">
                                    @if($order->invoice)
                                        <a class="btn btn-sm btn-outline-success" href="{{ route('invoices.show', $order->invoice) }}" title="Invoice">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    @endif
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('orders.show', $order) }}">
                                        <i class="bi bi-eye"></i> View
                                    </a>
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
