<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class AdminZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::orderBy('priority')->orderBy('name')->get();

        return view('admin.zones', [
            'zones' => $zones,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'service_type' => ['nullable', 'string', 'max:50'],
            'base_fee' => ['nullable', 'numeric', 'min:0'],
            'traveler_capacity' => ['nullable', 'integer', 'min:0'],
            'priority' => ['nullable', 'integer', 'min:0'],
        ]);

        Zone::create($data);

        return back()->with('status', 'Zone created.');
    }
}

