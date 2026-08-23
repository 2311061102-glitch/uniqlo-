<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * user_id trỏ về bảng "users" (đã có sẵn từ phần Thành viên 1).
     * Vì cả nhóm dùng chung 1 database, bảng users phải được tạo TRƯỚC khi
     * chạy migration này (đã đúng thứ tự nếu bạn chạy migrate của Thành viên 1 trước).
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1 đến 5 sao, check ở tầng validate (FormRequest), không check ở DB
            $table->text('comment')->nullable();
            $table->timestamps();

            // 1 user chỉ được đánh giá 1 sản phẩm đúng 1 lần (tránh spam review)
            $table->unique(['product_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
