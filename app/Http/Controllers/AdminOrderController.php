<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\AdminAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $from = $request->query('from');
        $to = $request->query('to');
        $orderId = (string) $request->query('order_id', '');

        $query = Order::with(['customer', 'chef', 'delivery.traveler', 'payment'])
            ->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($orderId !== '') {
            $query->where('id', (int) $orderId);
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'orderId' => $orderId,
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'chef', 'items.meal', 'payment', 'delivery.traveler', 'delivery']);

        $availableTravelers = User::where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get();

        return view('admin.order-show', [
            'order' => $order,
            'availableTravelers' => $availableTravelers,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->status;
        $order->status = $data['status'];
        $order->save();

        $this->logAdminAction('order_force_status_change', $order, $data['reason'] ?? null, [
            'old_status' => $oldStatus,
            'new_status' => $order->status,
        ]);

        return back()->with('status', 'Order status updated.');
    }

    public function cancel(Request $request, Order $order)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->status;
        $order->status = 'cancelled';
        $order->save();

        $this->logAdminAction('order_admin_cancel', $order, $data['reason'], [
            'old_status' => $oldStatus,
        ]);

        return back()->with('status', 'Order cancelled. Refund processing should be triggered separately via the finance dashboard.');
    }

    public function reassignTraveler(Request $request, Order $order)
    {
        $data = $request->validate([
            'traveler_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $traveler = User::where('id', $data['traveler_id'])
            ->where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_APPROVED)
            ->firstOrFail();

        $delivery = $order->delivery;

        if (! $delivery) {
            $delivery = $order->delivery()->create([
                'traveler_id' => $traveler->id,
                'status' => 'assigned',
            ]);
        } else {
            $delivery->traveler_id = $traveler->id;
            if ($delivery->status === 'unassigned') {
                $delivery->status = 'assigned';
            }
            $delivery->save();
        }

        $this->logAdminAction('order_reassign_traveler', $order, $data['reason'] ?? null, [
            'delivery_id' => $delivery->id,
            'traveler_id' => $traveler->id,
        ]);

        return back()->with('status', 'Delivery reassigned to selected traveler.');
    }

    private function logAdminAction(string $action, Order $order, ?string $reason = null, array $meta = []): void
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== User::ROLE_ADMIN) {
            return;
        }

        try {
            AdminAction::create([
                'admin_id' => $admin->id,
                'target_user_id' => $order->customer_id,
                'action' => $action,
                'reason' => $reason,
                'meta' => array_merge($meta, ['order_id' => $order->id]),
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // no-op
        }
    }
}

