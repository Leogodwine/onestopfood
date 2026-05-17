@extends('layouts.dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Rate Chef & Meals — Order #{{ $order->id }}</h3>
    <a class="btn btn-outline-secondary" href="{{ route('orders.show', $order) }}">Back</a>
</div>

<form method="POST" action="{{ route('reviews.store', $order) }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-egg-fried me-2"></i>Rate the meals / menu</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">How was each dish? Your ratings help other customers and our chefs.</p>
                    @foreach($order->items->groupBy(fn($i) => $i->meal_id) as $mealId => $items)
                        @php $item = $items->first(); @endphp
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div>
                                <strong>{{ $item->meal->name }}</strong>
                                @if($items->sum('quantity') > 1)
                                    <span class="text-muted">× {{ $items->sum('quantity') }}</span>
                                @endif
                            </div>
                            <select class="form-select form-select-sm w-auto" name="meal_ratings[{{ $item->meal_id }}]" style="max-width: 6rem;">
                                <option value="">—</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} ★</option>
                                @endfor
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Order total</h5>
                    <div class="h5 mb-0">TZS {{ number_format((float)$order->total, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-check me-2"></i>Rate the chef</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Chef</label>
                        <select class="form-select" name="chef_rating">
                            <option value="">Select stars (1–5)</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'star' : 'stars' }}</option>
                            @endfor
                        </select>
                        <div class="form-text">{{ $order->chef?->name ?? ($order->orderChefs?->map(fn($oc) => $oc->chef->name)->join(', ') ?? '—') }}</div>
                    </div>
                </div>
            </div>

            @if($order->delivery && $order->delivery->traveler)
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Rate the traveler</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label class="form-label">Delivery</label>
                            <select class="form-select" name="traveler_rating">
                                <option value="">Select stars (1–5)</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'star' : 'stars' }}</option>
                                @endfor
                            </select>
                            <div class="form-text">{{ $order->delivery->traveler->name }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <label class="form-label">Comment (optional)</label>
                    <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience..."></textarea>
                </div>
            </div>

            <button class="btn btn-success w-100 btn-lg" type="submit"><i class="bi bi-check2 me-2"></i>Submit review</button>
        </div>
    </div>
</form>
@endsection
