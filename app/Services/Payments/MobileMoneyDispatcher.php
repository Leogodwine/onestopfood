<?php

namespace App\Services\Payments;

use App\Contracts\MobileMoneyGateway;
use App\Models\Payment;
use App\Services\Airtel\AirtelMoneyService;
use App\Services\Mpesa\MpesaDarajaService;
use App\Services\Tigo\TigoPesaService;
use InvalidArgumentException;

class MobileMoneyDispatcher
{
    /** @var array<string, MobileMoneyGateway> */
    private array $gateways;

    public function __construct(
        MpesaDarajaService $mpesa,
        TigoPesaService $tigo,
        AirtelMoneyService $airtel,
    ) {
        $this->gateways = [
            $mpesa->method() => $mpesa,
            $tigo->method() => $tigo,
            $airtel->method() => $airtel,
        ];
    }

    public function gatewayFor(string $method): ?MobileMoneyGateway
    {
        return $this->gateways[$method] ?? null;
    }

    public function supports(string $method): bool
    {
        return isset($this->gateways[$method]);
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function initiate(Payment $payment, string $phone): array
    {
        $gateway = $this->gatewayFor($payment->method);
        if (! $gateway) {
            throw new InvalidArgumentException('Unsupported mobile money method: ' . $payment->method);
        }

        if (! $gateway->isConfigured()) {
            return [
                'success' => false,
                'message' => $gateway->label() . ' is not configured on this server.',
            ];
        }

        return $gateway->initiatePush($payment, $phone);
    }
}
