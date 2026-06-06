<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNationalPhoneNumber implements ValidationRule
{
    public function __construct(
        private readonly string $countryCodeField = 'phone_country_code',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $countryCode = request()->input($this->countryCodeField, PhoneNumber::defaultCountryCode());

        if (! PhoneNumber::isValidNational($countryCode, (string) $value)) {
            $fail(PhoneNumber::invalidNationalMessage($countryCode));
        }
    }
}
