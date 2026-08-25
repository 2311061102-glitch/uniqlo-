<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tương tự orders, order_items cũng "chụp ảnh" (snapshot) lại tên sản phẩm,
     * size, màu, đơn giá TẠI THỜI ĐIỂM ĐẶT HÀNG — vì sau này Thành viên 2/4 có
     * thể đổi tên sản phẩm, đổi giá, thậm chí xóa sản phẩm, nhưng đơn hàng cũ
     * (hóa đơn) phải giữ nguyên đúng những gì khách đã mua lúc đó.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('product_variant_id')->constrained();

            $table->string('product_name');   // snapshot tên sản phẩm
            $table->string('variant_size');   // snapshot size
            $table->string('variant_color');  // snapshot màu
            $table->unsignedInteger('unit_price'); // snapshot đơn giá tại thời điểm mua
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('subtotal'); // unit_price * quantity

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
