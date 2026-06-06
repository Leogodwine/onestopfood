<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerificationDocument;
use Illuminate\Support\Str;

class VerificationProgressService
{
    /** @return array<int, array<string, mixed>> */
    public function stepsFor(User $user): array
    {
        $user->loadMissing(['chefProfile', 'travelerProfile', 'verificationDocuments']);

        $documents = $user->verificationDocuments;
        $uploadedCount = $documents->filter(fn (UserVerificationDocument $d) => $d->file_path)->count();
        $approvedCount = $documents->where('status', 'approved')->count();
        $pendingDocCount = $documents->where('status', 'pending')->count();
        $rejectedCount = $documents->where('status', 'rejected')->count();
        $totalDocs = $documents->count();

        $profileComplete = $this->profileDetailsComplete($user);

        $steps = [
            [
                'key' => 'account_created',
                'label' => 'Account created',
                'description' => 'Your OneStopFood account was registered.',
                'status' => 'completed',
                'completed_at' => $user->created_at,
            ],
            [
                'key' => 'personal_details',
                'label' => 'Personal details',
                'description' => 'Identity, contact, and profile information.',
                'status' => $profileComplete ? 'completed' : ($uploadedCount > 0 ? 'in_progress' : 'pending'),
                'completed_at' => $profileComplete ? ($user->updated_at) : null,
            ],
            [
                'key' => 'documents_uploaded',
                'label' => 'Documents uploaded',
                'description' => $totalDocs > 0
                    ? "{$uploadedCount} of {$totalDocs} document(s) on file."
                    : 'Upload required verification documents.',
                'status' => $totalDocs > 0 && $uploadedCount === $totalDocs
                    ? 'completed'
                    : ($uploadedCount > 0 ? 'in_progress' : 'pending'),
                'completed_at' => $uploadedCount === $totalDocs && $totalDocs > 0 ? $documents->max('updated_at') : null,
            ],
            [
                'key' => 'document_review',
                'label' => 'Document review',
                'description' => $this->documentReviewDescription($documents),
                'status' => $totalDocs === 0
                    ? 'pending'
                    : ($rejectedCount > 0
                        ? 'rejected'
                        : ($pendingDocCount > 0
                            ? 'in_progress'
                            : ($approvedCount === $totalDocs ? 'completed' : 'pending'))),
                'completed_at' => $pendingDocCount === 0 && $approvedCount === $totalDocs && $totalDocs > 0
                    ? $documents->max('updated_at')
                    : null,
            ],
            [
                'key' => 'account_approval',
                'label' => 'Account approval',
                'description' => $this->accountApprovalDescription($user),
                'status' => match ($user->status) {
                    User::STATUS_APPROVED => 'completed',
                    User::STATUS_REJECTED => 'rejected',
                    User::STATUS_SUSPENDED => 'rejected',
                    default => 'in_progress',
                },
                'completed_at' => $user->approved_at,
            ],
        ];

        return $steps;
    }

    /** @return array<int, array<string, mixed>> */
    public function documentsFor(User $user): array
    {
        return $user->verificationDocuments()
            ->orderBy('type')
            ->get()
            ->map(fn (UserVerificationDocument $doc) => [
                'id' => $doc->id,
                'type' => $doc->type,
                'label' => Str::headline(str_replace('_', ' ', $doc->type)),
                'status' => $doc->status,
                'document_no' => $doc->document_no,
                'admin_notes' => $doc->admin_notes,
                'expires_at' => $doc->expires_at,
                'url' => $doc->url(),
                'is_image' => $doc->isImage(),
                'updated_at' => $doc->updated_at,
            ])
            ->all();
    }

    /** @return array<string, mixed> */
    public function personalDetailsFor(User $user): array
    {
        $user->loadMissing(['chefProfile', 'travelerProfile']);

        $base = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status,
            'created_at' => $user->created_at,
            'approved_at' => $user->approved_at,
        ];

        if ($user->role === User::ROLE_CHEF && $user->chefProfile) {
            $p = $user->chefProfile;

            return array_merge($base, [
                'dob' => $p->dob,
                'nida_id' => $p->nida_id,
                'nationality' => $p->nationality,
                'gender' => $p->gender,
                'city' => $p->city,
                'district' => $p->district,
                'street_address' => $p->street_address,
                'bio' => $p->bio,
                'cuisine_type' => $p->cuisine_type,
                'years_experience' => $p->years_experience,
                'bank_name' => $p->bank_name,
                'account_number' => $p->account_number,
                'account_holder_name' => $p->account_holder_name,
            ]);
        }

        if ($user->role === User::ROLE_TRAVELER && $user->travelerProfile) {
            $p = $user->travelerProfile;

            return array_merge($base, [
                'dob' => $p->dob,
                'nida_id' => $p->nida_id,
                'nationality' => $p->nationality,
                'gender' => $p->gender,
                'city' => $p->city,
                'district' => $p->district,
                'street_address' => $p->street_address,
                'vehicle_type' => $p->vehicle_type,
                'bank_name' => $p->bank_name,
                'account_number' => $p->account_number,
                'account_holder_name' => $p->account_holder_name,
            ]);
        }

        return $base;
    }

    private function profileDetailsComplete(User $user): bool
    {
        if ($user->role === User::ROLE_CHEF) {
            $p = $user->chefProfile;

            return $p
                && filled($p->nida_id)
                && filled($p->street_address)
                && filled($p->city);
        }

        if ($user->role === User::ROLE_TRAVELER) {
            $p = $user->travelerProfile;

            return $p
                && filled($p->nida_id)
                && filled($p->street_address)
                && filled($p->city);
        }

        return true;
    }

    private function documentReviewDescription($documents): string
    {
        if ($documents->isEmpty()) {
            return 'Waiting for document uploads.';
        }

        $pending = $documents->where('status', 'pending')->count();
        $approved = $documents->where('status', 'approved')->count();
        $rejected = $documents->where('status', 'rejected')->count();

        if ($rejected > 0) {
            return "{$rejected} document(s) need attention. {$approved} approved.";
        }

        if ($pending > 0) {
            return "{$pending} document(s) awaiting admin review.";
        }

        return 'All documents reviewed.';
    }

    private function accountApprovalDescription(User $user): string
    {
        return match ($user->status) {
            User::STATUS_APPROVED => 'Your partner account is approved and active.',
            User::STATUS_REJECTED => 'Your application was not approved. Contact support for help.',
            User::STATUS_SUSPENDED => 'Your account is suspended.',
            default => 'Admin will approve your account after document verification.',
        };
    }
}
