@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Financial Dashboard</h2>
    <p class="text-muted mb-0 page-header-subtitle">Monitor transactions, refunds, and payouts</p>
</div>

<div class="row g-3 g-md-4 mb-3 mb-md-4">
    <div class="col-6 col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-currency-exchange"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$totalRevenue, 2) }}</div>
            <div class="stat-label">Total Collected</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="bi bi-arrow-counterclockwise"></i>
            </div>
            <div class="stat-value">TZS {{ number_format((float)$totalRefunded, 2) }}</div>
            <div class="stat-label">Total Refunded</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="stat-value">{{ number_format($ordersCount) }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
</div>

<div class="dashboard-card mb-3 mb-md-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form method="GET" action="{{ route('admin.finance.index') }}" class="dashboard-filter-form dashboard-filter-form--inline dashboard-filter-form--wrap-sm">
        <div class="dashboard-filter-fields">
        <div class="dashboard-filter-field">
            <label class="form-label dashboard-filter-label" for="finance-status">Status</label>
            <select id="finance-status" name="status" class="form-select">
                <option value="">All</option>
                @foreach(['pending','paid','failed','refunded'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="dashboard-filter-field dashboard-filter-field--date">
            <label class="form-label dashboard-filter-label" for="finance-from">From</label>
            <input type="date" id="finance-from" name="from" value="{{ $from }}" class="form-control">
        </div>
        <div class="dashboard-filter-field dashboard-filter-field--date">
            <label class="form-label dashboard-filter-label" for="finance-to">To</label>
            <input type="date" id="finance-to" name="to" value="{{ $to }}" class="form-control">
        </div>
        </div>
        <div class="dashboard-filter-actions dashboard-filter-actions--end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.finance.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-wallet2"></i> Transactions</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>#{{ str_pad($payment->order_id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-uppercase">{{ $payment->method }}</td>
                        <td>TZS {{ number_format((float)$payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->status) }}</td>
                        <td>{{ $payment->provider_reference ?? 'N/A' }}</td>
                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" onsubmit="return confirm('Mark this payment as refunded?');">
                                    @csrf
                                    <input type="hidden" name="reason" value="Manual refund from admin dashboard">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-arrow-counterclockwise"></i> Refund
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        {{ $payments->links() }}
    </div>
</div>
@endsection

