<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-size: 14px; color: #222; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">
    @php
        $brand = $siteName ?? config('app.name', 'One Stop');
        $currency = $invoice->currency ?? 'TZS';
    @endphp

    <div class="no-print mb-3 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> Print</button>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm">Back to invoice</a>
    </div>

    <div class="border p-4">
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h3 class="text-success mb-0">{{ $brand }}</h3>
                <small class="text-muted">Tax invoice / receipt</small>
            </div>
            <div class="text-end">
                <div class="text-muted small">Invoice #</div>
                <strong>{{ $invoice->invoice_number }}</strong>
                <div class="mt-2">
                    <span class="badge bg-{{ $invoice->paymentStatusBadgeClass() }}">
                        {{ $invoice->paymentStatusLabel() }}
                    </span>
                </div>
            </div>
        </div>

        @include('invoices.partials.payment-progress', ['invoice' => $invoice, 'order' => $order])

        <div class="row mb-3">
            <div class="col-6">
                <strong>Bill to</strong><br>
                {{ $order->customer->name }}<br>
                {{ $order->customer->email }}
            </div>
            <div class="col-6 text-end">
                <strong>Date:</strong> {{ $invoice->issued_at->format('M d, Y') }}<br>
                <strong>Order:</strong> #{{ $order->id }}<br>
                <strong>Payment:</strong> {{ ucfirst($order->payment?->method ?? '—') }}
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->meal?->name }}</td>
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

        @if($invoice->isPaid() && $order->payment?->receiptNumber())
            <p class="small mb-0"><strong>Payment receipt:</strong> {{ $order->payment->receiptNumber() }}</p>
        @endif
    </div>
</body>
</html>

