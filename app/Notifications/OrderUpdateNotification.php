<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param  array{message?: string, body?: string, url?: string, channels_sent?: array<int, string>}  $payload */
    public function __construct(
        public Order $order,
        public string $event,
        public array $payload = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $this->event));

        return [
            'type' => 'order_'.$this->event,
            'category' => 'orders',
            'order_id' => $this->order->id,
            'message' => $this->payload['message'] ?? __('notifications.order.status', [
                'id' => $this->order->id,
                'status' => $statusLabel,
            ]),
            'body' => $this->payload['body'] ?? __('notifications.order.status_body', [
                'id' => $this->order->id,
                'status' => $statusLabel,
            ]),
            'url' => $this->payload['url'] ?? route('orders.show', $this->order),
            'channels_sent' => $this->payload['channels_sent'] ?? ['in_app'],
        ];
    }
}
