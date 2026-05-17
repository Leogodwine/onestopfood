<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index(Request $request)
    {
        $query = Meal::query()
            ->where('is_available', true)
            ->with('chef');

        // Search functionality - includes heritage stories and origins
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('heritage_story', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('dietary_tags', 'like', "%{$search}%")
                  ->orWhereHas('chef', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('chefProfile', function ($q) use ($search) {
                            $q->where('heritage_story', 'like', "%{$search}%")
                              ->orWhere('bio', 'like', "%{$search}%");
                        });
                  });
            });
        }

        // Filter by heritage
        if ($request->filled('filter') && $request->input('filter') === 'heritage') {
            $query->where('is_heritage', true);
        }

        // Filter by popular
        if ($request->filled('sort') && $request->input('sort') === 'popular') {
            $query->where('is_popular', true);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Sort options
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $meals = $query->paginate(12)->withQueryString();

        // Get unique categories for filter: standard first, then any from DB
        $dbCategories = Meal::where('is_available', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();
        $standardNames = array_keys(Meal::getStandardCategories());
        $categories = collect($standardNames)
            ->merge($dbCategories->diff($standardNames)->sort()->values())
            ->unique()
            ->values();

        // Heritage Stories block (same dataset as home: flagged or with story)
        $heritageMeals = Meal::query()
            ->where('is_available', true)
            ->where(function ($query) {
                $query->where('is_heritage', true)
                    ->orWhereNotNull('heritage_story');
            })
            ->with(['chef.chefProfile'])
            ->get()
            ->map(function ($meal) {
                $meal->average_rating = $this->mealAverageRating($meal->id);
                $meal->total_reviews = $this->mealReviewCount($meal->id);

                return $meal;
            })
            ->sortByDesc('average_rating')
            ->values();

        return view('meals.index', compact('meals', 'categories', 'heritageMeals'));
    }

    private function mealAverageRating(int $mealId): float
    {
        $orderIds = OrderItem::query()->where('meal_id', $mealId)->pluck('order_id');
        $reviews = Review::query()->whereIn('order_id', $orderIds)->whereNotNull('chef_rating')->get();
        if ($reviews->isEmpty()) {
            return 0.0;
        }

        return round((float) $reviews->avg('chef_rating'), 1);
    }

    private function mealReviewCount(int $mealId): int
    {
        $orderIds = OrderItem::query()->where('meal_id', $mealId)->pluck('order_id');

        return Review::query()->whereIn('order_id', $orderIds)->whereNotNull('chef_rating')->count();
    }

    public function chefIndex(Request $request)
    {
        $query = Meal::query()->where('chef_id', $request->user()->id);

        if ($request->filled('available')) {
            $query->where('is_available', (bool) $request->input('available'));
        }

        $meals = $query->latest()->paginate(12)->withQueryString();
        $availableFilter = $request->input('available');

        return view('chef.meals.index', compact('meals', 'availableFilter'));
    }

    public function create()
    {
        return view('chef.meals.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'heritage_story' => ['nullable', 'string'],
            'origin' => ['nullable', 'string', 'max:255'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'dietary_tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'], // 2MB max
            'is_available' => ['nullable', 'boolean'],
            'is_heritage' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
        ], [
            'image.required' => 'Please select an image for the meal.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, jpg, png, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('meals', 'public');
            $data['image_path'] = $imagePath;
        }

        Meal::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'heritage_story' => $data['heritage_story'] ?? null,
            'origin' => $data['origin'] ?? null,
            'prep_time_minutes' => $data['prep_time_minutes'] ?? null,
            'price' => $data['price'],
            'category' => $data['category'] ?? null,
            'dietary_tags' => $data['dietary_tags'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'chef_id' => $request->user()->id,
            'is_available' => (bool) ($data['is_available'] ?? true),
            'is_heritage' => (bool) ($data['is_heritage'] ?? false),
            'is_popular' => (bool) ($data['is_popular'] ?? false),
        ]);

        return redirect()->route('chef.meals.index')->with('status', 'Meal created successfully!');
    }
}

