<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Meal::query()
            ->where('is_available', true)
            ->whereNotNull('heritage_story')
            ->with('chef.chefProfile');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('heritage_story', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhereHas('chef', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $stories = $query->latest()->paginate(12)->withQueryString();

        return view('stories.index', compact('stories'));
    }
}
