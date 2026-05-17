<?php

namespace App\Contracts;

use App\Models\Payment;

interface MobileMoneyGateway
{
    public function method(): string;

    public function label(): string;

    public function isConfigured(): bool;

    /**
     * @return array{success: bool, message: string, checkout_request_id?: string}
     */
    public function initiatePush(Payment $payment, string $phone): array;

    public function handleCallback(array $payload): void;
}
