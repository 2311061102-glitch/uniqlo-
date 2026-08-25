<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Giỏ hàng không cần bảng "carts" riêng — mỗi dòng trong cart_items
     * đại diện cho 1 sản phẩm (1 biến thể size+màu cụ thể) mà 1 user đang để trong giỏ.
     * product_variant_id trỏ thẳng tới bảng product_variants (Thành viên 2) vì
     * giá và tồn kho phải tính theo ĐÚNG size+màu, không tính theo sản phẩm chung chung.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // 1 user không thể có 2 dòng giỏ hàng trùng y hệt 1 biến thể
            // (thêm sản phẩm đã có trong giỏ -> cộng dồn số lượng, không tạo dòng mới)
            $table->unique(['user_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
