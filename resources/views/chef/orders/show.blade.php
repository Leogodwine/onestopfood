@extends('layouts.dashboard')

@section('content')
@php
    $myItems = $chefPortion ? $order->items->filter(fn($i) => $i->meal && (int)$i->meal->chef_id === (int)auth()->id()) : $order->items;
    $mySubtotal = $chefPortion ? (float)$chefPortion->subtotal : (float)$order->subtotal;
    $myStatus = $chefPortion ? $chefPortion->status : $order->status;
@endphp
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Order #{{ $order->id }}</h2>
            <p class="text-muted mb-0">
                @if($chefPortion)
                    Your portion ({{ $myItems->count() }} item(s)) – TZS {{ number_format($mySubtotal, 2) }}
                @else
                    Order details and management
                @endif
            </p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('chef.orders.index') }}">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-list-ul"></i> @if($chefPortion) Your Items @else Order Items @endif</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($myItems as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $item->meal->name }}</div>
                                <div class="text-muted small">x {{ $item->quantity }} @ TZS {{ number_format((float)$item->unit_price, 2) }}</div>
                            </div>
                            <div class="fw-bold">TZS {{ number_format((float)$item->line_total, 2) }}</div>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-end mt-3">
                    <div class="text-end">
                        <div>Your subtotal: <strong>TZS {{ number_format($mySubtotal, 2) }}</strong></div>
                        @if(!$chefPortion)
                            <div>Delivery: <strong>TZS {{ number_format((float)$order->delivery_fee, 2) }}</strong></div>
                            <div class="h5 mt-2">Order total: TZS {{ number_format((float)$order->total, 2) }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($order->special_instructions)
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Special Instructions</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $order->special_instructions }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Your Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Current Status</div>
                    <div class="h5">
                        <span class="badge bg-{{ match($myStatus) {
                            'pending' => 'warning',
                            'accepted' => 'info',
                            'preparing' => 'primary',
                            'ready' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary'
                        } }}">
                            {{ ucfirst(str_replace('_', ' ', $myStatus)) }}
                        </span>
                    </div>
                </div>

                @if($order->status === 'pending' && $myStatus === 'pending')
                    <form method="POST" action="{{ route('chef.orders.accept', $order) }}" class="mb-2">
                        @csrf
                        <button class="btn btn-success w-100" type="submit">Accept Order</button>
                    </form>
                    <form method="POST" action="{{ route('chef.orders.reject', $order) }}">
                        @csrf
                        <button class="btn btn-danger w-100" type="submit">Reject Order</button>
                    </form>
                @elseif($order->status !== 'cancelled' && in_array($myStatus, ['accepted', 'preparing', 'ready']))
                    <form method="POST" action="{{ route('chef.orders.update-status', $order) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Update Your Status</label>
                            <select class="form-select" name="status" required>
                                <option value="accepted" @selected($myStatus === 'accepted')>Accepted</option>
                                <option value="preparing" @selected($myStatus === 'preparing')>Preparing</option>
                                <option value="ready" @selected($myStatus === 'ready')>Ready for Pickup</option>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Update Status</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-credit-card"></i> Payment</h5>
            </div>
            <div class="card-body">
                @include('orders.partials.payment-status', ['order' => $order])
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-person"></i> Customer & Delivery</h5>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Customer:</strong> {{ $order->customer->name }}</div>
                @if($order->delivery)
                    <div class="mb-2"><strong>Delivery:</strong> {{ ucfirst($order->delivery->status) }}</div>
                    @if($order->delivery->traveler)
                        <div class="mb-2"><strong>Traveler:</strong> {{ $order->delivery->traveler->name }}</div>
                    @endif
                @endif
            </div>
        </div>

        @if((!empty($needsAssignment) || !empty($canReassign)) && $nearbyTravelers->isNotEmpty())
            <div class="dashboard-card mt-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-truck"></i> Nearby Travelers (online)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Order quantity: <strong>{{ $orderQuantity }}</strong> item(s).
                        Ranked by distance to your kitchen and customer, vehicle capacity, and live GPS when available.
                    </p>
                    <ul class="list-group list-group-flush">
                        @foreach($nearbyTravelers as $item)
                            @php $t = $item->user; @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong>{{ $t->name }}</strong>
                                    @if($item->recommended)
                                        <span class="badge bg-success ms-1">Best match</span>
                                    @endif
                                    <div class="small text-muted mt-1">
                                        {{ ucfirst($item->vehicle_type ?? 'vehicle') }}
                                        · cap {{ $item->vehicle_capacity }}
                                        @if($item->max_load_capacity) (max {{ $item->max_load_capacity }}) @endif
                                        · {{ $item->location_source === 'gps' ? 'Live GPS' : 'Registered address' }}
                                    </div>
                                    @if($item->distance_km_to_chef !== null || $item->distance_km_to_customer !== null)
                                        <div class="small text-muted">
                                            @if($item->distance_km_to_chef !== null) {{ number_format($item->distance_km_to_chef, 1) }} km to kitchen @endif
                                            @if($item->distance_km_to_customer !== null) · {{ number_format($item->distance_km_to_customer, 1) }} km to customer @endif
                                            @if($item->combined_km !== null) · <strong>{{ number_format($item->combined_km, 1) }} km total</strong> @endif
                                        </div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('chef.orders.assign-traveler', $order) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="traveler_id" value="{{ $t->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">{{ !empty($canReassign) ? 'Reassign' : 'Assign' }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @elseif((!empty($needsAssignment) || !empty($canReassign)) && $nearbyTravelers->isEmpty())
            <div class="dashboard-card mt-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-truck"></i> Nearby Travelers</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">No suitable online travelers nearby. They must be online, within delivery radius, have GPS/location, and vehicle capacity for {{ $orderQuantity }} item(s).</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
