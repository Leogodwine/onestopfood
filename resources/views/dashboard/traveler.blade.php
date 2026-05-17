@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>Traveler Dashboard</h2>
    <p>Accept deliveries and track your earnings</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-value">{{ $stats['total_deliveries'] }}</div>
            <div class="stat-label">Total Deliveries</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value">{{ $stats['pending_deliveries'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value">{{ $stats['completed_deliveries'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-value">${{ number_format($stats['total_earnings'], 2) }}</div>
            <div class="stat-label">Total Earnings</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title">Quick Actions</h5>
    </div>
    <div class="d-flex flex-wrap gap-3">
        <a class="btn btn-primary" href="{{ route('traveler.deliveries') }}">
            <i class="bi bi-truck"></i> View Deliveries
        </a>
        <a class="btn btn-outline-success" href="{{ route('traveler.earnings') }}">
            <i class="bi bi-cash-coin"></i> View Earnings Details
        </a>
    </div>
</div>

<!-- Earnings Summary -->
<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title"><i class="bi bi-cash-coin"></i> Earnings Summary</h5>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="dashboard-summary-box">
                <div class="dashboard-summary-value">${{ number_format($stats['total_earnings'], 2) }}</div>
                <div class="dashboard-summary-label">Total Earnings</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-summary-box">
                <div class="dashboard-summary-value">{{ $stats['completed_deliveries'] }}</div>
                <div class="dashboard-summary-label">Completed Deliveries</div>
            </div>
        </div>
    </div>
</div>
@endsection

