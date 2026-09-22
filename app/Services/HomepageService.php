<?php

namespace App\Services;

use App\Models\Product;

class HomepageService
{
    public function __construct(private CategoryService $categoryService) {}

    public function data(): array
    {
        return [
            'products' => Product::active()->with(['images', 'reviews'])->latest()->take(8)->get(),
            'bestSellers' => Product::active()->bestSelling()->with(['images', 'reviews'])->take(8)->get(),
            'featuredCategories' => $this->categoryService->featuredCategories(),
        ];
    }
}
