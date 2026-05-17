@extends('layouts.dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">My Earnings</h3>
    <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Dashboard</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$totalRevenue, 2) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-percent"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$totalCommission, 2) }}</div>
            <div class="stat-label">Commission ({{ $commissionRate }}%)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$totalEarnings, 2) }}</div>
            <div class="stat-label">Your Earnings</div>
        </div>
    </div>
</div>

@if($monthlyEarnings->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Monthly Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Orders</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Commission</th>
                            <th class="text-end">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyEarnings->reverse() as $month => $data)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($month)->format('F Y') }}</td>
                                <td class="text-end">{{ $data['orders'] }}</td>
                                <td class="text-end">TZS {{ number_format((float)$data['revenue'], 2) }}</td>
                                <td class="text-end">TZS {{ number_format((float)$data['commission'], 2) }}</td>
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
        <h5 class="card-title"><i class="bi bi-clock-history"></i> Recent Orders</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($earningsRows as $row)
                        <tr>
                            <td>#{{ $row->order->id }}</td>
                            <td>{{ $row->order->customer->name }}</td>
                            <td>{{ $row->created_at->format('M d, Y') }}</td>
                            <td class="text-end">TZS {{ number_format((float)$row->subtotal, 2) }}</td>
                            <td class="text-end fw-bold">TZS {{ number_format((float)($row->subtotal * 0.85), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center">No completed orders yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
