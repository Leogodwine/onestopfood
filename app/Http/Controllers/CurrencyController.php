<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function switch(Request $request, string $currency, CurrencyService $currencies)
    {
        if (! $currencies->isSupported($currency)) {
            abort(404);
        }

        $currencies->setCurrent($currency);

        if ($request->boolean('checkout')) {
            session(['checkout_currency' => strtoupper($currency)]);
        }

        return redirect()->back();
    }
}
