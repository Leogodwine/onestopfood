<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderChef extends Model
{
    use HasFactory;

    protected $table = 'order_chefs';

    protected $fillable = [
        'order_id',
        'chef_id',
        'subtotal',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    /** Items in this order that belong to this chef */
    public function getItemsAttribute()
    {
        return $this->order->items->filter(
            fn ($item) => $item->meal && (int) $item->meal->chef_id === (int) $this->chef_id
        );
    }
}
