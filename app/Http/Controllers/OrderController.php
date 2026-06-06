<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Location;
use App\Models\Meal;
use App\Models\Order;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\InvoiceService;
use App\Services\MultiChefCheckoutService;
use App\Services\OrderPricingService;
use App\Services\Payments\MobileMoneyDispatcher;
use App\Support\PhoneNumber;
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
        private readonly InvoiceService $invoices,
        private readonly MultiChefCheckoutService $multiChefCheckout,
        private readonly CurrencyService $currencies,
    ) {}

    public function checkout(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('meals.index')->with('status', 'Your cart is empty');
        }

        $step = max(1, min(5, (int) $request->query('step', 1)));
        [$cart, $meals] = app(CartController::class)->availableCartMeals($request);

        if ($cart === []) {
            return redirect()->route('meals.index')->with('status', 'Your cart is empty or contains unavailable items.');
        }

        $chefGroups = $this->multiChefCheckout->groupCartByChef($cart, $meals);
        $chefCount = $chefGroups->count();
        $isMultiChef = $chefCount > 1;

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

        $deliveryFee = $this->multiChefCheckout->calculateTotalDeliveryFee($chefCount, $deliveryLocation);
        $total = $subtotal + $deliveryFee;
        $paymentMethod = $request->session()->get('checkout_payment_method');
        $paymentPhone = $request->session()->get('checkout_payment_phone');
        $paymentReference = $request->session()->get('checkout_payment_reference');
        $specialInstructions = $request->session()->get('checkout_special_instructions');

        if (! $request->session()->has('checkout_currency')) {
            $request->session()->put('checkout_currency', $this->currencies->current());
        }

        return view('orders.checkout', [
            'step' => $step,
            'meals' => $meals,
            'cart' => $cart,
            'items' => $items,
            'chefGroups' => $chefGroups,
            'chefCount' => $chefCount,
            'isMultiChef' => $isMultiChef,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'deliveryFeePerChef' => $chefCount > 0 ? $deliveryFee / $chefCount : 0,
            'total' => $total,
            'userLocations' => $userLocations,
            'deliveryLocation' => $deliveryLocation,
            'deliveryLocationId' => $deliveryLocationId,
            'paymentMethod' => $paymentMethod,
            'paymentPhone' => $paymentPhone,
            'paymentReference' => $paymentReference,
            'specialInstructions' => $specialInstructions,
            'checkoutCurrency' => session('checkout_currency', $this->currencies->current()),
            'currencyOptions' => $this->currencies->supported(),
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
        PhoneNumber::mergeIntoRequest($request, 'payment_phone', 'payment_phone_country_code', 'payment_phone_number');

        $mobileMoney = in_array($request->payment_method, ['mpesa', 'tigo', 'airtel'], true);
        $paymentRules = [
            'payment_method' => ['required', Rule::in(['mpesa', 'tigo', 'airtel', 'card', 'cod'])],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
        ];

        if ($mobileMoney) {
            $paymentRules['payment_phone_country_code'] = ['required', 'string', Rule::in(array_keys(PhoneNumber::countries()))];
            $paymentRules['payment_phone_number'] = PhoneNumber::nationalNumberRules('payment_phone_country_code');
            $paymentRules['payment_phone'] = ['required', 'string', 'max:50'];
        }

        $request->validate($paymentRules, PhoneNumber::validationMessages(
            'payment_phone_country_code',
            'payment_phone_number',
            'payment_phone'
        ));
        $request->session()->put('checkout_payment_method', $request->payment_method);
        $request->session()->put('checkout_payment_phone', $request->payment_phone);
        $request->session()->put('checkout_payment_reference', $request->payment_reference);
        $request->session()->put('checkout_special_instructions', $request->special_instructions);

        return redirect()->route('orders.checkout', ['step' => 5]);
    }

    public function place(Request $request)
    {
        $request->merge([
            'payment_method' => $request->input('payment_method') ?: $request->session()->get('checkout_payment_method'),
            'payment_phone' => $request->input('payment_phone') ?: $request->session()->get('checkout_payment_phone'),
            'payment_reference' => $request->input('payment_reference') ?: $request->session()->get('checkout_payment_reference'),
            'special_instructions' => $request->input('special_instructions') ?: $request->session()->get('checkout_special_instructions'),
            'delivery_location_id' => $request->input('delivery_location_id') ?: $request->session()->get('checkout_delivery_location_id'),
        ]);

        if (! $request->filled('payment_method')) {
            return redirect()->route('orders.checkout', ['step' => 4])
                ->withErrors(['payment_method' => 'Please select a payment method before placing your order.']);
        }

        PhoneNumber::mergeIntoRequest($request, 'payment_phone', 'payment_phone_country_code', 'payment_phone_number');

        $mobileMoney = in_array($request->payment_method, ['mpesa', 'tigo', 'airtel'], true);
        $placeRules = [
            'payment_method' => ['required', Rule::in(['mpesa', 'tigo', 'airtel', 'card', 'cod'])],
            'special_instructions' => ['nullable', 'string', 'max:2000'],
            'delivery_location_id' => ['nullable', 'exists:locations,id'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ];

        if ($mobileMoney) {
            $placeRules['payment_phone_country_code'] = ['required', 'string', Rule::in(array_keys(PhoneNumber::countries()))];
            $placeRules['payment_phone_number'] = PhoneNumber::nationalNumberRules('payment_phone_country_code');
            $placeRules['payment_phone'] = ['required', 'string', 'max:50'];
        }

        $data = $request->validate($placeRules, PhoneNumber::validationMessages(
            'payment_phone_country_code',
            'payment_phone_number',
            'payment_phone'
        ));

        $cart = $request->session()->get('cart', []);
        if (! $cart) {
            return redirect()->route('meals.index')->with('status', 'Your cart is empty');
        }

        $meals = Meal::query()
            ->whereIn('id', array_keys($cart))
            ->visibleToCustomers()
            ->with('chef')
            ->get()
            ->keyBy('id');

        if ($meals->isEmpty()) {
            return redirect()->route('meals.index')->with('status', 'No available items in cart');
        }

        return DB::transaction(function () use ($request, $data, $cart, $meals) {
            $deliveryLocationId = $data['delivery_location_id']
                ?? $request->session()->get('checkout_delivery_location_id')
                ?? $request->user()->location?->id;
            if (! $deliveryLocationId) {
                return redirect()->route('orders.checkout', ['step' => 2])
                    ->withErrors(['delivery_location_id' => 'Please select a delivery address.']);
            }

            $deliveryLocation = Location::where('id', $deliveryLocationId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $paymentPhone = $data['payment_phone']
                ?? $request->session()->get('checkout_payment_phone');
            $paymentReference = $data['payment_reference']
                ?? $request->session()->get('checkout_payment_reference');
            $specialInstructions = $data['special_instructions']
                ?? $request->session()->get('checkout_special_instructions');

            $providerRef = in_array($data['payment_method'], ['mpesa', 'tigo', 'airtel'], true)
                ? (trim((string) $paymentPhone) ?: null)
                : (trim(implode(' ', array_filter([$paymentPhone, $paymentReference]))) ?: null);

            $checkoutCurrency = session('checkout_currency', $this->currencies->current());
            if (! $this->currencies->isSupported($checkoutCurrency)) {
                $checkoutCurrency = $this->currencies->default();
            }

            $result = $this->multiChefCheckout->createOrdersFromCart(
                $request->user(),
                $cart,
                $meals,
                $deliveryLocation,
                $specialInstructions,
                $data['payment_method'],
                $providerRef,
                $checkoutCurrency
            );

            $orders = $result['orders'];
            $payment = $result['payment'];
            $isMultiChef = $result['is_multi_chef'];

            $pushMessage = null;
            $paymentStatus = $payment->status;
            if (
                $paymentStatus === 'pending'
                && $this->mobileMoney->supports($data['payment_method'])
            ) {
                $gateway = $this->mobileMoney->gatewayFor($data['payment_method']);
                if ($gateway?->isConfigured()) {
                    $phone = $paymentPhone
                        ?? $request->user()->phone
                        ?? '';
                    $push = $this->mobileMoney->initiate($payment, $phone);
                    $label = $gateway->label();
                    $pushMessage = $push['success']
                        ? $push['message']
                        : "Order placed. {$label} prompt could not be sent: ".$push['message'];
                }
            }

            $request->session()->forget('cart');
            $request->session()->forget('checkout_delivery_location_id');
            $request->session()->forget('checkout_payment_method');
            $request->session()->forget('checkout_payment_phone');
            $request->session()->forget('checkout_payment_reference');
            $request->session()->forget('checkout_special_instructions');
            $request->session()->forget('checkout_currency');

            foreach ($orders as $order) {
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
                try {
                    $order->chef?->notify(new \App\Notifications\OrderPlacedNotification($order));
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            $brand = SystemSetting::getValue('site_name', config('app.name'));
            if ($request->user()->phone) {
                $orderIds = $orders->pluck('id')->join(', #');
                $smsMessage = $brand.': '.($isMultiChef ? 'Orders' : 'Order').' #'.$orderIds
                    .' placed. Total TZS '.number_format((float) $result['grand_total'], 2).'.';
                Log::info('Order confirmation SMS', [
                    'order_ids' => $orders->pluck('id')->all(),
                    'phone' => $request->user()->phone,
                    'message' => $smsMessage,
                ]);
            }

            $paymentNote = match (true) {
                $pushMessage !== null => $pushMessage,
                $paymentStatus === 'paid' => 'Payment recorded.',
                default => __('payments.order_placed_unpaid'),
            };

            $orderList = $orders->map(fn ($o) => '#'.$o->id)->join(', ');
            $statusMessage = $isMultiChef
                ? "Checkout complete. Separate orders created for each chef: {$orderList}. Each chef prepares and ships their items independently. {$paymentNote}"
                : 'Order confirmed. Invoice '.$result['invoices']->first()->invoice_number.' generated. '.$paymentNote;

            $firstInvoice = $result['invoices']->first();
            $primaryOrder = $orders->first();

            return redirect()
                ->route('orders.invoice', $primaryOrder)
                ->with('status', $statusMessage)
                ->with('placed_invoice_id', $firstInvoice?->id)
                ->with('placed_batch_order_ids', $isMultiChef ? $orders->pluck('id')->all() : []);
        });
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        $isCustomer = (int) $order->customer_id === (int) $user->id;
        $isChef = (int) $order->chef_id === (int) $user->id
            || $order->orderChefs()->where('chef_id', $user->id)->exists();
        $isTraveler = $order->delivery && (int) $order->delivery->traveler_id === (int) $user->id;
        if ($user->role !== User::ROLE_ADMIN && ! $isCustomer && ! $isChef && ! $isTraveler) {
            abort(403, 'You do not have permission to view this order.');
        }
        $order->load(['items.meal.chef', 'chef', 'customer', 'payment', 'sharedPayment', 'invoice', 'delivery.traveler', 'orderChefs.chef', 'deliveryLocation']);

        $batchOrders = $order->checkout_batch_id
            ? Order::query()->where('checkout_batch_id', $order->checkout_batch_id)->with('chef')->orderBy('id')->get()
            : collect();

        return view('orders.show', compact('order', 'batchOrders'));
    }
}
