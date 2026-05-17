<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\User;
use App\Models\Review;
use App\Models\Location;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Get user's location if authenticated
        $userLocation = null;
        if (auth()->check()) {
            $userLocation = auth()->user()->location;
        }

        // Define food categories
        $categories = [
            'African Heritage' => ['African', 'African Heritage', 'Tanzanian', 'Ethiopian', 'Nigerian', 'Ghanaian'],
            'American' => ['American', 'USA', 'BBQ', 'Southern'],
            'Asian' => ['Asian', 'Chinese', 'Japanese', 'Thai', 'Indian', 'Korean', 'Vietnamese'],
            'European' => ['European', 'Italian', 'French', 'Spanish', 'Greek', 'Mediterranean'],
            'Middle Eastern' => ['Middle Eastern', 'Lebanese', 'Turkish', 'Moroccan'],
            'Mexican' => ['Mexican', 'Latin American', 'Tex-Mex'],
        ];

        // Get meals by category
        $mealsByCategory = [];
        foreach ($categories as $categoryName => $categoryKeywords) {
            $meals = Meal::where('is_available', true)
                ->where(function ($query) use ($categoryKeywords) {
                    foreach ($categoryKeywords as $keyword) {
                        $query->orWhere('category', 'like', "%{$keyword}%")
                              ->orWhere('origin', 'like', "%{$keyword}%");
                    }
                })
                ->with(['chef.chefProfile', 'chef.location'])
                ->limit(6)
                ->get()
                ->map(function ($meal) {
                    $meal->average_rating = $this->calculateMealRating($meal);
                    $meal->total_reviews = $this->getMealReviewCount($meal);
                    return $meal;
                });

            if ($meals->count() > 0) {
                $mealsByCategory[$categoryName] = $meals;
            }
        }

        // Get nearest chefs (if user has location)
        $nearestChefs = collect();
        if ($userLocation && $userLocation->latitude && $userLocation->longitude) {
            $nearestChefs = User::where('role', User::ROLE_CHEF)
                ->where('status', User::STATUS_APPROVED)
                ->whereHas('location', function ($query) use ($userLocation) {
                    // Simple distance calculation (can be improved with proper geospatial queries)
                    $query->whereNotNull('latitude')
                          ->whereNotNull('longitude');
                })
                ->with(['chefProfile', 'location'])
                ->get()
                ->map(function ($chef) use ($userLocation) {
                    if ($chef->location) {
                        $chef->distance = $this->calculateDistance(
                            $userLocation->latitude,
                            $userLocation->longitude,
                            $chef->location->latitude,
                            $chef->location->longitude
                        );
                    } else {
                        $chef->distance = null;
                    }
                    $chef->average_rating = $this->calculateChefRating($chef);
                    $chef->total_reviews = $this->getChefReviewCount($chef);
                    return $chef;
                })
                ->sortBy('distance')
                ->take(6);
        } else {
            // If no location, show top rated chefs
            $nearestChefs = User::where('role', User::ROLE_CHEF)
                ->where('status', User::STATUS_APPROVED)
                ->with(['chefProfile', 'location'])
                ->get()
                ->map(function ($chef) {
                    $chef->average_rating = $this->calculateChefRating($chef);
                    $chef->total_reviews = $this->getChefReviewCount($chef);
                    return $chef;
                })
                ->sortByDesc('average_rating')
                ->take(6);
        }

        // Get most liked/popular meals
        $mostLikedMeals = Meal::where('is_available', true)
            ->where('is_popular', true)
            ->with(['chef.chefProfile'])
            ->get()
            ->map(function ($meal) {
                $meal->average_rating = $this->calculateMealRating($meal);
                $meal->total_reviews = $this->getMealReviewCount($meal);
                $meal->order_count = \App\Models\OrderItem::where('meal_id', $meal->id)->sum('quantity');
                return $meal;
            })
            ->sortByDesc('order_count')
            ->take(8);

        // Get top rated meals
        $topRatedMeals = Meal::where('is_available', true)
            ->with(['chef.chefProfile'])
            ->get()
            ->map(function ($meal) {
                $meal->average_rating = $this->calculateMealRating($meal);
                $meal->total_reviews = $this->getMealReviewCount($meal);
                return $meal;
            })
            ->filter(function ($meal) {
                return $meal->average_rating > 0;
            })
            ->sortByDesc('average_rating')
            ->take(6);

        // Get all unique categories for filter
        $allCategories = Meal::where('is_available', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();


        return view('home', compact(
            'mealsByCategory',
            'nearestChefs',
            'mostLikedMeals',
            'topRatedMeals',
            'allCategories',
            'userLocation'
        ));
    }


    private function calculateMealRating($meal)
    {
        // Get ratings from reviews through order items
        $orderItemIds = \App\Models\OrderItem::where('meal_id', $meal->id)
            ->pluck('order_id');

        $reviews = Review::whereIn('order_id', $orderItemIds)
            ->whereNotNull('chef_rating')
            ->get();

        if ($reviews->count() === 0) {
            return 0;
        }

        return round($reviews->avg('chef_rating'), 1);
    }

    private function getMealReviewCount($meal)
    {
        $orderItemIds = \App\Models\OrderItem::where('meal_id', $meal->id)
            ->pluck('order_id');

        return Review::whereIn('order_id', $orderItemIds)
            ->whereNotNull('chef_rating')
            ->count();
    }

    private function calculateChefRating($chef)
    {
        $reviews = Review::where('chef_id', $chef->id)
            ->whereNotNull('chef_rating')
            ->get();

        if ($reviews->count() === 0) {
            return 0;
        }

        return round($reviews->avg('chef_rating'), 1);
    }

    private function getChefReviewCount($chef)
    {
        return Review::where('chef_id', $chef->id)
            ->whereNotNull('chef_rating')
            ->count();
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Haversine formula to calculate distance in kilometers
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }
}
