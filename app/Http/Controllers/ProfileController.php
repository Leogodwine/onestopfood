<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show profile (read-only view).
     */
    public function show(Request $request)
    {
        $user = User::query()->find($request->user()->id);

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the edit profile form.
     */
    public function edit(Request $request)
    {
        $user = User::query()->find($request->user()->id);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Serve the current user's avatar image (avoids relying on public/storage symlink).
     */
    public function avatar(Request $request)
    {
        $user = User::query()->find($request->user()->id);

        return $this->avatarFileResponse($user);
    }

    /**
     * Serve any user's avatar for public display (chefs, listings, etc.).
     */
    public function userAvatar(User $user)
    {
        return $this->avatarFileResponse($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048',
            ],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        $data = $request->validate($rules);

        $user->name = $data['name'];

        if (! empty($data['remove_avatar']) || $request->hasFile('avatar')) {
            $this->deleteStoredAvatar($user);
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = $file->store('avatars', 'public');
            if ($path) {
                $user->avatar = $path;
            }
        }

        $user->save();

        Auth::setUser($user->fresh());

        return redirect()->route('profile.show')->with('status', 'Profile updated successfully.');
    }

    private function avatarFileResponse(User $user)
    {
        $path = $user->resolveAvatarStoragePath();

        if ($path === null || filter_var($path, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($path);

        if (! is_file($absolutePath)) {
            abort(404);
        }

        return response()->file($absolutePath, [
            'Content-Type' => File::mimeType($absolutePath),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    private function deleteStoredAvatar(User $user): void
    {
        if (empty($user->avatar) || filter_var($user->avatar, FILTER_VALIDATE_URL)) {
            return;
        }

        if (str_starts_with($user->avatar, 'avatars/') && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}
