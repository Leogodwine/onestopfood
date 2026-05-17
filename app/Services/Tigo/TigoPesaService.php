<?php

namespace App\Services\Tigo;

use App\Contracts\MobileMoneyGateway;
use App\Models\Payment;
use App\Services\Payments\PhoneNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TigoPesaService implements MobileMoneyGateway
{
    public function method(): string
    {
        return 'tigo';
    }

    public function label(): string
    {
        return 'Tigo Pesa';
    }

    public function isConfigured(): bool
    {
        return (bool) config('tigo.enabled')
            && config('tigo.client_id')
            && config('tigo.client_secret')
            && config('tigo.merchant_id')
            && config('tigo.callback_url');
    }

    public function initiatePush(Payment $payment, string $phone): array
    {
        $phone = PhoneNormalizer::toTzMsisdn($phone);
        if (! $phone) {
            return ['success' => false, 'message' => 'Enter a valid Tigo Pesa number (e.g. 25565XXXXXXX).'];
        }

        $token = $this->accessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Could not connect to Tigo Pesa. Try again later.'];
        }

        $reference = 'ORD' . $payment->order_id . Str::upper(Str::random(4));
        $amount = (int) ceil((float) $payment->amount);

        $payload = [
            'merchantId' => config('tigo.merchant_id'),
            'msisdn' => $phone,
            'amount' => $amount,
            'currency' => 'TZS',
            'externalReference' => $reference,
            'callbackUrl' => config('tigo.callback_url'),
            'remarks' => 'Order #' . $payment->order_id,
        ];

        if ($account = config('tigo.account_msisdn')) {
            $payload['accountMsisdn'] = $account;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->baseUrl() . config('tigo.push_path'), $payload);

        $body = $response->json() ?? [];

        Log::info('Tigo Pesa push response', [
            'order_id' => $payment->order_id,
            'http_status' => $response->status(),
            'body' => $body,
        ]);

        if (! $response->successful()) {
            $message = $body['message'] ?? $body['error_description'] ?? $body['error'] ?? 'Tigo Pesa push failed.';

            return ['success' => false, 'message' => $message];
        }

        $transactionId = $body['transactionId']
            ?? $body['referenceId']
            ?? $body['data']['transactionId']
            ?? $reference;

        $payment->update([
            'checkout_request_id' => $transactionId,
            'merchant_request_id' => $body['merchantRequestId'] ?? $reference,
            'provider_reference' => $phone,
            'status' => 'pending',
            'failure_reason' => null,
        ]);

        return [
            'success' => true,
            'message' => $body['message'] ?? $body['responseDescription'] ?? 'Check your phone to approve the Tigo Pesa payment.',
            'checkout_request_id' => $transactionId,
        ];
    }

    public function handleCallback(array $payload): void
    {
        $reference = $payload['externalReference']
            ?? $payload['reference']
            ?? $payload['transactionId']
            ?? $payload['data']['transactionId']
            ?? null;

        $checkoutId = $payload['checkoutRequestId']
            ?? $payload['transactionId']
            ?? $reference;

        if (! $checkoutId) {
            Log::warning('Tigo Pesa callback missing transaction reference', ['payload' => $payload]);

            return;
        }

        $payment = Payment::query()
            ->where('method', 'tigo')
            ->where(function ($q) use ($checkoutId, $reference) {
                $q->where('checkout_request_id', $checkoutId)
                    ->orWhere('merchant_request_id', $reference);
            })
            ->first();

        if (! $payment && $reference && preg_match('/ORD(\d+)/', (string) $reference, $m)) {
            $payment = Payment::query()
                ->where('method', 'tigo')
                ->where('order_id', (int) $m[1])
                ->where('status', 'pending')
                ->latest('id')
                ->first();
        }

        if (! $payment) {
            Log::warning('Tigo Pesa callback for unknown payment', ['id' => $checkoutId]);

            return;
        }

        $status = strtolower((string) ($payload['status'] ?? $payload['transactionStatus'] ?? ''));
        $success = in_array($status, ['success', 'successful', 'completed', 'paid'], true)
            || ($payload['resultCode'] ?? null) === '0'
            || ($payload['success'] ?? false) === true;

        if (! $success && $status !== '') {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $payload['message'] ?? $payload['description'] ?? 'Payment declined',
            ]);

            return;
        }

        if (! $success) {
            return;
        }

        $receipt = $payload['receiptNumber']
            ?? $payload['receipt']
            ?? $payload['financialTransactionId']
            ?? $checkoutId;

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
        $response = Http::asForm()
            ->post($this->baseUrl() . config('tigo.token_path'), [
                'grant_type' => 'client_credentials',
                'client_id' => config('tigo.client_id'),
                'client_secret' => config('tigo.client_secret'),
            ]);

        if (! $response->successful()) {
            Log::error('Tigo Pesa OAuth failed', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json('access_token') ?? $response->json('data.access_token');
    }

    private function baseUrl(): string
    {
        if ($custom = config('tigo.base_url')) {
            return rtrim($custom, '/');
        }

        return config('tigo.environment') === 'production'
            ? 'https://api.tigo.co.tz'
            : 'https://api.tigo.co.tz/sandbox';
    }
}
