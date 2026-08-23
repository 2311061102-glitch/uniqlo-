<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description',
        'material', 'base_price', 'is_featured', 'is_active', 'sold_count',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'base_price' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(5);
                // thêm 5 ký tự random phía sau để tránh trùng slug nếu 2 sản phẩm cùng tên
            }
        });
    }

    // ----- Quan hệ -----

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // ----- Scope: cách viết query rút gọn, dùng như Product::active()->get() -----

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBestSelling($query)
    {
        return $query->orderByDesc('sold_count');
    }

    // ----- Accessor: thuộc tính tính toán, gọi như $product->total_stock -----

    /**
     * Tổng tồn kho = cộng dồn tồn kho của TẤT CẢ biến thể (size+màu) của sản phẩm này.
     */
    protected function totalStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants->sum('stock_quantity'),
        );
    }

    /**
     * Điểm đánh giá trung bình (VD 4.5), làm tròn 1 chữ số thập phân.
     * Nếu chưa có review nào thì trả về null (Controller/Blade tự xử lý hiện "Chưa có đánh giá").
     */
    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews->isNotEmpty()
                ? round($this->reviews->avg('rating'), 1)
                : null,
        );
    }

    /**
     * Ảnh đại diện: ưu tiên ảnh có is_primary = true, nếu không có thì lấy ảnh đầu tiên.
     */
    protected function primaryImage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->images->firstWhere('is_primary', true) ?? $this->images->first(),
        );
    }
}
