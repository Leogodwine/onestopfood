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

