<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var array<int, string> */
    private const SUPPORTED = ['en', 'sw'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale && $request->user()?->locale) {
            $locale = $request->user()->locale;
            session(['locale' => $locale]);
        }

        $locale ??= config('app.locale', 'en');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
