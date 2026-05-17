<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->syncInvoice($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->syncInvoice($payment);
    }

    private function syncInvoice(Payment $payment): void
    {
        $invoice = $payment->order?->invoice;
        $invoice?->syncFromPayment($payment);
    }
}
