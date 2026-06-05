<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Location;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MultiChefCheckoutService
{
    public function __construct(
        private readonly OrderPricingService $pricing,
        private readonly DeliveryAssignmentService $assignment,
        private readonly InvoiceService $invoices
    ) {}

    /**
     * @param  array<int, int>  $cart
     * @param  Collection<int, Meal>  $meals
     * @return Collection<int, array{chef: User, items: array<int, array{meal: Meal, quantity: int, line_total: float}>, subtotal: float}>
     */
    public function groupCartByChef(array $cart, Collection $meals): Collection
    {
        $groups = [];

        foreach ($cart as $mealId => $qty) {
            $meal = $meals->get((int) $mealId);
            if (! $meal || ! $meal->chef) {
                continue;
            }
            $chefId = (int) $meal->chef_id;
            $quantity = max(1, (int) $qty);
            $lineTotal = (float) $meal->price * $quantity;

            if (! isset($groups[$chefId])) {
                $groups[$chefId] = [
                    'chef' => $meal->chef,
                    'items' => [],
                    'subtotal' => 0.0,
                ];
            }

            $groups[$chefId]['items'][] = [
                'meal' => $meal,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
            $groups[$chefId]['subtotal'] += $lineTotal;
        }

        return collect($groups)->values();
    }

    public function countChefsInCart(array $cart, Collection $meals): int
    {
        return $this->groupCartByChef($cart, $meals)->count();
    }

    /**
     * Total delivery fee: one fee per chef (separate pickups/deliveries).
     */
    public function calculateTotalDeliveryFee(int $chefCount, ?Location $deliveryLocation): float
    {
        if ($chefCount < 1) {
            return 0.0;
        }

        $perChef = $this->pricing->calculateDeliveryFee($deliveryLocation);

        return round($perChef * $chefCount, 2);
    }

    /**
     * @param  array<int, int>  $cart
     * @param  Collection<int, Meal>  $meals
     * @return array{
     *     orders: Collection<int, Order>,
     *     payment: Payment,
     *     invoices: Collection<int, \App\Models\Invoice>,
     *     is_multi_chef: bool,
     *     checkout_batch_id: string|null,
     *     grand_total: float
     * }
     */
    public function createOrdersFromCart(
        User $customer,
        array $cart,
        Collection $meals,
        Location $deliveryLocation,
        ?string $specialInstructions,
        string $paymentMethod,
        ?string $providerRef,
        ?string $displayCurrency = null
    ): array {
        $groups = $this->groupCartByChef($cart, $meals);
        $isMultiChef = $groups->count() > 1;
        $checkoutBatchId = $isMultiChef ? (string) Str::uuid() : null;
        $perChefDeliveryFee = $this->pricing->calculateDeliveryFee($deliveryLocation);

        $orders = collect();
        $grandTotal = 0.0;

        foreach ($groups as $group) {
            /** @var User $chef */
            $chef = $group['chef'];
            $chefSubtotal = round((float) $group['subtotal'], 2);
            $deliveryFee = round($perChefDeliveryFee, 2);
            $orderTotal = round($chefSubtotal + $deliveryFee, 2);
            $grandTotal += $orderTotal;

            $order = Order::create([
                'customer_id' => $customer->id,
                'chef_id' => $chef->id,
                'checkout_batch_id' => $checkoutBatchId,
                'status' => 'pending',
                'special_instructions' => $specialInstructions,
                'subtotal' => $chefSubtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $orderTotal,
                'delivery_location_id' => $deliveryLocation->id,
            ]);

            foreach ($group['items'] as $row) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'meal_id' => $row['meal']->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['meal']->price,
                    'line_total' => $row['line_total'],
                ]);
            }

            $autoAssign = (bool) \App\Models\SystemSetting::getValue('auto_assign_traveler', true);
            $traveler = null;
            if ($autoAssign) {
                $order->load(['items.meal.chef.chefProfile', 'chef.chefProfile', 'deliveryLocation']);
                $traveler = $this->assignment->assignBestTraveler($order);
            }

            Delivery::create([
                'order_id' => $order->id,
                'traveler_id' => $traveler?->id,
                'status' => $traveler ? 'assigned' : 'unassigned',
                'traveler_earning' => $this->pricing->travelerEarningFromDeliveryFee($deliveryFee),
            ]);

            if ($traveler) {
                try {
                    $traveler->notify(new \App\Notifications\DeliveryAssignedNotification($order));
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            $orders->push($order->fresh(['items.meal', 'chef', 'delivery']));
        }

        $paymentStatus = $this->pricing->initialPaymentStatus($paymentMethod);

        $primaryOrder = $orders->first();
        $payment = Payment::create([
            'order_id' => $primaryOrder->id,
            'checkout_batch_id' => $checkoutBatchId,
            'method' => $paymentMethod,
            'status' => $paymentStatus,
            'amount' => round($grandTotal, 2),
            'provider_reference' => $providerRef,
        ]);

        $invoices = collect();
        foreach ($orders as $order) {
            $invoices->push($this->invoices->createForOrder($order, $payment, $displayCurrency));
        }

        return [
            'orders' => $orders,
            'payment' => $payment,
            'invoices' => $invoices,
            'is_multi_chef' => $isMultiChef,
            'checkout_batch_id' => $checkoutBatchId,
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
