<?php

namespace App\Services;

use App\Models\Cart;

class CartCounter
{
    /**
     * Đếm tổng số lượng sản phẩm trong giỏ hàng hiện tại (không tạo mới
     * giỏ hàng/Cookie nếu chưa có — chỉ "nhìn" xem đã có giỏ hàng chưa,
     * tránh việc chỉ hiện badge thôi mà lại vô tình tạo ra dữ liệu rác).
     */
    public static function count(): int
    {
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();
        } else {
            $guestToken = request()->cookie(CartService::GUEST_COOKIE_NAME);
            $cart = $guestToken ? Cart::where('guest_token', $guestToken)->first() : null;
        }

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }
}