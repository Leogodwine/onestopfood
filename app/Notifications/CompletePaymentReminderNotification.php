<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CompletePaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $orders = $this->payment->batchOrders()->pluck('id');
        $orderList = $orders->map(fn ($id) => '#'.$id)->join(', ');

        return [
            'type' => 'payment_reminder',
            'payment_id' => $this->payment->id,
            'order_ids' => $orders->all(),
            'message' => __('payments.reminder_message', [
                'amount' => number_format((float) $this->payment->amount, 0),
                'orders' => $orderList,
            ]),
        ];
    }
}
