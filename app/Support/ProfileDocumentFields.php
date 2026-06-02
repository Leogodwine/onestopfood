<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class ProfileDocumentFields
{
    /** @var array<string, string|array{column: string, index?: int}> */
    private const CHEF = [
        'selfie' => 'selfie_path',
        'proof-of-kitchen' => 'proof_of_kitchen_path',
        'professional-training' => 'professional_training_path',
        'food-safety-cert' => 'food_safety_cert_path',
        'business-license' => 'business_license_path',
        'food-handling-permit' => 'food_handling_permit_path',
        'health-inspection-cert' => 'health_inspection_cert_path',
        'kitchen-photo-1' => ['column' => 'kitchen_photos', 'index' => 0],
        'kitchen-photo-2' => ['column' => 'kitchen_photos', 'index' => 1],
    ];

    /** @var array<string, string|array{column: string, index?: int}> */
    private const TRAVELER = [
        'selfie' => 'selfie_path',
        'proof-of-address' => 'proof_of_address_path',
        'vehicle-photo' => 'vehicle_photo_path',
        'vehicle-proof-of-ownership' => 'vehicle_proof_of_ownership_path',
        'vehicle-insurance' => 'vehicle_insurance_path',
    ];

    public static function fieldKeyFromType(string $type): string
    {
        return str_replace('_', '-', $type);
    }

    public static function isAllowed(string $fieldKey): bool
    {
        $key = self::normalizeKey($fieldKey);

        return isset(self::CHEF[$key]) || isset(self::TRAVELER[$key]);
    }

    public static function resolvePath(User $user, string $fieldKey): ?string
    {
        $key = self::normalizeKey($fieldKey);
        $map = $user->role === User::ROLE_CHEF ? self::CHEF : self::TRAVELER;
        $definition = $map[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        $profile = $user->role === User::ROLE_CHEF
            ? $user->chefProfile
            : $user->travelerProfile;

        if (! $profile) {
            return null;
        }

        if (is_string($definition)) {
            $path = $profile->{$definition};

            return is_string($path) && $path !== '' ? $path : null;
        }

        $column = $definition['column'];
        $photos = $profile->{$column};

        if (! is_array($photos)) {
            return null;
        }

        $index = $definition['index'] ?? 0;
        $path = $photos[$index] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public static function normalizeKey(string $fieldKey): string
    {
        return Str::slug($fieldKey);
    }
}
