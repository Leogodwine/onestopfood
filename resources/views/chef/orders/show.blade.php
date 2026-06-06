@extends('layouts.dashboard')

@section('content')
@php
    $myItems = $chefPortion ? $order->items->filter(fn($i) => $i->meal && (int)$i->meal->chef_id === (int)auth()->id()) : $order->items;
    $mySubtotal = $chefPortion ? (float)$chefPortion->subtotal : (float)$order->subtotal;
    $myStatus = $chefPortion ? $chefPortion->status : $order->status;
    $assignBtnLabel = !empty($canReassign) ? 'Reassign Traveler' : 'Assign Traveler';
@endphp
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Order #{{ $order->id }}</h2>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-outline-primary page-header-action-btn" href="{{ route('chef.orders.index') }}">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">
        @if($chefPortion)
            Your portion ({{ $myItems->count() }} item(s)) – TZS {{ number_format($mySubtotal, 2) }}
        @else
            Order details and management
        @endif
    </p>
</div>

<div class="row g-3 g-md-4">
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

                @if(!empty($canManageDelivery))
                    <hr class="my-4" id="assign-traveler">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div>
                            <div class="fw-semibold mb-1"><i class="bi bi-truck me-1"></i> Delivery traveler</div>
                            @if($order->delivery?->traveler)
                                <div class="text-muted small">
                                    Assigned to <strong class="text-body">{{ $order->delivery->traveler->name }}</strong>
                                    · {{ ucfirst(str_replace('_', ' ', $order->delivery->status)) }}
                                </div>
                            @else
                                <div class="text-muted small">No traveler assigned yet for this delivery.</div>
                            @endif
                        </div>
                        <button
                            type="button"
                            class="btn btn-success btn-sm flex-shrink-0"
                            data-bs-toggle="modal"
                            data-bs-target="#assignTravelerModal"
                        >
                            <i class="bi bi-person-check"></i> {{ $assignBtnLabel }}
                        </button>
                    </div>
                @endif
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
                    <div class="mb-2"><strong>Delivery:</strong> {{ ucfirst(str_replace('_', ' ', $order->delivery->status)) }}</div>
                    <div class="mb-2">
                        <strong>Traveler:</strong>
                        @if($order->delivery->traveler)
                            {{ $order->delivery->traveler->name }}
                        @else
                            <span class="text-muted">Not assigned</span>
                        @endif
                    </div>
                @else
                    <div class="mb-2 text-muted">Delivery not created yet.</div>
                @endif
                @if(!empty($canManageDelivery))
                    <button
                        type="button"
                        class="btn btn-outline-success btn-sm w-100 mt-2"
                        data-bs-toggle="modal"
                        data-bs-target="#assignTravelerModal"
                    >
                        <i class="bi bi-truck"></i> {{ $assignBtnLabel }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!empty($canManageDelivery))
    @include('chef.orders.partials.assign-traveler-modal')
@endif
@endsection

@push('scripts')
@if(!empty($canManageDelivery))
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('assignTravelerModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    var shouldOpen = @json(request()->boolean('assign') || request()->has('assign') || $errors->has('error'));

    if (shouldOpen || window.location.hash === '#assign-traveler') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
});
</script>
@endif
@endpush
