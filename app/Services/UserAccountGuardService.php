<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Dispute;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderChef;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserAccountGuardService
{
    /**
     * @return array<string, int>
     */
    public function dependencies(User $user): array
    {
        $deps = [];

        $deps['orders_as_customer'] = Order::query()->where('customer_id', $user->id)->count();
        $deps['orders_as_chef'] = Order::query()->where('chef_id', $user->id)->count();
        $deps['deliveries_as_traveler'] = Delivery::query()->where('traveler_id', $user->id)->count();
        $deps['reviews_as_customer'] = Review::query()->where('customer_id', $user->id)->count();
        $deps['reviews_as_chef'] = Review::query()->where('chef_id', $user->id)->count();
        $deps['reviews_as_traveler'] = Review::query()->where('traveler_id', $user->id)->count();
        $deps['disputes_filed'] = Dispute::query()->where('created_by_user_id', $user->id)->count();
        $deps['multi_chef_orders'] = OrderChef::query()->where('chef_id', $user->id)->count();

        if ($user->role === User::ROLE_CHEF) {
            $mealIds = Meal::query()->where('chef_id', $user->id)->pluck('id');
            $deps['meals_in_orders'] = $mealIds->isEmpty()
                ? 0
                : OrderItem::query()->whereIn('meal_id', $mealIds)->distinct()->count('order_id');
        }

        return array_filter($deps, fn (int $count) => $count > 0);
    }

    public function hasBlockingDependencies(User $user): bool
    {
        return $this->dependencies($user) !== [];
    }

    public function dependencyMessage(User $user): string
    {
        $labels = [
            'orders_as_customer' => 'customer order(s)',
            'orders_as_chef' => 'chef order(s)',
            'deliveries_as_traveler' => 'delivery record(s)',
            'reviews_as_customer' => 'customer review(s)',
            'reviews_as_chef' => 'chef review(s)',
            'reviews_as_traveler' => 'traveler review(s)',
            'disputes_filed' => 'dispute(s)',
            'multi_chef_orders' => 'multi-chef order link(s)',
            'meals_in_orders' => 'order(s) containing their meals',
        ];

        $parts = [];
        foreach ($this->dependencies($user) as $key => $count) {
            $label = $labels[$key] ?? $key;
            $parts[] = "{$count} {$label}";
        }

        if ($parts === []) {
            return '';
        }

        return implode(', ', $parts);
    }

    public function canHardDelete(User $user): bool
    {
        return $user->role !== User::ROLE_ADMIN && ! $this->hasBlockingDependencies($user);
    }

    /**
     * Permanently remove only users with no linked business records.
     *
     * @throws \RuntimeException
     */
    public function deleteIfAllowed(User $user): void
    {
        if ($user->role === User::ROLE_ADMIN) {
            throw new \RuntimeException('Admin accounts cannot be deleted.');
        }

        if ($this->hasBlockingDependencies($user)) {
            throw new \RuntimeException(
                "Cannot delete {$user->email}: this account has linked records ({$this->dependencyMessage($user)}). "
                . 'Suspend or block the account instead to preserve orders, payments, and complaints.'
            );
        }

        DB::transaction(function () use ($user) {
            if ($user->role === User::ROLE_CHEF) {
                Meal::query()
                    ->where('chef_id', $user->id)
                    ->get()
                    ->each(function (Meal $meal) {
                        if ($meal->image_path) {
                            Storage::disk('public')->delete($meal->image_path);
                        }
                    });

                Meal::query()->where('chef_id', $user->id)->delete();
            }

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();
        });
    }

    public function suspend(User $user, ?string $reason = null): void
    {
        $user->update(['status' => User::STATUS_SUSPENDED]);
    }
}
