<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.45;
        }
        .header-table,
        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding-bottom: 16px;
        }
        .brand {
            font-size: 20px;
            font-weight: bold;
            color: #198754;
            margin: 0;
        }
        .muted {
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
        }
        .badge-success { background: #198754; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-danger { background: #dc3545; }
        .badge-secondary { background: #6c757d; }
        .section-title {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        .items-table th {
            background: #f5f5f5;
            text-align: left;
        }
        .text-end { text-align: right; }
        .totals-table td {
            padding: 6px 8px;
        }
        .total-row td {
            font-weight: bold;
            border-top: 2px solid #222;
        }
        .payment-box {
            margin: 16px 0;
            padding: 10px 12px;
            border: 1px solid #ddd;
            background: #fafafa;
        }
        .payment-box.paid { border-color: #198754; background: #f0fff4; }
        .payment-box.pending { border-color: #ffc107; background: #fffdf0; }
        .payment-box.failed { border-color: #dc3545; background: #fff5f5; }
    </style>
</head>
<body>
@php
    $brand = $siteName ?? config('app.name', 'One Stop');
    $currency = $invoice->currency ?? 'TZS';
    $status = $invoice->payment_status;
    $badge = $invoice->paymentStatusBadgeClass();
    $label = $invoice->paymentStatusLabel();
@endphp

<table class="header-table">
    <tr>
        <td>
            <p class="brand">{{ $brand }}</p>
            <span class="muted">Tax invoice / receipt</span>
        </td>
        <td class="text-end">
            <div class="muted">Invoice #</div>
            <strong>{{ $invoice->invoice_number }}</strong><br>
            <span class="badge badge-{{ $badge }}">{{ $label }}</span>
        </td>
    </tr>
</table>

<div class="payment-box {{ $status === 'paid' ? 'paid' : ($status === 'failed' ? 'failed' : 'pending') }}">
    <strong>Payment status:</strong> {{ $label }}
    @if($invoice->isPaid() && $invoice->paid_at)
        · Paid on {{ $invoice->paid_at->format('M d, Y H:i') }}
    @elseif($status === 'pending')
        · Unpaid — payment pending
    @endif
    @if($order->payment?->receiptNumber())
        · Receipt: {{ $order->payment->receiptNumber() }}
    @endif
</div>

<table class="info-table" style="margin-bottom: 16px;">
    <tr>
        <td style="width: 50%;">
            <div class="section-title">Bill to</div>
            {{ $order->customer->name }}<br>
            {{ $order->customer->email }}
            @if($order->customer->phone)
                <br>{{ $order->customer->phone }}
            @endif
        </td>
        <td class="text-end" style="width: 50%;">
            <div class="section-title">Invoice details</div>
            Date: {{ $invoice->issued_at->format('M d, Y') }}<br>
            Order: #{{ $order->id }}<br>
            Payment method: {{ ucfirst($order->payment?->method ?? '—') }}
            @if($invoice->due_at)
                <br>Due: {{ $invoice->due_at->format('M d, Y') }}
            @endif
        </td>
    </tr>
</table>

<table class="items-table">
    <thead>
        <tr>
            <th>Item</th>
            <th class="text-end" style="width: 70px;">Qty</th>
            <th class="text-end" style="width: 120px;">Amount</th>
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
</table>

<table class="totals-table" style="margin-top: 12px;">
    <tr>
        <td class="text-end" style="width: 80%;">Subtotal</td>
        <td class="text-end" style="width: 20%;">{{ $currency }} {{ number_format((float) $order->subtotal, 2) }}</td>
    </tr>
    <tr>
        <td class="text-end">Delivery</td>
        <td class="text-end">{{ $currency }} {{ number_format((float) $order->delivery_fee, 2) }}</td>
    </tr>
    <tr class="total-row">
        <td class="text-end">Total</td>
        <td class="text-end">{{ $currency }} {{ number_format((float) $invoice->amount, 2) }}</td>
    </tr>
</table>

@if($order->deliveryLocation)
    <p style="margin-top: 16px;" class="muted">
        <strong>Delivery:</strong>
        {{ $order->deliveryLocation->address_line }}
        @if($order->deliveryLocation->city), {{ $order->deliveryLocation->city }}@endif
    </p>
@endif

</body>
</html>
