<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChef;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningsController extends Controller
{
    public function chefEarnings(Request $request)
    {
        $chefId = $request->user()->id;
        $commissionRate = 0.15;

        $legacyOrders = Order::query()
            ->where('chef_id', $chefId)
            ->whereIn('status', ['delivered'])
            ->with(['payment', 'customer'])
            ->latest()
            ->get();

        $orderChefs = OrderChef::query()
            ->where('chef_id', $chefId)
            ->whereHas('order', fn ($q) => $q->where('status', 'delivered'))
            ->with(['order.payment', 'order.customer'])
            ->latest()
            ->get();

        $totalRevenue = $legacyOrders->sum('subtotal') + $orderChefs->sum('subtotal');
        $totalCommission = $totalRevenue * $commissionRate;
        $totalEarnings = $totalRevenue - $totalCommission;

        $earningsRows = $legacyOrders->map(fn ($o) => (object)['order' => $o, 'subtotal' => (float) $o->subtotal, 'created_at' => $o->created_at])
            ->merge($orderChefs->map(fn ($oc) => (object)['order' => $oc->order, 'subtotal' => (float) $oc->subtotal, 'created_at' => $oc->order->created_at]))
            ->sortByDesc('created_at')->values();

        $monthlyEarnings = $earningsRows->groupBy(fn ($r) => $r->created_at->format('Y-m'))->map(function ($group) use ($commissionRate) {
            $revenue = $group->sum('subtotal');
            return [
                'revenue' => $revenue,
                'commission' => $revenue * $commissionRate,
                'earnings' => $revenue * (1 - $commissionRate),
                'orders' => $group->count(),
            ];
        });

        return view('chef.earnings', [
            'earningsRows' => $earningsRows->take(20),
            'totalRevenue' => $totalRevenue,
            'totalCommission' => $totalCommission,
            'totalEarnings' => $totalEarnings,
            'monthlyEarnings' => $monthlyEarnings,
            'commissionRate' => $commissionRate * 100,
        ]);
    }

    public function travelerEarnings(Request $request)
    {
        $travelerId = $request->user()->id;

        $deliveries = Delivery::query()
            ->where('traveler_id', $travelerId)
            ->where('status', 'delivered')
            ->with(['order.customer', 'order.chef', 'order.orderChefs.chef'])
            ->latest()
            ->get();

        $totalEarnings = $deliveries->sum('traveler_earning');

        // Group by period
        $dailyEarnings = $deliveries->groupBy(function ($delivery) {
            return $delivery->created_at->format('Y-m-d');
        })->map(function ($group) {
            return [
                'earnings' => $group->sum('traveler_earning'),
                'deliveries' => $group->count(),
            ];
        });

        return view('traveler.earnings', [
            'deliveries' => $deliveries->take(30),
            'totalEarnings' => $totalEarnings,
            'dailyEarnings' => $dailyEarnings,
            'totalDeliveries' => $deliveries->count(),
        ]);
    }
}
