<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng categories hỗ trợ danh mục CON qua parent_id (tự trỏ về chính nó).
     * Ví dụ: "Áo" (parent) -> "Áo thun", "Áo sơ mi" (con của Áo).
     * Nếu chỉ cần danh mục 1 cấp (không lồng nhau) thì vẫn dùng bảng này bình thường,
     * chỉ cần luôn để parent_id = null.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique(); // dùng cho URL đẹp: /danh-muc/ao-thun-nam
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
