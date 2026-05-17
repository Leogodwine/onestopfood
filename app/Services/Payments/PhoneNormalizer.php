<?php

namespace App\Services\Payments;

class PhoneNormalizer
{
    /**
     * Normalize to Tanzania MSISDN (255XXXXXXXXX).
     */
    public static function toTzMsisdn(string $phone, array $allowedFirstDigits = ['6', '7']): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '255' . substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            $digits = '255' . $digits;
        }

        if (str_starts_with($digits, '255') && strlen($digits) === 12) {
            $nationalFirst = $digits[3];
            foreach ($allowedFirstDigits as $allowed) {
                if ($nationalFirst === $allowed || str_starts_with(substr($digits, 3), $allowed)) {
                    return $digits;
                }
            }
        }

        return null;
    }
}
