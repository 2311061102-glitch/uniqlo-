<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Lưu đúng trường "id" giao dịch mà SePay gửi sang, dùng để CHỐNG XỬ LÝ TRÙNG
            // (SePay có thể gửi lại cùng 1 webhook nhiều lần do retry).
            $table->string('gateway_transaction_id')->nullable()->unique()->after('transaction_code');

            // Lưu nguyên payload webhook để đối chiếu/debug khi cần, không bắt buộc dùng tới.
            $table->text('raw_webhook_payload')->nullable()->after('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway_transaction_id', 'raw_webhook_payload']);
        });
    }
};