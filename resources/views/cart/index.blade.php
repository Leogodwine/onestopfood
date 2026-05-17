@extends(auth()->check() ? 'layouts.dashboard' : 'layout')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2>Cart</h2>
        <p class="text-muted mb-0">{{ empty($items) ? 'Your cart is empty.' : count($items) . ' item(s) in your cart' }}</p>
    </div>
    <a class="btn btn-outline-primary" href="{{ route('meals.index') }}">
        <i class="bi bi-shop"></i> Continue shopping
    </a>
</div>

@if(empty($items))
    <div class="dashboard-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
            <p class="mt-3 mb-0 text-muted">Your cart is empty.</p>
            <a class="btn btn-success mt-3" href="{{ route('meals.index') }}">Browse meals</a>
        </div>
    </div>
@else
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
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end pt-3 border-top">
                <div class="text-end">
                    <div class="text-muted">Subtotal</div>
                    <div class="h4 mb-3">TZS {{ number_format((float)$subtotal, 2) }}</div>
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

