@extends('layouts.dashboard')

@section('content')
@php
    $profile = auth()->user()->travelerProfile;
    $isOnline = $profile && $profile->is_online;
    $trackGpsForAdminMap = $assignedDeliveries->contains(fn ($d) => in_array($d->status, ['assigned', 'picked_up'], true));
@endphp

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>My Deliveries</h2>
            <p class="text-muted mb-0">Manage your delivery assignments</p>
        </div>
        <form method="POST" action="{{ route('traveler.toggle-online') }}" class="d-inline">
            @csrf
            <button class="btn btn-{{ $isOnline ? 'success' : 'secondary' }}" type="submit">
                <i class="bi bi-{{ $isOnline ? 'wifi' : 'wifi-off' }}"></i> {{ $isOnline ? 'Online' : 'Offline' }}
            </button>
        </form>
    </div>
</div>

@if(!$isOnline)
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle"></i> You are currently offline. Toggle online to receive delivery assignments.
    </div>
@endif

<div class="row g-4">
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-inbox"></i> Available Deliveries</h5>
            </div>
            <div class="card-body">
                @forelse($availableDeliveries as $delivery)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">Order #{{ $delivery->order->id }}</div>
                                <div class="text-muted small">Customer: {{ $delivery->order->customer->name }}</div>
                                <div class="text-muted small">Chef: {{ $delivery->order->chef?->name ?? ($delivery->order->orderChefs?->map(fn($oc) => $oc->chef->name)->join(', ') ?? '—') }}</div>
                                <div class="fw-bold mt-2">TZS {{ number_format((float)$delivery->order->total, 2) }}</div>
                            </div>
                            <form method="POST" action="{{ route('traveler.deliveries.accept', $delivery) }}">
                                @csrf
                                <button class="btn btn-sm btn-success" type="submit">Accept</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No available deliveries</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> My Assigned Deliveries</h5>
            </div>
            <div class="card-body">
                @forelse($assignedDeliveries as $delivery)
                    <div class="border rounded p-3 mb-3">
                        <div class="mb-2">
                            <div class="fw-semibold">Order #{{ $delivery->order->id }}</div>
                            <div class="text-muted small">Customer: {{ $delivery->order->customer->name }}</div>
                            <div class="text-muted small">Chef: {{ $delivery->order->chef?->name ?? ($delivery->order->orderChefs?->map(fn($oc) => $oc->chef->name)->join(', ') ?? '—') }}</div>
                            <div class="mt-2">
                                <span class="badge bg-{{ match($delivery->status) {
                                    'assigned' => 'info',
                                    'picked_up' => 'primary',
                                    'delivered' => 'success',
                                    default => 'secondary'
                                } }}">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </div>
                        </div>

                        @if(in_array($delivery->status, ['assigned', 'picked_up']))
                            <form method="POST" action="{{ route('traveler.deliveries.update-status', $delivery) }}">
                                @csrf
                                <div class="d-flex gap-2">
                                    @if($delivery->status === 'assigned')
                                        <button class="btn btn-sm btn-primary" name="status" value="picked_up" type="submit">Mark Picked Up</button>
                                    @endif
                                    @if($delivery->status === 'picked_up')
                                        <button class="btn btn-sm btn-success" name="status" value="delivered" type="submit">Mark Delivered</button>
                                    @endif
                                </div>
                            </form>
                        @endif

                        @if($delivery->traveler_earning > 0)
                            <div class="mt-2 text-success fw-bold">Earning: TZS {{ number_format((float)$delivery->traveler_earning, 2) }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No assigned deliveries</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if($trackGpsForAdminMap)
<div class="alert alert-info small mb-0 mt-3">
    <i class="bi bi-geo-alt"></i> Location is sent about once per minute while this page is open (synced, not continuous). Allow the browser prompt if asked.
</div>
@endif

@push('scripts')
@if($trackGpsForAdminMap)
<script>
(function () {
    if (!navigator.geolocation) return;
    var url = @json(route('traveler.location.update'));
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;
    var SYNC_INTERVAL_MS = 60 * 1000;
    function post(lat, lng) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': meta.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ latitude: lat, longitude: lng })
        }).catch(function () {});
    }
    function syncOnce() {
        if (document.visibilityState !== 'visible') return;
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                post(pos.coords.latitude, pos.coords.longitude);
            },
            function () {},
            { enableHighAccuracy: false, maximumAge: 120000, timeout: 20000 }
        );
    }
    syncOnce();
    setInterval(syncOnce, SYNC_INTERVAL_MS);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') syncOnce();
    });
})();
</script>
@endif
@endpush
@endsection
