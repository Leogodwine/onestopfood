<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserVerificationDocument;
use App\Support\ProfileDocumentFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends Controller
{
    /**
     * Serve a row from user_verification_documents (verifications/* on public disk).
     */
    public function verification(Request $request, UserVerificationDocument $document): StreamedResponse
    {
        $this->authorizeDocumentAccess($request, $document->user_id);

        return $this->servePublicPath((string) $document->file_path);
    }

    /**
     * Serve a profile upload (selfie, proof-of-kitchen, etc.) from chef/traveler profile columns.
     */
    public function profile(Request $request, User $user, string $field): StreamedResponse
    {
        $this->authorizeDocumentAccess($request, $user->id);

        if (! ProfileDocumentFields::isAllowed($field)) {
            abort(404, 'Unknown document type.');
        }

        $path = ProfileDocumentFields::resolvePath($user, $field);

        if (! $path) {
            abort(404, 'Document file not found.');
        }

        return $this->servePublicPath($path);
    }

    private function authorizeDocumentAccess(Request $request, int $ownerUserId): void
    {
        $user = $request->user();

        if ($user->role === User::ROLE_ADMIN || (int) $user->id === $ownerUserId) {
            return;
        }

        abort(403);
    }

    private function servePublicPath(string $path): StreamedResponse
    {
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(404, 'Invalid file path.');
        }

        if (! str_starts_with($path, 'verifications/') && ! str_starts_with($path, 'meals/')) {
            abort(404, 'File not allowed.');
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->response($path);
    }
}
