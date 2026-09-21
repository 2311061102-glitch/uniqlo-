<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gỡ giá trị phương thức thanh toán MoMo khỏi schema đang chạy.
     * Đơn/giao dịch cũ dùng cổng đó được chuyển sang COD để enum mới hợp lệ.
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasTable('payments')) {
            return;
        }

        DB::table('orders')->where('payment_method', 'momo')->update(['payment_method' => 'cod']);
        DB::table('payments')->where('method', 'momo')->update(['method' => 'cod']);

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
    }
};
