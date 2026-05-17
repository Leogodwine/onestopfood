<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        $meals = Meal::query()
            ->whereIn('id', array_keys($cart))
            ->with('chef')
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0;
        foreach ($cart as $mealId => $qty) {
            $meal = $meals->get((int) $mealId);
            if (!$meal) {
                continue;
            }
            $line = $meal->price * (int) $qty;
            $subtotal += $line;
            $items[] = [
                'meal' => $meal,
                'quantity' => (int) $qty,
                'line_total' => $line,
            ];
        }

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $subtotal,
        ]);
    }

    public function add(Request $request, Meal $meal)
    {
        $qty = max(1, (int) $request->input('quantity', 1));

        $cart = $request->session()->get('cart', []);
        $cart[$meal->id] = ($cart[$meal->id] ?? 0) + $qty;
        $request->session()->put('cart', $cart);

        return redirect()->back()->with('status', 'Added to cart')->with('cart_added_qty', $qty);
    }

    public function remove(Request $request, Meal $meal)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$meal->id]);
        $request->session()->put('cart', $cart);

        return redirect()->back()->with('status', 'Removed from cart');
    }
}

