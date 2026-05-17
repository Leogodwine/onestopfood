<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\OrderChef;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderPortionPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public OrderChef $orderChef
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_portion_placed',
            'order_id' => $this->order->id,
            'order_chef_id' => $this->orderChef->id,
            'subtotal' => (float) $this->orderChef->subtotal,
            'message' => 'Order #' . $this->order->id . ' – your portion: TZS ' . number_format((float) $this->orderChef->subtotal, 2),
        ];
    }
}
