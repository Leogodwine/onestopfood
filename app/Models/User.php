<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'status',
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

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

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
     * Get the full URL for the user's avatar, or null if none set.
     * For the current user, uses a route that serves the file from storage (works without symlink).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        $ts = $this->updated_at ? $this->updated_at->timestamp : time();

        // For the logged-in user, serve avatar via app so it works even if public/storage link is broken
        if (auth()->check() && (int) auth()->id() === (int) $this->id) {
            return route('profile.avatar') . '?v=' . $ts;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar) . '?v=' . $ts;
    }
}
