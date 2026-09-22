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
        $parents = [
            ['name' => 'Áo nam', 'slug' => 'ao-nam'],
            ['name' => 'Quần nam', 'slug' => 'quan-nam'],
        ];

        foreach ($parents as $parent) {
            Category::updateOrCreate(['slug' => $parent['slug']], $parent + ['parent_id' => null]);
        }

        $aoNam = Category::where('slug', 'ao-nam')->firstOrFail();
        $quanNam = Category::where('slug', 'quan-nam')->firstOrFail();

        $categories = [
            ['name' => 'Áo Polo', 'slug' => 'ao-polo', 'parent_id' => $aoNam->id],
            ['name' => 'Áo thun', 'slug' => 'ao-thun', 'parent_id' => $aoNam->id],
            ['name' => 'Áo sơ mi', 'slug' => 'ao-so-mi', 'parent_id' => $aoNam->id],
            ['name' => 'Áo - Quần nỉ', 'slug' => 'ao-quan-ni', 'parent_id' => $aoNam->id],
            ['name' => 'Áo Blazer', 'slug' => 'ao-blazer', 'parent_id' => $aoNam->id],
            ['name' => 'Áo len', 'slug' => 'ao-len', 'parent_id' => $aoNam->id],
            ['name' => 'Áo khoác', 'slug' => 'ao-khoac', 'parent_id' => $aoNam->id],
            ['name' => 'Quần jean', 'slug' => 'quan-jean', 'parent_id' => $quanNam->id],
            ['name' => 'Quần kaki', 'slug' => 'quan-kaki', 'parent_id' => $quanNam->id],
            ['name' => 'Quần short', 'slug' => 'quan-short', 'parent_id' => $quanNam->id],
            ['name' => 'Đồ lót & Đồ mặc nhà', 'slug' => 'do-lot-do-mac-nha', 'parent_id' => null],
            ['name' => 'Phụ kiện', 'slug' => 'phu-kien', 'parent_id' => null],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
