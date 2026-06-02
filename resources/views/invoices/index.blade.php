@extends('layouts.dashboard')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2>Billing && Invoice</h2>
        <p class="text-muted mb-0">View and download your invoices</p>
    </div>
    <a class="btn btn-outline-primary" href="{{ auth()->user()->role === 'chef' ? route('chef.orders.index') : route('customer.orders') }}">
        <i class="bi bi-arrow-left"></i> My Orders
    </a>
</div>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Order</th>
                    <th>{{ auth()->user()->role === 'chef' ? 'Customer' : 'Chef' }}</th>
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
                        <td>{{ auth()->user()->role === 'chef' ? ($order->customer->name ?? '—') : ($order?->chef?->name ?? '—') }}</td>
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
                        <td colspan="7" class="text-center text-muted py-5">
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

