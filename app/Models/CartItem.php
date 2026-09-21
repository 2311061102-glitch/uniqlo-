<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_variant_id', 'quantity'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Truy cập nhanh sản phẩm cha từ 1 dòng giỏ hàng: $cartItem->product
     */
    public function product()
    {
        return $this->variant->product;
    }

    public function subtotal(): float
    {
        return $this->variant->final_price * $this->quantity;
    }
}