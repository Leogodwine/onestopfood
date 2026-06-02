<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Collection;

class DeliveryAssignmentService
{
    public function __construct(
        private readonly OrderPricingService $pricing
    ) {}

    public function haversineKm(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round($earth * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    public function orderItemQuantity(Order $order): int
    {
        if ($order->relationLoaded('items')) {
            return (int) $order->items->sum('quantity');
        }

        return (int) $order->items()->sum('quantity');
    }

    /**
     * @return array{lat: float, lng: float, source: string}|null
     */
    public function getTravelerCoordinates(User $traveler): ?array
    {
        $profile = $traveler->travelerProfile;
        $freshMinutes = (int) config('food_delivery.traveler_gps_fresh_minutes', 15);

        if (
            $profile
            && $profile->last_latitude !== null
            && $profile->last_longitude !== null
            && $profile->last_location_at
            && $profile->last_location_at->gte(now()->subMinutes($freshMinutes))
        ) {
            return [
                'lat' => (float) $profile->last_latitude,
                'lng' => (float) $profile->last_longitude,
                'source' => 'gps',
            ];
        }

        $loc = $traveler->relationLoaded('location') ? $traveler->location : $traveler->location()->first();
        if ($loc && $loc->latitude !== null && $loc->longitude !== null) {
            return [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'source' => 'address',
            ];
        }

        return null;
    }

    /**
     * @return array{lat: float, lng: float, chef: User}|null
     */
    public function getPrimaryKitchenForOrder(Order $order, ?User $chef = null): ?array
    {
        if ($chef && $chef->chefProfile) {
            $lat = $chef->chefProfile->kitchen_latitude;
            $lng = $chef->chefProfile->kitchen_longitude;
            if ($lat !== null && $lng !== null) {
                return ['lat' => (float) $lat, 'lng' => (float) $lng, 'chef' => $chef];
            }
        }

        if ($order->chef && $order->chef->chefProfile) {
            $lat = $order->chef->chefProfile->kitchen_latitude;
            $lng = $order->chef->chefProfile->kitchen_longitude;
            if ($lat !== null && $lng !== null) {
                return ['lat' => (float) $lat, 'lng' => (float) $lng, 'chef' => $order->chef];
            }
        }

        $order->loadMissing(['items.meal.chef.chefProfile', 'orderChefs.chef.chefProfile']);

        foreach ($order->items as $item) {
            $itemChef = $item->meal?->chef;
            if (! $itemChef?->chefProfile) {
                continue;
            }
            $lat = $itemChef->chefProfile->kitchen_latitude;
            $lng = $itemChef->chefProfile->kitchen_longitude;
            if ($lat !== null && $lng !== null) {
                return ['lat' => (float) $lat, 'lng' => (float) $lng, 'chef' => $itemChef];
            }
        }

        foreach ($order->orderChefs ?? [] as $orderChef) {
            $itemChef = $orderChef->chef;
            if (! $itemChef?->chefProfile) {
                continue;
            }
            $lat = $itemChef->chefProfile->kitchen_latitude;
            $lng = $itemChef->chefProfile->kitchen_longitude;
            if ($lat !== null && $lng !== null) {
                return ['lat' => (float) $lat, 'lng' => (float) $lng, 'chef' => $itemChef];
            }
        }

        return null;
    }

    public function vehicleCapacity(?string $vehicleType): int
    {
        $type = strtolower(trim((string) $vehicleType));
        $defaults = config('food_delivery.vehicle_capacity', []);

        return (int) ($defaults[$type] ?? $defaults['default'] ?? 10);
    }

    public function vehicleSuitableForLoad(?string $vehicleType, int $orderQty, ?int $maxLoadCapacity): bool
    {
        $vehicleCap = $this->vehicleCapacity($vehicleType);
        $effective = $maxLoadCapacity !== null && $maxLoadCapacity > 0
            ? min($vehicleCap, (int) $maxLoadCapacity)
            : $vehicleCap;

        return $orderQty <= $effective;
    }

    public function travelerHasActiveDelivery(int $travelerId, ?int $exceptDeliveryId = null): bool
    {
        $query = Delivery::query()
            ->where('traveler_id', $travelerId)
            ->whereIn('status', ['assigned', 'picked_up']);

        if ($exceptDeliveryId) {
            $query->where('id', '!=', $exceptDeliveryId);
        }

        return $query->exists();
    }

    public function isTravelerEligible(User $traveler, bool $requireOnline = true): bool
    {
        if ($traveler->role !== User::ROLE_TRAVELER || $traveler->status !== User::STATUS_APPROVED) {
            return false;
        }

        $profile = $traveler->travelerProfile;
        if (! $profile) {
            return false;
        }

        if ($requireOnline && ! $profile->is_online) {
            return false;
        }

        return ! $this->travelerHasActiveDelivery((int) $traveler->id);
    }

    /**
     * Rank online travelers for an order (nearest first, vehicle/load aware).
     *
     * @return Collection<int, object{
     *   user: User,
     *   distance_km_to_chef: ?float,
     *   distance_km_to_customer: ?float,
     *   combined_km: ?float,
     *   order_quantity: int,
     *   vehicle_type: ?string,
     *   vehicle_capacity: int,
     *   load_ok: bool,
     *   location_source: ?string,
     *   score: float,
     *   recommended: bool
     * }>
     */
    public function rankTravelersForOrder(Order $order, ?User $chef = null, bool $onlineOnly = true): Collection
    {
        $order->loadMissing(['deliveryLocation', 'items']);

        $orderQty = $this->orderItemQuantity($order);
        $kitchen = $this->getPrimaryKitchenForOrder($order, $chef);
        $customerLat = $order->deliveryLocation?->latitude ? (float) $order->deliveryLocation->latitude : null;
        $customerLng = $order->deliveryLocation?->longitude ? (float) $order->deliveryLocation->longitude : null;

        $maxRadius = (float) SystemSetting::getValue('max_delivery_radius_km', 15);

        $query = User::query()
            ->where('role', User::ROLE_TRAVELER)
            ->where('status', User::STATUS_APPROVED)
            ->with(['travelerProfile', 'location']);

        if ($onlineOnly) {
            $query->whereHas('travelerProfile', fn ($q) => $q->where('is_online', true));
        }

        $travelers = $query->get();

        $ranked = $travelers->map(function (User $traveler) use (
            $orderQty,
            $kitchen,
            $customerLat,
            $customerLng,
            $maxRadius
        ) {
            if ($this->travelerHasActiveDelivery((int) $traveler->id)) {
                return null;
            }

            $coords = $this->getTravelerCoordinates($traveler);
            if (! $coords) {
                return null;
            }

            $profile = $traveler->travelerProfile;
            $vehicleType = $profile?->vehicle_type;
            $loadOk = $this->vehicleSuitableForLoad($vehicleType, $orderQty, $profile?->max_load_capacity);

            if (! $loadOk) {
                return null;
            }

            $distToCustomer = $this->haversineKm($coords['lat'], $coords['lng'], $customerLat, $customerLng);
            $distToChef = null;
            $combined = null;

            if ($kitchen) {
                $distToChef = $this->haversineKm($coords['lat'], $coords['lng'], $kitchen['lat'], $kitchen['lng']);
                if ($distToChef !== null && $distToCustomer !== null) {
                    $combined = round($distToChef + $distToCustomer, 2);
                } elseif ($distToChef !== null) {
                    $combined = $distToChef;
                }
            } elseif ($distToCustomer !== null) {
                $combined = $distToCustomer;
            }

            if ($combined === null) {
                return null;
            }

            $travelerRadius = $profile?->delivery_radius ? (float) $profile->delivery_radius : null;
            $allowedRadius = $travelerRadius !== null ? min($travelerRadius, $maxRadius) : $maxRadius;

            if ($combined > $allowedRadius) {
                return null;
            }

            if ($this->matchesPreferredZone($profile?->preferred_zones, $order->deliveryLocation?->city)) {
                // preferred zone match — no filter
            }

            $score = $combined;
            if ($coords['source'] === 'gps') {
                $score -= 0.25;
            }

            return (object) [
                'user' => $traveler,
                'distance_km_to_chef' => $distToChef,
                'distance_km_to_customer' => $distToCustomer,
                'combined_km' => $combined,
                'order_quantity' => $orderQty,
                'vehicle_type' => $vehicleType,
                'vehicle_capacity' => $this->vehicleCapacity($vehicleType),
                'max_load_capacity' => $profile?->max_load_capacity,
                'load_ok' => true,
                'location_source' => $coords['source'],
                'score' => round($score, 2),
                'recommended' => false,
            ];
        })->filter()->sortBy('score')->values();

        if ($ranked->isNotEmpty()) {
            $ranked->first()->recommended = true;
        }

        return $ranked;
    }

    public function assignBestTraveler(Order $order, ?User $chef = null): ?User
    {
        $best = $this->rankTravelersForOrder($order, $chef)->first();

        return $best?->user;
    }

    public function validateAssignment(
        Order $order,
        User $traveler,
        ?User $chef = null,
        bool $onlineOnly = true,
        ?int $exceptDeliveryId = null
    ): ?string {
        if ($traveler->role !== User::ROLE_TRAVELER || $traveler->status !== User::STATUS_APPROVED) {
            return 'Invalid traveler selected.';
        }

        $profile = $traveler->travelerProfile;
        if (! $profile) {
            return 'Traveler profile is incomplete.';
        }

        if ($onlineOnly && ! $profile->is_online) {
            return 'Traveler must be online to receive assignments.';
        }

        if ($this->travelerHasActiveDelivery((int) $traveler->id, $exceptDeliveryId)) {
            return 'Traveler already has an active delivery.';
        }

        $orderQty = $this->orderItemQuantity($order);
        if (! $this->vehicleSuitableForLoad($profile->vehicle_type, $orderQty, $profile->max_load_capacity)) {
            return 'Order quantity (' . $orderQty . ') exceeds this traveler\'s vehicle capacity.';
        }

        $coords = $this->getTravelerCoordinates($traveler);
        if (! $coords) {
            return 'Traveler location is unknown. Ask them to go online and allow GPS on the deliveries page.';
        }

        $kitchen = $this->getPrimaryKitchenForOrder($order, $chef);
        $customerLat = $order->deliveryLocation?->latitude ? (float) $order->deliveryLocation->latitude : null;
        $customerLng = $order->deliveryLocation?->longitude ? (float) $order->deliveryLocation->longitude : null;

        $distToCustomer = $this->haversineKm($coords['lat'], $coords['lng'], $customerLat, $customerLng);
        $distToChef = null;
        $combined = null;

        if ($kitchen) {
            $distToChef = $this->haversineKm($coords['lat'], $coords['lng'], $kitchen['lat'], $kitchen['lng']);
            if ($distToChef !== null && $distToCustomer !== null) {
                $combined = round($distToChef + $distToCustomer, 2);
            } elseif ($distToChef !== null) {
                $combined = $distToChef;
            }
        } elseif ($distToCustomer !== null) {
            $combined = $distToCustomer;
        }

        if ($combined === null) {
            return 'Cannot calculate distance for this order (missing delivery or kitchen coordinates).';
        }

        $maxRadius = (float) SystemSetting::getValue('max_delivery_radius_km', 15);
        $travelerRadius = $profile->delivery_radius ? (float) $profile->delivery_radius : null;
        $allowedRadius = $travelerRadius !== null ? min($travelerRadius, $maxRadius) : $maxRadius;

        if ($combined > $allowedRadius) {
            return 'Traveler is too far away (' . number_format($combined, 1) . ' km; max ' . number_format($allowedRadius, 1) . ' km).';
        }

        return null;
    }

    public function assignTravelerToOrder(
        Order $order,
        User $traveler,
        ?User $chef = null,
        bool $onlineOnly = true,
        ?int $exceptDeliveryId = null
    ): bool {
        if ($this->validateAssignment($order, $traveler, $chef, $onlineOnly, $exceptDeliveryId) !== null) {
            return false;
        }

        $delivery = $order->delivery;
        $earning = $this->pricing->travelerEarningFromDeliveryFee((float) $order->delivery_fee);

        if (! $delivery) {
            $order->delivery()->create([
                'traveler_id' => $traveler->id,
                'status' => 'assigned',
                'traveler_earning' => $earning,
            ]);
        } else {
            $delivery->update([
                'traveler_id' => $traveler->id,
                'status' => 'assigned',
                'traveler_earning' => $delivery->traveler_earning > 0 ? $delivery->traveler_earning : $earning,
            ]);
        }

        return true;
    }

    /**
     * @param  array<int, string>|null  $preferredZones
     */
    private function matchesPreferredZone(?array $preferredZones, ?string $city): bool
    {
        if (empty($preferredZones) || $city === null || $city === '') {
            return true;
        }

        $cityLower = strtolower($city);
        foreach ($preferredZones as $zone) {
            if ($zone !== null && str_contains($cityLower, strtolower((string) $zone))) {
                return true;
            }
        }

        // If traveler set zones but city doesn't match, still allow (soft preference only in scoring future)
        return true;
    }
}
