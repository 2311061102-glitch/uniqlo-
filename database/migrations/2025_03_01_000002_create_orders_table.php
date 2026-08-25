<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * QUAN TRỌNG: các cột địa chỉ giao hàng (recipient_name, phone, province...)
     * được COPY NGUYÊN GIÁ TRỊ vào đây tại thời điểm đặt hàng, KHÔNG chỉ lưu
     * address_id trỏ về bảng addresses. Lý do: nếu sau này khách SỬA hoặc XÓA
     * địa chỉ đó trong sổ địa chỉ, đơn hàng CŨ vẫn phải giữ đúng thông tin
     * giao hàng tại thời điểm đặt hàng — đây là nguyên tắc bắt buộc khi thiết
     * kế hệ thống đơn hàng thật, không riêng gì đồ án.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('order_code')->unique(); // mã đơn hàng hiện cho khách, VD: DH20260824001

            // Snapshot địa chỉ giao hàng tại thời điểm đặt hàng
            $table->string('recipient_name');
            $table->string('phone', 15);
            $table->string('province');
            $table->string('district');
            $table->string('ward');
            $table->string('address_detail');

            $table->unsignedInteger('subtotal');       // tổng tiền hàng (chưa gồm ship/giảm giá)
            $table->unsignedInteger('shipping_fee')->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('total_amount');    // subtotal + shipping_fee - discount_amount

            $table->enum('payment_method', ['cod', 'vietqr', 'momo', 'vnpay']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('order_status', ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])
                  ->default('pending');

            $table->text('note')->nullable(); // ghi chú của khách khi đặt hàng
            $table->timestamps();

            $table->index('order_status');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
