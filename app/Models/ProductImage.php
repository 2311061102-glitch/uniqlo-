<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'variant_id', 'image_path', 'is_primary', 'sort_order'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://'))
                ? $this->image_path
                : asset('storage/'.$this->image_path),
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
