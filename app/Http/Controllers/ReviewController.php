<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product, ReviewService $reviewService)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $reviewService->save($request->user(), $product, $validated);

        return back()->with('success', 'Cảm ơn bạn đã chia sẻ đánh giá.');
    }
}
