<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'customer_id',
        'chef_id',
        'traveler_id',
        'chef_rating',
        'traveler_rating',
        'comment',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function mealRatings()
    {
        return $this->hasMany(MealRating::class);
    }
}

