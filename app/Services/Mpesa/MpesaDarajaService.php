<?php

namespace App\Services\Mpesa;

use App\Contracts\MobileMoneyGateway;
use App\Models\Payment;
use App\Services\Payments\PhoneNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaDarajaService implements MobileMoneyGateway
{
    public function method(): string
    {
        return 'mpesa';
    }

    public function label(): string
    {
        return 'M-Pesa';
    }

    public function isConfigured(): bool
    {
        return (bool) config('mpesa.enabled')
            && config('mpesa.consumer_key')
            && config('mpesa.consumer_secret')
            && config('mpesa.shortcode')
            && config('mpesa.passkey')
            && config('mpesa.callback_url');
    }

    public function initiatePush(Payment $payment, string $phone): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'M-Pesa is not configured on this server.'];
        }

        $phone = PhoneNormalizer::toTzMsisdn($phone);
        if (! $phone) {
            return ['success' => false, 'message' => 'Enter a valid M-Pesa number (e.g. 255712345678).'];
        }

        $token = $this->accessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Could not connect to M-Pesa. Try again later.'];
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode(
            config('mpesa.shortcode') . config('mpesa.passkey') . $timestamp
        );

        $amount = (int) ceil((float) $payment->amount);
        $accountRef = 'ORDER' . $payment->order_id;

        $payload = [
            'BusinessShortCode' => config('mpesa.shortcode'),
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => config('mpesa.transaction_type'),
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => config('mpesa.shortcode'),
            'PhoneNumber' => $phone,
            'CallBackURL' => config('mpesa.callback_url'),
            'AccountReference' => Str::limit($accountRef, 12, ''),
            'TransactionDesc' => Str::limit('Food order #' . $payment->order_id, 20, ''),
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', $payload);

        $body = $response->json() ?? [];

        Log::info('M-Pesa STK push response', [
            'order_id' => $payment->order_id,
            'http_status' => $response->status(),
            'body' => $body,
        ]);

        if (! $response->successful()) {
            $message = $body['errorMessage'] ?? $body['error'] ?? 'STK push request failed.';

            return ['success' => false, 'message' => $message];
        }

        $checkoutId = $body['CheckoutRequestID'] ?? null;
        $merchantId = $body['MerchantRequestID'] ?? null;

        if (! $checkoutId) {
            return ['success' => false, 'message' => $body['ResponseDescription'] ?? 'Invalid response from M-Pesa.'];
        }

        $payment->update([
            'checkout_request_id' => $checkoutId,
            'merchant_request_id' => $merchantId,
            'provider_reference' => $phone,
            'status' => 'pending',
            'failure_reason' => null,
        ]);

        return [
            'success' => true,
            'message' => $body['CustomerMessage'] ?? 'Check your phone to enter your M-Pesa PIN.',
            'checkout_request_id' => $checkoutId,
        ];
    }

  /**
   * Handle Daraja STK callback JSON body.
   */
    public function handleCallback(array $payload): void
    {
        $callback = $payload['Body']['stkCallback'] ?? null;
        if (! $callback) {
            return;
        }

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        if (! $checkoutRequestId) {
            return;
        }

        $payment = Payment::query()
            ->where('checkout_request_id', $checkoutRequestId)
            ->first();

        if (! $payment) {
            Log::warning('M-Pesa callback for unknown CheckoutRequestID', ['id' => $checkoutRequestId]);

            return;
        }

        $resultCode = (int) ($callback['ResultCode'] ?? -1);
        $resultDesc = $callback['ResultDesc'] ?? 'Unknown result';

        if ($resultCode !== 0) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $resultDesc,
            ]);

            return;
        }

        $receipt = null;
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                $receipt = $item['Value'] ?? null;
                break;
            }
        }

        $payment->update([
            'status' => 'paid',
            'mpesa_receipt' => $receipt,
            'provider_receipt' => $receipt,
            'provider_reference' => $receipt ?: $payment->provider_reference,
            'paid_at' => now(),
            'failure_reason' => null,
        ]);
    }

    private function accessToken(): ?string
    {
        $key = config('mpesa.consumer_key');
        $secret = config('mpesa.consumer_secret');

        $response = Http::withBasicAuth($key, $secret)
            ->get($this->baseUrl() . '/oauth/v1/generate', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            Log::error('M-Pesa OAuth failed', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json('access_token');
    }

    private function baseUrl(): string
    {
        if ($custom = config('mpesa.base_url')) {
            return rtrim($custom, '/');
        }

        return config('mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

}
