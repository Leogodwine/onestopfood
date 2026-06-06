<?php

namespace App\Support;

use App\Rules\ValidNationalPhoneNumber;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PhoneNumber
{
    /** @return array<string, array{label: string, iso?: string, national_length?: int, placeholder?: string}> */
    public static function countries(): array
    {
        return config('phone.countries', ['255' => ['label' => 'Tanzania', 'iso' => 'TZ', 'national_length' => 9]]);
    }

    public static function defaultCountryCode(): string
    {
        return (string) config('phone.default_country_code', '255');
    }

    public static function nationalLength(string $countryCode): int
    {
        $code = preg_replace('/\D+/', '', $countryCode) ?: self::defaultCountryCode();
        $meta = self::countries()[$code] ?? self::countries()[self::defaultCountryCode()];

        return max(1, (int) ($meta['national_length'] ?? 9));
    }

    public static function nationalPlaceholder(string $countryCode): string
    {
        $code = preg_replace('/\D+/', '', $countryCode) ?: self::defaultCountryCode();
        $meta = self::countries()[$code] ?? self::countries()[self::defaultCountryCode()];

        return (string) ($meta['placeholder'] ?? '712 345 678');
    }

    public static function sanitizeNational(string $national): string
    {
        $digits = preg_replace('/\D+/', '', $national) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    public static function isValidNational(string $countryCode, string $national): bool
    {
        $code = preg_replace('/\D+/', '', $countryCode) ?: self::defaultCountryCode();

        if (! isset(self::countries()[$code])) {
            return false;
        }

        $digits = self::sanitizeNational($national);
        $length = self::nationalLength($code);

        if ($digits === '' || strlen($digits) !== $length) {
            return false;
        }

        return (bool) preg_match('/^[1-9][0-9]{'.($length - 1).'}$/', $digits);
    }

    public static function invalidNationalMessage(string $countryCode): string
    {
        $code = preg_replace('/\D+/', '', $countryCode) ?: self::defaultCountryCode();
        $meta = self::countries()[$code] ?? self::countries()[self::defaultCountryCode()];
        $display = self::countryDisplay($code);

        return __('auth.phone_invalid_format', [
            'country' => $meta['label'] ?? $display['label'],
            'code' => $code,
            'length' => self::nationalLength($code),
            'example' => self::nationalPlaceholder($code),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function nationalNumberRules(string $countryCodeKey, bool $required = true): array
    {
        return array_merge(
            $required ? ['required'] : ['nullable'],
            ['string', new ValidNationalPhoneNumber($countryCodeKey)]
        );
    }

    /**
     * @return array<string, array{length: int, pattern: string, placeholder: string, title: string}>
     */
    public static function frontendRules(): array
    {
        $rules = [];

        foreach (self::countries() as $code => $meta) {
            $length = self::nationalLength((string) $code);
            $rules[$code] = [
                'length' => $length,
                'pattern' => '[1-9][0-9]{'.($length - 1).'}',
                'placeholder' => self::nationalPlaceholder((string) $code),
                'title' => self::invalidNationalMessage((string) $code),
            ];
        }

        return $rules;
    }

    public static function combine(?string $countryCode, ?string $nationalNumber): string
    {
        $code = preg_replace('/\D+/', '', (string) $countryCode) ?: self::defaultCountryCode();
        $national = self::sanitizeNational((string) $nationalNumber);

        if ($national === '') {
            throw new InvalidArgumentException('Phone number is required.');
        }

        if (! self::isValidNational($code, $national)) {
            throw new InvalidArgumentException(self::invalidNationalMessage($code));
        }

        return '+'.$code.$national;
    }

    /**
     * @return array{country_code: string, national: string, formatted: string}
     */
    public static function split(?string $full): array
    {
        $digits = preg_replace('/\D+/', '', (string) $full) ?? '';

        if ($digits === '') {
            return [
                'country_code' => self::defaultCountryCode(),
                'national' => '',
                'formatted' => '',
            ];
        }

        if (str_starts_with($digits, '0')) {
            $digits = self::defaultCountryCode().substr($digits, 1);
        }

        $codes = array_keys(self::countries());
        usort($codes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $code) {
            if (str_starts_with($digits, $code)) {
                $national = substr($digits, strlen($code));

                return [
                    'country_code' => $code,
                    'national' => $national,
                    'formatted' => '+'.$code.$national,
                ];
            }
        }

        $defaultLength = self::nationalLength(self::defaultCountryCode());
        if (
            strlen($digits) === $defaultLength
            && ! str_starts_with($digits, self::defaultCountryCode())
        ) {
            return [
                'country_code' => self::defaultCountryCode(),
                'national' => $digits,
                'formatted' => '+'.self::defaultCountryCode().$digits,
            ];
        }

        return [
            'country_code' => self::defaultCountryCode(),
            'national' => $digits,
            'formatted' => '+'.$digits,
        ];
    }

    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        try {
            $split = self::split($phone);

            return $split['formatted'] !== ''
                ? $split['formatted']
                : self::combine($split['country_code'], $split['national']);
        } catch (InvalidArgumentException) {
            $digits = preg_replace('/\D+/', '', $phone) ?? '';

            return $digits !== '' ? '+'.$digits : null;
        }
    }

    public static function mergeIntoRequest(
        Request $request,
        string $mergedKey = 'phone',
        string $countryCodeKey = 'phone_country_code',
        string $nationalKey = 'phone_number',
    ): void {
        if ($request->filled($nationalKey) || $request->filled($countryCodeKey)) {
            try {
                $request->merge([
                    $mergedKey => self::combine(
                        $request->input($countryCodeKey, self::defaultCountryCode()),
                        $request->input($nationalKey, '')
                    ),
                ]);
            } catch (InvalidArgumentException) {
                // Let validation rules on phone_number / phone report the error.
            }

            return;
        }

        if ($request->filled($mergedKey)) {
            $request->merge([
                $mergedKey => self::normalize((string) $request->input($mergedKey)),
            ]);
        }
    }

    public static function isValidCountryCode(string $code): bool
    {
        return isset(self::countries()[preg_replace('/\D+/', '', $code)]);
    }

    public static function flagEmoji(?string $iso): string
    {
        $iso = strtoupper(preg_replace('/[^A-Z]/', '', (string) $iso));

        if (strlen($iso) !== 2) {
            return '🌐';
        }

        $first = ord($iso[0]) - ord('A') + 0x1F1E6;
        $second = ord($iso[1]) - ord('A') + 0x1F1E6;

        return mb_chr($first, 'UTF-8').mb_chr($second, 'UTF-8');
    }

    /**
     * @return array{label: string, iso: string, flag: string, dial: string}
     */
    public static function countryDisplay(string $dialCode): array
    {
        $code = preg_replace('/\D+/', '', $dialCode) ?: self::defaultCountryCode();
        $meta = self::countries()[$code] ?? self::countries()[self::defaultCountryCode()];
        $iso = (string) ($meta['iso'] ?? '');

        return [
            'label' => (string) ($meta['label'] ?? $code),
            'iso' => $iso,
            'flag' => self::flagEmoji($iso),
            'dial' => '+'.$code,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(
        string $countryCodeKey = 'phone_country_code',
        string $numberKey = 'phone_number',
        ?string $mergedKey = 'phone',
    ): array {
        $invalid = __('auth.phone_invalid');
        $required = __('auth.phone_required');

        $messages = [
            "{$countryCodeKey}.required" => $invalid,
            "{$countryCodeKey}.in" => $invalid,
            "{$numberKey}.required" => $required,
            "{$numberKey}.*" => $invalid,
        ];

        if ($mergedKey !== null && $mergedKey !== '') {
            $messages["{$mergedKey}.required"] = $invalid;
        }

        return $messages;
    }
}
