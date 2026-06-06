@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Order Monitoring</h2>
    <p class="text-muted mb-0 page-header-subtitle">Monitor all orders and intervene when needed</p>
</div>

@php
    $ordersListQuery = array_filter([
        'order_id' => $orderId ?: null,
        'status' => $status ?: null,
        'from' => $from ?: null,
        'to' => $to ?: null,
        'per_page' => $perPage,
    ]);
    $hasOrderFilters = $orderId !== '' || $status !== '' || $from || $to;
@endphp

<div class="dashboard-card mb-3 mb-md-4">
    <div class="card-header py-2">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form method="GET" action="{{ route('admin.orders.index') }}" class="dashboard-filter-form dashboard-filter-form--inline dashboard-filter-form--wrap-sm">
        @if($perPage)
            <input type="hidden" name="per_page" value="{{ $perPage }}">
        @endif
        <div class="dashboard-filter-fields">
        <div class="dashboard-filter-field dashboard-filter-field--narrow">
            <label class="form-label dashboard-filter-label" for="filter-order-id">Order ID</label>
            <input type="text" id="filter-order-id" name="order_id" value="{{ $orderId }}" class="form-control" placeholder="#123" inputmode="numeric">
        </div>
        <div class="dashboard-filter-field">
            <label class="form-label dashboard-filter-label" for="filter-status">Status</label>
            <select id="filter-status" name="status" class="form-select">
                <option value="">All</option>
                @foreach(['pending','accepted','preparing','ready','out_for_delivery','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="dashboard-filter-field dashboard-filter-field--date">
            <label class="form-label dashboard-filter-label" for="filter-from">From</label>
            <input type="date" id="filter-from" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="dashboard-filter-field dashboard-filter-field--date">
            <label class="form-label dashboard-filter-label" for="filter-to">To</label>
            <input type="date" id="filter-to" name="to" value="{{ $to }}" class="form-control">
        </div>
        </div>
        <div class="dashboard-filter-actions dashboard-filter-actions--end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.orders.index', ['per_page' => $perPage]) }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header all-users-card-header">
        <div class="all-users-header-row">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-check"></i> Orders ({{ number_format($orders->total()) }})
            </h5>
            <div class="all-users-table-toolbar">
                <div class="all-users-toolbar-group all-users-pager-group">
                    <span class="all-users-toolbar-label">Per page</span>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ (int) $perPage }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach([10, 20, 50, 100] as $size)
                                <li>
                                    <a class="dropdown-item @if((int) $perPage === $size) active @endif"
                                       href="{{ route('admin.orders.index', array_merge($ordersListQuery, ['per_page' => $size])) }}">
                                        {{ $size }} per page
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @if($hasOrderFilters)
                    <div class="all-users-toolbar-group">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.orders.index', ['per_page' => $perPage]) }}">
                            <i class="bi bi-x-circle"></i> Clear filter
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Chef</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $order->customer->name ?? 'N/A' }}</td>
                        <td>{{ $order->chef->name ?? 'N/A' }}</td>
                        <td>TZS {{ number_format((float)$order->total, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$order->status)) }}</td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        {{ $orders->links() }}
    </div>
</div>
@endsection
