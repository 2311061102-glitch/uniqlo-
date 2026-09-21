<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Chỉ có giá trị với đơn thanh toán QR/Chuyển khoản. Sau mốc thời gian này
            // mà vẫn chưa thanh toán -> đơn tự động bị hủy, hoàn lại tồn kho.
            $table->timestamp('qr_expires_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qr_expires_at');
        });
    }
};