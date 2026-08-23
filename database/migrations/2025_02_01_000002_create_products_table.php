<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique(); // dùng cho URL: /san-pham/ao-thun-basic
            $table->text('description')->nullable();
            $table->string('material')->nullable(); // chất liệu: "Cotton 100%", "Polyester"...
            $table->unsignedInteger('base_price'); // giá gốc (VNĐ), lưu số nguyên để tránh lỗi làm tròn thập phân
            $table->boolean('is_featured')->default(false); // sản phẩm nổi bật (hiện ở trang chủ)
            $table->boolean('is_active')->default(true); // false = ẩn sản phẩm, không hiện ra ngoài
            $table->unsignedInteger('sold_count')->default(0); // đếm số lượng đã bán, dùng để sắp xếp "bán chạy"
            $table->timestamps();

            // Đánh index cho các cột hay dùng để lọc/sắp xếp, giúp query nhanh hơn khi data lớn
            $table->index('is_featured');
            $table->index('sold_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
