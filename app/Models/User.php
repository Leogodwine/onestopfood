<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'phone',
        'password',
        'role',
        'is_super_admin',
        'admin_title',
        'status',
        'suspended_by',
        'deactivated_at',
        'locale',
        'approved_at',
        'failed_login_attempts',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_device_fingerprint',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'signup_otp_code',
        'signup_otp_expires_at',
        'signup_otp_channel',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'is_super_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_expires_at' => 'datetime',
            'signup_otp_expires_at' => 'datetime',
        ];
    }

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CHEF = 'chef';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_TRAVELER = 'traveler';

    public const ADMIN_TITLE_CEO = 'ceo';
    public const ADMIN_TITLE_MANAGER = 'manager';
    public const ADMIN_TITLE_SYSTEM_ADMINISTRATOR = 'system_administrator';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public const SUSPENDED_BY_SELF = 'self';

    public const SUSPENDED_BY_ADMIN = 'admin';

    public function location()
    {
        return $this->hasOne(Location::class)->where('is_primary', true);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function chefProfile()
    {
        return $this->hasOne(ChefProfile::class);
    }

    public function travelerProfile()
    {
        return $this->hasOne(TravelerProfile::class);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class, 'chef_id');
    }

    public function ordersAsCustomer()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function ordersAsChef()
    {
        return $this->hasMany(Order::class, 'chef_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'traveler_id');
    }

    public function verificationDocuments()
    {
        return $this->hasMany(UserVerificationDocument::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function isSocialOnlyUser(): bool
    {
        return $this->socialAccounts()->exists() && empty($this->password);
    }

    /**
     * Roles that can create their own account (register or partner signup).
     */
    public function isSelfRegisteredRole(): bool
    {
        return in_array($this->role, [
            self::ROLE_CUSTOMER,
            self::ROLE_CHEF,
            self::ROLE_TRAVELER,
        ], true);
    }

    public function isSelfDeactivated(): bool
    {
        return $this->status === self::STATUS_SUSPENDED
            && $this->suspended_by === self::SUSPENDED_BY_SELF;
    }

    public function canSelfReactivate(): bool
    {
        return $this->isSelfDeactivated();
    }

    public function accountActionRequests()
    {
        return $this->hasMany(AccountActionRequest::class);
    }

    public function pendingDeletionRequest(): ?AccountActionRequest
    {
        return $this->accountActionRequests()
            ->where('action', AccountActionRequest::ACTION_DELETION)
            ->where('status', AccountActionRequest::STATUS_PENDING)
            ->latest()
            ->first();
    }

    public function effectiveAdminTitle(): ?string
    {
        return app(\App\Services\AdminAccessService::class)->effectiveTitle($this);
    }

    public function adminCan(string $permission): bool
    {
        return app(\App\Services\AdminAccessService::class)->can($this, $permission);
    }

    public function isCeo(): bool
    {
        return $this->role === self::ROLE_ADMIN
            && $this->effectiveAdminTitle() === self::ADMIN_TITLE_CEO;
    }

    public function isOperationsManager(): bool
    {
        return $this->role === self::ROLE_ADMIN
            && $this->effectiveAdminTitle() === self::ADMIN_TITLE_MANAGER;
    }

    public function isSystemAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMIN
            && $this->effectiveAdminTitle() === self::ADMIN_TITLE_SYSTEM_ADMINISTRATOR;
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'chef_id')
            ->orWhereHas('order', fn($q) => $q->whereColumn('reviews.traveler_id', 'deliveries.traveler_id'));
    }

    public function chefReviews()
    {
        return $this->hasMany(Review::class, 'chef_id');
    }

    public function getAverageRatingAttribute()
    {
        if ($this->role !== self::ROLE_CHEF) {
            return 0;
        }

        $reviews = $this->chefReviews()->whereNotNull('chef_rating')->get();
        
        if ($reviews->count() === 0) {
            return 0;
        }

        return round($reviews->avg('chef_rating'), 1);
    }

    public function getTotalReviewsAttribute()
    {
        if ($this->role !== self::ROLE_CHEF) {
            return 0;
        }

        return $this->chefReviews()->whereNotNull('chef_rating')->count();
    }

    /**
     * Storage path or external URL for this user's profile photo.
     * Priority: uploaded avatar, then verification selfie.
     */
    public function resolveAvatarStoragePath(): ?string
    {
        if (filled($this->avatar)) {
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }

            if (Storage::disk('public')->exists($this->avatar)) {
                return $this->avatar;
            }
        }

        $this->loadMissing(['chefProfile', 'travelerProfile']);

        $selfie = $this->chefProfile?->selfie_path ?? $this->travelerProfile?->selfie_path;

        if ($selfie && Storage::disk('public')->exists($selfie)) {
            return $selfie;
        }

        return null;
    }

    /**
     * Get the full URL for the user's avatar, or null if none set.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->resolveAvatarStoragePath();

        if ($path === null) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $version = $this->updated_at?->getTimestamp() ?? time();

        if (auth()->check() && (int) auth()->id() === (int) $this->id) {
            return route('profile.avatar', ['v' => $version]);
        }

        return route('users.avatar', ['user' => $this->id, 'v' => $version]);
    }
}
