<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantService
{
    public function find(Product $product, string $size, string $color): ?ProductVariant
    {
        return $product->variants()
            ->where('size', $size)
            ->where('color', $color)
            ->first();
    }

    public function stockData(ProductVariant $variant): array
    {
        return [
            'variant_id' => $variant->id,
            'sku' => $variant->sku,
            'size' => $variant->size,
            'color' => $variant->color,
            'in_stock' => $variant->in_stock,
            'stock_quantity' => $variant->stock_quantity,
            'price' => $variant->final_price,
        ];
    }
}
