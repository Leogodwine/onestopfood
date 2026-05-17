<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderChef;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChefOrderController extends Controller
{
    /**
     * Haversine distance in km between two points.
     */
    private static function distanceKm(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }
        $earth = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earth * $c, 2);
    }
    public function index(Request $request)
    {
        $chefId = $request->user()->id;
        $orderIdsFromOrderChefs = OrderChef::where('chef_id', $chefId)->pluck('order_id');
        $orderIdsFromLegacy = Order::where('chef_id', $chefId)->pluck('id');
        $orderIds = $orderIdsFromOrderChefs->merge($orderIdsFromLegacy)->unique()->values();

        $query = Order::query()
            ->whereIn('id', $orderIds)
            ->with(['customer', 'items.meal', 'payment', 'delivery', 'orderChefs']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $statusFilter = $request->input('status');

        return view('chef.orders.index', compact('orders', 'statusFilter'));
    }

    public function show(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (!$orderChef && !$isLegacyChef) {
            abort(403);
        }

        $order->load(['customer', 'items.meal', 'payment', 'delivery.traveler', 'deliveryLocation', 'orderChefs.chef']);
        $chefPortion = $orderChef ?? null;

        $delivery = $order->delivery;
        $needsAssignment = $delivery && (empty($delivery->traveler_id) || $delivery->status === 'unassigned');
        $availableTravelers = collect();
        if ($needsAssignment) {
            $travelers = User::query()
                ->where('role', User::ROLE_TRAVELER)
                ->where('status', User::STATUS_APPROVED)
                ->whereHas('travelerProfile', fn ($q) => $q->where('is_online', true))
                ->with(['travelerProfile', 'location'])
                ->get();

            $deliveryLat = $order->deliveryLocation?->latitude ? (float) $order->deliveryLocation->latitude : null;
            $deliveryLon = $order->deliveryLocation?->longitude ? (float) $order->deliveryLocation->longitude : null;
            $chefProfile = $request->user()->chefProfile;
            $chefLat = $chefProfile?->kitchen_latitude ? (float) $chefProfile->kitchen_latitude : null;
            $chefLon = $chefProfile?->kitchen_longitude ? (float) $chefProfile->kitchen_longitude : null;

            $travelersWithDistance = $travelers->map(function (User $t) use ($deliveryLat, $deliveryLon, $chefLat, $chefLon) {
                $loc = $t->location;
                $tLat = $loc?->latitude ? (float) $loc->latitude : null;
                $tLon = $loc?->longitude ? (float) $loc->longitude : null;
                $distToCustomer = self::distanceKm($tLat, $tLon, $deliveryLat, $deliveryLon);
                $distToChef = self::distanceKm($tLat, $tLon, $chefLat, $chefLon);
                $combined = null;
                if ($distToCustomer !== null && $distToChef !== null) {
                    $combined = round($distToCustomer + $distToChef, 2);
                } elseif ($distToCustomer !== null) {
                    $combined = $distToCustomer;
                } elseif ($distToChef !== null) {
                    $combined = $distToChef;
                }
                return (object) [
                    'user' => $t,
                    'distance_km_to_customer' => $distToCustomer,
                    'distance_km_to_chef' => $distToChef,
                    'combined_km' => $combined,
                ];
            });
            // Sort: nearest first (null distances last)
            $availableTravelers = $travelersWithDistance->sortBy(function ($o) {
                return $o->combined_km ?? 999999;
            })->values();
        }

        return view('chef.orders.show', compact('order', 'chefPortion', 'availableTravelers', 'needsAssignment'));
    }

    public function assignTraveler(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (! $orderChef && ! $isLegacyChef) {
            abort(403);
        }

        $data = $request->validate([
            'traveler_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $traveler = User::query()
            ->where('id', $data['traveler_id'])
            ->where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_APPROVED)
            ->whereHas('travelerProfile', fn ($q) => $q->where('is_online', true))
            ->firstOrFail();

        $delivery = $order->delivery;
        if (! $delivery) {
            $order->delivery()->create([
                'traveler_id' => $traveler->id,
                'status' => 'assigned',
                'traveler_earning' => 0,
            ]);
        } else {
            $delivery->update([
                'traveler_id' => $traveler->id,
                'status' => 'assigned',
            ]);
        }

        return back()->with('status', 'Delivery assigned to ' . $traveler->name . '.');
    }

    public function accept(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (!$orderChef && !$isLegacyChef) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['error' => 'Order cannot be accepted']);
        }

        if ($orderChef) {
            $orderChef->update(['status' => 'accepted']);
            $allAccepted = $order->orderChefs()->where('status', '!=', 'accepted')->doesntExist();
            if ($allAccepted) {
                $order->update(['status' => 'accepted']);
            }
        } else {
            $order->update(['status' => 'accepted']);
        }

        return back()->with('status', 'Order accepted');
    }

    public function reject(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (!$orderChef && !$isLegacyChef) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['error' => 'Order cannot be rejected']);
        }

        if ($orderChef) {
            $orderChef->update(['status' => 'rejected']);
        }
        $order->update(['status' => 'cancelled']);

        if ($order->payment && $order->payment->status === 'paid') {
            $order->payment->update(['status' => 'refunded']);
        }

        return back()->with('status', 'Order rejected');
    }

    public function updateStatus(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (!$orderChef && !$isLegacyChef) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'preparing', 'ready'])],
        ]);

        if ($orderChef) {
            $orderChef->update(['status' => $data['status']]);
            if ($data['status'] === 'accepted') {
                $allAccepted = $order->orderChefs()->where('status', '!=', 'accepted')->doesntExist();
                if ($allAccepted) {
                    $order->update(['status' => 'accepted']);
                }
            }
        } else {
            $order->update(['status' => $data['status']]);
        }

        return back()->with('status', 'Order status updated');
    }
}
