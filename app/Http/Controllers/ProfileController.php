<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show profile (read-only view).
     */
    public function show(Request $request)
    {
        $user = User::find($request->user()->id);

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the edit profile form.
     */
    public function edit(Request $request)
    {
        $user = User::find($request->user()->id);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Serve the current user's avatar image (avoids relying on public/storage symlink).
     */
    public function avatar(Request $request)
    {
        $user = User::find($request->user()->id);
        if (empty($user->avatar)) {
            abort(404);
        }
        $path = Storage::disk('public')->path($user->avatar);
        if (! is_file($path)) {
            abort(404);
        }
        return response()->file($path, [
            'Content-Type' => \Illuminate\Support\Facades\File::mimeType($path),
        ]);
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
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
                $user->avatar = null;
            }
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = $file->store('avatars', 'public');
            if ($path) {
                $user->avatar = $path;
            }
        }

        $user->save();

        return redirect()->route('profile.show')->with('status', 'Profile updated successfully.');
    }
}
