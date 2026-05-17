<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;

class Meal extends Model
{
    use HasFactory;
    protected $fillable = [
        'chef_id',
        'name',
        'description',
        'heritage_story',
        'origin',
        'prep_time_minutes',
        'price',
        'category',
        'dietary_tags',
        'image_path',
        'is_available',
        'is_heritage',
        'is_popular',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_heritage' => 'boolean',
        'is_popular' => 'boolean',
    ];

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, OrderItem::class, 'meal_id', 'order_id', 'id', 'order_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function mealRatings()
    {
        return $this->hasMany(MealRating::class);
    }

    public function getAverageRatingAttribute()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('meal_ratings')) {
            $fromMealRatings = $this->mealRatings()->avg('rating');
            if ($fromMealRatings !== null) {
                return round((float) $fromMealRatings, 1);
            }
        }
        $orderItemIds = $this->orderItems()->pluck('order_id');
        $reviews = Review::whereIn('order_id', $orderItemIds)
            ->whereNotNull('chef_rating')
            ->get();
        if ($reviews->count() === 0) {
            return 0;
        }
        return round($reviews->avg('chef_rating'), 1);
    }

    public function getTotalReviewsAttribute()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('meal_ratings')) {
            $count = $this->mealRatings()->count();
            if ($count > 0) {
                return $count;
            }
        }
        $orderItemIds = $this->orderItems()->pluck('order_id');
        return Review::whereIn('order_id', $orderItemIds)
            ->whereNotNull('chef_rating')
            ->count();
    }

    /**
     * Get standard meal categories with descriptions
     */
    public static function getStandardCategories(): array
    {
        return [
            'Breakfast' => [
                'name' => 'Breakfast',
                'description' => 'First meal of the day',
                'examples' => 'tea/coffee, bread, eggs, porridge, fruits',
            ],
            'Brunch' => [
                'name' => 'Brunch',
                'description' => 'Combination of breakfast and lunch',
                'examples' => 'pancakes, omelets, juices',
            ],
            'Lunch' => [
                'name' => 'Lunch',
                'description' => 'Midday meal',
                'examples' => 'rice, beans, meat, vegetables',
            ],
            'Dinner' => [
                'name' => 'Dinner',
                'description' => 'Main evening meal',
                'examples' => 'ugali, fish, beef stew, vegetables',
            ],
        ];
    }
}

