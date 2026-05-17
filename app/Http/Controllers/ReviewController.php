<?php

namespace App\Http\Controllers;

use App\Models\MealRating;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function create(Order $order, Request $request)
    {
        if ($order->customer_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->withErrors(['error' => 'Order must be delivered before reviewing']);
        }

        $existingReview = Review::where('order_id', $order->id)->first();
        if ($existingReview) {
            return redirect()->route('reviews.edit', $existingReview);
        }

        $order->load(['chef', 'delivery.traveler', 'items.meal']);

        return view('reviews.create', compact('order'));
    }

    public function store(Order $order, Request $request)
    {
        if ($order->customer_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->withErrors(['error' => 'Order must be delivered before reviewing']);
        }

        $existingReview = Review::where('order_id', $order->id)->first();
        if ($existingReview) {
            return redirect()->route('reviews.edit', $existingReview);
        }

        $data = $request->validate([
            'chef_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'traveler_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'meal_ratings' => ['nullable', 'array'],
            'meal_ratings.*' => ['integer', 'min:1', 'max:5'],
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $request->user()->id,
            'chef_id' => $order->chef_id,
            'traveler_id' => $order->delivery?->traveler_id,
            'chef_rating' => $data['chef_rating'] ?? null,
            'traveler_rating' => $data['traveler_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);

        $orderMealIds = $order->items->pluck('meal_id')->unique()->filter();
        foreach ($data['meal_ratings'] ?? [] as $mealId => $rating) {
            if ($orderMealIds->contains((int) $mealId)) {
                MealRating::create([
                    'review_id' => $review->id,
                    'meal_id' => (int) $mealId,
                    'rating' => (int) $rating,
                ]);
            }
        }

        return redirect()->route('orders.show', $order)->with('status', 'Review submitted');
    }

    public function edit(Review $review, Request $request)
    {
        if ($review->customer_id !== $request->user()->id) {
            abort(403);
        }

        $review->load(['order.chef', 'order.delivery.traveler', 'order.items.meal']);

        return view('reviews.edit', compact('review'));
    }

    public function update(Review $review, Request $request)
    {
        if ($review->customer_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'chef_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'traveler_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'meal_ratings' => ['nullable', 'array'],
            'meal_ratings.*' => ['integer', 'min:1', 'max:5'],
        ]);

        $review->load('order.items');

        $review->update([
            'chef_rating' => $data['chef_rating'] ?? null,
            'traveler_rating' => $data['traveler_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);

        $orderMealIds = $review->order->items->pluck('meal_id')->unique()->filter();
        $review->mealRatings()->delete();
        foreach ($data['meal_ratings'] ?? [] as $mealId => $rating) {
            if ($orderMealIds->contains((int) $mealId)) {
                MealRating::create([
                    'review_id' => $review->id,
                    'meal_id' => (int) $mealId,
                    'rating' => (int) $rating,
                ]);
            }
        }

        return redirect()->route('orders.show', $review->order_id)->with('status', 'Review updated');
    }
}
