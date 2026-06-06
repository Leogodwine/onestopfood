<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PartnerApplicationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerApplicationController extends Controller
{
    public function __construct(
        private readonly PartnerApplicationService $partnerApplication
    ) {}

    public function apply(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(PartnerApplicationService::PARTNER_ROLES)],
        ]);

        /** @var User $user */
        $user = $request->user();
        $wasCustomer = $user->role === User::ROLE_CUSTOMER;

        if ($user->role !== User::ROLE_CUSTOMER) {
            if ($user->role === $data['role'] && in_array($user->status, [User::STATUS_PENDING, User::STATUS_REJECTED], true)) {
                return redirect()
                    ->route('verification.show')
                    ->with('success', 'Continue completing your verification profile.');
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Only customer accounts can apply to become a partner.');
        }

        try {
            $user = $this->partnerApplication->apply($user, $data['role']);
            if ($wasCustomer) {
                app(\App\Services\AccountLifecycleNotifier::class)->accountCreated($user);
            }
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('dashboard')
                ->with('error', $e->getMessage());
        }

        $label = $data['role'] === User::ROLE_CHEF ? 'chef' : 'traveler';

        return redirect()
            ->route('verification.show')
            ->with('success', 'Your '.$label.' application has started. Complete verification to submit for admin approval.');
    }
}
