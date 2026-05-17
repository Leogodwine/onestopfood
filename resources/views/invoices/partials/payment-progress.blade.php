@php
    $invoice = $invoice ?? null;
    $payment = $order->payment ?? null;
    $status = $invoice?->payment_status ?? $payment?->status ?? 'pending';
    $percent = $invoice?->paymentProgressPercent() ?? 50;
    $label = $invoice?->paymentStatusLabel() ?? ucfirst($status);
    $badge = $invoice?->paymentStatusBadgeClass() ?? 'warning';
@endphp

<div class="invoice-payment-tracker mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold">Payment progress</span>
        <span class="badge bg-{{ $badge }}">{{ $label }}</span>
    </div>
    <div class="progress mb-3" style="height: 10px;">
        <div
            class="progress-bar bg-{{ $status === 'paid' ? 'success' : ($status === 'failed' ? 'danger' : 'warning') }}"
            role="progressbar"
            style="width: {{ $percent }}%;"
            aria-valuenow="{{ $percent }}"
            aria-valuemin="0"
            aria-valuemax="100">
        </div>
    </div>
    <div class="d-flex justify-content-between small text-muted invoice-progress-steps">
        <span class="{{ $percent >= 10 ? 'text-success fw-semibold' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Issued
        </span>
        <span class="{{ $status === 'pending' ? 'text-warning fw-semibold' : ($percent >= 50 ? 'text-success' : '') }}">
            <i class="bi bi-hourglass-split"></i> {{ $status === 'paid' ? 'Confirmed' : 'Awaiting payment' }}
        </span>
        <span class="{{ $status === 'paid' ? 'text-success fw-semibold' : '' }}">
            <i class="bi bi-check-circle"></i> Paid
        </span>
    </div>
    @if($status === 'paid' && ($payment?->receiptNumber() || $invoice?->paid_at))
        <div class="alert alert-success small mt-3 mb-0 py-2">
            <i class="bi bi-check2-circle me-1"></i>
            Paid
            @if($invoice?->paid_at)
                on {{ $invoice->paid_at->format('M d, Y H:i') }}
            @endif
            @if($payment?->receiptNumber())
                · Receipt: <strong>{{ $payment->receiptNumber() }}</strong>
            @endif
        </div>
    @elseif($status === 'pending')
        <div class="alert alert-warning small mt-3 mb-0 py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>Unpaid</strong> — complete payment to confirm your order.
        </div>
    @elseif($status === 'failed')
        <div class="alert alert-danger small mt-3 mb-0 py-2">
            <i class="bi bi-x-circle me-1"></i>
            Payment failed. {{ $payment?->failure_reason }}
        </div>
    @endif
</div>
