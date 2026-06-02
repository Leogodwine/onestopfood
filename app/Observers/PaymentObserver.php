<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->syncInvoices($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->syncInvoices($payment);
    }

    private function syncInvoices(Payment $payment): void
    {
        $orders = $payment->batchOrders()->with('invoice')->get();

        foreach ($orders as $order) {
            $order->invoice?->syncFromPayment($payment);
        }
    }
}
