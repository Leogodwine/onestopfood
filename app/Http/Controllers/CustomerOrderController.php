<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()
            ->ordersAsCustomer()
            ->with(['chef', 'orderChefs.chef', 'items.meal', 'payment', 'sharedPayment', 'invoice', 'delivery'])
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }
}
