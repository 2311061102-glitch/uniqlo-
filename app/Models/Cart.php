<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'guest_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Tổng tiền hàng trong giỏ (chưa gồm ship, chưa trừ voucher).
     * Luôn dùng final_price (giá THẬT hiện tại của biến thể) để tính,
     * không lưu giá cứng trong cart_items để tránh sai lệch khi giá thay đổi.
     */
    public function subtotal(): float
    {
        return $this->items->sum(function ($item) {
            return $item->variant->final_price * $item->quantity;
        });
    }

    public function totalQuantity(): int
    {
        return $this->items->sum('quantity');
    }
}