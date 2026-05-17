<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\TravelerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelerController extends Controller
{
    public function toggleOnline(Request $request)
    {
        $profile = TravelerProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['is_online' => false]
        );

        $profile->update([
            'is_online' => !$profile->is_online,
        ]);

        return back()->with('status', $profile->is_online ? 'You are now online' : 'You are now offline');
    }

    public function deliveries(Request $request)
    {
        $travelerId = $request->user()->id;

        $assignedDeliveries = Delivery::query()
            ->where('traveler_id', $travelerId)
            ->with(['order.customer', 'order.chef', 'order.orderChefs.chef', 'order.items.meal', 'order.payment'])
            ->latest()
            ->get();

        $availableDeliveries = Delivery::query()
            ->where('status', 'unassigned')
            ->with(['order.customer', 'order.chef', 'order.orderChefs.chef', 'order.items.meal', 'order.payment'])
            ->latest()
            ->limit(10)
            ->get();

        return view('traveler.deliveries', [
            'assignedDeliveries' => $assignedDeliveries,
            'availableDeliveries' => $availableDeliveries,
        ]);
    }

    public function acceptDelivery(Delivery $delivery, Request $request)
    {
        if ($delivery->status !== 'unassigned') {
            return back()->withErrors(['error' => 'Delivery already assigned']);
        }

        $profile = TravelerProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['is_online' => false]
        );

        if (!$profile->is_online) {
            return back()->withErrors(['error' => 'You must be online to accept deliveries']);
        }

        $delivery->update([
            'traveler_id' => $request->user()->id,
            'status' => 'assigned',
        ]);

        $delivery->order->update(['status' => 'out_for_delivery']);

        return back()->with('status', 'Delivery accepted');
    }

    public function updateDeliveryStatus(Delivery $delivery, Request $request)
    {
        if ($delivery->traveler_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:picked_up,delivered'],
        ]);

        DB::transaction(function () use ($delivery, $data, $request) {
            $delivery->update(['status' => $data['status']]);

            if ($data['status'] === 'picked_up') {
                $delivery->order->update(['status' => 'out_for_delivery']);
            } elseif ($data['status'] === 'delivered') {
                $delivery->order->update(['status' => 'delivered']);

                // Calculate traveler earning (simplified - could be based on distance)
                $earning = max(500, $delivery->order->delivery_fee * 0.8); // 80% of delivery fee, min 500
                $delivery->update(['traveler_earning' => $earning]);
            }
        });

        return back()->with('status', 'Delivery status updated');
    }

    /**
     * Store traveler GPS for live admin map (throttle from the client).
     */
    public function updateLocation(Request $request)
    {
        if ($request->user()->role !== User::ROLE_TRAVELER) {
            abort(403);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $hasActiveDelivery = Delivery::query()
            ->where('traveler_id', $request->user()->id)
            ->whereIn('status', ['assigned', 'picked_up'])
            ->exists();

        if (!$hasActiveDelivery) {
            return response()->json(['ok' => true, 'stored' => false]);
        }

        $profile = TravelerProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['is_online' => false]
        );

        $profile->update([
            'last_latitude' => $data['latitude'],
            'last_longitude' => $data['longitude'],
            'last_location_at' => now(),
        ]);

        return response()->json(['ok' => true, 'stored' => true]);
    }
}
