<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Product $product, ReviewService $reviewService)
    {
        return response()->json($reviewService->productsReviews($product));
    }

    public function store(Request $request, ReviewService $reviewService)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $review = $reviewService->save($request->user(), $product, [
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review->load('user:id,name'), 201);
    }
}
