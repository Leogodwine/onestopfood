@php
    $payment = $order->effectivePayment();
    $labels = ['mpesa' => 'M-Pesa', 'tigo' => 'Tigo Pesa', 'airtel' => 'Airtel Money'];
    $label = $labels[$payment?->method] ?? 'Mobile money';
    $receipt = $payment?->receiptNumber();
@endphp

@if($receipt)
    <div class="mt-2 small">
        <span class="text-muted">{{ $label }} receipt:</span>
        <strong class="ms-1">{{ $receipt }}</strong>
    </div>
@endif
@if($payment?->failure_reason && $payment?->status === 'failed')
    <div class="mt-2 small text-danger">{{ $payment->failure_reason }}</div>
@endif
@if(
    auth()->id() === $order->customer_id
    && $payment?->isMobileMoney()
    && $payment?->status === 'pending'
)
    <hr class="my-3">
    <p class="small text-muted mb-2">Enter your {{ $label }} number to receive a payment prompt on your phone.</p>
    <form method="POST" action="{{ route('orders.pay.mobile', $order) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-12">
            <label for="mobile_money_phone" class="form-label small mb-1">{{ $label }} phone</label>
            <input type="text" name="phone" id="mobile_money_phone" class="form-control form-control-sm"
                   placeholder="255712345678" required
                   value="{{ old('phone', $payment->provider_reference ?? auth()->user()->phone) }}">
            @error('phone')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="bi bi-phone"></i> Pay with {{ $label }}
            </button>
        </div>
    </form>
@endif
