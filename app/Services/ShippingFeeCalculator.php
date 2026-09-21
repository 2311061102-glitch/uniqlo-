<?php

namespace App\Services;

use App\Models\Address;

class ShippingFeeCalculator
{
    const FEE_BY_REGION = [
        'inner_city' => 20000,
        'outer_city' => 35000,
        'other'      => 50000,
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

        return match (true) {
            $distance <= 5 => 20000,
            $distance <= 15 => 30000,
            $distance <= 30 => 45000,
            $distance <= 60 => 65000,
            default => 90000,
        };
    }

    public static function distanceInKm(?float $latitude, ?float $longitude): ?float
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $warehouseLatitude = (float) config('services.shipping.warehouse_latitude', 21.0712);
        $warehouseLongitude = (float) config('services.shipping.warehouse_longitude', 105.7489);
        $earthRadius = 6371;
        $latDelta = deg2rad($latitude - $warehouseLatitude);
        $lonDelta = deg2rad($longitude - $warehouseLongitude);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($warehouseLatitude)) * cos(deg2rad($latitude)) * sin($lonDelta / 2) ** 2;

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
