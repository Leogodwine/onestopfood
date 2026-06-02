<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Services\MultiChefCheckoutService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        [$cart, $meals] = $this->availableCartMeals($request);

        $items = [];
        $subtotal = 0;
        foreach ($cart as $mealId => $qty) {
            $meal = $meals->get((int) $mealId);
            if (! $meal) {
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

        $chefGroups = app(MultiChefCheckoutService::class)->groupCartByChef($cart, $meals);
        $chefCount = $chefGroups->count();

        $removedCount = count($request->session()->get('cart_removed_unavailable', []));
        $request->session()->forget('cart_removed_unavailable');

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'chefGroups' => $chefGroups,
            'chefCount' => $chefCount,
            'isMultiChef' => $chefCount > 1,
            'removedUnavailableCount' => $removedCount,
        ]);
    }

    public function add(Request $request, Meal $meal)
    {
        if (! $meal->isVisibleToCustomers()) {
            return redirect()->back()->withErrors([
                'error' => 'This meal is currently unavailable and hidden from the menu.',
            ]);
        }

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

    /**
     * @return array{0: array<int, int>, 1: \Illuminate\Support\Collection<int, Meal>}
     */
    public function availableCartMeals(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return [[], collect()];
        }

        $meals = Meal::query()
            ->visibleToCustomers()
            ->whereIn('id', array_keys($cart))
            ->with('chef')
            ->get()
            ->keyBy('id');

        $pruned = [];
        $removed = [];
        foreach ($cart as $mealId => $qty) {
            if ($meals->has((int) $mealId)) {
                $pruned[$mealId] = $qty;
            } else {
                $removed[] = (int) $mealId;
            }
        }

        if ($removed !== []) {
            $request->session()->put('cart_removed_unavailable', $removed);
        }

        $request->session()->put('cart', $pruned);

        return [$pruned, $meals];
    }
}
