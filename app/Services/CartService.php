<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartService
{
    const GUEST_COOKIE_NAME = 'guest_cart_token';

    public static function userCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public static function currentCart(Request $request, bool $create = true): ?Cart
    {
        if ($request->user()) {
            return $create ? self::userCart($request->user()) : Cart::where('user_id', $request->user()->id)->first();
        }

        $token = $request->cookie(self::GUEST_COOKIE_NAME);
        $cart = $token ? Cart::where('guest_token', $token)->first() : null;
        if ($cart || ! $create) {
            return $cart;
        }

        $token = (string) Str::uuid();
        Cookie::queue(self::GUEST_COOKIE_NAME, $token, 60 * 24 * 30);

        return Cart::create(['guest_token' => $token]);
    }

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

        $userCart = self::userCart($user);

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
