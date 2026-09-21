<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class CartService
{
    const GUEST_COOKIE_NAME = 'guest_cart_token';

    /**
     * Gọi ngay sau khi user đăng nhập/đăng ký thành công (trong AuthController).
     */
    public static function mergeGuestCartIntoUser(User $user): void
    {
        $guestToken = request()->cookie(self::GUEST_COOKIE_NAME);

        if (!$guestToken) {
            return;
        }

        $guestCart = Cart::with('items')->where('guest_token', $guestToken)->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            Cookie::queue(Cookie::forget(self::GUEST_COOKIE_NAME));
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $guestItem->quantity]);
            } else {
                $userCart->items()->create([
                    'product_variant_id' => $guestItem->product_variant_id,
                    'quantity'           => $guestItem->quantity,
                ]);
            }
        }

        $guestCart->delete();

        Cookie::queue(Cookie::forget(self::GUEST_COOKIE_NAME));
    }
}