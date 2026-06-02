<?php

namespace App\Services;

use App\Models\ChefProfile;
use App\Models\TravelerProfile;
use App\Models\User;
use App\Models\UserVerificationDocument;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class VerificationDocumentSync
{
    /**
     * Sync profile file uploads into user_verification_documents for all chefs/travelers.
     */
    public static function backfillFromProfiles(): void
    {
        User::query()
            ->whereIn('role', [User::ROLE_CHEF, User::ROLE_TRAVELER])
            ->with(['chefProfile', 'travelerProfile'])
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    static::syncUser($user);
                }
            });
    }

    public static function syncUser(User $user): void
    {
        if ($user->role === User::ROLE_CHEF && $user->chefProfile) {
            static::syncChefProfile($user, $user->chefProfile);
        } elseif ($user->role === User::ROLE_TRAVELER && $user->travelerProfile) {
            static::syncTravelerProfile($user, $user->travelerProfile);
        }
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'selfie' => 'Selfie / ID Photo',
            'proof_of_kitchen' => 'Proof of Kitchen',
            'proof_of_address' => 'Proof of Address',
            'professional_training' => 'Professional Training',
            'food_safety_cert' => 'Food Safety Certificate',
            'business_license' => 'Business License',
            'food_handling_permit' => 'Food Handling Permit',
            'health_inspection_cert' => 'Health Inspection Certificate',
            'kitchen_photo_1' => 'Kitchen Photo 1',
            'kitchen_photo_2' => 'Kitchen Photo 2',
            'vehicle_photo' => 'Vehicle Photo',
            'vehicle_proof_of_ownership' => 'Proof of Vehicle Ownership',
            'vehicle_insurance' => 'Vehicle Insurance',
            'driving_license' => 'Driving License',
            default => Str::headline(str_replace('_', ' ', $type)),
        };
    }

    private static function syncChefProfile(User $user, ChefProfile $profile): void
    {
        $fileMap = [
            'selfie_path' => ['type' => 'selfie', 'document_no' => $profile->nida_id ?: $profile->passport_no],
            'proof_of_kitchen_path' => ['type' => 'proof_of_kitchen', 'document_no' => $profile->nida_id],
            'professional_training_path' => ['type' => 'professional_training'],
            'food_safety_cert_path' => ['type' => 'food_safety_cert', 'document_no' => $profile->food_handler_certificate_no],
            'business_license_path' => ['type' => 'business_license', 'document_no' => $profile->tin_number],
            'food_handling_permit_path' => ['type' => 'food_handling_permit'],
            'health_inspection_cert_path' => ['type' => 'health_inspection_cert'],
        ];

        foreach ($fileMap as $column => $meta) {
            $path = $profile->{$column};
            if (is_string($path) && $path !== '') {
                static::upsertDocument(
                    $user,
                    $meta['type'],
                    $path,
                    $meta['document_no'] ?? null,
                    $meta['expires_at'] ?? null
                );
            }
        }

        $photos = is_array($profile->kitchen_photos) ? $profile->kitchen_photos : [];
        foreach ($photos as $index => $path) {
            if (is_string($path) && $path !== '') {
                static::upsertDocument($user, 'kitchen_photo_' . ($index + 1), $path);
            }
        }
    }

    private static function syncTravelerProfile(User $user, TravelerProfile $profile): void
    {
        $licenseExpiry = $profile->license_expiry_date
            ? Carbon::parse($profile->license_expiry_date)
            : null;

        $fileMap = [
            'selfie_path' => ['type' => 'selfie', 'document_no' => $profile->nida_id ?: $profile->driving_license_no],
            'proof_of_address_path' => ['type' => 'proof_of_address', 'document_no' => $profile->nida_id],
            'vehicle_photo_path' => ['type' => 'vehicle_photo', 'document_no' => $profile->vehicle_reg_no],
            'vehicle_proof_of_ownership_path' => ['type' => 'vehicle_proof_of_ownership', 'document_no' => $profile->vehicle_reg_no],
            'vehicle_insurance_path' => [
                'type' => 'vehicle_insurance',
                'document_no' => $profile->vehicle_reg_no,
                'expires_at' => $profile->vehicle_insurance_expiry
                    ? Carbon::parse($profile->vehicle_insurance_expiry)
                    : null,
            ],
        ];

        foreach ($fileMap as $column => $meta) {
            $path = $profile->{$column};
            if (is_string($path) && $path !== '') {
                static::upsertDocument(
                    $user,
                    $meta['type'],
                    $path,
                    $meta['document_no'] ?? null,
                    $meta['expires_at'] ?? null
                );
            }
        }

        if ($profile->license_number) {
            static::upsertDocument(
                $user,
                'driving_license',
                is_string($profile->selfie_path) && $profile->selfie_path !== '' ? $profile->selfie_path : null,
                $profile->license_number,
                $licenseExpiry
            );
        }
    }

    private static function upsertDocument(
        User $user,
        string $type,
        ?string $filePath,
        ?string $documentNo = null,
        ?Carbon $expiresAt = null
    ): void {
        if (! $filePath && ! $documentNo) {
            return;
        }

        $existing = UserVerificationDocument::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        $payload = [
            'document_no' => $documentNo,
            'file_path' => $filePath,
            'expires_at' => $expiresAt,
        ];

        if ($existing) {
            $fileChanged = $filePath && $filePath !== $existing->file_path;
            $metaChanged = ($documentNo && $documentNo !== $existing->document_no)
                || ($expiresAt && ! $existing->expires_at?->equalTo($expiresAt));

            if ($fileChanged) {
                $existing->fill(array_merge($payload, [
                    'status' => 'pending',
                    'admin_notes' => null,
                ]));
                $existing->save();

                return;
            }

            if ($metaChanged) {
                $existing->fill([
                    'document_no' => $documentNo ?? $existing->document_no,
                    'expires_at' => $expiresAt ?? $existing->expires_at,
                ]);
                $existing->save();
            }

            return;
        }

        UserVerificationDocument::create([
            'user_id' => $user->id,
            'type' => $type,
            'document_no' => $documentNo,
            'file_path' => $filePath,
            'expires_at' => $expiresAt,
            'status' => 'pending',
        ]);
    }
}
