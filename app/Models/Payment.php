<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function receiptNumber(): ?string
    {
        return $this->provider_receipt ?? $this->mpesa_receipt;
    }

    public function isMobileMoney(): bool
    {
        return in_array($this->method, ['mpesa', 'tigo', 'airtel'], true);
    }
}

