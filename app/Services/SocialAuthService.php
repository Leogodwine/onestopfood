<?php

namespace App\Services;

use App\Models\LoginActivity;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    /** @var list<string> */
    public const PROVIDERS = [
        SocialAccount::PROVIDER_GOOGLE,
        SocialAccount::PROVIDER_FACEBOOK,
    ];

    public function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, self::PROVIDERS, true);
    }

    public function isConfigured(string $provider): bool
    {
        return $this->isSupportedProvider($provider)
            && (string) config("services.{$provider}.client_id") !== ''
            && (string) config("services.{$provider}.client_secret") !== '';
    }

    public function redirectUrl(Request $request, string $provider): string
    {
        $configured = trim((string) config("services.{$provider}.redirect", ''));

        if ($configured !== '' && ! str_contains($configured, '${')) {
            return rtrim($configured, '/');
        }

        return rtrim($request->getSchemeAndHttpHost(), '/')."/auth/{$provider}/callback";
    }

    public function authenticate(string $provider, SocialiteUser $socialUser, Request $request): User
    {
        if (! $socialUser->getEmail() && $provider !== SocialAccount::PROVIDER_FACEBOOK) {
            throw new \RuntimeException('The provider did not return an email address.');
        }

        return DB::transaction(function () use ($provider, $socialUser, $request) {
            $account = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $socialUser->getId())
                ->first();

            if ($account) {
                $user = $account->user;
            } else {
                $user = null;

                if ($socialUser->getEmail()) {
                    $user = User::query()->where('email', $socialUser->getEmail())->first();
                }

                if (! $user) {
                    $user = User::create([
                        'name' => $socialUser->getName() ?: ucfirst($provider).' User',
                        'email' => $socialUser->getEmail() ?: $provider.'_'.$socialUser->getId().'@social.local',
                        'password' => null,
                        'role' => User::ROLE_CUSTOMER,
                        'status' => User::STATUS_APPROVED,
                        'email_verified_at' => null,
                        'phone_verified_at' => null,
                    ]);
                }

                $account = SocialAccount::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $socialUser->getId(),
                ]);
            }

            $this->syncSocialAccount($account, $socialUser, $request);
            $this->syncUserFromSocialAccount($user, $account);

            LoginActivity::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->role === User::ROLE_ADMIN,
                'successful' => true,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'device_fingerprint' => null,
                'reason' => $provider.'_oauth',
            ]);

            return $user->fresh();
        });
    }

    private function syncSocialAccount(SocialAccount $account, SocialiteUser $socialUser, Request $request): void
    {
        $account->update([
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName(),
            'avatar_url' => $socialUser->getAvatar(),
            'access_token' => $socialUser->token ?? null,
            'refresh_token' => $socialUser->refreshToken ?? null,
            'token_expires_at' => isset($socialUser->expiresIn)
                ? now()->addSeconds((int) $socialUser->expiresIn)
                : null,
            'profile_data' => [
                'id' => $socialUser->getId(),
                'nickname' => $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
                'raw' => $socialUser->getRaw(),
            ],
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => (string) $request->userAgent(),
            'last_login_at' => now(),
        ]);
    }

    private function syncUserFromSocialAccount(User $user, SocialAccount $account): void
    {
        $updates = [];

        if ($account->name && ($user->name === '' || str_ends_with((string) $user->email, '@social.local'))) {
            $updates['name'] = $account->name;
        }

        if ($account->email && str_ends_with((string) $user->email, '@social.local')) {
            $updates['email'] = $account->email;
        }

        if ($updates !== []) {
            $user->update($updates);
        }
    }
}
