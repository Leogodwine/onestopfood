<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'chef_id',
        'checkout_batch_id',
        'status',
        'special_instructions',
        'subtotal',
        'delivery_fee',
        'total',
        'delivery_location_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /** Shared payment when multiple orders were placed in one checkout. */
    public function sharedPayment()
    {
        return $this->hasOne(Payment::class, 'checkout_batch_id', 'checkout_batch_id');
    }

    public function effectivePayment(): ?Payment
    {
        if ($this->checkout_batch_id) {
            if ($this->relationLoaded('sharedPayment')) {
                return $this->sharedPayment;
            }

            return Payment::query()
                ->where('checkout_batch_id', $this->checkout_batch_id)
                ->first();
        }

        if ($this->relationLoaded('payment')) {
            return $this->payment;
        }

        return $this->payment()->first();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function deliveryLocation()
    {
        return $this->belongsTo(Location::class, 'delivery_location_id');
    }

    public function orderChefs()
    {
        return $this->hasMany(OrderChef::class);
    }

    /** Other orders placed in the same checkout (multi-chef). */
    public function batchOrders()
    {
        if (! $this->checkout_batch_id) {
            return collect([$this]);
        }

        return static::query()
            ->where('checkout_batch_id', $this->checkout_batch_id)
            ->orderBy('id')
            ->get();
    }

    public function scopeForChef($query, int $chefId)
    {
        return $query->where(function ($q) use ($chefId) {
            $q->where('chef_id', $chefId)
                ->orWhereHas('orderChefs', fn ($oc) => $oc->where('chef_id', $chefId));
        });
    }

    /** Order items that belong to a given chef (for multi-chef orders) */
    public function itemsForChef(int $chefId)
    {
        return $this->items->filter(fn ($item) => $item->meal && (int) $item->meal->chef_id === $chefId);
    }

    /** Whether this order has multiple chefs (order_chefs rows) */
    public function getIsMultiChefAttribute(): bool
    {
        return $this->orderChefs()->exists();
    }
}

