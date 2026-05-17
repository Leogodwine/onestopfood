<?php

namespace App\Services\Airtel;

use App\Contracts\MobileMoneyGateway;
use App\Models\Payment;
use App\Services\Payments\PhoneNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AirtelMoneyService implements MobileMoneyGateway
{
    public function method(): string
    {
        return 'airtel';
    }

    public function label(): string
    {
        return 'Airtel Money';
    }

    public function isConfigured(): bool
    {
        return (bool) config('airtel.enabled')
            && config('airtel.client_id')
            && config('airtel.client_secret')
            && config('airtel.callback_url');
    }

    public function initiatePush(Payment $payment, string $phone): array
    {
        $phone = PhoneNormalizer::toTzMsisdn($phone, ['6', '7', '68', '69', '78']);
        if (! $phone) {
            return ['success' => false, 'message' => 'Enter a valid Airtel Money number (e.g. 25568XXXXXXX).'];
        }

        $msisdn = str_starts_with($phone, '255') ? substr($phone, 3) : $phone;

        $token = $this->accessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Could not connect to Airtel Money. Try again later.'];
        }

        $transactionId = 'ORD' . $payment->order_id . Str::upper(Str::random(6));
        $amount = (int) ceil((float) $payment->amount);
        $country = config('airtel.country');
        $currency = config('airtel.currency');

        $payload = [
            'reference' => 'Order #' . $payment->order_id,
            'subscriber' => [
                'country' => $country,
                'currency' => $currency,
                'msisdn' => $msisdn,
            ],
            'transaction' => [
                'amount' => $amount,
                'country' => $country,
                'currency' => $currency,
                'id' => $transactionId,
            ],
        ];

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Country' => $country,
                'X-Currency' => $currency,
            ])
            ->acceptJson()
            ->post($this->baseUrl() . '/merchant/v1/payments/', $payload);

        $body = $response->json() ?? [];

        Log::info('Airtel Money collection response', [
            'order_id' => $payment->order_id,
            'http_status' => $response->status(),
            'body' => $body,
        ]);

        $status = $body['status'] ?? [];
        $responseCode = $status['code'] ?? $body['status_code'] ?? null;
        $success = $response->successful() && in_array($responseCode, ['200', '201', 'DP00800001001', null], true);

        if (! $success && ! $response->successful()) {
            $message = $status['message'] ?? $body['message'] ?? 'Airtel Money payment request failed.';

            return ['success' => false, 'message' => $message];
        }

        $data = $body['data'] ?? $body;
        $airtelId = $data['transaction']['id'] ?? $transactionId;

        $payment->update([
            'checkout_request_id' => $airtelId,
            'merchant_request_id' => $data['transaction']['airtel_money_id'] ?? $transactionId,
            'provider_reference' => $phone,
            'status' => 'pending',
            'failure_reason' => null,
        ]);

        return [
            'success' => true,
            'message' => $status['message'] ?? 'Check your phone to approve the Airtel Money payment.',
            'checkout_request_id' => $airtelId,
        ];
    }

    public function handleCallback(array $payload): void
    {
        $transaction = $payload['transaction'] ?? $payload['data']['transaction'] ?? $payload;
        $transactionId = $transaction['id'] ?? $payload['transaction_id'] ?? null;

        if (! $transactionId) {
            Log::warning('Airtel Money callback missing transaction id', ['payload' => $payload]);

            return;
        }

        $payment = Payment::query()
            ->where('method', 'airtel')
            ->where(function ($q) use ($transactionId) {
                $q->where('checkout_request_id', $transactionId)
                    ->orWhere('merchant_request_id', $transactionId);
            })
            ->first();

        if (! $payment && preg_match('/ORD(\d+)/', (string) $transactionId, $m)) {
            $payment = Payment::query()
                ->where('method', 'airtel')
                ->where('order_id', (int) $m[1])
                ->where('status', 'pending')
                ->latest('id')
                ->first();
        }

        if (! $payment) {
            Log::warning('Airtel Money callback for unknown payment', ['id' => $transactionId]);

            return;
        }

        $statusCode = $transaction['status_code'] ?? $payload['status_code'] ?? '';
        $success = in_array($statusCode, ['TS', 'SUCCESS', '200'], true)
            || strtolower((string) ($transaction['status'] ?? '')) === 'success';

        if (! $success && $statusCode !== '') {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $transaction['message'] ?? $payload['message'] ?? 'Payment failed',
            ]);

            return;
        }

        if (! $success) {
            return;
        }

        $receipt = $transaction['airtel_money_id'] ?? $transaction['receipt_number'] ?? $transactionId;

        $payment->update([
            'status' => 'paid',
            'provider_receipt' => $receipt,
            'mpesa_receipt' => $receipt,
            'provider_reference' => $receipt,
            'paid_at' => now(),
            'failure_reason' => null,
        ]);
    }

    private function accessToken(): ?string
    {
        $response = Http::acceptJson()
            ->post($this->baseUrl() . '/auth/oauth2/token', [
                'client_id' => config('airtel.client_id'),
                'client_secret' => config('airtel.client_secret'),
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            Log::error('Airtel Money OAuth failed', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json('access_token');
    }

    private function baseUrl(): string
    {
        if ($custom = config('airtel.base_url')) {
            return rtrim($custom, '/');
        }

        return config('airtel.environment') === 'production'
            ? 'https://openapi.airtel.africa'
            : 'https://openapiuat.airtel.africa';
    }
}
