<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;

class AdminMealController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $search = (string) $request->query('search', '');
        $availability = (string) $request->query('availability', ''); // available | unavailable

        $query = Meal::query()
            ->with(['chef.chefProfile'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('origin', 'like', '%' . $search . '%');
            });
        }

        if ($availability === 'available') {
            $query->where('is_available', true);
        } elseif ($availability === 'unavailable') {
            $query->where('is_available', false);
        }

        $meals = $query->paginate($perPage)->withQueryString();

        return view('admin.meals', [
            'meals' => $meals,
            'search' => $search,
            'availability' => $availability,
            'perPage' => $perPage,
        ]);
    }
}

