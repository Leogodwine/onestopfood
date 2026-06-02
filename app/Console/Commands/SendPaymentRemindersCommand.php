<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Notifications\CompletePaymentReminderNotification;
use Illuminate\Console\Command;

class SendPaymentRemindersCommand extends Command
{
    protected $signature = 'payments:send-reminders';

    protected $description = 'Remind customers to complete pending payments after the configured delay';

    public function handle(): int
    {
        $minutes = max(1, (int) config('food_delivery.payment_reminder_minutes', 30));
        $cutoff = now()->subMinutes($minutes);

        $payments = Payment::query()
            ->where('status', 'pending')
            ->whereNull('payment_reminder_sent_at')
            ->where('created_at', '<=', $cutoff)
            ->with(['order.customer'])
            ->get();

        $sent = 0;

        foreach ($payments as $payment) {
            $customer = $payment->order?->customer;
            if (! $customer) {
                continue;
            }

            try {
                $customer->notify(new CompletePaymentReminderNotification($payment));
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            $payment->update(['payment_reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} payment reminder(s).");

        return self::SUCCESS;
    }
}
