@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>Billing && Invoice</h2>
            <p class="text-muted mb-0">Manage invoices and track payment status</p>
        </div>
    </div>
</div>

<div class="dashboard-card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form method="GET" action="{{ route('admin.invoices.index') }}" class="row g-3 p-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small text-muted">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Invoice number or order ID">
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted">Payment status</label>
            <select name="status" class="form-select">
                <option value="" @selected($status === '')>All</option>
                @foreach(['paid','pending','failed','refunded'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <div class="flex-grow-1">
                <label class="form-label small text-muted d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>
            <div class="flex-grow-1">
                <label class="form-label small text-muted d-block">&nbsp;</label>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
        <div class="col-12 d-flex justify-content-end">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Per page: {{ (int)$perPage }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach([10, 20, 50, 100] as $size)
                        <li>
                            <a class="dropdown-item @if((int)$perPage === $size) active @endif"
                               href="{{ route('admin.invoices.index', array_filter(['search' => $search ?: null, 'status' => $status ?: null, 'per_page' => $size])) }}">
                                {{ $size }} per page
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-receipt"></i> Invoices ({{ number_format($invoices->total()) }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Chef</th>
                    <th>Status</th>
                    <th class="text-end">Amount</th>
                    <th>Issued</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $order = $invoice->order;
                        $badge = $invoice->paymentStatusBadgeClass();
                        $currency = $invoice->currency ?? 'TZS';
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                        <td class="text-muted">#{{ $invoice->order_id }}</td>
                        <td>{{ $order?->customer?->name ?? '—' }}</td>
                        <td>{{ $order?->chef?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $badge }}">{{ $invoice->paymentStatusLabel() }}</span>
                        </td>
                        <td class="text-end fw-bold">{{ $currency }} {{ number_format((float)$invoice->amount, 2) }}</td>
                        <td class="text-muted small">{{ optional($invoice->issued_at)->format('M d, Y') }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('invoices.show', $invoice) }}" title="View invoice">
                                <i class="bi bi-receipt"></i>
                            </a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.print', $invoice) }}" target="_blank" title="Print">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('invoices.download', $invoice) }}" title="Download PDF">
                                <i class="bi bi-download"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <div class="mt-3">No invoices found</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-center">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection

