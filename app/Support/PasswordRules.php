<?php

namespace App\Support;

use App\Rules\StrongPassword;
use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    /**
     * Strong password rules. Stricter in production (HIBP check when enabled).
     */
    public static function defaults(): Password
    {
        $minLength = max(8, (int) config('auth.password_min_length', 8));

        $rule = Password::min($minLength)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();

        if (config('auth.password_uncompromised', false)) {
            $rule->uncompromised();
        }

        return $rule;
    }

    /**
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        $weakHelp = __('auth.password_weak_help');

        return [
            'password.min' => $weakHelp,
            'password.confirmed' => __('auth.password_confirmed'),
            'password.letters' => $weakHelp,
            'password.mixed' => $weakHelp,
            'password.numbers' => $weakHelp,
            'password.symbols' => $weakHelp,
            'password.uncompromised' => $weakHelp,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function registerMessages(): array
    {
        return array_merge(self::validationMessages(), [
            'name.required' => __('auth.name_required'),
            'email.required' => __('auth.email_required'),
            'phone.required' => __('auth.phone_required'),
            'role.required' => __('auth.role_required'),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function forRegistration(): array
    {
        return ['required', 'string', new StrongPassword(), 'confirmed'];
    }

    /**
     * @return array<int, mixed>
     */
    public static function forReset(): array
    {
        return ['required', 'string', new StrongPassword(), 'confirmed'];
    }
}
