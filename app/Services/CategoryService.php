<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ProductVariant;

class CategoryService
{
    public function activeRootCategories()
    {
        return Category::where('is_active', true)->whereNull('parent_id')->with('children')->get();
    }

    public function activeCategories()
    {
        return Category::where('is_active', true)->withCount('products')->get();
    }

    public function featuredCategories(int $limit = 6)
    {
        return Category::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->take($limit)
            ->get();
    }

    public function filterOptions(): array
    {
        return [
            'categories' => $this->activeRootCategories(),
            'sizes' => ProductVariant::query()->select('size')->distinct()->pluck('size'),
            'colors' => ProductVariant::query()->select('color', 'color_hex')->distinct()->get(),
        ];
    }
}
