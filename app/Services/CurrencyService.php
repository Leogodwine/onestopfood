<?php

namespace App\Services;

class CurrencyService
{
    /** @return array<string, array{label: string, symbol: string, decimals: int, rate: float}> */
    private function fallbackCurrencies(): array
    {
        return [
            'TZS' => [
                'label' => 'Tanzanian Shilling',
                'symbol' => 'TZS',
                'decimals' => 0,
                'rate' => 1,
            ],
        ];
    }

    public function base(): string
    {
        return (string) config('currency.base', 'TZS');
    }

    public function default(): string
    {
        return (string) config('currency.default', 'TZS');
    }

    /** @return array<string, array{label: string, symbol: string, decimals: int, rate: float}> */
    public function supported(): array
    {
        $currencies = config('currency.currencies');

        return is_array($currencies) && $currencies !== []
            ? $currencies
            : $this->fallbackCurrencies();
    }

    public function isSupported(string $code): bool
    {
        return isset($this->supported()[strtoupper($code)]);
    }

    public function current(): string
    {
        $code = strtoupper((string) session('display_currency', $this->default()));

        return $this->isSupported($code) ? $code : $this->default();
    }

    public function setCurrent(string $code): void
    {
        $code = strtoupper($code);
        session(['display_currency' => $this->isSupported($code) ? $code : $this->default()]);
    }

    public function meta(string $code): array
    {
        $code = strtoupper($code);
        $supported = $this->supported();

        return $supported[$code]
            ?? $supported[$this->default()]
            ?? $this->fallbackCurrencies()['TZS'];
    }

    /** TZS per 1 unit of foreign currency */
    public function rate(string $code): float
    {
        $code = strtoupper($code);

        if ($code === $this->base()) {
            return 1.0;
        }

        return max(0.0001, (float) ($this->meta($code)['rate'] ?? 1));
    }

    public function fromTzs(float $amountTzs, ?string $toCode = null): float
    {
        $toCode = strtoupper($toCode ?? $this->current());

        if ($toCode === $this->base()) {
            return round($amountTzs, $this->meta($toCode)['decimals'] ?? 0);
        }

        return round($amountTzs / $this->rate($toCode), $this->meta($toCode)['decimals'] ?? 2);
    }

    public function format(float|int|string $amountTzs, ?string $code = null): string
    {
        $code = strtoupper($code ?? $this->current());
        $meta = $this->meta($code);
        $decimals = (int) ($meta['decimals'] ?? 2);
        $display = $this->fromTzs((float) $amountTzs, $code);
        $formatted = number_format($display, $decimals);

        if ($code === 'TZS') {
            return 'TZS ' . $formatted;
        }

        $symbol = (string) ($meta['symbol'] ?? $code);

        if (in_array($code, ['USD', 'EUR', 'GBP'], true)) {
            return $symbol . $formatted;
        }

        return $symbol . ' ' . $formatted;
    }

    public function formatWithCode(float|int|string $amountTzs, ?string $code = null): string
    {
        $code = strtoupper($code ?? $this->current());

        return $this->format($amountTzs, $code) . ($code !== 'TZS' ? ' (' . $code . ')' : '');
    }
}
