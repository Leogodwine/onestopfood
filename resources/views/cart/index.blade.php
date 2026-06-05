@extends(auth()->check() ? 'layouts.dashboard' : 'layout')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Cart</h2>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-outline-primary page-header-action-btn" href="{{ route('meals.index') }}">
                <i class="bi bi-shop"></i> Shop
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">{{ empty($items) ? 'Your cart is empty.' : count($items) . ' item(s) in your cart' }}</p>
</div>

@if(!empty($removedUnavailableCount))
    <div class="alert alert-warning">
        {{ $removedUnavailableCount === 1 ? 'One meal was removed' : $removedUnavailableCount . ' meals were removed' }} from your cart because {{ $removedUnavailableCount === 1 ? 'it is' : 'they are' }} no longer available.
    </div>
@endif

@if(empty($items))
    <div class="dashboard-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
            <p class="mt-3 mb-0 text-muted">Your cart is empty.</p>
            <a class="btn btn-success mt-3" href="{{ route('meals.index') }}">Browse meals</a>
        </div>
    </div>
@else
    @if(!empty($isMultiChef))
        <div class="alert alert-info mb-3">
            <i class="bi bi-shop me-2"></i>
            Your cart includes meals from <strong>{{ $chefCount }} chefs</strong>.
            At checkout we create <strong>separate orders</strong> — one per chef — each with its own delivery.
        </div>
    @endif
    <div class="dashboard-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Meal</th>
                            <th>Chef</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($isMultiChef) && isset($chefGroups))
                            @foreach($chefGroups as $group)
                                <tr class="table-light">
                                    <td colspan="6" class="fw-semibold py-2">
                                        <i class="bi bi-person-badge me-1"></i> Chef: {{ $group['chef']->name }}
                                    </td>
                                </tr>
                                @foreach($group['items'] as $item)
                                    <tr>
                                        <td>{{ $item['meal']->name }}</td>
                                        <td class="text-muted">{{ $group['chef']->name }}</td>
                                        <td class="text-end">TZS {{ number_format((float)$item['meal']->price, 2) }}</td>
                                        <td class="text-end">{{ $item['quantity'] }}</td>
                                        <td class="text-end fw-bold">TZS {{ number_format((float)$item['line_total'], 2) }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('cart.remove', $item['meal']) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @else
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item['meal']->name }}</td>
                                <td class="text-muted">{{ $item['meal']->chef?->name }}</td>
                                <td class="text-end">TZS {{ number_format((float)$item['meal']->price, 2) }}</td>
                                <td class="text-end">{{ $item['quantity'] }}</td>
                                <td class="text-end fw-bold">TZS {{ number_format((float)$item['line_total'], 2) }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('cart.remove', $item['meal']) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end pt-2 pt-md-3 border-top cart-totals-strip">
                <div class="text-end">
                    <div class="text-muted small">Subtotal</div>
                    <div class="h5 mb-2 mb-md-3 cart-subtotal-amount">TZS {{ number_format((float)$subtotal, 2) }}</div>
                    @auth
                        <a class="btn btn-success" href="{{ route('orders.checkout') }}">Proceed to Checkout</a>
                    @else
                        <a class="btn btn-success" href="{{ route('login') }}">Sign in to Place Order</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

