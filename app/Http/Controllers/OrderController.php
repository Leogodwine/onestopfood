<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Delivery;
use App\Models\Location;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderChef;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderPricingService;
use App\Services\Payments\MobileMoneyDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderPricingService $pricing,
        private readonly MobileMoneyDispatcher $mobileMoney,
        private readonly InvoiceService $invoices
    ) {}

    public function checkout(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('meals.index')->with('status', 'Your cart is empty');
        }

        $step = max(1, min(5, (int) $request->query('step', 1)));
        $meals = Meal::query()
            ->whereIn('id', array_keys($cart))
            ->with('chef')
            ->get();

        $items = [];
        $subtotal = 0;
        foreach ($meals as $meal) {
            $qty = (int) ($cart[$meal->id] ?? 0);
            if ($qty < 1) {
                continue;
            }
            $lineTotal = $meal->price * $qty;
            $subtotal += $lineTotal;
            $items[] = [
                'meal' => $meal,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];
        }
        $userLocations = $request->user()->locations()->orderByDesc('is_primary')->get();
        $deliveryLocationId = $request->session()->get('checkout_delivery_location_id');
        $deliveryLocation = $deliveryLocationId
            ? Location::find($deliveryLocationId)
            : $userLocations->where('is_primary', true)->first() ?? $userLocations->first();

        $deliveryFee = $this->pricing->calculateDeliveryFee($deliveryLocation);
        $total = $subtotal + $deliveryFee;
        $paymentMethod = $request->session()->get('checkout_payment_method');
        $paymentPhone = $request->session()->get('checkout_payment_phone');
        $paymentReference = $request->session()->get('checkout_payment_reference');
        $specialInstructions = $request->session()->get('checkout_special_instructions');

        return view('orders.checkout', [
            'step' => $step,
            'meals' => $meals,
            'cart' => $cart,
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'total' => $total,
            'userLocations' => $userLocations,
            'deliveryLocation' => $deliveryLocation,
            'deliveryLocationId' => $deliveryLocationId,
            'paymentMethod' => $paymentMethod,
            'paymentPhone' => $paymentPhone,
            'paymentReference' => $paymentReference,
            'specialInstructions' => $specialInstructions,
        ]);
    }

    public function storeDeliveryStep(Request $request)
    {
        $request->validate([
            'delivery_location_id' => ['required', 'exists:locations,id'],
        ]);
        $location = Location::where('id', $request->delivery_location_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        $request->session()->put('checkout_delivery_location_id', $location->id);
        return redirect()->route('orders.checkout', ['step' => 3]);
    }

    public function storePaymentStep(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', Rule::in(['mpesa', 'tigo', 'airtel', 'card', 'cod'])],
            'payment_phone' => [
                Rule::requiredIf(in_array($request->payment_method, ['mpesa', 'tigo', 'airtel'], true)),
                'nullable',
                'string',
                'max:50',
            ],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
        ]);
        $request->session()->put('checkout_payment_method', $request->payment_method);
        $request->session()->put('checkout_payment_phone', $request->payment_phone);
        $request->session()->put('checkout_payment_reference', $request->payment_reference);
        $request->session()->put('checkout_special_instructions', $request->special_instructions);
        return redirect()->route('orders.checkout', ['step' => 5]);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['mpesa', 'tigo', 'airtel', 'card', 'cod'])],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
            'delivery_location_id' => ['nullable', 'exists:locations,id'],
            'payment_phone' => [
                Rule::requiredIf(fn () => in_array($request->payment_method, ['mpesa', 'tigo', 'airtel'], true)),
                'nullable',
                'string',
                'max:50',
            ],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $cart = $request->session()->get('cart', []);
        if (!$cart) {
            return redirect()->route('meals.index')->with('status', 'Your cart is empty');
        }

        $meals = Meal::query()
            ->whereIn('id', array_keys($cart))
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        if ($meals->isEmpty()) {
            return redirect()->route('meals.index')->with('status', 'No available items in cart');
        }

        $chefIds = $meals->pluck('chef_id')->unique()->values();
        $customerId = (int) $request->user()->id;
        $isMultiChef = $chefIds->count() > 1;

        return DB::transaction(function () use ($request, $data, $cart, $meals, $chefIds, $customerId, $isMultiChef) {
            $subtotal = 0;
            foreach ($cart as $mealId => $qty) {
                $meal = $meals->get((int) $mealId);
                if (!$meal) {
                    continue;
                }
                $subtotal += $meal->price * (int) $qty;
            }

            $deliveryLocationId = $data['delivery_location_id']
                ?? $request->session()->get('checkout_delivery_location_id')
                ?? $request->user()->location?->id;
            if (!$deliveryLocationId) {
                return redirect()->route('orders.checkout', ['step' => 2])
                    ->withErrors(['delivery_location_id' => 'Please select a delivery address.']);
            }

            $deliveryLocation = Location::where('id', $deliveryLocationId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $deliveryFee = $this->pricing->calculateDeliveryFee($deliveryLocation);
            $total = $subtotal + $deliveryFee;

            $order = Order::create([
                'customer_id' => $customerId,
                'chef_id' => $isMultiChef ? null : $chefIds->first(),
                'status' => 'pending',
                'special_instructions' => $data['special_instructions'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'delivery_location_id' => $deliveryLocationId,
            ]);

            foreach ($cart as $mealId => $qty) {
                $meal = $meals->get((int) $mealId);
                if (!$meal) {
                    continue;
                }
                $qty = max(1, (int) $qty);

                OrderItem::create([
                    'order_id' => $order->id,
                    'meal_id' => $meal->id,
                    'quantity' => $qty,
                    'unit_price' => $meal->price,
                    'line_total' => $meal->price * $qty,
                ]);
            }

            if ($isMultiChef) {
                foreach ($chefIds as $chefId) {
                    $chefSubtotal = 0;
                    foreach ($cart as $mealId => $qty) {
                        $meal = $meals->get((int) $mealId);
                        if (!$meal || (int) $meal->chef_id !== (int) $chefId) {
                            continue;
                        }
                        $chefSubtotal += $meal->price * max(1, (int) $qty);
                    }
                    if ($chefSubtotal > 0) {
                        OrderChef::create([
                            'order_id' => $order->id,
                            'chef_id' => $chefId,
                            'subtotal' => $chefSubtotal,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            $paymentStatus = $this->pricing->initialPaymentStatus($data['payment_method']);
            if (config('food_delivery.auto_confirm_payments') && $data['payment_method'] !== 'cod') {
                $paymentStatus = 'paid';
            }

            $paymentPhone = $data['payment_phone']
                ?? $request->session()->get('checkout_payment_phone');
            $paymentReference = $data['payment_reference']
                ?? $request->session()->get('checkout_payment_reference');

            $providerRef = in_array($data['payment_method'], ['mpesa', 'tigo', 'airtel'], true)
                ? (trim((string) $paymentPhone) ?: null)
                : (trim(implode(' ', array_filter([$paymentPhone, $paymentReference]))) ?: null);

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $data['payment_method'],
                'status' => $paymentStatus,
                'amount' => $order->total,
                'provider_reference' => $providerRef,
            ]);

            $pushMessage = null;
            if (
                $paymentStatus === 'pending'
                && $this->mobileMoney->supports($data['payment_method'])
            ) {
                $gateway = $this->mobileMoney->gatewayFor($data['payment_method']);
                if ($gateway?->isConfigured()) {
                    $phone = $data['payment_phone']
                        ?? $request->session()->get('checkout_payment_phone')
                        ?? $request->user()->phone
                        ?? '';
                    $push = $this->mobileMoney->initiate($payment, $phone);
                    $label = $gateway->label();
                    $pushMessage = $push['success']
                        ? $push['message']
                        : "Order placed. {$label} prompt could not be sent: " . $push['message'];
                }
            }

            $autoAssign = (bool) SystemSetting::getValue('auto_assign_traveler', true);
            $traveler = null;
            if ($autoAssign) {
                $traveler = User::query()
                    ->where('role', User::ROLE_TRAVELER)
                    ->where('status', User::STATUS_APPROVED)
                    ->whereHas('travelerProfile', fn ($q) => $q->where('is_online', true))
                    ->inRandomOrder()
                    ->first();
            }

            Delivery::create([
                'order_id' => $order->id,
                'traveler_id' => $traveler?->id,
                'status' => $traveler ? 'assigned' : 'unassigned',
                'traveler_earning' => $this->pricing->travelerEarningFromDeliveryFee($deliveryFee),
            ]);

            $payment->refresh();
            $invoice = $this->invoices->createForOrder($order, $payment);

            $request->session()->forget('cart');
            $request->session()->forget('checkout_delivery_location_id');
            $request->session()->forget('checkout_payment_method');
            $request->session()->forget('checkout_payment_phone');
            $request->session()->forget('checkout_payment_reference');
            $request->session()->forget('checkout_special_instructions');

            try {
                Mail::to($order->customer->email)->send(new OrderConfirmation($order));
            } catch (\Throwable $e) {
                report($e);
            }
            try {
                $order->customer->notify(new \App\Notifications\OrderPlacedNotification($order));
            } catch (\Throwable $e) {
                report($e);
            }
            if ($isMultiChef) {
                foreach ($order->orderChefs as $orderChef) {
                    try {
                        $orderChef->chef->notify(new \App\Notifications\OrderPortionPlacedNotification($order, $orderChef));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            } else {
                try {
                    $order->chef?->notify(new \App\Notifications\OrderPlacedNotification($order));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            $brand = SystemSetting::getValue('site_name', config('app.name'));
            if ($order->customer->phone) {
                $smsMessage = $brand . ': Order #' . $order->id . ' placed. Total TZS ' . number_format((float) $order->total, 2) . '. Confirmation sent to your email.';
                Log::info('Order confirmation SMS', [
                    'order_id' => $order->id,
                    'phone' => $order->customer->phone,
                    'message' => $smsMessage,
                ]);
            }

            $paymentNote = match (true) {
                $pushMessage !== null => $pushMessage,
                $paymentStatus === 'paid' => 'Payment recorded.',
                default => 'Complete payment on your order page.',
            };

            return redirect()->route('invoices.show', $invoice)
                ->with('status', 'Order confirmed. Invoice ' . $invoice->invoice_number . ' generated. ' . $paymentNote);
        });
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        $isCustomer = (int) $order->customer_id === (int) $user->id;
        $isChef = (int) $order->chef_id === (int) $user->id
            || $order->orderChefs()->where('chef_id', $user->id)->exists();
        $isTraveler = $order->delivery && (int) $order->delivery->traveler_id === (int) $user->id;
        if ($user->role !== User::ROLE_ADMIN && !$isCustomer && !$isChef && !$isTraveler) {
            abort(403, 'You do not have permission to view this order.');
        }
        $order->load(['items.meal.chef', 'chef', 'customer', 'payment', 'invoice', 'delivery.traveler', 'orderChefs.chef', 'deliveryLocation']);

        return view('orders.show', compact('order'));
    }
}

