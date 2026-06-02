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
            ->with(['customer', 'items.meal', 'payment', 'sharedPayment', 'delivery', 'orderChefs', 'chef']);

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
        $needsAssignment = $delivery && (empty($delivery->traveler_id) || $delivery->status === 'unassigned');
        $canReassign = $delivery && $delivery->traveler_id && ! in_array($delivery->status, ['delivered', 'cancelled'], true);
        $orderQuantity = $this->assignment->orderItemQuantity($order);

        $nearbyTravelers = collect();
        if ($needsAssignment || $canReassign) {
            $nearbyTravelers = $this->assignment->rankTravelersForOrder($order, $request->user());
        }

        return view('chef.orders.show', compact(
            'order',
            'chefPortion',
            'nearbyTravelers',
            'needsAssignment',
            'canReassign',
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

        $data = $request->validate([
            'traveler_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $order->load(['deliveryLocation', 'items', 'delivery']);

        $traveler = User::query()
            ->where('id', $data['traveler_id'])
            ->where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_APPROVED)
            ->with(['travelerProfile', 'location'])
            ->firstOrFail();

        $exceptDeliveryId = $order->delivery?->id;
        $error = $this->assignment->validateAssignment($order, $traveler, $request->user(), true, $exceptDeliveryId);
        if ($error) {
            return back()->withErrors(['error' => $error]);
        }

        if (! $this->assignment->assignTravelerToOrder($order, $traveler, $request->user(), true, $exceptDeliveryId)) {
            return back()->withErrors(['error' => 'Could not assign traveler. Please try another traveler.']);
        }

        try {
            $traveler->notify(new DeliveryAssignedNotification($order));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'Delivery assigned to ' . $traveler->name . '.');
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
