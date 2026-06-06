<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $orders = $this->payment->batchOrders()->get();
        $orderList = $orders->map(fn (Order $order) => '#'.$order->id)->join(', ');
        $primaryOrder = $this->payment->order ?? $orders->first();
        $amount = number_format((float) $this->payment->amount, 0);

        if ($this->payment->status === 'paid') {
            return [
                'type' => 'payment_paid',
                'category' => 'payments',
                'payment_id' => $this->payment->id,
                'message' => __('notifications.payment.paid', ['orders' => $orderList, 'amount' => $amount]),
                'body' => __('notifications.payment.paid_body', ['orders' => $orderList, 'amount' => $amount]),
                'url' => $primaryOrder ? route('orders.show', $primaryOrder) : route('notifications.index'),
                'channels_sent' => ['in_app'],
            ];
        }

        return [
            'type' => 'payment_failed',
            'category' => 'payments',
            'payment_id' => $this->payment->id,
            'message' => __('notifications.payment.failed', ['orders' => $orderList]),
            'body' => __('notifications.payment.failed_body', [
                'orders' => $orderList,
                'reason' => $this->payment->failure_reason ?? __('notifications.payment.unknown_reason'),
            ]),
            'url' => $primaryOrder ? route('orders.show', $primaryOrder) : route('notifications.index'),
            'channels_sent' => ['in_app'],
        ];
    }
}
