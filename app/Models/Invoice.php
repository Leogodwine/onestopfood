<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_number',
        'issued_at',
        'due_at',
        'amount',
        'currency',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function syncFromPayment(?Payment $payment = null): void
    {
        $payment ??= $this->order?->payment;
        if (! $payment) {
            return;
        }

        $status = $payment->status;
        $this->update([
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? ($payment->paid_at ?? now()) : null,
            'amount' => $payment->amount,
        ]);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return in_array($this->payment_status, ['pending', 'failed'], true);
    }

    /** @return int 0–100 */
    public function paymentProgressPercent(): int
    {
        return match ($this->payment_status) {
            'paid' => 100,
            'pending' => 50,
            'failed' => 25,
            'refunded' => 0,
            default => 10,
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Paid',
            'pending' => 'Unpaid',
            'failed' => 'Payment failed',
            'refunded' => 'Refunded',
            default => ucfirst($this->payment_status),
        };
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'secondary',
            default => 'secondary',
        };
    }
}
