<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Áo thun AIRism Cotton', 'slug' => 'ao-thun-airism-cotton', 'category' => 'ao-thun', 'material' => 'Cotton pha AIRism', 'price' => 399000, 'discount' => 20, 'sold' => 248, 'featured' => true, 'images' => ['photo-1521572163474-6864f9cf17ab', 'photo-1503342217505-b0a15ec3261c', 'photo-1489987707025-afc232f7ea0f']],
            ['name' => 'Áo sơ mi Oxford Premium', 'slug' => 'ao-so-mi-oxford-premium', 'category' => 'ao-so-mi', 'material' => 'Cotton Oxford', 'price' => 699000, 'discount' => 15, 'sold' => 183, 'featured' => true, 'images' => ['photo-1602810318383-e386cc2a3ccf', 'photo-1596755094514-f87e34085b2c', 'photo-1603252110481-7ba873bf42ab']],
            ['name' => 'Áo khoác Parka chống UV', 'slug' => 'ao-khoac-parka-chong-uv', 'category' => 'ao-khoac', 'material' => 'Polyester tái chế', 'price' => 1299000, 'sold' => 156, 'featured' => true, 'images' => ['photo-1551028719-00167b16eac5', 'photo-1548883354-7622d03aca27', 'photo-1516826957135-700dedea698c']],
            ['name' => 'Quần jean Slim Fit', 'slug' => 'quan-jean-slim-fit', 'category' => 'quan-jean', 'material' => 'Denim co giãn', 'price' => 899000, 'sold' => 211, 'featured' => false, 'images' => ['photo-1542272604-787c3835535d', 'photo-1473966968600-fa801b869a1a', 'photo-1624378439575-d8705ad7ae80']],
            ['name' => 'Quần kaki Smart Ankle', 'slug' => 'quan-kaki-smart-ankle', 'category' => 'quan-kaki', 'material' => 'Cotton twill', 'price' => 799000, 'sold' => 128, 'featured' => false, 'images' => ['photo-1624378439575-d8705ad7ae80', 'photo-1515886657613-9f3515b0c78f', 'photo-1506629905607-d9c297d7a5d6']],
            ['name' => 'Quần short Dry Stretch', 'slug' => 'quan-short-dry-stretch', 'category' => 'quan-short', 'material' => 'Polyester Dry', 'price' => 499000, 'discount' => 10, 'sold' => 196, 'featured' => true, 'images' => ['photo-1591195853828-11db59a44f6b', 'photo-1565084888279-aca607ecce0c', 'photo-1517841905240-472988babdf9']],
        ];

        $sizes = ['S', 'M', 'L', 'XL'];
        $colors = [
            ['name' => 'Đen', 'hex' => '#111111'],
            ['name' => 'Trắng', 'hex' => '#f5f5f5'],
            ['name' => 'Xanh navy', 'hex' => '#1d2d44'],
            ['name' => 'Be', 'hex' => '#c9b79c'],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['category'])->first();
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                ['category_id' => $category?->id, 'name' => $data['name'], 'material' => $data['material'], 'base_price' => $data['price'], 'discount_percent' => $data['discount'] ?? 0, 'sold_count' => $data['sold'], 'is_featured' => $data['featured'], 'description' => 'Thiết kế tối giản, dễ phối và thoải mái cho nhịp sống hàng ngày.'],
            );

            $variantIndex = 0;

            foreach ($sizes as $size) {
                foreach ($colors as $color) {
                    $productVariant = $product->variants()->updateOrCreate(
                        ['size' => $size, 'color' => $color['name']],
                        [
                            'color_hex' => $color['hex'],
                            'sku' => strtoupper('UN-'.$product->id.'-'.$size.'-'.$variantIndex),
                            'stock_quantity' => 12 + (($variantIndex + $product->id) % 5) * 6,
                        ],
                    );

                    $image = 'https://images.unsplash.com/'.$data['images'][$variantIndex % count($data['images'])].'?auto=format&fit=crop&w=900&q=85';
                    $product->images()->firstOrCreate(
                        ['image_path' => $image],
                        ['variant_id' => $productVariant->id, 'is_primary' => $variantIndex === 0, 'sort_order' => $variantIndex],
                    );

                    $variantIndex++;
                }
            }
        }
    }
}
