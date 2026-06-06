@php
    $currency = $invoice->currency ?? 'TZS';
    $brand = $siteName ?? config('app.name', 'One Stop');
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3 pb-3 border-bottom">
    <div>
        <div class="text-muted small">Invoice</div>
        <div class="h5 mb-1">{{ $invoice->invoice_number }}</div>
        <span class="badge bg-{{ $invoice->paymentStatusBadgeClass() }}">{{ $invoice->paymentStatusLabel() }}</span>
    </div>
    <div class="text-end small text-muted">
        <div>Issued {{ $invoice->issued_at->format('M d, Y') }}</div>
        <div>Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>
</div>

@include('invoices.partials.payment-progress', ['invoice' => $invoice, 'order' => $order])

<div class="row small mb-3 mt-3">
    <div class="col-md-6">
        <div class="text-muted">Bill to</div>
        <div class="fw-semibold">{{ $order->customer->name }}</div>
        <div class="text-muted">{{ $order->customer->email }}</div>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <div class="text-muted">Payment method</div>
        <div>{{ ucfirst($order->payment?->method ?? '—') }}</div>
    </div>
</div>

<div class="table-responsive table-responsive-fit">
    <table class="table table-sm align-middle mb-0 order-line-table">
        @include('partials.order-line-colgroup')
        <thead class="table-light">
            <tr>
                <th>Item</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->meal?->name ?? 'N/A' }}</td>
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
                <td colspan="2" class="text-end">Delivery</td>
                <td class="text-end">{{ $currency }} {{ number_format((float) $order->delivery_fee, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-end fw-bold">Total</td>
                <td class="text-end fw-bold">{{ $currency }} {{ number_format((float) $invoice->amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@if($invoice->isPaid() && $order->payment?->receiptNumber())
    <p class="small text-muted mb-0 mt-3"><strong>Receipt:</strong> {{ $order->payment->receiptNumber() }}</p>
@endif
