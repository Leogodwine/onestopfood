<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function isConfigured(): bool
    {
        if (! config('sms.enabled', false)) {
            return false;
        }

        return config('sms.driver') === 'log'
            || (config('sms.driver') === 'africas_talking'
                && config('sms.africas_talking.username')
                && config('sms.africas_talking.api_key'));
    }

    public function send(?string $phone, string $message): bool
    {
        $phone = \App\Support\PhoneNumber::normalize($phone);
        if ($phone === null || trim($message) === '') {
            return false;
        }

        if (! config('sms.enabled', false)) {
            Log::info('SMS skipped (disabled)', ['phone' => $phone, 'message' => $message]);

            return false;
        }

        $driver = config('sms.driver', 'log');

        if ($driver === 'log') {
            Log::info('SMS', ['phone' => $phone, 'message' => $message]);

            return true;
        }

        if ($driver === 'africas_talking') {
            return $this->sendViaAfricasTalking($phone, $message);
        }

        Log::warning('SMS driver not supported', ['driver' => $driver]);

        return false;
    }

    private function sendViaAfricasTalking(string $phone, string $message): bool
    {
        $username = config('sms.africas_talking.username');
        $apiKey = config('sms.africas_talking.api_key');
        $from = config('sms.africas_talking.from');

        if (! $username || ! $apiKey) {
            Log::warning('Africa\'s Talking SMS not configured');

            return false;
        }

        try {
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => $username,
                'to' => $phone,
                'message' => $message,
                'from' => $from,
            ]);

            if (! $response->successful()) {
                Log::warning('SMS send failed', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS exception', ['phone' => $phone, 'error' => $e->getMessage()]);

            return false;
        }
    }

}
