<?php

namespace App\Services;

use App\Models\Address;

class ShippingFeeCalculator
{
    const FEE_BY_REGION = [
        'inner_city' => 15000,
        'outer_city' => 25000,
        'other'      => 35000,
    ];

    const FREE_SHIP_THRESHOLD = 500000;

    public static function calculate(string $region, float $subtotal): float
    {
        if ($subtotal >= self::freeShipThreshold()) {
            return 0;
        }

        return self::FEE_BY_REGION[$region] ?? self::FEE_BY_REGION['other'];
    }

    public static function calculateForAddress(?Address $address, float $subtotal): float
    {
        if ($subtotal >= self::freeShipThreshold()) {
            return 0;
        }

        $distance = self::distanceInKm($address?->latitude, $address?->longitude);
        if ($distance === null) {
            return self::FEE_BY_REGION['other'];
        }

        foreach (self::distanceFeeTiers() as $tier) {
            $aboveMinimum = $tier['min_distance'] === null || $distance >= $tier['min_distance'];
            $belowMaximum = $tier['max_distance'] === null || $distance < $tier['max_distance'];

            if ($aboveMinimum && $belowMaximum) {
                return $tier['fee'];
            }
        }

        return self::FEE_BY_REGION['other'];
    }

    /**
     * Mức phí giao hàng thân thiện với khách hàng, dùng chung cho backend và checkout.
     */
    public static function distanceFeeTiers(): array
    {
        return [
            ['label' => 'Dưới 5 km', 'min_distance' => null, 'max_distance' => 5, 'fee' => 0],
            ['label' => 'Từ 5 đến dưới 15 km', 'min_distance' => 5, 'max_distance' => 15, 'fee' => 15000],
            ['label' => 'Từ 15 đến dưới 30 km', 'min_distance' => 15, 'max_distance' => 30, 'fee' => 25000],
            ['label' => 'Từ 30 đến dưới 60 km', 'min_distance' => 30, 'max_distance' => 60, 'fee' => 35000],
            ['label' => 'Từ 60 km trở lên', 'min_distance' => 60, 'max_distance' => null, 'fee' => 50000],
        ];
    }

    public static function distanceInKm(?float $latitude, ?float $longitude): ?float
    {
        return self::nearestBranchForCoordinates($latitude, $longitude)['distance_km'] ?? null;
    }

    public static function nearestBranch(?Address $address): ?array
    {
        return self::nearestBranchForCoordinates($address?->latitude, $address?->longitude);
    }

    public static function branches(): array
    {
        return config('services.shipping.branches', []);
    }

    private static function nearestBranchForCoordinates(?float $latitude, ?float $longitude): ?array
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $nearest = null;
        foreach (self::branches() as $branch) {
            $distance = self::haversineDistance(
                (float) $latitude,
                (float) $longitude,
                (float) $branch['latitude'],
                (float) $branch['longitude'],
            );

            if ($nearest === null || $distance < $nearest['distance_km']) {
                $nearest = array_merge($branch, ['distance_km' => $distance]);
            }
        }

        return $nearest;
    }

    private static function haversineDistance(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($targetLatitude - $latitude);
        $lonDelta = deg2rad($targetLongitude - $longitude);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($targetLatitude)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function freeShipThreshold(): float
    {
        return (float) config('services.shipping.free_threshold', self::FREE_SHIP_THRESHOLD);
    }

    public static function regionOptions(): array
    {
        return [
            'inner_city' => 'Nội thành (' . number_format(self::FEE_BY_REGION['inner_city']) . 'đ)',
            'outer_city' => 'Ngoại thành (' . number_format(self::FEE_BY_REGION['outer_city']) . 'đ)',
            'other'      => 'Tỉnh khác (' . number_format(self::FEE_BY_REGION['other']) . 'đ)',
        ];
    }
}
