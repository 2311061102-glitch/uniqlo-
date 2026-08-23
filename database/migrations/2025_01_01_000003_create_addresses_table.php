<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng addresses: 1 user có thể có NHIỀU địa chỉ giao hàng (sổ địa chỉ).
     * user_id là khóa ngoại trỏ về bảng users.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // xóa user thì xóa luôn địa chỉ của họ
            $table->string('recipient_name'); // tên người nhận (có thể khác tên chủ tài khoản)
            $table->string('phone', 15);
            $table->string('province'); // Tỉnh/Thành phố
            $table->string('district'); // Quận/Huyện
            $table->string('ward');     // Phường/Xã
            $table->string('address_detail'); // số nhà, tên đường
            $table->boolean('is_default')->default(false); // địa chỉ mặc định khi checkout
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
