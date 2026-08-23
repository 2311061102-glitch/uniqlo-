<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1 sản phẩm có NHIỀU ảnh (gallery). variant_id để nullable vì:
     * - Nếu variant_id = null: ảnh chung cho cả sản phẩm (VD ảnh mô tả chất liệu)
     * - Nếu variant_id có giá trị: ảnh RIÊNG cho 1 màu cụ thể
     *   (VD khách chọn màu "Đen" thì đổi sang đúng ảnh áo màu đen)
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('image_path');
            $table->boolean('is_primary')->default(false); // ảnh đại diện hiện ở trang danh sách sản phẩm
            $table->unsignedInteger('sort_order')->default(0); // thứ tự hiện ảnh trong gallery
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
