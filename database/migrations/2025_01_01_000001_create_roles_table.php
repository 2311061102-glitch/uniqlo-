<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng roles: lưu các vai trò trong hệ thống (admin, staff, customer)
     * Dùng để phân quyền thay vì viết cứng if/else trong code.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin | staff | customer (dùng để so sánh trong code)
            $table->string('display_name')->nullable(); // "Quản trị viên" (dùng để hiển thị ra giao diện)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
