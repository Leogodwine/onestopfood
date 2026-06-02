<?php

namespace App\Services;

use App\Mail\SocialSignupOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SocialSignupService
{
    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_PHONE = 'phone';

    public const CHANNEL_BOTH = 'both';

    public const RESEND_COOLDOWN_SECONDS = 45;

    public const OTP_EXPIRY_MINUTES = 10;

    public function needsCompletion(User $user): bool
    {
        if (! $user->isSocialOnlyUser()) {
            return false;
        }

        return empty($user->phone)
            || ! $user->email_verified_at
            || ! $user->phone_verified_at;
    }

    public function sendOtp(User $user, string $phone): void
    {
        if ($this->hasDeliverableEmail($user) === false && $phone === '') {
            throw new \RuntimeException('A phone number is required for verification.');
        }

        $code = (string) random_int(100000, 999999);

        $user->update([
            'phone' => $phone,
            'signup_otp_code' => $code,
            'signup_otp_expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'signup_otp_channel' => self::CHANNEL_BOTH,
        ]);

        $user = $user->fresh();

        if ($this->hasDeliverableEmail($user)) {
            try {
                Mail::to($user->email)->send(new SocialSignupOtpMail($user, $code));
            } catch (\Throwable $e) {
                report($e);
                logger()->warning('Social signup OTP email failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (config('app.show_developer_hints')) {
            logger()->info('Social signup OTP (dev)', [
                'user_id' => $user->id,
                'phone' => $phone,
                'code' => $code,
            ]);
        } else {
            logger()->info('Social signup OTP sent', [
                'user_id' => $user->id,
                'phone' => $phone,
            ]);
        }
    }

    public function verifyOtp(User $user, string $code): bool
    {
        if (
            ! $user->signup_otp_code
            || ! $user->signup_otp_expires_at
            || now()->greaterThan($user->signup_otp_expires_at)
            || ! hash_equals($user->signup_otp_code, $code)
        ) {
            return false;
        }

        $user->update([
            'signup_otp_code' => null,
            'signup_otp_expires_at' => null,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'phone_verified_at' => $user->phone_verified_at ?? now(),
        ]);

        return true;
    }

    public function otpHintForRequest(Request $request, User $user): ?string
    {
        if (! config('app.show_developer_hints')) {
            return null;
        }

        return $user->signup_otp_code ?: $request->session()->get('social_signup_otp_hint');
    }

    public function hasDeliverableEmail(User $user): bool
    {
        return filled($user->email) && ! str_ends_with((string) $user->email, '@social.local');
    }

    public function otpDeliverySummary(User $user): string
    {
        $parts = [];

        if ($this->hasDeliverableEmail($user)) {
            $parts[] = 'your linked email';
        }

        if (filled($user->phone)) {
            $parts[] = 'your phone number';
        }

        return implode(' and ', $parts) ?: 'your contact details';
    }

    public function resendCooldownRemaining(?int $sentAtTimestamp): int
    {
        if ($sentAtTimestamp === null) {
            return 0;
        }

        $elapsed = time() - $sentAtTimestamp;

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }
}
