<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'size', 'color', 'color_hex',
        'sku', 'price_override', 'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'integer',
            'stock_quantity' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    /**
     * Giá thực tế hiển thị cho biến thể này: nếu biến thể có giá riêng (price_override)
     * thì lấy giá đó, không thì lấy giá gốc của sản phẩm cha (base_price).
     */
    protected function finalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_override ?? $this->product->base_price,
        );
    }

    /**
     * Kiểm tra còn hàng hay không — dùng ở trang chi tiết sản phẩm khi khách chọn size/màu.
     */
    protected function inStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_quantity > 0,
        );
    }
}
