@extends('layouts.dashboard')

@section('content')
@php
    $brand = $siteName ?? config('app.name', 'One Stop');
    $currency = $invoice->currency ?? $currencyCode ?? 'TZS';
@endphp

@if(session('placed_batch_order_ids') && count(session('placed_batch_order_ids')) > 1)
    <div class="alert alert-info mb-4">
        <i class="bi bi-receipt me-2"></i>
        Your checkout created <strong>{{ count(session('placed_batch_order_ids')) }} invoices</strong> (one per chef).
        <a href="{{ route('billing.index') }}" class="alert-link">View all billing &amp; invoices</a>
    </div>
@endif

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2>Invoice {{ $invoice->invoice_number }}</h2>
        <p class="text-muted mb-0">Order #{{ $order->id }} · Issued {{ $invoice->issued_at->format('M d, Y') }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="{{ route('orders.show', $order) }}">
            <i class="bi bi-box-seam"></i> Order details
        </a>
        <a class="btn btn-outline-primary" href="{{ route('billing.index') }}">
            <i class="bi bi-wallet2"></i> Billing &amp; invoices
        </a>
        <a class="btn btn-outline-primary" href="{{ route('invoices.print', $invoice) }}" target="_blank">
            <i class="bi bi-printer"></i> Print
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('invoices.download', $invoice) }}">
            <i class="bi bi-download"></i> Download PDF
        </a>
        @php
            $backRoute = match(auth()->user()->role) {
                'admin' => route('admin.invoices.index'),
                'chef' => route('chef.orders.index'),
                'traveler' => route('traveler.deliveries'),
                default => route('billing.index'),
            };
            $backLabel = match(auth()->user()->role) {
                'admin' => 'All invoices',
                'chef' => 'My orders',
                'traveler' => 'My deliveries',
                default => 'Billing & invoices',
            };
        @endphp
        <a class="btn btn-outline-primary" href="{{ $backRoute }}">
            <i class="bi bi-arrow-left"></i> {{ $backLabel }}
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="dashboard-card invoice-document">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h4 class="mb-1 text-success">{{ $brand }}</h4>
                        <p class="text-muted small mb-0">Food order & delivery</p>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Invoice</div>
                        <div class="h5 mb-0">{{ $invoice->invoice_number }}</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Bill to</p>
                        <p class="mb-0 fw-semibold">{{ $order->customer->name }}</p>
                        <p class="text-muted small mb-0">{{ $order->customer->email }}</p>
                        @if($order->customer->phone)
                            <p class="text-muted small mb-0">{{ $order->customer->phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <p class="text-muted small mb-1">Invoice date</p>
                        <p class="mb-2">{{ $invoice->issued_at->format('F j, Y') }}</p>
                        @if($invoice->due_at)
                            <p class="text-muted small mb-1">Due date</p>
                            <p class="mb-0">{{ $invoice->due_at->format('F j, Y') }}</p>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->meal?->name }}</div>
                                        @if($item->meal?->chef)
                                            <small class="text-muted">Chef: {{ $item->meal->chef->name }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ $currency }} {{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end">Subtotal</td>
                                <td class="text-end">{{ $currency }} {{ number_format((float) $order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end">Delivery fee</td>
                                <td class="text-end">{{ $currency }} {{ number_format((float) $order->delivery_fee, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td colspan="2" class="text-end fw-bold">Total due</td>
                                <td class="text-end fw-bold h5 mb-0">{{ $currency }} {{ number_format((float) $invoice->amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($order->deliveryLocation)
                    <p class="small text-muted mb-0">
                        <strong>Delivery:</strong>
                        {{ $order->deliveryLocation->address_line }}
                        @if($order->deliveryLocation->city), {{ $order->deliveryLocation->city }}@endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @php $invoicePayment = $order->effectivePayment(); @endphp
        <div class="dashboard-card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment status</h5>
            </div>
            <div class="card-body">
                @include('invoices.partials.payment-progress', ['invoice' => $invoice, 'order' => $order, 'payment' => $invoicePayment])

                <dl class="small mb-0 mt-3">
                    <dt class="text-muted">Method</dt>
                    <dd>{{ $invoicePayment?->methodLabel() ?? '—' }}</dd>
                    <dt class="text-muted">Amount</dt>
                    <dd>{{ $currency }} {{ number_format((float) $invoice->amount, 2) }}</dd>
                    <dt class="text-muted">Order status</dt>
                    <dd>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</dd>
                </dl>
            </div>
        </div>

        @if(auth()->id() === $order->customer_id && $invoicePayment?->isPending())
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pay now</h5>
                </div>
                <div class="card-body">
                    @include('orders.partials.mobile-money-pay')
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

