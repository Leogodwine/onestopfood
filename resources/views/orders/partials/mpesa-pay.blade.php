@if($order->payment?->mpesa_receipt)
    <div class="mt-2 small">
        <span class="text-muted">M-Pesa receipt:</span>
        <strong class="ms-1">{{ $order->payment->mpesa_receipt }}</strong>
    </div>
@endif
@if($order->payment?->failure_reason && $order->payment?->status === 'failed')
    <div class="mt-2 small text-danger">{{ $order->payment->failure_reason }}</div>
@endif
@if(
    auth()->id() === $order->customer_id
    && $order->payment?->method === 'mpesa'
    && $order->payment?->status === 'pending'
)
    <hr class="my-3">
    <p class="small text-muted mb-2">Enter your M-Pesa number to receive a payment prompt on your phone.</p>
    <form method="POST" action="{{ route('orders.pay.mpesa', $order) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-12">
            @include('partials.phone-input', [
                'label' => 'M-Pesa ' . __('auth.phone_label'),
                'value' => old('phone', $order->payment->provider_reference ?? auth()->user()->phone),
                'inputId' => 'mpesa_phone_number',
                'selectId' => 'mpesa_phone_country_code',
                'size' => 'sm',
            ])
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="bi bi-phone"></i> Pay with M-Pesa
            </button>
        </div>
    </form>
@endif
