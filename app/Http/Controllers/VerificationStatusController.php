<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VerificationProgressService;
use Illuminate\Http\Request;

class VerificationStatusController extends Controller
{
    public function __construct(
        private readonly VerificationProgressService $progress,
    ) {}

    public function show(Request $request)
    {
        $user = User::query()
            ->with(['chefProfile', 'travelerProfile', 'verificationDocuments'])
            ->find($request->user()->id);

        if (! in_array($user->role, [User::ROLE_CHEF, User::ROLE_TRAVELER], true)) {
            return redirect()->route('profile.show');
        }

        return view('profile.verification-status', [
            'user' => $user,
            'steps' => $this->progress->stepsFor($user),
            'documents' => $this->progress->documentsFor($user),
            'personalDetails' => $this->progress->personalDetailsFor($user),
        ]);
    }
}
