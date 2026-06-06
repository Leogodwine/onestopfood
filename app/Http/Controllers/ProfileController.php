<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PasswordRules;
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
        $user = User::query()
            ->with('socialAccounts')
            ->find($request->user()->id);

        return view('profile.edit', [
            'user' => $user,
            'isSocialOnlyUser' => $user->isSocialOnlyUser(),
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
        $user = User::query()->find($request->user()->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048',
            ],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

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

        return redirect()->route('profile.show')->with('status', __('auth.profile_updated'));
    }

    public function updatePassword(Request $request)
    {
        $user = User::query()
            ->with('socialAccounts')
            ->find($request->user()->id);

        $rules = [
            'password' => PasswordRules::forReset(),
        ];

        if (! $user->isSocialOnlyUser()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules, PasswordRules::validationMessages());

        $user->password = $data['password'];
        $user->save();

        Auth::setUser($user->fresh());

        return redirect()->route('profile.show')->with('status', __('auth.password_updated'));
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
