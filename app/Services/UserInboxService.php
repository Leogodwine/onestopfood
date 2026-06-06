<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderUpdateNotification;
use Illuminate\Support\Facades\Log;

class UserInboxService
{
    /** @param  array<string, mixed>  $channelsContext */
    public function channelsFor(User $user, bool $smsSent = false): array
    {
        $channels = ['in_app'];

        if (filled($user->email)) {
            $channels[] = 'email';
        }

        if ($smsSent && filled($user->phone)) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    public function orderAccepted(Order $order, User $chef): void
    {
        $this->notifyCustomer($order, 'accepted', [
            'message' => __('notifications.order.accepted', ['id' => $order->id, 'chef' => $chef->name]),
            'body' => __('notifications.order.accepted_body', ['id' => $order->id, 'chef' => $chef->name]),
        ]);
    }

    public function orderRejected(Order $order, User $chef): void
    {
        $this->notifyCustomer($order, 'cancelled', [
            'message' => __('notifications.order.rejected', ['id' => $order->id, 'chef' => $chef->name]),
            'body' => __('notifications.order.rejected_body', ['id' => $order->id, 'chef' => $chef->name]),
        ]);
    }

    /** @param  array{message?: string, body?: string}  $copy */
    public function orderStatusChanged(Order $order, string $status, ?User $actor = null, array $copy = []): void
    {
        $label = ucfirst(str_replace('_', ' ', $status));
        $message = $copy['message'] ?? __('notifications.order.status', ['id' => $order->id, 'status' => $label]);
        $body = $copy['body'] ?? __('notifications.order.status_body', ['id' => $order->id, 'status' => $label]);

        $this->notifyCustomer($order, $status, compact('message', 'body'));

        if ($actor && (int) $order->chef_id === (int) $actor->id) {
            return;
        }

        $chef = $order->chef;
        if ($chef && $actor && (int) $chef->id !== (int) $actor->id) {
            $this->safeNotify($chef, new OrderUpdateNotification($order, $status, [
                'message' => $message,
                'body' => $body,
                'url' => route('chef.orders.show', $order),
            ]));
        }
    }

    public function deliveryAssignedToTraveler(Order $order, User $traveler): void
    {
        $this->notifyCustomer($order, 'delivery_assigned', [
            'message' => __('notifications.order.delivery_assigned', ['id' => $order->id, 'traveler' => $traveler->name]),
            'body' => __('notifications.order.delivery_assigned_body', ['id' => $order->id, 'traveler' => $traveler->name]),
        ]);
    }

    public function deliveryPickedUp(Order $order): void
    {
        $this->orderStatusChanged($order, 'out_for_delivery', null, [
            'message' => __('notifications.order.out_for_delivery', ['id' => $order->id]),
            'body' => __('notifications.order.out_for_delivery_body', ['id' => $order->id]),
        ]);
    }

    public function orderDelivered(Order $order): void
    {
        $this->notifyCustomer($order, 'delivered', [
            'message' => __('notifications.order.delivered', ['id' => $order->id]),
            'body' => __('notifications.order.delivered_body', ['id' => $order->id]),
        ]);

        $chef = $order->chef;
        if ($chef) {
            $this->safeNotify($chef, new OrderUpdateNotification($order, 'delivered', [
                'message' => __('notifications.order.delivered_chef', ['id' => $order->id]),
                'body' => __('notifications.order.delivered_chef_body', ['id' => $order->id]),
                'url' => route('chef.orders.show', $order),
            ]));
        }
    }

    /** @param  array{message: string, body: string}  $copy */
    private function notifyCustomer(Order $order, string $event, array $copy): void
    {
        $customer = $order->customer;
        if (! $customer) {
            return;
        }

        $this->safeNotify($customer, new OrderUpdateNotification($order, $event, [
            'message' => $copy['message'],
            'body' => $copy['body'],
            'url' => route('orders.show', $order),
        ]));
    }

    private function safeNotify(User $user, object $notification): void
    {
        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            Log::warning('Inbox notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
