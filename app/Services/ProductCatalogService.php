<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Queries\ProductCatalogQuery;
use Illuminate\Http\Request;

class ProductCatalogService
{
    public function __construct(
        private ProductCatalogQuery $catalogQuery,
        private CategoryService $categoryService,
        private SaleProductService $saleProductService,
    ) {}

    public function webList(Request $request, ?Category $category = null): array
    {
        $filters = $request->only(['category', 'size', 'color', 'min_price', 'max_price', 'material', 'q', 'sort']);

        if ($category) {
            unset($filters['category']);
        }

        $products = $this->catalogQuery
            ->web($filters, $category)
            ->paginate(12)
            ->withQueryString();

        return [
            'products' => $products,
            ...$this->categoryService->filterOptions(),
            'currentCategory' => $category,
            'isSaleCategory' => false,
        ];
    }

    public function apiList(Request $request)
    {
        return $this->catalogQuery
            ->api($request->only(['category', 'size', 'color', 'min_price', 'max_price', 'material', 'search', 'sort']))
            ->paginate($request->integer('per_page', 12))
            ->withQueryString();
    }

    public function saleList(): array
    {
        return [
            'products' => $this->saleProductService->paginate(),
            ...$this->categoryService->filterOptions(),
            'currentCategory' => null,
            'isSaleCategory' => true,
        ];
    }

    public function suggestions(string $search)
    {
        return Product::active()
            ->where('name', 'like', '%'.$search.'%')
            ->select('name', 'slug')
            ->take(6)
            ->get();
    }

    public function details(Product $product): array
    {
        $product->load(['images', 'variants', 'category', 'reviews.user']);

        return [
            'product' => $product,
            'sizes' => $product->variants->pluck('size')->unique()->values(),
            'colors' => $product->variants->unique('color')->values(),
        ];
    }

    public function apiDetails(Product $product): Product
    {
        abort_unless($product->is_active, 404);
        $product->load(['images', 'variants', 'category']);

        return $product;
    }
}
