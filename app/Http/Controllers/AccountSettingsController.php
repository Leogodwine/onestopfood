<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserAccountGuardService;
use Illuminate\Http\Request;

class AccountSettingsController extends Controller
{
    public function __construct(
        private readonly UserAccountGuardService $accountGuard,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user()->load(['chefProfile', 'travelerProfile', 'locations']);

        abort_if($user->role === User::ROLE_ADMIN, 403);

        return view('account.settings', [
            'user' => $user,
            'pendingDeletion' => $user->pendingDeletionRequest(),
            'canHardDelete' => $this->accountGuard->canHardDelete($user),
            'dependencyMessage' => $this->accountGuard->dependencyMessage($user),
            'effectsKey' => match ($user->role) {
                User::ROLE_CHEF => 'delete_effects_chef',
                User::ROLE_TRAVELER => 'delete_effects_traveler',
                default => 'delete_effects_customer',
            },
            'deactivateEffectsKey' => match ($user->role) {
                User::ROLE_CHEF => 'deactivate_effects_chef',
                User::ROLE_TRAVELER => 'deactivate_effects_traveler',
                default => 'deactivate_effects_customer',
            },
        ]);
    }

    public function updateLocation(Request $request)
    {
        $user = $request->user();
        abort_if($user->role === User::ROLE_ADMIN, 403);

        if ($user->role === User::ROLE_CUSTOMER) {
            return redirect()->route('locations.index');
        }

        $data = $request->validate([
            'street_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city_district' => ['nullable', 'string', 'max:255'],
            'ward_neighborhood' => ['nullable', 'string', 'max:255'],
            'landmark_directions' => ['nullable', 'string', 'max:500'],
            'kitchen_address' => ['nullable', 'string', 'max:500'],
            'kitchen_latitude' => ['nullable', 'numeric'],
            'kitchen_longitude' => ['nullable', 'numeric'],
        ]);

        if ($user->role === User::ROLE_CHEF) {
            $profile = $user->chefProfile ?: $user->chefProfile()->create(['user_id' => $user->id]);
            $profile->update([
                'street_address' => $data['street_address'],
                'city' => $data['city'],
                'district' => $data['district'] ?? null,
                'city_district' => $data['city_district'] ?? null,
                'ward_neighborhood' => $data['ward_neighborhood'] ?? null,
                'landmark_directions' => $data['landmark_directions'] ?? null,
                'kitchen_address' => $data['kitchen_address'] ?? $data['street_address'],
                'kitchen_latitude' => $data['kitchen_latitude'] ?? null,
                'kitchen_longitude' => $data['kitchen_longitude'] ?? null,
            ]);
        }

        if ($user->role === User::ROLE_TRAVELER) {
            $profile = $user->travelerProfile ?: $user->travelerProfile()->create(['user_id' => $user->id]);
            $profile->update([
                'street_address' => $data['street_address'],
                'city' => $data['city'],
                'district' => $data['district'] ?? null,
                'city_district' => $data['city_district'] ?? null,
                'ward_neighborhood' => $data['ward_neighborhood'] ?? null,
            ]);
        }

        return back()->with('status', __('account.location_updated'));
    }

    public function deactivate(Request $request)
    {
        $user = $request->user();
        abort_if($user->role === User::ROLE_ADMIN, 403);

        $request->validate([
            'confirm_deactivate' => ['accepted'],
        ]);

        $this->accountGuard->deactivateSelf($user);
        UserAccountGuardService::logoutCurrentUser();

        return redirect()->route('login')->with('status', __('account.deactivated_success'));
    }

    public function reactivate(Request $request)
    {
        $user = $request->user();

        $this->accountGuard->reactivateSelf($user);

        return redirect()->route('dashboard')->with('status', __('account.reactivated_success'));
    }

    public function requestDeletion(Request $request)
    {
        $user = $request->user();
        abort_if($user->role === User::ROLE_ADMIN, 403);

        $request->validate([
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
            'confirm_delete' => ['accepted'],
        ]);

        $this->accountGuard->requestDeletion($user, $request->input('reason'));

        return back()->with('status', __('account.deletion_requested_success'));
    }

    public function cancelDeletion(Request $request)
    {
        $this->accountGuard->cancelDeletionRequest($request->user());

        return back()->with('status', __('account.deletion_cancelled_success'));
    }
}
