<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductCatalogService;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function products(Request $request, ProductCatalogService $catalogService)
    {
        return response()->json($catalogService->apiList($request));
    }

    public function product(Product $product, ProductCatalogService $catalogService)
    {
        return response()->json($catalogService->apiDetails($product));
    }

    public function categories(CategoryService $categoryService)
    {
        return response()->json($categoryService->activeCategories());
    }

    public function stock(Request $request, Product $product, ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'size' => ['required', 'string'],
            'color' => ['required', 'string'],
        ]);
        $variant = $variantService->find($product, $validated['size'], $validated['color']);

        abort_unless($variant, 404, 'Variant not found.');

        return response()->json($variantService->stockData($variant));
    }
}
