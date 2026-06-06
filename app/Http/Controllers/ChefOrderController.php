<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderChef;
use App\Models\User;
use App\Notifications\DeliveryAssignedNotification;
use App\Services\DeliveryAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChefOrderController extends Controller
{
    public function __construct(
        private readonly DeliveryAssignmentService $assignment
    ) {}

    public function index(Request $request)
    {
        $chefId = $request->user()->id;

        $query = Order::query()
            ->forChef($chefId)
            ->with(['customer', 'items.meal', 'payment', 'sharedPayment', 'delivery.traveler', 'orderChefs', 'chef']);

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

        if (! $orderChef && ! $isLegacyChef) {
            abort(403);
        }

        $order->load(['customer', 'items.meal', 'payment', 'sharedPayment', 'delivery.traveler', 'deliveryLocation', 'orderChefs.chef']);
        $chefPortion = $orderChef ?? null;

        $delivery = $order->delivery;
        $canManageDelivery = $this->canChefManageDelivery($order);
        $needsAssignment = $canManageDelivery && (! $delivery || empty($delivery->traveler_id) || $delivery->status === 'unassigned');
        $canReassign = $canManageDelivery && $delivery && $delivery->traveler_id && ! in_array($delivery->status, ['delivered', 'cancelled', 'unassigned'], true);
        $orderQuantity = $this->assignment->orderItemQuantity($order);

        $nearbyTravelers = $canManageDelivery
            ? $this->assignment->rankTravelersForOrder($order, $request->user())
            : collect();

        $availableTravelers = $canManageDelivery
            ? User::query()
                ->where('role', User::ROLE_TRAVELER)
                ->where('status', User::STATUS_APPROVED)
                ->whereHas('travelerProfile')
                ->with('travelerProfile')
                ->orderBy('name')
                ->get()
            : collect();

        return view('chef.orders.show', compact(
            'order',
            'chefPortion',
            'nearbyTravelers',
            'availableTravelers',
            'needsAssignment',
            'canReassign',
            'canManageDelivery',
            'orderQuantity'
        ));
    }

    public function assignTraveler(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (! $orderChef && ! $isLegacyChef) {
            abort(403);
        }

        if (! $this->canChefManageDelivery($order)) {
            return back()->withErrors(['error' => 'This order can no longer be assigned to a traveler.']);
        }

        $data = $request->validate([
            'traveler_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $order->load(['deliveryLocation', 'items', 'delivery', 'chef.chefProfile']);

        $traveler = User::query()
            ->where('id', $data['traveler_id'])
            ->where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_APPROVED)
            ->with(['travelerProfile', 'location'])
            ->firstOrFail();

        $hadTraveler = (bool) $order->delivery?->traveler_id;
        $exceptDeliveryId = $order->delivery?->id;
        $error = $this->assignment->validateAssignment($order, $traveler, $request->user(), false, $exceptDeliveryId);
        if ($error) {
            return back()->withErrors(['error' => $error]);
        }

        if (! $this->assignment->assignTravelerToOrder($order, $traveler, $request->user(), false, $exceptDeliveryId)) {
            return back()->withErrors(['error' => 'Could not assign traveler. Check distance, vehicle capacity, and availability.']);
        }

        try {
            $traveler->notify(new DeliveryAssignedNotification($order));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'Delivery '.($hadTraveler ? 'reassigned' : 'assigned').' to '.$traveler->name.'.');
    }

    private function canChefManageDelivery(Order $order): bool
    {
        if (in_array($order->status, ['cancelled', 'delivered'], true)) {
            return false;
        }

        $delivery = $order->delivery;
        if ($delivery && in_array($delivery->status, ['delivered', 'cancelled'], true)) {
            return false;
        }

        return true;
    }

    public function accept(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (! $orderChef && ! $isLegacyChef) {
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

        if (! $orderChef && ! $isLegacyChef) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['error' => 'Order cannot be rejected']);
        }

        if ($orderChef) {
            $orderChef->update(['status' => 'rejected']);
        }
        $order->update(['status' => 'cancelled']);

        $payment = $order->effectivePayment();
        if ($payment && $payment->isPaid()) {
            $payment->update(['status' => 'refunded']);
        }

        return back()->with('status', 'Order rejected');
    }

    public function updateStatus(Order $order, Request $request)
    {
        $chefId = $request->user()->id;
        $orderChef = $order->orderChefs()->where('chef_id', $chefId)->first();
        $isLegacyChef = (int) $order->chef_id === $chefId;

        if (! $orderChef && ! $isLegacyChef) {
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
