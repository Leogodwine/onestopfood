<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDER_FACEBOOK = 'facebook';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'email',
        'name',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'profile_data',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'profile_data' => 'array',
            'token_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            self::PROVIDER_GOOGLE => 'Google',
            self::PROVIDER_FACEBOOK => 'Facebook',
            default => ucfirst($this->provider),
        };
    }
}
