<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealRating extends Model
{
    protected $fillable = ['review_id', 'meal_id', 'rating'];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}
