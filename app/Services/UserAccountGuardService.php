<?php

namespace App\Services;

use App\Models\AccountActionRequest;
use App\Models\Meal;
use App\Models\User;
use App\Models\UserVerificationDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserAccountGuardService
{
    public function __construct(
        private readonly AccountLifecycleNotifier $notifier,
    ) {}

    /**
     * @return array<string, int>
     */
    public function dependencies(User $user): array
    {
        $deps = [];

        $deps['orders_as_customer'] = \App\Models\Order::query()->where('customer_id', $user->id)->count();
        $deps['orders_as_chef'] = \App\Models\Order::query()->where('chef_id', $user->id)->count();
        $deps['deliveries_as_traveler'] = \App\Models\Delivery::query()->where('traveler_id', $user->id)->count();
        $deps['reviews_as_customer'] = \App\Models\Review::query()->where('customer_id', $user->id)->count();
        $deps['reviews_as_chef'] = \App\Models\Review::query()->where('chef_id', $user->id)->count();
        $deps['reviews_as_traveler'] = \App\Models\Review::query()->where('traveler_id', $user->id)->count();
        $deps['disputes_filed'] = \App\Models\Dispute::query()->where('created_by_user_id', $user->id)->count();
        $deps['multi_chef_orders'] = \App\Models\OrderChef::query()->where('chef_id', $user->id)->count();

        if ($user->role === User::ROLE_CHEF) {
            $mealIds = Meal::query()->where('chef_id', $user->id)->pluck('id');
            $deps['meals_in_orders'] = $mealIds->isEmpty()
                ? 0
                : \App\Models\OrderItem::query()->whereIn('meal_id', $mealIds)->distinct()->count('order_id');
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

        return implode(', ', $parts);
    }

    public function canHardDelete(User $user): bool
    {
        return $user->role !== User::ROLE_ADMIN && ! $this->hasBlockingDependencies($user);
    }

    public function deactivateSelf(User $user): void
    {
        if ($user->role === User::ROLE_ADMIN) {
            throw new \RuntimeException('Admin accounts cannot be self-deactivated.');
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'status' => User::STATUS_SUSPENDED,
                'suspended_by' => User::SUSPENDED_BY_SELF,
                'deactivated_at' => now(),
            ]);

            if ($user->role === User::ROLE_CHEF) {
                Meal::query()->where('chef_id', $user->id)->update(['is_available' => false]);
            }

            if ($user->role === User::ROLE_TRAVELER && $user->travelerProfile) {
                $user->travelerProfile->update(['is_online' => false]);
            }
        });

        $this->notifier->accountSelfDeactivated($user->fresh());
    }

    public function reactivateSelf(User $user): void
    {
        if (! $user->canSelfReactivate()) {
            throw new \RuntimeException('This account cannot be reactivated from your dashboard. Please contact support.');
        }

        $user->update([
            'status' => User::STATUS_APPROVED,
            'suspended_by' => null,
            'deactivated_at' => null,
        ]);

        $this->notifier->accountSelfReactivated($user->fresh());
    }

    public function requestDeletion(User $user, string $reason): AccountActionRequest
    {
        if ($user->role === User::ROLE_ADMIN) {
            throw new \RuntimeException('Admin accounts cannot request deletion.');
        }

        if ($user->pendingDeletionRequest()) {
            throw new \RuntimeException('You already have a pending deletion request.');
        }

        $request = AccountActionRequest::create([
            'user_id' => $user->id,
            'action' => AccountActionRequest::ACTION_DELETION,
            'reason' => $reason,
            'status' => AccountActionRequest::STATUS_PENDING,
        ]);

        $this->notifier->deletionRequested($user->fresh(), $request);

        return $request;
    }

    public function cancelDeletionRequest(User $user): void
    {
        $pending = $user->pendingDeletionRequest();
        if (! $pending) {
            throw new \RuntimeException('No pending deletion request to cancel.');
        }

        $pending->update(['status' => AccountActionRequest::STATUS_CANCELLED]);
    }

    public function approveDeletionRequest(AccountActionRequest $request, User $admin, ?string $adminNotes = null): void
    {
        if (! $request->isPending()) {
            throw new \RuntimeException('This request has already been processed.');
        }

        $user = $request->user;
        if (! $user) {
            throw new \RuntimeException('User not found.');
        }

        if (! $this->canHardDelete($user)) {
            throw new \RuntimeException(
                'Cannot permanently delete this account: '.$this->dependencyMessage($user)
            );
        }

        DB::transaction(function () use ($request, $admin, $adminNotes, $user) {
            $request->update([
                'status' => AccountActionRequest::STATUS_APPROVED,
                'admin_notes' => $adminNotes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            $this->notifier->deletionApproved($user->fresh(), $request->fresh(), $adminNotes);
            $this->purgeUserData($user);
            $user->delete();
        });
    }

    public function rejectDeletionRequest(AccountActionRequest $request, User $admin, ?string $adminNotes = null): void
    {
        if (! $request->isPending()) {
            throw new \RuntimeException('This request has already been processed.');
        }

        $user = $request->user;
        $request->update([
            'status' => AccountActionRequest::STATUS_REJECTED,
            'admin_notes' => $adminNotes,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        if ($user) {
            $this->notifier->deletionRejected($user->fresh(), $request->fresh(), $adminNotes);
        }
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
            $this->purgeUserData($user);
            $user->delete();
        });
    }

    public function suspend(User $user, ?string $reason = null, string $by = User::SUSPENDED_BY_ADMIN): void
    {
        $user->update([
            'status' => User::STATUS_SUSPENDED,
            'suspended_by' => $by,
            'deactivated_at' => $by === User::SUSPENDED_BY_SELF ? now() : $user->deactivated_at,
        ]);

        if ($user->role === User::ROLE_CHEF) {
            Meal::query()->where('chef_id', $user->id)->update(['is_available' => false]);
        }
    }

    private function purgeUserData(User $user): void
    {
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

        $this->deleteStoredFilesForUser($user);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->notifications()->delete();
    }

    private function deleteStoredFilesForUser(User $user): void
    {
        $user->verificationDocuments()->get()->each(function (UserVerificationDocument $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
        });

        foreach ([$user->chefProfile, $user->travelerProfile] as $profile) {
            if (! $profile) {
                continue;
            }

            foreach (['selfie_path', 'proof_of_address_path', 'vehicle_photo_path', 'vehicle_proof_of_ownership_path', 'vehicle_insurance_path'] as $field) {
                $path = $profile->{$field} ?? null;
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            if ($user->chefProfile && is_array($user->chefProfile->kitchen_photos)) {
                foreach ($user->chefProfile->kitchen_photos as $photo) {
                    if ($photo && Storage::disk('public')->exists($photo)) {
                        Storage::disk('public')->delete($photo);
                    }
                }
            }
        }
    }

    public static function logoutCurrentUser(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
