<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Giỏ hàng phải lưu theo BIẾN THỂ sản phẩm (product_variant_id),
     * vì mỗi size+màu có tồn kho và có thể có giá riêng (price_override).
     * Không lưu trực tiếp product_id ở đây.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // Mỗi biến thể chỉ xuất hiện 1 dòng trong 1 giỏ hàng
            $table->unique(['cart_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};