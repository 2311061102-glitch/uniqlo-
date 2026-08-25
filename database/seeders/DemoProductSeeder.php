<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
        $this->seedImage($p1, 'ao-thun', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=85');

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
        $this->seedImage($p2, 'ao-thun', 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?auto=format&fit=crop&w=900&q=85');

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
        $this->seedImage($p3, 'quan-jean', 'https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=900&q=85');

        $this->seedAdditionalProducts();
    }

    private function seedAdditionalProducts(): void
    {
        $products = [
            ['code' => 'AT003', 'category' => 'ao-thun', 'name' => 'Áo thun Airism cổ tròn', 'material' => 'Polyester Airism', 'price' => 299000, 'sold' => 78],
            ['code' => 'AT004', 'category' => 'ao-thun', 'name' => 'Áo thun Supima cotton', 'material' => 'Cotton Supima', 'price' => 349000, 'sold' => 64],
            ['code' => 'AT005', 'category' => 'ao-thun', 'name' => 'Áo thun oversized graphic', 'material' => 'Cotton 100%', 'price' => 399000, 'sold' => 52],
            ['code' => 'AT006', 'category' => 'ao-thun', 'name' => 'Áo polo thể thao Dry-Ex', 'material' => 'Polyester Dry-Ex', 'price' => 499000, 'sold' => 41],
            ['code' => 'ASM001', 'category' => 'ao-so-mi', 'name' => 'Áo sơ mi Oxford slim fit', 'material' => 'Cotton Oxford', 'price' => 599000, 'sold' => 37],
            ['code' => 'ASM002', 'category' => 'ao-so-mi', 'name' => 'Áo sơ mi linen tay dài', 'material' => 'Linen pha cotton', 'price' => 699000, 'sold' => 29],
            ['code' => 'ASM003', 'category' => 'ao-so-mi', 'name' => 'Áo sơ mi flannel caro', 'material' => 'Cotton flannel', 'price' => 649000, 'sold' => 24],
            ['code' => 'AK001', 'category' => 'ao-khoac', 'name' => 'Áo khoác parka chống nước', 'material' => 'Nylon chống nước', 'price' => 1299000, 'sold' => 19],
            ['code' => 'AK002', 'category' => 'ao-khoac', 'name' => 'Áo khoác denim trucker', 'material' => 'Denim cotton', 'price' => 899000, 'sold' => 33],
            ['code' => 'AK003', 'category' => 'ao-khoac', 'name' => 'Áo khoác nỉ có mũ', 'material' => 'Cotton fleece', 'price' => 799000, 'sold' => 46],
            ['code' => 'AK004', 'category' => 'ao-khoac', 'name' => 'Áo khoác gió siêu nhẹ', 'material' => 'Polyester', 'price' => 749000, 'sold' => 58],
            ['code' => 'QJ002', 'category' => 'quan-jean', 'name' => 'Quần jean straight fit', 'material' => 'Denim co giãn', 'price' => 499000, 'sold' => 27],
            ['code' => 'QJ003', 'category' => 'quan-jean', 'name' => 'Quần jean relaxed fit', 'material' => 'Denim cotton', 'price' => 549000, 'sold' => 22],
            ['code' => 'QK001', 'category' => 'quan-kaki', 'name' => 'Quần kaki slim fit', 'material' => 'Cotton twill', 'price' => 499000, 'sold' => 35],
            ['code' => 'QK002', 'category' => 'quan-kaki', 'name' => 'Quần kaki jogger', 'material' => 'Cotton co giãn', 'price' => 529000, 'sold' => 31],
            ['code' => 'QK003', 'category' => 'quan-kaki', 'name' => 'Quần chinos công sở', 'material' => 'Cotton twill', 'price' => 579000, 'sold' => 26],
            ['code' => 'QS001', 'category' => 'quan-short', 'name' => 'Quần short Dry co giãn', 'material' => 'Polyester Dry', 'price' => 299000, 'sold' => 61],
            ['code' => 'QS002', 'category' => 'quan-short', 'name' => 'Quần short linen', 'material' => 'Linen pha cotton', 'price' => 399000, 'sold' => 44],
            ['code' => 'QS003', 'category' => 'quan-short', 'name' => 'Quần short cargo túi hộp', 'material' => 'Cotton ripstop', 'price' => 449000, 'sold' => 38],
            ['code' => 'DNM001', 'category' => 'do-lot-do-mac-nha', 'name' => 'Áo ba lỗ Airism nam', 'material' => 'Polyester Airism', 'price' => 199000, 'sold' => 72],
            ['code' => 'DNM002', 'category' => 'do-lot-do-mac-nha', 'name' => 'Quần short mặc nhà', 'material' => 'Cotton jersey', 'price' => 249000, 'sold' => 55],
            ['code' => 'DNM003', 'category' => 'do-lot-do-mac-nha', 'name' => 'Bộ đồ mặc nhà cotton', 'material' => 'Cotton 100%', 'price' => 399000, 'sold' => 28],
            ['code' => 'PK001', 'category' => 'phu-kien', 'name' => 'Mũ lưỡi trai cotton', 'material' => 'Cotton', 'price' => 199000, 'sold' => 49],
            ['code' => 'PK002', 'category' => 'phu-kien', 'name' => 'Thắt lưng da tối giản', 'material' => 'Da tổng hợp', 'price' => 299000, 'sold' => 32],
            ['code' => 'PK003', 'category' => 'phu-kien', 'name' => 'Tất cổ ngắn thể thao', 'material' => 'Cotton pha', 'price' => 99000, 'sold' => 86],
        ];

        $topSizes = ['S', 'M', 'L', 'XL'];
        $topColors = [
            ['name' => 'Đen', 'hex' => '#000000'],
            ['name' => 'Trắng', 'hex' => '#FFFFFF'],
            ['name' => 'Xanh Navy', 'hex' => '#1A2B4C'],
        ];
        $bottomSizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $bottomColors = [
            ['name' => 'Đen', 'hex' => '#000000'],
            ['name' => 'Be', 'hex' => '#C6A77B'],
            ['name' => 'Xanh đậm', 'hex' => '#2C3E6B'],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            if (! $category) {
                continue;
            }

            $product = Product::firstOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id' => $category->id,
                    'material' => $productData['material'],
                    'description' => $productData['name'].', thiết kế hiện đại, thoải mái và phù hợp cho phong cách nam hàng ngày.',
                    'base_price' => $productData['price'],
                    'is_featured' => $productData['sold'] >= 50,
                    'sold_count' => $productData['sold'],
                ]
            );

            if (! $product->variants()->exists()) {
                $isBottom = in_array($productData['category'], ['quan-jean', 'quan-kaki', 'quan-short'], true);
                $sizes = $isBottom ? $bottomSizes : $topSizes;
                $colors = $isBottom ? $bottomColors : $topColors;
                $variants = [];

                foreach ($sizes as $sizeIndex => $size) {
                    foreach ($colors as $colorIndex => $color) {
                        $variants[] = [
                            'size' => $size,
                            'color' => $color['name'],
                            'color_hex' => $color['hex'],
                            'sku' => $productData['code'].'-'.$size.'-'.($colorIndex + 1),
                            'stock_quantity' => 8 + (($sizeIndex + $colorIndex) % 5) * 4,
                        ];
                    }
                }

                $product->variants()->createMany($variants);
            }

            $this->seedImage($product, $productData['category'], $this->imageUrlFor($productData['code']));
        }
    }

    private function seedImage(Product $product, string $category, string $imageUrl): void
    {
        if ($product->images()->exists()) {
            return;
        }

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $imageUrl,
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    private function imageUrlFor(string $code): string
    {
        $imageUrls = [
            'AT003' => 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?auto=format&fit=crop&w=900&q=85',
            'AT004' => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=900&q=85',
            'AT005' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=900&q=85',
            'AT006' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=900&q=85',
            'ASM001' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=900&q=85',
            'ASM002' => 'https://images.unsplash.com/photo-1603252109303-2751441dd157?auto=format&fit=crop&w=900&q=85',
            'ASM003' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?auto=format&fit=crop&w=900&q=85',
            'AK001' => 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?auto=format&fit=crop&w=900&q=85',
            'AK002' => 'https://images.unsplash.com/photo-1495105787522-5334e3ffa0ef?auto=format&fit=crop&w=900&q=85',
            'AK003' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=900&q=85',
            'AK004' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?auto=format&fit=crop&w=900&q=85',
            'QJ002' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=900&q=85',
            'QJ003' => 'https://images.unsplash.com/photo-1475178626620-a4d074967452?auto=format&fit=crop&w=900&q=85',
            'QK001' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?auto=format&fit=crop&w=900&q=85',
            'QK002' => 'https://images.unsplash.com/photo-1517438476312-10d79c077509?auto=format&fit=crop&w=900&q=85',
            'QK003' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=900&q=85',
            'QS001' => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?auto=format&fit=crop&w=900&q=85',
            'QS002' => 'https://images.unsplash.com/photo-1565084888279-aca607ecce0c?auto=format&fit=crop&w=900&q=85',
            'QS003' => 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?auto=format&fit=crop&w=900&q=85',
            'DNM001' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=900&q=85',
            'DNM002' => 'https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?auto=format&fit=crop&w=900&q=85',
            'DNM003' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?auto=format&fit=crop&w=900&q=85',
            'PK001' => 'https://images.unsplash.com/photo-1521369909029-2afed882baee?auto=format&fit=crop&w=900&q=85',
            'PK002' => 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=900&q=85',
            'PK003' => 'https://images.unsplash.com/photo-1582966772680-860e372bb558?auto=format&fit=crop&w=900&q=85',
        ];

        return $imageUrls[$code] ?? 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=85';
    }
}
