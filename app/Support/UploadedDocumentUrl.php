<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserVerificationDocument;

class UploadedDocumentUrl
{
    public static function verification(?UserVerificationDocument $document): ?string
    {
        if (! $document?->file_path || ! $document->fileExists()) {
            return null;
        }

        return route('documents.verifications.show', $document);
    }

    public static function profile(User $user, string $fieldKey): ?string
    {
        if (! ProfileDocumentFields::isAllowed($fieldKey)) {
            return null;
        }

        if (! ProfileDocumentFields::resolvePath($user, $fieldKey)) {
            return null;
        }

        return route('documents.profiles.show', [
            'user' => $user->id,
            'field' => ProfileDocumentFields::normalizeKey($fieldKey),
        ]);
    }
}
