<?php

namespace App\Services;

use App\Models\ChefProfile;
use App\Models\TravelerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PartnerApplicationService
{
    /** @var list<string> */
    public const PARTNER_ROLES = [
        User::ROLE_CHEF,
        User::ROLE_TRAVELER,
    ];

    /** @var list<string> */
    public const SIGNUP_ROLES = [
        User::ROLE_CUSTOMER,
        User::ROLE_CHEF,
        User::ROLE_TRAVELER,
    ];

    public function isSignupRole(string $role): bool
    {
        return in_array($role, self::SIGNUP_ROLES, true);
    }

    public function isPartnerRole(string $role): bool
    {
        return in_array($role, self::PARTNER_ROLES, true);
    }

    public function canApply(User $user, string $role): bool
    {
        if (! $this->isPartnerRole($role)) {
            return false;
        }

        return $user->role === User::ROLE_CUSTOMER;
    }

    public function applyIfEligible(User $user, string $role): User
    {
        if (! $this->canApply($user, $role)) {
            return $user;
        }

        return $this->apply($user, $role);
    }

    public function apply(User $user, string $role): User
    {
        if (! $this->isPartnerRole($role)) {
            throw new InvalidArgumentException('Invalid partner role.');
        }

        if ($user->role === $role) {
            if (in_array($user->status, [User::STATUS_PENDING, User::STATUS_REJECTED], true)) {
                return $user->fresh(['chefProfile', 'travelerProfile']);
            }

            throw new RuntimeException('You are already registered as a '.$this->roleLabel($role).'.');
        }

        if ($user->role !== User::ROLE_CUSTOMER) {
            throw new RuntimeException('Your account cannot be converted to a partner role.');
        }

        return DB::transaction(function () use ($user, $role) {
            $user->update([
                'role' => $role,
                'status' => User::STATUS_PENDING,
                'approved_at' => null,
            ]);

            if ($role === User::ROLE_CHEF) {
                ChefProfile::firstOrCreate(['user_id' => $user->id]);
            } else {
                TravelerProfile::firstOrCreate(['user_id' => $user->id]);
            }

            return $user->fresh(['chefProfile', 'travelerProfile']);
        });
    }

    public function setSignupRole(User $user, string $role): User
    {
        if (! $this->isSignupRole($role)) {
            throw new InvalidArgumentException('Invalid account type.');
        }

        if ($role === User::ROLE_CUSTOMER) {
            return DB::transaction(function () use ($user) {
                $user->update([
                    'role' => User::ROLE_CUSTOMER,
                    'status' => User::STATUS_APPROVED,
                ]);

                return $user->fresh(['chefProfile', 'travelerProfile']);
            });
        }

        if ($user->role === $role && in_array($user->status, [User::STATUS_PENDING, User::STATUS_REJECTED], true)) {
            return $user->fresh(['chefProfile', 'travelerProfile']);
        }

        if ($user->role !== User::ROLE_CUSTOMER && $user->role !== $role) {
            throw new RuntimeException('Account type cannot be changed at this step. Start over or contact support.');
        }

        return $this->apply($user, $role);
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            User::ROLE_CHEF => 'chef',
            User::ROLE_TRAVELER => 'traveler',
            default => $role,
        };
    }
}
