<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChef;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class ChefLogisticsController extends Controller
{
    public function index(Request $request)
    {
        $chefId = $request->user()->id;
        $commissionRate = (int) SystemSetting::getValue('chef_commission_rate', 10) / 100;

        $orderIds = OrderChef::where('chef_id', $chefId)->pluck('order_id')
            ->merge(Order::where('chef_id', $chefId)->pluck('id'))
            ->unique()
            ->values();

        $query = Order::query()
            ->whereIn('id', $orderIds)
            ->whereHas('delivery', fn ($q) => $q->whereNotNull('traveler_id'))
            ->with([
                'customer',
                'delivery.traveler',
                'orderChefs' => fn ($q) => $q->where('chef_id', $chefId),
            ]);

        if ($request->filled('traveler_id')) {
            $query->whereHas('delivery', fn ($q) => $q->where('traveler_id', $request->integer('traveler_id')));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $rows = $orders->getCollection()->map(function (Order $order) use ($chefId, $commissionRate) {
            $subtotal = $this->chefSubtotal($order, $chefId);
            $commission = round($subtotal * $commissionRate, 2);

            return (object) [
                'order' => $order,
                'traveler' => $order->delivery?->traveler,
                'delivery' => $order->delivery,
                'subtotal' => $subtotal,
                'commission' => $commission,
                'net' => round($subtotal - $commission, 2),
            ];
        });

        $orders->setCollection($rows);

        $allOrderIds = $orderIds;
        $allDeliveries = Order::query()
            ->whereIn('id', $allOrderIds)
            ->whereHas('delivery', fn ($q) => $q->whereNotNull('traveler_id'))
            ->with(['delivery.traveler', 'orderChefs' => fn ($q) => $q->where('chef_id', $chefId)])
            ->get();

        $travelerSummaries = $allDeliveries
            ->groupBy(fn (Order $order) => $order->delivery->traveler_id)
            ->map(function ($group) use ($chefId, $commissionRate) {
                $traveler = $group->first()->delivery->traveler;
                $revenue = $group->sum(fn (Order $order) => $this->chefSubtotal($order, $chefId));
                $commission = round($revenue * $commissionRate, 2);

                return (object) [
                    'traveler' => $traveler,
                    'orders_count' => $group->count(),
                    'revenue' => $revenue,
                    'commission' => $commission,
                    'net' => round($revenue - $commission, 2),
                ];
            })
            ->sortByDesc('orders_count')
            ->values();

        $totalCommission = $travelerSummaries->sum('commission');
        $totalOrders = $allDeliveries->count();

        return view('chef.logistics.index', [
            'orders' => $orders,
            'travelerSummaries' => $travelerSummaries,
            'travelers' => $travelerSummaries->pluck('traveler')->filter()->unique('id')->values(),
            'selectedTravelerId' => $request->input('traveler_id'),
            'commissionRate' => $commissionRate * 100,
            'totalTravelers' => $travelerSummaries->count(),
            'totalOrders' => $totalOrders,
            'totalCommission' => $totalCommission,
        ]);
    }

    private function chefSubtotal(Order $order, int $chefId): float
    {
        $portion = $order->orderChefs->firstWhere('chef_id', $chefId);

        if ($portion) {
            return (float) $portion->subtotal;
        }

        if ((int) $order->chef_id === $chefId) {
            return (float) $order->subtotal;
        }

        return 0.0;
    }
}
