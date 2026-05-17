<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = $request->user()->locations()->latest()->get();

        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        // If this is set as primary, unset others
        if ($data['is_primary'] ?? false) {
            $request->user()->locations()->update(['is_primary' => false]);
        }

        // If no primary exists, make this one primary
        if (!$request->user()->locations()->where('is_primary', true)->exists()) {
            $data['is_primary'] = true;
        }

        $location = $request->user()->locations()->create($data);

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Location added successfully',
                'location' => $location
            ]);
        }

        return redirect()->route('locations.index')->with('status', 'Location added');
    }

    public function edit(Location $location, Request $request)
    {
        if ($location->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($request->boolean('partial')) {
            return view('locations._edit_form', compact('location'));
        }

        return view('locations.edit', compact('location'));
    }

    public function update(Location $location, Request $request)
    {
        if ($location->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        // If this is set as primary, unset others
        if ($data['is_primary'] ?? false) {
            $request->user()->locations()->where('id', '!=', $location->id)->update(['is_primary' => false]);
        }

        $location->update($data);

        return redirect()->route('locations.index')->with('status', 'Location updated');
    }

    public function destroy(Location $location, Request $request)
    {
        if ($location->user_id !== $request->user()->id) {
            abort(403);
        }

        $location->delete();

        return redirect()->route('locations.index')->with('status', 'Location deleted');
    }

    public function setPrimary(Location $location, Request $request)
    {
        if ($location->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->locations()->update(['is_primary' => false]);
        $location->update(['is_primary' => true]);

        return back()->with('status', 'Primary location updated');
    }
}
