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

        if (Schema::hasColumn('orders', 'payment_method')) {
            DB::table('orders')->where('payment_method', 'momo')->update(['payment_method' => 'cod']);
        }

        $paymentMethodColumn = Schema::hasColumn('payments', 'method') ? 'method' : 'payment_method';
        if (Schema::hasColumn('payments', $paymentMethodColumn)) {
            DB::table('payments')->where($paymentMethodColumn, 'momo')->update([$paymentMethodColumn => 'cod']);
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::getColumnType('orders', 'payment_method') === 'enum') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
        }

        if ($paymentMethodColumn === 'method' && Schema::getColumnType('payments', 'method') === 'enum') {
            DB::statement("ALTER TABLE payments MODIFY method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
        }
    }

    public function down(): void
    {
        $paymentMethodColumn = Schema::hasColumn('payments', 'method') ? 'method' : 'payment_method';

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::getColumnType('orders', 'payment_method') === 'enum') {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
        }

        if ($paymentMethodColumn === 'method' && Schema::getColumnType('payments', 'method') === 'enum') {
            DB::statement("ALTER TABLE payments MODIFY method ENUM('cod', 'vietqr', 'vnpay') NOT NULL");
        }
    }
};
