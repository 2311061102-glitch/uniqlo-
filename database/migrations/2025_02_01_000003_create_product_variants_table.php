<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đây là bảng QUAN TRỌNG NHẤT cho tồn kho: 1 sản phẩm (VD "Áo thun basic")
     * có nhiều biến thể theo size+màu (VD: "S-Đen", "M-Đen", "S-Trắng"...),
     * mỗi biến thể có SỐ LƯỢNG TỒN KHO RIÊNG. Khi khách mua, phải trừ tồn kho
     * đúng ở biến thể cụ thể đó, không trừ chung vào bảng products.
     */
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size', 10); // S, M, L, XL, XXL
            $table->string('color');    // Đen, Trắng, Xanh Navy...
            $table->string('color_hex', 7)->nullable(); // "#000000" - để hiện ô màu (swatch) ngoài giao diện
            $table->string('sku')->unique(); // mã quản lý riêng từng biến thể, VD: AT001-S-DEN
            $table->unsignedInteger('price_override')->nullable(); // giá riêng cho biến thể này (null = dùng base_price của sản phẩm)
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->timestamps();

            // 1 sản phẩm không được có 2 biến thể trùng y hệt size+màu
            $table->unique(['product_id', 'size', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
