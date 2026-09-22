<?php

namespace App\Services;

use App\Models\Product;

class SaleProductService
{
    public function query()
    {
        return Product::query()
            ->active()
            ->where('discount_percent', '>', 0)
            ->with(['images', 'variants', 'reviews'])
            ->orderByDesc('discount_percent');
    }

    public function paginate(int $perPage = 12)
    {
        return $this->query()->paginate($perPage)->withQueryString();
    }
}
