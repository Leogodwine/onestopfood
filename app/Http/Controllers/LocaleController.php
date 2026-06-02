<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    /** @var array<int, string> */
    private const SUPPORTED = ['en', 'sw'];

    public function switch(Request $request, string $locale)
    {
        if (! in_array($locale, self::SUPPORTED, true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        $user = Auth::user();
        if ($user && $user->locale !== $locale) {
            $user->forceFill(['locale' => $locale])->save();
        }

        return redirect()->back();
    }
}
