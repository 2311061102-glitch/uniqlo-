<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoProductSeeder extends Seeder
{
    /**
     * Tạo vài sản phẩm mẫu kèm biến thể (size+màu+tồn kho) để có dữ liệu
     * test ngay sau mỗi lần migrate:fresh, không cần vào Tinker gõ tay lại.
     * CategorySeeder PHẢI chạy trước seeder này (đã xếp đúng thứ tự trong
     * DatabaseSeeder.php).
     */
    public function run(): void
    {
        $aoThun = Category::where('slug', 'ao-thun')->first();
        $quanJean = Category::where('slug', 'quan-jean')->first();

        if (! $aoThun || ! $quanJean) {
            return; // an toàn: nếu danh mục chưa có thì bỏ qua, tránh lỗi
        }

        $p1 = Product::firstOrCreate(
            ['name' => 'Áo thun cotton basic'],
            [
                'category_id' => $aoThun->id,
                'material' => 'Cotton 100%',
                'description' => 'Áo thun cotton basic, form regular fit, thoáng mát, phù hợp mặc hàng ngày.',
                'base_price' => 199000,
                'is_featured' => true,
                'sold_count' => 120,
            ]
        );

        if ($p1->variants()->count() === 0) {
            $p1->variants()->createMany([
                ['size' => 'S', 'color' => 'Đen', 'color_hex' => '#000000', 'sku' => 'AT001-S-DEN', 'stock_quantity' => 15],
                ['size' => 'M', 'color' => 'Đen', 'color_hex' => '#000000', 'sku' => 'AT001-M-DEN', 'stock_quantity' => 20],
                ['size' => 'L', 'color' => 'Đen', 'color_hex' => '#000000', 'sku' => 'AT001-L-DEN', 'stock_quantity' => 10],
                ['size' => 'M', 'color' => 'Trắng', 'color_hex' => '#FFFFFF', 'sku' => 'AT001-M-TRANG', 'stock_quantity' => 18],
                ['size' => 'L', 'color' => 'Trắng', 'color_hex' => '#FFFFFF', 'sku' => 'AT001-L-TRANG', 'stock_quantity' => 0], // cố tình để hết hàng, test trạng thái "Hết hàng"
            ]);
        }

        $p2 = Product::firstOrCreate(
            ['name' => 'Áo thun polyester thể thao'],
            [
                'category_id' => $aoThun->id,
                'material' => 'Polyester',
                'description' => 'Áo thun thể thao, chất liệu polyester co giãn, thấm hút mồ hôi tốt.',
                'base_price' => 249000,
                'sold_count' => 45,
            ]
        );

        if ($p2->variants()->count() === 0) {
            $p2->variants()->createMany([
                ['size' => 'M', 'color' => 'Xanh Navy', 'color_hex' => '#1a2b4c', 'sku' => 'AT002-M-NAVY', 'stock_quantity' => 12],
                ['size' => 'L', 'color' => 'Xanh Navy', 'color_hex' => '#1a2b4c', 'sku' => 'AT002-L-NAVY', 'stock_quantity' => 8],
            ]);
        }

        $p3 = Product::firstOrCreate(
            ['name' => 'Quần jean slim fit'],
            [
                'category_id' => $quanJean->id,
                'material' => 'Denim',
                'description' => 'Quần jean slim fit, form ôm nhẹ, chất denim co giãn thoải mái vận động.',
                'base_price' => 459000,
                'sold_count' => 30,
            ]
        );

        if ($p3->variants()->count() === 0) {
            $p3->variants()->createMany([
                ['size' => '30', 'color' => 'Xanh đậm', 'color_hex' => '#2c3e6b', 'sku' => 'QJ001-30-XANH', 'stock_quantity' => 10],
                ['size' => '31', 'color' => 'Xanh đậm', 'color_hex' => '#2c3e6b', 'sku' => 'QJ001-31-XANH', 'stock_quantity' => 7],
                ['size' => '32', 'color' => 'Đen', 'color_hex' => '#000000', 'sku' => 'QJ001-32-DEN', 'stock_quantity' => 5],
            ]);
        }
    }
}
