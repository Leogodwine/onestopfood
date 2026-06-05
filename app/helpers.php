<?php

use App\Services\CurrencyService;

if (! function_exists('money')) {
    /**
     * Format a TZS amount for display in the user's selected currency.
     */
    function money(float|int|string $amountTzs, ?string $currency = null): string
    {
        return app(CurrencyService::class)->format($amountTzs, $currency);
    }
}

if (! function_exists('display_currency')) {
    function display_currency(): string
    {
        return app(CurrencyService::class)->current();
    }
}
