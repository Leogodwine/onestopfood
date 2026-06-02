@php
    $payment = $payment ?? $order->effectivePayment();
@endphp
@if($payment)
    <div class="payment-status-block">
        <div class="mb-1">
            <span class="text-muted small">Method:</span>
            <strong class="small">{{ $payment->methodLabel() }}</strong>
        </div>
        <div>
            <span class="badge bg-{{ $payment->statusBadgeClass() }}">
                {{ $payment->statusLabel() }}
            </span>
        </div>
        @if($payment->isPending())
            <p class="small text-muted mb-0 mt-2">
                <i class="bi bi-info-circle"></i>
                {{ __('payments.order_placed_unpaid') }}
            </p>
        @endif
    </div>
@else
    <span class="text-muted small">—</span>
@endif
