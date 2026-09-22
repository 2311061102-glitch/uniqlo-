<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()->wishlists()->with(['product.images', 'product.variants', 'product.reviews'])->latest()->paginate(12);
        return view('wishlist.index', compact('wishlists'));
    }

    public function toggle(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $wishlist = $request->user()->wishlists()->where('product_id', $product->id)->first();
        if ($wishlist) {
            $wishlist->delete();
            $wishlisted = false;
            $message = 'Đã bỏ sản phẩm khỏi danh sách yêu thích.';
        } else {
            $request->user()->wishlists()->create(['product_id' => $product->id]);
            $wishlisted = true;
            $message = 'Đã thêm sản phẩm vào danh sách yêu thích.';
        }

        if ($request->expectsJson()) return response()->json(['wishlisted' => $wishlisted, 'message' => $message]);
        return back()->with('success', $message);
    }
}
