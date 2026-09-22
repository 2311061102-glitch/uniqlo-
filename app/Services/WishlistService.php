<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

class WishlistService
{
    public function products(User $user)
    {
        return $user->wishlists()
            ->with(['product.images', 'product.reviews'])
            ->latest()
            ->paginate(12);
    }

    public function add(User $user, Product $product): Wishlist
    {
        return $user->wishlists()->firstOrCreate(['product_id' => $product->id]);
    }

    public function remove(User $user, Product $product): void
    {
        $user->wishlists()->where('product_id', $product->id)->delete();
    }

    public function toggle(User $user, Product $product): bool
    {
        $wishlist = $user->wishlists()->where('product_id', $product->id)->first();

        if ($wishlist) {
            $wishlist->delete();

            return false;
        }

        $this->add($user, $product);

        return true;
    }
}
