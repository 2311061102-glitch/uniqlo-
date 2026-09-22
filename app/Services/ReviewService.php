<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    public function productsReviews(Product $product)
    {
        return $product->reviews()->with('user:id,name')->latest()->paginate(10);
    }

    public function save(User $user, Product $product, array $data): Review
    {
        return $user->reviews()->updateOrCreate(
            ['product_id' => $product->id],
            $data,
        );
    }
}
