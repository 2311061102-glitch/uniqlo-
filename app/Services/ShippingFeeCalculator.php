<?php

namespace App\Services;

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
        if ($subtotal >= self::FREE_SHIP_THRESHOLD) {
            return 0;
        }

        return self::FEE_BY_REGION[$region] ?? self::FEE_BY_REGION['other'];
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