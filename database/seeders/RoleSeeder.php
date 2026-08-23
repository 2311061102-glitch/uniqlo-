<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Tạo sẵn 3 role cơ bản trong hệ thống, chạy 1 lần sau khi migrate.
     * firstOrCreate: nếu role đã tồn tại thì bỏ qua, không tạo trùng.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Quản trị viên'],
            ['name' => 'staff', 'display_name' => 'Nhân viên'],
            ['name' => 'customer', 'display_name' => 'Khách hàng'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
