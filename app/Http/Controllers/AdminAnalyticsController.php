<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $ordersQuery = Order::query();
        $paymentsQuery = Payment::query()->where('status', 'paid');

        if ($from) {
            $ordersQuery->whereDate('created_at', '>=', $from);
            $paymentsQuery->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $ordersQuery->whereDate('created_at', '<=', $to);
            $paymentsQuery->whereDate('created_at', '<=', $to);
        }

        $totalOrders = $ordersQuery->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'delivered')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();

        $revenue = $paymentsQuery->sum('amount');

        $totalCustomers = User::where('role', User::ROLE_CUSTOMER)->count();
        $totalChefs = User::where('role', User::ROLE_CHEF)->count();
        $totalTravelers = User::where('role', User::ROLE_TRAVELER)->count();

        $activeDeliveries = Delivery::whereIn('status', ['assigned', 'picked_up'])->count();

        return view('admin.analytics', [
            'deliveryMapPollUrl' => route('admin.analytics.delivery-locations'),
            'from' => $from,
            'to' => $to,
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'revenue' => $revenue,
            'totalCustomers' => $totalCustomers,
            'totalChefs' => $totalChefs,
            'totalTravelers' => $totalTravelers,
            'activeDeliveries' => $activeDeliveries,
        ]);
    }

    /**
     * JSON for live delivery map: traveler GPS (fresh) or saved delivery address.
     */
    public function deliveryLocations()
    {
        $gpsTtlMinutes = 15;

        $deliveries = Delivery::query()
            ->whereIn('status', ['assigned', 'picked_up'])
            ->with(['order.deliveryLocation', 'traveler.travelerProfile'])
            ->orderBy('id')
            ->get();

        $markers = [];
        $withoutCoords = 0;

        foreach ($deliveries as $delivery) {
            $lat = null;
            $lng = null;
            $source = null;
            $updatedAt = null;

            $profile = $delivery->traveler?->travelerProfile;
            if (
                $profile
                && $profile->last_latitude !== null
                && $profile->last_longitude !== null
                && $profile->last_location_at
                && $profile->last_location_at->gt(now()->subMinutes($gpsTtlMinutes))
            ) {
                $lat = (float) $profile->last_latitude;
                $lng = (float) $profile->last_longitude;
                $source = 'gps';
                $updatedAt = $profile->last_location_at->toIso8601String();
            } else {
                $loc = $delivery->order?->deliveryLocation;
                if ($loc && $loc->latitude !== null && $loc->longitude !== null) {
                    $lat = (float) $loc->latitude;
                    $lng = (float) $loc->longitude;
                    $source = 'delivery_address';
                }
            }

            if ($lat === null || $lng === null) {
                $withoutCoords++;
                continue;
            }

            $markers[] = [
                'delivery_id' => $delivery->id,
                'order_id' => $delivery->order_id,
                'status' => $delivery->status,
                'traveler' => $delivery->traveler?->name,
                'lat' => $lat,
                'lng' => $lng,
                'source' => $source,
                'location_updated_at' => $updatedAt,
                'address' => $delivery->order?->deliveryLocation
                    ? trim(implode(', ', array_filter([
                        $delivery->order->deliveryLocation->address_line,
                        $delivery->order->deliveryLocation->city,
                    ])))
                    : null,
            ];
        }

        return response()->json([
            'markers' => $markers,
            'active_total' => $deliveries->count(),
            'mapped_count' => count($markers),
            'without_coordinates' => $withoutCoords,
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}

