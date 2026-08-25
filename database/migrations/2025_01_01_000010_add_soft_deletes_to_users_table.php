<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft delete: khi user "xóa tài khoản", KHÔNG xóa thật khỏi database,
     * chỉ đánh dấu thời điểm xóa vào cột deleted_at. User bị ẩn khỏi mọi
     * query bình thường (không đăng nhập được, không hiện trong danh sách),
     * nhưng dữ liệu vẫn còn (địa chỉ, đánh giá cũ...) — tránh mất dữ liệu
     * liên quan nếu user xóa tài khoản rồi hối hận, hoặc cần tra cứu sau này.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes(); // tương đương $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
