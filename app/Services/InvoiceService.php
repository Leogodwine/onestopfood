<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SystemSetting;

class InvoiceService
{
    public function createForOrder(Order $order, Payment $payment): Invoice
    {
        $currency = SystemSetting::getValue('currency', 'TZS');

        return Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $this->generateInvoiceNumber($order),
            'issued_at' => now(),
            'due_at' => now()->addDays(7),
            'amount' => $order->total,
            'currency' => $currency,
            'payment_status' => $payment->status,
            'paid_at' => $payment->status === 'paid' ? now() : null,
        ]);
    }

    public function generateInvoiceNumber(Order $order): string
    {
        $prefix = 'INV-' . now()->format('Ym');
        $seq = str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

        $candidate = "{$prefix}-{$seq}";
        if (Invoice::where('invoice_number', $candidate)->exists()) {
            $candidate = "{$prefix}-{$seq}-" . strtoupper(substr(uniqid(), -4));
        }

        return $candidate;
    }
}
