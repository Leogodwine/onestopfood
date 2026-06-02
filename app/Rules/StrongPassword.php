<?php

namespace App\Rules;

use App\Support\PasswordRules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => [PasswordRules::defaults()]],
            PasswordRules::validationMessages()
        );

        if ($validator->fails()) {
            $fail(__('auth.password_weak_help'));
        }
    }
}
