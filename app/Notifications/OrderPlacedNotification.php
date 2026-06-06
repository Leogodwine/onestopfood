<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isCustomer = (int) $notifiable->id === (int) $this->order->customer_id;

        return [
            'type' => 'order_placed',
            'category' => 'orders',
            'order_id' => $this->order->id,
            'total' => (float) $this->order->total,
            'message' => $isCustomer
                ? __('notifications.order.placed_customer', ['id' => $this->order->id, 'total' => number_format((float) $this->order->total, 0)])
                : __('notifications.order.placed_chef', ['id' => $this->order->id, 'total' => number_format((float) $this->order->total, 0)]),
            'body' => $isCustomer
                ? __('notifications.order.placed_customer_body', ['id' => $this->order->id, 'total' => number_format((float) $this->order->total, 0)])
                : __('notifications.order.placed_chef_body', ['id' => $this->order->id, 'total' => number_format((float) $this->order->total, 0)]),
            'url' => $isCustomer ? route('orders.show', $this->order) : route('chef.orders.show', $this->order),
            'channels_sent' => ['in_app', 'email'],
        ];
    }
}
