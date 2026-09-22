<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request, WishlistService $wishlistService)
    {
        $products = $wishlistService->products($request->user());

        return view('wishlist.index', compact('products'));
    }

    public function toggle(Request $request, Product $product, WishlistService $wishlistService)
    {
        $message = $wishlistService->toggle($request->user(), $product)
            ? 'Đã thêm vào danh sách yêu thích.'
            : 'Đã xóa khỏi danh sách yêu thích.';

        return back()->with('success', $message);
    }
}
