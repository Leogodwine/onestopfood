@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>My Earnings</h2>
            <p class="text-muted mb-0 page-header-subtitle">Track your delivery earnings</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$totalEarnings, 2) }}</div>
            <div class="stat-label">Total Earnings</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-value">{{ $totalDeliveries }}</div>
            <div class="stat-label">Total Deliveries</div>
        </div>
    </div>
</div>

@if($dailyEarnings->isNotEmpty())
    <div class="dashboard-card mb-4">
        <div class="card-header">
            <h5 class="card-title"><i class="bi bi-calendar-day"></i> Daily Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Deliveries</th>
                            <th class="text-end">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyEarnings->reverse() as $date => $data)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                                <td class="text-end">{{ $data['deliveries'] }}</td>
                                <td class="text-end fw-bold">TZS {{ number_format((float)$data['earnings'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title"><i class="bi bi-clock-history"></i> Recent Deliveries</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Chef</th>
                        <th>Date</th>
                        <th class="text-end">Earning</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td>#{{ $delivery->order->id }}</td>
                            <td>{{ $delivery->order->customer->name }}</td>
                            <td>{{ $delivery->order->chef?->name ?? ($delivery->order->orderChefs?->map(fn($oc) => $oc->chef->name)->join(', ') ?? '—') }}</td>
                            <td>{{ $delivery->created_at->format('M d, Y') }}</td>
                            <td class="text-end fw-bold">TZS {{ number_format((float)$delivery->traveler_earning, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center">No completed deliveries yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
