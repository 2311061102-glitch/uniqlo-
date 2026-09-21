<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();

            // Tham chiếu tới sổ địa chỉ (để tiện tra cứu), nhưng vẫn SAO CHÉP đầy đủ
            // thông tin địa chỉ vào các cột bên dưới. Vì nếu khách sau này sửa/xóa địa chỉ
            // trong sổ địa chỉ, đơn hàng cũ vẫn phải giữ nguyên đúng địa chỉ lúc đặt hàng.
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();

            $table->string('recipient_name');
            $table->string('recipient_phone', 15);
            $table->string('province');
            $table->string('district');
            $table->string('ward');
            $table->string('address_detail');

            $table->decimal('subtotal_amount', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            $table->string('payment_method'); // cod, bank_transfer, qr
            $table->string('payment_status')->default('unpaid');
            $table->string('order_status')->default('pending'); // pending, processing, shipping, completed, cancelled

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};