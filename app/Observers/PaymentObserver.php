<?php

namespace App\Observers;

use App\Models\Payment;
use App\Notifications\PaymentUpdateNotification;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        if (! in_array($payment->status, ['paid', 'failed'], true)) {
            return;
        }

        $customer = $payment->order?->customer;
        if (! $customer) {
            return;
        }

        try {
            $customer->notify(new PaymentUpdateNotification($payment));
        } catch (\Throwable $e) {
            Log::warning('Payment inbox notification failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
