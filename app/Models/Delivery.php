<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'traveler_id',
        'status',
        'traveler_earning',
    ];

    protected $casts = [
        'traveler_earning' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }
}

