<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) return response()->json(['data' => []]);

        $products = Product::active()->with('images')->where(function ($query) use ($term) {
            $query->where('name', 'like', '%'.$term.'%')->orWhere('material', 'like', '%'.$term.'%');
        })->orderByDesc('sold_count')->limit(6)->get();

        return response()->json(['data' => $products->map(fn (Product $product) => [
            'name' => $product->name,
            'url' => route('products.show', $product),
            'price' => number_format($product->base_price, 0, ',', '.').'₫',
            'image' => $product->primary_image?->url,
        ])]);
    }
}
