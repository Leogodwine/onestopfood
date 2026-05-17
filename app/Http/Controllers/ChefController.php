<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Meal;
use Illuminate\Http\Request;

class ChefController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', User::ROLE_CHEF)
            ->where('status', User::STATUS_APPROVED)
            ->with('chefProfile');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('chefProfile', function ($q) use ($search) {
                      $q->where('bio', 'like', "%{$search}%")
                        ->orWhere('heritage_story', 'like', "%{$search}%")
                        ->orWhere('cuisine_type', 'like', "%{$search}%");
                  });
            });
        }

        $chefs = $query->latest()->paginate(12)->withQueryString();

        // Popular chefs strip (by order count)
        $popularChefs = User::query()
            ->where('role', User::ROLE_CHEF)
            ->where('status', User::STATUS_APPROVED)
            ->with('chefProfile')
            ->withCount(['ordersAsChef as orders_count' => function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            }])
            ->orderByDesc('orders_count')
            ->limit(12)
            ->get();

        return view('chefs.index', compact('chefs', 'popularChefs'));
    }

    public function show(User $chef)
    {
        if ($chef->role !== User::ROLE_CHEF || $chef->status !== User::STATUS_APPROVED) {
            abort(404);
        }

        $chef->load('chefProfile');
        $meals = Meal::where('chef_id', $chef->id)
            ->where('is_available', true)
            ->latest()
            ->get();

        // Calculate average rating (placeholder - would need proper review aggregation)
        $totalOrders = \App\Models\Order::where('chef_id', $chef->id)
            ->where('status', 'delivered')
            ->count();

        return view('chefs.show', compact('chef', 'meals', 'totalOrders'));
    }
}
