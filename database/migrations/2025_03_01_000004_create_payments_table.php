<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng riêng cho GIAO DỊCH thanh toán (khác với orders.payment_status chỉ là
     * trạng thái tóm tắt). Cần bảng riêng vì:
     * - 1 đơn hàng có thể có NHIỀU lần thử thanh toán (lần 1 thất bại, thử lại lần 2)
     * - Cần lưu lại dữ liệu THÔ mà cổng thanh toán trả về (gateway_response) để
     *   tra cứu/đối soát khi có tranh chấp, hoặc để debug khi callback bị lỗi
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->enum('method', ['cod', 'vietqr', 'momo', 'vnpay']);
            $table->unsignedInteger('amount');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');

            $table->string('gateway_transaction_id')->nullable(); // mã giao dịch bên MoMo/VNPay trả về
            $table->json('gateway_response')->nullable(); // toàn bộ dữ liệu thô cổng thanh toán gửi callback về
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
