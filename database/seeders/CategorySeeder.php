<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Tạo sẵn danh mục cơ bản để có dữ liệu test ngay khi chưa có sản phẩm thật.
     * firstOrCreate theo "slug" để chạy lại nhiều lần không bị tạo trùng.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Áo thun', 'slug' => 'ao-thun'],
            ['name' => 'Áo sơ mi', 'slug' => 'ao-so-mi'],
            ['name' => 'Áo khoác', 'slug' => 'ao-khoac'],
            ['name' => 'Quần jean', 'slug' => 'quan-jean'],
            ['name' => 'Quần kaki', 'slug' => 'quan-kaki'],
            ['name' => 'Quần short', 'slug' => 'quan-short'],
            ['name' => 'Đồ lót & Đồ mặc nhà', 'slug' => 'do-lot-do-mac-nha'],
            ['name' => 'Phụ kiện', 'slug' => 'phu-kien'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
