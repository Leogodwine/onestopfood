<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DeliveryAssignedNotification extends Notification implements ShouldQueue
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
        return [
            'type' => 'delivery_assigned',
            'category' => 'orders',
            'order_id' => $this->order->id,
            'delivery_fee' => (float) $this->order->delivery_fee,
            'message' => __('notifications.order.delivery_assigned_traveler', [
                'id' => $this->order->id,
                'earning' => number_format((float) $this->order->delivery_fee, 0),
            ]),
            'body' => __('notifications.order.delivery_assigned_traveler_body', [
                'id' => $this->order->id,
                'earning' => number_format((float) $this->order->delivery_fee, 0),
            ]),
            'url' => route('traveler.deliveries'),
            'channels_sent' => ['in_app'],
        ];
    }
}
