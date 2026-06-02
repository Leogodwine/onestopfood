<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'checkout_batch_id',
        'method',
        'status',
        'amount',
        'provider_reference',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt',
        'provider_receipt',
        'failure_reason',
        'paid_at',
        'payment_reminder_sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_reminder_sent_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function batchOrders()
    {
        if ($this->checkout_batch_id) {
            return Order::query()->where('checkout_batch_id', $this->checkout_batch_id);
        }

        return Order::query()->whereKey($this->order_id);
    }

    public function paymentReferenceLabel(): string
    {
        if ($this->checkout_batch_id) {
            return 'BATCH-' . substr($this->checkout_batch_id, 0, 8);
        }

        return 'ORDER' . $this->order_id;
    }

    public function receiptNumber(): ?string
    {
        return $this->provider_receipt ?? $this->mpesa_receipt;
    }

    public function isMobileMoney(): bool
    {
        return in_array($this->method, ['mpesa', 'tigo', 'airtel'], true);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'mpesa' => 'M-Pesa',
            'tigo' => 'Tigo Pesa',
            'airtel' => 'Airtel Money',
            'card' => 'Card',
            'cod' => 'Cash on delivery',
            default => ucfirst((string) $this->method),
        };
    }

    /** Customer/chef-facing payment status label. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => __('payments.status_paid'),
            'pending' => __('payments.status_waiting'),
            'failed' => __('payments.status_failed'),
            'refunded' => __('payments.status_refunded'),
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'secondary',
            default => 'secondary',
        };
    }
}

