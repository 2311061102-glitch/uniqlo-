<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'product_variant_id', 'quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Thành tiền của riêng dòng này = đơn giá biến thể * số lượng.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variant->final_price * $this->quantity,
        );
    }
}
