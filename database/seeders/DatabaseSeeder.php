<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * File này là "điểm vào" khi chạy: php artisan db:seed (hoặc migrate:fresh --seed)
     * Liệt kê thứ tự gọi các seeder khác — PHẢI đúng thứ tự phụ thuộc:
     * roles trước (vì users cần role_id), categories trước products
     * (vì products cần category_id).
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            DemoProductSeeder::class,
        ]);
    }
}
