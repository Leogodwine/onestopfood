<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SystemSetting;

class OrderPricingService
{
    public function baseDeliveryFee(): float
    {
        return (float) SystemSetting::getValue('base_delivery_fee', 2000);
    }

    public function deliveryFeePerKm(): float
    {
        return (float) SystemSetting::getValue('delivery_fee_per_km', 500);
    }

    /**
     * Delivery fee from admin logistics settings. Uses per-km component when coordinates exist.
     */
    public function calculateDeliveryFee(?Location $deliveryLocation = null): float
    {
        $fee = $this->baseDeliveryFee();

        if (
            $deliveryLocation
            && $deliveryLocation->latitude !== null
            && $deliveryLocation->longitude !== null
        ) {
            $hubLat = (float) config('food_delivery.hub_latitude', -6.7924);
            $hubLng = (float) config('food_delivery.hub_longitude', 39.2083);
            $km = $this->haversineKm(
                $hubLat,
                $hubLng,
                (float) $deliveryLocation->latitude,
                (float) $deliveryLocation->longitude
            );
            $maxKm = (float) SystemSetting::getValue('max_delivery_radius_km', 15);
            $km = min($km, $maxKm);
            $fee += $km * $this->deliveryFeePerKm();
        }

        return round($fee, 2);
    }

    public function travelerEarningFromDeliveryFee(float $deliveryFee): float
    {
        $platformRate = (int) SystemSetting::getValue('traveler_commission_rate', 5);
        $travelerShare = max(0, min(100, 100 - $platformRate));

        return round($deliveryFee * ($travelerShare / 100), 2);
    }

    /**
     * Production payment records start pending until gateway confirmation or COD collection.
     */
    public function initialPaymentStatus(string $method): string
    {
        return 'pending';
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
