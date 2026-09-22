<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function store(Request $request, Product $product, WishlistService $wishlistService)
    {
        $wishlistService->add($request->user(), $product);

        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function destroy(Request $request, Product $product, WishlistService $wishlistService)
    {
        $wishlistService->remove($request->user(), $product);

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
