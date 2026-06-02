<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: #fff; padding: 16px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { border: 1px solid #e9ecef; border-top: none; padding: 24px; border-radius: 0 0 8px 8px; }
        .order-id { font-size: 1.25rem; font-weight: bold; margin-bottom: 16px; }
        table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.items th, table.items td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #eee; }
        table.items th { background: #f8f9fa; }
        .total { font-size: 1.1rem; font-weight: bold; margin-top: 16px; }
        .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee; font-size: 0.9rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{ \App\Models\SystemSetting::getValue('site_name', config('app.name')) }}</strong><br>
        Order Confirmation
    </div>
    <div class="content">
        <p>Hello {{ $order->customer->name }},</p>
        <p>Thank you for your order. We have received it and will process it shortly.</p>

        <div class="order-id">Order #{{ $order->id }}</div>

        @if($order->chef)
            <p><strong>Chef:</strong> {{ $order->chef->name }}</p>
        @elseif($order->orderChefs && $order->orderChefs->isNotEmpty())
            <p><strong>Chefs:</strong> {{ $order->orderChefs->map(fn($oc) => $oc->chef->name)->join(', ') }}</p>
        @endif

        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->meal->name ?? 'Meal' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>TZS {{ number_format((float)$item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>
            <strong>Subtotal:</strong> TZS {{ number_format((float)$order->subtotal, 2) }}<br>
            <strong>Delivery:</strong> TZS {{ number_format((float)$order->delivery_fee, 2) }}<br>
            <span class="total">Total: TZS {{ number_format((float)$order->total, 2) }}</span>
        </p>

        @php
            $payStatus = $order->payment->status ?? 'pending';
            $payLabel = match($payStatus) {
                'paid' => 'Paid',
                'pending' => 'Unpaid',
                default => ucfirst($payStatus),
            };
        @endphp
        <p><strong>Payment:</strong> {{ ucfirst($order->payment->method ?? 'N/A') }} — <strong>{{ $payLabel }}</strong></p>
        @if($order->invoice)
            <p><strong>Invoice:</strong> {{ $order->invoice->invoice_number }}</p>
            <p style="margin-top:12px;">
                <a href="{{ route('invoices.show', $order->invoice) }}" style="display:inline-block;background:#28a745;color:#fff;padding:10px 16px;text-decoration:none;border-radius:6px;">
                    View invoice & payment status
                </a>
            </p>
        @endif

        @if($order->deliveryLocation)
            <p><strong>Delivery address:</strong><br>
                {{ $order->deliveryLocation->address_line }}<br>
                @if($order->deliveryLocation->city){{ $order->deliveryLocation->city }}@endif
                @if($order->deliveryLocation->region){{ $order->deliveryLocation->region }}@endif
                @if($order->deliveryLocation->country){{ $order->deliveryLocation->country }}@endif
            </p>
        @endif

        <div class="footer">
            You can view your order anytime from "My Orders" in your account.<br>
            Need help? Contact us at
            <a href="mailto:{{ $supportEmail ?? config('contacts.support_email') }}">{{ $supportEmail ?? config('contacts.support_email') }}</a>.<br>
            <span class="small">This message was sent from {{ $noreplyEmail ?? config('contacts.noreply_email') }} — replies to that address are not monitored.</span>
        </div>
    </div>
</body>
</html>
